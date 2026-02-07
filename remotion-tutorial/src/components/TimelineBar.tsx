/* eslint-disable react/no-inline-styles, react/jsx-no-inline-styles */
import React from 'react';

export const TimelineBar: React.FC<{label: string; progress: number}> = ({
  label,
  progress,
}) => {
  return (
    <div style={{display: 'flex', flexDirection: 'column', gap: 8}}>
      <div style={{fontSize: 14, textTransform: 'uppercase', letterSpacing: 1}}>
        {label}
      </div>
      <div
        style={{
          height: 10,
          borderRadius: 999,
          background: 'rgba(148,163,184,0.2)',
          overflow: 'hidden',
        }}
      >
        <div
          style={{
            width: `${Math.max(0, Math.min(progress, 1)) * 100}%`,
            height: '100%',
            background: 'linear-gradient(90deg, #38bdf8, #a855f7)',
          }}
        />
      </div>
    </div>
  );
};
