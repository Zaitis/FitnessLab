import { useState } from 'react';
import { useTranslation } from 'react-i18next';
import { useNavigate } from 'react-router';
import { BmiForm } from '@/components/BmiForm';
import { BmiResult } from '@/components/BmiResult';
import { Button } from '@/components/ui/button';
import { useLogin } from '@/hooks/useAuth';
import type { BmiCalculation } from '@/lib/bmi';

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
    <div className="flex flex-col gap-10">
      <section className="flex flex-col items-center gap-4 text-center">
        <h1 className="text-3xl font-bold sm:text-4xl">{t('landing.heroTitle')}</h1>
        <p className="text-lg text-muted-foreground">{t('landing.heroSubtitle')}</p>

        <div className="flex flex-col items-center gap-2">
          <Button
            type="button"
            variant="outline"
            onClick={handleTryDemo}
            disabled={demoLogin.isPending}
          >
            {demoLogin.isPending ? t('landing.tryDemoPending') : t('landing.tryDemo')}
          </Button>
          {demoFailed && <p className="text-sm text-destructive">{t('landing.tryDemoError')}</p>}
        </div>
      </section>

      <div className="mx-auto flex w-full max-w-md flex-col gap-6">
        <BmiForm onResult={setResult} />
        {result && <BmiResult result={result} />}
      </div>
    </div>
  );
}
