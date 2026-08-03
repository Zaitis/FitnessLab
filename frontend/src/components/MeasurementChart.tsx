import { useTranslation } from 'react-i18next';
import {
  CartesianGrid,
  Legend,
  Line,
  ReferenceArea,
  ResponsiveContainer,
  ComposedChart,
  Tooltip,
  XAxis,
  YAxis,
} from 'recharts';
import type { Measurement } from '@/hooks/useMeasurements';

interface MeasurementChartProps {
  measurements: Measurement[];
}

const CATEGORY_BANDS = [
  { category: 'underweight', from: 0, to: 18.5, color: '#60a5fa' },
  { category: 'normal', from: 18.5, to: 25, color: '#4ade80' },
  { category: 'overweight', from: 25, to: 30, color: '#fbbf24' },
  { category: 'obese', from: 30, to: 60, color: '#f87171' },
] as const;

export function MeasurementChart({ measurements }: MeasurementChartProps) {
  const { t } = useTranslation();

  if (measurements.length === 0) {
    return <p className="text-muted-foreground">{t('progress.empty')}</p>;
  }

  const chronological = [...measurements].reverse();
  const maxBmi = Math.max(40, ...chronological.map((m) => m.value));

  return (
    <div className="h-80 w-full" role="img" aria-label={t('progress.chartTitle')}>
      <ResponsiveContainer width="100%" height="100%">
        <ComposedChart data={chronological} margin={{ top: 8, right: 8, bottom: 8, left: 8 }}>
          <CartesianGrid strokeDasharray="3 3" stroke="#71717a33" />
          {CATEGORY_BANDS.map((band) => (
            <ReferenceArea
              key={band.category}
              yAxisId="bmi"
              y1={band.from}
              y2={Math.min(band.to, maxBmi)}
              fill={band.color}
              fillOpacity={0.15}
              ifOverflow="hidden"
            />
          ))}
          <XAxis dataKey="measured_on" stroke="#71717a" tick={{ fontSize: 12 }} />
          <YAxis
            yAxisId="bmi"
            domain={[0, maxBmi]}
            stroke="#71717a"
            tick={{ fontSize: 12 }}
            label={{ value: t('progress.chart.bmiAxis'), angle: -90, position: 'insideLeft' }}
          />
          <YAxis
            yAxisId="weight"
            orientation="right"
            stroke="#71717a"
            tick={{ fontSize: 12 }}
            label={{ value: t('progress.chart.weightAxis'), angle: 90, position: 'insideRight' }}
          />
          <Tooltip />
          <Legend />
          <Line
            yAxisId="bmi"
            type="monotone"
            dataKey="value"
            name={t('progress.chart.bmiAxis')}
            stroke="#3b82f6"
            strokeWidth={2}
            dot
          />
          <Line
            yAxisId="weight"
            type="monotone"
            dataKey="weight_kg"
            name={t('progress.chart.weightAxis')}
            stroke="#a855f7"
            strokeWidth={2}
            dot
          />
        </ComposedChart>
      </ResponsiveContainer>
    </div>
  );
}
