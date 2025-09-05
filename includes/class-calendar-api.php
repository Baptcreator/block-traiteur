<?php
/**
 * Classe d'intégration Google Calendar API pour Block Traiteur
 *
 * @package Block_Traiteur
 * @subpackage Includes
 * @since 1.0.0
 */

// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Classe Block_Traiteur_Calendar_Integration
 * 
 * Gère l'intégration avec Google Calendar pour la vérification des disponibilités
 */
class Block_Traiteur_Calendar_Integration {
    
    /**
     * URL de base de l'API Google Calendar
     */
    private $api_base_url = 'https://www.googleapis.com/calendar/v3/';
    
    /**
     * Clé API Google
     */
    private $api_key;
    
    /**
     * ID du calendrier Google
     */
    private $calendar_id;
    
    /**
     * Token d'accès OAuth2
     */
    private $access_token;
    
    /**
     * Token de rafraîchissement
     */
    private $refresh_token;
    
    /**
     * ID client OAuth2
     */
    private $client_id;
    
    /**
     * Secret client OAuth2
     */
    private $client_secret;
    
    /**
     * Constructeur
     */
    public function __construct() {
        $this->load_settings();
        $this->init_hooks();
    }
    
    /**
     * Charger les paramètres depuis la base de données
     */
    private function load_settings() {
        $this->api_key = get_option('block_traiteur_google_api_key', '');
        $this->calendar_id = get_option('block_traiteur_google_calendar_id', '');
        $this->access_token = get_option('block_traiteur_google_access_token', '');
        $this->refresh_token = get_option('block_traiteur_google_refresh_token', '');
        $this->client_id = get_option('block_traiteur_google_client_id', '');
        $this->client_secret = get_option('block_traiteur_google_client_secret', '');
    }
    
    /**
     * Initialiser les hooks WordPress
     */
    private function init_hooks() {
        // Hook pour les actions AJAX
        add_action('wp_ajax_block_traiteur_google_auth', array($this, 'handle_google_auth'));
        add_action('wp_ajax_block_traiteur_check_availability', array($this, 'handle_availability_check'));
        add_action('wp_ajax_nopriv_block_traiteur_check_availability', array($this, 'handle_availability_check'));
        
        // Hook pour synchronisation automatique
        add_action('block_traiteur_sync_calendar', array($this, 'sync_events'));
    }
    
    /**
     * Vérifier si l'API est configurée
     */
    public function is_configured() {
        return !empty($this->api_key) && !empty($this->calendar_id);
    }
    
    /**
     * Vérifier si l'authentification OAuth2 est active
     */
    public function is_authenticated() {
        return !empty($this->access_token) && !empty($this->refresh_token);
    }
    
    /**
     * Obtenir l'URL d'autorisation OAuth2
     */
    public function get_auth_url() {
        if (empty($this->client_id)) {
            return false;
        }
        
        $params = array(
            'client_id' => $this->client_id,
            'redirect_uri' => admin_url('admin.php?page=block-traiteur-settings&tab=calendar'),
            'scope' => 'https://www.googleapis.com/auth/calendar.readonly',
            'response_type' => 'code',
            'access_type' => 'offline',
            'prompt' => 'consent'
        );
        
        return 'https://accounts.google.com/o/oauth2/auth?' . http_build_query($params);
    }
    
    /**
     * Échanger le code d'autorisation contre un token d'accès
     */
    public function exchange_code_for_token($code) {
        if (empty($this->client_id) || empty($this->client_secret)) {
            return new WP_Error('missing_credentials', 'Identifiants OAuth2 manquants');
        }
        
        $response = wp_remote_post('https://oauth2.googleapis.com/token', array(
            'body' => array(
                'client_id' => $this->client_id,
                'client_secret' => $this->client_secret,
                'code' => $code,
                'grant_type' => 'authorization_code',
                'redirect_uri' => admin_url('admin.php?page=block-traiteur-settings&tab=calendar')
            ),
            'timeout' => 30
        ));
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (empty($data['access_token'])) {
            return new WP_Error('token_error', 'Impossible d\'obtenir le token d\'accès');
        }
        
        // Sauvegarder les tokens
        update_option('block_traiteur_google_access_token', $data['access_token']);
        if (!empty($data['refresh_token'])) {
            update_option('block_traiteur_google_refresh_token', $data['refresh_token']);
        }
        
        $this->access_token = $data['access_token'];
        if (!empty($data['refresh_token'])) {
            $this->refresh_token = $data['refresh_token'];
        }
        
        return true;
    }
    
    /**
     * Rafraîchir le token d'accès
     */
    private function refresh_access_token() {
        if (empty($this->refresh_token) || empty($this->client_id) || empty($this->client_secret)) {
            return false;
        }
        
        $response = wp_remote_post('https://oauth2.googleapis.com/token', array(
            'body' => array(
                'client_id' => $this->client_id,
                'client_secret' => $this->client_secret,
                'refresh_token' => $this->refresh_token,
                'grant_type' => 'refresh_token'
            ),
            'timeout' => 30
        ));
        
        if (is_wp_error($response)) {
            return false;
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (empty($data['access_token'])) {
            return false;
        }
        
        // Mettre à jour le token d'accès
        $this->access_token = $data['access_token'];
        update_option('block_traiteur_google_access_token', $data['access_token']);
        
        return true;
    }
    
    /**
     * Effectuer une requête à l'API Google Calendar
     */
    private function make_api_request($endpoint, $params = array()) {
        if (!$this->is_configured()) {
            return new WP_Error('not_configured', 'API Google Calendar non configurée');
        }
        
        $url = $this->api_base_url . $endpoint;
        
        // Ajouter la clé API aux paramètres
        $params['key'] = $this->api_key;
        
        // Si on a un token d'accès, l'utiliser au lieu de la clé API
        $headers = array();
        if (!empty($this->access_token)) {
            $headers['Authorization'] = 'Bearer ' . $this->access_token;
            unset($params['key']); // Retirer la clé API si on utilise OAuth2
        }
        
        $url .= '?' . http_build_query($params);
        
        $response = wp_remote_get($url, array(
            'headers' => $headers,
            'timeout' => 30
        ));
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        
        // Si le token a expiré, essayer de le rafraîchir
        if ($response_code === 401 && !empty($this->access_token)) {
            if ($this->refresh_access_token()) {
                // Relancer la requête avec le nouveau token
                $headers['Authorization'] = 'Bearer ' . $this->access_token;
                $response = wp_remote_get($url, array(
                    'headers' => $headers,
                    'timeout' => 30
                ));
                
                if (!is_wp_error($response)) {
                    $response_code = wp_remote_retrieve_response_code($response);
                    $body = wp_remote_retrieve_body($response);
                }
            }
        }
        
        if ($response_code !== 200) {
            return new WP_Error('api_error', 'Erreur API: ' . $response_code . ' - ' . $body);
        }
        
        return json_decode($body, true);
    }
    
    /**
     * Obtenir les événements du calendrier pour une période donnée
     */
    public function get_events($start_date, $end_date) {
        $endpoint = 'calendars/' . urlencode($this->calendar_id) . '/events';
        
        $params = array(
            'timeMin' => date('c', strtotime($start_date)),
            'timeMax' => date('c', strtotime($end_date)),
            'singleEvents' => 'true',
            'orderBy' => 'startTime'
        );
        
        $result = $this->make_api_request($endpoint, $params);
        
        if (is_wp_error($result)) {
            error_log('Block Traiteur Calendar API: ' . $result->get_error_message());
            return array();
        }
        
        return isset($result['items']) ? $result['items'] : array();
    }
    
    /**
     * Vérifier la disponibilité pour une date et heure données
     */
    public function check_availability($event_date, $event_duration = 2) {
        // Calculer les timestamps de début et fin
        $start_time = strtotime($event_date);
        $end_time = $start_time + ($event_duration * 3600); // durée en heures
        
        // Récupérer les événements pour cette journée
        $start_date = date('Y-m-d 00:00:00', $start_time);
        $end_date = date('Y-m-d 23:59:59', $start_time);
        
        $events = $this->get_events($start_date, $end_date);
        
        if (empty($events)) {
            return array(
                'available' => true,
                'message' => 'Créneau disponible'
            );
        }
        
        // Vérifier les conflits
        foreach ($events as $event) {
            if (empty($event['start']) || empty($event['end'])) {
                continue;
            }
            
            $event_start = strtotime($event['start']['dateTime'] ?? $event['start']['date']);
            $event_end = strtotime($event['end']['dateTime'] ?? $event['end']['date']);
            
            // Vérifier s'il y a un chevauchement
            if (($start_time < $event_end) && ($end_time > $event_start)) {
                return array(
                    'available' => false,
                    'message' => 'Créneau non disponible',
                    'conflict_event' => $event['summary'] ?? 'Événement sans titre'
                );
            }
        }
        
        return array(
            'available' => true,
            'message' => 'Créneau disponible'
        );
    }
    
    /**
     * Obtenir les créneaux occupés pour un mois donné
     */
    public function get_busy_slots($year, $month) {
        $start_date = sprintf('%04d-%02d-01 00:00:00', $year, $month);
        $end_date = date('Y-m-t 23:59:59', strtotime($start_date));
        
        $events = $this->get_events($start_date, $end_date);
        
        $busy_slots = array();
        
        foreach ($events as $event) {
            if (empty($event['start']) || empty($event['end'])) {
                continue;
            }
            
            $start = $event['start']['dateTime'] ?? $event['start']['date'];
            $end = $event['end']['dateTime'] ?? $event['end']['date'];
            
            $busy_slots[] = array(
                'start' => $start,
                'end' => $end,
                'title' => $event['summary'] ?? 'Événement',
                'all_day' => isset($event['start']['date'])
            );
        }
        
        return $busy_slots;
    }
    
    /**
     * Synchroniser les événements et mettre à jour le cache
     */
    public function sync_events() {
        if (!$this->is_configured()) {
            return false;
        }
        
        try {
            // Synchroniser les 3 prochains mois
            $events_data = array();
            
            for ($i = 0; $i < 3; $i++) {
                $year = (int) date('Y', strtotime("+{$i} months"));
                $month = (int) date('m', strtotime("+{$i} months"));
                
                $busy_slots = $this->get_busy_slots($year, $month);
                $events_data["{$year}-{$month}"] = $busy_slots;
            }
            
            // Mettre à jour le cache
            set_transient('block_traiteur_calendar_events', $events_data, 3600); // 1 heure
            
            // Log de synchronisation
            error_log('Block Traiteur: Synchronisation calendrier réussie - ' . count($events_data) . ' mois synchronisés');
            
            return true;
            
        } catch (Exception $e) {
            error_log('Block Traiteur: Erreur synchronisation calendrier - ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtenir les événements depuis le cache
     */
    public function get_cached_events($year, $month) {
        $cached_data = get_transient('block_traiteur_calendar_events');
        
        if (empty($cached_data)) {
            // Si pas de cache, synchroniser
            $this->sync_events();
            $cached_data = get_transient('block_traiteur_calendar_events');
        }
        
        $key = "{$year}-{$month}";
        return isset($cached_data[$key]) ? $cached_data[$key] : array();
    }
    
    /**
     * Gestionnaire AJAX pour l'authentification Google
     */
    public function handle_google_auth() {
        if (!current_user_can('manage_options')) {
            wp_die('Permissions insuffisantes');
        }
        
        check_ajax_referer('block_traiteur_admin', 'nonce');
        
        $code = sanitize_text_field($_POST['code'] ?? '');
        
        if (empty($code)) {
            wp_send_json_error('Code d\'autorisation manquant');
        }
        
        $result = $this->exchange_code_for_token($code);
        
        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }
        
        wp_send_json_success('Authentification réussie');
    }
    
    /**
     * Gestionnaire AJAX pour vérification de disponibilité
     */
    public function handle_availability_check() {
        // Vérifier le nonce
        if (!wp_verify_nonce($_POST['nonce'], 'block_traiteur_ajax')) {
            wp_send_json_error('Nonce invalide');
        }
        
        $event_date = sanitize_text_field($_POST['eventDate'] ?? '');
        $event_duration = (int) ($_POST['eventDuration'] ?? 2);
        
        if (empty($event_date)) {
            wp_send_json_error('Date manquante');
        }
        
        // Vérifier le format de date
        $timestamp = strtotime($event_date);
        if (!$timestamp) {
            wp_send_json_error('Format de date invalide');
        }
        
        $availability = $this->check_availability($event_date, $event_duration);
        
        wp_send_json_success($availability);
    }
    
    /**
     * Tester la connexion à l'API
     */
    public function test_connection() {
        if (!$this->is_configured()) {
            return array(
                'success' => false,
                'message' => 'Configuration incomplète'
            );
        }
        
        $result = $this->make_api_request('calendars/' . urlencode($this->calendar_id));
        
        if (is_wp_error($result)) {
            return array(
                'success' => false,
                'message' => $result->get_error_message()
            );
        }
        
        return array(
            'success' => true,
            'message' => 'Connexion réussie',
            'calendar_name' => $result['summary'] ?? 'Calendrier'
        );
    }
    
    /**
     * Obtenir les statistiques d'utilisation
     */
    public function get_usage_stats() {
        $stats = array(
            'configured' => $this->is_configured(),
            'authenticated' => $this->is_authenticated(),
            'last_sync' => get_option('block_traiteur_last_calendar_sync', 'Jamais'),
            'cached_months' => 0,
            'total_events' => 0
        );
        
        $cached_data = get_transient('block_traiteur_calendar_events');
        if (!empty($cached_data)) {
            $stats['cached_months'] = count($cached_data);
            
            foreach ($cached_data as $month_data) {
                $stats['total_events'] += count($month_data);
            }
        }
        
        return $stats;
    }
    
    /**
     * Réinitialiser la configuration
     */
    public function reset_configuration() {
        $options_to_delete = array(
            'block_traiteur_google_api_key',
            'block_traiteur_google_calendar_id',
            'block_traiteur_google_access_token',
            'block_traiteur_google_refresh_token',
            'block_traiteur_google_client_id',
            'block_traiteur_google_client_secret',
            'block_traiteur_last_calendar_sync'
        );
        
        foreach ($options_to_delete as $option) {
            delete_option($option);
        }
        
        // Supprimer le cache
        delete_transient('block_traiteur_calendar_events');
        
        error_log('Block Traiteur: Configuration Google Calendar réinitialisée');
    }
}