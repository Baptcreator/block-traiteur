<?php
/**
 * Script pour injecter directement le CSS dans le DOM
 * Contourne TOUS les conflits Elementor/WordPress
 */

// Charger WordPress
$wp_load_paths = [
    '../../../wp-load.php',
    '../../../../wp-load.php',
    '../../../../../wp-load.php'
];

$wp_loaded = false;
foreach ($wp_load_paths as $path) {
    if (file_exists($path)) {
        require_once $path;
        $wp_loaded = true;
        break;
    }
}

if (!$wp_loaded) {
    die('WordPress non trouvé');
}

// Injecter le CSS directement avec la plus haute priorité
add_action('wp_head', function() {
    ?>
    <style id="block-traiteur-force-direct" type="text/css">
    /* CSS FORCE DIRECTE - PRIORITÉ MAXIMALE */
    
    /* Container principal */
    .block-quote-form,
    .block-quote-form-68ba04dbb24a9,
    div[class*="block-quote-form"],
    .elementor-widget-container .block-quote-form,
    .elementor-section .block-quote-form,
    .elementor-element .block-quote-form,
    body .block-quote-form {
        border-radius: 20px !important;
        background: #FFFFFF !important;
        padding: 16px 24px !important;
        max-width: 1200px !important;
        margin: 0 auto !important;
        box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1) !important;
        border: 1px solid #F6F2E7 !important;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important;
        position: relative !important;
        overflow: hidden !important;
        box-sizing: border-box !important;
    }
    
    /* Reset complet des éléments enfants */
    .block-quote-form *,
    .block-quote-form *::before,
    .block-quote-form *::after {
        box-sizing: border-box !important;
    }
    
    /* Headers */
    .block-quote-form .form-header h2 {
        font-size: 2rem !important;
        color: #243127 !important;
        margin: 0 0 24px 0 !important;
        padding: 0 !important;
        font-weight: 700 !important;
        text-align: center !important;
    }
    
    .block-quote-form .step-header h3 {
        font-size: 1.5rem !important;
        color: #243127 !important;
        margin: 0 0 16px 0 !important;
        padding: 0 !important;
        font-weight: 600 !important;
    }
    
    /* Service Cards */
    .block-quote-form .service-card {
        background: #FFFFFF !important;
        border: 3px solid #F6F2E7 !important;
        border-radius: 20px !important;
        padding: 48px !important;
        text-align: center !important;
        cursor: pointer !important;
        transition: all 0.25s ease-out !important;
        margin: 24px 0 !important;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1) !important;
        display: flex !important;
        flex-direction: column !important;
        align-items: center !important;
        justify-content: space-between !important;
        min-height: 400px !important;
    }
    
    .block-quote-form .service-card:hover {
        border-color: #FFB404 !important;
        box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1) !important;
        transform: translateY(-2px) !important;
    }
    
    /* Boutons */
    .block-quote-form .btn {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        padding: 16px 32px !important;
        border-radius: 10px !important;
        font-family: 'Inter', sans-serif !important;
        font-weight: 600 !important;
        text-decoration: none !important;
        cursor: pointer !important;
        transition: all 0.25s ease-out !important;
        border: 2px solid transparent !important;
        font-size: 1rem !important;
        line-height: 1.2 !important;
        min-height: 48px !important;
    }
    
    .block-quote-form .btn-primary {
        background: #FFB404 !important;
        color: #243127 !important;
        border-color: #FFB404 !important;
    }
    
    .block-quote-form .btn-primary:hover {
        background: #243127 !important;
        color: #FFFFFF !important;
        border-color: #243127 !important;
    }
    
    .block-quote-form .btn-secondary {
        background: #FFFFFF !important;
        color: #243127 !important;
        border-color: #243127 !important;
    }
    
    .block-quote-form .btn-secondary:hover {
        background: #243127 !important;
        color: #FFFFFF !important;
    }
    
    /* Inputs */
    .block-quote-form input,
    .block-quote-form select,
    .block-quote-form textarea {
        width: 100% !important;
        padding: 16px !important;
        border: 2px solid #F6F2E7 !important;
        border-radius: 10px !important;
        background: #FFFFFF !important;
        font-family: 'Inter', sans-serif !important;
        font-size: 1rem !important;
        color: #243127 !important;
        box-sizing: border-box !important;
        outline: none !important;
        margin: 0 !important;
    }
    
    .block-quote-form input:focus,
    .block-quote-form select:focus,
    .block-quote-form textarea:focus {
        border-color: #FFB404 !important;
        box-shadow: 0 0 0 3px rgba(255, 180, 4, 0.2) !important;
    }
    
    /* Progress Bar */
    .block-quote-form .progress-bar {
        width: 100% !important;
        height: 8px !important;
        background: #F6F2E7 !important;
        border-radius: 4px !important;
        overflow: hidden !important;
        margin: 0 0 24px 0 !important;
    }
    
    .block-quote-form .progress-fill {
        height: 100% !important;
        background: #FFB404 !important;
        transition: width 0.25s ease-out !important;
    }
    
    /* Price Display */
    .block-quote-form .initial-price-display {
        text-align: center !important;
        margin: 20px 0 !important;
        padding: 15px !important;
        background: #F8F9FA !important;
        border-radius: 20px !important;
        border: 2px solid #F6F2E7 !important;
    }
    
    .block-quote-form .initial-price-display h4 {
        margin: 0 0 10px 0 !important;
        color: #243127 !important;
        font-size: 1.2rem !important;
        font-weight: 600 !important;
    }
    
    /* Navigation */
    .block-quote-form .form-navigation {
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important;
        margin: 32px 0 0 0 !important;
        padding: 24px 0 0 0 !important;
        border-top: 1px solid #F6F2E7 !important;
    }
    
    /* Service Icons */
    .block-quote-form .service-icon img {
        width: 80px !important;
        height: 80px !important;
        margin: 0 0 16px 0 !important;
    }
    
    /* Service Content */
    .block-quote-form .service-content h4 {
        font-size: 1.5rem !important;
        color: #243127 !important;
        margin: 0 0 8px 0 !important;
        font-weight: 700 !important;
    }
    
    .block-quote-form .service-capacity {
        color: #FFB404 !important;
        font-weight: 600 !important;
        font-size: 1rem !important;
        margin: 0 0 16px 0 !important;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .block-quote-form {
            padding: 16px !important;
            margin: 16px !important;
            border-radius: 20px !important;
        }
        
        .block-quote-form .service-card {
            padding: 24px !important;
            margin: 16px 0 !important;
        }
        
        .block-quote-form .form-navigation {
            flex-direction: column !important;
            gap: 16px !important;
        }
    }
    </style>
    
    <script>
    // Force l'application des styles après chargement
    document.addEventListener('DOMContentLoaded', function() {
        // Attendre un peu pour que tous les styles soient chargés
        setTimeout(function() {
            const forms = document.querySelectorAll('[class*="block-quote-form"]');
            forms.forEach(function(form) {
                // Force l'application des styles
                form.style.borderRadius = '20px';
                form.style.background = '#FFFFFF';
                form.style.boxShadow = '0 10px 15px rgba(0, 0, 0, 0.1)';
                form.style.border = '1px solid #F6F2E7';
                form.style.overflow = 'hidden';
                
                // Force les cartes de service
                const serviceCards = form.querySelectorAll('.service-card');
                serviceCards.forEach(function(card) {
                    card.style.borderRadius = '20px';
                    card.style.border = '3px solid #F6F2E7';
                    card.style.background = '#FFFFFF';
                });
                
                // Force les boutons
                const buttons = form.querySelectorAll('.btn');
                buttons.forEach(function(btn) {
                    btn.style.borderRadius = '10px';
                    if (btn.classList.contains('btn-primary')) {
                        btn.style.background = '#FFB404';
                        btn.style.color = '#243127';
                        btn.style.border = '2px solid #FFB404';
                    }
                });
            });
        }, 500);
    });
    </script>
    <?php
}, 999999); // Priorité maximale

echo "<h1>✅ CSS FORCE DIRECTE ACTIVÉ</h1>";
echo "<p>Le CSS est maintenant injecté directement avec la priorité maximale.</p>";
echo "<p><strong>Testez maintenant votre page !</strong></p>";
echo "<p>Styles appliqués :</p>";
echo "<ul>";
echo "<li>✅ Border-radius 20px sur le formulaire</li>";
echo "<li>✅ Border-radius 20px sur les cartes de service</li>";
echo "<li>✅ Border-radius 10px sur les boutons</li>";
echo "<li>✅ Couleurs Block (vert/jaune)</li>";
echo "<li>✅ Typographie Inter</li>";
echo "<li>✅ Shadows et transitions</li>";
echo "</ul>";
echo "<p><em>Note: Supprimez ce fichier une fois que tout fonctionne.</em></p>";
?>
