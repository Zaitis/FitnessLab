import { zodResolver } from '@hookform/resolvers/zod';
import { useForm } from 'react-hook-form';
import { useTranslation } from 'react-i18next';
import { Link, useNavigate } from 'react-router-dom';
import { z } from 'zod';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useLogin } from '@/hooks/useAuth';
import { applyServerErrors } from '@/lib/formErrors';

const schema = z.object({
  email: z.string().email(),
  password: z.string().min(1),
});

type FormValues = z.infer<typeof schema>;

export function LoginPage() {
  const { t } = useTranslation();
  const navigate = useNavigate();
  const loginMutation = useLogin();
  const {
    register,
    handleSubmit,
    setError,
    formState: { errors },
  } = useForm<FormValues>({ resolver: zodResolver(schema) });

  async function onSubmit(values: FormValues) {
    try {
      await loginMutation.mutateAsync(values);
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
      <h1 className="text-xl font-semibold">{t('auth.loginTitle')}</h1>

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

      <Button type="submit" disabled={loginMutation.isPending}>
        {loginMutation.isPending ? t('auth.submitting') : t('auth.loginSubmit')}
      </Button>

      <p className="text-sm text-muted-foreground">
        {t('auth.noAccount')}{' '}
        <Link to="/register" className="underline underline-offset-2">
          {t('auth.registerLink')}
        </Link>
      </p>
    </form>
  );
}
