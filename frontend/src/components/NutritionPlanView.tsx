import { useTranslation } from 'react-i18next';
import type { NutritionPlan, NutritionPlanItem } from '@/hooks/useNutritionPlans';
import { apiUrl } from '@/lib/api';

interface NutritionPlanViewProps {
  plan: NutritionPlan;
}

function groupByDay(items: NutritionPlanItem[]): Map<number, NutritionPlanItem[]> {
  const days = new Map<number, NutritionPlanItem[]>();

  for (const item of items) {
    days.set(item.day, [...(days.get(item.day) ?? []), item]);
  }

  return days;
}

export function NutritionPlanView({ plan }: NutritionPlanViewProps) {
  const { t } = useTranslation();
  const days = groupByDay(plan.items);

  return (
    <div className="flex flex-col gap-4 rounded-xl border p-6">
      <div className="flex items-start justify-between gap-4">
        <div>
          <h2 className="text-xl font-semibold">{t(`nutritionPlan.goals.${plan.goal}`)}</h2>
          <p className="text-sm text-muted-foreground">
            {t('nutritionPlan.summary', {
              calories: plan.daily_calorie_target,
              protein: plan.daily_protein_target_g,
              fat: plan.daily_fat_target_g,
              carbs: plan.daily_carbs_target_g,
            })}
          </p>
        </div>
        <a
          href={apiUrl(`/nutrition-plans/${plan.id}/export`)}
          className="shrink-0 text-sm underline underline-offset-2"
        >
          {t('nutritionPlan.downloadPdf')}
        </a>
      </div>

      {Array.from(days.entries()).map(([day, items]) => (
        <div key={day} className="flex flex-col gap-2">
          <h3 className="font-semibold">{t('nutritionPlan.day', { day })}</h3>
          <ul className="flex flex-col gap-1.5">
            {items.map((item) => (
              <li key={item.id} className="flex items-baseline justify-between gap-4 text-sm">
                <span>
                  <span className="mr-2 rounded bg-muted px-1.5 py-0.5 text-xs uppercase">
                    {t(`nutritionPlan.mealTime.${item.meal_time}`)}
                  </span>
                  {item.name}
                </span>
                <span className="text-muted-foreground">
                  {t('nutritionPlan.calories', { calories: item.calories })}
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
