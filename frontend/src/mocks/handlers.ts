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
];
