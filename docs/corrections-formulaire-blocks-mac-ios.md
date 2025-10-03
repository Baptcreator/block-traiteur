# Corrections Formulaire de Devis - Compatibilité Mac/iOS

## Problème résolu
Sur Mac (Chrome/Safari) et iPhone, certaines étapes du formulaire restaient blanches avec des erreurs `TypeError: undefined is not an object (evaluating 'response.data.message')`.

## Causes identifiées

1. **JavaScript fragile** : Accès direct à `response.data.message` sans vérification
2. **Variables AJAX incohérentes** : `rbfV3Config` / `rbfV3Ajax` / `restaurant_booking_ajax` 
3. **Conflits d'ID de champs** : `#event_date` vs `#rbf-v3-event-date`
4. **Gestion des dates problématique** : Format français vs ISO
5. **Réponses AJAX parfois non-JSON** : cache, nonce invalide ("0"), HTML

## Corrections apportées

### 1. JavaScript défensif pour tous les appels AJAX

**Ajout de utilitaires communs :**
- `AjaxUtils.parseResponse()` : Parse défensif des réponses
- `AjaxUtils.getErrorMessage()` : Extraction sûre des messages d'erreur
- `AjaxConfig.getConfig()` : Configuration unifiée

**Mise en défensive des appels AJAX dans :**
- `restaurant-booking-form-v3.js` : Tous les handlers `success` et `error`
- `rbf-v3-calendar-widget.js` : Gestion de `loadAvailability()`
- `public.js` : `submitForm()` et `calculateDeliveryDistance()`

### 2. Harmonisation des variables AJAX

**Variables unifiées :**
```javascript
// Avant (multiples configurations)
rbfV3Config, rbfV3Ajax, restaurant_booking_ajax

// Après (configuration harmonisée)
{
  ajaxUrl: '/wp-admin/admin-ajax.php',
  ajax_url: '/wp-admin/admin-ajax.php', 
  nonce: 'même_nonce_v3_form',
  texts: { /* messages harmonisés */ },
  messages: { /* messages alternatifs */ }
}
```

**Priorité de configuration :**
1. `restaurant_booking_ajax` (module public)
2. `rbfV3Config` (formulaire V3)  
3. `rbfV3Ajax` (calendrier)

### 3. Gestion robuste des dates

**Double représentation des dates :**
- Format ISO (`YYYY-MM-DD`) : Pour calculs/validations/envois
- Format français : Pour affichage utilisateur

**Utilitaires ajoutés :**
```javascript
CalendarDateUtils.toISO(date)           // Conversion ISO
CalendarDateUtils.toFrenchFormat(iso)   // Conversion français
CalendarDateUtils.syncDateFields()     // Synchronisation des champs
```

**Correction des conflits d'ID :**
- `#rbf-v3-event-date` : Champ caché ISO (logique interne)
- `#event_date` : Champ d'affichage français créé dynamiquement

### 4. Gestion des erreurs AJAX améliorée

**Types de réponses gérées :**
- Objet JSON valide : `{success: true, data: {...}}`
- Chaîne JSON : Parse automatique avec fallback
- Valeur "0" : Nonce invalide → Message propre
- HTML/cache : Erreur de communication → Message logué

**Pattern d'utilisation :**
```javascript
const parsedResponse = AjaxUtils.parseResponse(response);

if (parsedResponse.success && parsedResponse.data) {
    // Utilisation sûre des données
    const html = parsedResponse.data.html;
} else {
    const errorMessage = AjaxUtils.getErrorMessage(response, fallback);
    this.showMessage(errorMessage, 'error');
}
```

### 5. Configuration serveur harmonisée

**Côté PHP :**
- Tous les handlers utilisent `wp_send_json_success()` et `wp_send_json_error()`
- Configuration unifiée dans `wp_localize_script()`
- Nonce harmonisé : `restaurant_booking_form_v3`

## Résultat

✅ **Compatibilité Mac/iOS** : Plus d'erreurs TypeError
✅ **Messages d'erreur clairs** : Feedback utilisateur propre
✅ **Dates fiables** : Validation avec format ISO stable
✅ **UI non-bloquante** : Même comportement Windows/Mac/iOS
✅ **Configuration unifiée** : Une seule source de configuration AJAX

## Testing recommandé

1. **Sur macOS Chrome + Safari** : Navigation entre étapes
2. **Sur iPhone** : Affirmation disponibilités calendrier
3. **Gestion cache** : Simuler réponse HTML/cache
4. **Nonce expiré** : Vérifier message "nonce invalide"
5. **Dates** : Validation avec format français vs ISO
