import { zodResolver } from '@hookform/resolvers/zod';
import { useForm } from 'react-hook-form';
import { useTranslation } from 'react-i18next';
import { z } from 'zod';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useGenerateWorkoutPlan } from '@/hooks/useWorkoutPlans';

const schema = z.object({
  goal: z.enum(['fat_loss', 'muscle_gain', 'maintenance']),
  experience_level: z.enum(['beginner', 'intermediate', 'advanced']),
  days_per_week: z.coerce.number().int().min(1).max(6),
  location: z.enum(['gym', 'home']),
});

type FormInput = z.input<typeof schema>;
type FormValues = z.output<typeof schema>;

interface WorkoutPlanFormProps {
  onGenerated: (planId: number) => void;
}

export function WorkoutPlanForm({ onGenerated }: WorkoutPlanFormProps) {
  const { t } = useTranslation();
  const {
    register,
    handleSubmit,
    formState: { errors },
  } = useForm<FormInput, unknown, FormValues>({
    resolver: zodResolver(schema),
    defaultValues: {
      goal: 'fat_loss',
      experience_level: 'beginner',
      days_per_week: 3,
      location: 'gym',
    },
  });

  const mutation = useGenerateWorkoutPlan();

  return (
    <form
      onSubmit={handleSubmit((values) =>
        mutation.mutate(values, { onSuccess: (plan) => onGenerated(plan.id) }),
      )}
      noValidate
      className="flex flex-col gap-4 rounded-xl border p-6"
    >
      <h2 className="text-xl font-semibold">{t('workoutPlan.form.title')}</h2>

      <div className="flex flex-col gap-1.5">
        <Label htmlFor="goal">{t('workoutPlan.form.goalLabel')}</Label>
        <select
          id="goal"
          className="rounded-md border border-input bg-transparent px-2 py-1.5 text-sm"
          {...register('goal')}
        >
          <option value="fat_loss">{t('workoutPlan.goals.fat_loss')}</option>
          <option value="muscle_gain">{t('workoutPlan.goals.muscle_gain')}</option>
          <option value="maintenance">{t('workoutPlan.goals.maintenance')}</option>
        </select>
      </div>

      <div className="flex flex-col gap-1.5">
        <Label htmlFor="experience_level">{t('workoutPlan.form.experienceLabel')}</Label>
        <select
          id="experience_level"
          className="rounded-md border border-input bg-transparent px-2 py-1.5 text-sm"
          {...register('experience_level')}
        >
          <option value="beginner">{t('workoutPlan.experienceLevels.beginner')}</option>
          <option value="intermediate">{t('workoutPlan.experienceLevels.intermediate')}</option>
          <option value="advanced">{t('workoutPlan.experienceLevels.advanced')}</option>
        </select>
      </div>

      <div className="flex flex-col gap-1.5">
        <Label htmlFor="days_per_week">{t('workoutPlan.form.daysLabel')}</Label>
        <Input
          id="days_per_week"
          type="number"
          min={1}
          max={6}
          aria-invalid={Boolean(errors.days_per_week)}
          {...register('days_per_week')}
        />
        {errors.days_per_week && (
          <p className="text-sm text-destructive">{t('workoutPlan.form.errors.days')}</p>
        )}
      </div>

      <div className="flex flex-col gap-1.5">
        <Label htmlFor="location">{t('workoutPlan.form.locationLabel')}</Label>
        <select
          id="location"
          className="rounded-md border border-input bg-transparent px-2 py-1.5 text-sm"
          {...register('location')}
        >
          <option value="gym">{t('workoutPlan.locations.gym')}</option>
          <option value="home">{t('workoutPlan.locations.home')}</option>
        </select>
      </div>

      {mutation.isError && (
        <p className="text-sm text-destructive">{t('workoutPlan.form.errors.generic')}</p>
      )}

      <Button type="submit" disabled={mutation.isPending}>
        {mutation.isPending ? t('workoutPlan.form.submitting') : t('workoutPlan.form.submit')}
      </Button>
    </form>
  );
}
