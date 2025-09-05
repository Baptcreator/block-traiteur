<?php
/**
 * Classe de gestion des paramètres pour Block Traiteur
 * Singleton pour accès global aux settings de la base de données
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
 * Classe Block_Traiteur_Settings
 * 
 * Gère tous les paramètres configurables du plugin depuis la table restaurant_settings
 */
class Block_Traiteur_Settings {
    
    /**
     * @var Block_Traiteur_Settings Instance unique (singleton)
     */
    private static $instance = null;
    
    /**
     * @var array Cache des settings pour éviter les requêtes répétées
     */
    private $cache = array();
    
    /**
     * @var bool Indique si le cache a été chargé
     */
    private $cache_loaded = false;
    
    /**
     * Constructeur privé pour le singleton
     */
    private function __construct() {
        // Constructeur privé
    }
    
    /**
     * Obtenir l'instance unique (singleton)
     */
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Charger tous les settings en cache
     */
    private function load_cache() {
        if ($this->cache_loaded) {
            return;
        }
        
        global $wpdb;
        $table = $wpdb->prefix . 'restaurant_settings';
        
        // Vérifier que la table existe
        if ($wpdb->get_var("SHOW TABLES LIKE '{$table}'") !== $table) {
            error_log("Block Traiteur Settings: Table {$table} n'existe pas");
            $this->cache_loaded = true;
            return;
        }
        
        $settings = $wpdb->get_results(
            "SELECT setting_key, setting_value, setting_type FROM {$table} WHERE is_active = 1",
            ARRAY_A
        );
        
        foreach ($settings as $setting) {
            $value = $setting['setting_value'];
            
            // Convertir selon le type
            switch ($setting['setting_type']) {
                case 'number':
                    $value = is_numeric($value) ? (float)$value : 0;
                    break;
                case 'boolean':
                    $value = in_array(strtolower($value), array('1', 'true', 'yes', 'on'));
                    break;
                case 'json':
                    $value = json_decode($value, true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        $value = array();
                    }
                    break;
                case 'html':
                case 'text':
                default:
                    // Garder comme string
                    break;
            }
            
            $this->cache[$setting['setting_key']] = $value;
        }
        
        $this->cache_loaded = true;
        error_log("Block Traiteur Settings: " . count($settings) . " paramètres chargés en cache");
    }
    
    /**
     * Obtenir un paramètre
     * 
     * @param string $key Clé du paramètre
     * @param mixed $default Valeur par défaut si non trouvé
     * @return mixed Valeur du paramètre
     */
    public function get($key, $default = null) {
        $this->load_cache();
        
        if (isset($this->cache[$key])) {
            return $this->cache[$key];
        }
        
        return $default;
    }
    
    /**
     * Définir un paramètre
     * 
     * @param string $key Clé du paramètre
     * @param mixed $value Valeur à sauvegarder
     * @param string $type Type de données ('text', 'number', 'boolean', 'json', 'html')
     * @param string $group Groupe du paramètre
     * @param string $description Description du paramètre
     * @return bool Succès de l'opération
     */
    public function set($key, $value, $type = 'text', $group = 'general', $description = '') {
        global $wpdb;
        $table = $wpdb->prefix . 'restaurant_settings';
        
        // Préparer la valeur selon le type
        $db_value = $value;
        switch ($type) {
            case 'boolean':
                $db_value = $value ? '1' : '0';
                break;
            case 'json':
                $db_value = json_encode($value);
                break;
            case 'number':
                $db_value = (string)$value;
                break;
            default:
                $db_value = (string)$value;
                break;
        }
        
        // Insérer ou mettre à jour
        $result = $wpdb->replace(
            $table,
            array(
                'setting_key' => $key,
                'setting_value' => $db_value,
                'setting_type' => $type,
                'setting_group' => $group,
                'description' => $description,
                'is_active' => 1
            ),
            array('%s', '%s', '%s', '%s', '%s', '%d')
        );
        
        if ($result !== false) {
            // Mettre à jour le cache
            $this->cache[$key] = $value;
            return true;
        }
        
        return false;
    }
    
    /**
     * Obtenir tous les paramètres d'un groupe
     * 
     * @param string $group Nom du groupe
     * @return array Paramètres du groupe
     */
    public function get_group($group) {
        global $wpdb;
        $table = $wpdb->prefix . 'restaurant_settings';
        
        $settings = $wpdb->get_results($wpdb->prepare(
            "SELECT setting_key, setting_value, setting_type FROM {$table} WHERE setting_group = %s AND is_active = 1",
            $group
        ), ARRAY_A);
        
        $result = array();
        foreach ($settings as $setting) {
            $value = $setting['setting_value'];
            
            // Convertir selon le type
            switch ($setting['setting_type']) {
                case 'number':
                    $value = is_numeric($value) ? (float)$value : 0;
                    break;
                case 'boolean':
                    $value = in_array(strtolower($value), array('1', 'true', 'yes', 'on'));
                    break;
                case 'json':
                    $value = json_decode($value, true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        $value = array();
                    }
                    break;
            }
            
            $result[$setting['setting_key']] = $value;
        }
        
        return $result;
    }
    
    /**
     * Supprimer un paramètre
     * 
     * @param string $key Clé du paramètre
     * @return bool Succès de l'opération
     */
    public function delete($key) {
        global $wpdb;
        $table = $wpdb->prefix . 'restaurant_settings';
        
        $result = $wpdb->delete(
            $table,
            array('setting_key' => $key),
            array('%s')
        );
        
        if ($result !== false) {
            unset($this->cache[$key]);
            return true;
        }
        
        return false;
    }
    
    /**
     * Vider le cache
     */
    public function clear_cache() {
        $this->cache = array();
        $this->cache_loaded = false;
    }
    
    /**
     * Obtenir les paramètres de tarification
     * 
     * @return array Paramètres de prix et contraintes
     */
    public function get_pricing_settings() {
        return array(
            'restaurant_base_price' => $this->get('restaurant_base_price', 300),
            'remorque_base_price' => $this->get('remorque_base_price', 350),
            'restaurant_included_hours' => $this->get('restaurant_included_hours', 2),
            'remorque_included_hours' => $this->get('remorque_included_hours', 2),
            'hourly_supplement' => $this->get('hourly_supplement', 50),
            'remorque_50_guests_supplement' => $this->get('remorque_50_guests_supplement', 150),
            'restaurant_min_guests' => $this->get('restaurant_min_guests', 10),
            'restaurant_max_guests' => $this->get('restaurant_max_guests', 30),
            'remorque_min_guests' => $this->get('remorque_min_guests', 20),
            'remorque_max_guests' => $this->get('remorque_max_guests', 100),
            'restaurant_max_hours' => $this->get('restaurant_max_hours', 4),
            'remorque_max_hours' => $this->get('remorque_max_hours', 5)
        );
    }
    
    /**
     * Obtenir les textes de l'interface
     * 
     * @return array Textes configurables
     */
    public function get_interface_texts() {
        return array(
            // Page d'accueil
            'homepage_restaurant_title' => $this->get('homepage_restaurant_title', 'LE RESTAURANT'),
            'homepage_restaurant_description' => $this->get('homepage_restaurant_description', 'Découvrez notre restaurant unique'),
            'homepage_traiteur_title' => $this->get('homepage_traiteur_title', 'LE TRAITEUR ÉVÉNEMENTIEL'),
            'homepage_button_privatiser' => $this->get('homepage_button_privatiser', 'Privatiser Block'),
            
            // Page traiteur
            'traiteur_restaurant_title' => $this->get('traiteur_restaurant_title', 'Privatisation du restaurant'),
            'traiteur_restaurant_subtitle' => $this->get('traiteur_restaurant_subtitle', 'De 10 à 30 personnes'),
            'traiteur_restaurant_description' => $this->get('traiteur_restaurant_description', 'Privatisez notre restaurant'),
            'traiteur_remorque_title' => $this->get('traiteur_remorque_title', 'Privatisation de la remorque Block'),
            'traiteur_remorque_subtitle' => $this->get('traiteur_remorque_subtitle', 'À partir de 20 personnes'),
            'traiteur_remorque_description' => $this->get('traiteur_remorque_description', 'Notre remorque mobile'),
            
            // Messages d'erreur
            'error_date_unavailable' => $this->get('error_date_unavailable', 'Cette date n\'est pas disponible'),
            'error_guests_min' => $this->get('error_guests_min', 'Nombre minimum de convives : {min}'),
            'error_guests_max' => $this->get('error_guests_max', 'Nombre maximum de convives : {max}'),
            'error_duration_max' => $this->get('error_duration_max', 'Durée maximum : {max} heures'),
            'error_selection_required' => $this->get('error_selection_required', 'Sélection obligatoire')
        );
    }
    
    /**
     * Obtenir les paramètres des forfaits
     * 
     * @return array Descriptions et inclusions des forfaits
     */
    public function get_package_settings() {
        return array(
            'restaurant_package_description' => $this->get('restaurant_package_description', '<h3>Forfait Restaurant - 300€</h3><p>Privatisation complète du restaurant pour 2 heures incluses.</p>'),
            'restaurant_package_includes' => $this->get('restaurant_package_includes', array('Privatisation complète', 'Service 2 heures', 'Équipement son', 'Décoration de base')),
            'remorque_package_description' => $this->get('remorque_package_description', '<h3>Forfait Remorque - 350€</h3><p>Remorque mobile avec équipement complet pour 2 heures.</p>'),
            'remorque_package_includes' => $this->get('remorque_package_includes', array('Remorque équipée', 'Livraison/retour', 'Service 2 heures', 'Équipement complet'))
        );
    }
    
    /**
     * Obtenir les paramètres d'emails
     * 
     * @return array Templates et paramètres d'emails
     */
    public function get_email_settings() {
        return array(
            'email_quote_subject' => $this->get('email_quote_subject', 'Votre devis privatisation Block'),
            'email_quote_header_html' => $this->get('email_quote_header_html', '<div style="text-align:center;"><h1>Block Street Food & Events</h1></div>'),
            'email_quote_body_html' => $this->get('email_quote_body_html', '<p>Madame, Monsieur,</p><p>Veuillez trouver ci-joint votre devis personnalisé.</p>'),
            'email_quote_footer_html' => $this->get('email_quote_footer_html', '<hr><p><small>Block Street Food & Events - SIRET: 123456789</small></p>')
        );
    }
    
    /**
     * Remplacer les variables dans un texte
     * 
     * @param string $text Texte avec variables {variable}
     * @param array $variables Variables à remplacer
     * @return string Texte avec variables remplacées
     */
    public function replace_variables($text, $variables = array()) {
        foreach ($variables as $key => $value) {
            $text = str_replace('{' . $key . '}', $value, $text);
        }
        return $text;
    }
    
    /**
     * Obtenir une liste de toutes les clés de paramètres disponibles
     * 
     * @return array Liste des clés organisées par groupe
     */
    public function get_available_settings() {
        global $wpdb;
        $table = $wpdb->prefix . 'restaurant_settings';
        
        $settings = $wpdb->get_results(
            "SELECT setting_key, setting_group, description, setting_type FROM {$table} WHERE is_active = 1 ORDER BY setting_group, setting_key",
            ARRAY_A
        );
        
        $grouped = array();
        foreach ($settings as $setting) {
            $group = $setting['setting_group'] ?: 'general';
            if (!isset($grouped[$group])) {
                $grouped[$group] = array();
            }
            $grouped[$group][] = array(
                'key' => $setting['setting_key'],
                'description' => $setting['description'],
                'type' => $setting['setting_type']
            );
        }
        
        return $grouped;
    }
}
