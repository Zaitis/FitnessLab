import { render, screen } from '@testing-library/react';
import { describe, expect, it } from 'vitest';
import '@/i18n';
import { MeasurementChart } from './MeasurementChart';

function makeMeasurement(id: number, measuredOn: string, weightKg: number, value: number) {
  return {
    id,
    weight_kg: weightKg,
    height_cm: 175,
    value,
    category: 'normal' as const,
    measured_on: measuredOn,
  };
}

describe('MeasurementChart', () => {
  it('shows an empty state with zero points', () => {
    render(<MeasurementChart measurements={[]} />);

    expect(screen.getByText(/no measurements yet/i)).toBeInTheDocument();
    expect(screen.queryByRole('img')).not.toBeInTheDocument();
  });

  it('renders the chart with a single point', () => {
    render(<MeasurementChart measurements={[makeMeasurement(1, '2026-01-01', 70, 22.9)]} />);

    expect(screen.getByRole('img', { name: /weight and bmi trend/i })).toBeInTheDocument();
  });

  it('renders the chart with many points', () => {
    const measurements = Array.from({ length: 12 }, (_, i) =>
      makeMeasurement(i + 1, `2026-01-${String(i + 1).padStart(2, '0')}`, 70 + i, 22 + i * 0.1),
    );

    render(<MeasurementChart measurements={measurements} />);

    expect(screen.getByRole('img', { name: /weight and bmi trend/i })).toBeInTheDocument();
  });
});
