import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { render, screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { http, HttpResponse } from 'msw';
import { MemoryRouter, Route, Routes } from 'react-router';
import { describe, expect, it } from 'vitest';
import { server } from '@/mocks/server';
import { renderWithProviders } from '@/test/renderWithProviders';
import { LandingPage } from './LandingPage';

const API_ROOT = 'http://localhost:8000/api';

function renderPage() {
  renderWithProviders(<LandingPage />);
}

/**
 * The demo button's success path navigates to /dashboard — needs a real
 * route match to observe, since MemoryRouter alone can't assert the URL.
 */
function renderPageWithRouting() {
  const queryClient = new QueryClient();

  return render(
    <QueryClientProvider client={queryClient}>
      <MemoryRouter initialEntries={['/']}>
        <Routes>
          <Route path="/" element={<LandingPage />} />
          <Route path="/dashboard" element={<h1>Dashboard</h1>} />
        </Routes>
      </MemoryRouter>
    </QueryClientProvider>,
  );
}

describe('LandingPage', () => {
  it('shows the registration cta only after a bmi result exists', async () => {
    const user = userEvent.setup();
    renderPage();

    expect(screen.queryByRole('link', { name: /create a free account/i })).not.toBeInTheDocument();

    await user.type(screen.getByLabelText(/weight/i), '70');
    await user.type(screen.getByLabelText(/height/i), '175');
    await user.type(screen.getByLabelText(/age/i), '30');
    await user.click(screen.getByRole('button', { name: /calculate/i }));

    expect(await screen.findByRole('link', { name: /create a free account/i })).toBeInTheDocument();
  });

  it('signs in and reaches the dashboard with one click on the demo button', async () => {
    const user = userEvent.setup();
    renderPageWithRouting();

    await user.click(screen.getByRole('button', { name: /try the demo account/i }));

    await waitFor(() =>
      expect(screen.getByRole('heading', { name: 'Dashboard' })).toBeInTheDocument(),
    );
  });

  it('shows an error message when the demo login fails', async () => {
    server.use(
      http.post(`${API_ROOT}/login`, () =>
        HttpResponse.json({ message: 'Server error.' }, { status: 500 }),
      ),
    );

    const user = userEvent.setup();
    renderPage();

    await user.click(screen.getByRole('button', { name: /try the demo account/i }));

    expect(await screen.findByText(/couldn't sign in/i)).toBeInTheDocument();
  });
});
