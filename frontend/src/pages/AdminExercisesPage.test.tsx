import { screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { http, HttpResponse } from 'msw';
import { describe, expect, it, vi } from 'vitest';
import { exerciseFixture } from '@/mocks/handlers';
import { server } from '@/mocks/server';
import { renderWithProviders } from '@/test/renderWithProviders';
import { AdminExercisesPage } from './AdminExercisesPage';

const API_BASE_URL = 'http://localhost:8000/api';

describe('AdminExercisesPage', () => {
  it('shows the empty state with no exercises', async () => {
    renderWithProviders(<AdminExercisesPage />);

    expect(await screen.findByText('No exercises yet.')).toBeInTheDocument();
  });

  it('lists exercises with their current-locale name and details', async () => {
    server.use(
      http.get(`${API_BASE_URL}/admin/exercises`, () => HttpResponse.json([exerciseFixture])),
    );

    renderWithProviders(<AdminExercisesPage />);

    expect(await screen.findByText('Push-up')).toBeInTheDocument();
    expect(screen.getByText('3 × 10')).toBeInTheDocument();
  });

  it('opens the create form, submits, and refreshes the list', async () => {
    let created = false;
    server.use(
      http.get(`${API_BASE_URL}/admin/exercises`, () =>
        HttpResponse.json(created ? [exerciseFixture] : []),
      ),
      http.post(`${API_BASE_URL}/admin/exercises`, async ({ request }) => {
        created = true;
        const body = (await request.json()) as Record<string, unknown>;

        return HttpResponse.json({ id: 1, ...body }, { status: 201 });
      }),
    );

    const user = userEvent.setup();
    renderWithProviders(<AdminExercisesPage />);

    await user.click(await screen.findByRole('button', { name: 'Add exercise' }));
    await user.type(screen.getByLabelText(/Name \(EN\)/), 'Push-up');
    await user.type(screen.getByLabelText(/Instructions \(EN\)/), 'Lower and push back up.');
    await user.click(screen.getByRole('button', { name: 'PL' }));
    await user.type(screen.getByLabelText(/Name \(PL\)/), 'Pompka');
    await user.type(
      screen.getByLabelText(/Instructions \(PL\)/),
      'Opuść i wypchnij się z powrotem.',
    );
    await user.click(screen.getByRole('button', { name: 'Save' }));

    await waitFor(() => expect(screen.getByText('Push-up')).toBeInTheDocument());
  });

  it('deletes an exercise after confirmation', async () => {
    vi.spyOn(window, 'confirm').mockReturnValue(true);
    let deleted = false;
    server.use(
      http.get(`${API_BASE_URL}/admin/exercises`, () =>
        HttpResponse.json(deleted ? [] : [exerciseFixture]),
      ),
      http.delete(`${API_BASE_URL}/admin/exercises/:id`, () => {
        deleted = true;

        return new HttpResponse(null, { status: 204 });
      }),
    );

    const user = userEvent.setup();
    renderWithProviders(<AdminExercisesPage />);

    await user.click(await screen.findByRole('button', { name: 'Delete' }));

    await waitFor(() => expect(screen.queryByText('Push-up')).not.toBeInTheDocument());
  });
});
