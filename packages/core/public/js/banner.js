(function () {
    // Always (re)define so published updates replace a stale in-memory class.
    window.BladewindBanner = class BladewindBanner {
        id;
        persistKey;
        root;

        constructor(id, persistKey = '') {
            this.id = id;
            this.persistKey = persistKey;
            this.root = document.querySelector(`[data-bw-banner="${id}"]`);

            if (!this.root) {
                return;
            }

            if (this.persistKey && this.isDismissed()) {
                this.root.remove();
                return;
            }

            this.root.querySelector('[data-bw-banner-dismiss]')
                ?.addEventListener('click', () => this.dismiss());
        }

        storageKey = () => `bw-banner-dismissed-${this.persistKey}`;

        isDismissed = () => {
            try {
                return localStorage.getItem(this.storageKey()) === '1';
            } catch (e) {
                return false;
            }
        };

        dismiss = () => {
            this.root.remove();

            if (!this.persistKey) {
                return;
            }

            try {
                localStorage.setItem(this.storageKey(), '1');
            } catch (e) {
                // storage unavailable (private browsing, quota) — the
                // dismissal just won't survive a reload
            }
        };
    };
})();
