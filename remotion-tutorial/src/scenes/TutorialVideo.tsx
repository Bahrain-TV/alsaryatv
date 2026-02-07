import React from 'react';
import {Sequence, useVideoConfig} from '@remotion/core';
import {Intro} from './Intro';
import {DashboardScene} from './DashboardScene';
import {FrontendScene} from './FrontendScene';
import {Outro} from './Outro';

export const TutorialVideo: React.FC<{titleAr: string; titleEn: string}> = ({
  titleAr,
  titleEn,
}) => {
  const {fps} = useVideoConfig();
  const intro = 12 * fps;
  const frontend = 88 * fps;
  const dashboard = 70 * fps;
  const outro = 10 * fps;

  return (
    <>
      <Sequence from={0} durationInFrames={intro}>
        <Intro titleAr={titleAr} titleEn={titleEn} />
      </Sequence>
      <Sequence from={intro} durationInFrames={frontend}>
        <FrontendScene />
      </Sequence>
      <Sequence from={intro + frontend} durationInFrames={dashboard}>
        <DashboardScene />
      </Sequence>
      <Sequence
        from={intro + frontend + dashboard}
        durationInFrames={outro}
      >
        <Outro />
      </Sequence>
    </>
  );
};
