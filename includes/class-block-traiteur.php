<?php
/**
 * Classe principale du plugin Block Traiteur
 */

if (!defined('ABSPATH')) {
    exit;
}

class Block_Traiteur {
    
    protected $plugin_name;
    protected $version;
    
    public function __construct() {
        $this->version = BLOCK_TRAITEUR_VERSION;
        $this->plugin_name = 'block-traiteur';
        
        $this->set_locale();
    }
    
    private function set_locale() {
        add_action('plugins_loaded', function() {
            load_plugin_textdomain(
                'block-traiteur',
                false,
                dirname(plugin_basename(BLOCK_TRAITEUR_PLUGIN_FILE)) . '/languages/'
            );
        });
    }
    
    public function run() {
        // Le plugin est déjà en cours d'exécution
    }
}