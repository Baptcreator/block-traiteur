<?php
/**
 * Template de base pour tous les emails Block Traiteur
 *
 * @package Block_Traiteur
 * @subpackage Templates/Emails
 * @since 1.0.0
 * 
 * Variables disponibles :
 * @var string $email_title Titre de l'email
 * @var string $email_content Contenu principal de l'email
 * @var array $quote_data Données du devis (optionnel)
 * @var array $settings Paramètres du plugin
 */

// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit;
}

// Variables par défaut
$company_name = $settings['company_name'] ?? 'Block Strasbourg';
$company_email = $settings['company_email'] ?? 'contact@block-strasbourg.fr';
$company_phone = $settings['company_phone'] ?? '';
$company_address = $settings['company_address'] ?? '';
$header_color = $settings['email_template_header_color'] ?? '#243127';
$accent_color = $settings['email_template_accent_color'] ?? '#FFB404';
$logo_url = $settings['company_logo_url'] ?? '';
$website_url = home_url();
$current_year = date('Y');

?><!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?php echo esc_html($email_title ?? 'Block Strasbourg'); ?></title>
    
    <!--[if mso]>
    <noscript>
        <xml>
            <o:OfficeDocumentSettings>
                <o:PixelsPerInch>96</o:PixelsPerInch>
            </o:OfficeDocumentSettings>
        </xml>
    </noscript>
    <![endif]-->
    
    <style type="text/css">
        /* Reset et styles de base */
        body, table, td, a { 
            -webkit-text-size-adjust: 100%; 
            -ms-text-size-adjust: 100%; 
        }
        
        table, td { 
            mso-table-lspace: 0pt; 
            mso-table-rspace: 0pt; 
        }
        
        img { 
            -ms-interpolation-mode: bicubic; 
            border: 0;
            max-width: 100%;
            height: auto;
        }
        
        /* Styles principaux */
        body {
            margin: 0 !important;
            padding: 0 !important;
            background-color: #f8f9fa;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            font-size: 16px;
            line-height: 1.6;
            color: #333333;
        }
        
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        /* En-tête */
        .email-header {
            background: linear-gradient(135deg, <?php echo esc_attr($header_color); ?> 0%, <?php echo esc_attr($accent_color); ?> 100%);
            padding: 30px 20px;
            text-align: center;
            position: relative;
        }
        
        .email-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml;charset=utf8,%3Csvg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 20"%3E%3Cpath d="M0 20L100 0v20z" fill="rgba(255,255,255,0.1)"/%3E%3C/svg%3E') repeat-x;
            background-size: 100px 20px;
        }
        
        .email-logo {
            position: relative;
            z-index: 2;
            margin-bottom: 15px;
        }
        
        .email-logo img {
            max-width: 80px;
            height: auto;
        }
        
        .email-logo-fallback {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            font-weight: bold;
            color: #ffffff;
            text-decoration: none;
        }
        
        .email-header h1 {
            margin: 0;
            color: #ffffff;
            font-size: 28px;
            font-weight: 600;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
            position: relative;
            z-index: 2;
        }
        
        .email-header p {
            margin: 10px 0 0 0;
            color: rgba(255, 255, 255, 0.9);
            font-size: 16px;
            position: relative;
            z-index: 2;
        }
        
        /* Corps de l'email */
        .email-body {
            padding: 40px 30px;
        }
        
        .email-content {
            font-size: 16px;
            line-height: 1.6;
            color: #333333;
        }
        
        .email-content h2 {
            color: <?php echo esc_attr($header_color); ?>;
            font-size: 24px;
            font-weight: 600;
            margin: 30px 0 20px 0;
            padding-bottom: 10px;
            border-bottom: 2px solid <?php echo esc_attr($accent_color); ?>;
        }
        
        .email-content h3 {
            color: <?php echo esc_attr($header_color); ?>;
            font-size: 20px;
            font-weight: 600;
            margin: 25px 0 15px 0;
        }
        
        .email-content h4 {
            color: <?php echo esc_attr($header_color); ?>;
            font-size: 18px;
            font-weight: 600;
            margin: 20px 0 10px 0;
        }
        
        .email-content p {
            margin: 0 0 16px 0;
        }
        
        .email-content ul, .email-content ol {
            margin: 16px 0;
            padding-left: 20px;
        }
        
        .email-content li {
            margin-bottom: 8px;
        }
        
        /* Tableaux */
        .email-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .email-table th {
            background-color: <?php echo esc_attr($header_color); ?>;
            color: #ffffff;
            padding: 15px 12px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .email-table td {
            padding: 12px;
            border-bottom: 1px solid #e9ecef;
            vertical-align: top;
        }
        
        .email-table tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        .email-table tr:last-child td {
            border-bottom: none;
        }
        
        .table-total {
            background-color: <?php echo esc_attr($header_color); ?> !important;
            color: #ffffff !important;
            font-weight: bold;
            font-size: 16px;
        }
        
        /* Boutons */
        .email-button {
            display: inline-block;
            padding: 15px 30px;
            background: linear-gradient(135deg, <?php echo esc_attr($header_color); ?> 0%, <?php echo esc_attr($accent_color); ?> 100%);
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            font-size: 16px;
            text-align: center;
            margin: 20px 0;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
        }
        
        .email-button:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
        }
        
        .email-button-secondary {
            background: #ffffff;
            color: <?php echo esc_attr($header_color); ?> !important;
            border: 2px solid <?php echo esc_attr($header_color); ?>;
        }
        
        /* Alertes et encadrés */
        .email-alert {
            padding: 20px;
            margin: 20px 0;
            border-radius: 6px;
            border-left: 4px solid;
        }
        
        .email-alert.success {
            background-color: #d4edda;
            border-color: #28a745;
            color: #155724;
        }
        
        .email-alert.info {
            background-color: #d1ecf1;
            border-color: #17a2b8;
            color: #0c5460;
        }
        
        .email-alert.warning {
            background-color: #fff3cd;
            border-color: #ffc107;
            color: #856404;
        }
        
        .email-alert.error {
            background-color: #f8d7da;
            border-color: #dc3545;
            color: #721c24;
        }
        
        /* Informations de contact */
        .contact-info {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 25px;
            margin: 30px 0;
            border-radius: 8px;
            border: 1px solid #dee2e6;
        }
        
        .contact-info h4 {
            color: <?php echo esc_attr($header_color); ?>;
            margin: 0 0 15px 0;
            font-size: 18px;
        }
        
        .contact-item {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            font-size: 14px;
        }
        
        .contact-icon {
            width: 20px;
            height: 20px;
            margin-right: 10px;
            background-color: <?php echo esc_attr($accent_color); ?>;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: <?php echo esc_attr($header_color); ?>;
            font-weight: bold;
            font-size: 10px;
        }
        
        /* Pied de page */
        .email-footer {
            background-color: #343a40;
            color: #ffffff;
            padding: 30px 20px;
            text-align: center;
        }
        
        .email-footer p {
            margin: 0 0 10px 0;
            font-size: 14px;
            line-height: 1.5;
        }
        
        .email-footer a {
            color: <?php echo esc_attr($accent_color); ?>;
            text-decoration: none;
        }
        
        .email-footer a:hover {
            text-decoration: underline;
        }
        
        .social-links {
            margin: 20px 0 10px 0;
        }
        
        .social-links a {
            display: inline-block;
            margin: 0 8px;
            padding: 8px;
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            color: #ffffff;
            text-decoration: none;
            font-size: 16px;
            width: 36px;
            height: 36px;
            line-height: 20px;
            text-align: center;
        }
        
        .social-links a:hover {
            background-color: <?php echo esc_attr($accent_color); ?>;
            transform: translateY(-1px);
        }
        
        .unsubscribe-link {
            font-size: 12px;
            color: #adb5bd;
            margin-top: 15px;
        }
        
        /* Responsive */
        @media only screen and (max-width: 600px) {
            .email-container {
                width: 100% !important;
                margin: 0 !important;
                border-radius: 0 !important;
            }
            
            .email-header {
                padding: 20px 15px;
            }
            
            .email-header h1 {
                font-size: 24px;
            }
            
            .email-body {
                padding: 25px 20px;
            }
            
            .email-content {
                font-size: 15px;
            }
            
            .email-table th,
            .email-table td {
                padding: 8px 6px;
                font-size: 14px;
            }
            
            .email-button {
                display: block;
                text-align: center;
                padding: 12px 20px;
                font-size: 15px;
            }
            
            .contact-info {
                padding: 20px 15px;
            }
            
            .email-footer {
                padding: 20px 15px;
            }
        }
        
        /* Dark mode support */
        @media (prefers-color-scheme: dark) {
            .email-container {
                background-color: #1a1a1a;
            }
            
            .email-content {
                color: #e9ecef;
            }
            
            .email-table tr:nth-child(even) {
                background-color: #2d2d2d;
            }
            
            .email-table td {
                border-bottom-color: #404040;
            }
            
            .contact-info {
                background: linear-gradient(135deg, #2d2d2d 0%, #404040 100%);
                border-color: #495057;
            }
        }
        
        /* Print styles */
        @media print {
            .email-container {
                box-shadow: none;
                border: 1px solid #000000;
            }
            
            .email-header {
                background: <?php echo esc_attr($header_color); ?> !important;
                -webkit-print-color-adjust: exact;
            }
            
            .email-button {
                background: <?php echo esc_attr($header_color); ?> !important;
                -webkit-print-color-adjust: exact;
            }
            
            .social-links {
                display: none;
            }
        }
    </style>
</head>

<body>
    <div class="email-container">
        <!-- En-tête -->
        <div class="email-header">
            <div class="email-logo">
                <?php if (!empty($logo_url)): ?>
                    <img src="<?php echo esc_url($logo_url); ?>" alt="<?php echo esc_attr($company_name); ?>" />
                <?php else: ?>
                    <div class="email-logo-fallback">B</div>
                <?php endif; ?>
            </div>
            
            <h1><?php echo esc_html($company_name); ?></h1>
            <p><?php _e('Traiteur événementiel - Cuisine de rue authentique', 'block-traiteur'); ?></p>
        </div>
        
        <!-- Corps de l'email -->
        <div class="email-body">
            <div class="email-content">
                <?php echo $email_content; ?>
            </div>
            
            <!-- Informations de contact -->
            <?php if (!empty($company_phone) || !empty($company_email) || !empty($company_address)): ?>
            <div class="contact-info">
                <h4><?php _e('Nous contacter', 'block-traiteur'); ?></h4>
                
                <?php if (!empty($company_phone)): ?>
                <div class="contact-item">
                    <span class="contact-icon">📞</span>
                    <span><?php echo esc_html($company_phone); ?></span>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($company_email)): ?>
                <div class="contact-item">
                    <span class="contact-icon">✉</span>
                    <a href="mailto:<?php echo esc_attr($company_email); ?>"><?php echo esc_html($company_email); ?></a>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($company_address)): ?>
                <div class="contact-item">
                    <span class="contact-icon">📍</span>
                    <span><?php echo esc_html($company_address); ?></span>
                </div>
                <?php endif; ?>
                
                <div class="contact-item">
                    <span class="contact-icon">🌐</span>
                    <a href="<?php echo esc_url($website_url); ?>"><?php echo esc_html(parse_url($website_url, PHP_URL_HOST)); ?></a>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Pied de page -->
        <div class="email-footer">
            <p><strong><?php echo esc_html($company_name); ?></strong></p>
            <p><?php _e('L\'expérience Block - Cuisine de rue authentique et généreuse', 'block-traiteur'); ?></p>
            
            <!-- Liens sociaux (optionnels) -->
            <div class="social-links">
                <?php if (!empty($settings['facebook_url'])): ?>
                <a href="<?php echo esc_url($settings['facebook_url']); ?>" title="Facebook">f</a>
                <?php endif; ?>
                
                <?php if (!empty($settings['instagram_url'])): ?>
                <a href="<?php echo esc_url($settings['instagram_url']); ?>" title="Instagram">📷</a>
                <?php endif; ?>
                
                <?php if (!empty($settings['linkedin_url'])): ?>
                <a href="<?php echo esc_url($settings['linkedin_url']); ?>" title="LinkedIn">in</a>
                <?php endif; ?>
            </div>
            
            <p>
                &copy; <?php echo $current_year; ?> <?php echo esc_html($company_name); ?>. 
                <?php _e('Tous droits réservés.', 'block-traiteur'); ?>
            </p>
            
            <div class="unsubscribe-link">
                <p>
                    <?php _e('Vous recevez cet email car vous avez effectué une demande de devis.', 'block-traiteur'); ?><br>
                    <a href="<?php echo esc_url($website_url . '/mentions-legales'); ?>"><?php _e('Mentions légales', 'block-traiteur'); ?></a> | 
                    <a href="<?php echo esc_url($website_url . '/confidentialite'); ?>"><?php _e('Politique de confidentialité', 'block-traiteur'); ?></a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>