import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { apiFetch } from '@/lib/api';

export type ExerciseType = 'strength' | 'cardio';
export type ExerciseLocation = 'gym' | 'home' | 'outdoor';
export type ExerciseDifficulty = 'beginner' | 'intermediate' | 'advanced';
export type MuscleGroup = 'chest' | 'back' | 'legs' | 'shoulders' | 'arms' | 'core';

export interface Exercise {
  id: number;
  type: ExerciseType;
  location: ExerciseLocation;
  difficulty: ExerciseDifficulty;
  muscle_group: MuscleGroup | null;
  sets: number | null;
  reps: number | null;
  duration_minutes: number | null;
  name: Record<string, string>;
  instructions: Record<string, string>;
}

export type ExerciseInput = Omit<Exercise, 'id'>;

const QUERY_KEY = ['admin', 'exercises'];

export function useAdminExercises() {
  return useQuery({
    queryKey: QUERY_KEY,
    queryFn: () => apiFetch<Exercise[]>('/admin/exercises'),
  });
}

export function useCreateExercise() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (values: ExerciseInput) =>
      apiFetch<Exercise>('/admin/exercises', {
        method: 'POST',
        body: JSON.stringify(values),
      }),
    onSuccess: () => queryClient.invalidateQueries({ queryKey: QUERY_KEY }),
  });
}

export function useUpdateExercise() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: ({ id, values }: { id: number; values: ExerciseInput }) =>
      apiFetch<Exercise>(`/admin/exercises/${id}`, {
        method: 'PUT',
        body: JSON.stringify(values),
      }),
    onSuccess: () => queryClient.invalidateQueries({ queryKey: QUERY_KEY }),
  });
}

export function useDeleteExercise() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (id: number) => apiFetch<void>(`/admin/exercises/${id}`, { method: 'DELETE' }),
    onSuccess: () => queryClient.invalidateQueries({ queryKey: QUERY_KEY }),
  });
}
