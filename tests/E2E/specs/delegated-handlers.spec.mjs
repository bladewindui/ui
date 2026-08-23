import { expect, test } from '@playwright/test'

/**
 * #608 — every library-owned handler moved from an inline on* attribute to a
 * delegated listener, so a strict CSP no longer disables the component. Whether
 * clicking still *does* anything is not something a PHP test can tell you.
 */

test.beforeEach(async ({ page }) => {
  await page.goto('/delegated-handlers.html')
})

test('no library-owned inline handler survives in the rendered page', async ({ page }) => {
  const inline = await page.evaluate(() => {
    const events = ['onclick', 'onchange', 'oninput', 'onkeyup', 'onkeydown', 'onmouseover', 'onmouseout']
    const found = []

    document.querySelectorAll('*').forEach((el) => {
      events.forEach((event) => {
        const value = el.getAttribute(event)
        if (value) found.push(`${el.tagName.toLowerCase()} ${event}="${value.slice(0, 40)}"`)
      })
    })

    return found
  })

  expect(inline, 'inline handlers remain in the rendered markup').toEqual([])
})

test('an accordion still opens and closes', async ({ page }) => {
  const header = page.locator('#accordion [role="button"]')
  await expect(header).toHaveAttribute('aria-expanded', 'false')

  await header.click()
  await expect(header).toHaveAttribute('aria-expanded', 'true')

  await header.click()
  await expect(header).toHaveAttribute('aria-expanded', 'false')
})

test('an accordion header responds to the keyboard', async ({ page }) => {
  const header = page.locator('#accordion [role="button"]')

  await header.focus()
  await page.keyboard.press('Enter')

  await expect(header).toHaveAttribute('aria-expanded', 'true')
})

test('a tab still switches', async ({ page }) => {
  await expect(page.locator('#tabs .bw-tc-two')).toHaveClass(/hidden/)

  await page.locator('#tabs .atab-two').click()

  await expect(page.locator('#tabs .bw-tc-two')).not.toHaveClass(/hidden/)
  await expect(page.locator('#tabs .atab-two')).toHaveAttribute('aria-selected', 'true')
})

test('a sortable column still sorts', async ({ page }) => {
  const cells = () => page.locator('#table tbody td[data-column="ref"]').allTextContents()

  expect((await cells()).map((t) => t.trim())).toEqual(['c', 'a', 'b'])

  await page.locator('#table th[data-can-sort="true"]').click()

  expect((await cells()).map((t) => t.trim())).toEqual(['a', 'b', 'c'])
})

test('a rating still previews on hover and sets on click', async ({ page }) => {
  const fourth = page.locator('#rating [data-rating="4"]')

  await fourth.hover()
  // preview swaps the empty star for the filled one; `rated` is only set on click
  await expect(page.locator('#rating .bw-rating-3.r .filled')).toBeVisible()
  await expect(page.locator('#rating .bw-rating-5.r .filled')).toBeHidden()

  await fourth.click()
  await expect(page.locator('input.rating-value-r')).toHaveValue('4')
})

test('a closable tag still removes itself', async ({ page }) => {
  await expect(page.locator('#tag label')).toHaveCount(1)

  await page.locator('#tag label a').click()

  await expect(page.locator('#tag label')).toHaveCount(0)
})

test('an alert still dismisses', async ({ page }) => {
  const alert = page.locator('#alert .bw-alert')
  await expect(alert).toBeVisible()

  await page.locator('#alert [data-bw-alert-dismiss]').click()

  await expect(alert).toBeHidden()
})
