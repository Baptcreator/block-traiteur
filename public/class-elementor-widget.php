<?php
/**
 * Widget Elementor pour Block Traiteur
 */

// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit;
}

// VÉRIFIER QUE ELEMENTOR EST DISPONIBLE AVANT TOUT
if (!did_action('elementor/loaded') || !class_exists('\Elementor\Widget_Base')) {
    return; // Sortir complètement du fichier
}

// MAINTENANT on peut utiliser les classes Elementor en sécurité
use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Background;
use Elementor\Core\Schemes\Typography;
use Elementor\Core\Schemes\Color;

/**
 * Classe Block_Traiteur_Elementor_Widget
 * 
 * Widget Elementor pour afficher le formulaire de devis Block Traiteur
 */
class Block_Traiteur_Elementor_Widget extends Widget_Base {
    
    /**
     * Obtenir le nom du widget
     */
    public function get_name() {
        return 'block-traiteur-form';
    }
    
    /**
     * Obtenir le titre du widget
     */
    public function get_title() {
        return __('Block Traiteur - Formulaire de Devis', 'block-traiteur');
    }
    
    /**
     * Obtenir l'icône du widget
     */
    public function get_icon() {
        return 'eicon-form-horizontal';
    }
    
    /**
     * Obtenir les catégories du widget
     */
    public function get_categories() {
        return ['block-traiteur', 'general'];
    }
    
    /**
     * Obtenir les mots-clés du widget
     */
    public function get_keywords() {
        return ['block', 'traiteur', 'devis', 'formulaire', 'restaurant', 'remorque'];
    }
    
    /**
     * Obtenir les dépendances de scripts
     */
    public function get_script_depends() {
        return ['block-traiteur-form', 'block-traiteur-calculator'];
    }
    
    /**
     * Obtenir les dépendances de styles
     */
    public function get_style_depends() {
        return ['block-traiteur-public', 'block-traiteur-form'];
    }
    
    /**
     * Enregistrer les contrôles du widget
     */
    protected function register_controls() {
        // Section Contenu
        $this->start_controls_section(
            'content_section',
            [
                'label' => __('Contenu', 'block-traiteur'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );
        
        $this->add_control(
            'form_title',
            [
                'label' => __('Titre du formulaire', 'block-traiteur'),
                'type' => Controls_Manager::TEXT,
                'default' => __('Demandez votre devis personnalisé', 'block-traiteur'),
                'placeholder' => __('Entrez le titre...', 'block-traiteur'),
                'label_block' => true,
            ]
        );
        
        $this->add_control(
            'form_subtitle',
            [
                'label' => __('Sous-titre', 'block-traiteur'),
                'type' => Controls_Manager::TEXTAREA,
                'default' => __('Privatisation restaurant ou remorque mobile, configurez votre événement en quelques clics', 'block-traiteur'),
                'placeholder' => __('Entrez le sous-titre...', 'block-traiteur'),
                'rows' => 3,
            ]
        );
        
        $this->add_control(
            'show_progress_bar',
            [
                'label' => __('Afficher la barre de progression', 'block-traiteur'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => __('Oui', 'block-traiteur'),
                'label_off' => __('Non', 'block-traiteur'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );
        
        $this->add_control(
            'show_price_calculator',
            [
                'label' => __('Afficher le calculateur de prix', 'block-traiteur'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => __('Oui', 'block-traiteur'),
                'label_off' => __('Non', 'block-traiteur'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );
        
        $this->add_control(
            'default_service',
            [
                'label' => __('Service par défaut', 'block-traiteur'),
                'type' => Controls_Manager::SELECT,
                'default' => 'none',
                'options' => [
                    'none' => __('Laisser le choix', 'block-traiteur'),
                    'restaurant' => __('Restaurant', 'block-traiteur'),
                    'remorque' => __('Remorque', 'block-traiteur'),
                ],
            ]
        );
        
        $this->add_control(
            'redirect_after_submit',
            [
                'label' => __('Page de redirection après soumission', 'block-traiteur'),
                'type' => Controls_Manager::URL,
                'placeholder' => __('https://votre-site.com/merci', 'block-traiteur'),
                'description' => __('Laissez vide pour rester sur la même page', 'block-traiteur'),
            ]
        );
        
        $this->end_controls_section();
        
        // Section Apparence
        $this->start_controls_section(
            'appearance_section',
            [
                'label' => __('Apparence', 'block-traiteur'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );
        
        $this->add_control(
            'form_layout',
            [
                'label' => __('Disposition', 'block-traiteur'),
                'type' => Controls_Manager::SELECT,
                'default' => 'vertical',
                'options' => [
                    'vertical' => __('Verticale', 'block-traiteur'),
                    'horizontal' => __('Horizontale', 'block-traiteur'),
                    'compact' => __('Compacte', 'block-traiteur'),
                ],
            ]
        );
        
        $this->add_control(
            'form_width',
            [
                'label' => __('Largeur du formulaire', 'block-traiteur'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['%', 'px'],
                'range' => [
                    '%' => [
                        'min' => 50,
                        'max' => 100,
                    ],
                    'px' => [
                        'min' => 300,
                        'max' => 1200,
                    ],
                ],
                'default' => [
                    'unit' => '%',
                    'size' => 100,
                ],
                'selectors' => [
                    '{{WRAPPER}} .block-traiteur-form-container' => 'max-width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );
        
        $this->add_control(
            'form_alignment',
            [
                'label' => __('Alignement', 'block-traiteur'),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => __('Gauche', 'block-traiteur'),
                        'icon' => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => __('Centre', 'block-traiteur'),
                        'icon' => 'eicon-text-align-center',
                    ],
                    'right' => [
                        'title' => __('Droite', 'block-traiteur'),
                        'icon' => 'eicon-text-align-right',
                    ],
                ],
                'default' => 'left',
                'selectors' => [
                    '{{WRAPPER}} .block-traiteur-widget-wrapper' => 'text-align: {{VALUE}};',
                ],
            ]
        );
        
        $this->end_controls_section();
        
        // Section Style - Titre
        $this->start_controls_section(
            'title_style_section',
            [
                'label' => __('Style du titre', 'block-traiteur'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );
        
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'title_typography',
                'label' => __('Typographie', 'block-traiteur'),
                'selector' => '{{WRAPPER}} .form-title',
            ]
        );
        
        $this->add_control(
            'title_color',
            [
                'label' => __('Couleur', 'block-traiteur'),
                'type' => Controls_Manager::COLOR,
                'default' => '#243127',
                'selectors' => [
                    '{{WRAPPER}} .form-title' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_responsive_control(
            'title_margin',
            [
                'label' => __('Marge', 'block-traiteur'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .form-title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        $this->end_controls_section();
        
        // Section Style - Formulaire
        $this->start_controls_section(
            'form_style_section',
            [
                'label' => __('Style du formulaire', 'block-traiteur'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );
        
        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => 'form_background',
                'label' => __('Arrière-plan', 'block-traiteur'),
                'types' => ['classic', 'gradient'],
                'selector' => '{{WRAPPER}} .block-traiteur-form-container',
            ]
        );
        
        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'form_border',
                'label' => __('Bordure', 'block-traiteur'),
                'selector' => '{{WRAPPER}} .block-traiteur-form-container',
            ]
        );
        
        $this->add_responsive_control(
            'form_border_radius',
            [
                'label' => __('Rayon de bordure', 'block-traiteur'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .block-traiteur-form-container' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'form_box_shadow',
                'label' => __('Ombre', 'block-traiteur'),
                'selector' => '{{WRAPPER}} .block-traiteur-form-container',
            ]
        );
        
        $this->add_responsive_control(
            'form_padding',
            [
                'label' => __('Espacement interne', 'block-traiteur'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .block-traiteur-form-container' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        $this->end_controls_section();
        
        // Section Style - Boutons
        $this->start_controls_section(
            'buttons_style_section',
            [
                'label' => __('Style des boutons', 'block-traiteur'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );
        
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'button_typography',
                'label' => __('Typographie', 'block-traiteur'),
                'selector' => '{{WRAPPER}} .btn, {{WRAPPER}} .form-submit-btn',
            ]
        );
        
        $this->add_responsive_control(
            'button_padding',
            [
                'label' => __('Espacement interne', 'block-traiteur'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .btn, {{WRAPPER}} .form-submit-btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        $this->start_controls_tabs('button_style_tabs');
        
        // Onglet Normal
        $this->start_controls_tab(
            'button_normal_tab',
            [
                'label' => __('Normal', 'block-traiteur'),
            ]
        );
        
        $this->add_control(
            'button_color',
            [
                'label' => __('Couleur du texte', 'block-traiteur'),
                'type' => Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .btn-primary, {{WRAPPER}} .form-submit-btn' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_control(
            'button_background_color',
            [
                'label' => __('Couleur de fond', 'block-traiteur'),
                'type' => Controls_Manager::COLOR,
                'default' => '#243127',
                'selectors' => [
                    '{{WRAPPER}} .btn-primary, {{WRAPPER}} .form-submit-btn' => 'background-color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'button_border',
                'label' => __('Bordure', 'block-traiteur'),
                'selector' => '{{WRAPPER}} .btn, {{WRAPPER}} .form-submit-btn',
            ]
        );
        
        $this->end_controls_tab();
        
        // Onglet Hover
        $this->start_controls_tab(
            'button_hover_tab',
            [
                'label' => __('Survol', 'block-traiteur'),
            ]
        );
        
        $this->add_control(
            'button_hover_color',
            [
                'label' => __('Couleur du texte', 'block-traiteur'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .btn:hover, {{WRAPPER}} .form-submit-btn:hover' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_control(
            'button_hover_background_color',
            [
                'label' => __('Couleur de fond', 'block-traiteur'),
                'type' => Controls_Manager::COLOR,
                'default' => '#FFB404',
                'selectors' => [
                    '{{WRAPPER}} .btn:hover, {{WRAPPER}} .form-submit-btn:hover' => 'background-color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_control(
            'button_hover_border_color',
            [
                'label' => __('Couleur de bordure', 'block-traiteur'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .btn:hover, {{WRAPPER}} .form-submit-btn:hover' => 'border-color: {{VALUE}};',
                ],
            ]
        );
        
        $this->end_controls_tab();
        
        $this->end_controls_tabs();
        
        $this->add_responsive_control(
            'button_border_radius',
            [
                'label' => __('Rayon de bordure', 'block-traiteur'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .btn, {{WRAPPER}} .form-submit-btn' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'separator' => 'before',
            ]
        );
        
        $this->end_controls_section();
        
        // Section Style - Calculateur de prix
        $this->start_controls_section(
            'calculator_style_section',
            [
                'label' => __('Style du calculateur', 'block-traiteur'),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'show_price_calculator' => 'yes',
                ],
            ]
        );
        
        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => 'calculator_background',
                'label' => __('Arrière-plan', 'block-traiteur'),
                'types' => ['classic', 'gradient'],
                'selector' => '{{WRAPPER}} .price-calculator',
            ]
        );
        
        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'calculator_border',
                'label' => __('Bordure', 'block-traiteur'),
                'selector' => '{{WRAPPER}} .price-calculator',
            ]
        );
        
        $this->add_control(
            'calculator_text_color',
            [
                'label' => __('Couleur du texte', 'block-traiteur'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .price-calculator' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_control(
            'calculator_price_color',
            [
                'label' => __('Couleur du prix', 'block-traiteur'),
                'type' => Controls_Manager::COLOR,
                'default' => '#EF3D1D',
                'selectors' => [
                    '{{WRAPPER}} .price-total, {{WRAPPER}} .price-highlight' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_responsive_control(
            'calculator_padding',
            [
                'label' => __('Espacement interne', 'block-traiteur'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .price-calculator' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        $this->end_controls_section();
        
        // Section Avancé
        $this->start_controls_section(
            'advanced_section',
            [
                'label' => __('Avancé', 'block-traiteur'),
                'tab' => Controls_Manager::TAB_ADVANCED,
            ]
        );
        
        $this->add_control(
            'custom_css_class',
            [
                'label' => __('Classe CSS personnalisée', 'block-traiteur'),
                'type' => Controls_Manager::TEXT,
                'placeholder' => 'ma-classe-personnalisee',
                'description' => __('Ajoutez une classe CSS personnalisée au widget', 'block-traiteur'),
            ]
        );
        
        $this->add_control(
            'form_id',
            [
                'label' => __('ID du formulaire', 'block-traiteur'),
                'type' => Controls_Manager::TEXT,
                'placeholder' => 'mon-formulaire-unique',
                'description' => __('ID HTML unique pour ce formulaire', 'block-traiteur'),
            ]
        );
        
        $this->add_control(
            'enable_analytics',
            [
                'label' => __('Activer le suivi analytique', 'block-traiteur'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => __('Oui', 'block-traiteur'),
                'label_off' => __('Non', 'block-traiteur'),
                'return_value' => 'yes',
                'default' => 'yes',
                'description' => __('Envoie des événements Google Analytics lors des interactions', 'block-traiteur'),
            ]
        );
        
        $this->end_controls_section();
    }
    
    /**
     * Rendu du widget côté frontend
     */
    protected function render() {
        $settings = $this->get_settings_for_display();
        
        // Préparer les classes CSS
        $css_classes = ['block-traiteur-widget-wrapper'];
        if (!empty($settings['custom_css_class'])) {
            $css_classes[] = esc_attr($settings['custom_css_class']);
        }
        if (!empty($settings['form_layout'])) {
            $css_classes[] = 'layout-' . esc_attr($settings['form_layout']);
        }
        
        // Générer un ID unique si non spécifié
        $form_id = !empty($settings['form_id']) ? esc_attr($settings['form_id']) : 'block-traiteur-form-' . $this->get_id();
        
        // Configuration du widget
        $widget_config = [
            'show_progress_bar' => $settings['show_progress_bar'] === 'yes',
            'show_price_calculator' => $settings['show_price_calculator'] === 'yes',
            'default_service' => $settings['default_service'],
            'redirect_url' => !empty($settings['redirect_after_submit']['url']) ? $settings['redirect_after_submit']['url'] : '',
            'enable_analytics' => $settings['enable_analytics'] === 'yes',
            'widget_id' => $this->get_id()
        ];
        ?>
        
        <div class="<?php echo implode(' ', $css_classes); ?>" data-widget-config="<?php echo esc_attr(json_encode($widget_config)); ?>">
            <?php if (!empty($settings['form_title']) || !empty($settings['form_subtitle'])): ?>
                <div class="form-header">
                    <?php if (!empty($settings['form_title'])): ?>
                        <h2 class="form-title"><?php echo esc_html($settings['form_title']); ?></h2>
                    <?php endif; ?>
                    
                    <?php if (!empty($settings['form_subtitle'])): ?>
                        <p class="form-subtitle"><?php echo esc_html($settings['form_subtitle']); ?></p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <div class="block-traiteur-form-container" id="<?php echo $form_id; ?>">
                <?php
                // Rendu du formulaire principal
                $this->render_quote_form($settings);
                ?>
            </div>
            
            <?php if ($settings['show_price_calculator'] === 'yes'): ?>
                <div class="price-calculator-container">
                    <?php $this->render_price_calculator(); ?>
                </div>
            <?php endif; ?>
        </div>
        
        <?php
        // Ajouter le script d'initialisation
        $this->render_widget_script($widget_config);
    }
    
    /**
     * Rendu du formulaire de devis
     */
    private function render_quote_form($settings) {
        // Utiliser le shortcode existant ou créer le formulaire
        if (class_exists('Block_Traiteur_Shortcode')) {
            $shortcode_atts = [
                'default_service' => $settings['default_service'],
                'show_progress' => $settings['show_progress_bar'] === 'yes' ? 'true' : 'false',
                'form_id' => !empty($settings['form_id']) ? $settings['form_id'] : 'elementor-' . $this->get_id()
            ];
            
            $shortcode = new Block_Traiteur_Shortcode();
            echo $shortcode->render_form($shortcode_atts);
        } else {
            // Fallback : formulaire basique
            $this->render_fallback_form();
        }
    }
    
    /**
     * Rendu du calculateur de prix
     */
    private function render_price_calculator() {
        ?>
        <div class="price-calculator">
            <div class="calculator-header">
                <h3><?php _e('Estimation de prix', 'block-traiteur'); ?></h3>
                <p class="calculator-description">
                    <?php _e('Prix calculé en temps réel selon vos sélections', 'block-traiteur'); ?>
                </p>
            </div>
            
            <div class="price-breakdown">
                <div class="price-line base-price">
                    <span class="price-label"><?php _e('Forfait de base', 'block-traiteur'); ?></span>
                    <span class="price-value">0 €</span>
                </div>
                
                <div class="price-line extras-price" style="display: none;">
                    <span class="price-label"><?php _e('Suppléments', 'block-traiteur'); ?></span>
                    <span class="price-value">0 €</span>
                </div>
                
                <div class="price-line travel-price" style="display: none;">
                    <span class="price-label"><?php _e('Frais de déplacement', 'block-traiteur'); ?></span>
                    <span class="price-value">0 €</span>
                </div>
                
                <div class="price-total-line">
                    <span class="price-label total-label"><?php _e('Total TTC', 'block-traiteur'); ?></span>
                    <span class="price-value price-total">0 €</span>
                </div>
            </div>
            
            <div class="calculator-footer">
                <p class="price-disclaimer">
                    <?php _e('* Prix indicatif, susceptible de modifications selon les options choisies', 'block-traiteur'); ?>
                </p>
            </div>
        </div>
        <?php
    }
    
    /**
     * Rendu du formulaire de fallback
     */
    private function render_fallback_form() {
        ?>
        <div class="fallback-form">
            <div class="notice notice-warning">
                <p><?php _e('Le formulaire Block Traiteur n\'est pas disponible. Veuillez vérifier la configuration du plugin.', 'block-traiteur'); ?></p>
            </div>
            
            <div class="contact-info">
                <h3><?php _e('Nous contacter directement', 'block-traiteur'); ?></h3>
                <?php
                $company_phone = get_option('block_traiteur_company_phone', '');
                $company_email = get_option('block_traiteur_company_email', '');
                ?>
                
                <?php if (!empty($company_phone)): ?>
                    <p><strong><?php _e('Téléphone :', 'block-traiteur'); ?></strong> 
                       <a href="tel:<?php echo esc_attr($company_phone); ?>"><?php echo esc_html($company_phone); ?></a>
                    </p>
                <?php endif; ?>
                
                <?php if (!empty($company_email)): ?>
                    <p><strong><?php _e('Email :', 'block-traiteur'); ?></strong> 
                       <a href="mailto:<?php echo esc_attr($company_email); ?>"><?php echo esc_html($company_email); ?></a>
                    </p>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
    
    /**
     * Rendu du script d'initialisation du widget
     */
    private function render_widget_script($config) {
        ?>
        <script type="text/javascript">
        (function($) {
            $(document).ready(function() {
                // Initialiser le widget Block Traiteur
                if (typeof window.BlockTraiteurWidget !== 'undefined') {
                    window.BlockTraiteurWidget.init('<?php echo $this->get_id(); ?>', <?php echo json_encode($config); ?>);
                }
                
                // Événements d'analyse si activés
                <?php if ($config['enable_analytics']): ?>
                if (typeof gtag !== 'undefined') {
                    // Suivi de l'affichage du widget
                    gtag('event', 'widget_loaded', {
                        'event_category': 'Block Traiteur',
                        'event_label': 'Widget Elementor',
                        'widget_id': '<?php echo $this->get_id(); ?>'
                    });
                }
                <?php endif; ?>
            });
        })(jQuery);
        </script>
        <?php
    }
    
    /**
     * Rendu du widget en mode éditeur Elementor
     */
    protected function content_template() {
        ?>
        <#
        var cssClasses = ['block-traiteur-widget-wrapper'];
        if (settings.custom_css_class) {
            cssClasses.push(settings.custom_css_class);
        }
        if (settings.form_layout) {
            cssClasses.push('layout-' + settings.form_layout);
        }
        
        var formId = settings.form_id || 'block-traiteur-form-preview';
        #>
        
        <div class="{{ cssClasses.join(' ') }}">
            <# if (settings.form_title || settings.form_subtitle) { #>
                <div class="form-header">
                    <# if (settings.form_title) { #>
                        <h2 class="form-title">{{{ settings.form_title }}}</h2>
                    <# } #>
                    
                    <# if (settings.form_subtitle) { #>
                        <p class="form-subtitle">{{{ settings.form_subtitle }}}</p>
                    <# } #>
                </div>
            <# } #>
            
            <div class="block-traiteur-form-container" id="{{ formId }}">
                <div class="elementor-preview-notice">
                    <div class="preview-icon">
                        <i class="eicon-form-horizontal"></i>
                    </div>
                    <h3><?php _e('Formulaire Block Traiteur', 'block-traiteur'); ?></h3>
                    <p><?php _e('Le formulaire de devis s\'affichera ici sur le site en ligne.', 'block-traiteur'); ?></p>
                    
                    <div class="preview-features">
                        <# if (settings.show_progress_bar === 'yes') { #>
                            <span class="feature-badge"><?php _e('Barre de progression', 'block-traiteur'); ?></span>
                        <# } #>
                        
                        <# if (settings.show_price_calculator === 'yes') { #>
                            <span class="feature-badge"><?php _e('Calculateur de prix', 'block-traiteur'); ?></span>
                        <# } #>
                        
                        <# if (settings.default_service !== 'none') { #>
                            <span class="feature-badge">
                                <?php _e('Service par défaut:', 'block-traiteur'); ?> {{ settings.default_service }}
                            </span>
                        <# } #>
                    </div>
                </div>
            </div>
            
            <# if (settings.show_price_calculator === 'yes') { #>
                <div class="price-calculator-container">
                    <div class="price-calculator preview-mode">
                        <div class="calculator-header">
                            <h3><?php _e('Estimation de prix', 'block-traiteur'); ?></h3>
                            <p><?php _e('Aperçu du calculateur', 'block-traiteur'); ?></p>
                        </div>
                        <div class="price-preview">
                            <div class="price-line">
                                <span><?php _e('Forfait de base', 'block-traiteur'); ?></span>
                                <span>350 €</span>
                            </div>
                            <div class="price-total-line">
                                <span><?php _e('Total TTC', 'block-traiteur'); ?></span>
                                <span class="price-total">350 €</span>
                            </div>
                        </div>
                    </div>
                </div>
            <# } #>
        </div>
        <?php
    }
}

// Enregistrer le widget auprès d'Elementor
function register_block_traiteur_elementor_widget() {
    if (did_action('elementor/loaded')) {
        \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new Block_Traiteur_Elementor_Widget());
    }
}
add_action('elementor/widgets/widgets_registered', 'register_block_traiteur_elementor_widget');

// Créer une catégorie personnalisée pour les widgets Block Traiteur
function add_block_traiteur_elementor_category($elements_manager) {
    $elements_manager->add_category(
        'block-traiteur',
        [
            'title' => __('Block Traiteur', 'block-traiteur'),
            'icon' => 'fa fa-cutlery',
        ]
    );
}
add_action('elementor/elements/categories_registered', 'add_block_traiteur_elementor_category');