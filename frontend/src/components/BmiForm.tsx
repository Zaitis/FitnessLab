import { zodResolver } from '@hookform/resolvers/zod';
import { useMutation } from '@tanstack/react-query';
import { useEffect, useMemo, useRef } from 'react';
import { useForm } from 'react-hook-form';
import { useTranslation } from 'react-i18next';
import { z } from 'zod';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { apiFetch } from '@/lib/api';
import type { BmiCalculation } from '@/lib/bmi';
import { savePendingMeasurement } from '@/lib/pendingMeasurement';
import { useUnitSystem } from '@/lib/unitPreference';
import { heightToCm, heightToUnit, weightToKg, weightToUnit, type UnitSystem } from '@/lib/units';

const METRIC_WEIGHT_BOUNDS = { min: 1, max: 500 };
const METRIC_HEIGHT_BOUNDS = { min: 30, max: 250 };

function buildSchema(unit: UnitSystem) {
  const weightBounds = {
    min: weightToUnit(METRIC_WEIGHT_BOUNDS.min, unit),
    max: weightToUnit(METRIC_WEIGHT_BOUNDS.max, unit),
  };
  const heightBounds = {
    min: heightToUnit(METRIC_HEIGHT_BOUNDS.min, unit),
    max: heightToUnit(METRIC_HEIGHT_BOUNDS.max, unit),
  };

  return z.object({
    weight: z.coerce.number().min(weightBounds.min).max(weightBounds.max),
    height: z.coerce.number().min(heightBounds.min).max(heightBounds.max),
    age: z.coerce.number().int().min(1).max(120),
    sex: z.enum(['male', 'female']),
    activityLevel: z.enum(['sedentary', 'light', 'moderate', 'active', 'very_active']),
  });
}

type FormInput = z.input<ReturnType<typeof buildSchema>>;
type FormValues = z.output<ReturnType<typeof buildSchema>>;

interface BmiFormProps {
  onResult: (result: BmiCalculation) => void;
}

export function BmiForm({ onResult }: BmiFormProps) {
  const { t } = useTranslation();
  const [unit] = useUnitSystem();
  const schema = useMemo(() => buildSchema(unit), [unit]);
  const {
    register,
    handleSubmit,
    getValues,
    setValue,
    formState: { errors },
  } = useForm<FormInput, unknown, FormValues>({
    resolver: zodResolver(schema),
    defaultValues: { sex: 'male', activityLevel: 'moderate' },
  });

  // Re-express whatever the user already typed in the new unit, rather than
  // leaving a raw number that silently means something else after the toggle.
  const previousUnit = useRef(unit);
  useEffect(() => {
    if (previousUnit.current === unit) {
      return;
    }

    const { weight: rawWeight, height: rawHeight } = getValues();
    if (rawWeight !== undefined && rawWeight !== null && `${rawWeight}` !== '') {
      const weightKg = weightToKg(Number(rawWeight), previousUnit.current);
      setValue('weight', weightToUnit(weightKg, unit));
    }
    if (rawHeight !== undefined && rawHeight !== null && `${rawHeight}` !== '') {
      const heightCm = heightToCm(Number(rawHeight), previousUnit.current);
      setValue('height', heightToUnit(heightCm, unit));
    }
    previousUnit.current = unit;
  }, [unit, getValues, setValue]);

  const weightSuffix = t(`units.suffix.weight.${unit}`);
  const heightSuffix = t(`units.suffix.height.${unit}`);
  const weightBounds = {
    min: Math.round(weightToUnit(METRIC_WEIGHT_BOUNDS.min, unit)),
    max: Math.round(weightToUnit(METRIC_WEIGHT_BOUNDS.max, unit)),
  };
  const heightBounds = {
    min: Math.round(heightToUnit(METRIC_HEIGHT_BOUNDS.min, unit)),
    max: Math.round(heightToUnit(METRIC_HEIGHT_BOUNDS.max, unit)),
  };

  const mutation = useMutation({
    // Age/sex/activity level aren't sent here — the BMI value itself never
    // depends on them, and this endpoint persists nothing. They're carried
    // through to the real POST /measurements call after registration
    // instead, via the pending-measurement payload below.
    mutationFn: (values: FormValues) =>
      apiFetch<BmiCalculation>('/bmi/calculate', {
        method: 'POST',
        body: JSON.stringify({
          weight_kg: weightToKg(values.weight, unit),
          height_cm: heightToCm(values.height, unit),
        }),
      }),
    onSuccess: (data, values) => {
      savePendingMeasurement({
        weightKg: weightToKg(values.weight, unit),
        heightCm: heightToCm(values.height, unit),
        age: values.age,
        sex: values.sex,
        activityLevel: values.activityLevel,
      });
      onResult(data);
    },
  });

  return (
    <form
      onSubmit={handleSubmit((values) => mutation.mutate(values))}
      noValidate
      className="flex flex-col gap-4 rounded-xl border p-6"
    >
      <h2 className="text-xl font-semibold">{t('bmiForm.title')}</h2>

      <div className="flex flex-col gap-1.5">
        <Label htmlFor="weight">
          {t('bmiForm.weightLabel')} ({weightSuffix})
        </Label>
        <Input
          id="weight"
          type="number"
          step="0.1"
          aria-invalid={Boolean(errors.weight)}
          {...register('weight')}
        />
        {errors.weight && (
          <p className="text-sm text-destructive">
            {t('bmiForm.errors.weight', { ...weightBounds, unit: weightSuffix })}
          </p>
        )}
      </div>

      <div className="flex flex-col gap-1.5">
        <Label htmlFor="height">
          {t('bmiForm.heightLabel')} ({heightSuffix})
        </Label>
        <Input
          id="height"
          type="number"
          step="0.1"
          aria-invalid={Boolean(errors.height)}
          {...register('height')}
        />
        {errors.height && (
          <p className="text-sm text-destructive">
            {t('bmiForm.errors.height', { ...heightBounds, unit: heightSuffix })}
          </p>
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
        <p className="text-sm text-destructive">{t('bmiForm.errors.generic')}</p>
      )}

      <Button type="submit" disabled={mutation.isPending}>
        {mutation.isPending ? t('bmiForm.submitting') : t('bmiForm.submit')}
      </Button>
    </form>
  );
}
