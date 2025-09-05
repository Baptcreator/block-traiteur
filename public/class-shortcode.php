<?php
/**
 * Classe des shortcodes publics pour Block Traiteur
 * VERSION COMPLÈTEMENT REFAITE selon cahier des charges
 *
 * @package Block_Traiteur
 * @subpackage Public
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class Block_Traiteur_Shortcode {
    
    private $settings;
    private $calculator;
    
    public function __construct() {
        $this->settings = Block_Traiteur_Settings::get_instance();
        $this->calculator = new Block_Traiteur_Calculator();
        
        // Enregistrer les shortcodes
        add_shortcode('block_traiteur_form', array($this, 'render_quote_form'));
        add_shortcode('block_traiteur_services', array($this, 'render_services_selection'));
        
        error_log('Block Traiteur: Shortcodes enregistrés - nouvelle architecture');
    }
    
    /**
     * Shortcode principal : formulaire de devis
     * Usage: [block_traiteur_form service="restaurant|remorque"]
     */
    public function render_quote_form($atts) {
        $atts = shortcode_atts(array(
            'service' => 'restaurant'
        ), $atts);
        
        $service_type = sanitize_text_field($atts['service']);
        if (!in_array($service_type, array('restaurant', 'remorque'))) {
            $service_type = 'restaurant';
        }
        
        $pricing = $this->settings->get_pricing_settings();
        $texts = $this->settings->get_interface_texts();
        
        ob_start();
        ?>
        <div class="block-traiteur-form" data-service="<?php echo esc_attr($service_type); ?>">
            <div class="form-header">
                <h2><?php echo $service_type === 'restaurant' ? esc_html($texts['traiteur_restaurant_title']) : esc_html($texts['traiteur_remorque_title']); ?></h2>
                <p><?php echo $service_type === 'restaurant' ? esc_html($texts['traiteur_restaurant_subtitle']) : esc_html($texts['traiteur_remorque_subtitle']); ?></p>
            </div>
            
            <form id="quote-form">
                <div class="form-step active" id="step-1">
                    <h3>Étape 1 : Informations de base</h3>
                    
                    <div class="field-group">
                        <label for="event-date">Date de l'événement *</label>
                        <input type="date" id="event-date" name="event_date" required min="<?php echo date('Y-m-d'); ?>">
                    </div>
                    
                    <div class="field-group">
                        <label for="guest-count">Nombre de convives *</label>
                        <input type="number" id="guest-count" name="guest_count" required 
                               min="<?php echo $service_type === 'restaurant' ? $pricing['restaurant_min_guests'] : $pricing['remorque_min_guests']; ?>"
                               max="<?php echo $service_type === 'restaurant' ? $pricing['restaurant_max_guests'] : $pricing['remorque_max_guests']; ?>">
                        <small>Entre <?php echo $service_type === 'restaurant' ? $pricing['restaurant_min_guests'] : $pricing['remorque_min_guests']; ?> et <?php echo $service_type === 'restaurant' ? $pricing['restaurant_max_guests'] : $pricing['remorque_max_guests']; ?> personnes</small>
                    </div>
                    
                    <div class="field-group">
                        <label for="duration">Durée *</label>
                        <select id="duration" name="duration" required>
                            <option value="2">2 heures (incluses)</option>
                            <option value="3">3 heures (+<?php echo $pricing['hourly_supplement']; ?>€)</option>
                            <option value="4">4 heures (+<?php echo $pricing['hourly_supplement'] * 2; ?>€)</option>
                            <?php if ($service_type === 'remorque'): ?>
                            <option value="5">5 heures (+<?php echo $pricing['hourly_supplement'] * 3; ?>€)</option>
                            <?php endif; ?>
                        </select>
                    </div>
                    
                    <?php if ($service_type === 'remorque'): ?>
                    <div class="field-group">
                        <label for="postal-code">Code postal *</label>
                        <input type="text" id="postal-code" name="postal_code" pattern="[0-9]{5}" required>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="form-step" id="step-2">
                    <h3>Étape 2 : Vos coordonnées</h3>
                    
                    <div class="field-row">
                        <div class="field-group">
                            <label for="first-name">Prénom *</label>
                            <input type="text" id="first-name" name="first_name" required>
                        </div>
                        <div class="field-group">
                            <label for="last-name">Nom *</label>
                            <input type="text" id="last-name" name="last_name" required>
                        </div>
                    </div>
                    
                    <div class="field-group">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    
                    <div class="field-group">
                        <label for="phone">Téléphone *</label>
                        <input type="tel" id="phone" name="phone" required>
                    </div>
                    
                    <div class="field-group">
                        <label for="message">Message</label>
                        <textarea id="message" name="message" rows="4"></textarea>
                    </div>
                </div>
                
                <div class="price-calculator">
                    <h4>Estimation du prix</h4>
                    <div class="price-breakdown">
                        <div class="price-line">
                            <span>Forfait de base :</span>
                            <span id="base-price"><?php echo number_format($service_type === 'restaurant' ? $pricing['restaurant_base_price'] : $pricing['remorque_base_price'], 0, ',', ' '); ?> €</span>
                        </div>
                        <div class="price-line" id="duration-line" style="display: none;">
                            <span>Supplément durée :</span>
                            <span id="duration-price">0 €</span>
                        </div>
                        <?php if ($service_type === 'remorque'): ?>
                        <div class="price-line" id="guests-line" style="display: none;">
                            <span>Supplément convives :</span>
                            <span id="guests-price">0 €</span>
                        </div>
                        <div class="price-line" id="delivery-line" style="display: none;">
                            <span>Frais livraison :</span>
                            <span id="delivery-price">0 €</span>
                        </div>
                        <?php endif; ?>
                        <div class="price-total">
                            <span><strong>Total estimé :</strong></span>
                            <span id="total-price"><strong><?php echo number_format($service_type === 'restaurant' ? $pricing['restaurant_base_price'] : $pricing['remorque_base_price'], 0, ',', ' '); ?> €</strong></span>
                        </div>
                    </div>
                </div>
                
                <div class="form-navigation">
                    <button type="button" id="prev-step" style="display: none;">Précédent</button>
                    <button type="button" id="next-step">Suivant</button>
                    <button type="submit" id="submit-quote" style="display: none;">Demander un devis</button>
                </div>
            </form>
            
            <div id="form-messages"></div>
        </div>
        
        <style>
        .block-traiteur-form {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            font-family: system-ui, -apple-system, sans-serif;
        }
        .form-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .form-step {
            display: none;
        }
        .form-step.active {
            display: block;
        }
        .field-group {
            margin-bottom: 20px;
        }
        .field-row {
            display: flex;
            gap: 15px;
        }
        .field-row .field-group {
            flex: 1;
        }
        .field-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
        }
        .field-group input,
        .field-group select,
        .field-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        .field-group small {
            color: #666;
            font-size: 14px;
        }
        .price-calculator {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .price-line {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
        }
        .price-total {
            display: flex;
            justify-content: space-between;
            padding: 15px 0;
            border-top: 2px solid #007cba;
            margin-top: 10px;
        }
        .form-navigation {
            text-align: center;
            margin-top: 30px;
        }
        .form-navigation button {
            background: #007cba;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 4px;
            cursor: pointer;
            margin: 0 10px;
        }
        .form-navigation button:hover {
            background: #005a87;
        }
        .message {
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .message.success {
            background: #d1e7dd;
            color: #0f5132;
        }
        .message.error {
            background: #f8d7da;
            color: #721c24;
        }
        @media (max-width: 768px) {
            .field-row {
                flex-direction: column;
            }
        }
        </style>
        
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            let currentStep = 0;
            const steps = document.querySelectorAll('.form-step');
            const nextBtn = document.getElementById('next-step');
            const prevBtn = document.getElementById('prev-step');
            const submitBtn = document.getElementById('submit-quote');
            const form = document.getElementById('quote-form');
            const serviceType = '<?php echo $service_type; ?>';
            
            // Navigation
            nextBtn.addEventListener('click', function() {
                if (validateStep(currentStep)) {
                    showStep(currentStep + 1);
                }
            });
            
            prevBtn.addEventListener('click', function() {
                showStep(currentStep - 1);
            });
            
            function showStep(step) {
                steps[currentStep].classList.remove('active');
                steps[step].classList.add('active');
                currentStep = step;
                
                prevBtn.style.display = step > 0 ? 'inline-block' : 'none';
                nextBtn.style.display = step < steps.length - 1 ? 'inline-block' : 'none';
                submitBtn.style.display = step === steps.length - 1 ? 'inline-block' : 'none';
            }
            
            function validateStep(step) {
                const stepDiv = steps[step];
                const required = stepDiv.querySelectorAll('[required]');
                let valid = true;
                
                required.forEach(field => {
                    if (!field.value) {
                        field.style.borderColor = 'red';
                        valid = false;
                    } else {
                        field.style.borderColor = '#ddd';
                    }
                });
                
                return valid;
            }
            
            // Calculateur de prix
            const guestInput = document.getElementById('guest-count');
            const durationSelect = document.getElementById('duration');
            const postalInput = document.getElementById('postal-code');
            
            [guestInput, durationSelect, postalInput].forEach(input => {
                if (input) {
                    input.addEventListener('input', updatePrice);
                }
            });
            
            function updatePrice() {
                const guests = parseInt(guestInput.value) || 0;
                const duration = parseInt(durationSelect.value) || 2;
                const postal = postalInput ? postalInput.value : '';
                
                if (typeof blockTraiteur !== 'undefined') {
                    fetch(blockTraiteur.ajaxUrl, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: new URLSearchParams({
                            action: 'block_traiteur_calculate_price',
                            nonce: blockTraiteur.nonce,
                            service_type: serviceType,
                            guest_count: guests,
                            duration: duration,
                            postal_code: postal,
                            products: '[]'
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            updatePriceDisplay(data.data);
                        }
                    });
                }
            }
            
            function updatePriceDisplay(calc) {
                document.getElementById('total-price').innerHTML = '<strong>' + calc.formatted.total_price + '</strong>';
                
                const durationLine = document.getElementById('duration-line');
                if (calc.duration_supplement > 0) {
                    document.getElementById('duration-price').textContent = calc.formatted.duration_supplement;
                    durationLine.style.display = 'flex';
                } else {
                    durationLine.style.display = 'none';
                }
                
                if (serviceType === 'remorque') {
                    const guestsLine = document.getElementById('guests-line');
                    if (calc.guests_supplement > 0) {
                        document.getElementById('guests-price').textContent = calc.formatted.guests_supplement;
                        guestsLine.style.display = 'flex';
                    } else {
                        guestsLine.style.display = 'none';
                    }
                    
                    const deliveryLine = document.getElementById('delivery-line');
                    if (calc.delivery_cost > 0) {
                        document.getElementById('delivery-price').textContent = calc.formatted.delivery_cost;
                        deliveryLine.style.display = 'flex';
                    } else {
                        deliveryLine.style.display = 'none';
                    }
                }
            }
            
            // Soumission
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                if (!validateStep(currentStep)) return;
                
                const formData = new FormData(form);
                formData.append('action', 'block_traiteur_submit_quote');
                formData.append('nonce', blockTraiteur.nonce);
                formData.append('service_type', serviceType);
                
                const customerData = {};
                ['first_name', 'last_name', 'email', 'phone', 'message'].forEach(field => {
                    const input = form.querySelector('[name="' + field + '"]');
                    if (input) customerData[field] = input.value;
                });
                
                formData.append('customer_data', JSON.stringify(customerData));
                formData.append('selected_products', '[]');
                
                fetch(blockTraiteur.ajaxUrl, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    const messagesDiv = document.getElementById('form-messages');
                    if (data.success) {
                        messagesDiv.innerHTML = '<div class="message success">Devis envoyé avec succès ! Numéro : ' + data.data.quote_number + '</div>';
                        form.style.display = 'none';
                    } else {
                        messagesDiv.innerHTML = '<div class="message error">Erreur : ' + (data.data.message || 'Une erreur est survenue') + '</div>';
                    }
                });
            });
        });
        </script>
        <?php
        
        return ob_get_clean();
    }
    
    /**
     * Shortcode : sélection des services
     */
    public function render_services_selection($atts) {
        $texts = $this->settings->get_interface_texts();
        
        ob_start();
        ?>
        <div class="block-traiteur-services">
            <div class="services-grid">
                <div class="service-card">
                    <h3><?php echo esc_html($texts['traiteur_restaurant_title']); ?></h3>
                    <p><?php echo esc_html($texts['traiteur_restaurant_subtitle']); ?></p>
                    <div class="service-description">
                        <?php echo wp_kses_post($texts['traiteur_restaurant_description']); ?>
                    </div>
                    <a href="#restaurant" class="service-button">Choisir le restaurant</a>
                </div>
                
                <div class="service-card">
                    <h3><?php echo esc_html($texts['traiteur_remorque_title']); ?></h3>
                    <p><?php echo esc_html($texts['traiteur_remorque_subtitle']); ?></p>
                    <div class="service-description">
                        <?php echo wp_kses_post($texts['traiteur_remorque_description']); ?>
                    </div>
                    <a href="#remorque" class="service-button">Choisir la remorque</a>
                </div>
            </div>
        </div>
        
        <style>
        .block-traiteur-services {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }
        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 30px;
        }
        .service-card {
            background: white;
            padding: 30px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .service-button {
            display: inline-block;
            background: #007cba;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 20px;
        }
        .service-button:hover {
            background: #005a87;
        }
        </style>
        <?php
        
        return ob_get_clean();
    }
}
