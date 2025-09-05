<?php
/**
 * Classe d'administration des boissons
 */

if (!defined('ABSPATH')) {
    exit;
}

class Block_Traiteur_Beverages_Admin {
    
    /**
     * Afficher la page des boissons
     */
    public function display_page() {
        $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'list';
        
        switch ($action) {
            case 'add':
                $this->add_beverage();
                break;
            case 'edit':
                $this->edit_beverage();
                break;
            case 'delete':
                $this->delete_beverage();
                break;
            case 'categories':
                $this->manage_categories();
                break;
            default:
                $this->list_beverages();
                break;
        }
    }
    
    /**
     * Lister les boissons
     */
    private function list_beverages() {
        global $wpdb;
        
        // Récupérer les boissons avec leurs catégories
        $beverages = $wpdb->get_results(
            "SELECT b.*, c.name as category_name, c.slug as category_slug
             FROM {$wpdb->prefix}block_beverages b
             LEFT JOIN {$wpdb->prefix}block_beverage_categories c ON b.category_id = c.id
             ORDER BY c.sort_order ASC, b.sort_order ASC"
        );
        
        // Récupérer les catégories pour le filtre
        $categories = $wpdb->get_results(
            "SELECT * FROM {$wpdb->prefix}block_beverage_categories ORDER BY sort_order"
        );
        
        include BLOCK_TRAITEUR_PLUGIN_DIR . 'admin/partials/beverages-list.php';
    }
    
    /**
     * Ajouter une boisson
     */
    private function add_beverage() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_beverage'])) {
            check_admin_referer('add_beverage');
            
            $this->save_beverage();
        }
        
        $categories = $this->get_categories();
        $beverage = null; // Nouvelle boisson
        
        include BLOCK_TRAITEUR_PLUGIN_DIR . 'admin/partials/beverage-form.php';
    }
    
    /**
     * Éditer une boisson
     */
    private function edit_beverage() {
        $beverage_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        
        if (!$beverage_id) {
            wp_die(__('ID de boisson invalide', 'block-traiteur'));
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_beverage'])) {
            check_admin_referer('update_beverage_' . $beverage_id);
            
            $this->save_beverage($beverage_id);
        }
        
        global $wpdb;
        $beverage = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}block_beverages WHERE id = %d",
            $beverage_id
        ));
        
        if (!$beverage) {
            wp_die(__('Boisson non trouvée', 'block-traiteur'));
        }
        
        $categories = $this->get_categories();
        
        include BLOCK_TRAITEUR_PLUGIN_DIR . 'admin/partials/beverage-form.php';
    }
    
    /**
     * Sauvegarder une boisson
     */
    private function save_beverage($beverage_id = null) {
        global $wpdb;
        
        // Convertir le volume en ml
        $volume = sanitize_text_field($_POST['volume']);
        $volume_ml = $this->convert_volume_to_ml($volume);
        
        $data = array(
            'name' => sanitize_text_field($_POST['name']),
            'category_id' => intval($_POST['category_id']),
            'price' => floatval($_POST['price']),
            'volume' => $volume,
            'volume_ml' => $volume_ml,
            'alcohol_degree' => floatval($_POST['alcohol_degree']),
            'container_type' => sanitize_text_field($_POST['container_type']),
            'description' => sanitize_textarea_field($_POST['description']),
            'origin' => sanitize_text_field($_POST['origin']),
            'image_url' => esc_url_raw($_POST['image_url']),
            'sort_order' => intval($_POST['sort_order']),
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
            'service_type' => sanitize_text_field($_POST['service_type'])
        );
        
        $format = array('%s', '%d', '%f', '%s', '%d', '%f', '%s', '%s', '%s', '%s', '%d', '%d', '%s');
        
        if ($beverage_id) {
            // Mise à jour
            $result = $wpdb->update(
                $wpdb->prefix . 'block_beverages',
                $data,
                array('id' => $beverage_id),
                $format,
                array('%d')
            );
            
            $message = __('Boisson mise à jour avec succès', 'block-traiteur');
        } else {
            // Création
            $result = $wpdb->insert(
                $wpdb->prefix . 'block_beverages',
                $data,
                $format
            );
            
            $beverage_id = $wpdb->insert_id;
            $message = __('Boisson créée avec succès', 'block-traiteur');
        }
        
        if ($result !== false) {
            // Vider le cache des boissons
            Block_Traiteur_Cache::clear_beverages_cache();
            
            // Déclencher l'action pour les hooks
            do_action('block_traiteur_beverage_updated', $beverage_id);
            
            $redirect_url = add_query_arg(array(
                'page' => 'block-traiteur-beverages',
                'message' => urlencode($message),
                'type' => 'success'
            ), admin_url('admin.php'));
            
            wp_redirect($redirect_url);
            exit;
        } else {
            $error_message = __('Erreur lors de la sauvegarde de la boisson', 'block-traiteur');
            wp_die($error_message);
        }
    }
    
    /**
     * Convertir un volume en millilitres
     */
    private function convert_volume_to_ml($volume_string) {
        // Extraire le nombre et l'unité
        preg_match('/(\d+(?:\.\d+)?)\s*(ml|cl|l|L)?/i', $volume_string, $matches);
        
        if (empty($matches[1])) {
            return 0;
        }
        
        $number = floatval($matches[1]);
        $unit = isset($matches[2]) ? strtolower($matches[2]) : 'ml';
        
        switch ($unit) {
            case 'l':
                return $number * 1000;
            case 'cl':
                return $number * 10;
            case 'ml':
            default:
                return $number;
        }
    }
    
    /**
     * Supprimer une boisson
     */
    private function delete_beverage() {
        $beverage_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        
        if (!$beverage_id) {
            wp_die(__('ID de boisson invalide', 'block-traiteur'));
        }
        
        check_admin_referer('delete_beverage_' . $beverage_id);
        
        global $wpdb;
        
        $result = $wpdb->delete(
            $wpdb->prefix . 'block_beverages',
            array('id' => $beverage_id),
            array('%d')
        );
        
        if ($result !== false) {
            Block_Traiteur_Cache::clear_beverages_cache();
            
            $redirect_url = add_query_arg(array(
                'page' => 'block-traiteur-beverages',
                'message' => urlencode(__('Boisson supprimée avec succès', 'block-traiteur')),
                'type' => 'success'
            ), admin_url('admin.php'));
        } else {
            $redirect_url = add_query_arg(array(
                'page' => 'block-traiteur-beverages',
                'message' => urlencode(__('Erreur lors de la suppression', 'block-traiteur')),
                'type' => 'error'
            ), admin_url('admin.php'));
        }
        
        wp_redirect($redirect_url);
        exit;
    }
    
    /**
     * Obtenir les catégories de boissons
     */
    private function get_categories() {
        global $wpdb;
        
        return $wpdb->get_results(
            "SELECT * FROM {$wpdb->prefix}block_beverage_categories ORDER BY sort_order"
        );
    }
    
    /**
     * Gérer les catégories de boissons
     */
    private function manage_categories() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['add_category'])) {
                check_admin_referer('add_beverage_category');
                $this->save_category();
            }
        }
        
        $categories = $this->get_categories();
        
        include BLOCK_TRAITEUR_PLUGIN_DIR . 'admin/partials/beverage-categories.php';
    }
    
    /**
     * Sauvegarder une catégorie de boisson
     */
    private function save_category() {
        global $wpdb;
        
        $data = array(
            'name' => sanitize_text_field($_POST['category_name']),
            'slug' => sanitize_title($_POST['category_slug']),
            'type' => sanitize_text_field($_POST['category_type']),
            'description' => sanitize_textarea_field($_POST['category_description']),
            'sort_order' => intval($_POST['sort_order']),
            'service_type' => sanitize_text_field($_POST['service_type'])
        );
        
        $result = $wpdb->insert(
            $wpdb->prefix . 'block_beverage_categories',
            $data,
            array('%s', '%s', '%s', '%s', '%d', '%s')
        );
        
        if ($result !== false) {
            Block_Traiteur_Cache::clear_beverages_cache();
        }
    }
}