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
     * Créer toutes les tables du plugin selon les spécifications exactes
     */
    public static function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        $sql = array();
        
        // 1. Table wp_restaurant_products (selon spécifications)
        $sql[] = "CREATE TABLE {$wpdb->prefix}restaurant_products (
            id int(11) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            description text,
            price decimal(10,2) NOT NULL DEFAULT 0.00,
            category_id int(11) NOT NULL,
            unit_type enum('piece','gramme','6personnes','litre','centilitre') DEFAULT 'piece',
            min_quantity int(11) DEFAULT 1,
            max_quantity int(11) DEFAULT NULL,
            is_active boolean DEFAULT TRUE,
            created_at timestamp DEFAULT CURRENT_TIMESTAMP,
            updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY category_id (category_id),
            KEY is_active (is_active)
        ) $charset_collate;";
        
        // 2. Table wp_restaurant_categories (selon spécifications)
        $sql[] = "CREATE TABLE {$wpdb->prefix}restaurant_categories (
            id int(11) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            type enum('plat_signature','mini_boss','accompagnement','buffet_sale','buffet_sucre','soft','vin_blanc','vin_rouge','vin_rose','cremant','biere','fut','option') NOT NULL,
            service_type enum('restaurant','remorque','both') DEFAULT 'both',
            is_required boolean DEFAULT FALSE,
            min_selection int(11) DEFAULT 0,
            max_selection int(11) DEFAULT NULL,
            created_at timestamp DEFAULT CURRENT_TIMESTAMP,
            updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY type (type),
            KEY service_type (service_type)
        ) $charset_collate;";
        
        // 3. Table wp_restaurant_quotes (selon spécifications)
        $sql[] = "CREATE TABLE {$wpdb->prefix}restaurant_quotes (
            id int(11) NOT NULL AUTO_INCREMENT,
            service_type enum('restaurant','remorque') NOT NULL,
            event_date date NOT NULL,
            event_duration int(11) NOT NULL COMMENT 'en heures',
            guest_count int(11) NOT NULL,
            postal_code varchar(10) DEFAULT NULL COMMENT 'pour remorque uniquement',
            customer_data json,
            selected_products json,
            base_price decimal(10,2) NOT NULL DEFAULT 0.00,
            total_price decimal(10,2) NOT NULL DEFAULT 0.00,
            status enum('draft','sent','confirmed') DEFAULT 'draft',
            created_at timestamp DEFAULT CURRENT_TIMESTAMP,
            updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY service_type (service_type),
            KEY event_date (event_date),
            KEY status (status)
        ) $charset_collate;";
        
        // 4. Table wp_restaurant_availability (selon spécifications)
        $sql[] = "CREATE TABLE {$wpdb->prefix}restaurant_availability (
            id int(11) NOT NULL AUTO_INCREMENT,
            date date NOT NULL,
            service_type enum('restaurant','remorque','both') NOT NULL,
            is_available boolean DEFAULT TRUE,
            notes text,
            created_at timestamp DEFAULT CURRENT_TIMESTAMP,
            updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY date_service (date, service_type)
        ) $charset_collate;";
        
        // 5. Table pour les codes postaux et calcul de distance
        $sql[] = "CREATE TABLE {$wpdb->prefix}restaurant_postal_codes (
            id int(11) NOT NULL AUTO_INCREMENT,
            postal_code varchar(5) NOT NULL,
            city varchar(255) NOT NULL,
            department varchar(3) NOT NULL,
            region varchar(255),
            latitude decimal(10,7),
            longitude decimal(10,7),
            distance_from_base decimal(8,2) DEFAULT NULL COMMENT 'Distance depuis 67000 Strasbourg en km',
            delivery_zone enum('gratuit','zone1','zone2','zone3','zone4') DEFAULT 'zone4',
            delivery_price decimal(10,2) DEFAULT 0.00,
            PRIMARY KEY (id),
            UNIQUE KEY postal_code (postal_code),
            KEY department (department),
            KEY delivery_zone (delivery_zone)
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
     * Vérifier que les tables ont été créées (nouvelles tables selon spécifications)
     */
    private static function verify_tables_creation() {
        global $wpdb;
        
        $required_tables = array(
            'restaurant_products',
            'restaurant_categories',
            'restaurant_quotes',
            'restaurant_availability',
            'restaurant_postal_codes'
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
     * Insérer les données par défaut selon les spécifications
     */
    public static function seed_default_data() {
        self::insert_default_categories();
        self::insert_default_products();
        self::insert_postal_codes_sample();
    }
    
    /**
     * Insérer les catégories par défaut selon les spécifications
     */
    private static function insert_default_categories() {
        global $wpdb;
        
        $categories = array(
            // Plats signature (DOG/CROQ)
            array('DOG', 'plat_signature', 'restaurant', true, 1, 1),
            array('CROQ', 'plat_signature', 'restaurant', true, 1, 1),
            
            // Mini Boss (obligatoire, minimum 1 par convive)
            array('Mini Boss', 'mini_boss', 'both', true, 1, null),
            
            // Accompagnements (obligatoire, minimum 1 par convive)  
            array('Accompagnements', 'accompagnement', 'both', true, 1, null),
            
            // Buffets (au moins 1 buffet obligatoire)
            array('Buffet Salé', 'buffet_sale', 'both', false, 2, null),
            array('Buffet Sucré', 'buffet_sucre', 'both', false, 1, null),
            
            // Boissons (optionnelles)
            array('Softs', 'soft', 'both', false, 0, null),
            array('Vins Blancs', 'vin_blanc', 'both', false, 0, null),
            array('Vins Rouges', 'vin_rouge', 'both', false, 0, null),
            array('Vins Rosés', 'vin_rose', 'both', false, 0, null),
            array('Crémants', 'cremant', 'both', false, 0, null),
            array('Bières', 'biere', 'both', false, 0, null),
            array('Fûts', 'fut', 'both', false, 0, null),
            
            // Options (remorque uniquement)
            array('Options Remorque', 'option', 'remorque', false, 0, null)
        );
        
        $table_name = $wpdb->prefix . 'restaurant_categories';
        
        foreach ($categories as $category) {
            $wpdb->replace(
                $table_name,
                array(
                    'name' => $category[0],
                    'type' => $category[1],
                    'service_type' => $category[2],
                    'is_required' => $category[3],
                    'min_selection' => $category[4],
                    'max_selection' => $category[5]
                ),
                array('%s', '%s', '%s', '%d', '%d', '%d')
            );
        }
    }
    
    /**
     * Insérer les produits par défaut selon les spécifications
     */
    private static function insert_default_products() {
        global $wpdb;
        
        // Récupérer les IDs des catégories
        $categories = $wpdb->get_results(
            "SELECT id, type FROM {$wpdb->prefix}restaurant_categories",
            OBJECT_K
        );
        
        $cat_ids = array();
        foreach ($categories as $cat) {
            $cat_ids[$cat->type] = $cat->id;
        }
        
        $products = array(
            // Plats signature DOG
            array('DOG Classique', 'Hot-dog signature avec saucisse artisanale', 8.50, $cat_ids['plat_signature'], 'piece', 1, null),
            array('DOG BBQ', 'Hot-dog BBQ avec sauce maison', 9.00, $cat_ids['plat_signature'], 'piece', 1, null),
            array('DOG Végétarien', 'Hot-dog végétarien avec légumes grillés', 8.50, $cat_ids['plat_signature'], 'piece', 1, null),
            
            // Plats signature CROQ
            array('CROQ Jambon', 'Croque-monsieur au jambon de Parme', 8.50, $cat_ids['plat_signature'], 'piece', 1, null),
            array('CROQ Saumon', 'Croque-monsieur au saumon fumé', 10.00, $cat_ids['plat_signature'], 'piece', 1, null),
            array('CROQ Végétarien', 'Croque-monsieur aux légumes grillés', 8.50, $cat_ids['plat_signature'], 'piece', 1, null),
            
            // Mini Boss (prix fixe 8€)
            array('Mini DOG', 'Version réduite du DOG classique', 8.00, $cat_ids['mini_boss'], 'piece', 1, null),
            array('Mini CROQ', 'Version réduite du CROQ jambon', 8.00, $cat_ids['mini_boss'], 'piece', 1, null),
            
            // Accompagnements
            array('Salade verte', 'Salade fraîche de saison', 4.00, $cat_ids['accompagnement'], 'portion', 1, null),
            array('Frites maison', 'Frites fraîches coupées maison', 4.50, $cat_ids['accompagnement'], 'portion', 1, null),
            array('Sauce chimichurri', 'Supplément sauce pour frites', 1.00, $cat_ids['accompagnement'], 'piece', 0, null),
            
            // Buffet Salé (7 items selon spécifications)
            array('Grilled Cheese', 'Sandwich grillé au fromage', 6.00, $cat_ids['buffet_sale'], 'piece', 0, null),
            array('Supplément Jambon GC', 'Jambon pour Grilled Cheese', 1.00, $cat_ids['buffet_sale'], 'piece', 0, null),
            array('Wraps Poulet', 'Wraps au poulet et crudités', 150, $cat_ids['buffet_sale'], 'gramme', 0, null),
            array('Mini Quiches', 'Assortiment de mini quiches', 3.50, $cat_ids['buffet_sale'], 'piece', 0, null),
            array('Verrines Salées', 'Verrines apéritives variées', 4.00, $cat_ids['buffet_sale'], 'piece', 0, null),
            array('Plateau Fromages', 'Sélection de fromages régionaux', 200, $cat_ids['buffet_sale'], 'gramme', 0, null),
            array('Charcuterie Fine', 'Plateau de charcuterie artisanale', 180, $cat_ids['buffet_sale'], 'gramme', 0, null),
            
            // Buffet Sucré (3 items selon spécifications)
            array('Muffins Maison', 'Muffins aux fruits de saison', 3.50, $cat_ids['buffet_sucre'], 'piece', 0, null),
            array('Tartelettes Fruits', 'Tartelettes aux fruits frais', 4.50, $cat_ids['buffet_sucre'], 'piece', 0, null),
            array('Brownies Chocolat', 'Brownies au chocolat noir', 3.00, $cat_ids['buffet_sucre'], 'piece', 0, null),
            
            // Softs (5 produits selon spécifications)
            array('Eau plate 50cL', 'Eau de source naturelle', 1.50, $cat_ids['soft'], 'piece', 0, null),
            array('Eau plate 1L', 'Eau de source naturelle grande bouteille', 2.50, $cat_ids['soft'], 'piece', 0, null),
            array('Jus Orange 5L', 'Jus d\'orange 100% pur fruit', 15.00, $cat_ids['soft'], 'piece', 0, null),
            array('Jus Orange 20L', 'Jus d\'orange 100% pur fruit - fontaine', 45.00, $cat_ids['soft'], 'piece', 0, null),
            array('Coca-Cola 33cL', 'Coca-Cola original', 2.50, $cat_ids['soft'], 'piece', 0, null),
            
            // Vins (75cL selon spécifications)
            array('Riesling Alsace', 'Vin blanc sec alsacien', 18.00, $cat_ids['vin_blanc'], 'piece', 0, null),
            array('Gewürztraminer', 'Vin blanc aromatique', 20.00, $cat_ids['vin_blanc'], 'piece', 0, null),
            array('Pinot Noir Alsace', 'Vin rouge délicat', 22.00, $cat_ids['vin_rouge'], 'piece', 0, null),
            array('Côtes du Rhône', 'Vin rouge charpenté', 16.00, $cat_ids['vin_rouge'], 'piece', 0, null),
            array('Rosé de Provence', 'Rosé fruité et frais', 15.00, $cat_ids['vin_rose'], 'piece', 0, null),
            array('Crémant d\'Alsace', 'Effervescent alsacien', 25.00, $cat_ids['cremant'], 'piece', 0, null),
            
            // Bières bouteilles
            array('Kronenbourg 33cL', 'Bière blonde française', 3.50, $cat_ids['biere'], 'piece', 0, null),
            array('Heineken 33cL', 'Bière blonde internationale', 4.00, $cat_ids['biere'], 'piece', 0, null),
            array('Leffe Blonde 33cL', 'Bière belge blonde', 4.50, $cat_ids['biere'], 'piece', 0, null),
            
            // Fûts (10L et 20L selon spécifications)
            array('Fût Blonde 10L', 'Bière blonde en fût 10 litres', 85.00, $cat_ids['fut'], 'piece', 0, null),
            array('Fût Blonde 20L', 'Bière blonde en fût 20 litres', 150.00, $cat_ids['fut'], 'piece', 0, null),
            array('Fût Blanche 10L', 'Bière blanche en fût 10 litres', 90.00, $cat_ids['fut'], 'piece', 0, null),
            array('Fût Blanche 20L', 'Bière blanche en fût 20 litres', 160.00, $cat_ids['fut'], 'piece', 0, null),
            array('Fût IPA 10L', 'Bière IPA en fût 10 litres', 95.00, $cat_ids['fut'], 'piece', 0, null),
            array('Fût IPA 20L', 'Bière IPA en fût 20 litres', 170.00, $cat_ids['fut'], 'piece', 0, null),
            array('Fût Ambrée 10L', 'Bière ambrée en fût 10 litres', 88.00, $cat_ids['fut'], 'piece', 0, null),
            array('Fût Ambrée 20L', 'Bière ambrée en fût 20 litres', 155.00, $cat_ids['fut'], 'piece', 0, null),
            
            // Options remorque
            array('Tireuse à bière', 'Mise à disposition tireuse', 50.00, $cat_ids['option'], 'piece', 0, 1),
            array('Installation jeux', 'Installation et animation jeux', 70.00, $cat_ids['option'], 'piece', 0, 1)
        );
        
        $table_name = $wpdb->prefix . 'restaurant_products';
        
        foreach ($products as $product) {
            $wpdb->replace(
                $table_name,
                array(
                    'name' => $product[0],
                    'description' => $product[1],
                    'price' => $product[2],
                    'category_id' => $product[3],
                    'unit_type' => $product[4],
                    'min_quantity' => $product[5],
                    'max_quantity' => $product[6]
                ),
                array('%s', '%s', '%f', '%d', '%s', '%d', '%d')
            );
        }
    }
    
    
    /**
     * Insérer un échantillon de codes postaux avec zones de livraison
     */
    private static function insert_postal_codes_sample() {
        global $wpdb;
        
        $postal_codes = array(
            // Zone gratuite (0-30km)
            array('67000', 'Strasbourg', '67', 'Grand Est', 48.5734053, 7.7521113, 0, 'gratuit', 0.00),
            array('67100', 'Strasbourg', '67', 'Grand Est', 48.5734053, 7.7521113, 0, 'gratuit', 0.00),
            array('67200', 'Strasbourg', '67', 'Grand Est', 48.5734053, 7.7521113, 0, 'gratuit', 0.00),
            array('67118', 'Geispolsheim', '67', 'Grand Est', 48.5158, 7.6447, 12, 'gratuit', 0.00),
            array('67540', 'Ostwald', '67', 'Grand Est', 48.5444, 7.7097, 8, 'gratuit', 0.00),
            
            // Zone 1 (30-50km) : +20€
            array('67600', 'Sélestat', '67', 'Grand Est', 48.2600, 7.4519, 45, 'zone1', 20.00),
            array('67500', 'Haguenau', '67', 'Grand Est', 48.8156, 7.7889, 35, 'zone1', 20.00),
            
            // Zone 2 (50-100km) : +70€
            array('68000', 'Colmar', '68', 'Grand Est', 48.0793589, 7.3584461, 75, 'zone2', 70.00),
            array('68100', 'Mulhouse', '68', 'Grand Est', 47.7508, 7.3359, 110, 'zone2', 70.00),
            
            // Zone 3 (100-150km) : +118€
            array('54000', 'Nancy', '54', 'Grand Est', 48.6921395, 6.1844023, 145, 'zone3', 118.00),
            array('57000', 'Metz', '57', 'Grand Est', 49.1193089, 6.1757156, 135, 'zone3', 118.00),
            
            // Zone 4 (>150km) : Non desservi
            array('75000', 'Paris', '75', 'Île-de-France', 48.8566, 2.3522, 480, 'zone4', 0.00),
            array('69000', 'Lyon', '69', 'Auvergne-Rhône-Alpes', 45.7640, 4.8357, 460, 'zone4', 0.00)
        );
        
        $postal_table = $wpdb->prefix . 'restaurant_postal_codes';
        
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
                    'distance_from_base' => $postal[6],
                    'delivery_zone' => $postal[7],
                    'delivery_price' => $postal[8]
                ),
                array('%s', '%s', '%s', '%s', '%f', '%f', '%f', '%s', '%f')
            );
        }
    }
    
    /**
     * Supprimer toutes les tables du plugin (nouvelles tables selon spécifications)
     */
    public static function drop_tables() {
        global $wpdb;
        
        $tables = array(
            'restaurant_quotes',
            'restaurant_products',
            'restaurant_categories',
            'restaurant_availability',
            'restaurant_postal_codes'
        );
        
        foreach ($tables as $table) {
            $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}{$table}");
        }
    }
}