import { screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { http, HttpResponse } from 'msw';
import { afterEach, describe, expect, it, vi } from 'vitest';
import { server } from '@/mocks/server';
import { setUnitSystem } from '@/lib/unitPreference';
import { renderWithProviders } from '@/test/renderWithProviders';
import { MeasurementForm } from './MeasurementForm';

describe('MeasurementForm', () => {
  afterEach(() => setUnitSystem('metric'));

  it('shows inline validation errors for invalid input', async () => {
    const user = userEvent.setup();
    renderWithProviders(<MeasurementForm />);

    await user.click(screen.getByRole('button', { name: /save measurement/i }));

    expect(await screen.findByText(/enter a weight/i)).toBeInTheDocument();
    expect(await screen.findByText(/enter a height/i)).toBeInTheDocument();
  });

  it('sends weight_kg/height_cm converted to metric when the imperial unit is active', async () => {
    setUnitSystem('imperial');

    const requests = vi.fn();
    server.use(
      http.post('http://localhost:8000/api/measurements', async ({ request }) => {
        requests(await request.json());

        return HttpResponse.json({
          id: 1,
          weight_kg: 70,
          height_cm: 180,
          age: 30,
          sex: 'male',
          activity_level: 'moderate',
          value: 21.6,
          category: 'normal',
          measured_on: '2026-01-01',
        });
      }),
    );

    const user = userEvent.setup();
    renderWithProviders(<MeasurementForm />);

    expect(screen.getByLabelText(/weight/i)).toHaveAccessibleName(/weight \(lb\)/i);
    expect(screen.getByLabelText(/height/i)).toHaveAccessibleName(/height \(in\)/i);

    await user.type(screen.getByLabelText(/weight/i), '154.32');
    await user.type(screen.getByLabelText(/height/i), '70.87');
    await user.type(screen.getByLabelText(/age/i), '30');
    await user.click(screen.getByRole('button', { name: /save measurement/i }));

    await waitFor(() => expect(requests).toHaveBeenCalled());
    const [body] = requests.mock.calls[0] as [{ weight_kg: number; height_cm: number }];
    expect(body.weight_kg).toBeCloseTo(70, 0);
    expect(body.height_cm).toBeCloseTo(180, 0);
  });
});
