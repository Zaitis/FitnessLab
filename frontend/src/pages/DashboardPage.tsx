import { useTranslation } from 'react-i18next';
import { useSearchParams } from 'react-router-dom';
import { useUser } from '@/hooks/useAuth';

export function DashboardPage() {
  const { t } = useTranslation();
  const { data: user } = useUser();
  const [searchParams] = useSearchParams();

  return (
    <div className="flex flex-col gap-4">
      {searchParams.get('verified') === '1' && (
        <p className="rounded-md bg-primary/10 px-4 py-2 text-sm font-medium text-primary">
          {t('dashboard.emailVerified')}
        </p>
      )}
      <div className="flex flex-col gap-2">
        <h1 className="text-2xl font-bold">{t('dashboard.welcome', { name: user?.name ?? '' })}</h1>
        <p className="text-muted-foreground">{t('dashboard.placeholder')}</p>
      </div>
    </div>
  );
}
