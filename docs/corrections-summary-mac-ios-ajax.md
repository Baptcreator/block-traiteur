# Résumé des Corrections AJAX - Mac/iOS

## Problème initial
Sur Mac et iPhone, les appels AJAX retournent "0" ou du HTML (cache/WAF) au lieu de JSON, causant l'affichage permanent de "Erreur de communication... (réponse inattendue)".

## Solutions implémentées

### 1. ✅ Headers Anti-Cache Stricts (PHP)

**Ajout de `send_ajax_headers()` :**
```php
private function send_ajax_headers()
{
    header('Cache-Control: no-cache, no-store, must-revalidate, max-age=0');
    header('Cache-Control: post-check=0, pre-check=0', false);
    header('Pragma: no-cache');
    header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');
    header('Vary: User-Agent, Accept-Encoding');
    
    if (function_exists('header_remove')) {
        header_remove('Last-Modified');
        header_remove('ETag');
    }
}
```

**Tous handlers AJAX modifiés :**
- `load_step()` → `send_json_response(true, ['html' => $html])`
- `calculate_price()` → `send_json_response(true, $price_data)`
- `submit_quote()` → `send_json_response(true, ['quote_id' => $quote_id])`
- `get_availability()` → `send_json_response(true, $availability_data)`
- `calculate_distance()` → `send_json_response(true, $response_data)`

### 2. ✅ Nettoyage Sortie PHP

**Prévention pollution JSON :**
```php
private function clean_output_for_json()
{
    // Supprimer tous les output buffers
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    // Supprimer BOM UTF-8 et whitespace
    $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);
    
    // Logger les contaminations pour debug
    if (!empty($content) && !preg_match('/^\s*\{/', $content)) {
        error_log('Sortie PHP avant JSON: ' . substr($content, 0, 100));
    }
}
```

### 3. ✅ Nonces Robusta

**Gestion défensive :**
```php
// Avant : wp_verify_nonce($_POST['nonce'], 'restaurant_booking_form_v3')
// Après :
if (!wp_verify_nonce($_POST['nonce'] ?? '', 'restaurant_booking_form_v3')) {
    $this->send_json_response(false, ['message' => 'Erreur de sécurité (nonce invalide)']);
}
```

**Uniformité des actions :**
- Toutes les actions V3 utilisent `'restaurant_booking_form_v3'`
- Plus de mélange entre `'rbf_v3_form'` et autres

### 4. ✅ Configuration AJAX Unifiée

**Côté serveur (PHP) :**
```php
$unified_config = [
    'ajaxUrl' => admin_url('admin-ajax.php'),
    'ajax_url' => admin_url('admin-ajax.php'), // Double compatibilité
    'nonce' => wp_create_nonce('restaurant_booking_form_v3'),
    'texts' => [ /* messages V3 */ ],
    'messages' => [ /* messages legacy */ ]
];

// Appliqué à tous les scripts
wp_localize_script('restaurant-booking-form-v3', 'rbfV3Config', $unified_config);
wp_localize_script('rbf-v3-calendar-widget', 'rbfV3Ajax', $unified_config);
wp_localize_script('restaurant-booking-public', 'restaurant_booking_ajax', [
    'ajax_url' => admin_url('admin-ajax.php'),
    'ajaxUrl' => admin_url('admin-ajax.php'),
    'nonce' => wp_create_nonce('restaurant_booking_form_v3'), // Harmonisé
    'messages' => [ /* messages unifiés */ ]
]);
```

### 5. ✅ Journalisation Côté Client

**Diagnostics détaillés :**
```javascript
logAjaxError: function(request, xhr, status, error) {
    const logData = {
        url: request.url,
        status: xhr.status,
        contentType: xhr.getResponseHeader('Content-Type'),
        responseText: xhr.responseText ? xhr.responseText.substring(0, 200) : '(vide)',
        userAgent: navigator.userAgent,
        timestamp: new Date().toISOString()
    };
    
    console.group('🚨 AJAX Error - Diagnostics Mac/iOS');
    console.log('📱 User Agent:', logData.userAgent);
    console.log('📊 Status:', logData.status, logData.statusText);
    console.log('📄 Content-Type:', logData.contentType);
    console.log('📝 Response (200 premiers chars):', logData.responseText);
    console.groupEnd();
}
```

**Parse JSON défensive :**
```javascript
parseResponse: function(response) {
    if (typeof response === 'string') {
        if (response === '0') {
            return { success: false, data: { message: 'Erreur de sécurité (nonce invalide)' } };
        }
        
        try {
            return JSON.parse(response);
        } catch (e) {
            // Journalisation détaillée des erreurs de parsing
            console.group('🚨 JSON Parse Error - Diagnostics Mac/iOS');
            console.log('📝 Raw Response (preview):', response.substring(0, 200));
            console.log('⚠️ Parse Error:', e.message);
            console.groupEnd();
            
            return { 
                success: false, 
                data: { message: 'Erreur de communication avec le serveur (réponse inattendue)' } 
            };
        }
    }
}
```

### 6. ✅ Exclusion Cache (Configuration)

**Fichiers créés :**
- `wp-cache-config.php` : Headers anti-cache globaux
- `docs/config-cache-waaf.md` : Instructions pour WP Rocket/Autoptimize/Cloudflare

**Règles .htaccess :**
```apache
# Anti-cache pour AJAX du plugin Restaurant Booking
RewriteCond %{REQUEST_URI} ^/wp-admin/admin-ajax\.php$ [NC]
RewriteCond %{QUERY_STRING} action=rbf_v3_ [NC]
RewriteRule ^(.*)$ - [E=nocache:1,L]

Header always set Cache-Control "no-cache, no-store, must-revalidate" env=nocache
Header always set Pragma "no-cache" env=nocache
```

## Résultats attendus

### ✅ Critères de succès

**Network Tab :**
- `Content-Type: application/json` sur tous les appels AJAX
- Body commence par `{` (JSON valide), jamais `<` (HTML)
- Status 200, aucun 403/5xx
- Aucun `body: "0"` sur Mac/iPhone

**Console JavaScript :**
- ❌ Plus de `TypeError: undefined is not an object (evaluating 'response.data.message')`
- ❌ Plus de `SyntaxError: Unexpected token < in JSON...`
- ❌ Plus de `Erreur de communication... (réponse inattendue)`
- ✅ Messages d'erreur JSON clairs si problème (ex: "nonce invalide")

**Comportement :**
- ✅ Navigation fluide entre étapes sur Mac/iPhone
- ✅ Calendrier affiche disponibilités sans erreur
- ✅ Calcul de prix fonctionne sur toutes plateformes
- ✅ Soumission de devis réussie

### 🛠️ Debug en cas de problème

**1. Tests manuels :**
Voir `docs/test-ajax-endpoints.md` pour les scripts de test console

**2. Configuration cache :**
Voir `docs/config-cache-waaf.md` pour l'exclusion des plugins

**3. Logs serveur :**
```bash
tail -f wp-content/debug.log | grep "Restaurant Booking V3"
```

**4. Debug client :**
```javascript
// Activer le debug avancé
window.rbfDebugMode = true;

// Accéder aux erreurs stockées
console.log(window.rbfAjaxErrors);
```

## Statut final

✅ **Serveur** : Headers anti-cache, sortie propre, nonces robustes  
✅ **Client** : JavaScript défensif, configuration unifiée, journalisation défaillée  
✅ **Config** : Exclusion cache, documentation complète  
✅ **Tests** : Scripts de validation, critères clairs  

Le formulaire devrait maintenant fonctionner identiquement sur Windows et Mac/iOS sans erreurs "réponse inattendue".
