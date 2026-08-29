import { expect, test } from '@playwright/test'

test.beforeEach(async ({ page }) => page.goto('/stepper.html'))

test('initialises the canonical current step, panels, semantics and unique IDs without stealing focus', async ({ page }) => {
  const stepper = page.locator('[data-name="setup"]')
  await expect(stepper).toHaveAttribute('data-current', 'profile')
  await expect(stepper.locator('[data-step="profile"]')).toHaveAttribute('data-state', 'current')
  await expect(stepper.locator('[data-step="profile"] button')).toHaveAttribute('aria-current', 'step')
  await expect(stepper.locator('[data-bw-stepper-panel="profile"]')).toBeVisible()
  await expect(stepper.locator('[data-bw-stepper-panel="profile"]')).toHaveCSS('border-top-width', '0px')
  await expect(stepper.locator('[data-bw-stepper-panel="profile"]')).toHaveClass(/bw-stepper-panel-borderless/)
  await expect(stepper.locator('[data-bw-stepper-panel="account"]')).toBeHidden()
  expect(await page.evaluate(() => document.activeElement === document.body)).toBe(true)

  const ids = await page.locator('[data-bw-stepper-step], [data-bw-stepper-panel]').evaluateAll((nodes) => nodes.map((node) => node.id))
  expect(ids.every(Boolean)).toBe(true)
  expect(new Set(ids).size).toBe(ids.length)
  await expect(stepper.locator('[data-bw-stepper-panel="profile"]')).toHaveAttribute('aria-labelledby', await stepper.locator('[data-step="profile"] button').getAttribute('id'))
})

test('linear navigation blocks future direct activation but allows next and completed previous steps', async ({ page }) => {
  const stepper = page.locator('[data-name="setup"]')
  await stepper.locator('[data-step="security"] button').click()
  await expect(stepper).toHaveAttribute('data-current', 'profile')

  expect(await page.evaluate(() => nextStepperStep('setup'))).toBe(true)
  await expect(stepper).toHaveAttribute('data-current', 'security')
  await expect(stepper.locator('[data-step="security"] button')).toBeFocused()
  await expect(stepper.locator('[data-bw-stepper-panel="security"]')).toBeVisible()

  await stepper.locator('[data-step="account"] button').click()
  await expect(stepper).toHaveAttribute('data-current', 'account')
  expect(await page.evaluate(() => previousStepperStep('setup'))).toBe(false)
})

test('programmatic selection, reset, cancelable validation and event details work', async ({ page }) => {
  const stepper = page.locator('[data-name="setup"]')
  expect(await page.evaluate(() => showStepperStep('missing', 'none'))).toBe(false)
  expect(await page.evaluate(() => showStepperStep('setup', 'missing'))).toBe(false)

  await page.evaluate(() => { window.blockSecurity = true })
  expect(await page.evaluate(() => nextStepperStep('setup'))).toBe(false)
  await expect(stepper).toHaveAttribute('data-current', 'profile')

  await page.evaluate(() => { window.blockSecurity = false })
  expect(await page.evaluate(() => nextStepperStep('setup'))).toBe(true)
  expect(await page.evaluate(() => resetStepper('setup'))).toBe(true)
  await expect(stepper).toHaveAttribute('data-current', 'profile')

  const events = await page.evaluate(() => window.stepperEvents)
  expect(events.some((event) => event.type === 'bladewind:stepper:before-change' && event.previousStep === 'profile' && event.nextStep === 'security' && event.direction === 'forward')).toBe(true)
  expect(events.some((event) => event.type === 'bladewind:stepper:changed')).toBe(true)
})

test('non-linear activation and multiple steppers remain independent', async ({ page }) => {
  const free = page.locator('[data-name="free"]')
  const setup = page.locator('[data-name="setup"]')
  await free.locator('[data-step="three"] button').click()
  await expect(free).toHaveAttribute('data-current', 'three')
  await expect(free.locator('[data-bw-stepper-panel="three"]')).toBeVisible()
  await expect(setup).toHaveAttribute('data-current', 'profile')
})

test('keyboard navigation skips disabled steps and supports Home, End and RTL arrows', async ({ page }) => {
  const setup = page.locator('[data-name="setup"]')
  const profile = setup.locator('[data-step="profile"] button')
  await profile.focus()
  await page.keyboard.press('End')
  await expect(setup.locator('[data-step="security"] button')).toBeFocused()
  await page.keyboard.press('Home')
  await expect(setup.locator('[data-step="account"] button')).toBeFocused()
  await page.keyboard.press('ArrowRight')
  await expect(profile).toBeFocused()

  const rtl = page.locator('[data-name="rtl"]')
  await rtl.locator('[data-step="a"] button').focus()
  await page.keyboard.press('ArrowLeft')
  await expect(rtl.locator('[data-step="b"] button')).toBeFocused()
  await page.keyboard.press('Enter')
  await expect(rtl).toHaveAttribute('data-current', 'b')
})

test('horizontal, vertical, mobile, dark, RTL and reduced motion geometry does not overflow the page', async ({ page }) => {
  await page.setViewportSize({width: 390, height: 844})
  await page.emulateMedia({reducedMotion: 'reduce'})
  await expect(page.locator('[data-name="setup"] .bw-stepper-list')).toHaveCSS('flex-direction', 'row')
  await expect(page.locator('[data-name="free"] .bw-stepper-list')).toHaveCSS('flex-direction', 'column')
  await expect(page.locator('[data-name="free"] .bw-stepper-panel:not([hidden])')).toHaveCSS('background-color', 'rgb(28, 31, 36)')
  await expect(page.locator('[data-name="rtl"]')).toHaveAttribute('dir', 'rtl')
  await expect(page.locator('[data-name="setup"] .bw-stepper-indicator').first()).toHaveCSS('transition-duration', '1e-05s')
  const overflow = await page.evaluate(() => document.documentElement.scrollWidth - document.documentElement.clientWidth)
  expect(overflow).toBeLessThanOrEqual(1)
})

test('connectors are centred on indicators and stay clear of horizontal labels', async ({ page }) => {
  const horizontal = await page.locator('[data-name="setup"] .bw-stepper-item:not(:last-child)').evaluateAll((items) => items.map((item) => {
    const indicator = item.querySelector('.bw-stepper-indicator').getBoundingClientRect()
    const connector = item.querySelector('.bw-stepper-connector').getBoundingClientRect()
    const copy = item.querySelector('.bw-stepper-copy').getBoundingClientRect()

    return {
      centreDifference: Math.abs((indicator.top + indicator.height / 2) - (connector.top + connector.height / 2)),
      clearsText: connector.bottom <= copy.top,
    }
  }))

  expect(horizontal.every(({ centreDifference, clearsText }) => centreDifference <= 1 && clearsText)).toBe(true)

  const vertical = await page.locator('[data-name="free"] .bw-stepper-item:not(:last-child)').evaluateAll((items) => items.map((item) => {
    const indicator = item.querySelector('.bw-stepper-indicator').getBoundingClientRect()
    const connector = item.querySelector('.bw-stepper-connector').getBoundingClientRect()
    const copy = item.querySelector('.bw-stepper-copy').getBoundingClientRect()

    return {
      centreDifference: Math.abs((indicator.left + indicator.width / 2) - (connector.left + connector.width / 2)),
      clearsText: connector.right <= copy.left,
    }
  }))

  expect(vertical.every(({ centreDifference, clearsText }) => centreDifference <= 1 && clearsText)).toBe(true)
})

test('chevrons, bars and line styles preserve shared navigation behavior', async ({ page }) => {
  const chevronGeometry = await page.locator('[data-name="style-chevrons"] [data-state="current"]').evaluate((item) => {
    const previous = item.previousElementSibling
    const next = item.nextElementSibling
    const itemBox = item.getBoundingClientRect()
    const indicatorBox = item.querySelector('.bw-stepper-indicator').getBoundingClientRect()
    const nextIndicatorBox = next.querySelector('.bw-stepper-indicator').getBoundingClientRect()

    return {
      topBorderWidth: getComputedStyle(item).borderTopWidth,
      bottomBorderWidth: getComputedStyle(item).borderBottomWidth,
      leftBorderWidth: getComputedStyle(item).borderLeftWidth,
      rightBorderWidth: getComputedStyle(item).borderRightWidth,
      leftBorderColor: getComputedStyle(item).borderLeftColor,
      rightBorderColor: getComputedStyle(item).borderRightColor,
      incomingSeparator: getComputedStyle(previous, '::after').borderTopColor,
      outgoingSeparator: getComputedStyle(item, '::after').borderTopColor,
      incomingIndicatorGap: indicatorBox.left - itemBox.left,
      outgoingIndicatorGap: nextIndicatorBox.left - itemBox.right,
    }
  })
  expect(chevronGeometry.topBorderWidth).toBe('0px')
  expect(chevronGeometry.bottomBorderWidth).toBe('0px')
  expect(chevronGeometry.leftBorderWidth).toBe('1px')
  expect(chevronGeometry.rightBorderWidth).toBe('1px')
  expect(chevronGeometry.leftBorderColor).toBe(chevronGeometry.incomingSeparator)
  expect(chevronGeometry.rightBorderColor).toBe(chevronGeometry.outgoingSeparator)
  expect(chevronGeometry.incomingSeparator).toBe(chevronGeometry.outgoingSeparator)
  expect(chevronGeometry.incomingIndicatorGap).toBeGreaterThanOrEqual(28)
  expect(chevronGeometry.outgoingIndicatorGap).toBeGreaterThanOrEqual(28)

  for (const style of ['chevrons', 'bars', 'line']) {
    const stepper = page.locator(`[data-name="style-${style}"]`)
    await expect(stepper).toHaveAttribute('data-style', style)
    await expect(stepper).toHaveClass(new RegExp(`bw-stepper-style-${style}`))
    await stepper.locator('[data-step="three"] button').click()
    await expect(stepper).toHaveAttribute('data-current', 'three')
    await expect(stepper.locator('[data-bw-stepper-panel="three"]')).toBeVisible()
  }

  await expect(page.locator('[data-name="style-chevrons"] .bw-stepper-connector').first()).toHaveCSS('display', 'none')
  await expect(page.locator('[data-name="style-bars"] .bw-stepper-indicator').first()).toHaveCSS('display', 'none')
  await expect(page.locator('[data-name="style-bars"] .bw-stepper-item:last-child .bw-stepper-connector')).toHaveCSS('display', 'block')
  await expect(page.locator('[data-name="style-line"] .bw-stepper-indicator').first()).toHaveCSS('width', '16px')
  await expect(page.locator('[data-name="style-line"] [data-state="current"] .bw-stepper-indicator')).toHaveCSS('background-color', 'rgba(0, 0, 0, 0)')
  const lineStateStyles = await page.locator('[data-name="style-line"]').evaluate((stepper) => {
    const complete = getComputedStyle(stepper.querySelector('[data-state="complete"] .bw-stepper-indicator'))
    const current = getComputedStyle(stepper.querySelector('[data-state="current"] .bw-stepper-indicator'))
    return {
      completeBackground: complete.backgroundColor,
      currentBackground: current.backgroundColor,
      currentBorderWidth: current.borderTopWidth,
      currentBorderStyle: current.borderTopStyle,
    }
  })
  expect(lineStateStyles.currentBorderWidth).toBe('2px')
  expect(lineStateStyles.currentBorderStyle).toBe('solid')
  expect(lineStateStyles.currentBackground).toBe('rgba(0, 0, 0, 0)')
  expect(lineStateStyles.completeBackground).not.toBe(lineStateStyles.currentBackground)
  await expect(page.locator('[data-name="style-line"] .bw-stepper-complete-indicator')).toHaveCount(0)
  await expect(page.locator('[data-name="style-line"] .bw-stepper-error-indicator')).toHaveCount(0)

})
