#!/usr/bin/env node
/**
 * Inject literal fallbacks into the compiled bundle's custom-property references.
 *
 * BladewindUI ships a prebuilt stylesheet that a consuming app loads alongside its
 * own Tailwind build. Where a .bw-* rule reads `var(--color-gray-200)` with no
 * fallback, the component's appearance is decided by whoever defines that token
 * last — and `@theme { --color-*: initial }` is a documented v4 way to trim the
 * palette, which makes the reference resolve to nothing.
 *
 * This rewrites those references to `var(--color-gray-200, #e5e7eb)`, taking the
 * literal from the bundle's own :root block or from an @property initial-value.
 * An app that defines the token still wins; an app that removes it no longer
 * breaks the component. See improvements.md item 1 / issue #589.
 *
 * Only .bw-* rules are touched, and only tokens with a concrete literal value —
 * a token deliberately defined as empty keeps its current behaviour.
 */

import { readFileSync, writeFileSync } from 'node:fs'
import { resolve, dirname } from 'node:path'
import { fileURLToPath } from 'node:url'

const here = dirname(fileURLToPath(import.meta.url))
const files = [
  'bladewind-ui.min.css',
  'bladewind-ui-no-preflight.min.css',
].map((name) => resolve(here, '../packages/core/public/css/', name))

/** Literal values the bundle itself defines, from :root and @property. */
function tokenValues(source) {
  const values = new Map()

  for (const match of source.matchAll(/:root[^{]*\{([^{}]*)\}/g)) {
    for (const decl of match[1].split(';')) {
      const at = decl.indexOf(':')
      if (at < 0) continue
      const name = decl.slice(0, at).trim()
      const value = decl.slice(at + 1).trim()
      if (name.startsWith('--') && value !== '' && !value.includes('var(')) {
        values.set(name, value)
      }
    }
  }

  for (const match of source.matchAll(/@property\s+(--[\w-]+)\s*\{([^{}]*)\}/g)) {
    const initial = /initial-value\s*:\s*([^;}]+)/.exec(match[2])
    if (!initial) continue
    const value = initial[1].trim()
    if (value !== '' && !value.includes('var(')) values.set(match[1], value)
  }

  return values
}

for (const file of files) {
  let css
  try {
    css = readFileSync(file, 'utf8')
  } catch {
    continue
  }

  const values = tokenValues(css)
  let injected = 0
  const skipped = new Set()

  // rewrite declaration blocks belonging to .bw-* selectors, leaving every other
  // rule — and the vendored stylesheets — exactly as they are. references that
  // already carry a fallback are not matched, so this is safe to run twice.
  const hardened = css.replace(/([^{}]*)\{([^{}]*)\}/g, (whole, selector, body) => {
    if (!selector.includes('.bw-')) return whole

    const next = body.replace(/var\(\s*(--[\w-]+)\s*\)/g, (ref, name) => {
      const value = values.get(name)
      if (value === undefined) {
        skipped.add(name)
        return ref
      }
      injected++
      return `var(${name}, ${value})`
    })

    return `${selector}{${next}}`
  })

  writeFileSync(file, hardened)

  const label = file.split('/').pop()
  console.log(`harden-css: ${label} — ${injected} fallbacks injected`)
  if (skipped.size) {
    console.log(`harden-css: ${label} — no literal for: ${[...skipped].join(', ')}`)
  }
}
