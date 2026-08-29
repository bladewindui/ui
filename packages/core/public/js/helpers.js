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
