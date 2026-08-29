import { expect, test } from '@playwright/test'

test.beforeEach(async ({ page }) => {
  await page.goto('/data-grid.html')
})

test('client sort cycles none, ascending and descending, and reorders rows', async ({ page }) => {
  const grid = page.locator('[data-bw-data-grid][data-name="orders"]')
  const sortButton = grid.locator('[data-bw-data-grid-sort="reference"]')
  const firstRowRef = () => grid.locator('tbody tr[data-bw-data-grid-row]:not([hidden]) td[data-column="reference"]').first().innerText()

  await expect(sortButton).toHaveAttribute('data-direction', 'none')
  await sortButton.click()
  await expect(sortButton).toHaveAttribute('data-direction', 'asc')
  await expect(grid.locator('th[data-column="reference"]')).toHaveAttribute('aria-sort', 'ascending')
  expect(await firstRowRef()).toBe('ORD-001')

  await sortButton.click()
  await expect(sortButton).toHaveAttribute('data-direction', 'desc')
  expect(await firstRowRef()).toBe('ORD-007')

  await sortButton.click()
  await expect(sortButton).toHaveAttribute('data-direction', 'none')
  expect(await firstRowRef()).toBe('ORD-001')

  const events = await page.evaluate(() => window.dataGridEvents)
  expect(events.filter((e) => e.type === 'bladewind:data-grid:sort-change').length).toBeGreaterThanOrEqual(3)
})

test('sorting by a numeric column with a formatted display uses the raw sort value', async ({ page }) => {
  const grid = page.locator('[data-bw-data-grid][data-name="orders"]')
  await grid.locator('[data-bw-data-grid-sort="total"]').click()
  const firstTotal = await grid.locator('tbody tr[data-bw-data-grid-row]:not([hidden]) td[data-column="total"]').first().innerText()
  expect(firstTotal).toBe('$15.00')
})

test('search filters rows by rendered cell text and shows the empty state for no matches', async ({ page }) => {
  const grid = page.locator('[data-bw-data-grid][data-name="orders"]')
  const search = grid.locator('[data-bw-data-grid-search]')
  await search.fill('Kofi')
  const visible = grid.locator('tbody tr[data-bw-data-grid-row]:not([hidden])')
  await expect(visible).toHaveCount(await visible.count())
  for (const row of await visible.all()) {
    await expect(row.locator('td[data-column="customer"]')).toHaveText('Kofi Addo')
  }
  await expect(grid.locator('[data-bw-data-grid-pagination]')).toBeHidden()

  await search.fill('nobody matches this')
  await expect(grid.locator('[data-bw-data-grid-empty]')).toBeVisible()

  await search.fill('')
  await expect(grid.locator('[data-bw-data-grid-pagination]')).toBeVisible()
})

test('client pagination shows three rows per page and updates the page label', async ({ page }) => {
  const grid = page.locator('[data-bw-data-grid][data-name="orders"]')
  await expect(grid.locator('tbody tr[data-bw-data-grid-row]:not([hidden])')).toHaveCount(3)
  await expect(grid.locator('[data-bw-data-grid-pagination-label]')).toHaveText('Page 1 of 3')
  await expect(grid.locator('[data-bw-data-grid-page="prev"]')).toBeDisabled()

  await grid.locator('[data-bw-data-grid-page="next"]').click()
  await expect(grid.locator('[data-bw-data-grid-pagination-label]')).toHaveText('Page 2 of 3')
  await expect(grid.locator('tbody tr[data-bw-data-grid-row]:not([hidden])')).toHaveCount(3)

  await grid.locator('[data-bw-data-grid-page="next"]').click()
  await expect(grid.locator('[data-bw-data-grid-pagination-label]')).toHaveText('Page 3 of 3')
  await expect(grid.locator('tbody tr[data-bw-data-grid-row]:not([hidden])')).toHaveCount(1)
  await expect(grid.locator('[data-bw-data-grid-page="next"]')).toBeDisabled()

  const events = await page.evaluate(() => window.dataGridEvents)
  expect(events.some((e) => e.type === 'bladewind:data-grid:page-change' && e.page === 2)).toBe(true)
})

test('sorting a paginated grid resets to page one with the new order', async ({ page }) => {
  const grid = page.locator('[data-bw-data-grid][data-name="orders"]')
  await grid.locator('[data-bw-data-grid-page="next"]').click()
  await expect(grid.locator('[data-bw-data-grid-pagination-label]')).toHaveText('Page 2 of 3')

  await grid.locator('[data-bw-data-grid-sort="total"]').click()
  await expect(grid.locator('[data-bw-data-grid-pagination-label]')).toHaveText('Page 1 of 3')
  const firstTotal = await grid.locator('tbody tr[data-bw-data-grid-row]:not([hidden]) td[data-column="total"]').first().innerText()
  expect(firstTotal).toBe('$15.00')
})

test('multiple selection: row checkboxes update the selection bar and select-all is tri-state', async ({ page }) => {
  const grid = page.locator('[data-bw-data-grid][data-name="orders"]')
  const bar = grid.locator('[data-bw-data-grid-selection-bar]')
  const selectAll = grid.locator('[data-bw-data-grid-select-all]')
  const rows = grid.locator('tbody tr[data-bw-data-grid-row]:not([hidden]) input[data-bw-data-grid-select]')

  await expect(bar).toBeHidden()
  await rows.nth(0).check()
  await expect(bar).toBeVisible()
  await expect(grid.locator('[data-bw-data-grid-selection-count]')).toHaveText('1 selected')
  expect(await selectAll.evaluate((el) => el.indeterminate)).toBe(true)

  await rows.nth(1).check()
  await rows.nth(2).check()
  expect(await selectAll.evaluate((el) => el.indeterminate)).toBe(false)
  await expect(selectAll).toBeChecked()

  await grid.locator('[data-bw-data-grid-clear-selection]').click()
  await expect(bar).toBeHidden()
  for (const row of await rows.all()) await expect(row).not.toBeChecked()

  const events = await page.evaluate(() => window.dataGridEvents)
  expect(events.some((e) => e.type === 'bladewind:data-grid:select-change')).toBe(true)
})

test('select-all toggles every visible enabled row', async ({ page }) => {
  const grid = page.locator('[data-bw-data-grid][data-name="orders"]')
  await grid.locator('[data-bw-data-grid-select-all]').check()
  const rows = grid.locator('tbody tr[data-bw-data-grid-row]:not([hidden]) input[data-bw-data-grid-select]')
  for (const row of await rows.all()) await expect(row).toBeChecked()
  await expect(grid.locator('[data-bw-data-grid-selection-count]')).toHaveText('3 selected')
})

test('single selection mode uses radio inputs that clear each other and update aria-selected', async ({ page }) => {
  const grid = page.locator('[data-bw-data-grid][data-name="assignee"]')
  const ama = grid.locator('tr[data-row-key="ama"] input[data-bw-data-grid-select]')
  const kofi = grid.locator('tr[data-row-key="kofi"] input[data-bw-data-grid-select]')

  await expect(grid.locator('[data-bw-data-grid-select-all]')).toHaveCount(0)
  await ama.check()
  await expect(grid.locator('tr[data-row-key="ama"]')).toHaveAttribute('aria-selected', 'true')
  await kofi.check()
  await expect(grid.locator('tr[data-row-key="ama"]')).toHaveAttribute('aria-selected', 'false')
  await expect(grid.locator('tr[data-row-key="kofi"]')).toHaveAttribute('aria-selected', 'true')
  await expect(ama).not.toBeChecked()
})

test('a cancelable before-select-change event reverts the checkbox', async ({ page }) => {
  const grid = page.locator('[data-bw-data-grid][data-name="orders"]')
  await page.evaluate(() => { window.blockSelect = true })
  const first = grid.locator('tbody tr[data-bw-data-grid-row]:not([hidden]) input[data-bw-data-grid-select]').first()
  await first.click({ force: true })
  await expect(first).not.toBeChecked()
  await expect(grid.locator('[data-bw-data-grid-selection-bar]')).toBeHidden()
})

test('a cancelable before-sort-change event stops the reorder', async ({ page }) => {
  const grid = page.locator('[data-bw-data-grid][data-name="orders"]')
  await page.evaluate(() => { window.blockSort = true })
  const sortButton = grid.locator('[data-bw-data-grid-sort="reference"]')
  await sortButton.click()
  await expect(sortButton).toHaveAttribute('data-direction', 'none')
})

test('server-paginated grid renders real pagination links instead of the client footer', async ({ page }) => {
  const grid = page.locator('[data-bw-data-grid][data-name="server-orders"]')
  await expect(grid.locator('[data-bw-data-grid-pagination]')).toHaveCount(0)
  await expect(grid.locator('.bw-pagination-server')).toBeVisible()
  await expect(grid.locator('table')).toHaveAttribute('aria-rowcount', '7')
})
