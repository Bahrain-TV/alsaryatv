import {Composition} from '@remotion/core';
import {TutorialVideo} from './scenes/TutorialVideo';

export const Root = () => {
  return (
    <>
      <Composition
        id="TutorialVideo"
        component={TutorialVideo}
        durationInFrames={5400}
        fps={30}
        width={1920}
        height={1080}
        defaultProps={{
          titleAr: 'دليل السارية',
          titleEn: 'AlSarya TV Tutorial',
        }}
      />
    </>
  );
};
