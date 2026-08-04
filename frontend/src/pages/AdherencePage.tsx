import { useMemo, useState } from 'react';
import { format } from 'date-fns';
import { useTranslation } from 'react-i18next';
import { Calendar } from '@/components/ui/calendar';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import {
  type AdherencePlanType,
  useMonthAdherence,
  useToggleAdherence,
} from '@/hooks/useAdherence';
import { useNutritionPlans, type NutritionPlanItem } from '@/hooks/useNutritionPlans';
import { useWorkoutPlans, type WorkoutPlanItem } from '@/hooks/useWorkoutPlans';
import { planDayForDate } from '@/lib/adherence';

function startOfToday(): Date {
  const now = new Date();

  return new Date(now.getFullYear(), now.getMonth(), now.getDate());
}

interface ChecklistProps {
  title: string;
  emptyMessage?: string;
  items: { id: string; name: string }[];
  isChecked: (planItemId: string) => boolean;
  onToggle: (planItemId: string, checked: boolean) => void;
  idPrefix: string;
}

function Checklist({ title, emptyMessage, items, isChecked, onToggle, idPrefix }: ChecklistProps) {
  return (
    <Card>
      <CardHeader>
        <CardTitle>{title}</CardTitle>
      </CardHeader>
      <CardContent>
        {items.length === 0 && emptyMessage ? (
          <p className="text-sm text-muted-foreground">{emptyMessage}</p>
        ) : (
          <ul className="flex flex-col gap-2">
            {items.map((item) => {
              const inputId = `${idPrefix}-${item.id}`;
              const checked = isChecked(item.id);

              return (
                <li key={item.id} className="flex items-center gap-2">
                  <Checkbox
                    id={inputId}
                    checked={checked}
                    onCheckedChange={(value) => onToggle(item.id, value === true)}
                  />
                  <label htmlFor={inputId} className="text-sm">
                    {item.name}
                  </label>
                </li>
              );
            })}
          </ul>
        )}
      </CardContent>
    </Card>
  );
}

export function AdherencePage() {
  const { t } = useTranslation();
  const [selectedDate, setSelectedDate] = useState<Date>(startOfToday);
  const [visibleMonth, setVisibleMonth] = useState<Date>(startOfToday);

  const monthKey = format(visibleMonth, 'yyyy-MM');
  const selectedDateKey = format(selectedDate, 'yyyy-MM-dd');

  const { data: workoutPlans } = useWorkoutPlans();
  const { data: nutritionPlans } = useNutritionPlans();
  const { data: entries } = useMonthAdherence(monthKey);
  const toggle = useToggleAdherence(monthKey);

  const workoutPlan = workoutPlans?.data[0] ?? null;
  const nutritionPlan = nutritionPlans?.data[0] ?? null;

  const checkedKeys = useMemo(
    () => new Set((entries ?? []).map((entry) => `${entry.entry_date}|${entry.plan_item_id}`)),
    [entries],
  );

  const entryDates = useMemo(
    () =>
      [...new Set((entries ?? []).map((entry) => entry.entry_date))].map(
        (date) => new Date(`${date}T00:00:00`),
      ),
    [entries],
  );

  const isChecked = (planItemId: string) => checkedKeys.has(`${selectedDateKey}|${planItemId}`);

  const toggleItem = (
    planType: AdherencePlanType,
    planId: number,
    planItemId: string,
    checked: boolean,
  ) => {
    toggle.mutate({
      entry_date: selectedDateKey,
      plan_type: planType,
      plan_id: planId,
      plan_item_id: planItemId,
      checked,
    });
  };

  const workoutItems: WorkoutPlanItem[] = workoutPlan
    ? workoutPlan.items.filter(
        (item) =>
          item.day ===
          planDayForDate(selectedDate, new Date(workoutPlan.created_at), workoutPlan.days_per_week),
      )
    : [];

  const nutritionItems: NutritionPlanItem[] = nutritionPlan
    ? nutritionPlan.items.filter(
        (item) => item.day === planDayForDate(selectedDate, new Date(nutritionPlan.created_at), 7),
      )
    : [];

  return (
    <div className="flex flex-col gap-8">
      <h1 className="text-2xl font-bold">{t('adherence.title')}</h1>

      {!workoutPlan && !nutritionPlan ? (
        <p className="text-muted-foreground">{t('adherence.noPlans')}</p>
      ) : (
        <div className="flex flex-col gap-6 md:flex-row md:items-start">
          <Calendar
            mode="single"
            selected={selectedDate}
            onSelect={(date) => date && setSelectedDate(date)}
            month={visibleMonth}
            onMonthChange={setVisibleMonth}
            modifiers={{ hasEntries: entryDates }}
            modifiersClassNames={{
              hasEntries:
                "after:absolute after:bottom-1 after:left-1/2 after:size-1 after:-translate-x-1/2 after:rounded-full after:bg-primary after:content-['']",
            }}
            className="rounded-xl border"
          />

          <div className="flex flex-1 flex-col gap-4">
            <h2 className="font-semibold">{format(selectedDate, 'PPPP')}</h2>

            {workoutPlan && (
              <Checklist
                title={t('adherence.workoutSection')}
                emptyMessage={t('adherence.restDay')}
                items={workoutItems}
                isChecked={isChecked}
                onToggle={(planItemId, checked) =>
                  toggleItem('workout', workoutPlan.id, planItemId, checked)
                }
                idPrefix="workout"
              />
            )}

            {nutritionPlan && (
              <Checklist
                title={t('adherence.nutritionSection')}
                items={nutritionItems}
                isChecked={isChecked}
                onToggle={(planItemId, checked) =>
                  toggleItem('nutrition', nutritionPlan.id, planItemId, checked)
                }
                idPrefix="nutrition"
              />
            )}
          </div>
        </div>
      )}
    </div>
  );
}
