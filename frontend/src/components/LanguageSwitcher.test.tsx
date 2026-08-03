import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { afterEach, describe, expect, it } from 'vitest';
import i18n, { LOCALE_STORAGE_KEY } from '@/i18n';
import { LanguageSwitcher } from './LanguageSwitcher';

describe('LanguageSwitcher', () => {
  afterEach(async () => {
    window.localStorage.removeItem(LOCALE_STORAGE_KEY);
    await i18n.changeLanguage('en');
  });

  it('changes the active language and persists the choice to localStorage', async () => {
    const user = userEvent.setup();
    render(<LanguageSwitcher />);

    await user.selectOptions(screen.getByRole('combobox'), 'pl');

    expect(i18n.language).toBe('pl');
    expect(window.localStorage.getItem(LOCALE_STORAGE_KEY)).toBe('pl');
  });
});
