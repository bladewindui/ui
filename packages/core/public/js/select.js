(function () {
    if (typeof window.BladewindSelect === 'undefined') {
        window.BladewindSelect = class BladewindSelect {
            clickArea;
            rootElement;
            itemsContainer;
            searchInput;
            selectItems;
            isMultiple;
            required;
            displayArea;
            formInput;
            maxSelection;
            toFilter;
            filterBy;
            selectedValue;
            canClear;
            enabled;
            metaData;
            skipReset;
            typeAheadBuffer;
            typeAheadTimer;

            constructor(name, placeholder) {
                this.name = name;
                this.placeholder = placeholder || 'Select One';
                this.rootElement = this.strRootElement();
                this.clickArea = `${this.rootElement} .clickable`;
                this.displayArea = `${this.rootElement} .display-area`;
                this.itemsContainer = `${this.rootElement} .bw-select-items-container`;
                this.searchInput = `${this.itemsContainer} .bw_search`;
                this.selectItems = this.strSelectItems();
                this.isMultiple = (domEl(this.rootElement).getAttribute('data-multiple') === 'true');
                this.required = (domEl(this.rootElement).getAttribute('data-required') === 'true');
                this.formInput = `input.bw-${this.name}`;
                domEl(this.displayArea).style.maxWidth = `${(domEl(this.rootElement).offsetWidth - 40)}px`;
                this.maxSelection = -1;
                this.canClear = (!this.required && !this.isMultiple);
                this.enabled = true;
                this.selectedItem = null;
                this.metaData = domEl(this.rootElement).getAttribute('data-meta-data') || null;
                this.typeAheadBuffer = '';
                this.typeAheadTimer = null;
            }

            activate = (options = {}) => {
                if (options.disabled !== '1' && options.readonly !== '1') {
                    domEl(this.clickArea).addEventListener('click', (e) => {
                        unhide(this.itemsContainer);
                        this.positionItems();
                    });
                    this.hide();
                    this.search();
                    this.manualModePreSelection();
                    this.selectItem();
                    this.enableKeyboardNavigation();
                    this.setEmptyStateMessage();
                    this.prepareAccessibility();
                } else {
                    this.selectItem();
                    this.enabled = false;
                }
            }

            /**
             * aria-activedescendant has to point at an id, so every option needs
             * one. They are assigned here rather than in the blade template
             * because a value is only unique within its own select.
             */
            prepareAccessibility = () => {
                domEls(this.selectItems).forEach((el, index) => {
                    if (!el.id) el.id = `bw-option-${this.name}-${index}`;
                });

                // the expanded state is only true while the list is actually open
                let container = domEl(this.itemsContainer);
                if (!container) return;

                // only aria here. positioning is driven from show/hide directly:
                // positionItems() changes the very class attribute this watches,
                // so doing it here feeds the observer its own mutations.
                new MutationObserver(() => {
                    let trigger = domEl(this.clickArea);
                    if (trigger) {
                        trigger.setAttribute('aria-expanded', String(!container.classList.contains('hidden')));
                    }
                }).observe(container, {attributes: true, attributeFilter: ['class']});

                if (!this._repositionBound) {
                    this._repositionBound = true;
                    let onViewportChange = () => {
                        if (!container.classList.contains('hidden')) this.positionItems();
                    };
                    window.addEventListener('resize', onViewportChange);
                    // capture phase, so scrolling any ancestor is caught too
                    window.addEventListener('scroll', onViewportChange, true);
                }
            }

            /**
             * Position the list with position:fixed against the trigger's bounding
             * rect, rather than absolutely inside the component's own subtree.
             *
             * An absolutely positioned list is clipped by any ancestor that
             * establishes a scroll container — and the overwhelmingly common one is
             * the overflow-x-auto wrapper every wide table needs, where overflow-x:
             * auto silently computes overflow-y to auto and clips vertically too.
             * Fixed positioning takes the list out of that flow entirely. Same
             * approach dropmenu already uses. See #591.
             */
            positionItems = () => {
                let root = domEl(this.rootElement);
                let container = domEl(this.itemsContainer);
                if (!root || !container) return;

                let rect = root.getBoundingClientRect();

                container.classList.remove('absolute');
                container.classList.add('fixed');
                container.style.width = `${rect.width}px`;
                container.style.left = `${rect.left}px`;
                container.style.zIndex = '9999';

                // flip above the trigger when the space below cannot hold the list
                let height = container.offsetHeight || 0;
                let below = window.innerHeight - rect.bottom;

                if (height > below && rect.top > below) {
                    container.style.top = `${Math.max(0, rect.top - height)}px`;
                } else {
                    // -mt-1.5 in the markup tucks the list under the trigger's border
                    container.style.top = `${rect.bottom - 6}px`;
                }
            }

            clearItemsPosition = () => {
                let container = domEl(this.itemsContainer);
                if (!container) return;

                container.classList.remove('fixed');
                container.classList.add('absolute');
                ['width', 'left', 'top', 'zIndex'].forEach((property) => {
                    container.style[property] = '';
                });
            }

            enableKeyboardNavigation = () => {
                domEl(this.rootElement).addEventListener('keydown', (e) => {
                    if (e.key === "Enter") {
                        if (!this.selectedItem) {
                            e.preventDefault();
                            unhide(this.itemsContainer);
                            this.positionItems();
                            domEl(this.searchInput).focus();
                        } else {
                            hide(this.itemsContainer);
                            this.clearItemsPosition();
                        }
                    }
                    if (e.key === "Tab" || e.key === "Escape") {
                        hide(this.itemsContainer);
                        this.clearItemsPosition();
                    }

                    if (e.key === "ArrowDown" || e.key === "ArrowUp" || e.key === "Home" || e.key === "End") {
                        e.preventDefault();
                        let els = [...domEls(this.selectItems)].filter((el) => {
                            if (el.classList.contains('hidden')) {
                                return false;
                            }

                            return el.getAttribute('data-unselectable') === null;
                        });

                        let next;
                        if (e.key === 'Home') {
                            next = els[0];
                        } else if (e.key === 'End') {
                            next = els[els.length - 1];
                        } else if (!this.selectedItem) {
                            next = e.key === 'ArrowDown' ? els[0] : els[els.length - 1];
                        } else {
                            let idx = els.indexOf(this.selectedItem);

                            next = e.key === 'ArrowDown' ? els[idx + 1] : els[idx - 1];
                        }

                        // already at either end of the list, or there is nothing to move to
                        if (!next) return;

                        this.selectedItem = next;
                        this.highlightItem(this.selectedItem);
                        this.setValue(this.selectedItem);
                        this.callUserFunction(this.selectedItem);
                    }

                    if (this.isTypeAheadKey(e)) this.typeAhead(e.key);
                });
            }

            /**
             * Printable characters typed while the select has focus are used to
             * jump to an item. Anything typed in the search box is left alone.
             */
            isTypeAheadKey = (e) => {
                if (e.key.length !== 1 || e.ctrlKey || e.metaKey || e.altKey) return false;

                return !e.target.classList.contains('bw_search');
            }

            /**
             * Jump to the first selectable item whose label starts with what has been
             * typed so far. Typing 'gha' selects Ghana without opening the select.
             * The typed characters are forgotten after a short pause.
             */
            typeAhead = (key) => {
                clearTimeout(this.typeAheadTimer);
                this.typeAheadBuffer += key.toLowerCase();
                this.typeAheadTimer = setTimeout(() => this.typeAheadBuffer = '', 700);

                let match = [...domEls(this.selectItems)].find((el) => {
                    if (el.classList.contains('hidden')) return false;
                    if (el.getAttribute('data-unselectable') !== null) return false;

                    return (el.getAttribute('data-label') || '').toLowerCase().startsWith(this.typeAheadBuffer);
                });

                if (!match) return;

                this.selectedItem = match;
                this.highlightItem(match);
                match.scrollIntoView({block: 'nearest'});

                // in multiple mode setValue toggles, so a second pass over an
                // already selected item would silently unselect it
                if (this.isMultiple && domEl(this.formInput).value.split(',').includes(match.getAttribute('data-value'))) return;

                this.setValue(match);
                this.callUserFunction(match);
            }

            highlightItem = (item) => {
                changeCssForDomArray(`${this.rootElement} .bw-select-item`, 'bg-slate-100/90', 'remove');
                changeCss(item, 'bg-slate-100/90', 'add', true);

                let trigger = domEl(this.clickArea);
                if (trigger && item && item.id) trigger.setAttribute('aria-activedescendant', item.id);

                // single select: only one option can carry aria-selected
                if (!this.isMultiple) {
                    domEls(this.selectItems).forEach((el) => el.setAttribute('aria-selected', 'false'));
                }
                if (item) item.setAttribute('aria-selected', 'true');
            }


            clearable = () => {
                this.canClear = true;
            }

            hide = () => {
                document.addEventListener('mouseup', (e) => {
                    let searchArea = domEl(this.searchInput);
                    let container = domEl((this.isMultiple) ? this.itemsContainer : this.clickArea);
                    if (searchArea && container && !searchArea.contains(e.target) && !container.contains(e.target)) {
                        hide(this.itemsContainer);
                        this.clearItemsPosition();
                    }
                });
            }

            search = () => {
                domEl(this.searchInput).addEventListener('keyup', (e) => {
                    let searchScope = domEl(this.rootElement).getAttribute('data-filter-by') || null;
                    let value = (domEl(this.searchInput).value);
                    let items = (searchScope) ? `${this.selectItems}[data-filter-value="${searchScope}"]` : this.selectItems;
                    domEls(items).forEach((el) => {
                        (el.innerText.toLowerCase().indexOf(value.toLowerCase()) !== -1) ?
                            unhide(el, true) :
                            hide(el, true);
                    });
                    this.setEmptyStateMessage();
                });
            }

            /**
             * When using non-dynamic selects, ensure select_value=<value>
             * works the same way as for dynamic selects. This saves the user from
             * manually checking if each select-item should be selected or not.
             */
            manualModePreSelection = () => {
                let selectMode = domEl(`${this.rootElement}`).getAttribute('data-type');
                let selectedValue = domEl(`${this.rootElement}`).getAttribute('data-selected-value');
                if (selectMode === 'manual' && selectedValue !== null) {
                    domEls(this.selectItems).forEach((el) => {
                        let item_value = el.getAttribute('data-value');
                        if (item_value === selectedValue) el.setAttribute('data-selected', true);
                    });
                }
            }

            setEmptyStateMessage = (element = this.name) => {
                const root = domEl(this.strRootElement(element));
                const copyFrom = root?.getAttribute('data-copy-empty-state-from');
                const source = domEl(`.bw-empty-state.${copyFrom}`);
                const target = domEl(this.strSelectItems(element, ' div.empty-state-copy'));

                if (copyFrom && source && target) {
                    target.innerHTML = source.innerHTML;
                }

                this.showHideEmptyState(element);
            }

            totalItems = (element = this.name) => {
                return this.items(element)?.length || 0;
            }

            items = (element = this.name) => {
                return domEls(this.strSelectItems(element, `:not(.hidden):not(.empty-state)`));
            }

            showHideEmptyState = (element = this.name) => {
                const emptyStateItem = domEl(this.strSelectItems(element, '.empty-state'));
                const searchBar = `${this.strRootElement(element)} .search-bar`;
                const hasNoItems = this.totalItems(element) === 0;

                if (!emptyStateItem) return;

                hasNoItems ? unhide(emptyStateItem, true) : hide(emptyStateItem, true);

                if (this.isSearchable(element)) {
                    // hasNoItems ? hide(searchBar) : unhide(searchBar);
                }
            }

            selectItem = () => {
                domEls(this.selectItems).forEach((el) => {
                    let selected = (el.getAttribute('data-selected') !== null);
                    if (selected) this.setValue(el);
                    let isSelectable = (el.getAttribute('data-unselectable') === null);
                    if (isSelectable) {
                        el.addEventListener('click', () => {
                            this.setValue(el);
                            this.callUserFunction(el);
                        });
                    }
                });
            }

            moveLabel = (direction = 'up') => {
                let placeholderElement = domEl(`${this.rootElement} .placeholder`);
                let labelElement = domEl(`${this.rootElement} .placeholder .form-label`);
                if (labelElement) {
                    if (direction === 'up') {
                        changeCss(labelElement, '!top-[13px]', 'remove', true);
                    } else {
                        changeCss(labelElement, '!top-[13px]', 'add', true);
                    }
                    unhide(placeholderElement, true);
                }
            }

            setValue = (item) => {
                this.selectedValue = item ? item.getAttribute('data-value') : null;
                let selectedLabel = item ? item.getAttribute('data-label') : null;
                let svg = domEl(`${this.rootElement} div[data-value="${this.selectedValue}"] svg`);
                let input = domEl(this.formInput);

                if (this.toFilter && !this.skipReset) {
                    this.filter(this.toFilter, this.selectedValue, false);
                }

                hide(`${this.rootElement} .placeholder`);
                unhide(this.displayArea);

                if (this.enabled) {
                    if (!this.isMultiple) {
                        changeCssForDomArray(`${this.selectItems} svg`, 'hidden');
                        domEl(this.displayArea).innerText = selectedLabel;
                        input.value = this.selectedValue;
                        unhide(svg, true);
                        this.moveLabel();
                        if (this.canClear) {
                            unhide(`${this.clickArea} .reset`);
                            domEl(`${this.clickArea} .reset`).addEventListener('click', (e) => {
                                this.unsetValue(item);
                                e.stopImmediatePropagation();
                            });
                        }
                    } else {
                        if (input.value.includes(this.selectedValue)) {
                            this.unsetValue(item);
                        } else {
                            if (!this.maxSelectableExceeded()) {
                                unhide(svg, true);
                                input.value += `,${this.selectedValue}`;
                                domEl(this.displayArea).innerHTML += this.labelTemplate(selectedLabel, this.selectedValue);
                                this.removeLabel(this.selectedValue);
                            } else {
                                showNotification('', this.maxSelectionError, 'error');
                            }
                            this.moveLabel();
                        }
                        this.scrollbars();
                    }
                    stripComma(input);
                    changeCss(`${this.clickArea}`, 'has-error', 'remove');
                }
            }

            unsetValue = (item) => {
                this.selectedValue = item ? item.getAttribute('data-value') : null;
                // let selectedValue = item.getAttribute('data-value');
                let svg = domEl(`${this.rootElement} div[data-value="${this.selectedValue}"] svg`);
                let input = domEl(this.formInput);

                // only unset values if the Select component is not disabled
                if (this.enabled) { //!domEl(this.clickArea).classList.contains('disabled')
                    if (!this.isMultiple) {
                        unhide(`${this.rootElement} .placeholder`);
                        changeCssForDomArray(`${this.selectItems} svg`, 'hidden');
                        domEl(this.displayArea).innerText = '';
                        input.value = '';
                        hide(this.displayArea);
                        hide(`${this.clickArea} .reset`);
                        this.moveLabel('down');
                    } else {
                        if (domEl(`${this.displayArea} span.bw-sp-${this.selectedValue}`)) {
                            let keyword = `(,?)${this.selectedValue}`;
                            input.value = input.value.replace(input.value.match(keyword)[0], '');
                            hide(svg, true);
                            domEl(`${this.displayArea} span.bw-sp-${this.selectedValue}`).remove();
                            if (domEl(this.displayArea).innerText === '') {
                                unhide(`${this.rootElement} .placeholder`);
                                hide(this.displayArea);
                                this.moveLabel('down');
                            }
                        }
                    }
                    stripComma(input);
                    this.callUserFunction(item);
                    if (this.toFilter) {
                        (new BladewindSelect(this.toFilter, '')).reset(); //FIXME: dont new up an instance
                        this.clearFilter(this.toFilter);
                    }
                }
            }

            scrollbars = () => {
                if (domEl(this.displayArea).scrollWidth > domEl(this.rootElement).clientWidth) {
                    unhide(`${this.clickArea} .scroll-left`);
                    unhide(`${this.clickArea} .scroll-right`);
                    domEl(`${this.clickArea} .scroll-right`).addEventListener('click', (e) => {
                        this.scroll(150);
                        e.stopImmediatePropagation();
                    });
                    domEl(`${this.clickArea} .scroll-left`).addEventListener('click', (e) => {
                        this.scroll(-150);
                        e.stopImmediatePropagation();
                    });
                } else {
                    hide(`${this.clickArea} .scroll-left`);
                    hide(`${this.clickArea} .scroll-right`);
                }
            }

            scroll = (amount) => {
                domEl(this.displayArea).scrollBy(amount, 0);
                ((domEl(this.displayArea).clientWidth + domEl(this.displayArea).scrollLeft) >= domEl(this.displayArea).scrollWidth) ?
                    hide(`${this.clickArea} .scroll-right`) :
                    unhide(`${this.clickArea} .scroll-right`);
                (domEl(this.displayArea).scrollLeft === 0) ?
                    hide(`${this.clickArea} .scroll-left`) :
                    unhide(`${this.clickArea} .scroll-left`);
            }

            labelTemplate = (label, value) => {
                return `<span class="bg-slate-200 hover:bg-slate-300 dark:bg-dark-800 dark:hover:bg-dark-900 
                    inline-flex items-center text-slate-700 dark:text-dark-300 py-[2.5px] pl-2.5 pr-1 ` +
                    `mr-2 text-sm rounded-md bw-sp-${value} animate__animated animate__bounceIn animate__faster" ` +
                    `onclick="event.stopPropagation();window.event.cancelBubble = true">${label}` +
                    `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" 
                    stroke="currentColor" class="!size-4 p-0.5 rounded-full  bg-gray-400 hover:bg-gray-600 text-slate-100 
                    dark:bg-dark-500 dark:hover:bg-dark-600 dark:text-dark-300 ml-1" data-value="${value}">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg></span>`;
            }

            removeLabel = () => {
                domEls(`${this.displayArea} span svg`).forEach((el) => {
                    el.addEventListener('click', (e) => {
                        let value = el.getAttribute('data-value');
                        this.unsetValue(domEl(`.bw-select-item[data-value="${value}"]`));
                    });
                });
            }

            selectByValue = (value) => {
                domEls(this.selectItems).forEach((el) => {
                    if (el.getAttribute('data-value') === value) this.setValue(el);
                });
            }

            reset = () => {
                if (this.enabled) {
                    domEls(this.selectItems).forEach((el) => {
                        this.unsetValue(el);
                    });
                    hide(this.displayArea);
                    unhide(this.placeholder);
                }
            }

            disable = () => {
                changeCss(this.clickArea, 'disabled');
                changeCss(this.clickArea, 'enabled, readonly', 'remove');
                // hide(`${this.clickArea} .reset`);
                domEl(this.clickArea).addEventListener('click', () => {
                    hide(this.itemsContainer);
                    this.clearItemsPosition();
                });
                this.enabled = false;
            }

            enable = () => {
                changeCss(this.clickArea, 'readonly, disabled', 'remove');
                changeCss(this.clickArea, 'enabled');
                domEl(this.clickArea).addEventListener('click', (e) => {
                    unhide(this.itemsContainer);
                    this.positionItems();
                });
                this.enabled = true;
            }

            callUserFunction = (item) => {
                let userFunction = item ? item.getAttribute('data-user-function') : null;
                if (userFunction !== null && userFunction !== undefined) {
                    let meta = (this.metaData) ? JSON.parse(JSON.stringify(this.metaData)) : null;
                    callUserFunction(
                        `${userFunction}(
                '${item.getAttribute('data-value')}',
                '${item.getAttribute('data-label')}',
                '${domEl(this.formInput).value}',
                ${meta})`
                    );
                }
            }

            maxSelectable = (max_number, error_message) => {
                this.maxSelection = (this.isMultiple) ? max_number : false;
                this.maxSelectionError = error_message;
            }

            maxSelectableExceeded = () => {
                let input = domEl(this.formInput);
                let totalSelected = (input.value.split(',')).length;
                return ((this.maxSelection !== -1) && totalSelected === this.maxSelection);
            }

            strSelectItems = (name = this.name, options = '') => {
                return `.bw-select-${name} .bw-select-items .bw-select-item${options}`;
            }

            strRootElement = (name = this.name) => {
                return (name.includes('bw-select')) ? name : `.bw-select-${name}`;
            }

            isSearchable = (name = this.name) => {
                return domEl(this.strRootElement(name)).className.includes('searchable');
            }

            filter = (element, by = '', skipReset = true) => {
                this.toFilter = element || this.name;
                this.filterBy = by;
                this.skipReset = skipReset;
                if (by !== '') {
                    domEls(this.strSelectItems(this.toFilter, `:not(.empty-state)`)).forEach((el) => {
                        const filterValue = el.getAttribute('data-filter-value');
                        (filterValue === by) ? unhide(el, true) : hide(el, true);
                    });
                    domEl(`.bw-select-${this.toFilter}`).setAttribute('data-filter-by', by);
                    this.setEmptyStateMessage(element);
                }
            }

            clearFilter = (element, by = '') => {
                if (element) {
                    const elementItems = this.strSelectItems(element);
                    if (by === '') { // clear all filters
                        domEls(elementItems).forEach((el) => {
                            unhide(el, true);
                        });
                    } else { // clear specific values' filters
                        domEls(elementItems).forEach((el) => {
                            const filterValue = el.getAttribute('data-filter-value');
                            (filterValue === this.selectedValue) ? hide(el, true) : unhide(el, true);
                        });
                    }
                }
            }

            addItem = (value, label) => {
                const container = domEl(`${this.rootElement} .bw-select-items`);
                if (!container) return;

                const item = document.createElement('div');
                item.className = 'bw-select-item';
                item.setAttribute('data-value', value);
                item.setAttribute('data-label', label);
                item.innerHTML = `${label} <svg class="hidden"><!-- your checkmark SVG --></svg>`;

                container.appendChild(item);
                this.selectItem(); // Rebind events
            }

            removeItem = ({value = null, index = null}) => {
                let item = null;
                if (value !== null) {
                    item = domEl(`${this.rootElement} .bw-select-item[data-value="${value}"]`);
                } else if (index !== null) {
                    const items = domEls(`${this.rootElement} .bw-select-item`);
                    item = items[index];
                }
                if (item) item.remove();
            }

            itemExists = (value) => {
                return domEl(`${this.rootElement} .bw-select-item[data-value="${value}"]`) !== false;
            }

        }
    }
})();

/*
 | The search box used to carry inline onfocus/onblur that ringed the trigger.
 | Delegated here instead, so a strict CSP does not disable it. focus and blur do
 | not bubble, hence capture. See #608.
 */
(() => {
    const ring = '!border-2, !outline-2, !-outline-offset-1, !outline-primary-500, ' +
        '!border-primary-500, dark:!border-primary-500, dark:!outline-primary-500';

    const toggle = (input, action) => {
        const name = input.getAttribute('data-bw-select-search');
        changeCss(`.bw-select-${name} .clickable`, ring, action);
    };

    bwOn('focus', '[data-bw-select-search]', (input) => toggle(input, 'add'), {capture: true});
    bwOn('blur', '[data-bw-select-search]', (input) => toggle(input, 'remove'), {capture: true});
})();

