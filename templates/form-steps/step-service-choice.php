<?php
/**
 * Étape 0: Choix du service - Selon spécifications client
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="step-header">
    <h3><?php _e('Privatisation Block', 'block-traiteur'); ?></h3>
    <p><?php _e('Choisissez le type de privatisation qui correspond à votre événement', 'block-traiteur'); ?></p>
</div>

<div class="service-options">
    <div class="service-card" data-service="restaurant">
        <div class="service-icon">
            <img src="<?php echo BLOCK_TRAITEUR_PLUGIN_URL; ?>assets/images/restaurant-icon.svg" 
                 alt="<?php _e('Restaurant', 'block-traiteur'); ?>" 
                 class="service-image">
        </div>
        
        <div class="service-content">
            <h4><?php _e('Privatisation du Restaurant', 'block-traiteur'); ?></h4>
            <div class="service-capacity"><?php _e('De 10 à 30 personnes', 'block-traiteur'); ?></div>
            <p><?php _e('Privatisez notre restaurant Block pour votre événement dans un cadre intimiste et chaleureux.', 'block-traiteur'); ?></p>
            
            <div class="service-details">
                <h5><?php _e('Pourquoi privatiser notre restaurant ?', 'block-traiteur'); ?></h5>
                <ul class="service-features">
                    <li><?php _e('Ambiance cosy et authentique', 'block-traiteur'); ?></li>
                    <li><?php _e('Équipe dédiée sur place', 'block-traiteur'); ?></li>
                    <li><?php _e('Pas de frais de déplacement', 'block-traiteur'); ?></li>
                    <li><?php _e('Cadre unique au cœur de Strasbourg', 'block-traiteur'); ?></li>
                </ul>
                
                <h5><?php _e('Comment ça fonctionne ?', 'block-traiteur'); ?></h5>
                <ol class="service-process">
                    <li><?php _e('Forfait de base', 'block-traiteur'); ?></li>
                    <li><?php _e('Choix des formules repas (personnalisable)', 'block-traiteur'); ?></li>
                    <li><?php _e('Choix des boissons (optionnel)', 'block-traiteur'); ?></li>
                    <li><?php _e('Coordonnées / Contact', 'block-traiteur'); ?></li>
                </ol>
            </div>
        </div>
        
        <div class="service-action">
            <button type="button" class="btn btn-primary select-service" data-service="restaurant">
                <?php _e('CHOISIR', 'block-traiteur'); ?>
            </button>
        </div>
    </div>
    
    <div class="service-card" data-service="remorque">
        <div class="service-icon">
            <img src="<?php echo BLOCK_TRAITEUR_PLUGIN_URL; ?>assets/images/remorque-icon.svg" 
                 alt="<?php _e('Remorque Block', 'block-traiteur'); ?>" 
                 class="service-image">
        </div>
        
        <div class="service-content">
            <h4><?php _e('Privatisation de la Remorque Block', 'block-traiteur'); ?></h4>
            <div class="service-capacity"><?php _e('À partir de 20 personnes', 'block-traiteur'); ?></div>
            <p><?php _e('La remorque Block se déplace sur votre lieu d\'événement pour une expérience unique et mobile.', 'block-traiteur'); ?></p>
            
            <div class="service-details">
                <h5><?php _e('Pourquoi privatiser notre remorque Block ?', 'block-traiteur'); ?></h5>
                <ul class="service-features">
                    <li><?php _e('Service mobile à domicile', 'block-traiteur'); ?></li>
                    <li><?php _e('Capacité jusqu\'à 100 personnes', 'block-traiteur'); ?></li>
                    <li><?php _e('Expérience street food authentique', 'block-traiteur'); ?></li>
                    <li><?php _e('Parfait pour événements extérieurs', 'block-traiteur'); ?></li>
                </ul>
                
                <h5><?php _e('Comment ça fonctionne ?', 'block-traiteur'); ?></h5>
                <ol class="service-process">
                    <li><?php _e('Forfait de base', 'block-traiteur'); ?></li>
                    <li><?php _e('Choix des formules repas (personnalisable)', 'block-traiteur'); ?></li>
                    <li><?php _e('Choix des boissons (optionnel)', 'block-traiteur'); ?></li>
                    <li><?php _e('Choix des options (optionnel)', 'block-traiteur'); ?></li>
                    <li><?php _e('Coordonnées / Contact', 'block-traiteur'); ?></li>
                </ol>
            </div>
        </div>
        
        <div class="service-action">
            <button type="button" class="btn btn-primary select-service" data-service="remorque">
                <?php _e('CHOISIR', 'block-traiteur'); ?>
            </button>
        </div>
    </div>
</div>

<input type="hidden" name="serviceType" id="serviceType" value="">