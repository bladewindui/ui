import { expect, test } from '@playwright/test'

test('desktop shows the complete trail with visible focus and truncation', async ({ page }) => {
  await page.setViewportSize({ width: 1000, height: 700 })
  await page.goto('/breadcrumbs.html')

  const trail = page.locator('#light .bw-breadcrumbs')
  await expect(trail.locator('.bw-breadcrumb-overflow-marker')).toBeHidden()
  await expect(trail.locator('.bw-breadcrumb-item')).toHaveCount(5)
  await expect(trail.locator('[aria-current="page"]')).toHaveText('Shipment details')

  const longLabel = trail.locator('.bw-breadcrumb-label').filter({ hasText: 'Order 1042' })
  await expect(longLabel).toHaveCSS('text-overflow', 'ellipsis')

  await trail.locator('a').first().focus()
  await expect(trail.locator('a').first()).toBeFocused()
  await expect(trail.locator('a').first()).toHaveCSS('outline-style', 'solid')
})

test('mobile collapses long trails but keeps every destination keyboard reachable', async ({ page }) => {
  await page.setViewportSize({ width: 390, height: 700 })
  await page.goto('/breadcrumbs.html')

  const trail = page.locator('#light .bw-breadcrumbs')
  const items = trail.locator('.bw-breadcrumb-item')
  await expect(trail.locator('.bw-breadcrumb-overflow-marker')).toBeVisible()
  await expect(items).toHaveCount(5)
  await expect(items.nth(1)).toHaveCSS('opacity', '0')

  await trail.locator('a').nth(1).focus()
  await expect(trail.locator('a').nth(1)).toBeFocused()
  await expect(items.nth(1)).toHaveCSS('opacity', '1')
  await expect(trail.locator('[aria-current="page"]')).toBeVisible()

  const metrics = await trail.evaluate((node) => ({ width: node.clientWidth, scrollWidth: node.scrollWidth }))
  expect(metrics.scrollWidth).toBeLessThanOrEqual(metrics.width + 1)
})

test('dark and RTL trails use their intended colours and direction', async ({ page }) => {
  await page.goto('/breadcrumbs.html')

  await expect(page.locator('#dark [aria-current="page"]')).toHaveCSS('color', 'rgb(240, 241, 242)')
  await expect(page.locator('#rtl nav')).toHaveAttribute('dir', 'rtl')
  await expect(page.locator('#rtl .bw-breadcrumb-separator').nth(1)).toContainText('/')
})

