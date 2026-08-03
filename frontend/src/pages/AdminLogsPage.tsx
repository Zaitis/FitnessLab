import { useState } from 'react';
import { useTranslation } from 'react-i18next';
import { Navigate } from 'react-router-dom';
import { useUser } from '@/hooks/useAuth';
import { useErrorLogs } from '@/hooks/useErrorLogs';

const LEVELS = ['emergency', 'alert', 'critical', 'error', 'warning', 'notice', 'info', 'debug'];

export function AdminLogsPage() {
  const { t } = useTranslation();
  const { data: user, isLoading: isUserLoading } = useUser();
  const [level, setLevel] = useState<string | null>(null);
  const { data, isLoading } = useErrorLogs(level);

  if (isUserLoading) {
    return null;
  }

  if (!user?.is_admin) {
    return <Navigate to="/dashboard" replace />;
  }

  const entries = data?.data ?? [];

  return (
    <div className="flex flex-col gap-4">
      <h1 className="text-2xl font-bold">{t('admin.logs.title')}</h1>

      <label className="flex items-center gap-2 text-sm">
        <span>{t('admin.logs.levelFilter')}</span>
        <select
          value={level ?? ''}
          onChange={(event) => setLevel(event.target.value || null)}
          className="rounded-md border border-input bg-transparent px-2 py-1 text-sm"
        >
          <option value="">{t('admin.logs.allLevels')}</option>
          {LEVELS.map((l) => (
            <option key={l} value={l}>
              {l}
            </option>
          ))}
        </select>
      </label>

      {!isLoading && entries.length === 0 && (
        <p className="text-muted-foreground">{t('admin.logs.empty')}</p>
      )}

      {entries.length > 0 && (
        <table className="w-full text-left text-sm">
          <thead>
            <tr className="border-b text-muted-foreground">
              <th className="py-2">{t('admin.logs.table.time')}</th>
              <th className="py-2">{t('admin.logs.table.level')}</th>
              <th className="py-2">{t('admin.logs.table.message')}</th>
            </tr>
          </thead>
          <tbody>
            {entries.map((entry) => (
              <tr key={entry.id} className="border-b align-top last:border-0">
                <td className="py-2 whitespace-nowrap">{entry.created_at}</td>
                <td className="py-2">{entry.level}</td>
                <td className="py-2">{entry.message}</td>
              </tr>
            ))}
          </tbody>
        </table>
      )}
    </div>
  );
}
