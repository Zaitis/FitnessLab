import { screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { http, HttpResponse } from 'msw';
import { describe, expect, it, vi } from 'vitest';
import { mealTemplateFixture } from '@/mocks/handlers';
import { server } from '@/mocks/server';
import { renderWithProviders } from '@/test/renderWithProviders';
import { AdminMealTemplatesPage } from './AdminMealTemplatesPage';

const API_BASE_URL = 'http://localhost:8000/api';

describe('AdminMealTemplatesPage', () => {
  it('shows the empty state with no meal templates', async () => {
    renderWithProviders(<AdminMealTemplatesPage />);

    expect(await screen.findByText('No meal templates yet.')).toBeInTheDocument();
  });

  it('lists meal templates with their current-locale name and macros', async () => {
    server.use(
      http.get(`${API_BASE_URL}/admin/meal-templates`, () =>
        HttpResponse.json([mealTemplateFixture]),
      ),
    );

    renderWithProviders(<AdminMealTemplatesPage />);

    expect(await screen.findByText('Oatmeal with Banana')).toBeInTheDocument();
    expect(screen.getByText('20 / 15 / 45')).toBeInTheDocument();
  });

  it('opens the create form, submits, and refreshes the list', async () => {
    let created = false;
    server.use(
      http.get(`${API_BASE_URL}/admin/meal-templates`, () =>
        HttpResponse.json(created ? [mealTemplateFixture] : []),
      ),
      http.post(`${API_BASE_URL}/admin/meal-templates`, async ({ request }) => {
        created = true;
        const body = (await request.json()) as Record<string, unknown>;

        return HttpResponse.json({ id: 1, ...body }, { status: 201 });
      }),
    );

    const user = userEvent.setup();
    renderWithProviders(<AdminMealTemplatesPage />);

    await user.click(await screen.findByRole('button', { name: 'Add meal template' }));
    await user.type(screen.getByLabelText(/Name \(EN\)/), 'Oatmeal with Banana');
    await user.type(screen.getByLabelText(/Description \(EN\)/), 'Rolled oats with banana.');
    await user.click(screen.getByRole('button', { name: 'PL' }));
    await user.type(screen.getByLabelText(/Name \(PL\)/), 'Owsianka z bananem');
    await user.type(screen.getByLabelText(/Description \(PL\)/), 'Płatki owsiane z bananem.');
    await user.click(screen.getByRole('button', { name: 'Save' }));

    await waitFor(() => expect(screen.getByText('Oatmeal with Banana')).toBeInTheDocument());
  });

  it('deletes a meal template after confirmation', async () => {
    vi.spyOn(window, 'confirm').mockReturnValue(true);
    let deleted = false;
    server.use(
      http.get(`${API_BASE_URL}/admin/meal-templates`, () =>
        HttpResponse.json(deleted ? [] : [mealTemplateFixture]),
      ),
      http.delete(`${API_BASE_URL}/admin/meal-templates/:id`, () => {
        deleted = true;

        return new HttpResponse(null, { status: 204 });
      }),
    );

    const user = userEvent.setup();
    renderWithProviders(<AdminMealTemplatesPage />);

    await user.click(await screen.findByRole('button', { name: 'Delete' }));

    await waitFor(() => expect(screen.queryByText('Oatmeal with Banana')).not.toBeInTheDocument());
  });
});
