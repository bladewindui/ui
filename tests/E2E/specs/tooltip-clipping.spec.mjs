import { expect, test } from '@playwright/test'

/**
 * #591, tooltips.
 *
 * These used to be drawn with [data-tooltip]::after on the trigger. A
 * pseudo-element cannot leave its own element, so a scrolling ancestor cut it
 * off — and the table's action icons, which carry data-tooltip directly, live
 * inside exactly such a wrapper.
 *
 * As with the other popups the assertion is geometric: the bubble's rect has to
 * extend past the wrapper's, because one that fits inside proves nothing.
 */

const cases = [
  { name: 'tooltip component', wrapper: '#tooltip-wrapper', trigger: '#tooltip-wrapper [data-tooltip]', text: 'Archive this order' },
  {
    name: 'table action icon',
    wrapper: '#icons-wrapper',
    trigger: '#icons-wrapper [data-tooltip]',
    // long enough to wrap, so the bubble cannot simply fit inside the wrapper
    text: 'Edit this order, change its line items, adjust the delivery window, and leave a note for the warehouse team before it is dispatched, or hand it back to the account manager if the customer has asked for changes that cannot be made here',
  },
]

test.beforeEach(async ({ page }) => {
  await page.goto('/tooltip-clipping.html')
})

for (const { name, wrapper, trigger, text } of cases) {
  test(`${name} bubble escapes its scrolling ancestor`, async ({ page }) => {
    await page.locator(trigger).first().hover()

    const bubble = page.locator('.bw-tooltip-bubble')
    await expect(bubble).toHaveAttribute('data-open', '1')
    await expect(bubble).toHaveText(text)

    const [bubbleBox, wrapperBox] = await Promise.all([
      bubble.boundingBox(),
      page.locator(wrapper).boundingBox(),
    ])

    // appended to body and positioned fixed, so no ancestor can contain it
    await expect(bubble).toHaveCSS('position', 'fixed')
    expect(await bubble.evaluate((el) => el.parentElement.tagName)).toBe('BODY')

    const escapes =
      bubbleBox.y < wrapperBox.y || bubbleBox.y + bubbleBox.height > wrapperBox.y + wrapperBox.height

    expect(escapes, `${name} bubble is contained by its scrolling ancestor, i.e. clipped`).toBe(true)

  })

  test(`${name} bubble disappears when the pointer leaves`, async ({ page }) => {
    await page.locator(trigger).first().hover()
    await expect(page.locator('.bw-tooltip-bubble')).toHaveAttribute('data-open', '1')

    await page.mouse.move(0, 0)
    await expect(page.locator('.bw-tooltip-bubble')).toHaveAttribute('data-open', '0')
  })
}

test('the old pseudo-element bubble is switched off when the script runs', async ({ page }) => {
  await expect(page.locator('html')).toHaveClass(/bw-tooltip-js/)

  const pseudo = await page.locator('#tooltip-wrapper [data-tooltip]').first()
    .evaluate((el) => getComputedStyle(el, '::after').content)

  // two bubbles for one tooltip would be worse than the clipping
  expect(pseudo === 'none' || pseudo === 'normal').toBe(true)
})
