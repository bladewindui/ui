(function () {
    // Always (re)define so published updates replace a stale in-memory class.
    window.BladewindCodeBlock = class BladewindCodeBlock {
        id;
        root;

        constructor(id) {
            this.id = id;
            this.root = document.querySelector(`[data-bw-code-block="${id}"]`);

            if (!this.root) {
                return;
            }

            this.source = this.root.querySelector('[data-bw-code-block-source]');
            this.copyTrigger = this.root.querySelector('[data-bw-code-block-copy]');
            this.iconDefault = this.root.querySelector('[data-icon-default]');
            this.iconSuccess = this.root.querySelector('[data-icon-success]');

            if (window.Prism && this.source) {
                window.Prism.highlightElement(this.source);
            }

            this.copyTrigger?.addEventListener('click', () => this.copy());
        }

        copy = () => {
            const text = this.source?.textContent || '';

            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(text).then(() => this.onSuccess(), () => this.onFailure());
                return;
            }

            const textarea = document.createElement('textarea');
            textarea.value = text;
            textarea.style.position = 'fixed';
            textarea.style.opacity = '0';
            document.body.appendChild(textarea);
            textarea.select();

            try {
                document.execCommand('copy');
                this.onSuccess();
            } catch (e) {
                this.onFailure();
            }

            textarea.remove();
        };

        onSuccess = () => {
            this.iconDefault?.classList.add('hidden');
            this.iconSuccess?.classList.remove('hidden');

            setTimeout(() => {
                this.iconDefault?.classList.remove('hidden');
                this.iconSuccess?.classList.add('hidden');
            }, 2000);
        };

        onFailure = () => {
            // clipboard write failed (permissions, insecure context with no
            // execCommand fallback available) — nothing further to do, the
            // icon simply does not switch to the success state
        };
    };
})();
