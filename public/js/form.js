/**
 * Gestionnaire principal du formulaire Block Traiteur - VERSION MISE À JOUR
 * Selon les spécifications client
 */

console.log('form-updated.js: Fichier chargé');

(function($) {
    'use strict';

    window.BlockQuoteForm = function(formId) {
        console.log('BlockQuoteForm: Construction avec ID', formId);
        this.formId = formId;
        this.form = document.getElementById(formId);
        this.currentStep = 0;
        this.totalSteps = 6; // Service, Forfait, Repas, Buffets, Boissons, Contact (+ Options pour remorque)
        this.formData = {
            serviceType: '',
            basePrice: 0,
            totalPrice: 0,
            guestCount: 0,
            duration: 2,
            recipes: {},
            buffets: {},
            beverages: {},
            options: {}
        };
        this.settings = {
            restaurant: {
                basePrice: 300,
                minGuests: 10,
                maxGuests: 30,
                minDuration: 2,
                maxDuration: 4,
                hourSupplement: 50
            },
            remorque: {
                basePrice: 350,
                minGuests: 20,
                maxGuests: 100,
                minDuration: 2,
                maxDuration: 5,
                hourSupplement: 50,
                guestSupplementThreshold: 50,
                guestSupplement: 150,
                zones: [
                    {min: 0, max: 30, price: 0, label: 'Zone 1 (0-30km)'},
                    {min: 30, max: 50, price: 20, label: 'Zone 2 (30-50km)'},
                    {min: 50, max: 100, price: 70, label: 'Zone 3 (50-100km)'},
                    {min: 100, max: 150, price: 118, label: 'Zone 4 (100-150km)'}
                ]
            }
        };
        
        if (!this.form) {
            console.error('Formulaire Block Traiteur non trouvé:', formId);
            return;
        }
        
        console.log('BlockQuoteForm: Formulaire trouvé, initialisation...');
        this.init();
    };

    BlockQuoteForm.prototype = {
        init: function() {
            console.log('BlockQuoteForm: init() démarré');
            this.bindEvents();
            this.showStep(0);
            this.updateProgress();
            this.initPriceDisplay();
            console.log('BlockQuoteForm: init() terminé');
        },

        // Initialiser l'affichage du prix
        initPriceDisplay: function() {
            var $form = $('#' + this.formId);
            var $priceDisplay = $form.find('.price-display');
            
            if ($priceDisplay.length === 0) {
                // Créer l'affichage du prix s'il n'existe pas
                var priceHtml = '<div class="price-display">';
                priceHtml += '  <div class="price-content">';
                priceHtml += '    <div class="price-label">Estimation</div>';
                priceHtml += '    <div class="price-value">À partir de 300€</div>';
                priceHtml += '    <div class="price-note">TTC - Montant indicatif estimatif</div>';
                priceHtml += '  </div>';
                priceHtml += '</div>';
                
                $form.append(priceHtml);
            }
        },

        // Lier les événements
        bindEvents: function() {
            var self = this;
            var $form = $('#' + this.formId);

            // Navigation
            $form.on('click', '.next-step', function(e) {
                e.preventDefault();
                if (self.validateCurrentStep()) {
                    self.nextStep();
                }
            });

            $form.on('click', '.prev-step', function(e) {
                e.preventDefault();
                self.prevStep();
            });

            // Choix de service
            $form.on('click', '.select-service', function(e) {
                e.preventDefault();
                var serviceType = $(this).data('service');
                self.selectService(serviceType);
            });

            // Gestion des inputs de quantité
            $form.on('click', '.qty-btn', function(e) {
                e.preventDefault();
                self.handleQuantityButton($(this));
            });

            // Gestion des inputs numériques
            $form.on('change', '.qty-input', function() {
                self.calculatePrice();
            });

            // Gestion du choix de plat signature
            $form.on('change', 'input[name="signature_type"]', function() {
                self.handleSignatureChoice($(this).val());
            });

            // Gestion du choix d'accompagnement
            $form.on('change', 'input[name="accompaniment_type"]', function() {
                self.handleAccompanimentChoice($(this).val());
            });

            // Gestion des buffets
            $form.on('change', 'input[name="buffetTypes[]"]', function() {
                self.handleBuffetTypeChange();
            });

            // Gestion du skip boissons
            $form.on('change', '#skip-beverages', function() {
                self.handleSkipBeverages($(this).is(':checked'));
            });

            // Gestion des options remorque
            $form.on('change', '#option-tireuse', function() {
                self.handleTireuseOption($(this).is(':checked'));
            });

            // Calcul automatique à chaque changement
            $form.on('change input', 'input, select, textarea', function() {
                setTimeout(function() {
                    self.calculatePrice();
                }, 100);
            });
        },

        // Sélectionner un service
        selectService: function(serviceType) {
            console.log('Service sélectionné:', serviceType);
            this.formData.serviceType = serviceType;
            
            // Mettre à jour l'interface
            $('#serviceType').val(serviceType);
            
            // Configurer les limites selon le service
            this.updateServiceLimits(serviceType);
            
            // Passer à l'étape suivante
            this.nextStep();
        },

        // Mettre à jour les limites selon le service
        updateServiceLimits: function(serviceType) {
            var settings = this.settings[serviceType];
            var $guestCount = $('#guestCount');
            var $form = $('#' + this.formId);
            
            if (settings) {
                // Mettre à jour les limites d'invités
                $guestCount.attr('min', settings.minGuests);
                $guestCount.attr('max', settings.maxGuests);
                $guestCount.val(settings.minGuests);
                
                // Mettre à jour les textes d'aide
                $('.guest-limits').text('min ' + settings.minGuests + 'p / max ' + settings.maxGuests + 'p');
                
                // Afficher/masquer les champs spécifiques
                if (serviceType === 'remorque') {
                    $('.remorque-fields').show();
                    $('.remorque-only').show();
                    $('.restaurant-only').hide();
                    this.totalSteps = 7; // Inclure l'étape options
                } else {
                    $('.remorque-fields').hide();
                    $('.remorque-only').hide();
                    $('.restaurant-only').show();
                    this.totalSteps = 6; // Pas d'étape options
                }
                
                // Mettre à jour la barre de progression
                this.updateProgressSteps();
                
                // Mettre à jour le prix de base
                this.formData.basePrice = settings.basePrice;
                this.calculatePrice();
            }
        },

        // Mettre à jour les étapes de progression
        updateProgressSteps: function() {
            var $steps = $('.progress-steps .step');
            if (this.formData.serviceType === 'restaurant') {
                $('.service-remorque-only').hide();
            } else {
                $('.service-remorque-only').show();
            }
        },

        // Gérer le choix de plat signature
        handleSignatureChoice: function(signatureType) {
            $('#recipes-section').show();
            if (signatureType === 'dog') {
                $('#dog-recipes').show();
                $('#croq-recipes').hide();
            } else {
                $('#croq-recipes').show();
                $('#dog-recipes').hide();
            }
            this.updateRecipeCounter();
        },

        // Gérer le choix d'accompagnement
        handleAccompanimentChoice: function(accompanimentType) {
            if (accompanimentType === 'frites') {
                $('#frites-options').show();
            } else {
                $('#frites-options').hide();
            }
        },

        // Gérer les changements de type de buffet
        handleBuffetTypeChange: function() {
            var saleChecked = $('input[value="sale"]').is(':checked') || $('input[value="mixte"]').is(':checked');
            var sucreChecked = $('input[value="sucre"]').is(':checked') || $('input[value="mixte"]').is(':checked');
            
            $('#buffet-sale').toggle(saleChecked);
            $('#buffet-sucre').toggle(sucreChecked);
            $('.buffet-sale-counter').toggle(saleChecked);
            $('.buffet-sucre-counter').toggle(sucreChecked);
            
            this.updateBuffetCounters();
        },

        // Gérer le skip des boissons
        handleSkipBeverages: function(skip) {
            if (skip) {
                $('#beverages-content').hide();
                $('#beverages-content .qty-input').val(0);
            } else {
                $('#beverages-content').show();
            }
            this.calculatePrice();
        },

        // Gérer l'option tireuse
        handleTireuseOption: function(selected) {
            if (selected) {
                $('#tireuse-futs').show();
            } else {
                $('#tireuse-futs').hide();
                $('input[name="tireuse_futs[]"]').prop('checked', false);
            }
            this.calculatePrice();
        },

        // Gérer les boutons de quantité
        handleQuantityButton: function($btn) {
            var target = $btn.data('target');
            var $input = $('#' + target);
            var isIncrease = $btn.hasClass('increase');
            var currentValue = parseInt($input.val()) || 0;
            
            if (isIncrease) {
                $input.val(currentValue + 1);
            } else if (currentValue > 0) {
                $input.val(currentValue - 1);
            }
            
            // Mettre à jour les compteurs selon le contexte
            if (target.includes('recipe') || target.includes('dog-') || target.includes('croq-') || target.includes('mini-')) {
                this.updateRecipeCounter();
            } else if (target.includes('buffet-')) {
                this.updateBuffetCounters();
            }
            
            this.calculatePrice();
        },

        // Mettre à jour le compteur de recettes
        updateRecipeCounter: function() {
            var guestCount = parseInt($('#guestCount').val()) || 0;
            var totalRecipes = 0;
            
            $('.recipes-group .qty-input, .mini-boss-items .qty-input').each(function() {
                totalRecipes += parseInt($(this).val()) || 0;
            });
            
            $('#total-recipes').text(totalRecipes);
            $('#required-recipes').text(guestCount);
            
            var $status = $('#recipe-status');
            if (totalRecipes >= guestCount && guestCount > 0) {
                $status.html('<span class="status-icon">✅</span><span class="status-text">Parfait !</span>');
                $status.removeClass('warning').addClass('success');
            } else {
                $status.html('<span class="status-icon">⚠️</span><span class="status-text">Il manque des recettes</span>');
                $status.removeClass('success').addClass('warning');
            }
        },

        // Mettre à jour les compteurs de buffets
        updateBuffetCounters: function() {
            var guestCount = parseInt($('#guestCount').val()) || 0;
            
            // Buffet salé
            var totalSale = 0;
            var recipesSale = 0;
            
            $('#buffet-sale .qty-input').each(function() {
                var value = parseInt($(this).val()) || 0;
                if (value > 0) {
                    totalSale += value;
                    recipesSale++;
                }
            });
            
            $('#total-buffet-sale').text(totalSale);
            $('#required-buffet-sale').text(guestCount);
            $('#recipes-buffet-sale').text(recipesSale);
            
            var $saleStatus = $('#buffet-sale-status');
            if (totalSale >= guestCount && recipesSale >= 2 && guestCount > 0) {
                $saleStatus.html('<span class="status-icon">✅</span><span class="status-text">Parfait !</span>');
                $saleStatus.removeClass('warning').addClass('success');
            } else {
                $saleStatus.html('<span class="status-icon">⚠️</span><span class="status-text">Sélection incomplète</span>');
                $saleStatus.removeClass('success').addClass('warning');
            }
            
            // Buffet sucré
            var totalSucre = 0;
            $('#buffet-sucre .qty-input').each(function() {
                totalSucre += parseInt($(this).val()) || 0;
            });
            
            $('#total-buffet-sucre').text(totalSucre);
            $('#required-buffet-sucre').text(guestCount);
            
            var $sucreStatus = $('#buffet-sucre-status');
            if (totalSucre >= guestCount && guestCount > 0) {
                $sucreStatus.html('<span class="status-icon">✅</span><span class="status-text">Parfait !</span>');
                $sucreStatus.removeClass('warning').addClass('success');
            } else {
                $sucreStatus.html('<span class="status-icon">⚠️</span><span class="status-text">Sélection incomplète</span>');
                $sucreStatus.removeClass('success').addClass('warning');
            }
        },

        // Calculer le prix total
        calculatePrice: function() {
            var total = this.formData.basePrice || 0;
            var serviceType = this.formData.serviceType;
            var guestCount = parseInt($('#guestCount').val()) || 0;
            var duration = parseInt($('input[name="duration"]:checked').val()) || 2;
            
            if (!serviceType) return;
            
            var settings = this.settings[serviceType];
            
            // Supplément durée
            if (duration > settings.minDuration) {
                var extraHours = duration - settings.minDuration;
                total += extraHours * settings.hourSupplement;
            }
            
            // Supplément invités (remorque seulement)
            if (serviceType === 'remorque' && guestCount > settings.guestSupplementThreshold) {
                total += settings.guestSupplement;
            }
            
            // Supplément zone (remorque seulement)
            if (serviceType === 'remorque') {
                // TODO: Calculer selon le code postal
                // Pour l'instant, on considère zone 1 (gratuit)
            }
            
            // Prix des recettes
            total += this.calculateRecipesPrice();
            
            // Prix des accompagnements
            total += this.calculateAccompanimentPrice();
            
            // Prix des buffets
            total += this.calculateBuffetPrice();
            
            // Prix des boissons
            if (!$('#skip-beverages').is(':checked')) {
                total += this.calculateBeveragePrice();
            }
            
            // Prix des options (remorque seulement)
            if (serviceType === 'remorque') {
                total += this.calculateOptionsPrice();
            }
            
            this.formData.totalPrice = total;
            this.updatePriceDisplay(total);
        },

        // Calculer le prix des recettes
        calculateRecipesPrice: function() {
            var total = 0;
            var recipePrices = {
                'dog-classic': 8,
                'dog-spicy': 8.5,
                'dog-veggie': 8,
                'dog-premium': 9,
                'croq-classic': 7.5,
                'croq-madame': 8,
                'croq-vege': 7.5,
                'croq-gourmet': 8.5,
                'mini-dog': 8,
                'mini-croq': 8,
                'mini-nuggets': 8
            };
            
            $('.qty-input').each(function() {
                var id = $(this).attr('id');
                var quantity = parseInt($(this).val()) || 0;
                if (recipePrices[id]) {
                    total += quantity * recipePrices[id];
                }
            });
            
            return total;
        },

        // Calculer le prix des accompagnements
        calculateAccompanimentPrice: function() {
            var total = 0;
            var basePrice = 4;
            
            var saladeQty = parseInt($('#acc-salade').val()) || 0;
            var fritesQty = parseInt($('#acc-frites').val()) || 0;
            
            total += (saladeQty + fritesQty) * basePrice;
            
            // Supplément chimichuri
            if ($('input[name="frites_chimichuri"]').is(':checked') && fritesQty > 0) {
                total += fritesQty * 1;
            }
            
            return total;
        },

        // Calculer le prix des buffets
        calculateBuffetPrice: function() {
            var total = 0;
            var buffetPrices = {
                'buffet-houmous': 3.5,
                'buffet-bruschetta': 2.5,
                'buffet-quiches': 3,
                'buffet-charcuterie': 25,
                'buffet-fromages': 28,
                'buffet-verrines': 4.5,
                'buffet-grilled': 5,
                'dessert-tarte': 18,
                'dessert-eclairs': 3.5,
                'dessert-macarons': 2.8
            };
            
            $('.qty-input').each(function() {
                var id = $(this).attr('id');
                var quantity = parseInt($(this).val()) || 0;
                if (buffetPrices[id]) {
                    total += quantity * buffetPrices[id];
                }
            });
            
            // Supplément jambon pour grilled cheese
            if ($('input[name="grilled_cheese_jambon"]').is(':checked')) {
                var grilledQty = parseInt($('#buffet-grilled').val()) || 0;
                total += grilledQty * 1;
            }
            
            return total;
        },

        // Calculer le prix des boissons
        calculateBeveragePrice: function() {
            var total = 0;
            var beveragePrices = {
                'coca-5l': 12, 'coca-20l': 45,
                'orangina-5l': 13, 'orangina-20l': 48,
                'pomme-5l': 15, 'pomme-10l': 28,
                'limonade-5l': 16, 'limonade-20l': 58,
                'eau-50cl': 1.5, 'eau-1l': 2.5,
                'riesling': 18, 'gewurz': 20, 'pinot-blanc': 16,
                'pinot-noir': 19, 'cotes-rhone': 17, 'beaujolais': 15,
                'rose-provence': 16, 'cremant': 22, 'cuvee-speciale': 25,
                'kro-1664': 3.5, 'stella': 3.8, 'hoegaarden': 4,
                'brooklyn-ipa': 5, 'pelforth': 4.2,
                'kro-fut-10l': 45, 'kro-fut-20l': 85,
                'stella-fut-10l': 48, 'stella-fut-20l': 90
            };
            
            $('.qty-input').each(function() {
                var id = $(this).attr('id');
                var quantity = parseInt($(this).val()) || 0;
                if (beveragePrices[id]) {
                    total += quantity * beveragePrices[id];
                }
            });
            
            return total;
        },

        // Calculer le prix des options
        calculateOptionsPrice: function() {
            var total = 0;
            
            // Option tireuse
            if ($('#option-tireuse').is(':checked')) {
                total += 50;
                
                // Prix des fûts sélectionnés
                var futPrices = {
                    'kro_10l': 45, 'kro_20l': 85,
                    'stella_10l': 48, 'stella_20l': 90,
                    'hoegaarden_10l': 52, 'hoegaarden_20l': 95,
                    'brooklyn_10l': 65, 'brooklyn_20l': 120,
                    'pelforth_10l': 55, 'pelforth_20l': 100
                };
                
                $('input[name="tireuse_futs[]"]:checked').each(function() {
                    var futType = $(this).val();
                    if (futPrices[futType]) {
                        total += futPrices[futType];
                    }
                });
            }
            
            // Option jeux
            if ($('#option-jeux').is(':checked')) {
                total += 70;
            }
            
            return total;
        },

        // Mettre à jour l'affichage du prix
        updatePriceDisplay: function(price) {
            var $priceValue = $('.price-value');
            $priceValue.text(price + '€');
            
            // Mettre à jour les textes de durée
            var duration = parseInt($('input[name="duration"]:checked').val()) || 2;
            $('#duration-display').text(duration + 'H de privatisation (service inclus)');
        },

        // Validation des étapes
        validateCurrentStep: function() {
            var step = this.currentStep;
            
            switch(step) {
                case 0: // Service
                    if (!this.formData.serviceType) {
                        this.showError('Veuillez choisir un type de service');
                        return false;
                    }
                    break;
                    
                case 1: // Forfait
                    var date = $('#eventDate').val();
                    var guests = parseInt($('#guestCount').val());
                    
                    if (!date) {
                        this.showError('Veuillez sélectionner une date');
                        return false;
                    }
                    
                    if (!guests || guests < this.settings[this.formData.serviceType].minGuests) {
                        this.showError('Nombre d\'invités insuffisant');
                        return false;
                    }
                    
                    if (this.formData.serviceType === 'remorque') {
                        var location = $('#eventLocation').val();
                        var postal = $('#postalCode').val();
                        
                        if (!location) {
                            this.showError('Veuillez indiquer le lieu de l\'événement');
                            return false;
                        }
                        
                        if (!postal || postal.length !== 5) {
                            this.showError('Veuillez indiquer un code postal valide');
                            return false;
                        }
                    }
                    break;
                    
                case 2: // Formules repas
                    var signatureType = $('input[name="signature_type"]:checked').val();
                    if (!signatureType) {
                        this.showError('Veuillez choisir un plat signature');
                        return false;
                    }
                    
                    var totalRecipes = parseInt($('#total-recipes').text()) || 0;
                    var requiredRecipes = parseInt($('#required-recipes').text()) || 0;
                    
                    if (totalRecipes < requiredRecipes) {
                        this.showError('Il manque des recettes (minimum 1 par personne)');
                        return false;
                    }
                    
                    var accompanimentType = $('input[name="accompaniment_type"]:checked').val();
                    if (!accompanimentType) {
                        this.showError('Veuillez choisir un accompagnement');
                        return false;
                    }
                    
                    var accTotal = (parseInt($('#acc-salade').val()) || 0) + (parseInt($('#acc-frites').val()) || 0);
                    if (accTotal < requiredRecipes) {
                        this.showError('Accompagnement insuffisant (minimum 1 par personne)');
                        return false;
                    }
                    break;
                    
                case 3: // Buffets
                    var buffetTypes = $('input[name="buffetTypes[]"]:checked');
                    if (buffetTypes.length === 0) {
                        this.showError('Veuillez choisir au moins un type de buffet');
                        return false;
                    }
                    
                    // Validation buffet salé
                    if ($('input[value="sale"]').is(':checked') || $('input[value="mixte"]').is(':checked')) {
                        var saleTotal = parseInt($('#total-buffet-sale').text()) || 0;
                        var saleRecipes = parseInt($('#recipes-buffet-sale').text()) || 0;
                        var required = parseInt($('#required-buffet-sale').text()) || 0;
                        
                        if (saleTotal < required || saleRecipes < 2) {
                            this.showError('Buffet salé incomplet (min 1/personne et 2 recettes)');
                            return false;
                        }
                    }
                    
                    // Validation buffet sucré
                    if ($('input[value="sucre"]').is(':checked') || $('input[value="mixte"]').is(':checked')) {
                        var sucreTotal = parseInt($('#total-buffet-sucre').text()) || 0;
                        var requiredSucre = parseInt($('#required-buffet-sucre').text()) || 0;
                        
                        if (sucreTotal < requiredSucre) {
                            this.showError('Buffet sucré incomplet (min 1/personne)');
                            return false;
                        }
                    }
                    break;
                    
                case 4: // Boissons (optionnel)
                    // Pas de validation obligatoire
                    break;
                    
                case 5: // Options (optionnel, remorque seulement)
                    // Pas de validation obligatoire
                    break;
                    
                case 6: // Contact
                    var name = $('#customerName').val();
                    var firstname = $('#customerFirstname').val();
                    var phone = $('#customerPhone').val();
                    var email = $('#customerEmail').val();
                    
                    if (!name || !firstname || !phone || !email) {
                        this.showError('Tous les champs de contact sont obligatoires');
                        return false;
                    }
                    
                    if (!this.validateEmail(email)) {
                        this.showError('Email invalide');
                        return false;
                    }
                    break;
            }
            
            return true;
        },

        // Validation email
        validateEmail: function(email) {
            var re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        },

        // Afficher une erreur
        showError: function(message) {
            var $errorDiv = $('.error-message');
            $errorDiv.text(message).show();
            setTimeout(function() {
                $errorDiv.fadeOut();
            }, 5000);
        },

        // Navigation
        nextStep: function() {
            if (this.currentStep < this.totalSteps - 1) {
                this.currentStep++;
                this.showStep(this.currentStep);
                this.updateProgress();
            }
        },

        prevStep: function() {
            if (this.currentStep > 0) {
                this.currentStep--;
                this.showStep(this.currentStep);
                this.updateProgress();
            }
        },

        showStep: function(step) {
            $('.form-step').removeClass('active').hide();
            $('.form-step[data-step="' + step + '"]').addClass('active').show();
            
            // Gestion de la navigation
            $('.prev-step').toggle(step > 0);
            $('.next-step').toggle(step < this.totalSteps - 1);
            $('.submit-form').toggle(step === this.totalSteps - 1);
            
            // Skip l'étape options si restaurant
            if (step === 5 && this.formData.serviceType === 'restaurant') {
                this.nextStep();
                return;
            }
        },

        updateProgress: function() {
            var progress = ((this.currentStep + 1) / this.totalSteps) * 100;
            $('.progress-fill').css('width', progress + '%');
            
            $('.progress-steps .step').removeClass('active completed');
            $('.progress-steps .step').each(function(index) {
                if (index < this.currentStep) {
                    $(this).addClass('completed');
                } else if (index === this.currentStep) {
                    $(this).addClass('active');
                }
            }.bind(this));
        }
    };

})(jQuery);

// Initialisation automatique
document.addEventListener('DOMContentLoaded', function() {
    // Trouver tous les formulaires Block Traiteur
    var forms = document.querySelectorAll('.block-quote-form');
    forms.forEach(function(form) {
        if (typeof BlockQuoteForm !== 'undefined') {
            new BlockQuoteForm(form.id);
        }
    });
});
