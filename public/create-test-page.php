<?php
/**
 * Script pour créer une page de test dans WordPress
 * À exécuter une fois pour créer la page de test
 */

// Charger WordPress
require_once('../../../wp-load.php');

// Créer une page de test temporaire
$page_title = 'Test AJAX Restaurant Booking Debug';
$page_content = '
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<div id="ajax-test-results"></div>
<button onclick="testAjaxPermissions()">🧪 Test AJAX Permissions</button>

<script>
const nonce = "' . wp_create_nonce('restaurant_booking_form_v3') . '";

async function testAjaxPermissions() {
    console.log("🧪 Test AJAX avec nonce:", nonce);
    
    const results = document.getElementById("ajax-test-results");
    results.innerHTML = "<p>🟡 Tests en cours...</p>";
    
    const actions = ["rbf_v3_load_step", "rbf_v3_calculate_price", "rbf_v3_submit_quote"];
    let html = "<h3>📊 Résultats des tests AJAX</h3>";
    
    for (const action of actions) {
        try {
            console.log("Test:", action);
            
            const formData = new FormData();
            formData.append("action", action);
            formData.append("nonce", nonce);
            formData.append("step_number", "1");
            formData.append("service_type", "restaurant");
            formData.append("form_data", "{}");
            
            const response = await fetch("/wp-admin/admin-ajax.php", {
                method: "POST",
                body: formData
            });
            
            const text = await response.text();
            
            let status = "❌ ERREUR";
            let details = "";
            
            console.log("Status:", response.status, "Response:", text.substring(0, 100));
            
            // Analyser la réponse
            if (response.status === 200) {
                if (text.includes("Permissions insuffisantes")) {
                    status = "⚡ PERMISSIONS DETECTÉES";
                    details = "Response en texte brut: " + text.substring(0, 100);
                } else {
                    try {
                        const json = JSON.parse(text);
                        status = json.success ? "✅ SUCCÈS" : "⚠️ ERREUR JSON";
                        details = JSON.stringify(json).substring(0, 200);
                    } catch (e) {
                        status = "❌ PAS JSON";
                        details = "Parse error: " + e.message + " | Body: " + text.substring(0, 100);
                    }
                }
            } else {
                status = "❌ HTTP " + response.status;
                details = text.substring(0) + 100;
            }
            
            html += `
                <div style="border: 1px solid #ccc; margin: 10px 0; padding: 10px;">
                    <h4>${action}</h4>
                    <p><strong>Status:</strong> ${status}</p>
                    <p><strong>HTTP:</strong> ${response.status} | <strong>Content-Type:</strong> ${response.headers.get("content-type")}</p>
                    <details><summary>Détails</summary><pre>${details}</pre></details>
                </div>
            `;
            
        } catch (error) {
            html += `
                <div style="border: 1px solid red; margin: 10px 0; padding: 10px;">
                    <h4>${action}</h4>
                    <p><strong>❌ ERREUR RÉSEAU:</strong> ${error.message}</p>
                </div>
            `;
        }
    }
    
    results.innerHTML = html;
}

// Test automatique au chargement
window.addEventListener("load", () => {
    setTimeout(testAjaxPermissions, 1000);
});
</script>
';

// Chercher si la page existe déjà
$existing_page = get_page_by_title($page_title);

if ($existing_page) {
    echo "Page existe déjà: <a href='" . get_permalink($existing_page->ID) . "' target='_blank'>Ouvrir la page de test</a>\n";
} else {
    // Créer la page
    $page_id = wp_insert_post([
        'post_title' => $page_title,
        'post_content' => $page_content,
        'post_status' => 'publish',
        'post_type' => 'page',
        'post_name' => 'test-ajax-restaurant-booking'
    ]);
    
    if ($page_id && !is_wp_error($page_id)) {
        echo "✅ Page de test créée !\n";
        echo "Lien: <a href='" . get_permalink($page_id) . "' target='_blank'>" . get_permalink($page_id) . "</a>\n";
        
        // Donner l'URL complète pour faciliter l'accès
        $test_url = home_url('/test-ajax-restaurant-booking/');
        echo "URL complète: " . $test_url . "\n";
        echo "\n📋 Instructions:\n";
        echo "1. Cliquer sur le lien ci-dessus\n";
        echo "2. Ouvrir en navigation privée pour tester utilisateurs non connectés\n";
        echo "3. Ouvrir F12 → Console pour voir les logs détaillés\n";
        echo "4. Le test démarre automatiquement\n";
        
    } else {
        echo "❌ Erreur création page: " . $page_id->get_error_message();
    }
}
?>
