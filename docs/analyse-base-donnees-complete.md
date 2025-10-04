# 🔍 Analyse Complète de la Base de Données - Restaurant Block

**Date d'analyse :** 04/10/2025 10:45:47  
**Serveur :** block-streetfood.fr  
**Analysé par :** Script automatique d'analyse DB  

---

## 📊 Informations Générales de la Base de Données

| Paramètre | Valeur |
|-----------|--------|
| **Nom de la base** | u844876091_da32M |
| **Hôte** | 127.0.0.1 |
| **Utilisateur** | u844876091_JvLvO |
| **Charset** | utf8 |
| **Collate** | (vide) |
| **Préfixe des tables** | wp_ |
| **Version MySQL** | 11.8.3-MariaDB-log |

---

## 🏠 Tables WordPress Standard

| Table | Nombre d'enregistrements | Taille | Statut |
|-------|-------------------------|---------|---------|
| wp_posts | 679 | 4.64 MB | ✅ OK |
| wp_postmeta | 4,512 | 41.72 MB | ✅ OK |
| wp_users | 5 | 0.06 MB | ✅ OK |
| wp_usermeta | 159 | 0.08 MB | ✅ OK |
| wp_options | 668 | 2.59 MB | ✅ OK |
| wp_terms | 17 | 0.05 MB | ✅ OK |
| wp_term_taxonomy | 17 | 0.05 MB | ✅ OK |
| wp_term_relationships | 50 | 0.03 MB | ✅ OK |

**📈 Statistiques WordPress :**
- **Total des enregistrements :** 6,107
- **Taille totale :** ~49.22 MB
- **Santé générale :** ✅ Excellente

---

## 🍽️ Tables du Plugin Restaurant Block

### Vue d'ensemble des tables

| Table | Description | Enregistrements | Taille | Dernière MAJ |
|-------|-------------|----------------|--------|--------------|
| wp_restaurant_accompaniment_options | Options d'accompagnement | 3 | 61.44 KB | 2025-10-04 10:19:22 |
| wp_restaurant_accompaniment_suboptions | Sous-options d'accompagnement | 0 | N/A | N/A |
| wp_restaurant_availability | Disponibilités/Planning | 4 | 112.64 KB | 2025-09-29 12:02:36 |
| wp_restaurant_available_containers | Contenants disponibles | 2 | 61.44 KB | 2025-10-04 10:21:45 |
| wp_restaurant_beer_types | Types de bières | 4 | 61.44 KB | 2025-09-29 14:47:57 |
| wp_restaurant_beverage_sizes | Tailles de boissons | 1 | 61.44 KB | 2025-10-04 10:20:06 |
| wp_restaurant_categories | Catégories de produits | 11 | 112.64 KB | 2025-09-29 17:36:56 |
| wp_restaurant_delivery_zones | Zones de livraison | 4 | 61.44 KB | 2025-09-05 16:45:38 |
| wp_restaurant_keg_sizes | Tailles de fûts | 3 | 81.92 KB | 2025-10-04 10:22:01 |
| wp_restaurant_logs | Logs système | 25,176 | 5.55 MB | 2025-10-03 13:32:04 |
| wp_restaurant_products | Produits et menus | 11 | 112.64 KB | 2025-10-04 10:22:21 |
| wp_restaurant_product_supplements_v2 | Suppléments produits V2 | 0 | N/A | N/A |
| wp_restaurant_quotes | Devis clients | 3 | 92.16 KB | 2025-10-04 10:32:29 |
| wp_restaurant_settings | Paramètres du plugin | 69 | 61.44 KB | 2025-10-01 22:53:59 |
| wp_restaurant_subcategories | Sous-catégories | 12 | 61.44 KB | 2025-10-04 10:40:06 |
| wp_restaurant_wine_types | Types de vins | 4 | 61.44 KB | 2025-10-03 14:02:43 |

**📊 Totaux :**
- **16 tables** spécialisées
- **25,307 enregistrements** au total
- **~6.53 MB** de données

---

## 🔍 Analyse Détaillée des Tables Supplémentaires

### 🍺 Table: restaurant_beer_types

**Structure :**
- **4 enregistrements** - Types de bières configurés
- Colonnes : id, name, slug, description, display_order, is_active, created_at, updated_at

**Données :**
| ID | Nom | Slug | Description | Ordre | Actif |
|----|-----|------|-------------|-------|-------|
| 1 | Blonde | blonde | Bières blondes | 1 | ✅ |
| 2 | Blanche | blanche | Bières blanches | 2 | ✅ |
| 3 | Brune | brune | Bières brunes | 3 | ✅ |
| 4 | IPA | ipa | India Pale Ale | 4 | ✅ |

### 🍷 Table: restaurant_wine_types

**Structure :**
- **4 enregistrements** - Types de vins configurés
- Colonnes : id, name, slug, description, display_order, is_active, created_at, updated_at

**Données :**
| ID | Nom | Slug | Description | Ordre | Actif |
|----|-----|------|-------------|-------|-------|
| 1 | Rouge | rouge | Vins rouges | 1 | ✅ |
| 4 | Crémant | cremant | Vins effervescents | 4 | ✅ |
| 6 | Alsace | alsace |  | 24 | ✅ |
| 7 | Champagne | champagne |  | 34 | ✅ |

---

## 📂 Analyse des Catégories de Produits

| ID | Nom | Type | Service | Produits | Requis | Min/Max | Actif |
|----|-----|------|---------|----------|---------|---------|-------|
| 100 | Plats Signature DOG | plat_signature_dog | both | 1 | ❌ | 0/∞ | ✅ |
| 101 | Plats Signature CROQ | plat_signature_croq | both | 1 | ❌ | 0/∞ | ✅ |
| 102 | Menu Enfant (Mini Boss) | mini_boss | both | 1 | ❌ | 0/∞ | ✅ |
| 103 | Accompagnements | accompagnement | both | 1 | ❌ | 0/∞ | ✅ |
| 104 | Buffet Salé | buffet_sale | both | 1 | ❌ | 0/∞ | ✅ |
| 105 | Buffet Sucré | buffet_sucre | both | 1 | ❌ | 0/∞ | ✅ |
| 106 | Boissons Soft | soft | both | 1 | ❌ | 0/∞ | ✅ |
| 109 | Bières Bouteilles | biere_bouteille | both | 1 | ❌ | 0/∞ | ✅ |
| 110 | Fûts de Bière | fut | remorque | 1 | ❌ | 0/∞ | ✅ |
| 111 | Jeux et Animations |  | remorque | 1 | ❌ | 0/∞ | ✅ |
| 112 | Vins |  | both | 1 | ❌ | 0/∞ | ✅ |

### 📈 Statistiques des Catégories

- **Service "both" :** 9 catégories (9 actives, 0 requises)
- **Service "remorque" :** 2 catégories (2 actives, 0 requises)
- **Total :** 11 catégories (11 actives, 0 obligatoires)

---

## 🍕 Analyse des Produits

### Statistiques par catégorie

| Catégorie | Type | Nb Produits | Produits Actifs | Prix Min | Prix Moyen | Prix Max |
|-----------|------|-------------|-----------------|----------|------------|----------|
| Plats Signature DOG | plat_signature_dog | 1 | 1 | 11,50€ | 11,50€ | 11,50€ |
| Plats Signature CROQ | plat_signature_croq | 1 | 1 | 11,50€ | 11,50€ | 11,50€ |
| Menu Enfant (Mini Boss) | mini_boss | 1 | 1 | 11,50€ | 11,50€ | 11,50€ |
| Accompagnements | accompagnement | 1 | 1 | 11,50€ | 11,50€ | 11,50€ |
| Buffet Salé | buffet_sale | 1 | 1 | 11,50€ | 11,50€ | 11,50€ |
| Buffet Sucré | buffet_sucre | 1 | 1 | 11,50€ | 11,50€ | 11,50€ |
| Boissons Soft | soft | 1 | 1 | 2,50€ | 2,50€ | 2,50€ |
| Bières Bouteilles | biere_bouteille | 1 | 1 | 11,50€ | 11,50€ | 11,50€ |
| Fûts de Bière | fut | 1 | 1 | 0,00€ | 0,00€ | 0,00€ |
| Jeux et Animations |  | 1 | 1 | 55,50€ | 55,50€ | 55,50€ |
| Vins |  | 1 | 1 | 11,50€ | 11,50€ | 11,50€ |

### 🔍 Détail des 20 derniers produits créés

| ID | Nom | Catégorie | Prix | Unité | Supplément | Actif | Créé le |
|----|-----|-----------|------|--------|------------|-------|----------|
| 131 | test jeu | N/A | 55,50€ |  | - | ✅ | 04/10/2025 |
| 121 | test dog | N/A | 11,50€ |  | - | ✅ | 30/11/-0001 |
| 122 | test croq | N/A | 11,50€ |  | - | ✅ | 30/11/-0001 |
| 123 | test mini boss | N/A | 11,50€ |  | - | ✅ | 30/11/-0001 |
| 124 | test accomp | N/A | 11,50€ |  | - | ✅ | 30/11/-0001 |
| 125 | test buffet salé | N/A | 11,50€ |  | - | ✅ | 30/11/-0001 |
| 126 | test buffet sucré | N/A | 11,50€ |  | - | ✅ | 30/11/-0001 |
| 127 | test soft | N/A | 2,50€ |  | - | ✅ | 30/11/-0001 |
| 128 | test vin | N/A | 11,50€ |  | - | ✅ | 30/11/-0001 |
| 129 | test biere | N/A | 11,50€ |  | - | ✅ | 30/11/-0001 |
| 130 | test fut | N/A | 0,00€ |  | - | ✅ | 30/11/-0001 |

---

## ⚙️ Paramètres du Plugin

### 📋 Groupe: Constraints

| Clé | Valeur | Type | Description |
|-----|--------|------|-------------|
| remorque_max_delivery_distance | 150 | number | N/A |
| remorque_max_guests | 100 | number | N/A |
| remorque_max_hours | 5 | number | N/A |
| remorque_min_guests | 20 | number | N/A |
| restaurant_max_guests | 30 | number | N/A |
| restaurant_max_hours | 4 | number | N/A |
| restaurant_min_guests | 10 | number | N/A |

### 📧 Groupe: Emails

| Clé | Valeur | Type | Description |
|-----|--------|------|-------------|
| admin_notification_emails | ["admin@restaurant-block.fr"] | json | N/A |
| email_quote_body_html | <p>Madame, Monsieur,</p><p>Nous vous remercions po... | html | N/A |
| email_quote_footer_html | <div style="font-size: 12px; color: #666; margin-t... | html | N/A |
| email_quote_header_html | <div style="text-align: center; padding: 20px;"><h... | html | N/A |
| email_quote_subject | Votre devis privatisation Block | text | N/A |

### 📝 Groupe: Forms

| Clé | Valeur | Type | Description |
|-----|--------|------|-------------|
| form_date_label | Date souhaitée événement | text | N/A |
| form_duration_label | Durée souhaitée événement | text | N/A |
| form_guests_label | Nombre de convives | text | N/A |
| form_postal_label | Commune événement | text | N/A |
| form_step1_title | Forfait de base | text | N/A |
| form_step2_title | Choix des formules repas | text | N/A |
| form_step3_title | Choix des boissons | text | N/A |
| form_step4_title | Coordonnées / Contact | text | N/A |

### 🏠 Groupe: General

| Clé | Valeur | Type | Description |
|-----|--------|------|-------------|
| restaurant_postal_code | 67000 | text | N/A |

### 🎨 Groupe: Interface

| Clé | Valeur | Type | Description |
|-----|--------|------|-------------|
| homepage_button_booking | Réserver à table | text | N/A |
| homepage_button_infos | Infos | text | N/A |
| homepage_button_menu | Voir le menu | text | N/A |
| homepage_button_privatiser | Privatiser Block | text | N/A |
| homepage_restaurant_description | Découvrez notre cuisine authentique dans un cadre... | html | N/A |
| homepage_restaurant_title | LE RESTAURANT | text | N/A |
| homepage_traiteur_title | LE TRAITEUR ÉVÉNEMENTIEL | text | N/A |
| traiteur_remorque_description | Notre remorque mobile se déplace pour vos événe... | html | N/A |
| traiteur_remorque_subtitle | À partir de 20 personnes | text | N/A |
| traiteur_remorque_title | Privatisation de la remorque Block | text | N/A |
| traiteur_restaurant_description | Privatisez notre restaurant pour vos événements ... | html | N/A |
| traiteur_restaurant_subtitle | De 10 à 30 personnes | text | N/A |
| traiteur_restaurant_title | Privatisation du restaurant | text | N/A |

### ⚠️ Groupe: Messages

| Clé | Valeur | Type | Description |
|-----|--------|------|-------------|
| error_date_unavailable | Cette date n'est pas disponible | text | N/A |
| error_duration_max | Durée maximum : {max} heures | text | N/A |
| error_guests_max | Nombre maximum de convives : {max} | text | N/A |
| error_guests_min | Nombre minimum de convives : {min} | text | N/A |
| error_selection_required | Sélection obligatoire | text | N/A |

### 💰 Groupe: Pricing

| Clé | Valeur | Type | Description |
|-----|--------|------|-------------|
| delivery_zone_100_150_price | 120.00 | number | N/A |
| delivery_zone_30_50_price | 20.00 | number | N/A |
| delivery_zone_50_100_price | 70.00 | number | N/A |
| hourly_supplement | 50.00 | number | N/A |
| remorque_50_guests_supplement | 150.00 | number | N/A |
| remorque_base_price | 350 | text | N/A |
| remorque_games_base_price | 70.00 | number | N/A |
| remorque_included_hours | 2 | number | N/A |
| remorque_tireuse_price | 50.00 | number | N/A |
| restaurant_base_price | 300 | text | N/A |
| restaurant_included_hours | 2 | number | N/A |

### 📋 Groupe: Widget_texts

| Clé | Valeur | Type | Description |
|-----|--------|------|-------------|
| quote_success_message | Votre devis est d'ores et déjà disponible dans v... | html | N/A |
| remorque_step1_process_list | ["Forfait de base", "Choix du formule repas (perso... | json | N/A |
| remorque_step1_title | Pourquoi privatiser notre remorque Block ? | text | N/A |
| remorque_step2_card_title | FORFAIT DE BASE PRIVATISATION REMORQUE BLOCK | text | N/A |
| remorque_step2_included_items | ["Notre équipe salle + cuisine assurant la presta... | json | N/A |
| remorque_step2_title | FORFAIT DE BASE | text | N/A |
| restaurant_step1_card_title | Comment ça fonctionne ? | text | N/A |
| restaurant_step1_process_list | ["Forfait de base", "Choix du formule repas (perso... | json | N/A |
| restaurant_step1_title | Pourquoi privatiser notre restaurant ? | text | N/A |
| restaurant_step2_card_title | FORFAIT DE BASE PRIVATISATION RESTO | text | N/A |
| restaurant_step2_included_items | ["Mise à disposition des murs de Block", "Notre �... | json | N/A |
| restaurant_step2_title | FORFAIT DE BASE | text | N/A |
| widget_remorque_card_description | Notre remorque mobile se déplace pour vos événe... | html | N/A |
| widget_remorque_card_subtitle | À partir de 20 personnes | text | N/A |
| widget_remorque_card_title | Privatisation de la remorque Block | text | N/A |
| widget_restaurant_card_description | Privatisez notre restaurant pour vos événements ... | html | N/A |
| widget_restaurant_card_subtitle | De 10 à 30 personnes | text | N/A |
| widget_restaurant_card_title | PRIVATISATION DU RESTAURANT | text | N/A |
| widget_service_selection_title | Choisissez votre service | text | N/A |

---

## 📋 Analyse des Devis

### Statistiques par statut et service

| Statut | Service | Nombre | Prix Moyen | Total CA | Convives Moyen |
|--------|---------|--------|------------|----------|----------------|
| sent | remorque | 3 | 1 747,87€ | 5 243,60€ | 21 |

### 🕐 10 Derniers Devis

| N° Devis | Service | Date Événement | Convives | Prix Total | Statut | Créé le |
|----------|---------|----------------|----------|------------|--------|----------|
| BLOCK-2025-3390 | remorque | 15/10/2025 | 20 | 1 872,50€ | sent | 04/10/2025 10:32 |
| BLOCK-2025-6437 | remorque | 23/10/2025 | 20 | 1 811,80€ | sent | 04/10/2025 09:58 |
| BLOCK-2025-5388 | remorque | 30/10/2025 | 24 | 1 559,30€ | sent | 03/10/2025 17:46 |

---

## 📝 Logs et Activité Récente

### Statistiques des logs

| Niveau | Nombre | Dernier log |
|--------|--------|-------------|
| 🐛 DEBUG | 22,290 | 03/10/2025 10:11 |
| ℹ️ INFO | 2,674 | 03/10/2025 13:32 |
| ⚠️ WARNING | 175 | 02/10/2025 08:07 |
| ❌ ERROR | 37 | 03/10/2025 10:06 |

### 📋 15 Derniers Logs

| Niveau | Message | Date | Utilisateur | IP |
|--------|---------|------|-------------|-----|
| ℹ️ info | Nouveau produit créé: 1664 | 03/10/2025 13:32:04 | 2 | 2a02:8424:9004:8d01:e595:21ca:4da6:d8bd |
| ℹ️ info | Début sauvegarde bière | 03/10/2025 13:32:04 | 2 | 2a02:8424:9004:8d01:e595:21ca:4da6:d8bd |
| ℹ️ info | Nouveau produit créé: Bière belge | 03/10/2025 13:30:03 | 2 | 2a02:8424:9004:8d01:e595:21ca:4da6:d8bd |
| ℹ️ info | Début sauvegarde bière | 03/10/2025 13:30:03 | 2 | 2a02:8424:9004:8d01:e595:21ca:4da6:d8bd |
| ℹ️ info | Produit mis à jour: Lagunidas | 03/10/2025 13:29:12 | 2 | 2a02:8424:9004:8d01:e595:21ca:4da6:d8bd |
| ℹ️ info | Début sauvegarde bière | 03/10/2025 13:29:12 | 2 | 2a02:8424:9004:8d01:e595:21ca:4da6:d8bd |
| ℹ️ info | Nouveau produit créé: Lagunidas | 03/10/2025 13:29:07 | 2 | 2a02:8424:9004:8d01:e595:21ca:4da6:d8bd |
| ℹ️ info | Début sauvegarde bière | 03/10/2025 13:29:07 | 2 | 2a02:8424:9004:8d01:e595:21ca:4da6:d8bd |
| ℹ️ info | Produit supprimé: lagunidas | 03/10/2025 13:28:27 | 2 | 2a02:8424:9004:8d01:e595:21ca:4da6:d8bd |
| ℹ️ info | Produit supprimé: 1664 | 03/10/2025 13:28:25 | 2 | 2a02:8424:9004:8d01:e595:21ca:4da6:d8bd |
| ℹ️ info | Produit supprimé: Kronenbourg | 03/10/2025 13:28:23 | 2 | 2a02:8424:9004:8d01:e595:21ca:4da6:d8bd |
| ℹ️ info | Produit supprimé: Heineken | 03/10/2025 13:28:20 | 2 | 2a02:8424:9004:8d01:e595:21ca:4da6:d8bd |
| ℹ️ info | Produit mis à jour: Mini wraps saumon & fromage frais | 03/10/2025 13:23:49 | 2 | 2a02:8424:9004:8d01:e595:21ca:4da6:d8bd |
| ℹ️ info | Sauvegarde de configuration effectuée | 03/10/2025 13:23:18 |  | 2a02:4780:27:1234::af |
| ℹ️ info | Nettoyage automatique effectué | 03/10/2025 13:23:18 |  | 2a02:4780:27:1234::af |

---

## 📊 Résumé Exécutif

### 📈 DONNÉES GÉNÉRALES
- **Catégories actives :** 11
- **Produits actifs :** 11
- **Total devis générés :** 3
- **Chiffre d'affaires total :** 5 243,60€
- **Panier moyen :** 1 747,87€

### 🎯 SERVICES DISPONIBLES
- **Both :** 9 catégories
- **Remorque :** 2 catégories

### 📈 PERFORMANCE DEVIS
- **Sent :** 3 (100%)

### 🔧 ÉTAT TECHNIQUE
- **Tables plugin :** 16 tables créées
- **Dernière activité :** 2025-10-04 10:45:47

### ✅ STATUT GLOBAL
**Plugin configuré et opérationnel**

---

## 🔍 Insights et Recommandations

### ✅ Points Forts
1. **Structure complète :** 16 tables bien organisées
2. **Configuration riche :** 8 groupes de paramètres configurés
3. **Système de logs actif :** 25,176 entrées de monitoring
4. **Gestion fine des produits :** Suppléments, tailles, options
5. **Planning synchronisé :** Intégration Google Calendar

### ⚠️ Points d'Attention
1. **Erreurs récentes :** 37 erreurs dans les logs
2. **Warnings fréquents :** 175 avertissements
3. **Peu de devis :** Seulement 3 devis générés
4. **Catégories non requises :** 0 catégorie obligatoire configurée

### 🚀 Recommandations
1. **Corriger les erreurs** de création de produits
2. **Résoudre les warnings** de catégories manquantes  
3. **Optimiser le taux de conversion** des devis
4. **Configurer des catégories obligatoires** selon les besoins
5. **Surveiller les performances** avec plus de devis réels

---

**📅 Document généré le :** 04/10/2025  
**🔄 Prochaine analyse recommandée :** Dans 1 mois  
**📧 Contact support :** Pour toute question technique