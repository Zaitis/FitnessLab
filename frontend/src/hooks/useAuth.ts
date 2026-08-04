import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { apiFetch, ApiError } from '@/lib/api';

export interface User {
  id: number;
  name: string;
  email: string;
  locale: string | null;
  email_verified_at: string | null;
  is_admin: boolean;
}

export function useUser() {
  return useQuery({
    queryKey: ['user'],
    queryFn: async (): Promise<User | null> => {
      try {
        return await apiFetch<User>('/user');
      } catch (error) {
        if (error instanceof ApiError && error.status === 401) {
          return null;
        }

        throw error;
      }
    },
    // Both Layout's AuthNav and ProtectedRoute mount this query independently;
    // without a staleTime every mount refetches instead of sharing the cache.
    staleTime: 60_000,
  });
}

interface LoginValues {
  email: string;
  password: string;
}

export function useLogin() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (values: LoginValues) =>
      apiFetch<void>('/login', { method: 'POST', body: JSON.stringify(values) }),
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['user'] }),
  });
}

interface RegisterValues {
  name: string;
  email: string;
  password: string;
  password_confirmation: string;
}

export function useRegister() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (values: RegisterValues) =>
      apiFetch<void>('/register', { method: 'POST', body: JSON.stringify(values) }),
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['user'] }),
  });
}

export function useLogout() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: () => apiFetch<void>('/logout', { method: 'POST' }),
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['user'] }),
  });
}

export function useForgotPassword() {
  return useMutation({
    mutationFn: (email: string) =>
      apiFetch<{ status: string }>('/forgot-password', {
        method: 'POST',
        body: JSON.stringify({ email }),
      }),
  });
}

interface ResetPasswordValues {
  token: string;
  email: string;
  password: string;
  password_confirmation: string;
}

export function useResetPassword() {
  return useMutation({
    mutationFn: (values: ResetPasswordValues) =>
      apiFetch<{ status: string }>('/reset-password', {
        method: 'POST',
        body: JSON.stringify(values),
      }),
  });
}

export function useUpdateLocale() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (locale: string) =>
      apiFetch<{ locale: string }>('/user/locale', {
        method: 'PATCH',
        body: JSON.stringify({ locale }),
      }),
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['user'] }),
  });
}
