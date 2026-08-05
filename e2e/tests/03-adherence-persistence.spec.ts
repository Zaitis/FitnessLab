import { expect, test } from '@playwright/test';
import { generateWorkoutPlan, registerNewUser } from './helpers';

/**
 * The adherence component test already covers optimistic update + rollback
 * against a mocked API (frontend/src/pages/AdherencePage.test.tsx) — what it
 * cannot cover is whether the check-off actually reaches the database.
 * Reloading the page is what tells them apart: an optimistic-only bug would
 * still pass every assertion right up until the reload.
 */
test('a checked-off adherence item survives a reload', async ({ page }) => {
  await registerNewUser(page, 'adherence');
  await generateWorkoutPlan(page);

  await page.goto('/dashboard/adherence');

  const checkbox = page.getByRole('checkbox').first();
  await expect(checkbox).toBeVisible();
  await expect(checkbox).not.toBeChecked();

  // toBeChecked() right after the click would pass on the optimistic UI
  // state alone, before the request that actually persists it has
  // resolved — reloading at that point can cancel the in-flight request
  // entirely (a navigation aborts pending fetches from the old page), which
  // defeats the one thing this test exists to catch. Waiting for the real
  // response is what makes the reload below a meaningful assertion instead
  // of a race.
  const persisted = page.waitForResponse(
    (r) => r.url().includes('/api/adherence') && r.request().method() === 'POST',
  );
  await checkbox.click();
  await expect(checkbox).toBeChecked();
  await persisted;

  await page.reload();

  await expect(page.getByRole('checkbox').first()).toBeChecked();
});
