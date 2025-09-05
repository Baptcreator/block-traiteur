<?php
/**
 * Classe de calcul des prix pour Block Traiteur
 * Calculs selon les spécifications exactes du cahier des charges (ligne 473-513)
 *
 * @package Block_Traiteur
 * @subpackage Includes
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Classe Block_Traiteur_Calculator
 * 
 * Gère tous les calculs de prix selon les règles métier définies dans le cahier des charges
 */
class Block_Traiteur_Calculator {
    
    /**
     * @var Block_Traiteur_Settings Instance des settings
     */
    private $settings;
    
    /**
     * Constructeur
     */
    public function __construct() {
        $this->settings = Block_Traiteur_Settings::get_instance();
    }
    
    /**
     * Calculer le prix total d'un devis selon les spécifications (ligne 473-513)
     * 
     * @param string $service_type 'restaurant' ou 'remorque'
     * @param int $guest_count Nombre de convives
     * @param int $duration Durée en heures
     * @param string $postal_code Code postal pour calcul livraison (remorque)
     * @param array $products Produits sélectionnés avec quantités
     * @return array Détail complet du calcul
     */
    public function calculate_total_price($service_type, $guest_count, $duration, $postal_code = '', $products = array()) {
        $calculation = array(
            'service_type' => $service_type,
            'guest_count' => $guest_count,
            'duration' => $duration,
            'postal_code' => $postal_code
        );
        
        // 1. Prix de base (ligne 478-481)
        $base_price = $this->calculate_base_price($service_type);
        $calculation['base_price'] = $base_price;
        
        // 2. Supplément durée (ligne 483-487)
        $duration_supplement = $this->calculate_duration_supplement($service_type, $duration);
        $calculation['duration_supplement'] = $duration_supplement;
        
        // 3. Supplément convives - remorque uniquement (ligne 489-495)
        $guests_supplement = $this->calculate_guests_supplement($service_type, $guest_count);
        $calculation['guests_supplement'] = $guests_supplement;
        
        // 4. Frais de livraison - remorque uniquement (ligne 497-501)
        $delivery_cost = $this->calculate_delivery_cost($service_type, $postal_code);
        $calculation['delivery_cost'] = $delivery_cost;
        $calculation['distance_km'] = $this->get_distance_km($postal_code);
        
        // 5. Total produits (ligne 503-507)
        $products_calculation = $this->calculate_products_total($products);
        $calculation['products_total'] = $products_calculation['total'];
        $calculation['products_detail'] = $products_calculation['detail'];
        $calculation['supplements_total'] = $products_calculation['supplements'];
        
        // 6. Prix final (ligne 510-512)
        $calculation['subtotal'] = $base_price + $duration_supplement + $guests_supplement + $delivery_cost;
        $calculation['total_price'] = $calculation['subtotal'] + $calculation['products_total'] + $calculation['supplements_total'];
        
        // Formatage pour affichage
        $calculation['formatted'] = array(
            'base_price' => number_format($base_price, 2, ',', ' ') . ' €',
            'duration_supplement' => number_format($duration_supplement, 2, ',', ' ') . ' €',
            'guests_supplement' => number_format($guests_supplement, 2, ',', ' ') . ' €',
            'delivery_cost' => number_format($delivery_cost, 2, ',', ' ') . ' €',
            'products_total' => number_format($calculation['products_total'], 2, ',', ' ') . ' €',
            'supplements_total' => number_format($calculation['supplements_total'], 2, ',', ' ') . ' €',
            'subtotal' => number_format($calculation['subtotal'], 2, ',', ' ') . ' €',
            'total_price' => number_format($calculation['total_price'], 2, ',', ' ') . ' €'
        );
        
        return $calculation;
    }
    
    /**
     * Calculer le prix de base selon le service (ligne 478-481)
     * 
     * @param string $service_type
     * @return float Prix de base
     */
    private function calculate_base_price($service_type) {
        if ($service_type === 'restaurant') {
            return $this->settings->get('restaurant_base_price', 300);
        } else {
            return $this->settings->get('remorque_base_price', 350);
        }
    }
    
    /**
     * Calculer le supplément durée (ligne 483-487)
     * 
     * @param string $service_type
     * @param int $duration Durée souhaitée en heures
     * @return float Supplément durée
     */
    private function calculate_duration_supplement($service_type, $duration) {
        $included_hours = $service_type === 'restaurant' 
            ? $this->settings->get('restaurant_included_hours', 2)
            : $this->settings->get('remorque_included_hours', 2);
        
        $extra_hours = max(0, $duration - $included_hours);
        $hourly_rate = $this->settings->get('hourly_supplement', 50);
        
        return $extra_hours * $hourly_rate;
    }
    
    /**
     * Calculer le supplément convives pour remorque (ligne 489-495)
     * 
     * @param string $service_type
     * @param int $guest_count
     * @return float Supplément convives
     */
    private function calculate_guests_supplement($service_type, $guest_count) {
        if ($service_type !== 'remorque') {
            return 0;
        }
        
            if ($guest_count > 50) {
            return $this->settings->get('remorque_50_guests_supplement', 150);
        }
        
        return 0;
    }
    
    /**
     * Calculer les frais de livraison pour remorque (ligne 497-501)
     * 
     * @param string $service_type
     * @param string $postal_code
     * @return float Frais de livraison
     */
    private function calculate_delivery_cost($service_type, $postal_code) {
        if ($service_type !== 'remorque' || empty($postal_code)) {
            return 0;
        }
        
        $distance = $this->get_distance_km($postal_code);
        return $this->get_delivery_price_by_distance($distance);
    }
    
    /**
     * Obtenir la distance en km depuis le restaurant
     * 
     * @param string $postal_code
     * @return int Distance en km
     */
    private function get_distance_km($postal_code) {
        if (empty($postal_code)) {
            return 0;
        }
        
        // Simulation simple basée sur les départements
        $department = substr($postal_code, 0, 2);
        $restaurant_dept = substr($this->settings->get('restaurant_postal_code', '67000'), 0, 2);
        
        if ($department === $restaurant_dept) {
            // Même département - distance aléatoire 0-30km
            return rand(0, 30);
        }
        
        // Départements limitrophes Grand Est
        $nearby_depts = array('68', '54', '57', '88', '52', '51', '08', '55');
        if (in_array($department, $nearby_depts)) {
            return rand(30, 120);
        }
        
        // Plus loin
        return rand(120, 500);
    }
    
    /**
     * Obtenir le prix de livraison selon la distance
     * 
     * @param int $distance_km Distance en kilomètres
     * @return float Prix de livraison
     */
    private function get_delivery_price_by_distance($distance_km) {
        global $wpdb;
        $table = $wpdb->prefix . 'restaurant_delivery_zones';
        
        $zone = $wpdb->get_row($wpdb->prepare(
            "SELECT delivery_price FROM {$table} 
             WHERE %d >= distance_min AND %d <= distance_max AND is_active = 1
             ORDER BY display_order LIMIT 1",
            $distance_km,
            $distance_km
        ));
        
        return $zone ? (float)$zone->delivery_price : 0;
    }
    
    /**
     * Calculer le total des produits sélectionnés (ligne 503-507)
     * 
     * @param array $products Produits avec quantités
     * @return array Détail du calcul produits
     */
    private function calculate_products_total($products) {
        $result = array(
            'total' => 0,
            'supplements' => 0,
            'detail' => array()
        );
        
        if (empty($products) || !is_array($products)) {
            return $result;
        }
        
        global $wpdb;
        $products_table = $wpdb->prefix . 'restaurant_products';
        
        foreach ($products as $product_item) {
            if (!isset($product_item['id']) || !isset($product_item['quantity'])) {
                continue;
            }
            
            $product_id = intval($product_item['id']);
            $quantity = intval($product_item['quantity']);
            $with_supplement = isset($product_item['with_supplement']) ? (bool)$product_item['with_supplement'] : false;
            
            if ($quantity <= 0) {
                continue;
            }
            
            // Récupérer les données du produit
            $product = $wpdb->get_row($wpdb->prepare(
                "SELECT name, price, supplement_name, supplement_price, unit_label 
                 FROM {$products_table} 
                 WHERE id = %d AND is_active = 1",
                $product_id
            ));
            
            if (!$product) {
                continue;
            }
            
            // Calcul prix produit
            $product_total = $product->price * $quantity;
            $result['total'] += $product_total;
            
            // Calcul supplément si sélectionné
            $supplement_total = 0;
            if ($with_supplement && $product->supplement_price > 0) {
                $supplement_total = $product->supplement_price * $quantity;
                $result['supplements'] += $supplement_total;
            }
            
            // Ajouter au détail
            $result['detail'][] = array(
                'id' => $product_id,
                'name' => $product->name,
                'quantity' => $quantity,
                'unit_price' => $product->price,
                'unit_label' => $product->unit_label,
                'subtotal' => $product_total,
                'supplement_name' => $product->supplement_name,
                'supplement_price' => $product->supplement_price,
                'supplement_total' => $supplement_total,
                'with_supplement' => $with_supplement,
                'total' => $product_total + $supplement_total
            );
        }
        
        return $result;
    }
    
    /**
     * Valider les contraintes de convives selon les spécifications
     * 
     * @param string $service_type
     * @param int $guest_count
     * @return array Résultat de validation
     */
    public function validate_guest_count($service_type, $guest_count) {
        $pricing = $this->settings->get_pricing_settings();
        
        if ($service_type === 'restaurant') {
            $min = $pricing['restaurant_min_guests'];
            $max = $pricing['restaurant_max_guests'];
        } else {
            $min = $pricing['remorque_min_guests'];
            $max = $pricing['remorque_max_guests'];
        }
        
        return array(
            'valid' => ($guest_count >= $min && $guest_count <= $max),
            'min' => $min,
            'max' => $max,
            'message' => $guest_count < $min 
                ? "Minimum {$min} convives requis" 
                : ($guest_count > $max ? "Maximum {$max} convives autorisés" : '')
        );
    }
    
    /**
     * Valider les contraintes de durée
     * 
     * @param string $service_type
     * @param int $duration
     * @return array Résultat de validation
     */
    public function validate_duration($service_type, $duration) {
        $pricing = $this->settings->get_pricing_settings();
        
        $max = $service_type === 'restaurant' 
            ? $pricing['restaurant_max_hours']
            : $pricing['remorque_max_hours'];
        
        return array(
            'valid' => ($duration > 0 && $duration <= $max),
            'max' => $max,
            'message' => $duration > $max ? "Maximum {$max} heures autorisées" : ''
        );
    }
    
    /**
     * Obtenir un estimé rapide pour affichage
     * 
     * @param string $service_type
     * @param int $guest_count
     * @param int $duration
     * @return array Estimé simple
     */
    public function get_quick_estimate($service_type, $guest_count = null, $duration = null) {
        $base_price = $this->calculate_base_price($service_type);
        
        $estimate = array(
            'base_price' => $base_price,
            'formatted_base' => number_format($base_price, 0, ',', ' ') . ' €'
        );
        
        if ($duration !== null) {
            $duration_supplement = $this->calculate_duration_supplement($service_type, $duration);
            $estimate['with_duration'] = $base_price + $duration_supplement;
            $estimate['formatted_with_duration'] = number_format($estimate['with_duration'], 0, ',', ' ') . ' €';
        }
        
        if ($guest_count !== null) {
            $guests_supplement = $this->calculate_guests_supplement($service_type, $guest_count);
            $with_guests = $base_price + ($duration_supplement ?? 0) + $guests_supplement;
            $estimate['with_guests'] = $with_guests;
            $estimate['formatted_with_guests'] = number_format($with_guests, 0, ',', ' ') . ' €';
        }
        
        return $estimate;
    }
}