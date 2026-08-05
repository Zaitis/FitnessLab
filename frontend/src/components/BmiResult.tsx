import { useTranslation } from 'react-i18next';
import { Link } from 'react-router';
import { buttonVariants } from '@/components/ui/button';
import type { BmiCalculation } from '@/lib/bmi';

interface BmiResultProps {
  result: BmiCalculation;
}

export function BmiResult({ result }: BmiResultProps) {
  const { t } = useTranslation();

  return (
    <section className="flex flex-col gap-3 rounded-xl border p-6" aria-live="polite">
      <h2 className="text-xl font-semibold">{t('bmiResult.title')}</h2>
      <p>
        <span className="text-muted-foreground">{t('bmiResult.valueLabel')}: </span>
        <span className="text-lg font-semibold">{result.value}</span>
      </p>
      <p>
        <span className="text-muted-foreground">{t('bmiResult.categoryLabel')}: </span>
        <span className="text-lg font-semibold">
          {t(`bmiResult.categories.${result.category}`)}
        </span>
      </p>
      <Link
        to="/register"
        className={buttonVariants({
          variant: 'default',
          className: '!h-auto !whitespace-normal py-2',
        })}
      >
        {t('bmiResult.cta')}
      </Link>
    </section>
  );
}
