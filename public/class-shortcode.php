<?php
/**
 * Classe de gestion des shortcodes
 */

if (!defined('ABSPATH')) {
    exit;
}

class Block_Traiteur_Shortcode {
    
    /**
     * Constructeur
     */
    public function __construct() {
        // Enregistrer les deux shortcodes pour compatibilité
        add_shortcode('block_traiteur_form', array($this, 'render_quote_form'));
        add_shortcode('block_quote_form', array($this, 'render_quote_form'));
        
        // Debug - à supprimer après tests
        error_log('Block Traiteur: Shortcodes enregistrés');
    }
    
    /**
     * Rendu du shortcode principal
     */
    public function render_quote_form($atts) {
        // Debug - à supprimer après tests
        error_log('Block Traiteur: Shortcode appelé avec attributs: ' . print_r($atts, true));
        
        $atts = shortcode_atts(array(
            'type' => 'both',
            'theme' => 'light',
            'show_progress' => 'true',
            'auto_start' => 'false',
            'hide_header' => 'false',
            'show_price' => 'true',
            'custom_class' => ''
        ), $atts, 'block_traiteur_form');
        
        // Valider les paramètres
        $valid_types = array('both', 'restaurant', 'remorque');
        if (!in_array($atts['type'], $valid_types)) {
            $atts['type'] = 'both';
        }
        
        // Enqueue des scripts et styles
        $this->enqueue_form_assets();
        
        // Générer un ID unique pour ce formulaire
        $form_id = 'block-quote-form-' . uniqid();
        
        // Construire les classes CSS
        $css_classes = array(
            'block-quote-form',
            'block-traiteur-widget',
            $form_id  // Ajouter l'ID unique comme classe
        );
        $css_classes[] = 'service-type-' . $atts['type'];
        $css_classes[] = 'theme-' . $atts['theme'];
        
        if ($atts['custom_class']) {
            $css_classes[] = sanitize_html_class($atts['custom_class']);
        }
        
        // Configuration pour JavaScript
        $js_config = array(
            'formId' => $form_id,
            'serviceType' => $atts['type'],
            'showProgress' => ($atts['show_progress'] === 'true'),
            'autoStart' => ($atts['auto_start'] === 'true'),
            'hideHeader' => ($atts['hide_header'] === 'true'),
            'showPrice' => ($atts['show_price'] === 'true'),
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('block_traiteur_ajax'),
            'settings' => $this->get_plugin_settings()
        );
        
        // Démarrer la capture de sortie
        ob_start();
        
        // Vérifier si le template existe
        $template_path = BLOCK_TRAITEUR_PLUGIN_DIR . 'templates/form-main.php';
        if (file_exists($template_path)) {
            include $template_path;
        } else {
            // Template de fallback
            echo $this->render_fallback_form($form_id, $css_classes, $js_config);
        }
        
        return ob_get_clean();
    }
    
    /**
     * Template de fallback si le fichier principal n'existe pas
     */
    private function render_fallback_form($form_id, $css_classes, $js_config) {
        return sprintf(
            '<div id="%s" class="%s" data-config="%s">
                <div class="form-header">
                    <h2>Demande de Devis Block Traiteur</h2>
                    <p>Le formulaire sera bientôt disponible.</p>
                </div>
                <div class="contact-info">
                    <p><strong>En attendant, contactez-nous directement :</strong></p>
                    <p>Téléphone : 06 58 13 38 05</p>
                    <p>Email : contact@block-strasbourg.fr</p>
                </div>
            </div>',
            esc_attr($form_id),
            esc_attr(implode(' ', $css_classes)),
            esc_attr(wp_json_encode($js_config))
        );
    }
    
    /**
     * Récupérer les paramètres du plugin
     */
    private function get_plugin_settings() {
        // Si la classe Block_Traiteur_Cache existe
        if (class_exists('Block_Traiteur_Cache')) {
            return Block_Traiteur_Cache::get_settings();
        }
        
        // Sinon, paramètres par défaut
        return array(
            'restaurant_base_price' => 300,
            'remorque_base_price' => 350,
            'company_phone' => '06 58 13 38 05',
            'company_email' => 'contact@block-strasbourg.fr'
        );
    }
    
/**
 * Enqueue des assets du formulaire
 */
private function enqueue_form_assets() {
    // CSS de base
    wp_enqueue_style(
        'block-traiteur-base',
        BLOCK_TRAITEUR_PLUGIN_URL . 'public/css/base.css',
        array(),
        BLOCK_TRAITEUR_VERSION
    );
    
    // CSS du formulaire principal
    wp_enqueue_style(
        'block-traiteur-form',
        BLOCK_TRAITEUR_PLUGIN_URL . 'public/css/form.css',
        array('block-traiteur-base'),
        BLOCK_TRAITEUR_VERSION
    );
    
    // CSS des étapes détaillées
    wp_enqueue_style(
        'block-traiteur-form-steps',
        BLOCK_TRAITEUR_PLUGIN_URL . 'public/css/form-steps.css',
        array('block-traiteur-form'),
        BLOCK_TRAITEUR_VERSION
    );
    
    // CSS public (si nécessaire)
    wp_enqueue_style(
        'block-traiteur-public',
        BLOCK_TRAITEUR_PLUGIN_URL . 'public/css/public.css',
        array('block-traiteur-form-steps'),
        BLOCK_TRAITEUR_VERSION
    );
    
    // JavaScript
    wp_enqueue_script(
        'block-traiteur-form',
        BLOCK_TRAITEUR_PLUGIN_URL . 'public/js/form.js',
        array('jquery'),
        BLOCK_TRAITEUR_VERSION,
        true
    );
    
    // Calculateur de prix
    wp_enqueue_script(
        'block-traiteur-price-calculator',
        BLOCK_TRAITEUR_PLUGIN_URL . 'public/js/price-calculator.js',
        array('block-traiteur-form'),
        BLOCK_TRAITEUR_VERSION,
        true
    );
    
    // Localisation avec toutes les variables nécessaires
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
            'noProducts' => __('Aucun produit disponible', 'block-traiteur'),
            'noBeverages' => __('Aucune boisson disponible', 'block-traiteur')
        )
    ));
}
}