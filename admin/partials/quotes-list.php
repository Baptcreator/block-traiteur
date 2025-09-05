<?php
/**
 * Template de la liste des devis - Block Traiteur
 *
 * @package Block_Traiteur
 * @subpackage Admin/Partials
 * @since 1.0.0
 */

// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit;
}

// Récupérer les paramètres de filtrage
$current_status = isset($_GET['status']) ? sanitize_key($_GET['status']) : 'all';
$current_service = isset($_GET['service']) ? sanitize_key($_GET['service']) : 'all';
$search_query = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
$per_page = 20;
$current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;

// Récupérer les statistiques des devis
global $wpdb;
$quotes_table = $wpdb->prefix . 'block_quotes';

$stats = array(
    'total' => $wpdb->get_var("SELECT COUNT(*) FROM {$quotes_table}"),
    'pending' => $wpdb->get_var("SELECT COUNT(*) FROM {$quotes_table} WHERE status = 'pending'"),
    'approved' => $wpdb->get_var("SELECT COUNT(*) FROM {$quotes_table} WHERE status = 'approved'"),
    'rejected' => $wpdb->get_var("SELECT COUNT(*) FROM {$quotes_table} WHERE status = 'rejected'"),
    'expired' => $wpdb->get_var("SELECT COUNT(*) FROM {$quotes_table} WHERE status = 'expired'")
);

// Construire la requête de récupération des devis
$where_conditions = array('1=1');
$query_params = array();

if ($current_status !== 'all') {
    $where_conditions[] = 'status = %s';
    $query_params[] = $current_status;
}

if ($current_service !== 'all') {
    $where_conditions[] = 'service_type = %s';
    $query_params[] = $current_service;
}

if (!empty($search_query)) {
    $where_conditions[] = '(customer_name LIKE %s OR customer_email LIKE %s OR quote_number LIKE %s)';
    $search_term = '%' . $wpdb->esc_like($search_query) . '%';
    $query_params[] = $search_term;
    $query_params[] = $search_term;
    $query_params[] = $search_term;
}

$where_clause = implode(' AND ', $where_conditions);
$offset = ($current_page - 1) * $per_page;

// Récupérer le nombre total d'éléments
$total_query = "SELECT COUNT(*) FROM {$quotes_table} WHERE {$where_clause}";
if (!empty($query_params)) {
    $total_items = $wpdb->get_var($wpdb->prepare($total_query, $query_params));
} else {
    $total_items = $wpdb->get_var($total_query);
}

// Récupérer les devis
$quotes_query = "
    SELECT * FROM {$quotes_table} 
    WHERE {$where_clause} 
    ORDER BY created_at DESC 
    LIMIT %d OFFSET %d
";

$final_params = array_merge($query_params, array($per_page, $offset));
$quotes = $wpdb->get_results($wpdb->prepare($quotes_query, $final_params));
?>

<div class="wrap block-traiteur-quotes">
    <!-- En-tête de la page -->
    <div class="quotes-header">
        <div class="header-content">
            <h1 class="wp-heading-inline">
                <span class="dashicons dashicons-clipboard"></span>
                <?php _e('Gestion des Devis', 'block-traiteur'); ?>
            </h1>
            
            <div class="header-actions">
                <button type="button" class="page-title-action export-quotes-btn">
                    <span class="dashicons dashicons-download"></span>
                    <?php _e('Exporter', 'block-traiteur'); ?>
                </button>
                <button type="button" class="page-title-action sync-calendar-btn">
                    <span class="dashicons dashicons-update"></span>
                    <?php _e('Sync Calendrier', 'block-traiteur'); ?>
                </button>
            </div>
        </div>
        
        <!-- Statistiques rapides -->
        <div class="quotes-stats">
            <div class="stat-card total">
                <div class="stat-number"><?php echo number_format($stats['total']); ?></div>
                <div class="stat-label"><?php _e('Total Devis', 'block-traiteur'); ?></div>
            </div>
            <div class="stat-card pending">
                <div class="stat-number"><?php echo number_format($stats['pending']); ?></div>
                <div class="stat-label"><?php _e('En Attente', 'block-traiteur'); ?></div>
            </div>
            <div class="stat-card approved">
                <div class="stat-number"><?php echo number_format($stats['approved']); ?></div>
                <div class="stat-label"><?php _e('Approuvés', 'block-traiteur'); ?></div>
            </div>
            <div class="stat-card rejected">
                <div class="stat-number"><?php echo number_format($stats['rejected']); ?></div>
                <div class="stat-label"><?php _e('Rejetés', 'block-traiteur'); ?></div>
            </div>
        </div>
    </div>

    <!-- Filtres et recherche -->
    <div class="quotes-filters">
        <div class="tablenav top">
            <div class="alignleft actions">
                <!-- Filtres par statut -->
                <select name="status-filter" id="status-filter">
                    <option value="all" <?php selected($current_status, 'all'); ?>>
                        <?php _e('Tous les statuts', 'block-traiteur'); ?>
                    </option>
                    <option value="pending" <?php selected($current_status, 'pending'); ?>>
                        <?php _e('En attente', 'block-traiteur'); ?>
                    </option>
                    <option value="approved" <?php selected($current_status, 'approved'); ?>>
                        <?php _e('Approuvés', 'block-traiteur'); ?>
                    </option>
                    <option value="rejected" <?php selected($current_status, 'rejected'); ?>>
                        <?php _e('Rejetés', 'block-traiteur'); ?>
                    </option>
                    <option value="expired" <?php selected($current_status, 'expired'); ?>>
                        <?php _e('Expirés', 'block-traiteur'); ?>
                    </option>
                </select>
                
                <!-- Filtres par service -->
                <select name="service-filter" id="service-filter">
                    <option value="all" <?php selected($current_service, 'all'); ?>>
                        <?php _e('Tous les services', 'block-traiteur'); ?>
                    </option>
                    <option value="restaurant" <?php selected($current_service, 'restaurant'); ?>>
                        <?php _e('Restaurant', 'block-traiteur'); ?>
                    </option>
                    <option value="remorque" <?php selected($current_service, 'remorque'); ?>>
                        <?php _e('Remorque', 'block-traiteur'); ?>
                    </option>
                </select>
                
                <button type="button" class="button apply-filters-btn">
                    <?php _e('Filtrer', 'block-traiteur'); ?>
                </button>
                
                <!-- Actions groupées -->
                <select name="bulk-action" id="bulk-action-top">
                    <option value="-1"><?php _e('Actions groupées', 'block-traiteur'); ?></option>
                    <option value="approve"><?php _e('Approuver', 'block-traiteur'); ?></option>
                    <option value="reject"><?php _e('Rejeter', 'block-traiteur'); ?></option>
                    <option value="delete"><?php _e('Supprimer', 'block-traiteur'); ?></option>
                    <option value="export"><?php _e('Exporter', 'block-traiteur'); ?></option>
                </select>
                <button type="button" class="button apply-bulk-action-btn">
                    <?php _e('Appliquer', 'block-traiteur'); ?>
                </button>
            </div>
            
            <!-- Recherche -->
            <div class="alignright">
                <form method="get" class="search-form">
                    <input type="hidden" name="page" value="block-traiteur-quotes" />
                    <input type="hidden" name="status" value="<?php echo esc_attr($current_status); ?>" />
                    <input type="hidden" name="service" value="<?php echo esc_attr($current_service); ?>" />
                    <input type="search" name="s" value="<?php echo esc_attr($search_query); ?>" 
                           placeholder="<?php _e('Rechercher un devis...', 'block-traiteur'); ?>" />
                    <button type="submit" class="button">
                        <?php _e('Rechercher', 'block-traiteur'); ?>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Liste des devis -->
    <div class="quotes-table-container">
        <?php if (empty($quotes)): ?>
            <div class="no-quotes-found">
                <div class="no-quotes-icon">
                    <span class="dashicons dashicons-clipboard"></span>
                </div>
                <h3><?php _e('Aucun devis trouvé', 'block-traiteur'); ?></h3>
                <p><?php _e('Aucun devis ne correspond à vos critères de recherche.', 'block-traiteur'); ?></p>
                <?php if (!empty($search_query) || $current_status !== 'all' || $current_service !== 'all'): ?>
                    <a href="<?php echo admin_url('admin.php?page=block-traiteur-quotes'); ?>" class="button">
                        <?php _e('Voir tous les devis', 'block-traiteur'); ?>
                    </a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <table class="wp-list-table widefat fixed striped quotes-list">
                <thead>
                    <tr>
                        <td class="manage-column column-cb check-column">
                            <input type="checkbox" id="select-all-quotes" />
                        </td>
                        <th scope="col" class="manage-column column-quote-number">
                            <a href="#" class="sortable-column" data-sort="quote_number">
                                <?php _e('N° Devis', 'block-traiteur'); ?>
                                <span class="sorting-indicator"></span>
                            </a>
                        </th>
                        <th scope="col" class="manage-column column-customer">
                            <?php _e('Client', 'block-traiteur'); ?>
                        </th>
                        <th scope="col" class="manage-column column-service">
                            <?php _e('Service', 'block-traiteur'); ?>
                        </th>
                        <th scope="col" class="manage-column column-event-date">
                            <a href="#" class="sortable-column" data-sort="event_date">
                                <?php _e('Date Événement', 'block-traiteur'); ?>
                                <span class="sorting-indicator"></span>
                            </a>
                        </th>
                        <th scope="col" class="manage-column column-guests">
                            <?php _e('Invités', 'block-traiteur'); ?>
                        </th>
                        <th scope="col" class="manage-column column-total">
                            <a href="#" class="sortable-column" data-sort="total_price">
                                <?php _e('Montant', 'block-traiteur'); ?>
                                <span class="sorting-indicator"></span>
                            </a>
                        </th>
                        <th scope="col" class="manage-column column-status">
                            <?php _e('Statut', 'block-traiteur'); ?>
                        </th>
                        <th scope="col" class="manage-column column-created">
                            <a href="#" class="sortable-column" data-sort="created_at">
                                <?php _e('Créé le', 'block-traiteur'); ?>
                                <span class="sorting-indicator"></span>
                            </a>
                        </th>
                        <th scope="col" class="manage-column column-actions">
                            <?php _e('Actions', 'block-traiteur'); ?>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($quotes as $quote): ?>
                        <tr class="quote-row" data-quote-id="<?php echo esc_attr($quote->id); ?>">
                            <th scope="row" class="check-column">
                                <input type="checkbox" name="quote_ids[]" value="<?php echo esc_attr($quote->id); ?>" />
                            </th>
                            
                            <td class="column-quote-number">
                                <strong>
                                    <a href="#" class="quote-details-link" data-quote-id="<?php echo esc_attr($quote->id); ?>">
                                        <?php echo esc_html($quote->quote_number); ?>
                                    </a>
                                </strong>
                            </td>
                            
                            <td class="column-customer">
                                <div class="customer-info">
                                    <strong><?php echo esc_html($quote->customer_name); ?></strong>
                                    <br>
                                    <a href="mailto:<?php echo esc_attr($quote->customer_email); ?>">
                                        <?php echo esc_html($quote->customer_email); ?>
                                    </a>
                                    <?php if (!empty($quote->customer_phone)): ?>
                                        <br>
                                        <a href="tel:<?php echo esc_attr($quote->customer_phone); ?>">
                                            <?php echo esc_html($quote->customer_phone); ?>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                            
                            <td class="column-service">
                                <div class="service-badge <?php echo esc_attr($quote->service_type); ?>">
                                    <span class="service-icon">
                                        <?php if ($quote->service_type === 'restaurant'): ?>
                                            🏢
                                        <?php else: ?>
                                            🚛
                                        <?php endif; ?>
                                    </span>
                                    <span class="service-name">
                                        <?php echo $quote->service_type === 'restaurant' ? __('Restaurant', 'block-traiteur') : __('Remorque', 'block-traiteur'); ?>
                                    </span>
                                </div>
                            </td>
                            
                            <td class="column-event-date">
                                <?php
                                $event_date = new DateTime($quote->event_date);
                                $now = new DateTime();
                                $is_past = $event_date < $now;
                                ?>
                                <div class="event-date <?php echo $is_past ? 'past-date' : 'future-date'; ?>">
                                    <strong><?php echo $event_date->format('d/m/Y'); ?></strong>
                                    <br>
                                    <span class="event-time"><?php echo $event_date->format('H:i'); ?></span>
                                    <?php if ($is_past): ?>
                                        <br><small class="past-indicator"><?php _e('Passé', 'block-traiteur'); ?></small>
                                    <?php endif; ?>
                                </div>
                            </td>
                            
                            <td class="column-guests">
                                <div class="guests-info">
                                    <strong><?php echo number_format($quote->guest_count); ?></strong>
                                    <small><?php _e('pers.', 'block-traiteur'); ?></small>
                                    <?php if (!empty($quote->duration)): ?>
                                        <br>
                                        <span class="duration"><?php echo esc_html($quote->duration); ?>h</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            
                            <td class="column-total">
                                <div class="price-display">
                                    <strong class="total-price">
                                        <?php echo number_format($quote->total_price, 2, ',', ' '); ?> €
                                    </strong>
                                    <small>TTC</small>
                                </div>
                            </td>
                            
                            <td class="column-status">
                                <?php
                                $status_classes = array(
                                    'pending' => 'status-pending',
                                    'approved' => 'status-approved',
                                    'rejected' => 'status-rejected',
                                    'expired' => 'status-expired'
                                );
                                
                                $status_labels = array(
                                    'pending' => __('En attente', 'block-traiteur'),
                                    'approved' => __('Approuvé', 'block-traiteur'),
                                    'rejected' => __('Rejeté', 'block-traiteur'),
                                    'expired' => __('Expiré', 'block-traiteur')
                                );
                                ?>
                                <span class="status-badge <?php echo esc_attr($status_classes[$quote->status] ?? ''); ?>">
                                    <?php echo esc_html($status_labels[$quote->status] ?? $quote->status); ?>
                                </span>
                            </td>
                            
                            <td class="column-created">
                                <?php
                                $created_date = new DateTime($quote->created_at);
                                echo $created_date->format('d/m/Y H:i');
                                ?>
                            </td>
                            
                            <td class="column-actions">
                                <div class="row-actions">
                                    <span class="view">
                                        <a href="#" class="quote-details-link" data-quote-id="<?php echo esc_attr($quote->id); ?>">
                                            <?php _e('Voir', 'block-traiteur'); ?>
                                        </a> |
                                    </span>
                                    
                                    <?php if ($quote->status === 'pending'): ?>
                                        <span class="approve">
                                            <a href="#" class="approve-quote-link" data-quote-id="<?php echo esc_attr($quote->id); ?>">
                                                <?php _e('Approuver', 'block-traiteur'); ?>
                                            </a> |
                                        </span>
                                        <span class="reject">
                                            <a href="#" class="reject-quote-link" data-quote-id="<?php echo esc_attr($quote->id); ?>">
                                                <?php _e('Rejeter', 'block-traiteur'); ?>
                                            </a> |
                                        </span>
                                    <?php endif; ?>
                                    
                                    <span class="edit">
                                        <a href="#" class="edit-quote-link" data-quote-id="<?php echo esc_attr($quote->id); ?>">
                                            <?php _e('Modifier', 'block-traiteur'); ?>
                                        </a> |
                                    </span>
                                    
                                    <span class="pdf">
                                        <a href="#" class="download-pdf-link" data-quote-id="<?php echo esc_attr($quote->id); ?>" target="_blank">
                                            <?php _e('PDF', 'block-traiteur'); ?>
                                        </a> |
                                    </span>
                                    
                                    <span class="email">
                                        <a href="#" class="resend-email-link" data-quote-id="<?php echo esc_attr($quote->id); ?>">
                                            <?php _e('Renvoyer', 'block-traiteur'); ?>
                                        </a> |
                                    </span>
                                    
                                    <span class="delete">
                                        <a href="#" class="delete-quote-link" data-quote-id="<?php echo esc_attr($quote->id); ?>">
                                            <?php _e('Supprimer', 'block-traiteur'); ?>
                                        </a>
                                    </span>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if ($total_items > $per_page): ?>
        <div class="tablenav bottom">
            <div class="tablenav-pages">
                <?php
                $total_pages = ceil($total_items / $per_page);
                $page_links = paginate_links(array(
                    'base' => add_query_arg(array(
                        'paged' => '%#%',
                        'status' => $current_status,
                        'service' => $current_service,
                        's' => $search_query
                    )),
                    'format' => '',
                    'prev_text' => '&laquo;',
                    'next_text' => '&raquo;',
                    'total' => $total_pages,
                    'current' => $current_page,
                    'show_all' => false,
                    'end_size' => 1,
                    'mid_size' => 2,
                    'type' => 'list'
                ));
                echo $page_links;
                ?>
                
                <span class="displaying-num">
                    <?php
                    printf(
                        _n('%s élément', '%s éléments', $total_items, 'block-traiteur'),
                        number_format_i18n($total_items)
                    );
                    ?>
                </span>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Modal pour les détails du devis -->
<div id="quote-details-modal" class="quote-modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3><?php _e('Détails du devis', 'block-traiteur'); ?></h3>
            <button type="button" class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <div id="quote-details-content">
                <!-- Contenu chargé via AJAX -->
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="button button-secondary modal-close">
                <?php _e('Fermer', 'block-traiteur'); ?>
            </button>
            <button type="button" class="button button-primary download-pdf-btn">
                <?php _e('Télécharger PDF', 'block-traiteur'); ?>
            </button>
        </div>
    </div>
</div>

<!-- Scripts JavaScript -->
<script type="text/javascript">
jQuery(document).ready(function($) {
    // Gestion de la sélection multiple
    $('#select-all-quotes').on('change', function() {
        $('input[name="quote_ids[]"]').prop('checked', $(this).is(':checked'));
    });
    
    // Application des filtres
    $('.apply-filters-btn').on('click', function() {
        var status = $('#status-filter').val();
        var service = $('#service-filter').val();
        var url = '<?php echo admin_url('admin.php?page=block-traiteur-quotes'); ?>';
        
        var params = [];
        if (status !== 'all') params.push('status=' + status);
        if (service !== 'all') params.push('service=' + service);
        
        if (params.length > 0) {
            url += '&' + params.join('&');
        }
        
        window.location.href = url;
    });
    
    // Affichage des détails du devis
    $('.quote-details-link').on('click', function(e) {
        e.preventDefault();
        var quoteId = $(this).data('quote-id');
        
        // Charger les détails via AJAX
        $.post(ajaxurl, {
            action: 'block_traiteur_get_quote_details',
            quote_id: quoteId,
            nonce: '<?php echo wp_create_nonce('block_traiteur_admin'); ?>'
        }, function(response) {
            if (response.success) {
                $('#quote-details-content').html(response.data.html);
                $('#quote-details-modal').fadeIn();
            } else {
                alert(response.data.message || 'Erreur lors du chargement des détails');
            }
        });
    });
    
    // Fermeture du modal
    $('.modal-close').on('click', function() {
        $('#quote-details-modal').fadeOut();
    });
    
    // Actions rapides sur les devis
    $('.approve-quote-link').on('click', function(e) {
        e.preventDefault();
        var quoteId = $(this).data('quote-id');
        
        if (confirm('<?php _e("Approuver ce devis ?", "block-traiteur"); ?>')) {
            updateQuoteStatus(quoteId, 'approved');
        }
    });
    
    $('.reject-quote-link').on('click', function(e) {
        e.preventDefault();
        var quoteId = $(this).data('quote-id');
        
        if (confirm('<?php _e("Rejeter ce devis ?", "block-traiteur"); ?>')) {
            updateQuoteStatus(quoteId, 'rejected');
        }
    });
    
    $('.delete-quote-link').on('click', function(e) {
        e.preventDefault();
        var quoteId = $(this).data('quote-id');
        
        if (confirm('<?php _e("Supprimer définitivement ce devis ?", "block-traiteur"); ?>')) {
            deleteQuote(quoteId);
        }
    });
    
    // Fonction pour mettre à jour le statut d'un devis
    function updateQuoteStatus(quoteId, status) {
        $.post(ajaxurl, {
            action: 'block_traiteur_update_quote_status',
            quote_id: quoteId,
            status: status,
            nonce: '<?php echo wp_create_nonce('block_traiteur_admin'); ?>'
        }, function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert(response.data.message || 'Erreur lors de la mise à jour');
            }
        });
    }
    
    // Fonction pour supprimer un devis
    function deleteQuote(quoteId) {
        $.post(ajaxurl, {
            action: 'block_traiteur_delete_quote',
            quote_id: quoteId,
            nonce: '<?php echo wp_create_nonce('block_traiteur_admin'); ?>'
        }, function(response) {
            if (response.success) {
                $('tr[data-quote-id="' + quoteId + '"]').fadeOut(function() {
                    $(this).remove();
                });
            } else {
                alert(response.data.message || 'Erreur lors de la suppression');
            }
        });
    }
});
</script>