<?php
/**
 * Classe de calcul des prix
 */

if (!defined('ABSPATH')) {
    exit;
}

class Block_Traiteur_Calculator {
    
    private $form_data = array();
    private $settings = array();
    private $price_breakdown = array(
        'base' => 0,
        'duration' => 0,
        'guests' => 0,
        'distance' => 0,
        'products' => 0,
        'beverages' => 0,
        'options' => 0
    );
    
    /**
     * Constructeur
     */
    public function __construct() {
        $this->settings = Block_Traiteur_Cache::get_settings();
    }
    
    /**
     * Définir les données du formulaire
     */
    public function set_form_data($form_data) {
        $this->form_data = $form_data;
        $this->calculate_all();
    }
    
    /**
     * Calculer tous les prix
     */
    private function calculate_all() {
        $this->calculate_base_price();
        $this->calculate_duration_supplement();
        $this->calculate_guest_supplement();
        $this->calculate_distance_supplement();
        $this->calculate_products_price();
        $this->calculate_beverages_price();
        $this->calculate_options_price();
    }
    
    /**
     * Calculer le prix de base
     */
    private function calculate_base_price() {
        $service_type = $this->form_data['serviceType'] ?? 'restaurant';
        
        if ($service_type === 'restaurant') {
            $this->price_breakdown['base'] = $this->settings['restaurant_base_price'];
        } else {
            $this->price_breakdown['base'] = $this->settings['remorque_base_price'];
        }
    }
    
    /**
     * Calculer le supplément durée
     */
    private function calculate_duration_supplement() {
        $duration = intval($this->form_data['duration'] ?? 2);
        $service_type = $this->form_data['serviceType'] ?? 'restaurant';
        
        $min_duration = $service_type === 'restaurant' 
            ? $this->settings['restaurant_min_duration'] 
            : $this->settings['remorque_min_duration'];
        
        if ($duration > $min_duration) {
            $extra_hours = $duration - $min_duration;
            $this->price_breakdown['duration'] = $extra_hours * $this->settings['hour_supplement'];
        } else {
            $this->price_breakdown['duration'] = 0;
        }
    }
    
    /**
     * Calculer le supplément invités
     */
    private function calculate_guest_supplement() {
        $guest_count = intval($this->form_data['guestCount'] ?? 0);
        $service_type = $this->form_data['serviceType'] ?? 'restaurant';
        
        // Supplément uniquement pour la remorque au-delà de 50 invités
        if ($service_type === 'remorque' && $guest_count > $this->settings['remorque_guest_supplement_threshold']) {
            $this->price_breakdown['guests'] = $this->settings['remorque_guest_supplement'];
        } else {
            $this->price_breakdown['guests'] = 0;
        }
    }
    
    /**
     * Calculer le supplément distance
     */
    private function calculate_distance_supplement() {
        if (!isset($this->form_data['distance']) || $this->form_data['serviceType'] !== 'remorque') {
            $this->price_breakdown['distance'] = 0;
            return;
        }
        
        $distance = floatval($this->form_data['distance']);
        
        if ($distance <= $this->settings['delivery_zone_1_max']) {
            $this->price_breakdown['distance'] = 0;
        } elseif ($distance <= $this->settings['delivery_zone_2_max']) {
            $this->price_breakdown['distance'] = $this->settings['delivery_zone_2_price'];
        } elseif ($distance <= $this->settings['delivery_zone_3_max']) {
            $this->price_breakdown['distance'] = $this->settings['delivery_zone_3_price'];
        } elseif ($distance <= $this->settings['delivery_zone_4_max']) {
            $this->price_breakdown['distance'] = $this->settings['delivery_zone_4_price'];
        } else {
            // Hors zone - prix sur devis
            $this->price_breakdown['distance'] = 0;
        }
    }
    
    /**
     * Calculer le prix des produits
     */
    private function calculate_products_price() {
        $total = 0;
        
        if (!empty($this->form_data['selectedProducts'])) {
            foreach ($this->form_data['selectedProducts'] as $product) {
                $quantity = intval($product['quantity'] ?? 0);
                $price = floatval($product['price'] ?? 0);
                $total += $quantity * $price;
            }
        }
        
        $this->price_breakdown['products'] = $total;
    }
    
    /**
     * Calculer le prix des boissons
     */
    private function calculate_beverages_price() {
        $total = 0;
        
        if (!empty($this->form_data['selectedBeverages'])) {
            foreach ($this->form_data['selectedBeverages'] as $beverage) {
                $quantity = intval($beverage['quantity'] ?? 0);
                $price = floatval($beverage['price'] ?? 0);
                $total += $quantity * $price;
            }
        }
        
        $this->price_breakdown['beverages'] = $total;
    }
    
    /**
     * Calculer le prix des options
     */
    private function calculate_options_price() {
        $total = 0;
        
        if (!empty($this->form_data['selectedOptions'])) {
            foreach ($this->form_data['selectedOptions'] as $option) {
                switch ($option) {
                    case 'tireuse':
                        $total += $this->settings['tireuse_option_price'];
                        break;
                    case 'jeux':
                        $total += $this->settings['jeux_option_price'];
                        break;
                }
            }
        }
        
        $this->price_breakdown['options'] = $total;
    }
    
    /**
     * Obtenir le prix total
     */
    public function get_total_price() {
        return array_sum($this->price_breakdown);
    }
    
    /**
     * Obtenir le détail des prix
     */
    public function get_price_breakdown() {
        return $this->price_breakdown;
    }
    
    /**
     * Obtenir le prix formaté
     */
    public function get_formatted_total() {
        return number_format($this->get_total_price(), 2) . ' € TTC';
    }
    
    /**
     * Calculer une estimation rapide pour AJAX
     */
    public static function quick_estimate($service_type, $guest_count, $duration, $distance = 0) {
        $settings = Block_Traiteur_Cache::get_settings();
        $total = 0;
        
        // Prix de base
        if ($service_type === 'restaurant') {
            $total += $settings['restaurant_base_price'];
            $min_duration = $settings['restaurant_min_duration'];
        } else {
            $total += $settings['remorque_base_price'];
            $min_duration = $settings['remorque_min_duration'];
        }
        
        // Supplément durée
        if ($duration > $min_duration) {
            $total += ($duration - $min_duration) * $settings['hour_supplement'];
        }
        
        // Supplément invités (remorque seulement)
        if ($service_type === 'remorque' && $guest_count > $settings['remorque_guest_supplement_threshold']) {
            $total += $settings['remorque_guest_supplement'];
        }
        
        // Supplément distance (remorque seulement)
        if ($service_type === 'remorque' && $distance > 0) {
            if ($distance <= $settings['delivery_zone_2_max']) {
                $total += $settings['delivery_zone_2_price'];
            } elseif ($distance <= $settings['delivery_zone_3_max']) {
                $total += $settings['delivery_zone_3_price'];
            } elseif ($distance <= $settings['delivery_zone_4_max']) {
                $total += $settings['delivery_zone_4_price'];
            }
        }
        
        return $total;
    }
}