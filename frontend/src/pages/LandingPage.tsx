import { useState } from 'react';
import { useTranslation } from 'react-i18next';
import { Link, useNavigate } from 'react-router';
import { BmiForm } from '@/components/BmiForm';
import { BmiResult } from '@/components/BmiResult';
import { Button, buttonVariants } from '@/components/ui/button';
import { useLogin } from '@/hooks/useAuth';
import type { BmiCalculation } from '@/lib/bmi';

const FEATURES = [
  { key: 'bmi', blobClass: 'blob bg-accent' },
  { key: 'training', blobClass: 'blob-alt bg-[oklch(93%_0.07_35)]' },
  { key: 'meals', blobClass: 'blob bg-[oklch(90%_0.1_95)]' },
] as const;

/**
 * Public credentials for the shared demo account — published in the README
 * and matched exactly by the backend's config/demo.php. Not a secret: this
 * is the "Try the demo account" flow, not a real login form.
 */
const DEMO_CREDENTIALS = {
  email: 'demo@fitnesslab.zaitis.dev',
  password: 'FitnessLabDemo!2026',
};

export function LandingPage() {
  const { t } = useTranslation();
  const [result, setResult] = useState<BmiCalculation | null>(null);
  const navigate = useNavigate();
  const demoLogin = useLogin();
  const [demoFailed, setDemoFailed] = useState(false);

  async function handleTryDemo() {
    setDemoFailed(false);

    try {
      await demoLogin.mutateAsync(DEMO_CREDENTIALS);
      navigate('/dashboard');
    } catch {
      setDemoFailed(true);
    }
  }

  return (
    <div className="flex flex-col gap-16">
      <section className="relative overflow-hidden pt-2 pb-4 sm:pt-6">
        <div
          aria-hidden
          className="blob animate-float-a absolute top-0 -left-8 -z-10 size-40 bg-accent opacity-60 sm:size-48"
        />
        <div
          aria-hidden
          className="blob-alt animate-float-b absolute top-24 right-[6%] -z-10 size-32 bg-[oklch(88%_0.09_35)] opacity-60 sm:size-36"
        />

        <div className="relative z-10 grid gap-10 lg:grid-cols-[1.1fr_1fr] lg:items-center">
          <div className="flex flex-col items-start gap-5">
            <h1 className="text-4xl sm:text-5xl">{t('landing.heroTitle')}</h1>
            <p className="text-lg text-muted-foreground">{t('landing.heroSubtitle')}</p>

            <div className="flex flex-wrap gap-3">
              <Link to="/register" className={buttonVariants({ size: 'lg' })}>
                {t('landing.heroCreateAccount')}
              </Link>
              <a href="#calc" className={buttonVariants({ variant: 'outline', size: 'lg' })}>
                {t('landing.heroTryCalculator')}
              </a>
            </div>

            <div className="flex flex-col items-start gap-2">
              <Button
                type="button"
                variant="ghost"
                onClick={handleTryDemo}
                disabled={demoLogin.isPending}
              >
                {demoLogin.isPending ? t('landing.tryDemoPending') : t('landing.tryDemo')}
              </Button>
              {demoFailed && (
                <p className="text-sm text-destructive">{t('landing.tryDemoError')}</p>
              )}
            </div>
          </div>

          <div className="flex justify-center" aria-hidden>
            <div className="w-64 rounded-[38px] bg-foreground p-3.5 shadow-2xl">
              <div className="flex flex-col gap-4 rounded-3xl bg-card p-5">
                <div className="flex items-center gap-2">
                  <div className="blob size-5 bg-gradient-to-br from-primary to-[oklch(72%_0.16_100)]" />
                  <span className="font-heading text-sm font-extrabold">{t('app.name')}</span>
                </div>

                <div className="flex items-center justify-center">
                  <div
                    className="flex size-28 items-center justify-center rounded-full"
                    style={{
                      background: 'conic-gradient(var(--primary) 0% 71%, var(--muted) 71% 100%)',
                    }}
                  >
                    <div className="flex size-[86px] flex-col items-center justify-center rounded-full bg-card">
                      <span className="font-heading text-xl font-extrabold">23.4</span>
                      <span className="text-[10px] text-muted-foreground">BMI</span>
                    </div>
                  </div>
                </div>

                <div className="flex h-11 items-end gap-1.5">
                  <div className="h-[60%] flex-1 rounded bg-accent" />
                  <div className="h-[75%] flex-1 rounded bg-accent" />
                  <div className="h-[50%] flex-1 rounded bg-accent" />
                  <div className="h-[90%] flex-1 rounded bg-primary" />
                </div>

                <div className="rounded-xl bg-primary py-2.5 text-center text-xs font-bold text-primary-foreground">
                  {t('landing.heroMockupCta')}
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <div id="calc" className="mx-auto flex w-full max-w-md scroll-mt-20 flex-col gap-6">
        <BmiForm onResult={setResult} />
        {result && <BmiResult result={result} />}
      </div>

      <section className="grid gap-6 sm:grid-cols-3">
        {FEATURES.map(({ key, blobClass }) => (
          <div
            key={key}
            className="rounded-2xl border bg-card p-7 transition-transform hover:-translate-y-1 hover:shadow-lg"
          >
            <div className={`size-11 ${blobClass}`} aria-hidden />
            <h3 className="mt-4 mb-2 text-lg">{t(`landing.features.${key}.title`)}</h3>
            <p className="text-sm text-muted-foreground">
              {t(`landing.features.${key}.description`)}
            </p>
          </div>
        ))}
      </section>
    </div>
  );
}
