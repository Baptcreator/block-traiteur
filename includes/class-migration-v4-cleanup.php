<?php
/**
 * Migration v4 - Cleanup des données
 */

if (!defined('ABSPATH')) {
    exit;
}

class RestaurantBooking_Migration_V4_Cleanup {
    
    /**
     * Exécuter la migration
     */
    public static function run() {
        global $wpdb;
        
        // Cette migration fait du nettoyage si nécessaire
        $cleanup_performed = false;
        
        // Nettoyer les doublons de types de bières
        if (self::cleanup_duplicate_beer_types()) {
            $cleanup_performed = true;
        }
        
        return $cleanup_performed;
    }
    
    /**
     * Nettoyer les doublons de types de bières
     */
    private static function cleanup_duplicate_beer_types() {
        global $wpdb;
        
        $beer_types_table = $wpdb->prefix . 'restaurant_beer_types';
        
        // Vérifier si la table existe
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$beer_types_table'");
        
        if (!$table_exists) {
            return false; // Pas de table, pas de nettoyage nécessaire
        }
        
        // Trouver les doublons de slug pour les types de bières
        $duplicates = $wpdb->get_results("
            SELECT slug, COUNT(*) as count, GROUP_CONCAT(id ORDER BY id) as ids
            FROM $beer_types_table
            WHERE is_active = 1
            GROUP BY slug
            HAVING COUNT(*) > 1
        ");
        
        $cleaned_count = 0;
        
        foreach ($duplicates as $duplicate) {
            // Garder le premier ID et supprimer les autres
            $ids_to_keep = explode(',', $duplicate->ids);
            $id_to_keep = array_shift($ids_to_keep); // Garder le premier
            
            if (!empty($ids_to_keep)) {
                // Supprimer les doublons
                $wpdb->query($wpdb->prepare("
                    DELETE FROM $beer_types_table 
                    WHERE id IN (" . implode(',', array_map('intval', $ids_to_keep)) . ")
                "));
                $cleaned_count += count($ids_to_keep);
                
                error_log("🧹 Migration V4: Nettoyé " . count($ids_to_keep) . " doublons pour le type de bière '$duplicate->slug'");
            }
        }
        
        return $cleaned_count > 0;
    }
    
    /**
     * Vérifier si la migration est nécessaire
     */
    public static function is_needed() {
        // Pour l'instant, la migration n'est pas nécessaire
        return false;
    }
    
    /**
     * Alias pour is_needed() - méthode appelée dans le plugin principal
     */
    public static function needs_migration() {
        return self::is_needed();
    }
}
?>