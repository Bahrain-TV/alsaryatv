/* eslint-disable react/no-inline-styles, react/jsx-no-inline-styles */
import React from 'react';

export const SectionTitle: React.FC<{ar: string; en: string}> = ({ar, en}) => {
  return (
    <div style={{display: 'flex', flexDirection: 'column', gap: 8}}>
      <div style={{fontSize: 54, fontWeight: 800, color: '#fde68a'}}>{ar}</div>
      <div style={{fontSize: 28, fontWeight: 500, color: '#e2e8f0'}}>{en}</div>
    </div>
  );
};
