(function () {
    // Always (re)define so published updates replace a stale in-memory class.
    window.BladewindCopyButton = class BladewindCopyButton {
        name;
        root;

        constructor(name) {
            this.name = name;
            this.root = domEl(`.${name}`);
            if (! this.root) {
                return;
            }

            this.trigger = this.root.querySelector('[data-trigger]');
            this.content = this.root.querySelector('[data-content]');
            this.iconDefault = this.root.querySelector('[data-icon-default]');
            this.iconSuccess = this.root.querySelector('[data-icon-success]');
            this.status = this.root.querySelector('[data-status]');

            this.activate();
        }

        activate = () => {
            this.trigger?.addEventListener('click', () => this.copy());
        };

        valueToCopy = () => {
            if (this.root.dataset.value !== undefined) {
                return this.root.dataset.value;
            }
            return (this.content?.textContent || '').trim();
        };

        copy = () => {
            const text = this.valueToCopy();

            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(text).then(() => this.onSuccess(), () => this.onFailure());
                return;
            }

            // legacy fallback for non-secure contexts / older browsers
            const textarea = document.createElement('textarea');
            textarea.value = text;
            textarea.style.position = 'fixed';
            textarea.style.opacity = '0';
            document.body.appendChild(textarea);
            textarea.focus();
            textarea.select();

            try {
                document.execCommand('copy') ? this.onSuccess() : this.onFailure();
            } catch (error) {
                this.onFailure();
            }

            document.body.removeChild(textarea);
        };

        onSuccess = () => {
            this.iconDefault?.classList.add('hidden');
            this.iconSuccess?.classList.remove('hidden');
            if (this.status) this.status.textContent = this.root.dataset.copiedMessage || 'Copied';

            clearTimeout(this._resetTimer);
            const timeout = parseInt(this.root.dataset.timeout, 10) || 1500;
            this._resetTimer = setTimeout(() => this.reset(), timeout);
        };

        onFailure = () => {
            if (this.status) this.status.textContent = this.root.dataset.failedMessage || 'Could not copy';
        };

        reset = () => {
            this.iconDefault?.classList.remove('hidden');
            this.iconSuccess?.classList.add('hidden');
            if (this.status) this.status.textContent = '';
        };
    };
})();
