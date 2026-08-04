import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { apiFetch } from '@/lib/api';

export type AdherencePlanType = 'workout' | 'nutrition';

export interface AdherenceEntry {
  entry_date: string;
  plan_type: AdherencePlanType;
  plan_id: number;
  plan_item_id: string;
}

export function useMonthAdherence(month: string) {
  return useQuery({
    queryKey: ['adherence', month],
    queryFn: () => apiFetch<AdherenceEntry[]>(`/adherence?month=${month}`),
  });
}

interface ToggleAdherenceValues {
  entry_date: string;
  plan_type: AdherencePlanType;
  plan_id: number;
  plan_item_id: string;
  checked: boolean;
}

interface ToggleAdherenceContext {
  previous: AdherenceEntry[] | undefined;
}

export function useToggleAdherence(month: string) {
  const queryClient = useQueryClient();
  const queryKey = ['adherence', month];

  return useMutation<{ checked: boolean }, Error, ToggleAdherenceValues, ToggleAdherenceContext>({
    mutationFn: (values) =>
      apiFetch<{ checked: boolean }>('/adherence', {
        method: values.checked ? 'POST' : 'DELETE',
        body: JSON.stringify(values),
      }),
    onMutate: async (values) => {
      await queryClient.cancelQueries({ queryKey });
      const previous = queryClient.getQueryData<AdherenceEntry[]>(queryKey);

      queryClient.setQueryData<AdherenceEntry[]>(queryKey, (old = []) => {
        if (values.checked) {
          return [
            ...old,
            {
              entry_date: values.entry_date,
              plan_type: values.plan_type,
              plan_id: values.plan_id,
              plan_item_id: values.plan_item_id,
            },
          ];
        }

        return old.filter(
          (entry) =>
            !(entry.entry_date === values.entry_date && entry.plan_item_id === values.plan_item_id),
        );
      });

      return { previous };
    },
    onError: (_error, _values, context) => {
      if (context) {
        queryClient.setQueryData(queryKey, context.previous);
      }
    },
    onSettled: () => {
      void queryClient.invalidateQueries({ queryKey });
    },
  });
}
