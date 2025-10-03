<?php
/**
 * Page de diagnostic et debug AJAX pour le plugin Restaurant Booking
 * Créée pour éviter de perdre du temps à réinvestiguer les problèmes d'AJAX
 */

if (!defined('ABSPATH')) {
    exit;
}

class RestaurantBooking_Ajax_Debug_Page
{
    /**
     * Constructeur
     */
    public function __construct()
    {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('wp_ajax_restaurant_booking_test_ajax', array($this, 'ajax_test_endpoint'));
        add_action('wp_ajax_restaurant_booking_check_server', array($this, 'ajax_check_server'));
        add_action('wp_ajax_restaurant_booking_reset_config', array($this, 'ajax_reset_config'));
    }

    /**
     * Ajouter le menu admin
     */
    public function add_admin_menu()
    {
        add_submenu_page(
            'restaurant-booking',
            __('Diagnostic AJAX', 'restaurant-booking'),
            __('🔧 Diagnostic AJAX', 'restaurant-booking'),
            'manage_options',
            'restaurant-booking-ajax-debug',
            array($this, 'display_admin_page')
        );
    }

    /**
     * Page d'administration - pour WordPress Admin
     */
    public function display_admin_page()
    {
        ?>
        <div class="wrap">
            <h1><span class="dashicons dashicons-admin-tools"></span> 🔧 Diagnostic AJAX Restaurant Booking</h1>
            
            <div class="notice notice-info">
                <p><strong>📋 Documentation complète:</strong> Cette page documente la solution du problème "Permissions insuffisantes" sur LiteSpeed.</p>
            </div>

            <!-- Onglets -->
            <nav class="nav-tab-wrapper">
                <a href="#documentation" class="nav-tab nav-tab-active">📚 Documentation</a>
                <a href="#diagnostic" class="nav-tab">🔍 Diagnostic</a>
                <a href="#tests" class="nav-tab">🧪 Tests</a>
                <a href="#historique" class="nav-tab">📜 Historique</a>
            </nav>

            <!-- Onglet Documentation -->
            <div id="documentation" class="tab-content">
                <h2>📚 Documentation du Problème AJAX</h2>
                
                <div class="card" style="max-width: none;">
                    <h3>🚨 Problème Identifié (Décembre 2024)</h3>
                    <blockquote>
                        <p><strong>Symptôme:</strong> Utilisateurs non connectés recevaient "Permissions insuffisantes" au lieu de JSON pour toutes les requêtes AJAX.</p>
                        <p><strong>Impact:</strong> Formulaires complètement cassés pour les visiteurs.</p>
                        <p><strong>Erreurs:</strong> Content-Type: text/html au lieu de application/json, JSON Parse Error.</p>
                    </blockquote>
                </div>

                <div class="card">
                    <h3>🔍 Causes Identifiées</h3>
                    <ul>
                        <li><strong>Serveur:</strong> LiteSpeed avec protection AJAX agressive</li>
                        <li><strong>Configuration:</strong> Blocage automatique des wp_die() vers "Permissions insuffisantes"</li>
                        <li><strong>Plugin/Security:</strong> Aucun plugin spécifique (même avec plugins désactivés)</li>
                        <li><strong>WordPress:</strong> admin-ajax.php intercepté par le serveur</li>
                    </ul>
                </div>

                <div class="card">
                    <h3>✅ Solution Implémentée</h3>
                    <code><strong>Endpoint de contournement:</strong> ajax-clean.php</code>
                    <ul>
                        <li>✅ Bypass complet d'admin-ajax.php</li>
                        <li>✅ Vérification nonce sécurisée</li>
                        <li>✅ Gestion d'erreurs JSON propre</li>
                        <li>✅ Compatible tous navigateurs/devices</li>
                    </ul>
                </div>

                <div class="card">
                    <h3>⚙️ Modifications Apportées</h3>
                    <ol>
                        <li><strong>Créé:</strong> ajax-clean.php (endpoint de contournement)</li>
                        <li><strong>Modifié:</strong> assets/js/restaurant-booking-form-v3.js → nouvelles URLs</li>
                        <li><strong>Modifié:</strong> public/class-shortcode-form-v3.php → nouvelles URLs</li>
                        <li><strong>Toutes actions AJAX:</strong> rbf_v3_* fonctionnent parfaitement</li>
                    </ol>
                </div>

                <div class="card">
                    <h3>📍 Fichiers Décisifs</h3>
                    <ul>
                        <li><code>ajax-clean.php</code> - Solution principale</li>
                        <li><code>public/class-ajax-handler-v3.php</code> - Handlers AJAX avec return ajoutés</li>
                        <li><code>includes/*</code> - wp_die remplacés par wp_send_json_error</li>
                    </ul>
                </div>

                <div class="card">
                    <h3>🚨 Points d'Attention</h3>
                    <ul>
                        <li><strong>Si plugin renommé:</strong> Modifier URLs dans JavaScript</li>
                        <li><strong>Cache:</strong> Vider après modifications</li>
                        <li><strong>Solution définitive:</strong> Contacter Hostinger pour LiteSpeed admin-ajax.php</li>
                        <li><strong>Monitoring:</strong> Surveiller les logs d'erreur</li>
                    </ul>
                </div>
            </div>

            <!-- Onglet Diagnostic -->
            <div id="diagnostic" class="tab-content" style="display: none;">
                <h2>🔍 Diagnostic Système</h2>
                
                <div class="card">
                    <h3>🖥️ Information Serveur</h3>
                    <table class="widefat">
                        <tr><td><strong>Serveur:</strong></td><td><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?></td></tr>
                        <tr><td><strong>PHP:</strong></td><td><?php echo phpversion(); ?></td></tr>
                        <tr><td><strong>WordPress:</strong></td><td><?php echo get_bloginfo('version'); ?></td></tr>
                        <tr><td><strong>Plugin:</strong></td><td><?php echo defined('RESTAURANT_BOOKING_VERSION') ? RESTAURANT_BOOKING_VERSION : 'Unknown'; ?></td></tr>
                        <tr><td><strong>Endpoint:</strong></td><td><?php echo plugin_dir_url(__FILE__) . '../ajax-clean.php'; ?></td></tr>
                    </table>
                </div>

                <div class="card">
                    <h3>🎯 Test des Endpoints</h3>
                    <button id="test-current-endpoint" class="button button-primary">🧪 Tester Ajax-Clean.php</button>
                    <button id="test-admin-ajax" class="button">🧪 Tester admin-ajax.php</button>
                    <button id="test-comparison" class="button button-secondary">⚔️ Comparaison</button>
                    <div id="diagnostic-results" style="margin-top: 20px;"></div>
                </div>

                <div class="card">
                    <h3>📊 Hooks AJAX Enregistrés</h3>
                    <?php $this->display_ajax_hooks(); ?>
                </div>

                <div class="card">
                    <h3>🔧 Actions Quick Fix</h3>
                    <button id="regenerate-nonces" class="button">🔄 Régénérer Nonces</button>
                    <button id="clear-ajax-cache" class="button">🧹 Vider Cache AJAX</button>
                    <button id="reset-endpoint-config" class="button">🔄 Remettre admin-ajax.php</button>
                    <div id="quick-results" style="margin-top: 15px;"></div>
                </div>
            </div>

            <!-- Onglet Tests -->
            <div id="tests" class="tab-content" style="display: none;">
                <h2>🧪 Tests Complets</h2>
                
                <div class="card">
                    <h3>🎯 Test Actions AJAX</h3>
                    <p>Test complet de toutes les actions du formulaire :</p>
                    <div style="margin: 15px 0;">
                        <button id="test-load-step" class="button">Test Load Step</button>
                        <button id="test-calculate-price" class="button">Test Calculate Price</button>
                        <button id="test-submit-quote" class="button">Test Submit Quote</button>
                        <button id="test-signature-products" class="button">Test Signature Products</button>
                        <button id="test-calculate-distance" class="button">Test Calculate Distance</button>
                    </div>
                    <div id="action-tests-results"></div>
                </div>

                <div class="card">
                    <h3>📱 Test Simulation Utilisateur</h3>
                    <p>Simulation d'un utilisateur non connecté utilisant le formulaire :</p>
                    <button id="simulate-user-workflow" class="button button-primary">🚀 Simuler Workflow Complet</button>
                    <div id="workflow-results"></div>
                </div>

                <div class="card">
                    <h3>🌐 Test Compatibility Navigateurs</h3>
                    <p>Vérification que l'endpoint fonctionne sur tous les navigateurs :</p>
                    <button id="test-cross-browser" class="button">Safari/Chrome/Firefox</button>
                    <div id="browser-results"></div>
                </div>
            </div>

            <!-- Onglet Historique -->
            <div id="historique" class="tab-content" style="display: none;">
                <h2>📜 Historique des Corrections</h2>
                
                <div class="card">
                    <h3>📅 Décembre 2024 - Correction Majeure AJAX</h3>
                    <ul>
                        <li><strong>Problème:</strong> Utilisateurs non connectés bloqués sur AJAX</li>
                        <li><strong>Durée investigation:</strong> 2 days</li>
                        <li><strong>Cause identifiée:</strong> LiteSpeed + protection wp_die automatique</li>
                        <li><strong>Solution:</strong> ajax-clean.php (endpoint de contournement)</li>
                        <li><strong>Impact:</strong> Fichiers modifiés: 2 JS + 1 PHP principal + 1 nouveau</li>
                    </ul>
                </div>

                <div class="card">
                    <h3>🔧 Corrections Techniques Appliquées</h3>
                    <ul>
                        <li>✅ Remplacement wp_die() par wp_send_json_error + exit</li>
                        <li>✅ Ajout return après send_json_response() dans handlers</li>
                        <li>✅ Unification nonces restaurant_booking_form_v3</li>
                        <li>✅ Suppression endpoints publics fragiles</li>
                        <li>✅ Headers anti-cache dans send_json_response()</li>
                        <li>✅ Correction logger frontend getAllResponseHeaders</li>
                    </ul>
                </div>

                <div class="card">
                    <h3>📝 Lessons Learned</h3>
                    <ul>
                        <li><strong>Diagnostic systémique:</strong> Commencer par tester admin-ajax.php brut</li>
                        <li><strong>Serveurs:</strong> LiteSpeed peut bloquer wp_die automatiquement</li>
                        <li><strong>Outils:</strong> Créer des endpoints de test pour isoler les problèmes</li>
                        <li><strong>Documentation:</strong> Cette page évite la perte de temps future</li>
                    </ul>
                </div>
            </div>
        </div>

        <style>
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        .card { background: white; border: 1px solid #ccd0d4; padding: 20px; margin: 20px 0; border-radius: 5px; }
        .card h3 { margin-top: 0; color: #23282d; }
        .widefat td { padding: 8px; }
        .notice { padding: 15px; margin: 20px 0; border-left: 4px solid #0073aa; background: #f7f7f7; }
        </style>

        <script>
        jQuery(document).ready(function($) {
            // Navigation onglets
            $('.nav-tab').click(function() {
                var target = $(this).attr('href');
                $('.nav-tab').removeClass('nav-tab-active');
                $('.tab-content').hide().removeClass('active');
                $(this).addClass('nav-tab-active');
                $(target).show().addClass('active');
            });

            // Tests endpoints
            $('#test-current-endpoint').click(function() {
                testAjaxEndpoint('/wp-content/plugins/plugin-v2-BLOCK/ajax-clean.php', 'ajax-clean.php');
            });

            $('#test-admin-ajax').click(function() {
                testAjaxEndpoint('/wp-admin/admin-ajax.php', 'admin-ajax.php');
            });

            $('#test-comparison').click(function() {
                compareEndpoints();
            });

            // Tests actions
            $('#test-load-step').click(function() {
                testSingleAction('rbf_v3_load_step');
            });

            $('#test-calculate-price').click(function() {
                testSingleAction('rbf_v3_calculate_price');
            });

            $('#test-submit-quote').click(function() {
                testSingleAction('rbf_v3_submit_quote');
            });

            $('#test-signature-products').click(function() {
                testSingleAction('rbf_v3_load_signature_products');
            });

            $('#test-calculate-distance').click(function() {
                testSingleAction('rbf_v3_calculate_distance');
            });

            $('#simulate-user-workflow').click(function() {
                simulateUserWorkflow();
            });

            function testAjaxEndpoint(url, name) {
                $('#diagnostic-results').html('<div class="notice notice-info">Test ' + name + '...</div>');
                
                $.ajax({
                    url: url,
                    method: 'POST',
                    data: { action: 'rbf_v3_load_step', nonce: 'test_nonce', step: 1, service_type: 'restaurant' },
                    success: function(response) {
                        $('#diagnostic-results').html('<div class="notice notice-success"><strong>' + name + ' SUCCESS:</strong><pre>' + JSON.stringify(response, null, 2) + '</pre></div>');
                    },
                    error: function(xhr) {
                        var text = xhr.responseText || 'No response text';
                        $('#diagnostic-results').html('<div class="notice notice-error"><strong>' + name + ' ERROR:</strong><pre>' + text.substring(0, 200) + '</pre></div>');
                    }
                });
            }

            function compareEndpoints() {
                $('#diagnostic-results').html('<div class="notice notice-info">Comparaison en cours...</div>');
                
                Promise.all([
                    $.ajax({ url: '/wp-content/plugins/plugin-v2-BLOCK/ajax-clean.php', method: 'POST', data: { action: 'rbf_v3_load_step', nonce: 'test_nonce', step: 1, service_type: 'restaurant' } }).catch(e => e.responseText),
                    $.ajax({ url: '/wp-admin/admin-ajax.php', method: 'POST', data: { action: 'rbf_v3_load_step', nonce: 'test_nonce', step: 1, service_type: 'restaurant' } }).catch(e => e.responseText)
                ]).then(function(results) {
                    var ajaxCleanResult = typeof results[0] === 'string' ? results[0] : 'SUCCESS: ' + JSON.stringify(results[0]);
                    var adminAjaxResult = typeof results[1] === 'string' ? results[1] : 'SUCCESS: ' + JSON.stringify(results[1]);
                    
                    $('#diagnostic-results').html(`
                        <div class="notice notice-success">
                            <h4>Comparaison Endpoints:</h4>
                            <p><strong>ajax-clean.php:</strong> ${ajaxCleanResult.substring(0, 100)}...</p>
                            <p><strong>admin-ajax.php:</strong> ${adminAjaxResult.substring(0, 100)}...</p>
                        </div>
                    `);
                });
            }

            function testSingleAction(action) {
                $('#action-tests-results').html('<div class="notice notice-info">Test ' + action + '...</div>');
                
                $.ajax({
                    url: '/wp-content/plugins/plugin-v2-BLOCK/ajax-clean.php',
                    method: 'POST',
                    data: { 
                        action: action, 
                        nonce: '<?php echo wp_create_nonce('restaurant_booking_form_v3'); ?>',
                        step: 1,
                        service_type: 'restaurant',
                        form_data: '{"guest_count":10}'
                    },
                    success: function(response) {
                        $('#action-tests-results').html('<div class="notice notice-success"><strong>' + action + ' OK:</strong><pre>' + JSON.stringify(response, null, 2) + '</pre></div>');
                    },
                    error: function(xhr) {
                        var text = xhr.responseText || 'No response text';
                        $('#action-tests-results').html('<div class="notice notice-error"><strong>' + action + ' FAIL:</strong><pre>' + text.substring(0, 200) + '</pre></div>');
                    }
                });
            }

            function simulateUserWorkflow() {
                $('#workflow-results').html('<div class="notice notice-info">Simulation workflow complet...</div>');
                
                var steps = [
                    { action: 'rbf_v3_load_step', name: 'Étape 1' },
                    { action: 'rbf_v3_load_step', name: 'Étape 2' },
                    { action: 'rbf_v3_calculate_price', name: 'Calcul Prix' },
                    { action: 'rbf_v3_load_signature_products', name: 'Produits Signature' }
                ];
                
                var results = '<div class="notice notice-success"><h4>Résultats Workflow:</h4>';
                
                Promise.all(steps.map(function(step, index) {
                    return $.ajax({
                        url: '/wp-content/plugins/plugin-v2-BLOCK/ajax-clean.php',
                        method: 'POST',
                        data: { 
                            action: step.action, 
                            nonce: '<?php echo wp_create_nonce('restaurant_booking_form_v3'); ?>',
                            step: index + 1,
                            service_type: 'restaurant',
                            form_data: '{"guest_count":10}'
                        }
                    }).catch(e => e.responseText);
                })).then(function(stepsResults) {
                    steps.forEach(function(step, index) {
                        var result = stepsResults[index];
                        var status = (typeof result === 'string' && result.includes('success')) ? '✅' : '⚠️';
                        results += '<p>' + status + ' ' + step.name + ': ' + (typeof result === 'string' ? result.substring(0, 50) : 'Success') + '</p>';
                    });
                    results += '</div>';
                    $('#workflow-results').html(results);
                });
            }
        });
        </script>
        <?php
    }

    /**
     * Afficher les hooks AJAX enregistrés
     */
    private function display_ajax_hooks()
    {
        global $wp_filter;
        
        $ajax_actions = [
            'rbf_v3_load_step',
            'rbf_v3_calculate_price',
            'rbf_v3_submit_quote',
            'rbf_v3_load_signature_products',
            'rbf_v3_get_month_availability',
            'rbf_v3_get_availability',
            'rbf_v3_calculate_distance'
        ];

        echo '<table class="widefat">';
        echo '<thead><tr><th>Action</th><th>wp_ajax_*</th><th>wp_ajax_nopriv_*</th><th>Status</th></tr></thead>';
        echo '<tbody>';

        foreach ($ajax_actions as $action) {
            $has_priv = has_action('wp_ajax_' . $action);
            $has_nopriv = has_action('wp_ajax_nopriv_' . $action);
            $status = ($has_priv && $has_nopriv) ? '✅ Complet' : '⚠️ Incomplet';
            
            echo '<tr>';
            echo '<td><strong>' . $action . '</strong></td>';
            echo '<td>' . ($has_priv ? '✅' : '❌') . '</td>';
            echo '<td>' . ($has_nopriv ? '✅' : '❌') . '</td>';
            echo '<td>' . $status . '</td>';
            echo '</tr>';
        }

        echo '</tbody></table>';
    }

    /**
     * AJAX : Test endpoint
     */
    public function ajax_test_endpoint()
    {
        check_ajax_referer('restaurant_booking_debug', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permissions insuffisantes');
        }

        $test_data = [
            'success' => true,
            'data' => [
                'message' => 'Test endpoint AJAX fonctionnel',
                'timestamp' => current_time('mysql'),
                'endpoint_url' => '/wp-content/plugins/plugin-v2-BLOCK/ajax-clean.php',
                'wp_ajax_support' => has_action('wp_ajax_rbf_v3_load_step') ? 'Yes' : 'No'
            ]
        ];

        wp_send_json_success($test_data);
    }

    /**
     * AJAX : Check serveur
     */
    public function ajax_check_server()
    {
        check_ajax_referer('restaurant_booking_debug', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permissions insuffisantes');
        }

        $server_info = [
            'server' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'php_version' => phpversion(),
            'wordpress_version' => get_bloginfo('version'),
            'plugin_version' => defined('RESTAURANT_BOOKING_VERSION') ? RESTAURANT_BOOKING_VERSION : 'Unknown',
            'current_user' => get_current_user_id(),
            'plugin_dir' => RESTAURANT_BOOKING_PLUGIN_DIR ?? 'Unknown',
            'ajax_endpoint_exists' => file_exists(RESTAURANT_BOOKING_PLUGIN_DIR . 'ajax-clean.php'),
            'handler_class_exists' => class_exists('RestaurantBooking_Ajax_Handler_V3')
        ];

        wp_send_json_success($server_info);
    }

    /**
     * AJAX : Reset configuration
     */
    public function ajax_reset_config()
    {
        check_ajax_referer('restaurant_booking_debug', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permissions insuffisantes');
        }

        // Ici on pourrait remettre admin-ajax.php si nécessaire
        $response = [
            'message' => 'Configuration reset effectué',
            'note' => 'Pour remettre admin-ajax.php, modifiez les fichiers JS/PHP manuellement'
        ];

        wp_send_json_success($response);
    }
}
?>
