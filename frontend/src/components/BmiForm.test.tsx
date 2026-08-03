import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { render, screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { delay, http, HttpResponse } from 'msw';
import { describe, expect, it, vi } from 'vitest';
import { server } from '@/mocks/server';
import { BmiForm } from './BmiForm';

function renderForm(onResult = vi.fn()) {
  const queryClient = new QueryClient();
  render(
    <QueryClientProvider client={queryClient}>
      <BmiForm onResult={onResult} />
    </QueryClientProvider>,
  );

  return { onResult };
}

describe('BmiForm', () => {
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
    await user.click(screen.getByRole('button', { name: /calculate/i }));

    expect(screen.getByRole('button')).toBeDisabled();

    await waitFor(() => expect(screen.getByRole('button')).not.toBeDisabled());
  });

  it('calls onResult with the calculated bmi on success', async () => {
    const user = userEvent.setup();
    const { onResult } = renderForm();

    await user.type(screen.getByLabelText(/weight/i), '70');
    await user.type(screen.getByLabelText(/height/i), '175');
    await user.click(screen.getByRole('button', { name: /calculate/i }));

    await waitFor(() => expect(onResult).toHaveBeenCalledWith({ value: 22.9, category: 'normal' }));
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
    await user.click(screen.getByRole('button', { name: /calculate/i }));

    expect(await screen.findByText(/something went wrong/i)).toBeInTheDocument();
  });
});
