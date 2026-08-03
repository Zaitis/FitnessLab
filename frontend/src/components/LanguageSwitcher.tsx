import { useTranslation } from 'react-i18next';
import { LOCALE_STORAGE_KEY, supportedLocales } from '@/i18n';

export function LanguageSwitcher() {
  const { i18n, t } = useTranslation();

  function handleChange(event: React.ChangeEvent<HTMLSelectElement>) {
    const locale = event.target.value;
    void i18n.changeLanguage(locale);
    window.localStorage.setItem(LOCALE_STORAGE_KEY, locale);
  }

  return (
    <label className="flex items-center gap-2 text-sm">
      <span className="sr-only">{t('languageSwitcher.label')}</span>
      <select
        value={i18n.language}
        onChange={handleChange}
        aria-label={t('languageSwitcher.label')}
        className="rounded-md border border-input bg-transparent px-2 py-1 text-sm"
      >
        {supportedLocales.map((locale) => (
          <option key={locale} value={locale}>
            {t(`languageSwitcher.${locale}`)}
          </option>
        ))}
      </select>
    </label>
  );
}
