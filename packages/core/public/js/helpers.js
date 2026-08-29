/**
 * ----------------------------------------------
 * Helper functions for BladeWindUI components
 * ----------------------------------------------
 */

const openModals = [];
const openDrawers = [];
const drawerReturnFocus = new Map();
/**
 * Shortcut for document.querySelector.
 * @param {string} element - The element to find in the DOM.
 * @return {(Element|boolean)} The matching DOM element.
 * @see {@link https://bladewindui.com/extra/helper-functions#domel}
 */
const domEl = (element) => {
    return (document.querySelector(element) !== null) ? document.querySelector(element) : false;
};

/**
 * Alias for domEl(element)
 */
const dom_el = domEl;

/**
 * Shortcut for document.querySelectorAll.
 * @param {string} element - The element(s) to find in the DOM.
 * @param scope
 * @return {NodeListOf<*>|boolean} The collection of DOM elements.
 * @see {@link https://bladewindui.com/extra/helper-functions#domels}
 */
const domEls = (element, scope = null) => {
    if (scope) {
        if (typeof scope === 'string') {
            if (!scope.includes('.') && !scope.includes('#')) {
                console.log(`${scope} needs to contain . or # to target a DOM element`);
            }
            scope = document.querySelector(scope);
        }
        return scope.querySelectorAll(element);
    }
    // an empty NodeList, not false. returning false meant every
    // `domEls(...).forEach(...)` in the library — 23 of them — was a TypeError
    // waiting for a page where the selector happened to match nothing, and one of
    // those runs at load time. See #595.
    return document.querySelectorAll(element);
};

/**
 * Alias for domEls(element)
 */
const dom_els = domEls;

/**
 * Check to see if val is empty
 * @param {string} val - The string to test emptiness for
 * @return {boolean} True if string is empty
 */
const isEmpty = (val) => {
    let regex = /^\s*$/;
    return regex.test(val);
};

/**
 * Check if this is a number
 * @param value The value to test
 * @return {boolean} True if string is empty
 */
const isNumeric = (value) => {
    return !isNaN(value) && !isNaN(parseFloat(value));
};

/**
 * Hide an element.
 * @param {Element|boolean} element - The css class (name) of the element to hide.
 * @param {boolean} elementIsDomObject - If true, <element> will not be treated as a string but DOM element.
 * @return {void}
 * @see {@link https://bladewindui.com/extra/helper-functions#hide}
 */
const hide = (element, elementIsDomObject = false) => {
    if ((!elementIsDomObject && domEl(element) != null) || (elementIsDomObject && element != null)) {
        changeCss(element, 'hidden', 'add', elementIsDomObject);
    }
};
/**
 * Display an element.
 * @param {Object|boolean} element - The css class (name) of the element to hide.
 * @param {boolean} elementIsDomObject - If true, <element> will not be treated as a string but DOM element.
 * @return {void}
 * @see {@link https://bladewindui.com/extra/helper-functions#unhide}
 */
const unhide = (element, elementIsDomObject = false) => {
    if ((!elementIsDomObject && domEl(element) != null) || (elementIsDomObject && element != null)) {
        changeCss(element, 'hidden', 'remove', elementIsDomObject);
    }
};
/**
 * Clear validation errors. Used together with validateForm().
 * If the user provides a value for a form field, that was earlier marked as an error, clear it.
 * @param {Object} obj - The DOM element to target for clearing.
 * @return {void}
 */
const clearErrors = (obj) => {
    let el = obj.el;
    let elParent = obj.elParent;
    let elName = obj.elName;
    let showErrorInline = obj.showErrorInline;
    if (el.value !== '') {
        if (elParent !== null) {
            domEl(`.${elParent} .clickable`).classList.remove('!border-red-400');
        } else {
            // el.classList.remove('!border-red-400');
            changeCss(el, 'has-error', 'remove', true);
            changeCss(el, 'focus:outline-primary-500,focus:border-primary-500', 'add', true);
        }
        (showErrorInline) ? hide(`.${elName}-inline-error`) : '';
    } else {
        if (elParent !== null) {
            domEl(`.${elParent} .clickable`).classList.add('!border-red-400');
        } else {
            // el.classList.add('!border-red-400');
            changeCss(el, 'has-error', 'add', true);
            changeCss(el, 'focus:outline-primary-500,focus:border-primary-500', 'remove', true);
        }
        (showErrorInline) ? unhide(`.${elName}-inline-error`) : '';
    }
};
/**
 * Modify the css for a DOM element.
 * @param {Element|boolean} element - The class name of ID of the DOM element to modify.
 * @param {string} css - Comma separated list of css classes to apply to <element>.
 * @param {string} mode - Add|Remove. Determines if <css> should be added or removed from <element>.
 * @param {boolean} elementIsDomObject - If true, <element> will not be treated as a string but DOM element.
 * @return {void}
 * @see {@link https://bladewindui.com/extra/helper-functions#changecss}
 * @example
 * changeCss('.email', 'border-2, border-red-500');
 * changeCss('.email', 'border-2, border-red-500', 'remove');
 * changeCss(domEl('.email'), 'border-2, border-red-500', 'remove', true);
 */
const changeCss = (element, css, mode = 'add', elementIsDomObject = false) => {
    // css can be comma separated
    // if !elementIsDomObject run it through domEl
    if (!elementIsDomObject) element = domEl(element);
    if (element) {
        if (css.indexOf(',') !== -1 || css.indexOf(' ') !== -1) {
            css = css.replace(/\s+/g, '').split(',');
            for (let classname of css) {
                (mode === 'add') ? element.classList.add(classname.trim()) : element.classList.remove(classname.trim());
            }
        } else {
            if (element.classList !== undefined) {
                (mode === 'add') ? element.classList.add(css) : element.classList.remove(css);
            }
        }
    }
};
/**
 * Validate a form and highlight each field that fails validation.
 *   element does not need to be a <form> tag. Can be any element containing form fields.
 * @param form
 * @return {boolean} True if validation passes and False if validation fails.
 * @see {@link https://bladewindui.com/extra/helper-functions#validateform}
 */
const validateForm = (form) => {
    let hasError = 0;
    let BreakException = {};
    let fieldToValidate = [];
    try {
        fieldToValidate = (typeof (form) === 'string') ? domEls(`${form} .required`) : form.querySelectorAll('.required');
        fieldToValidate.forEach((el) => {
            // changeCss(el, '!border-red-500', 'remove', true);
            changeCss(el, 'has-error', 'remove', true);
            changeCss(el, 'focus:outline-primary-500,focus:border-primary-500', 'add', true);
            if (isEmpty(el.value)) {
                let elName = el.getAttribute('name');
                let elParent = el.getAttribute('data-parent');
                let errorMessage = el.getAttribute('data-error-message');
                let showErrorInline = el.getAttribute('data-error-inline');
                let errorHeading = el.getAttribute('data-error-heading');

                if (elParent !== null) {
                    changeCss(`.${elParent} .clickable`, '!border-red-400');
                } else {
                    changeCss(el, 'has-error', 'add', true);
                    changeCss(el, 'focus:outline-primary-500,focus:border-primary-500', 'remove', true);
                }
                el.focus();
                if (errorMessage) {
                    (showErrorInline) ? unhide(`.${elName}-inline-error`) :
                        showNotification(errorHeading, errorMessage, 'error');
                }

                let listenerObj = {
                    'el': el,
                    'elParent': elParent,
                    'elName': elName,
                    'showErrorInline': showErrorInline
                };

                el.addEventListener('keyup', clearErrors.bind(null, listenerObj), false);

                hasError++;
                throw BreakException;
            }
        });
    } catch (e) {
    }
    return hasError === 0;
};


/**
 * Allow only numeric input in a text input field.
 * @param {event} event - The event object. Key events.
 * @param {boolean} with_dots - Should dots be allowed in the input. Useful when entering decimals.
 * @return {void}
 * @see {@link https://bladewindui.com/extra/helper-functions#isnumberkey}
 * @example
 * onkeypress="return isNumberKey(event)"
 */
const isNumberKey = (event, with_dots = 1) => {
    let acceptedKeys = (with_dots === 1) ? /[\d\b\\.,]/ : /\d\b/;
    if (!event.key.toString().match(acceptedKeys) && event.key !== 'Enter' && event.key !== 'Tab') {
        event.preventDefault();
    }
};

/**
 * Execute a user-defined function.
 * @param {string} func - The function to execute, with or without parameters.
 * @return {void}
 */
const callUserFunction = (func) => {
    if (func !== '' && func !== undefined) eval(func);
};

/**
 * Serialize a form into key/value pairs for ajax submission.
 * @param {string} form - The form to serialize.
 * @return {object} The serialized object.
 * @see {@link https://bladewindui.com/extra/helper-functions#serialize}
 */
const serialize = (form) => {
    let data = new FormData(domEl(form));
    let obj = {};
    for (let [key, value] of data) {
        /***
         ** in some cases the form field name and api parameter differ, and you want to
         ** display a more meaningful error message from Laravels $errors.. set an attr
         ** data-serialize-as on the form field. that value will be used instead of [key]
         ** example: input name="contact_name" data-serialize-as="contact_person"
         ** Laravel will display contact name field is required but contact_person : value
         ** will be sent to the API
         **/
        let thisElement = document.getElementsByName(key);
        let serializeAs = thisElement[0].getAttribute('data-serialize-as');
        obj[serializeAs ?? key] = value;
    }
    return obj;
};

/**
 * Check if string contains a keyword.
 * @param {string} str - The string to check for keyword existence.
 * @param {string} keyword - The keyword to check for.
 * @return {boolean} True if string contains keyword. False if it does not.
 * @see {@link https://bladewindui.com/extra/helper-functions#stringcontains}
 */
const stringContains = (str, keyword) => {
    if (typeof str !== 'string') return false;
    return (str.indexOf(keyword) !== -1);
};

var doNothing = () => {
}

/**
 * Modify the css for DOM elements of the same type.
 * @param {string} elements - The class name of ID of the DOM elements to modify.
 * @param {string} css - Comma separated list of css classes to apply to <elements>.
 * @param {string} mode - Add|Remove. Determines if <css> should be added or removed from <elements>.
 * @return {void}
 * @see {@link https://bladewindui.com/extra/helper-functions#changecssfordomarray}
 */
const changeCssForDomArray = (elements, css, mode = 'add') => {
    if (domEls(elements).length > 0) {
        domEls(elements).forEach((el) => {
            changeCss(el, css, mode, true);
        });
    }
};


/**
 * Animate an element.
 * @param {string} element - The css class (name) of the element to animate.
 * @param {string} animation - The css animation class to be applied.
 * @return {void}
 * @see {@link https://bladewindui.com/extra/helper-functions#animatecss}
 */
const animateCss = (element, animation) => {
    return new Promise((resolve, reject) => {
        const animationClass = `animate__${animation}`;
        const node = domEl(element);
        if (!node) return resolve();

        node.classList.remove('hidden');
        node.classList.add('animate__animated', animationClass);
        document.documentElement.style.setProperty('--animate-duration', '.5s');
        node.addEventListener('animationend', function handler() {
            node.classList.remove('animate__animated', animationClass);
            node.removeEventListener('animationend', handler);
            resolve();
        }, {once: true});
    });
}
const animateCSS = animateCss;
/**
 * Display a modal.
 * @param {string} element - The css class (name) of the modal.
 * @param placeholders
 * @return {void}
 * @see {@link https://bladewindui.com/extra/helper-functions#showmodal}
 */
const showModal = (element, placeholders = {}) => {
    unhide(`.bw-${element}-modal`);
    document.body.classList.add('overflow-hidden');
    domEl(`.bw-${element}-modal`).focus();
    let index = (openModals.length === 0) ? 0 : openModals.length + 1;
    animateCss(`.bw-${element}`, 'zoomIn').then(() => {
        openModals[index] = element;
        if (Object.keys(placeholders).length > 0) {
            const modalBody = domEl(`.bw-${element}-modal .modal-body`);
            if (!window.originalContent) {
                window.originalContent = modalBody.innerHTML;
            }
            modalBody.innerHTML =
                window.originalContent.replace(/:([\w]+)/g, (match, key) => placeholders[key] || match);
        }
    });
};

/**
 * Trap focus within an open modal to prevent scrolling behind the modal.
 * @param {Event} event - The event object.
 * @return {void}
 */
const trapFocusInModal = (event) => {
    let modalName = openModals[(openModals.length - 1)];
    if (modalName !== undefined) {
        const focusableElements = domEls(`.bw-${modalName}-modal input:not([type='hidden']):not([class*='hidden']), .bw-${modalName}-modal button:not([class*="hidden"]),  .bw-${modalName}-modal a:not([class*="hidden"])`);
        const firstElement = focusableElements[0];
        const lastElement = focusableElements[focusableElements.length - 1];
        if (event.key === 'Tab') {
            if (event.shiftKey && document.activeElement === firstElement) {
                event.preventDefault();
                lastElement.focus();
            } else if (!event.shiftKey && document.activeElement === lastElement) {
                event.preventDefault();
                firstElement.focus();
            }
        }
    }
};
/**
 * Hide a modal.
 * @param {string} element - The css class (name) of the modal.
 * @return {void}
 * @see {@link https://bladewindui.com/extra/helper-functions#hidemodal}
 */
const hideModal = (element) => {
    animateCss(`.bw-${element}`, 'zoomOut').then(() => {
        openModals.pop();
        syncDrawerScrollLock();
        domEl(`.bw-${element}-modal`).removeEventListener('keydown', trapFocusInModal);
        animateCss(`.bw-${element}-modal`, 'zoomOut').then(() => {
            hide(`.bw-${element}-modal`);
        });
    });
};

const drawerByName = (name) => Array.from(domEls('[data-bw-drawer]'))
    .find((drawer) => drawer.getAttribute('data-name') === name);

const drawerFocusable = (drawer) => Array.from(drawer.querySelectorAll(
    'a[href], button:not([disabled]), input:not([disabled]):not([type="hidden"]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])'
)).filter((element) => !element.hidden && element.getAttribute('aria-hidden') !== 'true');

const syncDrawerScrollLock = () => {
    const hasModalDrawer = openDrawers.some((name) => drawerByName(name)?.getAttribute('data-modal') === 'true');
    document.body.classList.toggle('overflow-hidden', hasModalDrawer || openModals.length > 0);
};

const focusDrawer = (drawer) => {
    const preferred = drawer.querySelector('[autofocus]') || drawerFocusable(drawer)[0] || drawer.querySelector('.bw-drawer-panel');
    preferred?.focus({preventScroll: true});
};

const showDrawer = (name) => {
    const drawer = drawerByName(name);
    if (!drawer || drawer.getAttribute('data-state') === 'open') return false;
    drawerReturnFocus.set(name, document.activeElement);
    drawer.hidden = false;
    drawer.setAttribute('data-state', 'opening');
    drawer.setAttribute('aria-hidden', 'false');
    drawer.style.zIndex = String(50 + (openDrawers.length * 10));
    openDrawers.push(name);
    syncDrawerScrollLock();
    requestAnimationFrame(() => {
        if (drawer.getAttribute('data-state') !== 'opening') return;
        drawer.setAttribute('data-state', 'open');
        focusDrawer(drawer);
        drawer.dispatchEvent(new CustomEvent('bladewind:drawer-opened', {bubbles: true, detail: {name}}));
    });
    return true;
};

const hideDrawer = (name) => {
    const drawer = drawerByName(name);
    if (!drawer || drawer.hidden || drawer.getAttribute('data-state') === 'closed') return false;
    drawer.setAttribute('data-state', 'closing');
    drawer.setAttribute('aria-hidden', 'true');
    const index = openDrawers.lastIndexOf(name);
    if (index !== -1) openDrawers.splice(index, 1);
    syncDrawerScrollLock();

    let finished = false;
    const finish = () => {
        if (finished) return;
        finished = true;
        drawer.hidden = true;
        drawer.setAttribute('data-state', 'closed');
        drawer.style.removeProperty('z-index');
        const trigger = drawerReturnFocus.get(name);
        drawerReturnFocus.delete(name);
        if (trigger?.isConnected) trigger.focus({preventScroll: true});
        drawer.dispatchEvent(new CustomEvent('bladewind:drawer-closed', {bubbles: true, detail: {name}}));
    };
    drawer.querySelector('.bw-drawer-panel')?.addEventListener('transitionend', finish, {once: true});
    setTimeout(finish, 300);
    return true;
};

const toggleDrawer = (name) => {
    const drawer = drawerByName(name);
    if (!drawer) return false;
    return drawer.hidden || drawer.getAttribute('data-state') !== 'open' ? showDrawer(name) : hideDrawer(name);
};

const initialiseDrawers = () => {
    domEls('[data-bw-drawer][data-state="open"]').forEach((drawer) => {
        const name = drawer.getAttribute('data-name');
        drawer.hidden = false;
        drawer.setAttribute('aria-hidden', 'false');
        if (!openDrawers.includes(name)) openDrawers.push(name);
    });
    syncDrawerScrollLock();
    const activeDrawer = drawerByName(openDrawers[openDrawers.length - 1]);
    if (activeDrawer) focusDrawer(activeDrawer);
};

/**
 * Display the spinning icon on a button.
 * @param {string} element - The css class (name) of the button.
 * @return {void}
 * @see {@link https://bladewindui.com/extra/helper-functions#showbuttonspinner}
 */
const showButtonSpinner = (element) => {
    unhide(`${element} .bw-spinner`);
};

/**
 * Hide the spinning icon on a button.
 * @param {string} element - The css class (name) of the button.
 * @return {void}
 * @see {@link https://bladewindui.com/extra/helper-functions#hidebuttonspinner}
 */
const hideButtonSpinner = (element) => {
    hide(`${element} .bw-spinner`);
};

/**
 * Show the action buttons on a modal.
 * @param {string} element - The css class (name) of the modal.
 * @return {void}
 * @see {@link https://bladewindui.com/extra/helper-functions#showmodalactionbuttons}
 */
const showModalActionButtons = (element) => {
    unhide(`.bw-${element} .modal-footer`);
};

/**
 * Hide the action buttons on a modal.
 * @param {string} element - The css class (name) of the modal.
 * @return {void}
 * @see {@link https://bladewindui.com/extra/helper-functions#hidemodalactionbuttons}
 */
const hideModalActionButtons = (element) => {
    hide(`.bw-${element} .modal-footer`);
};


/**
 * Alias for unhide().
 * @see {@link https://bladewindui.com/extra/helper-functions#show}
 */
const show = (element, elementIsDomObject = false) => {
    unhide(element, elementIsDomObject);
};


/**
 * Add a key/value pair to client's storage.
 * @param {string} key - The key.
 * @param {string} val - The value corresponding to key.
 * @param {string} storageType - The storage key/val should be added to. sessionStorage | localStorage.
 * @return {void}
 * @see {@link https://bladewindui.com/extra/helper-functions#addtostorage}
 */
const addToStorage = (key, val, storageType = 'localStorage') => {
    if (window.localStorage || window.sessionStorage) {
        (storageType === 'localStorage') ?
            localStorage.setItem(key, val) : sessionStorage.setItem(key, val);
    }
};

/**
 * Retrieve a value from client's storage based on its key.
 * @param {string} key - The key.
 * @param {string} storageType - The storage to retrieve value from. sessionStorage | localStorage.
 * @return {string} The value of <key>
 * @see {@link https://bladewindui.com/extra/helper-functions#getfromstorage}
 */
const getFromStorage = (key, storageType = 'localStorage') => {
    if (window.localStorage || window.sessionStorage) {
        return (storageType === 'localStorage') ?
            localStorage.getItem(key) : sessionStorage.getItem(key);
    }
};
/**
 * Delete a key/value pair from client's storage.
 * @param {string} key - The key.
 * @param {string} storageType - The storage to remove key/val from. sessionStorage | localStorage.
 * @return {void}
 * @see {@link https://bladewindui.com/extra/helper-functions#removefromstorage}
 */
const removeFromStorage = (key, storageType = 'localStorage') => {
    if (window.localStorage || window.sessionStorage) {
        (storageType === 'localStorage') ?
            localStorage.removeItem(key) : sessionStorage.removeItem(key);
    }
};

/**
 * Navigate to a tab.
 * @param {string} element - The css class (name) of the tab to navigate to.
 * @param {string} colour - The colour of the tab.
 * @param {string} scope - The scope within which to find <element>. More like a parent element.
 * @param {HTMLElement|null} tabHeading - The tab heading that triggered the navigation.
 * @return {(void|boolean)}
 */
const goToTab = (element, colour, scope, tabHeading = null) => {
    let scope_ = scope.replace(/-/g, '_');
    let tabContent = domEl('.bw-tc-' + element);
    if (tabContent === null) return false;

    changeCssForDomArray(`.${scope}-headings li.atab span`, `${colour}, is-active`, 'remove');
    changeCssForDomArray(`.${scope}-headings li.atab span`, 'is-inactive');
    changeCss(`.atab-${element} span`, 'is-inactive', 'remove');
    changeCss(`.atab-${element} span`, `is-active, ${colour}`);
    domEls(`.${scope_}-tab-contents > div.atab-content`).forEach((element) => {
        hide(element, true);
    });
    unhide(tabContent, true);
    positionTabActiveLine(tabHeading?.closest('.bw-tab') ?? domEl(`.bw-tab-${scope}`));
    syncTabAccessibility(scope, element);
};

/**
 * Keep aria-selected and the roving tabindex in step with the visible selection.
 * Only the selected tab stays in the tab order; the arrow keys reach the others.
 */
const syncTabAccessibility = (scope, activeName) => {
    domEls(`.${scope}-headings li.atab`).forEach((tab) => {
        const isActive = tab.classList.contains(`atab-${activeName}`);
        const isDisabled = tab.getAttribute('aria-disabled') === 'true';

        tab.setAttribute('aria-selected', String(isActive));
        tab.setAttribute('tabindex', isActive && !isDisabled ? '0' : '-1');
    });
};

/**
 * Arrow-key navigation for a tab list, per the ARIA tabs pattern: Left/Right move
 * between tabs, Home/End jump to the ends, and the newly focused tab is selected.
 * Disabled tabs are skipped rather than focused and ignored.
 */
const enableTabKeyboardNavigation = () => {
    domEls('[role="tablist"]').forEach((tablist) => {
        if (tablist.dataset.bwKeyboard === '1') return;
        tablist.dataset.bwKeyboard = '1';

        tablist.addEventListener('keydown', (e) => {
            const keys = ['ArrowLeft', 'ArrowRight', 'Home', 'End'];
            if (!keys.includes(e.key)) return;

            const tabs = [...tablist.querySelectorAll('li.atab')]
                .filter((t) => t.getAttribute('aria-disabled') !== 'true');
            if (tabs.length === 0) return;

            const current = tabs.indexOf(document.activeElement.closest('li.atab'));
            let next;

            if (e.key === 'Home') next = tabs[0];
            else if (e.key === 'End') next = tabs[tabs.length - 1];
            else if (current === -1) next = tabs[0];
            else {
                // wrap around, which is what the pattern expects of a tab list
                const step = e.key === 'ArrowRight' ? 1 : -1;
                next = tabs[(current + step + tabs.length) % tabs.length];
            }

            if (!next) return;

            e.preventDefault();
            next.focus();
            next.click();
        });
    });
};

document.addEventListener('DOMContentLoaded', enableTabKeyboardNavigation);

/**
 * Position the animated line under the active heading in a simple tab group.
 * @param {HTMLElement|null} tabGroup - The tab group containing the active heading.
 * @param {boolean} animate - Whether to animate to the new position.
 * @return {void}
 */
const positionTabActiveLine = (tabGroup, animate = true) => {
    if (!tabGroup?.classList.contains('simple')) return;

    const activeHeading = tabGroup.querySelector('.atab span.is-active');
    const activeLine = tabGroup.querySelector('.bw-tab-active-line');
    if (!activeHeading || !activeLine) return;

    const groupBounds = tabGroup.getBoundingClientRect();
    const headingBounds = activeHeading.getBoundingClientRect();

    // The group has no layout yet; it is hidden inside a modal, an accordion or a
    // tab that has not been opened. Keep the line hidden until it can be measured.
    if (headingBounds.width === 0) {
        activeLine.style.opacity = '0';
        return;
    }

    if (!animate) activeLine.style.transition = 'none';
    activeLine.style.width = `${headingBounds.width}px`;
    activeLine.style.transform = `translate(${headingBounds.left - groupBounds.left}px, ${headingBounds.bottom - groupBounds.top - 1}px)`;
    activeLine.style.opacity = '1';

    if (!animate) {
        requestAnimationFrame(() => activeLine.style.removeProperty('transition'));
    }
};

const observedTabGroups = new WeakSet();

const tabActiveLineObserver = (typeof ResizeObserver !== 'undefined')
    ? new ResizeObserver((entries) => {
        entries.forEach((entry) => positionTabActiveLine(entry.target, false));
    })
    : null;

/**
 * Measure every simple tab group and keep its active line in sync with its heading.
 * Each group is watched for size changes, so groups that are hidden at page load
 * (in a modal, an accordion or another tab) position themselves when they appear.
 * Safe to call again after adding tab groups to the page.
 * @return {void}
 */
const initialiseTabActiveLines = () => {
    domEls('.bw-tab.simple').forEach((tabGroup) => {
        positionTabActiveLine(tabGroup, false);

        if (tabActiveLineObserver === null || observedTabGroups.has(tabGroup)) return;
        observedTabGroups.add(tabGroup);
        tabActiveLineObserver.observe(tabGroup);
    });
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initialiseTabActiveLines);
} else {
    initialiseTabActiveLines();
}

window.addEventListener('resize', initialiseTabActiveLines);

// A page that loads in a background tab does not run the rendering loop, so the
// observer has nothing to report until the tab is looked at.
document.addEventListener('visibilitychange', () => {
    if (!document.hidden) initialiseTabActiveLines();
});

/**
 * Get the offsetWidth of a prefix/suffix label
 * @param {string} element - The css class (name) of the prefix/suffix field.
 * @return {int}
 */
const getPrefixSuffixOffsetWidth = (element) => {
    let ps_element = domEl(element);
    const clone = ps_element.cloneNode(true);
    clone.style.visibility = 'hidden';
    clone.style.position = 'absolute';
    clone.style.display = 'block';
    document.body.appendChild(clone);
    let offsetWidth = clone.offsetWidth;
    document.body.removeChild(clone);
    return offsetWidth;
};
/**
 * Position a prefix in an input field.
 * @param {string} element - The css class (name) of the input field.
 * @param {string} mode - Event to trigger the positioning.
 * @return {void}
 */
const positionPrefix = (element, mode = 'blur') => {
    let transparency = domEl(`.dv-${element} .prefix`).getAttribute('data-transparency');
    let offset = (transparency === '1') ? -5 : 7;
    let prefixWidth = ((getPrefixSuffixOffsetWidth(`.dv-${element} .prefix`)) + offset) * 1;
    let defaultLabelLeftPos = '0.875rem';
    let inputField = domEl(`input.${element}`);
    let labelField = domEl(`.dv-${element} label`);

    if (mode === 'blur') {
        if (labelField) {
            labelField.style.left = (inputField.value === '') ? `${prefixWidth}px` : defaultLabelLeftPos;
        }
        domEl(`input.${element}`).style.paddingLeft = `${prefixWidth}px`;
        inputField.addEventListener('focus', (event) => {
            positionPrefix(element, event.type);
            // for backward compatibility where {once:true} is not supported
            inputField.removeEventListener('focus', positionPrefix);
        }, {once: true});
    } else if (mode === 'focus') {
        if (labelField) labelField.style.left = defaultLabelLeftPos;
        inputField.addEventListener('blur', (event) => {
            positionPrefix(element, event.type);
            // for backward compatibility where {once:true} is not supported
            inputField.removeEventListener('blur', positionPrefix);
        }, {once: true});
    }
};


/**
 * Position a suffix in an input field.
 * @param {string} element - The css class (name) of the input field.
 * @param {string} mode - Event to trigger the positioning.
 * @return {void}
 */
const positionSuffix = (element) => {
    let transparency = domEl(`.dv-${element} .suffix`).getAttribute('data-transparency');
    let offset = (transparency === '1') ? -5 : 7;
    let suffixWidth = ((getPrefixSuffixOffsetWidth(`.dv-${element} .suffix`)) + offset) * 1;
    domEl(`input.${element}`).style.paddingRight = `${suffixWidth}px`;
};

/**
 * Show or hide password in a password input fiield.
 * @param {string} element - The css class (name) of the input field.
 * @param {string} mode - Show or hide.
 * @return {void}
 */
const togglePassword = (element, mode) => {
    let inputField = domEl(`input.${element}`);
    if (mode === 'show') {
        inputField.setAttribute('type', 'text');
        unhide(`.dv-${element} .suffix svg.hide-pwd`);
        hide(`.dv-${element} .suffix svg.show-pwd`);
    } else {
        inputField.setAttribute('type', 'password')
        unhide(`.dv-${element} .suffix svg.show-pwd`);
        hide(`.dv-${element} .suffix svg.hide-pwd`);
    }
};

/**
 * Partition an array into two separate arrays.
 * @param {array} arr - The array to be split.
 * @param {function} fn - The evaluation function to run on each element > should return true/false for each element
 * @return {[array, array]}
 */
const partition = (arr, fn) => {
    return arr.reduce(
        (acc, val, i, arr) => {
            acc[fn(val, i, arr) ? 0 : 1].push(val);
            return acc;
        },
        [[], []]
    );
}

/**
 * Filter a table based on keyword.
 * @param {string} keyword - The keyword to filter table by.
 * @param {string} table - The css class (name) of the table to filter.
 * @param {null} field - The field to search.
 * @param {array} tableData - The data to filter
 * @return {void}
 */
const filterTable = (keyword, table, field, tableData) => {
    if (tableData === null) {
        // not dynamic table, search row content
        domEls(`${table} tbody tr`).forEach((tr) => {
            (tr.innerText.toLowerCase().includes(keyword.toLowerCase())) ?
                unhide(tr, true) : hide(tr, true);
        });
        return;
    }

    let currentPage = domEl(table).getAttribute('data-current-page');
    const [showList, hideList] = partition(tableData, (row) => {
        if (field) {
            return row[field].toLowerCase().match(keyword.toLowerCase());
        } else {
            return Object.values(row).toString().toLowerCase().match(keyword.toLowerCase());
        }
    });

    hideList.forEach((row) => {
        let thisRow = (currentPage !== null) ? `${table} tbody tr[data-id="${row.id}"][data-page="${currentPage}"]` : `${table} tbody tr[data-id="${row.id}"]`;
        hide(domEl(thisRow), true);
    });
    showList.forEach((row) => {
        let thisRow = (currentPage !== null) ? `${table} tbody tr[data-id="${row.id}"][data-page="${currentPage}"]` : `${table} tbody tr[data-id="${row.id}"]`;
        const elem = domEl(thisRow);
        if (elem) {
            unhide(elem, true);
        }
    });
};

/**
 * Filter a table based on keyword, .
 * @param {string} keyword - The keyword to filter table by.
 * @param {string} table - The css class (name) of the table to filter.
 * @param {string} field - The field to search.
 * @param {int} delay - Number of milliseconds to debouce the search.
 * @return {function} - The debounced search function to be run
 */
let debounceTimerId;
const filterTableDebounced = (keyword, table, field = null, delay = 0, minLength = 0, tableData = {}) => {
    let currentPage = domEl(table).getAttribute('data-current-page');
    let rows = (currentPage !== null) ? `${table} tbody tr.hidden[data-page="${currentPage}"]` : `${table} tbody tr.hidden`;
    if (keyword.length >= minLength) {
        return (...args) => {
            clearTimeout(debounceTimerId);
            debounceTimerId = setTimeout(() => filterTable(keyword, table, field, tableData), delay);
        };
    } else {
        return (...args) => {
            clearTimeout(debounceTimerId);
            debounceTimerId = setTimeout(() => {
                domEls(rows).forEach((tr) => {
                    unhide(tr, true);
                });
            }, delay);
        };
    }
};


/**
 * Remove trailing comma from string.
 * @param {string} element - The input field to remove trailing comma from.
 * @return {void}
 */
const stripComma = (element) => {
    if (element.value.startsWith(',')) {
        element.value = element.value.replace(/^,/, '');
    }
    const event = new Event('change', {
        bubbles: true,
        cancelable: true
    });
    element.dispatchEvent(event);
};
/**
 * Select a tag.
 * @param {string} value - The value or uuid to pass when tag is selected.
 * @param {string} name - The name of the tag.
 * @return {void}
 */
const selectTag = (value, name) => {
    let input = domEl(`input[name="${name}"]`);
    let max_selection = input.getAttribute('data-max-selection');
    let tag = domEl(`.bw-${name}-${value}`);
    let css = tag.getAttribute('class');
    if (input.value.includes(value)) { // remove
        let keyword = `(,?)${value}`;
        input.value = input.value.replace(input.value.match(keyword)[0], '');
        changeCss(tag, css.match(/bg-[\w]+-500/)[0], 'remove', true);
        changeCss(tag, (css.match(/bg-[\w]+-500/)[0]).replace('500', '200/80'), 'add', true);
        changeCss(tag, css.match(/text-[\w]+-50/)[0], 'remove', true);
        changeCss(tag, (css.match(/text-[\w]+-50/)[0]).replace('50', '600'), 'add', true);
    } else { // add
        let total_selected = (input.value === '') ? 0 : input.value.split(',').length;
        if (total_selected < max_selection) {
            input.value += `,${value}`;
            changeCss(tag, css.match(/bg-[\w]+-200\/80/)[0], 'remove', true);
            changeCss(tag, (css.match(/bg-[\w]+-200\/80/)[0]).replace('200/80', '500'), 'add', true);
            changeCss(tag, css.match(/text-[\w]+-600/)[0], 'remove', true);
            changeCss(tag, (css.match(/text-[\w]+-600/)[0]).replace('600', '50'), 'add', true);
        } else {
            showNotification(input.getAttribute('data-error-heading'), input.getAttribute('data-error-message'), 'error');
        }
    }
    stripComma(input)
};


/**
 * Highlight selected tags.
 * @param {string} values - Comma separated list of values corresponding to tags to highlight.
 * @param {string} name - The name of the tags.
 * @return {void}
 */
const highlightSelectedTags = (values, name) => {
    if (values !== '') {
        let valuesArray = values.split(',');
        for (let x = 0; x < valuesArray.length; x++) {
            selectTag(valuesArray[x].trim(), name);
        }
    }
};

/**
 * Compare two dates and display an error if second date is less than first date.
 * This is used in the range Datepicker component to ensure dates make sense.
 * @param {string} element1 - The first date input field.
 * @param {string} element2 - The second date input field.
 * @param {string} message - Error message to display if validation fails.
 * @param {boolean} inline - Display error inline or in a notification component.
 * @return {boolean} True if date 2 is greater than date 1.
 * @see {@link https://bladewindui.com/extra/helper-functions#comparedates}
 */
const compareDates = (element1, element2, message, inline) => {
    let date1El = domEl(`.${element1}`);
    let date2El = domEl(`.${element2}`);

    setTimeout(() => {
        let startDate = new Date(date1El.value).getTime();
        let endDate = new Date(date2El.value).getTime();

        if (startDate !== '' && endDate !== '') {
            if (startDate > endDate) {
                changeCss(date2El, '!border-red-400', 'add', true);
                (inline !== 1) ? showNotification('', message, 'error') : domEl(`.error-${element1}${element2}`).innerHTML = message;
                return false;
            } else {
                changeCss(date2El, '!border-red-400', 'remove', true);
                return true;
            }
        }
    }, 100);
};


/**
 * Validate for minimum and maximum values of an input field
 * @param {number} min - The minimum value.
 * @param {number} max - The maximum value.
 * @param {string} element - The input field to validate.
 * @param {boolean} enforceLimits - Ensure input does not exceed maximum or go below minimum
 * @return {void}
 */
const checkMinMax = (min, max, element, enforceLimits = false) => {
    let field = domEl(`.${element}`);
    let minimum = parseInt(min);
    let maximum = parseInt(max);
    let errorMessage = field.getAttribute('data-error-message');
    let showErrorInline = field.getAttribute('data-error-inline');
    let errorHeading = field.getAttribute('data-error-heading');

    const clearErrorMessage = () => {
        if (errorMessage) hide(`.${element}-inline-error`);
        changeCss(field, 'has-error', 'remove', true);
        changeCss(field, 'focus:outline-primary-500,focus:border-primary-500', 'add', true);
    }

    if (field.value !== '') {
        if (enforceLimits) {
            if (!isNaN(minimum) && field.value < minimum) field.value = minimum;
            if (!isNaN(maximum) && field.value > maximum) field.value = maximum;
        } else {
            if (((!isNaN(minimum) && field.value < minimum) || (!isNaN(maximum) && field.value > maximum))) {
                changeCss(field, 'focus:outline-primary-500,focus:border-primary-500', 'remove', true);
                changeCss(field, 'has-error', 'add', true);
                if (errorMessage) {
                    (showErrorInline) ? unhide(`.${element}-inline-error`) :
                        showNotification(errorHeading, errorMessage, 'error');
                }
            } else {
                clearErrorMessage();
            }
        }
    } else {
        clearErrorMessage();
    }
};

/**
 * Display a clear button in an input field that has text.
 * @param {string} element - The css class (name) of the input field.
 * @return {void}
 */
const makeClearable = (element) => {
    let field = domEl(`.${element}`);
    let suffixElement = domEl(`.${element}-suffix svg`);
    let tableElement = element.replace('bw_search_', 'table.');
    let clearingFunction = (domEl(tableElement)) ? field.getAttribute('oninput').replace('this.value', "''") : '';
    if (!suffixElement.getAttribute('onclick')) {
        suffixElement.setAttribute('onclick', `domEl(\'.${element}\').value=''; hide(this, true); ${clearingFunction}`);
    }
    (field.value !== '') ? unhide(suffixElement, true) : hide(suffixElement, true);
};

/**
 * Convert a selected file to base64.
 * @param {string} file - Url of selected file.
 * @param {string} element - The input field to write the base64 string to.
 * @return {void}
 */
const convertToBase64 = (file, element) => {
    const reader = new FileReader();
    reader.onloadend = () => {
        const base64String = reader.result;//.replace('data:', '').replace(/^.+,/, '');
        domEl(element).value = base64String;
    };
    reader.readAsDataURL(file);
};

/**
 * Check if selected file size falls within allowed file size.
 * @param {number} fileSize - The selected file size.
 * @param {number} maxSize - THe maximum file size.
 * @return {boolean} True if <fileSize> if less than <maxSize>
 */
const allowedFileSize = (fileSize, maxSize) => {
    return (fileSize <= maxSize * 1000000);
};

/**
 * Set the value of a datepicker
 * @return {void}
 * @param {string} elName - name of the input field to update
 * @param {string} date - new value to set
 */
const setDatepickerValue = (elName, date) => {
    let input = domEl(`.${elName}`);
    if (!input) {
        console.error(`No datepicker found with the name ${elName}`);
        return;
    }
    // let alpineComponent = document.querySelector('[x-data]').__x.$data;
    if (!input._x_model) {
        console.error(`Alpine.js component not found for element ${elName}`);
        return;
    }
    input._x_model.set(date);
};

/**
 * Bind a delegated listener on the document.
 *
 * Components used to attach their behaviour with inline on* attributes, which a
 * strict Content-Security-Policy blocks outright — a nonce authorises <script>
 * elements, not on* attributes, so there was no way to keep them working. See #608.
 *
 * Delegation also survives markup arriving after load, which the table's
 * client-side pagination does on every page change and a per-element listener
 * would miss.
 *
 * @param {string} event
 * @param {string} selector matched against the event target and its ancestors
 * @param {function(HTMLElement, Event): void} handler receives the matched element
 * @param {object} options passed to addEventListener; focus and blur need capture
 */
const bwOn = (event, selector, handler, options = {}) => {
    document.addEventListener(event, (e) => {
        const el = e.target?.closest?.(selector);
        if (el) handler(el, e);
    }, options);
};

/**
 * Enter and Space activate anything given a button role, which a real <button>
 * does for free and a <div role="button"> does not.
 */
const bwActivateOnKey = (selector) => {
    bwOn('keydown', selector, (el, e) => {
        if (e.key !== 'Enter' && e.key !== ' ') return;
        e.preventDefault();
        el.click();
    });
};

/** Find a stepper by its public name without interpolating untrusted text into CSS. */
const stepperByName = (name) => {
    const steppers = Array.from(document.querySelectorAll('[data-bw-stepper]'));
    const stepper = steppers.find((candidate) => candidate.getAttribute('data-name') === String(name)) || null;
    if (stepper && stepper.dataset.bwInitialised !== 'true') initialiseStepper(stepper, steppers.indexOf(stepper));
    return stepper;
};

const stepperParts = (stepper) => ({
    items: Array.from(stepper.querySelectorAll('[data-bw-stepper-item]')),
    triggers: Array.from(stepper.querySelectorAll('[data-bw-stepper-step]')),
    panels: Array.from(stepper.querySelectorAll('[data-bw-stepper-panel]')),
});

const stepperDetail = (stepper, previousStep, nextStep, direction) => ({
    stepperName: stepper.getAttribute('data-name'),
    previousStep,
    nextStep,
    direction,
});

const initialiseStepper = (stepper, index = 0) => {
    if (stepper.dataset.bwInitialised === 'true') return;
    const {items, triggers, panels} = stepperParts(stepper);
    if (!items.length) return;

    // Content components share the default slot with items. Move them outside the
    // ordered list so the final DOM keeps list semantics and panels remain siblings.
    panels.forEach((panel) => stepper.appendChild(panel));

    const safeStepper = (stepper.getAttribute('data-name') || `stepper-${index + 1}`)
        .replace(/[^A-Za-z0-9_-]/g, '-');
    triggers.forEach((trigger, triggerIndex) => {
        const step = trigger.getAttribute('data-bw-stepper-step');
        const safeStep = (step || `step-${triggerIndex + 1}`).replace(/[^A-Za-z0-9_-]/g, '-');
        const idBase = `bw-stepper-${index + 1}-${safeStepper}-${safeStep}`;
        const panel = panels.find((candidate) => candidate.getAttribute('data-bw-stepper-panel') === step);
        trigger.id = `${idBase}-step`;
        if (panel) {
            panel.id = `${idBase}-panel`;
            panel.setAttribute('aria-labelledby', trigger.id);
            trigger.setAttribute('aria-controls', panel.id);
        }
    });

    const requested = stepper.getAttribute('data-current');
    const requestedTrigger = triggers.find((trigger) => trigger.getAttribute('data-bw-stepper-step') === requested
        && trigger.getAttribute('aria-disabled') !== 'true');
    const stateTrigger = triggers.find((trigger) => trigger.closest('[data-bw-stepper-item]')?.getAttribute('data-state') === 'current'
        && trigger.getAttribute('aria-disabled') !== 'true');
    const initial = requestedTrigger || stateTrigger || triggers.find((trigger) => trigger.getAttribute('aria-disabled') !== 'true');
    stepper.dataset.bwInitialised = 'true';
    if (initial) setStepperCurrent(stepper, initial.getAttribute('data-bw-stepper-step'), {focus: false, emit: false, force: true});
};

const initialiseSteppers = () => document.querySelectorAll('[data-bw-stepper]')
    .forEach((stepper, index) => initialiseStepper(stepper, index));

const setStepperCurrent = (stepper, stepName, options = {}) => {
    if (!stepper) return false;
    const {items, triggers, panels} = stepperParts(stepper);
    const target = triggers.find((trigger) => trigger.getAttribute('data-bw-stepper-step') === String(stepName));
    if (!target || target.getAttribute('aria-disabled') === 'true') return false;

    const currentName = stepper.getAttribute('data-current') || null;
    const currentIndex = triggers.findIndex((trigger) => trigger.getAttribute('data-bw-stepper-step') === currentName);
    const targetIndex = triggers.indexOf(target);
    const direction = targetIndex > currentIndex ? 'forward' : targetIndex < currentIndex ? 'backward' : 'none';
    if (!options.force && stepper.getAttribute('data-linear') === 'true') {
        if (direction === 'forward' && !options.allowForward) return false;
        const targetState = target.closest('[data-bw-stepper-item]')?.getAttribute('data-state');
        if (direction === 'backward' && !options.allowBackward && targetState !== 'complete') return false;
    }
    if (currentName === String(stepName) && !options.force) return true;

    const detail = stepperDetail(stepper, currentName, String(stepName), direction);
    if (options.emit !== false) {
        const before = new CustomEvent('bladewind:stepper:before-change', {bubbles: true, cancelable: true, detail});
        if (!stepper.dispatchEvent(before)) return false;
    }

    items.forEach((item, itemIndex) => {
        const trigger = triggers[itemIndex];
        if (!trigger) return;
        const isTarget = trigger === target;
        const disabled = trigger.getAttribute('aria-disabled') === 'true';
        const existing = item.getAttribute('data-state');
        let state = isTarget ? 'current' : itemIndex < targetIndex ? 'complete' : 'upcoming';
        if (disabled) state = 'disabled';
        else if (!isTarget && existing === 'error') state = 'error';
        item.setAttribute('data-state', state);
        trigger.setAttribute('aria-current', isTarget ? 'step' : 'false');
        trigger.setAttribute('tabindex', isTarget ? '0' : '-1');
        const stateText = trigger.querySelector('.bw-stepper-state-text');
        if (stateText) stateText.textContent = state.charAt(0).toUpperCase() + state.slice(1);
    });
    panels.forEach((panel) => {
        const shown = panel.getAttribute('data-bw-stepper-panel') === String(stepName);
        panel.hidden = !shown;
        panel.setAttribute('aria-hidden', shown ? 'false' : 'true');
        if (!shown) panel.setAttribute('inert', '');
        else panel.removeAttribute('inert');
    });
    stepper.setAttribute('data-current', String(stepName));
    if (options.focus !== false) target.focus({preventScroll: true});
    if (options.emit !== false) stepper.dispatchEvent(new CustomEvent('bladewind:stepper:changed', {bubbles: true, detail}));
    return true;
};

const showStepperStep = (stepperName, stepName) => setStepperCurrent(stepperByName(stepperName), stepName);

const nextStepperStep = (stepperName) => {
    const stepper = stepperByName(stepperName);
    if (!stepper) return false;
    const {triggers} = stepperParts(stepper);
    const current = triggers.findIndex((trigger) => trigger.getAttribute('data-bw-stepper-step') === stepper.getAttribute('data-current'));
    const next = triggers.slice(current + 1).find((trigger) => trigger.getAttribute('aria-disabled') !== 'true');
    if (!next) {
        const detail = stepperDetail(stepper, stepper.getAttribute('data-current'), null, 'complete');
        stepper.dispatchEvent(new CustomEvent('bladewind:stepper:complete', {bubbles: true, detail}));
        return true;
    }
    return setStepperCurrent(stepper, next.getAttribute('data-bw-stepper-step'), {allowForward: true});
};

const previousStepperStep = (stepperName) => {
    const stepper = stepperByName(stepperName);
    if (!stepper) return false;
    const {triggers} = stepperParts(stepper);
    const current = triggers.findIndex((trigger) => trigger.getAttribute('data-bw-stepper-step') === stepper.getAttribute('data-current'));
    const previous = triggers.slice(0, current).reverse().find((trigger) => trigger.getAttribute('aria-disabled') !== 'true');
    return previous ? setStepperCurrent(stepper, previous.getAttribute('data-bw-stepper-step'), {allowBackward: true}) : false;
};

const resetStepper = (stepperName) => {
    const stepper = stepperByName(stepperName);
    if (!stepper) return false;
    const {items, triggers} = stepperParts(stepper);
    items.forEach((item, index) => {
        const initial = triggers[index]?.getAttribute('data-initial-state') || 'upcoming';
        item.setAttribute('data-state', initial);
    });
    const initialName = stepper.getAttribute('data-initial-current');
    const initial = triggers.find((trigger) => trigger.getAttribute('data-bw-stepper-step') === initialName
        && trigger.getAttribute('aria-disabled') !== 'true') || triggers.find((trigger) => trigger.getAttribute('aria-disabled') !== 'true');
    return initial ? setStepperCurrent(stepper, initial.getAttribute('data-bw-stepper-step'), {force: true}) : false;
};

/** Find one Command Palette without interpolating its public name into a selector. */
const commandPaletteByName = (name) => Array.from(document.querySelectorAll('[data-bw-command-palette]'))
    .find((candidate) => candidate.getAttribute('data-name') === String(name)) || null;

const commandPaletteDetail = (palette, values = {}) => ({name: palette.getAttribute('data-name'), ...values});

const commandPaletteEvent = (palette, name, detail, cancelable = false) => palette.dispatchEvent(
    new CustomEvent(`bladewind:command-palette:${name}`, {bubbles: true, cancelable, detail})
);

const commandPaletteInput = (palette) => palette.querySelector('[data-bw-command-palette-input]');

const commandPaletteItems = (palette) => Array.from(palette.querySelectorAll('[data-bw-command-palette-item]'));

const commandPaletteVisibleItems = (palette) => commandPaletteItems(palette)
    .filter((item) => !item.hidden && item.getAttribute('aria-disabled') !== 'true');

const highlightedCommandPaletteItem = (palette) => commandPaletteItems(palette)
    .find((item) => item.getAttribute('data-highlighted') === 'true') || null;

const highlightCommandPaletteItem = (palette, item) => {
    const input = commandPaletteInput(palette);
    commandPaletteItems(palette).forEach((candidate) => {
        const isTarget = candidate === item;
        candidate.setAttribute('data-highlighted', isTarget ? 'true' : 'false');
        candidate.setAttribute('aria-selected', isTarget ? 'true' : 'false');
    });
    if (input) input.setAttribute('aria-activedescendant', item ? item.id : '');
    item?.scrollIntoView({block: 'nearest'});
};

const moveCommandPaletteHighlight = (palette, direction) => {
    const visible = commandPaletteVisibleItems(palette);
    if (visible.length === 0) return highlightCommandPaletteItem(palette, null);
    const current = highlightedCommandPaletteItem(palette);
    const index = current ? visible.indexOf(current) : -1;
    let nextIndex = 0;
    if (direction === 'last') nextIndex = visible.length - 1;
    else if (direction === 'down') nextIndex = index < 0 ? 0 : (index + 1) % visible.length;
    else if (direction === 'up') nextIndex = index < 0 ? visible.length - 1 : (index - 1 + visible.length) % visible.length;
    highlightCommandPaletteItem(palette, visible[nextIndex]);
};

const filterCommandPalette = (palette, query) => {
    const normalised = query.trim().toLowerCase();
    let visibleCount = 0;
    commandPaletteItems(palette).forEach((item) => {
        const matches = normalised === '' || (item.getAttribute('data-keywords') || '').includes(normalised);
        item.hidden = !matches;
        if (matches) visibleCount++;
    });
    palette.querySelectorAll('[data-bw-command-palette-group]').forEach((group) => {
        group.hidden = !Array.from(group.querySelectorAll('[data-bw-command-palette-item]')).some((item) => !item.hidden);
    });
    const empty = palette.querySelector('[data-bw-command-palette-empty]');
    if (empty) empty.hidden = visibleCount > 0 || palette.getAttribute('data-loading') === 'true';
    highlightCommandPaletteItem(palette, commandPaletteVisibleItems(palette)[0] || null);
    commandPaletteEvent(palette, 'search', commandPaletteDetail(palette, {query: query}));
};

const setCommandPaletteLoading = (name, loading) => {
    const palette = commandPaletteByName(name);
    if (!palette) return false;
    palette.setAttribute('data-loading', loading ? 'true' : 'false');
    const loadingEl = palette.querySelector('[data-bw-command-palette-loading]');
    if (loadingEl) loadingEl.hidden = !loading;
    const empty = palette.querySelector('[data-bw-command-palette-empty]');
    if (empty && loading) empty.hidden = true;
    return true;
};

/** Items carry tabindex="-1": keyboard navigation moves the highlight, not real
 *  focus, so only the search field and close button take part in the Tab trap. */
const commandPaletteFocusable = (palette) => Array.from(palette.querySelectorAll(
    'input:not([disabled]), button:not([disabled]), a[href]'
)).filter((element) => !element.hidden && element.getAttribute('tabindex') !== '-1');

const focusCommandPalette = (palette) => commandPaletteInput(palette)?.focus({preventScroll: true});

const openCommandPalettes = [];
const commandPaletteReturnFocus = new Map();

const openCommandPalette = (name, options = {}) => {
    const palette = commandPaletteByName(name);
    if (!palette || palette.getAttribute('data-state') === 'open') return false;
    const detail = commandPaletteDetail(palette, {triggeringElement: options.triggeringElement || null, source: options.source || 'api'});
    if (!commandPaletteEvent(palette, 'before-open', detail, true)) return false;
    commandPaletteReturnFocus.set(name, document.activeElement);
    palette.hidden = false;
    palette.setAttribute('aria-hidden', 'false');
    palette.setAttribute('data-state', 'opening');
    document.body.classList.add('overflow-hidden');
    openCommandPalettes.push(name);
    const input = commandPaletteInput(palette);
    if (input) input.value = '';
    filterCommandPalette(palette, '');
    requestAnimationFrame(() => {
        if (palette.getAttribute('data-state') !== 'opening') return;
        palette.setAttribute('data-state', 'open');
        focusCommandPalette(palette);
        commandPaletteEvent(palette, 'opened', detail);
    });
    return true;
};

const closeCommandPalette = (name, options = {}) => {
    const palette = commandPaletteByName(name);
    if (!palette || palette.hidden || palette.getAttribute('data-state') === 'closed') return false;
    const detail = commandPaletteDetail(palette, {triggeringElement: options.triggeringElement || null, source: options.source || 'api'});
    if (!commandPaletteEvent(palette, 'before-close', detail, true)) return false;
    palette.setAttribute('data-state', 'closed');
    palette.setAttribute('aria-hidden', 'true');
    palette.hidden = true;
    const index = openCommandPalettes.lastIndexOf(name);
    if (index !== -1) openCommandPalettes.splice(index, 1);
    if (openCommandPalettes.length === 0) document.body.classList.remove('overflow-hidden');
    const trigger = commandPaletteReturnFocus.get(name);
    commandPaletteReturnFocus.delete(name);
    if (trigger?.isConnected) trigger.focus({preventScroll: true});
    commandPaletteEvent(palette, 'closed', detail);
    return true;
};

const toggleCommandPalette = (name, options = {}) => {
    const palette = commandPaletteByName(name);
    if (!palette) return false;
    return (palette.hidden || palette.getAttribute('data-state') !== 'open')
        ? openCommandPalette(name, options) : closeCommandPalette(name, options);
};

const resetCommandPalette = (name) => {
    const palette = commandPaletteByName(name);
    if (!palette) return false;
    const input = commandPaletteInput(palette);
    if (input) input.value = '';
    filterCommandPalette(palette, '');
    return true;
};

const activateCommandPaletteItem = (palette, item, options = {}) => {
    if (!item || item.getAttribute('aria-disabled') === 'true') return false;
    const isLink = item.tagName === 'A';
    const detail = commandPaletteDetail(palette, {
        itemName: item.getAttribute('data-item-name'),
        href: isLink ? item.getAttribute('href') : null,
        triggeringElement: options.triggeringElement || item,
        source: options.source || 'pointer',
    });
    if (!commandPaletteEvent(palette, 'before-select', detail, true)) return false;
    commandPaletteEvent(palette, 'select', detail);
    if (palette.getAttribute('data-close-on-select') === 'true') {
        closeCommandPalette(palette.getAttribute('data-name'), {triggeringElement: item, source: 'select'});
    }
    return true;
};

/**
 * A shortcut string like "mod+k" against a keydown event. "mod" matches Ctrl
 * on Windows/Linux and Cmd on macOS, which is the conventional command
 * palette binding across editors and chat apps.
 */
const commandPaletteShortcutMatches = (shortcut, event) => {
    const tokens = (shortcut || '').split('+').map((token) => token.trim().toLowerCase()).filter(Boolean);
    if (tokens.length === 0) return false;
    const key = tokens[tokens.length - 1];
    if ((event.key || '').toLowerCase() !== key) return false;
    const modifiers = tokens.slice(0, -1);
    const wantsMod = modifiers.includes('mod');
    if (wantsMod && !(event.ctrlKey || event.metaKey)) return false;
    if (!wantsMod && modifiers.includes('ctrl') !== event.ctrlKey) return false;
    if (!wantsMod && (modifiers.includes('meta') || modifiers.includes('cmd')) !== event.metaKey) return false;
    if (modifiers.includes('alt') !== event.altKey) return false;
    if (modifiers.includes('shift') !== event.shiftKey) return false;
    return true;
};

const initialiseCommandPalettes = () => {
    document.querySelectorAll('[data-bw-command-palette][data-state="open"]').forEach((palette) => {
        const name = palette.getAttribute('data-name');
        palette.hidden = false;
        palette.setAttribute('aria-hidden', 'false');
        if (!openCommandPalettes.includes(name)) openCommandPalettes.push(name);
        document.body.classList.add('overflow-hidden');
        filterCommandPalette(palette, commandPaletteInput(palette)?.value || '');
    });
    const activePalette = commandPaletteByName(openCommandPalettes[openCommandPalettes.length - 1]);
    if (activePalette) focusCommandPalette(activePalette);
};

/** Find and initialise one Sidebar without interpolating its public name into a selector. */
const sidebarByName = (name) => {
    const sidebars = Array.from(document.querySelectorAll('[data-bw-sidebar]'));
    const sidebar = sidebars.find((candidate) => candidate.getAttribute('data-name') === String(name)) || null;
    if (sidebar && sidebar.dataset.bwInitialised !== 'true') initialiseSidebar(sidebar);
    return sidebar;
};

const sidebarHostByName = (attribute, name) => Array.from(document.querySelectorAll(`[${attribute}]`))
    .find((host) => host.getAttribute(attribute) === String(name)) || null;

const sidebarPresentation = () => window.matchMedia?.('(min-width: 1024px)')?.matches ? 'desktop' : 'mobile';

const sidebarDetail = (sidebar, values = {}) => ({
    sidebarName: sidebar.getAttribute('data-name'),
    presentation: sidebarPresentation(),
    placement: sidebar.getAttribute('data-resolved-placement') || sidebar.getAttribute('data-placement'),
    triggeringElement: values.triggeringElement || null,
    source: values.source || 'programmatic',
    ...values,
});

const sidebarEvent = (sidebar, name, detail, cancelable = false) => sidebar.dispatchEvent(
    new CustomEvent(`bladewind:sidebar:${name}`, {bubbles: true, cancelable, detail})
);

const sidebarStoredState = (sidebar) => {
    if (sidebar.getAttribute('data-persist') !== 'true' && sidebar.getAttribute('data-persist-groups') !== 'true') return null;
    try {
        const parsed = JSON.parse(window.localStorage.getItem(sidebar.getAttribute('data-storage-key')) || 'null');
        if (!parsed || typeof parsed !== 'object' || Array.isArray(parsed)) return null;
        if ('collapsed' in parsed && typeof parsed.collapsed !== 'boolean') return null;
        if ('groups' in parsed && (!parsed.groups || typeof parsed.groups !== 'object' || Array.isArray(parsed.groups))) return null;
        if (parsed.groups && Object.values(parsed.groups).some((value) => typeof value !== 'boolean')) return null;
        return parsed;
    } catch (_) {
        return null;
    }
};

const persistSidebarState = (sidebar) => {
    if (sidebar.getAttribute('data-persist') !== 'true' && sidebar.getAttribute('data-persist-groups') !== 'true') return false;
    const state = {};
    if (sidebar.getAttribute('data-persist') === 'true') state.collapsed = sidebar.getAttribute('data-state') === 'collapsed';
    if (sidebar.getAttribute('data-persist-groups') === 'true') {
        state.groups = {};
        sidebar.querySelectorAll('[data-bw-sidebar-group]').forEach((group) => {
            state.groups[group.getAttribute('data-group-name')] = group.getAttribute('data-expanded') === 'true';
        });
    }
    try {
        window.localStorage.setItem(sidebar.getAttribute('data-storage-key'), JSON.stringify(state));
        return true;
    } catch (_) {
        return false;
    }
};

const resolveSidebarPlacement = (sidebar) => {
    const placement = sidebar.getAttribute('data-placement');
    const rtl = window.getComputedStyle?.(sidebar)?.direction === 'rtl';
    const resolved = placement === 'start' ? (rtl ? 'right' : 'left')
        : placement === 'end' ? (rtl ? 'left' : 'right') : placement;
    sidebar.setAttribute('data-resolved-placement', resolved);
    const drawer = drawerByName(sidebar.getAttribute('data-drawer-name'));
    if (drawer) drawer.setAttribute('data-position', resolved);
};

const setSidebarGroupExpanded = (sidebar, group, expanded, options = {}) => {
    if (!sidebar || !group || group.getAttribute('data-disabled') === 'true') return false;
    const previousState = group.getAttribute('data-expanded') === 'true';
    if (previousState === expanded) return true;
    const groupName = group.getAttribute('data-group-name');
    const trigger = group.querySelector(':scope > [data-bw-sidebar-group-trigger]');
    const panel = group.querySelector(':scope > .bw-sidebar-group-panel');
    const detail = sidebarDetail(sidebar, {
        groupName,
        previousState: previousState ? 'expanded' : 'collapsed',
        nextState: expanded ? 'expanded' : 'collapsed',
        triggeringElement: options.triggeringElement || trigger,
        source: options.source || 'programmatic',
    });
    if (options.emit !== false && !sidebarEvent(sidebar, 'group:before-change', detail, true)) return false;
    if (!expanded && panel?.contains(document.activeElement)) trigger?.focus({preventScroll: true});
    group.setAttribute('data-expanded', expanded ? 'true' : 'false');
    trigger?.setAttribute('aria-expanded', expanded ? 'true' : 'false');
    if (panel) {
        panel.hidden = !expanded;
        if (expanded) panel.removeAttribute('inert');
        else panel.setAttribute('inert', '');
    }
    if (options.persist !== false) persistSidebarState(sidebar);
    if (options.emit !== false) sidebarEvent(sidebar, 'group:changed', detail);
    return true;
};

const sidebarGroupByName = (sidebar, groupName) => Array.from(sidebar?.querySelectorAll('[data-bw-sidebar-group]') || [])
    .find((group) => group.getAttribute('data-group-name') === String(groupName)) || null;

const expandSidebarGroup = (sidebarName, groupName) => setSidebarGroupExpanded(
    sidebarByName(sidebarName), sidebarGroupByName(sidebarByName(sidebarName), groupName), true
);

const collapseSidebarGroup = (sidebarName, groupName) => setSidebarGroupExpanded(
    sidebarByName(sidebarName), sidebarGroupByName(sidebarByName(sidebarName), groupName), false
);

const toggleSidebarGroup = (sidebarName, groupName) => {
    const sidebar = sidebarByName(sidebarName);
    const group = sidebarGroupByName(sidebar, groupName);
    if (!group) return false;
    return setSidebarGroupExpanded(sidebar, group, group.getAttribute('data-expanded') !== 'true');
};

const setSidebarCollapsed = (sidebar, collapsed, options = {}) => {
    if (!sidebar || sidebar.getAttribute('data-collapsible') !== 'true') return false;
    const previousState = sidebar.getAttribute('data-state');
    const nextState = collapsed ? 'collapsed' : 'expanded';
    if (previousState === nextState) return true;
    const action = collapsed ? 'collapse' : 'expand';
    const detail = sidebarDetail(sidebar, {
        previousState,
        nextState,
        triggeringElement: options.triggeringElement || null,
        source: options.source || 'programmatic',
    });
    if (options.emit !== false && !sidebarEvent(sidebar, `before-${action}`, detail, true)) return false;
    sidebar.setAttribute('data-state', nextState);
    const control = sidebar.querySelector('[data-bw-sidebar-collapse-control]');
    if (control) {
        const label = control.getAttribute(collapsed ? 'data-expand-label' : 'data-collapse-label');
        control.setAttribute('aria-label', label);
        control.setAttribute('title', label);
    }
    if (options.persist !== false) persistSidebarState(sidebar);
    if (options.emit !== false) sidebarEvent(sidebar, collapsed ? 'collapsed' : 'expanded', detail);
    return true;
};

const collapseSidebar = (sidebarName) => setSidebarCollapsed(sidebarByName(sidebarName), true);
const expandSidebar = (sidebarName) => setSidebarCollapsed(sidebarByName(sidebarName), false);

const moveSidebarToMobile = (sidebar) => {
    const host = sidebarHostByName('data-bw-sidebar-mobile-host', sidebar.getAttribute('data-name'));
    if (!host) return false;
    if (sidebar.parentElement !== host) host.appendChild(sidebar);
    return true;
};

const moveSidebarToDesktop = (sidebar) => {
    const host = sidebarHostByName('data-bw-sidebar-desktop-host', sidebar.getAttribute('data-name'));
    if (!host) return false;
    if (sidebar.parentElement !== host) host.appendChild(sidebar);
    return true;
};

const openSidebar = (sidebarName, options = {}) => {
    const sidebar = sidebarByName(sidebarName);
    if (!sidebar) return false;
    if (sidebarPresentation() === 'desktop') return expandSidebar(sidebarName);
    if (sidebar.getAttribute('data-mobile') !== 'drawer') return false;
    const drawerName = sidebar.getAttribute('data-drawer-name');
    const drawer = drawerByName(drawerName);
    if (!drawer || drawer.getAttribute('data-state') === 'open') return false;
    const detail = sidebarDetail(sidebar, {
        previousState: 'closed', nextState: 'open',
        triggeringElement: options.triggeringElement || document.activeElement,
        source: options.source || 'programmatic',
    });
    if (!sidebarEvent(sidebar, 'before-open', detail, true)) return false;
    resolveSidebarPlacement(sidebar);
    if (!moveSidebarToMobile(sidebar)) return false;
    sidebar._bwTransitionDetail = detail;
    if (!showDrawer(drawerName)) {
        moveSidebarToDesktop(sidebar);
        return false;
    }
    return true;
};

const closeSidebar = (sidebarName, options = {}) => {
    const sidebar = sidebarByName(sidebarName);
    if (!sidebar) return false;
    if (sidebarPresentation() === 'desktop') return collapseSidebar(sidebarName);
    const drawer = drawerByName(sidebar.getAttribute('data-drawer-name'));
    if (!drawer || drawer.hidden || drawer.getAttribute('data-state') === 'closed') return false;
    const detail = sidebarDetail(sidebar, {
        previousState: 'open', nextState: 'closed',
        triggeringElement: options.triggeringElement || document.activeElement,
        source: options.source || 'programmatic',
    });
    if (!sidebarEvent(sidebar, 'before-close', detail, true)) return false;
    sidebar._bwTransitionDetail = detail;
    return hideDrawer(sidebar.getAttribute('data-drawer-name'));
};

const toggleSidebar = (sidebarName) => {
    const sidebar = sidebarByName(sidebarName);
    if (!sidebar) return false;
    if (sidebarPresentation() === 'desktop') {
        return setSidebarCollapsed(sidebar, sidebar.getAttribute('data-state') !== 'collapsed');
    }
    const drawer = drawerByName(sidebar.getAttribute('data-drawer-name'));
    return drawer && !drawer.hidden && drawer.getAttribute('data-state') !== 'closed'
        ? closeSidebar(sidebarName) : openSidebar(sidebarName);
};

const resolveSidebarActiveItem = (sidebar) => {
    const items = Array.from(sidebar.querySelectorAll('[data-bw-sidebar-item]'));
    const enabled = items.filter((item) => item.getAttribute('data-disabled') !== 'true');
    const canonical = sidebar.getAttribute('data-active');
    let activeItems = canonical
        ? enabled.filter((item) => item.getAttribute('data-item-name') === canonical).slice(0, 1)
        : enabled.filter((item) => item.getAttribute('data-initial-active') === 'true');
    if (sidebar.getAttribute('data-multiple-active') !== 'true') activeItems = activeItems.slice(0, 1);
    items.forEach((item) => {
        const active = activeItems.includes(item);
        item.setAttribute('data-active', active ? 'true' : 'false');
        const action = item.querySelector(':scope > .bw-sidebar-item-action');
        if (active) action?.setAttribute('aria-current', 'page');
        else action?.removeAttribute('aria-current');
    });
    activeItems.forEach((item) => {
        let group = item.closest('[data-bw-sidebar-group]');
        while (group) {
            setSidebarGroupExpanded(sidebar, group, true, {emit: false, persist: false});
            group = group.parentElement?.closest('[data-bw-sidebar-group]') || null;
        }
    });
};

const initialiseSidebar = (sidebar) => {
    if (sidebar.dataset.bwInitialised === 'true') return;
    resolveSidebarPlacement(sidebar);
    const stored = sidebarStoredState(sidebar);
    sidebar.querySelectorAll('[data-bw-sidebar-group]').forEach((group) => {
        const groupName = group.getAttribute('data-group-name');
        const storedValue = stored?.groups && Object.prototype.hasOwnProperty.call(stored.groups, groupName)
            ? stored.groups[groupName] : null;
        const expanded = storedValue === null
            ? group.getAttribute('data-initial-expanded') === 'true' : storedValue;
        setSidebarGroupExpanded(sidebar, group, expanded, {emit: false, persist: false});
    });
    if (sidebar.getAttribute('data-persist') === 'true' && typeof stored?.collapsed === 'boolean') {
        setSidebarCollapsed(sidebar, stored.collapsed, {emit: false, persist: false});
    }
    resolveSidebarActiveItem(sidebar);
    sidebar.dataset.bwInitialised = 'true';
};

const initialiseSidebars = () => document.querySelectorAll('[data-bw-sidebar]').forEach(initialiseSidebar);

const resetSidebar = (sidebarName) => {
    const sidebar = sidebarByName(sidebarName);
    if (!sidebar) return false;
    try { window.localStorage.removeItem(sidebar.getAttribute('data-storage-key')); } catch (_) {}
    sidebar.querySelectorAll('[data-bw-sidebar-group]').forEach((group) => {
        setSidebarGroupExpanded(sidebar, group, group.getAttribute('data-initial-expanded') === 'true', {emit: false, persist: false});
    });
    setSidebarCollapsed(sidebar, sidebar.getAttribute('data-initial-state') === 'collapsed', {emit: false, persist: false});
    resolveSidebarActiveItem(sidebar);
    return true;
};

/** Find one Data Grid without interpolating its public name into a selector. */
const dataGridByName = (name) => Array.from(document.querySelectorAll('[data-bw-data-grid]'))
    .find((candidate) => candidate.getAttribute('data-name') === String(name)) || null;

const dataGridDetail = (grid, values = {}) => ({name: grid.getAttribute('data-name'), ...values});

const dataGridEvent = (grid, name, detail, cancelable = false) => grid.dispatchEvent(
    new CustomEvent(`bladewind:data-grid:${name}`, {bubbles: true, cancelable, detail})
);

const dataGridRows = (grid) => Array.from(grid.querySelectorAll('[data-bw-data-grid-row]'));

const dataGridPageSize = (grid) => Number(grid.getAttribute('data-page-size')) || 25;

/** Text a sortable column header shows and search matches against — the value
 *  in data-sort-value when the row supplies one, otherwise the cell's own text. */
const dataGridCellValue = (row, key, attribute) => {
    const cell = row.querySelector(`[data-column="${key}"]`);
    return (attribute ? cell?.getAttribute(attribute) : null) ?? cell?.textContent?.trim() ?? '';
};

const compareDataGridValues = (a, b) => {
    const numA = Number(a);
    const numB = Number(b);
    if (a !== '' && b !== '' && !Number.isNaN(numA) && !Number.isNaN(numB)) return numA - numB;
    return a.localeCompare(b, undefined, {numeric: true, sensitivity: 'base'});
};

const dataGridOriginalOrder = new WeakMap();

const updateDataGridPaginationUi = (grid, page, totalPages) => {
    const pageSize = dataGridPageSize(grid);
    const totalRows = dataGridRows(grid).length;
    const from = totalRows === 0 ? 0 : ((page - 1) * pageSize) + 1;
    const to = Math.min(page * pageSize, totalRows);
    const label = grid.querySelector('[data-bw-data-grid-pagination-label]');
    if (label) label.textContent = `Page ${page} of ${totalPages}`;
    const summary = grid.querySelector('[data-bw-data-grid-pagination-summary]');
    if (summary) summary.textContent = totalRows === 0 ? '' : `Showing ${from}–${to} of ${totalRows}`;
    const prev = grid.querySelector('[data-bw-data-grid-page="prev"]');
    const next = grid.querySelector('[data-bw-data-grid-page="next"]');
    if (prev) prev.disabled = page <= 1;
    if (next) next.disabled = page >= totalPages;
};

const setDataGridPage = (gridName, page, options = {}) => {
    const grid = dataGridByName(gridName);
    if (!grid || grid.getAttribute('data-paginated') !== 'true') return false;
    const table = grid.querySelector('[data-bw-data-grid-table]');
    const totalPages = Math.max(1, Math.ceil(dataGridRows(grid).length / dataGridPageSize(grid)));
    const previousPage = Number(table?.getAttribute('data-current-page')) || 1;
    const nextPage = Math.min(Math.max(1, Number(page) || 1), totalPages);
    if (nextPage === previousPage && options.force !== true) return false;
    const detail = dataGridDetail(grid, {page: nextPage, previousPage});
    if (options.emit !== false && !dataGridEvent(grid, 'before-page-change', detail, true)) return false;
    dataGridRows(grid).forEach((row) => {
        row.hidden = Number(row.getAttribute('data-page')) !== nextPage;
    });
    table?.setAttribute('data-current-page', String(nextPage));
    updateDataGridPaginationUi(grid, nextPage, totalPages);
    if (options.emit !== false) dataGridEvent(grid, 'page-change', detail);
    return true;
};

/** Recompute data-page after a client-side sort or filter change reorders rows. */
const recomputeDataGridPages = (grid) => {
    if (grid.getAttribute('data-paginated') !== 'true') return;
    const pageSize = dataGridPageSize(grid);
    dataGridRows(grid).forEach((row, index) => row.setAttribute('data-page', String(Math.ceil((index + 1) / pageSize))));
    grid.querySelector('[data-bw-data-grid-table]')?.removeAttribute('data-current-page');
    setDataGridPage(grid.getAttribute('data-name'), 1, {emit: false, force: true});
};

const filterDataGrid = (grid, query) => {
    const normalised = query.trim().toLowerCase();
    const paginated = grid.getAttribute('data-paginated') === 'true';
    const table = grid.querySelector('[data-bw-data-grid-table]');
    const currentPage = Number(table?.getAttribute('data-current-page')) || 1;
    let visibleCount = 0;
    dataGridRows(grid).forEach((row) => {
        const matches = normalised === '' || (row.getAttribute('data-search') || '').includes(normalised);
        row.hidden = paginated && normalised === ''
            ? Number(row.getAttribute('data-page')) !== currentPage
            : !matches;
        if (matches) visibleCount++;
    });
    const empty = grid.querySelector('[data-bw-data-grid-empty]');
    if (empty) empty.hidden = visibleCount > 0;
    const pagination = grid.querySelector('[data-bw-data-grid-pagination]');
    if (pagination) pagination.hidden = normalised !== '';
    dataGridEvent(grid, 'search', dataGridDetail(grid, {query}));
};

const sortDataGrid = (gridName, key, direction) => {
    const grid = dataGridByName(gridName);
    if (!grid || grid.getAttribute('data-client-sort') !== 'true') return false;
    const body = grid.querySelector('[data-bw-data-grid-body]');
    const empty = grid.querySelector('[data-bw-data-grid-empty]');
    if (!body) return false;
    if (!dataGridOriginalOrder.has(grid)) dataGridOriginalOrder.set(grid, dataGridRows(grid));

    const detail = dataGridDetail(grid, {key, direction});
    if (!dataGridEvent(grid, 'before-sort-change', detail, true)) return false;

    grid.querySelectorAll('[data-bw-data-grid-sort]').forEach((button) => {
        button.setAttribute('data-direction', button.getAttribute('data-bw-data-grid-sort') === key && direction ? direction : 'none');
        button.closest('th')?.setAttribute('aria-sort', button.getAttribute('data-bw-data-grid-sort') === key && direction
            ? (direction === 'desc' ? 'descending' : 'ascending') : 'none');
    });

    if (!direction) {
        (dataGridOriginalOrder.get(grid) || []).forEach((row) => body.insertBefore(row, empty || null));
    } else {
        const sorted = [...dataGridRows(grid)].sort((a, b) => {
            const result = compareDataGridValues(
                dataGridCellValue(a, key, 'data-sort-value'),
                dataGridCellValue(b, key, 'data-sort-value')
            );
            return direction === 'desc' ? -result : result;
        });
        sorted.forEach((row) => body.insertBefore(row, empty || null));
    }
    recomputeDataGridPages(grid);
    dataGridEvent(grid, 'sort-change', detail);
    return true;
};

const dataGridSortCycle = (grid, key) => {
    const current = grid.querySelector(`[data-bw-data-grid-sort="${key}"]`)?.getAttribute('data-direction') || 'none';
    const clientSort = grid.getAttribute('data-client-sort') === 'true';
    const next = current === 'none' ? 'asc' : (current === 'asc' ? 'desc' : (clientSort ? null : 'asc'));
    if (clientSort) return sortDataGrid(grid.getAttribute('data-name'), key, next);
    grid.querySelectorAll('[data-bw-data-grid-sort]').forEach((button) => {
        const isTarget = button.getAttribute('data-bw-data-grid-sort') === key;
        button.setAttribute('data-direction', isTarget ? next : 'none');
        button.closest('th')?.setAttribute('aria-sort', isTarget ? (next === 'desc' ? 'descending' : 'ascending') : 'none');
    });
    dataGridEvent(grid, 'sort-change', dataGridDetail(grid, {key, direction: next}));
    return true;
};

/** All selection inputs by default; visibleOnly narrows to the current page/search
 *  results, which is what "select all" and the header checkbox's tri-state operate on. */
const dataGridSelectionInputs = (grid, {visibleOnly = false} = {}) => {
    const inputs = Array.from(grid.querySelectorAll('[data-bw-data-grid-select]'));
    return visibleOnly ? inputs.filter((input) => !input.closest('[data-bw-data-grid-row]')?.hidden) : inputs;
};

const updateDataGridSelectionUi = (grid) => {
    const checked = dataGridSelectionInputs(grid).filter((input) => input.checked);
    const bar = grid.querySelector('[data-bw-data-grid-selection-bar]');
    if (bar) bar.hidden = checked.length === 0;
    const count = grid.querySelector('[data-bw-data-grid-selection-count]');
    if (count) count.textContent = `${checked.length} selected`;
    const selectAll = grid.querySelector('[data-bw-data-grid-select-all]');
    if (selectAll) {
        const visible = dataGridSelectionInputs(grid, {visibleOnly: true}).filter((input) => !input.disabled);
        const visibleChecked = visible.filter((input) => input.checked);
        selectAll.checked = visible.length > 0 && visibleChecked.length === visible.length;
        selectAll.indeterminate = visibleChecked.length > 0 && visibleChecked.length < visible.length;
    }
};

const dataGridSelectedKeys = (gridName) => {
    const grid = dataGridByName(gridName);
    if (!grid) return [];
    return dataGridSelectionInputs(grid).filter((input) => input.checked).map((input) => input.value);
};

const setDataGridRowSelected = (input, selected, options = {}) => {
    const grid = input.closest('[data-bw-data-grid]');
    const row = input.closest('[data-bw-data-grid-row]');
    if (!grid || !row) return false;
    const detail = dataGridDetail(grid, {
        rowKey: row.getAttribute('data-row-key'),
        triggeringElement: input,
        source: options.source || 'pointer',
    });
    if (options.emit !== false && !dataGridEvent(grid, 'before-select-change', detail, true)) {
        input.checked = !selected;
        return false;
    }
    if (input.type === 'radio') {
        grid.querySelectorAll('[data-bw-data-grid-row]').forEach((candidate) => {
            candidate.setAttribute('aria-selected', 'false');
            candidate.classList.remove('bw-data-grid-row-selected');
        });
    }
    input.checked = selected;
    row.setAttribute('aria-selected', selected ? 'true' : 'false');
    row.classList.toggle('bw-data-grid-row-selected', selected);
    updateDataGridSelectionUi(grid);
    if (options.emit !== false) {
        dataGridEvent(grid, 'select-change', dataGridDetail(grid, {selected: dataGridSelectedKeys(grid.getAttribute('data-name'))}));
    }
    return true;
};

/** Toggles every currently visible, enabled row — the current page and/or search
 *  results, matching the header checkbox. Use clearDataGridSelection to reset
 *  selections made on other pages too. */
const selectAllDataGridRows = (gridName, selected) => {
    const grid = dataGridByName(gridName);
    if (!grid || grid.getAttribute('data-selection-mode') !== 'multiple') return false;
    const detail = dataGridDetail(grid, {source: 'select-all'});
    if (!dataGridEvent(grid, 'before-select-change', detail, true)) return false;
    dataGridSelectionInputs(grid, {visibleOnly: true}).forEach((input) => {
        if (input.disabled) return;
        input.checked = selected;
        const row = input.closest('[data-bw-data-grid-row]');
        row?.setAttribute('aria-selected', selected ? 'true' : 'false');
        row?.classList.toggle('bw-data-grid-row-selected', selected);
    });
    updateDataGridSelectionUi(grid);
    dataGridEvent(grid, 'select-change', dataGridDetail(grid, {selected: dataGridSelectedKeys(gridName)}));
    return true;
};

const clearDataGridSelection = (gridName) => {
    const grid = dataGridByName(gridName);
    if (!grid) return false;
    const detail = dataGridDetail(grid, {source: 'clear-selection'});
    if (!dataGridEvent(grid, 'before-select-change', detail, true)) return false;
    dataGridSelectionInputs(grid).forEach((input) => {
        input.checked = false;
        const row = input.closest('[data-bw-data-grid-row]');
        row?.setAttribute('aria-selected', 'false');
        row?.classList.remove('bw-data-grid-row-selected');
    });
    updateDataGridSelectionUi(grid);
    dataGridEvent(grid, 'select-change', dataGridDetail(grid, {selected: []}));
    return true;
};

const setDataGridLoading = (gridName, loading) => {
    const grid = dataGridByName(gridName);
    if (!grid) return false;
    grid.setAttribute('data-loading', loading ? 'true' : 'false');
    grid.querySelector('[data-bw-data-grid-table]')?.setAttribute('aria-busy', loading ? 'true' : 'false');
    return true;
};

const resetDataGrid = (gridName) => {
    const grid = dataGridByName(gridName);
    if (!grid) return false;
    const search = grid.querySelector('[data-bw-data-grid-search]');
    if (search) { search.value = ''; filterDataGrid(grid, ''); }
    grid.querySelectorAll('[data-bw-data-grid-sort]').forEach((button) => {
        if (button.getAttribute('data-direction') !== 'none') sortDataGrid(gridName, button.getAttribute('data-bw-data-grid-sort'), null);
    });
    clearDataGridSelection(gridName);
    if (grid.getAttribute('data-paginated') === 'true') setDataGridPage(gridName, 1, {emit: false, force: true});
    return true;
};

const initialiseDataGrid = (grid) => {
    if (grid.dataset.bwInitialised === 'true') return;
    updateDataGridSelectionUi(grid);
    if (grid.getAttribute('data-paginated') === 'true') {
        const totalPages = Math.max(1, Math.ceil(dataGridRows(grid).length / dataGridPageSize(grid)));
        updateDataGridPaginationUi(grid, 1, totalPages);
    }
    grid.dataset.bwInitialised = 'true';
};

const initialiseDataGrids = () => document.querySelectorAll('[data-bw-data-grid]').forEach(initialiseDataGrid);

/*
 | Delegated bindings for components whose behaviour lives in this file.
 | Replaces inline on* attributes, which a strict CSP blocks. See #608.
 */
bwOn('click', '[data-bw-tag-value]', (tag) => {
    selectTag(tag.getAttribute('data-bw-tag-value'), tag.getAttribute('data-bw-tag-name'));
});

// a closable tag with no custom onclick simply removes itself
bwOn('click', '[data-bw-tag-remove]', (link) => link.parentElement?.remove());

// the modal's own close buttons. a consumer-supplied ok/cancel action is their
// javascript and stays inline, so it is not handled here
bwOn('click', '[data-bw-modal-close]', (el) => hideModal(el.getAttribute('data-bw-modal-close')));
bwOn('click', '[data-bw-drawer-close]', (el) => hideDrawer(el.getAttribute('data-bw-drawer-close')));
bwOn('click', '[data-bw-drawer-backdrop]', (backdrop) => {
    const drawer = backdrop.closest('[data-bw-drawer]');
    if (drawer?.getAttribute('data-backdrop-can-close') === 'true') hideDrawer(drawer.getAttribute('data-name'));
});

document.addEventListener('keydown', (event) => {
    const name = openDrawers[openDrawers.length - 1];
    const drawer = name ? drawerByName(name) : null;
    if (!drawer) return;
    if (event.key === 'Escape' && drawer.getAttribute('data-escape-can-close') === 'true') {
        event.preventDefault();
        hideDrawer(name);
        return;
    }
    if (event.key !== 'Tab' || drawer.getAttribute('data-modal') !== 'true') return;
    const focusable = drawerFocusable(drawer);
    if (focusable.length === 0) {
        event.preventDefault();
        drawer.querySelector('.bw-drawer-panel')?.focus();
        return;
    }
    const first = focusable[0];
    const last = focusable[focusable.length - 1];
    if (!drawer.contains(document.activeElement)) {
        event.preventDefault();
        first.focus();
        return;
    }
    if (event.shiftKey && document.activeElement === first) {
        event.preventDefault();
        last.focus();
    } else if (!event.shiftKey && document.activeElement === last) {
        event.preventDefault();
        first.focus();
    }
});

if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initialiseDrawers);
else initialiseDrawers();

bwOn('click', '[data-bw-command-palette-close]', (el) => closeCommandPalette(
    el.getAttribute('data-bw-command-palette-close'), {triggeringElement: el, source: 'pointer'}
));

bwOn('click', '[data-bw-command-palette-backdrop]', (backdrop) => {
    const palette = backdrop.closest('[data-bw-command-palette]');
    if (palette?.getAttribute('data-backdrop-can-close') === 'true') {
        closeCommandPalette(palette.getAttribute('data-name'), {triggeringElement: backdrop, source: 'backdrop'});
    }
});

bwOn('click', '[data-bw-command-palette-item]', (item, event) => {
    const palette = item.closest('[data-bw-command-palette]');
    if (!palette) return;
    if (!activateCommandPaletteItem(palette, item, {triggeringElement: item, source: event.detail === 0 ? 'keyboard' : 'pointer'})) {
        event.preventDefault();
    }
});

bwOn('mouseover', '[data-bw-command-palette-item]', (item) => {
    if (item.hidden || item.getAttribute('aria-disabled') === 'true') return;
    const palette = item.closest('[data-bw-command-palette]');
    if (palette) highlightCommandPaletteItem(palette, item);
});

bwOn('input', '[data-bw-command-palette-input]', (input) => {
    const palette = input.closest('[data-bw-command-palette]');
    if (palette) filterCommandPalette(palette, input.value);
});

document.addEventListener('keydown', (event) => {
    if (event.defaultPrevented) return;
    document.querySelectorAll('[data-bw-command-palette]').forEach((palette) => {
        const shortcut = palette.getAttribute('data-shortcut');
        if (!shortcut || !commandPaletteShortcutMatches(shortcut, event)) return;
        event.preventDefault();
        toggleCommandPalette(palette.getAttribute('data-name'), {triggeringElement: document.activeElement, source: 'shortcut'});
    });
});

document.addEventListener('keydown', (event) => {
    const name = openCommandPalettes[openCommandPalettes.length - 1];
    const palette = name ? commandPaletteByName(name) : null;
    if (!palette) return;
    if (event.key === 'Escape' && palette.getAttribute('data-escape-can-close') === 'true') {
        event.preventDefault();
        closeCommandPalette(name, {triggeringElement: document.activeElement, source: 'escape'});
        return;
    }
    if (event.key === 'ArrowDown') { event.preventDefault(); moveCommandPaletteHighlight(palette, 'down'); return; }
    if (event.key === 'ArrowUp') { event.preventDefault(); moveCommandPaletteHighlight(palette, 'up'); return; }
    if (event.key === 'Home') { event.preventDefault(); moveCommandPaletteHighlight(palette, 'first'); return; }
    if (event.key === 'End') { event.preventDefault(); moveCommandPaletteHighlight(palette, 'last'); return; }
    if (event.key === 'Enter') {
        event.preventDefault();
        highlightedCommandPaletteItem(palette)?.click();
        return;
    }
    if (event.key !== 'Tab') return;
    const focusable = commandPaletteFocusable(palette);
    if (focusable.length === 0) return;
    const first = focusable[0];
    const last = focusable[focusable.length - 1];
    if (!palette.contains(document.activeElement)) {
        event.preventDefault();
        first.focus();
        return;
    }
    if (event.shiftKey && document.activeElement === first) {
        event.preventDefault();
        last.focus();
    } else if (!event.shiftKey && document.activeElement === last) {
        event.preventDefault();
        first.focus();
    }
});

if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initialiseCommandPalettes);
else initialiseCommandPalettes();

bwOn('click', '[data-bw-sidebar-group-trigger]', (trigger) => {
    const sidebar = trigger.closest('[data-bw-sidebar]');
    const group = trigger.closest('[data-bw-sidebar-group]');
    if (!sidebar || !group) return;
    setSidebarGroupExpanded(sidebar, group, group.getAttribute('data-expanded') !== 'true', {
        triggeringElement: trigger,
        source: 'pointer',
    });
});

bwOn('click', '[data-bw-sidebar-collapse-control]', (control) => {
    const sidebar = control.closest('[data-bw-sidebar]');
    if (!sidebar) return;
    setSidebarCollapsed(sidebar, sidebar.getAttribute('data-state') !== 'collapsed', {
        triggeringElement: control,
        source: 'pointer',
    });
});

bwOn('click', '[data-bw-sidebar-close]', (control) => {
    const sidebar = control.closest('[data-bw-sidebar]');
    if (sidebar) closeSidebar(sidebar.getAttribute('data-name'), {triggeringElement: control, source: 'pointer'});
});

bwOn('click', '[data-bw-sidebar-item-action]', (action, event) => {
    const sidebar = action.closest('[data-bw-sidebar]');
    const item = action.closest('[data-bw-sidebar-item]');
    if (!sidebar || !item || item.getAttribute('data-disabled') === 'true') return;
    const isLink = action.tagName === 'A';
    const detail = sidebarDetail(sidebar, {
        itemName: item.getAttribute('data-item-name'),
        href: isLink ? action.getAttribute('href') : null,
        triggeringElement: action,
        source: event.detail === 0 ? 'keyboard' : 'pointer',
    });
    const eventName = isLink ? 'before-navigate' : 'item-activate';
    if (!sidebarEvent(sidebar, eventName, detail, true)) {
        event.preventDefault();
        return;
    }
    if (sidebarPresentation() === 'mobile' && sidebar.getAttribute('data-close-on-navigate') === 'true') {
        closeSidebar(sidebar.getAttribute('data-name'), {triggeringElement: action, source: 'navigation'});
    }
});

bwOn('keydown', '[data-bw-sidebar-focusable]', (current, event) => {
    const sidebar = current.closest('[data-bw-sidebar]');
    if (!sidebar) return;
    const focusable = Array.from(sidebar.querySelectorAll('[data-bw-sidebar-focusable]')).filter((candidate) =>
        candidate.getAttribute('aria-disabled') !== 'true' && !candidate.closest('[hidden]')
    );
    const index = focusable.indexOf(current);
    if (index < 0) return;
    let target = null;
    if (event.key === 'ArrowDown') target = focusable[(index + 1) % focusable.length];
    else if (event.key === 'ArrowUp') target = focusable[(index - 1 + focusable.length) % focusable.length];
    else if (event.key === 'Home') target = focusable[0];
    else if (event.key === 'End') target = focusable[focusable.length - 1];

    const rtl = window.getComputedStyle?.(sidebar)?.direction === 'rtl';
    const openKey = rtl ? 'ArrowLeft' : 'ArrowRight';
    const closeKey = rtl ? 'ArrowRight' : 'ArrowLeft';
    const group = current.closest('[data-bw-sidebar-group]');
    if (current.hasAttribute('data-bw-sidebar-group-trigger') && (event.key === 'Enter' || event.key === ' ')) {
        event.preventDefault();
        setSidebarGroupExpanded(sidebar, group, group?.getAttribute('data-expanded') !== 'true', {
            triggeringElement: current,
            source: 'keyboard',
        });
        return;
    }
    if (current.hasAttribute('data-bw-sidebar-group-trigger') && event.key === openKey) {
        if (group?.getAttribute('data-expanded') !== 'true') {
            setSidebarGroupExpanded(sidebar, group, true, {triggeringElement: current, source: 'keyboard'});
        } else {
            target = Array.from(group.querySelectorAll('[data-bw-sidebar-focusable]'))
                .find((candidate) => candidate !== current && !candidate.closest('[hidden]')) || null;
        }
    } else if (group && event.key === closeKey) {
        const groupTrigger = group.querySelector(':scope > [data-bw-sidebar-group-trigger]');
        if (current === groupTrigger && group.getAttribute('data-expanded') === 'true') {
            setSidebarGroupExpanded(sidebar, group, false, {triggeringElement: current, source: 'keyboard'});
        } else {
            target = groupTrigger;
        }
    }
    if (!target) return;
    event.preventDefault();
    target.focus({preventScroll: true});
});

document.addEventListener('click', (event) => {
    const backdrop = event.target?.closest?.('[data-bw-drawer-backdrop]');
    const drawer = backdrop?.closest?.('[data-bw-drawer]');
    const sidebar = drawer?.querySelector?.('[data-bw-sidebar]');
    if (!sidebar || drawer.getAttribute('data-backdrop-can-close') !== 'true') return;
    event.preventDefault();
    event.stopImmediatePropagation();
    closeSidebar(sidebar.getAttribute('data-name'), {triggeringElement: backdrop, source: 'backdrop'});
}, true);

document.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape') return;
    const drawer = drawerByName(openDrawers[openDrawers.length - 1]);
    const sidebar = drawer?.querySelector?.('[data-bw-sidebar]');
    if (!sidebar || drawer.getAttribute('data-escape-can-close') !== 'true') return;
    event.preventDefault();
    event.stopImmediatePropagation();
    closeSidebar(sidebar.getAttribute('data-name'), {triggeringElement: document.activeElement, source: 'escape'});
}, true);

document.addEventListener('bladewind:drawer-opened', (event) => {
    const sidebar = event.target?.querySelector?.('[data-bw-sidebar]');
    if (!sidebar) return;
    sidebarEvent(sidebar, 'opened', sidebar._bwTransitionDetail || sidebarDetail(sidebar, {previousState: 'closed', nextState: 'open'}));
    delete sidebar._bwTransitionDetail;
});

document.addEventListener('bladewind:drawer-closed', (event) => {
    const sidebar = event.target?.querySelector?.('[data-bw-sidebar]');
    if (!sidebar) return;
    const detail = sidebar._bwTransitionDetail || sidebarDetail(sidebar, {previousState: 'open', nextState: 'closed'});
    moveSidebarToDesktop(sidebar);
    sidebarEvent(sidebar, 'closed', detail);
    delete sidebar._bwTransitionDetail;
});

if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initialiseSidebars);
else initialiseSidebars();

window.addEventListener('resize', () => {
    document.querySelectorAll('[data-bw-sidebar]').forEach((sidebar) => {
        resolveSidebarPlacement(sidebar);
        if (sidebarPresentation() !== 'desktop') return;
        const drawer = drawerByName(sidebar.getAttribute('data-drawer-name'));
        if (drawer && !drawer.hidden) hideDrawer(sidebar.getAttribute('data-drawer-name'));
        else moveSidebarToDesktop(sidebar);
    });
});

bwOn('input', '[data-bw-data-grid-search]', (input) => {
    const grid = input.closest('[data-bw-data-grid]');
    if (grid?.getAttribute('data-client-search') === 'true') filterDataGrid(grid, input.value);
    else if (grid) dataGridEvent(grid, 'search', dataGridDetail(grid, {query: input.value}));
});

bwOn('click', '[data-bw-data-grid-sort]', (button) => {
    const grid = button.closest('[data-bw-data-grid]');
    const key = button.getAttribute('data-bw-data-grid-sort');
    if (grid && key) dataGridSortCycle(grid, key);
});

bwOn('change', '[data-bw-data-grid-select-all]', (checkbox) => {
    const grid = checkbox.closest('[data-bw-data-grid]');
    if (grid) selectAllDataGridRows(grid.getAttribute('data-name'), checkbox.checked);
});

bwOn('change', '[data-bw-data-grid-select]', (input) => {
    setDataGridRowSelected(input, input.checked, {source: 'pointer'});
});

bwOn('click', '[data-bw-data-grid-clear-selection]', (button) => {
    clearDataGridSelection(button.getAttribute('data-bw-data-grid-clear-selection'));
});

bwOn('click', '[data-bw-data-grid-page]', (button) => {
    const grid = button.closest('[data-bw-data-grid]');
    if (!grid) return;
    const table = grid.querySelector('[data-bw-data-grid-table]');
    const currentPage = Number(table?.getAttribute('data-current-page')) || 1;
    const direction = button.getAttribute('data-bw-data-grid-page');
    setDataGridPage(grid.getAttribute('data-name'), direction === 'next' ? currentPage + 1 : currentPage - 1);
});

if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initialiseDataGrids);
else initialiseDataGrids();

/**
 * Calendar
 *
 * The server renders the requested month/week in full, including events, so
 * the calendar works with no JS at all. Navigating (prev/next/today/PageUp,
 * PageDown, or crossing a month boundary with the arrow keys) rebuilds the
 * grid in the browser instead of round-tripping to the server, using pure
 * date math plus the `events` the page already passed in — see
 * initBladewindCalendar. Set `client-navigation="false"` on the component for
 * a server-driven calendar instead: navigation then only fires
 * before-navigate/navigate and the application re-renders.
 */
const bwCalendarRegistry = {};

/** Populates the per-instance registry the client-side renderer reads from. Called inline by the component itself, once per instance, before any interaction is possible. */
const initBladewindCalendar = ({name, monthNames, dayNames, events}) => {
    bwCalendarRegistry[name] = {monthNames, dayNames, ...buildCalendarEventIndexes(events)};
};

/** A timed event carries a clock time in `date`; anything else is all-day. Mirrors the PHP component's own detection so client-side navigation renders identically to the server. */
const isCalendarTimedEvent = (value) => /\d{1,2}:\d{2}/.test(String(value || ''));

const calendarAddMinutes = (date, minutes) => { const d = new Date(date); d.setMinutes(d.getMinutes() + minutes); return d; };

/** e.g. "9:00am", "2:30pm" — mirrors the PHP side's Carbon 'g:ia' format. */
const calendarFormatHourMinute = (date) => {
    let hours = date.getHours();
    const minutes = String(date.getMinutes()).padStart(2, '0');
    const ampm = hours >= 12 ? 'pm' : 'am';
    hours %= 12; if (hours === 0) hours = 12;
    return `${hours}:${minutes}${ampm}`;
};

/** e.g. "7 AM", "12 PM" — the week grid's hour-of-day gutter labels. */
const calendarFormatHourLabel = (hour) => {
    const ampm = hour >= 12 ? 'PM' : 'AM';
    let h = hour % 12; if (h === 0) h = 12;
    return `${h} ${ampm}`;
};

/**
 * Splits raw events into what each view needs:
 *  - monthMarkersIndex: per-date list behind month view's markers — all-day
 *    events as-is, timed events prefixed with their start time and sorted
 *    after the all-day ones, chronologically among themselves
 *  - timedIndex: per-date list of timed events for week view's hour grid
 *  - allDaySpans: all-day events with their original (unexpanded) date range,
 *    for week view's all-day row to clip and span across day columns
 */
const buildCalendarEventIndexes = (events) => {
    const monthMarkersIndex = {};
    const timedIndex = {};
    const allDaySpans = [];
    const timedByDate = {};

    (events || []).forEach((event) => {
        if (!event.date) return;

        if (isCalendarTimedEvent(event.date)) {
            const start = new Date(event.date.replace(' ', 'T'));
            let end = event.end && isCalendarTimedEvent(event.end) ? new Date(event.end.replace(' ', 'T')) : calendarAddMinutes(start, 60);
            if (end <= start) end = calendarAddMinutes(start, 60);
            const dayEnd = new Date(start); dayEnd.setHours(23, 59, 0, 0);
            if (end > dayEnd) end = dayEnd;

            const key = calendarISO(start);
            const item = {
                label: event.label || '', type: event.type || 'info', href: event.href || null,
                start, end, startMinutes: start.getHours() * 60 + start.getMinutes(), endMinutes: end.getHours() * 60 + end.getMinutes(),
            };
            (timedIndex[key] ??= []).push(item);
            (timedByDate[key] ??= []).push(item);
            return;
        }

        const start = new Date(`${event.date}T00:00:00`);
        const end = event.end ? new Date(`${event.end}T00:00:00`) : new Date(start);
        allDaySpans.push({label: event.label || '', type: event.type || 'info', href: event.href || null, start: new Date(start), end: new Date(end)});

        let cursor = new Date(start);
        let guard = 0;
        while (cursor <= end && guard < 366) {
            const key = calendarISO(cursor);
            (monthMarkersIndex[key] ??= []).push({label: event.label || '', type: event.type || 'info', href: event.href || null});
            cursor = calendarAddDays(cursor, 1);
            guard++;
        }
    });

    Object.keys(timedByDate).forEach((key) => {
        timedByDate[key].slice().sort((a, b) => a.startMinutes - b.startMinutes).forEach((timed) => {
            (monthMarkersIndex[key] ??= []).push({label: `${calendarFormatHourMinute(timed.start)} ${timed.label}`, type: timed.type, href: timed.href});
        });
    });

    return {monthMarkersIndex, timedIndex, allDaySpans};
};

/** Side-by-side column layout for a day's timed events: a run of mutually overlapping events gets one column each, sized to fit the widest moment in that run. Events that overlap nothing take the full width. Mirrors the PHP component's own layout exactly. */
const packCalendarTimedEvents = (events) => {
    const sorted = events.slice().sort((a, b) => a.startMinutes - b.startMinutes);
    const placed = [];
    let cluster = [];
    let clusterEndMinutes = null;

    const flush = () => {
        if (!cluster.length) return;
        const columns = [];
        const startAt = placed.length;
        cluster.forEach((item) => {
            let placedCol = null;
            for (let colIndex = 0; colIndex < columns.length; colIndex++) {
                if (item.startMinutes >= columns[colIndex]) { placedCol = colIndex; break; }
            }
            placedCol ??= columns.length;
            columns[placedCol] = item.endMinutes;
            placed.push({...item, col: placedCol});
        });
        const totalCols = columns.length;
        for (let i = startAt; i < placed.length; i++) placed[i].totalCols = totalCols;
        cluster = [];
    };

    sorted.forEach((event) => {
        if (clusterEndMinutes !== null && event.startMinutes >= clusterEndMinutes) { flush(); clusterEndMinutes = null; }
        cluster.push(event);
        clusterEndMinutes = clusterEndMinutes === null ? event.endMinutes : Math.max(clusterEndMinutes, event.endMinutes);
    });
    flush();

    return placed;
};

/** Stacks all-day banners onto as few rows as they need, so overlapping ones don't sit on top of each other. Mirrors the PHP component's own layout exactly. */
const packCalendarAllDayBanners = (rawBanners) => {
    const sorted = rawBanners.slice().sort((a, b) => a.startIndex - b.startIndex);
    const rowEnds = [];
    const placed = [];
    sorted.forEach((banner) => {
        const bannerEnd = banner.startIndex + banner.span - 1;
        let placedRow = null;
        for (let rowIndex = 0; rowIndex < rowEnds.length; rowIndex++) {
            if (banner.startIndex > rowEnds[rowIndex]) { placedRow = rowIndex; break; }
        }
        placedRow ??= rowEnds.length;
        rowEnds[placedRow] = bannerEnd;
        placed.push({...banner, row: placedRow});
    });
    return placed;
};

const calendarISO = (date) => `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
const calendarAddDays = (date, days) => { const d = new Date(date); d.setDate(d.getDate() + days); return d; };
const calendarSameMonth = (a, b) => a.getFullYear() === b.getFullYear() && a.getMonth() === b.getMonth();
const calendarSameDay = (a, b) => calendarISO(a) === calendarISO(b);

const calendarStartOfWeek = (date, weekStarts) => {
    const d = new Date(date);
    d.setHours(0, 0, 0, 0);
    const offset = weekStarts === 'monday' ? 1 : 0;
    d.setDate(d.getDate() - ((d.getDay() - offset + 7) % 7));
    return d;
};

/** ISO-8601 week number. */
const calendarWeekOfYear = (date) => {
    const d = new Date(Date.UTC(date.getFullYear(), date.getMonth(), date.getDate()));
    d.setUTCDate(d.getUTCDate() + 4 - (d.getUTCDay() || 7));
    const yearStart = new Date(Date.UTC(d.getUTCFullYear(), 0, 1));
    return Math.ceil((((d - yearStart) / 86400000) + 1) / 7);
};

const computeCalendarGrid = (anchor, view, weekStarts) => {
    let gridStart, gridEnd, periodMonth;
    if (view === 'day') {
        gridStart = new Date(anchor); gridStart.setHours(0, 0, 0, 0);
        gridEnd = new Date(gridStart);
        periodMonth = anchor.getMonth();
    } else if (view === 'week') {
        gridStart = calendarStartOfWeek(anchor, weekStarts);
        gridEnd = calendarAddDays(gridStart, 6);
        periodMonth = anchor.getMonth();
    } else {
        const monthStart = new Date(anchor.getFullYear(), anchor.getMonth(), 1);
        const monthEnd = new Date(anchor.getFullYear(), anchor.getMonth() + 1, 0);
        gridStart = calendarStartOfWeek(monthStart, weekStarts);
        gridEnd = calendarAddDays(calendarStartOfWeek(monthEnd, weekStarts), 6);
        periodMonth = anchor.getMonth();
    }

    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const days = [];
    let cursor = new Date(gridStart);
    while (cursor <= gridEnd) {
        days.push({
            date: new Date(cursor),
            iso: calendarISO(cursor),
            day: cursor.getDate(),
            inPeriod: view === 'week' || view === 'day' || cursor.getMonth() === periodMonth,
            isToday: calendarSameDay(cursor, today),
            weekNumber: calendarWeekOfYear(cursor),
        });
        cursor = calendarAddDays(cursor, 1);
    }

    // month/week view always divide evenly into chunks of 7; day view's
    // single day is its own one-item chunk
    const weeks = [];
    const chunkSize = view === 'day' ? 1 : 7;
    for (let i = 0; i < days.length; i += chunkSize) weeks.push(days.slice(i, i + chunkSize));
    return weeks;
};

const calendarPeriodLabel = (anchor, view, weekStarts, monthNames, dayNames) => {
    if (view === 'day') {
        return `${dayNames[anchor.getDay()]}, ${monthNames[anchor.getMonth()]} ${anchor.getDate()}, ${anchor.getFullYear()}`;
    }
    if (view === 'week') {
        const start = calendarStartOfWeek(anchor, weekStarts);
        const end = calendarAddDays(start, 6);
        const short = (d) => `${monthNames[d.getMonth()].slice(0, 3)} ${d.getDate()}`;
        return calendarSameMonth(start, end)
            ? `${short(start)} – ${end.getDate()}, ${end.getFullYear()}`
            : `${short(start)} – ${short(end)}, ${end.getFullYear()}`;
    }
    return `${monthNames[anchor.getMonth()]} ${anchor.getFullYear()}`;
};

const calendarByName = (name) => Array.from(document.querySelectorAll('[data-bw-calendar]'))
    .find((candidate) => candidate.getAttribute('data-name') === String(name)) || null;

const calendarDetail = (calendar, values = {}) => ({name: calendar.getAttribute('data-name'), ...values});

const calendarEvent = (calendar, name, detail, cancelable = false) => calendar.dispatchEvent(
    new CustomEvent(`bladewind:calendar:${name}`, {bubbles: true, cancelable, detail})
);

const calendarAnchorDate = (calendar) => new Date(`${calendar.getAttribute('data-anchor')}T00:00:00`);

const calendarConstraints = (calendar) => ({
    min: calendar.dataset.minDate ? new Date(`${calendar.dataset.minDate}T00:00:00`) : null,
    max: calendar.dataset.maxDate ? new Date(`${calendar.dataset.maxDate}T00:00:00`) : null,
    disabled: new Set((calendar.dataset.disabledDates || '').split(',').filter(Boolean)),
});

const calendarDayCell = (calendar, iso) => calendar.querySelector(`[data-bw-calendar-day][data-date="${iso}"]`);

const calendarSelectedDates = (name) => {
    const calendar = calendarByName(name);
    if (!calendar) return [];
    return Array.from(calendar.querySelectorAll('[data-bw-calendar-input]')).map((input) => input.value);
};

const buildCalendarCellLabel = (date, dayNames, monthNames) => `${dayNames[date.getDay()]}, ${monthNames[date.getMonth()]} ${date.getDate()}, ${date.getFullYear()}`;

/** Rebuilds the header title and every day cell for `anchor`, then re-derives the roving tabindex target. Used only when client navigation is enabled. */
const CALENDAR_WEEK_HOUR_ROW_REM = 3; // must match --bw-calendar hour row height in calendar.css
const CALENDAR_WEEK_HOURS_IN_DAY = 24;
const CALENDAR_WEEK_SCROLL_TO_HOUR = 7; // a sensible default window into the day, matching most calendars' behaviour

const calendarRemToPx = (rem) => rem * (parseFloat(getComputedStyle(document.documentElement).fontSize) || 16);

/** The hour body itself doesn't scroll — the sticky header and all-day rows sit
 * above it in the same scrollable ancestor, .bw-calendar-scroll, so its
 * distance from that ancestor's top has to be added in rather than assumed. */
const scrollCalendarWeekBodyToHour = (calendar, hour = CALENDAR_WEEK_SCROLL_TO_HOUR) => {
    const scroller = calendar.querySelector('[data-bw-calendar-scroll]');
    const body = calendar.querySelector('[data-bw-calendar-week-body]');
    if (!scroller || !body) return;
    const bodyOffset = body.getBoundingClientRect().top - scroller.getBoundingClientRect().top + scroller.scrollTop;
    scroller.scrollTop = bodyOffset + calendarRemToPx(CALENDAR_WEEK_HOUR_ROW_REM) * hour;
};

/** Builds one week's hour grid (day headers, all-day banners, and the scrollable hour body) fresh into `scroll`. Returns the previously-focused date's iso if it's still on screen, else null. */
const renderCalendarWeekGrid = (calendar, scroll, weekDays, registry, previouslyFocused, ctx) => {
    const {selectable, min, max, disabled, selected, showWeekNumbers} = ctx;

    const week = document.createElement('div');
    week.className = 'bw-calendar-week';
    week.setAttribute('data-bw-calendar-week', '');
    week.setAttribute('aria-labelledby', calendar.querySelector('[data-bw-calendar-title]')?.id || '');
    week.style.setProperty('--bw-calendar-week-days', String(weekDays.length));

    let focusIso = null;

    const headerRow = document.createElement('div');
    headerRow.className = 'bw-calendar-week-header-row';
    headerRow.setAttribute('role', 'row');
    const headerGutter = document.createElement('div');
    headerGutter.className = 'bw-calendar-week-gutter';
    if (showWeekNumbers) {
        const num = document.createElement('span');
        num.textContent = `W${weekDays[0].weekNumber}`;
        headerGutter.appendChild(num);
    }
    headerRow.appendChild(headerGutter);

    weekDays.forEach((day) => {
        const isDisabled = (min && day.date < min) || (max && day.date > max) || disabled.has(day.iso);
        const isSelected = selected.has(day.iso);
        const header = document.createElement('div');
        header.setAttribute('role', 'gridcell');
        header.setAttribute('data-bw-calendar-day', '');
        header.setAttribute('data-date', day.iso);
        header.tabIndex = -1;
        header.setAttribute('aria-label', buildCalendarCellLabel(day.date, registry.dayNames, registry.monthNames));
        if (selectable !== 'none') header.setAttribute('aria-selected', isSelected ? 'true' : 'false');
        if (day.isToday) header.setAttribute('aria-current', 'date');
        if (isDisabled) header.setAttribute('aria-disabled', 'true');
        header.className = 'bw-calendar-week-day-header'
            + (day.isToday ? ' bw-calendar-cell-today' : '')
            + (isSelected ? ' bw-calendar-cell-selected' : '')
            + (isDisabled ? ' bw-calendar-cell-disabled' : '');

        const dayName = document.createElement('span');
        dayName.className = 'bw-calendar-week-day-name';
        dayName.setAttribute('aria-hidden', 'true');
        dayName.textContent = registry.dayNames[day.date.getDay()].slice(0, 3);
        header.appendChild(dayName);

        const dateSpan = document.createElement('span');
        dateSpan.className = 'bw-calendar-cell-date';
        dateSpan.textContent = String(day.day);
        header.appendChild(dateSpan);

        headerRow.appendChild(header);
        if (!focusIso && day.iso === previouslyFocused) focusIso = day.iso;
    });
    week.appendChild(headerRow);

    const gridStart = weekDays[0].date;
    const gridEnd = weekDays[weekDays.length - 1].date;
    const rawAllDay = [];
    (registry.allDaySpans || []).forEach((event) => {
        const clipStart = event.start < gridStart ? gridStart : event.start;
        const clipEnd = event.end > gridEnd ? gridEnd : event.end;
        if (clipStart > gridEnd || clipEnd < gridStart) return;
        const startIndex = Math.round((clipStart - gridStart) / 86400000);
        const endIndex = Math.round((clipEnd - gridStart) / 86400000);
        rawAllDay.push({label: event.label, type: event.type, href: event.href, startIndex, span: endIndex - startIndex + 1});
    });
    const banners = packCalendarAllDayBanners(rawAllDay);

    if (banners.length) {
        const alldayRow = document.createElement('div');
        alldayRow.className = 'bw-calendar-week-allday-row';
        alldayRow.setAttribute('role', 'row');
        const alldayGutter = document.createElement('div');
        alldayGutter.className = 'bw-calendar-week-gutter bw-calendar-week-allday-label';
        alldayGutter.textContent = 'All day';
        alldayRow.appendChild(alldayGutter);
        const track = document.createElement('div');
        track.className = 'bw-calendar-week-allday-track';
        banners.forEach((banner) => {
            const el = document.createElement(banner.href ? 'a' : 'span');
            if (banner.href) el.href = banner.href;
            el.className = `bw-calendar-event bw-calendar-week-allday-banner bw-calendar-event-${banner.type}`;
            el.textContent = banner.label;
            el.style.gridColumn = `${banner.startIndex + 1} / span ${banner.span}`;
            el.style.gridRow = String(banner.row + 1);
            track.appendChild(el);
        });
        alldayRow.appendChild(track);
        week.appendChild(alldayRow);
    }

    const body = document.createElement('div');
    body.className = 'bw-calendar-week-body';
    body.setAttribute('data-bw-calendar-week-body', '');
    body.style.height = `${CALENDAR_WEEK_HOURS_IN_DAY * CALENDAR_WEEK_HOUR_ROW_REM}rem`;

    const hours = document.createElement('div');
    hours.className = 'bw-calendar-week-hours';
    hours.setAttribute('aria-hidden', 'true');
    for (let h = 0; h < CALENDAR_WEEK_HOURS_IN_DAY; h++) {
        const label = document.createElement('div');
        label.className = 'bw-calendar-week-hour-label';
        label.style.top = `${h * CALENDAR_WEEK_HOUR_ROW_REM}rem`;
        label.textContent = calendarFormatHourLabel(h);
        hours.appendChild(label);
    }
    body.appendChild(hours);

    const days = document.createElement('div');
    days.className = 'bw-calendar-week-days';
    weekDays.forEach((day) => {
        const column = document.createElement('div');
        column.className = 'bw-calendar-week-day-column' + (day.isToday ? ' bw-calendar-week-day-column-today' : '');
        column.setAttribute('data-date', day.iso);
        for (let h = 1; h < CALENDAR_WEEK_HOURS_IN_DAY; h++) {
            const line = document.createElement('div');
            line.className = 'bw-calendar-week-hour-line';
            line.style.top = `${h * CALENDAR_WEEK_HOUR_ROW_REM}rem`;
            line.setAttribute('aria-hidden', 'true');
            column.appendChild(line);
        }

        packCalendarTimedEvents(registry.timedIndex[day.iso] || []).forEach((event) => {
            const top = (event.startMinutes / 60) * CALENDAR_WEEK_HOUR_ROW_REM;
            const height = Math.max(1.25, ((event.endMinutes - event.startMinutes) / 60) * CALENDAR_WEEK_HOUR_ROW_REM);
            const widthPct = 100 / event.totalCols;
            const leftPct = widthPct * event.col;
            const el = document.createElement(event.href ? 'a' : 'span');
            if (event.href) el.href = event.href;
            el.className = `bw-calendar-event bw-calendar-week-timed-event bw-calendar-event-${event.type}`;
            el.style.top = `${top}rem`;
            el.style.height = `${height}rem`;
            el.style.left = `${leftPct}%`;
            el.style.width = `calc(${widthPct}% - 2px)`;
            const timeSpan = document.createElement('span');
            timeSpan.className = 'bw-calendar-week-timed-event-time';
            timeSpan.textContent = calendarFormatHourMinute(event.start);
            const labelSpan = document.createElement('span');
            labelSpan.className = 'bw-calendar-week-timed-event-label';
            labelSpan.textContent = event.label;
            el.appendChild(timeSpan);
            el.appendChild(labelSpan);
            column.appendChild(el);
        });

        days.appendChild(column);
    });
    body.appendChild(days);
    week.appendChild(body);

    scroll.appendChild(week);
    return focusIso;
};

/** Builds one month's `<table>` fresh into `scroll`, including the weekday header row. Returns the previously-focused date's iso if it's still on screen, else null. */
const renderCalendarMonthTable = (calendar, scroll, weeks, registry, previouslyFocused, ctx) => {
    const {selectable, maxEventsPerDay, showOtherMonthDays, showWeekNumbers, min, max, disabled, selected} = ctx;
    const weekStarts = calendar.getAttribute('data-week-starts');

    const table = document.createElement('table');
    table.className = 'bw-calendar-grid';
    table.setAttribute('data-bw-calendar-table', '');
    table.setAttribute('role', 'grid');
    table.setAttribute('aria-labelledby', calendar.querySelector('[data-bw-calendar-title]')?.id || '');

    const thead = document.createElement('thead');
    const headRow = document.createElement('tr');
    headRow.setAttribute('role', 'row');
    if (showWeekNumbers) {
        const th = document.createElement('th');
        th.className = 'bw-calendar-week-number-header';
        th.setAttribute('scope', 'col');
        const sr = document.createElement('span');
        sr.className = 'sr-only';
        sr.textContent = 'Week';
        th.appendChild(sr);
        headRow.appendChild(th);
    }
    const dayOrder = weekStarts === 'monday' ? [1, 2, 3, 4, 5, 6, 0] : [0, 1, 2, 3, 4, 5, 6];
    dayOrder.forEach((dow) => {
        const label = registry.dayNames[dow];
        const th = document.createElement('th');
        th.setAttribute('scope', 'col');
        th.setAttribute('abbr', label);
        const visible = document.createElement('span');
        visible.setAttribute('aria-hidden', 'true');
        visible.textContent = label.slice(0, 3);
        const sr = document.createElement('span');
        sr.className = 'sr-only';
        sr.textContent = label;
        th.appendChild(visible);
        th.appendChild(sr);
        headRow.appendChild(th);
    });
    thead.appendChild(headRow);
    table.appendChild(thead);

    const tbody = document.createElement('tbody');
    tbody.setAttribute('data-bw-calendar-body', '');

    let focusIso = null;
    weeks.forEach((week) => {
        const row = document.createElement('tr');
        row.setAttribute('role', 'row');
        if (showWeekNumbers) {
            const weekCell = document.createElement('td');
            weekCell.className = 'bw-calendar-week-number';
            weekCell.textContent = String(week[0].weekNumber);
            row.appendChild(weekCell);
        }

        week.forEach((day) => {
            const isDisabled = !day.inPeriod || (min && day.date < min) || (max && day.date > max) || disabled.has(day.iso);
            const isSelected = selected.has(day.iso);
            const cell = document.createElement('td');
            cell.setAttribute('role', 'gridcell');
            cell.setAttribute('data-bw-calendar-day', '');
            cell.setAttribute('data-date', day.iso);
            cell.tabIndex = -1;
            cell.setAttribute('aria-label', buildCalendarCellLabel(day.date, registry.dayNames, registry.monthNames));
            if (selectable !== 'none') cell.setAttribute('aria-selected', isSelected ? 'true' : 'false');
            if (day.isToday) cell.setAttribute('aria-current', 'date');
            if (isDisabled) cell.setAttribute('aria-disabled', 'true');
            cell.className = 'bw-calendar-cell'
                + (!day.inPeriod ? ' bw-calendar-cell-outside' : '')
                + (day.isToday ? ' bw-calendar-cell-today' : '')
                + (isSelected ? ' bw-calendar-cell-selected' : '')
                + (isDisabled ? ' bw-calendar-cell-disabled' : '');
            if (!day.inPeriod && !showOtherMonthDays) cell.hidden = true;

            const inner = document.createElement('div');
            inner.className = 'bw-calendar-cell-inner';
            cell.appendChild(inner);

            const dateSpan = document.createElement('span');
            dateSpan.className = 'bw-calendar-cell-date';
            dateSpan.textContent = String(day.day);
            inner.appendChild(dateSpan);

            const events = registry.monthMarkersIndex[day.iso] || [];
            if (events.length) {
                const wrap = document.createElement('div');
                wrap.className = 'bw-calendar-cell-events';
                events.forEach((event, index) => {
                    const isOverflow = index >= maxEventsPerDay;
                    const el = document.createElement(event.href ? 'a' : 'span');
                    if (event.href) el.href = event.href;
                    el.className = `bw-calendar-event bw-calendar-event-${event.type}`;
                    el.textContent = event.label;
                    el.setAttribute('data-bw-calendar-overflow-event', isOverflow ? 'true' : 'false');
                    if (isOverflow) el.hidden = true;
                    wrap.appendChild(el);
                });
                const overflowCount = Math.max(0, events.length - maxEventsPerDay);
                if (overflowCount > 0) {
                    const more = document.createElement('button');
                    more.type = 'button';
                    more.className = 'bw-calendar-event-more';
                    more.setAttribute('data-bw-calendar-more', '');
                    more.setAttribute('aria-expanded', 'false');
                    more.textContent = `+${overflowCount} more`;
                    wrap.appendChild(more);
                }
                inner.appendChild(wrap);
            }

            row.appendChild(cell);
            if (!focusIso && day.iso === previouslyFocused) focusIso = day.iso;
        });
        tbody.appendChild(row);
    });
    table.appendChild(tbody);
    scroll.appendChild(table);

    return focusIso;
};

/** Rebuilds the header title and the whole grid — the month table or the week hour grid, whichever `data-view` calls for — then re-derives the roving tabindex target. Used only when client navigation is enabled. */
const renderCalendarGrid = (calendar, anchor) => {
    const name = calendar.getAttribute('data-name');
    const registry = bwCalendarRegistry[name] || {monthNames: [], dayNames: [], monthMarkersIndex: {}, timedIndex: {}, allDaySpans: []};
    const view = calendar.getAttribute('data-view');
    const weekStarts = calendar.getAttribute('data-week-starts');
    const selectable = calendar.getAttribute('data-selectable');
    const maxEventsPerDay = parseInt(calendar.getAttribute('data-max-events-per-day'), 10) || 0;
    const showOtherMonthDays = calendar.getAttribute('data-show-other-month-days') === 'true';
    const showWeekNumbers = calendar.getAttribute('data-show-week-numbers') === 'true';
    const {min, max, disabled} = calendarConstraints(calendar);
    const selected = new Set(calendarSelectedDates(name));
    const previouslyFocused = calendar.querySelector('[data-bw-calendar-day][tabindex="0"]')?.getAttribute('data-date');

    const scroll = calendar.querySelector('[data-bw-calendar-scroll]');
    if (!scroll) return;
    const weeks = computeCalendarGrid(anchor, view, weekStarts);
    scroll.innerHTML = '';

    const isTimelineView = view === 'week' || view === 'day';
    const ctx = {selectable, maxEventsPerDay, showOtherMonthDays, showWeekNumbers, min, max, disabled, selected};
    let focusIso = isTimelineView
        ? renderCalendarWeekGrid(calendar, scroll, weeks[0], registry, previouslyFocused, ctx)
        : renderCalendarMonthTable(calendar, scroll, weeks, registry, previouslyFocused, ctx);

    if (!focusIso) {
        const flat = weeks.flat();
        const pick = flat.find((d) => selected.has(d.iso)) || flat.find((d) => d.isToday) || flat.find((d) => d.inPeriod) || flat[0];
        focusIso = pick.iso;
    }
    const focusCell = calendar.querySelector(`[data-bw-calendar-day][data-date="${focusIso}"]`);
    if (focusCell) focusCell.tabIndex = 0;

    const title = calendar.querySelector('[data-bw-calendar-title]');
    if (title) title.textContent = calendarPeriodLabel(anchor, view, weekStarts, registry.monthNames, registry.dayNames);

    calendar.setAttribute('data-anchor', calendarISO(anchor));
    calendar.querySelectorAll('[data-bw-calendar-view]').forEach((button) => {
        button.setAttribute('aria-pressed', button.getAttribute('data-bw-calendar-view') === view ? 'true' : 'false');
    });

    if (isTimelineView) scrollCalendarWeekBodyToHour(calendar);
};

const applyCalendarNavigation = (calendar, target, options = {}) => {
    if (calendar.getAttribute('data-client-navigation') !== 'true') return;
    renderCalendarGrid(calendar, target);
    if (options.focus === false) return;
    const focusCell = calendar.querySelector('[data-bw-calendar-day][tabindex="0"]');
    if (focusCell) focusCell.focus({preventScroll: true});
};

const navigateCalendarTo = (calendar, target, options = {}) => {
    const detail = calendarDetail(calendar, {view: calendar.getAttribute('data-view'), anchor: calendarISO(target), source: options.source || 'api'});
    if (!calendarEvent(calendar, 'before-navigate', detail, true)) return false;
    applyCalendarNavigation(calendar, target, options);
    calendarEvent(calendar, 'navigate', detail);
    return true;
};

const navigateCalendar = (name, delta, options = {}) => {
    const calendar = calendarByName(name);
    if (!calendar) return false;
    const target = new Date(calendarAnchorDate(calendar));
    if (delta.years) target.setFullYear(target.getFullYear() + delta.years);
    if (delta.months) target.setMonth(target.getMonth() + delta.months);
    if (delta.weeks) target.setDate(target.getDate() + delta.weeks * 7);
    if (delta.days) target.setDate(target.getDate() + delta.days);
    return navigateCalendarTo(calendar, target, options);
};

/** One step forward/backward in the given view: a day in day view, a week in week view, a month in month view. */
const calendarStepDelta = (view, direction) => (
    view === 'day' ? {days: direction} : view === 'week' ? {weeks: direction} : {months: direction}
);

const nextCalendarPeriod = (name, options = {}) => {
    const calendar = calendarByName(name);
    if (!calendar) return false;
    return navigateCalendar(name, calendarStepDelta(calendar.getAttribute('data-view'), 1), options);
};

const previousCalendarPeriod = (name, options = {}) => {
    const calendar = calendarByName(name);
    if (!calendar) return false;
    return navigateCalendar(name, calendarStepDelta(calendar.getAttribute('data-view'), -1), options);
};

const goToCalendarToday = (name, options = {}) => {
    const calendar = calendarByName(name);
    if (!calendar) return false;
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    return navigateCalendarTo(calendar, today, options);
};

const goToCalendarMonth = (name, year, month, options = {}) => {
    const calendar = calendarByName(name);
    if (!calendar) return false;
    const anchor = calendarAnchorDate(calendar);
    return navigateCalendarTo(calendar, new Date(year, month - 1, Math.min(anchor.getDate(), 28)), options);
};

const setCalendarView = (name, view, options = {}) => {
    const calendar = calendarByName(name);
    if (!calendar || !['month', 'week', 'day'].includes(view)) return false;
    if (calendar.getAttribute('data-view') === view) return true;
    const detail = calendarDetail(calendar, {view, source: options.source || 'api'});
    if (!calendarEvent(calendar, 'before-view-change', detail, true)) return false;
    calendar.setAttribute('data-view', view);
    applyCalendarNavigation(calendar, calendarAnchorDate(calendar), options);
    calendarEvent(calendar, 'view-change', detail);
    return true;
};

const focusCalendarDay = (calendar, cell) => {
    calendar.querySelectorAll('[data-bw-calendar-day]').forEach((el) => { el.tabIndex = -1; });
    cell.tabIndex = 0;
    cell.focus({preventScroll: true});
};

/** Moves the roving tabindex to `targetDate`. If it isn't in the rendered grid (or is a hidden padding day), navigates there first — overriding renderCalendarGrid's own generic focus pick, which has no way to know this is the date the arrow key actually asked for. */
const moveCalendarFocusTo = (calendar, targetDate) => {
    const iso = calendarISO(targetDate);
    const existing = calendarDayCell(calendar, iso);
    if (existing && !existing.hidden) {
        focusCalendarDay(calendar, existing);
        return;
    }
    if (navigateCalendarTo(calendar, targetDate, {source: 'keyboard', focus: false})) {
        const cell = calendarDayCell(calendar, iso);
        if (cell) focusCalendarDay(calendar, cell);
    }
};

const syncCalendarInputs = (calendar, selected) => {
    const name = calendar.getAttribute('data-name');
    const selectable = calendar.getAttribute('data-selectable');
    const container = calendar.querySelector('[data-bw-calendar-inputs]');
    if (!container) return;
    container.innerHTML = '';
    selected.forEach((iso) => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = selectable === 'multiple' ? `${name}[]` : name;
        input.value = iso;
        input.setAttribute('data-bw-calendar-input', iso);
        container.appendChild(input);
    });
};

const selectCalendarDate = (name, iso, options = {}) => {
    const calendar = calendarByName(name);
    if (!calendar) return false;
    const selectable = calendar.getAttribute('data-selectable');
    if (selectable === 'none') return false;
    const cell = calendarDayCell(calendar, iso);
    if (cell && cell.getAttribute('aria-disabled') === 'true') return false;

    const current = new Set(calendarSelectedDates(name));
    const wasSelected = current.has(iso);
    const next = selectable === 'single'
        ? (wasSelected ? new Set() : new Set([iso]))
        : new Set(current);
    if (selectable === 'multiple') { if (wasSelected) next.delete(iso); else next.add(iso); }

    const detail = calendarDetail(calendar, {date: iso, selected: Array.from(next), source: options.source || 'api'});
    if (!calendarEvent(calendar, 'before-select', detail, true)) return false;

    calendar.querySelectorAll('[data-bw-calendar-day]').forEach((el) => {
        const isSelected = next.has(el.getAttribute('data-date'));
        el.classList.toggle('bw-calendar-cell-selected', isSelected);
        el.setAttribute('aria-selected', isSelected ? 'true' : 'false');
    });
    syncCalendarInputs(calendar, next);

    calendarEvent(calendar, 'select', detail);
    return true;
};

const clearCalendarSelection = (name, options = {}) => {
    const calendar = calendarByName(name);
    if (!calendar) return false;
    calendar.querySelectorAll('[data-bw-calendar-day]').forEach((el) => {
        el.classList.remove('bw-calendar-cell-selected');
        if (calendar.getAttribute('data-selectable') !== 'none') el.setAttribute('aria-selected', 'false');
    });
    syncCalendarInputs(calendar, new Set());
    calendarEvent(calendar, 'select', calendarDetail(calendar, {selected: [], source: options.source || 'api'}));
    return true;
};

bwOn('click', '[data-bw-calendar-day]', (cell, event) => {
    if (event.target.closest('a,button')) return;
    const calendar = cell.closest('[data-bw-calendar]');
    if (!calendar || cell.getAttribute('aria-disabled') === 'true') return;
    focusCalendarDay(calendar, cell);
    if (calendar.getAttribute('data-selectable') !== 'none') {
        selectCalendarDate(calendar.getAttribute('data-name'), cell.getAttribute('data-date'), {source: 'pointer'});
    }
});

bwOn('keydown', '[data-bw-calendar-day]', (cell, event) => {
    if (event.target !== cell) return; // let a focused event link/more-button handle its own keys
    const calendar = cell.closest('[data-bw-calendar]');
    if (!calendar) return;
    const name = calendar.getAttribute('data-name');

    if (event.key === 'Enter' || event.key === ' ') {
        event.preventDefault();
        if (cell.getAttribute('aria-disabled') === 'true') return;
        if (calendar.getAttribute('data-selectable') !== 'none') {
            selectCalendarDate(name, cell.getAttribute('data-date'), {source: 'keyboard'});
        }
        return;
    }

    const dayDeltas = {ArrowRight: 1, ArrowLeft: -1, ArrowDown: 7, ArrowUp: -7};
    if (event.key in dayDeltas) {
        event.preventDefault();
        moveCalendarFocusTo(calendar, calendarAddDays(new Date(`${cell.getAttribute('data-date')}T00:00:00`), dayDeltas[event.key]));
        return;
    }

    if (event.key === 'Home' || event.key === 'End') {
        event.preventDefault();
        const cells = Array.from(cell.closest('[role="row"]')?.querySelectorAll('[data-bw-calendar-day]') || []);
        const target = event.key === 'Home' ? cells[0] : cells[cells.length - 1];
        if (target) focusCalendarDay(calendar, target);
        return;
    }

    if (event.key === 'PageUp' || event.key === 'PageDown') {
        event.preventDefault();
        const direction = event.key === 'PageUp' ? -1 : 1;
        const view = calendar.getAttribute('data-view');
        // Shift steps one level up from the plain step: a week in day view, a
        // month in week view, a year in month view.
        const delta = event.shiftKey
            ? (view === 'day' ? {weeks: direction} : view === 'week' ? {months: direction} : {years: direction})
            : calendarStepDelta(view, direction);
        navigateCalendar(name, delta, {source: 'keyboard'});
    }
});

bwOn('click', '[data-bw-calendar-more]', (button) => {
    const cell = button.closest('[data-bw-calendar-day]');
    if (!cell) return;
    const expanded = button.getAttribute('aria-expanded') === 'true';
    if (!button.dataset.moreLabel) button.dataset.moreLabel = button.textContent;
    cell.querySelectorAll('[data-bw-calendar-overflow-event]').forEach((el) => { el.hidden = expanded; });
    button.setAttribute('aria-expanded', expanded ? 'false' : 'true');
    button.textContent = expanded ? button.dataset.moreLabel : 'Show less';
});

bwOn('click', '[data-bw-calendar-prev]', (button) => {
    const calendar = button.closest('[data-bw-calendar]');
    if (calendar) previousCalendarPeriod(calendar.getAttribute('data-name'), {source: 'pointer'});
});

bwOn('click', '[data-bw-calendar-next]', (button) => {
    const calendar = button.closest('[data-bw-calendar]');
    if (calendar) nextCalendarPeriod(calendar.getAttribute('data-name'), {source: 'pointer'});
});

bwOn('click', '[data-bw-calendar-today]', (button) => {
    const calendar = button.closest('[data-bw-calendar]');
    if (calendar) goToCalendarToday(calendar.getAttribute('data-name'), {source: 'pointer'});
});

bwOn('click', '[data-bw-calendar-view]', (button) => {
    const calendar = button.closest('[data-bw-calendar]');
    if (calendar) setCalendarView(calendar.getAttribute('data-name'), button.getAttribute('data-bw-calendar-view'), {source: 'pointer'});
});

/** A server-rendered week view starts scrolled to the top of the day; give it the same sensible window client-side navigation already gets. */
const initialiseCalendars = () => document.querySelectorAll('[data-bw-calendar][data-view="week"], [data-bw-calendar][data-view="day"]').forEach((calendar) => scrollCalendarWeekBodyToHour(calendar));
if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initialiseCalendars);
else initialiseCalendars();

// a tab heading either switches tab or navigates, depending on its url prop
bwOn('click', '[data-bw-tab]', (tab) => {
    goToTab(
        tab.getAttribute('data-bw-tab'),
        tab.getAttribute('data-bw-tab-colour'),
        tab.parentElement?.getAttribute('data-name'),
        tab
    );
});

bwOn('click', '[data-bw-tab-url]', (tab) => {
    location.href = tab.getAttribute('data-bw-tab-url');
});

bwOn('click', '[data-bw-stepper-step]', (trigger) => {
    const stepper = trigger.closest('[data-bw-stepper]');
    if (!stepper || trigger.getAttribute('aria-disabled') === 'true') return;
    const clickable = trigger.getAttribute('data-clickable') ?? stepper.getAttribute('data-clickable');
    if (clickable !== 'true') return;
    setStepperCurrent(stepper, trigger.getAttribute('data-bw-stepper-step'));
});

bwOn('keydown', '[data-bw-stepper-step]', (trigger, event) => {
    const stepper = trigger.closest('[data-bw-stepper]');
    if (!stepper) return;
    const {triggers} = stepperParts(stepper);
    const enabled = triggers.filter((candidate) => candidate.getAttribute('aria-disabled') !== 'true');
    const index = enabled.indexOf(trigger);
    if (index < 0) return;
    const orientation = stepper.getAttribute('data-orientation');
    const rtl = getComputedStyle(stepper).direction === 'rtl';
    let target = null;
    if (event.key === 'Home') target = enabled[0];
    else if (event.key === 'End') target = enabled[enabled.length - 1];
    else if (orientation === 'vertical' && event.key === 'ArrowDown') target = enabled[(index + 1) % enabled.length];
    else if (orientation === 'vertical' && event.key === 'ArrowUp') target = enabled[(index - 1 + enabled.length) % enabled.length];
    else if (orientation === 'horizontal' && event.key === 'ArrowRight') target = enabled[(index + (rtl ? -1 : 1) + enabled.length) % enabled.length];
    else if (orientation === 'horizontal' && event.key === 'ArrowLeft') target = enabled[(index + (rtl ? 1 : -1) + enabled.length) % enabled.length];
    if (!target) return;
    event.preventDefault();
    target.focus();
});

if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initialiseSteppers);
else initialiseSteppers();

// clicking an input or textarea label focuses its field
bwOn('click', '[data-bw-focuses]', (label) => {
    domEl(`.${label.getAttribute('data-bw-focuses')}`)?.focus();
});

/*
 |----------------------------------------------------------------------------
 | Global exports
 |----------------------------------------------------------------------------
 |
 | Everything above is declared with `const` at the top level of a classic
 | script, which creates a script-scoped binding rather than a property of
 | window. Inline handlers in component markup resolve against window, and so
 | does anything a consumer writes in their own <script> block — which is why
 | every project using modals ended up copying the same shim into its layout:
 |
 |     window.showModal = showModal;
 |     window.hideModal = hideModal;
 |     …
 |
 | Assigning them here makes that shim unnecessary. See issue #595.
 */
Object.assign(window, {
    bwOn,
    bwActivateOnKey,
    domEl,
    dom_el,
    domEls,
    dom_els,
    isEmpty,
    isNumeric,
    hide,
    unhide,
    clearErrors,
    changeCss,
    validateForm,
    isNumberKey,
    callUserFunction,
    serialize,
    stringContains,
    changeCssForDomArray,
    animateCss,
    animateCSS,
    showModal,
    trapFocusInModal,
    hideModal,
    showDrawer,
    hideDrawer,
    toggleDrawer,
    openSidebar,
    closeSidebar,
    toggleSidebar,
    collapseSidebar,
    expandSidebar,
    toggleSidebarGroup,
    expandSidebarGroup,
    collapseSidebarGroup,
    resetSidebar,
    openCommandPalette,
    closeCommandPalette,
    toggleCommandPalette,
    resetCommandPalette,
    setCommandPaletteLoading,
    sortDataGrid,
    setDataGridPage,
    selectAllDataGridRows,
    clearDataGridSelection,
    dataGridSelectedKeys,
    setDataGridLoading,
    resetDataGrid,
    initBladewindCalendar,
    navigateCalendar,
    nextCalendarPeriod,
    previousCalendarPeriod,
    goToCalendarToday,
    goToCalendarMonth,
    setCalendarView,
    selectCalendarDate,
    clearCalendarSelection,
    calendarSelectedDates,
    showButtonSpinner,
    hideButtonSpinner,
    showModalActionButtons,
    hideModalActionButtons,
    show,
    addToStorage,
    getFromStorage,
    removeFromStorage,
    goToTab,
    syncTabAccessibility,
    enableTabKeyboardNavigation,
    positionTabActiveLine,
    initialiseTabActiveLines,
    showStepperStep,
    nextStepperStep,
    previousStepperStep,
    resetStepper,
    initialiseSteppers,
    getPrefixSuffixOffsetWidth,
    positionPrefix,
    positionSuffix,
    togglePassword,
    partition,
    filterTable,
    filterTableDebounced,
    stripComma,
    selectTag,
    highlightSelectedTags,
    compareDates,
    checkMinMax,
    makeClearable,
    convertToBase64,
    allowedFileSize,
    setDatepickerValue,
});
