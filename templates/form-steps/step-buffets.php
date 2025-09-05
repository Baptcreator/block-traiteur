<?php
/**
 * Étape 3: Choix des buffets - SELON SPÉCIFICATIONS CLIENT
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="step-header">
    <h3><?php _e('4. Choix du/des buffet(s)', 'block-traiteur'); ?></h3>
    <p><?php _e('Enrichissez votre événement avec nos buffets (minimum 1 buffet à choisir)', 'block-traiteur'); ?></p>
</div>

<div class="buffets-section">
    <!-- Choix du type de buffet -->
    <div class="buffet-type-selector">
        <h4><?php _e('Quel type de buffet souhaitez-vous ?', 'block-traiteur'); ?> <span class="required">*</span></h4>
        
        <div class="buffet-options">
            <label class="checkbox-card buffet-type-card">
                <input type="checkbox" name="buffetTypes[]" value="sale" required>
                <div class="card-content">
                    <div class="card-title"><?php _e('Buffet Salé', 'block-traiteur'); ?></div>
                    <div class="card-description">
                        <?php _e('Sélection de mets salés pour accompagner votre événement', 'block-traiteur'); ?>
                    </div>
                </div>
            </label>
            
            <label class="checkbox-card buffet-type-card">
                <input type="checkbox" name="buffetTypes[]" value="sucre">
                <div class="card-content">
                    <div class="card-title"><?php _e('Buffet Sucré', 'block-traiteur'); ?></div>
                    <div class="card-description">
                        <?php _e('Desserts et douceurs pour terminer en beauté', 'block-traiteur'); ?>
                    </div>
                </div>
            </label>
            
            <label class="checkbox-card buffet-type-card">
                <input type="checkbox" name="buffetTypes[]" value="mixte">
                <div class="card-content">
                    <div class="card-title"><?php _e('Buffets Salé et Sucré', 'block-traiteur'); ?></div>
                    <div class="card-description">
                        <?php _e('La formule complète avec les deux buffets', 'block-traiteur'); ?>
                    </div>
                </div>
            </label>
        </div>
    </div>

    <!-- Buffet Salé -->
    <div class="buffet-details buffet-sale" id="buffet-sale" style="display: none;">
        <h4><?php _e('Buffet Salé', 'block-traiteur'); ?></h4>
        <p class="buffet-help"><?php _e('Minimum 1/personne et minimum 2 recettes différentes sur les 7', 'block-traiteur'); ?></p>
        
        <div class="buffet-items">
            <div class="buffet-item">
                <div class="item-info">
                    <h5>Tartinade de Houmous</h5>
                    <div class="item-unit">Par portion (50g)</div>
                    <div class="item-price">3.50€</div>
                    <div class="item-description">Houmous maison aux pois chiches, tahini et épices orientales</div>
                </div>
                <div class="item-quantity">
                    <button type="button" class="qty-btn decrease" data-target="buffet-houmous">-</button>
                    <input type="number" id="buffet-houmous" name="buffet_sale[houmous]" value="0" min="0" class="qty-input">
                    <button type="button" class="qty-btn increase" data-target="buffet-houmous">+</button>
                </div>
            </div>
            
            <div class="buffet-item">
                <div class="item-info">
                    <h5>Bruschetta Tomate Basilic</h5>
                    <div class="item-unit">Par pièce</div>
                    <div class="item-price">2.50€</div>
                    <div class="item-description">Pain grillé, tomates fraîches, basilic, huile d'olive</div>
                </div>
                <div class="item-quantity">
                    <button type="button" class="qty-btn decrease" data-target="buffet-bruschetta">-</button>
                    <input type="number" id="buffet-bruschetta" name="buffet_sale[bruschetta]" value="0" min="0" class="qty-input">
                    <button type="button" class="qty-btn increase" data-target="buffet-bruschetta">+</button>
                </div>
            </div>
            
            <div class="buffet-item">
                <div class="item-info">
                    <h5>Mini Quiches Lorraines</h5>
                    <div class="item-unit">Par pièce</div>
                    <div class="item-price">3€</div>
                    <div class="item-description">Quiches individuelles lardons, œufs, crème fraîche</div>
                </div>
                <div class="item-quantity">
                    <button type="button" class="qty-btn decrease" data-target="buffet-quiches">-</button>
                    <input type="number" id="buffet-quiches" name="buffet_sale[quiches]" value="0" min="0" class="qty-input">
                    <button type="button" class="qty-btn increase" data-target="buffet-quiches">+</button>
                </div>
            </div>
            
            <div class="buffet-item">
                <div class="item-info">
                    <h5>Plateau de Charcuterie</h5>
                    <div class="item-unit">Pour 6 personnes</div>
                    <div class="item-price">25€</div>
                    <div class="item-description">Sélection de charcuteries artisanales, cornichons, pain</div>
                </div>
                <div class="item-quantity">
                    <button type="button" class="qty-btn decrease" data-target="buffet-charcuterie">-</button>
                    <input type="number" id="buffet-charcuterie" name="buffet_sale[charcuterie]" value="0" min="0" class="qty-input">
                    <button type="button" class="qty-btn increase" data-target="buffet-charcuterie">+</button>
                </div>
            </div>
            
            <div class="buffet-item">
                <div class="item-info">
                    <h5>Plateau de Fromages</h5>
                    <div class="item-unit">Pour 6 personnes</div>
                    <div class="item-price">28€</div>
                    <div class="item-description">Sélection de fromages régionaux, noix, raisins, pain</div>
                </div>
                <div class="item-quantity">
                    <button type="button" class="qty-btn decrease" data-target="buffet-fromages">-</button>
                    <input type="number" id="buffet-fromages" name="buffet_sale[fromages]" value="0" min="0" class="qty-input">
                    <button type="button" class="qty-btn increase" data-target="buffet-fromages">+</button>
                </div>
            </div>
            
            <div class="buffet-item">
                <div class="item-info">
                    <h5>Verrines Saumon Avocat</h5>
                    <div class="item-unit">Par pièce</div>
                    <div class="item-price">4.50€</div>
                    <div class="item-description">Verrines fraîches saumon fumé, avocat, crème citronnée</div>
                </div>
                <div class="item-quantity">
                    <button type="button" class="qty-btn decrease" data-target="buffet-verrines">-</button>
                    <input type="number" id="buffet-verrines" name="buffet_sale[verrines]" value="0" min="0" class="qty-input">
                    <button type="button" class="qty-btn increase" data-target="buffet-verrines">+</button>
                </div>
            </div>
            
            <div class="buffet-item special">
                <div class="item-info">
                    <h5>Grilled Cheese Block</h5>
                    <div class="item-unit">Par pièce</div>
                    <div class="item-price">5€</div>
                    <div class="item-description">Notre spécialité : croque grillé au fromage fondu</div>
                </div>
                <div class="item-options">
                    <label class="addon-option">
                        <input type="checkbox" name="grilled_cheese_jambon" value="1">
                        <span class="option-text">+ Jambon Blanc</span>
                        <span class="option-price">+1€</span>
                    </label>
                </div>
                <div class="item-quantity">
                    <button type="button" class="qty-btn decrease" data-target="buffet-grilled">-</button>
                    <input type="number" id="buffet-grilled" name="buffet_sale[grilled]" value="0" min="0" class="qty-input">
                    <button type="button" class="qty-btn increase" data-target="buffet-grilled">+</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Buffet Sucré -->
    <div class="buffet-details buffet-sucre" id="buffet-sucre" style="display: none;">
        <h4><?php _e('Buffet Sucré', 'block-traiteur'); ?></h4>
        <p class="buffet-help"><?php _e('Minimum 1/personne et minimum 1 recette', 'block-traiteur'); ?></p>
        
        <div class="buffet-items">
            <div class="buffet-item">
                <div class="item-info">
                    <h5>Tarte aux Fruits de Saison</h5>
                    <div class="item-unit">Pour 6 personnes</div>
                    <div class="item-price">18€</div>
                    <div class="item-description">Tarte pâtissière aux fruits frais de saison, crème pâtissière</div>
                </div>
                <div class="item-quantity">
                    <button type="button" class="qty-btn decrease" data-target="dessert-tarte">-</button>
                    <input type="number" id="dessert-tarte" name="buffet_sucre[tarte]" value="0" min="0" class="qty-input">
                    <button type="button" class="qty-btn increase" data-target="dessert-tarte">+</button>
                </div>
            </div>
            
            <div class="buffet-item">
                <div class="item-info">
                    <h5>Mini Éclairs</h5>
                    <div class="item-unit">Par pièce</div>
                    <div class="item-price">3.50€</div>
                    <div class="item-description">Éclairs miniatures chocolat, café ou vanille</div>
                </div>
                <div class="item-quantity">
                    <button type="button" class="qty-btn decrease" data-target="dessert-eclairs">-</button>
                    <input type="number" id="dessert-eclairs" name="buffet_sucre[eclairs]" value="0" min="0" class="qty-input">
                    <button type="button" class="qty-btn increase" data-target="dessert-eclairs">+</button>
                </div>
            </div>
            
            <div class="buffet-item">
                <div class="item-info">
                    <h5>Macarons Assortis</h5>
                    <div class="item-unit">Par pièce</div>
                    <div class="item-price">2.80€</div>
                    <div class="item-description">Macarons artisanaux parfums variés</div>
                </div>
                <div class="item-quantity">
                    <button type="button" class="qty-btn decrease" data-target="dessert-macarons">-</button>
                    <input type="number" id="dessert-macarons" name="buffet_sucre[macarons]" value="0" min="0" class="qty-input">
                    <button type="button" class="qty-btn increase" data-target="dessert-macarons">+</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Compteurs de validation -->
    <div class="buffet-counters">
        <div class="counter-section buffet-sale-counter" style="display: none;">
            <h5><?php _e('Buffet Salé', 'block-traiteur'); ?></h5>
            <div class="counter-info">
                <span class="counter-text">
                    <span id="total-buffet-sale">0</span> portions / 
                    <span id="required-buffet-sale" class="guest-count">0</span> requises
                </span>
                <span class="recipes-count">
                    (<span id="recipes-buffet-sale">0</span>/2 recettes minimum)
                </span>
                <div class="counter-status" id="buffet-sale-status">
                    <span class="status-icon">⚠️</span>
                    <span class="status-text">Sélection incomplète</span>
                </div>
            </div>
        </div>
        
        <div class="counter-section buffet-sucre-counter" style="display: none;">
            <h5><?php _e('Buffet Sucré', 'block-traiteur'); ?></h5>
            <div class="counter-info">
                <span class="counter-text">
                    <span id="total-buffet-sucre">0</span> portions / 
                    <span id="required-buffet-sucre" class="guest-count">0</span> requises
                </span>
                <div class="counter-status" id="buffet-sucre-status">
                    <span class="status-icon">⚠️</span>
                    <span class="status-text">Sélection incomplète</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gestion du choix de type de buffet
    const buffetCheckboxes = document.querySelectorAll('input[name="buffetTypes[]"]');
    const buffetSale = document.getElementById('buffet-sale');
    const buffetSucre = document.getElementById('buffet-sucre');
    const saleCounter = document.querySelector('.buffet-sale-counter');
    const sucreCounter = document.querySelector('.buffet-sucre-counter');
    
    buffetCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateBuffetDisplay();
        });
    });
    
    function updateBuffetDisplay() {
        const saleChecked = document.querySelector('input[value="sale"]').checked || document.querySelector('input[value="mixte"]').checked;
        const sucreChecked = document.querySelector('input[value="sucre"]').checked || document.querySelector('input[value="mixte"]').checked;
        
        buffetSale.style.display = saleChecked ? 'block' : 'none';
        buffetSucre.style.display = sucreChecked ? 'block' : 'none';
        saleCounter.style.display = saleChecked ? 'block' : 'none';
        sucreCounter.style.display = sucreChecked ? 'block' : 'none';
        
        updateBuffetCounters();
    }
    
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
            
            updateBuffetCounters();
        });
    });
    
    // Mise à jour des compteurs
    function updateBuffetCounters() {
        const guestCount = parseInt(document.getElementById('guestCount')?.value) || 0;
        
        // Compteur buffet salé
        const saleInputs = document.querySelectorAll('#buffet-sale .qty-input');
        let totalSale = 0;
        let recipesSale = 0;
        
        saleInputs.forEach(input => {
            const value = parseInt(input.value) || 0;
            if (value > 0) {
                totalSale += value;
                recipesSale++;
            }
        });
        
        document.getElementById('total-buffet-sale').textContent = totalSale;
        document.getElementById('required-buffet-sale').textContent = guestCount;
        document.getElementById('recipes-buffet-sale').textContent = recipesSale;
        
        const saleStatus = document.getElementById('buffet-sale-status');
        if (totalSale >= guestCount && recipesSale >= 2 && guestCount > 0) {
            saleStatus.innerHTML = '<span class="status-icon">✅</span><span class="status-text">Parfait !</span>';
            saleStatus.className = 'counter-status success';
        } else {
            saleStatus.innerHTML = '<span class="status-icon">⚠️</span><span class="status-text">Sélection incomplète</span>';
            saleStatus.className = 'counter-status warning';
        }
        
        // Compteur buffet sucré
        const sucreInputs = document.querySelectorAll('#buffet-sucre .qty-input');
        let totalSucre = 0;
        
        sucreInputs.forEach(input => {
            totalSucre += parseInt(input.value) || 0;
        });
        
        document.getElementById('total-buffet-sucre').textContent = totalSucre;
        document.getElementById('required-buffet-sucre').textContent = guestCount;
        
        const sucreStatus = document.getElementById('buffet-sucre-status');
        if (totalSucre >= guestCount && guestCount > 0) {
            sucreStatus.innerHTML = '<span class="status-icon">✅</span><span class="status-text">Parfait !</span>';
            sucreStatus.className = 'counter-status success';
        } else {
            sucreStatus.innerHTML = '<span class="status-icon">⚠️</span><span class="status-text">Sélection incomplète</span>';
            sucreStatus.className = 'counter-status warning';
        }
    }
    
    // Initialiser
    updateBuffetDisplay();
    updateBuffetCounters();
});
</script>