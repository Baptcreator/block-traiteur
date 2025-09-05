/**
 * Gestionnaire principal du formulaire Block Traiteur
 */

(function($) {
    'use strict';

    window.BlockQuoteForm = function(formId) {
        this.formId = formId;
        this.form = document.getElementById(formId);
        this.currentStep = 0;
        this.totalSteps = 6;
        this.formData = {};
        
        if (!this.form) {
            console.error('Formulaire Block Traiteur non trouvé:', formId);
            return;
        }
        
        this.init();
    };

    BlockQuoteForm.prototype = {
        init: function() {
            console.log('Initialisation du formulaire:', this.formId);
            this.bindEvents();
            this.showStep(0);
            this.updateProgress();
            this.initializeFormData();
        },

        bindEvents: function() {
            var self = this;
            
            // Navigation
            $(this.form).on('click', '.next-step', function(e) {
                e.preventDefault();
                self.nextStep();
            });
            
            $(this.form).on('click', '.prev-step', function(e) {
                e.preventDefault();
                self.prevStep();
            });
            
            // Sélection de service
            $(this.form).on('click', '.select-service', function(e) {
                e.preventDefault();
                var service = $(this).data('service');
                self.selectService(service);
            });
            
            // Soumission
            $(this.form).on('submit', function(e) {
                e.preventDefault();
                self.submitForm();
            });
        },

        showStep: function(stepNumber) {
            console.log('Affichage étape:', stepNumber);
            
            // Masquer toutes les étapes
            $(this.form).find('.form-step').removeClass('active').hide();
            
            // Afficher l'étape courante
            var $currentStep = $(this.form).find('[data-step="' + stepNumber + '"]');
            $currentStep.addClass('active').show();
            
            this.currentStep = stepNumber;
            this.updateNavigationButtons();
            this.updateProgress();
        },

        nextStep: function() {
            if (this.currentStep < this.totalSteps) {
                this.showStep(this.currentStep + 1);
            }
        },

        prevStep: function() {
            if (this.currentStep > 0) {
                this.showStep(this.currentStep - 1);
            }
        },

        selectService: function(serviceType) {
            console.log('Service sélectionné:', serviceType);
            this.formData.serviceType = serviceType;
            
            // Mettre à jour les cartes de service
            $(this.form).find('.service-card').removeClass('selected');
            $(this.form).find('[data-service="' + serviceType + '"]').addClass('selected');
            
            // Passer automatiquement à l'étape suivante
            setTimeout(() => {
                this.nextStep();
            }, 800);
        },

        updateNavigationButtons: function() {
            var $prevBtn = $(this.form).find('.prev-step');
            var $nextBtn = $(this.form).find('.next-step');
            var $submitBtn = $(this.form).find('.submit-form');
            
            // Bouton précédent
            if (this.currentStep === 0) {
                $prevBtn.hide();
            } else {
                $prevBtn.show();
            }
            
            // Bouton suivant / soumettre
            if (this.currentStep === this.totalSteps) {
                $nextBtn.hide();
                $submitBtn.show();
            } else {
                $nextBtn.show();
                $submitBtn.hide();
            }
        },

        updateProgress: function() {
            var progress = (this.currentStep / this.totalSteps) * 100;
            $(this.form).find('.progress-fill').css('width', progress + '%');
            
            // Mettre à jour les étapes
            $(this.form).find('.progress-steps .step').each(function(index) {
                var $step = $(this);
                $step.removeClass('active completed');
                
                if (index < this.currentStep) {
                    $step.addClass('completed');
                } else if (index === this.currentStep) {
                    $step.addClass('active');
                }
            }.bind(this));
        },

        initializeFormData: function() {
            this.formData = {
                serviceType: '',
                eventDate: '',
                guestCount: 20,
                duration: 2,
                selectedProducts: [],
                selectedBeverages: [],
                selectedOptions: []
            };
        },

        submitForm: function() {
            console.log('Soumission du formulaire');
            // Logique de soumission à implémenter
        }
    };

    // Auto-initialisation
    $(document).ready(function() {
        $('.block-quote-form').each(function() {
            var formId = $(this).attr('id');
            if (formId) {
                new BlockQuoteForm(formId);
            }
        });
    });

})(jQuery);