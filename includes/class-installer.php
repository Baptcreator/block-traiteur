<?php
/**
 * Classe d'installation et désinstallation du plugin Block Traiteur
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
 * Classe Block_Traiteur_Installer
 * 
 * Gère l'installation, la mise à jour et la désinstallation du plugin
 */
class Block_Traiteur_Installer {
    
    /**
     * Version de la base de données
     */
    const DB_VERSION = '1.0.0';
    
    /**
     * Activation du plugin
     */
    public static function activate() {
        // Vérifier les permissions
        if (!current_user_can('activate_plugins')) {
            wp_die(__('Vous n\'avez pas les permissions nécessaires pour activer ce plugin.', 'block-traiteur'));
        }
        
        // Créer les tables
        self::create_tables();
        
        // Insérer les données par défaut
        self::insert_default_data();
        
        // Créer les dossiers nécessaires
        self::create_directories();
        
        // Définir les options par défaut
        self::set_default_options();
        
        // Programmer les tâches cron
        self::schedule_cron_jobs();
        
        // Marquer comme activé
        update_option('block_traiteur_activated', true);
        set_transient('block_traiteur_activated', true, 30);
        
        // Log de l'activation
        error_log('Block Traiteur Plugin activé avec succès - Version ' . BLOCK_TRAITEUR_VERSION);
    }
    
    /**
     * Désactivation du plugin
     */
    public static function deactivate() {
        // Supprimer les tâches cron
        wp_clear_scheduled_hook('block_traiteur_cleanup_expired_quotes');
        wp_clear_scheduled_hook('block_traiteur_sync_calendar');
        wp_clear_scheduled_hook('block_traiteur_send_pending_emails');
        
        // Nettoyer le cache
        wp_cache_flush();
        
        // Supprimer les transients
        delete_transient('block_traiteur_activated');
        delete_transient('block_traiteur_settings_cache');
        
        // Log de la désactivation
        error_log('Block Traiteur Plugin désactivé');
    }
    
    /**
     * Désinstallation complète du plugin
     */
    public static function uninstall() {
        // Vérifier les permissions
        if (!current_user_can('delete_plugins')) {
            wp_die(__('Vous n\'avez pas les permissions nécessaires pour désinstaller ce plugin.', 'block-traiteur'));
        }
        
        // Supprimer les tables
        self::drop_tables();
        
        // Supprimer les options
        self::delete_options();
        
        // Supprimer les dossiers et fichiers
        self::remove_directories();
        
        // Supprimer les capacités personnalisées
        self::remove_capabilities();
        
        // Log de la désinstallation
        error_log('Block Traiteur Plugin désinstallé complètement');
    }
    
    /**
     * Créer les tables de la base de données
     */
    private static function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Table des devis
        $sql_quotes = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}block_quotes (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            quote_number varchar(20) NOT NULL UNIQUE,
            customer_name varchar(100) NOT NULL,
            customer_email varchar(100) NOT NULL,
            customer_phone varchar(20) DEFAULT NULL,
            customer_address text DEFAULT NULL,
            customer_postal_code varchar(10) DEFAULT NULL,
            customer_city varchar(50) DEFAULT NULL,
            event_date datetime NOT NULL,
            event_duration int(11) DEFAULT 4,
            service_type enum('restaurant', 'remorque') NOT NULL,
            base_package_id bigint(20) UNSIGNED DEFAULT NULL,
            guest_count int(11) NOT NULL DEFAULT 1,
            total_price decimal(10,2) NOT NULL DEFAULT 0.00,
            distance_km decimal(8,2) DEFAULT 0.00,
            travel_cost decimal(8,2) DEFAULT 0.00,
            status enum('draft', 'pending', 'approved', 'rejected', 'expired') DEFAULT 'pending',
            notes text DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            expires_at datetime DEFAULT NULL,
            PRIMARY KEY (id),
            KEY idx_quote_number (quote_number),
            KEY idx_customer_email (customer_email),
            KEY idx_event_date (event_date),
            KEY idx_status (status),
            KEY idx_created_at (created_at)
        ) $charset_collate;";
        
        // Table des produits
        $sql_products = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}block_products (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            name varchar(100) NOT NULL,
            description text DEFAULT NULL,
            category enum('base_package', 'meal_formula', 'buffet', 'beverage', 'option') NOT NULL,
            subcategory varchar(50) DEFAULT NULL,
            price decimal(8,2) NOT NULL DEFAULT 0.00,
            price_type enum('fixed', 'per_person', 'per_hour') DEFAULT 'fixed',
            service_type enum('restaurant', 'remorque', 'both') DEFAULT 'both',
            min_guests int(11) DEFAULT 1,
            max_guests int(11) DEFAULT NULL,
            is_active tinyint(1) DEFAULT 1,
            display_order int(11) DEFAULT 0,
            image_url varchar(255) DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_category (category),
            KEY idx_service_type (service_type),
            KEY idx_is_active (is_active),
            KEY idx_display_order (display_order)
        ) $charset_collate;";
        
        // Table des éléments de devis
        $sql_quote_items = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}block_quote_items (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            quote_id bigint(20) UNSIGNED NOT NULL,
            product_id bigint(20) UNSIGNED NOT NULL,
            quantity int(11) NOT NULL DEFAULT 1,
            unit_price decimal(8,2) NOT NULL,
            total_price decimal(8,2) NOT NULL,
            item_type enum('base_package', 'meal_formula', 'buffet', 'beverage', 'option') NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_quote_id (quote_id),
            KEY idx_product_id (product_id),
            FOREIGN KEY (quote_id) REFERENCES {$wpdb->prefix}block_quotes(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES {$wpdb->prefix}block_products(id) ON DELETE CASCADE
        ) $charset_collate;";
        
        // Table des boissons
        $sql_beverages = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}block_beverages (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            name varchar(100) NOT NULL,
            category enum('soft', 'hot', 'alcohol', 'wine', 'beer') NOT NULL,
            price_per_person decimal(6,2) NOT NULL DEFAULT 0.00,
            description text DEFAULT NULL,
            is_active tinyint(1) DEFAULT 1,
            service_type enum('restaurant', 'remorque', 'both') DEFAULT 'both',
            display_order int(11) DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_category (category),
            KEY idx_service_type (service_type),
            KEY idx_is_active (is_active),
            KEY idx_display_order (display_order)
        ) $charset_collate;";
        
        // Table des boissons dans les devis
        $sql_quote_beverages = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}block_quote_beverages (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            quote_id bigint(20) UNSIGNED NOT NULL,
            beverage_id bigint(20) UNSIGNED NOT NULL,
            guest_count int(11) NOT NULL,
            unit_price decimal(6,2) NOT NULL,
            total_price decimal(8,2) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_quote_id (quote_id),
            KEY idx_beverage_id (beverage_id),
            FOREIGN KEY (quote_id) REFERENCES {$wpdb->prefix}block_quotes(id) ON DELETE CASCADE,
            FOREIGN KEY (beverage_id) REFERENCES {$wpdb->prefix}block_beverages(id) ON DELETE CASCADE
        ) $charset_collate;";
        
        // Table des paramètres
        $sql_settings = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}block_settings (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            setting_key varchar(100) NOT NULL UNIQUE,
            setting_value longtext DEFAULT NULL,
            setting_type enum('string', 'number', 'boolean', 'array', 'object') DEFAULT 'string',
            is_autoload tinyint(1) DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_setting_key (setting_key),
            KEY idx_is_autoload (is_autoload)
        ) $charset_collate;";
        
        // Table des logs
        $sql_logs = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}block_logs (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            level enum('debug', 'info', 'warning', 'error', 'critical') NOT NULL DEFAULT 'info',
            message text NOT NULL,
            context longtext DEFAULT NULL,
            quote_id bigint(20) UNSIGNED DEFAULT NULL,
            user_id bigint(20) UNSIGNED DEFAULT NULL,
            ip_address varchar(45) DEFAULT NULL,
            user_agent text DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_level (level),
            KEY idx_quote_id (quote_id),
            KEY idx_user_id (user_id),
            KEY idx_created_at (created_at)
        ) $charset_collate;";
        
        // Table des codes postaux
        $sql_postal_codes = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}block_postal_codes (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            postal_code varchar(10) NOT NULL,
            city varchar(100) NOT NULL,
            department varchar(100) DEFAULT NULL,
            region varchar(100) DEFAULT NULL,
            latitude decimal(10,8) DEFAULT NULL,
            longitude decimal(11,8) DEFAULT NULL,
            distance_km decimal(8,2) DEFAULT NULL,
            travel_cost decimal(8,2) DEFAULT NULL,
            is_serviceable tinyint(1) DEFAULT 1,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY idx_postal_code (postal_code),
            KEY idx_city (city),
            KEY idx_distance_km (distance_km),
            KEY idx_is_serviceable (is_serviceable)
        ) $charset_collate;";
        
        // Table des emails
        $sql_emails = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}block_emails (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            quote_id bigint(20) UNSIGNED DEFAULT NULL,
            recipient_email varchar(100) NOT NULL,
            recipient_name varchar(100) DEFAULT NULL,
            subject varchar(255) NOT NULL,
            message longtext NOT NULL,
            email_type enum('quote_confirmation', 'admin_notification', 'quote_reminder', 'custom') NOT NULL,
            status enum('pending', 'sent', 'failed') DEFAULT 'pending',
            sent_at datetime DEFAULT NULL,
            attempts int(11) DEFAULT 0,
            last_error text DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_quote_id (quote_id),
            KEY idx_recipient_email (recipient_email),
            KEY idx_email_type (email_type),
            KEY idx_status (status),
            KEY idx_created_at (created_at)
        ) $charset_collate;";
        
        // Exécuter les requêtes
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        dbDelta($sql_quotes);
        dbDelta($sql_products);
        dbDelta($sql_quote_items);
        dbDelta($sql_beverages);
        dbDelta($sql_quote_beverages);
        dbDelta($sql_settings);
        dbDelta($sql_logs);
        dbDelta($sql_postal_codes);
        dbDelta($sql_emails);
        
        // Enregistrer la version de la base de données
        update_option('block_traiteur_db_version', self::DB_VERSION);
        
        // Log de la création des tables
        error_log('Block Traiteur: Tables de base de données créées avec succès');
    }
    
    /**
     * Insérer les données par défaut
     */
    private static function insert_default_data() {
        global $wpdb;
        
        // Vérifier si les données existent déjà
        $existing_products = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}block_products");
        if ($existing_products > 0) {
            return; // Les données existent déjà
        }
        
        // Forfaits de base restaurant
        $base_packages_restaurant = [
            [
                'name' => 'Forfait Restaurant Classique',
                'description' => 'Service restaurant avec équipe complète',
                'category' => 'base_package',
                'price' => 350.00,
                'price_type' => 'fixed',
                'service_type' => 'restaurant',
                'min_guests' => 20,
                'max_guests' => 100,
                'display_order' => 1
            ],
            [
                'name' => 'Forfait Restaurant Premium',
                'description' => 'Service restaurant haut de gamme avec chef',
                'category' => 'base_package',
                'price' => 500.00,
                'price_type' => 'fixed',
                'service_type' => 'restaurant',
                'min_guests' => 30,
                'max_guests' => 150,
                'display_order' => 2
            ]
        ];
        
        // Forfaits de base remorque
        $base_packages_remorque = [
            [
                'name' => 'Forfait Remorque Standard',
                'description' => 'Service remorque avec équipe',
                'category' => 'base_package',
                'price' => 250.00,
                'price_type' => 'fixed',
                'service_type' => 'remorque',
                'min_guests' => 10,
                'max_guests' => 80,
                'display_order' => 3
            ],
            [
                'name' => 'Forfait Remorque Deluxe',
                'description' => 'Service remorque premium avec options',
                'category' => 'base_package',
                'price' => 380.00,
                'price_type' => 'fixed',
                'service_type' => 'remorque',
                'min_guests' => 15,
                'max_guests' => 120,
                'display_order' => 4
            ]
        ];
        
        // Formules repas
        $meal_formulas = [
            [
                'name' => 'Formule Sandwichs Gourmets',
                'description' => 'Selection de sandwichs artisanaux',
                'category' => 'meal_formula',
                'subcategory' => 'sandwichs',
                'price' => 12.50,
                'price_type' => 'per_person',
                'service_type' => 'both',
                'display_order' => 1
            ],
            [
                'name' => 'Formule Burgers Artisanaux',
                'description' => 'Burgers faits maison avec frites',
                'category' => 'meal_formula',
                'subcategory' => 'burgers',
                'price' => 15.00,
                'price_type' => 'per_person',
                'service_type' => 'both',
                'display_order' => 2
            ],
            [
                'name' => 'Formule Plats Chauds',
                'description' => 'Plats cuisinés traditionnels',
                'category' => 'meal_formula',
                'subcategory' => 'plats_chauds',
                'price' => 18.00,
                'price_type' => 'per_person',
                'service_type' => 'restaurant',
                'display_order' => 3
            ]
        ];
        
        // Buffets
        $buffets = [
            [
                'name' => 'Buffet Apéritif',
                'description' => 'Assortiment d\'amuse-bouches et canapés',
                'category' => 'buffet',
                'price' => 8.50,
                'price_type' => 'per_person',
                'service_type' => 'both',
                'display_order' => 1
            ],
            [
                'name' => 'Buffet Desserts',
                'description' => 'Plateau de desserts variés',
                'category' => 'buffet',
                'price' => 6.00,
                'price_type' => 'per_person',
                'service_type' => 'both',
                'display_order' => 2
            ]
        ];
        
        // Options
        $options = [
            [
                'name' => 'Mise en place intégrale',
                'description' => 'Installation complète avec décoration',
                'category' => 'option',
                'price' => 50.00,
                'price_type' => 'fixed',
                'service_type' => 'both',
                'display_order' => 1
            ],
            [
                'name' => 'Installation jeux',
                'description' => 'Mise en place d\'animations',
                'category' => 'option',
                'price' => 70.00,
                'price_type' => 'fixed',
                'service_type' => 'both',
                'display_order' => 2
            ]
        ];
        
        // Insérer tous les produits
        $all_products = array_merge(
            $base_packages_restaurant,
            $base_packages_remorque,
            $meal_formulas,
            $buffets,
            $options
        );
        
        foreach ($all_products as $product) {
            $wpdb->insert(
                $wpdb->prefix . 'block_products',
                $product,
                ['%s', '%s', '%s', '%s', '%f', '%s', '%s', '%d', '%d', '%d', '%d']
            );
        }
        
        // Boissons par défaut
        $beverages = [
            [
                'name' => 'Sodas variés',
                'category' => 'soft',
                'price_per_person' => 2.50,
                'description' => 'Coca, Sprite, Orangina',
                'service_type' => 'both',
                'display_order' => 1
            ],
            [
                'name' => 'Jus de fruits',
                'category' => 'soft',
                'price_per_person' => 3.00,
                'description' => 'Jus d\'orange, pomme, multivitaminé',
                'service_type' => 'both',
                'display_order' => 2
            ],
            [
                'name' => 'Café et thé',
                'category' => 'hot',
                'price_per_person' => 1.50,
                'description' => 'Café espresso, thé varié',
                'service_type' => 'both',
                'display_order' => 3
            ],
            [
                'name' => 'Bières pression',
                'category' => 'beer',
                'price_per_person' => 4.00,
                'description' => 'Bières locales à la pression',
                'service_type' => 'both',
                'display_order' => 4
            ],
            [
                'name' => 'Vin de table',
                'category' => 'wine',
                'price_per_person' => 5.50,
                'description' => 'Vin rouge et blanc de qualité',
                'service_type' => 'restaurant',
                'display_order' => 5
            ]
        ];
        
        foreach ($beverages as $beverage) {
            $wpdb->insert(
                $wpdb->prefix . 'block_beverages',
                $beverage,
                ['%s', '%s', '%f', '%s', '%d', '%s', '%d']
            );
        }
        
        // Codes postaux de base (Strasbourg et environs)
        $postal_codes = [
            ['postal_code' => '67000', 'city' => 'Strasbourg', 'department' => 'Bas-Rhin', 'region' => 'Grand Est', 'distance_km' => 0, 'travel_cost' => 0],
            ['postal_code' => '67100', 'city' => 'Strasbourg', 'department' => 'Bas-Rhin', 'region' => 'Grand Est', 'distance_km' => 5, 'travel_cost' => 20],
            ['postal_code' => '67200', 'city' => 'Strasbourg', 'department' => 'Bas-Rhin', 'region' => 'Grand Est', 'distance_km' => 8, 'travel_cost' => 30],
            ['postal_code' => '67300', 'city' => 'Schiltigheim', 'department' => 'Bas-Rhin', 'region' => 'Grand Est', 'distance_km' => 12, 'travel_cost' => 45],
            ['postal_code' => '67400', 'city' => 'Illkirch-Graffenstaden', 'department' => 'Bas-Rhin', 'region' => 'Grand Est', 'distance_km' => 15, 'travel_cost' => 50],
            ['postal_code' => '67500', 'city' => 'Haguenau', 'department' => 'Bas-Rhin', 'region' => 'Grand Est', 'distance_km' => 35, 'travel_cost' => 120],
        ];
        
        foreach ($postal_codes as $postal_code) {
            $wpdb->insert(
                $wpdb->prefix . 'block_postal_codes',
                $postal_code,
                ['%s', '%s', '%s', '%s', '%f', '%f']
            );
        }
        
        error_log('Block Traiteur: Données par défaut insérées avec succès');
    }
    
    /**
     * Créer les dossiers nécessaires
     */
    private static function create_directories() {
        $upload_dir = wp_upload_dir();
        $base_dir = $upload_dir['basedir'] . '/block-traiteur';
        
        $directories = [
            $base_dir,
            $base_dir . '/pdf',
            $base_dir . '/logs',
            $base_dir . '/cache',
            $base_dir . '/temp'
        ];
        
        foreach ($directories as $dir) {
            if (!file_exists($dir)) {
                wp_mkdir_p($dir);
                
                // Créer un fichier .htaccess pour sécuriser
                $htaccess_content = "Options -Indexes\nDeny from all";
                file_put_contents($dir . '/.htaccess', $htaccess_content);
                
                // Créer un index.php vide
                file_put_contents($dir . '/index.php', '<?php // Silence is golden');
            }
        }
        
        error_log('Block Traiteur: Dossiers créés avec succès');
    }
    
    /**
     * Définir les options par défaut
     */
    private static function set_default_options() {
        $default_settings = [
            // Informations entreprise
            'company_name' => 'Block Strasbourg',
            'company_email' => 'contact@block-strasbourg.fr',
            'company_phone' => '03 88 XX XX XX',
            'company_address' => 'Strasbourg, France',
            'company_postal_code' => '67000',
            'company_city' => 'Strasbourg',
            
            // Paramètres de calcul
            'base_travel_cost_per_km' => 1.5,
            'min_travel_cost' => 20.0,
            'max_travel_distance' => 100,
            'default_event_duration' => 4,
            'quote_validity_days' => 30,
            
            // Paramètres emails
            'email_from_name' => 'Block Strasbourg',
            'email_from_address' => 'contact@block-strasbourg.fr',
            'admin_notification_email' => 'admin@block-strasbourg.fr',
            'email_template_header_color' => '#243127',
            'email_template_accent_color' => '#FFB404',
            
            // Paramètres PDF
            'pdf_company_logo' => '',
            'pdf_header_color' => '#243127',
            'pdf_accent_color' => '#FFB404',
            'pdf_footer_text' => 'Block Strasbourg - Traiteur événementiel',
            
            // Paramètres de sécurité
            'enable_honeypot' => true,
            'rate_limit_submissions' => 5,
            'rate_limit_window' => 3600,
            'require_phone' => false,
            'min_guests' => 1,
            'max_guests' => 500,
            
            // Google Calendar
            'google_calendar_enabled' => false,
            'google_calendar_id' => '',
            'google_api_key' => '',
            
            // Performance
            'enable_cache' => true,
            'cache_duration' => 3600,
            'enable_minification' => true,
            'lazy_load_images' => true
        ];
        
        foreach ($default_settings as $key => $value) {
            add_option('block_traiteur_' . $key, $value);
        }
        
        // Version du plugin
        update_option('block_traiteur_version', BLOCK_TRAITEUR_VERSION);
        
        error_log('Block Traiteur: Options par défaut définies');
    }
    
    /**
     * Programmer les tâches cron
     */
    private static function schedule_cron_jobs() {
        // Nettoyage quotidien des devis expirés
        if (!wp_next_scheduled('block_traiteur_cleanup_expired_quotes')) {
            wp_schedule_event(time(), 'daily', 'block_traiteur_cleanup_expired_quotes');
        }
        
        // Synchronisation du calendrier (si activée)
        if (!wp_next_scheduled('block_traiteur_sync_calendar')) {
            wp_schedule_event(time(), 'hourly', 'block_traiteur_sync_calendar');
        }
        
        // Envoi des emails en attente
        if (!wp_next_scheduled('block_traiteur_send_pending_emails')) {
            wp_schedule_event(time(), 'fifteen_minutes', 'block_traiteur_send_pending_emails');
        }
        
        // Ajouter l'intervalle de 15 minutes si nécessaire
        add_filter('cron_schedules', function($schedules) {
            $schedules['fifteen_minutes'] = array(
                'interval' => 15 * 60,
                'display' => __('Toutes les 15 minutes', 'block-traiteur')
            );
            return $schedules;
        });
        
        error_log('Block Traiteur: Tâches cron programmées');
    }
    
    /**
     * Supprimer les tables
     */
    private static function drop_tables() {
        global $wpdb;
        
        $tables = [
            'block_emails',
            'block_quote_beverages',
            'block_quote_items',
            'block_quotes',
            'block_beverages',
            'block_products',
            'block_postal_codes',
            'block_logs',
            'block_settings'
        ];
        
        foreach ($tables as $table) {
            $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}{$table}");
        }
        
        error_log('Block Traiteur: Tables supprimées lors de la désinstallation');
    }
    
    /**
     * Supprimer toutes les options
     */
    private static function delete_options() {
        global $wpdb;
        
        // Supprimer toutes les options du plugin
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE 'block_traiteur_%'");
        
        // Supprimer les transients
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_block_traiteur_%'");
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_block_traiteur_%'");
        
        // Supprimer les options spécifiques
        $options_to_delete = [
            'block_traiteur_version',
            'block_traiteur_db_version',
            'block_traiteur_activated',
            'block_traiteur_installation_date',
            'block_traiteur_last_cleanup',
            'block_traiteur_settings_cache'
        ];
        
        foreach ($options_to_delete as $option) {
            delete_option($option);
            delete_site_option($option); // Pour les installations multisite
        }
        
        error_log('Block Traiteur: Options supprimées lors de la désinstallation');
    }
    
    /**
     * Supprimer les dossiers et fichiers
     */
    private static function remove_directories() {
        $upload_dir = wp_upload_dir();
        $base_dir = $upload_dir['basedir'] . '/block-traiteur';
        
        if (file_exists($base_dir)) {
            self::recursive_rmdir($base_dir);
            error_log('Block Traiteur: Dossiers supprimés lors de la désinstallation');
        }
    }
    
    /**
     * Supprimer récursivement un dossier
     */
    private static function recursive_rmdir($dir) {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (is_dir($dir . "/" . $object)) {
                        self::recursive_rmdir($dir . "/" . $object);
                    } else {
                        unlink($dir . "/" . $object);
                    }
                }
            }
            rmdir($dir);
        }
    }
    
    /**
     * Supprimer les capacités personnalisées
     */
    private static function remove_capabilities() {
        $capabilities = [
            'manage_block_traiteur',
            'edit_block_quotes',
            'view_block_quotes',
            'delete_block_quotes',
            'manage_block_products',
            'edit_block_products',
            'view_block_settings'
        ];
        
        // Supprimer les capacités de tous les rôles
        $roles = ['administrator', 'editor', 'author'];
        
        foreach ($roles as $role_name) {
            $role = get_role($role_name);
            if ($role) {
                foreach ($capabilities as $cap) {
                    $role->remove_cap($cap);
                }
            }
        }
        
        error_log('Block Traiteur: Capacités supprimées lors de la désinstallation');
    }
    
    /**
     * Vérifier et mettre à jour la base de données si nécessaire
     */
    public static function check_database_update() {
        $current_db_version = get_option('block_traiteur_db_version', '0.0.0');
        
        if (version_compare($current_db_version, self::DB_VERSION, '<')) {
            self::update_database($current_db_version);
        }
    }
    
    /**
     * Mettre à jour la base de données
     */
    private static function update_database($from_version) {
        global $wpdb;
        
        error_log("Block Traiteur: Mise à jour de la base de données de {$from_version} vers " . self::DB_VERSION);
        
        // Exemple de mise à jour conditionnelle
        if (version_compare($from_version, '1.0.0', '<')) {
            // Ajouter de nouvelles colonnes ou tables si nécessaire
            // Exemple :
            /*
            $wpdb->query("ALTER TABLE {$wpdb->prefix}block_quotes 
                         ADD COLUMN new_field varchar(255) DEFAULT NULL AFTER existing_field");
            */
        }
        
        // Mettre à jour la version de la base de données
        update_option('block_traiteur_db_version', self::DB_VERSION);
        
        error_log('Block Traiteur: Base de données mise à jour avec succès');
    }
    
    /**
     * Vérifier l'intégrité de la base de données
     */
    public static function check_database_integrity() {
        global $wpdb;
        
        $required_tables = [
            'block_quotes',
            'block_products',
            'block_quote_items',
            'block_beverages',
            'block_quote_beverages',
            'block_settings',
            'block_logs',
            'block_postal_codes',
            'block_emails'
        ];
        
        $missing_tables = [];
        
        foreach ($required_tables as $table) {
            $table_name = $wpdb->prefix . $table;
            $exists = $wpdb->get_var("SHOW TABLES LIKE '{$table_name}'");
            
            if (!$exists) {
                $missing_tables[] = $table;
            }
        }
        
        if (!empty($missing_tables)) {
            error_log('Block Traiteur: Tables manquantes détectées: ' . implode(', ', $missing_tables));
            // Recréer les tables manquantes
            self::create_tables();
            return false;
        }
        
        return true;
    }
    
    /**
     * Nettoyer les données orphelines
     */
    public static function cleanup_orphaned_data() {
        global $wpdb;
        
        // Supprimer les éléments de devis orphelins
        $wpdb->query("DELETE qi FROM {$wpdb->prefix}block_quote_items qi 
                     LEFT JOIN {$wpdb->prefix}block_quotes q ON qi.quote_id = q.id 
                     WHERE q.id IS NULL");
        
        // Supprimer les boissons de devis orphelines
        $wpdb->query("DELETE qb FROM {$wpdb->prefix}block_quote_beverages qb 
                     LEFT JOIN {$wpdb->prefix}block_quotes q ON qb.quote_id = q.id 
                     WHERE q.id IS NULL");
        
        // Supprimer les emails orphelins
        $wpdb->query("DELETE e FROM {$wpdb->prefix}block_emails e 
                     LEFT JOIN {$wpdb->prefix}block_quotes q ON e.quote_id = q.id 
                     WHERE e.quote_id IS NOT NULL AND q.id IS NULL");
        
        // Nettoyer les logs anciens (plus de 90 jours)
        $wpdb->query("DELETE FROM {$wpdb->prefix}block_logs 
                     WHERE created_at < DATE_SUB(NOW(), INTERVAL 90 DAY)");
        
        error_log('Block Traiteur: Nettoyage des données orphelines effectué');
    }
    
    /**
     * Vérifier les permissions des fichiers
     */
    public static function check_file_permissions() {
        $upload_dir = wp_upload_dir();
        $base_dir = $upload_dir['basedir'] . '/block-traiteur';
        
        $directories_to_check = [
            $base_dir,
            $base_dir . '/pdf',
            $base_dir . '/logs',
            $base_dir . '/cache',
            $base_dir . '/temp'
        ];
        
        $permission_issues = [];
        
        foreach ($directories_to_check as $dir) {
            if (!file_exists($dir)) {
                $permission_issues[] = "Dossier manquant: {$dir}";
                continue;
            }
            
            if (!is_writable($dir)) {
                $permission_issues[] = "Dossier non inscriptible: {$dir}";
            }
        }
        
        if (!empty($permission_issues)) {
            error_log('Block Traiteur: Problèmes de permissions détectés: ' . implode(', ', $permission_issues));
            return false;
        }
        
        return true;
    }
    
    /**
     * Réparer l'installation
     */
    public static function repair_installation() {
        error_log('Block Traiteur: Début de la réparation de l\'installation');
        
        // Vérifier et réparer les tables
        if (!self::check_database_integrity()) {
            self::create_tables();
        }
        
        // Vérifier et créer les dossiers
        self::create_directories();
        
        // Vérifier les permissions
        self::check_file_permissions();
        
        // Nettoyer les données orphelines
        self::cleanup_orphaned_data();
        
        // Vérifier les options essentielles
        $essential_options = [
            'block_traiteur_company_name',
            'block_traiteur_company_email',
            'block_traiteur_base_travel_cost_per_km'
        ];
        
        foreach ($essential_options as $option) {
            if (!get_option($option)) {
                self::set_default_options();
                break;
            }
        }
        
        // Reprogrammer les tâches cron si nécessaire
        if (!wp_next_scheduled('block_traiteur_cleanup_expired_quotes')) {
            self::schedule_cron_jobs();
        }
        
        error_log('Block Traiteur: Réparation de l\'installation terminée');
    }
    
    /**
     * Obtenir des statistiques d'installation
     */
    public static function get_installation_stats() {
        global $wpdb;
        
        $stats = [
            'version' => get_option('block_traiteur_version', 'Inconnue'),
            'db_version' => get_option('block_traiteur_db_version', 'Inconnue'),
            'installation_date' => get_option('block_traiteur_installation_date', 'Inconnue'),
            'total_quotes' => $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}block_quotes"),
            'total_products' => $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}block_products"),
            'total_beverages' => $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}block_beverages"),
            'total_postal_codes' => $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}block_postal_codes"),
            'pending_quotes' => $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}block_quotes WHERE status = 'pending'"),
            'approved_quotes' => $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}block_quotes WHERE status = 'approved'"),
            'database_integrity' => self::check_database_integrity() ? 'OK' : 'Problème détecté',
            'file_permissions' => self::check_file_permissions() ? 'OK' : 'Problème détecté',
            'cron_jobs' => [
                'cleanup_scheduled' => wp_next_scheduled('block_traiteur_cleanup_expired_quotes') ? 'Programmé' : 'Non programmé',
                'calendar_sync_scheduled' => wp_next_scheduled('block_traiteur_sync_calendar') ? 'Programmé' : 'Non programmé',
                'email_sending_scheduled' => wp_next_scheduled('block_traiteur_send_pending_emails') ? 'Programmé' : 'Non programmé'
            ]
        ];
        
        return $stats;
    }
    
    /**
     * Exporter la configuration
     */
    public static function export_configuration() {
        global $wpdb;
        
        $config = [
            'version' => BLOCK_TRAITEUR_VERSION,
            'export_date' => current_time('mysql'),
            'settings' => [],
            'products' => [],
            'beverages' => [],
            'postal_codes' => []
        ];
        
        // Exporter les paramètres
        $settings = $wpdb->get_results("SELECT option_name, option_value FROM {$wpdb->options} WHERE option_name LIKE 'block_traiteur_%'");
        foreach ($settings as $setting) {
            $config['settings'][$setting->option_name] = $setting->option_value;
        }
        
        // Exporter les produits
        $config['products'] = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}block_products WHERE is_active = 1", ARRAY_A);
        
        // Exporter les boissons
        $config['beverages'] = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}block_beverages WHERE is_active = 1", ARRAY_A);
        
        // Exporter les codes postaux
        $config['postal_codes'] = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}block_postal_codes WHERE is_serviceable = 1", ARRAY_A);
        
        return $config;
    }
    
    /**
     * Importer une configuration
     */
    public static function import_configuration($config_data) {
        global $wpdb;
        
        if (!is_array($config_data) || !isset($config_data['version'])) {
            return new WP_Error('invalid_config', 'Configuration invalide');
        }
        
        // Sauvegarder la configuration actuelle
        $backup = self::export_configuration();
        update_option('block_traiteur_config_backup_' . time(), $backup);
        
        try {
            // Importer les paramètres
            if (isset($config_data['settings'])) {
                foreach ($config_data['settings'] as $option_name => $option_value) {
                    update_option($option_name, $option_value);
                }
            }
            
            // Importer les produits (remplacer les existants)
            if (isset($config_data['products'])) {
                $wpdb->query("DELETE FROM {$wpdb->prefix}block_products");
                
                foreach ($config_data['products'] as $product) {
                    unset($product['id']); // Supprimer l'ID pour éviter les conflits
                    $wpdb->insert($wpdb->prefix . 'block_products', $product);
                }
            }
            
            // Importer les boissons
            if (isset($config_data['beverages'])) {
                $wpdb->query("DELETE FROM {$wpdb->prefix}block_beverages");
                
                foreach ($config_data['beverages'] as $beverage) {
                    unset($beverage['id']);
                    $wpdb->insert($wpdb->prefix . 'block_beverages', $beverage);
                }
            }
            
            // Importer les codes postaux
            if (isset($config_data['postal_codes'])) {
                $wpdb->query("DELETE FROM {$wpdb->prefix}block_postal_codes");
                
                foreach ($config_data['postal_codes'] as $postal_code) {
                    unset($postal_code['id']);
                    $wpdb->insert($wpdb->prefix . 'block_postal_codes', $postal_code);
                }
            }
            
            error_log('Block Traiteur: Configuration importée avec succès');
            return true;
            
        } catch (Exception $e) {
            error_log('Block Traiteur: Erreur lors de l\'importation: ' . $e->getMessage());
            return new WP_Error('import_failed', 'Échec de l\'importation: ' . $e->getMessage());
        }
    }
}