import { zodResolver } from '@hookform/resolvers/zod';
import { useForm } from 'react-hook-form';
import { useTranslation } from 'react-i18next';
import { z } from 'zod';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useRecordMeasurement } from '@/hooks/useMeasurements';

const schema = z.object({
  weightKg: z.coerce.number().min(1).max(500),
  heightCm: z.coerce.number().min(30).max(250),
  age: z.coerce.number().int().min(1).max(120),
  sex: z.enum(['male', 'female']),
  activityLevel: z.enum(['sedentary', 'light', 'moderate', 'active', 'very_active']),
});

type FormInput = z.input<typeof schema>;
type FormValues = z.output<typeof schema>;

export function MeasurementForm() {
  const { t } = useTranslation();
  const {
    register,
    handleSubmit,
    reset,
    formState: { errors },
  } = useForm<FormInput, unknown, FormValues>({
    resolver: zodResolver(schema),
    defaultValues: { sex: 'male', activityLevel: 'moderate' },
  });

  const mutation = useRecordMeasurement();

  return (
    <form
      onSubmit={handleSubmit((values) => mutation.mutate(values, { onSuccess: () => reset() }))}
      noValidate
      className="flex flex-col gap-4 rounded-xl border p-6"
    >
      <h2 className="text-xl font-semibold">{t('progress.form.title')}</h2>

      <div className="flex flex-col gap-1.5">
        <Label htmlFor="weightKg">{t('progress.form.weightLabel')}</Label>
        <Input
          id="weightKg"
          type="number"
          step="0.1"
          aria-invalid={Boolean(errors.weightKg)}
          {...register('weightKg')}
        />
        {errors.weightKg && (
          <p className="text-sm text-destructive">{t('progress.form.errors.weight')}</p>
        )}
      </div>

      <div className="flex flex-col gap-1.5">
        <Label htmlFor="heightCm">{t('progress.form.heightLabel')}</Label>
        <Input
          id="heightCm"
          type="number"
          step="0.1"
          aria-invalid={Boolean(errors.heightCm)}
          {...register('heightCm')}
        />
        {errors.heightCm && (
          <p className="text-sm text-destructive">{t('progress.form.errors.height')}</p>
        )}
      </div>

      <div className="flex flex-col gap-1.5">
        <Label htmlFor="age">{t('demographics.ageLabel')}</Label>
        <Input id="age" type="number" aria-invalid={Boolean(errors.age)} {...register('age')} />
        {errors.age && <p className="text-sm text-destructive">{t('demographics.errors.age')}</p>}
      </div>

      <div className="flex flex-col gap-1.5">
        <Label htmlFor="sex">{t('demographics.sexLabel')}</Label>
        <select
          id="sex"
          className="rounded-md border border-input bg-transparent px-2 py-1.5 text-sm"
          {...register('sex')}
        >
          <option value="male">{t('demographics.sexOptions.male')}</option>
          <option value="female">{t('demographics.sexOptions.female')}</option>
        </select>
      </div>

      <div className="flex flex-col gap-1.5">
        <Label htmlFor="activityLevel">{t('demographics.activityLabel')}</Label>
        <select
          id="activityLevel"
          className="rounded-md border border-input bg-transparent px-2 py-1.5 text-sm"
          {...register('activityLevel')}
        >
          <option value="sedentary">{t('demographics.activityOptions.sedentary')}</option>
          <option value="light">{t('demographics.activityOptions.light')}</option>
          <option value="moderate">{t('demographics.activityOptions.moderate')}</option>
          <option value="active">{t('demographics.activityOptions.active')}</option>
          <option value="very_active">{t('demographics.activityOptions.very_active')}</option>
        </select>
      </div>

      {mutation.isError && (
        <p className="text-sm text-destructive">{t('progress.form.errors.generic')}</p>
      )}
      {mutation.isSuccess && (
        <p className="text-sm text-muted-foreground">{t('progress.form.success')}</p>
      )}

      <Button type="submit" disabled={mutation.isPending}>
        {mutation.isPending ? t('progress.form.submitting') : t('progress.form.submit')}
      </Button>
    </form>
  );
}
