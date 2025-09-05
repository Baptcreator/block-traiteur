<?php
/**
 * Widget Elementor Service Selection Cards
 * Selon les spécifications : 2 cards side-by-side pour page Traiteur
 */

if (!defined('ABSPATH')) {
    exit;
}

// Vérifier qu'Elementor est disponible
if (!did_action('elementor/loaded') || !class_exists('\Elementor\Widget_Base')) {
    return;
}

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Image_Size;

/**
 * Widget Service Selection pour page Traiteur
 */
class Block_Traiteur_Service_Selection_Widget extends Widget_Base {
    
    public function get_name() {
        return 'block-service-selection';
    }
    
    public function get_title() {
        return __('Service Selection Cards', 'block-traiteur');
    }
    
    public function get_icon() {
        return 'eicon-posts-grid';
    }
    
    public function get_categories() {
        return ['block-traiteur'];
    }
    
    public function get_keywords() {
        return ['block', 'service', 'selection', 'cards', 'traiteur', 'restaurant', 'remorque'];
    }
    
    protected function register_controls() {
        // Section Général
        $this->start_controls_section(
            'general_section',
            [
                'label' => __('Paramètres généraux', 'block-traiteur'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );
        
        $this->add_control(
            'section_title',
            [
                'label' => __('Titre de section', 'block-traiteur'),
                'type' => Controls_Manager::TEXT,
                'default' => __('Choisissez votre service', 'block-traiteur'),
                'label_block' => true,
            ]
        );
        
        $this->add_control(
            'section_subtitle',
            [
                'label' => __('Sous-titre de section', 'block-traiteur'),
                'type' => Controls_Manager::TEXTAREA,
                'default' => __('Sélectionnez le service qui correspond le mieux à votre événement', 'block-traiteur'),
                'rows' => 3,
            ]
        );
        
        $this->add_control(
            'cards_layout',
            [
                'label' => __('Disposition des cards', 'block-traiteur'),
                'type' => Controls_Manager::SELECT,
                'default' => 'side-by-side',
                'options' => [
                    'side-by-side' => __('Côte à côte', 'block-traiteur'),
                    'stacked' => __('Empilées', 'block-traiteur'),
                    'centered' => __('Centrées', 'block-traiteur'),
                ],
            ]
        );
        
        $this->end_controls_section();
        
        // Section Card Restaurant
        $this->start_controls_section(
            'restaurant_card_section',
            [
                'label' => __('Card Restaurant', 'block-traiteur'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );
        
        $this->add_control(
            'restaurant_title',
            [
                'label' => __('Titre', 'block-traiteur'),
                'type' => Controls_Manager::TEXT,
                'default' => __('Privatisation Restaurant', 'block-traiteur'),
                'label_block' => true,
            ]
        );
        
        $this->add_control(
            'restaurant_capacity',
            [
                'label' => __('Capacité', 'block-traiteur'),
                'type' => Controls_Manager::TEXT,
                'default' => __('10 à 30 personnes', 'block-traiteur'),
            ]
        );
        
        $this->add_control(
            'restaurant_description',
            [
                'label' => __('Description', 'block-traiteur'),
                'type' => Controls_Manager::TEXTAREA,
                'default' => __('Privatisez notre restaurant pour vos événements intimes. Ambiance chaleureuse garantie dans notre espace unique au cœur de Strasbourg.', 'block-traiteur'),
                'rows' => 4,
            ]
        );
        
        $this->add_control(
            'restaurant_features',
            [
                'label' => __('Caractéristiques', 'block-traiteur'),
                'type' => Controls_Manager::TEXTAREA,
                'default' => __("• Espace privatisé exclusivement\n• Ambiance cosy et moderne\n• Service personnalisé\n• Cuisine ouverte sur la salle", 'block-traiteur'),
                'rows' => 6,
                'description' => __('Une caractéristique par ligne, commencez par • ou -', 'block-traiteur'),
            ]
        );
        
        $this->add_control(
            'restaurant_image',
            [
                'label' => __('Image', 'block-traiteur'),
                'type' => Controls_Manager::MEDIA,
                'default' => [
                    'url' => BLOCK_TRAITEUR_PLUGIN_URL . 'assets/images/restaurant-icon.svg',
                ],
            ]
        );
        
        $this->add_group_control(
            Group_Control_Image_Size::get_type(),
            [
                'name' => 'restaurant_image',
                'default' => 'medium',
            ]
        );
        
        $this->add_control(
            'restaurant_button_text',
            [
                'label' => __('Texte du bouton', 'block-traiteur'),
                'type' => Controls_Manager::TEXT,
                'default' => __('Choisir le Restaurant', 'block-traiteur'),
            ]
        );
        
        $this->add_control(
            'restaurant_button_url',
            [
                'label' => __('Lien du bouton', 'block-traiteur'),
                'type' => Controls_Manager::URL,
                'placeholder' => __('https://votre-site.com/devis?service=restaurant', 'block-traiteur'),
            ]
        );
        
        $this->add_control(
            'restaurant_price_info',
            [
                'label' => __('Information prix', 'block-traiteur'),
                'type' => Controls_Manager::TEXT,
                'default' => __('À partir de 300€', 'block-traiteur'),
            ]
        );
        
        $this->end_controls_section();
        
        // Section Card Remorque
        $this->start_controls_section(
            'remorque_card_section',
            [
                'label' => __('Card Remorque', 'block-traiteur'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );
        
        $this->add_control(
            'remorque_title',
            [
                'label' => __('Titre', 'block-traiteur'),
                'type' => Controls_Manager::TEXT,
                'default' => __('Privatisation Remorque Mobile', 'block-traiteur'),
                'label_block' => true,
            ]
        );
        
        $this->add_control(
            'remorque_capacity',
            [
                'label' => __('Capacité', 'block-traiteur'),
                'type' => Controls_Manager::TEXT,
                'default' => __('À partir de 20 personnes', 'block-traiteur'),
            ]
        );
        
        $this->add_control(
            'remorque_description',
            [
                'label' => __('Description', 'block-traiteur'),
                'type' => Controls_Manager::TEXTAREA,
                'default' => __('Notre remorque mobile se déplace où vous voulez. Parfaite pour vos événements en extérieur, festivals, mariages ou événements d\'entreprise.', 'block-traiteur'),
                'rows' => 4,
            ]
        );
        
        $this->add_control(
            'remorque_features',
            [
                'label' => __('Caractéristiques', 'block-traiteur'),
                'type' => Controls_Manager::TEXTAREA,
                'default' => __("• Déplacement dans un rayon de 150km\n• Équipement professionnel complet\n• Installation rapide sur site\n• Options jeux et animation", 'block-traiteur'),
                'rows' => 6,
                'description' => __('Une caractéristique par ligne, commencez par • ou -', 'block-traiteur'),
            ]
        );
        
        $this->add_control(
            'remorque_image',
            [
                'label' => __('Image', 'block-traiteur'),
                'type' => Controls_Manager::MEDIA,
                'default' => [
                    'url' => BLOCK_TRAITEUR_PLUGIN_URL . 'assets/images/remorque-icon.svg',
                ],
            ]
        );
        
        $this->add_group_control(
            Group_Control_Image_Size::get_type(),
            [
                'name' => 'remorque_image',
                'default' => 'medium',
            ]
        );
        
        $this->add_control(
            'remorque_button_text',
            [
                'label' => __('Texte du bouton', 'block-traiteur'),
                'type' => Controls_Manager::TEXT,
                'default' => __('Choisir la Remorque', 'block-traiteur'),
            ]
        );
        
        $this->add_control(
            'remorque_button_url',
            [
                'label' => __('Lien du bouton', 'block-traiteur'),
                'type' => Controls_Manager::URL,
                'placeholder' => __('https://votre-site.com/devis?service=remorque', 'block-traiteur'),
            ]
        );
        
        $this->add_control(
            'remorque_price_info',
            [
                'label' => __('Information prix', 'block-traiteur'),
                'type' => Controls_Manager::TEXT,
                'default' => __('À partir de 350€', 'block-traiteur'),
            ]
        );
        
        $this->end_controls_section();
        
        // Section Style - Global
        $this->start_controls_section(
            'global_style_section',
            [
                'label' => __('Style global', 'block-traiteur'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );
        
        $this->add_control(
            'section_alignment',
            [
                'label' => __('Alignement de section', 'block-traiteur'),
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
                'default' => 'center',
                'selectors' => [
                    '{{WRAPPER}} .service-selection-widget' => 'text-align: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_responsive_control(
            'cards_gap',
            [
                'label' => __('Espacement entre cards', 'block-traiteur'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', 'em', '%'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                    'em' => [
                        'min' => 0,
                        'max' => 10,
                    ],
                    '%' => [
                        'min' => 0,
                        'max' => 20,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 30,
                ],
                'selectors' => [
                    '{{WRAPPER}} .services-cards' => 'gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );
        
        $this->end_controls_section();
        
        // Section Style - Titres
        $this->start_controls_section(
            'titles_style_section',
            [
                'label' => __('Style des titres', 'block-traiteur'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );
        
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'section_title_typography',
                'label' => __('Typographie titre section', 'block-traiteur'),
                'selector' => '{{WRAPPER}} .section-title',
            ]
        );
        
        $this->add_control(
            'section_title_color',
            [
                'label' => __('Couleur titre section', 'block-traiteur'),
                'type' => Controls_Manager::COLOR,
                'default' => '#243127',
                'selectors' => [
                    '{{WRAPPER}} .section-title' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'card_title_typography',
                'label' => __('Typographie titre card', 'block-traiteur'),
                'selector' => '{{WRAPPER}} .card-title',
            ]
        );
        
        $this->add_control(
            'card_title_color',
            [
                'label' => __('Couleur titre card', 'block-traiteur'),
                'type' => Controls_Manager::COLOR,
                'default' => '#243127',
                'selectors' => [
                    '{{WRAPPER}} .card-title' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        $this->end_controls_section();
        
        // Section Style - Cards
        $this->start_controls_section(
            'cards_style_section',
            [
                'label' => __('Style des cards', 'block-traiteur'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );
        
        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => 'card_background',
                'label' => __('Arrière-plan', 'block-traiteur'),
                'types' => ['classic', 'gradient'],
                'selector' => '{{WRAPPER}} .service-card',
            ]
        );
        
        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'card_border',
                'label' => __('Bordure', 'block-traiteur'),
                'selector' => '{{WRAPPER}} .service-card',
            ]
        );
        
        $this->add_responsive_control(
            'card_border_radius',
            [
                'label' => __('Rayon de bordure', 'block-traiteur'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .service-card' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'card_box_shadow',
                'label' => __('Ombre', 'block-traiteur'),
                'selector' => '{{WRAPPER}} .service-card',
            ]
        );
        
        $this->add_responsive_control(
            'card_padding',
            [
                'label' => __('Espacement interne', 'block-traiteur'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .service-card' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        $this->add_control(
            'card_hover_effect',
            [
                'label' => __('Effet au survol', 'block-traiteur'),
                'type' => Controls_Manager::SELECT,
                'default' => 'lift',
                'options' => [
                    'none' => __('Aucun', 'block-traiteur'),
                    'lift' => __('Élévation', 'block-traiteur'),
                    'scale' => __('Agrandissement', 'block-traiteur'),
                    'rotate' => __('Rotation légère', 'block-traiteur'),
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
                'selector' => '{{WRAPPER}} .card-button',
            ]
        );
        
        $this->add_responsive_control(
            'button_padding',
            [
                'label' => __('Espacement interne', 'block-traiteur'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .card-button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        $this->start_controls_tabs('button_style_tabs');
        
        $this->start_controls_tab(
            'button_normal_tab',
            [
                'label' => __('Normal', 'block-traiteur'),
            ]
        );
        
        $this->add_control(
            'button_text_color',
            [
                'label' => __('Couleur du texte', 'block-traiteur'),
                'type' => Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .card-button' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => 'button_background',
                'label' => __('Arrière-plan', 'block-traiteur'),
                'types' => ['classic', 'gradient'],
                'selector' => '{{WRAPPER}} .card-button',
            ]
        );
        
        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'button_border',
                'label' => __('Bordure', 'block-traiteur'),
                'selector' => '{{WRAPPER}} .card-button',
            ]
        );
        
        $this->end_controls_tab();
        
        $this->start_controls_tab(
            'button_hover_tab',
            [
                'label' => __('Survol', 'block-traiteur'),
            ]
        );
        
        $this->add_control(
            'button_hover_text_color',
            [
                'label' => __('Couleur du texte', 'block-traiteur'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .card-button:hover' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => 'button_hover_background',
                'label' => __('Arrière-plan', 'block-traiteur'),
                'types' => ['classic', 'gradient'],
                'selector' => '{{WRAPPER}} .card-button:hover',
            ]
        );
        
        $this->add_control(
            'button_hover_border_color',
            [
                'label' => __('Couleur de bordure', 'block-traiteur'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .card-button:hover' => 'border-color: {{VALUE}};',
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
                    '{{WRAPPER}} .card-button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'separator' => 'before',
            ]
        );
        
        $this->end_controls_section();
    }
    
    protected function render() {
        $settings = $this->get_settings_for_display();
        $hover_class = 'hover-' . $settings['card_hover_effect'];
        ?>
        
        <div class="service-selection-widget">
            <?php if (!empty($settings['section_title']) || !empty($settings['section_subtitle'])): ?>
                <div class="section-header">
                    <?php if (!empty($settings['section_title'])): ?>
                        <h2 class="section-title"><?php echo esc_html($settings['section_title']); ?></h2>
                    <?php endif; ?>
                    
                    <?php if (!empty($settings['section_subtitle'])): ?>
                        <p class="section-subtitle"><?php echo esc_html($settings['section_subtitle']); ?></p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <div class="services-cards layout-<?php echo esc_attr($settings['cards_layout']); ?> <?php echo esc_attr($hover_class); ?>">
                <!-- Card Restaurant -->
                <div class="service-card card-restaurant">
                    <?php if (!empty($settings['restaurant_image']['url'])): ?>
                        <div class="card-image">
                            <?php echo Group_Control_Image_Size::get_attachment_image_html($settings, 'restaurant_image', 'restaurant_image'); ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="card-content">
                        <div class="card-header">
                            <h3 class="card-title"><?php echo esc_html($settings['restaurant_title']); ?></h3>
                            <p class="card-capacity"><?php echo esc_html($settings['restaurant_capacity']); ?></p>
                        </div>
                        
                        <p class="card-description"><?php echo esc_html($settings['restaurant_description']); ?></p>
                        
                        <?php if (!empty($settings['restaurant_features'])): ?>
                            <div class="card-features">
                                <?php
                                $features = explode("\n", $settings['restaurant_features']);
                                foreach ($features as $feature) {
                                    $feature = trim($feature);
                                    if (!empty($feature)) {
                                        echo '<p class="feature-item">' . esc_html($feature) . '</p>';
                                    }
                                }
                                ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($settings['restaurant_price_info'])): ?>
                            <div class="card-price">
                                <span class="price-info"><?php echo esc_html($settings['restaurant_price_info']); ?></span>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($settings['restaurant_button_url']['url'])): ?>
                            <div class="card-action">
                                <a href="<?php echo esc_url($settings['restaurant_button_url']['url']); ?>" 
                                   class="card-button btn-restaurant"
                                   <?php if ($settings['restaurant_button_url']['is_external']): ?>target="_blank"<?php endif; ?>
                                   <?php if ($settings['restaurant_button_url']['nofollow']): ?>rel="nofollow"<?php endif; ?>>
                                    <?php echo esc_html($settings['restaurant_button_text']); ?>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Card Remorque -->
                <div class="service-card card-remorque">
                    <?php if (!empty($settings['remorque_image']['url'])): ?>
                        <div class="card-image">
                            <?php echo Group_Control_Image_Size::get_attachment_image_html($settings, 'remorque_image', 'remorque_image'); ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="card-content">
                        <div class="card-header">
                            <h3 class="card-title"><?php echo esc_html($settings['remorque_title']); ?></h3>
                            <p class="card-capacity"><?php echo esc_html($settings['remorque_capacity']); ?></p>
                        </div>
                        
                        <p class="card-description"><?php echo esc_html($settings['remorque_description']); ?></p>
                        
                        <?php if (!empty($settings['remorque_features'])): ?>
                            <div class="card-features">
                                <?php
                                $features = explode("\n", $settings['remorque_features']);
                                foreach ($features as $feature) {
                                    $feature = trim($feature);
                                    if (!empty($feature)) {
                                        echo '<p class="feature-item">' . esc_html($feature) . '</p>';
                                    }
                                }
                                ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($settings['remorque_price_info'])): ?>
                            <div class="card-price">
                                <span class="price-info"><?php echo esc_html($settings['remorque_price_info']); ?></span>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($settings['remorque_button_url']['url'])): ?>
                            <div class="card-action">
                                <a href="<?php echo esc_url($settings['remorque_button_url']['url']); ?>" 
                                   class="card-button btn-remorque"
                                   <?php if ($settings['remorque_button_url']['is_external']): ?>target="_blank"<?php endif; ?>
                                   <?php if ($settings['remorque_button_url']['nofollow']): ?>rel="nofollow"<?php endif; ?>>
                                    <?php echo esc_html($settings['remorque_button_text']); ?>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <?php
    }
    
    protected function content_template() {
        ?>
        <#
        var restaurantImageUrl = settings.restaurant_image.url || '';
        var remorqueImageUrl = settings.remorque_image.url || '';
        var hoverClass = 'hover-' + settings.card_hover_effect;
        #>
        
        <div class="service-selection-widget">
            <# if (settings.section_title || settings.section_subtitle) { #>
                <div class="section-header">
                    <# if (settings.section_title) { #>
                        <h2 class="section-title">{{{ settings.section_title }}}</h2>
                    <# } #>
                    
                    <# if (settings.section_subtitle) { #>
                        <p class="section-subtitle">{{{ settings.section_subtitle }}}</p>
                    <# } #>
                </div>
            <# } #>
            
            <div class="services-cards layout-{{ settings.cards_layout }} {{ hoverClass }}">
                <!-- Card Restaurant -->
                <div class="service-card card-restaurant">
                    <# if (restaurantImageUrl) { #>
                        <div class="card-image">
                            <img src="{{ restaurantImageUrl }}" alt="{{{ settings.restaurant_title }}}">
                        </div>
                    <# } #>
                    
                    <div class="card-content">
                        <div class="card-header">
                            <h3 class="card-title">{{{ settings.restaurant_title }}}</h3>
                            <p class="card-capacity">{{{ settings.restaurant_capacity }}}</p>
                        </div>
                        
                        <p class="card-description">{{{ settings.restaurant_description }}}</p>
                        
                        <# if (settings.restaurant_features) { #>
                            <div class="card-features">
                                <# 
                                var features = settings.restaurant_features.split('\n');
                                _.each(features, function(feature) {
                                    feature = feature.trim();
                                    if (feature) { #>
                                        <p class="feature-item">{{{ feature }}}</p>
                                    <# }
                                }); 
                                #>
                            </div>
                        <# } #>
                        
                        <# if (settings.restaurant_price_info) { #>
                            <div class="card-price">
                                <span class="price-info">{{{ settings.restaurant_price_info }}}</span>
                            </div>
                        <# } #>
                        
                        <# if (settings.restaurant_button_url.url) { #>
                            <div class="card-action">
                                <a href="{{ settings.restaurant_button_url.url }}" class="card-button btn-restaurant">
                                    {{{ settings.restaurant_button_text }}}
                                </a>
                            </div>
                        <# } #>
                    </div>
                </div>
                
                <!-- Card Remorque -->
                <div class="service-card card-remorque">
                    <# if (remorqueImageUrl) { #>
                        <div class="card-image">
                            <img src="{{ remorqueImageUrl }}" alt="{{{ settings.remorque_title }}}">
                        </div>
                    <# } #>
                    
                    <div class="card-content">
                        <div class="card-header">
                            <h3 class="card-title">{{{ settings.remorque_title }}}</h3>
                            <p class="card-capacity">{{{ settings.remorque_capacity }}}</p>
                        </div>
                        
                        <p class="card-description">{{{ settings.remorque_description }}}</p>
                        
                        <# if (settings.remorque_features) { #>
                            <div class="card-features">
                                <# 
                                var features = settings.remorque_features.split('\n');
                                _.each(features, function(feature) {
                                    feature = feature.trim();
                                    if (feature) { #>
                                        <p class="feature-item">{{{ feature }}}</p>
                                    <# }
                                }); 
                                #>
                            </div>
                        <# } #>
                        
                        <# if (settings.remorque_price_info) { #>
                            <div class="card-price">
                                <span class="price-info">{{{ settings.remorque_price_info }}}</span>
                            </div>
                        <# } #>
                        
                        <# if (settings.remorque_button_url.url) { #>
                            <div class="card-action">
                                <a href="{{ settings.remorque_button_url.url }}" class="card-button btn-remorque">
                                    {{{ settings.remorque_button_text }}}
                                </a>
                            </div>
                        <# } #>
                    </div>
                </div>
            </div>
        </div>
        
        <?php
    }
}
