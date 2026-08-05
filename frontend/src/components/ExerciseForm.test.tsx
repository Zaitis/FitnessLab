import { screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { describe, expect, it, vi } from 'vitest';
import { renderWithProviders } from '@/test/renderWithProviders';
import { ExerciseForm } from './ExerciseForm';

function renderForm() {
  const onSubmit = vi.fn();
  const onCancel = vi.fn();

  renderWithProviders(
    <ExerciseForm onSubmit={onSubmit} onCancel={onCancel} isSubmitting={false} isError={false} />,
  );

  return { onSubmit, onCancel };
}

describe('ExerciseForm', () => {
  it('submits a strength exercise with muscle group, sets, and reps', async () => {
    const user = userEvent.setup();
    const { onSubmit } = renderForm();

    await user.type(screen.getByLabelText(/Name \(EN\)/), 'Push-up');
    await user.type(screen.getByLabelText(/Instructions \(EN\)/), 'Lower and push back up.');
    await user.click(screen.getByRole('button', { name: 'PL' }));
    await user.type(screen.getByLabelText(/Name \(PL\)/), 'Pompka');
    await user.type(
      screen.getByLabelText(/Instructions \(PL\)/),
      'Opuść i wypchnij się z powrotem.',
    );

    await user.click(screen.getByRole('button', { name: 'Save' }));

    expect(onSubmit).toHaveBeenCalledWith(
      expect.objectContaining({
        type: 'strength',
        muscle_group: 'chest',
        sets: 3,
        reps: 10,
        name: { en: 'Push-up', pl: 'Pompka' },
      }),
    );
  });

  it('switches to duration instead of sets/reps for a cardio exercise', async () => {
    const user = userEvent.setup();
    renderForm();

    await user.selectOptions(screen.getByLabelText('Type'), 'cardio');

    expect(screen.getByLabelText('Duration (minutes)')).toBeInTheDocument();
    expect(screen.queryByLabelText('Sets')).not.toBeInTheDocument();
    expect(screen.queryByLabelText('Reps')).not.toBeInTheDocument();
  });

  it('calls onCancel when cancel is clicked', async () => {
    const user = userEvent.setup();
    const { onCancel } = renderForm();

    await user.click(screen.getByRole('button', { name: 'Cancel' }));

    expect(onCancel).toHaveBeenCalled();
  });
});
