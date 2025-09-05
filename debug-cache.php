<?php
/**
 * Script de diagnostic pour vérifier le chargement du plugin
 */

// Vérification si WordPress est chargé
if (!defined('ABSPATH')) {
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
        die('WordPress non trouvé. Placez ce fichier dans votre dossier de plugin.');
    }
}

echo "<h1>🔍 DIAGNOSTIC PLUGIN BLOCK TRAITEUR</h1>";

echo "<h2>1. VÉRIFICATION DES FICHIERS</h2>";
$files_to_check = [
    'block-traiteur.php' => 'Fichier principal',
    'public/css/form-steps.css' => 'CSS modifié',
    'public/js/form.js' => 'JavaScript modifié',
    'templates/form-steps/step-service-choice.php' => 'Template service modifié',
    'templates/form-steps/step-meal-formulas.php' => 'Template formules modifié'
];

foreach ($files_to_check as $file => $desc) {
    $path = plugin_dir_path(__FILE__) . $file;
    if (file_exists($path)) {
        $mtime = filemtime($path);
        $date = date('Y-m-d H:i:s', $mtime);
        echo "✅ <strong>$desc</strong>: Existe (modifié le $date)<br>";
        
        // Vérifier si le fichier contient nos modifications
        $content = file_get_contents($path);
        if ($file === 'templates/form-steps/step-service-choice.php') {
            if (strpos($content, 'Prix supprimé selon les spécifications') !== false) {
                echo "   ✅ Contient nos modifications<br>";
            } else {
                echo "   ❌ <strong style='color:red'>NE CONTIENT PAS nos modifications!</strong><br>";
            }
        }
    } else {
        echo "❌ <strong style='color:red'>$desc</strong>: Fichier manquant!<br>";
    }
}

echo "<h2>2. VÉRIFICATION PLUGIN WORDPRESS</h2>";
if (is_plugin_active('block-traiteur/block-traiteur.php')) {
    echo "✅ Plugin actif dans WordPress<br>";
} else {
    echo "❌ <strong style='color:red'>Plugin INACTIF dans WordPress!</strong><br>";
}

echo "<h2>3. VÉRIFICATION SHORTCODE</h2>";
global $shortcode_tags;
if (isset($shortcode_tags['block_traiteur_form'])) {
    echo "✅ Shortcode [block_traiteur_form] enregistré<br>";
} else {
    echo "❌ <strong style='color:red'>Shortcode NON enregistré!</strong><br>";
}

echo "<h2>4. VÉRIFICATION CONSTANTES</h2>";
if (defined('BLOCK_TRAITEUR_VERSION')) {
    echo "✅ BLOCK_TRAITEUR_VERSION: " . BLOCK_TRAITEUR_VERSION . "<br>";
} else {
    echo "❌ <strong style='color:red'>BLOCK_TRAITEUR_VERSION non définie!</strong><br>";
}

if (defined('BLOCK_TRAITEUR_PLUGIN_URL')) {
    echo "✅ BLOCK_TRAITEUR_PLUGIN_URL: " . BLOCK_TRAITEUR_PLUGIN_URL . "<br>";
} else {
    echo "❌ <strong style='color:red'>BLOCK_TRAITEUR_PLUGIN_URL non définie!</strong><br>";
}

echo "<h2>5. TEST CSS/JS ENQUEUE</h2>";
// Simuler l'enqueue des assets
if (class_exists('Block_Traiteur_Public')) {
    echo "✅ Classe Block_Traiteur_Public existe<br>";
} else {
    echo "❌ <strong style='color:red'>Classe Block_Traiteur_Public manquante!</strong><br>";
}

echo "<h2>6. VÉRIFICATION BASE DE DONNÉES</h2>";
global $wpdb;
$tables = [
    'block_products',
    'block_food_categories',
    'block_beverages',
    'block_beverage_categories'
];

foreach ($tables as $table) {
    $table_name = $wpdb->prefix . $table;
    $exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'");
    if ($exists) {
        $count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
        echo "✅ Table $table_name: $count enregistrements<br>";
    } else {
        echo "❌ <strong style='color:red'>Table $table_name manquante!</strong><br>";
    }
}

echo "<h2>7. ACTIONS RECOMMANDÉES</h2>";
echo "<div style='background:#f0f8ff;padding:15px;border-left:4px solid #0073aa;'>";
echo "<strong>Pour résoudre le problème :</strong><br>";
echo "1. Videz TOUS les caches (WordPress, Elementor, navigateur)<br>";
echo "2. Désactivez/Réactivez le plugin<br>";
echo "3. Vérifiez que les fichiers modifiés sont bien sur le serveur<br>";
echo "4. Testez en mode incognito<br>";
echo "5. Si ça ne marche toujours pas, contactez-moi avec ce rapport<br>";
echo "</div>";

echo "<h2>8. INFORMATIONS SYSTÈME</h2>";
echo "WordPress: " . get_bloginfo('version') . "<br>";
echo "PHP: " . PHP_VERSION . "<br>";
echo "Thème actif: " . get_template() . "<br>";
if (class_exists('\\Elementor\\Plugin')) {
    echo "Elementor: Actif<br>";
} else {
    echo "Elementor: Non détecté<br>";
}

// Vérifier les erreurs PHP récentes
echo "<h2>9. DERNIÈRES ERREURS PHP</h2>";
if (function_exists('error_get_last')) {
    $last_error = error_get_last();
    if ($last_error) {
        echo "<pre style='background:#ffe6e6;padding:10px;'>";
        print_r($last_error);
        echo "</pre>";
    } else {
        echo "✅ Aucune erreur PHP récente<br>";
    }
}
?>
