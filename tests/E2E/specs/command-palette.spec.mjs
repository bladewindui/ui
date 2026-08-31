import { expect, test } from '@playwright/test'

test.beforeEach(async ({ page }) => {
  await page.goto('/command-palette.html')
})

test('opens via helper, focuses the search field, and highlights the first visible item', async ({ page }) => {
  const palette = page.locator('[data-bw-command-palette][data-name="app-commands"]')
  await page.locator('#open-app').click()
  await expect(palette).toHaveAttribute('data-state', 'open')
  await expect(palette).toHaveAttribute('aria-hidden', 'false')
  await expect(palette.locator('[data-bw-command-palette-input]')).toBeFocused()
  await expect(palette.locator('[data-item-name="dashboard"]')).toHaveAttribute('data-highlighted', 'true')
  await expect(page.locator('body')).toHaveClass(/overflow-hidden/)
})

test('the search field has no focus ring or border of its own', async ({ page }) => {
  // Tailwind's own Preflight puts a default focus ring on every text-like
  // input via box-shadow, a property outline:none/border:0 don't touch --
  // the component has to explicitly neutralize it too.
  const palette = page.locator('[data-bw-command-palette][data-name="app-commands"]')
  await page.locator('#open-app').click()
  const input = palette.locator('[data-bw-command-palette-input]')
  await expect(input).toBeFocused()
  await expect(input).toHaveCSS('box-shadow', 'none')
  await expect(input).toHaveCSS('outline-style', 'none')
  await expect(input).toHaveCSS('border-width', '0px')
})

test('opens with the Cmd/Ctrl+K shortcut and toggles closed on a second press', async ({ page }) => {
  const palette = page.locator('[data-bw-command-palette][data-name="app-commands"]')
  await page.keyboard.press('Control+k')
  await expect(palette).toHaveAttribute('data-state', 'open')
  await page.keyboard.press('Control+k')
  await expect(palette).toHaveAttribute('data-state', 'closed')
  await expect(palette).toBeHidden()
})

test('typing filters items and groups by label, description, and keywords, and shows the empty state', async ({ page }) => {
  const palette = page.locator('[data-bw-command-palette][data-name="app-commands"]')
  await page.locator('#open-app').click()
  const input = palette.locator('[data-bw-command-palette-input]')
  await input.fill('order')
  await expect(palette.locator('[data-item-name="orders"]')).toBeVisible()
  await expect(palette.locator('[data-item-name="dashboard"]')).toBeHidden()
  await expect(palette.locator('[data-item-name="new-order"]')).toBeVisible()
  await expect(palette.locator('[data-bw-command-palette-empty]')).toBeHidden()

  await input.fill('add new')
  await expect(palette.locator('[data-item-name="new-order"]')).toBeVisible()
  await expect(palette.locator('[data-group-name="navigate"]')).toBeHidden()

  await input.fill('nothing matches this query')
  await expect(palette.locator('[data-bw-command-palette-empty]')).toBeVisible()

  const events = await page.evaluate(() => window.commandPaletteEvents)
  expect(events.some((event) => event.type === 'bladewind:command-palette:search' && event.query === 'nothing matches this query')).toBe(true)
})

test('arrow keys move the highlight, skip disabled and hidden items, and Home/End jump to the ends', async ({ page }) => {
  const palette = page.locator('[data-bw-command-palette][data-name="app-commands"]')
  await page.locator('#open-app').click()
  await page.keyboard.press('ArrowDown')
  await expect(palette.locator('[data-item-name="orders"]')).toHaveAttribute('data-highlighted', 'true')
  await page.keyboard.press('ArrowDown')
  await expect(palette.locator('[data-item-name="new-order"]')).toHaveAttribute('data-highlighted', 'true')
  await page.keyboard.press('End')
  await expect(palette.locator('[data-item-name="docs"]')).toHaveAttribute('data-highlighted', 'true')
  await page.keyboard.press('Home')
  await expect(palette.locator('[data-item-name="dashboard"]')).toHaveAttribute('data-highlighted', 'true')
  await page.keyboard.press('ArrowUp')
  await expect(palette.locator('[data-item-name="docs"]')).toHaveAttribute('data-highlighted', 'true')
})

test('Enter activates the highlighted item, closes on select, and restores focus to the trigger', async ({ page }) => {
  const palette = page.locator('[data-bw-command-palette][data-name="app-commands"]')
  const trigger = page.locator('#open-app')
  await trigger.click()
  await page.keyboard.press('ArrowDown')
  await page.keyboard.press('Enter')
  await expect(palette).toHaveAttribute('data-state', 'closed')
  await expect(trigger).toBeFocused()

  const events = await page.evaluate(() => window.commandPaletteEvents)
  expect(events.some((event) => event.type === 'bladewind:command-palette:select' && event.itemName === 'orders')).toBe(true)
})

test('cancelable events stop open, select, and close', async ({ page }) => {
  const palette = page.locator('[data-bw-command-palette][data-name="app-commands"]')
  await page.evaluate(() => { window.blockOpen = true })
  await page.locator('#open-app').click()
  await expect(palette).toHaveAttribute('data-state', 'closed')

  await page.evaluate(() => { window.blockOpen = false })
  await page.locator('#open-app').click()
  await expect(palette).toHaveAttribute('data-state', 'open')

  await page.evaluate(() => { window.blockSelect = true })
  await page.keyboard.press('Enter')
  await expect(palette).toHaveAttribute('data-state', 'open')

  await page.evaluate(() => { window.blockSelect = false; window.blockClose = true })
  await page.keyboard.press('Escape')
  await expect(palette).toHaveAttribute('data-state', 'open')
})

test('backdrop click and Escape close the palette when enabled', async ({ page }) => {
  const palette = page.locator('[data-bw-command-palette][data-name="app-commands"]')
  await page.locator('#open-app').click()
  await palette.locator('[data-bw-command-palette-backdrop]').click({ position: { x: 5, y: 5 } })
  await expect(palette).toBeHidden()

  await page.locator('#open-app').click()
  await page.keyboard.press('Escape')
  await expect(palette).toBeHidden()
})

test('the close button closes the palette and Tab stays trapped between the input and the close button', async ({ page }) => {
  const palette = page.locator('[data-bw-command-palette][data-name="app-commands"]')
  await page.locator('#open-app').click()
  await expect(palette.locator('[data-bw-command-palette-input]')).toBeFocused()
  await page.keyboard.press('Shift+Tab')
  await expect(palette.locator('[data-bw-command-palette-close]')).toBeFocused()
  await page.keyboard.press('Tab')
  await expect(palette.locator('[data-bw-command-palette-input]')).toBeFocused()

  await palette.locator('[data-bw-command-palette-close]').click()
  await expect(palette).toBeHidden()
})

test('disabled items cannot be highlighted, clicked, or counted toward matches', async ({ page }) => {
  const palette = page.locator('[data-bw-command-palette][data-name="app-commands"]')
  await page.locator('#open-app').click()
  const locked = palette.locator('[data-item-name="locked"]')
  await expect(locked).toHaveAttribute('aria-disabled', 'true')
  await locked.click({ force: true })
  await expect(palette).toHaveAttribute('data-state', 'open')
  await expect(locked).toHaveAttribute('data-highlighted', 'false')
})

test('a second named palette uses its own shortcut and state independently', async ({ page }) => {
  const app = page.locator('[data-bw-command-palette][data-name="app-commands"]')
  const secondary = page.locator('[data-bw-command-palette][data-name="secondary"]')
  await page.keyboard.press('Control+p')
  await expect(secondary).toHaveAttribute('data-state', 'open')
  await expect(app).toHaveAttribute('data-state', 'closed')
})
