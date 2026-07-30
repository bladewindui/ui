(function () {
    if (typeof window.BladewindDropmenu === 'undefined') {
        window.BladewindDropmenu = class BladewindDropmenu {
            name;
            items;
            options;

            constructor(name, options = {}) {
                this.name = name;
                this.items = `.${name} .bw-dropmenu-items`
                this.options = options;
                this.activate();
            }

            show = () => {
                // do this is only there are items
                if (this.hasItems()) {
                    changeCss(`.${this.name}`, 'z-50!');
                    changeCss(this.items, 'opacity-0,hidden', 'remove');
                    domEl(this.items).setAttribute('data-open', '1');
                    if (this.options.hideAfterClick && domEls(`${this.items} .bw-item`)) {
                        domEls(`${this.items} .bw-item`).forEach((item) => {
                            item.addEventListener('click', () => {
                                this.hide();
                            });
                        });
                    }
                    document.addEventListener('mouseup', (e) => {
                        let container = domEl(`.${this.name}`);
                        if (container && !container.contains(e.target)) this.hide();
                    });
                }
            }

            hide = () => {
                domEl(this.items).setAttribute('data-open', '0');
                changeCss(this.items, 'animate__fadeIn', 'remove');
                changeCss(this.items, 'animate__fadeOut');
                setTimeout(() => {
                    changeCss(this.items, 'opacity-0,hidden,animate__fadeIn');
                    changeCss(this.items, 'animate__fadeOut', 'remove');
                    changeCss(`.${this.name}`, 'z-50!', 'remove');
                    window.clearTimeout();
                }, 500);
            }

            reposition = () => {
                // TODO: reposition menu if too close to edge of browser window
            }

            toggle = () => {
                (domEl(this.items).getAttribute('data-open') === '0') ? this.show() : this.hide();
            }

            activate = () => {
                domEl(`.${this.name} .bw-trigger`).addEventListener(this.options.triggerOn, () => {
                    this.toggle();
                });
            }

            hasItems = () => {
                return domEls(`${this.items} .bw-item`);
            }
        }
    }
})();
