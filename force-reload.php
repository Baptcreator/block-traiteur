<?php
/**
 * Script pour forcer le rechargement des assets CSS/JS
 */

// Essayer de charger WordPress
$wp_load_paths = [
    '../../../wp-load.php',
    '../../../../wp-load.php',
    '../../../../../wp-load.php'
];

$wp_loaded = false;
foreach ($wp_load_paths as $path) {
    if (file_exists($path)) {
        require_once $path;
        $wp_loaded = true;
        break;
    }
}

if (!$wp_loaded) {
    die('WordPress non trouvé');
}

echo "<h1>🔄 FORCER RECHARGEMENT ASSETS</h1>";

// 1. Vider le cache Elementor
if (class_exists('\\Elementor\\Plugin')) {
    \Elementor\Plugin::$instance->files_manager->clear_cache();
    echo "✅ Cache Elementor vidé<br>";
}

// 2. Incrémenter la version des assets pour forcer le rechargement
update_option('block_traiteur_assets_version', time());
echo "✅ Version assets mise à jour<br>";

// 3. Vider les caches WordPress courants
if (function_exists('wp_cache_flush')) {
    wp_cache_flush();
    echo "✅ Cache WordPress vidé<br>";
}

// 4. Vider le cache des transients
global $wpdb;
$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_%' OR option_name LIKE '_site_transient_%'");
echo "✅ Transients vidés<br>";

// 5. Forcer la régénération des CSS
delete_option('block_traiteur_css_generated');
echo "✅ CSS forcé à se régénérer<br>";

echo "<div style='background:#d4edda;padding:15px;border:1px solid #c3e6cb;border-radius:5px;margin:20px 0;'>";
echo "<strong>✅ TERMINÉ !</strong><br>";
echo "Maintenant :<br>";
echo "1. Allez sur votre page avec le shortcode<br>";
echo "2. Faites Ctrl+F5 (rechargement forcé)<br>";
echo "3. Vérifiez si les modifications apparaissent<br>";
echo "</div>";

// Afficher les infos de debug
echo "<h2>🔍 INFORMATIONS DEBUG</h2>";
echo "Plugin actif: " . (is_plugin_active('block-traiteur/block-traiteur.php') ? 'OUI' : 'NON') . "<br>";
echo "Version assets: " . get_option('block_traiteur_assets_version', 'non définie') . "<br>";
echo "URL plugin: " . plugin_dir_url(__FILE__) . "<br>";

// Test d'inclusion du CSS
$css_file = plugin_dir_path(__FILE__) . 'public/css/form-steps.css';
if (file_exists($css_file)) {
    $css_size = filesize($css_file);
    $css_modified = date('Y-m-d H:i:s', filemtime($css_file));
    echo "CSS form-steps.css: {$css_size} octets, modifié le $css_modified<br>";
    
    // Vérifier si nos modifications sont présentes
    $css_content = file_get_contents($css_file);
    if (strpos($css_content, 'Corrections pour l\'étape 1 - Service Choice') !== false) {
        echo "✅ CSS contient nos modifications<br>";
    } else {
        echo "❌ <strong style='color:red'>CSS ne contient PAS nos modifications!</strong><br>";
    }
} else {
    echo "❌ CSS form-steps.css non trouvé<br>";
}

// Test du template
$template_file = plugin_dir_path(__FILE__) . 'templates/form-steps/step-service-choice.php';
if (file_exists($template_file)) {
    $template_modified = date('Y-m-d H:i:s', filemtime($template_file));
    echo "Template step-service-choice.php: modifié le $template_modified<br>";
    
    $template_content = file_get_contents($template_file);
    if (strpos($template_content, 'Prix supprimé selon les spécifications') !== false) {
        echo "✅ Template contient nos modifications<br>";
    } else {
        echo "❌ <strong style='color:red'>Template ne contient PAS nos modifications!</strong><br>";
    }
} else {
    echo "❌ Template step-service-choice.php non trouvé<br>";
}
?>
