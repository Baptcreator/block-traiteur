<?php
/**
 * Étape 5: Options (remorque uniquement)
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="step-header">
    <h3><?php _e('Options supplémentaires', 'block-traiteur'); ?></h3>
    <p><?php _e('Personnalisez votre événement avec nos options exclusives pour la remorque', 'block-traiteur'); ?></p>
</div>

<div class="options-section">
    <!-- Options disponibles -->
    <div class="options-grid">
        <!-- Option Tireuse -->
        <div class="option-card">
            <div class="option-icon">
                <span class="icon">🍺</span>
            </div>
            
            <div class="option-content">
                <h4><?php _e('Mise à disposition tireuse', 'block-traiteur'); ?></h4>
                <div class="option-price">50 €</div>
                <p class="option-description">
                    <?php _e('Description + matériel (fûts non inclus, à choisir)', 'block-traiteur'); ?>
                </p>
                
                <label class="option-selector">
                    <input type="checkbox" name="options[]" value="tireuse" data-price="50">
                    <div class="selector-content">
                        <span class="selector-text"><?php _e('Ajouter cette option', 'block-traiteur'); ?></span>
                        <div class="selector-button">
                            <span class="button-text"><?php _e('CHOISIR', 'block-traiteur'); ?></span>
                        </div>
                    </div>
                </label>
            </div>
        </div>
        
        <!-- Option Jeux -->
        <div class="option-card">
            <div class="option-icon">
                <span class="icon">🎯</span>
            </div>
            
            <div class="option-content">
                <h4><?php _e('Installation jeux', 'block-traiteur'); ?></h4>
                <div class="option-price">70 €</div>
                <p class="option-description">
                    <?php _e('Description avec listing (fléchettes, billard, baby foot)', 'block-traiteur'); ?>
                </p>
                
                <label class="option-selector">
                    <input type="checkbox" name="options[]" value="jeux" data-price="70">
                    <div class="selector-content">
                        <span class="selector-text"><?php _e('Ajouter cette option', 'block-traiteur'); ?></span>
                        <div class="selector-button">
                            <span class="button-text"><?php _e('CHOISIR', 'block-traiteur'); ?></span>
                        </div>
                    </div>
                </label>
            </div>
        </div>
    </div>
    
    <!-- Détails des options sélectionnées -->
    <div class="selected-options-details">
        <!-- Détails tireuse -->
        <div class="option-details tireuse-details" style="display: none;">
            <h5><?php _e('🍺 Configuration de la tireuse', 'block-traiteur'); ?></h5>
            <p><?php _e('Sélectionnez vos fûts dans l\'étape précédente (Boissons > Fûts)', 'block-traiteur'); ?></p>
            
            <div class="tireuse-info">
                <div class="info-grid">
                    <div class="info-item">
                        <strong><?php _e('Installation :', 'block-traiteur'); ?></strong>
                        <span><?php _e('Comprise dans le service', 'block-traiteur'); ?></span>
                    </div>
                    <div class="info-item">
                        <strong><?php _e('Maintenance :', 'block-traiteur'); ?></strong>
                        <span><?php _e('Assurée par notre équipe', 'block-traiteur'); ?></span>
                    </div>
                    <div class="info-item">
                        <strong><?php _e('Nettoyage :', 'block-traiteur'); ?></strong>
                        <span><?php _e('Inclus', 'block-traiteur'); ?></span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Détails jeux -->
        <div class="option-details jeux-details" style="display: none;">
            <h5><?php _e('🎯 Jeux disponibles', 'block-traiteur'); ?></h5>
            
            <div class="games-list">
                <div class="game-item">
                    <span class="game-icon">🎯</span>
                    <div class="game-info">
                        <strong><?php _e('Fléchettes', 'block-traiteur'); ?></strong>
                        <p><?php _e('Jeu de fléchettes professionnel avec cibles', 'block-traiteur'); ?></p>
                    </div>
                </div>
                
                <div class="game-item">
                    <span class="game-icon">🎱</span>
                    <div class="game-info">
                        <strong><?php _e('Billard', 'block-traiteur'); ?></strong>
                        <p><?php _e('Table de billard avec accessoires complets', 'block-traiteur'); ?></p>
                    </div>
                </div>
                
                <div class="game-item">
                    <span class="game-icon">⚽</span>
                    <div class="game-info">
                        <strong><?php _e('Baby-foot', 'block-traiteur'); ?></strong>
                        <p><?php _e('Baby-foot professionnel pour tous les âges', 'block-traiteur'); ?></p>
                    </div>
                </div>
            </div>
            
            <div class="games-note">
                <p><strong><?php _e('Note :', 'block-traiteur'); ?></strong> <?php _e('L\'installation et le rangement des jeux sont inclus dans le prix de l\'option', 'block-traiteur'); ?></p>
            </div>
        </div>
    </div>
    
    <!-- Message si aucune option -->
    <div class="no-options" id="no-options-message">
        <div class="message-content">
            <h4><?php _e('Aucune option sélectionnée', 'block-traiteur'); ?></h4>
            <p><?php _e('Ce n\'est pas un problème ! Vous pouvez passer directement à l\'étape suivante.', 'block-traiteur'); ?></p>
            <p><?php _e('Les options peuvent toujours être ajoutées lors de notre entretien téléphonique.', 'block-traiteur'); ?></p>
        </div>
    </div>
    
    <!-- Note importante -->
    <div class="options-note">
        <div class="note-content">
            <h5><?php _e('📋 Important à savoir', 'block-traiteur'); ?></h5>
            <ul>
                <li><?php _e('Ces options sont exclusivement disponibles avec la remorque', 'block-traiteur'); ?></li>
                <li><?php _e('L\'installation est toujours comprise dans le prix', 'block-traiteur'); ?></li>
                <li><?php _e('Notre équipe assure le bon fonctionnement pendant l\'événement', 'block-traiteur'); ?></li>
                <li><?php _e('Possibilité d\'ajouter d\'autres options sur demande', 'block-traiteur'); ?></li>
            </ul>
        </div>
    </div>
</div>

<!-- Récapitulatif options -->
<div class="options-summary" id="options-summary" style="display: none;">
    <h4><?php _e('Vos options sélectionnées', 'block-traiteur'); ?></h4>
    <div class="selected-options-list">
        <!-- Rempli dynamiquement -->
    </div>
    
    <div class="options-total">
        <span class="label"><?php _e('Total options :', 'block-traiteur'); ?></span>
        <span class="amount" id="options-total-amount">0 € TTC</span>
    </div>
</div>