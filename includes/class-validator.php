<?php
/**
 * Classe de validation des données
 */

if (!defined('ABSPATH')) {
    exit;
}

class Block_Traiteur_Validator {
    
    private $errors = array();
    private $settings = array();
    
    /**
     * Constructeur
     */
    public function __construct() {
        $this->settings = Block_Traiteur_Cache::get_settings();
    }
    
    /**
     * Valider une étape du formulaire
     */
    public function validate_step($step_number, $form_data) {
        $this->errors = array();
        
        switch ($step_number) {
            case 1:
                $this->validate_base_package($form_data);
                break;
            case 2:
                $this->validate_meal_formulas($form_data);
                break;
            case 3:
                $this->validate_buffets($form_data);
                break;
            case 4:
                $this->validate_beverages($form_data);
                break;
            case 5:
                $this->validate_options($form_data);
                break;
            case 6:
                $this->validate_contact($form_data);
                break;
        }
        
        return empty($this->errors);
    }
    
    /**
     * Valider le formulaire complet
     */
    public function validate_complete_form($form_data) {
        $this->errors = array();
        
        $this->validate_base_package($form_data);
        $this->validate_contact($form_data);
        $this->validate_products_selection($form_data);
        
        return empty($this->errors);
    }
    
    /**
     * Valider le forfait de base
     */
    private function validate_base_package($data) {
        // Type de service
        if (empty($data['serviceType']) || !in_array($data['serviceType'], array('restaurant', 'remorque'))) {
            $this->add_error('serviceType', __('Veuillez sélectionner un type de service', 'block-traiteur'));
        }
        
        // Date événement
        if (empty($data['eventDate'])) {
            $this->add_error('eventDate', __('La date de l\'événement est obligatoire', 'block-traiteur'));
        } else {
            $event_date = strtotime($data['eventDate']);
            $today = strtotime('today');
            
            if ($event_date <= $today) {
                $this->add_error('eventDate', __('La date doit être dans le futur', 'block-traiteur'));
            }
            
            // Vérifier disponibilité
            if (!$this->is_date_available($data['eventDate'], $data['serviceType'])) {
                $this->add_error('eventDate', __('Cette date n\'est pas disponible', 'block-traiteur'));
            }
        }
        
        // Nombre d'invités
        if (empty($data['guestCount'])) {
            $this->add_error('guestCount', __('Le nombre d\'invités est obligatoire', 'block-traiteur'));
        } else {
            $guests = intval($data['guestCount']);
            $service_type = $data['serviceType'] ?? 'restaurant';
            
            if ($service_type === 'restaurant') {
                $min_guests = $this->settings['restaurant_min_guests'];
                $max_guests = $this->settings['restaurant_max_guests'];
            } else {
                $min_guests = $this->settings['remorque_min_guests'];
                $max_guests = $this->settings['remorque_max_guests'];
            }
            
            if ($guests < $min_guests) {
                $this->add_error('guestCount', sprintf(__('Minimum %d invités requis', 'block-traiteur'), $min_guests));
            }
            
            if ($guests > $max_guests) {
                $this->add_error('guestCount', sprintf(__('Maximum %d invités autorisés', 'block-traiteur'), $max_guests));
            }
        }
        
        // Durée
        if (empty($data['duration'])) {
            $this->add_error('duration', __('La durée est obligatoire', 'block-traiteur'));
        } else {
            $duration = intval($data['duration']);
            $service_type = $data['serviceType'] ?? 'restaurant';
            
            if ($service_type === 'restaurant') {
                $min_duration = $this->settings['restaurant_min_duration'];
                $max_duration = $this->settings['restaurant_max_duration'];
            } else {
                $min_duration = $this->settings['remorque_min_duration'];
                $max_duration = $this->settings['remorque_max_duration'];
            }
            
            if ($duration < $min_duration) {
                $this->add_error('duration', sprintf(__('Durée minimum : %d heures', 'block-traiteur'), $min_duration));
            }
            
            if ($duration > $max_duration) {
                $this->add_error('duration', sprintf(__('Durée maximum : %d heures', 'block-traiteur'), $max_duration));
            }
        }
        
        // Code postal et distance (remorque uniquement)
        if (isset($data['serviceType']) && $data['serviceType'] === 'remorque') {
            if (empty($data['postalCode'])) {
                $this->add_error('postalCode', __('Le code postal est obligatoire pour la remorque', 'block-traiteur'));
            } else {
                $postal_validation = $this->validate_postal_code($data['postalCode']);
                if (!$postal_validation['valid']) {
                    $this->add_error('postalCode', $postal_validation['error']);
                }
            }
            
            if (empty($data['eventLocation'])) {
                $this->add_error('eventLocation', __('Le lieu de l\'événement est obligatoire pour la remorque', 'block-traiteur'));
            }
        }
    }
    
    /**
     * Valider les formules repas
     */
    private function validate_meal_formulas($data) {
        // Vérifier qu'au moins une signature est sélectionnée
        $has_signature = false;
        
        if (!empty($data['selectedProducts'])) {
            foreach ($data['selectedProducts'] as $product) {
                if (in_array($product['category_type'], array('signature'))) {
                    $has_signature = true;
                    break;
                }
            }
        }
        
        if (!$has_signature) {
            $this->add_error('signatures', __('Veuillez sélectionner au moins une signature', 'block-traiteur'));
        }
    }
    
    /**
     * Valider les buffets
     */
    private function validate_buffets($data) {
        // Validation optionnelle - les buffets ne sont pas obligatoires
        if (!empty($data['selectedBuffets'])) {
            // Vérifier les quantités minimales si buffets sélectionnés
            $buffet_sale_count = 0;
            $buffet_sucre_count = 0;
            
            foreach ($data['selectedBuffets'] as $buffet) {
                if ($buffet['category_type'] === 'buffet_sale') {
                    $buffet_sale_count++;
                } elseif ($buffet['category_type'] === 'buffet_sucre') {
                    $buffet_sucre_count++;
                }
            }
            
            if ($buffet_sale_count > 0 && $buffet_sale_count < 2) {
                $this->add_error('buffets', __('Minimum 2 recettes salées si buffet salé sélectionné', 'block-traiteur'));
            }
            
            if ($buffet_sucre_count > 0 && $buffet_sucre_count < 1) {
                $this->add_error('buffets', __('Minimum 1 recette sucrée si buffet sucré sélectionné', 'block-traiteur'));
            }
        }
    }
    
    /**
     * Valider les boissons
     */
    private function validate_beverages($data) {
        // Les boissons sont optionnelles
        return true;
    }
    
    /**
     * Valider les options
     */
    private function validate_options($data) {
        // Les options sont optionnelles
        return true;
    }
    
    /**
     * Valider les informations de contact
     */
    private function validate_contact($data) {
        // Nom
        if (empty($data['customerName'])) {
            $this->add_error('customerName', __('Le nom est obligatoire', 'block-traiteur'));
        } elseif (strlen($data['customerName']) < 2) {
            $this->add_error('customerName', __('Le nom doit contenir au moins 2 caractères', 'block-traiteur'));
        }
        
        // Email
        if (empty($data['customerEmail'])) {
            $this->add_error('customerEmail', __('L\'email est obligatoire', 'block-traiteur'));
        } elseif (!is_email($data['customerEmail'])) {
            $this->add_error('customerEmail', __('Format d\'email invalide', 'block-traiteur'));
        }
        
        // Téléphone (optionnel mais format validé si fourni)
        if (!empty($data['customerPhone'])) {
            $phone = preg_replace('/[^0-9+\-\s\(\)]/', '', $data['customerPhone']);
            if (strlen($phone) < 10) {
                $this->add_error('customerPhone', __('Format de téléphone invalide', 'block-traiteur'));
            }
        }
        
        // Conditions générales
        if (empty($data['acceptTerms'])) {
            $this->add_error('acceptTerms', __('Vous devez accepter les conditions générales', 'block-traiteur'));
        }
    }
    
    /**
     * Valider la sélection de produits
     */
    private function validate_products_selection($data) {
        // Vérifier que les quantités sont dans les limites
        if (!empty($data['selectedProducts'])) {
            foreach ($data['selectedProducts'] as $product) {
                $quantity = intval($product['quantity']);
                
                if (isset($product['min_quantity']) && $quantity < $product['min_quantity']) {
                    $this->add_error('products', sprintf(
                        __('Quantité minimum pour %s : %d', 'block-traiteur'),
                        $product['name'],
                        $product['min_quantity']
                    ));
                }
                
                if (isset($product['max_quantity']) && $quantity > $product['max_quantity']) {
                    $this->add_error('products', sprintf(
                        __('Quantité maximum pour %s : %d', 'block-traiteur'),
                        $product['name'],
                        $product['max_quantity']
                    ));
                }
            }
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
        
        $distance_calculator = new Block_Traiteur_Distance_Calculator();
        return $distance_calculator->validate_postal_code($postal_code);
    }
    
    /**
     * Vérifier la disponibilité d'une date
     */
    private function is_date_available($date, $service_type) {
        global $wpdb;
        
        $unavailable = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}block_availability 
             WHERE date = %s 
             AND (service_type = %s OR service_type = 'both') 
             AND is_available = 0",
            $date,
            $service_type
        ));
        
        return $unavailable == 0;
    }
    
    /**
     * Ajouter une erreur
     */
    private function add_error($field, $message) {
        $this->errors[$field] = $message;
    }
    
    /**
     * Obtenir les erreurs
     */
    public function get_errors() {
        return $this->errors;
    }
    
    /**
     * Vérifier s'il y a des erreurs
     */
    public function has_errors() {
        return !empty($this->errors);
    }
}