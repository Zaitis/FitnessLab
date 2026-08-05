import { useState } from 'react';
import { useTranslation } from 'react-i18next';
import { MealTemplateForm } from '@/components/MealTemplateForm';
import { Button } from '@/components/ui/button';
import {
  useAdminMealTemplates,
  useCreateMealTemplate,
  useDeleteMealTemplate,
  useUpdateMealTemplate,
  type MealTemplate,
  type MealTemplateInput,
} from '@/hooks/useAdminMealTemplates';

type FormState = 'closed' | 'create' | number;

export function AdminMealTemplatesPage() {
  const { t, i18n } = useTranslation();
  const { data: mealTemplates, isLoading } = useAdminMealTemplates();
  const createMutation = useCreateMealTemplate();
  const updateMutation = useUpdateMealTemplate();
  const deleteMutation = useDeleteMealTemplate();
  const [formState, setFormState] = useState<FormState>('closed');

  const editingMealTemplate =
    typeof formState === 'number'
      ? mealTemplates?.find((mealTemplate) => mealTemplate.id === formState)
      : undefined;

  function handleSubmit(values: MealTemplateInput) {
    if (typeof formState === 'number') {
      updateMutation.mutate({ id: formState, values }, { onSuccess: () => setFormState('closed') });

      return;
    }

    createMutation.mutate(values, { onSuccess: () => setFormState('closed') });
  }

  function handleDelete(mealTemplate: MealTemplate) {
    if (window.confirm(t('admin.mealTemplates.confirmDelete'))) {
      deleteMutation.mutate(mealTemplate.id);
    }
  }

  const activeMutation = typeof formState === 'number' ? updateMutation : createMutation;

  return (
    <div className="flex flex-col gap-6">
      <div className="flex items-center justify-between">
        <h1 className="text-2xl font-bold">{t('admin.mealTemplates.title')}</h1>
        {formState === 'closed' && (
          <Button type="button" onClick={() => setFormState('create')}>
            {t('admin.mealTemplates.addNew')}
          </Button>
        )}
      </div>

      {formState !== 'closed' && (
        <MealTemplateForm
          mealTemplate={editingMealTemplate}
          onSubmit={handleSubmit}
          onCancel={() => setFormState('closed')}
          isSubmitting={activeMutation.isPending}
          isError={activeMutation.isError}
        />
      )}

      {!isLoading && mealTemplates?.length === 0 && (
        <p className="text-muted-foreground">{t('admin.mealTemplates.empty')}</p>
      )}

      {mealTemplates && mealTemplates.length > 0 && (
        <table className="w-full text-left text-sm">
          <thead>
            <tr className="border-b text-muted-foreground">
              <th className="py-2">{t('admin.mealTemplates.table.name')}</th>
              <th className="py-2">{t('admin.mealTemplates.table.mealTime')}</th>
              <th className="py-2">{t('admin.mealTemplates.table.calories')}</th>
              <th className="py-2">{t('admin.mealTemplates.table.macros')}</th>
              <th className="py-2">{t('admin.mealTemplates.table.actions')}</th>
            </tr>
          </thead>
          <tbody>
            {mealTemplates.map((mealTemplate) => (
              <tr key={mealTemplate.id} className="border-b align-top last:border-0">
                <td className="py-2">{mealTemplate.name[i18n.language] ?? mealTemplate.name.en}</td>
                <td className="py-2">{t(`nutritionPlan.mealTime.${mealTemplate.meal_time}`)}</td>
                <td className="py-2">{mealTemplate.calories}</td>
                <td className="py-2">
                  {mealTemplate.protein_g} / {mealTemplate.fat_g} / {mealTemplate.carbs_g}
                </td>
                <td className="py-2">
                  <div className="flex gap-3">
                    <button
                      type="button"
                      onClick={() => setFormState(mealTemplate.id)}
                      className="text-muted-foreground underline-offset-2 hover:text-foreground hover:underline"
                    >
                      {t('admin.mealTemplates.edit')}
                    </button>
                    <button
                      type="button"
                      onClick={() => handleDelete(mealTemplate)}
                      className="text-destructive underline-offset-2 hover:underline"
                    >
                      {t('admin.mealTemplates.delete')}
                    </button>
                  </div>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      )}
    </div>
  );
}
