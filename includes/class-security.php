<?php
/**
 * Classe de sécurité
 */

if (!defined('ABSPATH')) {
    exit;
}

class Block_Traiteur_Security {
    
    /**
     * Nettoyer les données du formulaire
     */
    public static function sanitize_form_data($data) {
        $sanitized = array();
        
        foreach ($data as $key => $value) {
            switch ($key) {
                case 'customerEmail':
                    $sanitized[$key] = sanitize_email($value);
                    break;
                    
                case 'customerPhone':
                    $sanitized[$key] = preg_replace('/[^0-9\s\-\+\(\)\.]/', '', $value);
                    break;
                    
                case 'postalCode':
                    $sanitized[$key] = preg_replace('/[^0-9]/', '', $value);
                    break;
                    
                case 'eventDate':
                    $sanitized[$key] = sanitize_text_field($value);
                    if (!self::is_valid_date($value)) {
                        $sanitized[$key] = '';
                    }
                    break;
                    
                case 'guestCount':
                case 'duration':
                case 'distance':
                    $sanitized[$key] = intval($value);
                    break;
                    
                case 'basePrice':
                case 'supplementsPrice':
                case 'productsPrice':
                case 'beveragesPrice':
                case 'totalPrice':
                    $sanitized[$key] = floatval($value);
                    break;
                    
                case 'customerComments':
                    $sanitized[$key] = sanitize_textarea_field($value);
                    break;
                    
                case 'selectedProducts':
                case 'selectedBeverages':
                case 'selectedOptions':
                    if (is_array($value)) {
                        $sanitized[$key] = self::sanitize_product_array($value);
                    } else {
                        $sanitized[$key] = array();
                    }
                    break;
                    
                case 'acceptTerms':
                    $sanitized[$key] = (bool) $value;
                    break;
                    
                default:
                    if (is_array($value)) {
                        $sanitized[$key] = self::sanitize_form_data($value);
                    } else {
                        $sanitized[$key] = sanitize_text_field($value);
                    }
                    break;
            }
        }
        
        return $sanitized;
    }
    
    /**
     * Nettoyer un tableau de produits
     */
    private static function sanitize_product_array($products) {
        $sanitized = array();
        
        foreach ($products as $product) {
            if (is_array($product)) {
                $sanitized_product = array();
                
                foreach ($product as $key => $value) {
                    switch ($key) {
                        case 'id':
                        case 'quantity':
                        case 'min_quantity':
                        case 'max_quantity':
                            $sanitized_product[$key] = intval($value);
                            break;
                            
                        case 'price':
                        case 'total_price':
                            $sanitized_product[$key] = floatval($value);
                            break;
                            
                        default:
                            $sanitized_product[$key] = sanitize_text_field($value);
                            break;
                    }
                }
                
                $sanitized[] = $sanitized_product;
            }
        }
        
        return $sanitized;
    }
    
    /**
     * Vérifier le nonce
     */
    public static function verify_nonce($action) {
        if (!wp_verify_nonce($_POST['_wpnonce'], $action)) {
            wp_die(__('Erreur de sécurité. Veuillez rafraîchir la page et réessayer.', 'block-traiteur'));
        }
    }
    
    /**
     * Vérifier les permissions
     */
    public static function check_permissions($capability = 'manage_options') {
        if (!current_user_can($capability)) {
            wp_die(__('Vous n\'avez pas les permissions suffisantes pour accéder à cette page.', 'block-traiteur'));
        }
    }
    
    /**
     * Vérifier si une date est valide
     */
    public static function is_valid_date($date) {
        $d = DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }
    
    /**
     * Limitation du taux de requêtes
     */
    public static function rate_limit_check($ip, $action = 'quote_submission') {
        $transient_key = 'block_rate_limit_' . md5($ip . $action);
        $attempts = get_transient($transient_key);
        
        if ($attempts === false) {
            set_transient($transient_key, 1, HOUR_IN_SECONDS);
            return true;
        }
        
        if ($attempts >= 5) { // Max 5 tentatives par heure
            return false;
        }
        
        set_transient($transient_key, $attempts + 1, HOUR_IN_SECONDS);
        return true;
    }
    
    /**
     * Échapper pour JavaScript
     */
    public static function escape_for_js($data) {
        return wp_json_encode($data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
    }
    
    /**
     * Valider une requête AJAX
     */
    public static function validate_ajax_request() {
        if (!wp_verify_nonce($_POST['nonce'], 'block_traiteur_ajax')) {
            wp_send_json_error(__('Nonce invalide', 'block-traiteur'));
        }
        
        $ip = $_SERVER['REMOTE_ADDR'];
        if (!self::rate_limit_check($ip, 'ajax_request')) {
            wp_send_json_error(__('Trop de requêtes, veuillez patienter', 'block-traiteur'));
        }
    }
    
    /**
     * Nettoyer les entrées utilisateur pour éviter XSS
     */
    public static function clean_input($input) {
        return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Valider une adresse email
     */
    public static function validate_email($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Générer un token sécurisé
     */
    public static function generate_secure_token($length = 32) {
        return bin2hex(random_bytes($length / 2));
    }
    
    /**
     * Vérifier la force d'un mot de passe (si nécessaire)
     */
    public static function validate_password_strength($password) {
        $errors = array();
        
        if (strlen($password) < 8) {
            $errors[] = __('Le mot de passe doit contenir au moins 8 caractères', 'block-traiteur');
        }
        
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = __('Le mot de passe doit contenir au moins une majuscule', 'block-traiteur');
        }
        
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = __('Le mot de passe doit contenir au moins une minuscule', 'block-traiteur');
        }
        
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = __('Le mot de passe doit contenir au moins un chiffre', 'block-traiteur');
        }
        
        return array(
            'valid' => empty($errors),
            'errors' => $errors
        );
    }
}