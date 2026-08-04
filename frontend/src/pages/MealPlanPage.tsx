import { useState } from 'react';
import { useTranslation } from 'react-i18next';
import { NutritionPlanForm } from '@/components/NutritionPlanForm';
import { NutritionPlanView } from '@/components/NutritionPlanView';
import { useNutritionPlan, useNutritionPlans } from '@/hooks/useNutritionPlans';

export function MealPlanPage() {
  const { t } = useTranslation();
  const [selectedPlanId, setSelectedPlanId] = useState<number | null>(null);
  const { data: plans } = useNutritionPlans();
  const { data: selectedPlan } = useNutritionPlan(selectedPlanId);

  return (
    <div className="flex flex-col gap-8">
      <h1 className="text-2xl font-bold">{t('nutritionPlan.title')}</h1>

      <NutritionPlanForm onGenerated={setSelectedPlanId} />

      {selectedPlan && <NutritionPlanView plan={selectedPlan} />}

      {plans && plans.data.length > 0 && (
        <div className="flex flex-col gap-2">
          <h2 className="text-xl font-semibold">{t('nutritionPlan.history')}</h2>
          <ul className="flex flex-col gap-1">
            {plans.data.map((plan) => (
              <li key={plan.id}>
                <button
                  type="button"
                  onClick={() => setSelectedPlanId(plan.id)}
                  className="text-sm text-muted-foreground underline-offset-2 hover:text-foreground hover:underline"
                >
                  {t('nutritionPlan.historyEntry', {
                    goal: t(`nutritionPlan.goals.${plan.goal}`),
                    date: plan.created_at.slice(0, 10),
                  })}
                </button>
              </li>
            ))}
          </ul>
        </div>
      )}
    </div>
  );
}
