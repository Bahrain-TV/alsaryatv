/**
 * Splash Screen with GSAP Animations, SVG Masking & Particle Effects
 * Creates an Arabic calligraphy-style reveal animation with particles and effects
 */

import gsap from 'gsap';

class SplashScreen {
  constructor(options = {}) {
    this.duration = options.duration || 4; // Total animation duration in seconds
    this.logoSrc = options.logoSrc || '/images/bahrain-tv-sm.png';
    this.container = options.container || 'splash-screen-container';
    this.particleCount = options.particleCount || 150;
    this.timeline = null;
    this.particles = [];
  }

  /**
   * Initialize the splash screen
   */
  init() {
    this.createSplashHTML();
    this.setupSVGMask();
    this.createParticleSystem();
    this.setupAnimations();

    return this.timeline;
  }

  /**
   * Create the main splash screen HTML structure
   */
  createSplashHTML() {
    const container = document.getElementById(this.container);
    if (!container) {
      console.warn(`Splash screen container #${this.container} not found`);
      return;
    }

    container.innerHTML = `
      <div class="splash-screen-wrapper">
        <!-- Background -->
        <div class="splash-bg"></div>

        <!-- SVG Defs for Masking -->
        <svg style="position: absolute; width: 0; height: 0;">
          <defs>
            <!-- Calligraphy mask for the logo -->
            <mask id="calligraphy-mask" x="0" y="0" width="100%" height="100%">
              <rect width="100%" height="100%" fill="white" />
              <rect id="reveal-rect" x="0" y="0" width="0" height="100%" fill="black" />
            </mask>

            <!-- Glow filter -->
            <filter id="glow-filter">
              <feGaussianBlur stdDeviation="4" result="coloredBlur" />
              <feMerge>
                <feMergeNode in="coloredBlur" />
                <feMergeNode in="SourceGraphic" />
              </feMerge>
            </filter>

            <!-- Shimmer filter -->
            <filter id="shimmer-filter">
              <feGaussianBlur in="SourceGraphic" stdDeviation="2" />
              <feComponentTransfer>
                <feFuncA type="linear" slope="0.8" />
              </feComponentTransfer>
            </filter>

            <!-- Particle blur -->
            <filter id="particle-blur">
              <feGaussianBlur in="SourceGraphic" stdDeviation="1.5" />
            </filter>
          </defs>
        </svg>

        <!-- Logo Container with Mask -->
        <div class="logo-container">
          <div class="logo-wrapper">
            <img
              id="splash-logo"
              class="splash-logo"
              src="${this.logoSrc}"
              alt="Logo"
              style="mask-image: url(#calligraphy-mask); -webkit-mask-image: url(#calligraphy-mask);"
            />
            <!-- Glow effect layer -->
            <div class="logo-glow"></div>
          </div>
        </div>

        <!-- Particle container -->
        <div class="particle-container" id="particle-container"></div>

        <!-- Shimmer overlay -->
        <div class="shimmer-overlay"></div>

        <!-- Light rays effect -->
        <div class="light-rays">
          <div class="light-ray ray-1"></div>
          <div class="light-ray ray-2"></div>
          <div class="light-ray ray-3"></div>
        </div>

        <!-- Distortion waves -->
        <svg class="distortion-waves" viewBox="0 0 1200 100" preserveAspectRatio="none">
          <defs>
            <filter id="wave-distortion">
              <feTurbulence type="fractalNoise" baseFrequency="0.02" numOctaves="3" result="noise" />
              <feDisplacementMap in="SourceGraphic" in2="noise" scale="15" />
            </filter>
          </defs>
          <path class="wave-path" d="M0,50 Q300,30 600,50 T1200,50" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="2" filter="url(#wave-distortion)" />
        </svg>

        <!-- Text fadeout -->
        <div class="splash-text">
          <p id="splash-text-main">تلفزيون البحرين</p>
          <p id="splash-text-sub">Bahrain TV</p>
        </div>
      </div>
    `;
  }

  /**
   * Setup SVG mask for calligraphy effect
   */
  setupSVGMask() {
    const mask = document.getElementById('calligraphy-mask');
    if (!mask) return;

    // Create multiple mask paths to simulate calligraphy strokes
    const svg = mask.closest('svg');
    const defs = svg.querySelector('defs');

    // Add animated stroke masks
    const strokeMasks = [
      '<mask id="stroke-1"><rect width="100%" height="100%" fill="white" /><circle cx="30%" cy="50%" r="20%" fill="black" /></mask>',
      '<mask id="stroke-2"><rect width="100%" height="100%" fill="white" /><ellipse cx="70%" cy="40%" rx="25%" ry="35%" fill="black" /></mask>',
      '<mask id="stroke-3"><rect width="100%" height="100%" fill="white" /><path d="M 0,30 Q 50,10 100,30" stroke="black" stroke-width="30" fill="none" /></mask>'
    ];

    strokeMasks.forEach(mask => {
      const temp = document.createElement('div');
      temp.innerHTML = mask;
      defs.appendChild(temp.firstElementChild);
    });
  }

  /**
   * Create particle system
   */
  createParticleSystem() {
    const container = document.getElementById('particle-container');
    if (!container) return;

    const particleFragment = document.createDocumentFragment();

    for (let i = 0; i < this.particleCount; i++) {
      const particle = document.createElement('div');
      particle.className = 'particle';

      const size = Math.random() * 4 + 1;
      const delay = Math.random() * 0.5;
      const duration = Math.random() * 1.5 + 1;
      const startX = Math.random() * 100;
      const startY = Math.random() * 100;

      particle.style.cssText = `
        position: absolute;
        width: ${size}px;
        height: ${size}px;
        background: radial-gradient(circle, rgba(255,255,255,1) 0%, rgba(255,255,255,0) 70%);
        border-radius: 50%;
        left: ${startX}%;
        top: ${startY}%;
        opacity: 0;
        box-shadow: 0 0 ${size * 2}px rgba(255,255,255,0.8);
        filter: url(#particle-blur);
      `;

      this.particles.push({
        element: particle,
        delay,
        duration,
        startX,
        startY
      });

      particleFragment.appendChild(particle);
    }

    container.appendChild(particleFragment);
  }

  /**
   * Setup all animations
   */
  setupAnimations() {
    this.timeline = gsap.timeline({
      onComplete: () => {
        this.onAnimationComplete();
      }
    });

    const staggerDuration = this.duration * 0.6; // 60% of total time for reveal
    const particleDuration = this.duration * 0.8; // 80% of total time for particles

    // 1. Background fade in
    this.timeline.to('.splash-bg', {
      opacity: 0.9,
      duration: 0.3,
      ease: 'power2.inOut'
    }, 0);

    // 2. Calligraphy reveal animation using mask
    this.timeline.to(
      '#reveal-rect',
      {
        attr: { width: '100%' },
        duration: staggerDuration,
        ease: 'power1.inOut'
      },
      0.1
    );

    // 3. Logo scale and rotate entrance
    this.timeline.to(
      '.logo-container',
      {
        opacity: 1,
        scale: 1,
        duration: staggerDuration,
        ease: 'back.out'
      },
      0.1
    );

    // 4. Glow effect pulsing
    this.timeline.to(
      '.logo-glow',
      {
        boxShadow: '0 0 40px rgba(255,255,255,0.8), 0 0 80px rgba(99,102,241,0.6)',
        opacity: 1,
        duration: 0.4,
        ease: 'power2.inOut'
      },
      0.3
    );

    // 5. Glow pulse repeat
    this.timeline.to(
      '.logo-glow',
      {
        boxShadow: '0 0 20px rgba(255,255,255,0.4), 0 0 40px rgba(99,102,241,0.3)',
        duration: 0.6,
        ease: 'sine.inOut',
        repeat: -1
      },
      0.3
    );

    // 6. Particle burst animation
    this.particles.forEach((p, index) => {
      const angle = (index / this.particleCount) * Math.PI * 2;
      const velocity = 150 + Math.random() * 200;
      const endX = Math.cos(angle) * velocity;
      const endY = Math.sin(angle) * velocity;

      this.timeline.to(
        p.element,
        {
          x: endX,
          y: endY,
          opacity: 0,
          duration: p.duration,
          ease: 'power2.out'
        },
        p.delay
      );
    });

    // 7. Shimmer overlay effect
    this.timeline.to(
      '.shimmer-overlay',
      {
        opacity: 0.5,
        duration: 0.4,
        ease: 'power2.inOut'
      },
      0.2
    );

    this.timeline.to(
      '.shimmer-overlay',
      {
        x: 1000,
        opacity: 0,
        duration: 0.8,
        ease: 'power2.inOut'
      },
      0.4
    );

    // 8. Light rays animation
    this.timeline.to(
      '.light-rays',
      {
        opacity: 0.6,
        duration: 0.5
      },
      0.1
    );

    this.timeline.to(
      ['.ray-1', '.ray-2', '.ray-3'],
      {
        rotation: 360,
        duration: 2,
        ease: 'none',
        repeat: 1
      },
      0.2
    );

    // 9. Wave distortion animation
    this.timeline.to(
      '.wave-path',
      {
        attr: { d: 'M0,50 Q300,20 600,50 T1200,50' },
        duration: 0.6,
        ease: 'sine.inOut'
      },
      0.3
    );

    this.timeline.to(
      '.wave-path',
      {
        attr: { d: 'M0,50 Q300,70 600,50 T1200,50' },
        duration: 0.6,
        ease: 'sine.inOut'
      },
      0.9
    );

    // 10. Text entrance with stagger
    this.timeline.from(
      '#splash-text-main, #splash-text-sub',
      {
        opacity: 0,
        y: 20,
        duration: 0.4,
        stagger: 0.2,
        ease: 'power2.out'
      },
      0.5
    );

    // 11. Final fade out
    this.timeline.to(
      '.splash-screen-wrapper',
      {
        opacity: 0,
        duration: 0.8,
        ease: 'power2.inOut',
        delay: this.duration - 1.2
      }
    );
  }

  /**
   * Callback when animation completes
   */
  onAnimationComplete() {
    const container = document.getElementById(this.container);
    if (container) {
      // Remove the splash screen from DOM
      container.style.pointerEvents = 'none';
      gsap.to(container, {
        opacity: 0,
        duration: 0.5,
        onComplete: () => {
          container.innerHTML = '';
          container.style.display = 'none';
        }
      });
    }

    // Dispatch custom event for parent to handle
    document.dispatchEvent(new CustomEvent('splashScreenComplete'));
  }

  /**
   * Destroy the splash screen
   */
  destroy() {
    if (this.timeline) {
      this.timeline.kill();
    }
    const container = document.getElementById(this.container);
    if (container) {
      container.innerHTML = '';
    }
  }
}

// Export for use in other scripts
window.SplashScreen = SplashScreen;

export default SplashScreen;
