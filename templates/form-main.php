<?php
/**
 * Template principal du formulaire
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div id="<?php echo esc_attr($form_id); ?>" class="<?php echo esc_attr(implode(' ', $css_classes)); ?>" 
     data-config="<?php echo esc_attr(wp_json_encode($js_config)); ?>">
     
    <?php if ($atts['hide_header'] !== 'true'): ?>
        <div class="form-header">
            <h2>Demande de Devis Block Traiteur</h2>
            <p class="form-subtitle">Obtenez votre devis personnalisé en quelques minutes</p>
            
            <?php if ($atts['show_progress'] === 'true'): ?>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: 0%;"></div>
                </div>
                <div class="progress-steps">
                    <span class="step active" data-step="1">Forfait de base</span>
                    <span class="step" data-step="2">Formules repas</span>
                    <span class="step" data-step="3">Buffets</span>
                    <span class="step" data-step="4">Boissons</span>
                    <span class="step service-remorque-only" data-step="5">Options</span>
                    <span class="step" data-step="6">Contact</span>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <form class="quote-form" method="post">
        <?php wp_nonce_field('block_traiteur_submit', '_wpnonce'); ?>
        <input type="hidden" name="serviceType" id="serviceType" value="">
        
        <!-- Section Prix estimatif - PRÉSENTE SUR TOUTES LES PAGES selon spécifications -->
        <div class="initial-price-display" style="text-align: center; margin: 20px 0; padding: 15px; background: #f8f9fa; border-radius: 8px;">
            <h4 style="margin: 0; color: #243127;">Prix estimatif</h4>
            <div class="service-prices" style="display: flex; justify-content: center; gap: 30px; margin-top: 10px;">
                <div class="price-option">
                    <span style="font-weight: 600;">Restaurant :</span>
                    <span style="color: #EF3D1D; font-size: 1.2em; font-weight: 700;">300€</span>
                </div>
                <div class="price-option">
                    <span style="font-weight: 600;">Remorque :</span>
                    <span style="color: #EF3D1D; font-size: 1.2em; font-weight: 700;">350€</span>
                </div>
            </div>
            <p style="margin: 5px 0 0 0; font-size: 0.9em; color: #666;">
                Prix de base TTC (2h incluses) - Mise à jour en temps réel selon vos choix
            </p>
        </div>
        
        <!-- Sélection du service (Étape 0 - OBLIGATOIRE) -->
        <div class="service-selection-container">
            <?php include BLOCK_TRAITEUR_PLUGIN_DIR . 'templates/form-steps/step-service-choice.php'; ?>
        </div>
        
        <!-- Étapes du formulaire -->
        <div class="form-steps-container" style="display: none;">
            
            <!-- Étape 1: Forfait de base -->
            <div class="form-step active" data-step="1">
                <?php include BLOCK_TRAITEUR_PLUGIN_DIR . 'templates/form-steps/step-base-package.php'; ?>
            </div>
            
            <!-- Étape 2: Formules repas -->
            <div class="form-step" data-step="2">
                <?php include BLOCK_TRAITEUR_PLUGIN_DIR . 'templates/form-steps/step-meal-formulas.php'; ?>
            </div>
            
            <!-- Étape 3: Buffets -->
            <div class="form-step" data-step="3">
                <?php include BLOCK_TRAITEUR_PLUGIN_DIR . 'templates/form-steps/step-buffets.php'; ?>
            </div>
            
            <!-- Étape 4: Boissons (optionnelle) -->
            <div class="form-step" data-step="4">
                <?php include BLOCK_TRAITEUR_PLUGIN_DIR . 'templates/form-steps/step-beverages.php'; ?>
            </div>
            
            <!-- Étape 5: Options (remorque uniquement) -->
            <div class="form-step service-remorque-only" data-step="5">
                <?php include BLOCK_TRAITEUR_PLUGIN_DIR . 'templates/form-steps/step-options.php'; ?>
            </div>
            
            <!-- Étape 6: Contact -->
            <div class="form-step" data-step="6">
                <?php include BLOCK_TRAITEUR_PLUGIN_DIR . 'templates/form-steps/step-contact.php'; ?>
            </div>
        </div>
        
        <!-- Navigation -->
        <div class="form-navigation">
            <button type="button" class="btn btn-secondary prev-step" style="display: none;">
                ← Précédent
            </button>
            
            <button type="button" class="btn btn-primary next-step">
                Suivant →
            </button>
            
            <button type="submit" class="btn btn-primary submit-form" style="display: none;">
                ✓ Envoyer ma demande
            </button>
        </div>
        
        <!-- Messages de statut -->
        <div class="form-messages" style="display: none;">
            <div class="success-message" style="display: none;"></div>
            <div class="error-message" style="display: none;"></div>
            <div class="loading-message" style="display: none;">
                <span class="spinner"></span>
                Traitement en cours...
            </div>
        </div>
    </form>
    
    <!-- Calculateur de prix STICKY selon spécifications - Zone sticky en bas de page -->
    <div id="sticky-price-calculator" class="price-calculator-sticky" style="display: none;">
        <div class="price-calculator-content">
            <div class="price-main">
                <span class="price-label">Prix estimatif :</span>
                <span class="price-value" id="total-price">300€ TTC</span>
                <button type="button" class="price-toggle" onclick="togglePriceBreakdown()">
                    <span id="toggle-text">Détail</span>
                    <span class="toggle-icon">▼</span>
                </button>
            </div>
            <div class="price-breakdown" id="price-breakdown" style="display: none;">
                <div class="breakdown-item">
                    <span>Forfait de base :</span>
                    <span id="base-price">300€</span>
                </div>
                <div class="breakdown-item">
                    <span>Durée (+50€/h sup.) :</span>
                    <span id="duration-price">0€</span>
                </div>
                <div class="breakdown-item">
                    <span>Convives (>50p +150€) :</span>
                    <span id="guests-price">0€</span>
                </div>
                <div class="breakdown-item">
                    <span>Distance/Livraison :</span>
                    <span id="distance-price">0€</span>
                </div>
                <div class="breakdown-item">
                    <span>Produits sélectionnés :</span>
                    <span id="products-price">0€</span>
                </div>
                <div class="breakdown-item">
                    <span>Boissons :</span>
                    <span id="beverages-price">0€</span>
                </div>
                <div class="breakdown-item">
                    <span>Options :</span>
                    <span id="options-price">0€</span>
                </div>
                <div class="breakdown-total">
                    <span>TOTAL TTC :</span>
                    <span id="total-breakdown">300€</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Calculateur de prix (version originale pour compatibilité) -->
    <?php include BLOCK_TRAITEUR_PLUGIN_DIR . 'templates/price-calculator.php'; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const config = <?php echo wp_json_encode($js_config); ?>;
    
    // Gérer les boutons CHOISIR
    document.querySelectorAll('.select-service').forEach(button => {
        button.addEventListener('click', function() {
            const serviceType = this.getAttribute('data-service');
            
            // Définir le service sélectionné
            document.getElementById('serviceType').value = serviceType;
            
            // Masquer SEULEMENT la sélection de service - GARDER l'affichage des prix !
            const serviceContainer = document.querySelector('.service-selection-container');
            const initialPriceDisplay = document.querySelector('.initial-price-display');
            if (serviceContainer) {
                serviceContainer.style.display = 'none';
            }
            // GARDER l'affichage des prix selon les spécifications
            if (initialPriceDisplay) {
                initialPriceDisplay.style.display = 'block'; // TOUJOURS VISIBLE
            }
            
            // Afficher les étapes du formulaire
            const stepsContainer = document.querySelector('.form-steps-container');
            if (stepsContainer) {
                stepsContainer.style.display = 'block';
            }
            
            // Mettre à jour les contraintes selon le service
            updateServiceConstraints(serviceType);
            
                            // Initialiser le calculateur de prix ET le sticky calculator
                const basePrice = serviceType === 'restaurant' ? 300 : 350;
                
                // Afficher le calculateur sticky
                const stickyCalculator = document.getElementById('sticky-price-calculator');
                if (stickyCalculator) {
                    stickyCalculator.style.display = 'block';
                }
                
                // Mettre à jour les prix
                document.getElementById('total-price').textContent = basePrice + '€ TTC';
                document.getElementById('base-price').textContent = basePrice + '€';
                document.getElementById('total-breakdown').textContent = basePrice + '€';
                
                if (typeof window.updatePriceCalculator === 'function') {
                    window.updatePriceCalculator({
                        serviceType: serviceType,
                        total: basePrice,
                        breakdown: {
                            base: basePrice
                        }
                    });
                }
            
            // Log pour debug
            console.log('Service sélectionné:', serviceType);
        });
    });
    
    // Si un service est pré-sélectionné, démarrer automatiquement
    if (config.serviceType && config.serviceType !== 'both' && config.autoStart) {
        // Simuler le clic sur le bouton correspondant
        const button = document.querySelector('.select-service[data-service="' + config.serviceType + '"]');
        if (button) {
            button.click();
        }
    }
    
    // Initialiser le formulaire si disponible
    if (typeof BlockQuoteForm !== 'undefined') {
        new BlockQuoteForm('<?php echo esc_js($form_id); ?>');
    } else {
        console.error('BlockQuoteForm non trouvé');
    }
    
    // Fonction pour mettre à jour les contraintes selon le service
    function updateServiceConstraints(serviceType) {
        const guestInput = document.getElementById('guestCount');
        const guestLimits = document.querySelector('.guest-limits');
        const remorqueFields = document.querySelector('.remorque-fields');
        const restaurantOnlyElements = document.querySelectorAll('.restaurant-only');
        const remorqueOnlyElements = document.querySelectorAll('.remorque-only');
        
        if (serviceType === 'restaurant') {
            // Contraintes restaurant : 10-30 personnes
            if (guestInput) {
                guestInput.min = 10;
                guestInput.max = 30;
                guestInput.value = Math.min(Math.max(guestInput.value, 10), 30);
            }
            if (guestLimits) {
                guestLimits.textContent = 'min 10p / max 30p';
            }
            if (remorqueFields) {
                remorqueFields.style.display = 'none';
            }
            
            // Afficher éléments restaurant, masquer remorque
            restaurantOnlyElements.forEach(el => el.style.display = 'block');
            remorqueOnlyElements.forEach(el => el.style.display = 'none');
            
        } else if (serviceType === 'remorque') {
            // Contraintes remorque : 20-100+ personnes
            if (guestInput) {
                guestInput.min = 20;
                guestInput.max = 100;
                guestInput.value = Math.min(Math.max(guestInput.value, 20), 100);
            }
            if (guestLimits) {
                guestLimits.textContent = 'min 20p / max 100p';
            }
            if (remorqueFields) {
                remorqueFields.style.display = 'block';
            }
            
            // Afficher éléments remorque, masquer restaurant
            remorqueOnlyElements.forEach(el => el.style.display = 'block');
            restaurantOnlyElements.forEach(el => el.style.display = 'none');
        }
    }

    // Fonction pour toggle le breakdown des prix selon les spécifications
    window.togglePriceBreakdown = function() {
        const breakdown = document.getElementById('price-breakdown');
        const toggleText = document.getElementById('toggle-text');
        const toggleIcon = document.querySelector('.toggle-icon');
        
        if (breakdown.style.display === 'none') {
            breakdown.style.display = 'block';
            toggleText.textContent = 'Masquer';
            toggleIcon.textContent = '▲';
        } else {
            breakdown.style.display = 'none';
            toggleText.textContent = 'Détail';
            toggleIcon.textContent = '▼';
        }
    };
});
</script>