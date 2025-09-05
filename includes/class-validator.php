<?php
/**
 * Classe de validation des données pour Block Traiteur
 *
 * @package Block_Traiteur
 * @subpackage Includes
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class Block_Traiteur_Validator {
    
    private $settings;
    private $calculator;
    
    public function __construct() {
        $this->settings = Block_Traiteur_Settings::get_instance();
        $this->calculator = new Block_Traiteur_Calculator();
    }
    
    /**
     * Valider un devis complet
     */
    public function validate_quote($quote_data) {
        $errors = array();
        $valid = true;
        
        // Validation du type de service
        if (!in_array($quote_data['service_type'] ?? '', array('restaurant', 'remorque'))) {
            $errors['service_type'] = 'Type de service invalide';
            $valid = false;
        }
        
        // Validation de la date
        $date_validation = $this->validate_event_date($quote_data['event_date'] ?? '');
        if (!$date_validation['valid']) {
            $errors['event_date'] = $date_validation['message'];
            $valid = false;
        }
        
        // Validation du nombre de convives
        $guests_validation = $this->calculator->validate_guest_count(
            $quote_data['service_type'] ?? '',
            $quote_data['guest_count'] ?? 0
        );
        if (!$guests_validation['valid']) {
            $errors['guest_count'] = $guests_validation['message'];
            $valid = false;
        }
        
        // Validation de la durée
        $duration_validation = $this->calculator->validate_duration(
            $quote_data['service_type'] ?? '',
            $quote_data['event_duration'] ?? 0
        );
        if (!$duration_validation['valid']) {
            $errors['event_duration'] = $duration_validation['message'];
            $valid = false;
        }
        
        // Validation des données client
        $customer_validation = $this->validate_customer_data($quote_data['customer_data'] ?? array());
        if (!$customer_validation['valid']) {
            $errors['customer_data'] = $customer_validation['errors'];
            $valid = false;
        }
        
        return array(
            'valid' => $valid,
            'errors' => $errors
        );
    }
    
    /**
     * Valider la date d'événement
     */
    private function validate_event_date($event_date) {
        $date = DateTime::createFromFormat('Y-m-d', $event_date);
        if (!$date || $date->format('Y-m-d') !== $event_date) {
            return array('valid' => false, 'message' => 'Format de date invalide');
        }
        
        $today = new DateTime();
        $today->setTime(0, 0, 0);
        
        if ($date < $today) {
            return array('valid' => false, 'message' => 'La date ne peut pas être dans le passé');
        }
        
        return array('valid' => true, 'message' => '');
    }
    
    /**
     * Valider les données client
     */
    private function validate_customer_data($customer_data) {
        $errors = array();
        $valid = true;
        
        $required_fields = array(
            'first_name' => 'Prénom requis',
            'last_name' => 'Nom requis',
            'email' => 'Email requis',
            'phone' => 'Téléphone requis'
        );
        
        foreach ($required_fields as $field => $error_message) {
            if (empty($customer_data[$field])) {
                $errors[$field] = $error_message;
                $valid = false;
            }
        }
        
        if (!empty($customer_data['email']) && !is_email($customer_data['email'])) {
            $errors['email'] = 'Format d\'email invalide';
            $valid = false;
        }
        
        return array('valid' => $valid, 'errors' => $errors);
    }
}