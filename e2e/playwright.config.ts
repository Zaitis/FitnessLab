import { defineConfig, devices } from '@playwright/test';

/**
 * No `webServer` entry: in CI the stack is docker-compose'd up (built from
 * the same Dockerfiles as production) before this config is ever loaded,
 * and locally it's whatever dev servers are already running — see e2e's
 * own README for both. Playwright managing server lifecycle here would
 * hide the difference between "the app started" and "the app started the
 * way production actually starts it," which is the whole point of this
 * suite running against built images rather than `npm run dev`.
 */
const baseURL = process.env.BASE_URL ?? 'http://localhost:5173';

export default defineConfig({
  testDir: './tests',
  fullyParallel: false,
  workers: 1,
  forbidOnly: !!process.env.CI,
  // Zero, deliberately, in CI as well as locally: ".ai/testing.md" — a flaky
  // test is fixed or deleted, never retried into passing. Retrying would
  // also eat into the register rate limit (5/hour per IP,
  // docs/ARCHITECTURE.md) that every E2E request shares, for no benefit.
  retries: 0,
  reporter: process.env.CI ? [['html', { open: 'never' }], ['github']] : 'list',
  use: {
    baseURL,
    trace: 'retain-on-failure',
    screenshot: 'only-on-failure',
  },
  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] },
    },
  ],
});
