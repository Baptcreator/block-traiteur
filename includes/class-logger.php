<?php
/**
 * Logger pour le plugin Restaurant Drawing Form
 */

if (!defined('ABSPATH')) {
    exit;
}

class RestaurantBooking_Logger {
    
    /**
     * Instance unique de la classe
     */
    private static $instance = null;
    
    /**
     * Obtenir l'instance unique
     */
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructeur privé
     */
    private function __construct() {
        // Initialisation si nécessaire
    }
    
    /**
     * Logger pour les erreurs
     */
    public function error($message, $context = array()) {
        $this->log('ERROR', $message, $context);
    }
    
    /**
     * Logger pour les avertissements
     */
    public function warning($message, $context = array()) {
        $this->log('WARNING', $message, $context);
    }
    
    /**
     * Logger pour les informations
     */
    public function info($message, $context = array()) {
        $this->log('INFO', $message, $context);
    }
    
    /**
     * Logger pour le debug
     */
    public function debug($message, $context = array()) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            $this->log('DEBUG', $message, $context);
        }
    }
    
    /**
     * Log principal
     */
    private function log($level, $message, $context = array()) {
        $timestamp = current_time('Y-m-d H:i:s');
        $context_string = !empty($context) ? ' | Context: ' . wp_json_encode($context) : '';
        
        $log_entry = sprintf(
            '[%s] [Restaurant Booking] [%s] %s%s',
            $timestamp,
            $level,
            $message,
            $context_string
        );
        
        // Utiliser error_log si WP_DEBUG_LOG est activé
        if (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
            error_log($log_entry);
        }
        
        // Enregistrer également dans un fichier personnalisé si possible
        $log_file = WP_CONTENT_DIR . '/logs/restaurant-booking.log';
        if (is_dir(WP_CONTENT_DIR . '/logs') || wp_mkdir_p(WP_CONTENT_DIR . '/logs')) {
            file_put_contents($log_file, $log_entry . "\n", FILE_APPEND | LOCK_EX);
        }
    }
    
    /**
     * Obtenir la taille des logs
     */
    public static function get_logs_size() {
        $size_bytes = 0;
        
        // Vérifier le fichier de log personnalisé
        $log_file = WP_CONTENT_DIR . '/logs/restaurant-booking.log';
        if (file_exists($log_file)) {
            $size_bytes += filesize($log_file);
        }
        
        return array(
            'size_bytes' => $size_bytes,
            'size_mb' => round($size_bytes / 1024 / 1024, 2)
        );
    }
}
?>