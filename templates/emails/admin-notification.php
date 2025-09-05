<?php
/**
 * Template d'email de notification admin pour nouvelle demande de devis
 *
 * @package Block_Traiteur
 * @subpackage Templates/Emails
 * @since 1.0.0
 * 
 * Variables disponibles :
 * @var array $quote Données complètes du devis
 * @var array $quote_items Éléments du devis (produits)
 * @var array $quote_beverages Boissons du devis
 * @var array $settings Paramètres du plugin
 * @var string $admin_url URL d'administration pour gérer le devis
 */

// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit;
}

// Préparer les données pour le template de base
$email_title = sprintf(__('Nouvelle demande de devis #%s', 'block-traiteur'), $quote['quote_number']);

// Formatage des données
$event_date = new DateTime($quote['event_date']);
$formatted_event_date = $event_date->format('l j F Y à H\hi');
$formatted_event_date_fr = strtr($formatted_event_date, [
    'Monday' => 'Lundi', 'Tuesday' => 'Mardi', 'Wednesday' => 'Mercredi', 
    'Thursday' => 'Jeudi', 'Friday' => 'Vendredi', 'Saturday' => 'Samedi', 'Sunday' => 'Dimanche',
    'January' => 'janvier', 'February' => 'février', 'March' => 'mars', 'April' => 'avril',
    'May' => 'mai', 'June' => 'juin', 'July' => 'juillet', 'August' => 'août',
    'September' => 'septembre', 'October' => 'octobre', 'November' => 'novembre', 'December' => 'décembre'
]);

$service_type_label = $quote['service_type'] === 'restaurant' ? 'Restaurant' : 'Remorque';
$total_price_formatted = number_format($quote['total_price'], 2, ',', ' ') . ' €';

// Date de création
$created_date = new DateTime($quote['created_at']);
$formatted_created_date = $created_date->format('d/m/Y à H:i');

// Calcul du délai avant événement
$now = new DateTime();
$days_until_event = $now->diff($event_date)->days;
$is_urgent = $days_until_event <= 7;

// URL d'administration
$admin_quote_url = admin_url('admin.php?page=block-traiteur-quotes&action=view&id=' . $quote['id']);

// Début du contenu de l'email
ob_start();
?>

<!-- Alerte urgence si nécessaire -->
<?php if ($is_urgent): ?>
<div class="email-alert error">
    <h3 style="margin: 0 0 10px 0;">🚨 <?php _e('DEMANDE URGENTE', 'block-traiteur'); ?></h3>
    <p style="margin: 0;">
        <strong><?php printf(__('Événement dans %d jour(s) seulement !', 'block-traiteur'), $days_until_event); ?></strong><br>
        <?php _e('Cette demande nécessite un traitement prioritaire.', 'block-traiteur'); ?>
    </p>
</div>
<?php else: ?>
<div class="email-alert info">
    <h3 style="margin: 0 0 10px 0;">📋 <?php _e('Nouvelle demande de devis', 'block-traiteur'); ?></h3>
    <p style="margin: 0;">
        <?php printf(__('Événement prévu dans %d jour(s)', 'block-traiteur'), $days_until_event); ?>
    </p>
</div>
<?php endif; ?>

<p>
    <?php _e('Une nouvelle demande de devis vient d\'être soumise via le site web. Voici les détails de la demande à traiter :', 'block-traiteur'); ?>
</p>

<!-- Informations client -->
<h2>👤 <?php _e('Informations client', 'block-traiteur'); ?></h2>

<table class="email-table">
    <tbody>
        <tr>
            <td style="font-weight: 600; width: 150px;"><?php _e('Nom complet', 'block-traiteur'); ?></td>
            <td><?php echo esc_html($quote['customer_name']); ?></td>
        </tr>
        <tr>
            <td style="font-weight: 600;"><?php _e('Email', 'block-traiteur'); ?></td>
            <td>
                <a href="mailto:<?php echo esc_attr($quote['customer_email']); ?>?subject=<?php echo urlencode('Devis Block Traiteur #' . $quote['quote_number']); ?>">
                    <?php echo esc_html($quote['customer_email']); ?>
                </a>
            </td>
        </tr>
        <?php if (!empty($quote['customer_phone'])): ?>
        <tr>
            <td style="font-weight: 600;"><?php _e('Téléphone', 'block-traiteur'); ?></td>
            <td>
                <a href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', $quote['customer_phone'])); ?>">
                    <?php echo esc_html($quote['customer_phone']); ?>
                </a>
            </td>
        </tr>
        <?php endif; ?>
        <?php if (!empty($quote['customer_address'])): ?>
        <tr>
            <td style="font-weight: 600;"><?php _e('Adresse', 'block-traiteur'); ?></td>
            <td>
                <?php echo esc_html($quote['customer_address']); ?>
                <?php if (!empty($quote['customer_postal_code']) && !empty($quote['customer_city'])): ?>
                    <br><?php echo esc_html($quote['customer_postal_code'] . ' ' . $quote['customer_city']); ?>
                <?php endif; ?>
            </td>
        </tr>
        <?php endif; ?>
        <tr>
            <td style="font-weight: 600;"><?php _e('Demande reçue le', 'block-traiteur'); ?></td>
            <td><?php echo $formatted_created_date; ?></td>
        </tr>
    </tbody>
</table>

<!-- Détails de l'événement -->
<h2>🎉 <?php _e('Détails de l\'événement', 'block-traiteur'); ?></h2>

<div class="email-alert <?php echo $is_urgent ? 'warning' : 'info'; ?>">
    <table style="width: 100%; border: none;">
        <tr>
            <td style="border: none; padding: 5px 15px 5px 0; font-weight: 600; width: 150px;">
                <?php _e('Date et heure :', 'block-traiteur'); ?>
            </td>
            <td style="border: none; padding: 5px 0;">
                <strong><?php echo esc_html($formatted_event_date_fr); ?></strong>
                <?php if ($is_urgent): ?>
                    <span style="color: #dc3545; font-weight: bold;"> ⚠️ URGENT</span>
                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <td style="border: none; padding: 5px 15px 5px 0; font-weight: 600;">
                <?php _e('Service demandé :', 'block-traiteur'); ?>
            </td>
            <td style="border: none; padding: 5px 0;">
                <strong><?php echo esc_html($service_type_label); ?></strong>
            </td>
        </tr>
        <tr>
            <td style="border: none; padding: 5px 15px 5px 0; font-weight: 600;">
                <?php _e('Nombre d\'invités :', 'block-traiteur'); ?>
            </td>
            <td style="border: none; padding: 5px 0;">
                <strong><?php echo (int) $quote['guest_count']; ?></strong> <?php _e('personne(s)', 'block-traiteur'); ?>
            </td>
        </tr>
        <tr>
            <td style="border: none; padding: 5px 15px 5px 0; font-weight: 600;">
                <?php _e('Durée prévue :', 'block-traiteur'); ?>
            </td>
            <td style="border: none; padding: 5px 0;">
                <strong><?php echo (int) $quote['event_duration']; ?></strong> <?php _e('heure(s)', 'block-traiteur'); ?>
            </td>
        </tr>
        <tr>
            <td style="border: none; padding: 5px 15px 5px 0; font-weight: 600;">
                <?php _e('Montant estimé :', 'block-traiteur'); ?>
            </td>
            <td style="border: none; padding: 5px 0;">
                <strong style="font-size: 18px; color: #28a745;"><?php echo $total_price_formatted; ?></strong>
            </td>
        </tr>
    </table>
</div>

<!-- Commande détaillée -->
<h2>🛒 <?php _e('Détail de la commande', 'block-traiteur'); ?></h2>

<?php if (!empty($quote_items)): ?>
<h3><?php _e('Produits et services sélectionnés', 'block-traiteur'); ?></h3>

<table class="email-table">
    <thead>
        <tr>
            <th><?php _e('Produit/Service', 'block-traiteur'); ?></th>
            <th style="text-align: center; width: 80px;"><?php _e('Qté', 'block-traiteur'); ?></th>
            <th style="text-align: right; width: 100px;"><?php _e('Prix unit.', 'block-traiteur'); ?></th>
            <th style="text-align: right; width: 100px;"><?php _e('Total', 'block-traiteur'); ?></th>
        </tr>
    </thead>
    <tbody>
        <?php 
        $subtotal_products = 0;
        foreach ($quote_items as $item): 
            $subtotal_products += $item['total_price'];
        ?>
        <tr>
            <td>
                <strong><?php echo esc_html($item['product_name']); ?></strong>
                <?php if (!empty($item['product_description'])): ?>
                    <br><small style="color: #6c757d;"><?php echo esc_html($item['product_description']); ?></small>
                <?php endif; ?>
                <br><small style="color: #007bff; font-weight: 600;">
                    <?php 
                    $category_labels = [
                        'base_package' => 'Forfait de base',
                        'meal_formula' => 'Formule repas',
                        'buffet' => 'Buffet',
                        'option' => 'Option'
                    ];
                    echo $category_labels[$item['item_type']] ?? $item['item_type'];
                    ?>
                </small>
            </td>
            <td style="text-align: center;"><?php echo (int) $item['quantity']; ?></td>
            <td style="text-align: right;"><?php echo number_format($item['unit_price'], 2, ',', ' '); ?> €</td>
            <td style="text-align: right; font-weight: 600;"><?php echo number_format($item['total_price'], 2, ',', ' '); ?> €</td>
        </tr>
        <?php endforeach; ?>
        <tr style="background-color: #f8f9fa; font-weight: 600;">
            <td colspan="3"><?php _e('Sous-total produits', 'block-traiteur'); ?></td>
            <td style="text-align: right;"><?php echo number_format($subtotal_products, 2, ',', ' '); ?> €</td>
        </tr>
    </tbody>
</table>
<?php endif; ?>

<?php if (!empty($quote_beverages)): ?>
<h3><?php _e('Boissons demandées', 'block-traiteur'); ?></h3>

<table class="email-table">
    <thead>
        <tr>
            <th><?php _e('Boisson', 'block-traiteur'); ?></th>
            <th style="text-align: center; width: 100px;"><?php _e('Nb pers.', 'block-traiteur'); ?></th>
            <th style="text-align: right; width: 100px;"><?php _e('Prix/pers.', 'block-traiteur'); ?></th>
            <th style="text-align: right; width: 100px;"><?php _e('Total', 'block-traiteur'); ?></th>
        </tr>
    </thead>
    <tbody>
        <?php 
        $subtotal_beverages = 0;
        foreach ($quote_beverages as $beverage): 
            $subtotal_beverages += $beverage['total_price'];
        ?>
        <tr>
            <td>
                <strong><?php echo esc_html($beverage['beverage_name']); ?></strong>
                <?php if (!empty($beverage['beverage_description'])): ?>
                    <br><small style="color: #6c757d;"><?php echo esc_html($beverage['beverage_description']); ?></small>
                <?php endif; ?>
                <br><small style="color: #28a745; font-weight: 600;">
                    <?php 
                    $beverage_labels = [
                        'soft' => 'Boisson sans alcool',
                        'hot' => 'Boisson chaude',
                        'alcohol' => 'Alcool',
                        'wine' => 'Vin',
                        'beer' => 'Bière'
                    ];
                    echo $beverage_labels[$beverage['category']] ?? $beverage['category'];
                    ?>
                </small>
            </td>
            <td style="text-align: center;"><?php echo (int) $beverage['guest_count']; ?></td>
            <td style="text-align: right;"><?php echo number_format($beverage['unit_price'], 2, ',', ' '); ?> €</td>
            <td style="text-align: right; font-weight: 600;"><?php echo number_format($beverage['total_price'], 2, ',', ' '); ?> €</td>
        </tr>
        <?php endforeach; ?>
        <tr style="background-color: #f8f9fa; font-weight: 600;">
            <td colspan="3"><?php _e('Sous-total boissons', 'block-traiteur'); ?></td>
            <td style="text-align: right;"><?php echo number_format($subtotal_beverages, 2, ',', ' '); ?> €</td>
        </tr>
    </tbody>
</table>
<?php endif; ?>

<!-- Récapitulatif financier -->
<h3>💰 <?php _e('Récapitulatif financier', 'block-traiteur'); ?></h3>

<table class="email-table">
    <tbody>
        <?php if (!empty($quote_items)): ?>
        <tr>
            <td style="font-weight: 600;"><?php _e('Sous-total produits et services', 'block-traiteur'); ?></td>
            <td style="text-align: right; font-weight: 600;"><?php echo number_format($subtotal_products, 2, ',', ' '); ?> €</td>
        </tr>
        <?php endif; ?>
        
        <?php if (!empty($quote_beverages)): ?>
        <tr>
            <td style="font-weight: 600;"><?php _e('Sous-total boissons', 'block-traiteur'); ?></td>
            <td style="text-align: right; font-weight: 600;"><?php echo number_format($subtotal_beverages, 2, ',', ' '); ?> €</td>
        </tr>
        <?php endif; ?>
        
        <?php if (!empty($quote['travel_cost']) && $quote['travel_cost'] > 0): ?>
        <tr>
            <td>
                <?php _e('Frais de déplacement', 'block-traiteur'); ?>
                <?php if (!empty($quote['distance_km'])): ?>
                    <br><small style="color: #6c757d;"><?php printf(__('Distance calculée : %s km', 'block-traiteur'), number_format($quote['distance_km'], 1, ',', ' ')); ?></small>
                <?php endif; ?>
            </td>
            <td style="text-align: right;"><?php echo number_format($quote['travel_cost'], 2, ',', ' '); ?> €</td>
        </tr>
        <?php endif; ?>
        
        <tr class="table-total">
            <td style="font-size: 18px;"><?php _e('TOTAL TTC', 'block-traiteur'); ?></td>
            <td style="text-align: right; font-size: 18px;"><?php echo $total_price_formatted; ?></td>
        </tr>
    </tbody>
</table>

<?php if (!empty($quote['notes'])): ?>
<!-- Demandes spéciales -->
<h2>📝 <?php _e('Demandes spéciales du client', 'block-traiteur'); ?></h2>
<div class="email-alert warning">
    <h4 style="margin: 0 0 10px 0;"><?php _e('Notes importantes à prendre en compte :', 'block-traiteur'); ?></h4>
    <div style="background-color: rgba(255, 255, 255, 0.7); padding: 15px; border-radius: 4px; margin-top: 10px;">
        <p style="margin: 0; font-style: italic; font-size: 16px;">
            "<?php echo nl2br(esc_html($quote['notes'])); ?>"
        </p>
    </div>
</div>
<?php endif; ?>

<!-- Actions recommandées -->
<h2>⚡ <?php _e('Actions à effectuer', 'block-traiteur'); ?></h2>

<div style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); padding: 25px; border-radius: 8px; margin: 20px 0;">
    
    <div style="margin-bottom: 20px;">
        <h4 style="margin: 0 0 10px 0; color: #243127;">1️⃣ <?php _e('Vérifier la disponibilité', 'block-traiteur'); ?></h4>
        <p style="margin: 0; color: #6c757d;">
            <?php _e('Contrôler que la date et le service demandé sont disponibles dans l\'agenda.', 'block-traiteur'); ?>
        </p>
    </div>
    
    <div style="margin-bottom: 20px;">
        <h4 style="margin: 0 0 10px 0; color: #243127;">2️⃣ <?php _e('Contacter le client', 'block-traiteur'); ?></h4>
        <p style="margin: 0; color: #6c757d;">
            <?php if ($is_urgent): ?>
                <strong style="color: #dc3545;"><?php _e('URGENT :', 'block-traiteur'); ?></strong>
            <?php endif; ?>
            <?php _e('Prendre contact sous 24h pour confirmer les détails et finaliser l\'offre.', 'block-traiteur'); ?>
        </p>
    </div>
    
    <div style="margin-bottom: 20px;">
        <h4 style="margin: 0 0 10px 0; color: #243127;">3️⃣ <?php _e('Valider ou ajuster le devis', 'block-traiteur'); ?></h4>
        <p style="margin: 0; color: #6c757d;">
            <?php _e('Modifier si nécessaire les quantités, produits ou tarifs dans l\'interface d\'administration.', 'block-traiteur'); ?>
        </p>
    </div>
    
    <div>
        <h4 style="margin: 0 0 10px 0; color: #243127;">4️⃣ <?php _e('Envoyer la confirmation', 'block-traiteur'); ?></h4>
        <p style="margin: 0; color: #6c757d;">
            <?php _e('Une fois validé, envoyer le devis définitif au client avec les conditions de réservation.', 'block-traiteur'); ?>
        </p>
    </div>
</div>

<!-- Boutons d'action -->
<div style="text-align: center; margin: 30px 0;">
    <a href="<?php echo esc_url($admin_quote_url); ?>" class="email-button" style="margin: 10px;">
        🔧 <?php _e('Gérer ce devis', 'block-traiteur'); ?>
    </a>
    
    <a href="mailto:<?php echo esc_attr($quote['customer_email']); ?>?subject=<?php echo urlencode('Votre devis Block Traiteur #' . $quote['quote_number']); ?>&body=<?php echo urlencode('Bonjour ' . $quote['customer_name'] . ',\n\nNous avons bien reçu votre demande de devis...\n\nCordialement,\nL\'équipe Block Strasbourg'); ?>" class="email-button-secondary" style="margin: 10px;">
        ✉️ <?php _e('Répondre au client', 'block-traiteur'); ?>
    </a>
</div>

<!-- Informations contextuelles -->
<div class="email-alert info">
    <h4 style="margin: 0 0 10px 0;"><?php _e('Informations contextuelles', 'block-traiteur'); ?></h4>
    <ul style="margin: 0; padding-left: 20px;">
        <li><?php printf(__('Devis généré automatiquement le %s', 'block-traiteur'), $formatted_created_date); ?></li>
        <li><?php printf(__('Référence interne : %s', 'block-traiteur'), $quote['quote_number']); ?></li>
        <li><?php printf(__('Statut actuel : %s', 'block-traiteur'), '<span style="color: #ffc107; font-weight: bold;">En attente</span>'); ?></li>
        <?php if (!empty($quote['expires_at'])): ?>
        <li><?php printf(__('Validité jusqu\'au : %s', 'block-traiteur'), date('d/m/Y', strtotime($quote['expires_at']))); ?></li>
        <?php endif; ?>
        <li><?php _e('Email client déjà envoyé automatiquement', 'block-traiteur'); ?></li>
    </ul>
</div>

<!-- Statistiques rapides -->
<div style="background-color: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0; text-align: center;">
    <h4 style="margin: 0 0 15px 0; color: #243127;"><?php _e('Statistiques rapides', 'block-traiteur'); ?></h4>
    <div style="display: flex; justify-content: space-around; flex-wrap: wrap;">
        <div style="margin: 10px;">
            <div style="font-size: 24px; font-weight: bold; color: #28a745;"><?php echo $total_price_formatted; ?></div>
            <div style="font-size: 12px; color: #6c757d;"><?php _e('Montant devis', 'block-traiteur'); ?></div>
        </div>
        <div style="margin: 10px;">
            <div style="font-size: 24px; font-weight: bold; color: #007bff;"><?php echo (int) $quote['guest_count']; ?></div>
            <div style="font-size: 12px; color: #6c757d;"><?php _e('Invités', 'block-traiteur'); ?></div>
        </div>
        <div style="margin: 10px;">
            <div style="font-size: 24px; font-weight: bold; color: #6f42c1;"><?php echo $days_until_event; ?></div>
            <div style="font-size: 12px; color: #6c757d;"><?php _e('Jours restants', 'block-traiteur'); ?></div>
        </div>
    </div>
</div>

<p style="font-size: 14px; color: #6c757d; font-style: italic;">
    <?php _e('Cet email a été généré automatiquement par le système Block Traiteur. Pour toute question technique, consultez l\'interface d\'administration.', 'block-traiteur'); ?>
</p>

<?php
// Récupérer le contenu généré
$email_content = ob_get_clean();

// Inclure le template de base
include BLOCK_TRAITEUR_PLUGIN_DIR . 'templates/emails/email-base.php';
?>