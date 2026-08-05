import fs from 'node:fs';
import { expect, test } from '@playwright/test';
import { generateWorkoutPlan, registerNewUser } from './helpers';

/**
 * Generation and export are each unit/feature-tested in isolation on the
 * backend, and the frontend link is component-adjacent-tested — what none
 * of those can see is whether clicking the real link in a real browser
 * against a real generated plan actually produces a real, non-trivial PDF.
 */
test('generates a training plan and downloads it as a PDF', async ({ page }) => {
  await registerNewUser(page, 'export');
  await generateWorkoutPlan(page);

  const downloadPromise = page.waitForEvent('download');
  await page.getByRole('link', { name: 'Download PDF' }).click();
  const download = await downloadPromise;

  expect(download.suggestedFilename()).toMatch(/^workout-plan-\d+\.pdf$/);

  const path = await download.path();
  expect(path).not.toBeNull();

  const contents = fs.readFileSync(path as string);
  // A real dompdf render is comfortably tens of KB (embedded font, watermark,
  // plan content) — this rules out an empty or error-page response silently
  // saved with a .pdf name.
  expect(contents.byteLength).toBeGreaterThan(10_000);
  expect(contents.subarray(0, 5).toString('ascii')).toBe('%PDF-');
});
