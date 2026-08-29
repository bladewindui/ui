import { expect, test } from '@playwright/test'

test.beforeEach(async ({ page }) => {
  await page.goto('/calendar.html')
})

test('arrow keys move focus between day-column headers in week view, and Enter selects one', async ({ page }) => {
  const calendar = page.locator('[data-bw-calendar][data-name="team"]')
  await calendar.locator('[data-bw-calendar-view="week"]').click()

  const mon = calendar.locator('[data-bw-calendar-day][data-date="2026-08-10"]') // pre-selected by the fixture
  const tue = calendar.locator('[data-bw-calendar-day][data-date="2026-08-11"]') // not selected
  await mon.click()
  await expect(mon).toHaveAttribute('tabindex', '0')

  await page.keyboard.press('ArrowRight')
  await expect(tue).toBeFocused()
  await expect(tue).toHaveAttribute('tabindex', '0')
  await expect(tue).toHaveAttribute('aria-selected', 'false')

  await page.keyboard.press('Enter')
  await expect(tue).toHaveAttribute('aria-selected', 'true')
})

test('a day with an overflowing event list stays the same row height as an empty day', async ({ page }) => {
  const calendar = page.locator('[data-bw-calendar][data-name="team"]')
  const busyDay = calendar.locator('[data-bw-calendar-day][data-date="2026-08-05"]') // 4 events, 1 overflowing
  const emptyDay = calendar.locator('[data-bw-calendar-day][data-date="2026-08-06"]')

  const busyHeight = (await busyDay.boundingBox()).height
  const emptyHeight = (await emptyDay.boundingBox()).height
  expect(busyHeight).toBe(emptyHeight)

  // expanding "+1 more" reveals it within the cell's own scroll, not by growing the row
  await busyDay.locator('[data-bw-calendar-more]').click()
  expect((await busyDay.boundingBox()).height).toBe(busyHeight)
})

test('next/previous rebuild the grid client-side and update the title', async ({ page }) => {
  const calendar = page.locator('[data-bw-calendar][data-name="team"]')
  await expect(calendar.locator('[data-bw-calendar-title]')).toHaveText('August 2026')

  await calendar.locator('[data-bw-calendar-next]').click()
  await expect(calendar.locator('[data-bw-calendar-title]')).toHaveText('September 2026')
  await expect(calendar.locator('[data-bw-calendar-day][data-date="2026-09-15"]')).toHaveCount(1)

  await calendar.locator('[data-bw-calendar-prev]').click()
  await calendar.locator('[data-bw-calendar-prev]').click()
  await expect(calendar.locator('[data-bw-calendar-title]')).toHaveText('July 2026')

  const events = await page.evaluate(() => window.calendarEvents.filter((e) => e.type === 'bladewind:calendar:navigate'))
  expect(events.length).toBeGreaterThanOrEqual(3)
})

test('today jumps to the current month', async ({ page }) => {
  const calendar = page.locator('[data-bw-calendar][data-name="team"]')
  await calendar.locator('[data-bw-calendar-next]').click()
  await calendar.locator('[data-bw-calendar-today]').click()
  const today = new Date()
  const expected = today.toLocaleString('en-US', { month: 'long' }) + ' ' + today.getFullYear()
  await expect(calendar.locator('[data-bw-calendar-title]')).toHaveText(expected)
})

test('the view switch toggles between month and week and re-renders the grid', async ({ page }) => {
  const calendar = page.locator('[data-bw-calendar][data-name="team"]')
  await calendar.locator('[data-bw-calendar-view="week"]').click()
  await expect(calendar.locator('[data-bw-calendar-view="week"]')).toHaveAttribute('aria-pressed', 'true')
  await expect(calendar.locator('[data-bw-calendar-day]')).toHaveCount(7)

  await calendar.locator('[data-bw-calendar-view="month"]').click()
  await expect(calendar.locator('[data-bw-calendar-view="month"]')).toHaveAttribute('aria-pressed', 'true')
  await expect(calendar.locator('[data-bw-calendar-day]')).toHaveCount(42)

  const events = await page.evaluate(() => window.calendarEvents.filter((e) => e.type === 'bladewind:calendar:view-change'))
  expect(events.length).toBe(2)
})

test('switching to week view client-side builds the hour grid with positioned timed events', async ({ page }) => {
  const calendar = page.locator('[data-bw-calendar][data-name="team"]')
  await calendar.locator('[data-bw-calendar-view="week"]').click()

  const column = calendar.locator('[data-bw-calendar-week-body] .bw-calendar-week-day-column[data-date="2026-08-11"]')
  const events = column.locator('.bw-calendar-week-timed-event')
  await expect(events).toHaveCount(3) // Standup, Design sync, Kenya project review

  // Standup 09:00-10:00 -> top 27rem = 432px, Design sync overlaps -> second column
  await expect(events.nth(0)).toHaveCSS('top', '432px')
  await expect(events.nth(0)).toHaveCSS('left', '0px')
  await expect(events.nth(1)).not.toHaveCSS('left', '0px')

  await expect(calendar.locator('.bw-calendar-week-allday-banner')).toHaveText('Bisqui expected')
})

test('week view auto-scrolls to a sensible hour on load and after switching views', async ({ page }) => {
  const calendar = page.locator('[data-bw-calendar][data-name="team"]')
  const scroller = calendar.locator('[data-bw-calendar-scroll]')

  await calendar.locator('[data-bw-calendar-view="week"]').click()
  const scrollTop = await scroller.evaluate((el) => el.scrollTop)
  expect(scrollTop).toBeGreaterThan(0) // scrolled past midnight, not sitting at the very top
})

test('paging weeks while in week view rebuilds the hour grid for the new week', async ({ page }) => {
  const calendar = page.locator('[data-bw-calendar][data-name="team"]')
  await calendar.locator('[data-bw-calendar-view="week"]').click()
  await expect(calendar.locator('[data-bw-calendar-title]')).toHaveText('Aug 9 – 15, 2026')
  await expect(calendar.locator('.bw-calendar-week-timed-event')).toHaveCount(3)

  await calendar.locator('[data-bw-calendar-next]').click()
  await expect(calendar.locator('[data-bw-calendar-title]')).toHaveText('Aug 16 – 22, 2026')
  await expect(calendar.locator('.bw-calendar-week-timed-event')).toHaveCount(0)
  await expect(calendar.locator('[data-bw-calendar-day]')).toHaveCount(7)
})

test('clicking a day toggles multiple selection and syncs hidden inputs', async ({ page }) => {
  const calendar = page.locator('[data-bw-calendar][data-name="team"]')
  const day10 = calendar.locator('[data-bw-calendar-day][data-date="2026-08-10"]')
  const day12 = calendar.locator('[data-bw-calendar-day][data-date="2026-08-12"]')

  await expect(day10).toHaveAttribute('aria-selected', 'true')
  await expect(calendar.locator('input[data-bw-calendar-input]')).toHaveCount(1)

  await day12.click()
  await expect(day12).toHaveAttribute('aria-selected', 'true')
  await expect(calendar.locator('input[data-bw-calendar-input]')).toHaveCount(2)
  await expect(calendar.locator('input[value="2026-08-12"]')).toHaveAttribute('name', 'team[]')

  await day10.click()
  await expect(day10).toHaveAttribute('aria-selected', 'false')
  await expect(calendar.locator('input[data-bw-calendar-input]')).toHaveCount(1)
})

test('a cancelable before-select event reverts the selection', async ({ page }) => {
  const calendar = page.locator('[data-bw-calendar][data-name="team"]')
  await page.evaluate(() => { window.blockSelect = true })
  const day15 = calendar.locator('[data-bw-calendar-day][data-date="2026-08-15"]')
  await day15.click()
  await expect(day15).toHaveAttribute('aria-selected', 'false')
})

test('the overflow button reveals the remaining events and relabels itself', async ({ page }) => {
  const calendar = page.locator('[data-bw-calendar][data-name="team"]')
  const cell = calendar.locator('[data-bw-calendar-day][data-date="2026-08-05"]')
  const more = cell.locator('[data-bw-calendar-more]')
  const overflowEvent = cell.locator('[data-bw-calendar-overflow-event="true"]')

  await expect(overflowEvent).toBeHidden()
  await more.click()
  await expect(overflowEvent).toBeVisible()
  await expect(more).toHaveText('Show less')
  await expect(more).toHaveAttribute('aria-expanded', 'true')

  await more.click()
  await expect(overflowEvent).toBeHidden()
  await expect(more).toHaveText('+1 more')
})

test('a multi-day event renders on every date in its span', async ({ page }) => {
  const calendar = page.locator('[data-bw-calendar][data-name="team"]')
  for (const date of ['2026-08-18', '2026-08-19', '2026-08-20']) {
    await expect(calendar.locator(`[data-date="${date}"] .bw-calendar-event-success`)).toHaveCount(1)
  }
})

test('an arrow key onto an already-visible padding day just moves the roving tabindex', async ({ page }) => {
  const calendar = page.locator('[data-bw-calendar][data-name="team"]')
  const start = calendar.locator('[data-bw-calendar-day][data-date="2026-08-31"]')
  await start.click()
  await expect(start).toHaveAttribute('tabindex', '0')

  await page.keyboard.press('ArrowRight')
  const next = calendar.locator('[data-bw-calendar-day][data-date="2026-09-01"]')
  await expect(next).toBeFocused()
  await expect(next).toHaveAttribute('tabindex', '0')
  await expect(next).toHaveClass(/bw-calendar-cell-outside/)
  await expect(calendar.locator('[data-bw-calendar-title]')).toHaveText('August 2026')
})

test('an arrow key past the visible grid rebuilds it around the target month', async ({ page }) => {
  const calendar = page.locator('[data-bw-calendar][data-name="team"]')
  const start = calendar.locator('[data-bw-calendar-day][data-date="2026-08-30"]')
  await start.click()

  await page.keyboard.press('ArrowDown')
  const target = calendar.locator('[data-bw-calendar-day][data-date="2026-09-06"]')
  await expect(target).toBeFocused()
  await expect(target).toHaveAttribute('tabindex', '0')
  await expect(calendar.locator('[data-bw-calendar-title]')).toHaveText('September 2026')
})

test('Home and End move focus to the first and last day of the row', async ({ page }) => {
  const calendar = page.locator('[data-bw-calendar][data-name="team"]')
  const mid = calendar.locator('[data-bw-calendar-day][data-date="2026-08-12"]')
  await mid.focus()

  await page.keyboard.press('End')
  await expect(calendar.locator('[data-bw-calendar-day][data-date="2026-08-15"]')).toBeFocused()

  await page.keyboard.press('Home')
  await expect(calendar.locator('[data-bw-calendar-day][data-date="2026-08-09"]')).toBeFocused()
})

test('PageDown moves a month and Shift+PageDown moves a year', async ({ page }) => {
  const calendar = page.locator('[data-bw-calendar][data-name="team"]')
  const start = calendar.locator('[data-bw-calendar-day][data-date="2026-08-15"]')
  await start.focus()

  await page.keyboard.press('PageDown')
  await expect(calendar.locator('[data-bw-calendar-title]')).toHaveText('September 2026')

  await page.keyboard.press('Shift+PageDown')
  await expect(calendar.locator('[data-bw-calendar-title]')).toHaveText('September 2027')
})

test('Enter selects the focused day', async ({ page }) => {
  const calendar = page.locator('[data-bw-calendar][data-name="team"]')
  const day = calendar.locator('[data-bw-calendar-day][data-date="2026-08-15"]')
  await day.focus()
  await page.keyboard.press('Enter')
  await expect(day).toHaveAttribute('aria-selected', 'true')
})

test('disabled dates cannot be selected by click or keyboard', async ({ page }) => {
  const calendar = page.locator('[data-bw-calendar][data-name="booking"]')
  const disabledDay = calendar.locator('[data-bw-calendar-day][data-date="2026-08-14"]')
  const outOfRangeDay = calendar.locator('[data-bw-calendar-day][data-date="2026-08-25"]')

  await expect(disabledDay).toHaveAttribute('aria-disabled', 'true')
  await disabledDay.click({force: true})
  await expect(disabledDay).toHaveAttribute('aria-selected', 'false')

  await expect(outOfRangeDay).toHaveAttribute('aria-disabled', 'true')
  await outOfRangeDay.click({force: true})
  await expect(outOfRangeDay).toHaveAttribute('aria-selected', 'false')

  const enabledDay = calendar.locator('[data-bw-calendar-day][data-date="2026-08-16"]')
  await enabledDay.click()
  await expect(enabledDay).toHaveAttribute('aria-selected', 'true')
})

test('a server-driven calendar only emits navigate and does not rebuild its own grid', async ({ page }) => {
  const calendar = page.locator('[data-bw-calendar][data-name="remote"]')
  await expect(calendar.locator('[data-bw-calendar-title]')).toHaveText('August 2026')

  await calendar.locator('[data-bw-calendar-next]').click()
  await expect(calendar.locator('[data-bw-calendar-title]')).toHaveText('August 2026')

  const events = await page.evaluate(() => window.remoteCalendarEvents)
  expect(events).toEqual([{ anchor: '2026-09-15' }])
})

test('a fixed height caps the grid and the header stays pinned while the body scrolls', async ({ page }) => {
  const calendar = page.locator('[data-bw-calendar][data-name="sized"]')
  const scroller = calendar.locator('[data-bw-calendar-scroll]')

  const box = await scroller.boundingBox()
  expect(box.height).toBeLessThanOrEqual(97) // 6rem + a hair of rounding

  const { scrollHeight, clientHeight } = await scroller.evaluate((el) => ({ scrollHeight: el.scrollHeight, clientHeight: el.clientHeight }))
  expect(scrollHeight).toBeGreaterThan(clientHeight)

  const header = calendar.locator('thead th').first()
  const headerTopBefore = (await header.boundingBox()).y
  await scroller.evaluate((el) => { el.scrollTop = el.scrollHeight })
  const headerTopAfter = (await header.boundingBox()).y
  expect(headerTopAfter).toBe(headerTopBefore)
})

test('a fixed height keeps the calendar the same size across months and views with different row counts', async ({ page }) => {
  const calendar = page.locator('[data-bw-calendar][data-name="sized"]')
  const heightFor = async () => (await calendar.boundingBox()).height

  const august = await heightFor() // August 2026 is a 6-row month
  await calendar.locator('[data-bw-calendar-next]').click() // September 2026 has fewer weeks
  const september = await heightFor()
  expect(Math.abs(august - september)).toBeLessThan(2)

  await calendar.locator('[data-bw-calendar-view="week"]').click() // one row instead of several
  const week = await heightFor()
  expect(Math.abs(august - week)).toBeLessThan(2)
})

test('a calendar with no height prop still does not jump across months and views, by default', async ({ page }) => {
  const calendar = page.locator('[data-bw-calendar][data-name="team"]')
  const heightFor = async () => (await calendar.boundingBox()).height

  const august = await heightFor() // August 2026 is a 6-row month
  await calendar.locator('[data-bw-calendar-next]').click() // September 2026 has fewer weeks
  const september = await heightFor()
  expect(Math.abs(august - september)).toBeLessThan(2)

  await calendar.locator('[data-bw-calendar-view="week"]').click()
  const week = await heightFor()
  expect(Math.abs(august - week)).toBeLessThan(2)
})
