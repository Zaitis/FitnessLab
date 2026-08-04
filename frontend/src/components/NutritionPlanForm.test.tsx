import { screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { delay, http, HttpResponse } from 'msw';
import { describe, expect, it, vi } from 'vitest';
import { server } from '@/mocks/server';
import { renderWithProviders } from '@/test/renderWithProviders';
import { NutritionPlanForm } from './NutritionPlanForm';

function renderForm(onGenerated = vi.fn()) {
  renderWithProviders(<NutritionPlanForm onGenerated={onGenerated} />);

  return { onGenerated };
}

describe('NutritionPlanForm', () => {
  it('calls onGenerated with the new plan id on success', async () => {
    const user = userEvent.setup();
    const { onGenerated } = renderForm();

    await user.click(screen.getByRole('button', { name: /generate plan/i }));

    await waitFor(() => expect(onGenerated).toHaveBeenCalledWith(1));
  });

  it('disables the submit button while the request is pending', async () => {
    server.use(
      http.post('http://localhost:8000/api/nutrition-plans', async () => {
        await delay(50);

        return HttpResponse.json(
          {
            id: 1,
            goal: 'fat_loss',
            daily_calorie_target: 2000,
            daily_protein_target_g: 150,
            daily_fat_target_g: 65,
            daily_carbs_target_g: 200,
            items: [],
            disclaimer: '',
            created_at: new Date().toISOString(),
          },
          { status: 201 },
        );
      }),
    );

    const user = userEvent.setup();
    renderForm();

    await user.click(screen.getByRole('button', { name: /generate plan/i }));

    expect(screen.getByRole('button')).toBeDisabled();

    await waitFor(() => expect(screen.getByRole('button')).not.toBeDisabled());
  });

  it('shows a measurement-specific message when none is recorded yet', async () => {
    server.use(
      http.post('http://localhost:8000/api/nutrition-plans', () =>
        HttpResponse.json(
          {
            message: 'Record a measurement first.',
            errors: { measurement: ['Record a measurement first.'] },
          },
          { status: 422 },
        ),
      ),
    );

    const user = userEvent.setup();
    renderForm();

    await user.click(screen.getByRole('button', { name: /generate plan/i }));

    expect(await screen.findByRole('link', { name: /dashboard/i })).toBeInTheDocument();
  });

  it('shows a generic error message for other failures', async () => {
    server.use(
      http.post('http://localhost:8000/api/nutrition-plans', () =>
        HttpResponse.json({}, { status: 500 }),
      ),
    );

    const user = userEvent.setup();
    renderForm();

    await user.click(screen.getByRole('button', { name: /generate plan/i }));

    expect(await screen.findByText(/something went wrong/i)).toBeInTheDocument();
  });
});
