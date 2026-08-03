import { useQuery } from '@tanstack/react-query';
import { apiFetch } from '@/lib/api';

export interface ErrorLogEntry {
  id: number;
  level: string;
  message: string;
  context: Record<string, unknown> | null;
  created_at: string;
}

interface ErrorLogsPage {
  data: ErrorLogEntry[];
  current_page: number;
  last_page: number;
  total: number;
}

export function useErrorLogs(level: string | null) {
  return useQuery({
    queryKey: ['admin', 'logs', level],
    queryFn: () =>
      apiFetch<ErrorLogsPage>(`/admin/logs${level ? `?level=${encodeURIComponent(level)}` : ''}`),
  });
}
