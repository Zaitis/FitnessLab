import { useState } from 'react';
import { useTranslation } from 'react-i18next';
import { ExerciseForm } from '@/components/ExerciseForm';
import { Button } from '@/components/ui/button';
import {
  useAdminExercises,
  useCreateExercise,
  useDeleteExercise,
  useUpdateExercise,
  type Exercise,
  type ExerciseInput,
} from '@/hooks/useAdminExercises';

type FormState = 'closed' | 'create' | number;

export function AdminExercisesPage() {
  const { t, i18n } = useTranslation();
  const { data: exercises, isLoading } = useAdminExercises();
  const createMutation = useCreateExercise();
  const updateMutation = useUpdateExercise();
  const deleteMutation = useDeleteExercise();
  const [formState, setFormState] = useState<FormState>('closed');

  const editingExercise =
    typeof formState === 'number'
      ? exercises?.find((exercise) => exercise.id === formState)
      : undefined;

  function handleSubmit(values: ExerciseInput) {
    if (typeof formState === 'number') {
      updateMutation.mutate({ id: formState, values }, { onSuccess: () => setFormState('closed') });

      return;
    }

    createMutation.mutate(values, { onSuccess: () => setFormState('closed') });
  }

  function handleDelete(exercise: Exercise) {
    if (window.confirm(t('admin.exercises.confirmDelete'))) {
      deleteMutation.mutate(exercise.id);
    }
  }

  function details(exercise: Exercise): string {
    if (exercise.type === 'cardio') {
      return t('workoutPlan.duration', { minutes: exercise.duration_minutes });
    }

    return t('workoutPlan.setsReps', { sets: exercise.sets, reps: exercise.reps });
  }

  const activeMutation = typeof formState === 'number' ? updateMutation : createMutation;

  return (
    <div className="flex flex-col gap-6">
      <div className="flex items-center justify-between">
        <h1 className="text-2xl font-bold">{t('admin.exercises.title')}</h1>
        {formState === 'closed' && (
          <Button type="button" onClick={() => setFormState('create')}>
            {t('admin.exercises.addNew')}
          </Button>
        )}
      </div>

      {formState !== 'closed' && (
        <ExerciseForm
          exercise={editingExercise}
          onSubmit={handleSubmit}
          onCancel={() => setFormState('closed')}
          isSubmitting={activeMutation.isPending}
          isError={activeMutation.isError}
        />
      )}

      {!isLoading && exercises?.length === 0 && (
        <p className="text-muted-foreground">{t('admin.exercises.empty')}</p>
      )}

      {exercises && exercises.length > 0 && (
        <table className="w-full text-left text-sm">
          <thead>
            <tr className="border-b text-muted-foreground">
              <th className="py-2">{t('admin.exercises.table.name')}</th>
              <th className="py-2">{t('admin.exercises.table.type')}</th>
              <th className="py-2">{t('admin.exercises.table.location')}</th>
              <th className="py-2">{t('admin.exercises.table.difficulty')}</th>
              <th className="py-2">{t('admin.exercises.table.muscleGroup')}</th>
              <th className="py-2">{t('admin.exercises.table.details')}</th>
              <th className="py-2">{t('admin.exercises.table.actions')}</th>
            </tr>
          </thead>
          <tbody>
            {exercises.map((exercise) => (
              <tr key={exercise.id} className="border-b align-top last:border-0">
                <td className="py-2">{exercise.name[i18n.language] ?? exercise.name.en}</td>
                <td className="py-2">{t(`admin.exercises.types.${exercise.type}`)}</td>
                <td className="py-2">{t(`admin.exercises.locations.${exercise.location}`)}</td>
                <td className="py-2">{t(`workoutPlan.experienceLevels.${exercise.difficulty}`)}</td>
                <td className="py-2">
                  {exercise.muscle_group
                    ? t(`admin.exercises.muscleGroups.${exercise.muscle_group}`)
                    : '—'}
                </td>
                <td className="py-2">{details(exercise)}</td>
                <td className="py-2">
                  <div className="flex gap-3">
                    <button
                      type="button"
                      onClick={() => setFormState(exercise.id)}
                      className="text-muted-foreground underline-offset-2 hover:text-foreground hover:underline"
                    >
                      {t('admin.exercises.edit')}
                    </button>
                    <button
                      type="button"
                      onClick={() => handleDelete(exercise)}
                      className="text-destructive underline-offset-2 hover:underline"
                    >
                      {t('admin.exercises.delete')}
                    </button>
                  </div>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      )}
    </div>
  );
}
