/**
 * JavaScript public pour Block Traiteur
 * NOUVELLE ARCHITECTURE - Version minimaliste et fonctionnelle
 *
 * @package Block_Traiteur
 * @subpackage Public/JS
 * @since 1.0.0
 */

(function() {
    'use strict';

    /**
     * Objet principal pour le frontend
     */
    window.BlockTraiteurPublic = {
        
        /**
         * Configuration
         */
        config: {
            ajaxUrl: '',
            nonce: '',
            settings: {},
            texts: {}
        },
        
        /**
         * Initialisation
         */
        init: function() {
            // Vérifier si les données WordPress sont disponibles
            if (typeof blockTraiteur !== 'undefined') {
                this.config = blockTraiteur;
            }
            
            this.bindEvents();
            this.initForms();
            
            console.log('Block Traiteur Public: Initialisé');
        },
        
        /**
         * Lier les événements
         */
        bindEvents: function() {
            document.addEventListener('DOMContentLoaded', () => {
                this.initForms();
            });
        },
        
        /**
         * Initialiser les formulaires
         */
        initForms: function() {
            const forms = document.querySelectorAll('.block-traiteur-form');
            
            forms.forEach(form => {
                this.initSingleForm(form);
            });
        },
        
        /**
         * Initialiser un formulaire individuel
         */
        initSingleForm: function(formContainer) {
            const form = formContainer.querySelector('#quote-form');
            if (!form) return;
            
            const steps = formContainer.querySelectorAll('.form-step');
            const nextBtn = formContainer.querySelector('#next-step');
            const prevBtn = formContainer.querySelector('#prev-step');
            const submitBtn = formContainer.querySelector('#submit-quote');
            const messagesDiv = formContainer.querySelector('#form-messages');
            
            let currentStep = 0;
            const serviceType = formContainer.dataset.service;
            
            // Navigation entre étapes
            if (nextBtn) {
                nextBtn.addEventListener('click', () => {
                    if (this.validateStep(steps[currentStep])) {
                        this.showStep(steps, currentStep + 1, nextBtn, prevBtn, submitBtn);
                        currentStep++;
                    }
                });
            }
            
            if (prevBtn) {
                prevBtn.addEventListener('click', () => {
                    this.showStep(steps, currentStep - 1, nextBtn, prevBtn, submitBtn);
                    currentStep--;
                });
            }
            
            // Calculateur de prix en temps réel
            this.initPriceCalculator(formContainer, serviceType);
            
            // Soumission du formulaire
            if (form) {
                form.addEventListener('submit', (e) => {
                    e.preventDefault();
                    if (this.validateStep(steps[currentStep])) {
                        this.submitQuote(form, serviceType, messagesDiv);
                    }
                });
            }
        },
        
        /**
         * Afficher une étape
         */
        showStep: function(steps, stepIndex, nextBtn, prevBtn, submitBtn) {
            // Masquer toutes les étapes
            steps.forEach(step => {
                step.classList.remove('active');
            });
            
            // Afficher l'étape courante
            if (steps[stepIndex]) {
                steps[stepIndex].classList.add('active');
            }
            
            // Gérer les boutons de navigation
            if (prevBtn) {
                prevBtn.style.display = stepIndex > 0 ? 'inline-block' : 'none';
            }
            
            if (nextBtn) {
                nextBtn.style.display = stepIndex < steps.length - 1 ? 'inline-block' : 'none';
            }
            
            if (submitBtn) {
                submitBtn.style.display = stepIndex === steps.length - 1 ? 'inline-block' : 'none';
            }
        },
        
        /**
         * Valider une étape
         */
        validateStep: function(step) {
            const requiredFields = step.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.style.borderColor = '#dc3545';
                    isValid = false;
                } else {
                    field.style.borderColor = '';
                }
            });
            
            return isValid;
        },
        
        /**
         * Initialiser le calculateur de prix
         */
        initPriceCalculator: function(container, serviceType) {
            const guestInput = container.querySelector('#guest-count');
            const durationSelect = container.querySelector('#duration');
            const postalInput = container.querySelector('#postal-code');
            
            const updatePrice = () => {
                const guests = parseInt(guestInput?.value) || 0;
                const duration = parseInt(durationSelect?.value) || 2;
                const postal = postalInput?.value || '';
                
                if (this.config.ajaxUrl && guests > 0) {
                    this.calculatePrice(serviceType, guests, duration, postal, container);
                }
            };
            
            // Écouter les changements
            [guestInput, durationSelect, postalInput].forEach(input => {
                if (input) {
                    input.addEventListener('input', updatePrice);
                    input.addEventListener('change', updatePrice);
                }
            });
        },
        
        /**
         * Calculer le prix via AJAX
         */
        calculatePrice: function(serviceType, guests, duration, postal, container) {
            if (!this.config.ajaxUrl) return;
            
            const formData = new URLSearchParams({
                action: 'block_traiteur_calculate_price',
                nonce: this.config.nonce,
                service_type: serviceType,
                guest_count: guests,
                duration: duration,
                postal_code: postal,
                products: '[]'
            });
            
            fetch(this.config.ajaxUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.updatePriceDisplay(data.data, container);
                }
            })
            .catch(error => {
                console.error('Erreur calcul prix:', error);
            });
        },
        
        /**
         * Mettre à jour l'affichage des prix
         */
        updatePriceDisplay: function(calculation, container) {
            const totalPrice = container.querySelector('#total-price');
            if (totalPrice && calculation.formatted) {
                totalPrice.innerHTML = '<strong>' + calculation.formatted.total_price + '</strong>';
            }
            
            // Supplément durée
            const durationLine = container.querySelector('#duration-line');
            const durationPrice = container.querySelector('#duration-price');
            if (durationLine && durationPrice) {
                if (calculation.duration_supplement > 0) {
                    durationPrice.textContent = calculation.formatted.duration_supplement;
                    durationLine.style.display = 'flex';
                } else {
                    durationLine.style.display = 'none';
                }
            }
            
            // Suppléments remorque
            if (calculation.service_type === 'remorque') {
                // Supplément convives
                const guestsLine = container.querySelector('#guests-line');
                const guestsPrice = container.querySelector('#guests-price');
                if (guestsLine && guestsPrice) {
                    if (calculation.guests_supplement > 0) {
                        guestsPrice.textContent = calculation.formatted.guests_supplement;
                        guestsLine.style.display = 'flex';
                    } else {
                        guestsLine.style.display = 'none';
                    }
                }
                
                // Frais livraison
                const deliveryLine = container.querySelector('#delivery-line');
                const deliveryPrice = container.querySelector('#delivery-price');
                if (deliveryLine && deliveryPrice) {
                    if (calculation.delivery_cost > 0) {
                        deliveryPrice.textContent = calculation.formatted.delivery_cost;
                        deliveryLine.style.display = 'flex';
                    } else {
                        deliveryLine.style.display = 'none';
                    }
                }
            }
        },
        
        /**
         * Soumettre un devis
         */
        submitQuote: function(form, serviceType, messagesDiv) {
            const formData = new FormData(form);
            formData.append('action', 'block_traiteur_submit_quote');
            formData.append('nonce', this.config.nonce);
            formData.append('service_type', serviceType);
            
            // Collecter les données client
            const customerData = {};
            ['first_name', 'last_name', 'email', 'phone', 'message'].forEach(field => {
                const input = form.querySelector(`[name="${field}"]`);
                if (input && input.value) {
                    customerData[field] = input.value;
                }
            });
            
            formData.append('customer_data', JSON.stringify(customerData));
            formData.append('selected_products', '[]');
            
            // Afficher un indicateur de chargement
            const submitBtn = form.querySelector('#submit-quote');
            if (submitBtn) {
                submitBtn.textContent = 'Envoi en cours...';
                submitBtn.disabled = true;
            }
            
            fetch(this.config.ajaxUrl, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.showMessage(messagesDiv, 'Devis envoyé avec succès ! Numéro : ' + data.data.quote_number, 'success');
                    form.style.display = 'none';
                } else {
                    this.showMessage(messagesDiv, 'Erreur : ' + (data.data?.message || 'Une erreur est survenue'), 'error');
                }
            })
            .catch(error => {
                this.showMessage(messagesDiv, 'Erreur de connexion. Veuillez réessayer.', 'error');
            })
            .finally(() => {
                // Restaurer le bouton
                if (submitBtn) {
                    submitBtn.textContent = 'Demander un devis';
                    submitBtn.disabled = false;
                }
            });
        },
        
        /**
         * Afficher un message
         */
        showMessage: function(container, message, type) {
            if (!container) return;
            
            container.innerHTML = `<div class="message ${type}">${message}</div>`;
            container.scrollIntoView({ behavior: 'smooth' });
            
            // Auto-masquer les messages de succès
            if (type === 'success') {
                setTimeout(() => {
                    container.innerHTML = '';
                }, 10000);
            }
        }
    };

    // Initialisation automatique
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            BlockTraiteurPublic.init();
        });
    } else {
        BlockTraiteurPublic.init();
    }

})();