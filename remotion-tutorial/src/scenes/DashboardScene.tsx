import React from 'react';
import {Sequence, useCurrentFrame} from '@remotion/core';
import {interpolate, spring} from '@remotion/animation-utils';
import {Caption} from '../components/Caption';
import {Frame} from '../components/Frame';
import {SectionTitle} from '../components/SectionTitle';

export const DashboardScene: React.FC = () => {
  const frame = useCurrentFrame();
  const scale = spring({frame, fps: 30, from: 0.96, to: 1, durationInFrames: 40});
  const glow = interpolate(frame, [0, 60], [0, 1]);

  return (
    <Frame>
      <div style={{display: 'flex', flexDirection: 'column', gap: 28}}>
        <SectionTitle ar="لوحة التحكم" en="Dashboard" />

        <div
          style={{
            display: 'grid',
            gridTemplateColumns: 'repeat(4, minmax(0, 1fr))',
            gap: 16,
            transform: `scale(${scale})`,
          }}
        >
          {['إجمالي المتصلين', 'الفائزون', 'العائلات', 'الضغطات'].map((label, index) => (
            <div
              key={label}
              style={{
                background: 'linear-gradient(145deg, rgba(30,41,59,0.9), rgba(15,23,42,0.9))',
                borderRadius: 20,
                padding: 20,
                borderTop: `4px solid ${['#fbbf24', '#34d399', '#a855f7', '#fb7185'][index]}`,
                color: '#f8fafc',
              }}
            >
              <div style={{fontSize: 14, letterSpacing: 1, textTransform: 'uppercase'}}>
                {label}
              </div>
              <div style={{fontSize: 32, fontWeight: 800, marginTop: 12}}>
                {['2,430', '48', '410', '9,884'][index]}
              </div>
            </div>
          ))}
        </div>

        <div
          style={{
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'space-between',
            gap: 20,
          }}
        >
          <div style={{flex: 1}}>
            <Caption
              ar="ابحث وفلتر بسرعة، وتابع كل حالة مباشرة"
              en="Search, filter, and track every caller instantly"
              align="right"
            />
          </div>
          <div
            style={{
              background: 'rgba(15,23,42,0.7)',
              borderRadius: 20,
              padding: 18,
              border: '1px solid rgba(148,163,184,0.2)',
              boxShadow: `0 0 40px rgba(251,191,36,${0.25 + glow * 0.2})`,
            }}
          >
            <div style={{fontSize: 18, fontWeight: 700, color: '#fde68a'}}>
              زر المطاط
            </div>
            <div style={{fontSize: 14, opacity: 0.75, marginTop: 6}}>
              Rubber-style winner selector
            </div>
          </div>
        </div>

        <Sequence from={60}>
          <div
            style={{
              display: 'flex',
              justifyContent: 'center',
            }}
          >
            <div
              style={{
                background: 'linear-gradient(160deg, #fbbf24 0%, #f59e0b 35%, #d97706 100%)',
                color: '#0f172a',
                fontWeight: 900,
                fontSize: 28,
                padding: '16px 40px',
                borderRadius: 999,
                border: '2px solid rgba(255,255,255,0.3)',
                boxShadow:
                  'inset 0 -6px 12px rgba(124,45,18,0.35), inset 0 4px 8px rgba(255,255,255,0.25), 0 14px 30px rgba(245,158,11,0.4)',
              }}
            >
              اختيار الفائز العشوائي
            </div>
          </div>
        </Sequence>
      </div>
    </Frame>
  );
};
