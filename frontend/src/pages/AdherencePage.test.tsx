import { screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { delay, http, HttpResponse } from 'msw';
import { describe, expect, it } from 'vitest';
import { server } from '@/mocks/server';
import { renderWithProviders } from '@/test/renderWithProviders';
import { AdherencePage } from './AdherencePage';

const API_ROOT = 'http://localhost:8000/api';

interface AdherenceEntryFixture {
  entry_date: string;
  plan_type: string;
  plan_id: number;
  plan_item_id: string;
}

function mockWorkoutPlan() {
  server.use(
    http.get(`${API_ROOT}/workout-plans`, () =>
      HttpResponse.json({
        data: [
          {
            id: 10,
            goal: 'fat_loss',
            experience_level: 'beginner',
            days_per_week: 3,
            items: [
              {
                id: 'exercise-1',
                day: 1,
                type: 'strength',
                name: 'Bench Press',
                instructions: 'Press the bar up from chest height.',
                sets: 3,
                reps: 10,
                duration_minutes: null,
              },
            ],
            disclaimer: '',
            created_at: new Date().toISOString(),
          },
        ],
        current_page: 1,
        last_page: 1,
        total: 1,
      }),
    ),
  );
}

/**
 * Backs GET/POST/DELETE with a shared in-memory list, so the invalidation
 * that follows a successful mutation refetches the real persisted state
 * instead of reverting to the module-level empty-array default.
 */
function mockStatefulAdherenceApi() {
  let entries: AdherenceEntryFixture[] = [];

  server.use(
    http.get(`${API_ROOT}/adherence`, () => HttpResponse.json(entries)),

    http.post(`${API_ROOT}/adherence`, async ({ request }) => {
      const body = (await request.json()) as AdherenceEntryFixture;
      entries = [
        ...entries.filter(
          (entry) =>
            !(entry.entry_date === body.entry_date && entry.plan_item_id === body.plan_item_id),
        ),
        {
          entry_date: body.entry_date,
          plan_type: body.plan_type,
          plan_id: body.plan_id,
          plan_item_id: body.plan_item_id,
        },
      ];

      return HttpResponse.json({ checked: true });
    }),

    http.delete(`${API_ROOT}/adherence`, async ({ request }) => {
      const body = (await request.json()) as AdherenceEntryFixture;
      entries = entries.filter(
        (entry) =>
          !(entry.entry_date === body.entry_date && entry.plan_item_id === body.plan_item_id),
      );

      return HttpResponse.json({ checked: false });
    }),
  );
}

describe('AdherencePage', () => {
  it('checks off an item, reflecting the optimistic update and the persisted state', async () => {
    mockWorkoutPlan();
    mockStatefulAdherenceApi();
    const user = userEvent.setup();
    renderWithProviders(<AdherencePage />);

    const checkbox = await screen.findByRole('checkbox', { name: /bench press/i });
    expect(checkbox).not.toBeChecked();

    await user.click(checkbox);

    await waitFor(() => expect(checkbox).toBeChecked());
  });

  it('rolls back the optimistic update when the request fails', async () => {
    mockWorkoutPlan();
    server.use(
      http.get(`${API_ROOT}/adherence`, () => HttpResponse.json([])),
      http.post(`${API_ROOT}/adherence`, async () => {
        await delay(10);

        return HttpResponse.json({ message: 'Server error.' }, { status: 500 });
      }),
    );

    const user = userEvent.setup();
    renderWithProviders(<AdherencePage />);

    const checkbox = await screen.findByRole('checkbox', { name: /bench press/i });

    await user.click(checkbox);
    expect(checkbox).toBeChecked();

    await waitFor(() => expect(checkbox).not.toBeChecked());
  });
});
