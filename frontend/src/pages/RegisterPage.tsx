import { zodResolver } from '@hookform/resolvers/zod';
import { useForm } from 'react-hook-form';
import { useTranslation } from 'react-i18next';
import { Link, useNavigate } from 'react-router';
import { z } from 'zod';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useRegister } from '@/hooks/useAuth';
import { apiFetch } from '@/lib/api';
import { applyServerErrors } from '@/lib/formErrors';
import { clearPendingMeasurement, readPendingMeasurement } from '@/lib/pendingMeasurement';

const schema = z
  .object({
    name: z.string().min(1).max(255),
    email: z.string().email(),
    password: z.string().min(8),
    passwordConfirmation: z.string(),
  })
  .refine((data) => data.password === data.passwordConfirmation, {
    message: 'passwords-must-match',
    path: ['passwordConfirmation'],
  });

type FormValues = z.infer<typeof schema>;

async function submitPendingMeasurement(): Promise<void> {
  const pending = readPendingMeasurement();

  if (!pending) {
    return;
  }

  try {
    await apiFetch('/measurements', {
      method: 'POST',
      body: JSON.stringify({
        weight_kg: pending.weightKg,
        height_cm: pending.heightCm,
        age: pending.age,
        sex: pending.sex,
        activity_level: pending.activityLevel,
      }),
    });
  } catch {
    // Best-effort carry-over — registration itself already succeeded.
  } finally {
    clearPendingMeasurement();
  }
}

export function RegisterPage() {
  const { t } = useTranslation();
  const navigate = useNavigate();
  const registerMutation = useRegister();
  const {
    register,
    handleSubmit,
    setError,
    formState: { errors },
  } = useForm<FormValues>({ resolver: zodResolver(schema) });

  async function onSubmit(values: FormValues) {
    try {
      await registerMutation.mutateAsync({
        name: values.name,
        email: values.email,
        password: values.password,
        password_confirmation: values.passwordConfirmation,
      });

      await submitPendingMeasurement();
      navigate('/dashboard');
    } catch (error) {
      applyServerErrors(setError, error);
    }
  }

  return (
    <form
      onSubmit={handleSubmit(onSubmit)}
      noValidate
      className="mx-auto flex w-full max-w-md flex-col gap-4 rounded-xl border p-6"
    >
      <h1 className="text-xl font-semibold">{t('auth.registerTitle')}</h1>

      <div className="flex flex-col gap-1.5">
        <Label htmlFor="name">{t('auth.nameLabel')}</Label>
        <Input id="name" aria-invalid={Boolean(errors.name)} {...register('name')} />
        {errors.name && <p className="text-sm text-destructive">{errors.name.message}</p>}
      </div>

      <div className="flex flex-col gap-1.5">
        <Label htmlFor="email">{t('auth.emailLabel')}</Label>
        <Input
          id="email"
          type="email"
          aria-invalid={Boolean(errors.email)}
          {...register('email')}
        />
        {errors.email && <p className="text-sm text-destructive">{errors.email.message}</p>}
      </div>

      <div className="flex flex-col gap-1.5">
        <Label htmlFor="password">{t('auth.passwordLabel')}</Label>
        <Input
          id="password"
          type="password"
          aria-invalid={Boolean(errors.password)}
          {...register('password')}
        />
        {errors.password && <p className="text-sm text-destructive">{errors.password.message}</p>}
      </div>

      <div className="flex flex-col gap-1.5">
        <Label htmlFor="passwordConfirmation">{t('auth.passwordConfirmationLabel')}</Label>
        <Input
          id="passwordConfirmation"
          type="password"
          aria-invalid={Boolean(errors.passwordConfirmation)}
          {...register('passwordConfirmation')}
        />
        {errors.passwordConfirmation && (
          <p className="text-sm text-destructive">{t('auth.errors.passwordMismatch')}</p>
        )}
      </div>

      <Button type="submit" disabled={registerMutation.isPending}>
        {registerMutation.isPending ? t('auth.submitting') : t('auth.registerSubmit')}
      </Button>

      <p className="text-sm text-muted-foreground">
        {t('auth.hasAccount')}{' '}
        <Link to="/login" className="underline underline-offset-2">
          {t('auth.loginLink')}
        </Link>
      </p>
    </form>
  );
}
