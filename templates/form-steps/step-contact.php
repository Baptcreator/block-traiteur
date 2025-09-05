<?php
/**
 * Étape 6: Contact et finalisation
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="step-header">
    <h3><?php _e('4. Coordonnées / Contact', 'block-traiteur'); ?> <span class="required">(OBLIGATOIRE)</span></h3>
    <p><?php _e('Dernière étape ! Nous avons besoin de vos coordonnées pour finaliser votre devis', 'block-traiteur'); ?></p>
</div>

<div class="contact-form">
    <div class="form-grid">
        <!-- Nom -->
        <div class="form-group">
            <label for="customerName"><?php _e('Nom', 'block-traiteur'); ?> <span class="required">*</span></label>
            <input type="text" id="customerName" name="customerName" class="form-control" required>
        </div>
        
        <!-- Prénom -->
        <div class="form-group">
            <label for="customerFirstname"><?php _e('Prénom', 'block-traiteur'); ?> <span class="required">*</span></label>
            <input type="text" id="customerFirstname" name="customerFirstname" class="form-control" required>
        </div>
        
        <!-- Téléphone -->
        <div class="form-group">
            <label for="customerPhone"><?php _e('Téléphone', 'block-traiteur'); ?> <span class="required">*</span></label>
            <input type="tel" id="customerPhone" name="customerPhone" class="form-control" 
                   placeholder="06 12 34 56 78" required>
        </div>
        
        <!-- Email -->
        <div class="form-group">
            <label for="customerEmail"><?php _e('Mail', 'block-traiteur'); ?> <span class="required">*</span></label>
            <input type="email" id="customerEmail" name="customerEmail" class="form-control" required>
            <div class="field-help"><?php _e('Nous vous enverrons votre devis à cette adresse', 'block-traiteur'); ?></div>
        </div>
    </div>
    
    <!-- Commentaires -->
    <div class="form-group">
        <label for="customerComments"><?php _e('Section question/commentaire', 'block-traiteur'); ?></label>
        <textarea id="customerComments" name="customerComments" class="form-control" rows="4" 
                  placeholder="<?php _e('1 question, 1 souhait, n\'hésitez pas de nous en faire part, on en parle, on...', 'block-traiteur'); ?>"></textarea>
        <div class="field-help">
            <?php _e('Partagez vos questions, souhaits ou demandes spéciales', 'block-traiteur'); ?>
        </div>
    </div>
    
    <!-- Récapitulatif final -->
    <div class="final-summary">
        <h4><?php _e('Récapitulatif de votre demande', 'block-traiteur'); ?></h4>
        
        <div class="summary-grid">
            <div class="summary-section">
                <h5><?php _e('Événement', 'block-traiteur'); ?></h5>
                <div class="summary-item">
                    <span class="label"><?php _e('Service :', 'block-traiteur'); ?></span>
                    <span class="value" id="summary-service"></span>
                </div>
                <div class="summary-item">
                    <span class="label"><?php _e('Date :', 'block-traiteur'); ?></span>
                    <span class="value" id="summary-date"></span>
                </div>
                <div class="summary-item">
                    <span class="label"><?php _e('Invités :', 'block-traiteur'); ?></span>
                    <span class="value" id="summary-guests"></span>
                </div>
                <div class="summary-item">
                    <span class="label"><?php _e('Durée :', 'block-traiteur'); ?></span>
                    <span class="value" id="summary-duration"></span>
                </div>
                <div class="summary-item remorque-info" style="display: none;">
                    <span class="label"><?php _e('Lieu :', 'block-traiteur'); ?></span>
                    <span class="value" id="summary-location"></span>
                </div>
            </div>
            
            <div class="summary-section">
                <h5><?php _e('Sélection', 'block-traiteur'); ?></h5>
                <div class="selected-products" id="summary-products">
                    <!-- Rempli dynamiquement -->
                </div>
                <div class="selected-beverages" id="summary-beverages">
                    <!-- Rempli dynamiquement -->
                </div>
                <div class="selected-options" id="summary-options">
                    <!-- Rempli dynamiquement -->
                </div>
            </div>
            
            <div class="summary-section">
                <h5><?php _e('Prix', 'block-traiteur'); ?></h5>
                <div class="price-breakdown-final">
                    <div class="price-line">
                        <span><?php _e('Forfait de base', 'block-traiteur'); ?></span>
                        <span id="final-base-price">0 €</span>
                    </div>
                    <div class="price-line supplements-line" style="display: none;">
                        <span><?php _e('Suppléments', 'block-traiteur'); ?></span>
                        <span id="final-supplements-price">0 €</span>
                    </div>
                    <div class="price-line products-line" style="display: none;">
                        <span><?php _e('Produits', 'block-traiteur'); ?></span>
                        <span id="final-products-price">0 €</span>
                    </div>
                    <div class="price-line beverages-line" style="display: none;">
                        <span><?php _e('Boissons', 'block-traiteur'); ?></span>
                        <span id="final-beverages-price">0 €</span>
                    </div>
                    <div class="price-line options-line" style="display: none;">
                        <span><?php _e('Options', 'block-traiteur'); ?></span>
                        <span id="final-options-price">0 €</span>
                    </div>
                    <div class="price-line total-line">
                        <span><?php _e('TOTAL TTC', 'block-traiteur'); ?></span>
                        <span class="total-amount" id="final-total-price">0 € TTC</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Conditions -->
    <div class="terms-section">
        <div class="terms-content">
            <h5><?php _e('Conditions', 'block-traiteur'); ?></h5>
            <ul class="terms-list">
                <li><?php _e('Validité du devis : 30 jours', 'block-traiteur'); ?></li>
                <li><?php _e('Acompte : 50% à la confirmation', 'block-traiteur'); ?></li>
                <li><?php _e('Solde le jour de l\'événement', 'block-traiteur'); ?></li>
                <li><?php _e('Ce devis est estimatif et pourra être ajusté', 'block-traiteur'); ?></li>
            </ul>
        </div>
        
        <div class="acceptance">
            <label class="checkbox-card terms-checkbox">
                <input type="checkbox" name="acceptTerms" id="acceptTerms" required>
                <div class="card-content">
                    <span class="checkbox-text">
                        <?php _e('J\'accepte les conditions générales et autorise Block à me recontacter', 'block-traiteur'); ?>
                        <span class="required">*</span>
                    </span>
                </div>
            </label>
        </div>
    </div>
    
    <!-- Message final -->
    <div class="final-message">
        <div class="message-content">
            <div class="important-notice">
                <h4>⚠️ <?php _e('Important : Montant estimatif !', 'block-traiteur'); ?></h4>
                <p class="notice-text">
                    <?php _e('Votre devis est d\'ores et déjà disponible dans votre boîte mail, la suite ? Block va prendre contact avec vous afin d\'affiner celui-ci et de créer avec vous toute l\'expérience dont vous rêvez', 'block-traiteur'); ?>
                </p>
            </div>
            
            <div class="next-steps">
                <div class="step-item">
                    <span class="step-number">1</span>
                    <div class="step-content">
                        <strong><?php _e('Email immédiat', 'block-traiteur'); ?></strong>
                        <p><?php _e('Vous recevrez votre devis estimatif par email avec tous les détails', 'block-traiteur'); ?></p>
                    </div>
                </div>
                <div class="step-item">
                    <span class="step-number">2</span>
                    <div class="step-content">
                        <strong><?php _e('Contact Block', 'block-traiteur'); ?></strong>
                        <p><?php _e('Notre équipe vous contacte pour affiner votre devis et personnaliser votre expérience', 'block-traiteur'); ?></p>
                    </div>
                </div>
                <div class="step-item">
                    <span class="step-number">3</span>
                    <div class="step-content">
                        <strong><?php _e('Création sur mesure', 'block-traiteur'); ?></strong>
                        <p><?php _e('Ensemble, nous créons l\'événement dont vous rêvez', 'block-traiteur'); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>