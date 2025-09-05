<?php
/**
 * Étape 5: Options - UNIQUEMENT POUR REMORQUE (OPTIONNEL)
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="step-header">
    <h3><?php _e('4. Choix des options', 'block-traiteur'); ?> <span class="optional-label">(Optionnel)</span></h3>
    <p><?php _e('Ajoutez des options pour enrichir votre événement avec la remorque Block', 'block-traiteur'); ?></p>
</div>

<div class="options-section">
    
    <!-- Option 1: Mise à disposition tireuse -->
    <div class="option-card tireuse-option">
        <div class="option-header">
            <div class="option-title">
                <h4><?php _e('Mise à disposition tireuse', 'block-traiteur'); ?></h4>
                <div class="option-price">50€</div>
            </div>
            <div class="option-selector">
                <label class="option-toggle">
                    <input type="checkbox" name="option_tireuse" value="1" id="option-tireuse">
                    <span class="toggle-text"><?php _e('CHOISIR', 'block-traiteur'); ?></span>
                </label>
            </div>
        </div>
        
        <div class="option-description">
            <p><?php _e('Tireuse à bière professionnelle pour servir vos fûts dans les meilleures conditions. Installation et nettoyage inclus.', 'block-traiteur'); ?></p>
            <div class="option-includes">
                <ul>
                    <li><?php _e('Tireuse réfrigérée', 'block-traiteur'); ?></li>
                    <li><?php _e('Installation par notre équipe', 'block-traiteur'); ?></li>
                    <li><?php _e('Nettoyage et désinfection', 'block-traiteur'); ?></li>
                    <li><?php _e('Verres à bière fournis', 'block-traiteur'); ?></li>
                </ul>
            </div>
            <div class="option-note">
                <strong><?php _e('Mention :', 'block-traiteur'); ?></strong>
                <?php _e('Fûts non inclus, à choisir dans la section boissons', 'block-traiteur'); ?>
            </div>
        </div>
        
        <!-- Sélection des fûts (si tireuse sélectionnée) -->
        <div class="tireuse-futs" id="tireuse-futs" style="display: none;">
            <h5><?php _e('Sélectionnez vos fûts', 'block-traiteur'); ?></h5>
            <p class="futs-help"><?php _e('Choisissez les fûts que vous souhaitez utiliser avec la tireuse', 'block-traiteur'); ?></p>
            
            <div class="futs-selection">
                <!-- BLONDES -->
                <div class="futs-category">
                    <h6><?php _e('BLONDES', 'block-traiteur'); ?></h6>
                    <div class="fut-items">
                        <div class="fut-item">
                            <div class="fut-info">
                                <span class="fut-name">Kronenbourg</span>
                                <span class="fut-details">5.2°</span>
                            </div>
                            <div class="fut-sizes">
                                <label class="size-choice">
                                    <input type="checkbox" name="tireuse_futs[]" value="kro_10l">
                                    <span class="size-info">
                                        <span class="size-label">10L</span>
                                        <span class="size-price">45€</span>
                                    </span>
                                </label>
                                <label class="size-choice">
                                    <input type="checkbox" name="tireuse_futs[]" value="kro_20l">
                                    <span class="size-info">
                                        <span class="size-label">20L</span>
                                        <span class="size-price">85€</span>
                                    </span>
                                </label>
                            </div>
                        </div>
                        
                        <div class="fut-item">
                            <div class="fut-info">
                                <span class="fut-name">Stella Artois</span>
                                <span class="fut-details">5.2°</span>
                            </div>
                            <div class="fut-sizes">
                                <label class="size-choice">
                                    <input type="checkbox" name="tireuse_futs[]" value="stella_10l">
                                    <span class="size-info">
                                        <span class="size-label">10L</span>
                                        <span class="size-price">48€</span>
                                    </span>
                                </label>
                                <label class="size-choice">
                                    <input type="checkbox" name="tireuse_futs[]" value="stella_20l">
                                    <span class="size-info">
                                        <span class="size-label">20L</span>
                                        <span class="size-price">90€</span>
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- BLANCHES -->
                <div class="futs-category">
                    <h6><?php _e('BLANCHES', 'block-traiteur'); ?></h6>
                    <div class="fut-items">
                        <div class="fut-item">
                            <div class="fut-info">
                                <span class="fut-name">Hoegaarden</span>
                                <span class="fut-details">4.9°</span>
                            </div>
                            <div class="fut-sizes">
                                <label class="size-choice">
                                    <input type="checkbox" name="tireuse_futs[]" value="hoegaarden_10l">
                                    <span class="size-info">
                                        <span class="size-label">10L</span>
                                        <span class="size-price">52€</span>
                                    </span>
                                </label>
                                <label class="size-choice">
                                    <input type="checkbox" name="tireuse_futs[]" value="hoegaarden_20l">
                                    <span class="size-info">
                                        <span class="size-label">20L</span>
                                        <span class="size-price">95€</span>
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- IPA -->
                <div class="futs-category">
                    <h6><?php _e('IPA', 'block-traiteur'); ?></h6>
                    <div class="fut-items">
                        <div class="fut-item">
                            <div class="fut-info">
                                <span class="fut-name">Brooklyn IPA</span>
                                <span class="fut-details">6.5°</span>
                            </div>
                            <div class="fut-sizes">
                                <label class="size-choice">
                                    <input type="checkbox" name="tireuse_futs[]" value="brooklyn_10l">
                                    <span class="size-info">
                                        <span class="size-label">10L</span>
                                        <span class="size-price">65€</span>
                                    </span>
                                </label>
                                <label class="size-choice">
                                    <input type="checkbox" name="tireuse_futs[]" value="brooklyn_20l">
                                    <span class="size-info">
                                        <span class="size-label">20L</span>
                                        <span class="size-price">120€</span>
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- AMBRÉES -->
                <div class="futs-category">
                    <h6><?php _e('AMBRÉES', 'block-traiteur'); ?></h6>
                    <div class="fut-items">
                        <div class="fut-item">
                            <div class="fut-info">
                                <span class="fut-name">Pelforth Ambrée</span>
                                <span class="fut-details">5.8°</span>
                            </div>
                            <div class="fut-sizes">
                                <label class="size-choice">
                                    <input type="checkbox" name="tireuse_futs[]" value="pelforth_10l">
                                    <span class="size-info">
                                        <span class="size-label">10L</span>
                                        <span class="size-price">55€</span>
                                    </span>
                                </label>
                                <label class="size-choice">
                                    <input type="checkbox" name="tireuse_futs[]" value="pelforth_20l">
                                    <span class="size-info">
                                        <span class="size-label">20L</span>
                                        <span class="size-price">100€</span>
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Option 2: Installation jeux -->
    <div class="option-card jeux-option">
        <div class="option-header">
            <div class="option-title">
                <h4><?php _e('Installation jeux', 'block-traiteur'); ?></h4>
                <div class="option-price">70€</div>
            </div>
            <div class="option-selector">
                <label class="option-toggle">
                    <input type="checkbox" name="option_jeux" value="1" id="option-jeux">
                    <span class="toggle-text"><?php _e('CHOISIR', 'block-traiteur'); ?></span>
                </label>
            </div>
        </div>
        
        <div class="option-description">
            <p><?php _e('Animation ludique pour divertir vos invités pendant l\'événement. Installation et supervision incluses.', 'block-traiteur'); ?></p>
            
            <div class="jeux-listing">
                <h5><?php _e('Jeux inclus :', 'block-traiteur'); ?></h5>
                <div class="jeux-grid">
                    <div class="jeu-item">
                        <div class="jeu-icon">🎯</div>
                        <div class="jeu-name"><?php _e('Fléchettes', 'block-traiteur'); ?></div>
                    </div>
                    <div class="jeu-item">
                        <div class="jeu-icon">🏓</div>
                        <div class="jeu-name"><?php _e('Ping-pong', 'block-traiteur'); ?></div>
                    </div>
                    <div class="jeu-item">
                        <div class="jeu-icon">🎲</div>
                        <div class="jeu-name"><?php _e('Jeux de société', 'block-traiteur'); ?></div>
                    </div>
                    <div class="jeu-item">
                        <div class="jeu-icon">🃏</div>
                        <div class="jeu-name"><?php _e('Jeux de cartes', 'block-traiteur'); ?></div>
                    </div>
                    <div class="jeu-item">
                        <div class="jeu-icon">🎪</div>
                        <div class="jeu-name"><?php _e('Jeux d\'adresse', 'block-traiteur'); ?></div>
                    </div>
                    <div class="jeu-item">
                        <div class="jeu-icon">🎵</div>
                        <div class="jeu-name"><?php _e('Karaoké portable', 'block-traiteur'); ?></div>
                    </div>
                </div>
            </div>
            
            <div class="option-includes">
                <ul>
                    <li><?php _e('Installation complète par notre équipe', 'block-traiteur'); ?></li>
                    <li><?php _e('Supervision et explication des règles', 'block-traiteur'); ?></li>
                    <li><?php _e('Matériel de qualité professionnelle', 'block-traiteur'); ?></li>
                    <li><?php _e('Rangement en fin d\'événement', 'block-traiteur'); ?></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Récapitulatif des options sélectionnées -->
<div class="options-summary" id="options-summary" style="display: none;">
    <h4><?php _e('Options sélectionnées', 'block-traiteur'); ?></h4>
    <div class="selected-options">
        <div class="selected-option tireuse-summary" style="display: none;">
            <div class="option-name"><?php _e('Tireuse + Fûts', 'block-traiteur'); ?></div>
            <div class="option-details" id="tireuse-details"></div>
            <div class="option-total" id="tireuse-total">50€</div>
        </div>
        
        <div class="selected-option jeux-summary" style="display: none;">
            <div class="option-name"><?php _e('Installation jeux', 'block-traiteur'); ?></div>
            <div class="option-details"><?php _e('Animation complète pour vos invités', 'block-traiteur'); ?></div>
            <div class="option-total">70€</div>
        </div>
    </div>
    
    <div class="options-total">
        <strong><?php _e('Total options :', 'block-traiteur'); ?> <span id="total-options">0€</span></strong>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tireuse = document.getElementById('option-tireuse');
    const jeux = document.getElementById('option-jeux');
    const tireuseSection = document.getElementById('tireuse-futs');
    const optionsSummary = document.getElementById('options-summary');
    
    // Gestion de l'option tireuse
    tireuse.addEventListener('change', function() {
        if (this.checked) {
            tireuseSection.style.display = 'block';
        } else {
            tireuseSection.style.display = 'none';
            // Décocher tous les fûts
            document.querySelectorAll('input[name="tireuse_futs[]"]').forEach(input => {
                input.checked = false;
            });
        }
        updateOptionsSummary();
    });
    
    // Gestion de l'option jeux
    jeux.addEventListener('change', function() {
        updateOptionsSummary();
    });
    
    // Gestion des fûts
    document.querySelectorAll('input[name="tireuse_futs[]"]').forEach(input => {
        input.addEventListener('change', function() {
            updateOptionsSummary();
        });
    });
    
    function updateOptionsSummary() {
        const tireuseSummary = document.querySelector('.tireuse-summary');
        const jeuxSummary = document.querySelector('.jeux-summary');
        let total = 0;
        let hasOptions = false;
        
        // Tireuse
        if (tireuse.checked) {
            hasOptions = true;
            total += 50;
            tireuseSummary.style.display = 'block';
            
            // Calculer le total des fûts
            const selectedFuts = document.querySelectorAll('input[name="tireuse_futs[]"]:checked');
            let futsTotal = 0;
            let futsDetails = [];
            
            selectedFuts.forEach(input => {
                const label = input.closest('.size-choice').querySelector('.size-label').textContent;
                const price = parseInt(input.closest('.size-choice').querySelector('.size-price').textContent);
                const name = input.closest('.fut-item').querySelector('.fut-name').textContent;
                
                futsTotal += price;
                futsDetails.push(`${name} ${label}`);
            });
            
            total += futsTotal;
            document.getElementById('tireuse-details').textContent = futsDetails.length > 0 ? futsDetails.join(', ') : 'Aucun fût sélectionné';
            document.getElementById('tireuse-total').textContent = (50 + futsTotal) + '€';
        } else {
            tireuseSummary.style.display = 'none';
        }
        
        // Jeux
        if (jeux.checked) {
            hasOptions = true;
            total += 70;
            jeuxSummary.style.display = 'block';
        } else {
            jeuxSummary.style.display = 'none';
        }
        
        // Affichage du résumé
        if (hasOptions) {
            optionsSummary.style.display = 'block';
            document.getElementById('total-options').textContent = total + '€';
        } else {
            optionsSummary.style.display = 'none';
        }
    }
});
</script>