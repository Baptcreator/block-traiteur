<?php
/**
 * Script pour injecter le CSS directement dans le HTML (solution temporaire)
 */

// Charger WordPress
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

// Ajouter le CSS directement dans le head
add_action('wp_head', function() {
    $css_file = plugin_dir_path(__FILE__) . 'public/css/form-steps.css';
    if (file_exists($css_file)) {
        echo "<style id='block-traiteur-force-css'>\n";
        echo "/* CSS FORCÉ - Block Traiteur */\n";
        echo file_get_contents($css_file);
        echo "\n</style>\n";
    }
}, 999);

echo "<h1>✅ CSS FORCÉ ACTIVÉ</h1>";
echo "<p>Le CSS sera maintenant injecté directement dans toutes les pages.</p>";
echo "<p><strong>Testez maintenant votre page avec le shortcode !</strong></p>";
echo "<p><em>Note: Supprimez ce fichier une fois que le cache fonctionne normalement.</em></p>";
?>
