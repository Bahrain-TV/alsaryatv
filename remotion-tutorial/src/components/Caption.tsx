/* eslint-disable react/no-inline-styles, react/jsx-no-inline-styles */
import React from 'react';

export const Caption: React.FC<{
  ar: string;
  en: string;
  align?: 'left' | 'center' | 'right';
}> = ({ar, en, align = 'center'}) => {
  return (
    <div
      style={{
        textAlign: align,
        display: 'flex',
        flexDirection: 'column',
        gap: 6,
        fontSize: 34,
        fontWeight: 600,
        color: '#f8fafc',
        textShadow: '0 12px 24px rgba(2, 6, 23, 0.45)',
      }}
    >
      <span style={{fontSize: 34, fontWeight: 700}}>{ar}</span>
      <span style={{fontSize: 24, opacity: 0.8}}>{en}</span>
    </div>
  );
};
