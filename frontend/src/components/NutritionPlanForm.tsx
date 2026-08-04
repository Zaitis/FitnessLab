import { useForm } from 'react-hook-form';
import { useTranslation } from 'react-i18next';
import { Link } from 'react-router-dom';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { useGenerateNutritionPlan } from '@/hooks/useNutritionPlans';
import { ApiError } from '@/lib/api';

interface FormValues {
  goal: 'fat_loss' | 'muscle_gain' | 'maintenance';
}

interface NutritionPlanFormProps {
  onGenerated: (planId: number) => void;
}

export function NutritionPlanForm({ onGenerated }: NutritionPlanFormProps) {
  const { t } = useTranslation();
  const { register, handleSubmit } = useForm<FormValues>({
    defaultValues: { goal: 'fat_loss' },
  });

  const mutation = useGenerateNutritionPlan();
  const needsMeasurement =
    mutation.error instanceof ApiError && Boolean(mutation.error.errors?.measurement);

  return (
    <form
      onSubmit={handleSubmit((values) =>
        mutation.mutate(values.goal, { onSuccess: (plan) => onGenerated(plan.id) }),
      )}
      noValidate
      className="flex flex-col gap-4 rounded-xl border p-6"
    >
      <h2 className="text-xl font-semibold">{t('nutritionPlan.form.title')}</h2>

      <div className="flex flex-col gap-1.5">
        <Label htmlFor="goal">{t('nutritionPlan.form.goalLabel')}</Label>
        <select
          id="goal"
          className="rounded-md border border-input bg-transparent px-2 py-1.5 text-sm"
          {...register('goal')}
        >
          <option value="fat_loss">{t('nutritionPlan.goals.fat_loss')}</option>
          <option value="muscle_gain">{t('nutritionPlan.goals.muscle_gain')}</option>
          <option value="maintenance">{t('nutritionPlan.goals.maintenance')}</option>
        </select>
      </div>

      {needsMeasurement && (
        <p className="text-sm text-destructive">
          {t('nutritionPlan.form.errors.needsMeasurement')}{' '}
          <Link to="/dashboard" className="underline underline-offset-2">
            {t('nutritionPlan.form.errors.needsMeasurementLink')}
          </Link>
        </p>
      )}
      {mutation.isError && !needsMeasurement && (
        <p className="text-sm text-destructive">{t('nutritionPlan.form.errors.generic')}</p>
      )}

      <Button type="submit" disabled={mutation.isPending}>
        {mutation.isPending ? t('nutritionPlan.form.submitting') : t('nutritionPlan.form.submit')}
      </Button>
    </form>
  );
}
