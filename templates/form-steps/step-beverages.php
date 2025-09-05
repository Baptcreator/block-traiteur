<?php
/**
 * Étape 4: Choix des boissons - SELON SPÉCIFICATIONS CLIENT (OPTIONNEL)
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="step-header">
    <h3><?php _e('3. Choix des boissons', 'block-traiteur'); ?> <span class="optional-label">(Optionnel)</span></h3>
    <p><?php _e('Étape passable directement. Sélectionnez les boissons pour accompagner votre événement.', 'block-traiteur'); ?></p>
    
    <div class="skip-section">
        <label class="skip-option">
            <input type="checkbox" id="skip-beverages" name="skip_beverages" value="1">
            <span><?php _e('Passer cette étape (pas de boissons)', 'block-traiteur'); ?></span>
        </label>
    </div>
</div>

<div class="beverages-section" id="beverages-content">
    
    <!-- 1. SOFTS -->
    <div class="beverage-category softs-category">
        <h4><?php _e('1. SOFTS', 'block-traiteur'); ?></h4>
        
        <div class="beverage-items">
            <div class="beverage-item">
                <div class="item-info">
                    <h5>Coca-Cola</h5>
                    <div class="item-description">Boisson gazeuse classique</div>
                </div>
                <div class="item-sizes">
                    <div class="size-option">
                        <span class="size-label">5L</span>
                        <span class="size-price">12€</span>
                        <div class="size-quantity">
                            <button type="button" class="qty-btn decrease" data-target="coca-5l">-</button>
                            <input type="number" id="coca-5l" name="beverages[coca_5l]" value="0" min="0" class="qty-input">
                            <button type="button" class="qty-btn increase" data-target="coca-5l">+</button>
                        </div>
                    </div>
                    <div class="size-option">
                        <span class="size-label">20L</span>
                        <span class="size-price">45€</span>
                        <div class="size-quantity">
                            <button type="button" class="qty-btn decrease" data-target="coca-20l">-</button>
                            <input type="number" id="coca-20l" name="beverages[coca_20l]" value="0" min="0" class="qty-input">
                            <button type="button" class="qty-btn increase" data-target="coca-20l">+</button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="beverage-item">
                <div class="item-info">
                    <h5>Orangina</h5>
                    <div class="item-description">Boisson pétillante à l'orange</div>
                </div>
                <div class="item-sizes">
                    <div class="size-option">
                        <span class="size-label">5L</span>
                        <span class="size-price">13€</span>
                        <div class="size-quantity">
                            <button type="button" class="qty-btn decrease" data-target="orangina-5l">-</button>
                            <input type="number" id="orangina-5l" name="beverages[orangina_5l]" value="0" min="0" class="qty-input">
                            <button type="button" class="qty-btn increase" data-target="orangina-5l">+</button>
                        </div>
                    </div>
                    <div class="size-option">
                        <span class="size-label">20L</span>
                        <span class="size-price">48€</span>
                        <div class="size-quantity">
                            <button type="button" class="qty-btn decrease" data-target="orangina-20l">-</button>
                            <input type="number" id="orangina-20l" name="beverages[orangina_20l]" value="0" min="0" class="qty-input">
                            <button type="button" class="qty-btn increase" data-target="orangina-20l">+</button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="beverage-item">
                <div class="item-info">
                    <h5>Jus de Pomme</h5>
                    <div class="item-description">Pur jus de pomme artisanal</div>
                </div>
                <div class="item-sizes">
                    <div class="size-option">
                        <span class="size-label">5L</span>
                        <span class="size-price">15€</span>
                        <div class="size-quantity">
                            <button type="button" class="qty-btn decrease" data-target="pomme-5l">-</button>
                            <input type="number" id="pomme-5l" name="beverages[pomme_5l]" value="0" min="0" class="qty-input">
                            <button type="button" class="qty-btn increase" data-target="pomme-5l">+</button>
                        </div>
                    </div>
                    <div class="size-option">
                        <span class="size-label">10L</span>
                        <span class="size-price">28€</span>
                        <div class="size-quantity">
                            <button type="button" class="qty-btn decrease" data-target="pomme-10l">-</button>
                            <input type="number" id="pomme-10l" name="beverages[pomme_10l]" value="0" min="0" class="qty-input">
                            <button type="button" class="qty-btn increase" data-target="pomme-10l">+</button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="beverage-item">
                <div class="item-info">
                    <h5>Limonade Artisanale</h5>
                    <div class="item-description">Limonade maison au citron frais</div>
                </div>
                <div class="item-sizes">
                    <div class="size-option">
                        <span class="size-label">5L</span>
                        <span class="size-price">16€</span>
                        <div class="size-quantity">
                            <button type="button" class="qty-btn decrease" data-target="limonade-5l">-</button>
                            <input type="number" id="limonade-5l" name="beverages[limonade_5l]" value="0" min="0" class="qty-input">
                            <button type="button" class="qty-btn increase" data-target="limonade-5l">+</button>
                        </div>
                    </div>
                    <div class="size-option">
                        <span class="size-label">20L</span>
                        <span class="size-price">58€</span>
                        <div class="size-quantity">
                            <button type="button" class="qty-btn decrease" data-target="limonade-20l">-</button>
                            <input type="number" id="limonade-20l" name="beverages[limonade_20l]" value="0" min="0" class="qty-input">
                            <button type="button" class="qty-btn increase" data-target="limonade-20l">+</button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="beverage-item">
                <div class="item-info">
                    <h5>Eau</h5>
                    <div class="item-description">Eau plate ou gazeuse</div>
                </div>
                <div class="item-sizes">
                    <div class="size-option">
                        <span class="size-label">50cL</span>
                        <span class="size-price">1.50€</span>
                        <div class="size-quantity">
                            <button type="button" class="qty-btn decrease" data-target="eau-50cl">-</button>
                            <input type="number" id="eau-50cl" name="beverages[eau_50cl]" value="0" min="0" class="qty-input">
                            <button type="button" class="qty-btn increase" data-target="eau-50cl">+</button>
                        </div>
                    </div>
                    <div class="size-option">
                        <span class="size-label">1L</span>
                        <span class="size-price">2.50€</span>
                        <div class="size-quantity">
                            <button type="button" class="qty-btn decrease" data-target="eau-1l">-</button>
                            <input type="number" id="eau-1l" name="beverages[eau_1l]" value="0" min="0" class="qty-input">
                            <button type="button" class="qty-btn increase" data-target="eau-1l">+</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. LES VINS -->
    <div class="beverage-category wines-category">
        <h4><?php _e('2. LES VINS', 'block-traiteur'); ?></h4>
        
        <div class="wine-sections">
            <div class="wine-section">
                <h5><?php _e('BLANCS', 'block-traiteur'); ?></h5>
                <div class="wine-items">
                    <div class="wine-item">
                        <div class="wine-info">
                            <h6>Riesling d'Alsace</h6>
                            <div class="wine-details">75cL - 12.5°</div>
                            <div class="wine-description">Vin blanc sec, fruité et minéral</div>
                        </div>
                        <div class="wine-price">18€</div>
                        <div class="wine-quantity">
                            <button type="button" class="qty-btn decrease" data-target="riesling">-</button>
                            <input type="number" id="riesling" name="beverages[riesling]" value="0" min="0" class="qty-input">
                            <button type="button" class="qty-btn increase" data-target="riesling">+</button>
                        </div>
                    </div>
                    
                    <div class="wine-item">
                        <div class="wine-info">
                            <h6>Gewurztraminer</h6>
                            <div class="wine-details">75cL - 13°</div>
                            <div class="wine-description">Vin blanc aromatique aux notes florales</div>
                        </div>
                        <div class="wine-price">20€</div>
                        <div class="wine-quantity">
                            <button type="button" class="qty-btn decrease" data-target="gewurz">-</button>
                            <input type="number" id="gewurz" name="beverages[gewurz]" value="0" min="0" class="qty-input">
                            <button type="button" class="qty-btn increase" data-target="gewurz">+</button>
                        </div>
                    </div>
                    
                    <div class="wine-item">
                        <div class="wine-info">
                            <h6>Pinot Blanc</h6>
                            <div class="wine-details">75cL - 12°</div>
                            <div class="wine-description">Vin blanc frais et équilibré</div>
                        </div>
                        <div class="wine-price">16€</div>
                        <div class="wine-quantity">
                            <button type="button" class="qty-btn decrease" data-target="pinot-blanc">-</button>
                            <input type="number" id="pinot-blanc" name="beverages[pinot_blanc]" value="0" min="0" class="qty-input">
                            <button type="button" class="qty-btn increase" data-target="pinot-blanc">+</button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="wine-section">
                <h5><?php _e('ROUGES', 'block-traiteur'); ?></h5>
                <div class="wine-items">
                    <div class="wine-item">
                        <div class="wine-info">
                            <h6>Pinot Noir d'Alsace</h6>
                            <div class="wine-details">75cL - 13°</div>
                            <div class="wine-description">Vin rouge léger et fruité</div>
                        </div>
                        <div class="wine-price">19€</div>
                        <div class="wine-quantity">
                            <button type="button" class="qty-btn decrease" data-target="pinot-noir">-</button>
                            <input type="number" id="pinot-noir" name="beverages[pinot_noir]" value="0" min="0" class="qty-input">
                            <button type="button" class="qty-btn increase" data-target="pinot-noir">+</button>
                        </div>
                    </div>
                    
                    <div class="wine-item">
                        <div class="wine-info">
                            <h6>Côtes du Rhône</h6>
                            <div class="wine-details">75cL - 14°</div>
                            <div class="wine-description">Vin rouge généreux et épicé</div>
                        </div>
                        <div class="wine-price">17€</div>
                        <div class="wine-quantity">
                            <button type="button" class="qty-btn decrease" data-target="cotes-rhone">-</button>
                            <input type="number" id="cotes-rhone" name="beverages[cotes_rhone]" value="0" min="0" class="qty-input">
                            <button type="button" class="qty-btn increase" data-target="cotes-rhone">+</button>
                        </div>
                    </div>
                    
                    <div class="wine-item">
                        <div class="wine-info">
                            <h6>Beaujolais Villages</h6>
                            <div class="wine-details">75cL - 13°</div>
                            <div class="wine-description">Vin rouge souple et gourmand</div>
                        </div>
                        <div class="wine-price">15€</div>
                        <div class="wine-quantity">
                            <button type="button" class="qty-btn decrease" data-target="beaujolais">-</button>
                            <input type="number" id="beaujolais" name="beverages[beaujolais]" value="0" min="0" class="qty-input">
                            <button type="button" class="qty-btn increase" data-target="beaujolais">+</button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="wine-section">
                <h5><?php _e('ROSÉS', 'block-traiteur'); ?></h5>
                <div class="wine-items">
                    <div class="wine-item">
                        <div class="wine-info">
                            <h6>Rosé de Provence</h6>
                            <div class="wine-details">75cL - 12.5°</div>
                            <div class="wine-description">Rosé frais aux arômes de fruits rouges</div>
                        </div>
                        <div class="wine-price">16€</div>
                        <div class="wine-quantity">
                            <button type="button" class="qty-btn decrease" data-target="rose-provence">-</button>
                            <input type="number" id="rose-provence" name="beverages[rose_provence]" value="0" min="0" class="qty-input">
                            <button type="button" class="qty-btn increase" data-target="rose-provence">+</button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="wine-section">
                <h5><?php _e('CRÉMANTS', 'block-traiteur'); ?></h5>
                <div class="wine-items">
                    <div class="wine-item">
                        <div class="wine-info">
                            <h6>Crémant d'Alsace</h6>
                            <div class="wine-details">75cL - 12°</div>
                            <div class="wine-description">Effervescent élégant et festif</div>
                        </div>
                        <div class="wine-price">22€</div>
                        <div class="wine-quantity">
                            <button type="button" class="qty-btn decrease" data-target="cremant">-</button>
                            <input type="number" id="cremant" name="beverages[cremant]" value="0" min="0" class="qty-input">
                            <button type="button" class="qty-btn increase" data-target="cremant">+</button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="wine-section suggestion">
                <h5><?php _e('SUGGESTION DU MOMENT', 'block-traiteur'); ?></h5>
                <div class="wine-items">
                    <div class="wine-item special">
                        <div class="wine-info">
                            <h6>Cuvée Spéciale Block</h6>
                            <div class="wine-details">75cL - 13.5°</div>
                            <div class="wine-description">Notre sélection exclusive du moment</div>
                        </div>
                        <div class="wine-price">25€</div>
                        <div class="wine-quantity">
                            <button type="button" class="qty-btn decrease" data-target="cuvee-speciale">-</button>
                            <input type="number" id="cuvee-speciale" name="beverages[cuvee_speciale]" value="0" min="0" class="qty-input">
                            <button type="button" class="qty-btn increase" data-target="cuvee-speciale">+</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. LES BIÈRES BOUTEILLES -->
    <div class="beverage-category beers-category">
        <h4><?php _e('3. LES BIÈRES BOUTEILLES', 'block-traiteur'); ?></h4>
        
        <div class="beer-sections">
            <div class="beer-section">
                <h5><?php _e('BLONDES', 'block-traiteur'); ?></h5>
                <div class="beer-items">
                    <div class="beer-item">
                        <div class="beer-info">
                            <h6>Kronenbourg 1664</h6>
                            <div class="beer-details">33cL - 5.5°</div>
                        </div>
                        <div class="beer-price">3.50€</div>
                        <div class="beer-quantity">
                            <button type="button" class="qty-btn decrease" data-target="kro-1664">-</button>
                            <input type="number" id="kro-1664" name="beverages[kro_1664]" value="0" min="0" class="qty-input">
                            <button type="button" class="qty-btn increase" data-target="kro-1664">+</button>
                        </div>
                    </div>
                    
                    <div class="beer-item">
                        <div class="beer-info">
                            <h6>Stella Artois</h6>
                            <div class="beer-details">33cL - 5.2°</div>
                        </div>
                        <div class="beer-price">3.80€</div>
                        <div class="beer-quantity">
                            <button type="button" class="qty-btn decrease" data-target="stella">-</button>
                            <input type="number" id="stella" name="beverages[stella]" value="0" min="0" class="qty-input">
                            <button type="button" class="qty-btn increase" data-target="stella">+</button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="beer-section">
                <h5><?php _e('BLANCHES', 'block-traiteur'); ?></h5>
                <div class="beer-items">
                    <div class="beer-item">
                        <div class="beer-info">
                            <h6>Hoegaarden</h6>
                            <div class="beer-details">33cL - 4.9°</div>
                        </div>
                        <div class="beer-price">4€</div>
                        <div class="beer-quantity">
                            <button type="button" class="qty-btn decrease" data-target="hoegaarden">-</button>
                            <input type="number" id="hoegaarden" name="beverages[hoegaarden]" value="0" min="0" class="qty-input">
                            <button type="button" class="qty-btn increase" data-target="hoegaarden">+</button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="beer-section">
                <h5><?php _e('IPA', 'block-traiteur'); ?></h5>
                <div class="beer-items">
                    <div class="beer-item">
                        <div class="beer-info">
                            <h6>Brooklyn IPA</h6>
                            <div class="beer-details">33cL - 6.5°</div>
                        </div>
                        <div class="beer-price">5€</div>
                        <div class="beer-quantity">
                            <button type="button" class="qty-btn decrease" data-target="brooklyn-ipa">-</button>
                            <input type="number" id="brooklyn-ipa" name="beverages[brooklyn_ipa]" value="0" min="0" class="qty-input">
                            <button type="button" class="qty-btn increase" data-target="brooklyn-ipa">+</button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="beer-section">
                <h5><?php _e('AMBRÉES', 'block-traiteur'); ?></h5>
                <div class="beer-items">
                    <div class="beer-item">
                        <div class="beer-info">
                            <h6>Pelforth Ambrée</h6>
                            <div class="beer-details">33cL - 5.8°</div>
                        </div>
                        <div class="beer-price">4.20€</div>
                        <div class="beer-quantity">
                            <button type="button" class="qty-btn decrease" data-target="pelforth">-</button>
                            <input type="number" id="pelforth" name="beverages[pelforth]" value="0" min="0" class="qty-input">
                            <button type="button" class="qty-btn increase" data-target="pelforth">+</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. LES FÛTS -->
    <div class="beverage-category kegs-category">
        <h4><?php _e('4. LES FÛTS', 'block-traiteur'); ?></h4>
        
        <div class="keg-sections">
            <div class="keg-section">
                <h5><?php _e('BLONDES', 'block-traiteur'); ?></h5>
                <div class="keg-items">
                    <div class="keg-item">
                        <div class="keg-info">
                            <h6>Kronenbourg</h6>
                            <div class="keg-details">5.2°</div>
                        </div>
                        <div class="keg-sizes">
                            <div class="size-option">
                                <span class="size-label">10L</span>
                                <span class="size-price">45€</span>
                                <div class="size-quantity">
                                    <button type="button" class="qty-btn decrease" data-target="kro-fut-10l">-</button>
                                    <input type="number" id="kro-fut-10l" name="beverages[kro_fut_10l]" value="0" min="0" class="qty-input">
                                    <button type="button" class="qty-btn increase" data-target="kro-fut-10l">+</button>
                                </div>
                            </div>
                            <div class="size-option">
                                <span class="size-label">20L</span>
                                <span class="size-price">85€</span>
                                <div class="size-quantity">
                                    <button type="button" class="qty-btn decrease" data-target="kro-fut-20l">-</button>
                                    <input type="number" id="kro-fut-20l" name="beverages[kro_fut_20l]" value="0" min="0" class="qty-input">
                                    <button type="button" class="qty-btn increase" data-target="kro-fut-20l">+</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="keg-item">
                        <div class="keg-info">
                            <h6>Stella Artois</h6>
                            <div class="keg-details">5.2°</div>
                        </div>
                        <div class="keg-sizes">
                            <div class="size-option">
                                <span class="size-label">10L</span>
                                <span class="size-price">48€</span>
                                <div class="size-quantity">
                                    <button type="button" class="qty-btn decrease" data-target="stella-fut-10l">-</button>
                                    <input type="number" id="stella-fut-10l" name="beverages[stella_fut_10l]" value="0" min="0" class="qty-input">
                                    <button type="button" class="qty-btn increase" data-target="stella-fut-10l">+</button>
                                </div>
                            </div>
                            <div class="size-option">
                                <span class="size-label">20L</span>
                                <span class="size-price">90€</span>
                                <div class="size-quantity">
                                    <button type="button" class="qty-btn decrease" data-target="stella-fut-20l">-</button>
                                    <input type="number" id="stella-fut-20l" name="beverages[stella_fut_20l]" value="0" min="0" class="qty-input">
                                    <button type="button" class="qty-btn increase" data-target="stella-fut-20l">+</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Autres sections de fûts similaires -->
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const skipCheckbox = document.getElementById('skip-beverages');
    const beveragesContent = document.getElementById('beverages-content');
    
    // Gestion du skip
    skipCheckbox.addEventListener('change', function() {
        if (this.checked) {
            beveragesContent.style.display = 'none';
            // Réinitialiser tous les inputs
            document.querySelectorAll('#beverages-content .qty-input').forEach(input => {
                input.value = 0;
            });
        } else {
            beveragesContent.style.display = 'block';
        }
    });
    
    // Gestion des boutons quantité
    document.querySelectorAll('.qty-btn').forEach(button => {
        button.addEventListener('click', function() {
            const target = this.getAttribute('data-target');
            const input = document.getElementById(target);
            const isIncrease = this.classList.contains('increase');
            
            let currentValue = parseInt(input.value) || 0;
            
            if (isIncrease) {
                input.value = currentValue + 1;
            } else if (currentValue > 0) {
                input.value = currentValue - 1;
            }
        });
    });
});
</script>