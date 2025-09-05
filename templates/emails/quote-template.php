<?php
/**
 * Template PDF pour les devis Block Traiteur
 *
 * @package Block_Traiteur
 * @subpackage Templates/PDF
 * @since 1.0.0
 * 
 * Variables disponibles :
 * @var array $quote Données complètes du devis
 * @var array $quote_items Éléments du devis (produits)
 * @var array $quote_beverages Boissons du devis
 * @var array $settings Paramètres du plugin
 * @var object $pdf Instance TCPDF
 */

// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit;
}

// Configuration des couleurs et styles
$primary_color = $settings['pdf_header_color'] ?? '#243127';
$accent_color = $settings['pdf_accent_color'] ?? '#FFB404';
$text_color = '#333333';
$gray_color = '#6c757d';
$light_gray = '#f8f9fa';

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

// Date d'expiration
$expires_at = null;
if (!empty($quote['expires_at'])) {
    $expires_date = new DateTime($quote['expires_at']);
    $expires_at = $expires_date->format('d/m/Y');
}

// Configuration PDF
$pdf->SetCreator('Block Traiteur Plugin');
$pdf->SetAuthor($settings['company_name'] ?? 'Block Strasbourg');
$pdf->SetTitle('Devis #' . $quote['quote_number']);
$pdf->SetSubject('Devis Block Traiteur');
$pdf->SetKeywords('devis, traiteur, événement, Block');

// Marges
$pdf->SetMargins(15, 20, 15);
$pdf->SetHeaderMargin(5);
$pdf->SetFooterMargin(10);

// Auto page breaks
$pdf->SetAutoPageBreak(TRUE, 25);

// Polices
$pdf->SetFont('helvetica', '', 10);

// Fonction helper pour les couleurs HTML vers RGB
function hex2rgb($hex) {
    $hex = str_replace('#', '', $hex);
    return array(
        'r' => hexdec(substr($hex, 0, 2)),
        'g' => hexdec(substr($hex, 2, 2)),
        'b' => hexdec(substr($hex, 4, 2))
    );
}

$primary_rgb = hex2rgb($primary_color);
$accent_rgb = hex2rgb($accent_color);

// En-tête personnalisé
class PDF_Header extends TCPDF {
    public function Header() {
        global $settings, $quote, $primary_color, $accent_color, $primary_rgb, $accent_rgb;
        
        // Fond dégradé pour l'en-tête
        $this->SetFillColor($primary_rgb['r'], $primary_rgb['g'], $primary_rgb['b']);
        $this->Rect(0, 0, $this->getPageWidth(), 40, 'F');
        
        // Logo ou nom de l'entreprise
        $this->SetFont('helvetica', 'B', 24);
        $this->SetTextColor(255, 255, 255);
        $this->SetXY(15, 10);
        
        $company_name = $settings['company_name'] ?? 'Block Strasbourg';
        
        if (!empty($settings['company_logo_url'])) {
            // Si un logo est disponible
            try {
                $this->Image($settings['company_logo_url'], 15, 8, 25, 0, '', '', '', false, 300, '', false, false, 0);
                $this->SetXY(45, 12);
                $this->Cell(0, 0, $company_name, 0, 1, 'L');
            } catch (Exception $e) {
                // Fallback si le logo ne charge pas
                $this->Cell(0, 0, $company_name, 0, 1, 'L');
            }
        } else {
            $this->Cell(0, 0, $company_name, 0, 1, 'L');
        }
        
        // Sous-titre
        $this->SetFont('helvetica', '', 12);
        $this->SetXY(15, 22);
        $this->Cell(0, 0, 'Traiteur événementiel - Cuisine de rue authentique', 0, 1, 'L');
        
        // Informations de contact
        $this->SetFont('helvetica', '', 9);
        $this->SetXY(120, 12);
        
        $contact_info = array();
        if (!empty($settings['company_phone'])) {
            $contact_info[] = 'Tél : ' . $settings['company_phone'];
        }
        if (!empty($settings['company_email'])) {
            $contact_info[] = 'Email : ' . $settings['company_email'];
        }
        if (!empty($settings['company_address'])) {
            $contact_info[] = $settings['company_address'];
        }
        
        $y_contact = 12;
        foreach ($contact_info as $info) {
            $this->SetXY(120, $y_contact);
            $this->Cell(0, 0, $info, 0, 1, 'L');
            $y_contact += 5;
        }
        
        // Ligne de séparation
        $this->SetDrawColor($accent_rgb['r'], $accent_rgb['g'], $accent_rgb['b']);
        $this->SetLineWidth(2);
        $this->Line(15, 35, $this->getPageWidth() - 15, 35);
        
        $this->SetY(45); // Position après l'en-tête
    }
    
    public function Footer() {
        global $settings, $quote;
        
        $this->SetY(-20);
        $this->SetFont('helvetica', '', 8);
        $this->SetTextColor(128, 128, 128);
        
        // Ligne de séparation
        $this->SetDrawColor(200, 200, 200);
        $this->SetLineWidth(0.5);
        $this->Line(15, $this->GetY() - 2, $this->getPageWidth() - 15, $this->GetY() - 2);
        
        // Pied de page gauche
        $footer_text = $settings['pdf_footer_text'] ?? 'Block Strasbourg - Traiteur événementiel';
        $this->Cell(0, 0, $footer_text, 0, 0, 'L');
        
        // Numéro de page
        $this->Cell(0, 0, 'Page ' . $this->getAliasNumPage() . ' / ' . $this->getAliasNbPages(), 0, 0, 'R');
        
        // Date de génération
        $this->SetY(-12);
        $this->Cell(0, 0, 'Devis généré le ' . date('d/m/Y à H:i'), 0, 0, 'L');
        
        // Référence
        $this->Cell(0, 0, 'Référence : ' . $quote['quote_number'], 0, 0, 'R');
    }
}

// Créer une nouvelle page
$pdf->AddPage();

// Titre du document
$pdf->SetFont('helvetica', 'B', 20);
$pdf->SetTextColor($primary_rgb['r'], $primary_rgb['g'], $primary_rgb['b']);
$pdf->Cell(0, 15, 'DEVIS N° ' . $quote['quote_number'], 0, 1, 'C');

$pdf->Ln(5);

// Informations client et devis
$pdf->SetFont('helvetica', 'B', 12);
$pdf->SetTextColor($primary_rgb['r'], $primary_rgb['g'], $primary_rgb['b']);

// Section client (colonne gauche)
$pdf->SetXY(15, $pdf->GetY());
$pdf->Cell(85, 8, 'INFORMATIONS CLIENT', 0, 0, 'L');

// Section devis (colonne droite)
$pdf->SetXY(110, $pdf->GetY());
$pdf->Cell(85, 8, 'INFORMATIONS DEVIS', 0, 1, 'L');

$pdf->Ln(2);

// Contenu des colonnes
$pdf->SetFont('helvetica', '', 10);
$pdf->SetTextColor(51, 51, 51);

$y_start = $pdf->GetY();

// Colonne gauche - Client
$pdf->SetXY(15, $y_start);
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(0, 6, strtoupper($quote['customer_name']), 0, 1, 'L');

$pdf->SetXY(15, $pdf->GetY());
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(0, 5, $quote['customer_email'], 0, 1, 'L');

if (!empty($quote['customer_phone'])) {
    $pdf->SetXY(15, $pdf->GetY());
    $pdf->Cell(0, 5, $quote['customer_phone'], 0, 1, 'L');
}

if (!empty($quote['customer_address'])) {
    $pdf->SetXY(15, $pdf->GetY());
    $pdf->Cell(0, 5, $quote['customer_address'], 0, 1, 'L');
    
    if (!empty($quote['customer_postal_code']) && !empty($quote['customer_city'])) {
        $pdf->SetXY(15, $pdf->GetY());
        $pdf->Cell(0, 5, $quote['customer_postal_code'] . ' ' . $quote['customer_city'], 0, 1, 'L');
    }
}

// Colonne droite - Devis
$pdf->SetXY(110, $y_start);
$pdf->Cell(30, 5, 'Date d\'émission :', 0, 0, 'L');
$pdf->Cell(0, 5, date('d/m/Y', strtotime($quote['created_at'])), 0, 1, 'L');

$pdf->SetXY(110, $pdf->GetY());
$pdf->Cell(30, 5, 'Date d\'événement :', 0, 0, 'L');
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(0, 5, $formatted_event_date_fr, 0, 1, 'L');

$pdf->SetFont('helvetica', '', 10);
$pdf->SetXY(110, $pdf->GetY());
$pdf->Cell(30, 5, 'Type de service :', 0, 0, 'L');
$pdf->Cell(0, 5, $service_type_label, 0, 1, 'L');

$pdf->SetXY(110, $pdf->GetY());
$pdf->Cell(30, 5, 'Nombre d\'invités :', 0, 0, 'L');
$pdf->Cell(0, 5, $quote['guest_count'] . ' personne(s)', 0, 1, 'L');

$pdf->SetXY(110, $pdf->GetY());
$pdf->Cell(30, 5, 'Durée :', 0, 0, 'L');
$pdf->Cell(0, 5, $quote['event_duration'] . ' heure(s)', 0, 1, 'L');

if ($expires_at) {
    $pdf->SetXY(110, $pdf->GetY());
    $pdf->Cell(30, 5, 'Valide jusqu\'au :', 0, 0, 'L');
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->SetTextColor(220, 53, 69);
    $pdf->Cell(0, 5, $expires_at, 0, 1, 'L');
}

$pdf->Ln(10);

// Détail des produits
if (!empty($quote_items)) {
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->SetTextColor($primary_rgb['r'], $primary_rgb['g'], $primary_rgb['b']);
    $pdf->Cell(0, 10, 'DÉTAIL DES PRODUITS ET SERVICES', 0, 1, 'L');
    
    $pdf->Ln(3);
    
    // En-tête du tableau
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->SetFillColor($primary_rgb['r'], $primary_rgb['g'], $primary_rgb['b']);
    $pdf->SetTextColor(255, 255, 255);
    
    $col_widths = array(100, 25, 30, 30);
    $headers = array('PRODUIT / SERVICE', 'QTÉ', 'PRIX UNIT.', 'TOTAL');
    
    for ($i = 0; $i < count($headers); $i++) {
        $pdf->Cell($col_widths[$i], 8, $headers[$i], 1, 0, 'C', true);
    }
    $pdf->Ln();
    
    // Contenu du tableau
    $pdf->SetFont('helvetica', '', 9);
    $pdf->SetTextColor(51, 51, 51);
    $subtotal_products = 0;
    
    foreach ($quote_items as $index => $item) {
        $subtotal_products += $item['total_price'];
        
        // Alternance des couleurs de fond
        if ($index % 2 == 0) {
            $pdf->SetFillColor(248, 249, 250);
        } else {
            $pdf->SetFillColor(255, 255, 255);
        }
        
        // Nom du produit
        $pdf->Cell($col_widths[0], 8, $item['product_name'], 1, 0, 'L', true);
        
        // Quantité
        $pdf->Cell($col_widths[1], 8, $item['quantity'], 1, 0, 'C', true);
        
        // Prix unitaire
        $pdf->Cell($col_widths[2], 8, number_format($item['unit_price'], 2, ',', ' ') . ' €', 1, 0, 'R', true);
        
        // Total
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->Cell($col_widths[3], 8, number_format($item['total_price'], 2, ',', ' ') . ' €', 1, 0, 'R', true);
        $pdf->SetFont('helvetica', '', 9);
        
        $pdf->Ln();
        
        // Description si disponible
        if (!empty($item['product_description'])) {
            $pdf->SetFont('helvetica', 'I', 8);
            $pdf->SetTextColor(108, 117, 125);
            $pdf->Cell($col_widths[0], 5, '  ' . $item['product_description'], 0, 0, 'L');
            $pdf->SetFont('helvetica', '', 9);
            $pdf->SetTextColor(51, 51, 51);
            $pdf->Cell($col_widths[1] + $col_widths[2] + $col_widths[3], 5, '', 0, 1, 'L');
        }
    }
    
    // Sous-total produits
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->SetFillColor(233, 236, 239);
    $pdf->Cell($col_widths[0] + $col_widths[1] + $col_widths[2], 8, 'SOUS-TOTAL PRODUITS ET SERVICES', 1, 0, 'R', true);
    $pdf->Cell($col_widths[3], 8, number_format($subtotal_products, 2, ',', ' ') . ' €', 1, 1, 'R', true);
    
    $pdf->Ln(5);
}

// Boissons
if (!empty($quote_beverages)) {
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->SetTextColor($primary_rgb['r'], $primary_rgb['g'], $primary_rgb['b']);
    $pdf->Cell(0, 10, 'BOISSONS', 0, 1, 'L');
    
    $pdf->Ln(3);
    
    // En-tête du tableau boissons
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->SetFillColor($accent_rgb['r'], $accent_rgb['g'], $accent_rgb['b']);
    $pdf->SetTextColor($primary_rgb['r'], $primary_rgb['g'], $primary_rgb['b']);
    
    $col_widths_bev = array(100, 25, 30, 30);
    $headers_bev = array('BOISSON', 'NB PERS.', 'PRIX/PERS.', 'TOTAL');
    
    for ($i = 0; $i < count($headers_bev); $i++) {
        $pdf->Cell($col_widths_bev[$i], 8, $headers_bev[$i], 1, 0, 'C', true);
    }
    $pdf->Ln();
    
    // Contenu du tableau boissons
    $pdf->SetFont('helvetica', '', 9);
    $pdf->SetTextColor(51, 51, 51);
    $subtotal_beverages = 0;
    
    foreach ($quote_beverages as $index => $beverage) {
        $subtotal_beverages += $beverage['total_price'];
        
        // Alternance des couleurs
        if ($index % 2 == 0) {
            $pdf->SetFillColor(248, 249, 250);
        } else {
            $pdf->SetFillColor(255, 255, 255);
        }
        
        $pdf->Cell($col_widths_bev[0], 8, $beverage['beverage_name'], 1, 0, 'L', true);
        $pdf->Cell($col_widths_bev[1], 8, $beverage['guest_count'], 1, 0, 'C', true);
        $pdf->Cell($col_widths_bev[2], 8, number_format($beverage['unit_price'], 2, ',', ' ') . ' €', 1, 0, 'R', true);
        
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->Cell($col_widths_bev[3], 8, number_format($beverage['total_price'], 2, ',', ' ') . ' €', 1, 0, 'R', true);
        $pdf->SetFont('helvetica', '', 9);
        
        $pdf->Ln();
    }
    
    // Sous-total boissons
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->SetFillColor(233, 236, 239);
    $pdf->Cell($col_widths_bev[0] + $col_widths_bev[1] + $col_widths_bev[2], 8, 'SOUS-TOTAL BOISSONS', 1, 0, 'R', true);
    $pdf->Cell($col_widths_bev[3], 8, number_format($subtotal_beverages, 2, ',', ' ') . ' €', 1, 1, 'R', true);
    
    $pdf->Ln(5);
}

// Récapitulatif final
$pdf->SetFont('helvetica', 'B', 14);
$pdf->SetTextColor($primary_rgb['r'], $primary_rgb['g'], $primary_rgb['b']);
$pdf->Cell(0, 10, 'RÉCAPITULATIF', 0, 1, 'L');

$pdf->Ln(3);

$pdf->SetFont('helvetica', '', 10);
$pdf->SetTextColor(51, 51, 51);

// Tableau récapitulatif
$col_recap = array(140, 45);

if (!empty($quote_items)) {
    $pdf->Cell($col_recap[0], 6, 'Sous-total produits et services', 1, 0, 'L');
    $pdf->Cell($col_recap[1], 6, number_format($subtotal_products, 2, ',', ' ') . ' €', 1, 1, 'R');
}

if (!empty($quote_beverages)) {
    $pdf->Cell($col_recap[0], 6, 'Sous-total boissons', 1, 0, 'L');
    $pdf->Cell($col_recap[1], 6, number_format($subtotal_beverages, 2, ',', ' ') . ' €', 1, 1, 'R');
}

if (!empty($quote['travel_cost']) && $quote['travel_cost'] > 0) {
    $travel_label = 'Frais de déplacement';
    if (!empty($quote['distance_km'])) {
        $travel_label .= ' (' . number_format($quote['distance_km'], 1, ',', ' ') . ' km)';
    }
    $pdf->Cell($col_recap[0], 6, $travel_label, 1, 0, 'L');
    $pdf->Cell($col_recap[1], 6, number_format($quote['travel_cost'], 2, ',', ' ') . ' €', 1, 1, 'R');
}

// Total TTC
$pdf->SetFont('helvetica', 'B', 12);
$pdf->SetFillColor($primary_rgb['r'], $primary_rgb['g'], $primary_rgb['b']);
$pdf->SetTextColor(255, 255, 255);
$pdf->Cell($col_recap[0], 10, 'TOTAL TTC', 1, 0, 'L', true);
$pdf->Cell($col_recap[1], 10, $total_price_formatted, 1, 1, 'R', true);

$pdf->Ln(10);

// Notes spéciales
if (!empty($quote['notes'])) {
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->SetTextColor($primary_rgb['r'], $primary_rgb['g'], $primary_rgb['b']);
    $pdf->Cell(0, 8, 'NOTES ET DEMANDES SPÉCIALES', 0, 1, 'L');
    
    $pdf->SetFont('helvetica', '', 10);
    $pdf->SetTextColor(51, 51, 51);
    
    // Cadre pour les notes
    $pdf->SetFillColor(248, 249, 250);
    $pdf->SetXY(15, $pdf->GetY());
    $pdf->Rect(15, $pdf->GetY(), 180, 20, 'F');
    $pdf->SetXY(20, $pdf->GetY() + 5);
    
    $pdf->MultiCell(170, 5, '"' . $quote['notes'] . '"', 0, 'L');
    $pdf->Ln(10);
}

// Conditions générales
$pdf->SetFont('helvetica', 'B', 12);
$pdf->SetTextColor($primary_rgb['r'], $primary_rgb['g'], $primary_rgb['b']);
$pdf->Cell(0, 8, 'CONDITIONS GÉNÉRALES', 0, 1, 'L');

$pdf->SetFont('helvetica', '', 9);
$pdf->SetTextColor(51, 51, 51);

$conditions = array(
    '• Ce devis est valable ' . ($expires_at ? 'jusqu\'au ' . $expires_at : 'pendant 30 jours') . ', sous réserve de disponibilité.',
    '• Les prix sont exprimés en euros TTC (TVA incluse).',
    '• Un acompte de 30% sera demandé à la confirmation de commande.',
    '• Le solde sera réglé le jour de la prestation.',
    '• Toute modification de la commande moins de 48h avant l\'événement pourra entraîner des frais supplémentaires.',
    '• Les conditions météorologiques défavorables peuvent affecter le service en extérieur.',
    '• Nos conditions générales de vente sont disponibles sur simple demande.',
);

foreach ($conditions as $condition) {
    $pdf->Cell(0, 5, $condition, 0, 1, 'L');
}

$pdf->Ln(10);

// Signature et validation
$pdf->SetFont('helvetica', 'B', 12);
$pdf->SetTextColor($primary_rgb['r'], $primary_rgb['g'], $primary_rgb['b']);
$pdf->Cell(0, 8, 'VALIDATION DU DEVIS', 0, 1, 'L');

$pdf->SetFont('helvetica', '', 10);
$pdf->SetTextColor(51, 51, 51);

$pdf->Cell(0, 6, 'Pour accepter ce devis, merci de nous renvoyer ce document signé avec la mention "Bon pour accord".', 0, 1, 'L');
$pdf->Ln(5);

// Cadres pour signatures
$pdf->Cell(90, 6, 'Signature du client :', 0, 0, 'L');
$pdf->Cell(90, 6, 'Signature Block Strasbourg :', 0, 1, 'L');

$pdf->Ln(3);

// Cadres de signature
$pdf->Rect(15, $pdf->GetY(), 80, 20);
$pdf->Rect(105, $pdf->GetY(), 80, 20);

$pdf->SetXY(15, $pdf->GetY() + 22);
$pdf->SetFont('helvetica', '', 8);
$pdf->Cell(80, 4, 'Date et signature', 0, 0, 'C');
$pdf->SetXY(105, $pdf->GetY());
$pdf->Cell(80, 4, 'Cachet et signature', 0, 1, 'C');

// Si on approche de la fin de page, ajouter une nouvelle page pour le footer
if ($pdf->GetY() > 250) {
    $pdf->AddPage();
}

// Message de remerciement en bas
$pdf->SetY(-50);
$pdf->SetFont('helvetica', 'I', 11);
$pdf->SetTextColor($accent_rgb['r'], $accent_rgb['g'], $accent_rgb['b']);
$pdf->Cell(0, 8, 'Merci de votre confiance !', 0, 1, 'C');

$pdf->SetFont('helvetica', '', 10);
$pdf->SetTextColor(51, 51, 51);
$pdf->Cell(0, 6, 'Nous sommes impatients de contribuer au succès de votre événement', 0, 1, 'C');
$pdf->Cell(0, 6, 'et de vous faire découvrir l\'expérience Block.', 0, 1, 'C');

// QR Code ou URL du site (optionnel)
if (!empty($settings['website_qr_code'])) {
    try {
        $pdf->write2DBarcode(home_url(), 'QRCODE,L', 170, $pdf->GetY() - 15, 15, 15, array(), 'N');
    } catch (Exception $e) {
        // Ignorer si la génération du QR code échoue
    }
}

?>