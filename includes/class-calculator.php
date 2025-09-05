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
     * Calculer le prix de base selon les spécifications
     */
    private function calculate_base_price() {
        $service_type = $this->form_data['serviceType'] ?? 'restaurant';
        
        if ($service_type === 'restaurant') {
            // Prix de base restaurant : 300€ (2H incluses)
            $this->price_breakdown['base'] = 300.00;
        } else {
            // Prix de base remorque : 350€ (2H incluses)
            $this->price_breakdown['base'] = 350.00;
        }
    }
    
    /**
     * Calculer le supplément durée selon les spécifications
     */
    private function calculate_duration_supplement() {
        $duration = intval($this->form_data['duration'] ?? 2);
        $service_type = $this->form_data['serviceType'] ?? 'restaurant';
        
        // 2H incluses pour les deux services
        $base_duration = 2;
        
        // Contraintes selon les spécifications
        if ($service_type === 'restaurant') {
            // Restaurant : 2-4 heures
            $duration = max(2, min(4, $duration));
        } else {
            // Remorque : 2-5 heures
            $duration = max(2, min(5, $duration));
        }
        
        // Supplément : +50€/heure au-delà de 2H
        if ($duration > $base_duration) {
            $extra_hours = $duration - $base_duration;
            $this->price_breakdown['duration'] = $extra_hours * 50.00;
        } else {
            $this->price_breakdown['duration'] = 0;
        }
    }
    
    /**
     * Calculer le supplément invités selon les spécifications
     */
    private function calculate_guest_supplement() {
        $guest_count = intval($this->form_data['guestCount'] ?? 0);
        $service_type = $this->form_data['serviceType'] ?? 'restaurant';
        
        // Contraintes selon les spécifications
        if ($service_type === 'restaurant') {
            // Restaurant : 10-30 personnes (pas de supplément)
            $this->price_breakdown['guests'] = 0;
        } else {
            // Remorque : 20-100+ personnes, +150€ si >50 personnes
            if ($guest_count > 50) {
                $this->price_breakdown['guests'] = 150.00;
            } else {
                $this->price_breakdown['guests'] = 0;
            }
        }
    }
    
    /**
     * Calculer le supplément distance selon les spécifications
     */
    private function calculate_distance_supplement() {
        if (!isset($this->form_data['distance']) || $this->form_data['serviceType'] !== 'remorque') {
            $this->price_breakdown['distance'] = 0;
            return;
        }
        
        $distance = floatval($this->form_data['distance']);
        
        // Frais de livraison selon les zones spécifiées
        if ($distance <= 30) {
            // Zone gratuite : 0-30km
            $this->price_breakdown['distance'] = 0;
        } elseif ($distance <= 50) {
            // Zone 1 : 30-50km = +20€
            $this->price_breakdown['distance'] = 20.00;
        } elseif ($distance <= 100) {
            // Zone 2 : 50-100km = +70€
            $this->price_breakdown['distance'] = 70.00;
        } elseif ($distance <= 150) {
            // Zone 3 : 100-150km = +118€
            $this->price_breakdown['distance'] = 118.00;
        } else {
            // Hors zone (>150km) - non desservi
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
     * Calculer le prix des options selon les spécifications
     */
    private function calculate_options_price() {
        $total = 0;
        
        if (!empty($this->form_data['selectedOptions'])) {
            foreach ($this->form_data['selectedOptions'] as $option) {
                switch ($option) {
                    case 'tireuse':
                        // Option 1 : Mise à disposition tireuse = 50€
                        $total += 50.00;
                        break;
                    case 'jeux':
                        // Option 2 : Installation jeux = 70€
                        $total += 70.00;
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
     * Calculer une estimation rapide pour AJAX selon les spécifications
     */
    public static function quick_estimate($service_type, $guest_count, $duration, $distance = 0) {
        $total = 0;
        
        // Prix de base selon les spécifications
        if ($service_type === 'restaurant') {
            $total += 300.00; // Restaurant : 300€
        } else {
            $total += 350.00; // Remorque : 350€
        }
        
        // Supplément durée : +50€/heure au-delà de 2H
        if ($duration > 2) {
            $total += ($duration - 2) * 50.00;
        }
        
        // Supplément invités (remorque seulement) : +150€ si >50 personnes
        if ($service_type === 'remorque' && $guest_count > 50) {
            $total += 150.00;
        }
        
        // Supplément distance (remorque seulement) selon les zones
        if ($service_type === 'remorque' && $distance > 0) {
            if ($distance <= 30) {
                // Zone gratuite
                $total += 0;
            } elseif ($distance <= 50) {
                // Zone 1 : +20€
                $total += 20.00;
            } elseif ($distance <= 100) {
                // Zone 2 : +70€
                $total += 70.00;
            } elseif ($distance <= 150) {
                // Zone 3 : +118€
                $total += 118.00;
            }
            // >150km : non desservi
        }
        
        return $total;
    }
}