import { screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { http, HttpResponse } from 'msw';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import { readPendingMeasurement, savePendingMeasurement } from '@/lib/pendingMeasurement';
import { server } from '@/mocks/server';
import { renderWithProviders } from '@/test/renderWithProviders';
import { RegisterPage } from './RegisterPage';

async function fillAndSubmit(user: ReturnType<typeof userEvent.setup>, email: string) {
  await user.type(screen.getByLabelText(/^name$/i), 'Ada Lovelace');
  await user.type(screen.getByLabelText(/^email$/i), email);
  await user.type(screen.getByLabelText(/^password$/i), 'password123');
  await user.type(screen.getByLabelText(/confirm password/i), 'password123');
  await user.click(screen.getByRole('button', { name: /create account/i }));
}

describe('RegisterPage', () => {
  beforeEach(() => {
    window.sessionStorage.clear();
  });

  it('renders server validation errors', async () => {
    server.use(
      http.post('http://localhost:8000/api/register', () =>
        HttpResponse.json(
          {
            message: 'The email has already been taken.',
            errors: { email: ['The email has already been taken.'] },
          },
          { status: 422 },
        ),
      ),
    );

    const user = userEvent.setup();
    renderWithProviders(<RegisterPage />);

    await fillAndSubmit(user, 'ada@example.com');

    expect(await screen.findByText('The email has already been taken.')).toBeInTheDocument();
  });

  it('submits the pending measurement exactly once after registering, then clears it', async () => {
    savePendingMeasurement({ weightKg: 70, heightCm: 175 });

    const measurementRequests = vi.fn();
    server.use(
      http.post('http://localhost:8000/api/measurements', async ({ request }) => {
        measurementRequests(await request.json());

        return HttpResponse.json({ id: 1, value: 22.9, category: 'normal' }, { status: 201 });
      }),
    );

    const user = userEvent.setup();
    renderWithProviders(<RegisterPage />);

    await fillAndSubmit(user, 'ada@example.com');

    await waitFor(() => expect(measurementRequests).toHaveBeenCalledTimes(1));
    expect(measurementRequests).toHaveBeenCalledWith({ weight_kg: 70, height_cm: 175 });
    expect(readPendingMeasurement()).toBeNull();
  });

  it('does not resubmit a measurement on reload once none is pending', async () => {
    const measurementRequests = vi.fn();
    server.use(
      http.post('http://localhost:8000/api/measurements', async ({ request }) => {
        measurementRequests(await request.json());

        return HttpResponse.json({ id: 1, value: 22.9, category: 'normal' }, { status: 201 });
      }),
    );

    const user = userEvent.setup();
    renderWithProviders(<RegisterPage />);

    await fillAndSubmit(user, 'ada2@example.com');

    await waitFor(() => expect(screen.getByRole('button')).not.toBeDisabled());
    expect(measurementRequests).not.toHaveBeenCalled();
  });
});
