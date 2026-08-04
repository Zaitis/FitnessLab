import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { apiFetch } from '@/lib/api';
import type { ActivityLevel, BmiCategory, Sex } from '@/lib/bmi';

export interface Measurement {
  id: number;
  weight_kg: number;
  height_cm: number;
  age: number;
  sex: Sex;
  activity_level: ActivityLevel;
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
  age: number;
  sex: Sex;
  activityLevel: ActivityLevel;
}

export function useRecordMeasurement() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (values: RecordMeasurementValues) =>
      apiFetch<Measurement>('/measurements', {
        method: 'POST',
        body: JSON.stringify({
          weight_kg: values.weightKg,
          height_cm: values.heightCm,
          age: values.age,
          sex: values.sex,
          activity_level: values.activityLevel,
        }),
      }),
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['measurements'] }),
  });
}
