import { screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { http, HttpResponse } from 'msw';
import { MemoryRouter, Route, Routes } from 'react-router';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { render } from '@testing-library/react';
import { describe, expect, it } from 'vitest';
import { server } from '@/mocks/server';
import { ResetPasswordPage } from './ResetPasswordPage';

const API_ROOT = 'http://localhost:8000/api';

/**
 * The page reads its token from the path and the email from the query
 * string, so it has to be rendered through a real route match rather than
 * mounted bare.
 */
function renderAt(path: string) {
  const queryClient = new QueryClient();

  return render(
    <QueryClientProvider client={queryClient}>
      <MemoryRouter initialEntries={[path]}>
        <Routes>
          <Route path="/password-reset/:token" element={<ResetPasswordPage />} />
          <Route path="/login" element={<h1>Log in</h1>} />
          <Route path="/forgot-password" element={<h1>Forgot</h1>} />
        </Routes>
      </MemoryRouter>
    </QueryClientProvider>,
  );
}

const validPath = '/password-reset/tok123?email=ada%40example.com';

describe('ResetPasswordPage', () => {
  it('redirects to login after a successful reset', async () => {
    const user = userEvent.setup();
    renderAt(validPath);

    await user.type(screen.getByLabelText(/new password/i), 'Password!234');
    await user.type(screen.getByLabelText(/confirm password/i), 'Password!234');
    await user.click(screen.getByRole('button', { name: /save new password/i }));

    await waitFor(() =>
      expect(screen.getByRole('heading', { name: 'Log in' })).toBeInTheDocument(),
    );
  });

  it('rejects mismatched passwords without calling the API', async () => {
    const user = userEvent.setup();
    renderAt(validPath);

    await user.type(screen.getByLabelText(/new password/i), 'Password!234');
    await user.type(screen.getByLabelText(/confirm password/i), 'Different!234');
    await user.click(screen.getByRole('button', { name: /save new password/i }));

    expect(await screen.findByText(/passwords do not match/i)).toBeInTheDocument();
  });

  it('surfaces an expired token as a banner, not an invisible field error', async () => {
    server.use(
      http.post(`${API_ROOT}/reset-password`, () =>
        HttpResponse.json(
          { message: 'Invalid.', errors: { email: ['This password reset token is invalid.'] } },
          { status: 422 },
        ),
      ),
    );

    const user = userEvent.setup();
    renderAt(validPath);

    await user.type(screen.getByLabelText(/new password/i), 'Password!234');
    await user.type(screen.getByLabelText(/confirm password/i), 'Password!234');
    await user.click(screen.getByRole('button', { name: /save new password/i }));

    // The form has no email field, so the message would render nowhere if it
    // were applied to the field it nominally belongs to.
    expect(await screen.findByText(/this password reset token is invalid/i)).toBeInTheDocument();
  });

  it('explains a link that is missing its email parameter', () => {
    renderAt('/password-reset/tok123');

    expect(screen.getByText(/link is incomplete/i)).toBeInTheDocument();
    expect(screen.queryByLabelText(/new password/i)).not.toBeInTheDocument();
  });
});
