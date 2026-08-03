import { zodResolver } from '@hookform/resolvers/zod';
import { useMutation } from '@tanstack/react-query';
import { useForm } from 'react-hook-form';
import { useTranslation } from 'react-i18next';
import { z } from 'zod';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { apiFetch } from '@/lib/api';
import type { BmiCalculation } from '@/lib/bmi';
import { savePendingMeasurement } from '@/lib/pendingMeasurement';

const schema = z.object({
  weightKg: z.coerce.number().min(1).max(500),
  heightCm: z.coerce.number().min(30).max(250),
});

type FormValues = z.infer<typeof schema>;

interface BmiFormProps {
  onResult: (result: BmiCalculation) => void;
}

export function BmiForm({ onResult }: BmiFormProps) {
  const { t } = useTranslation();
  const {
    register,
    handleSubmit,
    formState: { errors },
  } = useForm<FormValues>({ resolver: zodResolver(schema) });

  const mutation = useMutation({
    mutationFn: (values: FormValues) =>
      apiFetch<BmiCalculation>('/bmi/calculate', {
        method: 'POST',
        body: JSON.stringify({ weight_kg: values.weightKg, height_cm: values.heightCm }),
      }),
    onSuccess: (data, values) => {
      savePendingMeasurement({ weightKg: values.weightKg, heightCm: values.heightCm });
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
        <Label htmlFor="weightKg">{t('bmiForm.weightLabel')}</Label>
        <Input
          id="weightKg"
          type="number"
          step="0.1"
          aria-invalid={Boolean(errors.weightKg)}
          {...register('weightKg')}
        />
        {errors.weightKg && (
          <p className="text-sm text-destructive">{t('bmiForm.errors.weight')}</p>
        )}
      </div>

      <div className="flex flex-col gap-1.5">
        <Label htmlFor="heightCm">{t('bmiForm.heightLabel')}</Label>
        <Input
          id="heightCm"
          type="number"
          step="0.1"
          aria-invalid={Boolean(errors.heightCm)}
          {...register('heightCm')}
        />
        {errors.heightCm && (
          <p className="text-sm text-destructive">{t('bmiForm.errors.height')}</p>
        )}
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
