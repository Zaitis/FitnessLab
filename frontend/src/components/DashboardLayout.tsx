import { useTranslation } from 'react-i18next';
import { NavLink, Outlet, useSearchParams } from 'react-router';
import { useUser } from '@/hooks/useAuth';

function navLinkClassName({ isActive }: { isActive: boolean }) {
  return `text-sm ${isActive ? 'font-semibold text-foreground' : 'text-muted-foreground hover:text-foreground'}`;
}

export function DashboardLayout() {
  const { t } = useTranslation();
  const { data: user } = useUser();
  const [searchParams] = useSearchParams();

  return (
    <div className="flex flex-col gap-6">
      {searchParams.get('verified') === '1' && (
        <p className="rounded-md bg-primary/10 px-4 py-2 text-sm font-medium text-primary">
          {t('dashboard.emailVerified')}
        </p>
      )}

      <div>
        <h1 className="text-2xl font-bold">{t('dashboard.welcome', { name: user?.name ?? '' })}</h1>
      </div>

      <nav className="flex flex-wrap gap-4 border-b pb-3">
        <NavLink to="/dashboard" end className={navLinkClassName}>
          {t('dashboard.nav.progress')}
        </NavLink>
        <NavLink to="/dashboard/training" className={navLinkClassName}>
          {t('dashboard.nav.training')}
        </NavLink>
        <NavLink to="/dashboard/meal-plan" className={navLinkClassName}>
          {t('dashboard.nav.mealPlan')}
        </NavLink>
        <NavLink to="/dashboard/adherence" className={navLinkClassName}>
          {t('dashboard.nav.adherence')}
        </NavLink>
        {user?.is_admin && (
          <NavLink to="/dashboard/admin" className={navLinkClassName}>
            {t('dashboard.nav.admin')}
          </NavLink>
        )}
      </nav>

      <Outlet />
    </div>
  );
}
