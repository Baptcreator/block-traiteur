<?php
/**
 * Classe de désactivation du plugin Block Traiteur - NOUVELLE ARCHITECTURE
 * 
 * @package Block_Traiteur
 * @since 1.0.0
 */

// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Classe Block_Traiteur_Deactivator - Version refaite
 */
class Block_Traiteur_Deactivator {
    
    /**
     * Méthode de désactivation du plugin - NOUVELLE VERSION
     */
    public static function deactivate() {
        // Vérifier les permissions
        if (!current_user_can('deactivate_plugins')) {
            wp_die(__('Vous n\'avez pas les permissions nécessaires pour désactiver ce plugin.', 'block-traiteur'));
        }
        
        // Vider le cache de réécriture des URLs
        flush_rewrite_rules();
        
        // Nettoyer les tâches cron programmées
        self::clear_scheduled_hooks();
        
        // Nettoyer le cache transient
        self::clear_transients();
        
        // Nettoyer le cache des settings
        self::clear_settings_cache();
        
        // Log de la désactivation
        error_log('Block Traiteur: Plugin désactivé - nouvelle architecture');
        
        // ATTENTION: Ne pas supprimer les tables par défaut
        // Décommenter seulement si nécessaire : self::drop_new_tables();
    }
    
    /**
     * Nettoyer les hooks programmés
     */
    private static function clear_scheduled_hooks() {
        // Nettoyer les événements cron du plugin
        wp_clear_scheduled_hook('block_traiteur_daily_cleanup');
        wp_clear_scheduled_hook('block_traiteur_weekly_reports');
        wp_clear_scheduled_hook('block_traiteur_email_queue');
        
        error_log('Block Traiteur: Tâches cron nettoyées');
    }
    
    /**
     * Nettoyer les transients
     */
    private static function clear_transients() {
        global $wpdb;
        
        // Supprimer tous les transients du plugin
        $wpdb->query("
            DELETE FROM {$wpdb->options} 
            WHERE option_name LIKE '_transient_block_traiteur_%' 
            OR option_name LIKE '_transient_timeout_block_traiteur_%'
        ");
        
        error_log('Block Traiteur: Transients nettoyés');
    }
    
    /**
     * Nettoyer le cache des settings
     */
    private static function clear_settings_cache() {
        // Forcer le rechargement du cache des settings au prochain chargement
        delete_transient('block_traiteur_settings_cache');
        
        error_log('Block Traiteur: Cache des settings nettoyé');
    }
    
    /**
     * Supprimer les NOUVELLES tables (à utiliser avec EXTRÊME précaution)
     * ATTENTION: Ceci supprimera TOUTES les données !
     */
    private static function drop_new_tables() {
        if (file_exists(BLOCK_TRAITEUR_PLUGIN_DIR . 'includes/class-database.php')) {
            require_once BLOCK_TRAITEUR_PLUGIN_DIR . 'includes/class-database.php';
            
            if (class_exists('Block_Traiteur_Database')) {
                Block_Traiteur_Database::drop_tables();
                error_log('Block Traiteur: NOUVELLES tables supprimées');
            }
        }
    }
    
    /**
     * Supprimer toutes les options du plugin (optionnel)
     */
    private static function delete_options() {
        global $wpdb;
        
        // Supprimer toutes les options du plugin
        $wpdb->query("
            DELETE FROM {$wpdb->options} 
            WHERE option_name LIKE 'block_traiteur_%'
        ");
        
        error_log('Block Traiteur: Options supprimées');
    }
}