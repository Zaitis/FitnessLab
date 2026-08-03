import { useTranslation } from 'react-i18next';
import { MeasurementChart } from '@/components/MeasurementChart';
import { MeasurementForm } from '@/components/MeasurementForm';
import { useMeasurements } from '@/hooks/useMeasurements';

export function ProgressPage() {
  const { t } = useTranslation();
  const { data, isLoading } = useMeasurements();
  const measurements = data?.data ?? [];

  return (
    <div className="flex flex-col gap-8">
      <div>
        <h1 className="mb-4 text-2xl font-bold">{t('progress.title')}</h1>
        {!isLoading && <MeasurementChart measurements={measurements} />}
      </div>

      <MeasurementForm />

      {measurements.length > 0 && (
        <div className="flex flex-col gap-2">
          <h2 className="text-xl font-semibold">{t('progress.history')}</h2>
          <table className="w-full text-left text-sm">
            <thead>
              <tr className="border-b text-muted-foreground">
                <th className="py-2">{t('progress.table.date')}</th>
                <th className="py-2">{t('progress.table.weight')}</th>
                <th className="py-2">{t('progress.table.bmi')}</th>
                <th className="py-2">{t('progress.table.category')}</th>
              </tr>
            </thead>
            <tbody>
              {measurements.map((measurement) => (
                <tr key={measurement.id} className="border-b last:border-0">
                  <td className="py-2">{measurement.measured_on}</td>
                  <td className="py-2">{measurement.weight_kg}</td>
                  <td className="py-2">{measurement.value}</td>
                  <td className="py-2">{t(`bmiResult.categories.${measurement.category}`)}</td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}
    </div>
  );
}
