<?php
/**
 * Widget Elementor Hero Restaurant Block
 * Selon les spécifications : Section "LE RESTAURANT" avec boutons d'action
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
 * Widget Hero Restaurant pour page d'accueil
 */
class Block_Traiteur_Hero_Restaurant_Widget extends Widget_Base {
    
    public function get_name() {
        return 'block-hero-restaurant';
    }
    
    public function get_title() {
        return __('Hero Restaurant Block', 'block-traiteur');
    }
    
    public function get_icon() {
        return 'eicon-header';
    }
    
    public function get_categories() {
        return ['block-traiteur'];
    }
    
    public function get_keywords() {
        return ['block', 'hero', 'restaurant', 'accueil', 'banner'];
    }
    
    protected function register_controls() {
        // Section Restaurant
        $this->start_controls_section(
            'restaurant_section',
            [
                'label' => __('Section Restaurant', 'block-traiteur'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );
        
        $this->add_control(
            'restaurant_title',
            [
                'label' => __('Titre Restaurant', 'block-traiteur'),
                'type' => Controls_Manager::TEXT,
                'default' => __('LE RESTAURANT', 'block-traiteur'),
                'label_block' => true,
            ]
        );
        
        $this->add_control(
            'restaurant_description',
            [
                'label' => __('Description Restaurant', 'block-traiteur'),
                'type' => Controls_Manager::TEXTAREA,
                'default' => __('Découvrez notre restaurant unique au cœur de Strasbourg. Une cuisine de rue revisitée dans un cadre chaleureux et moderne.', 'block-traiteur'),
                'rows' => 4,
            ]
        );
        
        $this->add_control(
            'restaurant_image',
            [
                'label' => __('Image Restaurant', 'block-traiteur'),
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
        
        $this->end_controls_section();
        
        // Section Boutons Restaurant
        $this->start_controls_section(
            'restaurant_buttons_section',
            [
                'label' => __('Boutons Restaurant', 'block-traiteur'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );
        
        $this->add_control(
            'menu_button_text',
            [
                'label' => __('Texte bouton Menu', 'block-traiteur'),
                'type' => Controls_Manager::TEXT,
                'default' => __('Voir le menu', 'block-traiteur'),
            ]
        );
        
        $this->add_control(
            'menu_button_url',
            [
                'label' => __('Lien Menu', 'block-traiteur'),
                'type' => Controls_Manager::URL,
                'placeholder' => __('https://votre-site.com/menu', 'block-traiteur'),
            ]
        );
        
        $this->add_control(
            'reservation_button_text',
            [
                'label' => __('Texte bouton Réservation', 'block-traiteur'),
                'type' => Controls_Manager::TEXT,
                'default' => __('Réserver à table', 'block-traiteur'),
            ]
        );
        
        $this->add_control(
            'reservation_button_url',
            [
                'label' => __('Lien Réservation', 'block-traiteur'),
                'type' => Controls_Manager::URL,
                'placeholder' => __('https://votre-site.com/reservation', 'block-traiteur'),
            ]
        );
        
        $this->end_controls_section();
        
        // Section Traiteur Événementiel
        $this->start_controls_section(
            'traiteur_section',
            [
                'label' => __('Section Traiteur Événementiel', 'block-traiteur'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );
        
        $this->add_control(
            'traiteur_title',
            [
                'label' => __('Titre Traiteur', 'block-traiteur'),
                'type' => Controls_Manager::TEXT,
                'default' => __('LE TRAITEUR ÉVÉNEMENTIEL', 'block-traiteur'),
                'label_block' => true,
            ]
        );
        
        $this->add_control(
            'traiteur_description',
            [
                'label' => __('Description Traiteur', 'block-traiteur'),
                'type' => Controls_Manager::TEXTAREA,
                'default' => __('Privatisez notre restaurant ou louez notre remorque mobile pour vos événements. Une expérience culinaire unique qui s\'adapte à vos besoins.', 'block-traiteur'),
                'rows' => 4,
            ]
        );
        
        $this->end_controls_section();
        
        // Section Cards Services
        $this->start_controls_section(
            'services_cards_section',
            [
                'label' => __('Cards Services', 'block-traiteur'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );
        
        // Card Restaurant
        $this->add_control(
            'card_restaurant_heading',
            [
                'label' => __('Card Privatisation Restaurant', 'block-traiteur'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );
        
        $this->add_control(
            'card_restaurant_title',
            [
                'label' => __('Titre', 'block-traiteur'),
                'type' => Controls_Manager::TEXT,
                'default' => __('Privatisation Restaurant', 'block-traiteur'),
            ]
        );
        
        $this->add_control(
            'card_restaurant_subtitle',
            [
                'label' => __('Sous-titre', 'block-traiteur'),
                'type' => Controls_Manager::TEXT,
                'default' => __('10 à 30 personnes', 'block-traiteur'),
            ]
        );
        
        $this->add_control(
            'card_restaurant_description',
            [
                'label' => __('Description', 'block-traiteur'),
                'type' => Controls_Manager::TEXTAREA,
                'default' => __('Privatisez notre restaurant pour vos événements intimes. Ambiance chaleureuse et cuisine de qualité garanties.', 'block-traiteur'),
                'rows' => 3,
            ]
        );
        
        $this->add_control(
            'card_restaurant_image',
            [
                'label' => __('Image', 'block-traiteur'),
                'type' => Controls_Manager::MEDIA,
                'default' => [
                    'url' => BLOCK_TRAITEUR_PLUGIN_URL . 'assets/images/restaurant-icon.svg',
                ],
            ]
        );
        
        $this->add_control(
            'card_restaurant_button_text',
            [
                'label' => __('Texte bouton', 'block-traiteur'),
                'type' => Controls_Manager::TEXT,
                'default' => __('Privatiser Block', 'block-traiteur'),
            ]
        );
        
        $this->add_control(
            'card_restaurant_button_url',
            [
                'label' => __('Lien bouton', 'block-traiteur'),
                'type' => Controls_Manager::URL,
                'placeholder' => __('https://votre-site.com/devis-restaurant', 'block-traiteur'),
            ]
        );
        
        $this->add_control(
            'card_restaurant_info_text',
            [
                'label' => __('Texte lien infos', 'block-traiteur'),
                'type' => Controls_Manager::TEXT,
                'default' => __('Infos', 'block-traiteur'),
            ]
        );
        
        $this->add_control(
            'card_restaurant_info_url',
            [
                'label' => __('Lien infos', 'block-traiteur'),
                'type' => Controls_Manager::URL,
                'placeholder' => __('https://votre-site.com/infos-restaurant', 'block-traiteur'),
            ]
        );
        
        // Card Remorque
        $this->add_control(
            'card_remorque_heading',
            [
                'label' => __('Card Privatisation Remorque', 'block-traiteur'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );
        
        $this->add_control(
            'card_remorque_title',
            [
                'label' => __('Titre', 'block-traiteur'),
                'type' => Controls_Manager::TEXT,
                'default' => __('Privatisation Remorque', 'block-traiteur'),
            ]
        );
        
        $this->add_control(
            'card_remorque_subtitle',
            [
                'label' => __('Sous-titre', 'block-traiteur'),
                'type' => Controls_Manager::TEXT,
                'default' => __('À partir de 20 personnes', 'block-traiteur'),
            ]
        );
        
        $this->add_control(
            'card_remorque_description',
            [
                'label' => __('Description', 'block-traiteur'),
                'type' => Controls_Manager::TEXTAREA,
                'default' => __('Notre remorque mobile se déplace pour vos événements. Flexibilité et qualité au rendez-vous, où que vous soyez.', 'block-traiteur'),
                'rows' => 3,
            ]
        );
        
        $this->add_control(
            'card_remorque_image',
            [
                'label' => __('Image', 'block-traiteur'),
                'type' => Controls_Manager::MEDIA,
                'default' => [
                    'url' => BLOCK_TRAITEUR_PLUGIN_URL . 'assets/images/remorque-icon.svg',
                ],
            ]
        );
        
        $this->add_control(
            'card_remorque_button_text',
            [
                'label' => __('Texte bouton', 'block-traiteur'),
                'type' => Controls_Manager::TEXT,
                'default' => __('Privatiser Block', 'block-traiteur'),
            ]
        );
        
        $this->add_control(
            'card_remorque_button_url',
            [
                'label' => __('Lien bouton', 'block-traiteur'),
                'type' => Controls_Manager::URL,
                'placeholder' => __('https://votre-site.com/devis-remorque', 'block-traiteur'),
            ]
        );
        
        $this->add_control(
            'card_remorque_info_text',
            [
                'label' => __('Texte lien infos', 'block-traiteur'),
                'type' => Controls_Manager::TEXT,
                'default' => __('Infos', 'block-traiteur'),
            ]
        );
        
        $this->add_control(
            'card_remorque_info_url',
            [
                'label' => __('Lien infos', 'block-traiteur'),
                'type' => Controls_Manager::URL,
                'placeholder' => __('https://votre-site.com/infos-remorque', 'block-traiteur'),
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
                'name' => 'main_title_typography',
                'label' => __('Typographie titre principal', 'block-traiteur'),
                'selector' => '{{WRAPPER}} .hero-main-title',
            ]
        );
        
        $this->add_control(
            'main_title_color',
            [
                'label' => __('Couleur titre principal', 'block-traiteur'),
                'type' => Controls_Manager::COLOR,
                'default' => '#243127',
                'selectors' => [
                    '{{WRAPPER}} .hero-main-title' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'section_title_typography',
                'label' => __('Typographie titres sections', 'block-traiteur'),
                'selector' => '{{WRAPPER}} .section-title',
            ]
        );
        
        $this->add_control(
            'section_title_color',
            [
                'label' => __('Couleur titres sections', 'block-traiteur'),
                'type' => Controls_Manager::COLOR,
                'default' => '#243127',
                'selectors' => [
                    '{{WRAPPER}} .section-title' => 'color: {{VALUE}};',
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
                'label' => __('Arrière-plan card', 'block-traiteur'),
                'types' => ['classic', 'gradient'],
                'selector' => '{{WRAPPER}} .service-card',
            ]
        );
        
        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'card_border',
                'label' => __('Bordure card', 'block-traiteur'),
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
                'label' => __('Ombre card', 'block-traiteur'),
                'selector' => '{{WRAPPER}} .service-card',
            ]
        );
        
        $this->add_responsive_control(
            'card_padding',
            [
                'label' => __('Espacement interne card', 'block-traiteur'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .service-card' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
                'label' => __('Typographie boutons', 'block-traiteur'),
                'selector' => '{{WRAPPER}} .btn',
            ]
        );
        
        $this->start_controls_tabs('button_style_tabs');
        
        // Bouton Principal
        $this->start_controls_tab(
            'button_primary_tab',
            [
                'label' => __('Bouton Principal', 'block-traiteur'),
            ]
        );
        
        $this->add_control(
            'button_primary_color',
            [
                'label' => __('Couleur texte', 'block-traiteur'),
                'type' => Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .btn-primary' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_control(
            'button_primary_background',
            [
                'label' => __('Couleur fond', 'block-traiteur'),
                'type' => Controls_Manager::COLOR,
                'default' => '#243127',
                'selectors' => [
                    '{{WRAPPER}} .btn-primary' => 'background-color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_control(
            'button_primary_hover_color',
            [
                'label' => __('Couleur texte (survol)', 'block-traiteur'),
                'type' => Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .btn-primary:hover' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_control(
            'button_primary_hover_background',
            [
                'label' => __('Couleur fond (survol)', 'block-traiteur'),
                'type' => Controls_Manager::COLOR,
                'default' => '#FFB404',
                'selectors' => [
                    '{{WRAPPER}} .btn-primary:hover' => 'background-color: {{VALUE}};',
                ],
            ]
        );
        
        $this->end_controls_tab();
        
        // Bouton Secondaire
        $this->start_controls_tab(
            'button_secondary_tab',
            [
                'label' => __('Bouton Secondaire', 'block-traiteur'),
            ]
        );
        
        $this->add_control(
            'button_secondary_color',
            [
                'label' => __('Couleur texte', 'block-traiteur'),
                'type' => Controls_Manager::COLOR,
                'default' => '#243127',
                'selectors' => [
                    '{{WRAPPER}} .btn-secondary' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_control(
            'button_secondary_background',
            [
                'label' => __('Couleur fond', 'block-traiteur'),
                'type' => Controls_Manager::COLOR,
                'default' => 'transparent',
                'selectors' => [
                    '{{WRAPPER}} .btn-secondary' => 'background-color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_control(
            'button_secondary_hover_color',
            [
                'label' => __('Couleur texte (survol)', 'block-traiteur'),
                'type' => Controls_Manager::COLOR,
                'default' => '#FFB404',
                'selectors' => [
                    '{{WRAPPER}} .btn-secondary:hover' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        $this->end_controls_tab();
        
        $this->end_controls_tabs();
        
        $this->add_responsive_control(
            'button_padding',
            [
                'label' => __('Espacement interne boutons', 'block-traiteur'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'separator' => 'before',
            ]
        );
        
        $this->add_responsive_control(
            'button_border_radius',
            [
                'label' => __('Rayon bordure boutons', 'block-traiteur'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .btn' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        $this->end_controls_section();
    }
    
    protected function render() {
        $settings = $this->get_settings_for_display();
        ?>
        
        <div class="block-hero-restaurant-widget">
            <!-- Section Restaurant -->
            <section class="hero-restaurant-section">
                <div class="hero-content">
                    <div class="hero-text">
                        <h1 class="hero-main-title section-title"><?php echo esc_html($settings['restaurant_title']); ?></h1>
                        <p class="hero-description"><?php echo esc_html($settings['restaurant_description']); ?></p>
                        
                        <div class="hero-buttons">
                            <?php if (!empty($settings['menu_button_url']['url'])): ?>
                                <a href="<?php echo esc_url($settings['menu_button_url']['url']); ?>" 
                                   class="btn btn-primary"
                                   <?php if ($settings['menu_button_url']['is_external']): ?>target="_blank"<?php endif; ?>
                                   <?php if ($settings['menu_button_url']['nofollow']): ?>rel="nofollow"<?php endif; ?>>
                                    <?php echo esc_html($settings['menu_button_text']); ?>
                                </a>
                            <?php endif; ?>
                            
                            <?php if (!empty($settings['reservation_button_url']['url'])): ?>
                                <a href="<?php echo esc_url($settings['reservation_button_url']['url']); ?>" 
                                   class="btn btn-secondary"
                                   <?php if ($settings['reservation_button_url']['is_external']): ?>target="_blank"<?php endif; ?>
                                   <?php if ($settings['reservation_button_url']['nofollow']): ?>rel="nofollow"<?php endif; ?>>
                                    <?php echo esc_html($settings['reservation_button_text']); ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="hero-image">
                        <?php if (!empty($settings['restaurant_image']['url'])): ?>
                            <?php echo Group_Control_Image_Size::get_attachment_image_html($settings, 'restaurant_image', 'restaurant_image'); ?>
                        <?php endif; ?>
                    </div>
                </div>
            </section>
            
            <!-- Section Traiteur Événementiel -->
            <section class="hero-traiteur-section">
                <h2 class="section-title"><?php echo esc_html($settings['traiteur_title']); ?></h2>
                <p class="section-description"><?php echo esc_html($settings['traiteur_description']); ?></p>
                
                <!-- Cards Services -->
                <div class="services-cards">
                    <!-- Card Restaurant -->
                    <div class="service-card card-restaurant">
                        <?php if (!empty($settings['card_restaurant_image']['url'])): ?>
                            <div class="card-image">
                                <img src="<?php echo esc_url($settings['card_restaurant_image']['url']); ?>" 
                                     alt="<?php echo esc_attr($settings['card_restaurant_title']); ?>">
                            </div>
                        <?php endif; ?>
                        
                        <div class="card-content">
                            <h3 class="card-title"><?php echo esc_html($settings['card_restaurant_title']); ?></h3>
                            <p class="card-subtitle"><?php echo esc_html($settings['card_restaurant_subtitle']); ?></p>
                            <p class="card-description"><?php echo esc_html($settings['card_restaurant_description']); ?></p>
                            
                            <div class="card-actions">
                                <?php if (!empty($settings['card_restaurant_button_url']['url'])): ?>
                                    <a href="<?php echo esc_url($settings['card_restaurant_button_url']['url']); ?>" 
                                       class="btn btn-primary card-btn-main"
                                       <?php if ($settings['card_restaurant_button_url']['is_external']): ?>target="_blank"<?php endif; ?>
                                       <?php if ($settings['card_restaurant_button_url']['nofollow']): ?>rel="nofollow"<?php endif; ?>>
                                        <?php echo esc_html($settings['card_restaurant_button_text']); ?>
                                    </a>
                                <?php endif; ?>
                                
                                <?php if (!empty($settings['card_restaurant_info_url']['url'])): ?>
                                    <a href="<?php echo esc_url($settings['card_restaurant_info_url']['url']); ?>" 
                                       class="btn btn-secondary card-btn-info"
                                       <?php if ($settings['card_restaurant_info_url']['is_external']): ?>target="_blank"<?php endif; ?>
                                       <?php if ($settings['card_restaurant_info_url']['nofollow']): ?>rel="nofollow"<?php endif; ?>>
                                        <?php echo esc_html($settings['card_restaurant_info_text']); ?>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Card Remorque -->
                    <div class="service-card card-remorque">
                        <?php if (!empty($settings['card_remorque_image']['url'])): ?>
                            <div class="card-image">
                                <img src="<?php echo esc_url($settings['card_remorque_image']['url']); ?>" 
                                     alt="<?php echo esc_attr($settings['card_remorque_title']); ?>">
                            </div>
                        <?php endif; ?>
                        
                        <div class="card-content">
                            <h3 class="card-title"><?php echo esc_html($settings['card_remorque_title']); ?></h3>
                            <p class="card-subtitle"><?php echo esc_html($settings['card_remorque_subtitle']); ?></p>
                            <p class="card-description"><?php echo esc_html($settings['card_remorque_description']); ?></p>
                            
                            <div class="card-actions">
                                <?php if (!empty($settings['card_remorque_button_url']['url'])): ?>
                                    <a href="<?php echo esc_url($settings['card_remorque_button_url']['url']); ?>" 
                                       class="btn btn-primary card-btn-main"
                                       <?php if ($settings['card_remorque_button_url']['is_external']): ?>target="_blank"<?php endif; ?>
                                       <?php if ($settings['card_remorque_button_url']['nofollow']): ?>rel="nofollow"<?php endif; ?>>
                                        <?php echo esc_html($settings['card_remorque_button_text']); ?>
                                    </a>
                                <?php endif; ?>
                                
                                <?php if (!empty($settings['card_remorque_info_url']['url'])): ?>
                                    <a href="<?php echo esc_url($settings['card_remorque_info_url']['url']); ?>" 
                                       class="btn btn-secondary card-btn-info"
                                       <?php if ($settings['card_remorque_info_url']['is_external']): ?>target="_blank"<?php endif; ?>
                                       <?php if ($settings['card_remorque_info_url']['nofollow']): ?>rel="nofollow"<?php endif; ?>>
                                        <?php echo esc_html($settings['card_remorque_info_text']); ?>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
        
        <?php
    }
    
    protected function content_template() {
        ?>
        <#
        var restaurantImageUrl = settings.restaurant_image.url || '';
        var cardRestaurantImageUrl = settings.card_restaurant_image.url || '';
        var cardRemorqueImageUrl = settings.card_remorque_image.url || '';
        #>
        
        <div class="block-hero-restaurant-widget">
            <!-- Section Restaurant -->
            <section class="hero-restaurant-section">
                <div class="hero-content">
                    <div class="hero-text">
                        <h1 class="hero-main-title section-title">{{{ settings.restaurant_title }}}</h1>
                        <p class="hero-description">{{{ settings.restaurant_description }}}</p>
                        
                        <div class="hero-buttons">
                            <# if (settings.menu_button_url.url) { #>
                                <a href="{{ settings.menu_button_url.url }}" class="btn btn-primary">
                                    {{{ settings.menu_button_text }}}
                                </a>
                            <# } #>
                            
                            <# if (settings.reservation_button_url.url) { #>
                                <a href="{{ settings.reservation_button_url.url }}" class="btn btn-secondary">
                                    {{{ settings.reservation_button_text }}}
                                </a>
                            <# } #>
                        </div>
                    </div>
                    
                    <div class="hero-image">
                        <# if (restaurantImageUrl) { #>
                            <img src="{{ restaurantImageUrl }}" alt="{{{ settings.restaurant_title }}}">
                        <# } #>
                    </div>
                </div>
            </section>
            
            <!-- Section Traiteur Événementiel -->
            <section class="hero-traiteur-section">
                <h2 class="section-title">{{{ settings.traiteur_title }}}</h2>
                <p class="section-description">{{{ settings.traiteur_description }}}</p>
                
                <!-- Cards Services -->
                <div class="services-cards">
                    <!-- Card Restaurant -->
                    <div class="service-card card-restaurant">
                        <# if (cardRestaurantImageUrl) { #>
                            <div class="card-image">
                                <img src="{{ cardRestaurantImageUrl }}" alt="{{{ settings.card_restaurant_title }}}">
                            </div>
                        <# } #>
                        
                        <div class="card-content">
                            <h3 class="card-title">{{{ settings.card_restaurant_title }}}</h3>
                            <p class="card-subtitle">{{{ settings.card_restaurant_subtitle }}}</p>
                            <p class="card-description">{{{ settings.card_restaurant_description }}}</p>
                            
                            <div class="card-actions">
                                <# if (settings.card_restaurant_button_url.url) { #>
                                    <a href="{{ settings.card_restaurant_button_url.url }}" class="btn btn-primary card-btn-main">
                                        {{{ settings.card_restaurant_button_text }}}
                                    </a>
                                <# } #>
                                
                                <# if (settings.card_restaurant_info_url.url) { #>
                                    <a href="{{ settings.card_restaurant_info_url.url }}" class="btn btn-secondary card-btn-info">
                                        {{{ settings.card_restaurant_info_text }}}
                                    </a>
                                <# } #>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Card Remorque -->
                    <div class="service-card card-remorque">
                        <# if (cardRemorqueImageUrl) { #>
                            <div class="card-image">
                                <img src="{{ cardRemorqueImageUrl }}" alt="{{{ settings.card_remorque_title }}}">
                            </div>
                        <# } #>
                        
                        <div class="card-content">
                            <h3 class="card-title">{{{ settings.card_remorque_title }}}</h3>
                            <p class="card-subtitle">{{{ settings.card_remorque_subtitle }}}</p>
                            <p class="card-description">{{{ settings.card_remorque_description }}}</p>
                            
                            <div class="card-actions">
                                <# if (settings.card_remorque_button_url.url) { #>
                                    <a href="{{ settings.card_remorque_button_url.url }}" class="btn btn-primary card-btn-main">
                                        {{{ settings.card_remorque_button_text }}}
                                    </a>
                                <# } #>
                                
                                <# if (settings.card_remorque_info_url.url) { #>
                                    <a href="{{ settings.card_remorque_info_url.url }}" class="btn btn-secondary card-btn-info">
                                        {{{ settings.card_remorque_info_text }}}
                                    </a>
                                <# } #>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
        
        <?php
    }
}
