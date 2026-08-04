import { differenceInCalendarDays } from 'date-fns';

/**
 * Plans repeat on a fixed-length cycle (days_per_week for workouts, 7 for
 * nutrition) starting from the day the plan was generated. This maps a real
 * calendar date onto that cycle so the calendar can show the right checklist
 * for any day, indefinitely, without the plan itself carrying dates.
 */
export function planDayForDate(date: Date, planCreatedAt: Date, cycleLength: number): number {
  const diff = differenceInCalendarDays(date, planCreatedAt);
  const cycleIndex = ((diff % cycleLength) + cycleLength) % cycleLength;

  return cycleIndex + 1;
}
