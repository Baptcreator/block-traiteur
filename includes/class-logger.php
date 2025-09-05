<?php
/**
 * Système de logs avancé pour Block Traiteur
 */

if (!defined('ABSPATH')) {
    exit;
}

class Block_Traiteur_Logger {
    
    private static $log_file;
    private static $max_file_size = 10485760; // 10MB
    private static $max_files = 5;
    
    /**
     * Initialisation
     */
    public static function init() {
        $upload_dir = wp_upload_dir();
        self::$log_file = $upload_dir['basedir'] . '/block-traiteur-logs/debug.log';
        
        // Créer le répertoire si nécessaire
        $log_dir = dirname(self::$log_file);
        if (!file_exists($log_dir)) {
            wp_mkdir_p($log_dir);
        }
    }
    
    /**
     * Log de debug (seulement si WP_DEBUG activé)
     */
    public static function debug($message, $context = array()) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            self::log('DEBUG', $message, $context);
        }
    }
    
    /**
     * Log d'information
     */
    public static function info($message, $context = array()) {
        self::log('INFO', $message, $context);
    }
    
    /**
     * Log d'avertissement
     */
    public static function warning($message, $context = array()) {
        self::log('WARNING', $message, $context);
    }
    
    /**
     * Log d'erreur
     */
    public static function error($message, $context = array()) {
        self::log('ERROR', $message, $context);
        
        // Envoyer email admin pour erreurs critiques
        if (defined('BLOCK_TRAITEUR_ERROR_EMAILS') && BLOCK_TRAITEUR_ERROR_EMAILS) {
            self::send_error_email($message, $context);
        }
    }
    
    /**
     * Log critique
     */
    public static function critical($message, $context = array()) {
        self::log('CRITICAL', $message, $context);
        
        // Toujours envoyer email pour les erreurs critiques
        self::send_error_email($message, $context);
    }
    
    /**
     * Écrire dans le log
     */
    private static function log($level, $message, $context = array()) {
        if (!self::$log_file) {
            self::init();
        }
        
        $timestamp = current_time('Y-m-d H:i:s');
        $context_str = !empty($context) ? ' ' . json_encode($context, JSON_UNESCAPED_UNICODE) : '';
        
        $log_entry = sprintf(
            "[%s] [%s] %s%s\n",
            $timestamp,
            $level,
            $message,
            $context_str
        );
        
        // Rotation des logs si nécessaire
        self::rotate_logs();
        
        // Écrire le log
        file_put_contents(self::$log_file, $log_entry, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Rotation des fichiers de log
     */
    private static function rotate_logs() {
        if (!file_exists(self::$log_file)) {
            return;
        }
        
        if (filesize(self::$log_file) > self::$max_file_size) {
            // Renommer le fichier actuel
            $backup_file = self::$log_file . '.1';
            rename(self::$log_file, $backup_file);
            
            // Décaler les anciens fichiers
            for ($i = self::$max_files; $i >= 1; $i--) {
                $old_file = self::$log_file . '.' . $i;
                if (file_exists($old_file)) {
                    if ($i === self::$max_files) {
                        unlink($old_file); // Supprimer le plus ancien
                    } else {
                        rename($old_file, self::$log_file . '.' . ($i + 1));
                    }
                }
            }
        }
    }
    
    /**
     * Envoyer email d'erreur à l'admin
     */
    private static function send_error_email($message, $context) {
        $admin_email = get_option('admin_email');
        $site_name = get_bloginfo('name');
        
        $subject = sprintf('[%s] Erreur Block Traiteur', $site_name);
        
        $body = sprintf(
            "Une erreur s'est produite dans le plugin Block Traiteur :\n\n" .
            "Message : %s\n" .
            "Contexte : %s\n" .
            "URL : %s\n" .
            "IP : %s\n" .
            "User Agent : %s\n" .
            "Timestamp : %s\n",
            $message,
            json_encode($context, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
            $_SERVER['REQUEST_URI'] ?? '',
            $_SERVER['REMOTE_ADDR'] ?? '',
            $_SERVER['HTTP_USER_AGENT'] ?? '',
            current_time('Y-m-d H:i:s')
        );
        
        wp_mail($admin_email, $subject, $body);
    }
    
    /**
     * Obtenir les logs récents
     */
    public static function get_recent_logs($lines = 100) {
        if (!file_exists(self::$log_file)) {
            return array();
        }
        
        $file = file(self::$log_file);
        return array_slice($file, -$lines);
    }
    
    /**
     * Vider les logs
     */
    public static function clear_logs() {
        if (file_exists(self::$log_file)) {
            unlink(self::$log_file);
        }
        
        // Supprimer aussi les backups
        $log_dir = dirname(self::$log_file);
        $backup_files = glob($log_dir . '/debug.log.*');
        foreach ($backup_files as $backup) {
            unlink($backup);
        }
    }
    
    /**
     * Obtenir la taille des logs
     */
    public static function get_logs_size() {
        $total_size = 0;
        
        if (file_exists(self::$log_file)) {
            $total_size += filesize(self::$log_file);
        }
        
        $log_dir = dirname(self::$log_file);
        $backup_files = glob($log_dir . '/debug.log.*');
        foreach ($backup_files as $backup) {
            $total_size += filesize($backup);
        }
        
        return $total_size;
    }
    
    /**
     * Formater la taille en unités lisibles
     */
    public static function format_size($size) {
        $units = array('B', 'KB', 'MB', 'GB');
        $unit = 0;
        
        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }
        
        return round($size, 2) . ' ' . $units[$unit];
    }
}

// Initialiser le logger
add_action('init', array('Block_Traiteur_Logger', 'init'));