import { screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { delay, http, HttpResponse } from 'msw';
import { describe, expect, it } from 'vitest';
import { server } from '@/mocks/server';
import { renderWithProviders } from '@/test/renderWithProviders';
import { ForgotPasswordPage } from './ForgotPasswordPage';

const API_ROOT = 'http://localhost:8000/api';

describe('ForgotPasswordPage', () => {
  it('validates the email before submitting', async () => {
    const user = userEvent.setup();
    renderWithProviders(<ForgotPasswordPage />);

    await user.type(screen.getByLabelText(/email/i), 'not-an-email');
    await user.click(screen.getByRole('button', { name: /send reset link/i }));

    expect(await screen.findByText(/invalid email/i)).toBeInTheDocument();
  });

  it('disables the submit button while the request is pending', async () => {
    server.use(
      http.post(`${API_ROOT}/forgot-password`, async () => {
        await delay(50);

        return HttpResponse.json({ status: 'sent' });
      }),
    );

    const user = userEvent.setup();
    renderWithProviders(<ForgotPasswordPage />);

    await user.type(screen.getByLabelText(/email/i), 'ada@example.com');
    await user.click(screen.getByRole('button', { name: /send reset link/i }));

    expect(screen.getByRole('button')).toBeDisabled();
  });

  it('shows the same non-committal confirmation for any address', async () => {
    const user = userEvent.setup();
    const { unmount } = renderWithProviders(<ForgotPasswordPage />);

    await user.type(screen.getByLabelText(/email/i), 'registered@example.com');
    await user.click(screen.getByRole('button', { name: /send reset link/i }));

    const known = await screen.findByText(/if that address has an account/i);
    const knownCopy = known.textContent;

    unmount();

    renderWithProviders(<ForgotPasswordPage />);
    await user.type(screen.getByLabelText(/email/i), 'stranger@example.com');
    await user.click(screen.getByRole('button', { name: /send reset link/i }));

    // The endpoint is deliberately not an enumeration oracle; the UI must not
    // become one either by wording the two cases differently.
    expect((await screen.findByText(/if that address has an account/i)).textContent).toBe(
      knownCopy,
    );
  });
});
