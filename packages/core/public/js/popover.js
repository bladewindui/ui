(function () {
    if (typeof window.BladewindPopover === 'undefined') {
        window.BladewindPopover = class BladewindPopover {
            name;
            content;
            options;

            constructor(name, options = {}) {
                this.name = name;
                this.content = `.${name} .bw-popover-content`
                this.options = options;
                this._outsideClickBound = false;
                this._repositionBound = false;
                this.activate();
            }

            show = () => {
                changeCss(this.content, 'opacity-0,hidden', 'remove');
                domEl(this.content).setAttribute('data-open', '1');
                domEl(`.${this.name} .bw-trigger`).setAttribute('aria-expanded', 'true');
                this.reposition();

                if (! this._outsideClickBound) {
                    this._outsideClickBound = true;
                    document.addEventListener('mouseup', (e) => {
                        let container = domEl(`.${this.name}`);
                        if (container && !container.contains(e.target)) this.hide();
                    });
                }

                if (! this._repositionBound) {
                    this._repositionBound = true;
                    this._onViewportChange = () => {
                        let content = domEl(this.content);
                        if (content && content.getAttribute('data-open') === '1') this.reposition();
                    };
                    window.addEventListener('resize', this._onViewportChange);
                    // capture phase, so scrolling any ancestor is caught too
                    window.addEventListener('scroll', this._onViewportChange, true);
                }
            }

            /**
             * Position with position:fixed against the trigger's bounding rect,
             * rather than absolutely inside the component's own subtree.
             *
             * An absolutely positioned panel is clipped by any ancestor that
             * establishes a scroll container — most often the overflow-x-auto
             * wrapper a wide table needs, where overflow-x: auto silently computes
             * overflow-y to auto and clips vertically too. Same approach dropmenu
             * and select already use. See #591.
             */
            reposition = () => {
                let trigger = domEl(`.${this.name} .bw-trigger`);
                let content = domEl(this.content);
                if (! trigger || ! content) return;

                let rect = trigger.getBoundingClientRect();
                let width = content.offsetWidth || 0;
                let height = content.offsetHeight || 0;
                let gap = 8;

                content.classList.remove('absolute');
                content.classList.add('fixed');
                // the utility classes place it relative to the trigger; fixed
                // positioning supplies the coordinates instead
                content.style.transform = 'none';
                content.style.zIndex = '9999';

                let position = this.options?.position || 'bottom';
                let top, left;

                if (position === 'top') {
                    top = rect.top - height - gap;
                    left = rect.left + (rect.width / 2) - (width / 2);
                } else if (position === 'left') {
                    top = rect.top + (rect.height / 2) - (height / 2);
                    left = rect.left - width - gap;
                } else if (position === 'right') {
                    top = rect.top + (rect.height / 2) - (height / 2);
                    left = rect.right + gap;
                } else {
                    top = rect.bottom + gap;
                    left = rect.left + (rect.width / 2) - (width / 2);
                }

                // flip vertically rather than run off the viewport
                if (position === 'bottom' && top + height > window.innerHeight && rect.top > height) {
                    top = rect.top - height - gap;
                } else if (position === 'top' && top < 0 && (window.innerHeight - rect.bottom) > height) {
                    top = rect.bottom + gap;
                }

                content.style.top = `${Math.max(0, top)}px`;
                content.style.left = `${Math.max(0, Math.min(left, window.innerWidth - width))}px`;
            }

            clearPosition = () => {
                let content = domEl(this.content);
                if (! content) return;

                content.classList.remove('fixed');
                content.classList.add('absolute');
                ['top', 'left', 'zIndex', 'transform'].forEach((property) => {
                    content.style[property] = '';
                });
            }

            hide = () => {
                domEl(this.content).setAttribute('data-open', '0');
                domEl(`.${this.name} .bw-trigger`).setAttribute('aria-expanded', 'false');
                changeCss(this.content, 'animate__fadeIn', 'remove');
                changeCss(this.content, 'animate__fadeOut');
                setTimeout(() => {
                    changeCss(this.content, 'opacity-0,hidden,animate__fadeIn');
                    changeCss(this.content, 'animate__fadeOut', 'remove');
                    this.clearPosition();
                }, 500);
            }

            toggle = () => {
                (domEl(this.content).getAttribute('data-open') === '0') ? this.show() : this.hide();
            }

            activate = () => {
                domEl(`.${this.name} .bw-trigger`).addEventListener(this.options.triggerOn, () => {
                    this.toggle();
                });
            }
        }
    }
})();
