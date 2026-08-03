import { useTranslation } from 'react-i18next';
import { Link } from 'react-router-dom';

export function NotFoundPage() {
  const { t } = useTranslation();

  return (
    <div className="flex flex-col items-center gap-4 py-16 text-center">
      <h1 className="text-2xl font-bold">{t('notFound.title')}</h1>
      <p className="text-muted-foreground">{t('notFound.message')}</p>
      <Link to="/" className="underline underline-offset-2">
        {t('notFound.backLink')}
      </Link>
    </div>
  );
}
