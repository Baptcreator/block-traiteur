<?php
/**
 * Gestionnaire des requêtes AJAX
 */

if (!defined('ABSPATH')) {
    exit;
}

class Block_Traiteur_Ajax_Handler {
    
    /**
     * Constructeur
     */
    public function __construct() {
        // AJAX pour utilisateurs connectés et non connectés
        add_action('wp_ajax_block_traiteur_submit_quote', array($this, 'submit_quote'));
        add_action('wp_ajax_nopriv_block_traiteur_submit_quote', array($this, 'submit_quote'));
        
        add_action('wp_ajax_block_traiteur_calculate_distance', array($this, 'calculate_distance'));
        add_action('wp_ajax_nopriv_block_traiteur_calculate_distance', array($this, 'calculate_distance'));
        
        add_action('wp_ajax_block_traiteur_check_availability', array($this, 'check_availability'));
        add_action('wp_ajax_nopriv_block_traiteur_check_availability', array($this, 'check_availability'));
        
        add_action('wp_ajax_block_traiteur_get_quote_estimate', array($this, 'get_quote_estimate'));
        add_action('wp_ajax_nopriv_block_traiteur_get_quote_estimate', array($this, 'get_quote_estimate'));
        
        // Endpoints pour récupérer les produits et boissons
        add_action('wp_ajax_block_traiteur_get_products', array($this, 'get_products'));
        add_action('wp_ajax_nopriv_block_traiteur_get_products', array($this, 'get_products'));
        
        add_action('wp_ajax_block_traiteur_get_beverages', array($this, 'get_beverages'));
        add_action('wp_ajax_nopriv_block_traiteur_get_beverages', array($this, 'get_beverages'));
    }
    
    /**
     * Soumettre un devis
     */
    public function submit_quote() {
        Block_Traiteur_Security::validate_ajax_request();
        
        // Récupérer et nettoyer les données
        $form_data = $_POST['form_data'] ?? array();
        $sanitized_data = Block_Traiteur_Security::sanitize_form_data($form_data);
        
        // Valider les données complètes
        $validator = new Block_Traiteur_Validator();
        if (!$validator->validate_complete_form($sanitized_data)) {
            wp_send_json_error(array(
                'message' => __('Données invalides', 'block-traiteur'),
                'errors' => $validator->get_errors()
            ));
        }
        
        try {
            // Calculer les prix
            $calculator = new Block_Traiteur_Calculator();
            $calculator->set_form_data($sanitized_data);
            $total_price = $calculator->get_total_price();
            $price_breakdown = $calculator->get_price_breakdown();
            
            // Créer le devis en base
            $quote_data = array(
                'quote_number' => $this->generate_quote_number(),
                'service_type' => $sanitized_data['serviceType'],
                'customer_name' => $sanitized_data['customerName'],
                'customer_email' => $sanitized_data['customerEmail'],
                'customer_phone' => $sanitized_data['customerPhone'] ?? '',
                'event_date' => $sanitized_data['eventDate'],
                'event_duration' => intval($sanitized_data['duration']),
                'guest_count' => intval($sanitized_data['guestCount']),
                'event_location' => $sanitized_data['eventLocation'] ?? '',
                'postal_code' => $sanitized_data['postalCode'] ?? '',
                'distance_km' => floatval($sanitized_data['distance'] ?? 0),
                'base_price' => $price_breakdown['base'],
                'supplements_price' => $price_breakdown['duration'] + $price_breakdown['guests'] + $price_breakdown['distance'],
                'products_price' => $price_breakdown['products'],
                'beverages_price' => $price_breakdown['beverages'],
                'total_price' => $total_price,
                'customer_comments' => $sanitized_data['customerComments'] ?? '',
                'status' => 'draft',
                'expires_at' => date('Y-m-d H:i:s', strtotime('+30 days'))
            );
            
            $quote_id = Block_Traiteur_Database::create_quote($quote_data);
            
            if (!$quote_id) {
                throw new Exception(__('Erreur lors de la création du devis', 'block-traiteur'));
            }
            
            // Sauvegarder les produits sélectionnés
            if (!empty($sanitized_data['selectedProducts'])) {
                Block_Traiteur_Database::save_quote_products($quote_id, $sanitized_data['selectedProducts'], 'food');
            }
            
            if (!empty($sanitized_data['selectedBeverages'])) {
                Block_Traiteur_Database::save_quote_products($quote_id, $sanitized_data['selectedBeverages'], 'beverage');
            }
            
            // Générer le PDF
            $pdf_generator = new Block_Traiteur_PDF_Generator();
            $pdf_path = $pdf_generator->generate_quote_pdf($quote_id);
            
            // Envoyer les emails
            $mailer = new Block_Traiteur_Mailer();
            $email_sent = $mailer->send_quote_confirmation($quote_id);
            
            // Logger l'événement
            Block_Traiteur_Logger::info('Nouveau devis créé', array(
                'quote_id' => $quote_id,
                'quote_number' => $quote_data['quote_number'],
                'customer_email' => $quote_data['customer_email'],
                'total_price' => $total_price
            ));
            
            wp_send_json_success(array(
                'message' => __('Votre demande de devis a été envoyée avec succès !', 'block-traiteur'),
                'quote_number' => $quote_data['quote_number'],
                'quote_id' => $quote_id,
                'email_sent' => $email_sent,
                'total_price' => $total_price
            ));
            
        } catch (Exception $e) {
            Block_Traiteur_Logger::error('Erreur création devis', array(
                'message' => $e->getMessage(),
                'form_data' => $sanitized_data
            ));
            
            wp_send_json_error(array(
                'message' => __('Une erreur est survenue lors de la création de votre devis. Veuillez réessayer.', 'block-traiteur')
            ));
        }
    }
    
    /**
     * Calculer la distance et le supplément
     */
    public function calculate_distance() {
        check_ajax_referer('block_traiteur_ajax', 'nonce');
        
        $postal_code = sanitize_text_field($_POST['postal_code'] ?? '');
        
        if (empty($postal_code)) {
            wp_send_json_error(__('Code postal manquant', 'block-traiteur'));
        }
        
        $distance_calculator = new Block_Traiteur_Distance_Calculator();
        $result = $distance_calculator->validate_postal_code($postal_code);
        
        if ($result['valid']) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error($result['error']);
        }
    }
    
    /**
     * Vérifier la disponibilité d'une date
     */
    public function check_availability() {
        check_ajax_referer('block_traiteur_ajax', 'nonce');
        
        $date = sanitize_text_field($_POST['date'] ?? '');
        $service_type = sanitize_text_field($_POST['service_type'] ?? '');
        
        if (empty($date) || empty($service_type)) {
            wp_send_json_error(__('Paramètres manquants', 'block-traiteur'));
        }
        
        global $wpdb;
        
        // Vérifier les blocages manuels
        $blocked = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}block_availability 
             WHERE date = %s 
             AND (service_type = %s OR service_type = 'both') 
             AND is_available = 0",
            $date,
            $service_type
        ));
        
        if ($blocked > 0) {
            wp_send_json_success(array(
                'available' => false,
                'message' => __('Cette date n\'est pas disponible', 'block-traiteur')
            ));
        }
        
        // Vérifier Google Calendar si configuré
        if (get_option('block_traiteur_google_calendar_enabled')) {
            $calendar = new Block_Traiteur_Calendar_Integration();
            $calendar_available = $calendar->check_date_availability($date);
            
            if (!$calendar_available) {
                wp_send_json_success(array(
                    'available' => false,
                    'message' => __('Cette date est déjà réservée', 'block-traiteur')
                ));
            }
        }
        
        wp_send_json_success(array(
            'available' => true,
            'message' => __('Date disponible', 'block-traiteur')
        ));
    }
    
    /**
     * Obtenir une estimation rapide
     */
    public function get_quote_estimate() {
        check_ajax_referer('block_traiteur_ajax', 'nonce');
        
        $service_type = sanitize_text_field($_POST['serviceType'] ?? 'restaurant');
        $guest_count = intval($_POST['guestCount'] ?? 20);
        $duration = intval($_POST['duration'] ?? 2);
        $distance = floatval($_POST['distance'] ?? 0);
        
        $estimate = Block_Traiteur_Calculator::quick_estimate($service_type, $guest_count, $duration, $distance);
        
        wp_send_json_success(array(
            'estimate' => $estimate,
            'formatted' => number_format($estimate, 2) . ' € TTC',
            'parameters' => array(
                'service_type' => $service_type,
                'guest_count' => $guest_count,
                'duration' => $duration,
                'distance' => $distance
            )
        ));
    }
    
    /**
     * Récupérer les produits par catégorie
     */
    public function get_products() {
        check_ajax_referer('block_traiteur_ajax', 'nonce');
        
        $category = sanitize_text_field($_POST['category'] ?? '');
        $service_type = sanitize_text_field($_POST['service_type'] ?? 'both');
        
        global $wpdb;
        
        try {
            // Récupérer par slug de catégorie
            $category_id = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM {$wpdb->prefix}block_food_categories WHERE slug = %s",
                $category
            ));
            
            if (!$category_id) {
                wp_send_json_error(__('Catégorie non trouvée: ' . $category, 'block-traiteur'));
            }
            
            $products = $wpdb->get_results($wpdb->prepare(
                "SELECT p.*, c.name as category_name, c.slug as category_slug
                 FROM {$wpdb->prefix}block_products p
                 LEFT JOIN {$wpdb->prefix}block_food_categories c ON p.category_id = c.id
                 WHERE p.category_id = %d
                 AND p.is_active = 1
                 AND (p.service_type = %s OR p.service_type = 'both')
                 ORDER BY p.sort_order ASC, p.name ASC",
                $category_id,
                $service_type
            ));
            
            // Formater les produits pour le frontend
            $formatted_products = array();
            foreach ($products as $product) {
                $formatted_products[] = array(
                    'id' => intval($product->id),
                    'name' => $product->name,
                    'description' => $product->description,
                    'price' => floatval($product->price),
                    'unit' => $product->unit,
                    'min_quantity' => intval($product->min_quantity),
                    'max_quantity' => $product->max_quantity ? intval($product->max_quantity) : null,
                    'allergens' => $product->allergens,
                    'image_url' => $product->image_url ?: '',
                    'category' => $product->category_slug,
                    'category_name' => $product->category_name
                );
            }
            
            wp_send_json_success(array(
                'products' => $formatted_products,
                'category' => $category,
                'count' => count($formatted_products)
            ));
            
        } catch (Exception $e) {
            Block_Traiteur_Logger::error('Erreur récupération produits', array(
                'category' => $category,
                'service_type' => $service_type,
                'error' => $e->getMessage()
            ));
            
            wp_send_json_error(__('Erreur lors de la récupération des produits', 'block-traiteur'));
        }
    }
    
    /**
     * Récupérer les boissons par catégorie
     */
    public function get_beverages() {
        check_ajax_referer('block_traiteur_ajax', 'nonce');
        
        $category = sanitize_text_field($_POST['category'] ?? '');
        $subcategory = sanitize_text_field($_POST['subcategory'] ?? '');
        $service_type = sanitize_text_field($_POST['service_type'] ?? 'both');
        
        global $wpdb;
        
        try {
            // Construire la requête selon la catégorie
            $where_clause = "b.is_active = 1 AND (b.service_type = %s OR b.service_type = 'both')";
            $params = array($service_type);
            
            if (!empty($category)) {
                if ($category === 'vins' && !empty($subcategory)) {
                    // Sous-catégories de vins
                    $category_slug = 'vins_' . $subcategory;
                } elseif ($category === 'bieres' && !empty($subcategory)) {
                    // Sous-catégories de bières  
                    $category_slug = 'bieres_' . $subcategory;
                } else {
                    $category_slug = $category;
                }
                
                $category_id = $wpdb->get_var($wpdb->prepare(
                    "SELECT id FROM {$wpdb->prefix}block_beverage_categories WHERE slug = %s",
                    $category_slug
                ));
                
                if ($category_id) {
                    $where_clause .= " AND b.category_id = %d";
                    $params[] = $category_id;
                }
            }
            
            $query = "SELECT b.*, c.name as category_name, c.slug as category_slug
                     FROM {$wpdb->prefix}block_beverages b
                     LEFT JOIN {$wpdb->prefix}block_beverage_categories c ON b.category_id = c.id
                     WHERE {$where_clause}
                     ORDER BY b.sort_order ASC, b.name ASC";
            
            $beverages = $wpdb->get_results($wpdb->prepare($query, ...$params));
            
            // Formater les boissons pour le frontend
            $formatted_beverages = array();
            foreach ($beverages as $beverage) {
                $formatted_beverages[] = array(
                    'id' => intval($beverage->id),
                    'name' => $beverage->name,
                    'description' => $beverage->description,
                    'price' => floatval($beverage->price),
                    'volume' => $beverage->volume,
                    'volume_ml' => intval($beverage->volume_ml),
                    'alcohol_degree' => floatval($beverage->alcohol_degree),
                    'container_type' => $beverage->container_type,
                    'origin' => $beverage->origin,
                    'image_url' => $beverage->image_url ?: '',
                    'category' => $beverage->category_slug,
                    'category_name' => $beverage->category_name
                );
            }
            
            wp_send_json_success(array(
                'beverages' => $formatted_beverages,
                'category' => $category,
                'subcategory' => $subcategory,
                'count' => count($formatted_beverages)
            ));
            
        } catch (Exception $e) {
            Block_Traiteur_Logger::error('Erreur récupération boissons', array(
                'category' => $category,
                'subcategory' => $subcategory,
                'service_type' => $service_type,
                'error' => $e->getMessage()
            ));
            
            wp_send_json_error(__('Erreur lors de la récupération des boissons', 'block-traiteur'));
        }
    }

    /**
     * Générer un numéro de devis unique
     */
    private function generate_quote_number() {
        $prefix = 'BLK';
        $date = date('Ymd');
        $random = str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        
        $quote_number = $prefix . '-' . $date . '-' . $random;
        
        // Vérifier l'unicité
        global $wpdb;
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}block_quotes WHERE quote_number = %s",
            $quote_number
        ));
        
        if ($exists > 0) {
            // Régénérer si le numéro existe déjà
            return $this->generate_quote_number();
        }
        
        return $quote_number;
    }
}

// Initialiser le gestionnaire AJAX
new Block_Traiteur_Ajax_Handler();