/**
 * Block Traiteur - JavaScript Frontend Principal
 * Version: 1.0.0
 */

(function($) {
    'use strict';

    // Objet principal
    window.BlockTraiteur = {
        
        // Configuration
        config: {
            ajaxUrl: blockTraiteurPublic.ajaxUrl || '/wp-admin/admin-ajax.php',
            nonce: blockTraiteurPublic.nonce || '',
            strings: blockTraiteurPublic.strings || {}
        },
        
        // Cache des éléments DOM
        cache: {},
        
        // État du formulaire
        state: {
            currentStep: 1,
            serviceType: null,
            formData: {},
            priceBreakdown: {}
        },

        /**
         * Initialisation
         */
        init: function() {
            this.cacheElements();
            this.bindEvents();
            this.initFormSteps();
            this.initCalculator();
            console.log('Block Traiteur: Frontend initialisé');
        },

        /**
         * Cache des éléments DOM
         */
        cacheElements: function() {
            this.cache = {
                $document: $(document),
                $window: $(window),
                $body: $('body'),
                $form: $('.block-traiteur-form'),
                $steps: $('.form-step'),
                $nextBtn: $('.btn-next-step'),
                $prevBtn: $('.btn-prev-step'),
                $submitBtn: $('.btn-submit-quote'),
                $progressBar: $('.progress-bar'),
                $priceDisplay: $('.price-display'),
                $serviceSelector: $('.service-selector')
            };
        },

        /**
         * Liaison des événements
         */
        bindEvents: function() {
            var self = this;

            // Navigation entre étapes
            this.cache.$nextBtn.on('click', function(e) {
                e.preventDefault();
                self.nextStep();
            });

            this.cache.$prevBtn.on('click', function(e) {
                e.preventDefault();
                self.prevStep();
            });

            // Sélection du service
            this.cache.$serviceSelector.on('change', function() {
                self.selectService($(this).val());
            });

            // Calcul automatique du prix
            this.cache.$form.on('change', 'input, select', function() {
                self.calculatePrice();
            });

            // Soumission du formulaire
            this.cache.$form.on('submit', function(e) {
                e.preventDefault();
                self.submitForm();
            });

            // Validation en temps réel
            this.cache.$form.on('blur', 'input[required]', function() {
                self.validateField($(this));
            });
        },

        /**
         * Initialisation des étapes du formulaire
         */
        initFormSteps: function() {
            this.updateProgressBar();
            this.showStep(1);
        },

        /**
         * Initialisation du calculateur
         */
        initCalculator: function() {
            this.calculatePrice();
        },

        /**
         * Sélection du service
         */
        selectService: function(serviceType) {
            this.state.serviceType = serviceType;
            
            // Afficher/masquer les options spécifiques
            $('.service-specific').hide();
            $('.service-' + serviceType).show();
            
            // Recalculer le prix
            this.calculatePrice();
            
            console.log('Service sélectionné:', serviceType);
        },

        /**
         * Navigation - Étape suivante
         */
        nextStep: function() {
            var currentStep = this.state.currentStep;
            
            // Valider l'étape actuelle
            if (!this.validateStep(currentStep)) {
                return false;
            }
            
            // Sauvegarder les données de l'étape
            this.saveStepData(currentStep);
            
            // Passer à l'étape suivante
            if (currentStep < this.cache.$steps.length) {
                this.showStep(currentStep + 1);
            }
        },

        /**
         * Navigation - Étape précédente
         */
        prevStep: function() {
            var currentStep = this.state.currentStep;
            
            if (currentStep > 1) {
                this.showStep(currentStep - 1);
            }
        },

        /**
         * Afficher une étape
         */
        showStep: function(stepNumber) {
            this.state.currentStep = stepNumber;
            
            // Masquer toutes les étapes
            this.cache.$steps.removeClass('active').hide();
            
            // Afficher l'étape courante
            $('.form-step[data-step="' + stepNumber + '"]').addClass('active').show();
            
            // Mettre à jour les boutons
            this.updateNavigationButtons();
            
            // Mettre à jour la barre de progression
            this.updateProgressBar();
            
            console.log('Étape affichée:', stepNumber);
        },

        /**
         * Mettre à jour les boutons de navigation
         */
        updateNavigationButtons: function() {
            var currentStep = this.state.currentStep;
            var totalSteps = this.cache.$steps.length;
            
            // Bouton précédent
            if (currentStep <= 1) {
                this.cache.$prevBtn.hide();
            } else {
                this.cache.$prevBtn.show();
            }
            
            // Bouton suivant / soumettre
            if (currentStep >= totalSteps) {
                this.cache.$nextBtn.hide();
                this.cache.$submitBtn.show();
            } else {
                this.cache.$nextBtn.show();
                this.cache.$submitBtn.hide();
            }
        },

        /**
         * Mettre à jour la barre de progression
         */
        updateProgressBar: function() {
            var currentStep = this.state.currentStep;
            var totalSteps = this.cache.$steps.length;
            var progress = (currentStep / totalSteps) * 100;
            
            this.cache.$progressBar.css('width', progress + '%');
            
            // Mettre à jour les indicateurs d'étapes
            $('.step-indicator').removeClass('active completed');
            $('.step-indicator').each(function(index) {
                var stepNum = index + 1;
                if (stepNum < currentStep) {
                    $(this).addClass('completed');
                } else if (stepNum === currentStep) {
                    $(this).addClass('active');
                }
            });
        },

        /**
         * Valider une étape
         */
        validateStep: function(stepNumber) {
            var $step = $('.form-step[data-step="' + stepNumber + '"]');
            var isValid = true;
            
            // Valider tous les champs requis de l'étape
            $step.find('input[required], select[required]').each(function() {
                if (!this.validateField($(this))) {
                    isValid = false;
                }
            }.bind(this));
            
            // Validation spécifique selon l'étape
            switch(stepNumber) {
                case 1:
                    if (!this.state.serviceType) {
                        this.showError('Veuillez sélectionner un service');
                        isValid = false;
                    }
                    break;
                case 2:
                    // Validation de la date et du nombre de personnes
                    var date = $step.find('[name="event_date"]').val();
                    var guests = $step.find('[name="guest_count"]').val();
                    
                    if (!date || !guests) {
                        this.showError('Veuillez remplir tous les champs obligatoires');
                        isValid = false;
                    }
                    break;
            }
            
            return isValid;
        },

        /**
         * Valider un champ
         */
        validateField: function($field) {
            var value = $field.val();
            var isValid = true;
            var errorMessage = '';
            
            // Supprimer les erreurs précédentes
            $field.removeClass('error');
            $field.next('.field-error').remove();
            
            // Validation selon le type de champ
            if ($field.prop('required') && !value) {
                errorMessage = 'Ce champ est obligatoire';
                isValid = false;
            } else if ($field.attr('type') === 'email' && value) {
                var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(value)) {
                    errorMessage = 'Adresse email invalide';
                    isValid = false;
                }
            } else if ($field.attr('type') === 'tel' && value) {
                var phoneRegex = /^[\d\s\-\+\(\)\.]{10,}$/;
                if (!phoneRegex.test(value)) {
                    errorMessage = 'Numéro de téléphone invalide';
                    isValid = false;
                }
            }
            
            // Afficher l'erreur si nécessaire
            if (!isValid) {
                $field.addClass('error');
                $field.after('<div class="field-error">' + errorMessage + '</div>');
            }
            
            return isValid;
        },

        /**
         * Sauvegarder les données d'une étape
         */
        saveStepData: function(stepNumber) {
            var $step = $('.form-step[data-step="' + stepNumber + '"]');
            
            $step.find('input, select, textarea').each(function() {
                var $field = $(this);
                var name = $field.attr('name');
                var value = $field.val();
                
                if (name && value) {
                    this.state.formData[name] = value;
                }
            }.bind(this));
            
            console.log('Données sauvegardées pour l\'étape ' + stepNumber, this.state.formData);
        },

        /**
         * Calculer le prix
         */
        calculatePrice: function() {
            var self = this;
            
            // Collecter toutes les données du formulaire
            var formData = this.collectFormData();
            
            // Ajouter le service sélectionné
            formData.service_type = this.state.serviceType;
            
            // Requête AJAX pour le calcul
            $.ajax({
                url: this.config.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'block_traiteur_calculate_price',
                    nonce: this.config.nonce,
                    data: formData
                },
                success: function(response) {
                    if (response.success) {
                        self.updatePriceDisplay(response.data);
                    } else {
                        console.error('Erreur calcul prix:', response.data);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Erreur AJAX calcul prix:', error);
                }
            });
        },

        /**
         * Mettre à jour l'affichage du prix
         */
        updatePriceDisplay: function(priceData) {
            this.state.priceBreakdown = priceData;
            
            // Prix total
            var totalPrice = priceData.total || 0;
            this.cache.$priceDisplay.find('.total-price').text(totalPrice + ' €');
            
            // Détail du prix
            var $breakdown = this.cache.$priceDisplay.find('.price-breakdown');
            $breakdown.empty();
            
            if (priceData.breakdown) {
                $.each(priceData.breakdown, function(key, value) {
                    $breakdown.append(
                        '<div class="price-item">' +
                        '<span class="price-label">' + key + '</span>' +
                        '<span class="price-value">' + value + ' €</span>' +
                        '</div>'
                    );
                });
            }
            
            console.log('Prix mis à jour:', priceData);
        },

        /**
         * Collecter toutes les données du formulaire
         */
        collectFormData: function() {
            var formData = {};
            
            this.cache.$form.find('input, select, textarea').each(function() {
                var $field = $(this);
                var name = $field.attr('name');
                var value = $field.val();
                
                if (name) {
                    if ($field.attr('type') === 'checkbox') {
                        value = $field.is(':checked');
                    } else if ($field.attr('type') === 'radio') {
                        if (!$field.is(':checked')) {
                            return;
                        }
                    }
                    
                    formData[name] = value;
                }
            });
            
            return formData;
        },

        /**
         * Soumettre le formulaire
         */
        submitForm: function() {
            var self = this;
            
            // Valider toutes les étapes
            var isValid = true;
            for (var i = 1; i <= this.cache.$steps.length; i++) {
                if (!this.validateStep(i)) {
                    isValid = false;
                    break;
                }
            }
            
            if (!isValid) {
                this.showError('Veuillez corriger les erreurs dans le formulaire');
                return;
            }
            
            // Collecter toutes les données
            var formData = this.collectFormData();
            formData.service_type = this.state.serviceType;
            formData.price_breakdown = this.state.priceBreakdown;
            
            // Afficher le loading
            this.showLoading();
            
            // Soumettre via AJAX
            $.ajax({
                url: this.config.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'block_traiteur_submit_quote',
                    nonce: this.config.nonce,
                    data: formData
                },
                success: function(response) {
                    self.hideLoading();
                    
                    if (response.success) {
                        self.showSuccess('Votre demande de devis a été envoyée avec succès !');
                        self.resetForm();
                    } else {
                        self.showError(response.data.message || 'Une erreur est survenue');
                    }
                },
                error: function(xhr, status, error) {
                    self.hideLoading();
                    self.showError('Erreur de connexion. Veuillez réessayer.');
                    console.error('Erreur AJAX soumission:', error);
                }
            });
        },

        /**
         * Réinitialiser le formulaire
         */
        resetForm: function() {
            this.cache.$form[0].reset();
            this.state.currentStep = 1;
            this.state.serviceType = null;
            this.state.formData = {};
            this.state.priceBreakdown = {};
            this.showStep(1);
            this.calculatePrice();
        },

        /**
         * Afficher un message de succès
         */
        showSuccess: function(message) {
            this.showNotification(message, 'success');
        },

        /**
         * Afficher un message d'erreur
         */
        showError: function(message) {
            this.showNotification(message, 'error');
        },

        /**
         * Afficher une notification
         */
        showNotification: function(message, type) {
            var $notification = $('<div class="block-notification ' + type + '">' + message + '</div>');
            
            $('body').append($notification);
            
            setTimeout(function() {
                $notification.addClass('show');
            }, 100);
            
            setTimeout(function() {
                $notification.removeClass('show');
                setTimeout(function() {
                    $notification.remove();
                }, 300);
            }, 5000);
        },

        /**
         * Afficher le loading
         */
        showLoading: function() {
            this.cache.$submitBtn.prop('disabled', true);
            this.cache.$submitBtn.html('<span class="spinner"></span> Envoi en cours...');
        },

        /**
         * Masquer le loading
         */
        hideLoading: function() {
            this.cache.$submitBtn.prop('disabled', false);
            this.cache.$submitBtn.html('Envoyer ma demande de devis');
        }
    };

    /**
     * Initialisation au chargement du DOM
     */
    $(document).ready(function() {
        // Vérifier si on est sur une page avec le formulaire
        if ($('.block-traiteur-form').length > 0) {
            BlockTraiteur.init();
        }
    });

})(jQuery);
