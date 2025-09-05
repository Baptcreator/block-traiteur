<?php
/**
 * Classe d'activation du plugin Block Traiteur
 * 
 * @package Block_Traiteur
 * @since 1.0.0
 */

// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit;
}

class Block_Traiteur_Activator {
    
    /**
     * Méthode d'activation du plugin
     * 
     * @since 1.0.0
     */
    public static function activate() {
        // Vérifier les prérequis
        self::check_requirements();
        
        // Créer les tables de base de données
        self::create_database_tables();
        
        // Créer les options par défaut
        self::create_default_options();
        
        // Créer les rôles et capacités
        self::create_capabilities();
        
        // Vider le cache de réécriture des URLs
        flush_rewrite_rules();
        
        // Log de l'activation
        error_log('Block Traiteur: Plugin activé avec succès');
    }
    
    /**
     * Vérifier les prérequis système
     */
    private static function check_requirements() {
        $errors = array();
        
        // Vérifier version PHP
        if (version_compare(PHP_VERSION, '8.0', '<')) {
            $errors[] = sprintf(
                __('Block Traiteur nécessite PHP 8.0 minimum. Version actuelle : %s', 'block-traiteur'),
                PHP_VERSION
            );
        }
        
        // Vérifier version WordPress
        if (version_compare(get_bloginfo('version'), '6.0', '<')) {
            $errors[] = sprintf(
                __('Block Traiteur nécessite WordPress 6.0 minimum. Version actuelle : %s', 'block-traiteur'),
                get_bloginfo('version')
            );
        }
        
        // Vérifier extensions PHP requises
        $required_extensions = array(
            'mysqli' => 'MySQL',
            'json' => 'JSON',
            'curl' => 'cURL',
            'gd' => 'GD (manipulation d\'images)',
            'zip' => 'ZIP (sauvegardes)'
        );
        
        foreach ($required_extensions as $ext => $name) {
            if (!extension_loaded($ext)) {
                $errors[] = sprintf(
                    __('Extension PHP manquante : %s (%s)', 'block-traiteur'),
                    $name,
                    $ext
                );
            }
        }
        
        // Si erreurs détectées, stopper l'activation
        if (!empty($errors)) {
            deactivate_plugins(plugin_basename(BLOCK_TRAITEUR_PLUGIN_FILE));
            wp_die(
                '<h1>' . __('Activation impossible', 'block-traiteur') . '</h1>' .
                '<p>' . __('Block Traiteur ne peut pas être activé pour les raisons suivantes :', 'block-traiteur') . '</p>' .
                '<ul><li>' . implode('</li><li>', $errors) . '</li></ul>' .
                '<p><a href="' . admin_url('plugins.php') . '">' . __('Retour aux plugins', 'block-traiteur') . '</a></p>'
            );
        }
    }
    
    /**
     * Créer les tables de base de données SELON LES SPÉCIFICATIONS EXACTES DU CAHIER DES CHARGES
     */
    private static function create_database_tables() {
        // Charger la nouvelle classe Database
        require_once BLOCK_TRAITEUR_PLUGIN_DIR . 'includes/class-database.php';
        
        try {
            // Supprimer les anciennes tables si elles existent
            self::drop_old_tables();
            
            // Créer les nouvelles tables selon les spécifications exactes
            Block_Traiteur_Database::create_tables();
            Block_Traiteur_Database::seed_default_data();
            
            error_log('Block Traiteur: NOUVELLES tables créées selon spécifications exactes du cahier des charges');
            return;
            
        } catch (Exception $e) {
            error_log('Block Traiteur: Erreur création tables - ' . $e->getMessage());
            wp_die('Erreur lors de la création de la base de données : ' . $e->getMessage());
        }
    }
    
    /**
     * Créer les options par défaut - NOUVELLE ARCHITECTURE
     */
    private static function create_default_options() {
        // Juste les options essentielles - le reste est géré par la nouvelle architecture
        $default_options = array(
            'block_traiteur_version' => BLOCK_TRAITEUR_VERSION,
            'block_traiteur_activated_at' => current_time('mysql')
        );
        
        foreach ($default_options as $option_name => $option_value) {
            if (get_option($option_name) === false) {
                add_option($option_name, $option_value);
            }
        }
        
        error_log('Block Traiteur: Options de base créées - nouvelle architecture');
    }
    
    /**
     * Créer les capacités utilisateur
     */
    private static function create_capabilities() {
        $role = get_role('administrator');
        
        if ($role) {
            $role->add_cap('manage_block_traiteur');
            $role->add_cap('edit_block_quotes');
            $role->add_cap('delete_block_quotes');
            $role->add_cap('view_block_reports');
        }
        
        error_log('Block Traiteur: Capacités utilisateur créées');
    }
    
    /**
     * Supprimer les anciennes tables pour faire place aux nouvelles
     */
    private static function drop_old_tables() {
        global $wpdb;
        
        // Anciennes tables à supprimer
        $old_tables = array(
            'block_quotes',
            'block_products', 
            'block_food_categories',
            'block_beverage_categories',
            'block_beverages',
            'block_settings'
        );
        
        // Désactiver les vérifications de clés étrangères
        $wpdb->query("SET FOREIGN_KEY_CHECKS = 0");
        
        foreach ($old_tables as $table) {
            $result = $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}{$table}");
            if ($result !== false) {
                error_log("Block Traiteur: Ancienne table {$table} supprimée");
            }
        }
        
        // Réactiver les vérifications
        $wpdb->query("SET FOREIGN_KEY_CHECKS = 1");
        
        error_log('Block Traiteur: Toutes les anciennes tables supprimées');
    }
}