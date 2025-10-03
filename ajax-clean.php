<?php
// Endpoint AJAX Propre - Contournement LiteSpeed

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');

// Charger WordPress
$wp_loaded = false;
$alt_paths = [
    '../../../wp-load.php',
    '../../../../wp-load.php',
    '../../../wp-config.php'
];

foreach ($alt_paths as $path) {
    if (file_exists($path)) {
        require_once($path);
        $wp_loaded = true;
        break;
    }
}

if (!$wp_loaded || !defined('ABSPATH')) {
    echo json_encode(['success' => false, 'data' => ['message' => 'WordPress inaccessible']]);
    exit;
}

$action = $_POST['action'] ?? '';
$nonce = $_POST['nonce'] ?? '';

$allowed_actions = [
    'rbf_v3_load_step',
    'rbf_v3_calculate_price',
    'rbf_v3_submit_quote',
    'rbf_v3_load_signature_products',
    'rbf_v3_get_month_availability',
    'rbf_v3_get_availability',
    'rbf_v3_calculate_distance'
];

// Fonction de reponse
function send_response($success, $data, $status_code = 200) {
    http_response_code($status_code);
    echo json_encode(['success' => $success, 'data' => $data]);
    exit;
}

// Verifications
if (!in_array($action, $allowed_actions)) {
    send_response(false, ['message' => 'Action non autorisee: ' . $action], 400);
}

if (empty($nonce) || !wp_verify_nonce($nonce, 'restaurant_booking_form_v3')) {
    send_response(false, ['message' => 'Nonce invalide'], 403);
}

// Definir DOING_AJAX
if (!defined('DOING_AJAX')) {
    define('DOING_AJAX', true);
}

try {
    if (!class_exists('RestaurantBooking_Ajax_Handler_V3')) {
        send_response(false, ['message' => 'Handler AJAX non disponible']);
    }
    
    $handler = new RestaurantBooking_Ajax_Handler_V3();
    ob_start();
    
    switch ($action) {
        case 'rbf_v3_load_step':
            $handler->load_step();
            break;
        case 'rbf_v3_calculate_price':
            $handler->calculate_price();
            break;
        case 'rbf_v3_submit_quote':
            $handler->submit_quote();
            break;
        case 'rbf_v3_load_signature_products':
            $handler->load_signature_products();
            break;
        case 'rbf_v3_get_month_availability':
            $handler->get_month_availability();
            break;
        case 'rbf_v3_get_availability':
            $handler->get_availability();
            break;
        case 'rbf_v3_calculate_distance':
            $handler->calculate_distance();
            break;
        default:
            send_response(false, ['message' => 'Action non reconnue: ' . $action]);
    }
    
    $output = ob_get_clean();
    
    // Si JSON valide, retourner tel quel
    if (json_decode($output)) {
        echo $output;
    } else {
        send_response(false, ['message' => 'Erreur execution', 'raw_output' => substr($output, 0, 100)]);
    }
    
} catch (Exception $e) {
    ob_end_clean();
    send_response(false, ['message' => 'Exception: ' . $e->getMessage()]);
} catch (Error $e) {
    ob_end_clean();
    send_response(false, ['message: ' . $e->getMessage()]);
}

exit;
?>
