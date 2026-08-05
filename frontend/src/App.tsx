import { QueryClientProvider } from '@tanstack/react-query';
import { BrowserRouter, Route, Routes } from 'react-router';
import { AdminLayout } from '@/components/AdminLayout';
import { DashboardLayout } from '@/components/DashboardLayout';
import { Layout } from '@/components/Layout';
import { ProtectedRoute } from '@/components/ProtectedRoute';
import { queryClient } from '@/lib/queryClient';
import { AdherencePage } from '@/pages/AdherencePage';
import { AdminExercisesPage } from '@/pages/AdminExercisesPage';
import { AdminLogsPage } from '@/pages/AdminLogsPage';
import { ForgotPasswordPage } from '@/pages/ForgotPasswordPage';
import { LandingPage } from '@/pages/LandingPage';
import { LoginPage } from '@/pages/LoginPage';
import { MealPlanPage } from '@/pages/MealPlanPage';
import { NotFoundPage } from '@/pages/NotFoundPage';
import { ProgressPage } from '@/pages/ProgressPage';
import { RegisterPage } from '@/pages/RegisterPage';
import { ResetPasswordPage } from '@/pages/ResetPasswordPage';
import { TermsPage } from '@/pages/TermsPage';
import { TrainingPlanPage } from '@/pages/TrainingPlanPage';

function App() {
  return (
    <QueryClientProvider client={queryClient}>
      <BrowserRouter>
        <Routes>
          <Route element={<Layout />}>
            <Route index element={<LandingPage />} />
            <Route path="terms" element={<TermsPage />} />
            <Route path="login" element={<LoginPage />} />
            <Route path="register" element={<RegisterPage />} />
            <Route path="forgot-password" element={<ForgotPasswordPage />} />
            {/* Must match the URL built by ResetPassword::createUrlUsing in
                the backend's AppServiceProvider — the token is a path
                segment and the email a query parameter. */}
            <Route path="password-reset/:token" element={<ResetPasswordPage />} />
            <Route element={<ProtectedRoute />}>
              <Route path="dashboard" element={<DashboardLayout />}>
                <Route index element={<ProgressPage />} />
                <Route path="training" element={<TrainingPlanPage />} />
                <Route path="meal-plan" element={<MealPlanPage />} />
                <Route path="adherence" element={<AdherencePage />} />
                <Route path="admin" element={<AdminLayout />}>
                  <Route index element={<AdminLogsPage />} />
                  <Route path="exercises" element={<AdminExercisesPage />} />
                </Route>
              </Route>
            </Route>
            <Route path="*" element={<NotFoundPage />} />
          </Route>
        </Routes>
      </BrowserRouter>
    </QueryClientProvider>
  );
}

export default App;
