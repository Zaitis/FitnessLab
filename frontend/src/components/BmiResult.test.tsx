import { screen } from '@testing-library/react';
import { describe, expect, it } from 'vitest';
import { renderWithProviders } from '@/test/renderWithProviders';
import { BmiResult } from './BmiResult';

describe('BmiResult', () => {
  it('renders the bmi value, category, and registration cta', () => {
    renderWithProviders(<BmiResult result={{ value: 22.9, category: 'normal' }} />);

    expect(screen.getByText('22.9')).toBeInTheDocument();
    expect(screen.getByText('Normal weight')).toBeInTheDocument();
    expect(screen.getByRole('link', { name: /create a free account/i })).toHaveAttribute(
      'href',
      '/register',
    );
  });
});
