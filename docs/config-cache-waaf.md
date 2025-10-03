# Configuration Anti-Cache - WP Rocket / Autoptimize / Cloudflare

## Problème résolu
Sur Mac/iPhone, les réponses AJAX retournent "0" ou du HTML (cache) au lieu de JSON, causant les erreurs "réponse inattendue".

## Configuration requise

### 1. WP Rocket - Exclusion du Cache

**Pages à exclure :**
```
/wp-admin/admin-ajax.php
/page-with-formula-shortcode/
/restaurant-booking/
```

**Scripts à ne PAS différer :**
- `restaurant-booking-form-v3.js`
- `rbf-v3-calendar-widget.js`
- `jquery`

**Dans WP Rocket > File Optimization :**
☐ ✅ Minify HTML files (PROBLÉMATIQUE)
☐ ✅ Minify CSS files 
☐ ✅ Concatenate CSS
☐ ✅ Exclude CSS files
☐ ✅ Exclude JavaScript files (NOUS AJOUTER `restaurant-booking-form-v3.js`)

### 2. Autoptimize

**Exclusion JavaScript :**
```
restaurant-booking-form-v3.js
rbf-v3-calendar-widget.js
admin-ajax.php
wp-content/plugins/restaurant-booking-plugin/
```

**Exclusion CSS :**
```
restaurant-booking-form-v3.css
rbf-v3-calendar-widget.css
```

### 3. Cloudflare - Page Rules

**Règle 1 : Exclusion AJAX**
```
URL Pattern: *domain.com/wp-admin/admin-ajax.php*
Settings:
- Cache Level: Bypass
- Browser Cache TTL: Respect existing headers
- Edge Cache TTL: 2 minutes
```

**Règle 2 : Page du formulaire**  
```
URL Pattern: *domain.com/page-with-formula-shortcode/*
Settings:
- Cache Level: Bypass
- Browser Cache TTL: Respect existing headers
```

### 4. LiteSpeed Cache

**Exclusion par pages :**
```
/wp-admin/admin-ajax.php
```

**Exclusion CSS/JS :**
```
restaurant-booking-form-v3.css
restaurant-booking-form-v3.js
rbf-v3-calendar-widget.css
rbf-v3-calendar-widget.js
```

### 5. Configuration PHP (.htaccess)

**Ajouter dans .htaccess :**
```apache
# ✅ CORRECTION : Anti-cache pour AJAX du plugin Restaurant Booking
<IfModule mod_rewrite.c>
    RewriteCond %{HTTP_HOST} ^your-domain\.com$ [NC]
    RewriteCond %{REQUEST_URI} ^/wp-admin/admin-ajax\.php$ [NC]
    RewriteCond %{QUERY_STRING} action=rbf_v3_ [NC,OR]
    RewriteCond %{QUERY_STRING} action=calculate_delivery_distance [NC]
    RewriteRule ^(.*)$ - [E=nocache:1,L]
</IfModule>

<IfModule mod_headers.c>
    Header always unset Cache-Control env=nocache
    Header always set Cache-Control "no-cache, no-store, must-revalidate" env=nocache
    Header always set Pragma "no-cache" env=nocache
    Header always set Expires "Thu, 01 Jan 1970 00:00:00 GMT" env=nocache
</IfModule>
```

## Variables globales AJAX uniformisées

**Côté serveur PHP :**
```php
$unified_config = [
    'ajaxUrl' => admin_url('admin-ajax.php'),
    'ajax_url' => admin_url('admin-ajax.php'),
    'nonce' => wp_create_nonce('restaurant_booking_form_v3'), // Uniformisé
    'texts' => [ /* messages harmonisés */ ],
    'messages' => [ /* messages alternatifs */ ]
];
```

**Côté client JavaScript :**
```javascript
// Priorité de configuration unifiée :
// 1. restaurant_booking_ajax (module public)
// 2. rbfV3Config (formulaire V3)  
// 3. rbfV3Ajax (calendrier)
```

## Tests de validation

### Critère 1 : Network tab
✅ `Content-Type: application/json` sur tous les appels AJAX
✅ Body commence par `{` (JSON valide), jamais `<` (HTML)
✅ Aucun `body: "0"` sur Mac/iPhone

### Critère 2 : Console JavaScript  
✅ Aucune erreur TypeError sur `response.data.message`
✅ Messages "nonce invalide" clairs si problème de sécurité
✅ Logs détaillés des erreurs AJAX avec diagnostics

### Critère 3 : Headers HTTP
✅ `Cache-Control: no-cache, no-store, must-revalidate`
✅ `Pragma: no-cache`
✅ `Expires: Thu, 01 Jan 1970 00:00:00 GMT`

## Vérification finale

**Sur Mac Safari + Chrome :**
1. Charger la page du formulaire
2. Ouvrir Network tab
3. Cliquer sur une étape → Vérifier que `admin-ajax.php` retourne Content-Type: json
4. Si erreur → Console devrait afficher `🚨 AJAX Error - Diagnostics Mac/iOS`

**Résultat attendu :**
- ✅ Plus d'erreurs "réponse inattendue"  
- ✅ JSON propre sur tous les appels
- ✅ Navigation fluide entre étapes
- ✅ Calendrier fonctionne sans erreur
