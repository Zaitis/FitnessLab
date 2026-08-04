import { useState } from 'react';
import { zodResolver } from '@hookform/resolvers/zod';
import { useForm } from 'react-hook-form';
import { useTranslation } from 'react-i18next';
import { Link, useNavigate, useParams, useSearchParams } from 'react-router';
import { z } from 'zod';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useResetPassword } from '@/hooks/useAuth';
import { ApiError } from '@/lib/api';
import { applyServerErrors } from '@/lib/formErrors';

const schema = z
  .object({
    password: z.string().min(8),
    password_confirmation: z.string(),
  })
  .refine((values) => values.password === values.password_confirmation, {
    path: ['password_confirmation'],
    message: 'passwordMismatch',
  });

type FormValues = z.infer<typeof schema>;

export function ResetPasswordPage() {
  const { t } = useTranslation();
  const navigate = useNavigate();
  const { token } = useParams<{ token: string }>();
  const [searchParams] = useSearchParams();
  const email = searchParams.get('email') ?? '';
  const resetPassword = useResetPassword();
  const [linkError, setLinkError] = useState<string | null>(null);

  const {
    register,
    handleSubmit,
    setError,
    formState: { errors },
  } = useForm<FormValues>({ resolver: zodResolver(schema) });

  async function onSubmit(values: FormValues) {
    setLinkError(null);

    try {
      await resetPassword.mutateAsync({
        token: token ?? '',
        email,
        password: values.password,
        password_confirmation: values.password_confirmation,
      });

      navigate('/login', { replace: true });
    } catch (error) {
      // Laravel reports an expired or already-used token as a validation
      // error on `email` — a field this form never shows, because it comes
      // from the link rather than the user. Surfacing it inline would attach
      // the message to nothing, so it becomes a banner instead.
      if (error instanceof ApiError && error.errors?.email) {
        setLinkError(error.errors.email[0]);

        return;
      }

      applyServerErrors(setError, error);
    }
  }

  if (!token || !email) {
    return (
      <div className="mx-auto flex w-full max-w-md flex-col gap-4 rounded-xl border p-6">
        <h1 className="text-xl font-semibold">{t('auth.resetPasswordTitle')}</h1>
        <p className="text-sm text-destructive">{t('auth.resetPasswordInvalidLink')}</p>
        <Link to="/forgot-password" className="text-sm underline underline-offset-2">
          {t('auth.forgotPasswordSubmit')}
        </Link>
      </div>
    );
  }

  return (
    <form
      onSubmit={handleSubmit(onSubmit)}
      noValidate
      className="mx-auto flex w-full max-w-md flex-col gap-4 rounded-xl border p-6"
    >
      <h1 className="text-xl font-semibold">{t('auth.resetPasswordTitle')}</h1>
      <p className="text-sm text-muted-foreground">{t('auth.resetPasswordIntro', { email })}</p>

      {linkError && (
        <div className="flex flex-col gap-2 rounded-lg border border-destructive/50 p-3">
          <p className="text-sm text-destructive">{linkError}</p>
          <Link to="/forgot-password" className="text-sm underline underline-offset-2">
            {t('auth.forgotPasswordSubmit')}
          </Link>
        </div>
      )}

      <div className="flex flex-col gap-1.5">
        <Label htmlFor="password">{t('auth.newPasswordLabel')}</Label>
        <Input
          id="password"
          type="password"
          autoComplete="new-password"
          aria-invalid={Boolean(errors.password)}
          {...register('password')}
        />
        {errors.password && (
          <p className="text-sm text-destructive">{t('auth.errors.passwordTooShort')}</p>
        )}
      </div>

      <div className="flex flex-col gap-1.5">
        <Label htmlFor="password_confirmation">{t('auth.passwordConfirmationLabel')}</Label>
        <Input
          id="password_confirmation"
          type="password"
          autoComplete="new-password"
          aria-invalid={Boolean(errors.password_confirmation)}
          {...register('password_confirmation')}
        />
        {errors.password_confirmation && (
          <p className="text-sm text-destructive">{t('auth.errors.passwordMismatch')}</p>
        )}
      </div>

      <Button type="submit" disabled={resetPassword.isPending}>
        {resetPassword.isPending ? t('auth.submitting') : t('auth.resetPasswordSubmit')}
      </Button>
    </form>
  );
}
