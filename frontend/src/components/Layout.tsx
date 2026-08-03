import { Link, Outlet } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { useDisclaimer } from '@/hooks/useDisclaimer';
import { AuthNav } from '@/components/AuthNav';
import { LanguageSwitcher } from '@/components/LanguageSwitcher';

export function Layout() {
  const { t } = useTranslation();
  const { data: disclaimer } = useDisclaimer();

  return (
    <div className="flex min-h-svh flex-col">
      <header className="border-b">
        {disclaimer && (
          <p className="bg-destructive/10 px-4 py-2 text-center text-sm font-medium text-destructive">
            {disclaimer.short}
          </p>
        )}
        <div className="mx-auto flex max-w-4xl items-center justify-between px-4 py-3">
          <Link to="/" className="text-lg font-semibold">
            {t('app.name')}
          </Link>
          <nav className="flex items-center gap-4">
            <Link to="/terms" className="text-sm text-muted-foreground hover:text-foreground">
              {t('nav.terms')}
            </Link>
            <AuthNav />
            <LanguageSwitcher />
          </nav>
        </div>
      </header>

      <main className="mx-auto w-full max-w-4xl flex-1 px-4 py-8">
        <Outlet />
      </main>

      <footer className="border-t">
        <div className="mx-auto max-w-4xl px-4 py-6 text-sm text-muted-foreground">
          {disclaimer && <p className="mb-3">{disclaimer.extended}</p>}
          <p>
            <Link to="/terms" className="underline underline-offset-2 hover:text-foreground">
              {t('footer.termsLink')}
            </Link>
          </p>
          <p className="mt-2">{t('footer.copyright')}</p>
        </div>
      </footer>
    </div>
  );
}
