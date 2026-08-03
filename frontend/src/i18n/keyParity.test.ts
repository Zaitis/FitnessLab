import { describe, expect, it } from 'vitest';

const localeFiles = import.meta.glob<{ default: object }>('./locales/*/*.json', { eager: true });

function keys(value: object, prefix = ''): string[] {
  return Object.entries(value).flatMap(([key, nested]) =>
    typeof nested === 'object' && nested !== null
      ? keys(nested as object, `${prefix}${key}.`)
      : [`${prefix}${key}`],
  );
}

function parsePath(path: string): { locale: string; namespace: string } {
  const match = /\.\/locales\/([^/]+)\/([^/]+)\.json$/.exec(path);

  if (!match) {
    throw new Error(`Unexpected locale file path: ${path}`);
  }

  return { locale: match[1], namespace: match[2] };
}

const byNamespace = new Map<string, Record<string, object>>();

for (const [path, mod] of Object.entries(localeFiles)) {
  const { locale, namespace } = parsePath(path);
  const perNamespace = byNamespace.get(namespace) ?? {};
  perNamespace[locale] = mod.default;
  byNamespace.set(namespace, perNamespace);
}

describe('i18n key parity', () => {
  for (const [namespace, byLocale] of byNamespace) {
    const locales = Object.keys(byLocale).sort();
    const [reference, ...rest] = locales;

    it(`exposes the same keys in every locale for the "${namespace}" namespace`, () => {
      const referenceKeys = keys(byLocale[reference]).sort();

      for (const locale of rest) {
        expect(keys(byLocale[locale]).sort()).toEqual(referenceKeys);
      }
    });
  }
});
