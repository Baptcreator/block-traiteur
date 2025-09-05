<?php
/**
 * Classe principale du plugin Block Traiteur
 * Architecture complète selon cahier des charges
 *
 * @package Block_Traiteur
 * @subpackage Includes
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Classe Block_Traiteur - Cœur du plugin
 * 
 * Gère l'initialisation et la coordination de tous les composants du plugin
 */
class Block_Traiteur {
    
    /**
     * @var string Nom du plugin
     */
    protected $plugin_name;
    
    /**
     * @var string Version du plugin
     */
    protected $version;
    
    /**
     * @var Block_Traiteur_Settings Instance des settings
     */
    protected $settings;
    
    /**
     * @var Block_Traiteur_Calculator Instance du calculateur
     */
    protected $calculator;
    
    /**
     * Constructeur
     */
    public function __construct() {
        $this->version = BLOCK_TRAITEUR_VERSION;
        $this->plugin_name = 'block-traiteur';
        
        $this->load_dependencies();
        $this->set_locale();
        $this->init_hooks();
        
        error_log("Block Traiteur: Classe principale initialisée");
    }
    
    /**
     * Charger les dépendances
     */
    private function load_dependencies() {
        // Charger les classes utilitaires
        $this->load_utilities();
        
        // Initialiser les composants principaux
        $this->init_components();
    }
    
    /**
     * Charger les classes utilitaires
     */
    private function load_utilities() {
        // Classe Settings pour gérer les paramètres
        if (!class_exists('Block_Traiteur_Settings')) {
            require_once BLOCK_TRAITEUR_PLUGIN_DIR . 'includes/class-settings.php';
        }
        
        // Classe Calculator pour les calculs de prix
        if (!class_exists('Block_Traiteur_Calculator')) {
            require_once BLOCK_TRAITEUR_PLUGIN_DIR . 'includes/class-calculator.php';
        }
        
        // Classe Validator pour les validations
        if (!class_exists('Block_Traiteur_Validator')) {
            require_once BLOCK_TRAITEUR_PLUGIN_DIR . 'includes/class-validator.php';
        }
    }
    
    /**
     * Initialiser les composants principaux
     */
    private function init_components() {
        // Instance des settings (singleton)
        $this->settings = Block_Traiteur_Settings::get_instance();
        
        // Instance du calculateur
        $this->calculator = new Block_Traiteur_Calculator();
    }
    
    /**
     * Configurer la localisation
     */
    private function set_locale() {
        add_action('plugins_loaded', function() {
            load_plugin_textdomain(
                'block-traiteur',
                false,
                dirname(plugin_basename(BLOCK_TRAITEUR_PLUGIN_FILE)) . '/languages/'
            );
        });
    }
    
    /**
     * Initialiser les hooks WordPress
     */
    private function init_hooks() {
        // Hook d'initialisation
        add_action('init', array($this, 'init'));
        
        // Hook pour vérifier la base de données
        add_action('admin_init', array($this, 'check_database'));
        
        // Enqueue des scripts et styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_public_assets'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        
        // AJAX hooks pour le frontend
        add_action('wp_ajax_block_traiteur_calculate_price', array($this, 'ajax_calculate_price'));
        add_action('wp_ajax_nopriv_block_traiteur_calculate_price', array($this, 'ajax_calculate_price'));
        add_action('wp_ajax_block_traiteur_check_availability', array($this, 'ajax_check_availability'));
        add_action('wp_ajax_nopriv_block_traiteur_check_availability', array($this, 'ajax_check_availability'));
        add_action('wp_ajax_block_traiteur_submit_quote', array($this, 'ajax_submit_quote'));
        add_action('wp_ajax_nopriv_block_traiteur_submit_quote', array($this, 'ajax_submit_quote'));
    }
    
    /**
     * Initialisation du plugin
     */
    public function init() {
        // Vérifier si la base de données doit être mise à jour
        if (Block_Traiteur_Database::needs_update()) {
            $this->update_database();
        }
    }
    
    /**
     * Vérifier l'état de la base de données
     */
    public function check_database() {
        if (current_user_can('manage_options')) {
            if (Block_Traiteur_Database::needs_update()) {
                add_action('admin_notices', function() {
                    echo '<div class="notice notice-warning is-dismissible">';
                    echo '<p><strong>Block Traiteur:</strong> La base de données doit être mise à jour.</p>';
                    echo '<p><a href="' . admin_url('admin.php?page=block-traiteur-settings&action=update_db') . '" class="button button-primary">Mettre à jour maintenant</a></p>';
                    echo '</div>';
                });
            }
        }
    }
    
    /**
     * Mettre à jour la base de données
     */
    private function update_database() {
        try {
            Block_Traiteur_Database::create_tables();
            Block_Traiteur_Database::seed_default_data();
            error_log("Block Traiteur: Base de données mise à jour avec succès");
        } catch (Exception $e) {
            error_log("Block Traiteur: Erreur mise à jour BDD - " . $e->getMessage());
        }
    }
    
    /**
     * Enqueue des assets publics
     */
    public function enqueue_public_assets() {
        // CSS public
        wp_enqueue_style(
            'block-traiteur-public',
            BLOCK_TRAITEUR_PLUGIN_URL . 'public/css/public.css',
            array(),
            $this->version
        );
        
        // JS public avec données localisées
        wp_enqueue_script(
            'block-traiteur-public',
            BLOCK_TRAITEUR_PLUGIN_URL . 'public/js/public.js',
            array('jquery'),
            $this->version,
            true
        );
        
        // Localisation pour AJAX
        wp_localize_script('block-traiteur-public', 'blockTraiteur', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('block_traiteur_nonce'),
            'settings' => array(
                'restaurantMinGuests' => $this->settings->get('restaurant_min_guests', 10),
                'restaurantMaxGuests' => $this->settings->get('restaurant_max_guests', 30),
                'remorqueMinGuests' => $this->settings->get('remorque_min_guests', 20),
                'remorqueMaxGuests' => $this->settings->get('remorque_max_guests', 100),
                'restaurantBasePrice' => $this->settings->get('restaurant_base_price', 300),
                'remorqueBasePrice' => $this->settings->get('remorque_base_price', 350),
                'hourlySuplement' => $this->settings->get('hourly_supplement', 50)
            ),
            'texts' => array(
                'errorDateUnavailable' => $this->settings->get('error_date_unavailable', 'Cette date n\'est pas disponible'),
                'errorGuestsMin' => $this->settings->get('error_guests_min', 'Nombre minimum de convives : {min}'),
                'errorGuestsMax' => $this->settings->get('error_guests_max', 'Nombre maximum de convives : {max}')
            )
        ));
    }
    
    /**
     * Enqueue des assets admin
     */
    public function enqueue_admin_assets($hook) {
        // Seulement sur les pages du plugin
        if (strpos($hook, 'block-traiteur') === false) {
            return;
        }
        
        wp_enqueue_style(
            'block-traiteur-admin',
            BLOCK_TRAITEUR_PLUGIN_URL . 'admin/css/admin.css',
            array(),
            $this->version
        );
        
        wp_enqueue_script(
            'block-traiteur-admin',
            BLOCK_TRAITEUR_PLUGIN_URL . 'admin/js/admin.js',
            array('jquery'),
            $this->version,
            true
        );
    }
    
    /**
     * AJAX: Calculer le prix
     */
    public function ajax_calculate_price() {
        check_ajax_referer('block_traiteur_nonce', 'nonce');
        
        $service_type = sanitize_text_field($_POST['service_type'] ?? '');
        $guest_count = intval($_POST['guest_count'] ?? 0);
        $duration = intval($_POST['duration'] ?? 2);
        $postal_code = sanitize_text_field($_POST['postal_code'] ?? '');
        $products = json_decode(stripslashes($_POST['products'] ?? '[]'), true);
        
        try {
            $calculation = $this->calculator->calculate_total_price(
                $service_type,
                $guest_count,
                $duration,
                $postal_code,
                $products
            );
            
            wp_send_json_success($calculation);
        } catch (Exception $e) {
            wp_send_json_error(array('message' => $e->getMessage()));
        }
    }
    
    /**
     * AJAX: Vérifier disponibilité
     */
    public function ajax_check_availability() {
        check_ajax_referer('block_traiteur_nonce', 'nonce');
        
        $date = sanitize_text_field($_POST['date'] ?? '');
        $service_type = sanitize_text_field($_POST['service_type'] ?? '');
        
        global $wpdb;
        $table = $wpdb->prefix . 'restaurant_availability';
        
        $availability = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table} WHERE date = %s AND (service_type = %s OR service_type = 'both')",
            $date,
            $service_type
        ));
        
        $is_available = !$availability || $availability->is_available;
        $reason = $availability->blocked_reason ?? '';
        
        wp_send_json_success(array(
            'available' => $is_available,
            'reason' => $reason
        ));
    }
    
    /**
     * AJAX: Soumettre un devis
     */
    public function ajax_submit_quote() {
        check_ajax_referer('block_traiteur_nonce', 'nonce');
        
        // Récupérer et valider les données
        $quote_data = array(
            'service_type' => sanitize_text_field($_POST['service_type'] ?? ''),
            'event_date' => sanitize_text_field($_POST['event_date'] ?? ''),
            'event_duration' => intval($_POST['event_duration'] ?? 2),
            'guest_count' => intval($_POST['guest_count'] ?? 0),
            'postal_code' => sanitize_text_field($_POST['postal_code'] ?? ''),
            'customer_data' => json_decode(stripslashes($_POST['customer_data'] ?? '{}'), true),
            'selected_products' => json_decode(stripslashes($_POST['selected_products'] ?? '[]'), true)
        );
        
        try {
            // Valider les données
            $validator = new Block_Traiteur_Validator();
            $validation = $validator->validate_quote($quote_data);
            
            if (!$validation['valid']) {
                wp_send_json_error(array('message' => 'Données invalides', 'errors' => $validation['errors']));
                return;
            }
            
            // Calculer le prix
            $calculation = $this->calculator->calculate_total_price(
                $quote_data['service_type'],
                $quote_data['guest_count'],
                $quote_data['event_duration'],
                $quote_data['postal_code'],
                $quote_data['selected_products']
            );
            
            // Générer un numéro de devis unique
            $quote_number = 'BT-' . date('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            
            // Sauvegarder en base
            global $wpdb;
            $table = $wpdb->prefix . 'restaurant_quotes';
            
            $result = $wpdb->insert(
                $table,
                array(
                    'quote_number' => $quote_number,
                    'service_type' => $quote_data['service_type'],
                    'event_date' => $quote_data['event_date'],
                    'event_duration' => $quote_data['event_duration'],
                    'guest_count' => $quote_data['guest_count'],
                    'postal_code' => $quote_data['postal_code'],
                    'customer_data' => json_encode($quote_data['customer_data']),
                    'selected_products' => json_encode($quote_data['selected_products']),
                    'price_breakdown' => json_encode($calculation),
                    'base_price' => $calculation['base_price'],
                    'supplements_total' => $calculation['supplements_total'],
                    'products_total' => $calculation['products_total'],
                    'total_price' => $calculation['total_price'],
                    'status' => 'draft'
                ),
                array('%s', '%s', '%s', '%d', '%d', '%s', '%s', '%s', '%s', '%f', '%f', '%f', '%f', '%s')
            );
            
            if ($result === false) {
                wp_send_json_error(array('message' => 'Erreur lors de la sauvegarde'));
                return;
            }
            
            wp_send_json_success(array(
                'quote_number' => $quote_number,
                'quote_id' => $wpdb->insert_id,
                'calculation' => $calculation,
                'message' => 'Devis créé avec succès'
            ));
            
        } catch (Exception $e) {
            wp_send_json_error(array('message' => $e->getMessage()));
        }
    }
    
    /**
     * Obtenir la version du plugin
     */
    public function get_version() {
        return $this->version;
    }
    
    /**
     * Obtenir le nom du plugin
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }
}