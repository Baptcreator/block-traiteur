<?php
/**
 * Classe d'activation du plugin Block Traiteur
 * 
 * @package Block_Traiteur
 * @since 1.0.0
 */

// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit;
}

class Block_Traiteur_Activator {
    
    /**
     * Méthode d'activation du plugin
     * 
     * @since 1.0.0
     */
    public static function activate() {
        // Vérifier les prérequis
        self::check_requirements();
        
        // Créer les tables de base de données
        self::create_database_tables();
        
        // Créer les options par défaut
        self::create_default_options();
        
        // Créer les rôles et capacités
        self::create_capabilities();
        
        // Vider le cache de réécriture des URLs
        flush_rewrite_rules();
        
        // Log de l'activation
        error_log('Block Traiteur: Plugin activé avec succès');
    }
    
    /**
     * Vérifier les prérequis système
     */
    private static function check_requirements() {
        $errors = array();
        
        // Vérifier version PHP
        if (version_compare(PHP_VERSION, '8.0', '<')) {
            $errors[] = sprintf(
                __('Block Traiteur nécessite PHP 8.0 minimum. Version actuelle : %s', 'block-traiteur'),
                PHP_VERSION
            );
        }
        
        // Vérifier version WordPress
        if (version_compare(get_bloginfo('version'), '6.0', '<')) {
            $errors[] = sprintf(
                __('Block Traiteur nécessite WordPress 6.0 minimum. Version actuelle : %s', 'block-traiteur'),
                get_bloginfo('version')
            );
        }
        
        // Vérifier extensions PHP requises
        $required_extensions = array(
            'mysqli' => 'MySQL',
            'json' => 'JSON',
            'curl' => 'cURL',
            'gd' => 'GD (manipulation d\'images)',
            'zip' => 'ZIP (sauvegardes)'
        );
        
        foreach ($required_extensions as $ext => $name) {
            if (!extension_loaded($ext)) {
                $errors[] = sprintf(
                    __('Extension PHP manquante : %s (%s)', 'block-traiteur'),
                    $name,
                    $ext
                );
            }
        }
        
        // Si erreurs détectées, stopper l'activation
        if (!empty($errors)) {
            deactivate_plugins(plugin_basename(BLOCK_TRAITEUR_PLUGIN_FILE));
            wp_die(
                '<h1>' . __('Activation impossible', 'block-traiteur') . '</h1>' .
                '<p>' . __('Block Traiteur ne peut pas être activé pour les raisons suivantes :', 'block-traiteur') . '</p>' .
                '<ul><li>' . implode('</li><li>', $errors) . '</li></ul>' .
                '<p><a href="' . admin_url('plugins.php') . '">' . __('Retour aux plugins', 'block-traiteur') . '</a></p>'
            );
        }
    }
    
    /**
     * Créer les tables de base de données
     */
    private static function create_database_tables() {
        // Utiliser le nouveau système de base de données
        require_once BLOCK_TRAITEUR_PLUGIN_DIR . 'includes/class-database.php';
        
        try {
            Block_Traiteur_Database::create_tables();
            Block_Traiteur_Database::seed_default_data();
            error_log('Block Traiteur: Base de données initialisée avec le nouveau système');
            return;
        } catch (Exception $e) {
            error_log('Block Traiteur: Erreur nouveau système, fallback vers ancien: ' . $e->getMessage());
        }
        
        // Fallback vers l'ancien système en cas d'erreur
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Table des devis
        $table_quotes = $wpdb->prefix . 'block_quotes';
        $sql_quotes = "CREATE TABLE $table_quotes (
            id int(11) NOT NULL AUTO_INCREMENT,
            quote_number varchar(50) NOT NULL,
            service_type varchar(50) NOT NULL,
            event_date date NOT NULL,
            guest_count int(11) NOT NULL,
            client_name varchar(100) NOT NULL,
            client_email varchar(100) NOT NULL,
            client_phone varchar(20) DEFAULT NULL,
            company_name varchar(100) DEFAULT NULL,
            event_address text DEFAULT NULL,
            total_price decimal(10,2) NOT NULL,
            status varchar(20) DEFAULT 'pending',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY quote_number (quote_number),
            KEY service_type (service_type),
            KEY status (status),
            KEY event_date (event_date)
        ) $charset_collate;";
        
        // Table des catégories de produits
        $table_food_categories = $wpdb->prefix . 'block_food_categories';
        $sql_food_categories = "CREATE TABLE $table_food_categories (
            id int(11) NOT NULL AUTO_INCREMENT,
            name varchar(100) NOT NULL,
            type varchar(50) NOT NULL DEFAULT 'food',
            sort_order int(11) DEFAULT 0,
            is_active tinyint(1) DEFAULT 1,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY type (type),
            KEY sort_order (sort_order)
        ) $charset_collate;";
        
        // Table des produits
        $table_products = $wpdb->prefix . 'block_products';
        $sql_products = "CREATE TABLE $table_products (
            id int(11) NOT NULL AUTO_INCREMENT,
            name varchar(100) NOT NULL,
            description text DEFAULT NULL,
            price decimal(10,2) NOT NULL,
            category_id int(11) NOT NULL,
            sort_order int(11) DEFAULT 0,
            is_active tinyint(1) DEFAULT 1,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY category_id (category_id),
            KEY sort_order (sort_order),
            FOREIGN KEY (category_id) REFERENCES $table_food_categories(id) ON DELETE CASCADE
        ) $charset_collate;";
        
        // Table des catégories de boissons
        $table_beverage_categories = $wpdb->prefix . 'block_beverage_categories';
        $sql_beverage_categories = "CREATE TABLE $table_beverage_categories (
            id int(11) NOT NULL AUTO_INCREMENT,
            name varchar(100) NOT NULL,
            type varchar(50) NOT NULL DEFAULT 'beverage',
            sort_order int(11) DEFAULT 0,
            is_active tinyint(1) DEFAULT 1,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY type (type),
            KEY sort_order (sort_order)
        ) $charset_collate;";
        
        // Table des boissons
        $table_beverages = $wpdb->prefix . 'block_beverages';
        $sql_beverages = "CREATE TABLE $table_beverages (
            id int(11) NOT NULL AUTO_INCREMENT,
            name varchar(100) NOT NULL,
            description text DEFAULT NULL,
            price decimal(10,2) NOT NULL,
            category_id int(11) NOT NULL,
            sort_order int(11) DEFAULT 0,
            is_active tinyint(1) DEFAULT 1,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY category_id (category_id),
            KEY sort_order (sort_order),
            FOREIGN KEY (category_id) REFERENCES $table_beverage_categories(id) ON DELETE CASCADE
        ) $charset_collate;";
        
        // Table des paramètres
        $table_settings = $wpdb->prefix . 'block_settings';
        $sql_settings = "CREATE TABLE $table_settings (
            id int(11) NOT NULL AUTO_INCREMENT,
            setting_name varchar(100) NOT NULL,
            setting_value longtext DEFAULT NULL,
            setting_type varchar(20) DEFAULT 'text',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY setting_name (setting_name)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        // Exécuter les requêtes
        dbDelta($sql_quotes);
        dbDelta($sql_food_categories);
        dbDelta($sql_products);
        dbDelta($sql_beverage_categories);
        dbDelta($sql_beverages);
        dbDelta($sql_settings);
        
        // Mettre à jour la version de la base de données
        update_option('block_traiteur_db_version', BLOCK_TRAITEUR_VERSION);
        
        error_log('Block Traiteur: Tables de base de données créées avec succès');
    }
    
    /**
     * Créer les options par défaut
     */
    private static function create_default_options() {
        $default_options = array(
            'block_traiteur_version' => BLOCK_TRAITEUR_VERSION,
            'block_traiteur_activated_at' => current_time('mysql'),
            'block_traiteur_email_notifications' => 1,
            'block_traiteur_admin_email' => get_option('admin_email'),
            'block_traiteur_currency' => 'EUR',
            'block_traiteur_tax_rate' => 20.0,
            'block_traiteur_company_name' => 'Block Street Food & Events',
            'block_traiteur_company_address' => '6 allée Adèle Klein, 67000 Strasbourg',
            'block_traiteur_company_phone' => '06 58 13 38 05',
            'block_traiteur_company_email' => 'contact@block-strasbourg.fr'
        );
        
        foreach ($default_options as $option_name => $option_value) {
            if (get_option($option_name) === false) {
                add_option($option_name, $option_value);
            }
        }
        
        error_log('Block Traiteur: Options par défaut créées');
    }
    
    /**
     * Créer les capacités utilisateur
     */
    private static function create_capabilities() {
        $role = get_role('administrator');
        
        if ($role) {
            $role->add_cap('manage_block_traiteur');
            $role->add_cap('edit_block_quotes');
            $role->add_cap('delete_block_quotes');
            $role->add_cap('view_block_reports');
        }
        
        error_log('Block Traiteur: Capacités utilisateur créées');
    }
}