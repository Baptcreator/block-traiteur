<?php
/**
 * Classe de gestion des mises à jour du plugin Block Traiteur
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
 * Classe Block_Traiteur_Updater
 * 
 * Gère les mises à jour automatiques et manuelles du plugin
 */
class Block_Traiteur_Updater {
    
    /**
     * Version actuelle du plugin
     */
    private $current_version;
    
    /**
     * Dernière version disponible
     */
    private $latest_version;
    
    /**
     * URL du serveur de mise à jour
     */
    private $update_server_url = 'https://updates.block-strasbourg.fr/api/v1/';
    
    /**
     * Slug du plugin
     */
    private $plugin_slug;
    
    /**
     * Chemin du fichier principal du plugin
     */
    private $plugin_file;
    
    /**
     * Données de mise à jour
     */
    private $update_data = null;
    
    /**
     * Constructeur
     */
    public function __construct() {
        $this->current_version = BLOCK_TRAITEUR_VERSION;
        $this->plugin_slug = plugin_basename(BLOCK_TRAITEUR_PLUGIN_FILE);
        $this->plugin_file = BLOCK_TRAITEUR_PLUGIN_FILE;
        
        $this->init_hooks();
    }
    
    /**
     * Initialisation des hooks WordPress
     */
    private function init_hooks() {
        // Vérifications automatiques de mise à jour
        add_filter('pre_set_site_transient_update_plugins', array($this, 'check_for_updates'));
        
        // Informations sur la mise à jour
        add_filter('plugins_api', array($this, 'plugin_update_info'), 20, 3);
        
        // Actions après mise à jour
        add_action('upgrader_process_complete', array($this, 'after_update'), 10, 2);
        
        // Vérification manuelle
        add_action('wp_ajax_block_traiteur_check_updates', array($this, 'manual_update_check'));
        
        // Nettoyage des transients
        add_action('admin_init', array($this, 'cleanup_update_cache'));
        
        // Notifications d'administration
        add_action('admin_notices', array($this, 'update_notices'));
        
        // Vérification périodique
        add_action('block_traiteur_daily_maintenance', array($this, 'daily_update_check'));
        
        // Hooks de licence (si applicable)
        add_action('wp_ajax_block_traiteur_activate_license', array($this, 'activate_license'));
        add_action('wp_ajax_block_traiteur_deactivate_license', array($this, 'deactivate_license'));
    }
    
    /**
     * Vérification des mises à jour
     */
    public function check_for_updates($transient) {
        if (empty($transient->checked)) {
            return $transient;
        }
        
        // Ne vérifier que si nécessaire
        $last_check = get_transient('block_traiteur_update_check');
        if ($last_check && (time() - $last_check) < HOUR_IN_SECONDS) {
            return $transient;
        }
        
        $remote_version = $this->get_remote_version();
        
        if ($remote_version && version_compare($this->current_version, $remote_version, '<')) {
            $update_data = $this->get_update_data();
            
            if ($update_data) {
                $transient->response[$this->plugin_slug] = (object) array(
                    'slug' => dirname($this->plugin_slug),
                    'plugin' => $this->plugin_slug,
                    'new_version' => $remote_version,
                    'url' => $update_data['details_url'] ?? '',
                    'package' => $update_data['download_url'] ?? '',
                    'icons' => $update_data['icons'] ?? array(),
                    'banners' => $update_data['banners'] ?? array(),
                    'banners_rtl' => $update_data['banners_rtl'] ?? array(),
                    'tested' => $update_data['tested'] ?? '',
                    'requires_php' => $update_data['requires_php'] ?? '7.4',
                    'compatibility' => $update_data['compatibility'] ?? array()
                );
            }
        }
        
        set_transient('block_traiteur_update_check', time(), HOUR_IN_SECONDS);
        
        return $transient;
    }
    
    /**
     * Récupération de la version distante
     */
    private function get_remote_version() {
        $request = wp_remote_get($this->update_server_url . 'version', array(
            'timeout' => 10,
            'headers' => array(
                'User-Agent' => 'Block-Traiteur/' . $this->current_version . '; ' . home_url(),
                'Content-Type' => 'application/json'
            ),
            'body' => json_encode(array(
                'plugin' => 'block-traiteur',
                'version' => $this->current_version,
                'site_url' => home_url(),
                'license_key' => $this->get_license_key()
            ))
        ));
        
        if (is_wp_error($request)) {
            $this->log_error('Erreur lors de la vérification de version: ' . $request->get_error_message());
            return false;
        }
        
        $body = wp_remote_retrieve_body($request);
        $response_code = wp_remote_retrieve_response_code($request);
        
        if ($response_code !== 200) {
            $this->log_error('Réponse invalide du serveur de mise à jour: ' . $response_code);
            return false;
        }
        
        $data = json_decode($body, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->log_error('Réponse JSON invalide du serveur');
            return false;
        }
        
        if (isset($data['version'])) {
            $this->latest_version = $data['version'];
            set_transient('block_traiteur_latest_version', $data['version'], DAY_IN_SECONDS);
            return $data['version'];
        }
        
        return false;
    }
    
    /**
     * Récupération des données de mise à jour
     */
    private function get_update_data() {
        $cached_data = get_transient('block_traiteur_update_data');
        if ($cached_data) {
            return $cached_data;
        }
        
        $request = wp_remote_get($this->update_server_url . 'info', array(
            'timeout' => 15,
            'headers' => array(
                'User-Agent' => 'Block-Traiteur/' . $this->current_version . '; ' . home_url(),
                'Content-Type' => 'application/json'
            ),
            'body' => json_encode(array(
                'plugin' => 'block-traiteur',
                'version' => $this->current_version,
                'site_url' => home_url(),
                'license_key' => $this->get_license_key()
            ))
        ));
        
        if (is_wp_error($request)) {
            return false;
        }
        
        $body = wp_remote_retrieve_body($request);
        $data = json_decode($body, true);
        
        if (json_last_error() !== JSON_ERROR_NONE || !isset($data['update_available'])) {
            return false;
        }
        
        if ($data['update_available']) {
            set_transient('block_traiteur_update_data', $data, HOUR_IN_SECONDS * 6);
            return $data;
        }
        
        return false;
    }
    
    /**
     * Informations détaillées sur la mise à jour
     */
    public function plugin_update_info($result, $action, $args) {
        if ($action !== 'plugin_information') {
            return $result;
        }
        
        if (!isset($args->slug) || $args->slug !== dirname($this->plugin_slug)) {
            return $result;
        }
        
        $update_data = $this->get_update_data();
        
        if (!$update_data) {
            return $result;
        }
        
        return (object) array(
            'name' => $update_data['name'] ?? 'Block Traiteur',
            'slug' => dirname($this->plugin_slug),
            'version' => $update_data['version'] ?? $this->latest_version,
            'author' => $update_data['author'] ?? 'Block Strasbourg',
            'author_profile' => $update_data['author_profile'] ?? 'https://block-strasbourg.fr',
            'contributors' => $update_data['contributors'] ?? array(),
            'requires' => $update_data['requires'] ?? '6.0',
            'tested' => $update_data['tested'] ?? get_bloginfo('version'),
            'requires_php' => $update_data['requires_php'] ?? '7.4',
            'sections' => array(
                'description' => $update_data['description'] ?? '',
                'changelog' => $this->format_changelog($update_data['changelog'] ?? ''),
                'installation' => $update_data['installation'] ?? '',
                'faq' => $update_data['faq'] ?? ''
            ),
            'short_description' => $update_data['short_description'] ?? '',
            'download_link' => $update_data['download_url'] ?? '',
            'trunk' => $update_data['download_url'] ?? '',
            'icons' => $update_data['icons'] ?? array(),
            'banners' => $update_data['banners'] ?? array(),
            'last_updated' => $update_data['last_updated'] ?? date('Y-m-d'),
            'added' => $update_data['added'] ?? '',
            'homepage' => $update_data['homepage'] ?? 'https://block-strasbourg.fr',
            'rating' => (float) ($update_data['rating'] ?? 100),
            'ratings' => $update_data['ratings'] ?? array(5 => 1),
            'num_ratings' => (int) ($update_data['num_ratings'] ?? 1),
            'downloaded' => (int) ($update_data['downloaded'] ?? 0),
            'active_installs' => (int) ($update_data['active_installs'] ?? 1)
        );
    }
    
    /**
     * Formatage du changelog
     */
    private function format_changelog($changelog) {
        if (empty($changelog)) {
            return '<p>Aucune information de changelog disponible.</p>';
        }
        
        // Si c'est déjà du HTML, on le retourne tel quel
        if (strpos($changelog, '<') !== false) {
            return $changelog;
        }
        
        // Convertir le markdown simple en HTML
        $formatted = nl2br(esc_html($changelog));
        $formatted = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $formatted);
        $formatted = preg_replace('/\*(.*?)\*/', '<em>$1</em>', $formatted);
        $formatted = preg_replace('/^= (.*?) =$/m', '<h4>$1</h4>', $formatted);
        $formatted = preg_replace('/^\* (.*?)$/m', '<li>$1</li>', $formatted);
        $formatted = preg_replace('/(<li>.*<\/li>)/s', '<ul>$1</ul>', $formatted);
        
        return $formatted;
    }
    
    /**
     * Actions après mise à jour
     */
    public function after_update($upgrader_object, $options) {
        if ($options['action'] !== 'update' || $options['type'] !== 'plugin') {
            return;
        }
        
        if (!isset($options['plugins']) || !is_array($options['plugins'])) {
            return;
        }
        
        if (in_array($this->plugin_slug, $options['plugins'])) {
            // Nettoyer les caches
            $this->cleanup_update_cache();
            
            // Vérifier l'intégrité après mise à jour
            $this->verify_update_integrity();
            
            // Exécuter les migrations si nécessaires
            $this->run_update_migrations();
            
            // Log de la mise à jour
            $this->log_update_success();
            
            // Déclencher un hook personnalisé
            do_action('block_traiteur_plugin_updated', $this->current_version, $this->latest_version);
        }
    }
    
    /**
     * Vérification de l'intégrité après mise à jour
     */
    private function verify_update_integrity() {
        // Vérifier que les fichiers essentiels sont présents
        $essential_files = array(
            'block-traiteur.php',
            'includes/class-block-traiteur.php',
            'includes/class-database.php',
            'admin/class-admin.php',
            'public/class-public.php'
        );
        
        $missing_files = array();
        
        foreach ($essential_files as $file) {
            $file_path = BLOCK_TRAITEUR_PLUGIN_DIR . $file;
            if (!file_exists($file_path)) {
                $missing_files[] = $file;
            }
        }
        
        if (!empty($missing_files)) {
            $this->log_error('Fichiers manquants après mise à jour: ' . implode(', ', $missing_files));
            
            // Tenter une réparation automatique
            $this->attempt_repair();
        }
        
        // Vérifier la version dans la base de données
        $db_version = get_option('block_traiteur_version');
        if (version_compare($db_version, $this->current_version, '<')) {
            update_option('block_traiteur_version', $this->current_version);
        }
    }
    
    /**
     * Exécution des migrations de mise à jour
     */
    private function run_update_migrations() {
        $db_version = get_option('block_traiteur_db_version', '0.0.0');
        
        // Migrations par version
        if (version_compare($db_version, '1.0.1', '<')) {
            $this->migrate_to_1_0_1();
        }
        
        if (version_compare($db_version, '1.1.0', '<')) {
            $this->migrate_to_1_1_0();
        }
        
        // Mettre à jour la version de la base de données
        update_option('block_traiteur_db_version', BLOCK_TRAITEUR_VERSION);
    }
    
    /**
     * Migration vers la version 1.0.1
     */
    private function migrate_to_1_0_1() {
        global $wpdb;
        
        // Exemple: Ajouter une nouvelle colonne
        $table_name = $wpdb->prefix . 'block_quotes';
        $column_exists = $wpdb->get_results("SHOW COLUMNS FROM {$table_name} LIKE 'new_column'");
        
        if (empty($column_exists)) {
            $wpdb->query("ALTER TABLE {$table_name} ADD COLUMN new_column VARCHAR(255) DEFAULT NULL");
        }
        
        $this->log_info('Migration 1.0.1 terminée');
    }
    
    /**
     * Migration vers la version 1.1.0
     */
    private function migrate_to_1_1_0() {
        global $wpdb;
        
        // Exemple: Modification de structure
        $table_name = $wpdb->prefix . 'block_settings';
        $wpdb->query("ALTER TABLE {$table_name} MODIFY COLUMN setting_value LONGTEXT");
        
        $this->log_info('Migration 1.1.0 terminée');
    }
    
    /**
     * Tentative de réparation automatique
     */
    private function attempt_repair() {
        // Réactiver le plugin pour déclencher les vérifications d'intégrité
        if (class_exists('Block_Traiteur_Installer')) {
            Block_Traiteur_Installer::repair_installation();
        }
        
        $this->log_info('Tentative de réparation automatique effectuée');
    }
    
    /**
     * Vérification manuelle des mises à jour
     */
    public function manual_update_check() {
        if (!current_user_can('update_plugins')) {
            wp_die(__('Permissions insuffisantes.', 'block-traiteur'));
        }
        
        check_ajax_referer('block_traiteur_admin', 'nonce');
        
        // Forcer la vérification en supprimant les transients
        delete_transient('block_traiteur_update_check');
        delete_transient('block_traiteur_latest_version');
        delete_transient('block_traiteur_update_data');
        
        $remote_version = $this->get_remote_version();
        
        if ($remote_version && version_compare($this->current_version, $remote_version, '<')) {
            wp_send_json_success(array(
                'update_available' => true,
                'current_version' => $this->current_version,
                'latest_version' => $remote_version,
                'message' => sprintf(
                    __('Une mise à jour est disponible : version %s', 'block-traiteur'),
                    $remote_version
                )
            ));
        } else {
            wp_send_json_success(array(
                'update_available' => false,
                'current_version' => $this->current_version,
                'message' => __('Vous utilisez déjà la dernière version.', 'block-traiteur')
            ));
        }
    }
    
    /**
     * Nettoyage du cache de mise à jour
     */
    public function cleanup_update_cache() {
        if (isset($_GET['force-check']) && current_user_can('update_plugins')) {
            delete_transient('block_traiteur_update_check');
            delete_transient('block_traiteur_latest_version');
            delete_transient('block_traiteur_update_data');
        }
    }
    
    /**
     * Notifications d'administration
     */
    public function update_notices() {
        if (!current_user_can('update_plugins')) {
            return;
        }
        
        $screen = get_current_screen();
        if (!in_array($screen->id, array('dashboard', 'plugins', 'toplevel_page_block-traiteur'))) {
            return;
        }
        
        $latest_version = get_transient('block_traiteur_latest_version');
        
        if ($latest_version && version_compare($this->current_version, $latest_version, '<')) {
            $update_url = wp_nonce_url(
                self_admin_url('update.php?action=upgrade-plugin&plugin=' . urlencode($this->plugin_slug)),
                'upgrade-plugin_' . $this->plugin_slug
            );
            
            echo '<div class="notice notice-warning is-dismissible">';
            echo '<p><strong>' . __('Block Traiteur', 'block-traiteur') . '</strong> : ';
            echo sprintf(
                __('Une nouvelle version (%s) est disponible. %s', 'block-traiteur'),
                $latest_version,
                '<a href="' . esc_url($update_url) . '">' . __('Mettre à jour maintenant', 'block-traiteur') . '</a>'
            );
            echo '</p>';
            echo '</div>';
        }
    }
    
    /**
     * Vérification quotidienne des mises à jour
     */
    public function daily_update_check() {
        $this->get_remote_version();
    }
    
    /**
     * Récupération de la clé de licence
     */
    private function get_license_key() {
        return get_option('block_traiteur_license_key', '');
    }
    
    /**
     * Activation de licence
     */
    public function activate_license() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Permissions insuffisantes.', 'block-traiteur'));
        }
        
        check_ajax_referer('block_traiteur_admin', 'nonce');
        
        $license_key = sanitize_text_field($_POST['license_key'] ?? '');
        
        if (empty($license_key)) {
            wp_send_json_error(array('message' => __('Clé de licence requise.', 'block-traiteur')));
        }
        
        $request = wp_remote_post($this->update_server_url . 'license/activate', array(
            'timeout' => 15,
            'body' => array(
                'license_key' => $license_key,
                'site_url' => home_url(),
                'plugin' => 'block-traiteur'
            )
        ));
        
        if (is_wp_error($request)) {
            wp_send_json_error(array('message' => $request->get_error_message()));
        }
        
        $body = wp_remote_retrieve_body($request);
        $response = json_decode($body, true);
        
        if (isset($response['success']) && $response['success']) {
            update_option('block_traiteur_license_key', $license_key);
            update_option('block_traiteur_license_status', 'active');
            
            wp_send_json_success(array('message' => __('Licence activée avec succès.', 'block-traiteur')));
        } else {
            wp_send_json_error(array('message' => $response['message'] ?? __('Erreur d\'activation.', 'block-traiteur')));
        }
    }
    
    /**
     * Désactivation de licence
     */
    public function deactivate_license() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Permissions insuffisantes.', 'block-traiteur'));
        }
        
        check_ajax_referer('block_traiteur_admin', 'nonce');
        
        $license_key = $this->get_license_key();
        
        if (empty($license_key)) {
            wp_send_json_error(array('message' => __('Aucune licence active.', 'block-traiteur')));
        }
        
        $request = wp_remote_post($this->update_server_url . 'license/deactivate', array(
            'timeout' => 15,
            'body' => array(
                'license_key' => $license_key,
                'site_url' => home_url(),
                'plugin' => 'block-traiteur'
            )
        ));
        
        if (!is_wp_error($request)) {
            delete_option('block_traiteur_license_key');
            delete_option('block_traiteur_license_status');
        }
        
        wp_send_json_success(array('message' => __('Licence désactivée.', 'block-traiteur')));
    }
    
    /**
     * Récupération du statut de licence
     */
    public function get_license_status() {
        $status = get_option('block_traiteur_license_status', 'inactive');
        $key = $this->get_license_key();
        
        return array(
            'status' => $status,
            'key' => $key ? substr($key, 0, 8) . '...' : '',
            'has_key' => !empty($key)
        );
    }
    
    /**
     * Téléchargement manuel de mise à jour
     */
    public function download_update() {
        if (!current_user_can('update_plugins')) {
            return false;
        }
        
        $update_data = $this->get_update_data();
        
        if (!$update_data || empty($update_data['download_url'])) {
            return false;
        }
        
        $download_url = $update_data['download_url'];
        
        // Ajouter la clé de licence si nécessaire
        $license_key = $this->get_license_key();
        if ($license_key) {
            $download_url = add_query_arg('license_key', $license_key, $download_url);
        }
        
        return $download_url;
    }
    
    /**
     * Sauvegarde avant mise à jour
     */
    public function create_backup() {
        $backup_dir = WP_CONTENT_DIR . '/backups/block-traiteur/';
        
        if (!file_exists($backup_dir)) {
            wp_mkdir_p($backup_dir);
        }
        
        $backup_file = $backup_dir . 'backup-' . date('Y-m-d-H-i-s') . '.zip';
        
        // Créer une archive du plugin actuel
        if (class_exists('ZipArchive')) {
            $zip = new ZipArchive();
            
            if ($zip->open($backup_file, ZipArchive::CREATE) === TRUE) {
                $this->add_folder_to_zip(BLOCK_TRAITEUR_PLUGIN_DIR, $zip, 'block-traiteur/');
                $zip->close();
                
                $this->log_info('Sauvegarde créée: ' . $backup_file);
                return $backup_file;
            }
        }
        
        return false;
    }
    
    /**
     * Ajouter un dossier à un ZIP
     */
    private function add_folder_to_zip($folder, &$zipFile, $exclusiveLength) {
        $handle = opendir($folder);
        
        while (false !== $f = readdir($handle)) {
            if ($f != '.' && $f != '..') {
                $filePath = $folder . '/' . $f;
                $localPath = substr($filePath, $exclusiveLength);
                
                if (is_file($filePath)) {
                    $zipFile->addFile($filePath, $localPath);
                } elseif (is_dir($filePath)) {
                    $zipFile->addEmptyDir($localPath);
                    $this->add_folder_to_zip($filePath, $zipFile, $exclusiveLength);
                }
            }
        }
        
        closedir($handle);
    }
    
    /**
     * Log de succès de mise à jour
     */
    private function log_update_success() {
        $message = sprintf(
            'Plugin Block Traiteur mis à jour avec succès de la version %s vers %s',
            $this->current_version,
            $this->latest_version
        );
        
        $this->log_info($message);
        
        // Enregistrer dans l'historique des mises à jour
        $update_history = get_option('block_traiteur_update_history', array());
        $update_history[] = array(
            'from_version' => $this->current_version,
            'to_version' => $this->latest_version,
            'date' => current_time('mysql'),
            'success' => true
        );
        
        // Garder seulement les 10 dernières mises à jour
        $update_history = array_slice($update_history, -10);
        update_option('block_traiteur_update_history', $update_history);
    }
    
    /**
     * Log d'information
     */
    private function log_info($message) {
        if (class_exists('Block_Traiteur_Logger')) {
            Block_Traiteur_Logger::info($message, array('component' => 'updater'));
        } else {
            error_log('[Block Traiteur Updater] ' . $message);
        }
    }
    
    /**
     * Log d'erreur
     */
    private function log_error($message) {
        if (class_exists('Block_Traiteur_Logger')) {
            Block_Traiteur_Logger::error($message, array('component' => 'updater'));
        } else {
            error_log('[Block Traiteur Updater ERROR] ' . $message);
        }
    }
    
    /**
     * Récupération de l'historique des mises à jour
     */
    public function get_update_history() {
        return get_option('block_traiteur_update_history', array());
    }
    
    /**
     * Vérification de la compatibilité
     */
    public function check_compatibility() {
        $update_data = $this->get_update_data();
        
        if (!$update_data) {
            return true;
        }
        
        $issues = array();
        
        // Vérifier PHP
        if (isset($update_data['requires_php'])) {
            if (version_compare(PHP_VERSION, $update_data['requires_php'], '<')) {
                $issues[] = sprintf(
                    __('PHP %s requis (version actuelle: %s)', 'block-traiteur'),
                    $update_data['requires_php'],
                    PHP_VERSION
                );
            }
        }
        
        // Vérifier WordPress
        if (isset($update_data['requires'])) {
            if (version_compare(get_bloginfo('version'), $update_data['requires'], '<')) {
                $issues[] = sprintf(
                    __('WordPress %s requis (version actuelle: %s)', 'block-traiteur'),
                    $update_data['requires'],
                    get_bloginfo('version')
                );
            }
        }
        
        return empty($issues) ? true : $issues;
    }
    
    /**
     * Planification de mise à jour automatique
     */
    public function schedule_auto_update() {
        if (!wp_next_scheduled('block_traiteur_auto_update')) {
            wp_schedule_event(time(), 'daily', 'block_traiteur_auto_update');
        }
        
        add_action('block_traiteur_auto_update', array($this, 'perform_auto_update'));
    }
    
    /**
     * Exécution de mise à jour automatique
     */
    public function perform_auto_update() {
        if (!get_option('block_traiteur_auto_updates_enabled', false)) {
            return;
        }
        
        $remote_version = $this->get_remote_version();
        
        if ($remote_version && version_compare($this->current_version, $remote_version, '<')) {
            // Vérifier la compatibilité
            $compatibility = $this->check_compatibility();
            
            if ($compatibility !== true) {
                $this->log_error('Mise à jour automatique annulée: incompatibilités détectées - ' . implode(', ', $compatibility));
                return;
            }
            
            // Créer une sauvegarde
            $backup_file = $this->create_backup();
            
            if (!$backup_file) {
                $this->log_error('Mise à jour automatique annulée: impossible de créer une sauvegarde');
                return;
            }
            
            // Effectuer la mise à jour
            include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
            include_once ABSPATH . 'wp-admin/includes/plugin-install.php';
            
            $upgrader = new Plugin_Upgrader(new Automatic_Upgrader_Skin());
            $result = $upgrader->upgrade($this->plugin_slug);
            
            if (is_wp_error($result)) {
                $this->log_error('Mise à jour automatique échouée: ' . $result->get_error_message());
                
                // Tenter de restaurer la sauvegarde
                $this->restore_backup($backup_file);
            } else {
                $this->log_info('Mise à jour automatique réussie vers la version ' . $remote_version);
                
                // Nettoyer les anciennes sauvegardes
                $this->cleanup_old_backups();
            }
        }
    }
    
    /**
     * Restauration depuis une sauvegarde
     */
    private function restore_backup($backup_file) {
        if (!file_exists($backup_file) || !class_exists('ZipArchive')) {
            return false;
        }
        
        $zip = new ZipArchive();
        
        if ($zip->open($backup_file) === TRUE) {
            $temp_dir = WP_CONTENT_DIR . '/temp/block-traiteur-restore/';
            
            if (!file_exists($temp_dir)) {
                wp_mkdir_p($temp_dir);
            }
            
            $zip->extractTo($temp_dir);
            $zip->close();
            
            // Remplacer les fichiers actuels
            $this->copy_directory($temp_dir . 'block-traiteur/', BLOCK_TRAITEUR_PLUGIN_DIR);
            
            // Nettoyer le dossier temporaire
            $this->delete_directory($temp_dir);
            
            $this->log_info('Restauration depuis la sauvegarde effectuée: ' . $backup_file);
            return true;
        }
        
        return false;
    }
    
    /**
     * Copie récursive de dossier
     */
    private function copy_directory($source, $destination) {
        if (!is_dir($source)) {
            return false;
        }
        
        if (!is_dir($destination)) {
            wp_mkdir_p($destination);
        }
        
        $handle = opendir($source);
        
        while (false !== ($file = readdir($handle))) {
            if ($file != '.' && $file != '..') {
                $source_path = $source . $file;
                $dest_path = $destination . $file;
                
                if (is_dir($source_path)) {
                    $this->copy_directory($source_path . '/', $dest_path . '/');
                } else {
                    copy($source_path, $dest_path);
                }
            }
        }
        
        closedir($handle);
        return true;
    }
    
    /**
     * Suppression récursive de dossier
     */
    private function delete_directory($dir) {
        if (!is_dir($dir)) {
            return false;
        }
        
        $files = array_diff(scandir($dir), array('.', '..'));
        
        foreach ($files as $file) {
            $path = $dir . $file;
            
            if (is_dir($path)) {
                $this->delete_directory($path . '/');
            } else {
                unlink($path);
            }
        }
        
        return rmdir($dir);
    }
    
    /**
     * Nettoyage des anciennes sauvegardes
     */
    private function cleanup_old_backups() {
        $backup_dir = WP_CONTENT_DIR . '/backups/block-traiteur/';
        
        if (!is_dir($backup_dir)) {
            return;
        }
        
        $files = glob($backup_dir . 'backup-*.zip');
        
        if (count($files) > 5) {
            // Trier par date de modification
            usort($files, function($a, $b) {
                return filemtime($a) - filemtime($b);
            });
            
            // Supprimer les plus anciens, garder les 5 derniers
            $files_to_delete = array_slice($files, 0, -5);
            
            foreach ($files_to_delete as $file) {
                unlink($file);
            }
            
            $this->log_info('Nettoyage effectué: ' . count($files_to_delete) . ' anciennes sauvegardes supprimées');
        }
    }
    
    /**
     * Configuration des mises à jour automatiques
     */
    public function configure_auto_updates($enabled = false, $type = 'minor') {
        update_option('block_traiteur_auto_updates_enabled', $enabled);
        update_option('block_traiteur_auto_updates_type', $type); // 'minor', 'major', 'all'
        
        if ($enabled) {
            $this->schedule_auto_update();
        } else {
            wp_clear_scheduled_hook('block_traiteur_auto_update');
        }
        
        $this->log_info('Configuration des mises à jour automatiques: ' . ($enabled ? 'activées' : 'désactivées'));
    }
    
    /**
     * Vérification de type de mise à jour
     */
    private function should_auto_update($current_version, $new_version) {
        $auto_update_type = get_option('block_traiteur_auto_updates_type', 'minor');
        
        $current_parts = explode('.', $current_version);
        $new_parts = explode('.', $new_version);
        
        switch ($auto_update_type) {
            case 'major':
                return $new_parts[0] > $current_parts[0];
                
            case 'minor':
                return ($new_parts[0] == $current_parts[0] && $new_parts[1] > $current_parts[1]) ||
                       ($new_parts[0] == $current_parts[0] && $new_parts[1] == $current_parts[1] && $new_parts[2] > $current_parts[2]);
                
            case 'all':
                return version_compare($new_version, $current_version, '>');
                
            default:
                return false;
        }
    }
    
    /**
     * Notification de mise à jour disponible
     */
    public function send_update_notification($version) {
        $admin_email = get_option('admin_email');
        $site_name = get_bloginfo('name');
        
        $subject = sprintf(
            __('[%s] Mise à jour Block Traiteur disponible', 'block-traiteur'),
            $site_name
        );
        
        $message = sprintf(
            __('Une nouvelle version de Block Traiteur est disponible pour votre site %s.

Version actuelle : %s
Nouvelle version : %s

Vous pouvez mettre à jour le plugin depuis votre tableau de bord WordPress :
%s

Pour plus d\'informations sur cette mise à jour, consultez :
%s', 'block-traiteur'),
            $site_name,
            $this->current_version,
            $version,
            admin_url('plugins.php'),
            'https://block-strasbourg.fr/changelog'
        );
        
        wp_mail($admin_email, $subject, $message);
        
        $this->log_info('Notification de mise à jour envoyée à ' . $admin_email);
    }
    
    /**
     * Rollback vers une version précédente
     */
    public function rollback_to_version($target_version) {
        if (!current_user_can('update_plugins')) {
            return new WP_Error('insufficient_permissions', __('Permissions insuffisantes.', 'block-traiteur'));
        }
        
        // Vérifier si la version cible est disponible
        $available_versions = $this->get_available_versions();
        
        if (!in_array($target_version, $available_versions)) {
            return new WP_Error('version_not_available', __('Version non disponible pour le rollback.', 'block-traiteur'));
        }
        
        // Créer une sauvegarde avant rollback
        $backup_file = $this->create_backup();
        
        if (!$backup_file) {
            return new WP_Error('backup_failed', __('Impossible de créer une sauvegarde.', 'block-traiteur'));
        }
        
        // Télécharger et installer la version cible
        $download_url = $this->get_version_download_url($target_version);
        
        if (!$download_url) {
            return new WP_Error('download_failed', __('Impossible de télécharger la version cible.', 'block-traiteur'));
        }
        
        include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        
        $upgrader = new Plugin_Upgrader(new Automatic_Upgrader_Skin());
        $result = $upgrader->install($download_url);
        
        if (is_wp_error($result)) {
            return $result;
        }
        
        $this->log_info('Rollback effectué vers la version ' . $target_version);
        
        return true;
    }
    
    /**
     * Récupération des versions disponibles
     */
    private function get_available_versions() {
        $request = wp_remote_get($this->update_server_url . 'versions', array(
            'timeout' => 10,
            'headers' => array(
                'User-Agent' => 'Block-Traiteur/' . $this->current_version . '; ' . home_url()
            )
        ));
        
        if (is_wp_error($request)) {
            return array();
        }
        
        $body = wp_remote_retrieve_body($request);
        $data = json_decode($body, true);
        
        return isset($data['versions']) ? $data['versions'] : array();
    }
    
    /**
     * URL de téléchargement pour une version spécifique
     */
    private function get_version_download_url($version) {
        $request = wp_remote_get($this->update_server_url . 'download/' . $version, array(
            'timeout' => 10,
            'headers' => array(
                'User-Agent' => 'Block-Traiteur/' . $this->current_version . '; ' . home_url()
            ),
            'body' => array(
                'license_key' => $this->get_license_key(),
                'site_url' => home_url()
            )
        ));
        
        if (is_wp_error($request)) {
            return false;
        }
        
        $body = wp_remote_retrieve_body($request);
        $data = json_decode($body, true);
        
        return isset($data['download_url']) ? $data['download_url'] : false;
    }
    
    /**
     * Vérification de l'intégrité des fichiers
     */
    public function verify_file_integrity() {
        $checksums = $this->get_file_checksums();
        
        if (!$checksums) {
            return new WP_Error('no_checksums', __('Impossible de récupérer les checksums.', 'block-traiteur'));
        }
        
        $failed_files = array();
        
        foreach ($checksums as $file => $expected_hash) {
            $file_path = BLOCK_TRAITEUR_PLUGIN_DIR . $file;
            
            if (!file_exists($file_path)) {
                $failed_files[] = $file . ' (manquant)';
                continue;
            }
            
            $actual_hash = hash_file('sha256', $file_path);
            
            if ($actual_hash !== $expected_hash) {
                $failed_files[] = $file . ' (modifié)';
            }
        }
        
        if (!empty($failed_files)) {
            return new WP_Error('integrity_check_failed', __('Vérification d\'intégrité échouée: ', 'block-traiteur') . implode(', ', $failed_files));
        }
        
        return true;
    }
    
    /**
     * Récupération des checksums des fichiers
     */
    private function get_file_checksums() {
        $request = wp_remote_get($this->update_server_url . 'checksums/' . $this->current_version, array(
            'timeout' => 10,
            'headers' => array(
                'User-Agent' => 'Block-Traiteur/' . $this->current_version . '; ' . home_url()
            )
        ));
        
        if (is_wp_error($request)) {
            return false;
        }
        
        $body = wp_remote_retrieve_body($request);
        $data = json_decode($body, true);
        
        return isset($data['checksums']) ? $data['checksums'] : false;
    }
    
    /**
     * Statistiques de mise à jour
     */
    public function get_update_stats() {
        $history = $this->get_update_history();
        $total_updates = count($history);
        $successful_updates = count(array_filter($history, function($update) {
            return $update['success'];
        }));
        
        $last_update = !empty($history) ? end($history) : null;
        $last_check = get_transient('block_traiteur_update_check');
        
        return array(
            'total_updates' => $total_updates,
            'successful_updates' => $successful_updates,
            'success_rate' => $total_updates > 0 ? round(($successful_updates / $total_updates) * 100, 2) : 0,
            'last_update' => $last_update,
            'last_check' => $last_check ? date('Y-m-d H:i:s', $last_check) : null,
            'current_version' => $this->current_version,
            'latest_version' => get_transient('block_traiteur_latest_version'),
            'auto_updates_enabled' => get_option('block_traiteur_auto_updates_enabled', false),
            'license_status' => $this->get_license_status()
        );
    }
    
    /**
     * Test de connectivité au serveur de mise à jour
     */
    public function test_update_server() {
        $start_time = microtime(true);
        
        $request = wp_remote_get($this->update_server_url . 'ping', array(
            'timeout' => 5,
            'headers' => array(
                'User-Agent' => 'Block-Traiteur/' . $this->current_version . '; ' . home_url()
            )
        ));
        
        $end_time = microtime(true);
        $response_time = round(($end_time - $start_time) * 1000, 2);
        
        if (is_wp_error($request)) {
            return array(
                'status' => 'error',
                'message' => $request->get_error_message(),
                'response_time' => null
            );
        }
        
        $response_code = wp_remote_retrieve_response_code($request);
        
        if ($response_code === 200) {
            return array(
                'status' => 'success',
                'message' => __('Connexion au serveur de mise à jour réussie', 'block-traiteur'),
                'response_time' => $response_time . ' ms'
            );
        } else {
            return array(
                'status' => 'error',
                'message' => sprintf(__('Erreur serveur: %d', 'block-traiteur'), $response_code),
                'response_time' => $response_time . ' ms'
            );
        }
    }
    
    /**
     * Récupération des notes de version
     */
    public function get_release_notes($version = null) {
        $version = $version ?: $this->latest_version;
        
        if (!$version) {
            return false;
        }
        
        $request = wp_remote_get($this->update_server_url . 'release-notes/' . $version, array(
            'timeout' => 10,
            'headers' => array(
                'User-Agent' => 'Block-Traiteur/' . $this->current_version . '; ' . home_url()
            )
        ));
        
        if (is_wp_error($request)) {
            return false;
        }
        
        $body = wp_remote_retrieve_body($request);
        $data = json_decode($body, true);
        
        return isset($data['notes']) ? $data['notes'] : false;
    }
    
    /**
     * Planification d'une mise à jour différée
     */
    public function schedule_delayed_update($delay_hours = 24) {
        $timestamp = time() + ($delay_hours * HOUR_IN_SECONDS);
        
        wp_clear_scheduled_hook('block_traiteur_delayed_update');
        wp_schedule_single_event($timestamp, 'block_traiteur_delayed_update');
        
        add_action('block_traiteur_delayed_update', array($this, 'perform_auto_update'));
        
        update_option('block_traiteur_scheduled_update', $timestamp);
        
        $this->log_info('Mise à jour programmée dans ' . $delay_hours . ' heures');
    }
    
    /**
     * Annulation d'une mise à jour programmée
     */
    public function cancel_scheduled_update() {
        wp_clear_scheduled_hook('block_traiteur_delayed_update');
        delete_option('block_traiteur_scheduled_update');
        
        $this->log_info('Mise à jour programmée annulée');
    }
    
    /**
     * Nettoyage lors de la désactivation
     */
    public function cleanup_on_deactivation() {
        // Supprimer les tâches programmées
        wp_clear_scheduled_hook('block_traiteur_auto_update');
        wp_clear_scheduled_hook('block_traiteur_delayed_update');
        
        // Nettoyer les transients
        delete_transient('block_traiteur_update_check');
        delete_transient('block_traiteur_latest_version');
        delete_transient('block_traiteur_update_data');
    }
    
    /**
     * Destructeur
     */
    public function __destruct() {
        // Rien de spécial à faire pour l'instant
    }
}