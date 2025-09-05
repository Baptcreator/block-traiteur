<?php
/**
 * Classe de gestion de la base de données pour Block Traiteur
 * Schéma selon cahier des charges - Version complète et fonctionnelle
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
 * Gère la création et la maintenance des tables de base de données selon les spécifications exactes
 */
class Block_Traiteur_Database {
    
    /**
     * Créer toutes les tables du plugin selon les spécifications exactes du cahier des charges
     */
    public static function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        $sql = array();
        
        // 1. TABLE wp_restaurant_categories (selon spécifications ligne 26-41)
        $sql[] = "CREATE TABLE {$wpdb->prefix}restaurant_categories (
            id int(11) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            slug varchar(100) NOT NULL,
            type enum('plat_signature','mini_boss','accompagnement','buffet_sale','buffet_sucre','soft','vin_blanc','vin_rouge','vin_rose','cremant','biere','fut','option_restaurant','option_remorque') NOT NULL,
            service_type enum('restaurant','remorque','both') DEFAULT 'both',
            description text,
            is_required boolean DEFAULT FALSE,
            min_selection int(11) DEFAULT 0,
            max_selection int(11) DEFAULT NULL,
            min_per_person boolean DEFAULT FALSE,
            display_order int(11) DEFAULT 0,
            is_active boolean DEFAULT TRUE,
            created_at timestamp DEFAULT CURRENT_TIMESTAMP,
            updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY slug (slug),
            KEY type (type),
            KEY service_type (service_type),
            KEY is_active (is_active),
            KEY display_order (display_order)
        ) $charset_collate;";
        
        // 2. TABLE wp_restaurant_products (selon spécifications ligne 43-64)
        $sql[] = "CREATE TABLE {$wpdb->prefix}restaurant_products (
            id int(11) NOT NULL AUTO_INCREMENT,
            category_id int(11) NOT NULL,
            name varchar(255) NOT NULL,
            description text,
            short_description varchar(500),
            price decimal(10,2) NOT NULL DEFAULT 0.00,
            unit_type enum('piece','gramme','portion_6p','litre','centilitre','bouteille') DEFAULT 'piece',
            unit_label varchar(50) DEFAULT '/pièce',
            min_quantity int(11) DEFAULT 1,
            max_quantity int(11) DEFAULT NULL,
            has_supplement boolean DEFAULT FALSE,
            supplement_name varchar(255),
            supplement_price decimal(10,2) DEFAULT 0.00,
            image_url varchar(500),
            alcohol_degree decimal(3,1),
            volume_cl int(11),
            display_order int(11) DEFAULT 0,
            is_active boolean DEFAULT TRUE,
            created_at timestamp DEFAULT CURRENT_TIMESTAMP,
            updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY category_id (category_id),
            KEY is_active (is_active),
            KEY display_order (display_order),
            FOREIGN KEY (category_id) REFERENCES {$wpdb->prefix}restaurant_categories(id) ON DELETE CASCADE
        ) $charset_collate;";
        
        // 3. TABLE wp_restaurant_settings (selon spécifications ligne 66-76)
        $sql[] = "CREATE TABLE {$wpdb->prefix}restaurant_settings (
            id int(11) NOT NULL AUTO_INCREMENT,
            setting_key varchar(100) NOT NULL,
            setting_value longtext,
            setting_type enum('text','number','boolean','json','html') DEFAULT 'text',
            setting_group varchar(100),
            description text,
            is_active boolean DEFAULT TRUE,
            PRIMARY KEY (id),
            UNIQUE KEY setting_key (setting_key),
            KEY setting_group (setting_group),
            KEY is_active (is_active)
        ) $charset_collate;";
        
        // 4. TABLE wp_restaurant_quotes (selon spécifications ligne 77-98)
        $sql[] = "CREATE TABLE {$wpdb->prefix}restaurant_quotes (
            id int(11) NOT NULL AUTO_INCREMENT,
            quote_number varchar(50) NOT NULL,
            service_type enum('restaurant','remorque') NOT NULL,
            event_date date NOT NULL,
            event_duration int(11) NOT NULL COMMENT 'Durée en heures',
            guest_count int(11) NOT NULL,
            postal_code varchar(10) DEFAULT NULL COMMENT 'Pour remorque',
            distance_km int(11) DEFAULT NULL COMMENT 'Distance calculée',
            customer_data json,
            selected_products json,
            price_breakdown json,
            base_price decimal(10,2) NOT NULL DEFAULT 0.00,
            supplements_total decimal(10,2) NOT NULL DEFAULT 0.00,
            products_total decimal(10,2) NOT NULL DEFAULT 0.00,
            total_price decimal(10,2) NOT NULL DEFAULT 0.00,
            status enum('draft','sent','confirmed','cancelled') DEFAULT 'draft',
            admin_notes text,
            sent_at datetime DEFAULT NULL,
            created_at timestamp DEFAULT CURRENT_TIMESTAMP,
            updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY quote_number (quote_number),
            KEY service_type (service_type),
            KEY event_date (event_date),
            KEY status (status),
            KEY postal_code (postal_code)
        ) $charset_collate;";
        
        // 5. TABLE wp_restaurant_availability (selon spécifications ligne 100-111)
        $sql[] = "CREATE TABLE {$wpdb->prefix}restaurant_availability (
            id int(11) NOT NULL AUTO_INCREMENT,
            date date NOT NULL,
            service_type enum('restaurant','remorque','both') NOT NULL,
            is_available boolean DEFAULT TRUE,
            blocked_reason varchar(255),
            notes text,
            created_by int(11),
            created_at timestamp DEFAULT CURRENT_TIMESTAMP,
            updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY date_service (date, service_type),
            KEY is_available (is_available),
            KEY created_by (created_by)
        ) $charset_collate;";
        
        // 6. TABLE wp_restaurant_delivery_zones (selon spécifications ligne 112-122)
        $sql[] = "CREATE TABLE {$wpdb->prefix}restaurant_delivery_zones (
            id int(11) NOT NULL AUTO_INCREMENT,
            zone_name varchar(100) NOT NULL,
            distance_min int(11) NOT NULL DEFAULT 0,
            distance_max int(11) NOT NULL,
            delivery_price decimal(10,2) NOT NULL DEFAULT 0.00,
            is_active boolean DEFAULT TRUE,
            display_order int(11) DEFAULT 0,
            PRIMARY KEY (id),
            KEY is_active (is_active),
            KEY display_order (display_order)
        ) $charset_collate;";
        
        // Exécuter les requêtes avec gestion d'erreurs
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        foreach ($sql as $query) {
            $result = dbDelta($query);
            if ($wpdb->last_error) {
                error_log("Erreur création table: " . $wpdb->last_error);
                error_log("Requête: " . $query);
            }
        }
        
        // Vérifier que les tables ont été créées
        self::verify_tables_creation();
        
        // Mettre à jour la version de la base de données
        update_option('block_traiteur_db_version', BLOCK_TRAITEUR_VERSION);
        
        error_log("Block Traiteur: Tables créées avec succès");
    }
    
    /**
     * Vérifier que les tables ont été créées selon les spécifications
     */
    private static function verify_tables_creation() {
        global $wpdb;
        
        $required_tables = array(
            'restaurant_categories',
            'restaurant_products',
            'restaurant_settings',
            'restaurant_quotes',
            'restaurant_availability',
            'restaurant_delivery_zones'
        );
        
        foreach ($required_tables as $table) {
            $table_name = $wpdb->prefix . $table;
            $exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'");
            
            if ($exists !== $table_name) {
                error_log("Erreur: Table $table_name non créée lors de l'installation");
                throw new Exception("Table manquante: $table_name");
            }
        }
        
        error_log("Block Traiteur: Toutes les tables ont été créées avec succès");
    }
    
    /**
     * Insérer les données par défaut selon les spécifications exactes du cahier des charges
     */
    public static function seed_default_data() {
        self::insert_default_settings();
        self::insert_default_categories();
        self::insert_default_products();
        self::insert_default_delivery_zones();
    }
    
    /**
     * Insérer les paramètres par défaut selon les spécifications (ligne 127-213)
     */
    private static function insert_default_settings() {
        global $wpdb;
        
        $settings = array(
            // Forfaits de base
            array('restaurant_base_price', '300', 'number', 'pricing', 'Prix forfait restaurant'),
            array('remorque_base_price', '350', 'number', 'pricing', 'Prix forfait remorque'),
            array('restaurant_included_hours', '2', 'number', 'pricing', 'Heures incluses restaurant'),
            array('remorque_included_hours', '2', 'number', 'pricing', 'Heures incluses remorque'),
            array('hourly_supplement', '50', 'number', 'pricing', 'Supplément horaire'),
            
            // Contraintes participants
            array('restaurant_min_guests', '10', 'number', 'constraints', 'Minimum convives restaurant'),
            array('restaurant_max_guests', '30', 'number', 'constraints', 'Maximum convives restaurant'),
            array('remorque_min_guests', '20', 'number', 'constraints', 'Minimum convives remorque'),
            array('remorque_max_guests', '100', 'number', 'constraints', 'Maximum convives remorque'),
            
            // Contraintes durée
            array('restaurant_max_hours', '4', 'number', 'constraints', 'Durée maximum restaurant'),
            array('remorque_max_hours', '5', 'number', 'constraints', 'Durée maximum remorque'),
            
            // Suppléments remorque
            array('remorque_50_guests_supplement', '150', 'number', 'pricing', 'Supplément +50 convives'),
            array('remorque_max_delivery_distance', '150', 'number', 'constraints', 'Distance maximum livraison'),
            array('restaurant_postal_code', '67000', 'text', 'general', 'Code postal restaurant'),
            
            // Textes interface utilisateur
            array('homepage_restaurant_title', 'LE RESTAURANT', 'text', 'texts', 'Titre restaurant'),
            array('homepage_restaurant_description', 'Découvrez notre restaurant unique avec une cuisine de qualité et une ambiance chaleureuse.', 'html', 'texts', 'Description restaurant'),
            array('homepage_button_menu', 'Voir le menu', 'text', 'texts', 'Bouton menu'),
            array('homepage_button_booking', 'Réserver à table', 'text', 'texts', 'Bouton réservation'),
            array('homepage_traiteur_title', 'LE TRAITEUR ÉVÉNEMENTIEL', 'text', 'texts', 'Titre traiteur'),
            array('homepage_button_privatiser', 'Privatiser Block', 'text', 'texts', 'Bouton privatisation'),
            array('homepage_button_infos', 'Infos', 'text', 'texts', 'Bouton infos'),
            
            // Page traiteur
            array('traiteur_restaurant_title', 'Privatisation du restaurant', 'text', 'texts', 'Titre privatisation restaurant'),
            array('traiteur_restaurant_subtitle', 'De 10 à 30 personnes', 'text', 'texts', 'Sous-titre restaurant'),
            array('traiteur_restaurant_description', 'Privatisez notre restaurant pour vos événements privés dans un cadre unique et convivial.', 'html', 'texts', 'Description privatisation restaurant'),
            array('traiteur_remorque_title', 'Privatisation de la remorque Block', 'text', 'texts', 'Titre remorque'),
            array('traiteur_remorque_subtitle', 'À partir de 20 personnes', 'text', 'texts', 'Sous-titre remorque'),
            array('traiteur_remorque_description', 'Notre remorque mobile se déplace chez vous pour tous vos événements.', 'html', 'texts', 'Description remorque'),
            
            // Forfaits de base - Descriptions
            array('restaurant_package_description', '<h3>Forfait Restaurant - 300€</h3><p>Privatisation complète du restaurant pour 2 heures incluses.</p>', 'html', 'packages', 'Description forfait restaurant'),
            array('restaurant_package_includes', '["Privatisation complète", "Service 2 heures", "Équipement son", "Décoration de base"]', 'json', 'packages', 'Inclusions restaurant'),
            array('remorque_package_description', '<h3>Forfait Remorque - 350€</h3><p>Remorque mobile avec équipement complet pour 2 heures.</p>', 'html', 'packages', 'Description forfait remorque'),
            array('remorque_package_includes', '["Remorque équipée", "Livraison/retour", "Service 2 heures", "Équipement complet"]', 'json', 'packages', 'Inclusions remorque'),
            
            // Templates d\'emails
            array('email_quote_subject', 'Votre devis privatisation Block', 'text', 'emails', 'Sujet email devis'),
            array('email_quote_header_html', '<div style="text-align:center;"><h1>Block Street Food & Events</h1></div>', 'html', 'emails', 'Header email devis'),
            array('email_quote_body_html', '<p>Madame, Monsieur,</p><p>Veuillez trouver ci-joint votre devis personnalisé.</p>', 'html', 'emails', 'Corps email devis'),
            array('email_quote_footer_html', '<hr><p><small>Block Street Food & Events - SIRET: 123456789</small></p>', 'html', 'emails', 'Footer email devis'),
            
            // Messages de validation
            array('error_date_unavailable', 'Cette date n\'est pas disponible', 'text', 'validation', 'Erreur date indisponible'),
            array('error_guests_min', 'Nombre minimum de convives : {min}', 'text', 'validation', 'Erreur minimum convives'),
            array('error_guests_max', 'Nombre maximum de convives : {max}', 'text', 'validation', 'Erreur maximum convives'),
            array('error_duration_max', 'Durée maximum : {max} heures', 'text', 'validation', 'Erreur durée maximum'),
            array('error_selection_required', 'Sélection obligatoire', 'text', 'validation', 'Erreur sélection obligatoire'),
        );
        
        $table_name = $wpdb->prefix . 'restaurant_settings';
        
        foreach ($settings as $setting) {
            $wpdb->replace(
                $table_name,
                array(
                    'setting_key' => $setting[0],
                    'setting_value' => $setting[1],
                    'setting_type' => $setting[2],
                    'setting_group' => $setting[3],
                    'description' => $setting[4]
                ),
                array('%s', '%s', '%s', '%s', '%s')
            );
        }
        
        error_log("Block Traiteur: Paramètres par défaut insérés");
    }
    
    /**
     * Insérer les catégories par défaut selon les spécifications exactes
     */
    private static function insert_default_categories() {
        global $wpdb;
        
        $categories = array(
            // Plats signature (obligatoire pour restaurant)
            array('Plats Signature', 'plats-signature', 'plat_signature', 'restaurant', 'Nos plats signature DOG et CROQ', true, 1, 1, true, 1),
            
            // Mini Boss (obligatoire, minimum 1 par convive)
            array('Mini Boss', 'mini-boss', 'mini_boss', 'both', 'Versions réduites de nos plats signature', true, 1, null, true, 2),
            
            // Accompagnements (obligatoire, minimum 1 par convive)  
            array('Accompagnements', 'accompagnements', 'accompagnement', 'both', 'Salades, frites et sauces', true, 1, null, true, 3),
            
            // Buffets (au moins 2 buffets salés obligatoires)
            array('Buffet Salé', 'buffet-sale', 'buffet_sale', 'both', 'Sélection de plats salés pour buffet', true, 2, null, false, 4),
            array('Buffet Sucré', 'buffet-sucre', 'buffet_sucre', 'both', 'Desserts et douceurs', false, 0, null, false, 5),
            
            // Boissons (optionnelles)
            array('Softs', 'softs', 'soft', 'both', 'Boissons sans alcool', false, 0, null, false, 6),
            array('Vins Blancs', 'vins-blancs', 'vin_blanc', 'both', 'Sélection de vins blancs', false, 0, null, false, 7),
            array('Vins Rouges', 'vins-rouges', 'vin_rouge', 'both', 'Sélection de vins rouges', false, 0, null, false, 8),
            array('Vins Rosés', 'vins-roses', 'vin_rose', 'both', 'Sélection de vins rosés', false, 0, null, false, 9),
            array('Crémants', 'cremants', 'cremant', 'both', 'Vins effervescents', false, 0, null, false, 10),
            array('Bières', 'bieres', 'biere', 'both', 'Bières en bouteille', false, 0, null, false, 11),
            array('Fûts', 'futs', 'fut', 'both', 'Bières en fût', false, 0, null, false, 12),
            
            // Options
            array('Options Restaurant', 'options-restaurant', 'option_restaurant', 'restaurant', 'Options spécifiques au restaurant', false, 0, null, false, 13),
            array('Options Remorque', 'options-remorque', 'option_remorque', 'remorque', 'Options spécifiques à la remorque', false, 0, null, false, 14)
        );
        
        $table_name = $wpdb->prefix . 'restaurant_categories';
        
        foreach ($categories as $category) {
            $wpdb->replace(
                $table_name,
                array(
                    'name' => $category[0],
                    'slug' => $category[1],
                    'type' => $category[2],
                    'service_type' => $category[3],
                    'description' => $category[4],
                    'is_required' => $category[5],
                    'min_selection' => $category[6],
                    'max_selection' => $category[7],
                    'min_per_person' => $category[8],
                    'display_order' => $category[9]
                ),
                array('%s', '%s', '%s', '%s', '%s', '%d', '%d', '%d', '%d', '%d')
            );
        }
        
        error_log("Block Traiteur: Catégories par défaut insérées");
    }
    
    /**
     * Insérer les produits par défaut selon les spécifications exactes
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
            // Plats signature DOG et CROQ
            array('DOG Classique', 'Hot-dog signature avec saucisse artisanale', 'Hot-dog avec saucisse', 8.50, $cat_ids['plat_signature'], 'piece', '/pièce', 1, null, false, null, 0.00, null, null, null, 1),
            array('DOG BBQ', 'Hot-dog BBQ avec sauce maison', 'Hot-dog BBQ', 9.00, $cat_ids['plat_signature'], 'piece', '/pièce', 1, null, false, null, 0.00, null, null, null, 2),
            array('DOG Végétarien', 'Hot-dog végétarien avec légumes grillés', 'Hot-dog végétarien', 8.50, $cat_ids['plat_signature'], 'piece', '/pièce', 1, null, false, null, 0.00, null, null, null, 3),
            array('CROQ Jambon', 'Croque-monsieur au jambon de Parme', 'Croque-monsieur jambon', 8.50, $cat_ids['plat_signature'], 'piece', '/pièce', 1, null, false, null, 0.00, null, null, null, 4),
            array('CROQ Saumon', 'Croque-monsieur au saumon fumé', 'Croque-monsieur saumon', 10.00, $cat_ids['plat_signature'], 'piece', '/pièce', 1, null, false, null, 0.00, null, null, null, 5),
            array('CROQ Végétarien', 'Croque-monsieur aux légumes grillés', 'Croque-monsieur végétarien', 8.50, $cat_ids['plat_signature'], 'piece', '/pièce', 1, null, false, null, 0.00, null, null, null, 6),
            
            // Mini Boss (prix fixe 8€)
            array('Mini DOG', 'Version réduite du DOG classique', 'Mini version DOG', 8.00, $cat_ids['mini_boss'], 'piece', '/pièce', 1, null, false, null, 0.00, null, null, null, 1),
            array('Mini CROQ', 'Version réduite du CROQ jambon', 'Mini version CROQ', 8.00, $cat_ids['mini_boss'], 'piece', '/pièce', 1, null, false, null, 0.00, null, null, null, 2),
            
            // Accompagnements
            array('Salade verte', 'Salade fraîche de saison', 'Salade fraîche', 4.00, $cat_ids['accompagnement'], 'piece', '/portion', 1, null, false, null, 0.00, null, null, null, 1),
            array('Frites maison', 'Frites fraîches coupées maison', 'Frites fraîches', 4.50, $cat_ids['accompagnement'], 'piece', '/portion', 1, null, true, 'Sauce chimichurri', 1.00, null, null, null, 2),
            
            // Buffet Salé (7 items selon spécifications)
            array('Grilled Cheese', 'Sandwich grillé au fromage fondant', 'Sandwich grillé', 6.00, $cat_ids['buffet_sale'], 'piece', '/pièce', 0, null, true, 'Jambon', 1.00, null, null, null, 1),
            array('Wraps Poulet', 'Wraps au poulet et crudités fraîches', 'Wraps poulet', 0.15, $cat_ids['buffet_sale'], 'gramme', '/g', 0, null, false, null, 0.00, null, null, null, 2),
            array('Mini Quiches', 'Assortiment de mini quiches variées', 'Mini quiches', 3.50, $cat_ids['buffet_sale'], 'piece', '/pièce', 0, null, false, null, 0.00, null, null, null, 3),
            array('Verrines Salées', 'Verrines apéritives variées', 'Verrines salées', 4.00, $cat_ids['buffet_sale'], 'piece', '/pièce', 0, null, false, null, 0.00, null, null, null, 4),
            array('Plateau Fromages', 'Sélection de fromages régionaux', 'Fromages régionaux', 0.20, $cat_ids['buffet_sale'], 'gramme', '/g', 0, null, false, null, 0.00, null, null, null, 5),
            array('Charcuterie Fine', 'Plateau de charcuterie artisanale', 'Charcuterie artisanale', 0.18, $cat_ids['buffet_sale'], 'gramme', '/g', 0, null, false, null, 0.00, null, null, null, 6),
            array('Tartines Gourmandes', 'Tartines variées pour buffet', 'Tartines variées', 5.50, $cat_ids['buffet_sale'], 'piece', '/pièce', 0, null, false, null, 0.00, null, null, null, 7),
            
            // Buffet Sucré (3 items selon spécifications)
            array('Muffins Maison', 'Muffins aux fruits de saison', 'Muffins fruits', 3.50, $cat_ids['buffet_sucre'], 'piece', '/pièce', 0, null, false, null, 0.00, null, null, null, 1),
            array('Tartelettes Fruits', 'Tartelettes aux fruits frais', 'Tartelettes fraîches', 4.50, $cat_ids['buffet_sucre'], 'piece', '/pièce', 0, null, false, null, 0.00, null, null, null, 2),
            array('Brownies Chocolat', 'Brownies au chocolat noir intense', 'Brownies chocolat', 3.00, $cat_ids['buffet_sucre'], 'piece', '/pièce', 0, null, false, null, 0.00, null, null, null, 3),
            
            // Softs (5 produits selon spécifications)
            array('Eau plate 50cL', 'Eau de source naturelle', 'Eau 50cL', 1.50, $cat_ids['soft'], 'bouteille', '/bouteille', 0, null, false, null, 0.00, null, null, 50, 1),
            array('Eau plate 1L', 'Eau de source naturelle grande bouteille', 'Eau 1L', 2.50, $cat_ids['soft'], 'bouteille', '/bouteille', 0, null, false, null, 0.00, null, null, 100, 2),
            array('Jus Orange 5L', 'Jus d\'orange 100% pur fruit', 'Jus orange 5L', 15.00, $cat_ids['soft'], 'bouteille', '/bouteille', 0, null, false, null, 0.00, null, null, 500, 3),
            array('Jus Orange 20L', 'Jus d\'orange 100% pur fruit - fontaine', 'Jus orange fontaine', 45.00, $cat_ids['soft'], 'bouteille', '/fontaine', 0, 1, false, null, 0.00, null, null, 2000, 4),
            array('Coca-Cola 33cL', 'Coca-Cola original', 'Coca 33cL', 2.50, $cat_ids['soft'], 'bouteille', '/bouteille', 0, null, false, null, 0.00, null, null, 33, 5),
            
            // Vins (75cL selon spécifications)
            array('Riesling Alsace', 'Vin blanc sec alsacien traditionnel', 'Riesling 75cL', 18.00, $cat_ids['vin_blanc'], 'bouteille', '/bouteille', 0, null, false, null, 0.00, null, 12.5, 75, 1),
            array('Gewürztraminer', 'Vin blanc aromatique alsacien', 'Gewürztraminer 75cL', 20.00, $cat_ids['vin_blanc'], 'bouteille', '/bouteille', 0, null, false, null, 0.00, null, 13.0, 75, 2),
            array('Pinot Noir Alsace', 'Vin rouge délicat d\'Alsace', 'Pinot Noir 75cL', 22.00, $cat_ids['vin_rouge'], 'bouteille', '/bouteille', 0, null, false, null, 0.00, null, 12.5, 75, 1),
            array('Côtes du Rhône', 'Vin rouge charpenté du Rhône', 'Côtes du Rhône 75cL', 16.00, $cat_ids['vin_rouge'], 'bouteille', '/bouteille', 0, null, false, null, 0.00, null, 13.5, 75, 2),
            array('Rosé de Provence', 'Rosé fruité et frais de Provence', 'Rosé Provence 75cL', 15.00, $cat_ids['vin_rose'], 'bouteille', '/bouteille', 0, null, false, null, 0.00, null, 12.0, 75, 1),
            array('Crémant d\'Alsace', 'Effervescent alsacien traditionnel', 'Crémant 75cL', 25.00, $cat_ids['cremant'], 'bouteille', '/bouteille', 0, null, false, null, 0.00, null, 12.0, 75, 1),
            
            // Bières bouteilles
            array('Kronenbourg 33cL', 'Bière blonde française', 'Kro 33cL', 3.50, $cat_ids['biere'], 'bouteille', '/bouteille', 0, null, false, null, 0.00, null, 5.0, 33, 1),
            array('Heineken 33cL', 'Bière blonde internationale', 'Heineken 33cL', 4.00, $cat_ids['biere'], 'bouteille', '/bouteille', 0, null, false, null, 0.00, null, 5.0, 33, 2),
            array('Leffe Blonde 33cL', 'Bière belge blonde premium', 'Leffe 33cL', 4.50, $cat_ids['biere'], 'bouteille', '/bouteille', 0, null, false, null, 0.00, null, 6.6, 33, 3),
            
            // Fûts (10L et 20L selon spécifications)
            array('Fût Blonde 10L', 'Bière blonde en fût 10 litres', 'Fût Blonde 10L', 85.00, $cat_ids['fut'], 'piece', '/fût', 0, 1, false, null, 0.00, null, 5.0, 1000, 1),
            array('Fût Blonde 20L', 'Bière blonde en fût 20 litres', 'Fût Blonde 20L', 150.00, $cat_ids['fut'], 'piece', '/fût', 0, 1, false, null, 0.00, null, 5.0, 2000, 2),
            array('Fût Blanche 10L', 'Bière blanche en fût 10 litres', 'Fût Blanche 10L', 90.00, $cat_ids['fut'], 'piece', '/fût', 0, 1, false, null, 0.00, null, 5.0, 1000, 3),
            array('Fût Blanche 20L', 'Bière blanche en fût 20 litres', 'Fût Blanche 20L', 160.00, $cat_ids['fut'], 'piece', '/fût', 0, 1, false, null, 0.00, null, 5.0, 2000, 4),
            
            // Options remorque
            array('Tireuse à bière', 'Mise à disposition tireuse professionnelle', 'Tireuse bière', 50.00, $cat_ids['option_remorque'], 'piece', '/service', 0, 1, false, null, 0.00, null, null, null, 1),
            array('Installation jeux', 'Installation et animation jeux', 'Animation jeux', 70.00, $cat_ids['option_remorque'], 'piece', '/service', 0, 1, false, null, 0.00, null, null, null, 2)
        );
        
        $table_name = $wpdb->prefix . 'restaurant_products';
        
        foreach ($products as $product) {
            $wpdb->replace(
                $table_name,
                array(
                    'name' => $product[0],
                    'description' => $product[1],
                    'short_description' => $product[2],
                    'price' => $product[3],
                    'category_id' => $product[4],
                    'unit_type' => $product[5],
                    'unit_label' => $product[6],
                    'min_quantity' => $product[7],
                    'max_quantity' => $product[8],
                    'has_supplement' => $product[9],
                    'supplement_name' => $product[10],
                    'supplement_price' => $product[11],
                    'image_url' => $product[12],
                    'alcohol_degree' => $product[13],
                    'volume_cl' => $product[14],
                    'display_order' => $product[15]
                ),
                array('%s', '%s', '%s', '%f', '%d', '%s', '%s', '%d', '%d', '%d', '%s', '%f', '%s', '%f', '%d', '%d')
            );
        }
        
        error_log("Block Traiteur: Produits par défaut insérés");
    }
    
    /**
     * Insérer les zones de livraison par défaut selon les spécifications (ligne 112-122)
     */
    private static function insert_default_delivery_zones() {
        global $wpdb;
        
        $delivery_zones = array(
            array('Zone Gratuite (0-30km)', 0, 30, 0.00, true, 1),
            array('Zone 1 (30-50km)', 30, 50, 20.00, true, 2),
            array('Zone 2 (50-100km)', 50, 100, 70.00, true, 3),
            array('Zone 3 (100-150km)', 100, 150, 118.00, true, 4),
            array('Hors zone (>150km)', 150, 999, 0.00, false, 5)
        );
        
        $table_name = $wpdb->prefix . 'restaurant_delivery_zones';
        
        foreach ($delivery_zones as $zone) {
            $wpdb->replace(
                $table_name,
                array(
                    'zone_name' => $zone[0],
                    'distance_min' => $zone[1],
                    'distance_max' => $zone[2],
                    'delivery_price' => $zone[3],
                    'is_active' => $zone[4],
                    'display_order' => $zone[5]
                ),
                array('%s', '%d', '%d', '%f', '%d', '%d')
            );
        }
        
        error_log("Block Traiteur: Zones de livraison par défaut insérées");
    }
    
    /**
     * Supprimer toutes les tables du plugin selon les nouvelles spécifications
     */
    public static function drop_tables() {
        global $wpdb;
        
        // Ordre de suppression important à cause des clés étrangères
        $tables = array(
            'restaurant_quotes',
            'restaurant_products',
            'restaurant_categories',
            'restaurant_settings',
            'restaurant_availability',
            'restaurant_delivery_zones'
        );
        
        // Désactiver les vérifications de clés étrangères temporairement
        $wpdb->query("SET FOREIGN_KEY_CHECKS = 0");
        
        foreach ($tables as $table) {
            $table_name = $wpdb->prefix . $table;
            $result = $wpdb->query("DROP TABLE IF EXISTS {$table_name}");
            if ($result === false) {
                error_log("Erreur suppression table: {$table_name} - " . $wpdb->last_error);
            } else {
                error_log("Table supprimée: {$table_name}");
            }
        }
        
        // Réactiver les vérifications de clés étrangères
        $wpdb->query("SET FOREIGN_KEY_CHECKS = 1");
        
        error_log("Block Traiteur: Toutes les tables ont été supprimées");
    }
    
    /**
     * Obtenir la version actuelle de la base de données
     */
    public static function get_db_version() {
        return get_option('block_traiteur_db_version', '0.0.0');
    }
    
    /**
     * Vérifier si la base de données doit être mise à jour
     */
    public static function needs_update() {
        $current_version = self::get_db_version();
        return version_compare($current_version, BLOCK_TRAITEUR_VERSION, '<');
    }
    
    /**
     * Forcer la recréation de la base de données
     */
    public static function force_recreate() {
        error_log("Block Traiteur: Recréation forcée de la base de données");
        
        try {
            self::drop_tables();
            self::create_tables();
            self::seed_default_data();
            
            error_log("Block Traiteur: Base de données recréée avec succès");
            return true;
        } catch (Exception $e) {
            error_log("Block Traiteur: Erreur lors de la recréation - " . $e->getMessage());
            return false;
        }
    }
}