import { render, screen } from '@testing-library/react';
import { MemoryRouter } from 'react-router-dom';
import { describe, expect, it } from 'vitest';
import { BmiResult } from './BmiResult';

describe('BmiResult', () => {
  it('renders the bmi value, category, and registration cta', () => {
    render(
      <MemoryRouter>
        <BmiResult result={{ value: 22.9, category: 'normal' }} />
      </MemoryRouter>,
    );

    expect(screen.getByText('22.9')).toBeInTheDocument();
    expect(screen.getByText('Normal weight')).toBeInTheDocument();
    expect(screen.getByRole('link', { name: /create a free account/i })).toHaveAttribute(
      'href',
      '/register',
    );
  });
});
