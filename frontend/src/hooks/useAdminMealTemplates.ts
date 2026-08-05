import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { apiFetch } from '@/lib/api';

export type MealTime = 'breakfast' | 'second_breakfast' | 'lunch' | 'afternoon_snack' | 'dinner';

export interface MealTemplate {
  id: number;
  meal_time: MealTime;
  calories: number;
  protein_g: number;
  fat_g: number;
  carbs_g: number;
  name: Record<string, string>;
  description: Record<string, string>;
}

export type MealTemplateInput = Omit<MealTemplate, 'id'>;

const QUERY_KEY = ['admin', 'meal-templates'];

export function useAdminMealTemplates() {
  return useQuery({
    queryKey: QUERY_KEY,
    queryFn: () => apiFetch<MealTemplate[]>('/admin/meal-templates'),
  });
}

export function useCreateMealTemplate() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (values: MealTemplateInput) =>
      apiFetch<MealTemplate>('/admin/meal-templates', {
        method: 'POST',
        body: JSON.stringify(values),
      }),
    onSuccess: () => queryClient.invalidateQueries({ queryKey: QUERY_KEY }),
  });
}

export function useUpdateMealTemplate() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: ({ id, values }: { id: number; values: MealTemplateInput }) =>
      apiFetch<MealTemplate>(`/admin/meal-templates/${id}`, {
        method: 'PUT',
        body: JSON.stringify(values),
      }),
    onSuccess: () => queryClient.invalidateQueries({ queryKey: QUERY_KEY }),
  });
}

export function useDeleteMealTemplate() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (id: number) => apiFetch<void>(`/admin/meal-templates/${id}`, { method: 'DELETE' }),
    onSuccess: () => queryClient.invalidateQueries({ queryKey: QUERY_KEY }),
  });
}
