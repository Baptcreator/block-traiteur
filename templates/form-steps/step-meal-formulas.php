<?php
/**
 * Étape 2: Formules repas - RESTRUCTURÉE selon les spécifications
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="step-content">
    <div class="step-header">
        <h3><?php _e('Choisissez vos formules repas', 'block-traiteur'); ?></h3>
        <p><?php _e('Sélectionnez les plats signature et accompagnements pour votre événement', 'block-traiteur'); ?></p>
    </div>

    <div class="meal-formulas">
        <!-- 1. Choix du plat signature -->
        <div class="formula-section signature-choice">
            <h4><?php _e('1. Choix du plat signature', 'block-traiteur'); ?> <span class="required">*</span></h4>
            <p class="section-help"><?php _e('Minimum 1 recette par personne requis', 'block-traiteur'); ?></p>
            
            <div class="signature-selector">
                <label class="signature-option">
                    <input type="radio" name="signature_type" value="dog" id="signature-dog">
                    <span class="signature-label">DOG</span>
                </label>
                <span class="or-separator"><?php _e('ou', 'block-traiteur'); ?></span>
                <label class="signature-option">
                    <input type="radio" name="signature_type" value="croq" id="signature-croq">
                    <span class="signature-label">CROQ</span>
                </label>
            </div>
            
            <p class="signature-note">
                <?php _e('PS = min 1/personne. Attention : il manque des recettes si vous n\'atteignez pas au moins 1 recette par convive !', 'block-traiteur'); ?>
            </p>
        </div>
        
        <!-- 2. Choix des recettes (affiché selon le choix signature) -->
        <div class="formula-section recipes-section" id="recipes-section" style="display: none;">
            <h4><?php _e('2. Choix des recettes', 'block-traiteur'); ?> <span class="required">*</span></h4>
            <p class="section-help"><?php _e('Les recettes sont détaillées en dessous de chaque choix', 'block-traiteur'); ?></p>
            
            <div class="recipes-container">
                <!-- Recettes DOG -->
                <div class="recipe-category" data-category="dog" id="dog-section" style="display: none;">
                    <div class="products-grid" id="dog-products">
                        <!-- Chargé dynamiquement -->
                    </div>
                </div>
                
                <!-- Recettes CROQ -->
                <div class="recipe-category" data-category="croq" id="croq-section" style="display: none;">
                    <div class="products-grid" id="croq-products">
                        <!-- Chargé dynamiquement -->
                    </div>
                </div>
            </div>
        </div>
        
        <!-- 3. Menu Mini Boss -->
        <div class="formula-section">
            <h4><?php _e('3. Menu Mini Boss', 'block-traiteur'); ?> <span class="price-info">8€</span></h4>
            <p class="section-help"><?php _e('Portions réduites pour accompagner', 'block-traiteur'); ?></p>
            
            <div class="mini-boss-grid" id="mini-boss-products">
                <!-- Chargé dynamiquement depuis la catégorie mini_boss -->
            </div>
        </div>
        
        <!-- 4. Choix de l'accompagnement -->
        <div class="formula-section">
            <h4><?php _e('4. Choix de l\'accompagnement', 'block-traiteur'); ?> <span class="price-info">4€</span></h4>
            <p class="section-help"><?php _e('Minimum 1 accompagnement par personne', 'block-traiteur'); ?></p>
            
            <div class="accompaniments-grid" id="accompaniment-products">
                <!-- Chargé dynamiquement -->
            </div>
            
            <!-- Section sauces conditionnelle pour frites -->
            <div class="sauces-section" id="frites-sauces" style="display: none;">
                <h5><?php _e('Si frites : choix de la sauce', 'block-traiteur'); ?> <span class="required">*</span></h5>
                <div class="sauces-grid">
                    <label class="sauce-option">
                        <input type="checkbox" name="sauces[]" value="mayo">
                        <span class="sauce-name"><?php _e('Mayonnaise', 'block-traiteur'); ?></span>
                    </label>
                    <label class="sauce-option">
                        <input type="checkbox" name="sauces[]" value="ketchup">
                        <span class="sauce-name"><?php _e('Ketchup', 'block-traiteur'); ?></span>
                    </label>
                    <label class="sauce-option">
                        <input type="checkbox" name="sauces[]" value="curry">
                        <span class="sauce-name"><?php _e('Curry', 'block-traiteur'); ?></span>
                    </label>
                    <label class="sauce-option">
                        <input type="checkbox" name="sauces[]" value="andalouse">
                        <span class="sauce-name"><?php _e('Andalouse', 'block-traiteur'); ?></span>
                    </label>
                    <label class="sauce-option">
                        <input type="checkbox" name="sauces[]" value="barbecue">
                        <span class="sauce-name"><?php _e('Barbecue', 'block-traiteur'); ?></span>
                    </label>
                </div>
                
                <!-- Option sauce chimichurri avec supplément -->
                <div class="sauce-premium">
                    <label class="sauce-option premium">
                        <input type="checkbox" name="sauces[]" value="chimichurri" data-supplement="1">
                        <span class="sauce-name"><?php _e('Option enrobée sauce Chimichurri', 'block-traiteur'); ?> <span class="supplement">+1€</span></span>
                    </label>
                </div>
            </div>
        </div>
        
        <!-- Résumé de sélection -->
        <div class="selection-summary">
            <h4><?php _e('Votre sélection', 'block-traiteur'); ?></h4>
            <div class="selected-items" id="meal-summary">
                <p class="no-selection"><?php _e('Aucun produit sélectionné', 'block-traiteur'); ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Validation rules hidden inputs -->
<input type="hidden" id="min-recipes-per-person" value="1">
<input type="hidden" id="guest-count-for-validation" value="">

<script>
// Script pour gérer la logique de sélection DOG/CROQ
document.addEventListener('DOMContentLoaded', function() {
    const dogRadio = document.getElementById('signature-dog');
    const croqRadio = document.getElementById('signature-croq');
    const recipesSection = document.getElementById('recipes-section');
    const dogSection = document.getElementById('dog-section');
    const croqSection = document.getElementById('croq-section');
    
    function showRecipeSection(type) {
        recipesSection.style.display = 'block';
        
        if (type === 'dog') {
            dogSection.style.display = 'block';
            croqSection.style.display = 'none';
            // Charger les produits DOG
            if (window.BlockQuoteForm && window.BlockQuoteForm.prototype.loadProducts) {
                const form = $('.block-quote-form').data('block-form-instance');
                if (form) {
                    form.loadProducts('dog', 'dog-products');
                }
            }
        } else if (type === 'croq') {
            dogSection.style.display = 'none';
            croqSection.style.display = 'block';
            // Charger les produits CROQ
            if (window.BlockQuoteForm && window.BlockQuoteForm.prototype.loadProducts) {
                const form = $('.block-quote-form').data('block-form-instance');
                if (form) {
                    form.loadProducts('croq', 'croq-products');
                }
            }
        }
    }
    
    if (dogRadio) {
        dogRadio.addEventListener('change', function() {
            if (this.checked) {
                showRecipeSection('dog');
            }
        });
    }
    
    if (croqRadio) {
        croqRadio.addEventListener('change', function() {
            if (this.checked) {
                showRecipeSection('croq');
            }
        });
    }
    
    // Gérer l'affichage des sauces si frites sélectionnées
    $(document).on('change', 'input[data-name*="frites"], input[data-name*="Frites"]', function() {
        const fritesSelected = $(this).val() > 0;
        const saucesSection = $('#frites-sauces');
        
        if (fritesSelected) {
            saucesSection.show();
        } else {
            saucesSection.hide();
            // Décocher toutes les sauces
            saucesSection.find('input[type="checkbox"]').prop('checked', false);
        }
    });
});
</script>