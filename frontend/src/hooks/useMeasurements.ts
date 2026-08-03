import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { apiFetch } from '@/lib/api';
import type { BmiCategory } from '@/lib/bmi';

export interface Measurement {
  id: number;
  weight_kg: number;
  height_cm: number;
  value: number;
  category: BmiCategory;
  measured_on: string;
}

interface MeasurementsPage {
  data: Measurement[];
  current_page: number;
  last_page: number;
  total: number;
}

export function useMeasurements() {
  return useQuery({
    queryKey: ['measurements'],
    queryFn: () => apiFetch<MeasurementsPage>('/measurements'),
  });
}

interface RecordMeasurementValues {
  weightKg: number;
  heightCm: number;
}

export function useRecordMeasurement() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (values: RecordMeasurementValues) =>
      apiFetch<Measurement>('/measurements', {
        method: 'POST',
        body: JSON.stringify({ weight_kg: values.weightKg, height_cm: values.heightCm }),
      }),
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['measurements'] }),
  });
}
