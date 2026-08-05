import { useTranslation } from 'react-i18next';
import { Navigate, NavLink, Outlet } from 'react-router';
import { useUser } from '@/hooks/useAuth';

function navLinkClassName({ isActive }: { isActive: boolean }) {
  return `border-b-2 pb-3 text-sm font-medium ${
    isActive
      ? 'border-primary text-foreground'
      : 'border-transparent text-muted-foreground hover:text-foreground'
  }`;
}

export function AdminLayout() {
  const { t } = useTranslation();
  const { data: user, isLoading } = useUser();

  if (isLoading) {
    return null;
  }

  if (!user?.is_admin) {
    return <Navigate to="/dashboard" replace />;
  }

  return (
    <div className="flex flex-col gap-6">
      <nav className="flex flex-wrap gap-6 border-b">
        <NavLink to="/dashboard/admin" end className={navLinkClassName}>
          {t('admin.nav.logs')}
        </NavLink>
        <NavLink to="/dashboard/admin/exercises" className={navLinkClassName}>
          {t('admin.nav.exercises')}
        </NavLink>
        <NavLink to="/dashboard/admin/meal-templates" className={navLinkClassName}>
          {t('admin.nav.mealTemplates')}
        </NavLink>
      </nav>

      <Outlet />
    </div>
  );
}
