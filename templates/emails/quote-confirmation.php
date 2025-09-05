<?php
/**
 * Template d'email de confirmation de devis pour le client
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
 * @var string $pdf_url URL du PDF du devis (optionnel)
 */

// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit;
}

// Préparer les données pour le template de base
$email_title = sprintf(__('Votre devis Block Traiteur #%s', 'block-traiteur'), $quote['quote_number']);

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

// Date d'expiration du devis
$expires_at = null;
if (!empty($quote['expires_at'])) {
    $expires_date = new DateTime($quote['expires_at']);
    $expires_at = $expires_date->format('d/m/Y');
}

// Début du contenu de l'email
ob_start();
?>

<div class="email-alert success">
    <h3 style="margin: 0 0 10px 0;"><?php _e('Devis envoyé avec succès !', 'block-traiteur'); ?></h3>
    <p style="margin: 0;">
        <?php _e('Nous avons bien reçu votre demande et nous vous remercions de nous faire confiance pour votre événement.', 'block-traiteur'); ?>
    </p>
</div>

<p><?php printf(__('Bonjour %s,', 'block-traiteur'), '<strong>' . esc_html($quote['customer_name']) . '</strong>'); ?></p>

<p>
    <?php _e('Nous avons le plaisir de vous adresser votre devis personnalisé pour votre événement. Notre équipe a soigneusement étudié votre demande pour vous proposer une offre adaptée à vos besoins et à votre budget.', 'block-traiteur'); ?>
</p>

<!-- Récapitulatif de l'événement -->
<h2><?php _e('Récapitulatif de votre événement', 'block-traiteur'); ?></h2>

<div class="email-alert info">
    <table style="width: 100%; border: none;">
        <tr>
            <td style="border: none; padding: 5px 10px 5px 0; font-weight: 600; color: #0c5460;">
                <?php _e('Date et heure :', 'block-traiteur'); ?>
            </td>
            <td style="border: none; padding: 5px 0;">
                <?php echo esc_html($formatted_event_date_fr); ?>
            </td>
        </tr>
        <tr>
            <td style="border: none; padding: 5px 10px 5px 0; font-weight: 600; color: #0c5460;">
                <?php _e('Type de service :', 'block-traiteur'); ?>
            </td>
            <td style="border: none; padding: 5px 0;">
                <?php echo esc_html($service_type_label); ?>
            </td>
        </tr>
        <tr>
            <td style="border: none; padding: 5px 10px 5px 0; font-weight: 600; color: #0c5460;">
                <?php _e('Nombre d\'invités :', 'block-traiteur'); ?>
            </td>
            <td style="border: none; padding: 5px 0;">
                <?php echo (int) $quote['guest_count']; ?> <?php _e('personne(s)', 'block-traiteur'); ?>
            </td>
        </tr>
        <tr>
            <td style="border: none; padding: 5px 10px 5px 0; font-weight: 600; color: #0c5460;">
                <?php _e('Durée :', 'block-traiteur'); ?>
            </td>
            <td style="border: none; padding: 5px 0;">
                <?php echo (int) $quote['event_duration']; ?> <?php _e('heure(s)', 'block-traiteur'); ?>
            </td>
        </tr>
        <?php if (!empty($quote['customer_address'])): ?>
        <tr>
            <td style="border: none; padding: 5px 10px 5px 0; font-weight: 600; color: #0c5460;">
                <?php _e('Lieu :', 'block-traiteur'); ?>
            </td>
            <td style="border: none; padding: 5px 0;">
                <?php echo esc_html($quote['customer_address']); ?>
                <?php if (!empty($quote['customer_postal_code']) && !empty($quote['customer_city'])): ?>
                    <br><?php echo esc_html($quote['customer_postal_code'] . ' ' . $quote['customer_city']); ?>
                <?php endif; ?>
            </td>
        </tr>
        <?php endif; ?>
    </table>
</div>

<!-- Détail du devis -->
<h2><?php _e('Détail de votre devis', 'block-traiteur'); ?></h2>

<?php if (!empty($quote_items)): ?>
<h3><?php _e('Produits et services', 'block-traiteur'); ?></h3>

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
            </td>
            <td style="text-align: center;"><?php echo (int) $item['quantity']; ?></td>
            <td style="text-align: right;"><?php echo number_format($item['unit_price'], 2, ',', ' '); ?> €</td>
            <td style="text-align: right; font-weight: 600;"><?php echo number_format($item['total_price'], 2, ',', ' '); ?> €</td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>

<?php if (!empty($quote_beverages)): ?>
<h3><?php _e('Boissons', 'block-traiteur'); ?></h3>

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
            </td>
            <td style="text-align: center;"><?php echo (int) $beverage['guest_count']; ?></td>
            <td style="text-align: right;"><?php echo number_format($beverage['unit_price'], 2, ',', ' '); ?> €</td>
            <td style="text-align: right; font-weight: 600;"><?php echo number_format($beverage['total_price'], 2, ',', ' '); ?> €</td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>

<!-- Récapitulatif des prix -->
<h3><?php _e('Récapitulatif financier', 'block-traiteur'); ?></h3>

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
                    <br><small style="color: #6c757d;"><?php printf(__('(%s km)', 'block-traiteur'), number_format($quote['distance_km'], 1, ',', ' ')); ?></small>
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

<!-- Informations importantes -->
<div class="email-alert warning">
    <h4 style="margin: 0 0 10px 0;"><?php _e('Informations importantes', 'block-traiteur'); ?></h4>
    <ul style="margin: 0; padding-left: 20px;">
        <?php if ($expires_at): ?>
        <li><strong><?php printf(__('Validité du devis : jusqu\'au %s', 'block-traiteur'), $expires_at); ?></strong></li>
        <?php endif; ?>
        <li><?php _e('Les prix sont exprimés en euros TTC', 'block-traiteur'); ?></li>
        <li><?php _e('Ce devis est valable sous réserve de disponibilité', 'block-traiteur'); ?></li>
        <li><?php _e('Un acompte de 30% sera demandé à la confirmation', 'block-traiteur'); ?></li>
    </ul>
</div>

<?php if (!empty($quote['notes'])): ?>
<!-- Notes spéciales -->
<h3><?php _e('Notes et informations complémentaires', 'block-traiteur'); ?></h3>
<div style="background-color: #f8f9fa; padding: 15px; border-radius: 6px; border-left: 4px solid #6c757d;">
    <p style="margin: 0; font-style: italic;">
        "<?php echo nl2br(esc_html($quote['notes'])); ?>"
    </p>
</div>
<?php endif; ?>

<!-- Prochaines étapes -->
<h2><?php _e('Prochaines étapes', 'block-traiteur'); ?></h2>

<p><?php _e('Pour donner suite à ce devis, nous vous proposons plusieurs options :', 'block-traiteur'); ?></p>

<div style="margin: 20px 0;">
    <h4 style="color: #243127; margin: 0 0 10px 0;">📞 <?php _e('Par téléphone', 'block-traiteur'); ?></h4>
    <p style="margin: 0 0 15px 20px;">
        <?php _e('Appelez-nous directement pour discuter de votre projet et finaliser les détails.', 'block-traiteur'); ?>
        <?php if (!empty($settings['company_phone'])): ?>
            <br><strong><?php echo esc_html($settings['company_phone']); ?></strong>
        <?php endif; ?>
    </p>
    
    <h4 style="color: #243127; margin: 0 0 10px 0;">✉️ <?php _e('Par email', 'block-traiteur'); ?></h4>
    <p style="margin: 0 0 15px 20px;">
        <?php _e('Répondez directement à cet email avec vos questions ou votre validation.', 'block-traiteur'); ?>
    </p>
    
    <h4 style="color: #243127; margin: 0 0 10px 0;">🤝 <?php _e('Rencontre sur site', 'block-traiteur'); ?></h4>
    <p style="margin: 0 0 15px 20px;">
        <?php _e('Nous pouvons nous déplacer pour une visite technique et finaliser les derniers détails.', 'block-traiteur'); ?>
    </p>
</div>

<?php if (!empty($pdf_url)): ?>
<!-- Bouton de téléchargement PDF -->
<div style="text-align: center; margin: 30px 0;">
    <a href="<?php echo esc_url($pdf_url); ?>" class="email-button">
        📄 <?php _e('Télécharger le devis PDF', 'block-traiteur'); ?>
    </a>
    <p style="font-size: 14px; color: #6c757d; margin: 10px 0 0 0;">
        <?php _e('Format PDF pour impression et archivage', 'block-traiteur'); ?>
    </p>
</div>
<?php endif; ?>

<!-- Message de remerciement -->
<div class="email-alert success">
    <h4 style="margin: 0 0 10px 0;"><?php _e('Merci de votre confiance !', 'block-traiteur'); ?></h4>
    <p style="margin: 0;">
        <?php _e('Nous sommes impatients de contribuer au succès de votre événement et de vous faire découvrir l\'expérience Block : une cuisine de rue authentique, généreuse et pleine de saveurs.', 'block-traiteur'); ?>
    </p>
</div>

<p>
    <?php _e('Notre équipe reste à votre entière disposition pour tout renseignement complémentaire ou adaptation de ce devis selon vos besoins spécifiques.', 'block-traiteur'); ?>
</p>

<p>
    <?php _e('Bien cordialement,', 'block-traiteur'); ?><br>
    <strong><?php _e('L\'équipe Block Strasbourg', 'block-traiteur'); ?></strong>
</p>

<!-- Call-to-action supplémentaire -->
<div style="text-align: center; margin: 30px 0; padding: 25px; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 8px;">
    <h4 style="margin: 0 0 15px 0; color: #243127;"><?php _e('Besoin d\'aide ou de conseils ?', 'block-traiteur'); ?></h4>
    <p style="margin: 0 0 20px 0; font-size: 14px; color: #6c757d;">
        <?php _e('Notre équipe d\'experts est là pour vous accompagner dans votre projet', 'block-traiteur'); ?>
    </p>
    
    <div style="display: flex; justify-content: center; gap: 15px; flex-wrap: wrap;">
        <?php if (!empty($settings['company_phone'])): ?>
        <a href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', $settings['company_phone'])); ?>" class="email-button-secondary" style="margin: 5px;">
            📞 <?php _e('Nous appeler', 'block-traiteur'); ?>
        </a>
        <?php endif; ?>
        
        <?php if (!empty($settings['company_email'])): ?>
        <a href="mailto:<?php echo esc_attr($settings['company_email']); ?>?subject=<?php echo urlencode('Question sur le devis #' . $quote['quote_number']); ?>" class="email-button-secondary" style="margin: 5px;">
            ✉️ <?php _e('Nous écrire', 'block-traiteur'); ?>
        </a>
        <?php endif; ?>
    </div>
</div>

<!-- Informations légales -->
<div style="font-size: 12px; color: #6c757d; border-top: 1px solid #dee2e6; padding-top: 20px; margin-top: 30px;">
    <p style="margin: 0 0 8px 0;">
        <strong><?php _e('Informations légales :', 'block-traiteur'); ?></strong>
        <?php _e('Ce devis est établi selon nos conditions générales de vente disponibles sur demande.', 'block-traiteur'); ?>
    </p>
    <p style="margin: 0;">
        <?php printf(
            __('Devis généré automatiquement le %s - Référence interne : %s', 'block-traiteur'),
            date('d/m/Y à H:i'),
            $quote['quote_number']
        ); ?>
    </p>
</div>

<?php
// Récupérer le contenu généré
$email_content = ob_get_clean();

// Inclure le template de base
include BLOCK_TRAITEUR_PLUGIN_DIR . 'templates/emails/email-base.php';
?>