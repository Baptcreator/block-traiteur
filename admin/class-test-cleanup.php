<?php
/**
 * Classe de nettoyage des fichiers de test AJAX
 */

if (!defined('ABSPATH')) {
    exit;
}

class RestaurantBooking_Test_Cleanup
{
    /**
     * Constructeur
     */
    public function __construct()
    {
        add_action('wp_ajax_restaurant_booking_cleanup_test_files', array($this, 'cleanup_test_files'));
    }

    /**
     * Liste des fichiers de test à nettoyer
     */
    private function get_test_files()
    {
        return [
            'test-wordpress-charge.php',
            'test-direct-endpoint.php',
            'test-ajax-endpoints.php',
            'test-simple-step.php',
            'test-barebones-ajax.php',
            'ajax-endpoint-bypass.php',
            'ajax-endpoint-bypass-fixed.php',
            'simple-bypass.php',
            'test-minimal.php',
            'test-bypass-endpoint.php',
            'test-bypass-final.php',
            'test-complet-final.php',
            'check-security-plugins.php',
            'check-server-config.php',
            'check-ajax-hooks.php',
            'deep-search-permissions.php',
            'diagnostic-ajax-config.php',
            'simple-handler-test.php',
            'theme-ajax-endpoint.php'
        ];
    }

    /**
     * Nettoyer les fichiers de test
     */
    public function cleanup_test_files()
    {
        check_ajax_referer('restaurant_booking_debug', 'nonce');
        
        if (!current_user_can('manage_options')) {
            gp_send_json_error('Permissions insuffisantes');
        }

        $files_cleaned = [];
        $files_errors = [];
        $plugin_dir = RESTAURANT_BOOKING_PLUGIN_DIR;

        foreach ($this->get_test_files() as $file) {
            $file_path = $plugin_dir . $file;
            if (file_exists($file_path)) {
                if (unlink($file_path)) {
                    $files_cleaned[] = $file;
                } else {
                    $files_errors[] = $file . ' (unable to delete)';
                }
            }
        }

        $result = [
            'cleaned' => count($files_cleaned),
            'files_cleaned' => $files_cleaned,
            'files_errors' => $files_errors,
            'message' => count($files_cleaned) . ' fichiers de test nettoyés'
        ];

        wp_send_json_success($result);
    }

    /**
     * Vérifier quels fichiers de test existent encore
     */
    public static function get_existing_test_files()
    {
        $test_files = [
            'test-wordpress-charge.php',
            'test-direct-endpoint.php', 
            'test-ajax-endpoints.php',
            'ajax-endpoint-bypass.php',
            'ajax-endpoint-bypass-fixed.php',
            'simple-bypass.php',
            'test-minimal.php',
            'test-bypass-endpoint.php',
            'test-bypass-final.php',
            'test-complet-final.php',
            'check-security-plugins.php',
            'check-server-config.php',
            'check-ajax-hooks.php',
            'deep-search-permissions.php',
            'diagnostic-ajax-config.php',
            'simple-handler-test.php',
            'theme-ajax-endpoint.php'
        ];

        $existing = [];
        $plugin_dir = RESTAURANT_BOOKING_PLUGIN_DIR;

        foreach ($test_files as $file) {
            if (file_exists($plugin_dir . $file)) {
                $existing[] = $file;
            }
        }

        return $existing;
    }
}

// Initialiser le nettoyage s'il n'y a pas déjà de classe de nettoyage
if (!class_exists('RestaurantBooking_Cleanup_Admin')) {
    new RestaurantBooking_Test_Cleanup();
}
?>
