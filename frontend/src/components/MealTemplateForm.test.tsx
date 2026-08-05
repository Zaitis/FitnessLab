import { screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { describe, expect, it, vi } from 'vitest';
import { renderWithProviders } from '@/test/renderWithProviders';
import { MealTemplateForm } from './MealTemplateForm';

function renderForm() {
  const onSubmit = vi.fn();
  const onCancel = vi.fn();

  renderWithProviders(
    <MealTemplateForm
      onSubmit={onSubmit}
      onCancel={onCancel}
      isSubmitting={false}
      isError={false}
    />,
  );

  return { onSubmit, onCancel };
}

describe('MealTemplateForm', () => {
  it('submits a meal template with values from both locales', async () => {
    const user = userEvent.setup();
    const { onSubmit } = renderForm();

    await user.type(screen.getByLabelText(/Name \(EN\)/), 'Oatmeal with Banana');
    await user.type(screen.getByLabelText(/Description \(EN\)/), 'Rolled oats with banana.');
    await user.click(screen.getByRole('button', { name: 'PL' }));
    await user.type(screen.getByLabelText(/Name \(PL\)/), 'Owsianka z bananem');
    await user.type(screen.getByLabelText(/Description \(PL\)/), 'Płatki owsiane z bananem.');

    await user.click(screen.getByRole('button', { name: 'Save' }));

    expect(onSubmit).toHaveBeenCalledWith(
      expect.objectContaining({
        meal_time: 'breakfast',
        calories: 400,
        protein_g: 20,
        fat_g: 15,
        carbs_g: 45,
        name: { en: 'Oatmeal with Banana', pl: 'Owsianka z bananem' },
      }),
    );
  });

  it('calls onCancel when cancel is clicked', async () => {
    const user = userEvent.setup();
    const { onCancel } = renderForm();

    await user.click(screen.getByRole('button', { name: 'Cancel' }));

    expect(onCancel).toHaveBeenCalled();
  });
});
