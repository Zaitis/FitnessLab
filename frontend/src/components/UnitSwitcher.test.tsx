import { screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { afterEach, describe, expect, it } from 'vitest';
import { UNIT_STORAGE_KEY, setUnitSystem } from '@/lib/unitPreference';
import { renderWithProviders } from '@/test/renderWithProviders';
import { UnitSwitcher } from './UnitSwitcher';

describe('UnitSwitcher', () => {
  afterEach(() => setUnitSystem('metric'));

  it('switches the active unit system and persists the choice to localStorage', async () => {
    const user = userEvent.setup();
    renderWithProviders(<UnitSwitcher />);

    await user.selectOptions(screen.getByRole('combobox'), 'imperial');

    expect(window.localStorage.getItem(UNIT_STORAGE_KEY)).toBe('imperial');
  });
});
