<?php
/**
 * Script de migration et de correction pour les types de vins
 * Ce script corrige les problèmes de duplication et assure une base de données propre
 */

// Assurer qu'on est dans un contexte WordPress
if (!defined('ABSPATH')) {
    require_once(__DIR__ . '/../../../wp-config.php');
}

class WineTypesMigration {
    
    public function __construct() {
        $this->run();
    }
    
    public function run() {
        echo "<h1>🚀 Migration des Types de Vins</h1>";
        
        // 1. Créer la table si elle n'existe pas
        $this->create_wine_types_table();
        
        // 2. Migrer les données depuis l'ancienne structure si nécessaire
        $this->migrate_existing_data();
        
        // 3. Nettoyer les doublons
        $this->clean_duplicates();
        
        // 4. Créer des types par défaut si nécessaire
        $this->create_default_types();
        
        // 5. Vérifier l'intégrité
        $this->verify_integrity();
        
        echo "<hr>";
        echo "<p style='color: green; font-weight: bold;'>✅ Migration terminée !</p>";
        echo "<p><a href='" . admin_url('admin.php?page=restaurant-booking-categories-manager&action=subcategories&category_id=wines_group') . "'>Retour à la gestion des types de vins</a></p>";
    }
    
    private function create_wine_types_table() {
        global $wpdb;
        
        $wine_types_table = $wpdb->prefix . 'restaurant_wine_types';
        
        echo "<h2>📋 Création/Vérification de la table</h2>";
        
        $sql = "CREATE TABLE IF NOT EXISTS $wine_types_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            slug varchar(255) NOT NULL,
            description text,
            display_order int(11) DEFAULT 0,
            is_active tinyint(1) DEFAULT 1,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY unique_slug (slug),
            KEY idx_active (is_active),
            KEY idx_display_order (display_order)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        $result = dbDelta($sql);
        
        if (empty($result)) {
            echo "<p style='color: green;'>✅ Table $wine_types_table existe et est à jour</p>";
        } else {
            echo "<p style='color: blue;'>✅ Table $wine_types_table créée/mise à jour</p>";
            foreach ($result as $message) {
                echo "<p style='margin-left: 20px;'>- $message</p>";
            }
        }
    }
    
    private function migrate_existing_data() {
        global $wdb;
        
        $wine_types_table = $wpdb->prefix . 'restaurant_wine_types';
        
        echo "<h2>🔄 Migration des données existantes</h2>";
        
        // Vérifier s'il y a des données dans l'ancienne table
        $subcategories_table = $wpdb->prefix . 'restaurant_subcategories';
        $wine_category_id = $wpdb->get_var("SELECT id FROM {$wpdb->prefix}restaurant_categories WHERE type = 'vin' LIMIT 1");
        
        if ($wine_category_id && $wpdb->get_var("SHOW TABLES LIKE '$subcategories_table'")) {
            $existing_subcats = $wpdb->get_results($wpdb->prepare("
                SELECT subcategory_key, subcategory_name, display_order
                FROM $subcategories_table
                WHERE parent_category_id = %d AND is_active = 1
            ", $wine_category_id));
            
            if (!empty($existing_subcats)) {
                echo "<p>Trouvé " . count($existing_subcats) . " sous-catégories existantes</p>";
                
                foreach ($existing_subcats as $subcat) {
                    // Vérifier si la clé existe déjà dans la nouvelle table
                    $exists = $wpdb->get_var($wpdb->prepare(
                        "SELECT COUNT(*) FROM $wine_types_table WHERE slug = %s",
                        $subcat->subcategory_key
                    ));
                    
                    if ($exists == 0) {
                        $wpdb->insert($wine_types_table, [
                            'name' => ucfirst($subcat->subcategory_name),
                            'slug' => $subcat->subcategory_key,
                            'description' => "Migré depuis l'ancienne structure",
                            'display_order' => $subcat->display_order ?: 10,
                            'is_active' => 1
                        ]);
                        
                        if ($wpdb->insert_id) {
                            echo "<p style='color: green; margin-left: 20px;'>✅ Migré: {$subcat->subcategory_name}</p>";
                        }
                    } else {
                        echo "<p style='color: orange; margin-left: 20px;'>⚠️ Déjà existe: {$subcat->subcategory_name}</p>";
                    }
                }
            }
        }
        
        // Migrer depuis les produits existants
        $product_types = $wpdb->get_results("
            SELECT DISTINCT wine_category, COUNT(*) as count
            FROM {$wpdb->prefix}restaurant_products p
            INNER JOIN {$wpdb->prefix}restaurant_categories c ON p.category_id = c.id
            WHERE c.type = 'vin' 
            AND p.wine_category IS NOT NULL 
            AND p.wine_category != ''
            GROUP BY wine_category
        ");
        
        if (!empty($product_types)) {
            echo "<p>Trouvé " . count($product_types) . " types dans les produits existants:</p>";
            
            foreach ($product_types as $type) {
                // Vérifier si la clé existe déjà dans la nouvelle table
                $exists = $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(*) FROM $wine_types_table WHERE slug = %s",
                    $type->wine_category
                ));
                
                if ($exists == 0) {
                    $wpdb->insert($wine_types_table, [
                        'name' => ucfirst($type->wine_category),
                        'slug' => $type->wine_category,
                        'description' => "Trouvé dans {$type->count} produit(s)",
                        'display_order' => 50,
                        'is_active' => 1
                    ]);
                    
                    if ($wpdb->insert_id) {
                        echo "<p style='color: green; margin-left: 20px;'>✅ Migré depuis produits: {$type->wine_category} ({$type->count} produit(s))</p>";
                    }
                } else {
                    echo "<p style='color: orange; margin-left: 20px;'>⚠️ Déjà migrateé: {$type->wine_category}</p>";
                }
            }
        }
    }
    
    private function clean_duplicates() {
        global $wpdb;
        
        $wine_types_table = $wpdb->prefix . 'restaurant_wine_types';
        
        echo "<h2>🧹 Nettoyage des doublons</h2>";
        
        // Trouver les doublons
        $duplicates = $wpdb->get_results("
            SELECT slug, COUNT(*) as count 
            FROM $wine_types_table 
            WHERE is_active = 1 
            GROUP BY slug 
            HAVING COUNT(*) > 1
        ");
        
        if (!empty($duplicates)) {
            echo "<p style='color: red;'>Trouvé " . count($duplicates) . " slug(s) avec des doublons:</p>";
            
            foreach ($duplicates as $dup) {
                echo "<p>- Slug '{$dup->slug}' apparaît {$dup->count} fois</p>";
                
                // Garder la plus ancienne entrée et supprimer les autres
                $wpdb->query($wpdb->prepare("
                    DELETE FROM $wine_types_table 
                    WHERE slug = %s AND is_active = 1
                    AND id NOT IN (
                        SELECT * FROM (
                            SELECT MIN(id) FROM $wine_types_table WHERE slug = %s AND is_active = 1
                        ) temp
                    )
                ", $dup->slug, $dup->slug));
                
                echo "<p style='color: green; margin-left: 20px;'>✅ Nettoyé: {$dup->slug}</p>";
            }
        } else {
            echo "<p style='color: green;'>✅ Aucun doublon trouvé</p>";
        }
    }
    
    private function create_default_types() {
        global $wpdb;
        
        $wine_types_table = $wpdb->prefix . 'restaurant_wine_types';
        
        echo "<h2>🎯 Création des types par défaut</h2>";
        
        $default_types = [
            ['name' => 'Blanc', 'slug' => 'blanc', 'order' => 10],
            ['name' => 'Rouge', 'slug' => 'rouge', 'order' => 20],
            ['name' => 'Rosé', 'slug' => 'rose', 'order' => 30],
            ['name' => 'Champagne', 'slug' => 'champagne', 'order' => 40],
        ];
        
        foreach ($default_types as $default_type) {
            $exists = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM $wine_types_table WHERE slug = %s",
                $default_type['slug']
            ));
            
            if ($exists == 0) {
                $wpdb->insert($wine_types_table, [
                    'name' => $default_type['name'],
                    'slug' => $default_type['slug'],
                    'description' => "Type de vin par défaut",
                    'display_order' => $default_type['order'],
                    'is_active' => 1
                ]);
                
                if ($wpdb->insert_id) {
                    echo "<p style='color: green; margin-left: 20px;'>✅ Créé type par défaut: {$default_type['name']}</p>";
                }
            } else {
                echo "<p style='color: orange; margin-left: 20px;'>⚠️ Existe déjà: {$default_type['name']}</p>";
            }
        }
    }
    
    private function verify_integrity() {
        global $wpdb;
        
        $wine_types_table = $wpdb->prefix . 'restaurant_wine_types';
        
        echo "<h2>✅ Vérification de l'intégrité</h2>";
        
        // Compter les types actifs
        $count = $wpdb->get_var("SELECT COUNT(*) FROM $wine_types_table WHERE is_active = 1");
        echo "<p>Types de vins actifs: $count</p>";
        
        // Vérifier les slugs uniques
        $unique_slugs = $wpdb->get_var("SELECT COUNT(DISTINCT slug) FROM $wine_types_table WHERE is_active = 1");
        echo "<p>Slugs uniques: $unique_slugs</p>";
        
        if ($count == $unique_slugs) {
            echo "<p style='color: green;'>✅ Intégrité: OK - Pas de doublons</p>";
        } else {
            echo "<p style='color: red;'>❌ Intégrité: PROBLÈME - Détection de $count entrées avec seulement $unique_slugs slugs uniques</p>";
        }
        
        // Afficher la liste finale
        $final_types = $wpdb->get_results("SELECT slug, name FROM $wine_types_table WHERE is_active = 1 ORDER BY display_order, name");
        
        echo "<h3>📋 Liste finale des types de vins:</h3>";
        echo "<ul>";
        foreach ($final_types as $type) {
            echo "<li><strong>{$type->name}</strong> (slug: {$type->slug})</li>";
        }
        echo "</ul>";
    }
}

// Rendre le script accessible uniquement aux administrateurs
if (!current_user_can('manage_options')) {
    wp_die('Permission refusée');
}

// Exécuter la migration
new WineTypesMigration();
