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
                    <span class="step active" data-step="0">Service</span>
                    <span class="step" data-step="1">Forfait</span>
                    <span class="step" data-step="2">Repas</span>
                    <span class="step" data-step="3">Buffets</span>
                    <span class="step" data-step="4">Boissons</span>
                    <span class="step" data-step="5">Options</span>
                    <span class="step" data-step="6">Contact</span>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <form class="quote-form" method="post">
        <?php wp_nonce_field('block_traiteur_submit', '_wpnonce'); ?>
        
        <!-- Étapes du formulaire -->
        <div class="form-steps-container">
            
            <!-- Étape 0: Choix du service -->
            <div class="form-step active" data-step="0">
                <?php include BLOCK_TRAITEUR_PLUGIN_DIR . 'templates/form-steps/step-service-choice.php'; ?>
            </div>
            
            <!-- Étape 1: Forfait de base -->
            <div class="form-step" data-step="1">
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
            
            <!-- Étape 4: Boissons -->
            <div class="form-step" data-step="4">
                <?php include BLOCK_TRAITEUR_PLUGIN_DIR . 'templates/form-steps/step-beverages.php'; ?>
            </div>
            
            <!-- Étape 5: Options -->
            <div class="form-step" data-step="5">
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
    
    <!-- Calculateur de prix -->
    <?php if ($atts['show_price'] === 'true'): ?>
        <div class="price-calculator">
            <h4>Estimation de prix</h4>
            <div class="price-breakdown">
                <div class="price-line">
                    <span>Forfait de base</span>
                    <span class="base-price">0 €</span>
                </div>
                <div class="price-line total-line">
                    <span>TOTAL TTC</span>
                    <span class="total-price">0 € TTC</span>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof BlockQuoteForm !== 'undefined') {
        new BlockQuoteForm('<?php echo esc_js($form_id); ?>');
    } else {
        console.error('BlockQuoteForm non trouvé');
    }
});
</script>