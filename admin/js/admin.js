/**
 * Scripts JavaScript pour l'interface d'administration Block Traiteur
 *
 * @package Block_Traiteur
 * @subpackage Admin
 * @since 1.0.0
 */

(function($) {
    'use strict';

    // Objet principal pour l'administration
    window.BlockTraiteurAdmin = {
        
        // Configuration
        config: {
            ajaxUrl: blockTraiteurAdmin.ajaxUrl,
            nonce: blockTraiteurAdmin.nonce,
            strings: blockTraiteurAdmin.strings,
            debug: false
        },

        // Cache des éléments DOM
        cache: {
            $window: $(window),
            $document: $(document),
            $body: $('body')
        },

        // Initialisation
        init: function() {
            this.bindEvents();
            this.initComponents();
            this.initTables();
            this.initForms();
            this.initModals();
            this.initTooltips();
            this.log('Block Traiteur Admin initialisé');
        },

        // Liaison des événements
        bindEvents: function() {
            var self = this;

            // Navigation par onglets
            self.cache.$document.on('click', '.block-admin-nav a', function(e) {
                e.preventDefault();
                self.switchTab($(this));
            });

            // Boutons d'action
            self.cache.$document.on('click', '[data-action]', function(e) {
                e.preventDefault();
                self.handleAction($(this));
            });

            // Confirmation de suppression
            self.cache.$document.on('click', '.delete-item', function(e) {
                if (!confirm(self.config.strings.confirmDelete)) {
                    e.preventDefault();
                    return false;
                }
            });

            // Sélection multiple dans les tableaux
            self.cache.$document.on('change', '.select-all', function() {
                var checked = $(this).is(':checked');
                $('.item-checkbox').prop('checked', checked);
                self.updateBulkActions();
            });

            self.cache.$document.on('change', '.item-checkbox', function() {
                self.updateBulkActions();
            });

            // Actions groupées
            self.cache.$document.on('click', '.bulk-action-btn', function(e) {
                e.preventDefault();
                self.handleBulkAction($(this));
            });

            // Recherche en temps réel
            self.cache.$document.on('input', '.live-search', function() {
                self.handleLiveSearch($(this));
            });

            // Upload d'images
            self.cache.$document.on('click', '.upload-image-btn', function(e) {
                e.preventDefault();
                self.openMediaUploader($(this));
            });

            // Suppression d'images
            self.cache.$document.on('click', '.remove-image-btn', function(e) {
                e.preventDefault();
                self.removeImage($(this));
            });

            // Validation de formulaire en temps réel
            self.cache.$document.on('blur', '.validate-field', function() {
                self.validateField($(this));
            });

            // Calcul de prix en temps réel
            self.cache.$document.on('input change', '.price-calculator input, .price-calculator select', function() {
                self.calculatePrice();
            });

            // Sauvegarde automatique
            self.cache.$document.on('input change', '.auto-save', function() {
                clearTimeout(self.autoSaveTimeout);
                self.autoSaveTimeout = setTimeout(function() {
                    self.autoSave();
                }, 2000);
            });
        },

        // Initialisation des composants
        initComponents: function() {
            this.initDatePickers();
            this.initColorPickers();
            this.initSortables();
            this.initCharts();
            this.initCounters();
        },

        // Initialisation des date pickers
        initDatePickers: function() {
            if ($.fn.datepicker) {
                $('.date-picker').datepicker({
                    dateFormat: 'dd/mm/yy',
                    changeMonth: true,
                    changeYear: true,
                    showButtonPanel: true,
                    closeText: 'Fermer',
                    prevText: '&laquo; Préc',
                    nextText: 'Suiv &raquo;',
                    currentText: 'Aujourd\'hui',
                    monthNames: ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin',
                                'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'],
                    monthNamesShort: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin',
                                     'Juil', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'],
                    dayNames: ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'],
                    dayNamesShort: ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'],
                    dayNamesMin: ['Di', 'Lu', 'Ma', 'Me', 'Je', 'Ve', 'Sa'],
                    weekHeader: 'Sem',
                    firstDay: 1
                });
            }
        },

        // Initialisation des color pickers
        initColorPickers: function() {
            if ($.fn.wpColorPicker) {
                $('.color-picker').wpColorPicker({
                    change: function(event, ui) {
                        $(this).trigger('change');
                    }
                });
            }
        },

        // Initialisation du tri par glisser-déposer
        initSortables: function() {
            if ($.fn.sortable) {
                $('.sortable-list').sortable({
                    handle: '.sort-handle',
                    placeholder: 'sort-placeholder',
                    tolerance: 'pointer',
                    update: function(event, ui) {
                        BlockTraiteurAdmin.updateSortOrder($(this));
                    }
                });
            }
        },

        // Initialisation des graphiques
        initCharts: function() {
            var self = this;
            
            // Graphique des devis par statut
            var quotesChart = document.getElementById('quotes-status-chart');
            if (quotesChart && typeof Chart !== 'undefined') {
                var ctx = quotesChart.getContext('2d');
                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: ['En attente', 'Approuvés', 'Rejetés', 'Expirés'],
                        datasets: [{
                            data: quotesChart.dataset.values || [0, 0, 0, 0],
                            backgroundColor: ['#ffc107', '#28a745', '#dc3545', '#6c757d'],
                            borderWidth: 2,
                            borderColor: '#fff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        legend: {
                            position: 'bottom'
                        }
                    }
                });
            }

            // Graphique des revenus mensuels
            var revenueChart = document.getElementById('revenue-chart');
            if (revenueChart && typeof Chart !== 'undefined') {
                var ctx = revenueChart.getContext('2d');
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: revenueChart.dataset.labels || [],
                        datasets: [{
                            label: 'Revenus (€)',
                            data: revenueChart.dataset.values || [],
                            borderColor: '#243127',
                            backgroundColor: 'rgba(36, 49, 39, 0.1)',
                            borderWidth: 2,
                            fill: true,
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return value + ' €';
                                    }
                                }
                            }
                        }
                    }
                });
            }
        },

        // Initialisation des compteurs animés
        initCounters: function() {
            $('.counter').each(function() {
                var $this = $(this);
                var countTo = parseInt($this.data('count'), 10);
                var duration = parseInt($this.data('duration') || 2000, 10);
                
                $({ countNum: 0 }).animate({
                    countNum: countTo
                }, {
                    duration: duration,
                    easing: 'linear',
                    step: function() {
                        $this.text(Math.floor(this.countNum));
                    },
                    complete: function() {
                        $this.text(countTo);
                    }
                });
            });
        },

        // Initialisation des tableaux
        initTables: function() {
            // DataTables si disponible
            if ($.fn.DataTable) {
                $('.data-table').DataTable({
                    responsive: true,
                    pageLength: 25,
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/fr-FR.json'
                    },
                    order: [[0, 'desc']],
                    columnDefs: [
                        { orderable: false, targets: 'no-sort' }
                    ]
                });
            }

            // Filtres de tableau
            this.initTableFilters();
        },

        // Initialisation des filtres de tableau
        initTableFilters: function() {
            var self = this;

            $('.table-filter').on('change', function() {
                var $table = $($(this).data('target'));
                var filterBy = $(this).data('filter');
                var filterValue = $(this).val();

                $table.find('tbody tr').each(function() {
                    var $row = $(this);
                    var cellValue = $row.find('[data-' + filterBy + ']').data(filterBy);
                    
                    if (filterValue === '' || cellValue === filterValue) {
                        $row.show();
                    } else {
                        $row.hide();
                    }
                });
            });
        },

        // Initialisation des formulaires
        initForms: function() {
            this.initValidation();
            this.initAjaxForms();
            this.initDependentFields();
        },

        // Initialisation de la validation
        initValidation: function() {
            if ($.fn.validate) {
                $('.validate-form').validate({
                    errorClass: 'error',
                    validClass: 'success',
                    errorPlacement: function(error, element) {
                        error.insertAfter(element).addClass('block-form-error');
                    },
                    highlight: function(element) {
                        $(element).addClass('error');
                    },
                    unhighlight: function(element) {
                        $(element).removeClass('error');
                    }
                });
            }
        },

        // Initialisation des formulaires AJAX
        initAjaxForms: function() {
            var self = this;

            $('.ajax-form').on('submit', function(e) {
                e.preventDefault();
                self.submitAjaxForm($(this));
            });
        },

        // Initialisation des champs dépendants
        initDependentFields: function() {
            $('[data-depends-on]').each(function() {
                var $field = $(this);
                var dependsOn = $field.data('depends-on');
                var dependsValue = $field.data('depends-value');
                var $dependentField = $('[name="' + dependsOn + '"]');

                $dependentField.on('change', function() {
                    if ($(this).val() === dependsValue) {
                        $field.closest('.block-form-group').show();
                    } else {
                        $field.closest('.block-form-group').hide();
                    }
                }).trigger('change');
            });
        },

        // Initialisation des modales
        initModals: function() {
            var self = this;

            // Ouvrir une modale
            self.cache.$document.on('click', '[data-modal]', function(e) {
                e.preventDefault();
                var modalId = $(this).data('modal');
                self.openModal(modalId);
            });

            // Fermer une modale
            self.cache.$document.on('click', '.block-modal-close, .block-modal-overlay', function(e) {
                if (e.target === this) {
                    self.closeModal();
                }
            });

            // Échapper pour fermer
            self.cache.$document.on('keydown', function(e) {
                if (e.keyCode === 27) { // Escape
                    self.closeModal();
                }
            });
        },

        // Initialisation des tooltips
        initTooltips: function() {
            if ($.fn.tooltip) {
                $('[data-tooltip]').tooltip({
                    position: { my: "center bottom-20", at: "center top" }
                });
            }
        },

        // Changement d'onglet
        switchTab: function($tab) {
            var targetTab = $tab.attr('href');
            
            // Mettre à jour la navigation
            $tab.closest('.block-admin-nav').find('a').removeClass('current nav-tab-active');
            $tab.addClass('current nav-tab-active');
            
            // Afficher le contenu de l'onglet
            $('.tab-content').hide();
            $(targetTab).show();
            
            // Déclencher un événement personnalisé
            this.cache.$document.trigger('block:tabChanged', [targetTab]);
        },

        // Gestion des actions
        handleAction: function($button) {
            var action = $button.data('action');
            var target = $button.data('target');
            var confirm = $button.data('confirm');

            if (confirm && !window.confirm(confirm)) {
                return false;
            }

            this.showLoading($button);

            switch (action) {
                case 'delete':
                    this.deleteItem($button);
                    break;
                case 'duplicate':
                    this.duplicateItem($button);
                    break;
                case 'export':
                    this.exportData($button);
                    break;
                case 'sync':
                    this.syncData($button);
                    break;
                case 'send-email':
                    this.sendEmail($button);
                    break;
                default:
                    this.customAction($button, action);
            }
        },

        // Gestion des actions groupées
        handleBulkAction: function($button) {
            var action = $button.data('action');
            var selectedItems = $('.item-checkbox:checked').map(function() {
                return $(this).val();
            }).get();

            if (selectedItems.length === 0) {
                this.showNotice('Veuillez sélectionner au moins un élément.', 'warning');
                return;
            }

            if (!confirm(this.config.strings.confirmDelete)) {
                return;
            }

            this.performBulkAction(action, selectedItems);
        },

        // Recherche en temps réel
        handleLiveSearch: function($input) {
            var query = $input.val().toLowerCase();
            var target = $input.data('target');
            var $target = $(target);

            clearTimeout(this.searchTimeout);
            this.searchTimeout = setTimeout(function() {
                $target.find('[data-searchable]').each(function() {
                    var $item = $(this);
                    var text = $item.data('searchable').toLowerCase();
                    
                    if (text.indexOf(query) !== -1 || query === '') {
                        $item.show();
                    } else {
                        $item.hide();
                    }
                });
            }, 300);
        },

        // Ouverture de l'uploader de médias
        openMediaUploader: function($button) {
            var self = this;
            var targetField = $button.data('target');
            var $targetField = $(targetField);

            if (typeof wp !== 'undefined' && wp.media) {
                var mediaUploader = wp.media({
                    title: 'Sélectionner une image',
                    button: {
                        text: 'Utiliser cette image'
                    },
                    multiple: false,
                    library: {
                        type: 'image'
                    }
                });

                mediaUploader.on('select', function() {
                    var attachment = mediaUploader.state().get('selection').first().toJSON();
                    $targetField.val(attachment.url);
                    
                    // Afficher l'aperçu
                    var $preview = $button.siblings('.image-preview');
                    if ($preview.length) {
                        $preview.html('<img src="' + attachment.url + '" style="max-width: 150px; height: auto;">');
                    }
                });

                mediaUploader.open();
            }
        },

        // Suppression d'image
        removeImage: function($button) {
            var targetField = $button.data('target');
            var $targetField = $(targetField);
            
            $targetField.val('');
            $button.siblings('.image-preview').html('');
        },

        // Validation de champ
        validateField: function($field) {
            var value = $field.val();
            var rules = $field.data('validate');
            var isValid = true;
            var errorMessage = '';

            if (rules) {
                if (rules.includes('required') && !value) {
                    isValid = false;
                    errorMessage = 'Ce champ est requis.';
                }

                if (rules.includes('email') && value && !this.isValidEmail(value)) {
                    isValid = false;
                    errorMessage = 'Veuillez saisir une adresse e-mail valide.';
                }

                if (rules.includes('phone') && value && !this.isValidPhone(value)) {
                    isValid = false;
                    errorMessage = 'Veuillez saisir un numéro de téléphone valide.';
                }

                if (rules.includes('postal-code') && value && !this.isValidPostalCode(value)) {
                    isValid = false;
                    errorMessage = 'Veuillez saisir un code postal valide.';
                }
            }

            this.displayFieldValidation($field, isValid, errorMessage);
            return isValid;
        },

        // Affichage de la validation
        displayFieldValidation: function($field, isValid, message) {
            var $group = $field.closest('.block-form-group');
            var $error = $group.find('.block-form-error');

            if (isValid) {
                $field.removeClass('error').addClass('success');
                $error.remove();
            } else {
                $field.removeClass('success').addClass('error');
                if ($error.length === 0) {
                    $field.after('<div class="block-form-error">' + message + '</div>');
                } else {
                    $error.text(message);
                }
            }
        },

        // Calcul de prix
        calculatePrice: function() {
            var data = this.gatherPriceData();
            
            if (data.service_type && data.guest_count) {
                this.ajaxRequest('calculate_price', data, function(response) {
                    if (response.success) {
                        $('.price-display').html(response.data.formatted_price);
                        $('.price-breakdown').html(response.data.breakdown);
                    }
                });
            }
        },

        // Collecte des données de prix
        gatherPriceData: function() {
            var data = {};
            
            $('.price-calculator').find('input, select').each(function() {
                var $field = $(this);
                var name = $field.attr('name');
                var value = $field.val();
                
                if (name && value) {
                    data[name] = value;
                }
            });
            
            return data;
        },

        // Sauvegarde automatique
        autoSave: function() {
            var $form = $('.auto-save-form');
            if ($form.length) {
                var data = $form.serialize();
                
                this.ajaxRequest('auto_save', data, function(response) {
                    if (response.success) {
                        $('.auto-save-indicator').text('Sauvegardé automatiquement').show().fadeOut(3000);
                    }
                });
            }
        },

        // Soumission de formulaire AJAX
        submitAjaxForm: function($form) {
            var self = this;
            var data = $form.serialize();
            var action = $form.data('action') || 'submit_form';
            var $submitBtn = $form.find('[type="submit"]');

            self.showLoading($submitBtn);

            self.ajaxRequest(action, data, function(response) {
                self.hideLoading($submitBtn);
                
                if (response.success) {
                    self.showNotice(response.data.message || 'Opération réussie', 'success');
                    
                    if (response.data.redirect) {
                        window.location.href = response.data.redirect;
                    }
                    
                    if (response.data.reload) {
                        window.location.reload();
                    }
                } else {
                    self.showNotice(response.data.message || 'Une erreur est survenue', 'error');
                }
            });
        },

        // Suppression d'élément
        deleteItem: function($button) {
            var self = this;
            var itemId = $button.data('id');
            var itemType = $button.data('type');

            self.ajaxRequest('delete_item', {
                id: itemId,
                type: itemType
            }, function(response) {
                self.hideLoading($button);
                
                if (response.success) {
                    $button.closest('tr, .item-card').fadeOut(function() {
                        $(this).remove();
                    });
                    self.showNotice('Élément supprimé avec succès', 'success');
                } else {
                    self.showNotice(response.data.message || 'Erreur lors de la suppression', 'error');
                }
            });
        },

        // Duplication d'élément
        duplicateItem: function($button) {
            var self = this;
            var itemId = $button.data('id');
            var itemType = $button.data('type');

            self.ajaxRequest('duplicate_item', {
                id: itemId,
                type: itemType
            }, function(response) {
                self.hideLoading($button);
                
                if (response.success) {
                    window.location.reload();
                } else {
                    self.showNotice(response.data.message || 'Erreur lors de la duplication', 'error');
                }
            });
        },

        // Export de données
        exportData: function($button) {
            var self = this;
            var exportType = $button.data('export-type');
            var format = $button.data('format') || 'csv';

            self.ajaxRequest('export_data', {
                type: exportType,
                format: format
            }, function(response) {
                self.hideLoading($button);
                
                if (response.success) {
                    // Téléchargement du fichier
                    var link = document.createElement('a');
                    link.href = response.data.file_url;
                    link.download = response.data.filename;
                    link.click();
                } else {
                    self.showNotice(response.data.message || 'Erreur lors de l\'export', 'error');
                }
            });
        },

        // Synchronisation de données
        syncData: function($button) {
            var self = this;
            var syncType = $button.data('sync-type');

            self.ajaxRequest('sync_data', {
                type: syncType
            }, function(response) {
                self.hideLoading($button);
                
                if (response.success) {
                    self.showNotice('Synchronisation terminée', 'success');
                    if (response.data.reload) {
                        window.location.reload();
                    }
                } else {
                    self.showNotice(response.data.message || 'Erreur lors de la synchronisation', 'error');
                }
            });
        },

        // Envoi d'email
        sendEmail: function($button) {
            var self = this;
            var emailId = $button.data('email-id');
            var quoteId = $button.data('quote-id');

            self.ajaxRequest('send_email', {
                email_id: emailId,
                quote_id: quoteId
            }, function(response) {
                self.hideLoading($button);
                
                if (response.success) {
                    self.showNotice('Email envoyé avec succès', 'success');
                } else {
                    self.showNotice(response.data.message || 'Erreur lors de l\'envoi', 'error');
                }
            });
        },

        // Action personnalisée
        customAction: function($button, action) {
            var self = this;
            var data = $button.data();

            self.ajaxRequest(action, data, function(response) {
                self.hideLoading($button);
                
                if (response.success) {
                    self.showNotice(response.data.message || 'Action réalisée', 'success');
                } else {
                    self.showNotice(response.data.message || 'Erreur', 'error');
                }
            });
        },

        // Action groupée
        performBulkAction: function(action, items) {
            var self = this;

            self.ajaxRequest('bulk_action', {
                action: action,
                items: items
            }, function(response) {
                if (response.success) {
                    window.location.reload();
                } else {
                    self.showNotice(response.data.message || 'Erreur lors de l\'action groupée', 'error');
                }
            });
        },

        // Mise à jour de l'ordre de tri
        updateSortOrder: function($list) {
            var order = $list.children().map(function() {
                return $(this).data('id');
            }).get();

            this.ajaxRequest('update_sort_order', {
                order: order,
                type: $list.data('type')
            }, function(response) {
                if (!response.success) {
                    console.error('Erreur lors de la mise à jour de l\'ordre');
                }
            });
        },

        // Mise à jour des actions groupées
        updateBulkActions: function() {
            var selectedCount = $('.item-checkbox:checked').length;
            var $bulkActions = $('.bulk-actions');
            
            if (selectedCount > 0) {
                $bulkActions.show();
                $bulkActions.find('.selected-count').text(selectedCount);
            } else {
                $bulkActions.hide();
            }
        },

        // Ouverture de modale
        openModal: function(modalId) {
            var $modal = $('#' + modalId);
            if ($modal.length) {
                $modal.addClass('active');
                this.cache.$body.addClass('modal-open');
            }
        },

        // Fermeture de modale
        closeModal: function() {
            $('.block-modal-overlay').removeClass('active');
            this.cache.$body.removeClass('modal-open');
        },

        // Affichage du loading
        showLoading: function($element) {
            $element.prop('disabled', true);
            if ($element.is('button')) {
                $element.data('original-text', $element.text());
                $element.html('<span class="block-spinner small"></span> ' + this.config.strings.loading);
            }
        },

        // Masquage du loading
        hideLoading: function($element) {
            $element.prop('disabled', false);
            if ($element.is('button') && $element.data('original-text')) {
                $element.text($element.data('original-text'));
            }
        },

        // Affichage d'une notification
        showNotice: function(message, type) {
            type = type || 'info';
            
            var $notice = $('<div class="block-alert block-alert-dismissible ' + type + '">' +
                          '<span>' + message + '</span>' +
                          '<button class="block-alert-dismiss">&times;</button>' +
                          '</div>');
            
            $('.block-notices').prepend($notice);
            
            setTimeout(function() {
                $notice.fadeOut(function() {
                    $(this).remove();
                });
            }, 5000);
            
            $notice.find('.block-alert-dismiss').on('click', function() {
                $notice.fadeOut(function() {
                    $(this).remove();
                });
            });
        },

        // Requête AJAX
        ajaxRequest: function(action, data, callback) {
            var self = this;
            
            data = data || {};
            data.action = 'block_traiteur_' + action;
            data.nonce = self.config.nonce;

            $.ajax({
                url: self.config.ajaxUrl,
                type: 'POST',
                data: data,
                dataType: 'json',
                success: function(response) {
                    if (typeof callback === 'function') {
                        callback(response);
                    }
                },
                error: function(xhr, status, error) {
                    self.log('Erreur AJAX: ' + error, 'error');
                    if (typeof callback === 'function') {
                        callback({
                            success: false,
                            data: { message: 'Erreur de communication avec le serveur' }
                        });
                    }
                }
            });
        },

        // Validation email
        isValidEmail: function(email) {
            var re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        },

        // Validation téléphone
        isValidPhone: function(phone) {
            var re = /^(?:(?:\+|00)33|0)\s*[1-9](?:[\s.-]*\d{2}){4}$/;
            return re.test(phone);
        },

        // Validation code postal
        isValidPostalCode: function(postalCode) {
            var re = /^[0-9]{5}$/;
            return re.test(postalCode);
        },

        // Formatage de prix
        formatPrice: function(price) {
            return parseFloat(price).toFixed(2).replace('.', ',') + ' €';
        },

        // Formatage de date
        formatDate: function(date) {
            if (typeof date === 'string') {
                date = new Date(date);
            }
            return date.toLocaleDateString('fr-FR');
        },

        // Debounce pour optimiser les performances
        debounce: function(func, wait, immediate) {
            var timeout;
            return function() {
                var context = this, args = arguments;
                var later = function() {
                    timeout = null;
                    if (!immediate) func.apply(context, args);
                };
                var callNow = immediate && !timeout;
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
                if (callNow) func.apply(context, args);
            };
        },

        // Log des erreurs
        log: function(message, type) {
            type = type || 'info';
            if (this.config.debug || type === 'error') {
                console[type === 'error' ? 'error' : 'log']('[Block Traiteur] ' + message);
            }
        },

        // Destruction propre
        destroy: function() {
            this.cache.$document.off('.blockTraiteur');
            this.cache.$window.off('.blockTraiteur');
            clearTimeout(this.autoSaveTimeout);
            clearTimeout(this.searchTimeout);
        }
    };

    // Composant de gestion des produits
    BlockTraiteurAdmin.ProductManager = {
        
        init: function() {
            this.bindEvents();
            this.initDragDrop();
        },

        bindEvents: function() {
            var self = this;

            // Ajout de produit
            $(document).on('click', '.add-product-btn', function(e) {
                e.preventDefault();
                self.showProductForm();
            });

            // Édition de produit
            $(document).on('click', '.edit-product-btn', function(e) {
                e.preventDefault();
                var productId = $(this).data('id');
                self.showProductForm(productId);
            });

            // Changement de catégorie
            $(document).on('change', '#product-category', function() {
                self.updateSubcategories($(this).val());
            });

            // Calcul automatique du prix par personne
            $(document).on('input', '#product-base-price, #product-min-guests', function() {
                self.calculatePerPersonPrice();
            });

            // Prévisualisation d'image
            $(document).on('change', '#product-image', function() {
                self.previewImage(this);
            });
        },

        initDragDrop: function() {
            if ($.fn.sortable) {
                $('.products-sortable').sortable({
                    handle: '.product-sort-handle',
                    placeholder: 'product-placeholder',
                    update: function(event, ui) {
                        BlockTraiteurAdmin.ProductManager.updateProductOrder();
                    }
                });
            }
        },

        showProductForm: function(productId) {
            var self = this;
            var isEdit = productId !== undefined;
            var title = isEdit ? 'Modifier le produit' : 'Ajouter un produit';

            var formHtml = '<div class="block-modal-overlay" id="product-modal">' +
                          '<div class="block-modal">' +
                          '<div class="block-modal-header">' +
                          '<h3 class="block-modal-title">' + title + '</h3>' +
                          '<button class="block-modal-close">&times;</button>' +
                          '</div>' +
                          '<div class="block-modal-body">' +
                          '<form id="product-form" class="ajax-form" data-action="save_product">' +
                          (isEdit ? '<input type="hidden" name="product_id" value="' + productId + '">' : '') +
                          '<div class="block-form-row">' +
                          '<div class="block-form-col">' +
                          '<label class="block-form-label required">Nom du produit</label>' +
                          '<input type="text" name="name" class="block-form-control" required>' +
                          '</div>' +
                          '<div class="block-form-col">' +
                          '<label class="block-form-label required">Catégorie</label>' +
                          '<select name="category" id="product-category" class="block-form-control" required>' +
                          '<option value="">Choisir une catégorie</option>' +
                          '<option value="base_package">Forfait de base</option>' +
                          '<option value="meal_formula">Formule repas</option>' +
                          '<option value="buffet">Buffet</option>' +
                          '<option value="option">Option</option>' +
                          '</select>' +
                          '</div>' +
                          '</div>' +
                          '<div class="block-form-group">' +
                          '<label class="block-form-label">Description</label>' +
                          '<textarea name="description" class="block-form-control" rows="3"></textarea>' +
                          '</div>' +
                          '<div class="block-form-row">' +
                          '<div class="block-form-col">' +
                          '<label class="block-form-label required">Prix (€)</label>' +
                          '<input type="number" name="price" id="product-base-price" class="block-form-control" step="0.01" required>' +
                          '</div>' +
                          '<div class="block-form-col">' +
                          '<label class="block-form-label">Type de prix</label>' +
                          '<select name="price_type" class="block-form-control">' +
                          '<option value="fixed">Prix fixe</option>' +
                          '<option value="per_person">Par personne</option>' +
                          '<option value="per_hour">Par heure</option>' +
                          '</select>' +
                          '</div>' +
                          '</div>' +
                          '<div class="block-form-row">' +
                          '<div class="block-form-col">' +
                          '<label class="block-form-label">Nombre minimum d\'invités</label>' +
                          '<input type="number" name="min_guests" id="product-min-guests" class="block-form-control" min="1">' +
                          '</div>' +
                          '<div class="block-form-col">' +
                          '<label class="block-form-label">Nombre maximum d\'invités</label>' +
                          '<input type="number" name="max_guests" class="block-form-control" min="1">' +
                          '</div>' +
                          '</div>' +
                          '<div class="block-form-group">' +
                          '<label class="block-form-label">Service applicable</label>' +
                          '<select name="service_type" class="block-form-control">' +
                          '<option value="both">Restaurant et Remorque</option>' +
                          '<option value="restaurant">Restaurant uniquement</option>' +
                          '<option value="remorque">Remorque uniquement</option>' +
                          '</select>' +
                          '</div>' +
                          '<div class="block-form-group">' +
                          '<label class="block-form-label">Image du produit</label>' +
                          '<div class="image-upload-wrapper">' +
                          '<input type="hidden" name="image_url" id="product-image-url">' +
                          '<button type="button" class="block-btn secondary upload-image-btn" data-target="#product-image-url">Choisir une image</button>' +
                          '<button type="button" class="block-btn danger remove-image-btn" data-target="#product-image-url" style="display:none;">Supprimer</button>' +
                          '<div class="image-preview" style="margin-top: 10px;"></div>' +
                          '</div>' +
                          '</div>' +
                          '<div class="block-form-group">' +
                          '<label class="block-form-label">' +
                          '<input type="checkbox" name="is_active" value="1" checked> Produit actif' +
                          '</label>' +
                          '</div>' +
                          '</form>' +
                          '</div>' +
                          '<div class="block-modal-footer">' +
                          '<button type="button" class="block-btn outline block-modal-close">Annuler</button>' +
                          '<button type="submit" form="product-form" class="block-btn primary">Enregistrer</button>' +
                          '</div>' +
                          '</div>' +
                          '</div>';

            $('body').append(formHtml);
            BlockTraiteurAdmin.openModal('product-modal');

            // Charger les données si édition
            if (isEdit) {
                self.loadProductData(productId);
            }
        },

        loadProductData: function(productId) {
            BlockTraiteurAdmin.ajaxRequest('get_product', { id: productId }, function(response) {
                if (response.success) {
                    var product = response.data;
                    $('#product-form [name="name"]').val(product.name);
                    $('#product-form [name="category"]').val(product.category);
                    $('#product-form [name="description"]').val(product.description);
                    $('#product-form [name="price"]').val(product.price);
                    $('#product-form [name="price_type"]').val(product.price_type);
                    $('#product-form [name="min_guests"]').val(product.min_guests);
                    $('#product-form [name="max_guests"]').val(product.max_guests);
                    $('#product-form [name="service_type"]').val(product.service_type);
                    $('#product-form [name="image_url"]').val(product.image_url);
                    $('#product-form [name="is_active"]').prop('checked', product.is_active == 1);

                    if (product.image_url) {
                        $('.image-preview').html('<img src="' + product.image_url + '" style="max-width: 150px;">');
                        $('.remove-image-btn').show();
                    }
                }
            });
        },

        updateSubcategories: function(category) {
            // Logique pour mettre à jour les sous-catégories selon la catégorie principale
            var subcategories = {
                'meal_formula': [
                    { value: 'sandwichs', label: 'Sandwichs' },
                    { value: 'burgers', label: 'Burgers' },
                    { value: 'plats_chauds', label: 'Plats chauds' },
                    { value: 'salades', label: 'Salades' }
                ],
                'buffet': [
                    { value: 'aperitif', label: 'Apéritif' },
                    { value: 'entrees', label: 'Entrées' },
                    { value: 'desserts', label: 'Desserts' }
                ]
            };

            var $subcategoryField = $('#product-subcategory');
            if (!$subcategoryField.length && subcategories[category]) {
                var subcategoryHtml = '<div class="block-form-group">' +
                                     '<label class="block-form-label">Sous-catégorie</label>' +
                                     '<select name="subcategory" id="product-subcategory" class="block-form-control">' +
                                     '<option value="">Choisir une sous-catégorie</option>';

                subcategories[category].forEach(function(sub) {
                    subcategoryHtml += '<option value="' + sub.value + '">' + sub.label + '</option>';
                });

                subcategoryHtml += '</select></div>';
                $('#product-category').closest('.block-form-col').after(subcategoryHtml);
            } else if (!subcategories[category]) {
                $subcategoryField.closest('.block-form-group').remove();
            }
        },

        calculatePerPersonPrice: function() {
            var basePrice = parseFloat($('#product-base-price').val()) || 0;
            var minGuests = parseInt($('#product-min-guests').val()) || 1;
            var priceType = $('[name="price_type"]').val();

            if (priceType === 'fixed' && minGuests > 0) {
                var perPersonPrice = basePrice / minGuests;
                $('#price-per-person-display').text('Prix par personne: ' + BlockTraiteurAdmin.formatPrice(perPersonPrice));
            }
        },

        previewImage: function(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    $('.image-preview').html('<img src="' + e.target.result + '" style="max-width: 150px;">');
                    $('.remove-image-btn').show();
                };
                reader.readAsDataURL(input.files[0]);
            }
        },

        updateProductOrder: function() {
            var order = $('.products-sortable .product-item').map(function() {
                return $(this).data('id');
            }).get();

            BlockTraiteurAdmin.ajaxRequest('update_product_order', { order: order }, function(response) {
                if (response.success) {
                    BlockTraiteurAdmin.showNotice('Ordre mis à jour', 'success');
                }
            });
        }
    };

    // Composant de gestion des devis
    BlockTraiteurAdmin.QuoteManager = {
        
        init: function() {
            this.bindEvents();
            this.initFilters();
        },

        bindEvents: function() {
            var self = this;

            // Voir le détail d'un devis
            $(document).on('click', '.view-quote-btn', function(e) {
                e.preventDefault();
                var quoteId = $(this).data('id');
                self.showQuoteDetails(quoteId);
            });

            // Changer le statut d'un devis
            $(document).on('change', '.quote-status-select', function() {
                var quoteId = $(this).data('id');
                var newStatus = $(this).val();
                self.updateQuoteStatus(quoteId, newStatus);
            });

            // Envoyer un devis par email
            $(document).on('click', '.send-quote-btn', function(e) {
                e.preventDefault();
                var quoteId = $(this).data('id');
                self.sendQuoteEmail(quoteId);
            });

            // Générer un PDF
            $(document).on('click', '.generate-pdf-btn', function(e) {
                e.preventDefault();
                var quoteId = $(this).data('id');
                self.generatePDF(quoteId);
            });

            // Dupliquer un devis
            $(document).on('click', '.duplicate-quote-btn', function(e) {
                e.preventDefault();
                var quoteId = $(this).data('id');
                self.duplicateQuote(quoteId);
            });
        },

        initFilters: function() {
            // Filtrage par statut
            $('#quote-status-filter').on('change', function() {
                var status = $(this).val();
                $('.quote-row').each(function() {
                    var rowStatus = $(this).data('status');
                    if (status === '' || rowStatus === status) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            });

            // Filtrage par date
            $('#quote-date-from, #quote-date-to').on('change', function() {
                var dateFrom = new Date($('#quote-date-from').val());
                var dateTo = new Date($('#quote-date-to').val());
                
                $('.quote-row').each(function() {
                    var rowDate = new Date($(this).data('date'));
                    var showRow = true;
                    
                    if ($('#quote-date-from').val() && rowDate < dateFrom) {
                        showRow = false;
                    }
                    if ($('#quote-date-to').val() && rowDate > dateTo) {
                        showRow = false;
                    }
                    
                    $(this).toggle(showRow);
                });
            });
        },

        showQuoteDetails: function(quoteId) {
            var self = this;
            
            BlockTraiteurAdmin.ajaxRequest('get_quote_details', { id: quoteId }, function(response) {
                if (response.success) {
                    var quote = response.data;
                    var modalHtml = self.buildQuoteDetailsModal(quote);
                    
                    $('body').append(modalHtml);
                    BlockTraiteurAdmin.openModal('quote-details-modal');
                }
            });
        },

        buildQuoteDetailsModal: function(quote) {
            var itemsHtml = '';
            quote.items.forEach(function(item) {
                itemsHtml += '<tr>' +
                           '<td>' + item.name + '</td>' +
                           '<td>' + item.quantity + '</td>' +
                           '<td>' + BlockTraiteurAdmin.formatPrice(item.unit_price) + '</td>' +
                           '<td>' + BlockTraiteurAdmin.formatPrice(item.total_price) + '</td>' +
                           '</tr>';
            });

            var beveragesHtml = '';
            if (quote.beverages && quote.beverages.length > 0) {
                quote.beverages.forEach(function(beverage) {
                    beveragesHtml += '<tr>' +
                                   '<td>' + beverage.name + '</td>' +
                                   '<td>' + beverage.guest_count + '</td>' +
                                   '<td>' + BlockTraiteurAdmin.formatPrice(beverage.unit_price) + '</td>' +
                                   '<td>' + BlockTraiteurAdmin.formatPrice(beverage.total_price) + '</td>' +
                                   '</tr>';
                });
            }

            return '<div class="block-modal-overlay" id="quote-details-modal">' +
                   '<div class="block-modal" style="max-width: 800px;">' +
                   '<div class="block-modal-header">' +
                   '<h3 class="block-modal-title">Devis #' + quote.quote_number + '</h3>' +
                   '<button class="block-modal-close">&times;</button>' +
                   '</div>' +
                   '<div class="block-modal-body">' +
                   '<div class="quote-details-grid">' +
                   '<div class="quote-info">' +
                   '<h4>Informations client</h4>' +
                   '<p><strong>Nom:</strong> ' + quote.customer_name + '</p>' +
                   '<p><strong>Email:</strong> ' + quote.customer_email + '</p>' +
                   '<p><strong>Téléphone:</strong> ' + (quote.customer_phone || 'Non renseigné') + '</p>' +
                   '<p><strong>Adresse:</strong> ' + (quote.customer_address || 'Non renseignée') + '</p>' +
                   '</div>' +
                   '<div class="event-info">' +
                   '<h4>Informations événement</h4>' +
                   '<p><strong>Date:</strong> ' + BlockTraiteurAdmin.formatDate(quote.event_date) + '</p>' +
                   '<p><strong>Service:</strong> ' + (quote.service_type === 'restaurant' ? 'Restaurant' : 'Remorque') + '</p>' +
                   '<p><strong>Nombre d\'invités:</strong> ' + quote.guest_count + '</p>' +
                   '<p><strong>Durée:</strong> ' + quote.event_duration + 'h</p>' +
                   '</div>' +
                   '</div>' +
                   '<h4>Produits</h4>' +
                   '<table class="block-table">' +
                   '<thead><tr><th>Produit</th><th>Quantité</th><th>Prix unitaire</th><th>Total</th></tr></thead>' +
                   '<tbody>' + itemsHtml + '</tbody>' +
                   '</table>' +
                   (beveragesHtml ? '<h4>Boissons</h4><table class="block-table"><thead><tr><th>Boisson</th><th>Nb personnes</th><th>Prix unitaire</th><th>Total</th></tr></thead><tbody>' + beveragesHtml + '</tbody></table>' : '') +
                   '<div class="quote-totals">' +
                   '<p><strong>Sous-total:</strong> ' + BlockTraiteurAdmin.formatPrice(quote.subtotal) + '</p>' +
                   (quote.travel_cost > 0 ? '<p><strong>Frais de déplacement:</strong> ' + BlockTraiteurAdmin.formatPrice(quote.travel_cost) + '</p>' : '') +
                   '<p class="total-price"><strong>Total:</strong> ' + BlockTraiteurAdmin.formatPrice(quote.total_price) + '</p>' +
                   '</div>' +
                   (quote.notes ? '<div class="quote-notes"><h4>Notes</h4><p>' + quote.notes + '</p></div>' : '') +
                   '</div>' +
                   '<div class="block-modal-footer">' +
                   '<button type="button" class="block-btn outline block-modal-close">Fermer</button>' +
                   '<button type="button" class="block-btn secondary generate-pdf-btn" data-id="' + quote.id + '">Télécharger PDF</button>' +
                   '<button type="button" class="block-btn primary send-quote-btn" data-id="' + quote.id + '">Envoyer par email</button>' +
                   '</div>' +
                   '</div>' +
                   '</div>';
        },

        updateQuoteStatus: function(quoteId, newStatus) {
            BlockTraiteurAdmin.ajaxRequest('update_quote_status', {
                id: quoteId,
                status: newStatus
            }, function(response) {
                if (response.success) {
                    BlockTraiteurAdmin.showNotice('Statut mis à jour', 'success');
                    // Mettre à jour l'affichage
                    var $row = $('[data-quote-id="' + quoteId + '"]');
                    $row.find('.status-badge').attr('class', 'block-status-badge ' + newStatus).text(response.data.status_label);
                } else {
                    BlockTraiteurAdmin.showNotice('Erreur lors de la mise à jour', 'error');
                }
            });
        },

        sendQuoteEmail: function(quoteId) {
            BlockTraiteurAdmin.ajaxRequest('send_quote_email', { id: quoteId }, function(response) {
                if (response.success) {
                    BlockTraiteurAdmin.showNotice('Email envoyé avec succès', 'success');
                } else {
                    BlockTraiteurAdmin.showNotice(response.data.message || 'Erreur lors de l\'envoi', 'error');
                }
            });
        },

        generatePDF: function(quoteId) {
            BlockTraiteurAdmin.ajaxRequest('generate_quote_pdf', { id: quoteId }, function(response) {
                if (response.success) {
                    // Télécharger le PDF
                    var link = document.createElement('a');
                    link.href = response.data.pdf_url;
                    link.download = response.data.filename;
                    link.click();
                } else {
                    BlockTraiteurAdmin.showNotice('Erreur lors de la génération du PDF', 'error');
                }
            });
        },

        duplicateQuote: function(quoteId) {
            if (confirm('Voulez-vous vraiment dupliquer ce devis ?')) {
                BlockTraiteurAdmin.ajaxRequest('duplicate_quote', { id: quoteId }, function(response) {
                    if (response.success) {
                        BlockTraiteurAdmin.showNotice('Devis dupliqué avec succès', 'success');
                        window.location.reload();
                    } else {
                        BlockTraiteurAdmin.showNotice('Erreur lors de la duplication', 'error');
                    }
                });
            }
        }
    };

    // Composant de gestion des paramètres
    BlockTraiteurAdmin.SettingsManager = {
        
        init: function() {
            this.bindEvents();
            this.initColorPickers();
            this.initTabs();
        },

        bindEvents: function() {
            var self = this;

            // Test de connexion email
            $(document).on('click', '#test-email-btn', function(e) {
                e.preventDefault();
                self.testEmailConnection();
            });

            // Test de l'API Google Calendar
            $(document).on('click', '#test-calendar-btn', function(e) {
                e.preventDefault();
                self.testCalendarConnection();
            });

            // Import/Export de configuration
            $(document).on('click', '#export-config-btn', function(e) {
                e.preventDefault();
                self.exportConfiguration();
            });

            $(document).on('change', '#import-config-file', function() {
                self.importConfiguration(this.files[0]);
            });

            // Réinitialisation des paramètres
            $(document).on('click', '#reset-settings-btn', function(e) {
                e.preventDefault();
                if (confirm('Êtes-vous sûr de vouloir réinitialiser tous les paramètres ?')) {
                    self.resetSettings();
                }
            });
        },

        initColorPickers: function() {
            $('.color-field').each(function() {
                $(this).wpColorPicker();
            });
        },

        initTabs: function() {
            $('.settings-tab-link').on('click', function(e) {
                e.preventDefault();
                var target = $(this).attr('href');
                
                $('.settings-tab-link').removeClass('active');
                $(this).addClass('active');
                
                $('.settings-tab-content').removeClass('active');
                $(target).addClass('active');
            });
        },

        testEmailConnection: function() {
            var $btn = $('#test-email-btn');
            BlockTraiteurAdmin.showLoading($btn);
            
            var emailData = {
                host: $('#smtp_host').val(),
                port: $('#smtp_port').val(),
                username: $('#smtp_username').val(),
                password: $('#smtp_password').val(),
                encryption: $('#smtp_encryption').val()
            };

            BlockTraiteurAdmin.ajaxRequest('test_email_connection', emailData, function(response) {
                BlockTraiteurAdmin.hideLoading($btn);
                
                if (response.success) {
                    BlockTraiteurAdmin.showNotice('Connexion email réussie', 'success');
                } else {
                    BlockTraiteurAdmin.showNotice('Erreur de connexion: ' + response.data.message, 'error');
                }
            });
        },

        testCalendarConnection: function() {
            var $btn = $('#test-calendar-btn');
            BlockTraiteurAdmin.showLoading($btn);
            
            var calendarData = {
                api_key: $('#google_api_key').val(),
                calendar_id: $('#google_calendar_id').val()
            };

            BlockTraiteurAdmin.ajaxRequest('test_calendar_connection', calendarData, function(response) {
                BlockTraiteurAdmin.hideLoading($btn);
                
                if (response.success) {
                    BlockTraiteurAdmin.showNotice('Connexion Google Calendar réussie', 'success');
                } else {
                    BlockTraiteurAdmin.showNotice('Erreur de connexion: ' + response.data.message, 'error');
                }
            });
        },

        exportConfiguration: function() {
            BlockTraiteurAdmin.ajaxRequest('export_configuration', {}, function(response) {
                if (response.success) {
                    var dataStr = JSON.stringify(response.data, null, 2);
                    var dataUri = 'data:application/json;charset=utf-8,'+ encodeURIComponent(dataStr);
                    
                    var exportFileDefaultName = 'block-traiteur-config-' + new Date().toISOString().slice(0,10) + '.json';
                    
                    var linkElement = document.createElement('a');
                    linkElement.setAttribute('href', dataUri);
                    linkElement.setAttribute('download', exportFileDefaultName);
                    linkElement.click();
                    
                    BlockTraiteurAdmin.showNotice('Configuration exportée', 'success');
                }
            });
        },

        importConfiguration: function(file) {
            if (!file) return;
            
            var reader = new FileReader();
            reader.onload = function(e) {
                try {
                    var config = JSON.parse(e.target.result);
                    
                    BlockTraiteurAdmin.ajaxRequest('import_configuration', { config: config }, function(response) {
                        if (response.success) {
                            BlockTraiteurAdmin.showNotice('Configuration importée avec succès', 'success');
                            setTimeout(function() {
                                window.location.reload();
                            }, 2000);
                        } else {
                            BlockTraiteurAdmin.showNotice('Erreur lors de l\'import: ' + response.data.message, 'error');
                        }
                    });
                } catch (error) {
                    BlockTraiteurAdmin.showNotice('Fichier de configuration invalide', 'error');
                }
            };
            reader.readAsText(file);
        },

        resetSettings: function() {
            BlockTraiteurAdmin.ajaxRequest('reset_settings', {}, function(response) {
                if (response.success) {
                    BlockTraiteurAdmin.showNotice('Paramètres réinitialisés', 'success');
                    setTimeout(function() {
                        window.location.reload();
                    }, 2000);
                } else {
                    BlockTraiteurAdmin.showNotice('Erreur lors de la réinitialisation', 'error');
                }
            });
        }
    };

    // Initialisation au chargement du DOM
    $(document).ready(function() {
        BlockTraiteurAdmin.init();
        
        // Initialiser les sous-composants selon la page
        if ($('.products-page').length) {
            BlockTraiteurAdmin.ProductManager.init();
        }
        
        if ($('.quotes-page').length) {
            BlockTraiteurAdmin.QuoteManager.init();
        }
        
        if ($('.settings-page').length) {
            BlockTraiteurAdmin.SettingsManager.init();
        }
    });

    // Composant de gestion du dashboard
    BlockTraiteurAdmin.Dashboard = {
        
        init: function() {
            this.loadStats();
            this.initRefreshButtons();
            this.startAutoRefresh();
        },

        loadStats: function() {
            BlockTraiteurAdmin.ajaxRequest('get_dashboard_stats', {}, function(response) {
                if (response.success) {
                    var stats = response.data;
                    
                    // Mettre à jour les compteurs
                    $('.stat-quotes-total').text(stats.total_quotes || 0);
                    $('.stat-quotes-pending').text(stats.pending_quotes || 0);
                    $('.stat-quotes-approved').text(stats.approved_quotes || 0);
                    $('.stat-revenue-month').text(BlockTraiteurAdmin.formatPrice(stats.monthly_revenue || 0));
                    $('.stat-revenue-year').text(BlockTraiteurAdmin.formatPrice(stats.yearly_revenue || 0));
                    
                    // Mettre à jour les graphiques
                    if (stats.chart_data) {
                        BlockTraiteurAdmin.Dashboard.updateCharts(stats.chart_data);
                    }
                }
            });
        },

        updateCharts: function(chartData) {
            // Mettre à jour le graphique des devis
            if (window.quotesChart && chartData.quotes_by_status) {
                window.quotesChart.data.datasets[0].data = chartData.quotes_by_status;
                window.quotesChart.update();
            }
            
            // Mettre à jour le graphique des revenus
            if (window.revenueChart && chartData.monthly_revenue) {
                window.revenueChart.data.labels = chartData.monthly_revenue.labels;
                window.revenueChart.data.datasets[0].data = chartData.monthly_revenue.data;
                window.revenueChart.update();
            }
        },

        initRefreshButtons: function() {
            var self = this;
            
            $('.refresh-stats-btn').on('click', function(e) {
                e.preventDefault();
                var $btn = $(this);
                BlockTraiteurAdmin.showLoading($btn);
                
                self.loadStats();
                
                setTimeout(function() {
                    BlockTraiteurAdmin.hideLoading($btn);
                    BlockTraiteurAdmin.showNotice('Statistiques mises à jour', 'success');
                }, 1000);
            });
        },

        startAutoRefresh: function() {
            // Actualiser les stats toutes les 5 minutes
            setInterval(function() {
                BlockTraiteurAdmin.Dashboard.loadStats();
            }, 300000);
        }
    };

    // Composant de gestion des notifications
    BlockTraiteurAdmin.NotificationManager = {
        
        init: function() {
            this.checkNotifications();
            this.bindEvents();
            this.startPolling();
        },

        bindEvents: function() {
            var self = this;
            
            // Marquer une notification comme lue
            $(document).on('click', '.notification-item', function() {
                var notificationId = $(this).data('id');
                self.markAsRead(notificationId);
                $(this).removeClass('unread');
            });
            
            // Marquer toutes les notifications comme lues
            $(document).on('click', '.mark-all-read-btn', function(e) {
                e.preventDefault();
                self.markAllAsRead();
            });
            
            // Supprimer une notification
            $(document).on('click', '.delete-notification-btn', function(e) {
                e.preventDefault();
                e.stopPropagation();
                var notificationId = $(this).closest('.notification-item').data('id');
                self.deleteNotification(notificationId);
            });
        },

        checkNotifications: function() {
            var self = this;
            
            BlockTraiteurAdmin.ajaxRequest('get_notifications', {}, function(response) {
                if (response.success) {
                    self.updateNotificationDisplay(response.data);
                }
            });
        },

        updateNotificationDisplay: function(notifications) {
            var $container = $('.notifications-container');
            var $counter = $('.notification-counter');
            var unreadCount = 0;
            
            if (notifications.length === 0) {
                $container.html('<div class="no-notifications">Aucune notification</div>');
            } else {
                var html = '';
                notifications.forEach(function(notification) {
                    if (!notification.is_read) {
                        unreadCount++;
                    }
                    
                    html += '<div class="notification-item ' + (notification.is_read ? '' : 'unread') + '" data-id="' + notification.id + '">' +
                           '<div class="notification-icon ' + notification.type + '"></div>' +
                           '<div class="notification-content">' +
                           '<div class="notification-title">' + notification.title + '</div>' +
                           '<div class="notification-message">' + notification.message + '</div>' +
                           '<div class="notification-time">' + notification.created_at + '</div>' +
                           '</div>' +
                           '<button class="delete-notification-btn">&times;</button>' +
                           '</div>';
                });
                $container.html(html);
            }
            
            // Mettre à jour le compteur
            if (unreadCount > 0) {
                $counter.text(unreadCount).show();
            } else {
                $counter.hide();
            }
        },

        markAsRead: function(notificationId) {
            BlockTraiteurAdmin.ajaxRequest('mark_notification_read', {
                id: notificationId
            }, function(response) {
                // Pas besoin de feedback visuel, déjà géré côté client
            });
        },

        markAllAsRead: function() {
            BlockTraiteurAdmin.ajaxRequest('mark_all_notifications_read', {}, function(response) {
                if (response.success) {
                    $('.notification-item').removeClass('unread');
                    $('.notification-counter').hide();
                    BlockTraiteurAdmin.showNotice('Toutes les notifications ont été marquées comme lues', 'success');
                }
            });
        },

        deleteNotification: function(notificationId) {
            var $notification = $('[data-id="' + notificationId + '"]');
            
            BlockTraiteurAdmin.ajaxRequest('delete_notification', {
                id: notificationId
            }, function(response) {
                if (response.success) {
                    $notification.fadeOut(function() {
                        $(this).remove();
                    });
                }
            });
        },

        startPolling: function() {
            // Vérifier les nouvelles notifications toutes les 2 minutes
            setInterval(function() {
                BlockTraiteurAdmin.NotificationManager.checkNotifications();
            }, 120000);
        },

        showNewNotification: function(notification) {
            // Afficher une notification toast pour les nouvelles notifications
            var $toast = $('<div class="notification-toast ' + notification.type + '">' +
                          '<div class="toast-title">' + notification.title + '</div>' +
                          '<div class="toast-message">' + notification.message + '</div>' +
                          '</div>');
            
            $('body').append($toast);
            
            setTimeout(function() {
                $toast.addClass('show');
            }, 100);
            
            setTimeout(function() {
                $toast.removeClass('show');
                setTimeout(function() {
                    $toast.remove();
                }, 300);
            }, 5000);
        }
    };

    // Gestion des erreurs globales
    window.onerror = function(msg, url, line, col, error) {
        if (BlockTraiteurAdmin.config.debug) {
            console.error('Erreur JavaScript:', {
                message: msg,
                source: url,
                line: line,
                column: col,
                error: error
            });
            
            // Envoyer l'erreur au serveur pour logging
            BlockTraiteurAdmin.ajaxRequest('log_js_error', {
                message: msg,
                source: url,
                line: line,
                column: col,
                stack: error ? error.stack : null,
                user_agent: navigator.userAgent,
                url: window.location.href
            });
        }
        return false;
    };

    // Gestion des erreurs AJAX globales
    $(document).ajaxError(function(event, xhr, settings, error) {
        if (BlockTraiteurAdmin.config.debug) {
            console.error('Erreur AJAX:', {
                url: settings.url,
                type: settings.type,
                error: error,
                status: xhr.status,
                responseText: xhr.responseText
            });
        }
        
        // Afficher une erreur utilisateur si ce n'est pas une erreur 200 déguisée
        if (xhr.status !== 200) {
            BlockTraiteurAdmin.showNotice('Erreur de communication avec le serveur', 'error');
        }
    });

    // Raccourcis clavier
    $(document).on('keydown', function(e) {
        // Ctrl/Cmd + S pour sauvegarder
        if ((e.ctrlKey || e.metaKey) && e.keyCode === 83) {
            e.preventDefault();
            var $saveBtn = $('.save-btn:visible, [type="submit"]:visible').first();
            if ($saveBtn.length) {
                $saveBtn.click();
            }
        }
        
        // Ctrl/Cmd + N pour nouveau
        if ((e.ctrlKey || e.metaKey) && e.keyCode === 78) {
            e.preventDefault();
            var $newBtn = $('.add-new-btn:visible, .add-btn:visible').first();
            if ($newBtn.length) {
                $newBtn.click();
            }
        }
        
        // F5 pour actualiser (uniquement sur les pages de données)
        if (e.keyCode === 116 && $('.data-page').length) {
            e.preventDefault();
            window.location.reload();
        }
    });

    // Gestion de la déconnexion automatique
    var lastActivity = Date.now();
    var sessionTimeout = 30 * 60 * 1000; // 30 minutes

    function updateActivity() {
        lastActivity = Date.now();
    }

    // Mettre à jour l'activité sur toute interaction
    $(document).on('click keypress scroll mousewheel', updateActivity);

    // Vérifier la session toutes les minutes
    setInterval(function() {
        if (Date.now() - lastActivity > sessionTimeout) {
            BlockTraiteurAdmin.showNotice('Session expirée. Veuillez vous reconnecter.', 'warning');
            // Rediriger vers la page de connexion après 5 secondes
            setTimeout(function() {
                window.location.href = '/wp-login.php';
            }, 5000);
        }
    }, 60000);

    // Confirmation avant fermeture si des changements non sauvegardés
    var hasUnsavedChanges = false;
    
    $(document).on('input change', 'form:not(.no-confirm) input, form:not(.no-confirm) select, form:not(.no-confirm) textarea', function() {
        hasUnsavedChanges = true;
    });
    
    $(document).on('submit', 'form', function() {
        hasUnsavedChanges = false;
    });
    
    window.addEventListener('beforeunload', function(e) {
        if (hasUnsavedChanges) {
            var confirmationMessage = 'Vous avez des modifications non sauvegardées. Êtes-vous sûr de vouloir quitter cette page ?';
            e.returnValue = confirmationMessage;
            return confirmationMessage;
        }
    });

    // Amélioration de l'accessibilité
    $(document).on('keydown', function(e) {
        // Navigation au clavier dans les tableaux
        if (e.target.tagName === 'TD' || e.target.tagName === 'TH') {
            var $current = $(e.target);
            var $row = $current.closest('tr');
            var $table = $current.closest('table');
            var currentIndex = $current.index();
            
            switch(e.keyCode) {
                case 37: // Flèche gauche
                    e.preventDefault();
                    $current.prev().focus();
                    break;
                case 39: // Flèche droite
                    e.preventDefault();
                    $current.next().focus();
                    break;
                case 38: // Flèche haut
                    e.preventDefault();
                    $row.prev().find('td, th').eq(currentIndex).focus();
                    break;
                case 40: // Flèche bas
                    e.preventDefault();
                    $row.next().find('td, th').eq(currentIndex).focus();
                    break;
            }
        }
    });

    // Ajout d'attributs aria pour l'accessibilité
    $('.block-table').attr('role', 'table');
    $('.block-table thead').attr('role', 'rowgroup');
    $('.block-table tbody').attr('role', 'rowgroup');
    $('.block-table tr').attr('role', 'row');
    $('.block-table th').attr('role', 'columnheader');
    $('.block-table td').attr('role', 'cell');

    // Initialisation des sous-composants selon la page après le DOM ready
    $(window).on('load', function() {
        if ($('.dashboard-page').length) {
            BlockTraiteurAdmin.Dashboard.init();
        }
        
        BlockTraiteurAdmin.NotificationManager.init();
    });

    // Export de l'objet pour utilisation externe
    window.BlockTraiteurAdmin = BlockTraiteurAdmin;

})(jQuery);