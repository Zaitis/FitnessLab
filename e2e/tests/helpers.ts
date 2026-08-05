import { expect, type Page } from '@playwright/test';

export function uniqueEmail(prefix: string): string {
  return `${prefix}-${Date.now()}-${Math.floor(Math.random() * 100_000)}@example.com`;
}

/**
 * Registers a throwaway account and waits for the post-registration redirect
 * to the dashboard, so each journey that needs an authenticated user gets
 * one that's genuinely isolated from every other test — no shared fixture
 * user, no ordering dependency between spec files.
 */
export async function registerNewUser(page: Page, namePrefix: string): Promise<string> {
  const email = uniqueEmail(namePrefix);

  await page.goto('/register');
  await page.getByLabel('Name').fill(`${namePrefix} User`);
  await page.getByLabel('Email').fill(email);
  await page.getByLabel('Password', { exact: true }).fill('Password!234');
  await page.getByLabel('Confirm password').fill('Password!234');
  await page.getByRole('button', { name: 'Create account' }).click();

  await expect(page).toHaveURL(/\/dashboard$/);

  return email;
}

/**
 * Generates a workout plan with the form's own defaults (fat loss, beginner,
 * 3 days a week, gym) — the journeys that need a plan don't care which one,
 * only that one exists to check off or export.
 */
export async function generateWorkoutPlan(page: Page): Promise<void> {
  await page.goto('/dashboard/training');
  await page.getByRole('button', { name: 'Generate plan' }).click();
  await expect(page.getByRole('link', { name: 'Download PDF' })).toBeVisible();
}
