<?php
/**
 * Classe de gestion des emails
 */

if (!defined('ABSPATH')) {
    exit;
}

class Block_Traiteur_Mailer {
    
    private $settings;
    
    /**
     * Constructeur
     */
    public function __construct() {
        $this->settings = Block_Traiteur_Cache::get_settings();
        $this->configure_smtp();
    }
    
    /**
     * Configurer SMTP si activé
     */
    private function configure_smtp() {
        if ($this->settings['smtp_enabled']) {
            add_action('phpmailer_init', array($this, 'configure_phpmailer'));
        }
    }
    
    /**
     * Configurer PHPMailer pour SMTP
     */
    public function configure_phpmailer($phpmailer) {
        $phpmailer->isSMTP();
        $phpmailer->Host = $this->settings['smtp_host'] ?? '';
        $phpmailer->SMTPAuth = true;
        $phpmailer->Port = $this->settings['smtp_port'] ?? 587;
        $phpmailer->Username = $this->settings['smtp_username'] ?? '';
        $phpmailer->Password = $this->settings['smtp_password'] ?? '';
        $phpmailer->SMTPSecure = $this->settings['smtp_encryption'] ?? 'tls';
        $phpmailer->From = $this->settings['email_from_email'];
        $phpmailer->FromName = $this->settings['email_from_name'];
    }
    
    /**
     * Envoyer la confirmation de devis au client
     */
    public function send_quote_confirmation($quote_id) {
        $quote = Block_Traiteur_Database::get_quote_data($quote_id);
        
        if (!$quote) {
            return false;
        }
        
        $to = $quote['customer_email'];
        $subject = 'Votre demande de devis Block Traiteur - ' . $quote['quote_number'];
        $message = $this->get_customer_email_template($quote);
        
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $this->settings['email_from_name'] . ' <' . $this->settings['email_from_email'] . '>',
            'Reply-To: ' . $this->settings['company_email']
        );
        
        // Ajouter le PDF en pièce jointe
        $attachments = array();
        if (!empty($quote['pdf_path']) && file_exists($quote['pdf_path'])) {
            $attachments[] = $quote['pdf_path'];
        }
        
        $sent = wp_mail($to, $subject, $message, $headers, $attachments);
        
        // Envoyer aussi la notification admin
        $this->send_admin_notification($quote);
        
        return $sent;
    }
    
    /**
     * Envoyer la notification à l'admin
     */
    public function send_admin_notification($quote) {
        $admin_email = $this->settings['company_email'];
        $subject = 'Nouvelle demande de devis - ' . $quote['quote_number'];
        $message = $this->get_admin_email_template($quote);
        
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $this->settings['email_from_name'] . ' <' . $this->settings['email_from_email'] . '>'
        );
        
        return wp_mail($admin_email, $subject, $message, $headers);
    }
    
    /**
     * Template email client
     */
    private function get_customer_email_template($quote) {
        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <style>
                body { 
                    font-family: Arial, sans-serif; 
                    line-height: 1.6; 
                    color: #333; 
                    margin: 0; 
                    padding: 0;
                }
                .container { 
                    max-width: 600px; 
                    margin: 0 auto; 
                    padding: 20px; 
                }
                .header { 
                    background: #243127; 
                    color: white; 
                    padding: 20px; 
                    text-align: center; 
                    border-radius: 10px 10px 0 0; 
                }
                .content { 
                    background: #f9f9f9; 
                    padding: 30px; 
                    border-radius: 0 0 10px 10px;
                }
                .quote-details { 
                    background: white; 
                    padding: 20px; 
                    margin: 20px 0; 
                    border-radius: 5px; 
                    border-left: 4px solid #FFB404; 
                }
                .footer { 
                    background: #243127; 
                    color: white; 
                    padding: 20px; 
                    text-align: center; 
                    border-radius: 0 0 10px 10px; 
                    font-size: 12px; 
                }
                .btn { 
                    background: #FFB404; 
                    color: #000; 
                    padding: 12px 24px; 
                    text-decoration: none; 
                    border-radius: 5px; 
                    display: inline-block; 
                    font-weight: bold; 
                    margin: 15px 0;
                }
                .highlight { 
                    color: #EF3D1D; 
                    font-weight: bold; 
                    font-size: 18px;
                }
                .price-table {
                    width: 100%;
                    border-collapse: collapse;
                    margin: 15px 0;
                }
                .price-table td {
                    padding: 8px;
                    border-bottom: 1px solid #ddd;
                }
                .price-table .total {
                    background: #f0f0f0;
                    font-weight: bold;
                    font-size: 16px;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>Block Street Food & Events</h1>
                    <p>Votre demande de devis a bien été reçue</p>
                </div>
                
                <div class="content">
                    <h2>Bonjour <?php echo esc_html($quote['customer_name']); ?>,</h2>
                    
                    <p>Nous vous remercions pour votre demande de devis pour votre événement.</p>
                    
                    <div class="quote-details">
                        <h3>Récapitulatif de votre demande</h3>
                        <p><strong>Numéro de devis:</strong> <?php echo esc_html($quote['quote_number']); ?></p>
                        <p><strong>Service:</strong> <?php echo ucfirst($quote['service_type']); ?></p>
                        <p><strong>Date événement:</strong> <?php echo date_i18n('d/m/Y', strtotime($quote['event_date'])); ?></p>
                        <p><strong>Nombre d'invités:</strong> <?php echo esc_html($quote['guest_count']); ?> personne(s)</p>
                        
                        <table class="price-table">
                            <tr>
                                <td>Forfait de base</td>
                                <td style="text-align: right;"><?php echo number_format($quote['base_price'], 2); ?> € TTC</td>
                            </tr>
                            <?php if ($quote['supplements_price'] > 0): ?>
                            <tr>
                                <td>Suppléments</td>
                                <td style="text-align: right;"><?php echo number_format($quote['supplements_price'], 2); ?> € TTC</td>
                            </tr>
                            <?php endif; ?>
                            <?php if ($quote['products_price'] > 0): ?>
                            <tr>
                                <td>Produits</td>
                                <td style="text-align: right;"><?php echo number_format($quote['products_price'], 2); ?> € TTC</td>
                            </tr>
                            <?php endif; ?>
                            <?php if ($quote['beverages_price'] > 0): ?>
                            <tr>
                                <td>Boissons</td>
                                <td style="text-align: right;"><?php echo number_format($quote['beverages_price'], 2); ?> € TTC</td>
                            </tr>
                            <?php endif; ?>
                            <tr class="total">
                                <td>TOTAL TTC</td>
                                <td style="text-align: right;" class="highlight"><?php echo number_format($quote['total_price'], 2); ?> € TTC</td>
                            </tr>
                        </table>
                    </div>
                    
                    <h3>Prochaines étapes</h3>
                    <p>Notre équipe va étudier votre demande et vous recontacter sous <strong>48 heures</strong> pour :</p>
                    <ul>
                        <li>Affiner votre devis selon vos besoins spécifiques</li>
                        <li>Confirmer la disponibilité à la date souhaitée</li>
                        <li>Répondre à toutes vos questions</li>
                        <li>Créer avec vous l'expérience parfaite pour votre événement</li>
                    </ul>
                    
                    <p>Vous trouverez en pièce jointe le devis détaillé au format PDF.</p>
                    
                    <p><em>Ce devis est estimatif et pourra être ajusté selon vos besoins finaux.</em></p>
                    
                    <div style="text-align: center; margin: 30px 0;">
                        <a href="tel:<?php echo esc_attr($this->settings['company_phone']); ?>" class="btn">
                            Nous contacter: <?php echo esc_html($this->settings['company_phone']); ?>
                        </a>
                    </div>
                    
                    <p>Nous avons hâte de collaborer avec vous pour faire de votre événement un moment inoubliable !</p>
                    
                    <p>L'équipe Block</p>
                </div>
                
                <div class="footer">
                    <p><strong><?php echo esc_html($this->settings['company_name']); ?></strong></p>
                    <p><?php echo nl2br(esc_html($this->settings['company_address'])); ?></p>
                    <p>Tél: <?php echo esc_html($this->settings['company_phone']); ?> | Email: <?php echo esc_html($this->settings['company_email']); ?></p>
                </div>
            </div>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Template email admin
     */
    private function get_admin_email_template($quote) {
        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #243127; color: white; padding: 20px; text-align: center; }
                .content { background: #f9f9f9; padding: 20px; }
                .quote-info { background: white; padding: 15px; margin: 15px 0; border-radius: 5px; }
                .urgent { color: #EF3D1D; font-weight: bold; }
                .actions { background: #FFB404; padding: 15px; text-align: center; margin: 20px 0; }
                .actions a { color: #000; text-decoration: none; font-weight: bold; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h2>Nouvelle demande de devis</h2>
                    <p>Devis N° <?php echo esc_html($quote['quote_number']); ?></p>
                </div>
                
                <div class="content">
                    <div class="quote-info">
                        <h3>Informations client</h3>
                        <p><strong>Nom:</strong> <?php echo esc_html($quote['customer_name']); ?></p>
                        <p><strong>Email:</strong> <a href="mailto:<?php echo esc_attr($quote['customer_email']); ?>"><?php echo esc_html($quote['customer_email']); ?></a></p>
                        <?php if ($quote['customer_phone']): ?>
                        <p><strong>Téléphone:</strong> <a href="tel:<?php echo esc_attr($quote['customer_phone']); ?>"><?php echo esc_html($quote['customer_phone']); ?></a></p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="quote-info">
                        <h3>Détails événement</h3>
                        <p><strong>Service:</strong> <?php echo ucfirst($quote['service_type']); ?></p>
                        <p><strong>Date:</strong> <span class="urgent"><?php echo date_i18n('d/m/Y', strtotime($quote['event_date'])); ?></span></p>
                        <p><strong>Invités:</strong> <?php echo esc_html($quote['guest_count']); ?> personnes</p>
                        <p><strong>Durée:</strong> <?php echo esc_html($quote['event_duration']); ?> heures</p>
                        <?php if ($quote['event_location']): ?>
                        <p><strong>Lieu:</strong> <?php echo esc_html($quote['event_location']); ?> (<?php echo esc_html($quote['postal_code']); ?>)</p>
                        <?php endif; ?>
                        <?php if ($quote['distance_km']): ?>
                        <p><strong>Distance:</strong> <?php echo esc_html($quote['distance_km']); ?> km</p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="quote-info">
                        <h3>Récapitulatif financier</h3>
                        <p><strong>Prix total:</strong> <span class="urgent"><?php echo number_format($quote['total_price'], 2); ?> € TTC</span></p>
                        <p>Forfait de base: <?php echo number_format($quote['base_price'], 2); ?> € TTC</p>
                        <?php if ($quote['supplements_price'] > 0): ?>
                        <p>Suppléments: <?php echo number_format($quote['supplements_price'], 2); ?> € TTC</p>
                        <?php endif; ?>
                        <?php if ($quote['products_price'] > 0): ?>
                        <p>Produits: <?php echo number_format($quote['products_price'], 2); ?> € TTC</p>
                        <?php endif; ?>
                        <?php if ($quote['beverages_price'] > 0): ?>
                        <p>Boissons: <?php echo number_format($quote['beverages_price'], 2); ?> € TTC</p>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($quote['customer_comments']): ?>
                    <div class="quote-info">
                        <h3>Commentaires client</h3>
                        <p><em><?php echo nl2br(esc_html($quote['customer_comments'])); ?></em></p>
                    </div>
                    <?php endif; ?>
                    
                    <div class="actions">
                        <p><strong>Action requise:</strong> Traiter cette demande dans les 48h</p>
                        <p><a href="<?php echo admin_url('admin.php?page=block-traiteur-quotes&action=view&id=' . $quote['id']); ?>">Voir le devis dans l'admin →</a></p>
                    </div>
                </div>
            </div>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Envoyer un rappel de devis
     */
    public function send_quote_reminder($quote_id) {
        $quote = Block_Traiteur_Database::get_quote_data($quote_id);
        
        if (!$quote || $quote['status'] !== 'sent') {
            return false;
        }
        
        $to = $quote['customer_email'];
        $subject = 'Rappel - Votre devis Block Traiteur - ' . $quote['quote_number'];
        $message = $this->get_reminder_email_template($quote);
        
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $this->settings['email_from_name'] . ' <' . $this->settings['email_from_email'] . '>',
            'Reply-To: ' . $this->settings['company_email']
        );
        
        return wp_mail($to, $subject, $message, $headers);
    }
    
    /**
     * Template email de rappel
     */
    private function get_reminder_email_template($quote) {
        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #243127; color: white; padding: 20px; text-align: center; }
                .content { background: #f9f9f9; padding: 20px; }
                .highlight { color: #EF3D1D; font-weight: bold; }
                .btn { background: #FFB404; color: #000; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block; font-weight: bold; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h2>Rappel - Votre devis Block Traiteur</h2>
                    <p>Devis N° <?php echo esc_html($quote['quote_number']); ?></p>
                </div>
                
                <div class="content">
                    <h3>Bonjour <?php echo esc_html($quote['customer_name']); ?>,</h3>
                    
                    <p>Nous espérons que vous allez bien !</p>
                    
                    <p>Il y a quelques jours, vous avez fait une demande de devis pour votre événement du <strong><?php echo date_i18n('d/m/Y', strtotime($quote['event_date'])); ?></strong>.</p>
                    
                    <p>Votre devis est toujours valable et nous serions ravis de vous accompagner dans l'organisation de votre événement.</p>
                    
                    <p>Récapitulatif :</p>
                    <ul>
                        <li>Service : <?php echo ucfirst($quote['service_type']); ?></li>
                        <li>Date : <?php echo date_i18n('d/m/Y', strtotime($quote['event_date'])); ?></li>
                        <li>Invités : <?php echo esc_html($quote['guest_count']); ?> personnes</li>
                        <li>Montant : <span class="highlight"><?php echo number_format($quote['total_price'], 2); ?> € TTC</span></li>
                    </ul>
                    
                    <p style="text-align: center; margin: 30px 0;">
                        <a href="tel:<?php echo esc_attr($this->settings['company_phone']); ?>" class="btn">
                            Contactez-nous : <?php echo esc_html($this->settings['company_phone']); ?>
                        </a>
                    </p>
                    
                    <p>N'hésitez pas à nous contacter si vous avez des questions ou si vous souhaitez modifier votre devis.</p>
                    
                    <p>À très bientôt,<br>L'équipe Block</p>
                </div>
            </div>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Tester la configuration email
     */
    public function test_email_configuration() {
        $admin_email = get_option('admin_email');
        $subject = 'Test email Block Traiteur';
        $message = '<p>Ceci est un email de test pour vérifier la configuration email du plugin Block Traiteur.</p>';
        
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $this->settings['email_from_name'] . ' <' . $this->settings['email_from_email'] . '>'
        );
        
        return wp_mail($admin_email, $subject, $message, $headers);
    }
}