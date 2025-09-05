<?php
/**
 * Classe d'administration des devis
 */

if (!defined('ABSPATH')) {
    exit;
}

class Block_Traiteur_Quotes_Admin {
    
    /**
     * Afficher la page des devis
     */
    public function display_page() {
        $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'list';
        
        switch ($action) {
            case 'view':
                $this->view_quote();
                break;
            case 'edit':
                $this->edit_quote();
                break;
            case 'delete':
                $this->delete_quote();
                break;
            default:
                $this->list_quotes();
                break;
        }
    }
    
    /**
     * Lister les devis
     */
    private function list_quotes() {
        // Gérer la pagination
        $current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        $per_page = 20;
        $offset = ($current_page - 1) * $per_page;
        
        // Gérer les filtres
        $status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
        $service_filter = isset($_GET['service']) ? sanitize_text_field($_GET['service']) : '';
        $search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
        
        global $wpdb;
        
        // Construire la requête WHERE
        $where_conditions = array('1=1');
        $where_params = array();
        
        if ($status_filter) {
            $where_conditions[] = 'status = %s';
            $where_params[] = $status_filter;
        }
        
        if ($service_filter) {
            $where_conditions[] = 'service_type = %s';
            $where_params[] = $service_filter;
        }
        
        if ($search) {
            $where_conditions[] = '(customer_name LIKE %s OR customer_email LIKE %s OR quote_number LIKE %s)';
            $where_params[] = '%' . $search . '%';
            $where_params[] = '%' . $search . '%';
            $where_params[] = '%' . $search . '%';
        }
        
        $where_clause = implode(' AND ', $where_conditions);
        
        // Compter le total
        $total_query = "SELECT COUNT(*) FROM {$wpdb->prefix}block_quotes WHERE {$where_clause}";
        if (!empty($where_params)) {
            $total_query = $wpdb->prepare($total_query, $where_params);
        }
        $total_items = $wpdb->get_var($total_query);
        
        // Récupérer les devis
        $quotes_query = "SELECT * FROM {$wpdb->prefix}block_quotes 
                        WHERE {$where_clause} 
                        ORDER BY created_at DESC 
                        LIMIT %d OFFSET %d";
        
        $query_params = array_merge($where_params, array($per_page, $offset));
        $quotes = $wpdb->get_results($wpdb->prepare($quotes_query, $query_params));
        
        // Calculer la pagination
        $total_pages = ceil($total_items / $per_page);
        
        include BLOCK_TRAITEUR_PLUGIN_DIR . 'admin/partials/quotes-list.php';
    }
    
    /**
     * Voir un devis
     */
    private function view_quote() {
        $quote_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        
        if (!$quote_id) {
            wp_die(__('ID de devis invalide', 'block-traiteur'));
        }
        
        $quote = Block_Traiteur_Database::get_quote_data($quote_id);
        
        if (!$quote) {
            wp_die(__('Devis non trouvé', 'block-traiteur'));
        }
        
        include BLOCK_TRAITEUR_PLUGIN_DIR . 'admin/partials/quote-view.php';
    }
    
    /**
     * Éditer un devis
     */
    private function edit_quote() {
        $quote_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        
        if (!$quote_id) {
            wp_die(__('ID de devis invalide', 'block-traiteur'));
        }
        
        // Traitement du formulaire
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_quote'])) {
            check_admin_referer('update_quote_' . $quote_id);
            
            $this->update_quote($quote_id);
        }
        
        $quote = Block_Traiteur_Database::get_quote_data($quote_id);
        
        if (!$quote) {
            wp_die(__('Devis non trouvé', 'block-traiteur'));
        }
        
        include BLOCK_TRAITEUR_PLUGIN_DIR . 'admin/partials/quote-edit.php';
    }
    
    /**
     * Mettre à jour un devis
     */
    private function update_quote($quote_id) {
        global $wpdb;
        
        $data = array(
            'customer_name' => sanitize_text_field($_POST['customer_name']),
            'customer_email' => sanitize_email($_POST['customer_email']),
            'customer_phone' => sanitize_text_field($_POST['customer_phone']),
            'event_date' => sanitize_text_field($_POST['event_date']),
            'event_duration' => intval($_POST['event_duration']),
            'guest_count' => intval($_POST['guest_count']),
            'event_location' => sanitize_text_field($_POST['event_location']),
            'postal_code' => sanitize_text_field($_POST['postal_code']),
            'total_price' => floatval($_POST['total_price']),
            'status' => sanitize_text_field($_POST['status']),
            'admin_notes' => sanitize_textarea_field($_POST['admin_notes'])
        );
        
        $result = $wpdb->update(
            $wpdb->prefix . 'block_quotes',
            $data,
            array('id' => $quote_id),
            array('%s', '%s', '%s', '%s', '%d', '%d', '%s', '%s', '%f', '%s', '%s'),
            array('%d')
        );
        
        if ($result !== false) {
            $redirect_url = add_query_arg(array(
                'page' => 'block-traiteur-quotes',
                'action' => 'view',
                'id' => $quote_id,
                'message' => urlencode(__('Devis mis à jour avec succès', 'block-traiteur')),
                'type' => 'success'
            ), admin_url('admin.php'));
            
            wp_redirect($redirect_url);
            exit;
        }
    }
    
    /**
     * Supprimer un devis
     */
    private function delete_quote() {
        $quote_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        
        if (!$quote_id) {
            wp_die(__('ID de devis invalide', 'block-traiteur'));
        }
        
        check_admin_referer('delete_quote_' . $quote_id);
        
        global $wpdb;
        
        // Supprimer les produits du devis
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
            $redirect_url = add_query_arg(array(
                'page' => 'block-traiteur-quotes',
                'message' => urlencode(__('Devis supprimé avec succès', 'block-traiteur')),
                'type' => 'success'
            ), admin_url('admin.php'));
        } else {
            $redirect_url = add_query_arg(array(
                'page' => 'block-traiteur-quotes',
                'message' => urlencode(__('Erreur lors de la suppression', 'block-traiteur')),
                'type' => 'error'
            ), admin_url('admin.php'));
        }
        
        wp_redirect($redirect_url);
        exit;
    }
}