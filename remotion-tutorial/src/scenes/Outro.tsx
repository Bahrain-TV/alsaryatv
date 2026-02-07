import React from 'react';
import {Caption} from '../components/Caption';
import {Frame} from '../components/Frame';

export const Outro: React.FC = () => {
  return (
    <Frame>
      <div style={{display: 'flex', flexDirection: 'column', gap: 28}}>
        <div style={{fontSize: 56, fontWeight: 800, color: '#fde68a'}}>
          شكرا
        </div>
        <Caption
          ar="جاهزين للتصوير النهائي بصيغة MP4"
          en="Ready to render the final MP4"
        />
        <div style={{fontSize: 20, opacity: 0.7}}>
          remotion render TutorialVideo out/tutorial.mp4
        </div>
      </div>
    </Frame>
  );
};
