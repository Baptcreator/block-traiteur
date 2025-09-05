<?php
/**
 * Classe d'administration des produits
 */

if (!defined('ABSPATH')) {
    exit;
}

class Block_Traiteur_Products_Admin {
    
    /**
     * Afficher la page des produits
     */
    public function display_page() {
        $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'list';
        
        switch ($action) {
            case 'add':
                $this->add_product();
                break;
            case 'edit':
                $this->edit_product();
                break;
            case 'delete':
                $this->delete_product();
                break;
            case 'categories':
                $this->manage_categories();
                break;
            default:
                $this->list_products();
                break;
        }
    }
    
    /**
     * Lister les produits
     */
    private function list_products() {
        global $wpdb;
        
        // Récupérer les produits avec leurs catégories
        $products = $wpdb->get_results(
            "SELECT p.*, c.name as category_name, c.slug as category_slug
             FROM {$wpdb->prefix}block_products p
             LEFT JOIN {$wpdb->prefix}block_food_categories c ON p.category_id = c.id
             ORDER BY c.sort_order ASC, p.sort_order ASC"
        );
        
        // Récupérer les catégories pour le filtre
        $categories = $wpdb->get_results(
            "SELECT * FROM {$wpdb->prefix}block_food_categories ORDER BY sort_order"
        );
        
        include BLOCK_TRAITEUR_PLUGIN_DIR . 'admin/partials/products-list.php';
    }
    
    /**
     * Ajouter un produit
     */
    private function add_product() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
            check_admin_referer('add_product');
            
            $this->save_product();
        }
        
        $categories = $this->get_categories();
        $product = null; // Nouveau produit
        
        include BLOCK_TRAITEUR_PLUGIN_DIR . 'admin/partials/product-form.php';
    }
    
    /**
     * Éditer un produit
     */
    private function edit_product() {
        $product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        
        if (!$product_id) {
            wp_die(__('ID de produit invalide', 'block-traiteur'));
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_product'])) {
            check_admin_referer('update_product_' . $product_id);
            
            $this->save_product($product_id);
        }
        
        global $wpdb;
        $product = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}block_products WHERE id = %d",
            $product_id
        ));
        
        if (!$product) {
            wp_die(__('Produit non trouvé', 'block-traiteur'));
        }
        
        $categories = $this->get_categories();
        
        include BLOCK_TRAITEUR_PLUGIN_DIR . 'admin/partials/product-form.php';
    }
    
    /**
     * Sauvegarder un produit
     */
    private function save_product($product_id = null) {
        global $wpdb;
        
        $data = array(
            'name' => sanitize_text_field($_POST['name']),
            'category_id' => intval($_POST['category_id']),
            'price' => floatval($_POST['price']),
            'unit' => sanitize_text_field($_POST['unit']),
            'min_quantity' => intval($_POST['min_quantity']),
            'max_quantity' => $_POST['max_quantity'] ? intval($_POST['max_quantity']) : null,
            'description' => sanitize_textarea_field($_POST['description']),
            'ingredients' => sanitize_textarea_field($_POST['ingredients']),
            'allergens' => sanitize_text_field($_POST['allergens']),
            'image_url' => esc_url_raw($_POST['image_url']),
            'sort_order' => intval($_POST['sort_order']),
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
            'service_type' => sanitize_text_field($_POST['service_type'])
        );
        
        $format = array('%s', '%d', '%f', '%s', '%d', '%d', '%s', '%s', '%s', '%s', '%d', '%d', '%s');
        
        if ($product_id) {
            // Mise à jour
            $result = $wpdb->update(
                $wpdb->prefix . 'block_products',
                $data,
                array('id' => $product_id),
                $format,
                array('%d')
            );
            
            $message = __('Produit mis à jour avec succès', 'block-traiteur');
        } else {
            // Création
            $result = $wpdb->insert(
                $wpdb->prefix . 'block_products',
                $data,
                $format
            );
            
            $product_id = $wpdb->insert_id;
            $message = __('Produit créé avec succès', 'block-traiteur');
        }
        
        if ($result !== false) {
            // Vider le cache des produits
            Block_Traiteur_Cache::clear_products_cache();
            
            // Déclencher l'action pour les hooks
            do_action('block_traiteur_product_updated', $product_id);
            
            $redirect_url = add_query_arg(array(
                'page' => 'block-traiteur-products',
                'message' => urlencode($message),
                'type' => 'success'
            ), admin_url('admin.php'));
            
            wp_redirect($redirect_url);
            exit;
        } else {
            $error_message = __('Erreur lors de la sauvegarde du produit', 'block-traiteur');
            wp_die($error_message);
        }
    }
    
    /**
     * Supprimer un produit
     */
    private function delete_product() {
        $product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        
        if (!$product_id) {
            wp_die(__('ID de produit invalide', 'block-traiteur'));
        }
        
        check_admin_referer('delete_product_' . $product_id);
        
        global $wpdb;
        
        $result = $wpdb->delete(
            $wpdb->prefix . 'block_products',
            array('id' => $product_id),
            array('%d')
        );
        
        if ($result !== false) {
            Block_Traiteur_Cache::clear_products_cache();
            
            $redirect_url = add_query_arg(array(
                'page' => 'block-traiteur-products',
                'message' => urlencode(__('Produit supprimé avec succès', 'block-traiteur')),
                'type' => 'success'
            ), admin_url('admin.php'));
        } else {
            $redirect_url = add_query_arg(array(
                'page' => 'block-traiteur-products',
                'message' => urlencode(__('Erreur lors de la suppression', 'block-traiteur')),
                'type' => 'error'
            ), admin_url('admin.php'));
        }
        
        wp_redirect($redirect_url);
        exit;
    }
    
    /**
     * Gérer les catégories
     */
    private function manage_categories() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['add_category'])) {
                check_admin_referer('add_category');
                $this->save_category();
            } elseif (isset($_POST['update_category'])) {
                check_admin_referer('update_category');
                $this->update_category();
            }
        }
        
        global $wpdb;
        $categories = $wpdb->get_results(
            "SELECT * FROM {$wpdb->prefix}block_food_categories ORDER BY sort_order"
        );
        
        include BLOCK_TRAITEUR_PLUGIN_DIR . 'admin/partials/categories-manage.php';
    }
    
    /**
     * Obtenir les catégories
     */
    private function get_categories() {
        global $wpdb;
        
        return $wpdb->get_results(
            "SELECT * FROM {$wpdb->prefix}block_food_categories ORDER BY sort_order"
        );
    }
    
    /**
     * Sauvegarder une catégorie
     */
    private function save_category() {
        global $wpdb;
        
        $data = array(
            'name' => sanitize_text_field($_POST['category_name']),
            'slug' => sanitize_title($_POST['category_slug']),
            'type' => sanitize_text_field($_POST['category_type']),
            'description' => sanitize_textarea_field($_POST['category_description']),
            'is_required' => isset($_POST['is_required']) ? 1 : 0,
            'min_selection' => intval($_POST['min_selection']),
            'max_selection' => $_POST['max_selection'] ? intval($_POST['max_selection']) : null,
            'sort_order' => intval($_POST['sort_order']),
            'service_type' => sanitize_text_field($_POST['service_type'])
        );
        
        $result = $wpdb->insert(
            $wpdb->prefix . 'block_food_categories',
            $data,
            array('%s', '%s', '%s', '%s', '%d', '%d', '%d', '%d', '%s')
        );
        
        if ($result !== false) {
            Block_Traiteur_Cache::clear_products_cache();
        }
    }
}