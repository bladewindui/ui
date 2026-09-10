(function () {
    // Always (re)define so published updates replace a stale in-memory class.
    window.BladewindInlineEdit = class BladewindInlineEdit {
        name;
        onSave;
        root;

        constructor(name, onSave = null) {
            this.name = name;
            this.onSave = onSave;
            this.root = domEl(`.${name}`);
            if (! this.root) {
                return;
            }

            this.display = this.root.querySelector('[data-display]');
            this.displayText = this.root.querySelector('[data-display-text]');
            this.editForm = this.root.querySelector('[data-edit-form]');
            this.input = this.root.querySelector('[data-input]');
            this.saveButton = this.root.querySelector('[data-save]');
            this.cancelButton = this.root.querySelector('[data-cancel]');
            this.editTrigger = this.root.querySelector('[data-edit-trigger]');
            this.errorEl = this.root.querySelector('[data-error]');
            this.hiddenInput = this.root.querySelector('[data-hidden-value]');
            this.saveIcon = this.root.querySelector('[data-icon-save]');
            this.spinner = this.root.querySelector('[data-spinner]');
            this.currentValue = this.root.dataset.value || '';

            this.activate();
        }

        activate = () => {
            this.editTrigger?.addEventListener('click', () => this.enterEdit());
            this.displayText?.addEventListener('click', () => this.enterEdit());
            this.displayText?.addEventListener('keydown', (event) => {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    this.enterEdit();
                }
            });

            this.cancelButton?.addEventListener('click', () => this.cancel());
            this.saveButton?.addEventListener('click', () => this.save());

            this.input?.addEventListener('keydown', (event) => {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    this.save();
                } else if (event.key === 'Escape') {
                    event.preventDefault();
                    this.cancel();
                }
            });
        };

        enterEdit = () => {
            this.hideError();
            this.input.value = this.currentValue;
            this.display.classList.add('hidden');
            this.editForm.classList.remove('hidden');
            this.input.focus();
            this.input.select();
        };

        exitEdit = () => {
            this.editForm.classList.add('hidden');
            this.display.classList.remove('hidden');
        };

        cancel = () => {
            this.input.value = this.currentValue;
            this.hideError();
            this.exitEdit();
        };

        showError = (message) => {
            this.errorEl.textContent = message;
            this.errorEl.classList.remove('hidden');
        };

        hideError = () => {
            this.errorEl.classList.add('hidden');
            this.errorEl.textContent = '';
        };

        setPending = (pending) => {
            this.input.disabled = pending;
            this.saveButton.disabled = pending;
            this.cancelButton.disabled = pending;
            this.saveIcon.classList.toggle('hidden', pending);
            this.spinner.classList.toggle('hidden', ! pending);
        };

        applyValue = (value) => {
            this.currentValue = value;
            this.root.dataset.value = value;
            this.hiddenInput.value = value;

            const isEmpty = value === '';
            this.displayText.textContent = isEmpty ? (this.root.dataset.placeholder || '') : value;
            this.displayText.classList.toggle('italic', isEmpty);
            this.displayText.classList.toggle('text-gray-400', isEmpty);
            this.displayText.classList.toggle('dark:text-dark-500', isEmpty);
            this.displayText.classList.toggle('text-gray-700', ! isEmpty);
            this.displayText.classList.toggle('dark:text-dark-200', ! isEmpty);
        };

        save = () => {
            const newValue = this.input.value.trim();
            const required = this.root.dataset.required === '1';

            if (required && newValue === '') {
                this.showError(this.root.dataset.requiredMessage || 'This field is required');
                return;
            }

            if (newValue === this.currentValue) {
                this.exitEdit();
                return;
            }

            this.hideError();

            if (! this.onSave) {
                this.applyValue(newValue);
                this.exitEdit();
                return;
            }

            this.setPending(true);
            Promise.resolve()
                .then(() => this.onSave(newValue, this.currentValue))
                .then(() => {
                    this.setPending(false);
                    this.applyValue(newValue);
                    this.exitEdit();
                })
                .catch((error) => {
                    this.setPending(false);
                    this.showError(typeof error === 'string' ? error : (error?.message || 'Something went wrong'));
                });
        };
    };
})();
