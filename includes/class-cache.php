<?php
/**
 * Classe de gestion du cache
 */

if (!defined('ABSPATH')) {
    exit;
}

class Block_Traiteur_Cache {
    
    private static $cache_group = 'block_traiteur';
    
    /**
     * Initialiser le cache
     */
    public static function init() {
        // Ajouter des hooks pour vider le cache lors des modifications
        add_action('block_traiteur_product_updated', array(__CLASS__, 'clear_products_cache'));
        add_action('block_traiteur_beverage_updated', array(__CLASS__, 'clear_beverages_cache'));
        add_action('block_traiteur_settings_updated', array(__CLASS__, 'clear_settings_cache'));
    }
    
    /**
     * Obtenir les produits avec cache
     */
    public static function get_products($service_type = 'both') {
        $cache_key = 'products_' . $service_type;
        $products = wp_cache_get($cache_key, self::$cache_group);
        
        if ($products === false) {
            global $wpdb;
            
            $where_clause = '';
            if ($service_type !== 'both') {
                $where_clause = $wpdb->prepare("AND (service_type = %s OR service_type = 'both')", $service_type);
            }
            
            $products = $wpdb->get_results(
                "SELECT p.*, c.name as category_name, c.type as category_type
                FROM {$wpdb->prefix}block_products p
                LEFT JOIN {$wpdb->prefix}block_food_categories c ON p.category_id = c.id
                WHERE p.is_active = 1 {$where_clause}
                ORDER BY c.sort_order, p.sort_order"
            );
            
            wp_cache_set($cache_key, $products, self::$cache_group, HOUR_IN_SECONDS);
        }
        
        return $products;
    }
    
    /**
     * Obtenir les boissons avec cache
     */
    public static function get_beverages($service_type = 'both') {
        $cache_key = 'beverages_' . $service_type;
        $beverages = wp_cache_get($cache_key, self::$cache_group);
        
        if ($beverages === false) {
            global $wpdb;
            
            $where_clause = '';
            if ($service_type !== 'both') {
                $where_clause = $wpdb->prepare("AND (service_type = %s OR service_type = 'both')", $service_type);
            }
            
            $beverages = $wpdb->get_results(
                "SELECT b.*, c.name as category_name, c.type as category_type
                FROM {$wpdb->prefix}block_beverages b
                LEFT JOIN {$wpdb->prefix}block_beverage_categories c ON b.category_id = c.id
                WHERE b.is_active = 1 {$where_clause}
                ORDER BY c.sort_order, b.sort_order"
            );
            
            wp_cache_set($cache_key, $beverages, self::$cache_group, HOUR_IN_SECONDS);
        }
        
        return $beverages;
    }
    
    /**
 * Obtenir les paramètres avec cache
 */
public static function get_settings() {
    $settings = wp_cache_get('settings', self::$cache_group);
    
    if ($settings === false) {
        // Utiliser les options WordPress au lieu de la table custom
        $settings = array(
            'restaurant_base_price' => get_option('block_traiteur_base_price_restaurant', 25),
            'remorque_base_price' => get_option('block_traiteur_base_price_remorque', 20),
            'company_phone' => get_option('block_traiteur_company_phone', '06 58 13 38 05'),
            'company_email' => get_option('block_traiteur_company_email', 'contact@block-strasbourg.fr'),
            'company_name' => get_option('block_traiteur_company_name', 'Block Street Food'),
            'company_address' => get_option('block_traiteur_company_address', 'Strasbourg')
        );
        
        wp_cache_set('settings', $settings, self::$cache_group, DAY_IN_SECONDS);
    }
    
    return $settings;
}
    
    /**
     * Vider le cache des produits
     */
    public static function clear_products_cache() {
        wp_cache_delete('products_restaurant', self::$cache_group);
        wp_cache_delete('products_remorque', self::$cache_group);
        wp_cache_delete('products_both', self::$cache_group);
    }
    
    /**
     * Vider le cache des boissons
     */
    public static function clear_beverages_cache() {
        wp_cache_delete('beverages_restaurant', self::$cache_group);
        wp_cache_delete('beverages_remorque', self::$cache_group);
        wp_cache_delete('beverages_both', self::$cache_group);
    }
    
    /**
     * Vider le cache des paramètres
     */
    public static function clear_settings_cache() {
        wp_cache_delete('settings', self::$cache_group);
    }
    
    /**
     * Vider tout le cache
     */
    public static function clear_all_cache() {
        wp_cache_flush_group(self::$cache_group);
    }
}