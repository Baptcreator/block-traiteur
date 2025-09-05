<?php
/**
 * Template de la liste des devis
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php _e('Devis', 'block-traiteur'); ?></h1>
    
    <!-- Filtres et recherche -->
    <div class="tablenav top">
        <div class="alignleft actions">
            <form method="get" id="quotes-filter">
                <input type="hidden" name="page" value="block-traiteur-quotes">
                
                <select name="status">
                    <option value=""><?php _e('Tous les statuts', 'block-traiteur'); ?></option>
                    <option value="draft" <?php selected($status_filter, 'draft'); ?>><?php _e('Brouillon', 'block-traiteur'); ?></option>
                    <option value="sent" <?php selected($status_filter, 'sent'); ?>><?php _e('Envoyé', 'block-traiteur'); ?></option>
                    <option value="accepted" <?php selected($status_filter, 'accepted'); ?>><?php _e('Accepté', 'block-traiteur'); ?></option>
                    <option value="declined" <?php selected($status_filter, 'declined'); ?>><?php _e('Refusé', 'block-traiteur'); ?></option>
                    <option value="expired" <?php selected($status_filter, 'expired'); ?>><?php _e('Expiré', 'block-traiteur'); ?></option>
                </select>
                
                <select name="service">
                    <option value=""><?php _e('Tous les services', 'block-traiteur'); ?></option>
                    <option value="restaurant" <?php selected($service_filter, 'restaurant'); ?>><?php _e('Restaurant', 'block-traiteur'); ?></option>
                    <option value="remorque" <?php selected($service_filter, 'remorque'); ?>><?php _e('Remorque', 'block-traiteur'); ?></option>
                </select>
                
                <?php submit_button(__('Filtrer', 'block-traiteur'), 'secondary', 'filter_action', false); ?>
            </form>
        </div>
        
        <div class="alignright actions">
            <form method="get" class="search-form">
                <input type="hidden" name="page" value="block-traiteur-quotes">
                <?php if ($status_filter): ?>
                    <input type="hidden" name="status" value="<?php echo esc_attr($status_filter); ?>">
                <?php endif; ?>
                <?php if ($service_filter): ?>
                    <input type="hidden" name="service" value="<?php echo esc_attr($service_filter); ?>">
                <?php endif; ?>
                
                <input type="search" name="s" value="<?php echo esc_attr($search); ?>" 
                       placeholder="<?php _e('Rechercher un devis...', 'block-traiteur'); ?>">
                <?php submit_button(__('Rechercher', 'block-traiteur'), 'secondary', 'search_submit', false); ?>
            </form>
        </div>
    </div>
    
    <!-- Tableau des devis -->
    <?php if (!empty($quotes)): ?>
        <table class="wp-list-table widefat fixed striped quotes">
            <thead>
                <tr>
                    <th scope="col" class="manage-column column-quote-number">
                        <?php _e('N° Devis', 'block-traiteur'); ?>
                    </th>
                    <th scope="col" class="manage-column column-customer">
                        <?php _e('Client', 'block-traiteur'); ?>
                    </th>
                    <th scope="col" class="manage-column column-service">
                        <?php _e('Service', 'block-traiteur'); ?>
                    </th>
                    <th scope="col" class="manage-column column-event-date">
                        <?php _e('Date événement', 'block-traiteur'); ?>
                    </th>
                    <th scope="col" class="manage-column column-guests">
                        <?php _e('Invités', 'block-traiteur'); ?>
                    </th>
                    <th scope="col" class="manage-column column-total">
                        <?php _e('Montant', 'block-traiteur'); ?>
                    </th>
                    <th scope="col" class="manage-column column-status">
                        <?php _e('Statut', 'block-traiteur'); ?>
                    </th>
                    <th scope="col" class="manage-column column-created">
                        <?php _e('Créé le', 'block-traiteur'); ?>
                    </th>
                    <th scope="col" class="manage-column column-actions">
                        <?php _e('Actions', 'block-traiteur'); ?>
                    </th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($quotes as $quote): ?>
                    <tr>
                        <td class="column-quote-number">
                            <strong>
                                <a href="<?php echo admin_url('admin.php?page=block-traiteur-quotes&action=view&id=' . $quote->id); ?>">
                                    <?php echo esc_html($quote->quote_number); ?>
                                </a>
                            </strong>
                        </td>
                        <td class="column-customer">
                            <strong><?php echo esc_html($quote->customer_name); ?></strong><br>
                            <a href="mailto:<?php echo esc_attr($quote->customer_email); ?>">
                                <?php echo esc_html($quote->customer_email); ?>
                            </a>
                            <?php if ($quote->customer_phone): ?>
                                <br><small><?php echo esc_html($quote->customer_phone); ?></small>
                            <?php endif; ?>
                        </td>
                        <td class="column-service">
                            <span class="service-badge service-<?php echo esc_attr($quote->service_type); ?>">
                                <?php echo ucfirst($quote->service_type); ?>
                            </span>
                        </td>
                        <td class="column-event-date">
                            <?php echo date_i18n('d/m/Y', strtotime($quote->event_date)); ?>
                            <br><small><?php echo esc_html($quote->event_duration); ?>h</small>
                        </td>
                        <td class="column-guests">
                            <?php echo esc_html($quote->guest_count); ?> pers.
                        </td>
                        <td class="column-total">
                            <strong><?php echo number_format($quote->total_price, 2); ?> € TTC</strong>
                        </td>
                        <td class="column-status">
                            <span class="status-badge status-<?php echo esc_attr($quote->status); ?>">
                                <?php 
                                $status_labels = array(
                                    'draft' => __('Brouillon', 'block-traiteur'),
                                    'sent' => __('Envoyé', 'block-traiteur'),
                                    'accepted' => __('Accepté', 'block-traiteur'),
                                    'declined' => __('Refusé', 'block-traiteur'),
                                    'expired' => __('Expiré', 'block-traiteur')
                                );
                                echo esc_html($status_labels[$quote->status]);
                                ?>
                            </span>
                        </td>
                        <td class="column-created">
                            <?php echo date_i18n('d/m/Y H:i', strtotime($quote->created_at)); ?>
                        </td>
                        <td class="column-actions">
                            <div class="row-actions">
                                <span class="view">
                                    <a href="<?php echo admin_url('admin.php?page=block-traiteur-quotes&action=view&id=' . $quote->id); ?>">
                                        <?php _e('Voir', 'block-traiteur'); ?>
                                    </a>
                                </span>
                                |
                                <span class="edit">
                                    <a href="<?php echo admin_url('admin.php?page=block-traiteur-quotes&action=edit&id=' . $quote->id); ?>">
                                        <?php _e('Modifier', 'block-traiteur'); ?>
                                    </a>
                                </span>
                                |
                                <span class="pdf">
                                    <a href="#" class="generate-pdf" data-quote-id="<?php echo $quote->id; ?>">
                                        <?php _e('PDF', 'block-traiteur'); ?>
                                    </a>
                                </span>
                                |
                                <span class="email">
                                    <a href="#" class="send-email" data-quote-id="<?php echo $quote->id; ?>">
                                        <?php _e('Email', 'block-traiteur'); ?>
                                    </a>
                                </span>
                                |
                                <span class="delete">
                                    <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=block-traiteur-quotes&action=delete&id=' . $quote->id), 'delete_quote_' . $quote->id); ?>" 
                                       onclick="return confirm('<?php _e('Êtes-vous sûr de vouloir supprimer ce devis ?', 'block-traiteur'); ?>')">
                                        <?php _e('Supprimer', 'block-traiteur'); ?>
                                    </a>
                                </span>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <div class="tablenav bottom">
                <div class="tablenav-pages">
                    <?php
                    $pagination_args = array(
                        'base' => add_query_arg('paged', '%#%'),
                        'format' => '',
                        'prev_text' => '&laquo;',
                        'next_text' => '&raquo;',
                        'total' => $total_pages,
                        'current' => $current_page
                    );
                    echo paginate_links($pagination_args);
                    ?>
                </div>
            </div>
        <?php endif; ?>
        
    <?php else: ?>
        <div class="no-quotes">
            <p><?php _e('Aucun devis trouvé.', 'block-traiteur'); ?></p>
            <?php if ($search || $status_filter || $service_filter): ?>
                <a href="<?php echo admin_url('admin.php?page=block-traiteur-quotes'); ?>" class="button">
                    <?php _e('Effacer les filtres', 'block-traiteur'); ?>
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Modal pour les actions rapides -->
<div id="quote-actions-modal" style="display: none;">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h3 id="modal-title"></h3>
        <div id="modal-body"></div>
        <div class="modal-actions">
            <button type="button" class="button" id="modal-cancel"><?php _e('Annuler', 'block-traiteur'); ?></button>
            <button type="button" class="button button-primary" id="modal-confirm"><?php _e('Confirmer', 'block-traiteur'); ?></button>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Génération PDF
    $('.generate-pdf').on('click', function(e) {
        e.preventDefault();
        
        var quoteId = $(this).data('quote-id');
        var $button = $(this);
        var originalText = $button.text();
        
        $button.text('<?php _e('Génération...', 'block-traiteur'); ?>');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'block_traiteur_generate_pdf',
                quote_id: quoteId,
                nonce: '<?php echo wp_create_nonce('block_traiteur_admin'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    window.open(response.data.pdf_url, '_blank');
                    alert(response.data.message);
                } else {
                    alert('Erreur: ' + response.data);
                }
            },
            error: function() {
                alert('<?php _e('Erreur lors de la génération du PDF', 'block-traiteur'); ?>');
            },
            complete: function() {
                $button.text(originalText);
            }
        });
    });
    
    // Envoi email
    $('.send-email').on('click', function(e) {
        e.preventDefault();
        
        var quoteId = $(this).data('quote-id');
        
        if (confirm('<?php _e('Envoyer l\'email de confirmation au client ?', 'block-traiteur'); ?>')) {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'block_traiteur_send_email',
                    quote_id: quoteId,
                    email_type: 'confirmation',
                    nonce: '<?php echo wp_create_nonce('block_traiteur_admin'); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        alert('<?php _e('Email envoyé avec succès', 'block-traiteur'); ?>');
                    } else {
                        alert('Erreur: ' + response.data);
                    }
                },
                error: function() {
                    alert('<?php _e('Erreur lors de l\'envoi de l\'email', 'block-traiteur'); ?>');
                }
            });
        }
    });
});
</script>

<style>
.service-badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 500;
    text-transform: uppercase;
}

.service-restaurant {
    background: #e7f3ff;
    color: #0073aa;
}

.service-remorque {
    background: #f0f6fc;
    color: #2271b1;
}

.status-badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 500;
}

.status-draft {
    background: #f0f0f1;
    color: #646970;
}

.status-sent {
    background: #fff3cd;
    color: #856404;
}

.status-accepted {
    background: #d1e7dd;
    color: #0f5132;
}

.status-declined {
    background: #f8d7da;
    color: #721c24;
}

.status-expired {
    background: #f0f0f1;
    color: #646970;
}

.no-quotes {
    padding: 40px;
    text-align: center;
    background: white;
    border: 1px solid #ccd0d4;
    margin-top: 20px;
}

.search-form {
    display: flex;
    gap: 5px;
    align-items: center;
}

.search-form input[type="search"] {
    width: 200px;
}

#quote-actions-modal {
    position: fixed;
    z-index: 100000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
}

.modal-content {
    background-color: #fefefe;
    margin: 15% auto;
    padding: 20px;
    border: 1px solid #888;
    width: 400px;
    border-radius: 8px;
}

.close {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}

.close:hover {
    color: black;
}

.modal-actions {
    margin-top: 20px;
    text-align: right;
}

.modal-actions .button {
    margin-left: 10px;
}
</style>