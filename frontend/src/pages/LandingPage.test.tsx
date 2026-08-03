import { screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { describe, expect, it } from 'vitest';
import { renderWithProviders } from '@/test/renderWithProviders';
import { LandingPage } from './LandingPage';

function renderPage() {
  renderWithProviders(<LandingPage />);
}

describe('LandingPage', () => {
  it('shows the registration cta only after a bmi result exists', async () => {
    const user = userEvent.setup();
    renderPage();

    expect(screen.queryByRole('link', { name: /create a free account/i })).not.toBeInTheDocument();

    await user.type(screen.getByLabelText(/weight/i), '70');
    await user.type(screen.getByLabelText(/height/i), '175');
    await user.click(screen.getByRole('button', { name: /calculate/i }));

    expect(await screen.findByRole('link', { name: /create a free account/i })).toBeInTheDocument();
  });
});
