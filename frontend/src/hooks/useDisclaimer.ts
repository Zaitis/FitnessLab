import { useQuery } from '@tanstack/react-query';
import { useTranslation } from 'react-i18next';
import { apiFetch } from '@/lib/api';

export interface Disclaimer {
  short: string;
  standard: string;
  extended: string;
}

export function useDisclaimer() {
  const { i18n } = useTranslation();

  return useQuery({
    queryKey: ['disclaimer', i18n.language],
    queryFn: () => apiFetch<Disclaimer>(`/disclaimer?locale=${i18n.language}`),
    staleTime: Infinity,
  });
}
