import { useTranslation } from 'react-i18next';
import type { WorkoutPlan, WorkoutPlanItem } from '@/hooks/useWorkoutPlans';
import { apiUrl } from '@/lib/api';

interface WorkoutPlanViewProps {
  plan: WorkoutPlan;
}

function groupByDay(items: WorkoutPlanItem[]): Map<number, WorkoutPlanItem[]> {
  const days = new Map<number, WorkoutPlanItem[]>();

  for (const item of items) {
    days.set(item.day, [...(days.get(item.day) ?? []), item]);
  }

  return days;
}

export function WorkoutPlanView({ plan }: WorkoutPlanViewProps) {
  const { t } = useTranslation();
  const days = groupByDay(plan.items);

  return (
    <div className="flex flex-col gap-4 rounded-xl border p-6">
      <div className="flex items-start justify-between gap-4">
        <div>
          <h2 className="text-xl font-semibold">{t(`workoutPlan.goals.${plan.goal}`)}</h2>
          <p className="text-sm text-muted-foreground">
            {t('workoutPlan.summary', {
              level: t(`workoutPlan.experienceLevels.${plan.experience_level}`),
              days: plan.days_per_week,
            })}
          </p>
        </div>
        <a
          href={apiUrl(`/workout-plans/${plan.id}/export`)}
          className="shrink-0 text-sm underline underline-offset-2"
        >
          {t('workoutPlan.downloadPdf')}
        </a>
      </div>

      {Array.from(days.entries()).map(([day, items]) => (
        <div key={day} className="flex flex-col gap-2">
          <h3 className="font-semibold">{t('workoutPlan.day', { day })}</h3>
          <ul className="flex flex-col gap-1.5">
            {items.map((item) => (
              <li key={item.id} className="flex items-baseline justify-between gap-4 text-sm">
                <span>
                  <span className="mr-2 rounded bg-muted px-1.5 py-0.5 text-xs uppercase">
                    {t(`workoutPlan.itemType.${item.type}`)}
                  </span>
                  {item.name}
                </span>
                <span className="text-muted-foreground">
                  {item.type === 'strength'
                    ? t('workoutPlan.setsReps', { sets: item.sets, reps: item.reps })
                    : t('workoutPlan.duration', { minutes: item.duration_minutes })}
                </span>
              </li>
            ))}
          </ul>
        </div>
      ))}

      <p className="border-t pt-3 text-xs text-muted-foreground">{plan.disclaimer}</p>
    </div>
  );
}
