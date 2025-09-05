<?php
/**
 * Étape 1: Forfait de base
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="step-header">
    <h3><?php _e('Configurez votre forfait de base', 'block-traiteur'); ?></h3>
    <p class="service-intro"></p>
</div>

<div class="base-package-form">
    <div class="form-grid">
        <!-- Date de l'événement -->
        <div class="form-group">
            <label for="eventDate"><?php _e('Date de votre événement', 'block-traiteur'); ?> <span class="required">*</span></label>
            <input type="date" id="eventDate" name="eventDate" class="form-control" required>
            <div class="field-help">
                <?php _e('Sélectionnez la date de votre événement', 'block-traiteur'); ?>
            </div>
            <div class="availability-status"></div>
        </div>
        
        <!-- Nombre d'invités -->
        <div class="form-group">
            <label for="guestCount"><?php _e('Nombre d\'invités', 'block-traiteur'); ?> <span class="required">*</span></label>
            <div class="number-input-group">
                <button type="button" class="number-btn decrease" data-target="guestCount">-</button>
                <input type="number" id="guestCount" name="guestCount" class="form-control" 
                       min="10" max="30" value="20" required data-service-restaurant>
                <button type="button" class="number-btn increase" data-target="guestCount">+</button>
            </div>
            <div class="field-help">
                <span class="guest-limits">min 10p / max 30p</span>
            </div>
        </div>
        
        <!-- Durée -->
        <div class="form-group">
            <label for="duration"><?php _e('Durée de l\'événement', 'block-traiteur'); ?> <span class="required">*</span></label>
            <div class="duration-selector">
                <div class="duration-options">
                    <label class="duration-option">
                        <input type="radio" name="duration" value="2" checked>
                        <span class="duration-label">2h</span>
                        <span class="duration-price"><?php _e('Inclus', 'block-traiteur'); ?></span>
                    </label>
                    <label class="duration-option">
                        <input type="radio" name="duration" value="3">
                        <span class="duration-label">3h</span>
                        <span class="duration-price"><?php _e('Inclus', 'block-traiteur'); ?></span>
                    </label>
                    <label class="duration-option">
                        <input type="radio" name="duration" value="4">
                        <span class="duration-label">4h</span>
                        <span class="duration-price"><?php _e('Inclus', 'block-traiteur'); ?></span>
                    </label>
                    <label class="duration-option">
                        <input type="radio" name="duration" value="5">
                        <span class="duration-label">5h</span>
                        <span class="duration-price">+20€ TTC</span>
                    </label>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Champs spécifiques à la remorque -->
    <div class="remorque-fields" style="display: none;">
        <div class="form-grid">
            <!-- Lieu de l'événement -->
            <div class="form-group">
                <label for="eventLocation"><?php _e('Lieu de votre événement', 'block-traiteur'); ?> <span class="required">*</span></label>
                <input type="text" id="eventLocation" name="eventLocation" class="form-control" 
                       placeholder="<?php _e('Adresse ou nom du lieu', 'block-traiteur'); ?>">
                <div class="field-help">
                    <?php _e('Indiquez l\'adresse précise ou le nom du lieu', 'block-traiteur'); ?>
                </div>
            </div>
            
            <!-- Code postal -->
            <div class="form-group">
                <label for="postalCode"><?php _e('Code postal', 'block-traiteur'); ?> <span class="required">*</span></label>
                <input type="text" id="postalCode" name="postalCode" class="form-control" 
                       pattern="[0-9]{5}" maxlength="5" 
                       placeholder="67000">
                <div class="field-help">
                    <?php _e('Pour calculer les frais de déplacement', 'block-traiteur'); ?>
                </div>
                <div class="distance-info"></div>
            </div>
        </div>
        
        <!-- Zones de livraison -->
        <div class="delivery-zones">
            <h4><?php _e('Zones de livraison', 'block-traiteur'); ?></h4>
            <div class="zones-grid">
                <div class="zone-card zone-1">
                    <div class="zone-title"><?php _e('Zone 1', 'block-traiteur'); ?></div>
                    <div class="zone-distance">0 - 30 km</div>
                    <div class="zone-price"><?php _e('Gratuit', 'block-traiteur'); ?></div>
                </div>
                <div class="zone-card zone-2">
                    <div class="zone-title"><?php _e('Zone 2', 'block-traiteur'); ?></div>
                    <div class="zone-distance">30 - 50 km</div>
                    <div class="zone-price">+20€</div>
                </div>
                <div class="zone-card zone-3">
                    <div class="zone-title"><?php _e('Zone 3', 'block-traiteur'); ?></div>
                    <div class="zone-distance">50 - 100 km</div>
                    <div class="zone-price">+70€</div>
                </div>
                <div class="zone-card zone-4">
                    <div class="zone-title"><?php _e('Zone 4', 'block-traiteur'); ?></div>
                    <div class="zone-distance">100 - 150 km</div>
                    <div class="zone-price">+118€</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Récapitulatif forfait -->
<div class="package-summary">
    <h4><?php _e('Votre forfait comprend', 'block-traiteur'); ?></h4>
    <div class="package-includes">
        <div class="include-item">
            <span class="include-icon">✓</span>
            <span class="include-text duration-text" id="duration-display">2H de privatisation (service inclus)</span>
        </div>
        <div class="include-item">
            <span class="include-icon">✓</span>
            <span class="include-text"><?php _e('Notre équipe salle + cuisine durant la prestation', 'block-traiteur'); ?></span>
        </div>
        <div class="include-item restaurant-only">
            <span class="include-icon">✓</span>
            <span class="include-text"><?php _e('Mise à disposition des murs de Block', 'block-traiteur'); ?></span>
        </div>
        <div class="include-item remorque-only" style="display: none;">
            <span class="include-icon">✓</span>
            <span class="include-text"><?php _e('Déplacement de la remorque Block (Aller-Retour)', 'block-traiteur'); ?></span>
        </div>
        <div class="include-item remorque-only" style="display: none;">
            <span class="include-icon">✓</span>
            <span class="include-text"><?php _e('Installation complète', 'block-traiteur'); ?></span>
        </div>
        <div class="include-item">
            <span class="include-icon">✓</span>
            <span class="include-text"><?php _e('Présentation et mise en place', 'block-traiteur'); ?></span>
        </div>
        <div class="include-item">
            <span class="include-icon">✓</span>
            <span class="include-text"><?php _e('Vaisselle et verrerie', 'block-traiteur'); ?></span>
        </div>
        <div class="include-item">
            <span class="include-icon">✓</span>
            <span class="include-text"><?php _e('Entretien et nettoyage', 'block-traiteur'); ?></span>
        </div>
    </div>
</div>