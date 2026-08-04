import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { apiFetch } from '@/lib/api';

export type NutritionGoal = 'fat_loss' | 'muscle_gain' | 'maintenance';
export type MealTime = 'breakfast' | 'second_breakfast' | 'lunch' | 'afternoon_snack' | 'dinner';

export interface NutritionPlanItem {
  id: string;
  day: number;
  meal_time: MealTime;
  calories: number;
  protein_g: number;
  fat_g: number;
  carbs_g: number;
  name: string;
  description: string;
}

export interface NutritionPlan {
  id: number;
  goal: NutritionGoal;
  daily_calorie_target: number;
  daily_protein_target_g: number;
  daily_fat_target_g: number;
  daily_carbs_target_g: number;
  items: NutritionPlanItem[];
  disclaimer: string;
  created_at: string;
}

interface NutritionPlansPage {
  data: NutritionPlan[];
  current_page: number;
  last_page: number;
  total: number;
}

export function useNutritionPlans() {
  return useQuery({
    queryKey: ['nutrition-plans'],
    queryFn: () => apiFetch<NutritionPlansPage>('/nutrition-plans'),
  });
}

export function useNutritionPlan(id: number | null) {
  return useQuery({
    queryKey: ['nutrition-plans', id],
    queryFn: () => apiFetch<NutritionPlan>(`/nutrition-plans/${id}`),
    enabled: id !== null,
  });
}

export function useGenerateNutritionPlan() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (goal: NutritionGoal) =>
      apiFetch<NutritionPlan>('/nutrition-plans', {
        method: 'POST',
        body: JSON.stringify({ goal }),
      }),
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['nutrition-plans'] }),
  });
}
