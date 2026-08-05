import { expect, test } from '@playwright/test';

/**
 * The path most reviewers will actually take, and the one whose failure is
 * most costly (docs/TESTING.md) — a reviewer who lands on the button and
 * gets a broken demo has no reason to try anything else. Reads the seeded
 * account rather than mutating it, so it's safe to run alongside the other
 * journeys without a reset in between.
 */
test('reaches a populated dashboard with one click on the demo button', async ({ page }) => {
  await page.goto('/');

  await page.getByRole('button', { name: 'Try the demo account' }).click();

  await expect(page).toHaveURL(/\/dashboard$/);
  await expect(page.getByRole('heading', { name: 'Welcome, Demo Account' })).toBeVisible();

  // Populated, not just reachable: the seeded measurement history renders as
  // a real trend chart, and the training plan already exists rather than
  // showing the empty generate-a-plan form state.
  await expect(page.getByRole('img', { name: /trend/i })).toBeVisible();

  await page.goto('/dashboard/training');
  await expect(page.getByRole('heading', { name: /previous plans/i })).toBeVisible();
});
