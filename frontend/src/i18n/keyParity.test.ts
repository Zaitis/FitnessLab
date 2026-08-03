import { describe, expect, it } from 'vitest';
import en from './locales/en/common.json';
import pl from './locales/pl/common.json';

function keys(value: object, prefix = ''): string[] {
  return Object.entries(value).flatMap(([key, nested]) =>
    typeof nested === 'object' && nested !== null
      ? keys(nested, `${prefix}${key}.`)
      : [`${prefix}${key}`],
  );
}

describe('i18n key parity', () => {
  it('exposes the same keys in every supported locale', () => {
    expect(keys(pl).sort()).toEqual(keys(en).sort());
  });
});
