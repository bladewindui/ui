#!/usr/bin/env node
/**
 * Load helpers.js the way a browser would on a page containing no BladewindUI
 * components at all, and check it survives and exports its API.
 *
 * This exists because it did not. domEls() returned `false` when a selector
 * matched nothing, so every `domEls(...).forEach(...)` — 23 across the library —
 * was a TypeError waiting for the right page. One of them, initialiseTabActiveLines,
 * runs at load time, so on a deferred or end-of-body script tag a page with no
 * simple tab group threw before the rest of the file ran. See #595.
 */

import { readFileSync } from 'node:fs'
import { dirname, resolve } from 'node:path'
import { fileURLToPath } from 'node:url'
import { createContext, runInContext } from 'node:vm'

const here = dirname(fileURLToPath(import.meta.url))
const src = readFileSync(resolve(here, '../packages/core/public/js/helpers.js'), 'utf8')

// the smallest DOM that answers what helpers.js touches at load, and matches nothing
const element = () => ({
  style: { setProperty() {} },
  classList: { add() {}, remove() {}, toggle() {}, contains: () => false },
  querySelector: () => null,
  querySelectorAll: () => [],
  getBoundingClientRect: () => ({ width: 0, height: 0, left: 0, top: 0 }),
  setAttribute() {}, getAttribute: () => null,
  appendChild() {}, removeChild() {}, addEventListener() {},
  closest: () => null, focus() {}, click() {}, dataset: {},
})

const storage = { getItem: () => null, setItem() {}, removeItem() {} }
const window = {
  addEventListener() {},
  localStorage: storage,
  sessionStorage: storage,
  getComputedStyle: () => ({}),
}
const document = {
  readyState: 'complete', // the deferred / end-of-body case, which runs synchronously
  addEventListener() {},
  querySelector: () => null,
  querySelectorAll: () => [],
  createElement: element,
  documentElement: element(),
  body: element(),
}

const Observer = function () { this.observe = () => {}; this.disconnect = () => {} }
window.innerHeight = 800
const context = createContext({
  window, document, console, setTimeout, clearTimeout,
  MutationObserver: Observer, IntersectionObserver: Observer, ResizeObserver: Observer,
  navigator: {}, location: {},
})

try {
  runInContext(src, context)
} catch (error) {
  console.error(`smoke-helpers: helpers.js threw while loading on a bare page — ${error.message}`)
  process.exit(1)
}

// the API consumers were shimming onto window by hand before #595
const required = [
  'showModal', 'hideModal', 'showModalActionButtons', 'hideModalActionButtons',
  'domEl', 'domEls', 'changeCss', 'goToTab', 'togglePassword', 'unhide', 'hide',
  'showDrawer', 'hideDrawer', 'toggleDrawer',
  'showStepperStep', 'nextStepperStep', 'previousStepperStep', 'resetStepper',
  'openSidebar', 'closeSidebar', 'toggleSidebar', 'collapseSidebar', 'expandSidebar',
  'toggleSidebarGroup', 'expandSidebarGroup', 'collapseSidebarGroup', 'resetSidebar',
]

const missing = required.filter((name) => typeof window[name] !== 'function')

if (missing.length) {
  console.error(`smoke-helpers: not exported on window — ${missing.join(', ')}`)
  process.exit(1)
}

// select.js layers onto the same globals, so load it in the same context and
// make sure it survives too. Its popup positioning (#591) lives here.
try {
  runInContext(readFileSync(resolve(here, '../packages/core/public/js/select.js'), 'utf8'), context)
} catch (error) {
  console.error(`smoke-helpers: select.js threw while loading — ${error.message}`)
  process.exit(1)
}

if (typeof window.BladewindSelect !== 'function') {
  console.error('smoke-helpers: BladewindSelect is not defined on window')
  process.exit(1)
}

console.log(`smoke-helpers: helpers.js loads clean and exports ${required.length} checked helpers`)
console.log('smoke-helpers: select.js loads clean and defines BladewindSelect')
