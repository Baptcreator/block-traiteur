<?php
/**
 * Script pour forcer la recréation complète de la base de données
 * 
 * À utiliser UNIQUEMENT en développement pour recréer les tables selon les nouvelles spécifications
 */

// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    require_once('../../../wp-config.php');
}

// Vérifier les permissions
if (!current_user_can('manage_options')) {
    wp_die('Accès non autorisé');
}

// Charger la classe database
require_once plugin_dir_path(__FILE__) . 'includes/class-database.php';

echo "<h1>Recréation de la base de données - Block Traiteur</h1>";
echo "<p><strong>ATTENTION:</strong> Cette opération va supprimer TOUTES les données existantes !</p>";

if (isset($_GET['force']) && $_GET['force'] === 'yes') {
    echo "<div style='background: #fff3cd; padding: 15px; border: 1px solid #ffeaa7; margin: 20px 0;'>";
    echo "<h3>🔄 Recréation en cours...</h3>";
    
    try {
        // 1. Supprimer les anciennes tables
        echo "<p>1. Suppression des anciennes tables...</p>";
        Block_Traiteur_Database::drop_tables();
        echo "<p style='color: green;'>✓ Tables supprimées</p>";
        
        // 2. Créer les nouvelles tables
        echo "<p>2. Création des nouvelles tables selon les spécifications...</p>";
        Block_Traiteur_Database::create_tables();
        echo "<p style='color: green;'>✓ Tables créées</p>";
        
        // 3. Insérer les données par défaut
        echo "<p>3. Insertion des données par défaut...</p>";
        Block_Traiteur_Database::seed_default_data();
        echo "<p style='color: green;'>✓ Données par défaut insérées</p>";
        
        echo "<div style='background: #d4edda; padding: 15px; border: 1px solid #c3e6cb; margin: 20px 0;'>";
        echo "<h3>🎉 Succès !</h3>";
        echo "<p>La base de données a été recréée avec succès selon les spécifications du cahier des charges.</p>";
        echo "</div>";
        
        // Afficher un résumé des tables créées
        global $wpdb;
        $tables = array(
            'restaurant_categories',
            'restaurant_products', 
            'restaurant_settings',
            'restaurant_quotes',
            'restaurant_availability',
            'restaurant_delivery_zones'
        );
        
        echo "<h3>📊 Résumé des tables créées :</h3>";
        echo "<ul>";
        foreach ($tables as $table) {
            $table_name = $wpdb->prefix . $table;
            $count = $wpdb->get_var("SELECT COUNT(*) FROM {$table_name}");
            echo "<li><strong>{$table_name}</strong> : {$count} enregistrements</li>";
        }
        echo "</ul>";
        
    } catch (Exception $e) {
        echo "<div style='background: #f8d7da; padding: 15px; border: 1px solid #f5c6cb; margin: 20px 0;'>";
        echo "<h3>❌ Erreur</h3>";
        echo "<p>Une erreur est survenue : " . esc_html($e->getMessage()) . "</p>";
        echo "</div>";
    }
    
} else {
    echo "<div style='background: #f8d7da; padding: 15px; border: 1px solid #f5c6cb; margin: 20px 0;'>";
    echo "<h3>⚠️ Confirmation requise</h3>";
    echo "<p>Cette opération va :</p>";
    echo "<ul>";
    echo "<li>Supprimer toutes les tables existantes</li>";
    echo "<li>Créer les nouvelles tables selon le cahier des charges</li>";
    echo "<li>Insérer les données par défaut (catégories, produits, paramètres)</li>";
    echo "</ul>";
    echo "<p><strong>Toutes les données existantes seront perdues !</strong></p>";
    echo "<a href='?force=yes' style='background: #dc3545; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Confirmer la recréation</a>";
    echo "</div>";
}

echo "<p><a href='" . admin_url('admin.php?page=block-traiteur') . "'>← Retour à l'administration</a></p>";
?>
