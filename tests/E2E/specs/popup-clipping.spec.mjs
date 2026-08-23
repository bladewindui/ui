import { expect, test } from '@playwright/test'

/**
 * #591 — a popup inside a scrolling ancestor must not be clipped by it.
 *
 * The ancestor here is the one that actually causes this in practice: an
 * overflow-x-auto wrapper, as every wide table needs. overflow-x: auto silently
 * computes overflow-y to auto too, so a horizontal scroll container clips
 * vertically — which is what eats the popups.
 *
 * The assertion is deliberately about geometry rather than CSS: the open popup's
 * bounding rect has to extend past the wrapper's, because a popup that fits
 * inside would pass a "not clipped" check without proving anything.
 */

const cases = [
  {
    name: 'select',
    wrapper: '#select-wrapper',
    open: async (page) => page.click('.bw-select-country .clickable'),
    popup: '.bw-select-country .bw-select-items-container',
  },
  {
    name: 'dropmenu',
    wrapper: '#dropmenu-wrapper',
    open: async (page) => page.click('.acts .bw-trigger'),
    popup: '.acts .bw-items-list',
  },
  {
    name: 'popover',
    wrapper: '#popover-wrapper',
    open: async (page) => page.click('.pop .bw-trigger'),
    popup: '.pop .bw-popover-content',
  },
]

test.beforeEach(async ({ page }) => {
  await page.goto('/popup-clipping.html')
})

for (const { name, wrapper, open, popup } of cases) {
  test(`${name} escapes its scrolling ancestor`, async ({ page }) => {
    await open(page)

    const popupEl = page.locator(popup)
    await expect(popupEl).toBeVisible()

    const [popupBox, wrapperBox] = await Promise.all([
      popupEl.boundingBox(),
      page.locator(wrapper).boundingBox(),
    ])

    // fixed positioning is the mechanism; if it regresses to absolute the popup
    // is back inside the clip port
    await expect(popupEl).toHaveCSS('position', 'fixed')

    // the popup is taller than the room left inside the wrapper, so an unclipped
    // one must overhang it. this is the actual bug, stated as geometry.
    expect(
      popupBox.y + popupBox.height,
      `${name} popup is contained by its scrolling ancestor, i.e. clipped`
    ).toBeGreaterThan(wrapperBox.y + wrapperBox.height)
  })

  test(`${name} stays with its trigger when an ancestor scrolls`, async ({ page }) => {
    await open(page)

    const popupEl = page.locator(popup)
    await expect(popupEl).toBeVisible()
    const before = await popupEl.boundingBox()

    // scroll the wrapper itself, not the window — the case a non-capturing
    // scroll listener misses
    await page.locator(wrapper).evaluate((el) => el.scrollBy(120, 0))
    await page.waitForTimeout(120)

    const after = await popupEl.boundingBox()

    expect(
      Math.round(after.x),
      `${name} popup did not follow its trigger when the ancestor scrolled`
    ).not.toBe(Math.round(before.x))
  })
}
