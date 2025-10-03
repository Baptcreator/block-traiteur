# Tests des Endpoints AJAX - Compatibilité Mac/iOS

## Script de test manuel

### Test 1: Chargement d'étape (Most Critical)

**Endpoint:** `wp-admin/admin-ajax.php`
**Action:** `rbf_v3_load_step`

```javascript
// À exécuter dans la console du navigateur
$.ajax({
    url: '/wp-admin/admin-ajax.php',
    type: 'POST',
    data: {
        action: 'rbf_v3_load_step',
        nonce: 'REPLACE_WITH_REAL_NONCE',
        step_number: 1,
        service_type: 'restaurant',
        form_data: {}
    },
    success: function(response) {
        console.log('✅ SUCCESS:', response);
        console.log('Content-Type:', response.headers?.['content-type'] || 'unknown');
        if (response.success && response.data && response.data.html) {
            console.log('✅ HTML generé:', response.data.html.length, 'chars');
        }
    },
    error: function(xhr, status, error) {
        console.group('❌ ERROR');
        console.log('Status:', xhr.status, xhr.statusText);
        console.log('Content-Type:', xhr.getResponseHeader('Content-Type'));
        console.log('Response:', xhr.responseText);
        console.groupEnd();
    }
});
```

### Test 2: Calendrier Availability

```javascript
$.ajax({
    url: '/wp-admin/admin-ajax.php',
    type: 'POST',
    data: {
        action: 'rbf_v3_get_availability',
        nonce: 'REPLACE_WITH_REAL_NONCE',
        start_date: '2024-01-01',
        end_date: '2024-01-31',
        service_type: 'restaurant'
    },
    success: function(response) {
        console.log('✅ Calendar SUCCESS:', response);
    },
    error: function(xhr, status, error) {
        console.group('❌ Calendar ERROR');
        console.log('Response:', xhr.responseText);
        console.groupEnd();
    }
});
```

### Test 3: Calcul Prix

```javascript
$.ajax({
    url: '/wp-admin/admin-ajax.php',
    type: 'POST',
    data: {
        action: 'rbf_v3_calculate_price',
        nonce: 'REPLACE_WITH_REAL_NONCE',
        service_type: 'restaurant',
        form_data: JSON.stringify({
            guest_count: 20,
            event_date: '2024-06-15'
        })
    },
    success: function(response) {
        console.log('✅ Price SUCCESS:', response);
    },
    error: function(xhr, status, error) {
        console.group('❌ Price ERROR');
        console.log('Response:', xhr.responseText);
        console.groupEnd();
    }
});
```

## Critères de validation

### ✅ Réponse attendue (Success)
```json
{
  "success": true,
  "data": { /* données spécifiques */ }
}
```

### ❌ Réponses problématiques

**1. Nonce invalide :**
```json
{
  "success": false,
  "data": {
    "message": "Erreur de sécurité (nonce invalide)"
  }
}
```

**2. Cache HTML (problème principal):**
```html
<!DOCTYPE html>
<html>...
```

**3. Valeur "0" (ancien WordPress):**
```
0
```

**4. Erreur 403 WAF:**
```html
<h1>Access Denied</h1>
<p>Your request was blocked by security rules</p>
```

## Checklist de test

### Préparation
- [ ] Obtenir un nonce valide depuis la console: `console.log(rbfV3Config.nonce)`
- [ ] Tester sur Mac (Chrome + Safari)
- [ ] Tester sur iPhone (Safari)
- [ ] Tester sur Windows (contrôle)

### Tests par endpoint

#### rbf_v3_load_step
- [ ] ✅ Retourne HTML étape (Mac/Chrome)
- [ ] ✅ Retourne HTML étape (Mac/Safari)  
- [ ] ✅ Retourne HTML étape (iPhone/Safari)
- [ ] ❌ Pas de réponse "0"
- [ ] ❌ Pas de réponse HTML cache
- [ ] ❌ Pas de 403 WAF

#### rbf_v3_get_availability  
- [ ] ✅ Retourne données calendrier (Mac/Chrome)
- [ ] ✅ Retourne données calendrier (Mac/Safari)
- [ ] ✅ Retourne données calendrier (iPhone/Safari)
- [ ] ❌ Pas d'erreur TypeError

#### rbf_v3_calculate_price
- [ ] ✅ Retourne prix calculé (tous plateformes)
- [ ] ❌ Pas de JSON parse error

#### rbf_v3_submit_quote
- [ ] ✅ Soumission réussie (tous plateformes)

### Network Tab verification

**Sur chaque appel AJAX :**
- [ ] Status: 200
- [ ] Content-Type: `application/json`
- [ ] Response Body commence par `{`
- [ ] Headers anti-cache présents
- [ ] Pas de "0" dans body  
- [ ] Pas de HTML dans body

### Console vérification

**Erreurs à éliminer :**
- [ ] `TypeError: undefined is not an object (evaluating 'response.data.message')`
- [ ] `SyntaxError: Unexpected token < in JSON...`
- [ ] `Erreur de communication avec le serveur (réponse inattendue)`

**Nouveaux logs attendus :**
- [ ] `🚨 AJAX Error - Diagnostics Mac/iOS` (en cas de problème)
- [ ] `🚨 JSON Parse Error - Diagnostics Mac/iOS` (si parsing échoue)

## Résolution des problèmes

### Si toujours "réponse inattendue"

1. **Vérifier la configuration des plugins de cache :**
   ```bash
   # WP Rocket
   wp-content/cache/
   wp-content/uploads/wp-rocket-config/
   
   # Vider ces dossiers et retester
   ```

2. **Vérifier les droits .htaccess :**
   ```bash
   chmod 644 .htaccess
   ```

3. **Activer le debug temporairement :**
   ```php
   // wp-config.php 
   define('WP_DEBUG', true);
   define('WP_DEBUG_LOG', true);
   ```

4. **Tester avec des nonces frais :**
   ```javascript
   // Rafraîchir la page et récupérer nouveau nonce
   location.reload();
   // Puis relancer les tests
   ```

### Si erreurs 403/5xx

1. **Whitelist dans WAF :**
   - Ajouter `/wp-admin/admin-ajax.php` aux exceptions
   - Ajouter le user-agent Safari/iOS si nécessaire

2. **Hosting cache :**
   - Contacter l'hébergeur pour exclusion AJAX
   - Vérifier les règles CDN
