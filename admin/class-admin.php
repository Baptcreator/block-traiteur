<?php
/**
 * Classe principale de l'interface administration
 */

if (!defined('ABSPATH')) {
    exit;
}

class Block_Traiteur_Admin {
    
    /**
     * Constructeur
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'admin_init'));
        add_action('admin_notices', array($this, 'admin_notices'));
    }
    
    /**
     * Ajouter le menu d'administration
     */
    public function add_admin_menu() {
        // Menu principal
        add_menu_page(
            __('Block Traiteur', 'block-traiteur'),
            __('Block Traiteur', 'block-traiteur'),
            'manage_options',
            'block-traiteur',
            array($this, 'dashboard_page'),
            'dashicons-food',
            30
        );
        
        // Tableau de bord
        add_submenu_page(
            'block-traiteur',
            __('Tableau de bord', 'block-traiteur'),
            __('Tableau de bord', 'block-traiteur'),
            'manage_options',
            'block-traiteur',
            array($this, 'dashboard_page')
        );
        
        // Devis
        add_submenu_page(
            'block-traiteur',
            __('Devis', 'block-traiteur'),
            __('Devis', 'block-traiteur'),
            'manage_options',
            'block-traiteur-quotes',
            array($this, 'quotes_page')
        );
        
        // Produits
        add_submenu_page(
            'block-traiteur',
            __('Produits', 'block-traiteur'),
            __('Produits', 'block-traiteur'),
            'manage_options',
            'block-traiteur-products',
            array($this, 'products_page')
        );
        
        // Boissons
        add_submenu_page(
            'block-traiteur',
            __('Boissons', 'block-traiteur'),
            __('Boissons', 'block-traiteur'),
            'manage_options',
            'block-traiteur-beverages',
            array($this, 'beverages_page')
        );
        
        // Disponibilités
        add_submenu_page(
            'block-traiteur',
            __('Disponibilités', 'block-traiteur'),
            __('Disponibilités', 'block-traiteur'),
            'manage_options',
            'block-traiteur-availability',
            array($this, 'availability_page')
        );
        
        // Paramètres
        add_submenu_page(
            'block-traiteur',
            __('Paramètres', 'block-traiteur'),
            __('Paramètres', 'block-traiteur'),
            'manage_options',
            'block-traiteur-settings',
            array($this, 'settings_page')
        );
        
        // Rapports
        add_submenu_page(
            'block-traiteur',
            __('Rapports', 'block-traiteur'),
            __('Rapports', 'block-traiteur'),
            'manage_options',
            'block-traiteur-reports',
            array($this, 'reports_page')
        );
    }
    
    /**
     * Initialisation admin
     */
    public function admin_init() {
        // Enregistrer les paramètres
        register_setting('block_traiteur_settings', 'block_traiteur_settings');
        
        // Ajouter les actions AJAX
        add_action('wp_ajax_block_traiteur_update_quote_status', array($this, 'ajax_update_quote_status'));
        add_action('wp_ajax_block_traiteur_delete_quote', array($this, 'ajax_delete_quote'));
        add_action('wp_ajax_block_traiteur_generate_pdf', array($this, 'ajax_generate_pdf'));
        add_action('wp_ajax_block_traiteur_send_email', array($this, 'ajax_send_email'));
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
     * Page tableau de bord
     */
    public function dashboard_page() {
        // Récupérer les statistiques
        $stats = $this->get_dashboard_stats();
        
        include BLOCK_TRAITEUR_PLUGIN_DIR . 'admin/partials/dashboard.php';
    }
    
    /**
     * Page des devis
     */
    public function quotes_page() {
        $quotes_admin = new Block_Traiteur_Quotes_Admin();
        $quotes_admin->display_page();
    }
    
    /**
     * Page des produits
     */
    public function products_page() {
        $products_admin = new Block_Traiteur_Products_Admin();
        $products_admin->display_page();
    }
    
    /**
     * Page des boissons
     */
    public function beverages_page() {
        $beverages_admin = new Block_Traiteur_Beverages_Admin();
        $beverages_admin->display_page();
    }
    
    /**
     * Page des disponibilités
     */
    public function availability_page() {
        include BLOCK_TRAITEUR_PLUGIN_DIR . 'admin/partials/availability.php';
    }
    
    /**
     * Page des paramètres
     */
    public function settings_page() {
        $settings_admin = new Block_Traiteur_Settings_Admin();
        $settings_admin->display_page();
    }
    
    /**
     * Page des rapports
     */
    public function reports_page() {
        include BLOCK_TRAITEUR_PLUGIN_DIR . 'admin/partials/reports.php';
    }
    
    /**
     * Obtenir les statistiques du tableau de bord
     */
    private function get_dashboard_stats() {
        global $wpdb;
        
        $stats = array();
        
        // Devis du mois
        $stats['quotes_this_month'] = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->prefix}block_quotes 
             WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) 
             AND YEAR(created_at) = YEAR(CURRENT_DATE())"
        );
        
        // Devis en attente
        $stats['pending_quotes'] = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->prefix}block_quotes WHERE status = 'sent'"
        );
        
        // CA estimé du mois
        $stats['revenue_estimate'] = $wpdb->get_var(
            "SELECT SUM(total_price) FROM {$wpdb->prefix}block_quotes 
             WHERE status IN ('accepted', 'sent')
             AND MONTH(created_at) = MONTH(CURRENT_DATE()) 
             AND YEAR(created_at) = YEAR(CURRENT_DATE())"
        );
        
        // Taux de conversion
        $total_quotes = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->prefix}block_quotes 
             WHERE status IN ('accepted', 'declined', 'expired')"
        );
        $accepted_quotes = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->prefix}block_quotes WHERE status = 'accepted'"
        );
        
        $stats['conversion_rate'] = $total_quotes > 0 ? round(($accepted_quotes / $total_quotes) * 100, 1) : 0;
        
        // Prochains événements
        $stats['upcoming_events'] = $wpdb->get_results(
            "SELECT quote_number, customer_name, event_date, service_type, guest_count 
             FROM {$wpdb->prefix}block_quotes 
             WHERE status = 'accepted' AND event_date >= CURRENT_DATE() 
             ORDER BY event_date ASC LIMIT 5"
        );
        
        // Devis récents
        $stats['recent_quotes'] = $wpdb->get_results(
            "SELECT id, quote_number, customer_name, service_type, total_price, status, created_at 
             FROM {$wpdb->prefix}block_quotes 
             ORDER BY created_at DESC LIMIT 10"
        );
        
        return $stats;
    }
    
    /**
     * AJAX - Mettre à jour le statut d'un devis
     */
    public function ajax_update_quote_status() {
        check_ajax_referer('block_traiteur_admin', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permissions insuffisantes');
        }
        
        $quote_id = intval($_POST['quote_id']);
        $new_status = sanitize_text_field($_POST['status']);
        
        global $wpdb;
        
        $result = $wpdb->update(
            $wpdb->prefix . 'block_quotes',
            array('status' => $new_status),
            array('id' => $quote_id),
            array('%s'),
            array('%d')
        );
        
        if ($result !== false) {
            wp_send_json_success('Statut mis à jour');
        } else {
            wp_send_json_error('Erreur lors de la mise à jour');
        }
    }
    
    /**
     * AJAX - Supprimer un devis
     */
    public function ajax_delete_quote() {
        check_ajax_referer('block_traiteur_admin', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permissions insuffisantes');
        }
        
        $quote_id = intval($_POST['quote_id']);
        
        global $wpdb;
        
        // Supprimer les produits du devis d'abord
        $wpdb->delete(
            $wpdb->prefix . 'block_quote_products',
            array('quote_id' => $quote_id),
            array('%d')
        );
        
        // Supprimer le devis
        $result = $wpdb->delete(
            $wpdb->prefix . 'block_quotes',
            array('id' => $quote_id),
            array('%d')
        );
        
        if ($result !== false) {
            wp_send_json_success('Devis supprimé');
        } else {
            wp_send_json_error('Erreur lors de la suppression');
        }
    }
    
    /**
     * AJAX - Générer un PDF
     */
    public function ajax_generate_pdf() {
        check_ajax_referer('block_traiteur_admin', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permissions insuffisantes');
        }
        
        $quote_id = intval($_POST['quote_id']);
        
        try {
            $pdf_generator = new Block_Traiteur_PDF_Generator();
            $pdf_path = $pdf_generator->generate_quote_pdf($quote_id);
            
            if ($pdf_path) {
                wp_send_json_success(array(
                    'message' => 'PDF généré avec succès',
                    'pdf_url' => wp_upload_dir()['baseurl'] . '/block-traiteur-quotes/' . basename($pdf_path)
                ));
            } else {
                wp_send_json_error('Erreur lors de la génération du PDF');
            }
        } catch (Exception $e) {
            wp_send_json_error('Erreur: ' . $e->getMessage());
        }
    }
    
    /**
     * AJAX - Envoyer un email
     */
    public function ajax_send_email() {
        check_ajax_referer('block_traiteur_admin', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permissions insuffisantes');
        }
        
        $quote_id = intval($_POST['quote_id']);
        $email_type = sanitize_text_field($_POST['email_type']);
        
        try {
            $mailer = new Block_Traiteur_Mailer();
            
            switch ($email_type) {
                case 'confirmation':
                    $result = $mailer->send_quote_confirmation($quote_id);
                    break;
                case 'reminder':
                    $result = $mailer->send_quote_reminder($quote_id);
                    break;
                default:
                    wp_send_json_error('Type d\'email invalide');
                    return;
            }
            
            if ($result) {
                wp_send_json_success('Email envoyé avec succès');
            } else {
                wp_send_json_error('Erreur lors de l\'envoi de l\'email');
            }
        } catch (Exception $e) {
            wp_send_json_error('Erreur: ' . $e->getMessage());
        }
    }
}