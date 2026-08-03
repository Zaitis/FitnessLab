import { useState } from 'react';
import { useTranslation } from 'react-i18next';
import { BmiForm } from '@/components/BmiForm';
import { BmiResult } from '@/components/BmiResult';
import type { BmiCalculation } from '@/lib/bmi';

export function LandingPage() {
  const { t } = useTranslation();
  const [result, setResult] = useState<BmiCalculation | null>(null);

  return (
    <div className="flex flex-col gap-10">
      <section className="flex flex-col gap-3 text-center">
        <h1 className="text-3xl font-bold sm:text-4xl">{t('landing.heroTitle')}</h1>
        <p className="text-lg text-muted-foreground">{t('landing.heroSubtitle')}</p>
      </section>

      <div className="mx-auto flex w-full max-w-md flex-col gap-6">
        <BmiForm onResult={setResult} />
        {result && <BmiResult result={result} />}
      </div>
    </div>
  );
}
