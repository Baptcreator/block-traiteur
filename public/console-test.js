/**
 * Test simple depuis la console du navigateur
 * À copier/coller dans F12 → Console sur votre site
 */

// Test AJAX simple depuis Console
const testAjaxPermissions = async () => {
    console.group('🧪 Test AJAX Permissions');
    
    // Obtenir un nonce (à remplacer par un vrai nonce)
    const nonce = 'abcdef123456'; // ⚠️ REMPLACER PAR UN NONCE RÉEL
    
    const actions = ['rbf_v3_load_step', 'rbf_v3_calculate_price', 'rbf_v3_submit_quote'];
    
    for (const action of actions) {
        console.group(`Test ${action}`);
        
        try {
            const formData = new FormData();
            formData.append('action', action);
            formData.append('nonce', nonce);
            formData.append('step_number', '1');
            formData.append('service_type', 'restaurant');
            formData.append('form_data', '{}');
            
            const response = await fetch('/wp-admin/admin-ajax.php', {
                method: 'POST',
                body: formData
            });
            
            const text = await response.text();
            
            console.log('Status:', response.status);
            console.log('Content-Type:', response.headers.get('content-type'));
            console.log('Response Preview:', text.substring(0, 200));
            
            // Vérifier le problème
            if (text.includes('Permissions insuffisantes')) {
                console.error('⚡ PROBLÈME DÉTECTÉ: Response "Permissions insuffisantes" en texte brut!');
            } else if (response.headers.get('content-type').includes('text/html')) {
                console.error('⚡ PROBLÈME DÉTECTÉ: Content-Type text/html au lieu de application/json!');
            } else {
                try {
                    const json = JSON.parse(text);
                    console.log('✅ JSON valide:', json);
                } catch (e) {
                    console.warn('⚠️ Pas JSON:', e.message);
                }
            }
            
        } catch (error) {
            console.error('❌ Erreur réseau:', error);
        }
        
        console.groupEnd();
    }
    
    console.groupEnd();
};

// Instructions pour obtenir un nonce
const getNonceInstructions = () => {
    console.log(`
🔑 Pour obtenir un nonce valide:
1. Aller sur votre site WordPress (page quelconque)
2. Ouvrir F12 → Console
3. Exécuter: console.log(restaurant_booking_ajax.nonce)
4. Ou: console.log(window.restaurant_booking_ajax?.nonce)
5. Copier cette valeur
6. Modifier la ligne dans ce script: const nonce = 'VOTRE_NONCE_ICI';
7. Relancer testAjaxPermissions()
    `);
};

console.log('🔑 Instructions pour obtenir nonce:');
getNonceInstructions();

console.log('🧪 Pour lancer le test: testAjaxPermissions()');

// Export pour usage
window.testAjaxPermissions = testAjaxPermissions;
