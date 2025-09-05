/**
 * Intégration Google Calendar pour Block Traiteur
 *
 * @package Block_Traiteur
 * @subpackage Public
 * @since 1.0.0
 */

(function($) {
    'use strict';

    // Objet principal pour l'intégration calendrier
    window.BlockCalendarIntegration = {
        
        // Configuration
        config: {
            apiKey: '',
            calendarId: '',
            isEnabled: false,
            timeZone: 'Europe/Paris',
            minDate: null,
            maxDate: null,
            blockedDates: [],
            blockedDays: [], // 0 = Dimanche, 1 = Lundi, etc.
            workingHours: {
                start: 8,
                end: 22
            },
            minBookingHours: 24, // Heures minimales avant réservation
            maxBookingDays: 365 // Jours maximaux à l'avance
        },

        // Cache des données
        cache: {
            events: {},
            availability: {},
            $calendar: null,
            $dateInput: null,
            $timeSelect: null
        },

        // État actuel
        state: {
            selectedDate: null,
            selectedTime: null,
            isLoading: false,
            availableTimes: []
        },

        // Initialisation
        init: function(options) {
            this.config = $.extend(true, {}, this.config, options || {});
            
            if (!this.config.isEnabled || !this.config.apiKey || !this.config.calendarId) {
                this.log('Google Calendar non configuré ou désactivé');
                return false;
            }

            this.bindEvents();
            this.initDatePicker();
            this.initTimeSelector();
            this.loadGoogleAPI();
            
            this.log('Intégration Google Calendar initialisée');
            return true;
        },

        // Liaison des événements
        bindEvents: function() {
            var self = this;

            // Changement de date
            $(document).on('change', '.block-date-picker', function() {
                var selectedDate = $(this).val();
                if (selectedDate) {
                    self.onDateSelected(selectedDate);
                }
            });

            // Changement d'heure
            $(document).on('change', '.block-time-selector', function() {
                var selectedTime = $(this).val();
                if (selectedTime) {
                    self.onTimeSelected(selectedTime);
                }
            });

            // Changement de durée d'événement
            $(document).on('change', '.block-duration-selector', function() {
                self.updateAvailableTimes();
            });

            // Rafraîchir la disponibilité
            $(document).on('click', '.refresh-availability-btn', function(e) {
                e.preventDefault();
                self.refreshAvailability();
            });
        },

        // Initialisation du sélecteur de date
        initDatePicker: function() {
            var self = this;
            
            this.cache.$dateInput = $('.block-date-picker');
            
            if (this.cache.$dateInput.length && $.fn.datepicker) {
                var minDate = new Date();
                minDate.setHours(minDate.getHours() + this.config.minBookingHours);
                
                var maxDate = new Date();
                maxDate.setDate(maxDate.getDate() + this.config.maxBookingDays);

                this.cache.$dateInput.datepicker({
                    dateFormat: 'yy-mm-dd',
                    minDate: minDate,
                    maxDate: maxDate,
                    firstDay: 1, // Lundi
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
                    beforeShowDay: function(date) {
                        return self.isDateAvailable(date);
                    },
                    onSelect: function(dateText) {
                        self.onDateSelected(dateText);
                    }
                });
            }
        },

        // Initialisation du sélecteur d'heure
        initTimeSelector: function() {
            this.cache.$timeSelect = $('.block-time-selector');
            
            if (this.cache.$timeSelect.length === 0) {
                // Créer le sélecteur s'il n'existe pas
                var timeSelectHtml = '<select class="block-time-selector block-form-control" name="event_time" required>' +
                                   '<option value="">Sélectionnez une heure</option>' +
                                   '</select>';
                
                $('.block-date-picker').closest('.block-form-group').after(
                    '<div class="block-form-group">' +
                    '<label class="block-form-label required">Heure de début</label>' +
                    timeSelectHtml +
                    '<div class="block-form-help">Les heures disponibles dépendent de votre sélection de date</div>' +
                    '</div>'
                );
                
                this.cache.$timeSelect = $('.block-time-selector');
            }
        },

        // Chargement de l'API Google
        loadGoogleAPI: function() {
            if (typeof gapi !== 'undefined') {
                this.initGoogleAPI();
                return;
            }

            var self = this;
            var script = document.createElement('script');
            script.src = 'https://apis.google.com/js/api.js';
            script.onload = function() {
                self.initGoogleAPI();
            };
            script.onerror = function() {
                self.log('Impossible de charger l\'API Google', 'error');
                self.config.isEnabled = false;
            };
            document.head.appendChild(script);
        },

        // Initialisation de l'API Google
        initGoogleAPI: function() {
            var self = this;
            
            gapi.load('client', function() {
                gapi.client.init({
                    apiKey: self.config.apiKey,
                    discoveryDocs: ['https://www.googleapis.com/discovery/v1/apis/calendar/v3/rest']
                }).then(function() {
                    self.log('API Google Calendar initialisée');
                    self.loadInitialData();
                }).catch(function(error) {
                    self.log('Erreur d\'initialisation de l\'API Google: ' + error.message, 'error');
                    self.config.isEnabled = false;
                });
            });
        },

        // Chargement des données initiales
        loadInitialData: function() {
            var today = new Date();
            var endDate = new Date();
            endDate.setDate(today.getDate() + 30); // Charger 30 jours à l'avance
            
            this.loadCalendarEvents(today, endDate);
        },

        // Chargement des événements du calendrier
        loadCalendarEvents: function(startDate, endDate) {
            if (!this.config.isEnabled) {
                return Promise.resolve([]);
            }

            var self = this;
            var cacheKey = this.formatDate(startDate) + '_' + this.formatDate(endDate);
            
            // Vérifier le cache
            if (this.cache.events[cacheKey]) {
                return Promise.resolve(this.cache.events[cacheKey]);
            }

            return gapi.client.calendar.events.list({
                calendarId: this.config.calendarId,
                timeMin: startDate.toISOString(),
                timeMax: endDate.toISOString(),
                showDeleted: false,
                singleEvents: true,
                orderBy: 'startTime'
            }).then(function(response) {
                var events = response.result.items || [];
                self.cache.events[cacheKey] = events;
                self.log('Événements chargés: ' + events.length);
                return events;
            }).catch(function(error) {
                self.log('Erreur lors du chargement des événements: ' + error.message, 'error');
                return [];
            });
        },

        // Vérification de la disponibilité d'une date
        isDateAvailable: function(date) {
            var dayOfWeek = date.getDay();
            var dateString = this.formatDate(date);
            
            // Vérifier les jours bloqués
            if (this.config.blockedDays.indexOf(dayOfWeek) !== -1) {
                return [false, 'blocked-day', 'Jour non disponible'];
            }
            
            // Vérifier les dates bloquées
            if (this.config.blockedDates.indexOf(dateString) !== -1) {
                return [false, 'blocked-date', 'Date non disponible'];
            }
            
            // Vérifier la date minimale
            var minDate = new Date();
            minDate.setHours(minDate.getHours() + this.config.minBookingHours);
            if (date < minDate) {
                return [false, 'too-early', 'Réservation trop proche'];
            }
            
            // Vérifier la date maximale
            var maxDate = new Date();
            maxDate.setDate(maxDate.getDate() + this.config.maxBookingDays);
            if (date > maxDate) {
                return [false, 'too-late', 'Date trop éloignée'];
            }
            
            return [true, 'available', 'Date disponible'];
        },

        // Gestion de la sélection de date
        onDateSelected: function(dateString) {
            this.state.selectedDate = dateString;
            this.state.selectedTime = null;
            
            this.updateTimeSelector();
            this.highlightSelectedDate();
            
            this.log('Date sélectionnée: ' + dateString);
        },

        // Gestion de la sélection d'heure
        onTimeSelected: function(time) {
            this.state.selectedTime = time;
            this.validateSelection();
            
            this.log('Heure sélectionnée: ' + time);
        },

        // Mise à jour du sélecteur d'heure
        updateTimeSelector: function() {
            if (!this.state.selectedDate || !this.cache.$timeSelect.length) {
                return;
            }

            var self = this;
            this.showLoading(this.cache.$timeSelect);
            
            this.getAvailableTimes(this.state.selectedDate).then(function(times) {
                self.hideLoading(self.cache.$timeSelect);
                self.populateTimeSelector(times);
            }).catch(function(error) {
                self.hideLoading(self.cache.$timeSelect);
                self.log('Erreur lors de la récupération des heures: ' + error.message, 'error');
                self.showError('Impossible de charger les heures disponibles');
            });
        },

        // Récupération des heures disponibles
        getAvailableTimes: function(dateString) {
            var self = this;
            var date = new Date(dateString + 'T00:00:00');
            var startOfDay = new Date(date);
            var endOfDay = new Date(date);
            endOfDay.setDate(endOfDay.getDate() + 1);
            
            return this.loadCalendarEvents(startOfDay, endOfDay).then(function(events) {
                return self.calculateAvailableTimes(date, events);
            });
        },

        // Calcul des heures disponibles
        calculateAvailableTimes: function(date, events) {
            var availableTimes = [];
            var duration = this.getEventDuration();
            var workingStart = this.config.workingHours.start;
            var workingEnd = this.config.workingHours.end;
            
            // Générer tous les créneaux possibles (par tranches de 30 minutes)
            for (var hour = workingStart; hour < workingEnd; hour++) {
                for (var minute = 0; minute < 60; minute += 30) {
                    var timeSlot = {
                        hour: hour,
                        minute: minute,
                        time: this.formatTime(hour, minute),
                        display: this.formatTimeDisplay(hour, minute)
                    };
                    
                    if (this.isTimeSlotAvailable(date, timeSlot, duration, events)) {
                        availableTimes.push(timeSlot);
                    }
                }
            }
            
            return availableTimes;
        },

        // Vérification de la disponibilité d'un créneau
        isTimeSlotAvailable: function(date, timeSlot, duration, events) {
            var slotStart = new Date(date);
            slotStart.setHours(timeSlot.hour, timeSlot.minute, 0, 0);
            
            var slotEnd = new Date(slotStart);
            slotEnd.setHours(slotEnd.getHours() + duration);
            
            // Vérifier si le créneau ne dépasse pas les heures de travail
            if (slotEnd.getHours() > this.config.workingHours.end) {
                return false;
            }
            
            // Vérifier les conflits avec les événements existants
            for (var i = 0; i < events.length; i++) {
                var event = events[i];
                var eventStart = new Date(event.start.dateTime || event.start.date);
                var eventEnd = new Date(event.end.dateTime || event.end.date);
                
                // Vérifier le chevauchement
                if (slotStart < eventEnd && slotEnd > eventStart) {
                    return false;
                }
            }
            
            // Vérifier la contrainte de réservation minimale
            var now = new Date();
            var minBookingTime = new Date(now.getTime() + (this.config.minBookingHours * 60 * 60 * 1000));
            if (slotStart < minBookingTime) {
                return false;
            }
            
            return true;
        },

        // Population du sélecteur d'heure
        populateTimeSelector: function(times) {
            this.state.availableTimes = times;
            var options = '<option value="">Sélectionnez une heure</option>';
            
            if (times.length === 0) {
                options += '<option value="" disabled>Aucune heure disponible</option>';
            } else {
                times.forEach(function(time) {
                    options += '<option value="' + time.time + '">' + time.display + '</option>';
                });
            }
            
            this.cache.$timeSelect.html(options);
            
            // Réactiver l'option précédemment sélectionnée si elle est toujours disponible
            if (this.state.selectedTime) {
                var isStillAvailable = times.some(function(time) {
                    return time.time === this.state.selectedTime;
                }, this);
                
                if (isStillAvailable) {
                    this.cache.$timeSelect.val(this.state.selectedTime);
                } else {
                    this.state.selectedTime = null;
                }
            }
            
            this.updateAvailabilityMessage(times.length);
        },

        // Mise à jour du message de disponibilité
        updateAvailabilityMessage: function(availableCount) {
            var $message = $('.availability-message');
            
            if ($message.length === 0) {
                $message = $('<div class="availability-message block-form-help"></div>');
                this.cache.$timeSelect.after($message);
            }
            
            if (availableCount === 0) {
                $message.text('Aucun créneau disponible pour cette date. Veuillez choisir une autre date.')
                       .removeClass('text-success')
                       .addClass('text-warning');
            } else {
                $message.text(availableCount + ' créneau(x) disponible(s) pour cette date.')
                       .removeClass('text-warning')
                       .addClass('text-success');
            }
        },

        // Mise en évidence de la date sélectionnée
        highlightSelectedDate: function() {
            $('.ui-datepicker-calendar td').removeClass('selected-date');
            
            if (this.state.selectedDate) {
                var date = new Date(this.state.selectedDate);
                var day = date.getDate();
                
                $('.ui-datepicker-calendar td').each(function() {
                    var $cell = $(this);
                    var cellDay = parseInt($cell.find('a').text(), 10);
                    
                    if (cellDay === day && !$cell.hasClass('ui-datepicker-other-month')) {
                        $cell.addClass('selected-date');
                    }
                });
            }
        },

        // Validation de la sélection
        validateSelection: function() {
            var isValid = this.state.selectedDate && this.state.selectedTime;
            
            var $submitBtn = $('.block-form-submit');
            if ($submitBtn.length) {
                $submitBtn.prop('disabled', !isValid);
            }
            
            if (isValid) {
                this.showSelectionSummary();
            }
            
            return isValid;
        },

        // Affichage du résumé de sélection
        showSelectionSummary: function() {
            var $summary = $('.selection-summary');
            
            if ($summary.length === 0) {
                $summary = $('<div class="selection-summary block-alert info"></div>');
                this.cache.$timeSelect.closest('.block-form-group').after($summary);
            }
            
            var date = new Date(this.state.selectedDate);
            var formattedDate = date.toLocaleDateString('fr-FR', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
            
            var time = this.state.selectedTime;
            var duration = this.getEventDuration();
            
            var endTime = this.calculateEndTime(time, duration);
            
            $summary.html(
                '<div class="block-alert-title">Événement planifié</div>' +
                '<div class="block-alert-message">' +
                '<strong>Date :</strong> ' + formattedDate + '<br>' +
                '<strong>Heure :</strong> ' + time + ' - ' + endTime + ' (' + duration + 'h)' +
                '</div>'
            );
        },

        // Rafraîchissement de la disponibilité
        refreshAvailability: function() {
            this.cache.events = {}; // Vider le cache
            this.cache.availability = {};
            
            if (this.state.selectedDate) {
                this.updateTimeSelector();
            }
            
            // Rafraîchir le datepicker
            if (this.cache.$dateInput.length) {
                this.cache.$dateInput.datepicker('refresh');
            }
            
            this.showNotification('Disponibilité mise à jour', 'success');
        },

        // Récupération de la durée de l'événement
        getEventDuration: function() {
            var $durationSelect = $('.block-duration-selector');
            if ($durationSelect.length) {
                return parseInt($durationSelect.val(), 10) || 4;
            }
            return 4; // Durée par défaut
        },

        // Calcul de l'heure de fin
        calculateEndTime: function(startTime, duration) {
            var timeParts = startTime.split(':');
            var startHour = parseInt(timeParts[0], 10);
            var startMinute = parseInt(timeParts[1], 10);
            
            var totalMinutes = (startHour * 60) + startMinute + (duration * 60);
            var endHour = Math.floor(totalMinutes / 60);
            var endMinute = totalMinutes % 60;
            
            return this.formatTime(endHour, endMinute);
        },

        // Création d'un événement dans le calendrier
        createCalendarEvent: function(eventData) {
            if (!this.config.isEnabled) {
                return Promise.resolve({ created: false, reason: 'Calendar disabled' });
            }

            var event = {
                summary: eventData.title || 'Événement Block Traiteur',
                description: eventData.description || '',
                start: {
                    dateTime: eventData.startDateTime,
                    timeZone: this.config.timeZone
                },
                end: {
                    dateTime: eventData.endDateTime,
                    timeZone: this.config.timeZone
                },
                attendees: eventData.attendees || [],
                reminders: {
                    useDefault: false,
                    overrides: [
                        { method: 'email', minutes: 24 * 60 }, // 24h avant
                        { method: 'popup', minutes: 60 }       // 1h avant
                    ]
                }
            };

            var self = this;
            return gapi.client.calendar.events.insert({
                calendarId: this.config.calendarId,
                resource: event
            }).then(function(response) {
                self.log('Événement créé dans le calendrier: ' + response.result.id);
                return {
                    created: true,
                    eventId: response.result.id,
                    htmlLink: response.result.htmlLink
                };
            }).catch(function(error) {
                self.log('Erreur lors de la création de l\'événement: ' + error.message, 'error');
                return {
                    created: false,
                    error: error.message
                };
            });
        },

        // Mise à jour d'un événement
        updateCalendarEvent: function(eventId, eventData) {
            if (!this.config.isEnabled) {
                return Promise.resolve({ updated: false, reason: 'Calendar disabled' });
            }

            var self = this;
            return gapi.client.calendar.events.patch({
                calendarId: this.config.calendarId,
                eventId: eventId,
                resource: eventData
            }).then(function(response) {
                self.log('Événement mis à jour: ' + eventId);
                return { updated: true };
            }).catch(function(error) {
                self.log('Erreur lors de la mise à jour: ' + error.message, 'error');
                return { updated: false, error: error.message };
            });
        },

        // Suppression d'un événement
        deleteCalendarEvent: function(eventId) {
            if (!this.config.isEnabled) {
                return Promise.resolve({ deleted: false, reason: 'Calendar disabled' });
            }

            var self = this;
            return gapi.client.calendar.events.delete({
                calendarId: this.config.calendarId,
                eventId: eventId
            }).then(function() {
                self.log('Événement supprimé: ' + eventId);
                return { deleted: true };
            }).catch(function(error) {
                self.log('Erreur lors de la suppression: ' + error.message, 'error');
                return { deleted: false, error: error.message };
            });
        },

        // Formatage de date
        formatDate: function(date) {
            return date.getFullYear() + '-' + 
                   String(date.getMonth() + 1).padStart(2, '0') + '-' + 
                   String(date.getDate()).padStart(2, '0');
        },

        // Formatage d'heure
        formatTime: function(hour, minute) {
            return String(hour).padStart(2, '0') + ':' + String(minute).padStart(2, '0');
        },

        // Formatage d'heure pour affichage
        formatTimeDisplay: function(hour, minute) {
            return this.formatTime(hour, minute);
        },

        // Affichage du loading
        showLoading: function($element) {
            $element.prop('disabled', true);
            $element.addClass('loading');
            
            var $loader = $('<div class="loading-overlay"><div class="block-spinner"></div></div>');
            $element.closest('.block-form-group').css('position', 'relative').append($loader);
        },

        // Masquage du loading
        hideLoading: function($element) {
            $element.prop('disabled', false);
            $element.removeClass('loading');
            $element.closest('.block-form-group').find('.loading-overlay').remove();
        },

        // Affichage d'erreur
        showError: function(message) {
            this.showNotification(message, 'error');
        },

        // Affichage de notification
        showNotification: function(message, type) {
            type = type || 'info';
            
            var $notification = $('<div class="block-alert ' + type + '">' +
                                '<div class="block-alert-message">' + message + '</div>' +
                                '</div>');
            
            $('.block-form-container').prepend($notification);
            
            setTimeout(function() {
                $notification.fadeOut(function() {
                    $(this).remove();
                });
            }, 5000);
        },

        // Récupération des données de sélection
        getSelectionData: function() {
            return {
                date: this.state.selectedDate,
                time: this.state.selectedTime,
                duration: this.getEventDuration(),
                isValid: this.validateSelection()
            };
        },

        // Mise à jour des heures disponibles
        updateAvailableTimes: function() {
            if (this.state.selectedDate) {
                this.updateTimeSelector();
            }
        },

        // Destruction propre
        destroy: function() {
            this.cache.events = {};
            this.cache.availability = {};
            this.state = {
                selectedDate: null,
                selectedTime: null,
                isLoading: false,
                availableTimes: []
            };
            
            if (this.cache.$dateInput && this.cache.$dateInput.datepicker) {
                this.cache.$dateInput.datepicker('destroy');
            }
        },

        // Log des messages
        log: function(message, type) {
            type = type || 'info';
            if (typeof console !== 'undefined') {
                console[type === 'error' ? 'error' : 'log']('[Block Calendar] ' + message);
            }
        }
    };

    // Auto-initialisation si la configuration est présente
    $(document).ready(function() {
        if (typeof blockCalendarConfig !== 'undefined') {
            BlockCalendarIntegration.init(blockCalendarConfig);
        }
    });

    // Export pour utilisation externe
    window.BlockCalendarIntegration = BlockCalendarIntegration;

})(jQuery);