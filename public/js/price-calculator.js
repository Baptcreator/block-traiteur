/**
 * Calculateur de prix en temps réel
 */

class PriceCalculator {
    constructor() {
        this.formData = {};
        this.settings = blockTraiteurAjax.settings;
        this.priceBreakdown = {
            base: 0,
            duration: 0,
            guests: 0,
            distance: 0,
            products: 0,
            beverages: 0,
            options: 0
        };
    }
    
    updateFormData(formData) {
        this.formData = formData;
        this.calculateAll();
    }
    
    calculateAll() {
        this.calculateBasePrice();
        this.calculateDurationSupplement();
        this.calculateGuestSupplement();
        this.calculateDistanceSupplement();
        this.calculateProductsPrice();
        this.calculateBeveragesPrice();
        this.calculateOptionsPrice();
    }
    
    calculateBasePrice() {
        const serviceType = this.formData.serviceType || 'restaurant';
        
        if (serviceType === 'restaurant') {
            this.priceBreakdown.base = parseFloat(this.settings.restaurant_base_price) || 300;
        } else {
            this.priceBreakdown.base = parseFloat(this.settings.remorque_base_price) || 350;
        }
    }
    
    calculateDurationSupplement() {
        const duration = parseInt(this.formData.duration) || 2;
        const serviceType = this.formData.serviceType || 'restaurant';
        
        const minDuration = serviceType === 'restaurant' 
            ? parseInt(this.settings.restaurant_min_duration) || 2
            : parseInt(this.settings.remorque_min_duration) || 2;
        
        if (duration > minDuration) {
            const extraHours = duration - minDuration;
            this.priceBreakdown.duration = extraHours * (parseFloat(this.settings.hour_supplement) || 50);
        } else {
            this.priceBreakdown.duration = 0;
        }
    }
    
    calculateGuestSupplement() {
        const guestCount = parseInt(this.formData.guestCount) || 0;
        const serviceType = this.formData.serviceType || 'restaurant';
        
        // Supplément uniquement pour la remorque au-delà de 50 invités
        if (serviceType === 'remorque') {
            const threshold = parseInt(this.settings.remorque_guest_supplement_threshold) || 50;
            if (guestCount > threshold) {
                this.priceBreakdown.guests = parseFloat(this.settings.remorque_guest_supplement) || 150;
            } else {
                this.priceBreakdown.guests = 0;
            }
        } else {
            this.priceBreakdown.guests = 0;
        }
    }
    
    calculateDistanceSupplement() {
        if (this.formData.serviceType !== 'remorque' || !this.formData.distance) {
            this.priceBreakdown.distance = 0;
            return;
        }
        
        const distance = parseFloat(this.formData.distance);
        const zone1Max = parseFloat(this.settings.delivery_zone_1_max) || 30;
        const zone2Max = parseFloat(this.settings.delivery_zone_2_max) || 50;
        const zone3Max = parseFloat(this.settings.delivery_zone_3_max) || 100;
        const zone4Max = parseFloat(this.settings.delivery_zone_4_max) || 150;
        
        if (distance <= zone1Max) {
            this.priceBreakdown.distance = 0;
        } else if (distance <= zone2Max) {
            this.priceBreakdown.distance = parseFloat(this.settings.delivery_zone_2_price) || 20;
        } else if (distance <= zone3Max) {
            this.priceBreakdown.distance = parseFloat(this.settings.delivery_zone_3_price) || 70;
        } else if (distance <= zone4Max) {
            this.priceBreakdown.distance = parseFloat(this.settings.delivery_zone_4_price) || 118;
        } else {
            this.priceBreakdown.distance = 0; // Hors zone - prix sur devis
        }
    }
    
    calculateProductsPrice() {
        let total = 0;
        
        if (this.formData.selectedProducts && this.formData.selectedProducts.length > 0) {
            this.formData.selectedProducts.forEach(product => {
                const quantity = parseInt(product.quantity) || 0;
                const price = parseFloat(product.price) || 0;
                total += quantity * price;
            });
        }
        
        this.priceBreakdown.products = total;
    }
    
    calculateBeveragesPrice() {
        let total = 0;
        
        if (this.formData.selectedBeverages && this.formData.selectedBeverages.length > 0) {
            this.formData.selectedBeverages.forEach(beverage => {
                const quantity = parseInt(beverage.quantity) || 0;
                const price = parseFloat(beverage.price) || 0;
                total += quantity * price;
            });
        }
        
        this.priceBreakdown.beverages = total;
    }
    
    calculateOptionsPrice() {
        let total = 0;
        
        if (this.formData.selectedOptions && this.formData.selectedOptions.length > 0) {
            this.formData.selectedOptions.forEach(option => {
                switch (option) {
                    case 'tireuse':
                        total += parseFloat(this.settings.tireuse_option_price) || 50;
                        break;
                    case 'jeux':
                        total += parseFloat(this.settings.jeux_option_price) || 70;
                        break;
                }
            });
        }
        
        this.priceBreakdown.options = total;
    }
    
    getTotalPrice() {
        return Object.values(this.priceBreakdown).reduce((sum, price) => sum + price, 0);
    }
    
    getPriceBreakdown() {
        return { ...this.priceBreakdown };
    }
    
    getFormattedTotal() {
        const total = this.getTotalPrice();
        return total > 0 ? this.formatPrice(total) : 'À partir de ' + this.formatPrice(this.priceBreakdown.base);
    }
    
    formatPrice(amount) {
        return new Intl.NumberFormat('fr-FR', {
            style: 'currency',
            currency: 'EUR'
        }).format(amount);
    }
    
    // Estimation rapide pour l'affichage initial
    static quickEstimate(serviceType, guestCount, duration, distance = 0) {
        const settings = blockTraiteurAjax.settings;
        let total = 0;
        
        // Prix de base
        if (serviceType === 'restaurant') {
            total += parseFloat(settings.restaurant_base_price) || 300;
            const minDuration = parseInt(settings.restaurant_min_duration) || 2;
            if (duration > minDuration) {
                total += (duration - minDuration) * (parseFloat(settings.hour_supplement) || 50);
            }
        } else {
            total += parseFloat(settings.remorque_base_price) || 350;
            const minDuration = parseInt(settings.remorque_min_duration) || 2;
            if (duration > minDuration) {
                total += (duration - minDuration) * (parseFloat(settings.hour_supplement) || 50);
            }
            
            // Supplément invités
            const threshold = parseInt(settings.remorque_guest_supplement_threshold) || 50;
            if (guestCount > threshold) {
                total += parseFloat(settings.remorque_guest_supplement) || 150;
            }
            
            // Supplément distance
            if (distance > 0) {
                const zone1Max = parseFloat(settings.delivery_zone_1_max) || 30;
                const zone2Max = parseFloat(settings.delivery_zone_2_max) || 50;
                const zone3Max = parseFloat(settings.delivery_zone_3_max) || 100;
                const zone4Max = parseFloat(settings.delivery_zone_4_max) || 150;
                
                if (distance <= zone2Max && distance > zone1Max) {
                    total += parseFloat(settings.delivery_zone_2_price) || 20;
                } else if (distance <= zone3Max) {
                    total += parseFloat(settings.delivery_zone_3_price) || 70;
                } else if (distance <= zone4Max) {
                    total += parseFloat(settings.delivery_zone_4_price) || 118;
                }
            }
        }
        
        return total;
    }
}