/* eslint-disable react/no-inline-styles, react/jsx-no-inline-styles */
import React from 'react';
import {Sequence, useCurrentFrame} from '@remotion/core';
import {interpolate, spring} from '@remotion/animation-utils';
import {Caption} from '../components/Caption';
import {Frame} from '../components/Frame';
import {SectionTitle} from '../components/SectionTitle';
import {TimelineBar} from '../components/TimelineBar';

const steps = [
  {
    ar: 'شاشة البداية',
    en: 'Splash screen',
    noteAr: 'هوية رمضانية وحركة تمهيدية',
    noteEn: 'Ramadan identity and intro motion',
  },
  {
    ar: 'صفحة الترحيب',
    en: 'Welcome page',
    noteAr: 'رسائل التهيئة مع العد التنازلي',
    noteEn: 'Messaging with countdown timer',
  },
  {
    ar: 'نموذج التسجيل',
    en: 'Registration form',
    noteAr: 'فردي وعائلي مع تحقق سريع',
    noteEn: 'Individual + family with validation',
  },
  {
    ar: 'شاشة النجاح',
    en: 'Success screen',
    noteAr: 'تعزيز المشاركة وعداد الضغطات',
    noteEn: 'Participation boost with hit counter',
  },
];

export const FrontendScene: React.FC = () => {
  const frame = useCurrentFrame();
  const cardPop = spring({frame, fps: 30, from: 0.95, to: 1, durationInFrames: 35});
  const glow = interpolate(frame, [0, 60], [0.2, 1]);

  return (
    <Frame>
      <div style={{display: 'flex', flexDirection: 'column', gap: 26}}>
        <SectionTitle ar="الواجهة الأمامية" en="Frontend Journey" />

        <div style={{display: 'flex', gap: 24, alignItems: 'center'}}>
          <div style={{flex: 1}}>
            <Caption
              ar="تجربة كاملة تبدأ من الشاشة الافتتاحية وتنتهي برسالة النجاح"
              en="A full experience from splash to confirmation"
              align="right"
            />
          </div>
          <div style={{width: 280}}>
            <TimelineBar label="Flow" progress={Math.min(1, frame / 1800)} />
          </div>
        </div>

        <div
          style={{
            display: 'grid',
            gridTemplateColumns: 'repeat(2, minmax(0, 1fr))',
            gap: 20,
            transform: `scale(${cardPop})`,
          }}
        >
          {steps.map((step, index) => (
            <div
              key={step.en}
              style={{
                background: 'rgba(15,23,42,0.75)',
                borderRadius: 20,
                padding: 22,
                border: '1px solid rgba(148,163,184,0.2)',
                display: 'flex',
                flexDirection: 'column',
                gap: 10,
                minHeight: 160,
                boxShadow: `0 0 30px rgba(251,191,36,${0.08 + glow * 0.08})`,
              }}
            >
              <div style={{fontSize: 18, fontWeight: 700, color: '#fde68a'}}>
                {index + 1}. {step.ar}
              </div>
              <div style={{fontSize: 14, opacity: 0.8}}>{step.en}</div>
              <div style={{fontSize: 14, color: '#cbd5e1'}}>{step.noteAr}</div>
              <div style={{fontSize: 12, opacity: 0.7}}>{step.noteEn}</div>
              <div
                style={{
                  marginTop: 'auto',
                  height: 8,
                  borderRadius: 999,
                  background: 'linear-gradient(90deg, #38bdf8, #a855f7)',
                  opacity: 0.7,
                }}
              />
            </div>
          ))}
        </div>

        <Sequence from={60}>
          <Caption
            ar="التجربة متجاوبة وتعرض الرسائل حسب حالة التسجيل"
            en="Responsive layout with messaging based on registration state"
          />
        </Sequence>
      </div>
    </Frame>
  );
};
