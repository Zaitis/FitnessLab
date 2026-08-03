const API_BASE_URL =
  (import.meta.env.VITE_API_URL as string | undefined) ?? 'http://localhost:8000/api';
const API_ROOT = API_BASE_URL.replace(/\/api\/?$/, '');

export class ApiError extends Error {
  constructor(
    message: string,
    public status: number,
    public errors?: Record<string, string[]>,
  ) {
    super(message);
  }
}

function readCookie(name: string): string | null {
  const match = new RegExp(`(?:^|; )${name}=([^;]*)`).exec(document.cookie);

  return match ? decodeURIComponent(match[1]) : null;
}

async function ensureCsrfCookie(): Promise<void> {
  await fetch(`${API_ROOT}/sanctum/csrf-cookie`, { credentials: 'include' });
}

export async function apiFetch<T>(path: string, init?: RequestInit): Promise<T> {
  const method = (init?.method ?? 'GET').toUpperCase();

  if (method !== 'GET' && method !== 'HEAD') {
    await ensureCsrfCookie();
  }

  const response = await fetch(`${API_BASE_URL}${path}`, {
    ...init,
    credentials: 'include',
    headers: {
      'Content-Type': 'application/json',
      Accept: 'application/json',
      ...(readCookie('XSRF-TOKEN') ? { 'X-XSRF-TOKEN': readCookie('XSRF-TOKEN')! } : {}),
      ...init?.headers,
    },
  });

  if (!response.ok) {
    const body = (await response.json().catch(() => null)) as {
      message?: string;
      errors?: Record<string, string[]>;
    } | null;

    throw new ApiError(body?.message ?? response.statusText, response.status, body?.errors);
  }

  return response.json() as Promise<T>;
}
