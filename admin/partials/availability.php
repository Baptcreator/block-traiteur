<?php
/**
 * Vue d'administration pour la gestion des disponibilités
 * 
 * @package Block_Traiteur
 * @since 1.0.0
 */

// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit;
}

// Récupérer les paramètres de disponibilité
$google_calendar_enabled = get_option('block_traiteur_google_calendar_enabled', false);
$calendar_id = get_option('block_traiteur_google_calendar_id', '');
$blocked_dates = get_option('block_traiteur_blocked_dates', array());
$working_hours = get_option('block_traiteur_working_hours', array(
    'monday' => array('start' => '09:00', 'end' => '18:00', 'enabled' => true),
    'tuesday' => array('start' => '09:00', 'end' => '18:00', 'enabled' => true),
    'wednesday' => array('start' => '09:00', 'end' => '18:00', 'enabled' => true),
    'thursday' => array('start' => '09:00', 'end' => '18:00', 'enabled' => true),
    'friday' => array('start' => '09:00', 'end' => '18:00', 'enabled' => true),
    'saturday' => array('start' => '10:00', 'end' => '16:00', 'enabled' => true),
    'sunday' => array('start' => '10:00', 'end' => '16:00', 'enabled' => false)
));

$days_labels = array(
    'monday' => __('Lundi', 'block-traiteur'),
    'tuesday' => __('Mardi', 'block-traiteur'),
    'wednesday' => __('Mercredi', 'block-traiteur'),
    'thursday' => __('Jeudi', 'block-traiteur'),
    'friday' => __('Vendredi', 'block-traiteur'),
    'saturday' => __('Samedi', 'block-traiteur'),
    'sunday' => __('Dimanche', 'block-traiteur')
);
?>

<div class="wrap">
    <h1><?php _e('Gestion des Disponibilités', 'block-traiteur'); ?></h1>

    <?php if (isset($_GET['message'])): ?>
        <div class="notice notice-success is-dismissible">
            <p><?php _e('Paramètres de disponibilité sauvegardés avec succès.', 'block-traiteur'); ?></p>
        </div>
    <?php endif; ?>

    <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
        <input type="hidden" name="action" value="save_availability_settings">
        <?php wp_nonce_field('block_traiteur_availability_settings', 'availability_nonce'); ?>

        <!-- Configuration Google Calendar -->
        <div class="postbox">
            <h2 class="hndle"><?php _e('Intégration Google Calendar', 'block-traiteur'); ?></h2>
            <div class="inside">
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="google_calendar_enabled"><?php _e('Activer Google Calendar', 'block-traiteur'); ?></label>
                        </th>
                        <td>
                            <input type="checkbox" name="google_calendar_enabled" id="google_calendar_enabled" 
                                   value="1" <?php checked($google_calendar_enabled); ?>>
                            <p class="description">
                                <?php _e('Synchroniser avec Google Calendar pour vérifier automatiquement les disponibilités.', 'block-traiteur'); ?>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="google_calendar_id"><?php _e('ID du Calendrier', 'block-traiteur'); ?></label>
                        </th>
                        <td>
                            <input type="text" name="google_calendar_id" id="google_calendar_id" 
                                   value="<?php echo esc_attr($calendar_id); ?>" class="regular-text">
                            <p class="description">
                                <?php _e('L\'ID de votre calendrier Google (ex: votre-email@gmail.com)', 'block-traiteur'); ?>
                            </p>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Horaires de travail -->
        <div class="postbox">
            <h2 class="hndle"><?php _e('Horaires de Travail', 'block-traiteur'); ?></h2>
            <div class="inside">
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('Jour', 'block-traiteur'); ?></th>
                            <th><?php _e('Actif', 'block-traiteur'); ?></th>
                            <th><?php _e('Heure de début', 'block-traiteur'); ?></th>
                            <th><?php _e('Heure de fin', 'block-traiteur'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($days_labels as $day => $label): ?>
                            <tr>
                                <td><strong><?php echo esc_html($label); ?></strong></td>
                                <td>
                                    <input type="checkbox" 
                                           name="working_hours[<?php echo $day; ?>][enabled]" 
                                           value="1" 
                                           <?php checked(isset($working_hours[$day]['enabled']) ? $working_hours[$day]['enabled'] : false); ?>>
                                </td>
                                <td>
                                    <input type="time" 
                                           name="working_hours[<?php echo $day; ?>][start]" 
                                           value="<?php echo esc_attr(isset($working_hours[$day]['start']) ? $working_hours[$day]['start'] : '09:00'); ?>"
                                           class="small-text">
                                </td>
                                <td>
                                    <input type="time" 
                                           name="working_hours[<?php echo $day; ?>][end]" 
                                           value="<?php echo esc_attr(isset($working_hours[$day]['end']) ? $working_hours[$day]['end'] : '18:00'); ?>"
                                           class="small-text">
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <p class="description">
                    <?php _e('Définissez vos horaires de travail pour chaque jour de la semaine.', 'block-traiteur'); ?>
                </p>
            </div>
        </div>

        <!-- Dates bloquées -->
        <div class="postbox">
            <h2 class="hndle"><?php _e('Dates Bloquées', 'block-traiteur'); ?></h2>
            <div class="inside">
                <div id="blocked-dates-manager">
                    <div class="blocked-dates-controls">
                        <input type="date" id="new-blocked-date" min="<?php echo date('Y-m-d'); ?>">
                        <input type="text" id="blocked-date-reason" placeholder="<?php esc_attr_e('Raison (optionnelle)', 'block-traiteur'); ?>" class="regular-text">
                        <button type="button" id="add-blocked-date" class="button">
                            <?php _e('Ajouter', 'block-traiteur'); ?>
                        </button>
                    </div>
                    
                    <div class="blocked-dates-list">
                        <table class="wp-list-table widefat fixed striped" id="blocked-dates-table">
                            <thead>
                                <tr>
                                    <th><?php _e('Date', 'block-traiteur'); ?></th>
                                    <th><?php _e('Raison', 'block-traiteur'); ?></th>
                                    <th><?php _e('Actions', 'block-traiteur'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($blocked_dates)): ?>
                                    <tr id="no-blocked-dates">
                                        <td colspan="3"><?php _e('Aucune date bloquée.', 'block-traiteur'); ?></td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($blocked_dates as $date => $reason): ?>
                                        <tr data-date="<?php echo esc_attr($date); ?>">
                                            <td><?php echo esc_html(date_i18n('j F Y', strtotime($date))); ?></td>
                                            <td><?php echo esc_html($reason ?: '-'); ?></td>
                                            <td>
                                                <button type="button" class="button button-small remove-blocked-date" 
                                                        data-date="<?php echo esc_attr($date); ?>">
                                                    <?php _e('Supprimer', 'block-traiteur'); ?>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Champ caché pour les dates bloquées -->
                    <input type="hidden" name="blocked_dates" id="blocked-dates-input" 
                           value="<?php echo esc_attr(json_encode($blocked_dates)); ?>">
                </div>
                
                <p class="description">
                    <?php _e('Ajoutez des dates où vos services ne sont pas disponibles (vacances, événements privés, etc.).', 'block-traiteur'); ?>
                </p>
            </div>
        </div>

        <!-- Paramètres avancés -->
        <div class="postbox">
            <h2 class="hndle"><?php _e('Paramètres Avancés', 'block-traiteur'); ?></h2>
            <div class="inside">
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="advance_booking_days"><?php _e('Délai minimum de réservation', 'block-traiteur'); ?></label>
                        </th>
                        <td>
                            <input type="number" name="advance_booking_days" id="advance_booking_days" 
                                   value="<?php echo esc_attr(get_option('block_traiteur_advance_booking_days', 7)); ?>" 
                                   min="1" max="365" class="small-text"> <?php _e('jours', 'block-traiteur'); ?>
                            <p class="description">
                                <?php _e('Nombre minimum de jours avant l\'événement pour accepter une réservation.', 'block-traiteur'); ?>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="max_booking_days"><?php _e('Délai maximum de réservation', 'block-traiteur'); ?></label>
                        </th>
                        <td>
                            <input type="number" name="max_booking_days" id="max_booking_days" 
                                   value="<?php echo esc_attr(get_option('block_traiteur_max_booking_days', 365)); ?>" 
                                   min="1" max="1095" class="small-text"> <?php _e('jours', 'block-traiteur'); ?>
                            <p class="description">
                                <?php _e('Nombre maximum de jours à l\'avance pour accepter une réservation.', 'block-traiteur'); ?>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="simultaneous_events"><?php _e('Événements simultanés', 'block-traiteur'); ?></label>
                        </th>
                        <td>
                            <input type="checkbox" name="simultaneous_events" id="simultaneous_events" 
                                   value="1" <?php checked(get_option('block_traiteur_simultaneous_events', false)); ?>>
                            <p class="description">
                                <?php _e('Autoriser plusieurs événements le même jour (si vous avez plusieurs équipes).', 'block-traiteur'); ?>
                            </p>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <?php submit_button(__('Sauvegarder les paramètres', 'block-traiteur')); ?>
    </form>
</div>

<style>
.blocked-dates-controls {
    margin-bottom: 20px;
    padding: 15px;
    background: #f9f9f9;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.blocked-dates-controls input,
.blocked-dates-controls button {
    margin-right: 10px;
    vertical-align: middle;
}

.blocked-dates-list {
    max-height: 400px;
    overflow-y: auto;
}

#blocked-dates-table tbody tr td {
    vertical-align: middle;
}

.postbox {
    margin-bottom: 20px;
}

.postbox .hndle {
    padding: 12px;
    font-size: 14px;
    line-height: 1.4;
}

.postbox .inside {
    padding: 12px;
}
</style>

<script>
jQuery(document).ready(function($) {
    var blockedDates = <?php echo json_encode($blocked_dates); ?>;

    // Ajouter une date bloquée
    $('#add-blocked-date').on('click', function() {
        var date = $('#new-blocked-date').val();
        var reason = $('#blocked-date-reason').val();

        if (!date) {
            alert('<?php _e('Veuillez sélectionner une date.', 'block-traiteur'); ?>');
            return;
        }

        if (blockedDates.hasOwnProperty(date)) {
            alert('<?php _e('Cette date est déjà bloquée.', 'block-traiteur'); ?>');
            return;
        }

        blockedDates[date] = reason;
        updateBlockedDatesDisplay();
        updateBlockedDatesInput();

        // Réinitialiser les champs
        $('#new-blocked-date').val('');
        $('#blocked-date-reason').val('');
    });

    // Supprimer une date bloquée
    $(document).on('click', '.remove-blocked-date', function() {
        var date = $(this).data('date');
        delete blockedDates[date];
        updateBlockedDatesDisplay();
        updateBlockedDatesInput();
    });

    // Mettre à jour l'affichage des dates bloquées
    function updateBlockedDatesDisplay() {
        var tbody = $('#blocked-dates-table tbody');
        tbody.empty();

        if (Object.keys(blockedDates).length === 0) {
            tbody.append('<tr id="no-blocked-dates"><td colspan="3"><?php _e('Aucune date bloquée.', 'block-traiteur'); ?></td></tr>');
        } else {
            $.each(blockedDates, function(date, reason) {
                var dateFormatted = new Date(date).toLocaleDateString('fr-FR', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                });
                
                var row = '<tr data-date="' + date + '">' +
                         '<td>' + dateFormatted + '</td>' +
                         '<td>' + (reason || '-') + '</td>' +
                         '<td><button type="button" class="button button-small remove-blocked-date" data-date="' + date + '"><?php _e('Supprimer', 'block-traiteur'); ?></button></td>' +
                         '</tr>';
                tbody.append(row);
            });
        }
    }

    // Mettre à jour le champ caché
    function updateBlockedDatesInput() {
        $('#blocked-dates-input').val(JSON.stringify(blockedDates));
    }

    // Validation du formulaire
    $('form').on('submit', function() {
        // Vérifier les horaires de travail
        var hasWorkingDay = false;
        $('.working-hours input[type="checkbox"]').each(function() {
            if ($(this).is(':checked')) {
                hasWorkingDay = true;
                return false;
            }
        });

        if (!hasWorkingDay) {
            alert('<?php _e('Vous devez définir au moins un jour de travail.', 'block-traiteur'); ?>');
            return false;
        }

        return true;
    });
});
</script>
