<?php
/**
 * Vue d'administration pour les rapports et statistiques
 * 
 * @package Block_Traiteur
 * @since 1.0.0
 */

// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;

// Récupération des paramètres de filtre
$period = isset($_GET['period']) ? sanitize_text_field($_GET['period']) : 'month';
$start_date = isset($_GET['start_date']) ? sanitize_text_field($_GET['start_date']) : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? sanitize_text_field($_GET['end_date']) : date('Y-m-t');

// Calcul des dates selon la période
switch ($period) {
    case 'week':
        $start_date = date('Y-m-d', strtotime('monday this week'));
        $end_date = date('Y-m-d', strtotime('sunday this week'));
        break;
    case 'month':
        $start_date = date('Y-m-01');
        $end_date = date('Y-m-t');
        break;
    case 'year':
        $start_date = date('Y-01-01');
        $end_date = date('Y-12-31');
        break;
}

// Requêtes pour les statistiques
$table_quotes = $wpdb->prefix . 'block_quotes';

// Statistiques générales
$total_quotes = $wpdb->get_var($wpdb->prepare("
    SELECT COUNT(*) FROM $table_quotes 
    WHERE created_at BETWEEN %s AND %s
", $start_date . ' 00:00:00', $end_date . ' 23:59:59'));

$confirmed_quotes = $wpdb->get_var($wpdb->prepare("
    SELECT COUNT(*) FROM $table_quotes 
    WHERE status = 'confirmed' AND created_at BETWEEN %s AND %s
", $start_date . ' 00:00:00', $end_date . ' 23:59:59'));

$total_revenue = $wpdb->get_var($wpdb->prepare("
    SELECT SUM(total_price) FROM $table_quotes 
    WHERE status = 'confirmed' AND created_at BETWEEN %s AND %s
", $start_date . ' 00:00:00', $end_date . ' 23:59:59')) ?: 0;

$avg_quote_value = $total_quotes > 0 ? $wpdb->get_var($wpdb->prepare("
    SELECT AVG(total_price) FROM $table_quotes 
    WHERE created_at BETWEEN %s AND %s
", $start_date . ' 00:00:00', $end_date . ' 23:59:59')) : 0;

// Taux de conversion
$conversion_rate = $total_quotes > 0 ? ($confirmed_quotes / $total_quotes) * 100 : 0;

// Répartition par service
$service_stats = $wpdb->get_results($wpdb->prepare("
    SELECT 
        service_type,
        COUNT(*) as count,
        SUM(total_price) as revenue,
        AVG(total_price) as avg_price
    FROM $table_quotes 
    WHERE created_at BETWEEN %s AND %s
    GROUP BY service_type
    ORDER BY count DESC
", $start_date . ' 00:00:00', $end_date . ' 23:59:59'));

// Évolution mensuelle (12 derniers mois)
$monthly_evolution = $wpdb->get_results("
    SELECT 
        DATE_FORMAT(created_at, '%Y-%m') as month,
        COUNT(*) as quotes_count,
        SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_count,
        SUM(CASE WHEN status = 'confirmed' THEN total_price ELSE 0 END) as revenue
    FROM $table_quotes 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY month DESC
");

// Répartition par statut
$status_stats = $wpdb->get_results($wpdb->prepare("
    SELECT 
        status,
        COUNT(*) as count,
        (COUNT(*) * 100.0 / (SELECT COUNT(*) FROM $table_quotes WHERE created_at BETWEEN %s AND %s)) as percentage
    FROM $table_quotes 
    WHERE created_at BETWEEN %s AND %s
    GROUP BY status
    ORDER BY count DESC
", $start_date . ' 00:00:00', $end_date . ' 23:59:59', $start_date . ' 00:00:00', $end_date . ' 23:59:59'));

// Top clients (par nombre de devis confirmés)
$top_clients = $wpdb->get_results($wpdb->prepare("
    SELECT 
        client_name,
        client_email,
        COUNT(*) as quotes_count,
        SUM(total_price) as total_spent
    FROM $table_quotes 
    WHERE status = 'confirmed' AND created_at BETWEEN %s AND %s
    GROUP BY client_email
    HAVING quotes_count > 1
    ORDER BY total_spent DESC
    LIMIT 10
", $start_date . ' 00:00:00', $end_date . ' 23:59:59'));
?>

<div class="wrap">
    <h1><?php _e('Rapports et Statistiques', 'block-traiteur'); ?></h1>

    <!-- Filtres de période -->
    <div class="reports-filters">
        <form method="get" class="filter-form">
            <input type="hidden" name="page" value="<?php echo esc_attr($_GET['page']); ?>">
            <input type="hidden" name="tab" value="reports">
            
            <label for="period"><?php _e('Période :', 'block-traiteur'); ?></label>
            <select name="period" id="period">
                <option value="week" <?php selected($period, 'week'); ?>><?php _e('Cette semaine', 'block-traiteur'); ?></option>
                <option value="month" <?php selected($period, 'month'); ?>><?php _e('Ce mois', 'block-traiteur'); ?></option>
                <option value="year" <?php selected($period, 'year'); ?>><?php _e('Cette année', 'block-traiteur'); ?></option>
                <option value="custom" <?php selected($period, 'custom'); ?>><?php _e('Personnalisée', 'block-traiteur'); ?></option>
            </select>

            <div id="custom-dates" style="<?php echo $period !== 'custom' ? 'display: none;' : ''; ?>">
                <label for="start_date"><?php _e('Du :', 'block-traiteur'); ?></label>
                <input type="date" name="start_date" id="start_date" value="<?php echo esc_attr($start_date); ?>">
                
                <label for="end_date"><?php _e('Au :', 'block-traiteur'); ?></label>
                <input type="date" name="end_date" id="end_date" value="<?php echo esc_attr($end_date); ?>">
            </div>

            <button type="submit" class="button"><?php _e('Appliquer', 'block-traiteur'); ?></button>
            <button type="button" class="button" id="export-report"><?php _e('Exporter', 'block-traiteur'); ?></button>
        </form>
    </div>

    <!-- Métriques principales -->
    <div class="reports-summary">
        <div class="summary-cards">
            <div class="summary-card">
                <h3><?php _e('Total Devis', 'block-traiteur'); ?></h3>
                <div class="metric-value"><?php echo number_format($total_quotes); ?></div>
                <div class="metric-label"><?php echo date_i18n('j F Y', strtotime($start_date)) . ' - ' . date_i18n('j F Y', strtotime($end_date)); ?></div>
            </div>

            <div class="summary-card confirmed">
                <h3><?php _e('Devis Confirmés', 'block-traiteur'); ?></h3>
                <div class="metric-value"><?php echo number_format($confirmed_quotes); ?></div>
                <div class="metric-label"><?php echo sprintf(__('Taux: %.1f%%', 'block-traiteur'), $conversion_rate); ?></div>
            </div>

            <div class="summary-card revenue">
                <h3><?php _e('Chiffre d\'Affaires', 'block-traiteur'); ?></h3>
                <div class="metric-value"><?php echo number_format($total_revenue, 2, ',', ' '); ?> €</div>
                <div class="metric-label"><?php _e('Devis confirmés uniquement', 'block-traiteur'); ?></div>
            </div>

            <div class="summary-card average">
                <h3><?php _e('Panier Moyen', 'block-traiteur'); ?></h3>
                <div class="metric-value"><?php echo number_format($avg_quote_value ?: 0, 2, ',', ' '); ?> €</div>
                <div class="metric-label"><?php _e('Tous devis confondus', 'block-traiteur'); ?></div>
            </div>
        </div>
    </div>

    <div class="reports-content">
        <div class="reports-row">
            <!-- Répartition par service -->
            <div class="report-section">
                <h2><?php _e('Répartition par Service', 'block-traiteur'); ?></h2>
                <div class="chart-container">
                    <canvas id="service-chart"></canvas>
                </div>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('Service', 'block-traiteur'); ?></th>
                            <th><?php _e('Devis', 'block-traiteur'); ?></th>
                            <th><?php _e('CA', 'block-traiteur'); ?></th>
                            <th><?php _e('Prix moyen', 'block-traiteur'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($service_stats as $stat): ?>
                            <tr>
                                <td>
                                    <?php 
                                    $service_names = array(
                                        'privatisation' => __('Privatisation Restaurant', 'block-traiteur'),
                                        'remorque' => __('Prestation Remorque', 'block-traiteur')
                                    );
                                    echo esc_html($service_names[$stat->service_type] ?? $stat->service_type);
                                    ?>
                                </td>
                                <td><?php echo number_format($stat->count); ?></td>
                                <td><?php echo number_format($stat->revenue, 2, ',', ' '); ?> €</td>
                                <td><?php echo number_format($stat->avg_price, 2, ',', ' '); ?> €</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Répartition par statut -->
            <div class="report-section">
                <h2><?php _e('Répartition par Statut', 'block-traiteur'); ?></h2>
                <div class="chart-container">
                    <canvas id="status-chart"></canvas>
                </div>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('Statut', 'block-traiteur'); ?></th>
                            <th><?php _e('Nombre', 'block-traiteur'); ?></th>
                            <th><?php _e('Pourcentage', 'block-traiteur'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($status_stats as $stat): ?>
                            <tr>
                                <td>
                                    <?php 
                                    $status_names = array(
                                        'pending' => __('En attente', 'block-traiteur'),
                                        'confirmed' => __('Confirmé', 'block-traiteur'),
                                        'cancelled' => __('Annulé', 'block-traiteur'),
                                        'completed' => __('Terminé', 'block-traiteur')
                                    );
                                    echo esc_html($status_names[$stat->status] ?? $stat->status);
                                    ?>
                                </td>
                                <td><?php echo number_format($stat->count); ?></td>
                                <td><?php echo number_format($stat->percentage, 1); ?>%</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Évolution mensuelle -->
        <div class="report-section full-width">
            <h2><?php _e('Évolution Mensuelle (12 derniers mois)', 'block-traiteur'); ?></h2>
            <div class="chart-container">
                <canvas id="evolution-chart"></canvas>
            </div>
        </div>

        <!-- Top clients -->
        <?php if (!empty($top_clients)): ?>
        <div class="report-section">
            <h2><?php _e('Meilleurs Clients', 'block-traiteur'); ?></h2>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('Client', 'block-traiteur'); ?></th>
                        <th><?php _e('Email', 'block-traiteur'); ?></th>
                        <th><?php _e('Devis', 'block-traiteur'); ?></th>
                        <th><?php _e('Total dépensé', 'block-traiteur'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($top_clients as $client): ?>
                        <tr>
                            <td><?php echo esc_html($client->client_name); ?></td>
                            <td><?php echo esc_html($client->client_email); ?></td>
                            <td><?php echo number_format($client->quotes_count); ?></td>
                            <td><?php echo number_format($client->total_spent, 2, ',', ' '); ?> €</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.reports-filters {
    background: white;
    padding: 20px;
    margin: 20px 0;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
}

.filter-form {
    display: flex;
    align-items: center;
    gap: 15px;
    flex-wrap: wrap;
}

.filter-form label {
    font-weight: 600;
}

.reports-summary {
    margin: 20px 0;
}

.summary-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.summary-card {
    background: white;
    padding: 20px;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    text-align: center;
    position: relative;
}

.summary-card.confirmed {
    border-left: 4px solid #46b450;
}

.summary-card.revenue {
    border-left: 4px solid #0073aa;
}

.summary-card.average {
    border-left: 4px solid #d63638;
}

.summary-card h3 {
    margin: 0 0 10px 0;
    font-size: 14px;
    color: #666;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.metric-value {
    font-size: 2.5em;
    font-weight: bold;
    color: #1d2327;
    margin: 10px 0;
}

.metric-label {
    font-size: 12px;
    color: #666;
}

.reports-content {
    display: grid;
    gap: 20px;
}

.reports-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.report-section {
    background: white;
    padding: 20px;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
}

.report-section.full-width {
    grid-column: 1 / -1;
}

.report-section h2 {
    margin: 0 0 20px 0;
    font-size: 18px;
    border-bottom: 2px solid #0073aa;
    padding-bottom: 10px;
}

.chart-container {
    position: relative;
    height: 300px;
    margin-bottom: 20px;
}

.chart-container canvas {
    max-height: 100%;
}

@media (max-width: 768px) {
    .summary-cards {
        grid-template-columns: 1fr;
    }
    
    .reports-row {
        grid-template-columns: 1fr;
    }
    
    .filter-form {
        flex-direction: column;
        align-items: flex-start;
    }
}

/* Styles pour les tableaux */
.wp-list-table th,
.wp-list-table td {
    padding: 12px;
}

.wp-list-table .column-primary {
    width: 40%;
}

/* Indicateurs de statut */
.status-indicator {
    display: inline-block;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    margin-right: 8px;
}

.status-pending {
    background-color: #f0ad4e;
}

.status-confirmed {
    background-color: #5cb85c;
}

.status-cancelled {
    background-color: #d9534f;
}

.status-completed {
    background-color: #0275d8;
}
</style>

<!-- Chargement de Chart.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>

<script>
jQuery(document).ready(function($) {
    
    // Gestion des filtres de période
    $('#period').on('change', function() {
        if ($(this).val() === 'custom') {
            $('#custom-dates').show();
        } else {
            $('#custom-dates').hide();
        }
    });

    // Export des rapports
    $('#export-report').on('click', function() {
        var params = new URLSearchParams(window.location.search);
        params.append('export', 'csv');
        window.location.href = window.location.pathname + '?' + params.toString();
    });

    // Configuration des couleurs pour les graphiques
    const colors = {
        primary: '#0073aa',
        success: '#46b450',
        warning: '#f0ad4e',
        danger: '#d63638',
        info: '#00a0d2',
        purple: '#826eb4',
        orange: '#ff8c00'
    };

    // Graphique répartition par service
    <?php if (!empty($service_stats)): ?>
    const serviceCtx = document.getElementById('service-chart').getContext('2d');
    const serviceData = {
        labels: [
            <?php foreach ($service_stats as $stat): ?>
                '<?php echo $stat->service_type === 'privatisation' ? __('Privatisation', 'block-traiteur') : __('Remorque', 'block-traiteur'); ?>',
            <?php endforeach; ?>
        ],
        datasets: [{
            data: [<?php echo implode(',', array_column($service_stats, 'count')); ?>],
            backgroundColor: [colors.primary, colors.success, colors.warning, colors.info],
            borderWidth: 0
        }]
    };

    new Chart(serviceCtx, {
        type: 'doughnut',
        data: serviceData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true
                    }
                }
            }
        }
    });
    <?php endif; ?>

    // Graphique répartition par statut
    <?php if (!empty($status_stats)): ?>
    const statusCtx = document.getElementById('status-chart').getContext('2d');
    const statusData = {
        labels: [
            <?php foreach ($status_stats as $stat): ?>
                '<?php 
                $status_names = array(
                    'pending' => __('En attente', 'block-traiteur'),
                    'confirmed' => __('Confirmé', 'block-traiteur'),
                    'cancelled' => __('Annulé', 'block-traiteur'),
                    'completed' => __('Terminé', 'block-traiteur')
                );
                echo $status_names[$stat->status] ?? $stat->status;
                ?>',
            <?php endforeach; ?>
        ],
        datasets: [{
            data: [<?php echo implode(',', array_column($status_stats, 'count')); ?>],
            backgroundColor: [colors.warning, colors.success, colors.danger, colors.info],
            borderWidth: 0
        }]
    };

    new Chart(statusCtx, {
        type: 'pie',
        data: statusData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true
                    }
                }
            }
        }
    });
    <?php endif; ?>

    // Graphique évolution mensuelle
    <?php if (!empty($monthly_evolution)): ?>
    const evolutionCtx = document.getElementById('evolution-chart').getContext('2d');
    const evolutionData = {
        labels: [
            <?php foreach (array_reverse($monthly_evolution) as $month): ?>
                '<?php echo date_i18n('M Y', strtotime($month->month . '-01')); ?>',
            <?php endforeach; ?>
        ],
        datasets: [
            {
                label: '<?php _e('Total devis', 'block-traiteur'); ?>',
                data: [<?php echo implode(',', array_reverse(array_column($monthly_evolution, 'quotes_count'))); ?>],
                borderColor: colors.primary,
                backgroundColor: colors.primary + '20',
                tension: 0.1,
                yAxisID: 'y'
            },
            {
                label: '<?php _e('Devis confirmés', 'block-traiteur'); ?>',
                data: [<?php echo implode(',', array_reverse(array_column($monthly_evolution, 'confirmed_count'))); ?>],
                borderColor: colors.success,
                backgroundColor: colors.success + '20',
                tension: 0.1,
                yAxisID: 'y'
            },
            {
                label: '<?php _e('Chiffre d\'affaires (€)', 'block-traiteur'); ?>',
                data: [<?php echo implode(',', array_reverse(array_column($monthly_evolution, 'revenue'))); ?>],
                borderColor: colors.warning,
                backgroundColor: colors.warning + '20',
                tension: 0.1,
                yAxisID: 'y1'
            }
        ]
    };

    new Chart(evolutionCtx, {
        type: 'line',
        data: evolutionData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            scales: {
                x: {
                    display: true,
                    title: {
                        display: true,
                        text: '<?php _e('Mois', 'block-traiteur'); ?>'
                    }
                },
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: '<?php _e('Nombre de devis', 'block-traiteur'); ?>'
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: '<?php _e('Chiffre d\'affaires (€)', 'block-traiteur'); ?>'
                    },
                    grid: {
                        drawOnChartArea: false,
                    },
                }
            },
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.datasetIndex === 2) {
                                label += new Intl.NumberFormat('fr-FR', {
                                    style: 'currency',
                                    currency: 'EUR'
                                }).format(context.parsed.y);
                            } else {
                                label += context.parsed.y;
                            }
                            return label;
                        }
                    }
                }
            }
        }
    });
    <?php endif; ?>

    // Fonction utilitaire pour formater les nombres
    function formatNumber(number, decimals = 0) {
        return new Intl.NumberFormat('fr-FR', {
            minimumFractionDigits: decimals,
            maximumFractionDigits: decimals
        }).format(number);
    }

    // Fonction utilitaire pour formater les devises
    function formatCurrency(amount) {
        return new Intl.NumberFormat('fr-FR', {
            style: 'currency',
            currency: 'EUR'
        }).format(amount);
    }

    // Animation des métriques au chargement
    $('.metric-value').each(function() {
        const $this = $(this);
        const finalValue = parseFloat($this.text().replace(/[^\d.,]/g, '').replace(',', '.'));
        
        if (!isNaN(finalValue)) {
            $this.text('0');
            $({ countNum: 0 }).animate({
                countNum: finalValue
            }, {
                duration: 2000,
                easing: 'swing',
                step: function() {
                    if ($this.text().includes('€')) {
                        $this.text(formatCurrency(this.countNum));
                    } else {
                        $this.text(formatNumber(this.countNum));
                    }
                },
                complete: function() {
                    if ($this.text().includes('€')) {
                        $this.text(formatCurrency(finalValue));
                    } else {
                        $this.text(formatNumber(finalValue));
                    }
                }
            });
        }
    });

    // Mise à jour automatique des données toutes les 5 minutes
    setInterval(function() {
        // Vérifier s'il y a de nouvelles données à charger
        $.post(ajaxurl, {
            action: 'check_reports_update',
            security: '<?php echo wp_create_nonce('block_traiteur_reports'); ?>',
            period: '<?php echo esc_js($period); ?>',
            start_date: '<?php echo esc_js($start_date); ?>',
            end_date: '<?php echo esc_js($end_date); ?>'
        }, function(response) {
            if (response.success && response.data.updated) {
                // Afficher une notification de mise à jour disponible
                if (!$('.reports-update-notice').length) {
                    $('.wrap h1').after(
                        '<div class="notice notice-info reports-update-notice">' +
                        '<p><?php _e('De nouvelles données sont disponibles.', 'block-traiteur'); ?> ' +
                        '<a href="#" id="refresh-reports"><?php _e('Actualiser', 'block-traiteur'); ?></a></p>' +
                        '</div>'
                    );
                }
            }
        });
    }, 300000); // 5 minutes

    // Actualisation des rapports
    $(document).on('click', '#refresh-reports', function(e) {
        e.preventDefault();
        location.reload();
    });

    // Gestion responsive des tableaux
    function makeTablesResponsive() {
        $('.wp-list-table').each(function() {
            if ($(window).width() < 768) {
                $(this).addClass('mobile-view');
            } else {
                $(this).removeClass('mobile-view');
            }
        });
    }

    $(window).on('resize', makeTablesResponsive);
    makeTablesResponsive();
});
</script>

<?php
// Gestion de l'export CSV
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=rapport-block-traiteur-' . date('Y-m-d') . '.csv');
    
    $output = fopen('php://output', 'w');
    
    // En-têtes CSV
    fputcsv($output, array(
        __('Période', 'block-traiteur'),
        __('Total Devis', 'block-traiteur'),
        __('Devis Confirmés', 'block-traiteur'),
        __('Taux Conversion', 'block-traiteur'),
        __('Chiffre Affaires', 'block-traiteur'),
        __('Panier Moyen', 'block-traiteur')
    ), ';');
    
    // Données CSV
    fputcsv($output, array(
        date_i18n('j F Y', strtotime($start_date)) . ' - ' . date_i18n('j F Y', strtotime($end_date)),
        $total_quotes,
        $confirmed_quotes,
        number_format($conversion_rate, 1) . '%',
        number_format($total_revenue, 2),
        number_format($avg_quote_value, 2)
    ), ';');
    
    fclose($output);
    exit;
}
?>
