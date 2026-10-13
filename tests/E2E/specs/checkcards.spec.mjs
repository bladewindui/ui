import { expect, test } from '@playwright/test'

test.beforeEach(async ({ page }) => page.goto('/checkcards.html'))

const card = (page, name, value) => page.locator(`[data-bw-checkcard="${name}"][data-value="${value}"]`)

// playwright waits for aria-disabled elements to become enabled before clicking
const forced = { force: true }

test('a disabled card ignores clicks and the keyboard, and looks disabled', async ({ page }) => {
  const legacy = card(page, 'plan', 'legacy')

  await legacy.click(forced)
  await legacy.focus()
  await page.keyboard.press('Enter')

  await expect(legacy).toHaveAttribute('aria-checked', 'false')
  await expect(page.locator('input.plan')).toHaveValue('')
  await expect(legacy).toHaveCSS('opacity', '0.5')
  await expect(legacy).toHaveCSS('cursor', 'not-allowed')
  expect(await page.evaluate(() => window.picks)).toEqual([])
})

test('a disabled group keeps its preselected value', async ({ page }) => {
  await card(page, 'locked', 'no').click(forced)

  await expect(page.locator('input.locked')).toHaveValue('yes')
  await expect(card(page, 'locked', 'yes')).toHaveAttribute('aria-checked', 'true')
  await expect(card(page, 'locked', 'no')).toHaveAttribute('aria-checked', 'false')
})

test('cards can be disabled and enabled after load, individually or as a group', async ({ page }) => {
  await page.evaluate(() => disableCheckcards('plan', 'pro'))
  await expect(card(page, 'plan', 'pro')).toHaveAttribute('aria-disabled', 'true')
  await expect(card(page, 'plan', 'lite')).not.toHaveAttribute('aria-disabled', 'true')

  await card(page, 'plan', 'pro').click(forced)
  await expect(page.locator('input.plan')).toHaveValue('')

  await page.evaluate(() => enableCheckcards('plan'))
  await expect(page.locator('[data-bw-checkcard="plan"][aria-disabled="true"]')).toHaveCount(0)

  await page.evaluate(() => enableCheckcards('locked', 'no'))
  await card(page, 'locked', 'no').click()
  await expect(page.locator('input.locked')).toHaveValue('no')

  await page.evaluate(() => disableCheckcards('locked'))
  await expect(page.locator('[data-bw-checkcard="locked"][aria-disabled="true"]')).toHaveCount(2)
})

test('onclick runs after the selection changes, with the value, selected state and card', async ({ page }) => {
  await card(page, 'plan', 'pro').click()
  await card(page, 'plan', 'lite').click()
  await card(page, 'plan', 'pro').click()

  expect(await page.evaluate(() => window.picks)).toEqual([
    ['pro', true, 'pro'],
    ['lite', true, 'lite'],
    ['pro', false, 'pro'],
  ])
  await expect(page.locator('input.plan')).toHaveValue('lite')
})
