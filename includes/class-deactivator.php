<?php
/**
 * Classe de désactivation du plugin Block Traiteur
 *
 * @package Block_Traiteur
 * @subpackage Includes
 * @since 1.0.0
 */

// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Classe Block_Traiteur_Deactivator
 * 
 * Gère la désactivation propre du plugin
 */
class Block_Traiteur_Deactivator {
    
    /**
     * Actions à la désactivation du plugin
     */
    public static function deactivate() {
        // Vérifier les permissions
        if (!current_user_can('deactivate_plugins')) {
            wp_die(__('Vous n\'avez pas les permissions nécessaires pour désactiver ce plugin.', 'block-traiteur'));
        }
        
        // Log du début de désactivation
        error_log('Block Traiteur: Début de la désactivation du plugin');
        
        // Nettoyer les tâches cron
        self::clear_scheduled_events();
        
        // Vider les caches
        self::clear_plugin_caches();
        
        // Supprimer les transients temporaires
        self::cleanup_transients();
        
        // Traitement des emails en attente
        self::process_pending_emails();
        
        // Nettoyer les sessions actives
        self::cleanup_active_sessions();
        
        // Supprimer les capacités temporaires
        self::remove_temporary_capabilities();
        
        // Flush des règles de réécriture
        flush_rewrite_rules();
        
        // Marquer la désactivation
        update_option('block_traiteur_deactivated_date', current_time('mysql'));
        delete_option('block_traiteur_activated');
        
        // Créer un rapport de désactivation
        self::create_deactivation_report();
        
        // Log de fin de désactivation
        error_log('Block Traiteur: Plugin désactivé avec succès le ' . current_time('mysql'));
        
        // Transient pour message d'information
        set_transient('block_traiteur_deactivation_notice', true, 30);
    }
    
    /**
     * Nettoyer tous les événements programmés
     */
    private static function clear_scheduled_events() {
        $cron_hooks = array(
            'block_traiteur_cleanup_expired_quotes',
            'block_traiteur_sync_calendar',
            'block_traiteur_cleanup_logs',
            'block_traiteur_weekly_maintenance',
            'block_traiteur_send_pending_emails',
            'block_traiteur_daily_backup',
            'block_traiteur_cache_cleanup'
        );
        
        $cleared_events = 0;
        
        foreach ($cron_hooks as $hook) {
            $timestamp = wp_next_scheduled($hook);
            if ($timestamp) {
                wp_unschedule_event($timestamp, $hook);
                $cleared_events++;
            }
        }
        
        // Supprimer tous les événements restants avec notre préfixe
        $cron_jobs = _get_cron_array();
        if (is_array($cron_jobs)) {
            foreach ($cron_jobs as $timestamp => $jobs) {
                if (is_array($jobs)) {
                    foreach ($jobs as $hook => $job_array) {
                        if (strpos($hook, 'block_traiteur_') === 0) {
                            wp_unschedule_event($timestamp, $hook);
                            $cleared_events++;
                        }
                    }
                }
            }
        }
        
        error_log("Block Traiteur: {$cleared_events} tâches cron supprimées");
    }
    
    /**
     * Vider tous les caches du plugin
     */
    private static function clear_plugin_caches() {
        // Cache WordPress standard
        wp_cache_flush();
        
        // Transients de cache du plugin
        $cache_transients = array(
            'block_traiteur_settings_cache',
            'block_traiteur_products_cache',
            'block_traiteur_beverages_cache',
            'block_traiteur_postal_codes_cache',
            'block_traiteur_price_calculations_cache',
            'block_traiteur_calendar_events_cache',
            'block_traiteur_distance_calculations_cache'
        );
        
        $cleared_caches = 0;
        
        foreach ($cache_transients as $transient) {
            if (delete_transient($transient)) {
                $cleared_caches++;
            }
        }
        
        // Cache personnalisé du plugin
        if (class_exists('Block_Traiteur_Cache')) {
            try {
                Block_Traiteur_Cache::clear_all();
                $cleared_caches++;
            } catch (Exception $e) {
                error_log('Block Traiteur: Erreur lors du nettoyage du cache - ' . $e->getMessage());
            }
        }
        
        // Supprimer les fichiers de cache
        self::clear_cache_files();
        
        error_log("Block Traiteur: {$cleared_caches} caches vidés");
    }
    
    /**
     * Supprimer les fichiers de cache
     */
    private static function clear_cache_files() {
        $upload_dir = wp_upload_dir();
        $cache_dir = $upload_dir['basedir'] . '/block-traiteur/cache/';
        
        if (is_dir($cache_dir)) {
            $files = glob($cache_dir . '*');
            $deleted_files = 0;
            
            foreach ($files as $file) {
                if (is_file($file) && unlink($file)) {
                    $deleted_files++;
                }
            }
            
            error_log("Block Traiteur: {$deleted_files} fichiers de cache supprimés");
        }
    }
    
    /**
     * Nettoyer les transients temporaires
     */
    private static function cleanup_transients() {
        global $wpdb;
        
        // Supprimer tous les transients du plugin
        $transient_keys = $wpdb->get_col(
            "SELECT option_name FROM {$wpdb->options} 
             WHERE option_name LIKE '_transient_block_traiteur_%' 
             OR option_name LIKE '_transient_timeout_block_traiteur_%'"
        );
        
        $deleted_transients = 0;
        
        foreach ($transient_keys as $key) {
            if (delete_option($key)) {
                $deleted_transients++;
            }
        }
        
        // Transients spécifiques
        $specific_transients = array(
            'block_traiteur_activation_notice',
            'block_traiteur_update_check',
            'block_traiteur_system_status',
            'block_traiteur_error_notices'
        );
        
        foreach ($specific_transients as $transient) {
            delete_transient($transient);
            $deleted_transients++;
        }
        
        error_log("Block Traiteur: {$deleted_transients} transients supprimés");
    }
    
    /**
     * Traiter les emails en attente
     */
    private static function process_pending_emails() {
        if (!class_exists('Block_Traiteur_Mailer')) {
            return;
        }
        
        try {
            global $wpdb;
            
            // Compter les emails en attente
            $pending_emails = $wpdb->get_var(
                "SELECT COUNT(*) FROM {$wpdb->prefix}block_emails 
                 WHERE status = 'pending' OR status = 'retry'"
            );
            
            if ($pending_emails > 0) {
                error_log("Block Traiteur: {$pending_emails} emails en attente détectés");
                
                // Option : traiter immédiatement ou marquer comme annulés
                $process_pending = get_option('block_traiteur_process_pending_on_deactivation', false);
                
                if ($process_pending) {
                    // Traiter les emails en attente
                    $mailer = new Block_Traiteur_Mailer();
                    $mailer->process_pending_emails();
                    error_log('Block Traiteur: Emails en attente traités avant désactivation');
                } else {
                    // Marquer comme annulés
                    $wpdb->update(
                        $wpdb->prefix . 'block_emails',
                        array('status' => 'cancelled', 'updated_at' => current_time('mysql')),
                        array('status' => 'pending'),
                        array('%s', '%s'),
                        array('%s')
                    );
                    error_log('Block Traiteur: Emails en attente marqués comme annulés');
                }
            }
        } catch (Exception $e) {
            error_log('Block Traiteur: Erreur traitement emails - ' . $e->getMessage());
        }
    }
    
    /**
     * Nettoyer les sessions actives
     */
    private static function cleanup_active_sessions() {
        // Supprimer les sessions de formulaire en cours
        global $wpdb;
        
        try {
            $wpdb->delete(
                $wpdb->prefix . 'options',
                array('option_name' => array('LIKE' => 'block_traiteur_form_session_%')),
                array('%s')
            );
            
            // Nettoyer les verrous de sécurité
            $wpdb->delete(
                $wpdb->prefix . 'options',
                array('option_name' => array('LIKE' => 'block_traiteur_rate_limit_%')),
                array('%s')
            );
            
            error_log('Block Traiteur: Sessions actives nettoyées');
        } catch (Exception $e) {
            error_log('Block Traiteur: Erreur nettoyage sessions - ' . $e->getMessage());
        }
    }
    
    /**
     * Supprimer les capacités temporaires
     */
    private static function remove_temporary_capabilities() {
        // Note: On garde les capacités principales pour éviter les problèmes
        // lors de la réactivation. Seules les capacités temporaires sont supprimées.
        
        $temporary_capabilities = array(
            'block_traiteur_temp_access',
            'block_traiteur_beta_features'
        );
        
        $roles = wp_roles();
        $removed_caps = 0;
        
        foreach ($roles->roles as $role_name => $role_info) {
            $role = get_role($role_name);
            
            if ($role) {
                foreach ($temporary_capabilities as $cap) {
                    if ($role->has_cap($cap)) {
                        $role->remove_cap($cap);
                        $removed_caps++;
                    }
                }
            }
        }
        
        error_log("Block Traiteur: {$removed_caps} capacités temporaires supprimées");
    }
    
    /**
     * Créer un rapport de désactivation
     */
    private static function create_deactivation_report() {
        try {
            global $wpdb;
            
            // Statistiques d'utilisation
            $stats = array(
                'total_quotes' => $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}block_quotes"),
                'pending_quotes' => $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}block_quotes WHERE status = 'pending'"),
                'approved_quotes' => $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}block_quotes WHERE status = 'approved'"),
                'total_products' => $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}block_products"),
                'total_beverages' => $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}block_beverages"),
                'deactivation_date' => current_time('mysql'),
                'plugin_version' => BLOCK_TRAITEUR_VERSION,
                'wordpress_version' => get_bloginfo('version'),
                'php_version' => PHP_VERSION
            );
            
            // Sauvegarder le rapport
            update_option('block_traiteur_last_deactivation_report', $stats);
            
            // Log du rapport
            error_log('Block Traiteur: Rapport de désactivation créé - ' . json_encode($stats));
            
        } catch (Exception $e) {
            error_log('Block Traiteur: Erreur création rapport - ' . $e->getMessage());
        }
    }
    
    /**
     * Nettoyer les fichiers temporaires
     */
    public static function cleanup_temp_files() {
        $upload_dir = wp_upload_dir();
        $temp_dir = $upload_dir['basedir'] . '/block-traiteur/temp/';
        
        if (is_dir($temp_dir)) {
            $files = glob($temp_dir . '*');
            $deleted_files = 0;
            
            foreach ($files as $file) {
                if (is_file($file)) {
                    // Supprimer les fichiers temporaires (plus de 1 heure)
                    if (time() - filemtime($file) > 3600) {
                        if (unlink($file)) {
                            $deleted_files++;
                        }
                    }
                }
            }
            
            error_log("Block Traiteur: {$deleted_files} fichiers temporaires supprimés");
        }
    }
    
    /**
     * Sauvegarder les données importantes avant désactivation
     */
    public static function backup_important_data() {
        try {
            global $wpdb;
            
            // Sauvegarder les paramètres essentiels
            $important_settings = array();
            $setting_keys = array(
                'block_traiteur_company_name',
                'block_traiteur_company_email',
                'block_traiteur_restaurant_base_price',
                'block_traiteur_remorque_base_price',
                'block_traiteur_google_calendar_enabled',
                'block_traiteur_google_calendar_id'
            );
            
            foreach ($setting_keys as $key) {
                $important_settings[$key] = get_option($key);
            }
            
            // Statistiques d'utilisation
            $usage_stats = array(
                'quotes_count' => $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}block_quotes"),
                'last_quote_date' => $wpdb->get_var("SELECT MAX(created_at) FROM {$wpdb->prefix}block_quotes"),
                'active_products_count' => $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}block_products WHERE status = 'active'")
            );
            
            // Sauvegarder pour réactivation future
            update_option('block_traiteur_backup_settings', $important_settings);
            update_option('block_traiteur_backup_stats', $usage_stats);
            
            error_log('Block Traiteur: Sauvegarde des données importantes effectuée');
            
        } catch (Exception $e) {
            error_log('Block Traiteur: Erreur sauvegarde données - ' . $e->getMessage());
        }
    }
    
    /**
     * Vérifier si la désactivation s'est bien déroulée
     */
    public static function verify_deactivation() {
        $checks = array(
            'cron_jobs_cleared' => !wp_next_scheduled('block_traiteur_cleanup_expired_quotes'),
            'transients_cleared' => !get_transient('block_traiteur_settings_cache'),
            'temp_files_cleaned' => self::verify_temp_cleanup(),
            'backup_created' => (bool) get_option('block_traiteur_backup_settings')
        );
        
        $success = !in_array(false, $checks);
        
        if ($success) {
            error_log('Block Traiteur: Vérification post-désactivation réussie');
        } else {
            error_log('Block Traiteur: Problèmes détectés lors de la désactivation : ' . print_r($checks, true));
        }
        
        return $success;
    }
    
    /**
     * Vérifier le nettoyage des fichiers temporaires
     */
    private static function verify_temp_cleanup() {
        $upload_dir = wp_upload_dir();
        $temp_dir = $upload_dir['basedir'] . '/block-traiteur/temp/';
        
        if (!is_dir($temp_dir)) {
            return true;
        }
        
        $files = glob($temp_dir . '*');
        $old_files = 0;
        
        foreach ($files as $file) {
            if (is_file($file) && (time() - filemtime($file) > 3600)) {
                $old_files++;
            }
        }
        
        return $old_files === 0;
    }
    
    /**
     * Message d'information post-désactivation
     */
    public static function show_deactivation_message() {
        if (get_transient('block_traiteur_deactivation_notice')) {
            ?>
            <div class="notice notice-info is-dismissible">
                <p>
                    <strong><?php _e('Block Traiteur', 'block-traiteur'); ?></strong> : 
                    <?php _e('Plugin désactivé avec succès. Vos données ont été conservées.', 'block-traiteur'); ?>
                </p>
                <p>
                    <em><?php _e('Lors de la réactivation, tous vos paramètres et données seront restaurés.', 'block-traiteur'); ?></em>
                </p>
            </div>
            <?php
            delete_transient('block_traiteur_deactivation_notice');
        }
    }
}