<?php
/**
 * Étape 4: Boissons (optionnel)
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="step-header">
    <h3><?php _e('Sélection des boissons', 'block-traiteur'); ?></h3>
    <p><?php _e('Complétez votre événement avec notre sélection de boissons (optionnel)', 'block-traiteur'); ?></p>
</div>

<div class="beverages-section">
    <!-- Navigation par catégories -->
    <div class="beverage-tabs">
        <button type="button" class="tab-btn active" data-category="softs">
            <span class="tab-icon">🥤</span>
            <?php _e('Softs', 'block-traiteur'); ?>
        </button>
        <button type="button" class="tab-btn" data-category="vins">
            <span class="tab-icon">🍷</span>
            <?php _e('Vins', 'block-traiteur'); ?>
        </button>
        <button type="button" class="tab-btn" data-category="bieres">
            <span class="tab-icon">🍺</span>
            <?php _e('Bières', 'block-traiteur'); ?>
        </button>
        <button type="button" class="tab-btn" data-category="futs" data-service="remorque">
            <span class="tab-icon">🍻</span>
            <?php _e('Fûts', 'block-traiteur'); ?>
        </button>
    </div>
    
    <!-- Contenu des catégories -->
    <div class="beverages-content">
        <!-- Softs -->
        <div class="beverage-category active" data-category="softs">
            <div class="category-header">
                <h4><?php _e('Boissons sans alcool', 'block-traiteur'); ?></h4>
                <p><?php _e('Rafraîchissements et boissons chaudes', 'block-traiteur'); ?></p>
            </div>
            
            <div class="beverages-grid" id="softs-beverages">
                <!-- Chargé dynamiquement via AJAX -->
            </div>
        </div>
        
        <!-- Vins -->
        <div class="beverage-category" data-category="vins">
            <div class="category-header">
                <h4><?php _e('Sélection de vins', 'block-traiteur'); ?></h4>
                <p><?php _e('Découvrez notre carte des vins soigneusement sélectionnés', 'block-traiteur'); ?></p>
            </div>
            
            <div class="wine-subcategories">
                <div class="wine-tabs">
                    <button type="button" class="wine-tab active" data-wine-type="blanc">
                        <?php _e('Blancs', 'block-traiteur'); ?>
                    </button>
                    <button type="button" class="wine-tab" data-wine-type="rouge">
                        <?php _e('Rouges', 'block-traiteur'); ?>
                    </button>
                    <button type="button" class="wine-tab" data-wine-type="rose">
                        <?php _e('Rosés', 'block-traiteur'); ?>
                    </button>
                    <button type="button" class="wine-tab" data-wine-type="cremant">
                        <?php _e('Crémants', 'block-traiteur'); ?>
                    </button>
                </div>
                
                <div class="wine-content">
                    <div class="wine-type active" data-wine-type="blanc">
                        <div class="beverages-grid" id="vins-blanc-beverages">
                            <!-- Chargé dynamiquement -->
                        </div>
                    </div>
                    
                    <div class="wine-type" data-wine-type="rouge">
                        <div class="suggestion-highlight">
                            <h5><?php _e('🌟 Suggestion du moment', 'block-traiteur'); ?></h5>
                        </div>
                        <div class="beverages-grid" id="vins-rouge-beverages">
                            <!-- Chargé dynamiquement -->
                        </div>
                    </div>
                    
                    <div class="wine-type" data-wine-type="rose">
                        <div class="beverages-grid" id="vins-rose-beverages">
                            <!-- Chargé dynamiquement -->
                        </div>
                    </div>
                    
                    <div class="wine-type" data-wine-type="cremant">
                        <div class="beverages-grid" id="vins-cremant-beverages">
                            <!-- Chargé dynamiquement -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Bières -->
        <div class="beverage-category" data-category="bieres">
            <div class="category-header">
                <h4><?php _e('Bières artisanales', 'block-traiteur'); ?></h4>
                <p><?php _e('Une sélection de bières locales et artisanales', 'block-traiteur'); ?></p>
            </div>
            
            <div class="beer-subcategories">
                <div class="beer-tabs">
                    <button type="button" class="beer-tab active" data-beer-type="blonde">
                        <?php _e('Blondes', 'block-traiteur'); ?>
                    </button>
                    <button type="button" class="beer-tab" data-beer-type="blanche">
                        <?php _e('Blanches', 'block-traiteur'); ?>
                    </button>
                    <button type="button" class="beer-tab" data-beer-type="ipa">
                        <?php _e('IPA', 'block-traiteur'); ?>
                    </button>
                    <button type="button" class="beer-tab" data-beer-type="ambree">
                        <?php _e('Ambrées', 'block-traiteur'); ?>
                    </button>
                </div>
                
                <div class="beer-content">
                    <div class="beer-type active" data-beer-type="blonde">
                        <div class="beverages-grid" id="bieres-blonde-beverages">
                            <!-- Chargé dynamiquement -->
                        </div>
                    </div>
                    
                    <div class="beer-type" data-beer-type="blanche">
                        <div class="beverages-grid" id="bieres-blanche-beverages">
                            <!-- Chargé dynamiquement -->
                        </div>
                    </div>
                    
                    <div class="beer-type" data-beer-type="ipa">
                        <div class="beverages-grid" id="bieres-ipa-beverages">
                            <!-- Chargé dynamiquement -->
                        </div>
                    </div>
                    
                    <div class="beer-type" data-beer-type="ambree">
                        <div class="beverages-grid" id="bieres-ambree-beverages">
                            <!-- Chargé dynamiquement -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Fûts (remorque uniquement) -->
        <div class="beverage-category" data-category="futs">
            <div class="category-header">
                <h4><?php _e('Fûts de bière', 'block-traiteur'); ?></h4>
                <p><?php _e('Pour les grands événements avec notre remorque (20L et 30L)', 'block-traiteur'); ?></p>
            </div>
            
            <div class="futs-info">
                <div class="info-card">
                    <h5><?php _e('📋 Informations importantes', 'block-traiteur'); ?></h5>
                    <ul>
                        <li><?php _e('Disponible uniquement avec la remorque', 'block-traiteur'); ?></li>
                        <li><?php _e('Fûts de 20L (≈ 40 pressions) et 30L (≈ 60 pressions)', 'block-traiteur'); ?></li>
                        <li><?php _e('Installation de tireuse incluse dans les options', 'block-traiteur'); ?></li>
                        <li><?php _e('Service par notre équipe', 'block-traiteur'); ?></li>
                    </ul>
                </div>
            </div>
            
            <div class="futs-grid">
                <div class="fut-sizes">
                    <div class="fut-size-option">
                        <h6><?php _e('Fût 20L', 'block-traiteur'); ?></h6>
                        <p><?php _e('≈ 40 pressions', 'block-traiteur'); ?></p>
                        <div class="beverages-grid" id="futs-20l-beverages">
                            <!-- Chargé dynamiquement -->
                        </div>
                    </div>
                    
                    <div class="fut-size-option">
                        <h6><?php _e('Fût 30L', 'block-traiteur'); ?></h6>
                        <p><?php _e('≈ 60 pressions', 'block-traiteur'); ?></p>
                        <div class="beverages-grid" id="futs-30l-beverages">
                            <!-- Chargé dynamiquement -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Skip option -->
    <div class="skip-beverages">
        <p><?php _e('Pas de boissons pour le moment ?', 'block-traiteur'); ?></p>
        <button type="button" class="btn btn-outline skip-step">
            <?php _e('Passer cette étape', 'block-traiteur'); ?>
        </button>
    </div>
</div>

<!-- Récapitulatif boissons -->
<div class="beverages-summary" id="beverages-summary" style="display: none;">
    <h4><?php _e('Vos boissons sélectionnées', 'block-traiteur'); ?></h4>
    <div class="selected-beverages">
        <!-- Rempli dynamiquement -->
    </div>
    
    <div class="beverages-total">
        <span class="label"><?php _e('Total boissons :', 'block-traiteur'); ?></span>
        <span class="amount" id="beverages-total-amount">0 € TTC</span>
    </div>
</div>