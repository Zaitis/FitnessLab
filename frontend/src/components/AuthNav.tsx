import { useTranslation } from 'react-i18next';
import { Link, useNavigate } from 'react-router-dom';
import { useLogout, useUser } from '@/hooks/useAuth';

export function AuthNav() {
  const { t } = useTranslation();
  const navigate = useNavigate();
  const { data: user, isLoading } = useUser();
  const logoutMutation = useLogout();

  if (isLoading) {
    return null;
  }

  if (!user) {
    return (
      <div className="flex items-center gap-3 text-sm">
        <Link to="/login" className="text-muted-foreground hover:text-foreground">
          {t('nav.login')}
        </Link>
        <Link to="/register" className="text-muted-foreground hover:text-foreground">
          {t('nav.register')}
        </Link>
      </div>
    );
  }

  async function handleLogout() {
    await logoutMutation.mutateAsync();
    navigate('/');
  }

  return (
    <div className="flex items-center gap-3 text-sm">
      <Link to="/dashboard" className="text-muted-foreground hover:text-foreground">
        {user.name}
      </Link>
      <button
        type="button"
        onClick={handleLogout}
        className="text-muted-foreground hover:text-foreground"
      >
        {t('nav.logout')}
      </button>
    </div>
  );
}
