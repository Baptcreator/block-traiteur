<?php
/**
 * Classe d'administration des paramètres Block Traiteur
 * 
 * @package Block_Traiteur
 * @since 1.0.0
 */

// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit;
}

class Block_Traiteur_Settings_Admin {
    
    /**
     * Initialisation des hooks
     */
    public function __construct() {
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_post_save_settings', array($this, 'save_settings'));
    }
    
    /**
     * Affichage de la page des paramètres
     */
    public function display_page() {
        // Vérifier les permissions
        if (!current_user_can('manage_options')) {
            wp_die(__('Vous n\'avez pas les permissions suffisantes pour accéder à cette page.', 'block-traiteur'));
        }
        
        // Récupération des paramètres actuels
        $settings = $this->get_all_settings();
        
        ?>
        <div class="wrap">
            <h1><?php _e('Paramètres Block Traiteur', 'block-traiteur'); ?></h1>
            
            <?php if (isset($_GET['settings-updated'])): ?>
                <div class="notice notice-success is-dismissible">
                    <p><?php _e('Paramètres sauvegardés avec succès.', 'block-traiteur'); ?></p>
                </div>
            <?php endif; ?>
            
            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                <input type="hidden" name="action" value="save_settings">
                <?php wp_nonce_field('block_traiteur_settings', 'settings_nonce'); ?>
                
                <!-- Onglets de navigation -->
                <nav class="nav-tab-wrapper">
                    <a href="#general" class="nav-tab nav-tab-active"><?php _e('Général', 'block-traiteur'); ?></a>
                    <a href="#emails" class="nav-tab"><?php _e('Emails', 'block-traiteur'); ?></a>
                    <a href="#pricing" class="nav-tab"><?php _e('Tarification', 'block-traiteur'); ?></a>
                    <a href="#integrations" class="nav-tab"><?php _e('Intégrations', 'block-traiteur'); ?></a>
                    <a href="#advanced" class="nav-tab"><?php _e('Avancé', 'block-traiteur'); ?></a>
                </nav>
                
                <!-- Onglet Général -->
                <div id="general" class="tab-content active">
                    <h2><?php _e('Informations Générales', 'block-traiteur'); ?></h2>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="company_name"><?php _e('Nom de l\'entreprise', 'block-traiteur'); ?></label>
                            </th>
                            <td>
                                <input type="text" name="company_name" id="company_name" 
                                       value="<?php echo esc_attr($settings['company_name']); ?>" 
                                       class="regular-text" required>
                                <p class="description"><?php _e('Nom affiché sur les devis et emails.', 'block-traiteur'); ?></p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="company_address"><?php _e('Adresse', 'block-traiteur'); ?></label>
                            </th>
                            <td>
                                <textarea name="company_address" id="company_address" 
                                          class="large-text" rows="3"><?php echo esc_textarea($settings['company_address']); ?></textarea>
                                <p class="description"><?php _e('Adresse complète de l\'entreprise.', 'block-traiteur'); ?></p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="company_phone"><?php _e('Téléphone', 'block-traiteur'); ?></label>
                            </th>
                            <td>
                                <input type="tel" name="company_phone" id="company_phone" 
                                       value="<?php echo esc_attr($settings['company_phone']); ?>" 
                                       class="regular-text">
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="company_email"><?php _e('Email de contact', 'block-traiteur'); ?></label>
                            </th>
                            <td>
                                <input type="email" name="company_email" id="company_email" 
                                       value="<?php echo esc_attr($settings['company_email']); ?>" 
                                       class="regular-text" required>
                                <p class="description"><?php _e('Email principal pour les demandes de devis.', 'block-traiteur'); ?></p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="company_website"><?php _e('Site web', 'block-traiteur'); ?></label>
                            </th>
                            <td>
                                <input type="url" name="company_website" id="company_website" 
                                       value="<?php echo esc_attr($settings['company_website']); ?>" 
                                       class="regular-text">
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="currency"><?php _e('Devise', 'block-traiteur'); ?></label>
                            </th>
                            <td>
                                <select name="currency" id="currency">
                                    <option value="EUR" <?php selected($settings['currency'], 'EUR'); ?>>Euro (€)</option>
                                    <option value="USD" <?php selected($settings['currency'], 'USD'); ?>>Dollar ($)</option>
                                    <option value="GBP" <?php selected($settings['currency'], 'GBP'); ?>>Livre Sterling (£)</option>
                                </select>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="tax_rate"><?php _e('Taux de TVA (%)', 'block-traiteur'); ?></label>
                            </th>
                            <td>
                                <input type="number" name="tax_rate" id="tax_rate" 
                                       value="<?php echo esc_attr($settings['tax_rate']); ?>" 
                                       step="0.01" min="0" max="100" class="small-text">
                                <p class="description"><?php _e('Taux de TVA appliqué aux devis.', 'block-traiteur'); ?></p>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <!-- Onglet Emails -->
                <div id="emails" class="tab-content">
                    <h2><?php _e('Configuration des Emails', 'block-traiteur'); ?></h2>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="email_notifications"><?php _e('Notifications par email', 'block-traiteur'); ?></label>
                            </th>
                            <td>
                                <input type="checkbox" name="email_notifications" id="email_notifications" 
                                       value="1" <?php checked($settings['email_notifications']); ?>>
                                <label for="email_notifications"><?php _e('Activer les notifications par email', 'block-traiteur'); ?></label>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="admin_email"><?php _e('Email administrateur', 'block-traiteur'); ?></label>
                            </th>
                            <td>
                                <input type="email" name="admin_email" id="admin_email" 
                                       value="<?php echo esc_attr($settings['admin_email']); ?>" 
                                       class="regular-text" required>
                                <p class="description"><?php _e('Email qui recevra les notifications de nouveaux devis.', 'block-traiteur'); ?></p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="email_from_name"><?php _e('Nom de l\'expéditeur', 'block-traiteur'); ?></label>
                            </th>
                            <td>
                                <input type="text" name="email_from_name" id="email_from_name" 
                                       value="<?php echo esc_attr($settings['email_from_name']); ?>" 
                                       class="regular-text">
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="email_signature"><?php _e('Signature email', 'block-traiteur'); ?></label>
                            </th>
                            <td>
                                <textarea name="email_signature" id="email_signature" 
                                          class="large-text" rows="5"><?php echo esc_textarea($settings['email_signature']); ?></textarea>
                                <p class="description"><?php _e('Signature ajoutée à tous les emails envoyés.', 'block-traiteur'); ?></p>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <!-- Onglet Tarification -->
                <div id="pricing" class="tab-content">
                    <h2><?php _e('Paramètres de Tarification', 'block-traiteur'); ?></h2>
                    
                    <h3><?php _e('Privatisation Restaurant', 'block-traiteur'); ?></h3>
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="privatisation_base_price"><?php _e('Prix de base', 'block-traiteur'); ?></label>
                            </th>
                            <td>
                                <input type="number" name="privatisation_base_price" id="privatisation_base_price" 
                                       value="<?php echo esc_attr($settings['privatisation_base_price']); ?>" 
                                       step="0.01" min="0" class="regular-text"> €
                                <p class="description"><?php _e('Prix de base pour la privatisation (10-30 personnes).', 'block-traiteur'); ?></p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="privatisation_price_per_person"><?php _e('Prix par personne supplémentaire', 'block-traiteur'); ?></label>
                            </th>
                            <td>
                                <input type="number" name="privatisation_price_per_person" id="privatisation_price_per_person" 
                                       value="<?php echo esc_attr($settings['privatisation_price_per_person']); ?>" 
                                       step="0.01" min="0" class="regular-text"> €
                            </td>
                        </tr>
                    </table>
                    
                    <h3><?php _e('Prestation Remorque Mobile', 'block-traiteur'); ?></h3>
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="remorque_base_price"><?php _e('Prix de base', 'block-traiteur'); ?></label>
                            </th>
                            <td>
                                <input type="number" name="remorque_base_price" id="remorque_base_price" 
                                       value="<?php echo esc_attr($settings['remorque_base_price']); ?>" 
                                       step="0.01" min="0" class="regular-text"> €
                                <p class="description"><?php _e('Prix de base pour la prestation remorque (20-100 personnes).', 'block-traiteur'); ?></p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="remorque_price_per_person"><?php _e('Prix par personne supplémentaire', 'block-traiteur'); ?></label>
                            </th>
                            <td>
                                <input type="number" name="remorque_price_per_person" id="remorque_price_per_person" 
                                       value="<?php echo esc_attr($settings['remorque_price_per_person']); ?>" 
                                       step="0.01" min="0" class="regular-text"> €
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="transport_fee"><?php _e('Frais de transport', 'block-traiteur'); ?></label>
                            </th>
                            <td>
                                <input type="number" name="transport_fee" id="transport_fee" 
                                       value="<?php echo esc_attr($settings['transport_fee']); ?>" 
                                       step="0.01" min="0" class="regular-text"> €
                                <p class="description"><?php _e('Frais de transport pour la remorque mobile.', 'block-traiteur'); ?></p>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <!-- Onglet Intégrations -->
                <div id="integrations" class="tab-content">
                    <h2><?php _e('Intégrations Externes', 'block-traiteur'); ?></h2>
                    
                    <h3><?php _e('Google Calendar', 'block-traiteur'); ?></h3>
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="google_calendar_enabled"><?php _e('Activer Google Calendar', 'block-traiteur'); ?></label>
                            </th>
                            <td>
                                <input type="checkbox" name="google_calendar_enabled" id="google_calendar_enabled" 
                                       value="1" <?php checked($settings['google_calendar_enabled']); ?>>
                                <label for="google_calendar_enabled"><?php _e('Synchroniser avec Google Calendar', 'block-traiteur'); ?></label>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="google_calendar_id"><?php _e('ID du calendrier', 'block-traiteur'); ?></label>
                            </th>
                            <td>
                                <input type="text" name="google_calendar_id" id="google_calendar_id" 
                                       value="<?php echo esc_attr($settings['google_calendar_id']); ?>" 
                                       class="regular-text">
                                <p class="description"><?php _e('Votre ID de calendrier Google (ex: votre-email@gmail.com).', 'block-traiteur'); ?></p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="google_api_key"><?php _e('Clé API Google', 'block-traiteur'); ?></label>
                            </th>
                            <td>
                                <input type="password" name="google_api_key" id="google_api_key" 
                                       value="<?php echo esc_attr($settings['google_api_key']); ?>" 
                                       class="regular-text">
                                <p class="description"><?php _e('Clé API pour accéder à Google Calendar.', 'block-traiteur'); ?></p>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <!-- Onglet Avancé -->
                <div id="advanced" class="tab-content">
                    <h2><?php _e('Paramètres Avancés', 'block-traiteur'); ?></h2>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="debug_mode"><?php _e('Mode debug', 'block-traiteur'); ?></label>
                            </th>
                            <td>
                                <input type="checkbox" name="debug_mode" id="debug_mode" 
                                       value="1" <?php checked($settings['debug_mode']); ?>>
                                <label for="debug_mode"><?php _e('Activer les logs de débogage', 'block-traiteur'); ?></label>
                                <p class="description"><?php _e('Active l\'enregistrement détaillé des actions du plugin.', 'block-traiteur'); ?></p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="cache_enabled"><?php _e('Cache', 'block-traiteur'); ?></label>
                            </th>
                            <td>
                                <input type="checkbox" name="cache_enabled" id="cache_enabled" 
                                       value="1" <?php checked($settings['cache_enabled']); ?>>
                                <label for="cache_enabled"><?php _e('Activer le cache pour améliorer les performances', 'block-traiteur'); ?></label>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="auto_cleanup"><?php _e('Nettoyage automatique', 'block-traiteur'); ?></label>
                            </th>
                            <td>
                                <input type="checkbox" name="auto_cleanup" id="auto_cleanup" 
                                       value="1" <?php checked($settings['auto_cleanup']); ?>>
                                <label for="auto_cleanup"><?php _e('Supprimer automatiquement les anciens devis (> 2 ans)', 'block-traiteur'); ?></label>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="quote_validity_days"><?php _e('Validité des devis (jours)', 'block-traiteur'); ?></label>
                            </th>
                            <td>
                                <input type="number" name="quote_validity_days" id="quote_validity_days" 
                                       value="<?php echo esc_attr($settings['quote_validity_days']); ?>" 
                                       min="1" max="365" class="small-text">
                                <p class="description"><?php _e('Durée de validité des devis en jours.', 'block-traiteur'); ?></p>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <?php submit_button(__('Sauvegarder les paramètres', 'block-traiteur')); ?>
            </form>
        </div>
        
        <style>
        .nav-tab-wrapper {
            margin-bottom: 20px;
        }
        
        .tab-content {
            display: none;
            background: white;
            padding: 20px;
            border: 1px solid #ccd0d4;
            border-top: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .tab-content h2 {
            margin-top: 0;
        }
        
        .tab-content h3 {
            margin-top: 30px;
            margin-bottom: 15px;
            padding-bottom: 5px;
            border-bottom: 1px solid #ddd;
        }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            // Gestion des onglets
            $('.nav-tab').on('click', function(e) {
                e.preventDefault();
                
                // Retirer la classe active de tous les onglets et contenus
                $('.nav-tab').removeClass('nav-tab-active');
                $('.tab-content').removeClass('active');
                
                // Ajouter la classe active à l'onglet cliqué
                $(this).addClass('nav-tab-active');
                
                // Afficher le contenu correspondant
                var target = $(this).attr('href');
                $(target).addClass('active');
            });
            
            // Validation du formulaire
            $('form').on('submit', function() {
                var valid = true;
                
                // Vérifier les champs requis
                $('input[required], select[required], textarea[required]').each(function() {
                    if (!$(this).val()) {
                        $(this).css('border-color', '#dc3232');
                        valid = false;
                    } else {
                        $(this).css('border-color', '');
                    }
                });
                
                if (!valid) {
                    alert('<?php _e('Veuillez remplir tous les champs obligatoires.', 'block-traiteur'); ?>');
                }
                
                return valid;
            });
        });
        </script>
        <?php
    }
    
    /**
     * Enregistrement des paramètres WordPress
     */
    public function register_settings() {
        $settings = array(
            'block_traiteur_company_name',
            'block_traiteur_company_address',
            'block_traiteur_company_phone',
            'block_traiteur_company_email',
            'block_traiteur_company_website',
            'block_traiteur_currency',
            'block_traiteur_tax_rate',
            'block_traiteur_email_notifications',
            'block_traiteur_admin_email',
            'block_traiteur_email_from_name',
            'block_traiteur_email_signature',
            'block_traiteur_privatisation_base_price',
            'block_traiteur_privatisation_price_per_person',
            'block_traiteur_remorque_base_price',
            'block_traiteur_remorque_price_per_person',
            'block_traiteur_transport_fee',
            'block_traiteur_google_calendar_enabled',
            'block_traiteur_google_calendar_id',
            'block_traiteur_google_api_key',
            'block_traiteur_debug_mode',
            'block_traiteur_cache_enabled',
            'block_traiteur_auto_cleanup',
            'block_traiteur_quote_validity_days'
        );
        
        foreach ($settings as $setting) {
            register_setting('block_traiteur_settings', $setting);
        }
    }
    
    /**
     * Sauvegarder les paramètres
     */
    public function save_settings() {
        // Vérifier les permissions et le nonce
        if (!current_user_can('manage_options') || !wp_verify_nonce($_POST['settings_nonce'], 'block_traiteur_settings')) {
            wp_die(__('Accès non autorisé.', 'block-traiteur'));
        }
        
        // Mapping des champs
        $fields_mapping = array(
            'company_name' => 'block_traiteur_company_name',
            'company_address' => 'block_traiteur_company_address',
            'company_phone' => 'block_traiteur_company_phone',
            'company_email' => 'block_traiteur_company_email',
            'company_website' => 'block_traiteur_company_website',
            'currency' => 'block_traiteur_currency',
            'tax_rate' => 'block_traiteur_tax_rate',
            'email_notifications' => 'block_traiteur_email_notifications',
            'admin_email' => 'block_traiteur_admin_email',
            'email_from_name' => 'block_traiteur_email_from_name',
            'email_signature' => 'block_traiteur_email_signature',
            'privatisation_base_price' => 'block_traiteur_privatisation_base_price',
            'privatisation_price_per_person' => 'block_traiteur_privatisation_price_per_person',
            'remorque_base_price' => 'block_traiteur_remorque_base_price',
            'remorque_price_per_person' => 'block_traiteur_remorque_price_per_person',
            'transport_fee' => 'block_traiteur_transport_fee',
            'google_calendar_enabled' => 'block_traiteur_google_calendar_enabled',
            'google_calendar_id' => 'block_traiteur_google_calendar_id',
            'google_api_key' => 'block_traiteur_google_api_key',
            'debug_mode' => 'block_traiteur_debug_mode',
            'cache_enabled' => 'block_traiteur_cache_enabled',
            'auto_cleanup' => 'block_traiteur_auto_cleanup',
            'quote_validity_days' => 'block_traiteur_quote_validity_days'
        );
        
        // Sauvegarder chaque paramètre
        foreach ($fields_mapping as $field => $option_name) {
            if (isset($_POST[$field])) {
                $value = sanitize_text_field($_POST[$field]);
                
                // Validation spécifique selon le type de champ
                switch ($field) {
                    case 'company_email':
                    case 'admin_email':
                        $value = sanitize_email($_POST[$field]);
                        break;
                    case 'company_website':
                        $value = esc_url_raw($_POST[$field]);
                        break;
                    case 'company_address':
                    case 'email_signature':
                        $value = sanitize_textarea_field($_POST[$field]);
                        break;
                    case 'tax_rate':
                    case 'privatisation_base_price':
                    case 'privatisation_price_per_person':
                    case 'remorque_base_price':
                    case 'remorque_price_per_person':
                    case 'transport_fee':
                        $value = floatval($_POST[$field]);
                        break;
                    case 'quote_validity_days':
                        $value = intval($_POST[$field]);
                        break;
                    case 'email_notifications':
                    case 'google_calendar_enabled':
                    case 'debug_mode':
                    case 'cache_enabled':
                    case 'auto_cleanup':
                        $value = 1;
                        break;
                }
                
                update_option($option_name, $value);
            } else {
                // Pour les checkboxes non cochées
                if (in_array($field, array('email_notifications', 'google_calendar_enabled', 'debug_mode', 'cache_enabled', 'auto_cleanup'))) {
                    update_option($fields_mapping[$field], 0);
                }
            }
        }
        
        // Redirection avec message de succès
        wp_redirect(add_query_arg(array(
            'page' => 'block-traiteur-settings',
            'settings-updated' => 'true'
        ), admin_url('admin.php')));
        exit;
    }
    
    /**
     * Récupérer tous les paramètres avec valeurs par défaut
     */
    private function get_all_settings() {
        return array(
            'company_name' => get_option('block_traiteur_company_name', 'Block Street Food & Events'),
            'company_address' => get_option('block_traiteur_company_address', '6 allée Adèle Klein, 67000 Strasbourg'),
            'company_phone' => get_option('block_traiteur_company_phone', '06 58 13 38 05'),
            'company_email' => get_option('block_traiteur_company_email', 'contact@block-strasbourg.fr'),
            'company_website' => get_option('block_traiteur_company_website', 'https://block-strasbourg.fr'),
            'currency' => get_option('block_traiteur_currency', 'EUR'),
            'tax_rate' => get_option('block_traiteur_tax_rate', 20.0),
            'email_notifications' => get_option('block_traiteur_email_notifications', 1),
            'admin_email' => get_option('block_traiteur_admin_email', get_option('admin_email')),
            'email_from_name' => get_option('block_traiteur_email_from_name', 'Block Street Food'),
            'email_signature' => get_option('block_traiteur_email_signature', "Cordialement,\nL'équipe Block Street Food & Events"),
            'privatisation_base_price' => get_option('block_traiteur_privatisation_base_price', 500.0),
            'privatisation_price_per_person' => get_option('block_traiteur_privatisation_price_per_person', 25.0),
            'remorque_base_price' => get_option('block_traiteur_remorque_base_price', 800.0),
            'remorque_price_per_person' => get_option('block_traiteur_remorque_price_per_person', 20.0),
            'transport_fee' => get_option('block_traiteur_transport_fee', 100.0),
            'google_calendar_enabled' => get_option('block_traiteur_google_calendar_enabled', 0),
            'google_calendar_id' => get_option('block_traiteur_google_calendar_id', ''),
            'google_api_key' => get_option('block_traiteur_google_api_key', ''),
            'debug_mode' => get_option('block_traiteur_debug_mode', 0),
            'cache_enabled' => get_option('block_traiteur_cache_enabled', 1),
            'auto_cleanup' => get_option('block_traiteur_auto_cleanup', 0),
            'quote_validity_days' => get_option('block_traiteur_quote_validity_days', 30)
        );
    }
}