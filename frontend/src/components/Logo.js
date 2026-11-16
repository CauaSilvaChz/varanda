'use client';

import { useId } from 'react';

// Um bloco de cobogó (o tijolo vazado das varandas brasileiras): um quadrado com uma
// estrela vazada no centro e quartos de círculo nos cantos. Repetido, vira a parede.
const COBOGO = 'M0 0H1V1H0Z' +
  'M.5 .2Q.555 .445 .8 .5Q.555 .555 .5 .8Q.445 .555 .2 .5Q.445 .445 .5 .2Z' +
  'M.22 0A.22 .22 0 0 1 0 .22V0Z' +
  'M1 .22A.22 .22 0 0 1 .78 0H1Z' +
  'M0 .78A.22 .22 0 0 1 .22 1H0Z' +
  'M.78 1A.22 .22 0 0 1 1 .78V1Z';

export default function Logo({ size = 30 }) {
  return (
    <span className="logo">
      <svg width={size} height={size} viewBox="0 0 1 1" aria-hidden="true">
        <path d={COBOGO} fill="currentColor" fillRule="evenodd" />
      </svg>
      <span className="logo-name">varanda</span>
    </span>
  );
}

// Parede de cobogó que preenche todo o espaço do elemento pai.
export function CobogoPattern({ color = '#1d6b5e', background = '#e2b13c', size = 48 }) {
  const id = 'cobogo' + useId().replace(/:/g, '');

  return (
    <svg width="100%" height="100%" aria-hidden="true" className="cobogo">
      <defs>
        <pattern id={id} width={size} height={size} patternUnits="userSpaceOnUse">
          <rect width={size} height={size} fill={background} />
          <path d={COBOGO} fill={color} fillRule="evenodd" transform={`scale(${size})`} />
        </pattern>
      </defs>
      <rect width="100%" height="100%" fill={`url(#${id})`} />
    </svg>
  );
}
