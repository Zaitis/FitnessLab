import { screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { delay, http, HttpResponse } from 'msw';
import { describe, expect, it, vi } from 'vitest';
import { server } from '@/mocks/server';
import { renderWithProviders } from '@/test/renderWithProviders';
import { WorkoutPlanForm } from './WorkoutPlanForm';

function renderForm(onGenerated = vi.fn()) {
  renderWithProviders(<WorkoutPlanForm onGenerated={onGenerated} />);

  return { onGenerated };
}

describe('WorkoutPlanForm', () => {
  it('calls onGenerated with the new plan id on success', async () => {
    const user = userEvent.setup();
    const { onGenerated } = renderForm();

    await user.click(screen.getByRole('button', { name: /generate plan/i }));

    await waitFor(() => expect(onGenerated).toHaveBeenCalledWith(1));
  });

  it('disables the submit button while the request is pending', async () => {
    server.use(
      http.post('http://localhost:8000/api/workout-plans', async () => {
        await delay(50);

        return HttpResponse.json(
          {
            id: 1,
            goal: 'fat_loss',
            experience_level: 'beginner',
            days_per_week: 3,
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

  it('shows a generic error message when the request fails', async () => {
    server.use(
      http.post('http://localhost:8000/api/workout-plans', () =>
        HttpResponse.json({}, { status: 500 }),
      ),
    );

    const user = userEvent.setup();
    renderForm();

    await user.click(screen.getByRole('button', { name: /generate plan/i }));

    expect(await screen.findByText(/something went wrong/i)).toBeInTheDocument();
  });
});
