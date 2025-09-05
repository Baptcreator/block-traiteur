<?php
/**
 * Classe publique du plugin
 */

if (!defined('ABSPATH')) {
    exit;
}

class Block_Traiteur_Public {
    
    /**
     * Constructeur
     */
    public function __construct() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_ajax_block_traiteur_get_products', array($this, 'ajax_get_products'));
        add_action('wp_ajax_nopriv_block_traiteur_get_products', array($this, 'ajax_get_products'));
        add_action('wp_ajax_block_traiteur_get_beverages', array($this, 'ajax_get_beverages'));
        add_action('wp_ajax_nopriv_block_traiteur_get_beverages', array($this, 'ajax_get_beverages'));
        add_action('wp_ajax_block_traiteur_quick_estimate', array($this, 'ajax_quick_estimate'));
        add_action('wp_ajax_nopriv_block_traiteur_quick_estimate', array($this, 'ajax_quick_estimate'));
    }
    
    /**
     * Enqueue des scripts publics
     */
    public function enqueue_scripts() {
        // Uniquement sur les pages avec le formulaire
        if (!$this->should_load_scripts()) {
            return;
        }
        
        // CSS principal
        wp_enqueue_style(
            'block-traiteur-public',
            BLOCK_TRAITEUR_PLUGIN_URL . 'public/css/public.css',
            array(),
            BLOCK_TRAITEUR_VERSION
        );
        
        // CSS spécifique formulaire
        wp_enqueue_style(
            'block-traiteur-form',
            BLOCK_TRAITEUR_PLUGIN_URL . 'public/css/form-steps.css',
            array('block-traiteur-public'),
            BLOCK_TRAITEUR_VERSION
        );
        
        // JavaScript principal
        wp_enqueue_script(
            'block-traiteur-form',
            BLOCK_TRAITEUR_PLUGIN_URL . 'public/js/form-handler.js',
            array('jquery'),
            BLOCK_TRAITEUR_VERSION,
            true
        );
        
        // Calculateur de prix
        wp_enqueue_script(
            'block-traiteur-calculator',
            BLOCK_TRAITEUR_PLUGIN_URL . 'public/js/price-calculator.js',
            array('block-traiteur-form'),
            BLOCK_TRAITEUR_VERSION,
            true
        );
        
        // Localisation JavaScript
        wp_localize_script('block-traiteur-form', 'blockTraiteurAjax', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('block_traiteur_ajax'),
            'settings' => array(
                'restaurant_base_price' => get_option('block_traiteur_restaurant_base_price', 300),
                'remorque_base_price' => get_option('block_traiteur_remorque_base_price', 350),
                'restaurant_min_duration' => 2,
                'restaurant_max_duration' => 4,
                'remorque_min_duration' => 2,
                'remorque_max_duration' => 5,
                'hour_supplement' => 50,
                'remorque_guest_supplement_threshold' => 50,
                'remorque_guest_supplement' => 150
            ),
            'strings' => array(
                'loading' => __('Chargement...', 'block-traiteur'),
                'error' => __('Une erreur est survenue', 'block-traiteur'),
                'success' => __('Demande envoyée avec succès', 'block-traiteur'),
                'validationError' => __('Veuillez corriger les erreurs', 'block-traiteur'),
                'confirmSubmit' => __('Confirmer l\'envoi de votre demande de devis ?', 'block-traiteur'),
                'dateUnavailable' => __('Cette date n\'est pas disponible', 'block-traiteur'),
                'postalCodeError' => __('Code postal invalide', 'block-traiteur'),
                'minQuantity' => __('Quantité minimum', 'block-traiteur'),
                'maxQuantity' => __('Quantité maximum', 'block-traiteur')
            )
        ));
    }
    
    /**
     * Vérifier si les scripts doivent être chargés
     */
    private function should_load_scripts() {
        global $post;
        
        // Charger sur les pages avec shortcode
        if ($post && has_shortcode($post->post_content, 'block_quote_form')) {
            return true;
        }
        
        // Charger si Elementor widget présent
        if (class_exists('\Elementor\Plugin') && \Elementor\Plugin::$instance->preview->is_preview_mode()) {
            return true;
        }
        
        return false;
    }
    
    /**
     * AJAX - Obtenir les produits par service
     */
    public function ajax_get_products() {
        check_ajax_referer('block_traiteur_ajax', 'nonce');
        
        $service_type = sanitize_text_field($_POST['serviceType'] ?? 'both');
        $category = sanitize_text_field($_POST['category'] ?? '');
        
        $products = Block_Traiteur_Cache::get_products($service_type);
        
        // Filtrer par catégorie si spécifiée
        if ($category) {
            $products = array_filter($products, function($product) use ($category) {
                return $product->category_type === $category;
            });
        }
        
        // Grouper par catégorie
        $grouped_products = array();
        foreach ($products as $product) {
            $grouped_products[$product->category_type][] = array(
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'price' => floatval($product->price),
                'unit' => $product->unit,
                'min_quantity' => intval($product->min_quantity),
                'max_quantity' => $product->max_quantity ? intval($product->max_quantity) : null,
                'allergens' => $product->allergens,
                'image_url' => $product->image_url,
                'category_name' => $product->category_name,
                'category_type' => $product->category_type
            );
        }
        
        wp_send_json_success($grouped_products);
    }
    
    /**
     * AJAX - Obtenir les boissons par service
     */
    public function ajax_get_beverages() {
        check_ajax_referer('block_traiteur_ajax', 'nonce');
        
        $service_type = sanitize_text_field($_POST['serviceType'] ?? 'both');
        $category = sanitize_text_field($_POST['category'] ?? '');
        
        $beverages = Block_Traiteur_Cache::get_beverages($service_type);
        
        // Filtrer par catégorie si spécifiée
        if ($category) {
            $beverages = array_filter($beverages, function($beverage) use ($category) {
                return $beverage->category_type === $category;
            });
        }
        
        // Grouper par catégorie
        $grouped_beverages = array();
        foreach ($beverages as $beverage) {
            $grouped_beverages[$beverage->category_type][] = array(
                'id' => $beverage->id,
                'name' => $beverage->name,
                'description' => $beverage->description,
                'price' => floatval($beverage->price),
                'volume' => $beverage->volume,
                'alcohol_degree' => floatval($beverage->alcohol_degree),
                'container_type' => $beverage->container_type,
                'origin' => $beverage->origin,
                'image_url' => $beverage->image_url,
                'category_name' => $beverage->category_name,
                'category_type' => $beverage->category_type
            );
        }
        
        wp_send_json_success($grouped_beverages);
    }
    
    /**
     * AJAX - Estimation rapide de prix
     */
    public function ajax_quick_estimate() {
        check_ajax_referer('block_traiteur_ajax', 'nonce');
        
        $service_type = sanitize_text_field($_POST['serviceType'] ?? 'restaurant');
        $guest_count = intval($_POST['guestCount'] ?? 0);
        $duration = intval($_POST['duration'] ?? 2);
        $distance = floatval($_POST['distance'] ?? 0);
        
        $estimate = Block_Traiteur_Calculator::quick_estimate($service_type, $guest_count, $duration, $distance);
        
        wp_send_json_success(array(
            'estimate' => $estimate,
            'formatted' => number_format($estimate, 2) . ' € TTC',
            'breakdown' => array(
                'service_type' => $service_type,
                'guest_count' => $guest_count,
                'duration' => $duration,
                'distance' => $distance
            )
        ));
    }
}