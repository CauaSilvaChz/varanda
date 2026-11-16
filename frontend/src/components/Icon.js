// Ícones SVG próprios. Uso: <Icon name="heart" size={20} />
const icons = {
  home: <path d="M3.5 10.5 12 3.5l8.5 7V20a1 1 0 0 1-1 1H15v-6H9v6H4.5a1 1 0 0 1-1-1z" />,
  search: (
    <>
      <circle cx="11" cy="11" r="6.5" />
      <path d="m20 20-4.2-4.2" />
    </>
  ),
  user: (
    <>
      <circle cx="12" cy="8" r="4" />
      <path d="M4.5 20.5c.6-3.9 3.7-6.5 7.5-6.5s6.9 2.6 7.5 6.5" />
    </>
  ),
  logout: (
    <>
      <path d="M14 4h3.5A1.5 1.5 0 0 1 19 5.5v13a1.5 1.5 0 0 1-1.5 1.5H14" />
      <path d="m9.5 16.5-4.5-4.5 4.5-4.5" />
      <path d="M5 12h10" />
    </>
  ),
  image: (
    <>
      <rect x="3.5" y="4.5" width="17" height="15" rx="2.5" />
      <circle cx="9" cy="10" r="1.6" />
      <path d="m20.5 15.5-4.5-4.5-8.5 8.5" />
    </>
  ),
  heart: <path d="M12 20s-7.2-4.4-9-9C1.8 7.9 3.8 5 7 5c2 0 3.6 1.1 5 3 1.4-1.9 3-3 5-3 3.2 0 5.2 2.9 4 6-1.8 4.6-9 9-9 9z" />,
  comment: <path d="M20.5 11.5a8 8 0 0 1-11.7 7.1L4 20l1.4-4.4a8 8 0 1 1 15.1-4.1z" />,
  edit: (
    <>
      <path d="M4 20h4.2L19 9.2 14.8 5 4 15.8z" />
      <path d="m12.8 7 4.2 4.2" />
    </>
  ),
  trash: (
    <>
      <path d="M4.5 7h15" />
      <path d="M9 7V4.5h6V7" />
      <path d="m6.5 7 1 13h9l1-13" />
      <path d="M10.2 11v5.5M13.8 11v5.5" />
    </>
  ),
  close: <path d="M6.5 6.5l11 11M17.5 6.5l-11 11" />,
  back: (
    <>
      <path d="M19 12H5.5" />
      <path d="m11 18-6-6 6-6" />
    </>
  ),
  send: (
    <>
      <path d="M4 12h14" />
      <path d="m13 6.5 5.5 5.5-5.5 5.5" />
    </>
  ),
  camera: (
    <>
      <path d="M4 8.5A1.5 1.5 0 0 1 5.5 7h2.3l1.4-2h5.6l1.4 2h2.3A1.5 1.5 0 0 1 20 8.5v9a1.5 1.5 0 0 1-1.5 1.5h-13A1.5 1.5 0 0 1 4 17.5z" />
      <circle cx="12" cy="12.8" r="3.3" />
    </>
  ),
};

export default function Icon({ name, size = 22, filled = false }) {
  return (
    <svg
      width={size}
      height={size}
      viewBox="0 0 24 24"
      fill={filled ? 'currentColor' : 'none'}
      stroke="currentColor"
      strokeWidth="1.8"
      strokeLinecap="round"
      strokeLinejoin="round"
      aria-hidden="true"
    >
      {icons[name]}
    </svg>
  );
}
