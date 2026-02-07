import React from 'react';
import {Sequence, useCurrentFrame} from '@remotion/core';
import {spring} from '@remotion/animation-utils';
import {Caption} from '../components/Caption';
import {Frame} from '../components/Frame';

export const Intro: React.FC<{titleAr: string; titleEn: string}> = ({
  titleAr,
  titleEn,
}) => {
  const frame = useCurrentFrame();
  const titleY = spring({frame, fps: 30, from: 40, to: 0, durationInFrames: 30});
  const opacity = Math.min(1, frame / 20);

  return (
    <Frame>
      <div style={{display: 'flex', flexDirection: 'column', gap: 32}}>
        <div style={{transform: `translateY(${titleY}px)`, opacity}}>
          <div style={{fontSize: 64, fontWeight: 800, color: '#fde68a'}}>
            {titleAr}
          </div>
          <div style={{fontSize: 30, fontWeight: 500, color: '#e2e8f0'}}>
            {titleEn}
          </div>
        </div>
        <Sequence from={20}>
          <Caption
            ar="جولة سريعة على لوحة التحكم والتسجيل والصيانة"
            en="A guided tour of the dashboard, registration, and maintenance"
          />
        </Sequence>
      </div>
    </Frame>
  );
};
