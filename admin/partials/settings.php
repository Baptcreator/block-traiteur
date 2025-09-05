<?php
/**
 * Template de la page des paramètres - Block Traiteur
 *
 * @package Block_Traiteur
 * @subpackage Admin/Partials
 * @since 1.0.0
 */

// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit;
}

// Récupérer les statistiques de configuration
$config_stats = $this->get_configuration_stats();
$tabs = $this->get_settings_tabs();
?>

<div class="wrap block-traiteur-settings">
    <!-- En-tête de la page -->
    <div class="settings-header">
        <div class="header-content">
            <div class="header-title">
                <h1>
                    <span class="dashicons dashicons-admin-settings"></span>
                    <?php _e('Paramètres Block Traiteur', 'block-traiteur'); ?>
                </h1>
                <p class="description">
                    <?php _e('Configurez votre système de devis en ligne', 'block-traiteur'); ?>
                </p>
            </div>
            
            <div class="header-stats">
                <div class="config-progress">
                    <div class="progress-label">
                        <?php _e('Configuration', 'block-traiteur'); ?>
                        <span class="progress-percentage"><?php echo $config_stats['completion_percentage']; ?>%</span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?php echo $config_stats['completion_percentage']; ?>%"></div>
                    </div>
                </div>
                
                <div class="quick-actions">
                    <button type="button" class="button button-secondary test-all-btn">
                        <span class="dashicons dashicons-yes-alt"></span>
                        <?php _e('Tester la config', 'block-traiteur'); ?>
                    </button>
                    <button type="button" class="button button-primary save-all-btn">
                        <span class="dashicons dashicons-saved"></span>
                        <?php _e('Sauvegarder tout', 'block-traiteur'); ?>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation par onglets -->
    <nav class="settings-tabs-nav">
        <ul class="tabs-list">
            <?php foreach ($tabs as $tab_key => $tab): ?>
                <li class="tab-item <?php echo $tab['active'] ? 'active' : ''; ?>">
                    <a href="<?php echo esc_url($tab['url']); ?>" class="tab-link">
                        <span class="dashicons dashicons-<?php echo esc_attr($tab['icon']); ?>"></span>
                        <span class="tab-text"><?php echo esc_html($tab['title']); ?></span>
                        <?php if ($tab_key === 'general' && !$config_stats['company_configured']): ?>
                            <span class="tab-badge warning">!</span>
                        <?php elseif ($tab_key === 'calendar' && $config_stats['calendar_configured']): ?>
                            <span class="tab-badge success">✓</span>
                        <?php endif; ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </nav>

    <!-- Zone de contenu principale -->
    <div class="settings-content">
        <!-- Messages de statut -->
        <div id="settings-messages" class="settings-messages" style="display: none;">
            <div class="notice notice-success">
                <p class="message-text"></p>
            </div>
        </div>

        <!-- Formulaire des paramètres -->
        <form id="block-traiteur-settings-form" method="post" action="options.php">
            <?php
            // Sécurité WordPress
            settings_fields('block_traiteur_' . $this->current_tab);
            ?>
            
            <div class="settings-tab-content" id="tab-<?php echo esc_attr($this->current_tab); ?>">
                
                <?php if ($this->current_tab === 'general'): ?>
                    <!-- Onglet Général -->
                    <div class="settings-sections">
                        <?php $this->render_company_info_fields(); ?>
                        <?php $this->render_pricing_fields(); ?>
                        <?php $this->render_capacity_fields(); ?>
                    </div>
                    
                <?php elseif ($this->current_tab === 'services'): ?>
                    <!-- Onglet Services -->
                    <div class="settings-sections">
                        <div class="settings-section">
                            <h3><?php _e('Configuration des services', 'block-traiteur'); ?></h3>
                            
                            <div class="services-grid">
                                <!-- Configuration Restaurant -->
                                <div class="service-config restaurant-config">
                                    <div class="service-header">
                                        <span class="dashicons dashicons-building"></span>
                                        <h4><?php _e('Restaurant', 'block-traiteur'); ?></h4>
                                    </div>
                                    
                                    <table class="form-table">
                                        <tr>
                                            <th scope="row"><?php _e('Durée minimale', 'block-traiteur'); ?></th>
                                            <td>
                                                <input type="number" name="block_traiteur_restaurant_min_duration" 
                                                       value="<?php echo esc_attr(get_option('block_traiteur_restaurant_min_duration', 2)); ?>" 
                                                       min="1" max="8" class="small-text" /> h
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row"><?php _e('Durée maximale', 'block-traiteur'); ?></th>
                                            <td>
                                                <input type="number" name="block_traiteur_restaurant_max_duration" 
                                                       value="<?php echo esc_attr(get_option('block_traiteur_restaurant_max_duration', 4)); ?>" 
                                                       min="2" max="12" class="small-text" /> h
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row"><?php _e('Disponible', 'block-traiteur'); ?></th>
                                            <td>
                                                <label>
                                                    <input type="checkbox" name="block_traiteur_restaurant_enabled" 
                                                           value="1" <?php checked(get_option('block_traiteur_restaurant_enabled', 1)); ?> />
                                                    <?php _e('Service actif', 'block-traiteur'); ?>
                                                </label>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                
                                <!-- Configuration Remorque -->
                                <div class="service-config remorque-config">
                                    <div class="service-header">
                                        <span class="dashicons dashicons-car"></span>
                                        <h4><?php _e('Remorque Mobile', 'block-traiteur'); ?></h4>
                                    </div>
                                    
                                    <table class="form-table">
                                        <tr>
                                            <th scope="row"><?php _e('Durée minimale', 'block-traiteur'); ?></th>
                                            <td>
                                                <input type="number" name="block_traiteur_remorque_min_duration" 
                                                       value="<?php echo esc_attr(get_option('block_traiteur_remorque_min_duration', 2)); ?>" 
                                                       min="1" max="8" class="small-text" /> h
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row"><?php _e('Durée maximale', 'block-traiteur'); ?></th>
                                            <td>
                                                <input type="number" name="block_traiteur_remorque_max_duration" 
                                                       value="<?php echo esc_attr(get_option('block_traiteur_remorque_max_duration', 5)); ?>" 
                                                       min="2" max="12" class="small-text" /> h
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row"><?php _e('Rayon maximum', 'block-traiteur'); ?></th>
                                            <td>
                                                <input type="number" name="block_traiteur_max_travel_distance" 
                                                       value="<?php echo esc_attr(get_option('block_traiteur_max_travel_distance', 150)); ?>" 
                                                       min="10" max="500" class="small-text" /> km
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row"><?php _e('Disponible', 'block-traiteur'); ?></th>
                                            <td>
                                                <label>
                                                    <input type="checkbox" name="block_traiteur_remorque_enabled" 
                                                           value="1" <?php checked(get_option('block_traiteur_remorque_enabled', 1)); ?> />
                                                    <?php _e('Service actif', 'block-traiteur'); ?>
                                                </label>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Configuration des distances -->
                        <div class="settings-section">
                            <h3><?php _e('Tarification par distance', 'block-traiteur'); ?></h3>
                            <p class="description">
                                <?php _e('Configurez les suppléments de déplacement selon les zones géographiques', 'block-traiteur'); ?>
                            </p>
                            
                            <div class="distance-zones">
                                <?php
                                $zones = get_option('block_traiteur_travel_cost_zones', array(
                                    '0-30' => 0,
                                    '30-50' => 20,
                                    '50-100' => 70,
                                    '100-150' => 118
                                ));
                                ?>
                                
                                <table class="widefat fixed striped">
                                    <thead>
                                        <tr>
                                            <th><?php _e('Zone (km)', 'block-traiteur'); ?></th>
                                            <th><?php _e('Supplément (€)', 'block-traiteur'); ?></th>
                                            <th><?php _e('Description', 'block-traiteur'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($zones as $zone => $cost): ?>
                                            <tr>
                                                <td><strong><?php echo esc_html($zone); ?> km</strong></td>
                                                <td>
                                                    <input type="number" 
                                                           name="block_traiteur_travel_cost_zones[<?php echo esc_attr($zone); ?>]" 
                                                           value="<?php echo esc_attr($cost); ?>" 
                                                           min="0" step="0.01" class="small-text" /> €
                                                </td>
                                                <td class="zone-description">
                                                    <?php
                                                    switch ($zone) {
                                                        case '0-30':
                                                            _e('Zone gratuite - Strasbourg et environs', 'block-traiteur');
                                                            break;
                                                        case '30-50':
                                                            _e('Proche périphérie', 'block-traiteur');
                                                            break;
                                                        case '50-100':
                                                            _e('Région Grand Est', 'block-traiteur');
                                                            break;
                                                        case '100-150':
                                                            _e('Régions limitrophes', 'block-traiteur');
                                                            break;
                                                    }
                                                    ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                <?php elseif ($this->current_tab === 'calendar'): ?>
                    <!-- Onglet Calendrier -->
                    <div class="settings-sections">
                        <?php $this->render_google_calendar_fields(); ?>
                        
                        <div class="settings-section">
                            <h3><?php _e('Paramètres de disponibilité', 'block-traiteur'); ?></h3>
                            <table class="form-table">
                                <tr>
                                    <th scope="row"><?php _e('Délai minimum de réservation', 'block-traiteur'); ?></th>
                                    <td>
                                        <input type="number" name="block_traiteur_min_booking_delay" 
                                               value="<?php echo esc_attr(get_option('block_traiteur_min_booking_delay', 7)); ?>" 
                                               min="1" max="30" class="small-text" /> <?php _e('jours', 'block-traiteur'); ?>
                                        <p class="description"><?php _e('Nombre de jours minimum avant l\'événement', 'block-traiteur'); ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Jours de fermeture', 'block-traiteur'); ?></th>
                                    <td>
                                        <?php
                                        $closed_days = get_option('block_traiteur_closed_days', array());
                                        $days = array(
                                            'monday' => __('Lundi', 'block-traiteur'),
                                            'tuesday' => __('Mardi', 'block-traiteur'),
                                            'wednesday' => __('Mercredi', 'block-traiteur'),
                                            'thursday' => __('Jeudi', 'block-traiteur'),
                                            'friday' => __('Vendredi', 'block-traiteur'),
                                            'saturday' => __('Samedi', 'block-traiteur'),
                                            'sunday' => __('Dimanche', 'block-traiteur')
                                        );
                                        ?>
                                        <div class="days-checkboxes">
                                            <?php foreach ($days as $day_key => $day_name): ?>
                                                <label class="day-checkbox">
                                                    <input type="checkbox" name="block_traiteur_closed_days[]" 
                                                           value="<?php echo esc_attr($day_key); ?>"
                                                           <?php checked(in_array($day_key, $closed_days)); ?> />
                                                    <?php echo esc_html($day_name); ?>
                                                </label>
                                            <?php endforeach; ?>
                                        </div>
                                        <p class="description"><?php _e('Jours où les services ne sont pas disponibles', 'block-traiteur'); ?></p>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                <?php elseif ($this->current_tab === 'emails'): ?>
                    <!-- Onglet Emails -->
                    <div class="settings-sections">
                        <div class="settings-section">
                            <h3><?php _e('Configuration des emails', 'block-traiteur'); ?></h3>
                            <table class="form-table">
                                <tr>
                                    <th scope="row"><?php _e('Nom expéditeur', 'block-traiteur'); ?></th>
                                    <td>
                                        <input type="text" name="block_traiteur_email_from_name" 
                                               value="<?php echo esc_attr(get_option('block_traiteur_email_from_name', get_option('block_traiteur_company_name', ''))); ?>" 
                                               class="regular-text" />
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Email expéditeur', 'block-traiteur'); ?></th>
                                    <td>
                                        <input type="email" name="block_traiteur_email_from_email" 
                                               value="<?php echo esc_attr(get_option('block_traiteur_email_from_email', get_option('admin_email'))); ?>" 
                                               class="regular-text" required />
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Email de notification admin', 'block-traiteur'); ?></th>
                                    <td>
                                        <input type="email" name="block_traiteur_admin_notification_email" 
                                               value="<?php echo esc_attr(get_option('block_traiteur_admin_notification_email', get_option('admin_email'))); ?>" 
                                               class="regular-text" required />
                                        <p class="description"><?php _e('Email qui recevra les notifications de nouveaux devis', 'block-traiteur'); ?></p>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        
                        <div class="settings-section">
                            <h3><?php _e('Configuration SMTP (optionnel)', 'block-traiteur'); ?></h3>
                            <p class="description">
                                <?php _e('Pour une meilleure délivrabilité, configurez un serveur SMTP', 'block-traiteur'); ?>
                            </p>
                            
                            <table class="form-table">
                                <tr>
                                    <th scope="row"><?php _e('Utiliser SMTP', 'block-traiteur'); ?></th>
                                    <td>
                                        <label>
                                            <input type="checkbox" name="block_traiteur_smtp_enabled" 
                                                   value="1" <?php checked(get_option('block_traiteur_smtp_enabled', false)); ?> />
                                            <?php _e('Activer l\'envoi SMTP', 'block-traiteur'); ?>
                                        </label>
                                    </td>
                                </tr>
                            </table>
                            
                            <div id="smtp-configuration" style="<?php echo get_option('block_traiteur_smtp_enabled') ? '' : 'display:none;'; ?>">
                                <table class="form-table">
                                    <tr>
                                        <th scope="row"><?php _e('Serveur SMTP', 'block-traiteur'); ?></th>
                                        <td>
                                            <input type="text" name="block_traiteur_smtp_host" 
                                                   value="<?php echo esc_attr(get_option('block_traiteur_smtp_host', '')); ?>" 
                                                   class="regular-text" placeholder="smtp.gmail.com" />
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row"><?php _e('Port', 'block-traiteur'); ?></th>
                                        <td>
                                            <input type="number" name="block_traiteur_smtp_port" 
                                                   value="<?php echo esc_attr(get_option('block_traiteur_smtp_port', 587)); ?>" 
                                                   class="small-text" />
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row"><?php _e('Chiffrement', 'block-traiteur'); ?></th>
                                        <td>
                                            <select name="block_traiteur_smtp_encryption">
                                                <option value="tls" <?php selected(get_option('block_traiteur_smtp_encryption', 'tls'), 'tls'); ?>>TLS</option>
                                                <option value="ssl" <?php selected(get_option('block_traiteur_smtp_encryption'), 'ssl'); ?>>SSL</option>
                                                <option value="" <?php selected(get_option('block_traiteur_smtp_encryption'), ''); ?>><?php _e('Aucun', 'block-traiteur'); ?></option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row"><?php _e('Nom d\'utilisateur', 'block-traiteur'); ?></th>
                                        <td>
                                            <input type="text" name="block_traiteur_smtp_username" 
                                                   value="<?php echo esc_attr(get_option('block_traiteur_smtp_username', '')); ?>" 
                                                   class="regular-text" />
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row"><?php _e('Mot de passe', 'block-traiteur'); ?></th>
                                        <td>
                                            <input type="password" name="block_traiteur_smtp_password" 
                                                   value="<?php echo esc_attr(get_option('block_traiteur_smtp_password', '')); ?>" 
                                                   class="regular-text" />
                                            <p class="description"><?php _e('Le mot de passe est stocké de manière chiffrée', 'block-traiteur'); ?></p>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        
                        <div class="settings-section">
                            <h3><?php _e('Test d\'envoi', 'block-traiteur'); ?></h3>
                            <table class="form-table">
                                <tr>
                                    <th scope="row"><?php _e('Email de test', 'block-traiteur'); ?></th>
                                    <td>
                                        <input type="email" id="test-email-address" 
                                               value="<?php echo esc_attr(get_option('admin_email')); ?>" 
                                               class="regular-text" />
                                        <button type="button" class="button test-email-btn">
                                            <?php _e('Envoyer un test', 'block-traiteur'); ?>
                                        </button>
                                        <span class="email-test-result"></span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                <?php elseif ($this->current_tab === 'advanced'): ?>
                    <!-- Onglet Avancé -->
                    <div class="settings-sections">
                        <div class="settings-section">
                            <h3><?php _e('Sécurité', 'block-traiteur'); ?></h3>
                            <table class="form-table">
                                <tr>
                                    <th scope="row"><?php _e('Limitation de taux', 'block-traiteur'); ?></th>
                                    <td>
                                        <label>
                                            <input type="checkbox" name="block_traiteur_rate_limit_enabled" 
                                                   value="1" <?php checked(get_option('block_traiteur_rate_limit_enabled', true)); ?> />
                                            <?php _e('Activer la protection anti-spam', 'block-traiteur'); ?>
                                        </label>
                                        <p class="description"><?php _e('Limite le nombre de soumissions par IP', 'block-traiteur'); ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Nombre max de tentatives', 'block-traiteur'); ?></th>
                                    <td>
                                        <input type="number" name="block_traiteur_rate_limit_requests" 
                                               value="<?php echo esc_attr(get_option('block_traiteur_rate_limit_requests', 5)); ?>" 
                                               min="1" max="50" class="small-text" />
                                        <span><?php _e('tentatives par 5 minutes', 'block-traiteur'); ?></span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        
                        <div class="settings-section">
                            <h3><?php _e('Performance', 'block-traiteur'); ?></h3>
                            <table class="form-table">
                                <tr>
                                    <th scope="row"><?php _e('Cache', 'block-traiteur'); ?></th>
                                    <td>
                                        <label>
                                            <input type="checkbox" name="block_traiteur_cache_enabled" 
                                                   value="1" <?php checked(get_option('block_traiteur_cache_enabled', true)); ?> />
                                            <?php _e('Activer le cache des paramètres', 'block-traiteur'); ?>
                                        </label>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Durée du cache', 'block-traiteur'); ?></th>
                                    <td>
                                        <input type="number" name="block_traiteur_cache_duration" 
                                               value="<?php echo esc_attr(get_option('block_traiteur_cache_duration', 3600)); ?>" 
                                               min="300" max="86400" class="small-text" />
                                        <span><?php _e('secondes', 'block-traiteur'); ?></span>
                                        <p class="description"><?php _e('3600 = 1 heure, 86400 = 24 heures', 'block-traiteur'); ?></p>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        
                        <div class="settings-section">
                            <h3><?php _e('Débogage', 'block-traiteur'); ?></h3>
                            <table class="form-table">
                                <tr>
                                    <th scope="row"><?php _e('Mode debug', 'block-traiteur'); ?></th>
                                    <td>
                                        <label>
                                            <input type="checkbox" name="block_traiteur_debug_mode" 
                                                   value="1" <?php checked(get_option('block_traiteur_debug_mode', false)); ?> />
                                            <?php _e('Activer les logs détaillés', 'block-traiteur'); ?>
                                        </label>
                                        <p class="description">
                                            <?php _e('Active uniquement en cas de problème. Peut impacter les performances.', 'block-traiteur'); ?>
                                        </p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Niveau de log', 'block-traiteur'); ?></th>
                                    <td>
                                        <select name="block_traiteur_log_level">
                                            <option value="error" <?php selected(get_option('block_traiteur_log_level', 'error'), 'error'); ?>>
                                                <?php _e('Erreurs seulement', 'block-traiteur'); ?>
                                            </option>
                                            <option value="warning" <?php selected(get_option('block_traiteur_log_level'), 'warning'); ?>>
                                                <?php _e('Erreurs + Avertissements', 'block-traiteur'); ?>
                                            </option>
                                            <option value="info" <?php selected(get_option('block_traiteur_log_level'), 'info'); ?>>
                                                <?php _e('Toutes les informations', 'block-traiteur'); ?>
                                            </option>
                                        </select>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        
                        <div class="settings-section danger-zone">
                            <h3><?php _e('Zone de danger', 'block-traiteur'); ?></h3>
                            <div class="danger-actions">
                                <div class="danger-action">
                                    <h4><?php _e('Réinitialiser les paramètres', 'block-traiteur'); ?></h4>
                                    <p><?php _e('Remet tous les paramètres aux valeurs par défaut', 'block-traiteur'); ?></p>
                                    <button type="button" class="button button-secondary reset-settings-btn">
                                        <?php _e('Réinitialiser', 'block-traiteur'); ?>
                                    </button>
                                </div>
                                
                                <div class="danger-action">
                                    <h4><?php _e('Vider le cache', 'block-traiteur'); ?></h4>
                                    <p><?php _e('Supprime tous les fichiers et données en cache', 'block-traiteur'); ?></p>
                                    <button type="button" class="button button-secondary clear-cache-btn">
                                        <?php _e('Vider le cache', 'block-traiteur'); ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                <?php endif; ?>
            </div>

            <!-- Boutons d'action -->
            <div class="settings-footer">
                <div class="footer-actions">
                    <button type="submit" class="button button-primary button-large">
                        <span class="dashicons dashicons-saved"></span>
                        <?php _e('Sauvegarder les modifications', 'block-traiteur'); ?>
                    </button>
                    
                    <button type="button" class="button button-secondary preview-btn">
                        <span class="dashicons dashicons-visibility"></span>
                        <?php _e('Aperçu du formulaire', 'block-traiteur'); ?>
                    </button>
                </div>
                
                <div class="footer-info">
                    <p class="description">
                        <?php printf(
                            __('Version %s - Dernière sauvegarde : %s', 'block-traiteur'),
                            BLOCK_TRAITEUR_VERSION,
                            get_option('block_traiteur_last_settings_save', __('Jamais', 'block-traiteur'))
                        ); ?>
                    </p>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Scripts JavaScript pour les interactions -->
<script type="text/javascript">
jQuery(document).ready(function($) {
    // Gestion de l'upload de logo
    $('.upload-logo-btn').on('click', function(e) {
        e.preventDefault();
        
        var frame = wp.media({
            title: '<?php _e("Choisir un logo", "block-traiteur"); ?>',
            button: { text: '<?php _e("Utiliser ce logo", "block-traiteur"); ?>' },
            multiple: false
        });
        
        frame.on('select', function() {
            var attachment = frame.state().get('selection').first().toJSON();
            $('input[name="block_traiteur_company_logo"]').val(attachment.url);
            $('.logo-preview').html('<img src="' + attachment.url + '" style="max-width: 200px; max-height: 100px;" />');
            $('.remove-logo-btn').show();
        });
        
        frame.open();
    });
    
    // Suppression du logo
    $('.remove-logo-btn').on('click', function(e) {
        e.preventDefault();
        $('input[name="block_traiteur_company_logo"]').val('');
        $('.logo-preview').empty();
        $(this).hide();
    });
    
    // Afficher/masquer config SMTP
    $('input[name="block_traiteur_smtp_enabled"]').on('change', function() {
        if ($(this).is(':checked')) {
            $('#smtp-configuration').slideDown();
        } else {
            $('#smtp-configuration').slideUp();
        }
    });
    
    // Afficher/masquer config Google Calendar
    $('input[name="block_traiteur_google_calendar_enabled"]').on('change', function() {
        if ($(this).is(':checked')) {
            $('#google-calendar-config').slideDown();
        } else {
            $('#google-calendar-config').slideUp();
        }
    });
});
</script>