<?php
/**
 * Classe de gestion de la base de données pour Block Traiteur
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
 * Classe Block_Traiteur_Database
 * 
 * Gère la création et la maintenance des tables de base de données
 */
class Block_Traiteur_Database {
    
    /**
     * Créer toutes les tables du plugin
     */
    public static function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        $sql = array();
        
        // Table des paramètres
        $sql[] = "CREATE TABLE {$wpdb->prefix}block_settings (
            id int(11) NOT NULL AUTO_INCREMENT,
            setting_key varchar(255) NOT NULL,
            setting_value text,
            setting_type enum('string','number','boolean','json') DEFAULT 'string',
            category varchar(100) DEFAULT 'general',
            description text,
            created_at timestamp DEFAULT CURRENT_TIMESTAMP,
            updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY setting_key (setting_key),
            KEY category (category)
        ) $charset_collate;";
        
        // Table des catégories de produits
        $sql[] = "CREATE TABLE {$wpdb->prefix}block_food_categories (
            id int(11) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            slug varchar(255) NOT NULL,
            description text,
            sort_order int(11) DEFAULT 0,
            is_active boolean DEFAULT TRUE,
            PRIMARY KEY (id),
            UNIQUE KEY slug (slug)
        ) $charset_collate;";
        
        // Table des catégories de boissons
        $sql[] = "CREATE TABLE {$wpdb->prefix}block_beverage_categories (
            id int(11) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            slug varchar(255) NOT NULL,
            description text,
            sort_order int(11) DEFAULT 0,
            is_active boolean DEFAULT TRUE,
            PRIMARY KEY (id),
            UNIQUE KEY slug (slug)
        ) $charset_collate;";
        
        // Table des produits alimentaires
        $sql[] = "CREATE TABLE {$wpdb->prefix}block_products (
            id int(11) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            category_id int(11) NOT NULL,
            price decimal(10,2) NOT NULL DEFAULT 0.00,
            unit enum('piece','gramme','portion','personne') DEFAULT 'piece',
            min_quantity int(11) DEFAULT 1,
            max_quantity int(11) DEFAULT NULL,
            description text,
            ingredients text,
            allergens varchar(500),
            image_url varchar(500),
            sort_order int(11) DEFAULT 0,
            is_active boolean DEFAULT TRUE,
            service_type enum('restaurant','remorque','both') DEFAULT 'both',
            created_at timestamp DEFAULT CURRENT_TIMESTAMP,
            updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY category_id (category_id),
            KEY service_type (service_type),
            KEY is_active (is_active)
        ) $charset_collate;";
        
        // Table des boissons
        $sql[] = "CREATE TABLE {$wpdb->prefix}block_beverages (
            id int(11) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            category_id int(11) NOT NULL,
            price decimal(10,2) NOT NULL DEFAULT 0.00,
            volume varchar(50),
            volume_ml int(11),
            alcohol_degree decimal(3,1) DEFAULT 0.0,
            container_type enum('bouteille','fut','canette','pack','autre') DEFAULT 'bouteille',
            description text,
            origin varchar(255),
            image_url varchar(500),
            sort_order int(11) DEFAULT 0,
            is_active boolean DEFAULT TRUE,
            service_type enum('restaurant','remorque','both') DEFAULT 'both',
            created_at timestamp DEFAULT CURRENT_TIMESTAMP,
            updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY category_id (category_id),
            KEY service_type (service_type),
            KEY is_active (is_active)
        ) $charset_collate;";
        
        // Table des devis
        $sql[] = "CREATE TABLE {$wpdb->prefix}block_quotes (
            id int(11) NOT NULL AUTO_INCREMENT,
            quote_number varchar(50) NOT NULL,
            service_type enum('restaurant','remorque') NOT NULL,
            customer_name varchar(255) NOT NULL,
            customer_email varchar(255) NOT NULL,
            customer_phone varchar(50) NOT NULL,
            customer_address text,
            event_date datetime NOT NULL,
            guest_count int(11) NOT NULL,
            duration int(11) NOT NULL,
            postal_code varchar(10) NOT NULL,
            distance_cost decimal(10,2) DEFAULT 0.00,
            base_price decimal(10,2) NOT NULL DEFAULT 0.00,
            products_total decimal(10,2) DEFAULT 0.00,
            beverages_total decimal(10,2) DEFAULT 0.00,
            options_total decimal(10,2) DEFAULT 0.00,
            total_price decimal(10,2) NOT NULL DEFAULT 0.00,
            status enum('pending','approved','rejected','expired') DEFAULT 'pending',
            notes text,
            admin_notes text,
            pdf_generated boolean DEFAULT FALSE,
            pdf_path varchar(500),
            expires_at datetime,
            created_at timestamp DEFAULT CURRENT_TIMESTAMP,
            updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY quote_number (quote_number),
            KEY status (status),
            KEY service_type (service_type),
            KEY event_date (event_date),
            KEY customer_email (customer_email)
        ) $charset_collate;";
        
        // Table des produits du devis
        $sql[] = "CREATE TABLE {$wpdb->prefix}block_quote_products (
            id int(11) NOT NULL AUTO_INCREMENT,
            quote_id int(11) NOT NULL,
            product_id int(11) NOT NULL,
            product_type enum('food','beverage') NOT NULL,
            quantity int(11) NOT NULL,
            unit_price decimal(10,2) NOT NULL,
            total_price decimal(10,2) NOT NULL,
            product_name varchar(255),
            product_unit varchar(50),
            PRIMARY KEY (id),
            KEY quote_id (quote_id),
            KEY product_id_type (product_id, product_type)
        ) $charset_collate;";
        
        // Table des disponibilités
        $sql[] = "CREATE TABLE {$wpdb->prefix}block_availability (
            id int(11) NOT NULL AUTO_INCREMENT,
            date date NOT NULL,
            service_type enum('restaurant','remorque','both') NOT NULL,
            is_available boolean DEFAULT TRUE,
            reason varchar(255),
            created_by int(11),
            created_at timestamp DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY date_service (date, service_type)
        ) $charset_collate;";
        
        // Table des codes postaux français
        $sql[] = "CREATE TABLE {$wpdb->prefix}block_postal_codes (
            id int(11) NOT NULL AUTO_INCREMENT,
            postal_code varchar(5) NOT NULL,
            city varchar(255) NOT NULL,
            department varchar(3) NOT NULL,
            region varchar(255),
            latitude decimal(10,7),
            longitude decimal(10,7),
            terrain_type enum('urban','rural','mountain') DEFAULT 'rural',
            PRIMARY KEY (id),
            UNIQUE KEY postal_code (postal_code),
            KEY department (department)
        ) $charset_collate;";
        
        // Exécuter les requêtes
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        foreach ($sql as $query) {
            dbDelta($query);
        }
        
        // Vérifier que les tables ont été créées
        self::verify_tables_creation();
        
        // Mettre à jour la version de la base de données
        update_option('block_traiteur_db_version', BLOCK_TRAITEUR_VERSION);
    }
    
    /**
     * Vérifier que les tables ont été créées
     */
    private static function verify_tables_creation() {
        global $wpdb;
        
        $required_tables = array(
            'block_products',
            'block_beverages', 
            'block_food_categories',
            'block_beverage_categories',
            'block_settings',
            'block_quotes',
            'block_quote_products',
            'block_availability',
            'block_postal_codes'
        );
        
        foreach ($required_tables as $table) {
            $table_name = $wpdb->prefix . $table;
            $exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'");
            
            if ($exists !== $table_name) {
                error_log("Erreur: Table $table_name non créée lors de l'installation");
                throw new Exception("Table manquante: $table_name");
            }
        }
    }
    
    /**
     * Insérer les données par défaut
     */
    public static function seed_default_data() {
        self::insert_default_settings();
        self::insert_default_categories();
        self::insert_default_products();
        self::insert_default_beverages();
        self::insert_postal_codes_sample();
    }
    
    /**
     * Insérer les paramètres par défaut
     */
    private static function insert_default_settings() {
        global $wpdb;
        
        $default_settings = array(
            // Forfaits de base
            array('restaurant_base_price', '300.00', 'number', 'restaurant', 'Prix forfait de base restaurant'),
            array('remorque_base_price', '350.00', 'number', 'remorque', 'Prix forfait de base remorque'),
            
            // Contraintes invités
            array('restaurant_min_guests', '10', 'number', 'restaurant', 'Nombre minimum invités restaurant'),
            array('restaurant_max_guests', '30', 'number', 'restaurant', 'Nombre maximum invités restaurant'),
            array('remorque_min_guests', '20', 'number', 'remorque', 'Nombre minimum invités remorque'),
            array('remorque_max_guests', '100', 'number', 'remorque', 'Nombre maximum invités remorque'),
            
            // Durées
            array('restaurant_min_duration', '2', 'number', 'restaurant', 'Durée minimum restaurant (heures)'),
            array('restaurant_max_duration', '4', 'number', 'restaurant', 'Durée maximum restaurant (heures)'),
            array('remorque_min_duration', '2', 'number', 'remorque', 'Durée minimum remorque (heures)'),
            array('remorque_max_duration', '5', 'number', 'remorque', 'Durée maximum remorque (heures)'),
            
            // Suppléments
            array('hour_supplement', '50.00', 'number', 'pricing', 'Supplément par heure supplémentaire'),
            array('guest_supplement_threshold', '50', 'number', 'pricing', 'Seuil invités pour supplément remorque'),
            array('guest_supplement_price', '150.00', 'number', 'pricing', 'Supplément au-delà de 50 personnes'),
            
            // Informations entreprise
            array('company_name', 'Block Street Food & Events', 'string', 'company', 'Nom de l\'entreprise'),
            array('company_email', 'contact@block-strasbourg.fr', 'string', 'company', 'Email de contact'),
            array('company_phone', '06 58 13 38 05', 'string', 'company', 'Téléphone'),
            array('company_address', '6 allée Adèle Klein, 67000 Strasbourg', 'string', 'company', 'Adresse'),
            array('base_postal_code', '67000', 'string', 'company', 'Code postal de base pour calculs distance')
        );
        
        $table_name = $wpdb->prefix . 'block_settings';
        
        foreach ($default_settings as $setting) {
            $wpdb->replace(
                $table_name,
                array(
                    'setting_key' => $setting[0],
                    'setting_value' => $setting[1], 
                    'setting_type' => $setting[2],
                    'category' => $setting[3],
                    'description' => $setting[4]
                ),
                array('%s', '%s', '%s', '%s', '%s')
            );
        }
    }
    
    /**
     * Insérer les catégories par défaut
     */
    private static function insert_default_categories() {
        global $wpdb;
        
        // Catégories alimentaires
        $food_categories = array(
            array('DOG', 'dog', 'Nos hot-dogs signature', 1),
            array('CROQ', 'croq', 'Nos croque-monsieur signature', 2),
            array('Mini Boss', 'mini_boss', 'Portions réduites pour accompagner', 3),
            array('Accompagnements', 'accompagnement', 'Salades et accompagnements', 4),
            array('Buffet Salé', 'buffet_sale', 'Options buffet salées', 5),
            array('Buffet Sucré', 'buffet_sucre', 'Options desserts et sucrées', 6)
        );
        
        $food_table = $wpdb->prefix . 'block_food_categories';
        
        foreach ($food_categories as $category) {
            $wpdb->replace(
                $food_table,
                array(
                    'name' => $category[0],
                    'slug' => $category[1],
                    'description' => $category[2],
                    'sort_order' => $category[3]
                ),
                array('%s', '%s', '%s', '%d')
            );
        }
        
        // Catégories de boissons
        $beverage_categories = array(
            array('Softs', 'softs', 'Boissons sans alcool', 1),
            array('Vins Blancs', 'vins_blancs', 'Sélection de vins blancs', 2),
            array('Vins Rouges', 'vins_rouges', 'Sélection de vins rouges', 3),
            array('Vins Rosés', 'vins_roses', 'Sélection de vins rosés', 4),
            array('Crémants', 'cremants', 'Crémants et effervescents', 5),
            array('Bières Bouteilles', 'bieres_bouteilles', 'Bières en bouteilles', 6),
            array('Fûts', 'futs', 'Bières en fûts 10L et 20L', 7)
        );
        
        $beverage_table = $wpdb->prefix . 'block_beverage_categories';
        
        foreach ($beverage_categories as $category) {
            $wpdb->replace(
                $beverage_table,
                array(
                    'name' => $category[0],
                    'slug' => $category[1],
                    'description' => $category[2],
                    'sort_order' => $category[3]
                ),
                array('%s', '%s', '%s', '%d')
            );
        }
    }
    
    /**
     * Insérer les produits par défaut
     */
    private static function insert_default_products() {
        global $wpdb;
        
        // Récupérer les IDs des catégories
        $dog_cat = $wpdb->get_var("SELECT id FROM {$wpdb->prefix}block_food_categories WHERE slug = 'dog'");
        $croq_cat = $wpdb->get_var("SELECT id FROM {$wpdb->prefix}block_food_categories WHERE slug = 'croq'");
        $mini_boss_cat = $wpdb->get_var("SELECT id FROM {$wpdb->prefix}block_food_categories WHERE slug = 'mini_boss'");
        $accomp_cat = $wpdb->get_var("SELECT id FROM {$wpdb->prefix}block_food_categories WHERE slug = 'accompagnement'");
        
        $products = array(
            // Recettes DOG
            array('DOG Classique', $dog_cat, 8.50, 'piece', 1, null, 'Saucisse, pain, oignons frits, sauce maison', 'Porc, blé, œufs', 'Gluten, Œufs', '', 1, true, 'both'),
            array('DOG BBQ', $dog_cat, 9.00, 'piece', 1, null, 'Saucisse grillée, sauce BBQ, oignons caramélisés', 'Porc, blé', 'Gluten', '', 2, true, 'both'),
            array('DOG Végé', $dog_cat, 8.50, 'piece', 1, null, 'Saucisse végétale, légumes grillés', 'Blé, soja', 'Gluten, Soja', '', 3, true, 'both'),
            
            // Recettes CROQ
            array('CROQ Jambon', $croq_cat, 8.50, 'piece', 1, null, 'Pain de mie, jambon, fromage, béchamel', 'Porc, blé, lait', 'Gluten, Lait', '', 1, true, 'both'),
            array('CROQ Saumon', $croq_cat, 10.00, 'piece', 1, null, 'Saumon fumé, fromage à la crème, aneth', 'Poisson, blé, lait', 'Gluten, Lait, Poisson', '', 2, true, 'both'),
            array('CROQ Végétarien', $croq_cat, 8.50, 'piece', 1, null, 'Légumes grillés, fromage de chèvre', 'Blé, lait', 'Gluten, Lait', '', 3, true, 'both'),
            
            // Mini Boss  
            array('Mini DOG', $mini_boss_cat, 6.00, 'piece', 1, null, 'Version réduite du DOG classique', 'Porc, blé, œufs', 'Gluten, Œufs', '', 1, true, 'both'),
            array('Mini CROQ', $mini_boss_cat, 6.00, 'piece', 1, null, 'Version réduite du CROQ jambon', 'Porc, blé, lait', 'Gluten, Lait', '', 2, true, 'both'),
            
            // Accompagnements
            array('Salade verte', $accomp_cat, 4.00, 'portion', 1, null, 'Salade fraîche de saison, vinaigrette', '', '', '', 1, true, 'both'),
            array('Frites maison', $accomp_cat, 4.50, 'portion', 1, null, 'Frites fraîches coupées maison', 'Pommes de terre', '', '', 2, true, 'both'),
            array('Salade de crudités', $accomp_cat, 5.00, 'portion', 1, null, 'Carottes, radis, concombre, tomates', '', '', '', 3, true, 'both')
        );
        
        $products_table = $wpdb->prefix . 'block_products';
        
        foreach ($products as $product) {
            $wpdb->replace(
                $products_table,
                array(
                    'name' => $product[0],
                    'category_id' => $product[1],
                    'price' => $product[2],
                    'unit' => $product[3],
                    'min_quantity' => $product[4],
                    'max_quantity' => $product[5],
                    'description' => $product[6],
                    'ingredients' => $product[7],
                    'allergens' => $product[8], 
                    'image_url' => $product[9],
                    'sort_order' => $product[10],
                    'is_active' => $product[11],
                    'service_type' => $product[12]
                ),
                array('%s', '%d', '%f', '%s', '%d', '%d', '%s', '%s', '%s', '%s', '%d', '%d', '%s')
            );
        }
    }
    
    /**
     * Insérer les boissons par défaut
     */
    private static function insert_default_beverages() {
        global $wpdb;
        
        // Récupérer les IDs des catégories
        $soft_cat = $wpdb->get_var("SELECT id FROM {$wpdb->prefix}block_beverage_categories WHERE slug = 'softs'");
        $blanc_cat = $wpdb->get_var("SELECT id FROM {$wpdb->prefix}block_beverage_categories WHERE slug = 'vins_blancs'");
        $rouge_cat = $wpdb->get_var("SELECT id FROM {$wpdb->prefix}block_beverage_categories WHERE slug = 'vins_rouges'");
        
        $beverages = array(
            // Softs
            array('Eau plate', $soft_cat, 1.50, '50cl', 500, 0.0, 'bouteille', 'Eau de source naturelle', '', '', 1, true, 'both'),
            array('Eau pétillante', $soft_cat, 1.80, '50cl', 500, 0.0, 'bouteille', 'Eau gazeuse naturelle', '', '', 2, true, 'both'),
            array('Jus d\'orange', $soft_cat, 3.00, '25cl', 250, 0.0, 'bouteille', 'Jus 100% fruit pressé', '', '', 3, true, 'both'),
            array('Jus de pomme', $soft_cat, 3.00, '25cl', 250, 0.0, 'bouteille', 'Jus 100% pommes locales', '', '', 4, true, 'both'),
            array('Coca-Cola', $soft_cat, 2.50, '33cl', 330, 0.0, 'canette', 'Coca-Cola original', '', '', 5, true, 'both'),
            array('Limonade artisanale', $soft_cat, 3.50, '25cl', 250, 0.0, 'bouteille', 'Limonade maison au citron', '', '', 6, true, 'both'),
            
            // Vins Blancs
            array('Riesling Alsace', $blanc_cat, 18.00, '75cl', 750, 12.5, 'bouteille', 'Vin blanc sec alsacien, notes florales', 'Alsace', '', 1, true, 'both'),
            array('Gewürztraminer', $blanc_cat, 20.00, '75cl', 750, 13.0, 'bouteille', 'Vin blanc aromatique, épicé', 'Alsace', '', 2, true, 'both'),
            array('Pinot Blanc', $blanc_cat, 16.00, '75cl', 750, 12.0, 'bouteille', 'Vin blanc frais et fruité', 'Alsace', '', 3, true, 'both'),
            
            // Vins Rouges  
            array('Pinot Noir Alsace', $rouge_cat, 22.00, '75cl', 750, 13.0, 'bouteille', 'Vin rouge délicat aux arômes de fruits rouges', 'Alsace', '', 1, true, 'both'),
            array('Côtes du Rhône', $rouge_cat, 16.00, '75cl', 750, 13.5, 'bouteille', 'Vin rouge charpenté et généreux', 'Rhône', '', 2, true, 'both'),
            array('Beaujolais', $rouge_cat, 14.00, '75cl', 750, 12.5, 'bouteille', 'Vin rouge fruité et gouleyant', 'Beaujolais', '', 3, true, 'both')
        );
        
        $beverages_table = $wpdb->prefix . 'block_beverages';
        
        foreach ($beverages as $beverage) {
            $wpdb->replace(
                $beverages_table,
                array(
                    'name' => $beverage[0],
                    'category_id' => $beverage[1],
                    'price' => $beverage[2],
                    'volume' => $beverage[3],
                    'volume_ml' => $beverage[4],
                    'alcohol_degree' => $beverage[5],
                    'container_type' => $beverage[6],
                    'description' => $beverage[7],
                    'origin' => $beverage[8],
                    'image_url' => $beverage[9],
                    'sort_order' => $beverage[10],
                    'is_active' => $beverage[11],
                    'service_type' => $beverage[12]
                ),
                array('%s', '%d', '%f', '%s', '%d', '%f', '%s', '%s', '%s', '%s', '%d', '%d', '%s')
            );
        }
    }
    
    /**
     * Insérer un échantillon de codes postaux
     */
    private static function insert_postal_codes_sample() {
        global $wpdb;
        
        $postal_codes = array(
            array('67000', 'Strasbourg', '67', 'Grand Est', 48.5734053, 7.7521113, 'urban'),
            array('67100', 'Strasbourg', '67', 'Grand Est', 48.5734053, 7.7521113, 'urban'),
            array('67200', 'Strasbourg', '67', 'Grand Est', 48.5734053, 7.7521113, 'urban'),
            array('68000', 'Colmar', '68', 'Grand Est', 48.0793589, 7.3584461, 'urban'),
            array('54000', 'Nancy', '54', 'Grand Est', 48.6921395, 6.1844023, 'urban'),
            array('57000', 'Metz', '57', 'Grand Est', 49.1193089, 6.1757156, 'urban')
        );
        
        $postal_table = $wpdb->prefix . 'block_postal_codes';
        
        foreach ($postal_codes as $postal) {
            $wpdb->replace(
                $postal_table,
                array(
                    'postal_code' => $postal[0],
                    'city' => $postal[1],
                    'department' => $postal[2],
                    'region' => $postal[3],
                    'latitude' => $postal[4],
                    'longitude' => $postal[5],
                    'terrain_type' => $postal[6]
                ),
                array('%s', '%s', '%s', '%s', '%f', '%f', '%s')
            );
        }
    }
    
    /**
     * Supprimer toutes les tables du plugin
     */
    public static function drop_tables() {
        global $wpdb;
        
        $tables = array(
            'block_quote_products',
            'block_quotes',
            'block_beverages',
            'block_products',
            'block_beverage_categories',
            'block_food_categories',
            'block_availability',
            'block_postal_codes',
            'block_settings'
        );
        
        foreach ($tables as $table) {
            $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}{$table}");
        }
    }
}