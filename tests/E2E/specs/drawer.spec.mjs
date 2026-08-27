import { expect, test } from '@playwright/test'

test.beforeEach(async ({ page }) => page.goto('/drawer.html'))

test('opens, toggles, closes, locks scroll and restores focus', async ({ page }) => {
  const trigger = page.locator('[data-open="right"]')
  const drawer = page.locator('[data-name="right"]')
  await trigger.click()
  await expect(drawer).toBeVisible()
  await expect(page.locator('body')).toHaveClass(/overflow-hidden/)
  await expect(drawer.locator('.bw-drawer-close')).toBeFocused()
  await page.evaluate(() => toggleDrawer('right'))
  await expect(drawer).toBeHidden()
  await expect(trigger).toBeFocused()
  await expect(page.locator('body')).not.toHaveClass(/overflow-hidden/)
})

test('escape and backdrop dismiss only when configured', async ({ page }) => {
  await page.locator('[data-open="left"]').click()
  await page.keyboard.press('Escape')
  await expect(page.locator('[data-name="left"]')).toBeHidden()
  await page.locator('[data-open="bottom"]').click()
  await page.locator('[data-name="bottom"] [data-bw-drawer-backdrop]').click({ position: {x: 5, y: 5} })
  await expect(page.locator('[data-name="bottom"]')).toBeHidden()
  await page.locator('[data-open="locked"]').click()
  await page.keyboard.press('Escape')
  await expect(page.locator('[data-name="locked"]')).toBeVisible()
})

test('modal focus wraps and stacked drawers close from the top', async ({ page }) => {
  await page.locator('[data-open="right"]').click()
  await page.locator('[data-name="right"] [data-last]').focus()
  await page.keyboard.press('Tab')
  await expect(page.locator('[data-name="right"] .bw-drawer-close')).toBeFocused()
  await page.locator('[data-open="top"]').evaluate((el) => el.click())
  await page.keyboard.press('Escape')
  await expect(page.locator('[data-name="top"]')).toBeHidden()
  await expect(page.locator('[data-name="right"]')).toBeVisible()
  await expect(page.locator('body')).toHaveClass(/overflow-hidden/)
})

test('non-modal drawer leaves the page interactive and does not lock scroll', async ({ page }) => {
  await page.locator('[data-open="nonmodal"]').click()
  await expect(page.locator('[data-name="nonmodal"]')).toBeVisible()
  await expect(page.locator('body')).not.toHaveClass(/overflow-hidden/)
  await page.locator('#background').click()
  await expect(page.locator('#background')).toBeFocused()
})

for (const position of ['left', 'right', 'top', 'bottom']) {
  test(`${position} drawer uses the expected viewport edge`, async ({ page }) => {
    await page.locator(`[data-open="${position}"]`).click()
    const panel = page.locator(`[data-name="${position}"] .bw-drawer-panel`)
    await expect(panel).toHaveCSS('transform', 'matrix(1, 0, 0, 1, 0, 0)')
    const box = await panel.boundingBox()
    const viewport = page.viewportSize()
    if (position === 'left') expect(box.x).toBeCloseTo(0, 0)
    if (position === 'right') expect(box.x + box.width).toBeCloseTo(viewport.width, 0)
    if (position === 'top') expect(box.y).toBeCloseTo(0, 0)
    if (position === 'bottom') expect(box.y + box.height).toBeCloseTo(viewport.height, 0)
  })
}

test('mobile, dark, RTL and reduced motion keep correct geometry and styles', async ({ page }) => {
  await page.setViewportSize({width: 390, height: 844})
  await page.emulateMedia({reducedMotion: 'reduce'})
  await page.evaluate(() => { document.documentElement.classList.add('dark'); document.documentElement.dir = 'rtl' })
  await page.locator('[data-open="right"]').click()
  const panel = page.locator('[data-name="right"] .bw-drawer-panel')
  await expect(panel).toHaveCSS('transform', 'matrix(1, 0, 0, 1, 0, 0)')
  const box = await panel.boundingBox()
  expect(box.width).toBeLessThanOrEqual(390)
  expect(box.x + box.width).toBeCloseTo(390, 0)
  await expect(panel).toHaveCSS('background-color', 'rgb(38, 42, 47)')
  await expect(panel).toHaveCSS('transition-duration', '1e-05s')
})
