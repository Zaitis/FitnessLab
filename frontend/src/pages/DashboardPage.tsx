import { useTranslation } from 'react-i18next';
import { useUser } from '@/hooks/useAuth';

export function DashboardPage() {
  const { t } = useTranslation();
  const { data: user } = useUser();

  return (
    <div className="flex flex-col gap-2">
      <h1 className="text-2xl font-bold">{t('dashboard.welcome', { name: user?.name ?? '' })}</h1>
      <p className="text-muted-foreground">{t('dashboard.placeholder')}</p>
    </div>
  );
}
