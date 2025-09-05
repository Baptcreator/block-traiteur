<?php
/**
 * Script de mise à jour forcée de la base de données
 * À exécuter UNE SEULE FOIS après avoir uploadé les nouveaux fichiers
 */

// Sécurité : vérifier qu'on est dans WordPress
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__FILE__) . '/../../../');
    require_once ABSPATH . 'wp-config.php';
}

// Définir les constantes du plugin
define('BLOCK_TRAITEUR_VERSION', '1.0.0');
define('BLOCK_TRAITEUR_PLUGIN_FILE', __FILE__);
define('BLOCK_TRAITEUR_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('BLOCK_TRAITEUR_PLUGIN_URL', plugin_dir_url(__FILE__));

echo "<h1>🔧 Mise à jour forcée Block Traiteur</h1>";

// 1. Supprimer les anciennes tables
echo "<h2>1. Suppression des anciennes tables...</h2>";
global $wpdb;

$old_tables = array(
    'block_quotes',
    'block_products', 
    'block_food_categories',
    'block_beverage_categories',
    'block_beverages',
    'block_settings'
);

foreach ($old_tables as $table) {
    $result = $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}{$table}");
    echo "- Suppression {$wpdb->prefix}{$table}: " . ($result !== false ? "✅ OK" : "❌ ERREUR") . "<br>";
}

// 2. Charger la nouvelle classe Database
echo "<h2>2. Création des nouvelles tables...</h2>";
require_once BLOCK_TRAITEUR_PLUGIN_DIR . 'includes/class-database.php';

try {
    Block_Traiteur_Database::create_tables();
    echo "✅ Nouvelles tables créées avec succès<br>";
    
    Block_Traiteur_Database::seed_default_data();
    echo "✅ Données par défaut insérées<br>";
    
} catch (Exception $e) {
    echo "❌ ERREUR: " . $e->getMessage() . "<br>";
}

// 3. Vérifier les tables créées
echo "<h2>3. Vérification des tables...</h2>";
$new_tables = array(
    'restaurant_products',
    'restaurant_categories',
    'restaurant_quotes',
    'restaurant_availability',
    'restaurant_postal_codes'
);

foreach ($new_tables as $table) {
    $exists = $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}{$table}'");
    echo "- Table {$wpdb->prefix}{$table}: " . ($exists ? "✅ EXISTE" : "❌ MANQUANTE") . "<br>";
}

// 4. Mettre à jour les options
echo "<h2>4. Mise à jour des options...</h2>";
update_option('block_traiteur_db_version', BLOCK_TRAITEUR_VERSION);
update_option('block_traiteur_updated_at', current_time('mysql'));
echo "✅ Options mises à jour<br>";

echo "<h2>🎉 MISE À JOUR TERMINÉE !</h2>";
echo "<p><strong>Vous pouvez maintenant :</strong></p>";
echo "<ul>";
echo "<li>✅ Supprimer ce fichier force-db-update.php</li>";
echo "<li>✅ Tester votre formulaire</li>";
echo "<li>✅ Vérifier les widgets Elementor</li>";
echo "</ul>";

echo "<p><a href='" . admin_url('plugins.php') . "' style='background: #0073aa; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Retour aux plugins</a></p>";
?>
