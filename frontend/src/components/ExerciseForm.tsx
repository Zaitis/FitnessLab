import { zodResolver } from '@hookform/resolvers/zod';
import { useForm } from 'react-hook-form';
import { useTranslation } from 'react-i18next';
import { z } from 'zod';
import { LocalizedFieldTabs } from '@/components/LocalizedFieldTabs';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { supportedLocales } from '@/i18n';
import type { Exercise, ExerciseInput } from '@/hooks/useAdminExercises';

const localizedText = Object.fromEntries(
  supportedLocales.map((locale) => [locale, z.string().min(1)]),
);

const baseSchema = z.object({
  location: z.enum(['gym', 'home', 'outdoor']),
  difficulty: z.enum(['beginner', 'intermediate', 'advanced']),
  name: z.object(localizedText),
  instructions: z.object(localizedText),
});

const strengthSchema = baseSchema.extend({
  type: z.literal('strength'),
  muscle_group: z.enum(['chest', 'back', 'legs', 'shoulders', 'arms', 'core']),
  sets: z.coerce.number().int().min(1).max(20),
  reps: z.coerce.number().int().min(1).max(100),
});

const cardioSchema = baseSchema.extend({
  type: z.literal('cardio'),
  duration_minutes: z.coerce.number().int().min(1).max(180),
});

const schema = z.discriminatedUnion('type', [strengthSchema, cardioSchema]);

type FormInput = z.input<typeof schema>;
type FormValues = z.output<typeof schema>;

function toFormValues(exercise?: Exercise): Partial<FormInput> {
  if (!exercise) {
    return {
      type: 'strength',
      location: 'gym',
      difficulty: 'beginner',
      muscle_group: 'chest',
      sets: 3,
      reps: 10,
      name: Object.fromEntries(supportedLocales.map((locale) => [locale, ''])),
      instructions: Object.fromEntries(supportedLocales.map((locale) => [locale, ''])),
    };
  }

  return exercise as unknown as FormInput;
}

interface ExerciseFormProps {
  exercise?: Exercise;
  onSubmit: (values: ExerciseInput) => void;
  onCancel: () => void;
  isSubmitting: boolean;
  isError: boolean;
}

export function ExerciseForm({
  exercise,
  onSubmit,
  onCancel,
  isSubmitting,
  isError,
}: ExerciseFormProps) {
  const { t } = useTranslation();
  const {
    register,
    watch,
    handleSubmit,
    formState: { errors },
  } = useForm<FormInput, unknown, FormValues>({
    // The strength/cardio split is enforced by the discriminated union above,
    // not by ad-hoc `sometimes`-style conditionals — mirrors the backend's
    // ExerciseRequest rather than diverging from it.
    resolver: zodResolver(schema),
    defaultValues: toFormValues(exercise),
  });

  const type = watch('type');

  function submit(values: FormValues) {
    // The discriminated union above omits the fields that don't apply to
    // the chosen type entirely (e.g. cardio has no `sets`) — the API
    // expects every column present, with the inapplicable ones explicit
    // nulls, matching how ExerciseRequest's own prohibited_if reads them.
    const payload: ExerciseInput =
      values.type === 'strength'
        ? { ...values, duration_minutes: null }
        : { ...values, muscle_group: null, sets: null, reps: null };

    onSubmit(payload);
  }

  return (
    <form
      onSubmit={handleSubmit(submit)}
      noValidate
      className="flex flex-col gap-4 rounded-xl border p-6"
    >
      <h2 className="text-xl font-semibold">
        {exercise ? t('admin.exercises.form.titleEdit') : t('admin.exercises.form.titleCreate')}
      </h2>

      <div className="flex flex-col gap-1.5">
        <Label htmlFor="type">{t('admin.exercises.form.typeLabel')}</Label>
        <select
          id="type"
          className="rounded-md border border-input bg-transparent px-2 py-1.5 text-sm"
          {...register('type')}
        >
          <option value="strength">{t('admin.exercises.types.strength')}</option>
          <option value="cardio">{t('admin.exercises.types.cardio')}</option>
        </select>
      </div>

      <div className="flex flex-col gap-1.5">
        <Label htmlFor="location">{t('admin.exercises.form.locationLabel')}</Label>
        <select
          id="location"
          className="rounded-md border border-input bg-transparent px-2 py-1.5 text-sm"
          {...register('location')}
        >
          <option value="gym">{t('admin.exercises.locations.gym')}</option>
          <option value="home">{t('admin.exercises.locations.home')}</option>
          <option value="outdoor">{t('admin.exercises.locations.outdoor')}</option>
        </select>
      </div>

      <div className="flex flex-col gap-1.5">
        <Label htmlFor="difficulty">{t('admin.exercises.form.difficultyLabel')}</Label>
        <select
          id="difficulty"
          className="rounded-md border border-input bg-transparent px-2 py-1.5 text-sm"
          {...register('difficulty')}
        >
          <option value="beginner">{t('workoutPlan.experienceLevels.beginner')}</option>
          <option value="intermediate">{t('workoutPlan.experienceLevels.intermediate')}</option>
          <option value="advanced">{t('workoutPlan.experienceLevels.advanced')}</option>
        </select>
      </div>

      {type === 'strength' && (
        <>
          <div className="flex flex-col gap-1.5">
            <Label htmlFor="muscle_group">{t('admin.exercises.form.muscleGroupLabel')}</Label>
            <select
              id="muscle_group"
              className="rounded-md border border-input bg-transparent px-2 py-1.5 text-sm"
              {...register('muscle_group')}
            >
              {(['chest', 'back', 'legs', 'shoulders', 'arms', 'core'] as const).map((group) => (
                <option key={group} value={group}>
                  {t(`admin.exercises.muscleGroups.${group}`)}
                </option>
              ))}
            </select>
          </div>

          <div className="grid grid-cols-2 gap-4">
            <div className="flex flex-col gap-1.5">
              <Label htmlFor="sets">{t('admin.exercises.form.setsLabel')}</Label>
              <Input id="sets" type="number" min={1} max={20} {...register('sets')} />
            </div>
            <div className="flex flex-col gap-1.5">
              <Label htmlFor="reps">{t('admin.exercises.form.repsLabel')}</Label>
              <Input id="reps" type="number" min={1} max={100} {...register('reps')} />
            </div>
          </div>
        </>
      )}

      {type === 'cardio' && (
        <div className="flex flex-col gap-1.5">
          <Label htmlFor="duration_minutes">{t('admin.exercises.form.durationLabel')}</Label>
          <Input
            id="duration_minutes"
            type="number"
            min={1}
            max={180}
            {...register('duration_minutes')}
          />
        </div>
      )}

      <LocalizedFieldTabs
        locales={supportedLocales}
        renderFields={(locale) => (
          <>
            <div className="flex flex-col gap-1.5">
              <Label htmlFor={`name-${locale}`}>
                {t('admin.exercises.form.nameLabel')} ({locale.toUpperCase()})
              </Label>
              <Input
                id={`name-${locale}`}
                aria-invalid={Boolean(errors.name?.[locale as keyof typeof errors.name])}
                {...register(`name.${locale}`)}
              />
            </div>
            <div className="flex flex-col gap-1.5">
              <Label htmlFor={`instructions-${locale}`}>
                {t('admin.exercises.form.instructionsLabel')} ({locale.toUpperCase()})
              </Label>
              <Input
                id={`instructions-${locale}`}
                aria-invalid={Boolean(
                  errors.instructions?.[locale as keyof typeof errors.instructions],
                )}
                {...register(`instructions.${locale}`)}
              />
            </div>
          </>
        )}
      />

      {isError && (
        <p className="text-sm text-destructive">{t('admin.exercises.form.errors.generic')}</p>
      )}

      <div className="flex gap-2">
        <Button type="submit" disabled={isSubmitting}>
          {isSubmitting ? t('admin.exercises.form.submitting') : t('admin.exercises.form.submit')}
        </Button>
        <Button type="button" variant="outline" onClick={onCancel}>
          {t('admin.exercises.cancel')}
        </Button>
      </div>
    </form>
  );
}
