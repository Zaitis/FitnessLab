import { screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { delay, http, HttpResponse } from 'msw';
import { afterEach, describe, expect, it, vi } from 'vitest';
import { server } from '@/mocks/server';
import { setUnitSystem } from '@/lib/unitPreference';
import { renderWithProviders } from '@/test/renderWithProviders';
import { BmiForm } from './BmiForm';

function renderForm(onResult = vi.fn()) {
  renderWithProviders(<BmiForm onResult={onResult} />);

  return { onResult };
}

describe('BmiForm', () => {
  afterEach(() => setUnitSystem('metric'));

  it('shows inline validation errors for invalid input', async () => {
    const user = userEvent.setup();
    renderForm();

    await user.click(screen.getByRole('button', { name: /calculate/i }));

    expect(await screen.findByText(/enter a weight/i)).toBeInTheDocument();
    expect(await screen.findByText(/enter a height/i)).toBeInTheDocument();
  });

  it('disables the submit button while the request is pending', async () => {
    server.use(
      http.post('http://localhost:8000/api/bmi/calculate', async () => {
        await delay(50);

        return HttpResponse.json({ value: 22.9, category: 'normal' });
      }),
    );

    const user = userEvent.setup();
    renderForm();

    await user.type(screen.getByLabelText(/weight/i), '70');
    await user.type(screen.getByLabelText(/height/i), '175');
    await user.type(screen.getByLabelText(/age/i), '30');
    await user.click(screen.getByRole('button', { name: /calculate/i }));

    expect(screen.getByRole('button')).toBeDisabled();

    await waitFor(() => expect(screen.getByRole('button')).not.toBeDisabled());
  });

  it('calls onResult with the calculated bmi on success', async () => {
    const user = userEvent.setup();
    const { onResult } = renderForm();

    await user.type(screen.getByLabelText(/weight/i), '70');
    await user.type(screen.getByLabelText(/height/i), '175');
    await user.type(screen.getByLabelText(/age/i), '30');
    await user.click(screen.getByRole('button', { name: /calculate/i }));

    await waitFor(() => expect(onResult).toHaveBeenCalledWith({ value: 22.9, category: 'normal' }));
  });

  it('sends weight_kg/height_cm converted to metric when the imperial unit is active', async () => {
    setUnitSystem('imperial');

    const requests = vi.fn();
    server.use(
      http.post('http://localhost:8000/api/bmi/calculate', async ({ request }) => {
        requests(await request.json());

        return HttpResponse.json({ value: 22.9, category: 'normal' });
      }),
    );

    const user = userEvent.setup();
    renderForm();

    expect(screen.getByLabelText(/weight/i)).toHaveAccessibleName(/weight \(lb\)/i);
    expect(screen.getByLabelText(/height/i)).toHaveAccessibleName(/height \(in\)/i);

    await user.type(screen.getByLabelText(/weight/i), '154.32');
    await user.type(screen.getByLabelText(/height/i), '70.87');
    await user.type(screen.getByLabelText(/age/i), '30');
    await user.click(screen.getByRole('button', { name: /calculate/i }));

    await waitFor(() => expect(requests).toHaveBeenCalled());
    const [body] = requests.mock.calls[0] as [{ weight_kg: number; height_cm: number }];
    expect(body.weight_kg).toBeCloseTo(70, 0);
    expect(body.height_cm).toBeCloseTo(180, 0);
  });

  it('shows a generic error message when the request fails', async () => {
    server.use(
      http.post('http://localhost:8000/api/bmi/calculate', () =>
        HttpResponse.json({}, { status: 500 }),
      ),
    );

    const user = userEvent.setup();
    renderForm();

    await user.type(screen.getByLabelText(/weight/i), '70');
    await user.type(screen.getByLabelText(/height/i), '175');
    await user.type(screen.getByLabelText(/age/i), '30');
    await user.click(screen.getByRole('button', { name: /calculate/i }));

    expect(await screen.findByText(/something went wrong/i)).toBeInTheDocument();
  });
});
