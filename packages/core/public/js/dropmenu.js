(function () {
    // Always (re)define so published updates replace a stale in-memory class.
    window.BladewindDropmenu = class BladewindDropmenu {
        name;
        items;
        options;

        constructor(name, options = {}) {
            this.name = name;
            this.items = `.${name} .bw-dropmenu-items`;
            this.options = options;
            this._outsideClickBound = false;
            this._repositionBound = false;
            this.activate();
        }

        listEl = () => {
            return domEl(`${this.items} .bw-items-list`) || domEl(`${this.items} > div`);
        };

        show = () => {
            if (! this.hasItems()) {
                return;
            }

            const root = domEl(`.${this.name}`);
            if (root) {
                root.style.zIndex = '9999';
                root.classList.add('bw-dropmenu-open');
            }

            changeCss(this.items, 'opacity-0,hidden', 'remove');
            domEl(this.items).setAttribute('data-open', '1');
            this.reposition();
            this.bindItemActions();

            if (! this._outsideClickBound) {
                this._outsideClickBound = true;
                document.addEventListener('mousedown', (e) => {
                    const container = domEl(`.${this.name}`);
                    if (! container || container.contains(e.target)) {
                        return;
                    }
                    this.hide();
                });
            }

            if (! this._repositionBound) {
                this._repositionBound = true;
                this._onViewportChange = () => {
                    const itemsEl = domEl(this.items);
                    if (itemsEl && itemsEl.getAttribute('data-open') === '1') {
                        this.reposition();
                    }
                };
                window.addEventListener('resize', this._onViewportChange);
                window.addEventListener('scroll', this._onViewportChange, true);
            }
        };

        bindItemActions = () => {
            if (! this.shouldHideAfterClick()) {
                return;
            }

            domEls(`${this.items} .bw-item`)?.forEach((item) => {
                if (item.dataset.bwDropmenuBound === '1') {
                    return;
                }
                item.dataset.bwDropmenuBound = '1';
                item.addEventListener('click', () => {
                    // Defer hide so inline onclick / navigation can run first.
                    setTimeout(() => this.hide(), 0);
                });
            });
        };

        shouldHideAfterClick = () => {
            const value = this.options?.hideAfterClick;
            return value === true || value === 1 || value === '1' || value === 'true';
        };

        hide = () => {
            const root = domEl(`.${this.name}`);
            const itemsEl = domEl(this.items);
            if (! itemsEl) {
                return;
            }

            itemsEl.setAttribute('data-open', '0');
            changeCss(this.items, 'animate__fadeIn', 'remove');
            changeCss(this.items, 'animate__fadeOut');
            setTimeout(() => {
                changeCss(this.items, 'opacity-0,hidden,animate__fadeIn');
                changeCss(this.items, 'animate__fadeOut', 'remove');
                this.clearFixedPosition();
                if (root) {
                    root.style.zIndex = '';
                    root.classList.remove('bw-dropmenu-open');
                }
            }, 180);
        };

        clearFixedPosition = () => {
            const list = this.listEl();
            if (! list) {
                return;
            }
            list.style.position = '';
            list.style.top = '';
            list.style.left = '';
            list.style.right = '';
            list.style.bottom = '';
            list.style.margin = '';
            list.style.zIndex = '';
            list.style.visibility = '';
            list.classList.add('absolute');
            list.classList.remove('fixed');
        };

        /**
         * Position with position:fixed so overflow ancestors cannot clip the menu.
         * Prefer the configured side; flip when the viewport does not have room.
         *
         * data-position="right" → align to trigger's right edge (opens leftward)
         * data-position="left"  → align to trigger's left edge (opens rightward)
         */
        reposition = () => {
            const root = domEl(`.${this.name}`);
            const trigger = domEl(`.${this.name} .bw-trigger`);
            const list = this.listEl();
            if (! root || ! trigger || ! list) {
                return;
            }

            const preferred = (root.getAttribute('data-position') || 'right').toLowerCase();
            const pad = 8;
            const triggerRect = trigger.getBoundingClientRect();

            list.classList.remove('-left-1', '-right-1', 'left-0', 'right-0', 'mt-1', 'mb-1', 'absolute');
            list.classList.add('fixed');
            list.style.position = 'fixed';
            list.style.zIndex = '9999';
            list.style.margin = '0';
            list.style.right = 'auto';
            list.style.bottom = 'auto';
            list.style.top = '0px';
            list.style.left = '0px';
            list.style.visibility = 'hidden';

            void list.offsetWidth;
            const menuWidth = Math.max(list.offsetWidth, list.scrollWidth);
            const menuHeight = Math.max(list.offsetHeight, list.scrollHeight);
            list.style.visibility = '';

            let left;
            if (preferred === 'left') {
                // Open toward the right from the trigger's left edge.
                left = triggerRect.left;
                if (left + menuWidth > window.innerWidth - pad) {
                    left = triggerRect.right - menuWidth;
                }
            } else {
                // Open toward the left from the trigger's right edge.
                left = triggerRect.right - menuWidth;
                if (left < pad) {
                    left = triggerRect.left;
                }
            }

            // Final clamp so the menu never leaves the viewport.
            left = Math.min(Math.max(left, pad), Math.max(pad, window.innerWidth - pad - menuWidth));

            let top = triggerRect.bottom + 4;
            if (top + menuHeight > window.innerHeight - pad) {
                top = triggerRect.top - menuHeight - 4;
            }
            top = Math.min(Math.max(top, pad), Math.max(pad, window.innerHeight - pad - menuHeight));

            list.style.top = `${Math.round(top)}px`;
            list.style.left = `${Math.round(left)}px`;
        };

        toggle = () => {
            (domEl(this.items).getAttribute('data-open') === '0') ? this.show() : this.hide();
        };

        activate = () => {
            const trigger = domEl(`.${this.name} .bw-trigger`);
            if (! trigger) {
                return;
            }
            trigger.addEventListener(this.options.triggerOn || 'click', (event) => {
                event.preventDefault();
                event.stopPropagation();
                this.toggle();
            });
        };

        hasItems = () => {
            return !! domEls(`${this.items} .bw-item`);
        };
    };
})();
