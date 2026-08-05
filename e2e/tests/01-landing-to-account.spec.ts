import { expect, test } from '@playwright/test';
import { uniqueEmail } from './helpers';

/**
 * The full anonymous-to-authenticated carry-over — crosses sessionStorage,
 * two independently-tested layers (BmiForm's pending-measurement write,
 * RegisterPage's post-registration submit), and the cookie session. Neither
 * layer's own tests can see the seam between them; this is the journey that
 * proves the seam actually holds (see docs/SECURITY-REVIEW.md S-1 for what
 * happens when a seam like this goes unverified).
 */
test('anonymous BMI result carries over into a newly registered account', async ({ page }) => {
  const email = uniqueEmail('landing');

  await page.goto('/');

  await page.getByLabel('Weight (kg)').fill('70');
  await page.getByLabel('Height (cm)').fill('175');
  await page.getByLabel('Age', { exact: true }).fill('30');
  await page.getByRole('button', { name: 'Calculate' }).click();

  await expect(page.getByRole('heading', { name: 'Your result' })).toBeVisible();
  await expect(page.getByText('22.9')).toBeVisible();

  await page.getByRole('link', { name: /create a free account/i }).click();
  await expect(page.getByRole('heading', { name: 'Create your account' })).toBeVisible();

  await page.getByLabel('Name').fill('Landing Journey User');
  await page.getByLabel('Email').fill(email);
  await page.getByLabel('Password', { exact: true }).fill('Password!234');
  await page.getByLabel('Confirm password').fill('Password!234');
  await page.getByRole('button', { name: 'Create account' }).click();

  await expect(page).toHaveURL(/\/dashboard$/);
  await expect(page.getByRole('heading', { name: /welcome/i })).toBeVisible();

  const historyRows = page.locator('table tbody tr');
  await expect(historyRows).toHaveCount(1);
  await expect(historyRows.first()).toContainText('22.9');

  // Not just optimistic UI: reload and confirm the carry-over actually
  // persisted server-side, and didn't get submitted a second time.
  await page.reload();
  await expect(historyRows).toHaveCount(1);
  await expect(historyRows.first()).toContainText('22.9');
});
