(function () {
    // Always (re)define so published updates replace a stale in-memory class.
    window.BladewindContextMenu = class BladewindContextMenu {
        name;
        root;
        region;
        itemsWrap;
        topList;
        openChain = [];

        constructor(name) {
            this.name = name;
            this.root = domEl(`.${name}`);
            if (! this.root) {
                return;
            }
            this.region = this.root.querySelector('.bw-context-menu-region');
            this.itemsWrap = this.root.querySelector('.bw-context-menu-items');
            this.topList = this.itemsWrap?.querySelector(':scope > .bw-items-list') || null;
            this._boundGlobal = false;
            this.activate();
        }

        activate = () => {
            if (! this.region || ! this.topList) {
                return;
            }

            this.region.addEventListener('contextmenu', (event) => {
                const disableNative = this.root.getAttribute('data-disable-native') === '1';
                if (! disableNative) {
                    return;
                }
                event.preventDefault();
                event.stopPropagation();
                this.open(event.clientX, event.clientY);
            });

            this.itemsWrap.addEventListener('click', (event) => this.onItemClick(event));
            this.itemsWrap.addEventListener('mouseover', (event) => this.onItemHover(event));
        };

        hasItems = () => !! this.topList.querySelector(':scope > [data-item="true"]');

        // ------------------------------------------------------------------
        // Open / close
        // ------------------------------------------------------------------

        open = (x, y) => {
            if (! this.hasItems()) {
                return;
            }

            this.closeSubmenusFrom(0);
            changeCss(`.${this.name} .bw-context-menu-items`, 'opacity-0,hidden', 'remove');
            this.itemsWrap.setAttribute('data-open', '1');
            this.itemsWrap.setAttribute('aria-hidden', 'false');
            this.positionAtPoint(this.topList, x, y);
            this.openChain = [{list: this.topList, parentItem: null}];
            this.focusFirst(this.topList);
            this.bindGlobalListeners();
        };

        close = () => {
            if (this.openChain.length === 0) {
                return;
            }
            this.closeSubmenusFrom(0);
            this.itemsWrap.setAttribute('data-open', '0');
            this.itemsWrap.setAttribute('aria-hidden', 'true');
            changeCss(`.${this.name} .bw-context-menu-items`, 'opacity-0,hidden');
            this.openChain = [];
        };

        isOpen = () => this.openChain.length > 0;

        // ------------------------------------------------------------------
        // Submenus
        // ------------------------------------------------------------------

        submenuOf = (item) => item.querySelector(':scope > .bw-context-menu-submenu');

        depthOf = (list) => this.openChain.findIndex((entry) => entry.list === list);

        closeSubmenusFrom = (depth) => {
            for (let i = this.openChain.length - 1; i > depth; i--) {
                const entry = this.openChain[i];
                entry.list.classList.add('hidden');
                entry.list.setAttribute('aria-hidden', 'true');
                entry.parentItem?.setAttribute('aria-expanded', 'false');
                this.openChain.pop();
            }
        };

        openSubmenu = (item, list) => {
            const submenu = this.submenuOf(item);
            if (! submenu) {
                return;
            }

            const depth = this.depthOf(list);
            this.closeSubmenusFrom(depth);

            if (submenu.classList.contains('hidden')) {
                submenu.classList.remove('hidden');
                submenu.setAttribute('aria-hidden', 'false');
                item.setAttribute('aria-expanded', 'true');
                this.positionSubmenu(submenu, item.getBoundingClientRect());
                this.openChain.push({list: submenu, parentItem: item});
            }

            this.focusFirst(submenu);
        };

        toggleSubmenu = (item, list) => {
            const submenu = this.submenuOf(item);
            if (submenu && ! submenu.classList.contains('hidden')) {
                this.closeSubmenusFrom(this.depthOf(list));
                item.focus();
                return;
            }
            this.openSubmenu(item, list);
        };

        // ------------------------------------------------------------------
        // Positioning
        // ------------------------------------------------------------------

        positionAtPoint = (list, x, y) => {
            const pad = 8;
            list.style.visibility = 'hidden';
            list.style.top = '0px';
            list.style.left = '0px';
            void list.offsetWidth;
            const width = Math.max(list.offsetWidth, list.scrollWidth);
            const height = Math.max(list.offsetHeight, list.scrollHeight);
            list.style.visibility = '';

            let left = x;
            if (left + width > window.innerWidth - pad) {
                left = x - width;
            }
            left = Math.min(Math.max(left, pad), Math.max(pad, window.innerWidth - pad - width));

            let top = y;
            if (top + height > window.innerHeight - pad) {
                top = y - height;
            }
            top = Math.min(Math.max(top, pad), Math.max(pad, window.innerHeight - pad - height));

            list.style.top = `${Math.round(top)}px`;
            list.style.left = `${Math.round(left)}px`;
        };

        positionSubmenu = (submenu, anchorRect) => {
            const pad = 8;
            const gap = 2;
            submenu.style.visibility = 'hidden';
            submenu.style.top = '0px';
            submenu.style.left = '0px';
            void submenu.offsetWidth;
            const width = Math.max(submenu.offsetWidth, submenu.scrollWidth);
            const height = Math.max(submenu.offsetHeight, submenu.scrollHeight);
            submenu.style.visibility = '';

            let left = anchorRect.right + gap;
            if (left + width > window.innerWidth - pad) {
                left = anchorRect.left - width - gap;
            }
            left = Math.min(Math.max(left, pad), Math.max(pad, window.innerWidth - pad - width));

            let top = anchorRect.top - 4;
            if (top + height > window.innerHeight - pad) {
                top = window.innerHeight - pad - height;
            }
            top = Math.max(top, pad);

            submenu.style.top = `${Math.round(top)}px`;
            submenu.style.left = `${Math.round(left)}px`;
        };

        // ------------------------------------------------------------------
        // Focus / roving tabindex
        // ------------------------------------------------------------------

        itemsIn = (list) => Array.from(list.querySelectorAll(':scope > [data-item="true"]'))
            .filter((el) => el.dataset.disabled !== '1');

        focusFirst = (list) => this.focusAt(list, 0);

        focusLast = (list) => {
            const items = this.itemsIn(list);
            this.focusAt(list, items.length - 1);
        };

        focusAt = (list, index) => {
            const items = this.itemsIn(list);
            if (items.length === 0) {
                return;
            }
            const clamped = Math.max(0, Math.min(index, items.length - 1));
            items.forEach((el, i) => el.setAttribute('tabindex', i === clamped ? '0' : '-1'));
            items[clamped].focus();
        };

        moveFocus = (list, delta) => {
            const items = this.itemsIn(list);
            if (items.length === 0) {
                return;
            }
            const current = items.findIndex((el) => el === document.activeElement);
            const next = current === -1
                ? (delta > 0 ? 0 : items.length - 1)
                : (current + delta + items.length) % items.length;
            this.focusAt(list, next);
        };

        // ------------------------------------------------------------------
        // Event handling
        // ------------------------------------------------------------------

        onItemClick = (event) => {
            const item = event.target.closest('[data-item="true"]');
            if (! item || item.dataset.disabled === '1') {
                return;
            }
            const list = item.closest('.bw-items-list, .bw-context-menu-submenu');
            if (item.getAttribute('aria-haspopup') === 'menu') {
                event.preventDefault();
                event.stopPropagation();
                this.toggleSubmenu(item, list);
                return;
            }
            // a leaf item: let any consumer onclick/wire:click on it run first,
            // then close the whole menu.
            setTimeout(() => this.close(), 0);
        };

        onItemHover = (event) => {
            const item = event.target.closest('[data-item="true"]');
            if (! item || item.dataset.disabled === '1') {
                return;
            }
            const list = item.closest('.bw-items-list, .bw-context-menu-submenu');
            if (item.getAttribute('aria-haspopup') === 'menu') {
                clearTimeout(this._hoverTimer);
                this._hoverTimer = setTimeout(() => this.openSubmenu(item, list), 120);
            } else {
                this.closeSubmenusFrom(this.depthOf(list));
            }
        };

        currentList = () => this.openChain[this.openChain.length - 1]?.list || null;

        onKeydown = (event) => {
            if (! this.isOpen()) {
                return;
            }
            const list = this.currentList();
            const focused = document.activeElement;
            const item = focused?.closest?.('[data-item="true"]');

            switch (event.key) {
                case 'ArrowDown':
                    event.preventDefault();
                    this.moveFocus(list, 1);
                    break;
                case 'ArrowUp':
                    event.preventDefault();
                    this.moveFocus(list, -1);
                    break;
                case 'Home':
                    event.preventDefault();
                    this.focusFirst(list);
                    break;
                case 'End':
                    event.preventDefault();
                    this.focusLast(list);
                    break;
                case 'ArrowRight':
                    if (item && item.getAttribute('aria-haspopup') === 'menu') {
                        event.preventDefault();
                        this.openSubmenu(item, list);
                    }
                    break;
                case 'ArrowLeft':
                    if (this.openChain.length > 1) {
                        event.preventDefault();
                        const parentItem = this.openChain[this.openChain.length - 1].parentItem;
                        this.closeSubmenusFrom(this.openChain.length - 2);
                        parentItem?.focus();
                    }
                    break;
                case 'Enter':
                case ' ':
                    if (item) {
                        event.preventDefault();
                        if (item.getAttribute('aria-haspopup') === 'menu') {
                            this.openSubmenu(item, list);
                        } else {
                            item.click();
                        }
                    }
                    break;
                case 'Escape':
                    event.preventDefault();
                    if (this.openChain.length > 1) {
                        const parentItem = this.openChain[this.openChain.length - 1].parentItem;
                        this.closeSubmenusFrom(this.openChain.length - 2);
                        parentItem?.focus();
                    } else {
                        this.close();
                    }
                    break;
                case 'Tab':
                    this.close();
                    break;
            }
        };

        bindGlobalListeners = () => {
            if (this._boundGlobal) {
                return;
            }
            this._boundGlobal = true;

            document.addEventListener('mousedown', (event) => {
                if (! this.isOpen()) {
                    return;
                }
                if (this.root.contains(event.target)) {
                    return;
                }
                this.close();
            });

            document.addEventListener('keydown', (event) => this.onKeydown(event));

            const onViewportChange = () => {
                if (this.isOpen()) {
                    this.close();
                }
            };
            window.addEventListener('resize', onViewportChange);
            window.addEventListener('scroll', onViewportChange, true);
        };
    };
})();
