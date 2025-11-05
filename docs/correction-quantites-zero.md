# Correction : Quantités à zéro au démarrage du formulaire

## ✅ Problème résolu

**Symptôme** : Le premier produit de la catégorie "Plats Signature DOG" affichait systématiquement une quantité de 2 au démarrage du formulaire, sans intervention de l'utilisateur.

## 🔍 Cause identifiée

Un **code de test oublié** dans la fonction `debugButtonsInStep3()` (ligne 2961-2965 du fichier JavaScript) cliquait automatiquement sur le premier bouton "+" après le chargement de l'étape 3 :

```javascript
// Code problématique (maintenant retiré)
const $firstPlusBtn = this.container.find('.rbf-v3-qty-plus').first();
if ($firstPlusBtn.length) {
    this.log('🧪 Test clic programmatique sur premier bouton +');
    $firstPlusBtn.trigger('click'); // ← Clic automatique !
}
```

Ce code était exécuté 200ms après le chargement de l'étape 3, ce qui ajoutait automatiquement 1 ou 2 unités au premier produit de la liste.

## 🛠️ Solutions implémentées

### 1. Retrait du code de test automatique (`assets/js/restaurant-booking-form-v3.js`)

**Ligne 2960-2962** : Suppression du code qui cliquait automatiquement sur le premier bouton.

```javascript
// ✅ CORRECTION : Code de test retiré - ne plus cliquer automatiquement sur le premier bouton
// Ce code de debug causait l'ajout automatique de 2 unités sur le premier produit
```

### 2. Nettoyage des données côté serveur (`public/class-ajax-handler-v3.php`)

**Ligne 210-233** : Nettoyage automatique des quantités de produits lors du premier chargement de l'étape 3.

```php
if ($step === 3) {
    $has_product_data = false;
    foreach ($form_data as $key => $value) {
        if (preg_match('/^(signature_|mini_boss_|accompaniment_|buffet_).+_qty$/', $key) && intval($value) > 0) {
            $has_product_data = true;
            break;
        }
    }
    
    if (!$has_product_data) {
        // Ne garder que les données de base
        $base_keys = ['service_type', 'guest_count', 'event_date', 'event_time', 'event_duration', 
                     'address', 'postal_code', 'city', 'has_parking', 'parking_info'];
        $clean_data = [];
        foreach ($base_keys as $base_key) {
            if (isset($form_data[$base_key])) {
                $clean_data[$base_key] = $form_data[$base_key];
            }
        }
        $form_data = $clean_data;
    }
}
```

### 3. Réinitialisation JavaScript au démarrage (`assets/js/restaurant-booking-form-v3.js`)

**Ligne 491-506** : Réinitialisation complète de `formData`, `priceData` et `beveragesDetails` lors de la sélection d'un service.

```javascript
this.formData = {
    service_type: service
};

this.priceData = {
    base: 0,
    supplements: 0,
    products: 0,
    total: 0
};

this.beveragesDetails = [];
```

### 4. Détection du premier chargement (`assets/js/restaurant-booking-form-v3.js`)

**Ligne 2965-2978** : La méthode `restoreQuantityValues()` vérifie s'il y a des données de produits avant de restaurer.

```javascript
const hasProductData = Object.keys(this.formData).some(key => 
    (key.startsWith('signature_') || key.startsWith('mini_boss_') || 
     key.startsWith('accompaniment_') || key.startsWith('buffet_')) && 
    key.endsWith('_qty') && 
    parseInt(this.formData[key]) > 0
);

if (!hasProductData) {
    this.log('Premier chargement détecté - pas de restauration de quantités');
    return;
}
```

## ✅ Résultat

- **Tous les produits démarrent à 0** ✅
- **Pas de clic automatique** ✅
- **Pas de quantités fantômes** ✅
- **Navigation arrière fonctionne** (les quantités sont bien restaurées) ✅

## 📝 Test de validation

1. Vider le cache du navigateur (`Ctrl + Shift + R`)
2. Ouvrir le formulaire de devis
3. Sélectionner un service (Restaurant ou Remorque)
4. Remplir les étapes 1 et 2
5. Arriver à l'étape 3 (Formules repas)
6. **Vérifier** : tous les produits ont une quantité à **0** ✅

## 📂 Fichiers modifiés

- `public/class-ajax-handler-v3.php` : Nettoyage des données côté serveur
- `assets/js/restaurant-booking-form-v3.js` : 
  - Retrait du code de test automatique
  - Réinitialisation complète au démarrage
  - Détection du premier chargement
- `public/class-shortcode-form-v3.php` : Mise à jour de la version (3.0.4)

## 📅 Date de résolution

5 novembre 2025

## 🔧 Version finale

**Version 3.0.4** - Fix du clic automatique sur le premier produit
