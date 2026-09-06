(function () {
    // Mirrors Mkocansey\Bladewind\CreditCard\CreditCardBrand exactly, so a
    // server-rendered value and a value typed live agree on the same brand.
    const BRANDS = {
        visa: { pattern: /^4/, groups: [4, 4, 4, 4], label: 'VISA' },
        mastercard: { pattern: /^(5[1-5]|2(22[1-9]|2[3-9]\d|[3-6]\d{2}|7[01]\d|720))/, groups: [4, 4, 4, 4], label: 'Mastercard' },
        amex: { pattern: /^3[47]/, groups: [4, 6, 5], label: 'AMEX' },
        discover: { pattern: /^(6011|65|64[4-9])/, groups: [4, 4, 4, 4], label: 'DISCOVER' },
        diners: { pattern: /^(30[0-5]|36|38)/, groups: [4, 6, 4], label: 'Diners Club' },
        jcb: { pattern: /^35(2[89]|[3-8]\d)/, groups: [4, 4, 4, 4], label: 'JCB' },
        unionpay: { pattern: /^62/, groups: [4, 4, 4, 4], label: 'UnionPay' },
        maestro: { pattern: /^(50|5[6-8]|6304|6759|676770|676774)/, groups: [4, 4, 4, 4], label: 'Maestro' },
    };

    const detectBrand = (digits) => {
        for (const brand in BRANDS) {
            if (BRANDS[brand].pattern.test(digits)) return brand;
        }
        return null;
    };

    const brandLength = (brand) => (brand === 'amex' ? 15 : brand === 'diners' ? 14 : 16);
    const cvcLength = (brand) => (brand === 'amex' ? 4 : 3);

    const formatNumber = (digits, brand) => {
        const groups = (BRANDS[brand] && BRANDS[brand].groups) || [4, 4, 4, 4];
        const chunks = [];
        let offset = 0;
        for (const size of groups) {
            const chunk = digits.slice(offset, offset + size);
            if (!chunk) break;
            chunks.push(chunk);
            offset += size;
        }
        if (offset < digits.length) chunks.push(digits.slice(offset));
        return chunks.join(' ');
    };

    // Always (re)define so published updates replace a stale in-memory class.
    window.BladewindCreditCard = class BladewindCreditCard {
        name;
        root;
        brand = null;

        constructor(name, options = {}) {
            this.name = name;
            this.root = document.querySelector(`[data-bw-credit-card="${name}"]`);
            if (!this.root) return;

            this.options = { required: false, errorMessage: '', onChange: null, ...options };

            this.numberEl = this.root.querySelector('[data-number]');
            this.nameEl = this.root.querySelector('[data-name]');
            this.expiryMonthEl = this.root.querySelector('[data-expiry-month]');
            this.expiryYearEl = this.root.querySelector('[data-expiry-year]');
            this.expiryEl = this.root.querySelector('[data-expiry]');
            this.cvcEl = this.root.querySelector('[data-cvc]');
            this.brandLabels = [...this.root.querySelectorAll('[data-brand-label]')];
            this.flipInner = this.root.querySelector('[data-flip-inner]');
            this.errorEl = this.root.querySelector('[data-error]');

            const initialDigits = this.digits();
            this.brand = initialDigits ? detectBrand(initialDigits) : null;

            this.bindNumber();
            this.bindExpiry();
            this.bindCvc();
            this.bindFlip();

            [this.nameEl].forEach((el) => {
                el?.addEventListener('input', () => this.notifyChange());
            });
        }

        digits = () => (this.numberEl?.value || '').replace(/\D/g, '');

        bindNumber = () => {
            if (!this.numberEl) return;

            this.numberEl.addEventListener('input', () => {
                const digits = this.digits();
                this.brand = detectBrand(digits);
                const capped = digits.slice(0, brandLength(this.brand));
                this.numberEl.value = formatNumber(capped, this.brand);
                this.updateBrandLabels();
                if (this.cvcEl) this.cvcEl.maxLength = cvcLength(this.brand);
                this.notifyChange();
            });
        };

        bindExpiry = () => {
            const twoDigitsOnly = (el, max) => {
                if (!el) return;
                el.addEventListener('input', () => {
                    let value = el.value.replace(/\D/g, '').slice(0, 2);
                    if (max && value.length === 2 && Number(value) > max) value = String(max);
                    el.value = value;
                    if (value.length === 2 && el === this.expiryMonthEl) this.expiryYearEl?.focus();
                    this.notifyChange();
                });
            };

            twoDigitsOnly(this.expiryMonthEl, 12);
            twoDigitsOnly(this.expiryYearEl, null);

            this.expiryEl?.addEventListener('input', () => {
                let digits = this.expiryEl.value.replace(/\D/g, '').slice(0, 4);
                this.expiryEl.value = digits.length > 2 ? `${digits.slice(0, 2)} / ${digits.slice(2)}` : digits;
                this.notifyChange();
            });
        };

        bindCvc = () => {
            this.cvcEl?.addEventListener('input', () => {
                this.cvcEl.value = this.cvcEl.value.replace(/\D/g, '').slice(0, cvcLength(this.brand));
                this.notifyChange();
            });
        };

        bindFlip = () => {
            if (!this.flipInner) return;

            this.root.querySelectorAll('[data-flip-button]').forEach((btn) => {
                btn.addEventListener('click', () => this.toggleFlip());
            });

            this.root.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && this.isFlipped()) this.toggleFlip();
            });
        };

        isFlipped = () => this.flipInner?.style.transform === 'rotateY(180deg)';

        toggleFlip = () => {
            if (!this.flipInner) return;
            const flipped = !this.isFlipped();
            this.flipInner.style.transform = flipped ? 'rotateY(180deg)' : '';
            if (flipped) this.cvcEl?.focus();
            else this.numberEl?.focus();
        };

        updateBrandLabels = () => {
            const label = (BRANDS[this.brand] && BRANDS[this.brand].label) || '';
            this.brandLabels.forEach((el) => (el.textContent = label));
        };

        expiryParts = () => {
            if (this.expiryMonthEl) {
                return [this.expiryMonthEl.value, this.expiryYearEl?.value || ''];
            }
            if (this.expiryEl) {
                const digits = this.expiryEl.value.replace(/\D/g, '');
                return [digits.slice(0, 2), digits.slice(2, 4)];
            }
            return ['', ''];
        };

        get value() {
            const [expiryMonth, expiryYear] = this.expiryParts();
            const numberDigits = this.digits();

            return {
                number: this.numberEl?.value || '',
                numberDigits,
                cardholderName: this.nameEl?.value || '',
                expiryMonth,
                expiryYear,
                cvc: this.cvcEl?.value || '',
                brand: this.brand,
            };
        }

        validate = () => {
            const value = this.value;
            let valid = true;

            if (!this.options.required) return true;

            if (value.numberDigits.length !== brandLength(value.brand)) valid = false;
            if (this.nameEl && value.cardholderName.trim() === '') valid = false;

            const month = Number(value.expiryMonth);
            const year = Number(value.expiryYear);
            if (!month || !year || month < 1 || month > 12) {
                valid = false;
            } else {
                const now = new Date();
                const currentYear = now.getFullYear() % 100;
                const currentMonth = now.getMonth() + 1;
                if (year < currentYear || (year === currentYear && month < currentMonth)) valid = false;
            }

            if (value.cvc.length !== cvcLength(value.brand)) valid = false;

            if (this.errorEl) this.errorEl.hidden = valid;

            return valid;
        };

        notifyChange = () => {
            if (typeof this.options.onChange === 'function') {
                this.options.onChange(this.value);
            }

            this.root.dispatchEvent(new CustomEvent('change', { detail: this.value, bubbles: true }));
        };
    };
})();
