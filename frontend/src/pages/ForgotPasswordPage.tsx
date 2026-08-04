import { zodResolver } from '@hookform/resolvers/zod';
import { useForm } from 'react-hook-form';
import { useTranslation } from 'react-i18next';
import { Link } from 'react-router';
import { z } from 'zod';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useForgotPassword } from '@/hooks/useAuth';
import { applyServerErrors } from '@/lib/formErrors';

const schema = z.object({
  email: z.string().email(),
});

type FormValues = z.infer<typeof schema>;

export function ForgotPasswordPage() {
  const { t } = useTranslation();
  const forgotPassword = useForgotPassword();
  const {
    register,
    handleSubmit,
    setError,
    formState: { errors },
  } = useForm<FormValues>({ resolver: zodResolver(schema) });

  async function onSubmit(values: FormValues) {
    try {
      await forgotPassword.mutateAsync(values.email);
    } catch (error) {
      applyServerErrors(setError, error);
    }
  }

  // The API deliberately answers identically for a known and an unknown
  // address (see docs/ARCHITECTURE.md), so this copy must stay non-committal
  // too — saying "we sent you an email" would re-open the enumeration hole
  // the endpoint was changed to close.
  if (forgotPassword.isSuccess) {
    return (
      <div className="mx-auto flex w-full max-w-md flex-col gap-4 rounded-xl border p-6">
        <h1 className="text-xl font-semibold">{t('auth.forgotPasswordTitle')}</h1>
        <p className="text-sm text-muted-foreground">{t('auth.forgotPasswordSent')}</p>
        <Link to="/login" className="text-sm underline underline-offset-2">
          {t('auth.backToLogin')}
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
      <h1 className="text-xl font-semibold">{t('auth.forgotPasswordTitle')}</h1>
      <p className="text-sm text-muted-foreground">{t('auth.forgotPasswordIntro')}</p>

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

      <Button type="submit" disabled={forgotPassword.isPending}>
        {forgotPassword.isPending ? t('auth.submitting') : t('auth.forgotPasswordSubmit')}
      </Button>

      <Link to="/login" className="text-sm underline underline-offset-2">
        {t('auth.backToLogin')}
      </Link>
    </form>
  );
}
