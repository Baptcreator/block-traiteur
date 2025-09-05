<?php
/**
 * Template du tableau de bord
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <!-- Cartes de statistiques -->
    <div class="block-dashboard-stats">
        <div class="block-stat-card">
            <div class="stat-icon">
                <span class="dashicons dashicons-chart-line"></span>
            </div>
            <div class="stat-content">
                <h3><?php echo esc_html($stats['quotes_this_month']); ?></h3>
                <p><?php _e('Devis ce mois', 'block-traiteur'); ?></p>
            </div>
        </div>
        
        <div class="block-stat-card">
            <div class="stat-icon">
                <span class="dashicons dashicons-clock"></span>
            </div>
            <div class="stat-content">
                <h3><?php echo esc_html($stats['pending_quotes']); ?></h3>
                <p><?php _e('Devis en attente', 'block-traiteur'); ?></p>
            </div>
        </div>
        
        <div class="block-stat-card">
            <div class="stat-icon">
                <span class="dashicons dashicons-money-alt"></span>
            </div>
            <div class="stat-content">
                <h3><?php echo number_format($stats['revenue_estimate'] ?? 0, 0, ',', ' '); ?> €</h3>
                <p><?php _e('CA estimé ce mois', 'block-traiteur'); ?></p>
            </div>
        </div>
        
        <div class="block-stat-card">
            <div class="stat-icon">
                <span class="dashicons dashicons-thumbs-up"></span>
            </div>
            <div class="stat-content">
                <h3><?php echo esc_html($stats['conversion_rate']); ?>%</h3>
                <p><?php _e('Taux de conversion', 'block-traiteur'); ?></p>
            </div>
        </div>
    </div>
    
    <div class="block-dashboard-content">
        <!-- Devis récents -->
        <div class="block-dashboard-section">
            <div class="section-header">
                <h2><?php _e('Devis récents', 'block-traiteur'); ?></h2>
                <a href="<?php echo admin_url('admin.php?page=block-traiteur-quotes'); ?>" class="button">
                    <?php _e('Voir tous les devis', 'block-traiteur'); ?>
                </a>
            </div>
            
            <div class="recent-quotes">
                <?php if (!empty($stats['recent_quotes'])): ?>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th><?php _e('N° Devis', 'block-traiteur'); ?></th>
                                <th><?php _e('Client', 'block-traiteur'); ?></th>
                                <th><?php _e('Service', 'block-traiteur'); ?></th>
                                <th><?php _e('Montant', 'block-traiteur'); ?></th>
                                <th><?php _e('Statut', 'block-traiteur'); ?></th>
                                <th><?php _e('Date', 'block-traiteur'); ?></th>
                                <th><?php _e('Actions', 'block-traiteur'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($stats['recent_quotes'] as $quote): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo esc_html($quote->quote_number); ?></strong>
                                    </td>
                                    <td><?php echo esc_html($quote->customer_name); ?></td>
                                    <td>
                                        <span class="service-badge service-<?php echo esc_attr($quote->service_type); ?>">
                                            <?php echo ucfirst($quote->service_type); ?>
                                        </span>
                                    </td>
                                    <td><?php echo number_format($quote->total_price, 2); ?> € TTC</td>
                                    <td>
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
                                    <td><?php echo date_i18n('d/m/Y H:i', strtotime($quote->created_at)); ?></td>
                                    <td>
                                        <a href="<?php echo admin_url('admin.php?page=block-traiteur-quotes&action=view&id=' . $quote->id); ?>" 
                                           class="button button-small">
                                            <?php _e('Voir', 'block-traiteur'); ?>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="no-items">
                        <p><?php _e('Aucun devis récent.', 'block-traiteur'); ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Prochains événements -->
        <div class="block-dashboard-section">
            <div class="section-header">
                <h2><?php _e('Prochains événements', 'block-traiteur'); ?></h2>
            </div>
            
            <div class="upcoming-events">
                <?php if (!empty($stats['upcoming_events'])): ?>
                    <div class="events-grid">
                        <?php foreach ($stats['upcoming_events'] as $event): ?>
                            <div class="event-card">
                                <div class="event-date">
                                    <span class="day"><?php echo date_i18n('d', strtotime($event->event_date)); ?></span>
                                    <span class="month"><?php echo date_i18n('M', strtotime($event->event_date)); ?></span>
                                </div>
                                <div class="event-details">
                                    <h4><?php echo esc_html($event->customer_name); ?></h4>
                                    <p>
                                        <span class="service-type"><?php echo ucfirst($event->service_type); ?></span>
                                        • <?php echo esc_html($event->guest_count); ?> invités
                                    </p>
                                    <small><?php echo esc_html($event->quote_number); ?></small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="no-items">
                        <p><?php _e('Aucun événement à venir.', 'block-traiteur'); ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Actions rapides -->
        <div class="block-dashboard-section">
            <div class="section-header">
                <h2><?php _e('Actions rapides', 'block-traiteur'); ?></h2>
            </div>
            
            <div class="quick-actions">
                <a href="<?php echo admin_url('admin.php?page=block-traiteur-products&action=add'); ?>" 
                   class="quick-action-button">
                    <span class="dashicons dashicons-plus-alt"></span>
                    <?php _e('Ajouter un produit', 'block-traiteur'); ?>
                </a>
                
                <a href="<?php echo admin_url('admin.php?page=block-traiteur-beverages&action=add'); ?>" 
                   class="quick-action-button">
                    <span class="dashicons dashicons-plus-alt"></span>
                    <?php _e('Ajouter une boisson', 'block-traiteur'); ?>
                </a>
                
                <a href="<?php echo admin_url('admin.php?page=block-traiteur-availability'); ?>" 
                   class="quick-action-button">
                    <span class="dashicons dashicons-calendar-alt"></span>
                    <?php _e('Gérer les disponibilités', 'block-traiteur'); ?>
                </a>
                
                <a href="<?php echo admin_url('admin.php?page=block-traiteur-settings'); ?>" 
                   class="quick-action-button">
                    <span class="dashicons dashicons-admin-settings"></span>
                    <?php _e('Paramètres', 'block-traiteur'); ?>
                </a>
            </div>
        </div>
    </div>
</div>

<style>
.block-dashboard-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin: 20px 0;
}

.block-stat-card {
    background: white;
    border: 1px solid #ccd0d4;
    border-radius: 8px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 15px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.stat-icon {
    font-size: 24px;
    color: #0073aa;
    background: #f0f6fc;
    padding: 15px;
    border-radius: 50%;
}

.stat-content h3 {
    margin: 0;
    font-size: 24px;
    font-weight: 600;
    color: #1d2327;
}

.stat-content p {
    margin: 5px 0 0 0;
    color: #646970;
    font-size: 14px;
}

.block-dashboard-content {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 20px;
    margin-top: 20px;
}

.block-dashboard-section {
    background: white;
    border: 1px solid #ccd0d4;
    border-radius: 8px;
    overflow: hidden;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    border-bottom: 1px solid #f0f0f1;
    background: #f9f9f9;
}

.section-header h2 {
    margin: 0;
    font-size: 18px;
}

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

.events-grid {
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.event-card {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px;
    border: 1px solid #f0f0f1;
    border-radius: 8px;
    background: #fafafa;
}

.event-date {
    text-align: center;
    min-width: 50px;
}

.event-date .day {
    display: block;
    font-size: 20px;
    font-weight: 600;
    color: #0073aa;
}

.event-date .month {
    display: block;
    font-size: 12px;
    color: #646970;
    text-transform: uppercase;
}

.event-details h4 {
    margin: 0 0 5px 0;
    font-size: 14px;
}

.event-details p {
    margin: 0;
    font-size: 13px;
    color: #646970;
}

.quick-actions {
    padding: 20px;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
}

.quick-action-button {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 15px;
    border: 1px solid #0073aa;
    border-radius: 8px;
    background: white;
    color: #0073aa;
    text-decoration: none;
    transition: all 0.2s;
}

.quick-action-button:hover {
    background: #0073aa;
    color: white;
}

.no-items {
    padding: 40px 20px;
    text-align: center;
    color: #646970;
}

@media (max-width: 768px) {
    .block-dashboard-content {
        grid-template-columns: 1fr;
    }
    
    .block-dashboard-stats {
        grid-template-columns: 1fr;
    }
}
</style>