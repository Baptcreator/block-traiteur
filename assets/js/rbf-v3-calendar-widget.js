/**
 * Widget Calendrier pour le Formulaire V3
 * Affichage des disponibilités avec synchronisation Google Calendar
 * 
 * @package RestaurantBooking
 * @version 1.0.0
 */

(function($) {
    'use strict';

    /**
     * Configuration AJAX unifiée pour le calendrier
     */
    const CalendarAjaxConfig = {
        /**
         * Récupère la configuration AJAX unifiée
         */
        getConfig: function() {
            // Priorité : rbfV3Ajax > rbfV3Config > restaurant_booking_ajax
            const configs = [rbfV3Ajax, rbfV3Config, restaurant_booking_ajax];
            
            for (const config of configs) {
                if (config && typeof config === 'object') {
                    return {
                        ajaxUrl: config.ajaxUrl || config.ajax_url || '/wp-admin/admin-ajax.php',
                        nonce: config.nonce || ''
                    };
                }
            }
            
            // Fallback par défaut
            return {
                ajaxUrl: '/wp-admin/admin-ajax.php',
                nonce: ''
            };
        },
        
        /**
         * Récupère l'URL AJAX
         */
        getAjaxUrl: function() {
            return this.getConfig().ajaxUrl;
        },
        
        /**
         * Récupère le nonce
         */
        getNonce: function() {
            return this.getConfig().nonce;
        }
    };

    /**
     * Utilitaires de gestion des dates - Accessibles globalement
     */
    window.CalendarDateUtils = {
        /**
         * Convertit une date en format ISO (YYYY-MM-DD)
         */
        toISO: function(date) {
            if (!date) return null;
            const d = new Date(date);
            if (isNaN(d.getTime())) return null;
            return d.toISOString().split('T')[0];
        },
        
        /**
         * Convertit une date en format français d'affichage
         */
        toFrenchFormat: function(dateString) {
            if (!dateString) return '';
            const date = new Date(dateString);
            if (isNaN(date.getTime())) return '';
            
            const options = { 
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            };
            return date.toLocaleDateString('fr-FR', options);
        },
        
        /**
         * Synchronise les champs de date avec deux représentations
         */
        syncDateFields: function(targetValue, showFieldId = null, isoFieldId = null) {
            if (targetValue) {
                // Mettre à jour le champ caché ISO
                if (isoFieldId) {
                    const isoField = document.getElementById(isoFieldId);
                    if (isoField) {
                        isoField.value = this.toISO(targetValue);
                    }
                }
                
                // Mettre à jour le champ d'affichage français
                if (showFieldId) {
                    const showField = document.getElementById(showFieldId);
                    if (showField) {
                        showField.value = this.toFrenchFormat(targetValue);
                    }
                }
            }
        }
    };

    /**
     * Utilitaires AJAX défensifs pour le calendrier
     */
    const CalendarAjaxUtils = {
        /**
         * Parse une réponse AJAX de manière défensive
         */
        parseResponse: function(response) {
            // Si c'est déjà un objet, on retourne tel quel
            if (typeof response === 'object' && response !== null) {
                return response;
            }
            
            // Si c'est une chaîne, on tente de la parser
            if (typeof response === 'string') {
                try {
                    // Si c'est "0" (nonce invalide), on retourne une erreur
                    if (response === '0') {
                        return { success: false, data: { message: 'Erreur de sécurité (nonce invalide)' } };
                    }
                    
                    // Tenter de parser en JSON
                    const parsed = JSON.parse(response);
                    return parsed;
                } catch (e) {
                    // Si le parsing échoue, on considère que c'est une erreur HTML/cache
                    return { 
                        success: false, 
                        data: { message: 'Erreur de communication avec le serveur (réponse inattendue)' } 
                    };
                }
            }
            
            // Par défaut, on retourne une erreur
            return { 
                success: false, 
                data: { message: 'Erreur de communication avec le serveur (réponse inattendue)' } 
            };
        }
    };

    class RbfV3CalendarWidget {
        constructor(container, options = {}) {
            this.container = $(container);
        this.options = {
            serviceType: 'restaurant', // Service par défaut (sera surchargé par les options passées)
            selectedDate: null,
            minDate: new Date(),
            maxDate: new Date(Date.now() + 90 * 24 * 60 * 60 * 1000), // 3 mois
            locale: 'fr',
            ...options
        };
            
            this.currentMonth = new Date().getMonth();
            this.currentYear = new Date().getFullYear();
            this.selectedDate = this.options.selectedDate ? new Date(this.options.selectedDate) : null;
            this.availabilityData = {};
            
            this.init();
        }

        init() {
            this.render();
            this.bindEvents();
            this.loadAvailability();
        }

        render() {
            const html = `
                <div class="rbf-v3-calendar-widget">
                    <div class="rbf-v3-calendar-header">
                        <button type="button" class="rbf-v3-calendar-nav rbf-v3-calendar-prev">
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                                <path d="M10 12l-4-4 4-4v8z"/>
                            </svg>
                        </button>
                        <h3 class="rbf-v3-calendar-title"></h3>
                        <button type="button" class="rbf-v3-calendar-nav rbf-v3-calendar-next">
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                                <path d="M6 4l4 4-4 4V4z"/>
                            </svg>
                        </button>
                    </div>
                    
                    <div class="rbf-v3-calendar-legend">
                        <div class="rbf-v3-legend-item">
                            <span class="rbf-v3-legend-color available"></span>
                            <span>Disponible</span>
                        </div>
                        <div class="rbf-v3-legend-item">
                            <span class="rbf-v3-legend-color blocked"></span>
                            <span>Non disponible</span>
                        </div>
                    </div>
                    
                    <div class="rbf-v3-calendar-grid">
                        <div class="rbf-v3-calendar-weekdays">
                            <div class="rbf-v3-weekday">Dim</div>
                            <div class="rbf-v3-weekday">Lun</div>
                            <div class="rbf-v3-weekday">Mar</div>
                            <div class="rbf-v3-weekday">Mer</div>
                            <div class="rbf-v3-weekday">Jeu</div>
                            <div class="rbf-v3-weekday">Ven</div>
                            <div class="rbf-v3-weekday">Sam</div>
                        </div>
                        <div class="rbf-v3-calendar-days"></div>
                    </div>
                    
                    <div class="rbf-v3-calendar-loading" style="display: none;">
                        <div class="rbf-v3-spinner"></div>
                        <span>Chargement des disponibilités...</span>
                    </div>
                    
                    <input type="hidden" name="event_date" id="rbf-v3-event-date">
                </div>
            `;
            
            this.container.html(html);
            this.updateCalendarTitle();
            this.renderDays();
        }

        bindEvents() {
            // Navigation mensuelle
            this.container.on('click', '.rbf-v3-calendar-prev', () => {
                this.previousMonth();
            });
            
            this.container.on('click', '.rbf-v3-calendar-next', () => {
                this.nextMonth();
            });
            
            // Sélection de date
            this.container.on('click', '.rbf-v3-calendar-day:not(.disabled):not(.blocked)', (e) => {
                this.selectDate($(e.currentTarget));
            });
        }

        previousMonth() {
            this.currentMonth--;
            if (this.currentMonth < 0) {
                this.currentMonth = 11;
                this.currentYear--;
            }
            this.updateCalendarTitle();
            this.renderDays();
            this.loadAvailability();
        }

        nextMonth() {
            this.currentMonth++;
            if (this.currentMonth > 11) {
                this.currentMonth = 0;
                this.currentYear++;
            }
            this.updateCalendarTitle();
            this.renderDays();
            this.loadAvailability();
        }

        updateCalendarTitle() {
            const monthNames = [
                'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin',
                'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'
            ];
            
            const title = `${monthNames[this.currentMonth]} ${this.currentYear}`;
            this.container.find('.rbf-v3-calendar-title').text(title);
        }

        renderDays() {
            const daysContainer = this.container.find('.rbf-v3-calendar-days');
            const firstDay = new Date(this.currentYear, this.currentMonth, 1);
            const lastDay = new Date(this.currentYear, this.currentMonth + 1, 0);
            const firstDayOfWeek = firstDay.getDay();
            const daysInMonth = lastDay.getDate();
            
            let html = '';
            
            // Cases vides pour les jours précédents
            for (let i = 0; i < firstDayOfWeek; i++) {
                html += '<div class="rbf-v3-calendar-day empty"></div>';
            }
            
            // Jours du mois
            for (let day = 1; day <= daysInMonth; day++) {
                const date = new Date(this.currentYear, this.currentMonth, day);
                const dateString = this.formatDate(date);
                const isPast = date < this.options.minDate;
                const isFuture = date > this.options.maxDate;
                const isSelected = this.selectedDate && this.isSameDate(date, this.selectedDate);
                const isToday = this.isSameDate(date, new Date());
                
                let classes = ['rbf-v3-calendar-day'];
                if (isPast || isFuture) classes.push('disabled');
                if (isSelected) classes.push('selected');
                if (isToday) classes.push('today');
                
                html += `
                    <div class="${classes.join(' ')}" data-date="${dateString}">
                        <span class="rbf-v3-day-number">${day}</span>
                        <div class="rbf-v3-day-status"></div>
                        <div class="rbf-v3-day-events"></div>
                    </div>
                `;
            }
            
            daysContainer.html(html);
        }

        loadAvailability() {
            const startDate = new Date(this.currentYear, this.currentMonth, 1);
            const endDate = new Date(this.currentYear, this.currentMonth + 1, 0);
            
            this.showLoading(true);
            
            // ✅ CORRECTION : Utiliser la configuration AJAX unifiée
            $.ajax({
                url: CalendarAjaxConfig.getAjaxUrl(),
                type: 'POST',
                data: {
                    action: 'rbf_v3_get_availability',
                    nonce: CalendarAjaxConfig.getNonce(),
                    start_date: this.formatDate(startDate),
                    end_date: this.formatDate(endDate),
                    service_type: this.options.serviceType
                },
                success: (response) => {
                    this.showLoading(false);
                    console.log('📅 Données de disponibilité reçues:', response);
                    console.log('🔧 Service Type demandé:', this.options.serviceType);
                    
                    // ✅ CORRECTION : Parse défensif de la réponse
                    const parsedResponse = CalendarAjaxUtils.parseResponse(response);
                    
                    if (parsedResponse.success && parsedResponse.data) {
                        this.availabilityData = parsedResponse.data;
                        console.log('📊 Données formatées:', this.availabilityData);
                        
                        // Compter les événements Google
                        let googleEventsCount = 0;
                        let totalEventsCount = 0;
                        Object.keys(this.availabilityData).forEach(date => {
                            const dayData = this.availabilityData[date];
                            if (dayData.events) {
                                totalEventsCount += dayData.events.length;
                                dayData.events.forEach(event => {
                                    if (event.google_event_id) {
                                        googleEventsCount++;
                                    }
                                });
                            }
                        });
                        console.log(`📊 Total événements: ${totalEventsCount}, Événements Google: ${googleEventsCount}`);
                        
                        // Debug spécial pour septembre 2025
                        if (this.currentMonth === 8 && this.currentYear === 2025) { // Septembre = index 8
                            console.log('🎯 Septembre 2025 - Vérification des événements:');
                            console.log('28 sept:', this.availabilityData['2025-09-28']);
                            console.log('29 sept:', this.availabilityData['2025-09-29']);
                        }
                        
                        this.updateDaysAvailability();
                    } else {
                        const errorMessage = parsedResponse.data ? parsedResponse.data.message : 'Réponse inattendue';
                        console.error('❌ Erreur chargement disponibilités:', errorMessage);
                        // Ne pas bloquer l'UI - afficher un état par défaut
                        this.updateDaysAvailability();
                    }
                },
                error: (xhr, status, error) => {
                    this.showLoading(false);
                    
                    // ✅ CORRECTION : Journalisation détaillée pour diagnostics Mac/iOS
                    const logAjaxError = function(request, xhr, status, error) {
                        const logData = {
                            url: request.url,
                            method: request.type || 'POST',
                            status: xhr.status,
                            statusText: xhr.statusText,
                            contentType: xhr.getResponseHeader('Content-Type'),
                            responseText: xhr.responseText ? xhr.responseText.substring(0, 200) : '(vide)',
                            userAgent: navigator.userAgent,
                            timestamp: new Date().toISOString()
                        };
                        
                        console.group('🚨 Calendar AJAX Error - Diagnostics Mac/iOS');
                        console.log('📱 User Agent:', logData.userAgent);
                        console.log('🔗 URL:', logData.url);
                        console.log('📊 Status:', logData.status, logData.statusText);
                        console.log('📄 Content-Type:', logData.contentType);
                        console.log('📝 Response (200 premiers chars):', logData.responseText);
                        console.log('🕐 Timestamp:', logData.timestamp);
                        console.groupEnd();
                    };
                    
                    logAjaxError(
                        {
                            url: CalendarAjaxConfig.getAjaxUrl(),
                            type: 'POST',
                            data: {
                                action: 'rbf_v3_get_availability',
                                nonce: CalendarAjaxConfig.getNonce(),
                                start_date: this.formatDate(startDate),
                                end_date: this.formatDate(endDate),
                                service_type: this.options.serviceType
                            }
                        },
                        xhr,
                        status,
                        error
                    );

                    // En cas d'erreur réseau, toujours mettre à jour l'affichage pour éviter un état bloqué
                    this.updateDaysAvailability();
                }
            });
        }

        updateDaysAvailability() {
            this.container.find('.rbf-v3-calendar-day[data-date]').each((index, element) => {
                const $day = $(element);
                const dateString = $day.data('date');
                
                // ✅ CORRECTION : Accès défensif aux données
                const availability = this.availabilityData && this.availabilityData[dateString] ? this.availabilityData[dateString] : null;
                
                // Logique simplifiée : seulement disponible ou bloqué
                $day.removeClass('available blocked google-sync partial-blocked');
                
                if (availability && typeof availability === 'object') {
                    // Si il y a des événements bloqués (peu importe le type)
                    if (availability.is_fully_blocked || 
                        (availability.events && Array.isArray(availability.events) && availability.events.some(event => event && event.is_available == 0))) {
                        $day.addClass('blocked');
                    } else {
                        $day.addClass('available');
                    }
                } else {
                    // Par défaut, considérer comme disponible
                    $day.addClass('available');
                }
                
                // Ne plus afficher les événements côté client
                $day.find('.rbf-v3-day-events').empty();
            });
        }

        // Méthode updateDayEvents supprimée - plus d'affichage d'horaires côté client

        selectDate($dayElement) {
            const dateString = $dayElement.data('date');
            const date = new Date(dateString);
            
            // Désélectionner l'ancienne date
            this.container.find('.rbf-v3-calendar-day').removeClass('selected');
            
            // Sélectionner la nouvelle date
            $dayElement.addClass('selected');
            this.selectedDate = date;
            
            // ✅ CORRECTION : Gestion des dates avec représentation ISO et affichage local
            // Mettre à jour le champ caché avec la valeur ISO
            this.container.find('#rbf-v3-event-date').val(dateString);
            
            // Synchroniser avec éventuels autres champs de date
            CalendarDateUtils.syncDateFields(
                dateString,
                '#event_date', // Champ d'affichage français
                '#rbf-v3-event-date' // Champ caché ISO
            );
            
            // Déclencher l'événement de changement
            this.container.trigger('dateSelected', {
                date: date,
                dateString: dateString,
                availability: this.availabilityData[dateString] || null
            });
        }

        showLoading(show) {
            const $loading = this.container.find('.rbf-v3-calendar-loading');
            if (show) {
                $loading.show();
            } else {
                $loading.hide();
            }
        }

        formatDate(date) {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        }

        isSameDate(date1, date2) {
            return date1.getFullYear() === date2.getFullYear() &&
                   date1.getMonth() === date2.getMonth() &&
                   date1.getDate() === date2.getDate();
        }

        // API publique
        getSelectedDate() {
            return this.selectedDate;
        }

        setSelectedDate(date) {
            this.selectedDate = new Date(date);
            this.renderDays();
            this.updateDaysAvailability();
        }

        refresh() {
            this.loadAvailability();
        }
    }

    // Plugin jQuery
    $.fn.rbfV3Calendar = function(options) {
        return this.each(function() {
            if (!$(this).data('rbfV3Calendar')) {
                $(this).data('rbfV3Calendar', new RbfV3CalendarWidget(this, options));
            }
        });
    };

    // Auto-initialisation
    $(document).ready(function() {
        $('[data-rbf-calendar]').each(function() {
            const $this = $(this);
            const options = $this.data('rbf-calendar-options') || {};
            $this.rbfV3Calendar(options);
        });
    });

})(jQuery);

// Fonctions globales pour le modal du calendrier
let selectedCalendarDate = null;
let calendarWidgetInstance = null;

function openCalendarModal() {
    const modal = document.getElementById('rbf-v3-calendar-modal');
    if (modal) {
        modal.classList.add('show');
        modal.style.display = 'flex';
        
        // Initialiser le calendrier dans le modal si pas déjà fait
        const $ = jQuery;
        const $container = $('[data-rbf-calendar]');
        if ($container.length && !$container.data('rbfV3Calendar')) {
            // Récupérer le service_type depuis le formulaire ou les données du conteneur
            let serviceType = 'restaurant'; // Valeur par défaut
            
            // Essayer de récupérer depuis le formulaire
            const serviceInput = document.querySelector('input[name="service_type"]');
            if (serviceInput && serviceInput.value) {
                serviceType = serviceInput.value;
            } else {
                // Essayer de récupérer depuis les données du conteneur
                const containerOptions = $container.attr('data-rbf-calendar-options');
                if (containerOptions) {
                    try {
                        const options = JSON.parse(containerOptions);
                        if (options.serviceType) {
                            serviceType = options.serviceType;
                        }
                    } catch (e) {
                        console.warn('Erreur parsing options calendrier:', e);
                    }
                }
            }
            
            $container.rbfV3Calendar({
                serviceType: serviceType,
                selectedDate: document.getElementById('rbf-v3-event-date').value
            });
            calendarWidgetInstance = $container.data('rbfV3Calendar');
        }
        
        // Écouter la sélection de date
        $container.off('dateSelected.modal').on('dateSelected.modal', function(event, data) {
            selectedCalendarDate = data.dateString;
            console.log('📅 Date sélectionnée dans le modal:', selectedCalendarDate);
        });
    }
}

function closeCalendarModal() {
    const modal = document.getElementById('rbf-v3-calendar-modal');
    if (modal) {
        modal.classList.remove('show');
        setTimeout(() => {
            modal.style.display = 'none';
        }, 300);
    }
    selectedCalendarDate = null;
}

function confirmDateSelection() {
    if (selectedCalendarDate) {
        const dateInput = document.getElementById('rbf-v3-event-date');
        if (dateInput) {
            // ✅ VERSION SIMPLIFIÉE : champ unique avec format français
            const formattedDate = CalendarDateUtils.toFrenchFormat(selectedCalendarDate);
            
            // Mettre à jour le champ principal avec le format français (plus convivial)
            dateInput.value = formattedDate;
            
            // Supprimer le champ d'affichage secondaire s'il existe
            const existingDisplayField = document.getElementById('event_date');
            if (existingDisplayField) {
                existingDisplayField.remove();
            }
            
            // Déclencher l'événement change pour la validation du formulaire
            const event = new Event('change', { bubbles: true });
            dateInput.dispatchEvent(event);
            
            console.log('✅ Date confirmée:', selectedCalendarDate, 'Affichée:', formattedDate);
        }
        closeCalendarModal();
    } else {
        alert('Veuillez sélectionner une date disponible.');
    }
}

// Fermer le modal en cliquant à l'extérieur
document.addEventListener('click', function(event) {
    const modal = document.getElementById('rbf-v3-calendar-modal');
    if (modal && event.target === modal) {
        closeCalendarModal();
    }
});

// Fermer le modal avec la touche Escape
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeCalendarModal();
    }
});