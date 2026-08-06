interface CherryLogoProps {
  className?: string;
}

/**
 * The site's mark — two cherries on a shared stem, a nod to "wiśnia"
 * (cherry), matching `public/favicon.svg`. `group-hover:animate-cherry-wiggle`
 * on the wrapper lets any parent link/button trigger the wiggle on hover
 * without this component needing its own hover state.
 */
export function CherryLogo({ className }: CherryLogoProps) {
  return (
    <svg
      viewBox="0 0 40 40"
      aria-hidden
      className={`origin-bottom transition-transform group-hover:animate-cherry-wiggle ${className ?? ''}`}
    >
      <ellipse cx="24" cy="7" rx="6" ry="3" fill="var(--primary)" transform="rotate(-35 24 7)" />
      <path
        d="M20 5 C17 11 14 13 13 18"
        stroke="var(--primary)"
        strokeWidth="2.6"
        fill="none"
        strokeLinecap="round"
      />
      <path
        d="M20 5 C23 10 26 11 27 16"
        stroke="var(--primary)"
        strokeWidth="2.6"
        fill="none"
        strokeLinecap="round"
      />
      <circle cx="12.5" cy="25" r="7.5" fill="#d33f4c" />
      <circle cx="27" cy="23" r="8" fill="#a81e2c" />
      <ellipse
        cx="10"
        cy="21.5"
        rx="1.6"
        ry="1"
        fill="#fff"
        opacity="0.55"
        transform="rotate(-30 10 21.5)"
      />
      <ellipse
        cx="24.5"
        cy="19"
        rx="1.8"
        ry="1.1"
        fill="#fff"
        opacity="0.55"
        transform="rotate(-30 24.5 19)"
      />
    </svg>
  );
}
