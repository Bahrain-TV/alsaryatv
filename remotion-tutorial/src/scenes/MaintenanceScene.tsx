import React from 'react';
import {Caption} from '../components/Caption';
import {Frame} from '../components/Frame';
import {SectionTitle} from '../components/SectionTitle';

export const MaintenanceScene: React.FC = () => {
  return (
    <Frame>
      <div style={{display: 'flex', flexDirection: 'column', gap: 24}}>
        <SectionTitle ar="وضع الصيانة" en="Maintenance Mode" />
        <div
          style={{
            display: 'grid',
            gridTemplateColumns: '1.2fr 0.8fr',
            gap: 20,
            alignItems: 'center',
          }}
        >
          <div
            style={{
              background: 'rgba(15,23,42,0.7)',
              borderRadius: 24,
              padding: 24,
              border: '1px solid rgba(148,163,184,0.2)',
            }}
          >
            <Caption
              ar="رسائل مرنة مع عداد زمني وإعادة تحديث تلقائية"
              en="Flexible messaging, countdown timer, and auto refresh"
              align="right"
            />
            <div
              style={{
                marginTop: 18,
                display: 'flex',
                alignItems: 'center',
                gap: 12,
                fontSize: 18,
                color: '#fde68a',
              }}
            >
              <span>⏱️ 40s</span>
              <span style={{fontSize: 14, opacity: 0.8}}>Auto refresh</span>
            </div>
          </div>
          <div
            style={{
              height: 220,
              borderRadius: 20,
              background:
                'linear-gradient(145deg, rgba(251,191,36,0.2), rgba(249,115,22,0.2))',
            }}
          />
        </div>
      </div>
    </Frame>
  );
};
