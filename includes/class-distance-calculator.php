<?php
/**
 * Classe de calcul des distances
 */

if (!defined('ABSPATH')) {
    exit;
}

class Block_Traiteur_Distance_Calculator {
    
    private $strasbourg_coords = array(
        'lat' => 48.5734,
        'lng' => 7.7521
    );
    
    private $terrain_coefficients = array(
        'urban' => 1.25,
        'rural' => 1.35,
        'mountain' => 1.50
    );
    
    /**
     * Calculer la distance depuis Strasbourg
     */
    public function calculate_distance($postal_code) {
        // 1. Récupérer les coordonnées du code postal
        $coords = $this->get_coordinates_from_postal($postal_code);
        
        if (!$coords) {
            return false;
        }
        
        // 2. Calculer la distance à vol d'oiseau
        $direct_distance = $this->haversine_distance(
            $this->strasbourg_coords,
            $coords
        );
        
        // 3. Appliquer le coefficient selon le terrain
        $terrain_type = $this->get_terrain_type($postal_code);
        $road_distance = $direct_distance * $this->terrain_coefficients[$terrain_type];
        
        return round($road_distance, 1);
    }
    
    /**
     * Obtenir les coordonnées d'un code postal
     */
    private function get_coordinates_from_postal($postal_code) {
        global $wpdb;
        
        $result = $wpdb->get_row($wpdb->prepare(
            "SELECT latitude, longitude, terrain_type FROM {$wpdb->prefix}block_postal_codes WHERE postal_code = %s",
            $postal_code
        ));
        
        if ($result) {
            return array(
                'lat' => floatval($result->latitude),
                'lng' => floatval($result->longitude),
                'terrain_type' => $result->terrain_type
            );
        }
        
        // Fallback: utiliser l'API gouvernementale française
        return $this->get_coords_from_api($postal_code);
    }
    
    /**
     * Utiliser l'API gouvernementale pour obtenir les coordonnées
     */
    private function get_coords_from_api($postal_code) {
        $url = "https://api-adresse.data.gouv.fr/search/?q={$postal_code}&type=municipality&limit=1";
        
        $response = wp_remote_get($url);
        
        if (is_wp_error($response)) {
            return false;
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (!empty($data['features'][0]['geometry']['coordinates'])) {
            $coords = $data['features'][0]['geometry']['coordinates'];
            $city = $data['features'][0]['properties']['name'] ?? '';
            $department = substr($postal_code, 0, 2);
            $terrain_type = $this->determine_terrain_type($department);
            
            // Sauvegarder pour usage futur
            $this->save_postal_code_data($postal_code, $coords[1], $coords[0], $city, $department, $terrain_type);
            
            return array(
                'lat' => floatval($coords[1]),
                'lng' => floatval($coords[0]),
                'terrain_type' => $terrain_type
            );
        }
        
        return false;
    }
    
    /**
     * Sauvegarder les données d'un code postal
     */
    private function save_postal_code_data($postal_code, $lat, $lng, $city, $department, $terrain_type) {
        global $wpdb;
        
        $wpdb->insert(
            $wpdb->prefix . 'block_postal_codes',
            array(
                'postal_code' => $postal_code,
                'city' => $city,
                'department' => $department,
                'latitude' => $lat,
                'longitude' => $lng,
                'terrain_type' => $terrain_type
            ),
            array('%s', '%s', '%s', '%f', '%f', '%s')
        );
    }
    
    /**
     * Déterminer le type de terrain selon le département
     */
    private function determine_terrain_type($department) {
        // Départements montagneux
        $mountain_depts = array('04', '05', '06', '38', '73', '74', '64', '65', '09', '66', '11', '48', '15', '43', '07', '26');
        
        // Grandes métropoles et zones urbaines denses
        $urban_depts = array('75', '92', '93', '94', '95', '91', '77', '78', '13', '69', '59', '62', '33', '31', '44', '35');
        
        if (in_array($department, $mountain_depts)) {
            return 'mountain';
        } elseif (in_array($department, $urban_depts)) {
            return 'urban';
        } else {
            return 'rural';
        }
    }
    
    /**
     * Obtenir le type de terrain d'un code postal
     */
    private function get_terrain_type($postal_code) {
        global $wpdb;
        
        $terrain = $wpdb->get_var($wpdb->prepare(
            "SELECT terrain_type FROM {$wpdb->prefix}block_postal_codes WHERE postal_code = %s",
            $postal_code
        ));
        
        return $terrain ?: 'rural';
    }
    
    /**
     * Calculer la distance haversine entre deux points
     */
    private function haversine_distance($point1, $point2) {
        $earth_radius = 6371; // Rayon de la Terre en km
        
        $lat1 = deg2rad($point1['lat']);
        $lon1 = deg2rad($point1['lng']);
        $lat2 = deg2rad($point2['lat']);
        $lon2 = deg2rad($point2['lng']);
        
        $dlat = $lat2 - $lat1;
        $dlon = $lon2 - $lon1;
        
        $a = sin($dlat/2) * sin($dlat/2) + cos($lat1) * cos($lat2) * sin($dlon/2) * sin($dlon/2);
        $c = 2 * asin(sqrt($a));
        
        return $earth_radius * $c;
    }
    
    /**
     * Obtenir le supplément de livraison selon la distance
     */
    public function get_delivery_supplement($distance) {
        $settings = Block_Traiteur_Cache::get_settings();
        
        if ($distance <= $settings['delivery_zone_1_max']) {
            return 0;
        } elseif ($distance <= $settings['delivery_zone_2_max']) {
            return $settings['delivery_zone_2_price'];
        } elseif ($distance <= $settings['delivery_zone_3_max']) {
            return $settings['delivery_zone_3_price'];
        } elseif ($distance <= $settings['delivery_zone_4_max']) {
            return $settings['delivery_zone_4_price'];
        } else {
            return false; // Hors zone
        }
    }
    
    /**
     * Valider un code postal
     */
    public function validate_postal_code($postal_code) {
        if (!preg_match('/^[0-9]{5}$/', $postal_code)) {
            return array(
                'valid' => false,
                'error' => __('Le code postal doit contenir exactement 5 chiffres', 'block-traiteur')
            );
        }
        
        $distance = $this->calculate_distance($postal_code);
        
        if ($distance === false) {
            return array(
                'valid' => false,
                'error' => __('Code postal non trouvé dans notre base de données', 'block-traiteur')
            );
        }
        
        $max_distance = 150; // Limite maximale
        if ($distance > $max_distance) {
            return array(
                'valid' => false,
                'error' => sprintf(
                    __('Zone de livraison dépassée (%skm). Contactez-nous au 06 58 13 38 05 pour étudier votre demande.', 'block-traiteur'),
                    $distance
                ),
                'distance' => $distance
            );
        }
        
        $supplement = $this->get_delivery_supplement($distance);
        
        return array(
            'valid' => true,
            'distance' => $distance,
            'supplement' => $supplement,
            'zone' => $this->get_delivery_zone($distance)
        );
    }
    
    /**
     * Obtenir la zone de livraison
     */
    private function get_delivery_zone($distance) {
        $settings = Block_Traiteur_Cache::get_settings();
        
        if ($distance <= $settings['delivery_zone_1_max']) {
            return 1;
        } elseif ($distance <= $settings['delivery_zone_2_max']) {
            return 2;
        } elseif ($distance <= $settings['delivery_zone_3_max']) {
            return 3;
        } elseif ($distance <= $settings['delivery_zone_4_max']) {
            return 4;
        } else {
            return 5; // Hors zone
        }
    }
}