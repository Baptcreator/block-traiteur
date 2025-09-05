/**
 * Gestionnaire principal du formulaire Block Traiteur
 */

console.log('form.js: Fichier chargé');

(function($) {
    'use strict';

    window.BlockQuoteForm = function(formId) {
        console.log('BlockQuoteForm: Construction avec ID', formId);
        this.formId = formId;
        this.form = document.getElementById(formId);
        this.currentStep = 0;
        this.totalSteps = 6;
        this.formData = {};
        
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
            var $priceValue = $form.find('.price-value');
            
            if ($priceValue.length === 0) {
                // Créer l'affichage du prix s'il n'existe pas
                var priceHtml = '<div class="price-display">';
                priceHtml += '  <div class="price-content">';
                priceHtml += '    <div class="price-label">Estimation</div>';
                priceHtml += '    <div class="price-value">À partir de 300€</div>';
                priceHtml += '    <div class="price-note">TTC - Prix indicatif</div>';
                priceHtml += '  </div>';
                priceHtml += '</div>';
                
                $form.find('.form-navigation').before(priceHtml);
            }
        },

        bindEvents: function() {
            var self = this;
            console.log('BlockQuoteForm: Binding des événements');
            
            // Navigation avec délégation d'événements
            $(document).on('click', '#' + this.formId + ' .next-step', function(e) {
                e.preventDefault();
                console.log('Bouton suivant cliqué');
                self.nextStep();
            });
            
            $(document).on('click', '#' + this.formId + ' .prev-step', function(e) {
                e.preventDefault();
                console.log('Bouton précédent cliqué');
                self.prevStep();
            });
            
            // Sélection de service
            $(document).on('click', '#' + this.formId + ' .select-service', function(e) {
                e.preventDefault();
                var service = $(this).data('service');
                console.log('Service sélectionné:', service);
                self.selectService(service);
            });
            
            // Onglets de recettes (DOG/CROQ)
            $(document).on('click', '#' + this.formId + ' .recipe-tabs .tab-btn', function(e) {
                e.preventDefault();
                var $btn = $(this);
                var category = $btn.data('category');
                
                // Mettre à jour les onglets
                $btn.siblings().removeClass('active');
                $btn.addClass('active');
                
                // Mettre à jour le contenu
                var $form = $('#' + self.formId);
                $form.find('.recipe-category').removeClass('active');
                $form.find('[data-category="' + category + '"]').addClass('active');
                
                // Charger les produits si nécessaire
                self.loadProducts(category, category + '-products');
            });
            
            // Onglets de boissons
            $(document).on('click', '#' + this.formId + ' .beverage-tabs .tab-btn', function(e) {
                e.preventDefault();
                var $btn = $(this);
                var category = $btn.data('category');
                
                // Mettre à jour les onglets
                $btn.siblings().removeClass('active');
                $btn.addClass('active');
                
                // Mettre à jour le contenu
                var $form = $('#' + self.formId);
                $form.find('.beverage-category').removeClass('active');
                $form.find('[data-category="' + category + '"]').addClass('active');
                
                // Charger les boissons
                self.loadBeverages(category, '', category + '-beverages');
            });
            
            // Onglets de vins
            $(document).on('click', '#' + this.formId + ' .wine-tabs .wine-tab', function(e) {
                e.preventDefault();
                var $btn = $(this);
                var wineType = $btn.data('wine-type');
                
                // Mettre à jour les onglets
                $btn.siblings().removeClass('active');
                $btn.addClass('active');
                
                // Mettre à jour le contenu
                var $form = $('#' + self.formId);
                $form.find('.wine-type').removeClass('active');
                $form.find('[data-wine-type="' + wineType + '"]').addClass('active');
                
                // Charger les vins
                self.loadBeverages('vins', wineType, 'vins-' + wineType + '-beverages');
            });
            
            // Onglets de bières
            $(document).on('click', '#' + this.formId + ' .beer-tabs .beer-tab', function(e) {
                e.preventDefault();
                var $btn = $(this);
                var beerType = $btn.data('beer-type');
                
                // Mettre à jour les onglets
                $btn.siblings().removeClass('active');
                $btn.addClass('active');
                
                // Mettre à jour le contenu
                var $form = $('#' + self.formId);
                $form.find('.beer-type').removeClass('active');
                $form.find('[data-beer-type="' + beerType + '"]').addClass('active');
                
                // Charger les bières
                self.loadBeverages('bieres', beerType, 'bieres-' + beerType + '-beverages');
            });
        },

        showStep: function(stepNumber) {
            console.log('Affichage étape:', stepNumber);
            
            var $form = $('#' + this.formId);
            
            // Masquer toutes les étapes
            $form.find('.form-step').removeClass('active').hide();
            
            // Afficher l'étape courante
            var $currentStep = $form.find('[data-step="' + stepNumber + '"]');
            if ($currentStep.length) {
                $currentStep.addClass('active').show();
                console.log('Étape', stepNumber, 'affichée avec succès');
            } else {
                console.error('Étape', stepNumber, 'non trouvée dans le DOM');
            }
            
            this.currentStep = stepNumber;
            this.updateNavigationButtons();
            this.updateProgress();
            
            // Charger les données spécifiques à l'étape
            this.loadStepData(stepNumber);
        },

        // Charger les données spécifiques à chaque étape
        loadStepData: function(stepNumber) {
            var self = this;
            
            switch(stepNumber) {
                case 2: // Étape formules repas
                    // Charger les produits Mini Boss et Accompagnements immédiatement
                    this.loadProducts('mini_boss', 'mini-boss-products');
                    this.loadProducts('accompagnement', 'accompaniment-products');
                    // DOG et CROQ seront chargés selon la sélection radio
                    break;
                    
                case 4: // Étape boissons
                    // Charger les softs par défaut (onglet actif)
                    this.loadBeverages('softs', '', 'softs-beverages');
                    break;
            }
        },

        nextStep: function() {
            console.log('nextStep: étape actuelle', this.currentStep, 'total', this.totalSteps);
            if (this.currentStep < this.totalSteps) {
                this.showStep(this.currentStep + 1);
            } else {
                console.log('Dernière étape atteinte');
            }
        },

        prevStep: function() {
            console.log('prevStep: étape actuelle', this.currentStep);
            if (this.currentStep > 0) {
                this.showStep(this.currentStep - 1);
            } else {
                console.log('Première étape atteinte');
            }
        },

        selectService: function(serviceType) {
            console.log('Service sélectionné:', serviceType);
            this.formData.serviceType = serviceType;
            
            var $form = $('#' + this.formId);
            
            // Mettre à jour les cartes de service
            $form.find('.service-card').removeClass('selected');
            $form.find('[data-service="' + serviceType + '"]').addClass('selected');
            
            // Calculer le prix initial
            this.calculatePrice();
            
            // Passer automatiquement à l'étape suivante
            var self = this;
            setTimeout(function() {
                self.nextStep();
            }, 800);
        },

        updateNavigationButtons: function() {
            var $form = $('#' + this.formId);
            var $prevBtn = $form.find('.prev-step');
            var $nextBtn = $form.find('.next-step');
            var $submitBtn = $form.find('.submit-form');
            
            console.log('Mise à jour boutons pour étape', this.currentStep);
            
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
            var progress = ((this.currentStep + 1) / (this.totalSteps + 1)) * 100;
            var $form = $('#' + this.formId);
            
            $form.find('.progress-fill').css('width', progress + '%');
            
            // Mettre à jour les étapes
            $form.find('.progress-steps .step').each(function(index) {
                var $step = $(this);
                $step.removeClass('active completed');
                
                if (index < this.currentStep) {
                    $step.addClass('completed');
                } else if (index === this.currentStep) {
                    $step.addClass('active');
                }
            }.bind(this));
        },

        // Charger les produits par catégorie
        loadProducts: function(category, containerId) {
            var self = this;
            console.log('Chargement des produits pour catégorie:', category);
            
            var serviceType = this.formData.serviceType || 'both';
            
            $.ajax({
                url: blockTraiteurAjax.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'block_traiteur_get_products',
                    nonce: blockTraiteurAjax.nonce,
                    category: category,
                    service_type: serviceType
                },
                beforeSend: function() {
                    $('#' + containerId).html('<div class="loading-products">Chargement des produits...</div>');
                },
                success: function(response) {
                    if (response.success && response.data.products) {
                        self.renderProducts(response.data.products, containerId);
                        console.log('Produits chargés:', response.data.count, 'produits pour', category);
                    } else {
                        $('#' + containerId).html('<div class="no-products">Aucun produit disponible pour cette catégorie.</div>');
                        console.error('Erreur chargement produits:', response.data);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Erreur AJAX produits:', error);
                    $('#' + containerId).html('<div class="error-products">Erreur lors du chargement des produits.</div>');
                }
            });
        },

        // Rendre les produits dans le DOM
        renderProducts: function(products, containerId) {
            var html = '';
            
            products.forEach(function(product) {
                html += '<div class="product-card" data-product-id="' + product.id + '">';
                html += '  <div class="product-content">';
                html += '    <div class="product-name">' + product.name + '</div>';
                if (product.description) {
                    html += '    <div class="product-description">' + product.description + '</div>';
                }
                if (product.allergens) {
                    html += '    <div class="product-allergens">Allergènes: ' + product.allergens + '</div>';
                }
                html += '    <div class="product-price">' + product.price + '€/' + product.unit + '</div>';
                html += '  </div>';
                html += '  <div class="product-selector">';
                html += '    <div class="quantity-selector">';
                html += '      <button type="button" class="qty-btn decrease" data-target="product-' + product.id + '">-</button>';
                html += '      <input type="number" id="product-' + product.id + '" class="qty-input" min="0" max="' + (product.max_quantity || 100) + '" value="0" data-price="' + product.price + '" data-product-id="' + product.id + '" data-name="' + product.name + '">';
                html += '      <button type="button" class="qty-btn increase" data-target="product-' + product.id + '">+</button>';
                html += '    </div>';
                html += '  </div>';
                html += '</div>';
            });
            
            $('#' + containerId).html(html);
            this.bindProductEvents();
        },

        // Charger les boissons par catégorie
        loadBeverages: function(category, subcategory, containerId) {
            var self = this;
            console.log('Chargement des boissons pour catégorie:', category, 'sous-catégorie:', subcategory);
            
            var serviceType = this.formData.serviceType || 'both';
            
            $.ajax({
                url: blockTraiteurAjax.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'block_traiteur_get_beverages',
                    nonce: blockTraiteurAjax.nonce,
                    category: category,
                    subcategory: subcategory,
                    service_type: serviceType
                },
                beforeSend: function() {
                    $('#' + containerId).html('<div class="loading-beverages">Chargement des boissons...</div>');
                },
                success: function(response) {
                    if (response.success && response.data.beverages) {
                        self.renderBeverages(response.data.beverages, containerId);
                        console.log('Boissons chargées:', response.data.count, 'boissons pour', category);
                    } else {
                        $('#' + containerId).html('<div class="no-beverages">Aucune boisson disponible pour cette catégorie.</div>');
                        console.error('Erreur chargement boissons:', response.data);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Erreur AJAX boissons:', error);
                    $('#' + containerId).html('<div class="error-beverages">Erreur lors du chargement des boissons.</div>');
                }
            });
        },

        // Rendre les boissons dans le DOM
        renderBeverages: function(beverages, containerId) {
            var html = '';
            
            beverages.forEach(function(beverage) {
                html += '<div class="product-card beverage-card" data-beverage-id="' + beverage.id + '">';
                html += '  <div class="product-content">';
                html += '    <div class="product-name">' + beverage.name + '</div>';
                if (beverage.description) {
                    html += '    <div class="product-description">' + beverage.description + '</div>';
                }
                if (beverage.origin) {
                    html += '    <div class="product-origin">Origine: ' + beverage.origin + '</div>';
                }
                html += '    <div class="product-details">' + beverage.volume;
                if (beverage.alcohol_degree > 0) {
                    html += ' - ' + beverage.alcohol_degree + '°';
                }
                html += '</div>';
                html += '    <div class="product-price">' + beverage.price + '€</div>';
                html += '  </div>';
                html += '  <div class="product-selector">';
                html += '    <div class="quantity-selector">';
                html += '      <button type="button" class="qty-btn decrease" data-target="beverage-' + beverage.id + '">-</button>';
                html += '      <input type="number" id="beverage-' + beverage.id + '" class="qty-input" min="0" max="50" value="0" data-price="' + beverage.price + '" data-beverage-id="' + beverage.id + '" data-name="' + beverage.name + '">';
                html += '      <button type="button" class="qty-btn increase" data-target="beverage-' + beverage.id + '">+</button>';
                html += '    </div>';
                html += '  </div>';
                html += '</div>';
            });
            
            $('#' + containerId).html(html);
            this.bindBeverageEvents();
        },

        // Lier les événements pour les produits
        bindProductEvents: function() {
            var self = this;
            
            // Boutons quantité
            $(document).off('click', '.qty-btn').on('click', '.qty-btn', function(e) {
                e.preventDefault();
                var $btn = $(this);
                var target = $btn.data('target');
                var $input = $('#' + target);
                var currentVal = parseInt($input.val()) || 0;
                var isIncrease = $btn.hasClass('increase');
                var min = parseInt($input.attr('min')) || 0;
                var max = parseInt($input.attr('max')) || 100;
                
                if (isIncrease && currentVal < max) {
                    $input.val(currentVal + 1).trigger('change');
                } else if (!isIncrease && currentVal > min) {
                    $input.val(currentVal - 1).trigger('change');
                }
            });
            
            // Changement de quantité
            $(document).off('change', '.qty-input').on('change', '.qty-input', function() {
                self.updateSelectionSummary();
                self.calculatePrice();
            });
        },

        // Lier les événements pour les boissons
        bindBeverageEvents: function() {
            this.bindProductEvents(); // Même logique que les produits
        },

        // Mettre à jour le récapitulatif de sélection
        updateSelectionSummary: function() {
            var selectedItems = [];
            var $form = $('#' + this.formId);
            
            // Récupérer les produits sélectionnés
            $form.find('.qty-input').each(function() {
                var $input = $(this);
                var quantity = parseInt($input.val()) || 0;
                
                if (quantity > 0) {
                    var name = $input.data('name');
                    var price = parseFloat($input.data('price'));
                    var total = quantity * price;
                    
                    selectedItems.push({
                        name: name,
                        quantity: quantity,
                        unitPrice: price,
                        total: total
                    });
                }
            });
            
            // Mettre à jour l'affichage
            var summaryHtml = '';
            if (selectedItems.length === 0) {
                summaryHtml = '<p class="no-selection">Aucun produit sélectionné</p>';
            } else {
                summaryHtml = '<div class="selected-items-list">';
                selectedItems.forEach(function(item) {
                    summaryHtml += '<div class="selected-item">';
                    summaryHtml += '  <span class="item-name">' + item.name + '</span>';
                    summaryHtml += '  <span class="item-quantity">x' + item.quantity + '</span>';
                    summaryHtml += '  <span class="item-price">' + item.total.toFixed(2) + '€</span>';
                    summaryHtml += '</div>';
                });
                summaryHtml += '</div>';
            }
            
            $form.find('#meal-summary, .selected-items').html(summaryHtml);
        },

        // Calculer le prix total
        calculatePrice: function() {
            console.log('🔢 Début calcul prix');
            
            if (!window.PriceCalculator) {
                console.error('❌ PriceCalculator non disponible');
                return;
            }
            
            var calculator = new PriceCalculator();
            var formData = this.collectFormData();
            
            console.log('📊 Données pour calcul:', formData);
            
            calculator.updateFormData(formData);
            var total = calculator.getFormattedTotal();
            
            console.log('💰 Prix calculé:', total);
            
            // Mettre à jour l'affichage du prix
            var $form = $('#' + this.formId);
            var $priceValue = $form.find('.price-value');
            
            if ($priceValue.length === 0) {
                console.log('⚠️ Élément .price-value non trouvé, création...');
                this.initPriceDisplay();
                $priceValue = $form.find('.price-value');
            }
            
            if ($priceValue.length > 0) {
                $priceValue.text(total);
                console.log('✅ Prix affiché:', total);
            } else {
                console.error('❌ Impossible de trouver .price-value');
            }
            
            console.log('Prix calculé:', total);
        },

        // Collecter toutes les données du formulaire
        collectFormData: function() {
            var $form = $('#' + this.formId);
            var data = Object.assign({}, this.formData);
            
            console.log('📋 Collecte des données formulaire');
            console.log('🔧 FormData de base:', this.formData);
            
            // S'assurer que le service type est défini
            if (!data.serviceType) {
                var selectedService = $form.find('.service-card.selected').data('service');
                if (selectedService) {
                    data.serviceType = selectedService;
                }
            }
            
            // Récupérer les données de base
            data.guestCount = parseInt($form.find('#guestCount').val()) || 
                            parseInt($form.find('input[name="guestCount"]').val()) || 20;
            data.duration = parseInt($form.find('#duration').val()) || 
                          parseInt($form.find('input[name="duration"]').val()) || 2;
            
            console.log('👥 Invités:', data.guestCount);
            console.log('⏰ Durée:', data.duration);
            console.log('🏢 Service:', data.serviceType);
            
            // Récupérer les produits sélectionnés
            data.selectedProducts = [];
            $form.find('.qty-input[data-product-id]').each(function() {
                var $input = $(this);
                var quantity = parseInt($input.val()) || 0;
                
                if (quantity > 0) {
                    data.selectedProducts.push({
                        id: $input.data('product-id'),
                        name: $input.data('name'),
                        price: parseFloat($input.data('price')),
                        quantity: quantity
                    });
                }
            });
            
            console.log('🍽️ Produits sélectionnés:', data.selectedProducts);
            
            // Récupérer les boissons sélectionnées
            data.selectedBeverages = [];
            $form.find('.qty-input[data-beverage-id]').each(function() {
                var $input = $(this);
                var quantity = parseInt($input.val()) || 0;
                
                if (quantity > 0) {
                    data.selectedBeverages.push({
                        id: $input.data('beverage-id'),
                        name: $input.data('name'),
                        price: parseFloat($input.data('price')),
                        quantity: quantity
                    });
                }
            });
            
            // Récupérer les autres données du formulaire
            data.guestCount = parseInt($form.find('#guestCount').val()) || 20;
            data.duration = parseInt($form.find('#duration').val()) || 2;
            data.miniBossCount = parseInt($form.find('#miniBossCount').val()) || 0;
            
            return data;
        },

        // Gérer les boutons de quantité et les interactions
        bindQuantityEvents: function() {
            var self = this;
            var $form = $('#' + this.formId);
            
            // Boutons +/- pour les quantités de produits
            $form.on('click', '.qty-btn', function(e) {
                e.preventDefault();
                
                var $btn = $(this);
                var target = $btn.data('target');
                var $input = $('#' + target);
                
                if ($input.length === 0) {
                    return;
                }
                
                var currentValue = parseInt($input.val()) || 0;
                var min = parseInt($input.attr('min')) || 0;
                var max = parseInt($input.attr('max')) || 999;
                
                if ($btn.hasClass('decrease')) {
                    if (currentValue > min) {
                        $input.val(currentValue - 1);
                    }
                } else if ($btn.hasClass('increase')) {
                    if (currentValue < max) {
                        $input.val(currentValue + 1);
                    }
                }
                
                $input.trigger('change');
                self.calculatePrice();
            });
            
            // Boutons +/- pour le nombre d'invités
            $form.on('click', '.number-btn', function(e) {
                e.preventDefault();
                
                var $btn = $(this);
                var target = $btn.data('target');
                var $input = $('#' + target);
                
                if ($input.length === 0) {
                    return;
                }
                
                var currentValue = parseInt($input.val()) || 0;
                var min = parseInt($input.attr('min')) || 1;
                var max = parseInt($input.attr('max')) || 100;
                
                if ($btn.hasClass('decrease')) {
                    if (currentValue > min) {
                        $input.val(currentValue - 1);
                    }
                } else if ($btn.hasClass('increase')) {
                    if (currentValue < max) {
                        $input.val(currentValue + 1);
                    }
                }
                
                $input.trigger('change');
                self.calculatePrice();
            });
            
            // Changement de durée - mettre à jour l'affichage
            $form.on('change', 'input[name="duration"]', function() {
                var duration = $(this).val();
                var $durationDisplay = $('#duration-display');
                
                if ($durationDisplay.length > 0) {
                    $durationDisplay.text(duration + 'H de privatisation (service inclus)');
                }
                
                self.calculatePrice();
            });
        }
    };

    // Auto-initialisation avec protection contre les doublons
    $(document).ready(function() {
        console.log('DOM prêt, initialisation BlockQuoteForm');
        
        $('.block-quote-form').each(function() {
            var $this = $(this);
            var formId = $this.attr('id');
            
            // Éviter les doublons
            if ($this.data('block-initialized')) {
                console.log('Formulaire déjà initialisé:', formId);
                return;
            }
            
            console.log('Initialisation du formulaire:', formId);
            $this.data('block-initialized', true);
            
            if (formId) {
                new BlockQuoteForm(formId);
            }
        });
    });

})(jQuery);