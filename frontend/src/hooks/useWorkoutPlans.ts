import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { apiFetch } from '@/lib/api';

export type Goal = 'fat_loss' | 'muscle_gain' | 'maintenance';
export type ExperienceLevel = 'beginner' | 'intermediate' | 'advanced';
export type ExerciseLocation = 'gym' | 'home' | 'outdoor';
export type ExerciseType = 'strength' | 'cardio';

export interface WorkoutPlanItem {
  id: string;
  day: number;
  type: ExerciseType;
  name: string;
  instructions: string;
  sets: number | null;
  reps: number | null;
  duration_minutes: number | null;
}

export interface WorkoutPlan {
  id: number;
  goal: Goal;
  experience_level: ExperienceLevel;
  days_per_week: number;
  items: WorkoutPlanItem[];
  disclaimer: string;
  created_at: string;
}

interface WorkoutPlansPage {
  data: WorkoutPlan[];
  current_page: number;
  last_page: number;
  total: number;
}

export function useWorkoutPlans() {
  return useQuery({
    queryKey: ['workout-plans'],
    queryFn: () => apiFetch<WorkoutPlansPage>('/workout-plans'),
  });
}

export function useWorkoutPlan(id: number | null) {
  return useQuery({
    queryKey: ['workout-plans', id],
    queryFn: () => apiFetch<WorkoutPlan>(`/workout-plans/${id}`),
    enabled: id !== null,
  });
}

interface GenerateWorkoutPlanValues {
  goal: Goal;
  experience_level: ExperienceLevel;
  days_per_week: number;
  location: ExerciseLocation;
}

export function useGenerateWorkoutPlan() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (values: GenerateWorkoutPlanValues) =>
      apiFetch<WorkoutPlan>('/workout-plans', {
        method: 'POST',
        body: JSON.stringify(values),
      }),
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['workout-plans'] }),
  });
}
