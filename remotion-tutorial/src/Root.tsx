import {Composition} from '@remotion/core';
import {TutorialVideo} from './scenes/TutorialVideo';
import {Intro} from './scenes/Intro';
import {DashboardScene} from './scenes/DashboardScene';
import {FrontendScene} from './scenes/FrontendScene';
import {RegistrationScene} from './scenes/RegistrationScene';
import {MaintenanceScene} from './scenes/MaintenanceScene';
import {Outro} from './scenes/Outro';

export const Root = () => {
  return (
    <>
      {/* Main tutorial video composition */}
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
      
      {/* Individual scene compositions for screenshots and previews */}
      <Composition
        id="IntroScene"
        component={Intro}
        durationInFrames={360} // 12 seconds at 30fps
        fps={30}
        width={1920}
        height={1080}
        defaultProps={{
          titleAr: 'دليل السارية',
          titleEn: 'AlSarya TV Tutorial',
        }}
      />
      
      <Composition
        id="DashboardScene"
        component={DashboardScene}
        durationInFrames={2100} // 70 seconds at 30fps
        fps={30}
        width={1920}
        height={1080}
      />
      
      <Composition
        id="FrontendScene"
        component={FrontendScene}
        durationInFrames={2640} // 88 seconds at 30fps
        fps={30}
        width={1920}
        height={1080}
      />
      
      <Composition
        id="RegistrationScene"
        component={RegistrationScene}
        durationInFrames={1350} // 45 seconds at 30fps
        fps={30}
        width={1920}
        height={1080}
      />
      
      <Composition
        id="MaintenanceScene"
        component={MaintenanceScene}
        durationInFrames={900} // 30 seconds at 30fps
        fps={30}
        width={1920}
        height={1080}
      />
      
      <Composition
        id="OutroScene"
        component={Outro}
        durationInFrames={300} // 10 seconds at 30fps
        fps={30}
        width={1920}
        height={1080}
      />
    </>
  );
};
