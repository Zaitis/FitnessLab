import i18n from 'i18next';
import { initReactI18next } from 'react-i18next';
import commonEn from './locales/en/common.json';
import termsEn from './locales/en/terms.json';
import commonPl from './locales/pl/common.json';
import termsPl from './locales/pl/terms.json';

export const supportedLocales = ['en', 'pl'] as const;
export type SupportedLocale = (typeof supportedLocales)[number];

export const LOCALE_STORAGE_KEY = 'fitnesslab.locale';

function isSupportedLocale(value: string | null): value is SupportedLocale {
  return (supportedLocales as readonly string[]).includes(value ?? '');
}

function initialLocale(): SupportedLocale {
  if (typeof window === 'undefined') {
    return 'en';
  }

  const stored = window.localStorage.getItem(LOCALE_STORAGE_KEY);

  return isSupportedLocale(stored) ? stored : 'en';
}

void i18n.use(initReactI18next).init({
  resources: {
    en: { common: commonEn, terms: termsEn },
    pl: { common: commonPl, terms: termsPl },
  },
  lng: initialLocale(),
  fallbackLng: 'en',
  defaultNS: 'common',
  interpolation: {
    escapeValue: false,
  },
});

export default i18n;
