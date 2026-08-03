import { useTranslation } from 'react-i18next';

export function AdherencePage() {
  const { t } = useTranslation();

  return <p className="text-muted-foreground">{t('comingSoon.adherence')}</p>;
}
