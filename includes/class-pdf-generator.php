<?php
/**
 * Classe de génération PDF avec TCPDF
 */

if (!defined('ABSPATH')) {
    exit;
}

// Inclure TCPDF
require_once BLOCK_TRAITEUR_PLUGIN_DIR . 'vendor/tcpdf/tcpdf.php';

class Block_Traiteur_PDF_Generator {
    
    private $settings;
    
    /**
     * Constructeur
     */
    public function __construct() {
        $this->settings = Block_Traiteur_Cache::get_settings();
    }
    
    /**
     * Générer le PDF d'un devis
     */
    public function generate_quote_pdf($quote_id) {
        $quote = Block_Traiteur_Database::get_quote_data($quote_id);
        
        if (!$quote) {
            return false;
        }
        
        // Initialiser TCPDF
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        
        // Configuration du document
        $pdf->SetCreator('Block Traiteur Plugin');
        $pdf->SetAuthor($this->settings['company_name']);
        $pdf->SetTitle('Devis Block Traiteur - ' . $quote['quote_number']);
        $pdf->SetSubject('Devis événementiel');
        
        // Supprimer header/footer par défaut
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        
        // Marges
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(true, 25);
        
        // Ajouter une page
        $pdf->AddPage();
        
        // Générer le contenu
        $html = $this->generate_quote_html($quote);
        $pdf->writeHTML($html, true, false, true, false, '');
        
        // Chemin de sauvegarde
        $upload_dir = wp_upload_dir();
        $pdf_dir = $upload_dir['basedir'] . '/block-traiteur-quotes/';
        
        if (!file_exists($pdf_dir)) {
            wp_mkdir_p($pdf_dir);
        }
        
        $filename = 'devis-' . $quote['quote_number'] . '.pdf';
        $filepath = $pdf_dir . $filename;
        
        // Sauvegarder le PDF
        $pdf->Output($filepath, 'F');
        
        // Mettre à jour le chemin dans la BDD
        global $wpdb;
        $wpdb->update(
            $wpdb->prefix . 'block_quotes',
            array('pdf_path' => $filepath),
            array('id' => $quote_id),
            array('%s'),
            array('%d')
        );
        
        return $filepath;
    }
    
    /**
     * Générer le HTML du devis
     */
    private function generate_quote_html($quote) {
        ob_start();
        ?>
        <style>
            body { 
                font-family: 'DejaVu Sans', sans-serif; 
                font-size: 12px;
                line-height: 1.4;
            }
            .header { 
                text-align: center; 
                margin-bottom: 30px; 
                border-bottom: 2px solid #243127;
                padding-bottom: 20px;
            }
            .logo { 
                width: 200px; 
                height: auto; 
                margin-bottom: 10px;
            }
            .company-info { 
                text-align: right; 
                font-size: 11px; 
                margin-bottom: 20px; 
                color: #666;
            }
            .quote-title { 
                font-size: 24px; 
                font-weight: bold; 
                color: #243127; 
                margin: 20px 0; 
                text-align: center;
            }
            .quote-number { 
                font-size: 14px; 
                color: #666; 
                text-align: center;
                margin-bottom: 30px;
            }
            .customer-info { 
                background: #F6F2E7; 
                padding: 15px; 
                margin: 20px 0; 
                border-radius: 10px; 
            }
            .event-details { 
                margin: 20px 0; 
            }
            .section-title { 
                font-size: 16px; 
                font-weight: bold; 
                color: #243127; 
                margin: 15px 0 10px 0; 
                border-bottom: 1px solid #ddd;
                padding-bottom: 5px;
            }
            .products-table { 
                width: 100%; 
                border-collapse: collapse; 
                margin: 20px 0; 
            }
            .products-table th, .products-table td {
                border: 1px solid #ddd;
                padding: 8px;
                text-align: left;
                font-size: 11px;
            }
            .products-table th { 
                background: #243127; 
                color: white; 
                font-weight: bold;
            }
            .price-total { 
                font-size: 18px; 
                font-weight: bold; 
                text-align: right; 
                margin: 20px 0; 
                background: #F6F2E7;
                padding: 15px;
                border-radius: 10px;
            }
            .footer { 
                font-size: 10px; 
                color: #666; 
                margin-top: 30px; 
                text-align: center; 
                border-top: 1px solid #ddd;
                padding-top: 15px;
            }
            .conditions {
                font-size: 10px;
                color: #666;
                margin-top: 20px;
                padding: 10px;
                background: #f9f9f9;
                border-radius: 5px;
            }
            .total-highlight {
                color: #EF3D1D;
                font-size: 20px;
            }
        </style>
        
        <!-- Header avec logo -->
        <div class="header">
            <h1 style="color: #243127; margin: 10px 0; font-size: 28px;">Block Street Food & Events</h1>
            <p style="margin: 0; color: #666;">Traiteur événementiel - Street Food</p>
        </div>
        
        <!-- Informations entreprise -->
        <div class="company-info">
            <strong><?php echo esc_html($this->settings['company_name']); ?></strong><br>
            <?php echo nl2br(esc_html($this->settings['company_address'])); ?><br>
            Tél: <?php echo esc_html($this->settings['company_phone']); ?><br>
            Email: <?php echo esc_html($this->settings['company_email']); ?><br>
            <?php if ($this->settings['company_siret']): ?>
                SIRET: <?php echo esc_html($this->settings['company_siret']); ?>
            <?php endif; ?>
        </div>
        
        <!-- Titre et numéro de devis -->
        <div class="quote-title">DEVIS ÉVÉNEMENTIEL</div>
        <div class="quote-number">N° <?php echo esc_html($quote['quote_number']); ?> - <?php echo date_i18n('d/m/Y', strtotime($quote['created_at'])); ?></div>
        
        <!-- Informations client -->
        <div class="customer-info">
            <div class="section-title">Client</div>
            <strong><?php echo esc_html($quote['customer_name']); ?></strong><br>
            Email: <?php echo esc_html($quote['customer_email']); ?><br>
            <?php if ($quote['customer_phone']): ?>
                Téléphone: <?php echo esc_html($quote['customer_phone']); ?>
            <?php endif; ?>
        </div>
        
        <!-- Détails événement -->
        <div class="event-details">
            <div class="section-title">Détails de l'événement</div>
            <strong>Service:</strong> <?php echo ucfirst($quote['service_type']); ?><br>
            <strong>Date:</strong> <?php echo date_i18n('d/m/Y', strtotime($quote['event_date'])); ?><br>
            <strong>Durée:</strong> <?php echo esc_html($quote['event_duration']); ?> heure(s)<br>
            <strong>Nombre d'invités:</strong> <?php echo esc_html($quote['guest_count']); ?> personne(s)<br>
            <?php if ($quote['event_location']): ?>
                <strong>Lieu:</strong> <?php echo esc_html($quote['event_location']); ?> (<?php echo esc_html($quote['postal_code']); ?>)<br>
            <?php endif; ?>
            <?php if ($quote['distance_km']): ?>
                <strong>Distance:</strong> <?php echo esc_html($quote['distance_km']); ?> km<br>
            <?php endif; ?>
        </div>
        
        <!-- Forfait de base -->
        <div class="section-title">Forfait de base</div>
        <p>Ce forfait comprend :</p>
        <ul>
            <li><?php echo esc_html($quote['event_duration']); ?>H de privatisation (service inclus)</li>
            <li>Notre équipe salle + cuisine durant la prestation</li>
            <?php if ($quote['service_type'] === 'restaurant'): ?>
                <li>Mise à disposition des murs de Block</li>
            <?php else: ?>
                <li>Déplacement de la remorque Block (Aller-Retour)</li>
                <li>Installation complète</li>
                <li>Fourniture de vaisselle jetable (recyclable)</li>
            <?php endif; ?>
            <li>Présentation et mise en place buffets</li>
            <li>Vaisselle et verrerie</li>
            <li>Entretien et nettoyage</li>
        </ul>
        
        <!-- Détail des produits -->
        <?php if (!empty($quote['products'])): ?>
            <div class="section-title">Produits sélectionnés</div>
            <table class="products-table">
                <thead>
                    <tr>
                        <th>Produit</th>
                        <th>Quantité</th>
                        <th>Prix unitaire</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($quote['products'] as $product): ?>
                        <tr>
                            <td><?php echo esc_html($product['product_name']); ?></td>
                            <td><?php echo esc_html($product['quantity']); ?> <?php echo esc_html($product['product_unit']); ?></td>
                            <td><?php echo number_format($product['unit_price'], 2); ?> € TTC</td>
                            <td><?php echo number_format($product['total_price'], 2); ?> € TTC</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
        
        <!-- Récapitulatif prix -->
        <div style="margin-top: 30px;">
            <table style="width: 60%; margin-left: auto; border-collapse: collapse;">
                <tr>
                    <td style="padding: 8px; border-bottom: 1px solid #ddd;"><strong>Forfait de base:</strong></td>
                    <td style="text-align: right; padding: 8px; border-bottom: 1px solid #ddd;"><?php echo number_format($quote['base_price'], 2); ?> € TTC</td>
                </tr>
                <?php if ($quote['supplements_price'] > 0): ?>
                    <tr>
                        <td style="padding: 8px; border-bottom: 1px solid #ddd;"><strong>Suppléments:</strong></td>
                        <td style="text-align: right; padding: 8px; border-bottom: 1px solid #ddd;"><?php echo number_format($quote['supplements_price'], 2); ?> € TTC</td>
                    </tr>
                <?php endif; ?>
                <?php if ($quote['products_price'] > 0): ?>
                    <tr>
                        <td style="padding: 8px; border-bottom: 1px solid #ddd;"><strong>Produits:</strong></td>
                        <td style="text-align: right; padding: 8px; border-bottom: 1px solid #ddd;"><?php echo number_format($quote['products_price'], 2); ?> € TTC</td>
                    </tr>
                <?php endif; ?>
                <?php if ($quote['beverages_price'] > 0): ?>
                    <tr>
                        <td style="padding: 8px; border-bottom: 1px solid #ddd;"><strong>Boissons:</strong></td>
                        <td style="text-align: right; padding: 8px; border-bottom: 1px solid #ddd;"><?php echo number_format($quote['beverages_price'], 2); ?> € TTC</td>
                    </tr>
                <?php endif; ?>
                <tr style="border-top: 2px solid #243127; font-size: 18px; background: #F6F2E7;">
                    <td style="padding: 12px;"><strong>TOTAL TTC:</strong></td>
                    <td style="text-align: right; padding: 12px;"><strong class="total-highlight"><?php echo number_format($quote['total_price'], 2); ?> € TTC</strong></td>
                </tr>
            </table>
        </div>
        
        <!-- Commentaires client -->
        <?php if ($quote['customer_comments']): ?>
            <div style="margin-top: 20px;">
                <div class="section-title">Commentaires du client</div>
                <p style="font-style: italic; background: #f9f9f9; padding: 10px; border-radius: 5px;">
                    <?php echo nl2br(esc_html($quote['customer_comments'])); ?>
                </p>
            </div>
        <?php endif; ?>
        
        <!-- Conditions -->
        <div class="conditions">
            <div class="section-title">Conditions</div>
            <p><strong>Validité du devis:</strong> 30 jours à compter de la date d'émission</p>
            <p><strong>Acompte:</strong> 50% à la confirmation, solde le jour de l'événement</p>
            <p><strong>Annulation:</strong> Possible jusqu'à 7 jours avant l'événement (frais de 30%)</p>
            <p>Ce devis est établi sous réserve de disponibilité à la date souhaitée.</p>
            <p>Les prix sont exprimés en euros TTC (TVA 20%).</p>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <p>Block Street Food & Events - <?php echo esc_html($this->settings['company_address']); ?></p>
            <p>Tél: <?php echo esc_html($this->settings['company_phone']); ?> - Email: <?php echo esc_html($this->settings['company_email']); ?></p>
            <?php if ($this->settings['company_siret']): ?>
                <p>SIRET: <?php echo esc_html($this->settings['company_siret']); ?></p>
            <?php endif; ?>
        </div>
        
        <?php
        return ob_get_clean();
    }
    
    /**
     * Générer un PDF de test
     */
    public function generate_test_pdf() {
        $pdf = new TCPDF();
        $pdf->AddPage();
        $pdf->SetFont('helvetica', '', 12);
        $pdf->Cell(0, 10, 'Test PDF Block Traiteur', 0, 1, 'C');
        
        $upload_dir = wp_upload_dir();
        $pdf_dir = $upload_dir['basedir'] . '/block-traiteur-quotes/';
        
        if (!file_exists($pdf_dir)) {
            wp_mkdir_p($pdf_dir);
        }
        
        $filepath = $pdf_dir . 'test-' . time() . '.pdf';
        $pdf->Output($filepath, 'F');
        
        return $filepath;
    }
}