import { useState } from 'react';
import { useTranslation } from 'react-i18next';
import { WorkoutPlanForm } from '@/components/WorkoutPlanForm';
import { WorkoutPlanView } from '@/components/WorkoutPlanView';
import { useWorkoutPlan, useWorkoutPlans } from '@/hooks/useWorkoutPlans';

export function TrainingPlanPage() {
  const { t } = useTranslation();
  const [selectedPlanId, setSelectedPlanId] = useState<number | null>(null);
  const { data: plans } = useWorkoutPlans();
  const { data: selectedPlan } = useWorkoutPlan(selectedPlanId);

  return (
    <div className="flex flex-col gap-8">
      <h1 className="text-2xl font-bold">{t('workoutPlan.title')}</h1>

      <WorkoutPlanForm onGenerated={setSelectedPlanId} />

      {selectedPlan && <WorkoutPlanView plan={selectedPlan} />}

      {plans && plans.data.length > 0 && (
        <div className="flex flex-col gap-2">
          <h2 className="text-xl font-semibold">{t('workoutPlan.history')}</h2>
          <ul className="flex flex-col gap-1">
            {plans.data.map((plan) => (
              <li key={plan.id}>
                <button
                  type="button"
                  onClick={() => setSelectedPlanId(plan.id)}
                  className="text-sm text-muted-foreground underline-offset-2 hover:text-foreground hover:underline"
                >
                  {t('workoutPlan.historyEntry', {
                    goal: t(`workoutPlan.goals.${plan.goal}`),
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
