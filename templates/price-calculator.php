<?php
/**
 * Template du calculateur de prix sticky
 * Selon les spécifications : Zone sticky en bas de page avec breakdown des coûts
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="price-calculator-sticky" id="price-calculator-sticky" style="display: none;">
    <div class="calculator-container">
        <div class="calculator-header">
            <h4 class="calculator-title"><?php _e('Prix estimatif', 'block-traiteur'); ?></h4>
            <button type="button" class="calculator-toggle" aria-label="<?php _e('Voir le détail', 'block-traiteur'); ?>">
                <span class="toggle-icon">▲</span>
            </button>
        </div>
        
        <div class="calculator-main">
            <div class="price-display">
                <span class="price-label"><?php _e('Total TTC', 'block-traiteur'); ?></span>
                <span class="price-amount" id="total-price-display">0 €</span>
            </div>
            
            <button type="button" class="price-breakdown-btn" id="show-breakdown">
                <?php _e('Voir le détail', 'block-traiteur'); ?>
            </button>
        </div>
    </div>
    
    <!-- Modal de détail des prix -->
    <div class="price-breakdown-modal" id="price-breakdown-modal" style="display: none;">
        <div class="modal-overlay"></div>
        <div class="modal-content">
            <div class="modal-header">
                <h3><?php _e('Détail de votre devis', 'block-traiteur'); ?></h3>
                <button type="button" class="modal-close" aria-label="<?php _e('Fermer', 'block-traiteur'); ?>">×</button>
            </div>
            
            <div class="modal-body">
                <div class="breakdown-section">
                    <h4><?php _e('Forfait de base', 'block-traiteur'); ?></h4>
                    <div class="breakdown-item">
                        <span class="item-label" id="base-service-label"><?php _e('Service de base', 'block-traiteur'); ?></span>
                        <span class="item-price" id="base-price-value">0 €</span>
                    </div>
                </div>
                
                <div class="breakdown-section" id="duration-section" style="display: none;">
                    <h4><?php _e('Suppléments durée', 'block-traiteur'); ?></h4>
                    <div class="breakdown-item">
                        <span class="item-label" id="duration-label"><?php _e('Heures supplémentaires', 'block-traiteur'); ?></span>
                        <span class="item-price" id="duration-price-value">0 €</span>
                    </div>
                </div>
                
                <div class="breakdown-section" id="guests-section" style="display: none;">
                    <h4><?php _e('Supplément invités', 'block-traiteur'); ?></h4>
                    <div class="breakdown-item">
                        <span class="item-label"><?php _e('Plus de 50 personnes', 'block-traiteur'); ?></span>
                        <span class="item-price" id="guests-price-value">150 €</span>
                    </div>
                </div>
                
                <div class="breakdown-section" id="distance-section" style="display: none;">
                    <h4><?php _e('Frais de déplacement', 'block-traiteur'); ?></h4>
                    <div class="breakdown-item">
                        <span class="item-label" id="distance-label"><?php _e('Zone de livraison', 'block-traiteur'); ?></span>
                        <span class="item-price" id="distance-price-value">0 €</span>
                    </div>
                </div>
                
                <div class="breakdown-section" id="products-section" style="display: none;">
                    <h4><?php _e('Produits sélectionnés', 'block-traiteur'); ?></h4>
                    <div class="products-list" id="products-breakdown-list">
                        <!-- Produits ajoutés dynamiquement -->
                    </div>
                    <div class="breakdown-item total-item">
                        <span class="item-label"><?php _e('Total produits', 'block-traiteur'); ?></span>
                        <span class="item-price" id="products-price-value">0 €</span>
                    </div>
                </div>
                
                <div class="breakdown-section" id="beverages-section" style="display: none;">
                    <h4><?php _e('Boissons sélectionnées', 'block-traiteur'); ?></h4>
                    <div class="beverages-list" id="beverages-breakdown-list">
                        <!-- Boissons ajoutées dynamiquement -->
                    </div>
                    <div class="breakdown-item total-item">
                        <span class="item-label"><?php _e('Total boissons', 'block-traiteur'); ?></span>
                        <span class="item-price" id="beverages-price-value">0 €</span>
                    </div>
                </div>
                
                <div class="breakdown-section" id="options-section" style="display: none;">
                    <h4><?php _e('Options sélectionnées', 'block-traiteur'); ?></h4>
                    <div class="options-list" id="options-breakdown-list">
                        <!-- Options ajoutées dynamiquement -->
                    </div>
                    <div class="breakdown-item total-item">
                        <span class="item-label"><?php _e('Total options', 'block-traiteur'); ?></span>
                        <span class="item-price" id="options-price-value">0 €</span>
                    </div>
                </div>
                
                <div class="breakdown-total">
                    <div class="total-line">
                        <span class="total-label"><?php _e('Total TTC', 'block-traiteur'); ?></span>
                        <span class="total-price" id="modal-total-price">0 €</span>
                    </div>
                </div>
                
                <div class="breakdown-footer">
                    <p class="disclaimer">
                        <?php _e('* Prix indicatif, susceptible de modifications selon les options choisies', 'block-traiteur'); ?>
                    </p>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary modal-close">
                    <?php _e('Fermer', 'block-traiteur'); ?>
                </button>
                <button type="button" class="btn btn-primary" id="continue-quote">
                    <?php _e('Continuer ma demande', 'block-traiteur'); ?>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const calculator = document.getElementById('price-calculator-sticky');
    const breakdownModal = document.getElementById('price-breakdown-modal');
    const showBreakdownBtn = document.getElementById('show-breakdown');
    const closeModalBtns = document.querySelectorAll('.modal-close');
    const continueBtn = document.getElementById('continue-quote');
    
    // Afficher/masquer le calculateur
    function showCalculator() {
        calculator.style.display = 'block';
        calculator.classList.add('visible');
    }
    
    function hideCalculator() {
        calculator.style.display = 'none';
        calculator.classList.remove('visible');
    }
    
    // Ouvrir la modal de détail
    showBreakdownBtn.addEventListener('click', function() {
        breakdownModal.style.display = 'block';
        document.body.classList.add('modal-open');
    });
    
    // Fermer la modal
    closeModalBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            breakdownModal.style.display = 'none';
            document.body.classList.remove('modal-open');
        });
    });
    
    // Fermer la modal en cliquant sur l'overlay
    breakdownModal.querySelector('.modal-overlay').addEventListener('click', function() {
        breakdownModal.style.display = 'none';
        document.body.classList.remove('modal-open');
    });
    
    // Continuer la demande
    continueBtn.addEventListener('click', function() {
        breakdownModal.style.display = 'none';
        document.body.classList.remove('modal-open');
        // Trigger next step
        if (typeof window.blockTraiteurForm !== 'undefined') {
            window.blockTraiteurForm.nextStep();
        }
    });
    
    // Fonction de mise à jour des prix
    window.updatePriceCalculator = function(priceData) {
        if (!priceData) return;
        
        const totalPrice = priceData.total || 0;
        const breakdown = priceData.breakdown || {};
        
        // Mettre à jour l'affichage principal
        document.getElementById('total-price-display').textContent = formatPrice(totalPrice);
        document.getElementById('modal-total-price').textContent = formatPrice(totalPrice);
        
        // Mettre à jour les sections de détail
        updateBreakdownSection('base', breakdown.base || 0, priceData.serviceType);
        updateBreakdownSection('duration', breakdown.duration || 0, priceData.duration);
        updateBreakdownSection('guests', breakdown.guests || 0);
        updateBreakdownSection('distance', breakdown.distance || 0, priceData.distance);
        updateBreakdownSection('products', breakdown.products || 0, priceData.selectedProducts);
        updateBreakdownSection('beverages', breakdown.beverages || 0, priceData.selectedBeverages);
        updateBreakdownSection('options', breakdown.options || 0, priceData.selectedOptions);
        
        // Afficher le calculateur si prix > 0
        if (totalPrice > 0) {
            showCalculator();
        } else {
            hideCalculator();
        }
    };
    
    // Mettre à jour une section du détail
    function updateBreakdownSection(section, price, data) {
        const sectionElement = document.getElementById(section + '-section');
        const priceElement = document.getElementById(section + '-price-value');
        
        if (price > 0) {
            sectionElement.style.display = 'block';
            priceElement.textContent = formatPrice(price);
            
            // Mise à jour des labels spécifiques
            switch (section) {
                case 'base':
                    const serviceLabel = document.getElementById('base-service-label');
                    serviceLabel.textContent = data === 'restaurant' 
                        ? '<?php _e("Privatisation restaurant (2H incluses)", "block-traiteur"); ?>'
                        : '<?php _e("Privatisation remorque (2H incluses)", "block-traiteur"); ?>';
                    break;
                    
                case 'duration':
                    if (data > 2) {
                        const durationLabel = document.getElementById('duration-label');
                        const extraHours = data - 2;
                        durationLabel.textContent = extraHours + ' heure' + (extraHours > 1 ? 's' : '') + ' supplémentaire' + (extraHours > 1 ? 's' : '');
                    }
                    break;
                    
                case 'distance':
                    if (data > 0) {
                        const distanceLabel = document.getElementById('distance-label');
                        let zoneText = '';
                        if (data <= 30) zoneText = 'Zone gratuite (0-30km)';
                        else if (data <= 50) zoneText = 'Zone 1 (30-50km)';
                        else if (data <= 100) zoneText = 'Zone 2 (50-100km)';
                        else if (data <= 150) zoneText = 'Zone 3 (100-150km)';
                        distanceLabel.textContent = zoneText;
                    }
                    break;
                    
                case 'products':
                    updateProductsList(data, section + '-breakdown-list');
                    break;
                    
                case 'beverages':
                    updateProductsList(data, section + '-breakdown-list');
                    break;
                    
                case 'options':
                    updateOptionsList(data, section + '-breakdown-list');
                    break;
            }
        } else {
            sectionElement.style.display = 'none';
        }
    }
    
    // Mettre à jour la liste des produits
    function updateProductsList(products, containerId) {
        const container = document.getElementById(containerId);
        container.innerHTML = '';
        
        if (products && products.length > 0) {
            products.forEach(product => {
                const item = document.createElement('div');
                item.className = 'breakdown-item';
                item.innerHTML = `
                    <span class="item-label">${product.name} (x${product.quantity})</span>
                    <span class="item-price">${formatPrice(product.quantity * product.price)}</span>
                `;
                container.appendChild(item);
            });
        }
    }
    
    // Mettre à jour la liste des options
    function updateOptionsList(options, containerId) {
        const container = document.getElementById(containerId);
        container.innerHTML = '';
        
        if (options && options.length > 0) {
            options.forEach(option => {
                const item = document.createElement('div');
                item.className = 'breakdown-item';
                
                let optionName = '';
                let optionPrice = 0;
                
                switch (option) {
                    case 'tireuse':
                        optionName = '<?php _e("Mise à disposition tireuse", "block-traiteur"); ?>';
                        optionPrice = 50;
                        break;
                    case 'jeux':
                        optionName = '<?php _e("Installation jeux", "block-traiteur"); ?>';
                        optionPrice = 70;
                        break;
                }
                
                item.innerHTML = `
                    <span class="item-label">${optionName}</span>
                    <span class="item-price">${formatPrice(optionPrice)}</span>
                `;
                container.appendChild(item);
            });
        }
    }
    
    // Formater le prix
    function formatPrice(price) {
        return new Intl.NumberFormat('fr-FR', {
            style: 'decimal',
            minimumFractionDigits: 0,
            maximumFractionDigits: 2
        }).format(price) + ' €';
    }
    
    // Exposer la fonction globalement
    window.priceCalculator = {
        update: window.updatePriceCalculator,
        show: showCalculator,
        hide: hideCalculator
    };
});
</script>
