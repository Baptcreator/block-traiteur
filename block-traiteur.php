<?php
/**
 * Plugin Name: Block Traiteur - Système de Devis
 * Plugin URI: https://block-strasbourg.fr
 * Description: Système de devis en ligne pour Block Street Food & Events avec formulaire multi-étapes, calcul automatique des prix et génération PDF.
 * Version: 1.0.0
 * Author: Block Street Food
 * Author URI: https://block-strasbourg.fr
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: block-traiteur
 * Domain Path: /languages
 * Requires at least: 6.0
 * Tested up to: 6.4
 * Requires PHP: 8.0
 * Network: false
 */

// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit;
}

// Définir les constantes du plugin
define('BLOCK_TRAITEUR_VERSION', '1.0.0');
define('BLOCK_TRAITEUR_PLUGIN_FILE', __FILE__);
define('BLOCK_TRAITEUR_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('BLOCK_TRAITEUR_PLUGIN_URL', plugin_dir_url(__FILE__));
define('BLOCK_TRAITEUR_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Vérifier la compatibilité PHP
if (version_compare(PHP_VERSION, '8.0', '<')) {
    add_action('admin_notices', function() {
        echo '<div class="notice notice-error"><p>';
        echo sprintf(
            __('Block Traiteur nécessite PHP 8.0 ou supérieur. Version actuelle : %s', 'block-traiteur'),
            PHP_VERSION
        );
        echo '</p></div>';
    });
    return;
}

// Vérifier la compatibilité WordPress
if (version_compare(get_bloginfo('version'), '6.0', '<')) {
    add_action('admin_notices', function() {
        echo '<div class="notice notice-error"><p>';
        echo __('Block Traiteur nécessite WordPress 6.0 ou supérieur.', 'block-traiteur');
        echo '</p></div>';
    });
    return;
}

// Utiliser une variable globale pour éviter les doublons
if (!defined('BLOCK_TRAITEUR_INITIALIZED')) {
    define('BLOCK_TRAITEUR_INITIALIZED', true);
} else {
    return; // Éviter les initialisations multiples
}

/**
 * Chargement automatique des classes (SANS Elementor)
 */
spl_autoload_register(function ($class_name) {
    if (strpos($class_name, 'Block_Traiteur') !== 0) {
        return;
    }
    
    // Exclure le widget Elementor de l'autoloader
    if ($class_name === 'Block_Traiteur_Elementor_Widget') {
        return;
    }
    
    $class_file = str_replace('_', '-', strtolower($class_name));
    $class_file = str_replace('block-traiteur-', '', $class_file);
    
    $possible_paths = array(
        BLOCK_TRAITEUR_PLUGIN_DIR . 'includes/class-' . $class_file . '.php',
        BLOCK_TRAITEUR_PLUGIN_DIR . 'admin/class-' . $class_file . '.php',
        BLOCK_TRAITEUR_PLUGIN_DIR . 'public/class-' . $class_file . '.php',
    );
    
    foreach ($possible_paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            break;
        }
    }
});

// Charger les classes d'activation/désactivation
require_once BLOCK_TRAITEUR_PLUGIN_DIR . 'includes/class-activator.php';
require_once BLOCK_TRAITEUR_PLUGIN_DIR . 'includes/class-deactivator.php';

// Hooks d'activation et désactivation
register_activation_hook(__FILE__, array('Block_Traiteur_Activator', 'activate'));
register_deactivation_hook(__FILE__, array('Block_Traiteur_Deactivator', 'deactivate'));

/**
 * Initialiser le plugin principal - VERSION COMPLÈTEMENT REFAITE
 */
function block_traiteur_init() {
    // Éviter les initialisations multiples
    static $initialized = false;
    if ($initialized) {
        return;
    }
    $initialized = true;
    
    error_log('Block Traiteur: Initialisation nouvelle architecture');
    
    // Charger la classe principale qui gère tout
    if (file_exists(BLOCK_TRAITEUR_PLUGIN_DIR . 'includes/class-block-traiteur.php')) {
        require_once BLOCK_TRAITEUR_PLUGIN_DIR . 'includes/class-block-traiteur.php';
        new Block_Traiteur();
    }
    
    error_log('Block Traiteur: Nouvelle architecture initialisée');
}


/**
 * Initialiser Elementor widget seulement si Elementor est disponible
 */
function block_traiteur_init_elementor() {
    // Vérifier si Elementor est vraiment chargé
    if (did_action('elementor/loaded') && class_exists('\Elementor\Plugin')) {
        // Charger le widget seulement maintenant
        require_once BLOCK_TRAITEUR_PLUGIN_DIR . 'public/class-elementor-widget.php';
        
        if (class_exists('Block_Traiteur_Elementor_Widget')) {
            \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new Block_Traiteur_Elementor_Widget());
        }
    }
}
add_action('elementor/widgets/widgets_registered', 'block_traiteur_init_elementor');

// Initialiser le plugin UNE SEULE FOIS avec priorité élevée
add_action('plugins_loaded', 'block_traiteur_init', 5);

// Plus besoin d'enregistrer les options individuelles - tout est géré par la nouvelle architecture

// Charger les traductions
add_action('plugins_loaded', function() {
    load_plugin_textdomain(
        'block-traiteur',
        false,
        dirname(BLOCK_TRAITEUR_PLUGIN_BASENAME) . '/languages/'
    );
});