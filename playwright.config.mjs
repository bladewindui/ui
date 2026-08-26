import { defineConfig, devices } from '@playwright/test'

/**
 * Tier C of #588 — the browser check.
 *
 * Blade render tests assert markup and the compiled-CSS tests assert the
 * stylesheet, but neither can see layout. Popup clipping (#591) is geometry: a
 * popup is broken when its bounding rect is cut by an ancestor's, and nothing
 * short of a real layout will tell you that.
 *
 * Deliberately small: Chromium only, one worker, and the fixtures are generated
 * from the real Blade templates by `phpunit --testsuite=fixtures` so they cannot
 * drift from what the components actually render.
 *
 *   npm run test:e2e
 */
export default defineConfig({
  testDir: './tests/E2E/specs',
  fullyParallel: false,
  workers: 1,
  forbidOnly: !!process.env.CI,
  retries: 0,
  reporter: process.env.CI ? 'list' : 'line',

  use: {
    baseURL: 'http://127.0.0.1:8910',
    trace: 'retain-on-failure',
  },

  projects: [
    { name: 'chromium', use: { ...devices['Desktop Chrome'] } },
  ],

  webServer: {
    command: 'php -S 127.0.0.1:8910 -t tests/E2E/public',
    url: 'http://127.0.0.1:8910/popup-clipping.html',
    reuseExistingServer: !process.env.CI,
    timeout: 30_000,
  },
})
