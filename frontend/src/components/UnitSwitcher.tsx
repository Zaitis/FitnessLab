import { useTranslation } from 'react-i18next';
import { useUnitSystem } from '@/lib/unitPreference';
import type { UnitSystem } from '@/lib/units';

export function UnitSwitcher() {
  const { t } = useTranslation();
  const [unit, setUnit] = useUnitSystem();

  function handleChange(event: React.ChangeEvent<HTMLSelectElement>) {
    setUnit(event.target.value as UnitSystem);
  }

  return (
    <label className="flex items-center gap-2 text-sm">
      <span className="sr-only">{t('unitSwitcher.label')}</span>
      <select
        value={unit}
        onChange={handleChange}
        aria-label={t('unitSwitcher.label')}
        className="rounded-md border border-input bg-transparent px-2 py-1 text-sm"
      >
        <option value="metric">{t('unitSwitcher.metric')}</option>
        <option value="imperial">{t('unitSwitcher.imperial')}</option>
      </select>
    </label>
  );
}
