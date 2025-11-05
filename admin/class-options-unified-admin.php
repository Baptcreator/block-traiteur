<?php
/**
 * Classe d'administration des Options Unifiées
 * Gère toutes les options configurables du plugin (restaurant et remorque)
 *
 * @package RestaurantBooking
 * @since 2.1.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class RestaurantBooking_Options_Unified_Admin
{
    /**
     * Options par défaut
     */
    private $default_options = array(
        // Règles de validation produits
        'buffet_sale_min_per_person' => 1,
        'buffet_sale_min_recipes' => 2,
        'buffet_sale_text' => 'min 1/personne et min 2 recettes différents',
        
        'buffet_sucre_min_per_person' => 1,
        'buffet_sucre_min_dishes' => 1,
        'buffet_sucre_text' => 'min 1/personne et min 1 plat',
        
        'accompaniment_min_per_person' => 1,
        'accompaniment_text' => 'minimum 1/personne',
        
        'signature_dish_min_per_person' => 1,
        'signature_dish_text' => 'exactement 1 plat par personne',
        
        // Limites privatisation restaurant
        'restaurant_min_guests' => 10,
        'restaurant_max_guests' => 30,
        'restaurant_guests_text' => 'De 10 à 30 personnes',
        
        'restaurant_min_duration' => 2,
        'restaurant_max_duration_included' => 2,
        'restaurant_extra_hour_price' => 50,
        'restaurant_duration_text' => 'min durée = 2H (compris) max durée = 4H (supplément de +50 €/TTC/H)',
        
        // Limites privatisation remorque
        'remorque_min_guests' => 20,
        'remorque_max_guests' => 100,
        'remorque_staff_threshold' => 50,
        'remorque_staff_supplement' => 150,
        'remorque_guests_text' => 'À partir de 20 personnes',
        'remorque_staff_text' => 'au delà de 50p 1 forfait de +150€ s\'applique',
        
        'remorque_min_duration' => 2,
        'remorque_max_duration' => 5,
        'remorque_extra_hour_price' => 50,
        
        // Distance/Déplacement (utilisé par Google Maps)
        'free_radius_km' => 30,
        'price_30_50km' => 20,
        'price_50_100km' => 70,
        'price_100_150km' => 120,
        'max_distance_km' => 150,
        
        // Prix options remorque
        'tireuse_price' => 50,
        'games_price' => 70,
        
        // Textes étape 0 - Sélection du service
        'widget_title' => 'Demande de Devis Privatisation',
        'widget_subtitle' => 'Choisissez votre service et obtenez votre devis personnalisé',
        'service_selection_title' => 'Choisissez votre service',
        'restaurant_card_title' => 'PRIVATISATION DU RESTAURANT',
        'restaurant_card_subtitle' => 'De 10 à 30 personnes',
        'restaurant_card_description' => 'Privatisez notre restaurant pour vos événements intimes et profitez d\'un service personnalisé dans un cadre chaleureux.',
        'remorque_card_title' => 'Privatisation de la remorque Block',
        'remorque_card_subtitle' => 'À partir de 20 personnes',
        'remorque_card_description' => 'Notre remorque mobile se déplace pour vos événements extérieurs et grandes réceptions.',
        
        // Textes étape 1 - Introduction
        'step1_title_restaurant' => 'Pourquoi privatiser notre restaurant ?',
        'step1_title_remorque' => 'Pourquoi privatiser notre remorque Block ?',
        'step1_card_title' => 'Comment ça fonctionne ?',
        'restaurant_steps_list' => 'Forfait de base|Choix du formule repas (personnalisable)|Choix des boissons (optionnel)|Coordonnées / Contact',
        'remorque_steps_list' => 'Forfait de base|Choix du formule repas (personnalisable)|Choix des boissons (optionnel)|Choix des options (optionnel)|Coordonnées/Contact',
        
        // Textes étape 2 - Forfait de base
        'step2_title' => 'Forfait de base',
        'restaurant_forfait_card_title' => 'FORFAIT DE BASE PRIVATISATION RESTO',
        'remorque_forfait_card_title' => 'FORFAIT DE BASE PRIVATISATION REMORQUE BLOCK',
        'restaurant_forfait_description' => 'Mise à disposition des murs de Block|Notre équipe salle + cuisine assurant la prestation|Présentation + mise en place buffets, selon vos choix|Mise à disposition vaisselle + verrerie|Entretien + nettoyage',
        'remorque_forfait_description' => 'Notre équipe salle + cuisine assurant la prestation|Déplacement et installation de la remorque BLOCK (aller et retour)|Présentation + mise en place buffets, selon vos choix|La fourniture de vaisselle jetable recyclable|La fourniture de verrerie (en cas d\'ajout de boisson)',
        
        // Textes étape 3 - Choix des repas
        'step3_title' => 'Choix du repas',
        'step3_signature_title' => 'PLAT SIGNATURE',
        'step3_hot_dogs_title' => 'NOS HOT-DOGS',
        'step3_croques_title' => 'NOS CROQUES',
        'step3_mini_boss_title' => 'PLAT MINI BLOCKER',
        'step3_accompaniments_title' => 'Accompagnements',
        'accompaniment_help_text' => 'Minimum : quantité égale ou supérieure au total des plats sélectionnés (DOG + CROQ + Mini Boss)',
        'mini_boss_text' => 'Optionnel - Pour les plus petits',
        'mini_boss_description' => 'Menu spécialement conçu pour les enfants',
        
        // Textes étape 4 - Buffets
        'step4_title' => 'CHOISISSEZ VOTRE/VOS BUFFET(S)',
        'step4_buffet_formula_title' => 'Choisissez votre formule buffet :',
        'step4_buffet_selection_help_text' => 'Sélectionnez le type de buffet qui correspond à votre événement',
        'step4_buffet_sale_title' => 'Buffet salé',
        'step4_buffet_sucre_title' => 'Buffet sucré',
        'step4_buffet_mixte_title' => 'Buffets salé + sucré',
        
        // Textes étape 5 - Boissons
        'step5_suggestions_title' => 'NOS SUGGESTIONS',
        'step5_all_soft_title' => 'TOUTES LES BOISSONS FRAÎCHES',
        'step5_all_beers_title' => 'TOUTES LES BIÈRES',
        'step5_tab_soft_label' => 'Boissons fraîches',
        'step5_filter_all_beers' => 'Toutes les bières',
        
        // Textes étape 6 - Options/Animations (remorque uniquement)
        'step6_title' => 'CHOIX DES ANIMATIONS',
        'step6_tireuse_title' => '🍺 MISE À DISPO TIREUSE A BIÈRES 50€',
        'step6_tireuse_description' => 'Faites mousser votre événement ! (fûts non inclus, les choisir parmi notre séléction)',
        'step6_tireuse_checkbox_label' => 'Ajouter la tireuse à bières',
        'step6_kegs_section_title' => 'SÉLECTION DES FÛTS',
        'step6_games_title' => '🎮 INSTALLATION ESPACE JEUX 70€',
        'step6_games_description' => 'Des jeux d\'exterieur pour rassembler, détendre et profiter !',
        'step6_games_section_title' => 'SÉLECTION DES JEUX',
        
        // Textes étape coordonnées
        'contact_recap_title' => 'RECAP DE VOTRE DEMANDE',
        
        // Messages système
        'success_message' => 'Votre devis est d\'ores et déjà disponible dans votre boîte mail',
        'success_message_subtitle' => 'L\'équipe BLOCK vous recontactera sous 48h max pour en parler, l\'ajuster et le valider ensemble !',
        'loading_message' => 'Génération de votre devis en cours...',
        'final_message' => 'Votre devis est d\'ores et déjà disponible dans votre boîte mail, la suite ? Block va prendre contact avec vous afin d\'affiner celui-ci et de créer avec vous toute l\'expérience dont vous rêvez',
        'comment_section_text' => '1 question, 1 souhait, n\'hésitez pas de nous en fait part, on en parle, on....',
        
        // Textes des emails
        'email_welcome_text' => 'Merci pour votre demande ! On a hâte de donner vie à votre événement ! Vous trouverez ci-dessous le récapitulatif de votre demande.',
        'email_quote_details_title' => 'Détails de votre demande',
        'email_download_button_text' => 'Télécharger ma demande',
        'email_next_steps_title' => 'Prochaines étapes :',
        'email_next_steps_text' => 'L\'équipe BLOCK vous recontactera sous 48h max pour en parler, l\'ajuster et le valider ensemble !',
        'email_questions_text' => 'N\'hésitez pas à nous contacter si vous avez la moindre question.',
        'email_signature' => 'A bientôt !<br><br>L\'équipe BLOCK',
        'email_footer_text' => 'Block Street Food & Events - Restaurant & Remorque<br><br>Ceci est un email automatique, merci de ne pas y répondre directement.',
        
        // Encadrés informatifs dans le formulaire
        'info_step3_title' => 'ℹ️ Information importante :',
        'info_step3_message' => 'Sélection obligatoire : le total des plats (DOG + CROQ + Mini Boss) doit être égal ou supérieur à {guest_count} convives.',
        'info_step4_title' => 'ℹ️ Information importante :',
        'info_step4_message' => 'Sélection obligatoire pour {guest_count} convives. Les quantités minimales sont calculées automatiquement.',
        'info_step5_title' => 'ℹ️ Étape optionnelle :',
        'info_step5_message' => 'Sélectionnez vos boissons pour accompagner votre événement.',
        'info_step5_skip_title' => 'ℹ️ Cette étape est optionnelle.',
        'info_step5_skip_message' => 'Vous pouvez passer directement à l\'étape suivante si vous ne souhaitez pas de boissons.',
        'info_step6_skip_title' => 'ℹ️ Cette étape est optionnelle.',
        'info_step6_skip_message' => 'Vous pouvez passer directement à l\'étape suivante si vous ne souhaitez pas d\'animations supplémentaires.',
        
        // Prix de base des forfaits
        'restaurant_base_price' => 300,
        'remorque_base_price' => 350,
        
        // Messages d'erreur de validation du formulaire
        // Étape 1 (Date et lieu - remorque)
        'error_event_date_required' => 'Veuillez compléter la date de l\'événement',
        'error_guest_count_required' => 'Veuillez indiquer le nombre de convives',
        'error_event_duration_required' => 'Veuillez choisir la durée de l\'événement',
        'error_postal_code_required' => 'Veuillez saisir votre code postal (5 chiffres)',
        'error_field_required_generic' => 'Veuillez compléter le champ "{field}"',
        
        // Étape 2 (Forfait de base)
        'error_min_guests' => 'Minimum {min} convives requis pour {service}',
        'error_max_guests' => 'Maximum {max} convives pour {service}',
        
        // Étape 3 (Repas)
        'error_insufficient_dishes' => 'Quantité insuffisante ! Il faut au minimum {required} plats pour {guests} convives. Actuellement sélectionnés : {selected} plats (HOT-DOGS / CROQUES + MINI BLOCKER).',
        'error_accompaniments_not_loaded' => 'Les accompagnements ne sont pas encore chargés. Veuillez recharger la page.',
        'error_insufficient_accompaniments' => 'Quantité insuffisante ! Il faut au minimum {required} accompagnements pour {guests} convives. Actuellement sélectionnés : {selected} accompagnements.',
        'error_too_many_sauces' => 'Trop de sauces ! Vous avez {fries} frites mais {sauces} sauces. Maximum {max} sauces.',
        'error_too_many_chimichurri' => 'Trop de chimichurri ! Vous avez {fries} frites mais {chimichurri} chimichurri. Maximum {max}.',
        'error_max_options_adjusted' => 'Maximum {max} options au total pour {quantity} {product}. Valeur ajustée.',
        
        // Étape 4 (Buffets)
        'error_buffet_required' => 'Veuillez sélectionner un type de buffet (obligatoire).',
        'error_buffet_sale_min_person' => 'Buffet salé : minimum 1 par personne requis. Actuellement {selected} pour {guests} convives.',
        'error_buffet_sale_min_recipes' => 'Buffet salé : minimum 2 recettes différentes requises.',
        'error_buffet_sucre_min_person' => 'Buffet sucré : minimum 1 par personne requis. Actuellement {selected} pour {guests} convives.',
        'error_buffet_sucre_min_dish' => 'Buffet sucré : minimum 1 plat requis.',
        
        // Étape 6/7 (Contact)
        'error_firstname_required' => 'Prénom est obligatoire.',
        'error_lastname_required' => 'Nom est obligatoire.',
        'error_email_required' => 'Email est obligatoire.',
        'error_phone_required' => 'Téléphone est obligatoire.',
        'error_email_invalid' => 'Format d\'email invalide.',
        'error_phone_invalid' => 'Format de téléphone invalide.',
        
        // Étape 6 (Options remorque)
        'error_kegs_without_tireuse' => 'Attention : Vous avez sélectionné des fûts mais pas de tireuse. Les fûts nécessitent une tireuse pour être servis.',
        
        // Messages de succès/information
        'success_tireuse_auto_added' => 'Tireuse automatiquement ajoutée pour vos fûts sélectionnés.',
        'success_games_auto_added' => 'Installation jeux automatiquement ajoutée pour vos jeux sélectionnés.',
        'success_tireuse_selected' => 'Tireuse sélectionnée ! Vous pouvez maintenant choisir vos fûts.',
        'warning_tireuse_deselected' => 'Tireuse désélectionnée - Les fûts ont été automatiquement retirés.',
        'success_games_selected' => 'Installation jeux sélectionnée ! Vous pouvez maintenant choisir vos jeux.',
        'warning_games_deselected' => 'Installation jeux désélectionnée - Les jeux ont été automatiquement retirés.',
        
        // Messages d\'erreur généraux
        'error_network' => 'Erreur de connexion. Veuillez réessayer.',
        'error_generic' => 'Une erreur est survenue.',
    );

    /**
     * Afficher la page des options
     */
    public function display_page()
    {
        // Traitement du formulaire
        if (isset($_POST['save_options']) && wp_verify_nonce($_POST['_wpnonce'], 'save_unified_options')) {
            $this->save_options();
            echo '<div class="notice notice-success is-dismissible"><p>' . __('Options sauvegardées avec succès !', 'restaurant-booking') . '</p></div>';
        }
        
        // Traitement du nettoyage des échappements
        if (isset($_POST['clean_escaped_quotes']) && wp_verify_nonce($_POST['_wpnonce'], 'save_unified_options')) {
            $this->clean_all_escaped_quotes();
            echo '<div class="notice notice-success is-dismissible"><p>' . __('Nettoyage des échappements terminé !', 'restaurant-booking') . '</p></div>';
        }

        $options = $this->get_options();
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline">⚙️ <?php _e('Options de Configuration', 'restaurant-booking'); ?></h1>
            <hr class="wp-header-end">

            <div class="restaurant-booking-info-card">
                <h3><?php _e('Configuration globale du plugin', 'restaurant-booking'); ?></h3>
                <p><?php _e('Cette page permet de configurer toutes les options, règles et textes utilisés dans les formulaires de devis.', 'restaurant-booking'); ?></p>
                <p><strong><?php _e('⚠️ Important :', 'restaurant-booking'); ?></strong> <?php _e('Les modifications apportées ici seront immédiatement visibles sur les widgets publics.', 'restaurant-booking'); ?></p>
                <p><strong>🟧 Légende :</strong> Les sections avec un liseret orange à gauche sont spécifiques à la privatisation de la remorque.</p>
            </div>

            <form method="post" action="">
                <?php wp_nonce_field('save_unified_options'); ?>
                
                <div class="restaurant-booking-options-container">
                    
                    <!-- ========================================
                         SECTION 1 : TEXTES DU FORMULAIRE
                         ======================================== -->
                    <div class="options-section">
                        <h2>📝 <?php _e('TEXTES DU FORMULAIRE DE DEVIS (par étape)', 'restaurant-booking'); ?></h2>
                        <p class="description"><?php _e('Tous les textes affichés dans le formulaire de devis, organisés par étape.', 'restaurant-booking'); ?></p>
                        
                        <!-- Étape 0 : Sélection du service -->
                        <div class="options-group">
                            <h3>📍 <?php _e('Étape 0 - Sélection du service', 'restaurant-booking'); ?></h3>
                            <p class="description"><?php _e('Première page où l\'utilisateur choisit entre Restaurant ou Remorque', 'restaurant-booking'); ?></p>
                            <table class="form-table">
                                <tr>
                                    <th scope="row"><?php _e('Titre principal du formulaire', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="text" name="widget_title" value="<?php echo esc_attr($options['widget_title']); ?>" class="large-text" />
                                        <p class="description"><?php _e('Titre affiché en haut du formulaire', 'restaurant-booking'); ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Sous-titre', 'restaurant-booking'); ?></th>
                                    <td>
                                        <textarea name="widget_subtitle" rows="2" class="large-text"><?php echo esc_textarea($options['widget_subtitle']); ?></textarea>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Titre "Choisissez votre service"', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="text" name="service_selection_title" value="<?php echo esc_attr($options['service_selection_title']); ?>" class="large-text" />
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Carte Restaurant - Titre', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="text" name="restaurant_card_title" value="<?php echo esc_attr($options['restaurant_card_title']); ?>" class="large-text" />
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Carte Restaurant - Sous-titre', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="text" name="restaurant_card_subtitle" value="<?php echo esc_attr($options['restaurant_card_subtitle']); ?>" class="large-text" />
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Carte Restaurant - Description', 'restaurant-booking'); ?></th>
                                    <td>
                                        <textarea name="restaurant_card_description" rows="3" class="large-text"><?php echo esc_textarea($options['restaurant_card_description']); ?></textarea>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Carte Remorque - Titre', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="text" name="remorque_card_title" value="<?php echo esc_attr($options['remorque_card_title']); ?>" class="large-text" />
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Carte Remorque - Sous-titre', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="text" name="remorque_card_subtitle" value="<?php echo esc_attr($options['remorque_card_subtitle']); ?>" class="large-text" />
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Carte Remorque - Description', 'restaurant-booking'); ?></th>
                                    <td>
                                        <textarea name="remorque_card_description" rows="3" class="large-text"><?php echo esc_textarea($options['remorque_card_description']); ?></textarea>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <!-- Étape 1 : Introduction -->
                        <div class="options-group">
                            <h3>1️⃣ <?php _e('Étape 1 - Introduction', 'restaurant-booking'); ?></h3>
                            <table class="form-table">
                                <tr>
                                    <th scope="row"><?php _e('Titre Introduction Restaurant', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="text" name="step1_title_restaurant" value="<?php echo esc_attr($options['step1_title_restaurant']); ?>" class="large-text" />
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Titre Introduction Remorque', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="text" name="step1_title_remorque" value="<?php echo esc_attr($options['step1_title_remorque']); ?>" class="large-text" />
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Titre carte "Comment ça fonctionne"', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="text" name="step1_card_title" value="<?php echo esc_attr($options['step1_card_title']); ?>" class="regular-text" />
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Liste des étapes Restaurant', 'restaurant-booking'); ?></th>
                                    <td>
                                        <textarea name="restaurant_steps_list" rows="4" class="large-text"><?php echo esc_textarea($options['restaurant_steps_list']); ?></textarea>
                                        <p class="description"><?php _e('Séparez chaque étape par un pipe (|)', 'restaurant-booking'); ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Liste des étapes Remorque', 'restaurant-booking'); ?></th>
                                    <td>
                                        <textarea name="remorque_steps_list" rows="4" class="large-text"><?php echo esc_textarea($options['remorque_steps_list']); ?></textarea>
                                        <p class="description"><?php _e('Séparez chaque étape par un pipe (|)', 'restaurant-booking'); ?></p>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <!-- Étape 2 : Forfait de base -->
                        <div class="options-group">
                            <h3>2️⃣ <?php _e('Étape 2 - Forfait de base', 'restaurant-booking'); ?></h3>
                            <table class="form-table">
                                <tr>
                                    <th scope="row"><?php _e('Titre de l\'étape', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="text" name="step2_title" value="<?php echo esc_attr($options['step2_title']); ?>" class="regular-text" />
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Titre carte forfait Restaurant', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="text" name="restaurant_forfait_card_title" value="<?php echo esc_attr($options['restaurant_forfait_card_title']); ?>" class="large-text" />
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Titre carte forfait Remorque', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="text" name="remorque_forfait_card_title" value="<?php echo esc_attr($options['remorque_forfait_card_title']); ?>" class="large-text" />
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <!-- Étape 3 : Choix des repas -->
                        <div class="options-group">
                            <h3>3️⃣ <?php _e('Étape 3 - Choix des repas', 'restaurant-booking'); ?></h3>
                            <table class="form-table">
                                <tr>
                                    <th scope="row"><?php _e('Titre de l\'étape', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="text" name="step3_title" value="<?php echo esc_attr($options['step3_title']); ?>" class="regular-text" />
                                        <p class="description"><?php _e('Titre principal de l\'étape choix des repas', 'restaurant-booking'); ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Titre section Plat Signature', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="text" name="step3_signature_title" value="<?php echo esc_attr($options['step3_signature_title']); ?>" class="regular-text" />
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Titre Hot-Dogs', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="text" name="step3_hot_dogs_title" value="<?php echo esc_attr($options['step3_hot_dogs_title']); ?>" class="regular-text" />
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Titre Croques', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="text" name="step3_croques_title" value="<?php echo esc_attr($options['step3_croques_title']); ?>" class="regular-text" />
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Titre Menu Mini Boss', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="text" name="step3_mini_boss_title" value="<?php echo esc_attr($options['step3_mini_boss_title']); ?>" class="regular-text" />
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Titre Accompagnements', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="text" name="step3_accompaniments_title" value="<?php echo esc_attr($options['step3_accompaniments_title']); ?>" class="regular-text" />
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Texte d\'aide Accompagnements', 'restaurant-booking'); ?></th>
                                    <td>
                                        <textarea name="accompaniment_help_text" rows="2" class="large-text"><?php echo esc_textarea($options['accompaniment_help_text']); ?></textarea>
                                        <p class="description"><?php _e('Texte explicatif affiché sous le titre "Accompagnements" dans le formulaire', 'restaurant-booking'); ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Texte Menu Mini Boss', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="text" name="mini_boss_text" value="<?php echo esc_attr($options['mini_boss_text']); ?>" class="regular-text" />
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Description Menu Mini Boss', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="text" name="mini_boss_description" value="<?php echo esc_attr($options['mini_boss_description']); ?>" class="regular-text" />
                                    </td>
                                </tr>
                            </table>
                        </div>
                        
                        <!-- Étape 4 : Buffets -->
                        <div class="options-group">
                            <h3>4️⃣ <?php _e('Étape 4 - Buffets', 'restaurant-booking'); ?></h3>
                            <table class="form-table">
                                <tr>
                                    <th scope="row"><?php _e('Titre de l\'étape', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="text" name="step4_title" value="<?php echo esc_attr($options['step4_title']); ?>" class="regular-text" />
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Titre formule buffet', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="text" name="step4_buffet_formula_title" value="<?php echo esc_attr($options['step4_buffet_formula_title']); ?>" class="regular-text" />
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Texte d\'aide sélection buffet', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="text" name="step4_buffet_selection_help_text" value="<?php echo esc_attr($options['step4_buffet_selection_help_text']); ?>" class="large-text" />
                                        <p class="description"><?php _e('Texte affiché sous "Choisissez votre formule buffet"', 'restaurant-booking'); ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Titre Buffet Salé', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="text" name="step4_buffet_sale_title" value="<?php echo esc_attr($options['step4_buffet_sale_title']); ?>" class="regular-text" />
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Titre Buffet Sucré', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="text" name="step4_buffet_sucre_title" value="<?php echo esc_attr($options['step4_buffet_sucre_title']); ?>" class="regular-text" />
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Titre Buffets Mixtes', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="text" name="step4_buffet_mixte_title" value="<?php echo esc_attr($options['step4_buffet_mixte_title']); ?>" class="regular-text" />
                                    </td>
                                </tr>
                            </table>
                        </div>
                        
                        <!-- Étape 5 : Boissons -->
                        <div class="options-group">
                            <h3>5️⃣ <?php _e('Étape 5 - Boissons', 'restaurant-booking'); ?></h3>
                            <table class="form-table">
                                <tr>
                                    <th scope="row"><?php _e('Titre Suggestions', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="text" name="step5_suggestions_title" value="<?php echo esc_attr($options['step5_suggestions_title']); ?>" class="regular-text" />
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Titre Toutes les boissons fraîches', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="text" name="step5_all_soft_title" value="<?php echo esc_attr($options['step5_all_soft_title']); ?>" class="regular-text" />
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Titre Toutes les bières', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="text" name="step5_all_beers_title" value="<?php echo esc_attr($options['step5_all_beers_title']); ?>" class="regular-text" />
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Label onglet boissons fraîches', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="text" name="step5_tab_soft_label" value="<?php echo esc_attr($options['step5_tab_soft_label']); ?>" class="regular-text" />
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Filtre toutes les bières', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="text" name="step5_filter_all_beers" value="<?php echo esc_attr($options['step5_filter_all_beers']); ?>" class="regular-text" />
                                    </td>
                                </tr>
                            </table>
                        </div>
                        
                        <!-- Étape 6 : Options/Animations (Remorque uniquement) -->
                        <div class="options-group options-group-remorque">
                            <h3>🟧 6️⃣ <?php _e('Étape 6 - Options/Animations (Remorque uniquement)', 'restaurant-booking'); ?></h3>
                            <p class="description" style="color: #FF8C00;"><strong><?php _e('Cette étape n\'apparaît que pour la privatisation de la remorque', 'restaurant-booking'); ?></strong></p>
                            <table class="form-table">
                                <tr>
                                    <th scope="row"><?php _e('Titre de l\'étape', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="text" name="step6_title" value="<?php echo esc_attr($options['step6_title']); ?>" class="regular-text" />
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Titre Tireuse', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="text" name="step6_tireuse_title" value="<?php echo esc_attr($options['step6_tireuse_title']); ?>" class="regular-text" />
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Description Tireuse', 'restaurant-booking'); ?></th>
                                    <td>
                                        <textarea name="step6_tireuse_description" rows="2" class="large-text"><?php echo esc_textarea($options['step6_tireuse_description']); ?></textarea>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Label checkbox Tireuse', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="text" name="step6_tireuse_checkbox_label" value="<?php echo esc_attr($options['step6_tireuse_checkbox_label']); ?>" class="regular-text" />
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Titre section Fûts', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="text" name="step6_kegs_section_title" value="<?php echo esc_attr($options['step6_kegs_section_title']); ?>" class="regular-text" />
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Titre Jeux', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="text" name="step6_games_title" value="<?php echo esc_attr($options['step6_games_title']); ?>" class="regular-text" />
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Description Jeux', 'restaurant-booking'); ?></th>
                                    <td>
                                        <textarea name="step6_games_description" rows="2" class="large-text"><?php echo esc_textarea($options['step6_games_description']); ?></textarea>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Titre section Jeux', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="text" name="step6_games_section_title" value="<?php echo esc_attr($options['step6_games_section_title']); ?>" class="regular-text" />
                                    </td>
                                </tr>
                            </table>
                        </div>
                        
                        <!-- Étape Finale : Coordonnées -->
                        <div class="options-group">
                            <h3>📋 <?php _e('Étape Finale - Coordonnées', 'restaurant-booking'); ?></h3>
                            <table class="form-table">
                                <tr>
                                    <th scope="row"><?php _e('Titre Récapitulatif', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="text" name="contact_recap_title" value="<?php echo esc_attr($options['contact_recap_title']); ?>" class="regular-text" />
                                    </td>
                                </tr>
                            </table>
                        </div>
                        
                        <!-- Messages système -->
                        <div class="options-group">
                            <h3>💬 <?php _e('Messages système', 'restaurant-booking'); ?></h3>
                            <table class="form-table">
                                <tr>
                                    <th scope="row"><?php _e('Message de succès', 'restaurant-booking'); ?></th>
                                    <td>
                                        <textarea name="success_message" rows="2" class="large-text"><?php echo esc_textarea($options['success_message']); ?></textarea>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Sous-titre message de succès', 'restaurant-booking'); ?></th>
                                    <td>
                                        <textarea name="success_message_subtitle" rows="2" class="large-text"><?php echo esc_textarea($options['success_message_subtitle']); ?></textarea>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Message de chargement', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="text" name="loading_message" value="<?php echo esc_attr($options['loading_message']); ?>" class="large-text" />
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Message final (legacy)', 'restaurant-booking'); ?></th>
                                    <td>
                                        <textarea name="final_message" rows="3" class="large-text"><?php echo esc_textarea($options['final_message']); ?></textarea>
                                        <p class="description"><?php _e('Message alternatif affiché après soumission du devis', 'restaurant-booking'); ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Texte section commentaire', 'restaurant-booking'); ?></th>
                                    <td>
                                        <textarea name="comment_section_text" rows="2" class="large-text"><?php echo esc_textarea($options['comment_section_text']); ?></textarea>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        
                        <!-- Textes des Emails -->
                        <div class="options-group">
                            <h3>📧 <?php _e('Textes des Emails', 'restaurant-booking'); ?></h3>
                            <table class="form-table">
                                <tr>
                                    <th scope="row"><?php _e('Texte de bienvenue', 'restaurant-booking'); ?></th>
                                    <td>
                                        <textarea name="email_welcome_text" rows="2" class="large-text"><?php echo esc_textarea($options['email_welcome_text']); ?></textarea>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Titre Détails demande', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="text" name="email_quote_details_title" value="<?php echo esc_attr($options['email_quote_details_title']); ?>" class="regular-text" />
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Texte bouton téléchargement', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="text" name="email_download_button_text" value="<?php echo esc_attr($options['email_download_button_text']); ?>" class="regular-text" />
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Titre prochaines étapes', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="text" name="email_next_steps_title" value="<?php echo esc_attr($options['email_next_steps_title']); ?>" class="regular-text" />
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Texte prochaines étapes', 'restaurant-booking'); ?></th>
                                    <td>
                                        <textarea name="email_next_steps_text" rows="2" class="large-text"><?php echo esc_textarea($options['email_next_steps_text']); ?></textarea>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Texte questions', 'restaurant-booking'); ?></th>
                                    <td>
                                        <textarea name="email_questions_text" rows="2" class="large-text"><?php echo esc_textarea($options['email_questions_text']); ?></textarea>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Signature email', 'restaurant-booking'); ?></th>
                                    <td>
                                        <textarea name="email_signature" rows="2" class="large-text"><?php echo esc_textarea($options['email_signature']); ?></textarea>
                                        <p class="description"><?php _e('Vous pouvez utiliser des balises HTML comme &lt;br&gt; pour les sauts de ligne.', 'restaurant-booking'); ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Pied de page email', 'restaurant-booking'); ?></th>
                                    <td>
                                        <textarea name="email_footer_text" rows="3" class="large-text"><?php echo esc_textarea($options['email_footer_text']); ?></textarea>
                                        <p class="description"><?php _e('Texte affiché au pied de l\'email client. Vous pouvez utiliser des balises HTML comme &lt;br&gt; pour les sauts de ligne.', 'restaurant-booking'); ?></p>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        
                        <!-- Encadrés informatifs -->
                        <div class="options-group">
                            <h3>ℹ️ <?php _e('Encadrés informatifs du formulaire', 'restaurant-booking'); ?></h3>
                            <p class="description"><?php _e('Ces textes apparaissent dans les encadrés d\'information à différentes étapes du formulaire. Utilisez {guest_count} comme placeholder pour afficher le nombre de convives.', 'restaurant-booking'); ?></p>
                            <table class="form-table">
                                <tr>
                                    <th scope="row"><?php _e('Étape 3 - Titre encadré', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="text" name="info_step3_title" value="<?php echo esc_attr($options['info_step3_title']); ?>" class="large-text" />
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Étape 3 - Message encadré', 'restaurant-booking'); ?></th>
                                    <td>
                                        <textarea name="info_step3_message" rows="2" class="large-text"><?php echo esc_textarea($options['info_step3_message']); ?></textarea>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Étape 4 - Titre encadré', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="text" name="info_step4_title" value="<?php echo esc_attr($options['info_step4_title']); ?>" class="large-text" />
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Étape 4 - Message encadré', 'restaurant-booking'); ?></th>
                                    <td>
                                        <textarea name="info_step4_message" rows="2" class="large-text"><?php echo esc_textarea($options['info_step4_message']); ?></textarea>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Étape 5 - Titre encadré', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="text" name="info_step5_title" value="<?php echo esc_attr($options['info_step5_title']); ?>" class="large-text" />
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Étape 5 - Message encadré', 'restaurant-booking'); ?></th>
                                    <td>
                                        <textarea name="info_step5_message" rows="2" class="large-text"><?php echo esc_textarea($options['info_step5_message']); ?></textarea>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Étape 5 - Titre "passer cette étape"', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="text" name="info_step5_skip_title" value="<?php echo esc_attr($options['info_step5_skip_title']); ?>" class="large-text" />
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Étape 5 - Message "passer cette étape"', 'restaurant-booking'); ?></th>
                                    <td>
                                        <textarea name="info_step5_skip_message" rows="2" class="large-text"><?php echo esc_textarea($options['info_step5_skip_message']); ?></textarea>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Étape 6 - Titre "passer cette étape"', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="text" name="info_step6_skip_title" value="<?php echo esc_attr($options['info_step6_skip_title']); ?>" class="large-text" />
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Étape 6 - Message "passer cette étape"', 'restaurant-booking'); ?></th>
                                    <td>
                                        <textarea name="info_step6_skip_message" rows="2" class="large-text"><?php echo esc_textarea($options['info_step6_skip_message']); ?></textarea>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        
                        <!-- Messages d'erreur de validation -->
                        <div class="options-group">
                            <h3>❌ <?php _e('Messages d\'erreur de validation', 'restaurant-booking'); ?></h3>
                            <p class="description"><?php _e('Messages affichés lors de la validation du formulaire. Utilisez les placeholders : {field}, {min}, {max}, {service}, {required}, {guests}, {selected}, {fries}, {sauces}, {chimichurri}, {quantity}, {product}, {max}.', 'restaurant-booking'); ?></p>
                            <table class="form-table">
                                <!-- Étape 1/2 -->
                                <tr>
                                    <th scope="row" colspan="2" style="background: #f0f0f0; padding: 10px;"><strong><?php _e('Étape 1 & 2 - Date, lieu et forfait de base', 'restaurant-booking'); ?></strong></th>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Date manquante', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="text" name="error_event_date_required" value="<?php echo esc_attr($options['error_event_date_required']); ?>" class="large-text" />
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Nombre de convives manquant', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="text" name="error_guest_count_required" value="<?php echo esc_attr($options['error_guest_count_required']); ?>" class="large-text" />
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Durée manquante', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="text" name="error_event_duration_required" value="<?php echo esc_attr($options['error_event_duration_required']); ?>" class="large-text" />
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Code postal manquant', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="text" name="error_postal_code_required" value="<?php echo esc_attr($options['error_postal_code_required']); ?>" class="large-text" />
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Champ générique manquant', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="text" name="error_field_required_generic" value="<?php echo esc_attr($options['error_field_required_generic']); ?>" class="large-text" />
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Nombre minimum de convives', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="text" name="error_min_guests" value="<?php echo esc_attr($options['error_min_guests']); ?>" class="large-text" />
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Nombre maximum de convives', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="text" name="error_max_guests" value="<?php echo esc_attr($options['error_max_guests']); ?>" class="large-text" />
                                    </td>
                                </tr>
                                
                                <!-- Étape 3 -->
                                <tr>
                                    <th scope="row" colspan="2" style="background: #f0f0f0; padding: 10px; margin-top: 20px;"><strong><?php _e('Étape 3 - Repas', 'restaurant-booking'); ?></strong></th>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Plats insuffisants', 'restaurant-booking'); ?></th>
                                    <td>
                                        <textarea name="error_insufficient_dishes" rows="2" class="large-text"><?php echo esc_textarea($options['error_insufficient_dishes']); ?></textarea>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Accompagnements non chargés', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="text" name="error_accompaniments_not_loaded" value="<?php echo esc_attr($options['error_accompaniments_not_loaded']); ?>" class="large-text" />
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Accompagnements insuffisants', 'restaurant-booking'); ?></th>
                                    <td>
                                        <textarea name="error_insufficient_accompaniments" rows="2" class="large-text"><?php echo esc_textarea($options['error_insufficient_accompaniments']); ?></textarea>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Trop de sauces', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="text" name="error_too_many_sauces" value="<?php echo esc_attr($options['error_too_many_sauces']); ?>" class="large-text" />
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Trop de chimichurri', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="text" name="error_too_many_chimichurri" value="<?php echo esc_attr($options['error_too_many_chimichurri']); ?>" class="large-text" />
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Options ajustées', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="text" name="error_max_options_adjusted" value="<?php echo esc_attr($options['error_max_options_adjusted']); ?>" class="large-text" />
                                    </td>
                                </tr>
                                
                                <!-- Étape 4 -->
                                <tr>
                                    <th scope="row" colspan="2" style="background: #f0f0f0; padding: 10px;"><strong><?php _e('Étape 4 - Buffets', 'restaurant-booking'); ?></strong></th>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Buffet obligatoire', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="text" name="error_buffet_required" value="<?php echo esc_attr($options['error_buffet_required']); ?>" class="large-text" />
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Buffet salé - Min par personne', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="text" name="error_buffet_sale_min_person" value="<?php echo esc_attr($options['error_buffet_sale_min_person']); ?>" class="large-text" />
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Buffet salé - Min recettes', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="text" name="error_buffet_sale_min_recipes" value="<?php echo esc_attr($options['error_buffet_sale_min_recipes']); ?>" class="large-text" />
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Buffet sucré - Min par personne', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="text" name="error_buffet_sucre_min_person" value="<?php echo esc_attr($options['error_buffet_sucre_min_person']); ?>" class="large-text" />
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Buffet sucré - Min 1 plat', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="text" name="error_buffet_sucre_min_dish" value="<?php echo esc_attr($options['error_buffet_sucre_min_dish']); ?>" class="large-text" />
                                    </td>
                                </tr>
                                
                                <!-- Étape 6/7 -->
                                <tr>
                                    <th scope="row" colspan="2" style="background: #f0f0f0; padding: 10px;"><strong><?php _e('Étape Contact', 'restaurant-booking'); ?></strong></th>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Prénom obligatoire', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="text" name="error_firstname_required" value="<?php echo esc_attr($options['error_firstname_required']); ?>" class="large-text" />
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Nom obligatoire', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="text" name="error_lastname_required" value="<?php echo esc_attr($options['error_lastname_required']); ?>" class="large-text" />
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Email obligatoire', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="text" name="error_email_required" value="<?php echo esc_attr($options['error_email_required']); ?>" class="large-text" />
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Téléphone obligatoire', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="text" name="error_phone_required" value="<?php echo esc_attr($options['error_phone_required']); ?>" class="large-text" />
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Email invalide', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="text" name="error_email_invalid" value="<?php echo esc_attr($options['error_email_invalid']); ?>" class="large-text" />
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Téléphone invalide', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="text" name="error_phone_invalid" value="<?php echo esc_attr($options['error_phone_invalid']); ?>" class="large-text" />
                                    </td>
                                </tr>
                                
                                <!-- Options remorque -->
                                <tr>
                                    <th scope="row" colspan="2" style="background: #FFF8F0; padding: 10px;"><strong>🟧 <?php _e('Étape 6 - Options (Remorque)', 'restaurant-booking'); ?></strong></th>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Fûts sans tireuse', 'restaurant-booking'); ?></th>
                                    <td>
                                        <textarea name="error_kegs_without_tireuse" rows="2" class="large-text"><?php echo esc_textarea($options['error_kegs_without_tireuse']); ?></textarea>
                                    </td>
                                </tr>
                                
                                <!-- Messages de succès -->
                                <tr>
                                    <th scope="row" colspan="2" style="background: #e8f5e9; padding: 10px;"><strong>✅ <?php _e('Messages de succès/information', 'restaurant-booking'); ?></strong></th>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Tireuse ajoutée automatiquement', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="text" name="success_tireuse_auto_added" value="<?php echo esc_attr($options['success_tireuse_auto_added']); ?>" class="large-text" />
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Jeux ajoutés automatiquement', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="text" name="success_games_auto_added" value="<?php echo esc_attr($options['success_games_auto_added']); ?>" class="large-text" />
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Tireuse sélectionnée', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="text" name="success_tireuse_selected" value="<?php echo esc_attr($options['success_tireuse_selected']); ?>" class="large-text" />
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Tireuse désélectionnée', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="text" name="warning_tireuse_deselected" value="<?php echo esc_attr($options['warning_tireuse_deselected']); ?>" class="large-text" />
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Jeux sélectionnés', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="text" name="success_games_selected" value="<?php echo esc_attr($options['success_games_selected']); ?>" class="large-text" />
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Jeux désélectionnés', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="text" name="warning_games_deselected" value="<?php echo esc_attr($options['warning_games_deselected']); ?>" class="large-text" />
                                    </td>
                                </tr>
                                
                                <!-- Messages généraux -->
                                <tr>
                                    <th scope="row" colspan="2" style="background: #ffebee; padding: 10px;"><strong>❌ <?php _e('Messages d\'erreur généraux', 'restaurant-booking'); ?></strong></th>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Erreur réseau', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="text" name="error_network" value="<?php echo esc_attr($options['error_network']); ?>" class="large-text" />
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Erreur générique', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="text" name="error_generic" value="<?php echo esc_attr($options['error_generic']); ?>" class="large-text" />
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- ========================================
                         SECTION 2 : RÈGLES DE VALIDATION DES PRODUITS
                         ======================================== -->
                    <div class="options-section">
                        <h2>🍽️ <?php _e('RÈGLES DE VALIDATION DES PRODUITS', 'restaurant-booking'); ?></h2>
                        <p class="description"><?php _e('Règles qui définissent les quantités minimales et maximales de produits que les clients peuvent commander. Ces règles s\'appliquent de la même manière pour le restaurant et la remorque.', 'restaurant-booking'); ?></p>
                        
                        <!-- Plats Signature -->
                        <div class="options-group">
                            <h3><?php _e('Plats Signature', 'restaurant-booking'); ?></h3>
                            <table class="form-table">
                                <tr>
                                    <th scope="row"><?php _e('Minimum par personne', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="number" name="signature_dish_min_per_person" value="<?php echo esc_attr($options['signature_dish_min_per_person']); ?>" min="1" class="small-text" />
                                        <p class="description"><?php _e('Nombre minimum de plats signature par personne', 'restaurant-booking'); ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Texte de règle affiché', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="text" name="signature_dish_text" value="<?php echo esc_attr($options['signature_dish_text']); ?>" class="regular-text" />
                                        <p class="description"><?php _e('Texte affiché dans le formulaire sous le titre "Plats Signature"', 'restaurant-booking'); ?></p>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <!-- Accompagnements -->
                        <div class="options-group">
                            <h3><?php _e('Accompagnements', 'restaurant-booking'); ?></h3>
                            <table class="form-table">
                                <tr>
                                    <th scope="row"><?php _e('Minimum par personne', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="number" name="accompaniment_min_per_person" value="<?php echo esc_attr($options['accompaniment_min_per_person']); ?>" min="1" class="small-text" />
                                        <p class="description"><?php _e('Nombre minimum d\'accompagnements par personne', 'restaurant-booking'); ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Texte de règle affiché', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="text" name="accompaniment_text" value="<?php echo esc_attr($options['accompaniment_text']); ?>" class="regular-text" />
                                        <p class="description"><?php _e('⚠️ Ce texte est différent du "Texte d\'aide" ci-dessus. Il est utilisé pour la validation et les messages d\'erreur.', 'restaurant-booking'); ?></p>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <!-- Buffet Salé -->
                        <div class="options-group">
                            <h3><?php _e('Buffet Salé', 'restaurant-booking'); ?></h3>
                            <table class="form-table">
                                <tr>
                                    <th scope="row"><?php _e('Minimum par personne', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="number" name="buffet_sale_min_per_person" value="<?php echo esc_attr($options['buffet_sale_min_per_person']); ?>" min="1" class="small-text" />
                                        <p class="description"><?php _e('Nombre minimum de plats de buffet salé par personne', 'restaurant-booking'); ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Minimum de recettes différentes', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="number" name="buffet_sale_min_recipes" value="<?php echo esc_attr($options['buffet_sale_min_recipes']); ?>" min="1" class="small-text" />
                                        <p class="description"><?php _e('Nombre minimum de plats différents à sélectionner', 'restaurant-booking'); ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Texte de règle affiché', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="text" name="buffet_sale_text" value="<?php echo esc_attr($options['buffet_sale_text']); ?>" class="regular-text" />
                                        <p class="description"><?php _e('Texte affiché dans le formulaire sous le titre "Buffet Salé"', 'restaurant-booking'); ?></p>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <!-- Buffet Sucré -->
                        <div class="options-group">
                            <h3><?php _e('Buffet Sucré', 'restaurant-booking'); ?></h3>
                            <table class="form-table">
                                <tr>
                                    <th scope="row"><?php _e('Minimum par personne', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="number" name="buffet_sucre_min_per_person" value="<?php echo esc_attr($options['buffet_sucre_min_per_person']); ?>" min="1" class="small-text" />
                                        <p class="description"><?php _e('Nombre minimum de desserts par personne', 'restaurant-booking'); ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Minimum de plats différents', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="number" name="buffet_sucre_min_dishes" value="<?php echo esc_attr($options['buffet_sucre_min_dishes']); ?>" min="1" class="small-text" />
                                        <p class="description"><?php _e('Nombre minimum de desserts différents à sélectionner', 'restaurant-booking'); ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Texte de règle affiché', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="text" name="buffet_sucre_text" value="<?php echo esc_attr($options['buffet_sucre_text']); ?>" class="regular-text" />
                                        <p class="description"><?php _e('Texte affiché dans le formulaire sous le titre "Buffet Sucré"', 'restaurant-booking'); ?></p>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- ========================================
                         SECTION 3 : CONFIGURATION RESTAURANT
                         ======================================== -->
                    <div class="options-section">
                        <h2>🏪 <?php _e('CONFIGURATION RESTAURANT', 'restaurant-booking'); ?></h2>
                        
                        <div class="options-group">
                            <h3><?php _e('Nombre de convives', 'restaurant-booking'); ?></h3>
                            <table class="form-table">
                                <tr>
                                    <th scope="row"><?php _e('Minimum', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="number" name="restaurant_min_guests" value="<?php echo esc_attr($options['restaurant_min_guests']); ?>" min="1" class="small-text" />
                                        <span><?php _e('personnes', 'restaurant-booking'); ?></span>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Maximum', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="number" name="restaurant_max_guests" value="<?php echo esc_attr($options['restaurant_max_guests']); ?>" min="1" class="small-text" />
                                        <span><?php _e('personnes', 'restaurant-booking'); ?></span>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Texte d\'affichage', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="text" name="restaurant_guests_text" value="<?php echo esc_attr($options['restaurant_guests_text']); ?>" class="regular-text" />
                                        <p class="description"><?php _e('Texte affiché dans le formulaire', 'restaurant-booking'); ?></p>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <div class="options-group">
                            <h3><?php _e('Durée événement', 'restaurant-booking'); ?></h3>
                            <table class="form-table">
                                <tr>
                                    <th scope="row"><?php _e('Durée minimum incluse', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="number" name="restaurant_min_duration" value="<?php echo esc_attr($options['restaurant_min_duration']); ?>" min="1" class="small-text" />
                                        <span><?php _e('heures', 'restaurant-booking'); ?></span>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Durée max sans supplément', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="number" name="restaurant_max_duration_included" value="<?php echo esc_attr($options['restaurant_max_duration_included']); ?>" min="1" class="small-text" />
                                        <span><?php _e('heures', 'restaurant-booking'); ?></span>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Prix par heure supplémentaire', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="number" name="restaurant_extra_hour_price" value="<?php echo esc_attr($options['restaurant_extra_hour_price']); ?>" min="0" step="0.01" class="small-text" />
                                        <span>€ TTC</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Texte d\'explication', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="text" name="restaurant_duration_text" value="<?php echo esc_attr($options['restaurant_duration_text']); ?>" class="large-text" />
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <div class="options-group">
                            <h3><?php _e('Prix de base', 'restaurant-booking'); ?></h3>
                            <table class="form-table">
                                <tr>
                                    <th scope="row"><?php _e('Prix forfait restaurant', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="number" name="restaurant_base_price" value="<?php echo esc_attr($options['restaurant_base_price']); ?>" min="0" step="0.01" class="small-text" />
                                        <span>€</span>
                                        <p class="description"><?php _e('Prix de base pour la privatisation du restaurant', 'restaurant-booking'); ?></p>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <div class="options-group">
                            <h3><?php _e('Description forfait', 'restaurant-booking'); ?></h3>
                            <table class="form-table">
                                <tr>
                                    <th scope="row"><?php _e('Éléments inclus', 'restaurant-booking'); ?></th>
                                    <td>
                                        <textarea name="restaurant_forfait_description" rows="5" class="large-text"><?php echo esc_textarea($options['restaurant_forfait_description']); ?></textarea>
                                        <p class="description"><?php _e('Séparez chaque élément par un pipe (|)', 'restaurant-booking'); ?></p>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- ========================================
                         SECTION 4 : CONFIGURATION REMORQUE
                         ======================================== -->
                    <div class="options-section options-section-remorque">
                        <h2>🟧 🚛 <?php _e('CONFIGURATION REMORQUE', 'restaurant-booking'); ?></h2>
                        
                        <div class="options-group">
                            <h3><?php _e('Nombre de convives', 'restaurant-booking'); ?></h3>
                            <table class="form-table">
                                <tr>
                                    <th scope="row"><?php _e('Minimum', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="number" name="remorque_min_guests" value="<?php echo esc_attr($options['remorque_min_guests']); ?>" min="1" class="small-text" />
                                        <span><?php _e('personnes', 'restaurant-booking'); ?></span>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Maximum', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="number" name="remorque_max_guests" value="<?php echo esc_attr($options['remorque_max_guests']); ?>" min="1" class="small-text" />
                                        <span><?php _e('personnes', 'restaurant-booking'); ?></span>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Seuil supplément personnel', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="number" name="remorque_staff_threshold" value="<?php echo esc_attr($options['remorque_staff_threshold']); ?>" min="1" class="small-text" />
                                        <span><?php _e('personnes', 'restaurant-booking'); ?></span>
                                        <p class="description"><?php _e('Au-delà de ce nombre, un supplément personnel s\'applique', 'restaurant-booking'); ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Montant supplément personnel', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="number" name="remorque_staff_supplement" value="<?php echo esc_attr($options['remorque_staff_supplement']); ?>" min="0" step="0.01" class="small-text" />
                                        <span>€</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Texte d\'affichage convives', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="text" name="remorque_guests_text" value="<?php echo esc_attr($options['remorque_guests_text']); ?>" class="regular-text" />
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Texte explication supplément', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="text" name="remorque_staff_text" value="<?php echo esc_attr($options['remorque_staff_text']); ?>" class="regular-text" />
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <div class="options-group">
                            <h3><?php _e('Durée événement', 'restaurant-booking'); ?></h3>
                            <table class="form-table">
                                <tr>
                                    <th scope="row"><?php _e('Durée minimum', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="number" name="remorque_min_duration" value="<?php echo esc_attr($options['remorque_min_duration']); ?>" min="1" class="small-text" />
                                        <span><?php _e('heures', 'restaurant-booking'); ?></span>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Durée maximum', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="number" name="remorque_max_duration" value="<?php echo esc_attr($options['remorque_max_duration']); ?>" min="1" class="small-text" />
                                        <span><?php _e('heures', 'restaurant-booking'); ?></span>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Prix par heure supplémentaire', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="number" name="remorque_extra_hour_price" value="<?php echo esc_attr($options['remorque_extra_hour_price']); ?>" min="0" step="0.01" class="small-text" />
                                        <span>€ TTC</span>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <div class="options-group">
                            <h3><?php _e('Distance et Déplacement', 'restaurant-booking'); ?></h3>
                            <p class="description"><?php _e('Configuration du calcul de distance via Google Maps API', 'restaurant-booking'); ?></p>
                            <table class="form-table">
                                <tr>
                                    <th scope="row"><?php _e('Rayon gratuit', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="number" name="free_radius_km" value="<?php echo esc_attr($options['free_radius_km']); ?>" min="0" class="small-text" />
                                        <span>km</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Prix 30-50km', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="number" name="price_30_50km" value="<?php echo esc_attr($options['price_30_50km']); ?>" min="0" step="0.01" class="small-text" />
                                        <span>€</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Prix 50-100km', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="number" name="price_50_100km" value="<?php echo esc_attr($options['price_50_100km']); ?>" min="0" step="0.01" class="small-text" />
                                        <span>€</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Prix 100-150km', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="number" name="price_100_150km" value="<?php echo esc_attr($options['price_100_150km']); ?>" min="0" step="0.01" class="small-text" />
                                        <span>€</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Distance maximum', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="number" name="max_distance_km" value="<?php echo esc_attr($options['max_distance_km']); ?>" min="1" class="small-text" />
                                        <span>km</span>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <div class="options-group">
                            <h3><?php _e('Prix Options Spécifiques', 'restaurant-booking'); ?></h3>
                            <table class="form-table">
                                <tr>
                                    <th scope="row"><?php _e('Mise à disposition tireuse', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="number" name="tireuse_price" value="<?php echo esc_attr($options['tireuse_price']); ?>" min="0" step="0.01" class="small-text" />
                                        <span>€</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Installation jeux', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="number" name="games_price" value="<?php echo esc_attr($options['games_price']); ?>" min="0" step="0.01" class="small-text" />
                                        <span>€</span>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <div class="options-group">
                            <h3><?php _e('Prix de base', 'restaurant-booking'); ?></h3>
                            <table class="form-table">
                                <tr>
                                    <th scope="row"><?php _e('Prix forfait remorque', 'restaurant-booking'); ?></th>
                                    <td>
                                        <input type="number" name="remorque_base_price" value="<?php echo esc_attr($options['remorque_base_price']); ?>" min="0" step="0.01" class="small-text" />
                                        <span>€</span>
                                        <p class="description"><?php _e('Prix de base pour la privatisation de la remorque', 'restaurant-booking'); ?></p>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <div class="options-group">
                            <h3><?php _e('Description forfait', 'restaurant-booking'); ?></h3>
                            <table class="form-table">
                                <tr>
                                    <th scope="row"><?php _e('Éléments inclus', 'restaurant-booking'); ?></th>
                                    <td>
                                        <textarea name="remorque_forfait_description" rows="5" class="large-text"><?php echo esc_textarea($options['remorque_forfait_description']); ?></textarea>
                                        <p class="description"><?php _e('Séparez chaque élément par un pipe (|)', 'restaurant-booking'); ?></p>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                </div>

                <p class="submit">
                    <input type="submit" name="save_options" class="button-primary" value="<?php _e('Sauvegarder toutes les options', 'restaurant-booking'); ?>" />
                    <input type="submit" name="clean_escaped_quotes" class="button-secondary" value="<?php _e('Nettoyer les échappements multiples', 'restaurant-booking'); ?>" style="margin-left: 10px;" 
                           onclick="return confirm('<?php _e('Êtes-vous sûr de vouloir nettoyer les échappements multiples ? Cette action corrigera les apostrophes mal échappées.', 'restaurant-booking'); ?>');" />
                </p>
            </form>
        </div>

        <style>
        .restaurant-booking-info-card {
            background: #f0f8ff;
            border: 1px solid #0073aa;
            border-radius: 4px;
            padding: 15px;
            margin: 20px 0;
        }
        .restaurant-booking-info-card h3 {
            margin-top: 0;
            color: #0073aa;
        }
        .restaurant-booking-options-container {
            max-width: 1200px;
        }
        .options-section {
            background: #fff;
            border: 1px solid #ccd0d4;
            border-radius: 4px;
            margin: 20px 0;
            padding: 20px;
            position: relative;
        }
        /* Liseret orange pour les sections Remorque */
        .options-section-remorque {
            border-left: 5px solid #FF8C00;
        }
        .options-section h2 {
            margin-top: 0;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
        }
        .options-group {
            margin: 20px 0;
            padding: 15px;
            background: #fafafa;
            border-radius: 4px;
            position: relative;
        }
        /* Liseret orange pour les sous-groupes Remorque */
        .options-group-remorque {
            border-left: 4px solid #FF8C00;
            background: #FFF8F0;
        }
        .options-group h3 {
            margin-top: 0;
            color: #333;
        }
        .form-table th {
            width: 250px;
        }
        .small-text {
            width: 80px;
        }
        </style>
        <?php
    }

    /**
     * Obtenir les options (avec valeurs par défaut)
     */
    public function get_options()
    {
        $saved_options = get_option('restaurant_booking_unified_options', array());
        
        // Nettoyer les échappements multiples dans les options sauvegardées
        foreach ($saved_options as $key => $value) {
            if (is_string($value)) {
                $saved_options[$key] = $this->clean_escaped_quotes($value);
            }
        }
        
        // Récupérer les prix de base depuis la table restaurant_settings
        $saved_options['restaurant_base_price'] = RestaurantBooking_Settings::get('restaurant_base_price', 300);
        $saved_options['remorque_base_price'] = RestaurantBooking_Settings::get('remorque_base_price', 350);
        
        return array_merge($this->default_options, $saved_options);
    }

    /**
     * Sauvegarder les options
     */
    private function save_options()
    {
        $options = array();
        
        // Liste des champs qui peuvent contenir du HTML
        $html_allowed_fields = array('email_signature', 'email_footer_text');
        
        // Récupérer toutes les options du formulaire
        foreach ($this->default_options as $key => $default_value) {
            if (isset($_POST[$key])) {
                // Utiliser une sanitization personnalisée pour les champs qui peuvent contenir du HTML
                if (in_array($key, $html_allowed_fields)) {
                    $value = $this->sanitize_html_field($_POST[$key]);
                } else {
                    $value = sanitize_text_field($_POST[$key]);
                }
                
                // Conversion des types pour les valeurs numériques
                if (is_numeric($default_value)) {
                    $options[$key] = floatval($value);
                } elseif (is_bool($default_value)) {
                    // Gestion des checkboxes
                    $options[$key] = ($value === '1');
                } else {
                    // Nettoyer les échappements multiples pour les textes
                    $value = $this->clean_escaped_quotes($value);
                    $options[$key] = $value;
                }
            } else {
                // Pour les checkboxes non cochées, elles ne sont pas dans $_POST
                if (is_bool($default_value)) {
                    $options[$key] = false;
                } else {
                    $options[$key] = $default_value;
                }
            }
        }
        
        // Sauvegarder les prix de base dans la table restaurant_settings
        if (isset($options['restaurant_base_price'])) {
            RestaurantBooking_Settings::set('restaurant_base_price', $options['restaurant_base_price']);
        }
        if (isset($options['remorque_base_price'])) {
            RestaurantBooking_Settings::set('remorque_base_price', $options['remorque_base_price']);
        }
        
        update_option('restaurant_booking_unified_options', $options);
    }
    
    /**
     * Sanitizer les champs HTML en autorisant certaines balises sans double-encoder les entités
     */
    private function sanitize_html_field($value)
    {
        // Supprimer les slashes ajoutés par WordPress
        $value = wp_unslash($value);
        
        // Liste des balises HTML autorisées
        $allowed_html = array(
            'br' => array(),
            'p' => array(),
            'strong' => array(),
            'em' => array(),
            'b' => array(),
            'i' => array(),
            'u' => array(),
            'a' => array(
                'href' => array(),
                'title' => array(),
                'target' => array()
            )
        );
        
        // Utiliser wp_kses pour nettoyer en autorisant seulement certaines balises
        $value = wp_kses($value, $allowed_html);
        
        return $value;
    }
    
    /**
     * Nettoyer les échappements multiples d'apostrophes
     */
    private function clean_escaped_quotes($text)
    {
        // Remplacer les multiples échappements par une seule apostrophe
        $text = preg_replace('/\\\\+\'/', "'", $text);
        $text = preg_replace('/\\\\+\"/', '"', $text);
        
        return $text;
    }
    
    /**
     * Nettoyer tous les échappements dans les options sauvegardées
     */
    private function clean_all_escaped_quotes()
    {
        $options = get_option('restaurant_booking_unified_options', array());
        $updated = false;
        
        // Liste des champs qui peuvent contenir du HTML
        $html_allowed_fields = array('email_signature', 'email_footer_text');
        
        foreach ($options as $key => $value) {
            if (is_string($value)) {
                $original_value = $value;
                
                // Nettoyer les échappements multiples
                $value = $this->clean_escaped_quotes($value);
                
                // Pour les champs HTML, décoder les entités HTML (comme &amp; -> &)
                if (in_array($key, $html_allowed_fields)) {
                    $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                }
                
                if ($value !== $original_value) {
                    $options[$key] = $value;
                    $updated = true;
                }
            }
        }
        
        if ($updated) {
            update_option('restaurant_booking_unified_options', $options);
        }
        
        return $updated;
    }

    /**
     * Obtenir une option spécifique
     */
    public static function get_option($key, $default = null)
    {
        $instance = new self();
        $options = $instance->get_options();
        
        return isset($options[$key]) ? $options[$key] : $default;
    }
}
