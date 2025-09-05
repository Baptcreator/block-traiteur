<?php
/**
 * Étape 3: Buffets (optionnel)
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="step-header">
    <h3><?php _e('Buffets complémentaires', 'block-traiteur'); ?></h3>
    <p><?php _e('Enrichissez votre événement avec nos buffets salés et sucrés (optionnel)', 'block-traiteur'); ?></p>
</div>

<div class="buffets-section">
    <!-- Choix du type de buffet -->
    <div class="buffet-type-selector">
        <h4><?php _e('Quel type de buffet souhaitez-vous ?', 'block-traiteur'); ?></h4>
        
        <div class="buffet-options">
            <label class="checkbox-card buffet-type-card">
                <input type="checkbox" name="buffetTypes[]" value="sale">
                <div class="card-content">
                    <div class="card-title"><?php _e('Buffet Salé', 'block-traiteur'); ?></div>
                    <div class="card-description">
                        <?php _e('Mise à disposition des murs de Block (…….)', 'block-traiteur'); ?><br>
                        <strong><?php _e('⚠️ H de privatisation (service inclus, non installée)', 'block-traiteur'); ?></strong><br>
                        <?php _e('Notre équipe salle + cuisine durant la prestation', 'block-traiteur'); ?><br>
                        <?php _e('Présentation + mise en place buffets, selon les choix', 'block-traiteur'); ?><br>
                        <?php _e('Mise à disposition vaisselle + verrerie', 'block-traiteur'); ?><br>
                        <?php _e('Entretien + nettoyage……', 'block-traiteur'); ?>
                    </div>
                    <div class="card-price">
                        <span class="price-amount">300 €</span>
                        <small><?php _e('Montant indicatif estimatif', 'block-traiteur'); ?></small>
                    </div>
                </div>
            </label>
            
            <label class="checkbox-card buffet-type-card">
                <input type="checkbox" name="buffetTypes[]" value="sucre">
                <div class="card-content">
                    <div class="card-title"><?php _e('Buffet Sucré', 'block-traiteur'); ?></div>
                    <div class="card-description">
                        <?php _e('À partir de 20 personnes', 'block-traiteur'); ?><br>
                        <?php _e('Texte descriptif', 'block-traiteur'); ?>
                    </div>
                    <div class="card-price">
                        <span class="price-amount">1200 €</span>
                        <small><?php _e('Montant indicatif estimatif', 'block-traiteur'); ?></small>
                    </div>
                </div>
            </label>
        </div>
    </div>
    
    <!-- Buffet salé - Détails -->
    <div class="buffet-details buffet-sale-details" style="display: none;">
        <h4><?php _e('4️⃣ Choix du/des buffet(s)', 'block-traiteur'); ?></h4>
        
        <div class="buffet-selections">
            <div class="buffet-category">
                <h5><?php _e('🥗 Buffet salé', 'block-traiteur'); ?></h5>
                <div class="note"><?php _e('⭐ = case à cocher par choisir', 'block-traiteur'); ?></div>
                <div class="note"><?php _e('⭐ Buffet = min 1 à choisir', 'block-traiteur'); ?></div>
                
                <div class="products-selection">
                    <p><?php _e('Selon le choix, le déroulé du buffet ou des buffets sélectionnés, s\'affiche (sélection des recettes)', 'block-traiteur'); ?></p>
                    
                    <div class="buffet-pricing">
                        <p><strong><?php _e('⭐ ce montant est celui de départ augmenté en % ou selon le choix et voir selon les priorités', 'block-traiteur'); ?></strong></p>
                        <div class="price-box">
                            <span class="price">1200 €</span>
                            <small><?php _e('Montant indicatif estimatif', 'block-traiteur'); ?></small>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="buffet-category">
                <h5><?php _e('🧁 Buffet sucré', 'block-traiteur'); ?></h5>
                <div class="note"><?php _e('⭐ min 1/personne et min 1 recette', 'block-traiteur'); ?></div>
                
                <div class="sweet-buffet-options">
                    <div class="option-group">
                        <h6><?php _e('1. BUFFET SALÉ = 1..... /G personne / € ⬜', 'block-traiteur'); ?></h6>
                        <h6><?php _e('2..... /pièce / € ⬜', 'block-traiteur'); ?></h6>
                        <h6><?php _e('3..... ~ ~ / € ⬜', 'block-traiteur'); ?></h6>
                        <h6><?php _e('4..... ~ ~ / € ⬜', 'block-traiteur'); ?></h6>
                        <h6><?php _e('5..... ~ ~ / € ⬜', 'block-traiteur'); ?></h6>
                        <h6><?php _e('6..... ~ ~ / € ⬜', 'block-traiteur'); ?></h6>
                        <h6><?php _e('7. Grilled Cheese ~ ~ / € ⬜', 'block-traiteur'); ?></h6>
                        <small><?php _e('5 + 1 € supp Jambon blanc ⬜', 'block-traiteur'); ?></small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Buffet sucré - Détails -->
    <div class="buffet-details buffet-sucre-details" style="display: none;">
        <h4><?php _e('3️⃣ Choix des boissons (optionnel)', 'block-traiteur'); ?></h4>
        <div class="note"><?php _e('⭐ la liste n\'est pas exhaustive mais tu vois l\'esprit !', 'block-traiteur'); ?></div>
        
        <div class="beverage-categories">
            <div class="category-tabs">
                <button type="button" class="tab-btn active" data-category="softs"><?php _e('1️⃣ SOFTS', 'block-traiteur'); ?></button>
                <button type="button" class="tab-btn" data-category="vins"><?php _e('2️⃣ LES VINS', 'block-traiteur'); ?></button>
                <button type="button" class="tab-btn" data-category="bieres"><?php _e('3️⃣ LES BIÈRES BT', 'block-traiteur'); ?></button>
                <button type="button" class="tab-btn" data-category="futs"><?php _e('4️⃣ LES FÛTS', 'block-traiteur'); ?></button>
            </div>
            
            <div class="category-content">
                <div class="beverage-category active" data-category="softs">
                    <div class="beverage-grid" id="softs-products">
                        <!-- Chargé dynamiquement -->
                    </div>
                </div>
                
                <div class="beverage-category" data-category="vins">
                    <div class="wine-sections">
                        <div class="wine-section">
                            <h6><?php _e('BLANCS', 'block-traiteur'); ?></h6>
                            <div class="wine-options">
                                <p>1.... 75 cL ⬜ 1.... 75 cL ⬜</p>
                                <p>2.... 75 cL ⬜ 2.... 75 cL ⬜</p>
                                <p>3.... 75 cL ⬜ 3.... 75 cL ⬜</p>
                            </div>
                        </div>
                        
                        <div class="wine-section">
                            <h6><?php _e('ROUGES', 'block-traiteur'); ?></h6>
                            <div class="wine-options">
                                <p>+ SUGGESTION MOMENT = ..... 75 cL ⬜</p>
                            </div>
                        </div>
                        
                        <div class="wine-section">
                            <h6><?php _e('ROSÉS', 'block-traiteur'); ?></h6>
                            <div class="wine-options">
                                <!-- Options roses -->
                            </div>
                        </div>
                        
                        <div class="wine-section">
                            <h6><?php _e('CRÉMANTS', 'block-traiteur'); ?></h6>
                            <div class="wine-options">
                                <!-- Options crémants -->
                            </div>
                        </div>
                    </div>
                    
                    <div class="wine-note">
                        <p><?php _e('Sa à soulette que les vins, par catégorie, avec degré d\'alcool et quantité en bouteilles = notre sélection viendra après', 'block-traiteur'); ?></p>
                        <p><strong><?php _e('⭐ Textes à intégrer par décrire les boissons', 'block-traiteur'); ?></strong></p>
                    </div>
                </div>
                
                <div class="beverage-category" data-category="bieres">
                    <div class="beer-grid">
                        <div class="beer-section">
                            <h6><?php _e('BLONDES', 'block-traiteur'); ?></h6>
                            <div class="beer-options">
                                <p>1.... ⬜ 30 L / € ⬜ 20 L / €</p>
                                <p>2... ... ⬜ 30 L / € ⬜ 20 L / €</p>
                            </div>
                        </div>
                        
                        <div class="beer-section">
                            <h6><?php _e('BLANCHES', 'block-traiteur'); ?></h6>
                            <div class="beer-options">
                                <!-- Options blanches -->
                            </div>
                        </div>
                        
                        <div class="beer-section">
                            <h6><?php _e('IPA', 'block-traiteur'); ?></h6>
                            <div class="beer-options">
                                <!-- Options IPA -->
                            </div>
                        </div>
                        
                        <div class="beer-section">
                            <h6><?php _e('AMBRÉES', 'block-traiteur'); ?></h6>
                            <div class="beer-options">
                                <!-- Options ambrées -->
                            </div>
                        </div>
                    </div>
                    
                    <div class="beer-pricing">
                        <p><strong><?php _e('⭐ toujours visible et qui augmente ou diminue selon les choix', 'block-traiteur'); ?></strong></p>
                        <div class="price-box">
                            <span class="price">1700 €</span>
                            <small><?php _e('Montant indicatif estimatif', 'block-traiteur'); ?></small>
                        </div>
                        <div class="next-button">
                            <span><?php _e('SUIVANT', 'block-traiteur'); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Skip option -->
    <div class="skip-buffets">
        <p><?php _e('Les buffets ne vous intéressent pas ?', 'block-traiteur'); ?></p>
        <button type="button" class="btn btn-outline skip-step">
            <?php _e('Passer cette étape', 'block-traiteur'); ?>
        </button>
    </div>
</div>

<!-- Récapitulatif buffets -->
<div class="buffets-summary" id="buffets-summary" style="display: none;">
    <h4><?php _e('Vos buffets sélectionnés', 'block-traiteur'); ?></h4>
    <div class="selected-buffets">
        <!-- Rempli dynamiquement -->
    </div>
</div>