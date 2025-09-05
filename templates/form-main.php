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
        <input type="hidden" name="serviceType" id="serviceType" value="<?php echo esc_attr($js_config['serviceType']); ?>">
        
        <!-- Étapes du formulaire -->
        <div class="form-steps-container">
            
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
        <div class="form-navigation" style="display: none;">
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
    
    <!-- Calculateur de prix sticky -->
    <?php include BLOCK_TRAITEUR_PLUGIN_DIR . 'templates/price-calculator.php'; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const config = <?php echo wp_json_encode($js_config); ?>;
    
    // Si un service est pré-sélectionné, démarrer automatiquement
    if (config.serviceType && config.serviceType !== 'both' && config.autoStart) {
        // Définir le service et afficher le formulaire
        document.getElementById('serviceType').value = config.serviceType;
        
        // Afficher les étapes du formulaire
        const stepsContainer = document.querySelector('.form-steps-container');
        const navigation = document.querySelector('.form-navigation');
        
        if (stepsContainer) {
            stepsContainer.style.display = 'block';
        }
        if (navigation) {
            navigation.style.display = 'flex';
        }
        
        // Mettre à jour les contraintes selon le service
        updateServiceConstraints(config.serviceType);
        
        // Initialiser le calculateur de prix
        if (typeof window.updatePriceCalculator === 'function') {
            window.updatePriceCalculator({
                serviceType: config.serviceType,
                total: config.serviceType === 'restaurant' ? 300 : 350,
                breakdown: {
                    base: config.serviceType === 'restaurant' ? 300 : 350
                }
            });
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
});
</script>