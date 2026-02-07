import React from 'react';
import {Sequence} from '@remotion/core';
import {Caption} from '../components/Caption';
import {Frame} from '../components/Frame';
import {SectionTitle} from '../components/SectionTitle';

export const RegistrationScene: React.FC = () => {
  return (
    <Frame>
      <div style={{display: 'flex', flexDirection: 'column', gap: 24}}>
        <SectionTitle ar="رحلة التسجيل" en="Registration Flow" />

        <div
          style={{
            display: 'grid',
            gridTemplateColumns: 'repeat(3, minmax(0, 1fr))',
            gap: 20,
          }}
        >
          {[
            {ar: 'شاشة البداية', en: 'Splash screen'},
            {ar: 'نموذج التسجيل', en: 'Registration form'},
            {ar: 'شاشة النجاح', en: 'Success screen'},
          ].map((step, index) => (
            <div
              key={step.en}
              style={{
                background: 'rgba(15,23,42,0.7)',
                borderRadius: 20,
                padding: 22,
                border: '1px solid rgba(148,163,184,0.2)',
                display: 'flex',
                flexDirection: 'column',
                gap: 12,
              }}
            >
              <div style={{fontSize: 18, fontWeight: 700, color: '#fde68a'}}>
                {index + 1}. {step.ar}
              </div>
              <div style={{fontSize: 14, opacity: 0.8}}>{step.en}</div>
              <div
                style={{
                  height: 120,
                  borderRadius: 16,
                  background:
                    'linear-gradient(135deg, rgba(56,189,248,0.2), rgba(168,85,247,0.2))',
                }}
              />
            </div>
          ))}
        </div>

        <Sequence from={45}>
          <Caption
            ar="المستخدم يرى العد التنازلي أو النموذج حسب فتح التسجيل"
            en="Users see the countdown or form based on registration status"
          />
        </Sequence>
      </div>
    </Frame>
  );
};
