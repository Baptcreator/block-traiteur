<?php
/**
 * Étape 2: Formules repas - SELON SPÉCIFICATIONS CLIENT
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="step-content">
    <div class="step-header">
        <h3><?php _e('Choix des formules repas', 'block-traiteur'); ?></h3>
        <p><?php _e('Sélectionnez les plats signature et accompagnements pour votre événement', 'block-traiteur'); ?></p>
    </div>

    <div class="meal-formulas">
        <!-- 1. Choix du plat signature (OBLIGATOIRE) -->
        <div class="formula-section signature-choice">
            <h4><?php _e('1. Choix du plat signature', 'block-traiteur'); ?> <span class="required">*</span></h4>
            <p class="section-help"><?php _e('Minimum 1 recette par personne requis', 'block-traiteur'); ?></p>
            
            <div class="signature-selector">
                <label class="signature-option">
                    <input type="radio" name="signature_type" value="dog" id="signature-dog" required>
                    <span class="signature-label">DOG</span>
                </label>
                <span class="or-separator"><?php _e('ou', 'block-traiteur'); ?></span>
                <label class="signature-option">
                    <input type="radio" name="signature_type" value="croq" id="signature-croq" required>
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
            <p class="section-help"><?php _e('Sélectionnez la quantité pour chaque recette. Les recettes sont détaillées en dessous de chaque choix.', 'block-traiteur'); ?></p>
            
            <!-- Recettes DOG -->
            <div class="recipes-group dog-recipes" id="dog-recipes" style="display: none;">
                <div class="recipe-item">
                    <div class="recipe-info">
                        <h5>Classic Dog</h5>
                        <div class="recipe-price">8€</div>
                        <div class="recipe-description">Hot dog classique avec saucisse de qualité, pain brioche, oignons confits et sauce maison</div>
                    </div>
                    <div class="recipe-quantity">
                        <button type="button" class="qty-btn decrease" data-target="dog-classic">-</button>
                        <input type="number" id="dog-classic" name="recipes[dog-classic]" value="0" min="0" class="qty-input">
                        <button type="button" class="qty-btn increase" data-target="dog-classic">+</button>
                    </div>
                </div>
                
                <div class="recipe-item">
                    <div class="recipe-info">
                        <h5>Spicy Dog</h5>
                        <div class="recipe-price">8.50€</div>
                        <div class="recipe-description">Hot dog épicé avec saucisse chorizo, jalapeños, sauce piquante maison</div>
                    </div>
                    <div class="recipe-quantity">
                        <button type="button" class="qty-btn decrease" data-target="dog-spicy">-</button>
                        <input type="number" id="dog-spicy" name="recipes[dog-spicy]" value="0" min="0" class="qty-input">
                        <button type="button" class="qty-btn increase" data-target="dog-spicy">+</button>
                    </div>
                </div>
                
                <div class="recipe-item">
                    <div class="recipe-info">
                        <h5>Veggie Dog</h5>
                        <div class="recipe-price">8€</div>
                        <div class="recipe-description">Version végétarienne avec saucisse végétale, légumes grillés, sauce verte</div>
                    </div>
                    <div class="recipe-quantity">
                        <button type="button" class="qty-btn decrease" data-target="dog-veggie">-</button>
                        <input type="number" id="dog-veggie" name="recipes[dog-veggie]" value="0" min="0" class="qty-input">
                        <button type="button" class="qty-btn increase" data-target="dog-veggie">+</button>
                    </div>
                </div>
                
                <div class="recipe-item">
                    <div class="recipe-info">
                        <h5>Premium Dog</h5>
                        <div class="recipe-price">9€</div>
                        <div class="recipe-description">Hot dog premium avec saucisse artisanale, pain spécial, garniture gourmet</div>
                    </div>
                    <div class="recipe-quantity">
                        <button type="button" class="qty-btn decrease" data-target="dog-premium">-</button>
                        <input type="number" id="dog-premium" name="recipes[dog-premium]" value="0" min="0" class="qty-input">
                        <button type="button" class="qty-btn increase" data-target="dog-premium">+</button>
                    </div>
                </div>
            </div>
            
            <!-- Recettes CROQ -->
            <div class="recipes-group croq-recipes" id="croq-recipes" style="display: none;">
                <div class="recipe-item">
                    <div class="recipe-info">
                        <h5>Croq Classic</h5>
                        <div class="recipe-price">7.50€</div>
                        <div class="recipe-description">Croque-monsieur traditionnel jambon-fromage, pain de mie, béchamel</div>
                    </div>
                    <div class="recipe-quantity">
                        <button type="button" class="qty-btn decrease" data-target="croq-classic">-</button>
                        <input type="number" id="croq-classic" name="recipes[croq-classic]" value="0" min="0" class="qty-input">
                        <button type="button" class="qty-btn increase" data-target="croq-classic">+</button>
                    </div>
                </div>
                
                <div class="recipe-item">
                    <div class="recipe-info">
                        <h5>Croq Madame</h5>
                        <div class="recipe-price">8€</div>
                        <div class="recipe-description">Croque-madame avec œuf au plat, jambon, fromage, béchamel</div>
                    </div>
                    <div class="recipe-quantity">
                        <button type="button" class="qty-btn decrease" data-target="croq-madame">-</button>
                        <input type="number" id="croq-madame" name="recipes[croq-madame]" value="0" min="0" class="qty-input">
                        <button type="button" class="qty-btn increase" data-target="croq-madame">+</button>
                    </div>
                </div>
                
                <div class="recipe-item">
                    <div class="recipe-info">
                        <h5>Croq Végé</h5>
                        <div class="recipe-price">7.50€</div>
                        <div class="recipe-description">Version végétarienne avec légumes grillés, fromage, sauce verte</div>
                    </div>
                    <div class="recipe-quantity">
                        <button type="button" class="qty-btn decrease" data-target="croq-vege">-</button>
                        <input type="number" id="croq-vege" name="recipes[croq-vege]" value="0" min="0" class="qty-input">
                        <button type="button" class="qty-btn increase" data-target="croq-vege">+</button>
                    </div>
                </div>
                
                <div class="recipe-item">
                    <div class="recipe-info">
                        <h5>Croq Gourmet</h5>
                        <div class="recipe-price">8.50€</div>
                        <div class="recipe-description">Croque gourmet avec jambon de Parme, fromage affiné, truffe</div>
                    </div>
                    <div class="recipe-quantity">
                        <button type="button" class="qty-btn decrease" data-target="croq-gourmet">-</button>
                        <input type="number" id="croq-gourmet" name="recipes[croq-gourmet]" value="0" min="0" class="qty-input">
                        <button type="button" class="qty-btn increase" data-target="croq-gourmet">+</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Menu des Mini Boss -->
        <div class="formula-section mini-boss-section">
            <h4><?php _e('Le menu des Mini Boss', 'block-traiteur'); ?> <span class="price-tag">8€</span></h4>
            <p class="section-help"><?php _e('Menu spécial pour les plus jeunes', 'block-traiteur'); ?></p>
            
            <div class="mini-boss-items">
                <div class="recipe-item">
                    <div class="recipe-info">
                        <h5>Mini Dog</h5>
                        <div class="recipe-description">Hot dog adapté aux enfants, saucisse douce, pain moelleux</div>
                    </div>
                    <div class="recipe-quantity">
                        <button type="button" class="qty-btn decrease" data-target="mini-dog">-</button>
                        <input type="number" id="mini-dog" name="recipes[mini-dog]" value="0" min="0" class="qty-input">
                        <button type="button" class="qty-btn increase" data-target="mini-dog">+</button>
                    </div>
                </div>
                
                <div class="recipe-item">
                    <div class="recipe-info">
                        <h5>Mini Croq</h5>
                        <div class="recipe-description">Croque-monsieur format enfant, jambon-fromage</div>
                    </div>
                    <div class="recipe-quantity">
                        <button type="button" class="qty-btn decrease" data-target="mini-croq">-</button>
                        <input type="number" id="mini-croq" name="recipes[mini-croq]" value="0" min="0" class="qty-input">
                        <button type="button" class="qty-btn increase" data-target="mini-croq">+</button>
                    </div>
                </div>
                
                <div class="recipe-item">
                    <div class="recipe-info">
                        <h5>Nuggets Block</h5>
                        <div class="recipe-description">Nuggets de poulet maison avec sauce douce</div>
                    </div>
                    <div class="recipe-quantity">
                        <button type="button" class="qty-btn decrease" data-target="mini-nuggets">-</button>
                        <input type="number" id="mini-nuggets" name="recipes[mini-nuggets]" value="0" min="0" class="qty-input">
                        <button type="button" class="qty-btn increase" data-target="mini-nuggets">+</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. Choix de l'accompagnement (OBLIGATOIRE) -->
        <div class="formula-section accompaniment-section">
            <h4><?php _e('3. Choix de l\'accompagnement', 'block-traiteur'); ?> <span class="price-tag">4€</span> <span class="required">*</span></h4>
            <p class="section-help"><?php _e('Accompagnement minimum 1/personne', 'block-traiteur'); ?></p>
            
            <div class="accompaniment-choice">
                <div class="accompaniment-item">
                    <label class="accompaniment-option">
                        <input type="radio" name="accompaniment_type" value="salade" required>
                        <div class="option-content">
                            <h5>Salade</h5>
                            <div class="option-description">Salade fraîche de saison avec vinaigrette maison</div>
                        </div>
                    </label>
                    <div class="recipe-quantity">
                        <button type="button" class="qty-btn decrease" data-target="acc-salade">-</button>
                        <input type="number" id="acc-salade" name="accompaniments[salade]" value="0" min="0" class="qty-input">
                        <button type="button" class="qty-btn increase" data-target="acc-salade">+</button>
                    </div>
                </div>
                
                <div class="accompaniment-item">
                    <label class="accompaniment-option">
                        <input type="radio" name="accompaniment_type" value="frites" required>
                        <div class="option-content">
                            <h5>Frites</h5>
                            <div class="option-description">Frites fraîches maison, croustillantes à souhait</div>
                        </div>
                    </label>
                    <div class="recipe-quantity">
                        <button type="button" class="qty-btn decrease" data-target="acc-frites">-</button>
                        <input type="number" id="acc-frites" name="accompaniments[frites]" value="0" min="0" class="qty-input">
                        <button type="button" class="qty-btn increase" data-target="acc-frites">+</button>
                    </div>
                </div>
            </div>
            
            <!-- Options pour les frites -->
            <div class="frites-options" id="frites-options" style="display: none;">
                <div class="frites-sauce-option">
                    <label class="checkbox-option">
                        <input type="checkbox" name="frites_chimichuri" value="1">
                        <span class="option-text">Option enrobée sauce chimichuri</span>
                        <span class="option-price">+1€</span>
                    </label>
                </div>
                
                <div class="sauce-choice">
                    <h5><?php _e('Choix de la sauce', 'block-traiteur'); ?></h5>
                    <div class="sauce-options">
                        <label class="sauce-option">
                            <input type="radio" name="frites_sauce" value="ketchup">
                            <span>Ketchup maison</span>
                        </label>
                        <label class="sauce-option">
                            <input type="radio" name="frites_sauce" value="mayo">
                            <span>Mayonnaise</span>
                        </label>
                        <label class="sauce-option">
                            <input type="radio" name="frites_sauce" value="barbecue">
                            <span>Barbecue</span>
                        </label>
                        <label class="sauce-option">
                            <input type="radio" name="frites_sauce" value="andalouse">
                            <span>Andalouse</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Compteur de recettes -->
    <div class="recipe-counter">
        <div class="counter-info">
            <span class="counter-text">
                <span id="total-recipes">0</span> recettes sélectionnées / 
                <span id="required-recipes" class="guest-count">0</span> requises
            </span>
            <div class="counter-status" id="recipe-status">
                <span class="status-icon">⚠️</span>
                <span class="status-text">Il manque des recettes</span>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gestion du choix de signature
    const signatureRadios = document.querySelectorAll('input[name="signature_type"]');
    const recipesSection = document.getElementById('recipes-section');
    const dogRecipes = document.getElementById('dog-recipes');
    const croqRecipes = document.getElementById('croq-recipes');
    
    signatureRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            recipesSection.style.display = 'block';
            
            if (this.value === 'dog') {
                dogRecipes.style.display = 'block';
                croqRecipes.style.display = 'none';
            } else {
                croqRecipes.style.display = 'block';
                dogRecipes.style.display = 'none';
            }
            
            updateRecipeCounter();
        });
    });
    
    // Gestion de l'accompagnement
    const accompanimentRadios = document.querySelectorAll('input[name="accompaniment_type"]');
    const fritesOptions = document.getElementById('frites-options');
    
    accompanimentRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'frites') {
                fritesOptions.style.display = 'block';
            } else {
                fritesOptions.style.display = 'none';
            }
        });
    });
    
    // Gestion des boutons quantité
    document.querySelectorAll('.qty-btn').forEach(button => {
        button.addEventListener('click', function() {
            const target = this.getAttribute('data-target');
            const input = document.getElementById(target);
            const isIncrease = this.classList.contains('increase');
            
            let currentValue = parseInt(input.value) || 0;
            
            if (isIncrease) {
                input.value = currentValue + 1;
            } else if (currentValue > 0) {
                input.value = currentValue - 1;
            }
            
            updateRecipeCounter();
        });
    });
    
    // Mise à jour du compteur
    function updateRecipeCounter() {
        const guestCount = parseInt(document.getElementById('guestCount')?.value) || 0;
        const recipeInputs = document.querySelectorAll('.recipes-group .qty-input, .mini-boss-items .qty-input');
        
        let totalRecipes = 0;
        recipeInputs.forEach(input => {
            totalRecipes += parseInt(input.value) || 0;
        });
        
        document.getElementById('total-recipes').textContent = totalRecipes;
        document.getElementById('required-recipes').textContent = guestCount;
        
        const status = document.getElementById('recipe-status');
        if (totalRecipes >= guestCount && guestCount > 0) {
            status.innerHTML = '<span class="status-icon">✅</span><span class="status-text">Parfait !</span>';
            status.className = 'counter-status success';
        } else {
            status.innerHTML = '<span class="status-icon">⚠️</span><span class="status-text">Il manque des recettes</span>';
            status.className = 'counter-status warning';
        }
    }
    
    // Initialiser le compteur
    updateRecipeCounter();
});
</script>