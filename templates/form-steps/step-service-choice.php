<?php
/**
 * Étape 0: Choix du service
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="step-header">
    <h3><?php _e('Quel service vous intéresse ?', 'block-traiteur'); ?></h3>
    <p><?php _e('Choisissez le type de prestation qui correspond le mieux à votre événement', 'block-traiteur'); ?></p>
</div>

<div class="service-options">
    <div class="service-card" data-service="restaurant">
        <div class="service-icon">
            <img src="<?php echo BLOCK_TRAITEUR_PLUGIN_URL; ?>assets/images/restaurant-icon.svg" 
                 alt="<?php _e('Restaurant', 'block-traiteur'); ?>" 
                 class="service-image">
        </div>
        
        <div class="service-content">
            <h4><?php _e('Privatisation Restaurant', 'block-traiteur'); ?></h4>
            <p><?php _e('Privatisez notre restaurant Block pour votre événement privé', 'block-traiteur'); ?></p>
            
            <ul class="service-features">
                <li><?php _e('10 à 30 invités', 'block-traiteur'); ?></li>
                <li><?php _e('Ambiance cosy et authentique', 'block-traiteur'); ?></li>
                <li><?php _e('Équipe dédiée sur place', 'block-traiteur'); ?></li>
                <li><?php _e('Pas de frais de déplacement', 'block-traiteur'); ?></li>
            </ul>
            
            <!-- Prix supprimé selon les spécifications -->
        </div>
        
        <div class="service-action">
            <button type="button" class="btn btn-primary select-service" data-service="restaurant">
                <?php _e('Choisir ce service', 'block-traiteur'); ?>
            </button>
        </div>
    </div>
    
    <div class="service-card" data-service="remorque">
        <div class="service-icon">
            <img src="<?php echo BLOCK_TRAITEUR_PLUGIN_URL; ?>assets/images/remorque-icon.svg" 
                 alt="<?php _e('Remorque', 'block-traiteur'); ?>" 
                 class="service-image">
        </div>
        
        <div class="service-content">
            <h4><?php _e('Remorque Mobile', 'block-traiteur'); ?></h4>
            <p><?php _e('Notre remorque Block se déplace sur le lieu de votre choix', 'block-traiteur'); ?></p>
            
            <ul class="service-features">
                <li><?php _e('20 à 100 invités', 'block-traiteur'); ?></li>
                <li><?php _e('Flexibilité totale du lieu', 'block-traiteur'); ?></li>
                <li><?php _e('Équipement professionnel mobile', 'block-traiteur'); ?></li>
                <li><?php _e('Options supplémentaires disponibles', 'block-traiteur'); ?></li>
            </ul>
            
            <!-- Prix supprimé selon les spécifications -->
        </div>
        
        <div class="service-action">
            <button type="button" class="btn btn-primary select-service" data-service="remorque">
                <?php _e('Choisir ce service', 'block-traiteur'); ?>
            </button>
        </div>
    </div>
</div>

<div class="service-comparison">
    <h4><?php _e('Besoin d\'aide pour choisir ?', 'block-traiteur'); ?></h4>
    
    <div class="comparison-table">
        <table>
            <thead>
                <tr>
                    <th></th>
                    <th><?php _e('Restaurant', 'block-traiteur'); ?></th>
                    <th><?php _e('Remorque', 'block-traiteur'); ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?php _e('Nombre d\'invités', 'block-traiteur'); ?></td>
                    <td>10 - 30</td>
                    <td>20 - 100</td>
                </tr>
                <tr>
                    <td><?php _e('Lieu', 'block-traiteur'); ?></td>
                    <td><?php _e('Restaurant Block', 'block-traiteur'); ?></td>
                    <td><?php _e('Votre choix', 'block-traiteur'); ?></td>
                </tr>
                <tr>
                    <td><?php _e('Durée', 'block-traiteur'); ?></td>
                    <td>2 - 4h</td>
                    <td>2 - 5h</td>
                </tr>
                <tr>
                    <td><?php _e('Déplacement', 'block-traiteur'); ?></td>
                    <td><?php _e('Inclus', 'block-traiteur'); ?></td>
                    <td><?php _e('Selon distance', 'block-traiteur'); ?></td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <div class="help-contact">
        <a href="tel:<?php echo esc_attr(Block_Traiteur_Cache::get_settings()['company_phone']); ?>" 
           class="btn btn-outline help-btn">
            <span class="btn-icon">📞</span>
            <?php _e('Encore des questions ?', 'block-traiteur'); ?>
        </a>
    </div>
</div>

<input type="hidden" name="serviceType" id="serviceType" value="">