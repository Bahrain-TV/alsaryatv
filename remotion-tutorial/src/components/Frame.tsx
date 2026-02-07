import React from 'react';

export const Frame: React.FC<{children: React.ReactNode}> = ({children}) => {
  return (
    <div
      style={{
        width: '100%',
        height: '100%',
        background:
          'radial-gradient(circle at 20% 10%, rgba(56,189,248,0.12), transparent 45%), radial-gradient(circle at 80% 0%, rgba(168,85,247,0.15), transparent 45%), linear-gradient(180deg, #0b1220 0%, #0f172a 100%)',
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        fontFamily: 'Tajawal, Changa, sans-serif',
        color: '#f8fafc',
      }}
    >
      <div
        style={{
          width: '90%',
          height: '86%',
          borderRadius: 32,
          border: '1px solid rgba(148,163,184,0.2)',
          background: 'rgba(15,23,42,0.65)',
          boxShadow: '0 30px 60px rgba(2,6,23,0.45)',
          padding: '48px 56px',
          display: 'flex',
          flexDirection: 'column',
          gap: 32,
        }}
      >
        {children}
      </div>
    </div>
  );
};
