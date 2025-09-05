<?php
/**
 * Classe principale de l'interface administration
 * Interface selon cahier des charges (ligne 216-311)
 *
 * @package Block_Traiteur
 * @subpackage Admin
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Classe Block_Traiteur_Admin
 * 
 * Gère l'interface d'administration WordPress selon les spécifications
 */
class Block_Traiteur_Admin {
    
    /**
     * @var Block_Traiteur_Settings Instance des settings
     */
    private $settings;
    
    /**
     * Constructeur
     */
    public function __construct() {
        $this->settings = Block_Traiteur_Settings::get_instance();
        
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'admin_init'));
        add_action('admin_notices', array($this, 'admin_notices'));
        add_action('admin_post_update_database', array($this, 'handle_database_update'));
        add_action('admin_post_recreate_database', array($this, 'handle_database_recreate'));
    }
    
    /**
     * Ajouter le menu d'administration selon les spécifications (ligne 218-284)
     */
    public function add_admin_menu() {
        // Menu principal : "Restaurant Devis" (ligne 218)
        add_menu_page(
            'Restaurant Devis',
            'Restaurant Devis',
            'manage_options',
            'block-traiteur',
            array($this, 'dashboard_page'),
            'dashicons-food',
            30
        );
        
        // Sous-menu "Tableau de bord" (ligne 220-224)
        add_submenu_page(
            'block-traiteur',
            'Tableau de bord',
            'Tableau de bord',
            'manage_options',
            'block-traiteur',
            array($this, 'dashboard_page')
        );
        
        // Sous-menu "Gestion des devis" (ligne 226-237)
        add_submenu_page(
            'block-traiteur',
            'Gestion des devis',
            'Gestion des devis',
            'manage_options',
            'block-traiteur-quotes',
            array($this, 'quotes_page')
        );
        
        // Sous-menu "Gestion des produits" (ligne 238-263)
        add_submenu_page(
            'block-traiteur',
            'Produits',
            'Produits',
            'manage_options',
            'block-traiteur-products',
            array($this, 'products_page')
        );
        
        // Sous-menu "Paramètres généraux" (ligne 264-283)
        add_submenu_page(
            'block-traiteur',
            'Paramètres',
            'Paramètres',
            'manage_options',
            'block-traiteur-settings',
            array($this, 'settings_page')
        );
        
        // Sous-menu "Calendrier" (ligne 284-297)
        add_submenu_page(
            'block-traiteur',
            'Disponibilités',
            'Disponibilités',
            'manage_options',
            'block-traiteur-calendar',
            array($this, 'calendar_page')
        );
    }
    
    /**
     * Initialisation admin
     */
    public function admin_init() {
        // Gérer les actions
        if (isset($_GET['action'])) {
            switch ($_GET['action']) {
                case 'update_db':
                    $this->handle_database_update();
                    break;
                case 'recreate_db':
                    $this->handle_database_recreate();
                    break;
            }
        }
    }
    
    /**
     * Afficher les notices admin
     */
    public function admin_notices() {
        if (isset($_GET['message'])) {
            $message = sanitize_text_field($_GET['message']);
            $type = isset($_GET['type']) ? sanitize_text_field($_GET['type']) : 'success';
            
            echo '<div class="notice notice-' . $type . ' is-dismissible">';
            echo '<p>' . esc_html($message) . '</p>';
            echo '</div>';
        }
    }
    
    /**
     * Page tableau de bord (ligne 220-224)
     */
    public function dashboard_page() {
        global $wpdb;
        
        // Statistiques rapides
        $quotes_table = $wpdb->prefix . 'restaurant_quotes';
        $stats = array(
            'total_quotes' => $wpdb->get_var("SELECT COUNT(*) FROM {$quotes_table}"),
            'pending_quotes' => $wpdb->get_var("SELECT COUNT(*) FROM {$quotes_table} WHERE status = 'draft'"),
            'confirmed_quotes' => $wpdb->get_var("SELECT COUNT(*) FROM {$quotes_table} WHERE status = 'confirmed'"),
            'total_revenue' => $wpdb->get_var("SELECT SUM(total_price) FROM {$quotes_table} WHERE status = 'confirmed'") ?: 0
        );
        
        // Devis récents
        $recent_quotes = $wpdb->get_results("
            SELECT id, quote_number, service_type, event_date, guest_count, total_price, status, created_at
            FROM {$quotes_table} 
            ORDER BY created_at DESC 
            LIMIT 10
        ");
        
        ?>
        <div class="wrap">
            <h1>Tableau de bord - Restaurant Devis</h1>
            
            <!-- Actions rapides -->
            <div class="dashboard-widgets-wrap">
                <div class="metabox-holder">
                    <div class="postbox-container" style="width: 100%;">
                        
                        <!-- Statistiques -->
                        <div class="postbox">
                            <h2 class="hndle"><span>Vue d'ensemble</span></h2>
                            <div class="inside">
                                <div style="display: flex; gap: 20px; flex-wrap: wrap;">
                                    <div class="dashboard-stat" style="background: #f0f6fc; padding: 15px; border-radius: 5px; min-width: 150px;">
                                        <h3 style="margin: 0; color: #1d2327;"><?php echo $stats['total_quotes']; ?></h3>
                                        <p style="margin: 5px 0 0; color: #646970;">Total devis</p>
                                    </div>
                                    <div class="dashboard-stat" style="background: #fff2e8; padding: 15px; border-radius: 5px; min-width: 150px;">
                                        <h3 style="margin: 0; color: #1d2327;"><?php echo $stats['pending_quotes']; ?></h3>
                                        <p style="margin: 5px 0 0; color: #646970;">En attente</p>
                                    </div>
                                    <div class="dashboard-stat" style="background: #f0fff4; padding: 15px; border-radius: 5px; min-width: 150px;">
                                        <h3 style="margin: 0; color: #1d2327;"><?php echo $stats['confirmed_quotes']; ?></h3>
                                        <p style="margin: 5px 0 0; color: #646970;">Confirmés</p>
                                    </div>
                                    <div class="dashboard-stat" style="background: #f6f7f7; padding: 15px; border-radius: 5px; min-width: 150px;">
                                        <h3 style="margin: 0; color: #1d2327;"><?php echo number_format($stats['total_revenue'], 2, ',', ' '); ?> €</h3>
                                        <p style="margin: 5px 0 0; color: #646970;">Chiffre d'affaires</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Actions rapides -->
                        <div class="postbox">
                            <h2 class="hndle"><span>Actions rapides</span></h2>
                            <div class="inside">
                                <p>
                                    <a href="<?php echo admin_url('admin.php?page=block-traiteur-quotes'); ?>" class="button button-primary">Voir tous les devis</a>
                                    <a href="<?php echo admin_url('admin.php?page=block-traiteur-products'); ?>" class="button">Gérer les produits</a>
                                    <a href="<?php echo admin_url('admin.php?page=block-traiteur-settings'); ?>" class="button">Paramètres</a>
                                </p>
                                <p>
                                    <a href="<?php echo admin_url('admin.php?page=block-traiteur&action=update_db'); ?>" class="button button-secondary">Mettre à jour la base de données</a>
                                    <a href="<?php echo admin_url('admin.php?page=block-traiteur&action=recreate_db'); ?>" class="button button-secondary" onclick="return confirm('Attention: Cette action va supprimer toutes les données existantes. Continuer ?')">Recréer la base de données</a>
                                </p>
                            </div>
                        </div>
                        
                        <!-- Devis récents -->
                        <div class="postbox">
                            <h2 class="hndle"><span>Devis récents (10 derniers)</span></h2>
                            <div class="inside">
                                <?php if ($recent_quotes): ?>
                                <table class="widefat striped">
                                    <thead>
                                        <tr>
                                            <th>Numéro</th>
                                            <th>Service</th>
                                            <th>Date événement</th>
                                            <th>Convives</th>
                                            <th>Total</th>
                                            <th>Statut</th>
                                            <th>Créé le</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recent_quotes as $quote): ?>
                                        <tr>
                                            <td><strong><?php echo esc_html($quote->quote_number); ?></strong></td>
                                            <td><?php echo $quote->service_type === 'restaurant' ? 'Restaurant' : 'Remorque'; ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($quote->event_date)); ?></td>
                                            <td><?php echo $quote->guest_count; ?> pers.</td>
                                            <td><?php echo number_format($quote->total_price, 2, ',', ' '); ?> €</td>
                                            <td>
                                                <span class="status-<?php echo $quote->status; ?>">
                                                    <?php 
                                                    switch($quote->status) {
                                                        case 'draft': echo 'Brouillon'; break;
                                                        case 'sent': echo 'Envoyé'; break;
                                                        case 'confirmed': echo 'Confirmé'; break;
                                                        case 'cancelled': echo 'Annulé'; break;
                                                        default: echo ucfirst($quote->status);
                                                    }
                                                    ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($quote->created_at)); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                                <?php else: ?>
                                <p>Aucun devis pour le moment.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>
        
        <style>
        .status-draft { color: #d63638; }
        .status-sent { color: #dba617; }
        .status-confirmed { color: #00a32a; }
        .status-cancelled { color: #8c8f94; }
        </style>
        <?php
    }
    
    /**
     * Page gestion des devis
     */
    public function quotes_page() {
        echo '<div class="wrap">';
        echo '<h1>Gestion des devis</h1>';
        echo '<p>Interface de gestion des devis en développement...</p>';
        echo '</div>';
    }
    
    /**
     * Page gestion des produits
     */
    public function products_page() {
        echo '<div class="wrap">';
        echo '<h1>Gestion des produits</h1>';
        echo '<p>Interface de gestion des produits en développement...</p>';
        echo '</div>';
    }
    
    /**
     * Page paramètres
     */
    public function settings_page() {
        // Traitement des données POST
        if (isset($_POST['submit']) && wp_verify_nonce($_POST['_wpnonce'], 'block_traiteur_settings')) {
            $this->save_settings();
            echo '<div class="notice notice-success"><p>Paramètres sauvegardés avec succès !</p></div>';
        }
        
        $pricing_settings = $this->settings->get_pricing_settings();
        
        ?>
        <div class="wrap">
            <h1>Paramètres - Restaurant Devis</h1>
            
            <form method="post" action="">
                <?php wp_nonce_field('block_traiteur_settings'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">Prix de base restaurant</th>
                        <td>
                            <input type="number" name="restaurant_base_price" value="<?php echo esc_attr($pricing_settings['restaurant_base_price']); ?>" step="0.01" />
                            <p class="description">Prix forfait restaurant (défaut: 300€)</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Prix de base remorque</th>
                        <td>
                            <input type="number" name="remorque_base_price" value="<?php echo esc_attr($pricing_settings['remorque_base_price']); ?>" step="0.01" />
                            <p class="description">Prix forfait remorque (défaut: 350€)</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Supplément horaire</th>
                        <td>
                            <input type="number" name="hourly_supplement" value="<?php echo esc_attr($pricing_settings['hourly_supplement']); ?>" step="0.01" />
                            <p class="description">Prix par heure supplémentaire (défaut: 50€)</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Convives minimum restaurant</th>
                        <td>
                            <input type="number" name="restaurant_min_guests" value="<?php echo esc_attr($pricing_settings['restaurant_min_guests']); ?>" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Convives maximum restaurant</th>
                        <td>
                            <input type="number" name="restaurant_max_guests" value="<?php echo esc_attr($pricing_settings['restaurant_max_guests']); ?>" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Convives minimum remorque</th>
                        <td>
                            <input type="number" name="remorque_min_guests" value="<?php echo esc_attr($pricing_settings['remorque_min_guests']); ?>" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Convives maximum remorque</th>
                        <td>
                            <input type="number" name="remorque_max_guests" value="<?php echo esc_attr($pricing_settings['remorque_max_guests']); ?>" />
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
    
    /**
     * Page calendrier/disponibilités
     */
    public function calendar_page() {
        echo '<div class="wrap">';
        echo '<h1>Gestion des disponibilités</h1>';
        echo '<p>Interface de gestion du calendrier en développement...</p>';
        echo '</div>';
    }
    
    /**
     * Sauvegarder les paramètres
     */
    private function save_settings() {
        $settings_to_save = array(
            'restaurant_base_price' => floatval($_POST['restaurant_base_price'] ?? 300),
            'remorque_base_price' => floatval($_POST['remorque_base_price'] ?? 350),
            'hourly_supplement' => floatval($_POST['hourly_supplement'] ?? 50),
            'restaurant_min_guests' => intval($_POST['restaurant_min_guests'] ?? 10),
            'restaurant_max_guests' => intval($_POST['restaurant_max_guests'] ?? 30),
            'remorque_min_guests' => intval($_POST['remorque_min_guests'] ?? 20),
            'remorque_max_guests' => intval($_POST['remorque_max_guests'] ?? 100)
        );
        
        foreach ($settings_to_save as $key => $value) {
            $this->settings->set($key, $value, 'number', 'pricing');
        }
    }
    
    /**
     * Gérer la mise à jour de la base de données
     */
    public function handle_database_update() {
        if (!current_user_can('manage_options')) {
            wp_die('Accès non autorisé');
        }
        
        try {
            Block_Traiteur_Database::create_tables();
            Block_Traiteur_Database::seed_default_data();
            
            wp_redirect(admin_url('admin.php?page=block-traiteur&message=' . urlencode('Base de données mise à jour avec succès') . '&type=success'));
            exit;
        } catch (Exception $e) {
            wp_redirect(admin_url('admin.php?page=block-traiteur&message=' . urlencode('Erreur: ' . $e->getMessage()) . '&type=error'));
            exit;
        }
    }
    
    /**
     * Gérer la recréation complète de la base de données
     */
    public function handle_database_recreate() {
        if (!current_user_can('manage_options')) {
            wp_die('Accès non autorisé');
        }
        
        try {
            Block_Traiteur_Database::force_recreate();
            
            wp_redirect(admin_url('admin.php?page=block-traiteur&message=' . urlencode('Base de données recréée avec succès') . '&type=success'));
            exit;
        } catch (Exception $e) {
            wp_redirect(admin_url('admin.php?page=block-traiteur&message=' . urlencode('Erreur: ' . $e->getMessage()) . '&type=error'));
            exit;
        }
    }
}