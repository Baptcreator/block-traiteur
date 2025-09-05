=== Block Traiteur ===
Contributors: Baptcreator  
Version: 1.0.7
Tested up to: 6.4

Plugin de devis pour Block Street Food & Events - Test déploiement depuis Cursor local !
Dernière modification : $(date)

# Block Traiteur - Plugin WordPress 🍴

> **Système de devis en ligne professionnel pour services de traiteur événementiel**

[![Version](https://img.shields.io/badge/version-1.0.0-blue.svg)](https://github.com/block-strasbourg/block-traiteur)
[![WordPress](https://img.shields.io/badge/WordPress-6.0%2B-blue.svg)](https://wordpress.org/)
[![PHP](https://img.shields.io/badge/PHP-8.0%2B-purple.svg)](https://php.net/)
[![License](https://img.shields.io/badge/license-GPL%20v2%2B-green.svg)](LICENSE)

## 📖 Description

**Block Traiteur** est un plugin WordPress complet spécialement développé pour **Block Street Food & Events** à Strasbourg. Il permet la gestion complète des demandes de devis en ligne pour deux services principaux :

- 🏢 **Privatisation du restaurant** (10-30 personnes)
- 🚛 **Prestation remorque mobile** (20-100 personnes)

### ✨ Fonctionnalités Principales

- **🎯 Formulaire multi-étapes intelligent** avec logique conditionnelle
- **💰 Calculateur de prix en temps réel** avec breakdown détaillé
- **📅 Intégration Google Calendar** pour vérification des disponibilités
- **📄 Génération PDF automatique** des devis professionnels
- **📧 Système d'emails personnalisés** (client + admin)
- **🎨 Widget Elementor complet** avec contrôles visuels
- **📊 Interface d'administration avancée** pour la gestion des devis
- **🔐 Sécurité renforcée** (CSRF, XSS, rate limiting)
- **⚡ Performance optimisée** (cache multi-niveaux, lazy loading)

---

## 🚀 Installation

### Prérequis Système

| Composant | Version Minimale | Recommandée |
|-----------|------------------|-------------|
| **WordPress** | 6.0+ | 6.4+ |
| **PHP** | 8.0+ | 8.2+ |
| **MySQL/MariaDB** | 5.7+ / 10.3+ | 8.0+ / 10.6+ |
| **Mémoire PHP** | 128 MB | 256 MB+ |

### Extensions PHP Requises

```bash
# Extensions obligatoires
- mysqli ou pdo_mysql
- json
- curl
- gd ou imagick
- zip

# Extensions recommandées
- mbstring
- intl
- opcache
```

### Installation Standard

1. **Télécharger** le fichier ZIP du plugin
2. **Uploader** via `WordPress Admin → Extensions → Ajouter → Téléverser`
3. **Activer** le plugin depuis la liste des extensions
4. **Configurer** via `Block Traiteur → Paramètres`

### Installation Manuelle (FTP)

```bash
# 1. Extraire l'archive
unzip block-traiteur.zip

# 2. Uploader vers WordPress
cp -r block-traiteur/ /path/to/wordpress/wp-content/plugins/

# 3. Activer depuis l'admin WordPress
```

### Installation Développeur

```bash
# Cloner le repository
git clone https://github.com/block-strasbourg/block-traiteur.git
cd block-traiteur

# Installer les dépendances (si applicable)
composer install --no-dev

# Lien symbolique pour développement
ln -s /path/to/block-traiteur /path/to/wordpress/wp-content/plugins/
```

---

## ⚙️ Configuration

### 1. Configuration de Base

Rendez-vous dans **Block Traiteur → Paramètres → Général** :

```php
// Informations entreprise (obligatoire)
Nom de l'entreprise: "Block Street Food & Events"
Email de contact: "contact@block-strasbourg.fr"
Téléphone: "06 58 13 38 05"
Adresse: "6 allée Adèle Klein, 67000 Strasbourg"
```

### 2. Tarification

Configurez les prix dans l'onglet **Services** :

```php
// Prix de base
Restaurant (2h): 300€ TTC
Remorque (2h): 350€ TTC
Heure supplémentaire: 50€ TTC
Supplément +50 pers: 150€ TTC

// Zones de déplacement
0-30 km: Gratuit
30-50 km: +20€
50-100 km: +70€
100-150 km: +118€
```

### 3. Google Calendar (Optionnel)

Pour la synchronisation des disponibilités :

1. **Créer un projet** sur [Google Cloud Console](https://console.cloud.google.com/)
2. **Activer l'API** Google Calendar
3. **Générer une clé API** et l'ajouter dans les paramètres
4. **Configurer l'ID** du calendrier

```php
// Dans les paramètres
Google Calendar ID: "votre-email@gmail.com"
API Key: "AIzaSyXXXXXXXXXXXXXXXXXXXXXXXXXX"
```

### 4. Configuration Email

Configurez SMTP pour une meilleure délivrabilité :

```php
// Paramètres SMTP recommandés
Serveur: smtp.gmail.com
Port: 587
Chiffrement: TLS
Authentification: Oui
```

---

## 🎨 Utilisation

### Affichage avec Elementor

1. **Éditer une page** avec Elementor
2. **Rechercher** "Block Traiteur" dans les widgets
3. **Glisser-déposer** le widget sur la page
4. **Personnaliser** les options dans le panneau

### Affichage avec Shortcode

```php
// Shortcode basique
[block_traiteur_form]

// Avec options
[block_traiteur_form 
    default_service="restaurant" 
    show_progress="true" 
    show_calculator="true"
]
```

### Intégration dans un thème

```php
// Dans un template PHP
<?php
if (function_exists('block_traiteur_render_form')) {
    echo block_traiteur_render_form(array(
        'default_service' => 'remorque',
        'show_progress_bar' => true
    ));
}
?>
```

---

## 🛠️ API & Développement

### Hooks WordPress Disponibles

#### Actions

```php
// Lifecycle du plugin
do_action('block_traiteur_loaded');
do_action('block_traiteur_activated');

// Gestion des devis
do_action('block_traiteur_quote_created', $quote_id, $quote_data);
do_action('block_traiteur_quote_approved', $quote_id);
do_action('block_traiteur_quote_rejected', $quote_id);

// Emails et notifications
do_action('block_traiteur_email_sent', $email_type, $recipient);
do_action('block_traiteur_pdf_generated', $quote_id, $pdf_path);
```

#### Filtres

```php
// Modification des données
$quote_data = apply_filters('block_traiteur_quote_data', $quote_data);
$total_price = apply_filters('block_traiteur_calculate_price', $price, $quote_data);

// Personnalisation des templates
$template = apply_filters('block_traiteur_email_template', $template, $type);
$pdf_content = apply_filters('block_traiteur_pdf_content', $content, $quote_id);

// Validation des données
$is_valid = apply_filters('block_traiteur_validate_form', $is_valid, $form_data);
```

### Fonctions Utilitaires

```php
// Obtenir les paramètres du plugin
$settings = Block_Traiteur_Cache::get_settings();

// Calculer un prix
$calculator = new Block_Traiteur_Calculator();
$calculator->set_form_data($data);
$total = $calculator->get_total_price();

// Vérifier une disponibilité
$calendar = new Block_Traiteur_Calendar_Integration();
$available = $calendar->check_availability('2024-12-25 14:00:00', 3);

// Générer un PDF
$pdf_generator = new Block_Traiteur_PDF_Generator();
$pdf_path = $pdf_generator->generate_quote_pdf($quote_id);
```

---

## 📊 Structure de la Base de Données

### Tables Principales

```sql
-- Devis
wp_block_quotes
├── id (PRIMARY KEY)
├── quote_number (UNIQUE)
├── service_type (restaurant|remorque)
├── customer_name, customer_email, customer_phone
├── event_date, guest_count, duration
├── total_price, status
└── created_at, updated_at

-- Éléments de devis
wp_block_quote_items
├── id, quote_id (FOREIGN KEY)
├── product_id, product_name
├── quantity, unit_price
└── total_price

-- Boissons de devis
wp_block_quote_beverages
├── id, quote_id (FOREIGN KEY)
├── beverage_id, beverage_name
├── quantity, volume, unit_price
└── total_price

-- Produits et services
wp_block_products
├── id, name, description
├── category, service_type
├── base_price, status
└── created_at, updated_at

-- Gestion des boissons
wp_block_beverages
├── id, name, category
├── volume_options (JSON)
├── price_per_unit, status
└── created_at, updated_at

-- Codes postaux et distances
wp_block_postal_codes
├── postal_code (PRIMARY KEY)
├── city, distance_from_base
├── zone_category, travel_cost
└── last_updated

-- Configuration
wp_block_settings
├── setting_key (PRIMARY KEY)
├── setting_value (JSON)
└── last_updated

-- Logs et emails
wp_block_logs
wp_block_emails
```

### Index de Performance

```sql
-- Index optimisés pour les requêtes fréquentes
CREATE INDEX idx_quotes_status ON wp_block_quotes(status);
CREATE INDEX idx_quotes_service ON wp_block_quotes(service_type);
CREATE INDEX idx_quotes_date ON wp_block_quotes(event_date);
CREATE INDEX idx_quotes_created ON wp_block_quotes(created_at);
```

---

## 🎨 Personnalisation

### Surcharge des Templates

Créez un dossier dans votre thème :

```
themes/votre-theme/
└── block-traiteur/
    ├── form-main.php
    ├── form-steps/
    │   ├── step-service-choice.php
    │   ├── step-base-package.php
    │   └── ...
    └── emails/
        ├── quote-confirmation.php
        └── admin-notification.php
```

### CSS Personnalisé

```css
/* Variables CSS disponibles */
:root {
    --block-primary: #243127;      /* Vert foncé */
    --block-secondary: #FFB404;    /* Jaune/Orange */
    --block-accent: #EF3D1D;       /* Rouge */
    --block-light: #f8f9fa;
    --block-dark: #212529;
}

/* Surcharge des styles */
.block-traiteur-form {
    /* Vos styles personnalisés */
}

.block-traiteur-widget .btn-primary {
    background-color: var(--block-primary);
    border-color: var(--block-primary);
}

.block-traiteur-widget .btn-primary:hover {
    background-color: var(--block-secondary);
    border-color: var(--block-secondary);
}
```

### JavaScript Personnalisé

```javascript
// Étendre les fonctionnalités
document.addEventListener('DOMContentLoaded', function() {
    // Hook dans les événements du formulaire
    window.BlockTraiteur = window.BlockTraiteur || {};
    
    // Événement après calcul de prix
    document.addEventListener('block-traiteur:price-calculated', function(e) {
        console.log('Prix calculé:', e.detail.totalPrice);
        // Vos actions personnalisées
    });
    
    // Événement après soumission
    document.addEventListener('block-traiteur:quote-submitted', function(e) {
        console.log('Devis soumis:', e.detail.quoteNumber);
        // Analytics, conversions, etc.
    });
});
```

---

## 🔧 Maintenance & Performance

### Optimisations Recommandées

```php
// Configuration PHP optimale
memory_limit = 256M
max_execution_time = 60
upload_max_filesize = 32M
post_max_size = 32M

// Cache WordPress
define('WP_CACHE', true);
define('BLOCK_TRAITEUR_CACHE_ENABLED', true);

// Optimisation base de données
define('WP_CACHE_KEY_SALT', 'votre-cle-unique');
```

### Monitoring et Logs

```php
// Activer les logs de débogage
define('BLOCK_TRAITEUR_DEBUG', true);
define('WP_DEBUG_LOG', true);

// Emplacement des logs
/wp-content/uploads/block-traiteur/logs/
├── error.log          # Erreurs système
├── performance.log    # Métriques de performance
├── email.log         # Historique des emails
└── calendar.log      # Synchronisation calendrier
```

### Maintenance Automatique

Le plugin inclut des tâches de maintenance automatique :

- **Quotidien** : Nettoyage des devis expirés
- **Bi-quotidien** : Synchronisation Google Calendar
- **Hebdomadaire** : Optimisation des tables, nettoyage des logs
- **Mensuel** : Archivage des données anciennes

---

## 🛡️ Sécurité

### Mesures de Protection

- **✅ Validation stricte** de tous les inputs utilisateur
- **✅ Protection CSRF** avec nonces WordPress
- **✅ Échappement XSS** de toutes les sorties
- **✅ Préparation des requêtes SQL** contre l'injection
- **✅ Rate limiting** pour prévenir le spam
- **✅ Sanitisation** des données de formulaire
- **✅ Vérification des permissions** WordPress

### Configuration Sécurisée

```php
// Paramètres de sécurité recommandés
Rate Limiting: 5 tentatives / 5 minutes
CSRF Protection: Activé
Input Validation: Strict
File Upload: Désactivé (pas nécessaire)
SQL Prepared Statements: Oui
```

---

## 🐛 Débogage & Support

### Mode Debug

```php
// Activer le mode debug
define('BLOCK_TRAITEUR_DEBUG', true);
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

### Outils de Diagnostic

```php
// Via l'interface admin
Block Traiteur → Outils → Diagnostic

// Via code
$diagnostics = block_traiteur_run_diagnostics();
print_r($diagnostics);

// Vérification de santé
$health = Block_Traiteur_System::check_health();
```

### Logs Disponibles

```bash
# Logs spécifiques du plugin
tail -f /wp-content/uploads/block-traiteur/logs/error.log

# Logs WordPress généraux
tail -f /wp-content/debug.log

# Logs serveur web
tail -f /var/log/apache2/error.log  # Apache
tail -f /var/log/nginx/error.log    # Nginx
```

### Support Technique

Pour obtenir de l'aide :

1. **Vérifier les logs** d'erreur
2. **Exporter les informations** système via `Block Traiteur → Outils → Diagnostic`
3. **Contacter le support** avec les détails de l'erreur

---

## 📝 Changelog

### Version 1.0.0 (2024-12-XX)

#### ✨ Nouveautés
- **Formulaire multi-étapes** avec 6 étapes de configuration
- **Calculateur de prix temps réel** avec breakdown détaillé
- **Intégration Google Calendar** pour gestion des disponibilités
- **Widget Elementor** avec contrôles visuels complets
- **Interface d'administration** pour gestion des devis
- **Génération PDF automatique** avec templates personnalisables
- **Système d'emails** avec templates HTML
- **Cache multi-niveaux** pour optimisation des performances

#### 🔧 Technique
- **Architecture MVC** avec classes organisées
- **Base de données optimisée** avec 9 tables relationnelles
- **API RESTful** pour interactions AJAX
- **Sécurité renforcée** avec protection complète
- **Tests automatisés** avec suite de validation
- **Documentation complète** avec guides détaillés

#### 🎯 Spécifique Block Strasbourg
- **Configuration sur mesure** pour les services Block
- **Zones de déplacement** configurées pour la région Grand Est
- **Templates PDF** avec identité visuelle Block
- **Emails personnalisés** avec branding entreprise

---

## 📄 Licence

Ce plugin est développé spécifiquement pour **Block Street Food & Events** sous licence GPL v2+.

```
Block Traiteur WordPress Plugin
Copyright (C) 2024 Block Street Food & Events

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
```

---

## 👥 Crédits

**Développé pour :** Block Street Food & Events  
**Adresse :** 6 allée Adèle Klein, 67000 Strasbourg  
**Site web :** [block-strasbourg.fr](https://block-strasbourg.fr)  
**Téléphone :** 06 58 13 38 05

### Technologies Utilisées

- **WordPress** 6.4+ - CMS
- **PHP** 8.2+ - Backend
- **MySQL** 8.0+ - Base de données
- **JavaScript ES6+** - Frontend interactif
- **Elementor** - Constructeur de page
- **TCPDF** - Génération PDF
- **Google Calendar API** - Synchronisation calendrier

---

## 🔗 Liens Utiles

- 📚 **[Documentation Technique](docs/technical.md)**
- 🎨 **[Guide de Personnalisation](docs/customization.md)**
- 🔌 **[API Reference](docs/api.md)**
- 🛠️ **[Guide de Débogage](docs/debugging.md)**
- 🏗️ **[Architecture du Plugin](docs/architecture.md)**

---

<div align="center">

**Fait avec ❤️ pour Block Street Food & Events**

*Plugin WordPress professionnel pour la gestion de devis traiteur événementiel*


</div>
