import { screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { http, HttpResponse } from 'msw';
import { afterEach, describe, expect, it, vi } from 'vitest';
import i18n, { LOCALE_STORAGE_KEY } from '@/i18n';
import { server } from '@/mocks/server';
import { userFixture } from '@/mocks/handlers';
import { renderWithProviders } from '@/test/renderWithProviders';
import { LanguageSwitcher } from './LanguageSwitcher';

describe('LanguageSwitcher', () => {
  afterEach(async () => {
    window.localStorage.removeItem(LOCALE_STORAGE_KEY);
    await i18n.changeLanguage('en');
  });

  it('changes the active language and persists the choice to localStorage', async () => {
    const user = userEvent.setup();
    renderWithProviders(<LanguageSwitcher />);

    await user.selectOptions(screen.getByRole('combobox'), 'pl');

    expect(i18n.language).toBe('pl');
    expect(window.localStorage.getItem(LOCALE_STORAGE_KEY)).toBe('pl');
  });

  it('persists the choice to the account when signed in', async () => {
    const userRequests = vi.fn();
    server.use(
      http.get('http://localhost:8000/api/user', () => {
        userRequests();

        return HttpResponse.json(userFixture);
      }),
    );

    const requests = vi.fn();
    server.use(
      http.patch('http://localhost:8000/api/user/locale', async ({ request }) => {
        requests(await request.json());

        return HttpResponse.json({ locale: 'pl' });
      }),
    );

    const user = userEvent.setup();
    renderWithProviders(<LanguageSwitcher />);

    // Wait for the user query to resolve before interacting — the switcher
    // only persists to the account once it knows someone is signed in.
    await waitFor(() => expect(userRequests).toHaveBeenCalled());

    await user.selectOptions(screen.getByRole('combobox'), 'pl');

    await waitFor(() => expect(requests).toHaveBeenCalledWith({ locale: 'pl' }));
  });
});
