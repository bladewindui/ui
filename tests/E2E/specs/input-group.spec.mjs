import { expect, test } from '@playwright/test'

test('an attached input and button share one level control edge', async ({ page }) => {
  await page.goto('/input-group.html')

  const input = page.locator('#matched-input')
  const button = page.locator('#matched-button')
  const [inputBox, buttonBox] = await Promise.all([
    input.boundingBox(),
    button.boundingBox(),
  ])

  expect(Math.round(buttonBox.y)).toBe(Math.round(inputBox.y))
  expect(Math.round(buttonBox.height)).toBe(Math.round(inputBox.height))
  expect(Math.round(buttonBox.x)).toBe(Math.round(inputBox.x + inputBox.width - 2))

  await expect(input).toHaveCSS('border-top-right-radius', '0px')
  await expect(button).toHaveCSS('border-top-left-radius', '0px')
})

test('grouping does not override either control size', async ({ page }) => {
  await page.goto('/input-group.html')

  const boxes = await Promise.all(
    ['#grouped-input', '#grouped-button', '#reference-input', '#reference-button']
      .map((selector) => page.locator(selector).boundingBox())
  )

  expect(Math.round(boxes[0].height)).toBe(Math.round(boxes[2].height))
  expect(Math.round(boxes[1].height)).toBe(Math.round(boxes[3].height))
  expect(Math.round(boxes[0].height)).not.toBe(Math.round(boxes[1].height))
})
