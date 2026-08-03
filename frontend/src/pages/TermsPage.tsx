import { useTranslation } from 'react-i18next';

interface TermsSection {
  heading: string;
  paragraphs: string[];
}

export function TermsPage() {
  const { t } = useTranslation('terms');
  const sections = t('sections', { returnObjects: true }) as TermsSection[];

  return (
    <article className="flex flex-col gap-6">
      <header>
        <h1 className="text-2xl font-bold">{t('title')}</h1>
        <p className="text-sm text-muted-foreground">{t('lastUpdated')}</p>
      </header>

      {sections.map((section) => (
        <section key={section.heading} className="flex flex-col gap-2">
          <h2 className="text-lg font-semibold">{section.heading}</h2>
          {section.paragraphs.map((paragraph) => (
            <p key={paragraph} className="text-sm leading-relaxed text-muted-foreground">
              {paragraph}
            </p>
          ))}
        </section>
      ))}
    </article>
  );
}
