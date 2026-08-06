import { zodResolver } from '@hookform/resolvers/zod';
import { useForm } from 'react-hook-form';
import { useTranslation } from 'react-i18next';
import { z } from 'zod';
import { LocalizedFieldTabs } from '@/components/LocalizedFieldTabs';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { supportedLocales } from '@/i18n';
import type { MealTemplate, MealTemplateInput } from '@/hooks/useAdminMealTemplates';

const localizedText = Object.fromEntries(
  supportedLocales.map((locale) => [locale, z.string().min(1)]),
);

const schema = z.object({
  meal_time: z.enum(['breakfast', 'second_breakfast', 'lunch', 'afternoon_snack', 'dinner']),
  calories: z.coerce.number().int().min(0).max(5000),
  protein_g: z.coerce.number().int().min(0).max(500),
  fat_g: z.coerce.number().int().min(0).max(500),
  carbs_g: z.coerce.number().int().min(0).max(500),
  name: z.object(localizedText),
  description: z.object(localizedText),
});

type FormInput = z.input<typeof schema>;
type FormValues = z.output<typeof schema>;

function toFormValues(mealTemplate?: MealTemplate): Partial<FormInput> {
  if (!mealTemplate) {
    return {
      meal_time: 'breakfast',
      calories: 400,
      protein_g: 20,
      fat_g: 15,
      carbs_g: 45,
      name: Object.fromEntries(supportedLocales.map((locale) => [locale, ''])),
      description: Object.fromEntries(supportedLocales.map((locale) => [locale, ''])),
    };
  }

  return mealTemplate as unknown as FormInput;
}

interface MealTemplateFormProps {
  mealTemplate?: MealTemplate;
  onSubmit: (values: MealTemplateInput) => void;
  onCancel: () => void;
  isSubmitting: boolean;
  isError: boolean;
}

export function MealTemplateForm({
  mealTemplate,
  onSubmit,
  onCancel,
  isSubmitting,
  isError,
}: MealTemplateFormProps) {
  const { t } = useTranslation();
  const {
    register,
    handleSubmit,
    formState: { errors },
  } = useForm<FormInput, unknown, FormValues>({
    resolver: zodResolver(schema),
    defaultValues: toFormValues(mealTemplate),
  });

  return (
    <form
      onSubmit={handleSubmit((values) => onSubmit(values))}
      noValidate
      className="flex flex-col gap-4 rounded-xl border p-6"
    >
      <h2 className="text-xl font-semibold">
        {mealTemplate
          ? t('admin.mealTemplates.form.titleEdit')
          : t('admin.mealTemplates.form.titleCreate')}
      </h2>

      <div className="flex flex-col gap-1.5">
        <Label htmlFor="meal_time">{t('admin.mealTemplates.form.mealTimeLabel')}</Label>
        <select
          id="meal_time"
          className="rounded-md border border-input bg-transparent px-2 py-1.5 text-sm"
          {...register('meal_time')}
        >
          {(['breakfast', 'second_breakfast', 'lunch', 'afternoon_snack', 'dinner'] as const).map(
            (mealTime) => (
              <option key={mealTime} value={mealTime}>
                {t(`nutritionPlan.mealTime.${mealTime}`)}
              </option>
            ),
          )}
        </select>
      </div>

      <div className="flex flex-col gap-1.5">
        <Label htmlFor="calories">{t('admin.mealTemplates.form.caloriesLabel')}</Label>
        <Input id="calories" type="number" min={0} max={5000} {...register('calories')} />
      </div>

      <div className="grid grid-cols-3 gap-4">
        <div className="flex flex-col gap-1.5">
          <Label htmlFor="protein_g">{t('admin.mealTemplates.form.proteinLabel')}</Label>
          <Input id="protein_g" type="number" min={0} max={500} {...register('protein_g')} />
        </div>
        <div className="flex flex-col gap-1.5">
          <Label htmlFor="fat_g">{t('admin.mealTemplates.form.fatLabel')}</Label>
          <Input id="fat_g" type="number" min={0} max={500} {...register('fat_g')} />
        </div>
        <div className="flex flex-col gap-1.5">
          <Label htmlFor="carbs_g">{t('admin.mealTemplates.form.carbsLabel')}</Label>
          <Input id="carbs_g" type="number" min={0} max={500} {...register('carbs_g')} />
        </div>
      </div>

      <LocalizedFieldTabs
        locales={supportedLocales}
        renderFields={(locale) => (
          <>
            <div className="flex flex-col gap-1.5">
              <Label htmlFor={`name-${locale}`}>
                {t('admin.mealTemplates.form.nameLabel')} ({locale.toUpperCase()})
              </Label>
              <Input
                id={`name-${locale}`}
                aria-invalid={Boolean(errors.name?.[locale as keyof typeof errors.name])}
                {...register(`name.${locale}`)}
              />
            </div>
            <div className="flex flex-col gap-1.5">
              <Label htmlFor={`description-${locale}`}>
                {t('admin.mealTemplates.form.descriptionLabel')} ({locale.toUpperCase()})
              </Label>
              <Input
                id={`description-${locale}`}
                aria-invalid={Boolean(
                  errors.description?.[locale as keyof typeof errors.description],
                )}
                {...register(`description.${locale}`)}
              />
            </div>
          </>
        )}
      />

      {isError && (
        <p className="text-sm text-destructive">{t('admin.mealTemplates.form.errors.generic')}</p>
      )}

      <div className="flex gap-2">
        <Button type="submit" disabled={isSubmitting}>
          {isSubmitting
            ? t('admin.mealTemplates.form.submitting')
            : t('admin.mealTemplates.form.submit')}
        </Button>
        <Button type="button" variant="outline" onClick={onCancel}>
          {t('admin.mealTemplates.cancel')}
        </Button>
      </div>
    </form>
  );
}
