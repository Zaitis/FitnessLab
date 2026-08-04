import { http, HttpResponse, type HttpHandler } from 'msw';

const API_ROOT = 'http://localhost:8000';
const API_BASE_URL = `${API_ROOT}/api`;

export const disclaimerFixture = {
  en: {
    short: 'DEMO PROJECT — NOT MEDICAL ADVICE',
    standard: 'Demo project. Not medical or nutritional advice.',
    extended: 'FitnessLab is a free demonstration project. Nothing here is medical advice.',
  },
  pl: {
    short: 'PROJEKT DEMONSTRACYJNY — TO NIE JEST PORADA MEDYCZNA',
    standard: 'Projekt demonstracyjny. To nie jest porada medyczna ani żywieniowa.',
    extended: 'FitnessLab to bezpłatny projekt demonstracyjny. Nic tutaj nie jest poradą medyczną.',
  },
};

export const userFixture = {
  id: 1,
  name: 'Ada Lovelace',
  email: 'ada@example.com',
  locale: null,
  email_verified_at: null,
  is_admin: false,
};

export const handlers: HttpHandler[] = [
  http.get(`${API_ROOT}/sanctum/csrf-cookie`, () => new HttpResponse(null, { status: 204 })),

  http.get(`${API_BASE_URL}/disclaimer`, ({ request }) => {
    const url = new URL(request.url);
    const locale = url.searchParams.get('locale') === 'pl' ? 'pl' : 'en';

    return HttpResponse.json(disclaimerFixture[locale]);
  }),

  http.post(`${API_BASE_URL}/bmi/calculate`, async ({ request }) => {
    const body = (await request.json()) as { weight_kg: number; height_cm: number };
    const heightInMeters = body.height_cm / 100;
    const value = Math.round((body.weight_kg / (heightInMeters * heightInMeters)) * 10) / 10;

    return HttpResponse.json({ value, category: 'normal' });
  }),

  http.get(`${API_BASE_URL}/user`, () =>
    HttpResponse.json({ message: 'Unauthenticated.' }, { status: 401 }),
  ),

  http.patch(`${API_BASE_URL}/user/locale`, async ({ request }) => {
    const body = (await request.json()) as { locale: string };

    return HttpResponse.json({ locale: body.locale });
  }),

  http.post(`${API_BASE_URL}/login`, () => new HttpResponse(null, { status: 204 })),

  http.post(`${API_BASE_URL}/register`, () => new HttpResponse(null, { status: 204 })),

  http.post(`${API_BASE_URL}/logout`, () => new HttpResponse(null, { status: 204 })),

  http.post(`${API_BASE_URL}/forgot-password`, () =>
    HttpResponse.json({ status: 'We have emailed your password reset link.' }),
  ),

  http.post(`${API_BASE_URL}/reset-password`, () =>
    HttpResponse.json({ status: 'Your password has been reset.' }),
  ),

  http.post(`${API_BASE_URL}/measurements`, async ({ request }) => {
    const body = (await request.json()) as { weight_kg: number; height_cm: number };
    const heightInMeters = body.height_cm / 100;
    const value = Math.round((body.weight_kg / (heightInMeters * heightInMeters)) * 10) / 10;

    return HttpResponse.json(
      { id: 1, weight_kg: body.weight_kg, height_cm: body.height_cm, value, category: 'normal' },
      { status: 201 },
    );
  }),

  http.get(`${API_BASE_URL}/measurements`, () =>
    HttpResponse.json({ data: [], current_page: 1, last_page: 1, total: 0 }),
  ),

  http.get(`${API_BASE_URL}/admin/logs`, () =>
    HttpResponse.json({ data: [], current_page: 1, last_page: 1, total: 0 }),
  ),

  http.get(`${API_BASE_URL}/workout-plans`, () =>
    HttpResponse.json({ data: [], current_page: 1, last_page: 1, total: 0 }),
  ),

  http.post(`${API_BASE_URL}/workout-plans`, async ({ request }) => {
    const body = (await request.json()) as {
      goal: string;
      experience_level: string;
      days_per_week: number;
    };

    return HttpResponse.json(
      {
        id: 1,
        goal: body.goal,
        experience_level: body.experience_level,
        days_per_week: body.days_per_week,
        items: [
          {
            id: 'item-1',
            day: 1,
            type: 'strength',
            name: 'Bench Press',
            instructions: 'Press the bar up from chest height.',
            sets: 3,
            reps: 10,
            duration_minutes: null,
          },
        ],
        disclaimer: disclaimerFixture.en.standard,
        created_at: new Date().toISOString(),
      },
      { status: 201 },
    );
  }),

  http.get(`${API_BASE_URL}/nutrition-plans`, () =>
    HttpResponse.json({ data: [], current_page: 1, last_page: 1, total: 0 }),
  ),

  http.post(`${API_BASE_URL}/nutrition-plans`, async ({ request }) => {
    const body = (await request.json()) as { goal: string };

    return HttpResponse.json(
      {
        id: 1,
        goal: body.goal,
        daily_calorie_target: 2000,
        daily_protein_target_g: 150,
        daily_fat_target_g: 65,
        daily_carbs_target_g: 200,
        items: [
          {
            id: 'item-1',
            day: 1,
            meal_time: 'breakfast',
            calories: 420,
            protein_g: 20,
            fat_g: 14,
            carbs_g: 55,
            name: 'Oatmeal with Banana and Honey',
            description: 'Rolled oats with banana and honey.',
          },
        ],
        disclaimer: disclaimerFixture.en.standard,
        created_at: new Date().toISOString(),
      },
      { status: 201 },
    );
  }),

  http.get(`${API_BASE_URL}/adherence`, () => HttpResponse.json([])),

  http.post(`${API_BASE_URL}/adherence`, () => HttpResponse.json({ checked: true })),

  http.delete(`${API_BASE_URL}/adherence`, () => HttpResponse.json({ checked: false })),
];
