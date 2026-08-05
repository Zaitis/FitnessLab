import { useState, type ReactNode } from 'react';

interface LocalizedFieldTabsProps {
  locales: readonly string[];
  renderFields: (locale: string) => ReactNode;
}

/**
 * Shared by ExerciseForm and MealTemplateForm — both admin catalogue forms
 * need a tab per supported language for their translatable fields, and the
 * tab chrome itself doesn't depend on which fields it's showing. The fields
 * for each locale stay with the caller, since those are wired to that
 * form's own react-hook-form register() calls.
 */
export function LocalizedFieldTabs({ locales, renderFields }: LocalizedFieldTabsProps) {
  const [activeLocale, setActiveLocale] = useState<string>(locales[0]);

  return (
    <>
      <div className="flex gap-2 border-b">
        {locales.map((locale) => (
          <button
            key={locale}
            type="button"
            onClick={() => setActiveLocale(locale)}
            className={`px-3 py-1.5 text-sm font-medium ${
              activeLocale === locale
                ? 'border-b-2 border-primary text-foreground'
                : 'text-muted-foreground'
            }`}
          >
            {locale.toUpperCase()}
          </button>
        ))}
      </div>

      {locales.map((locale) => (
        <div key={locale} className={activeLocale === locale ? 'flex flex-col gap-4' : 'hidden'}>
          {renderFields(locale)}
        </div>
      ))}
    </>
  );
}
