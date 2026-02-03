/**
 * Thank You Screen with GSAP Animations & Celebration Effects
 * Displays after successful form submission with confetti, particles, and celebratory animations
 */

import gsap from 'gsap';

class ThankYouScreen {
  constructor(options = {}) {
    this.duration = options.duration || 5; // Total animation duration in seconds
    this.message = options.message || 'شكراً لك!';
    this.subtitle = options.subtitle || 'Thank You for Your Participation';
    this.container = options.container || 'thank-you-container';
    this.particleCount = options.particleCount || 200;
    this.confettiCount = options.confettiCount || 100;
    this.type = options.type || 'individual'; // 'individual' or 'family'
    this.timeline = null;
    this.particles = [];
    this.confetti = [];
  }

  /**
   * Initialize the thank you screen
   */
  init() {
    this.createThankYouHTML();
    this.createParticleSystem();
    this.createConfetti();
    this.setupAnimations();

    return this.timeline;
  }

  /**
   * Create the main thank you screen HTML structure
   */
  createThankYouHTML() {
    // Create or get container
    let container = document.getElementById(this.container);
    if (!container) {
      container = document.createElement('div');
      container.id = this.container;
      // ensure it's appended to document.body to avoid being constrained by parent containers
      document.body.appendChild(container);
    }

    // Force full-screen fixed layout at runtime (defensive)
    container.style.position = 'fixed';
    container.style.inset = '0';
    container.style.width = '100vw';
    container.style.height = '100vh';
    container.style.display = 'flex';
    container.style.alignItems = 'center';
    container.style.justifyContent = 'center';
    container.style.zIndex = '9999';

    // Prevent body scroll while thank you screen is visible
    try {
      this._previousBodyOverflow = document.body.style.overflow || '';
      document.body.style.overflow = 'hidden';
    } catch (e) {
      // ignore if running in restricted environment
    }

    container.classList.add('thank-you-visible');

    const accentColor = this.type === 'family' ? '#9A3412' : '#4338CA';
    const accentColorRgb = this.type === 'family' ? '154, 52, 18' : '67, 56, 202';

    container.innerHTML = `
      <div class="thank-you-wrapper">
        <!-- Background -->
        <div class="thank-you-bg" style="background: linear-gradient(135deg, rgba(${accentColorRgb}, 0.1) 0%, rgba(${accentColorRgb}, 0.05) 100%);"></div>

        <!-- SVG Defs for Effects -->
        <svg style="position: absolute; width: 0; height: 0;">
          <defs>
            <!-- Success pulse filter -->
            <filter id="success-glow">
              <feGaussianBlur stdDeviation="3" result="coloredBlur" />
              <feMerge>
                <feMergeNode in="coloredBlur" />
                <feMergeNode in="SourceGraphic" />
              </feMerge>
            </filter>

            <!-- Shimmer effect -->
            <filter id="shimmer">
              <feTurbulence type="fractalNoise" baseFrequency="0.9" numOctaves="4" result="noise" />
              <feDisplacementMap in="SourceGraphic" in2="noise" scale="2" />
            </filter>

            <!-- Particle glow -->
            <filter id="particle-glow">
              <feGaussianBlur in="SourceGraphic" stdDeviation="2" />
            </filter>
          </defs>
        </svg>

        <!-- Particles container -->
        <div class="particle-container" id="thank-you-particles"></div>

        <!-- Confetti container -->
        <div class="confetti-container" id="thank-you-confetti"></div>

        <!-- Success checkmark -->
        <div class="success-checkmark-container">
          <svg class="checkmark" viewBox="0 0 52 52">
            <circle class="checkmark-circle" cx="26" cy="26" r="25" fill="none" />
            <path class="checkmark-check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8" />
          </svg>
        </div>

        <!-- Main message -->
        <div class="thank-you-content">
          <h1 class="thank-you-title" style="color: ${accentColor};">${this.message}</h1>
          <p class="thank-you-subtitle">${this.subtitle}</p>
          <div class="thank-you-details">
            <p class="detail-text">تم تسجيل استدعاؤك بنجاح</p>
            <p class="detail-text">Your call has been successfully registered</p>
          </div>
        </div>

        <!-- Celebration rays -->
        <div class="celebration-rays" style="--accent-color: ${accentColor};">
          <div class="ray ray-1"></div>
          <div class="ray ray-2"></div>
          <div class="ray ray-3"></div>
          <div class="ray ray-4"></div>
        </div>

        <!-- Animated shapes -->
        <div class="animated-shapes">
          <div class="shape shape-1" style="background: ${accentColor};"></div>
          <div class="shape shape-2" style="background: ${accentColor};"></div>
          <div class="shape shape-3" style="background: ${accentColor};"></div>
        </div>

        <!-- Auto-close timer display -->
        <div class="auto-close-timer">
          <span class="timer-text">Closing in <span class="timer-count">5</span>s</span>
        </div>
      </div>
    `;

    // Ensure the container is visible (defensive for CSS specificity overrides)
    container.style.display = 'flex';

    // Click backdrop to dismiss (only when clicking outside the inner wrapper)
    container.addEventListener('click', (e) => {
      if (e.target === container) {
        this.destroy();
      }
    });

    // Escape key to close
    this._escHandler = (e) => {
      if (e.key === 'Escape') {
        this.destroy();
      }
    };
    document.addEventListener('keydown', this._escHandler);
  }

  /**
   * Create particle system for celebration
   */
  createParticleSystem() {
    const container = document.getElementById('thank-you-particles');
    if (!container) return;

    const particleFragment = document.createDocumentFragment();

    for (let i = 0; i < this.particleCount; i++) {
      const particle = document.createElement('div');
      particle.className = 'particle';

      const size = Math.random() * 3 + 1;
      const delay = Math.random() * 0.3;
      const duration = Math.random() * 2 + 1.5;
      const startX = Math.random() * 100;
      const startY = Math.random() * 100;
      const hue = Math.random() * 60; // Color variation

      particle.style.cssText = `
        position: absolute;
        width: ${size}px;
        height: ${size}px;
        background: hsl(${hue}, 100%, 60%);
        border-radius: 50%;
        left: ${startX}%;
        top: ${startY}%;
        opacity: 0;
        box-shadow: 0 0 ${size * 3}px hsl(${hue}, 100%, 60%);
        filter: url(#particle-glow);
        pointer-events: none;
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
   * Create confetti pieces
   */
  createConfetti() {
    const container = document.getElementById('thank-you-confetti');
    if (!container) return;

    const confettiFragment = document.createDocumentFragment();

    for (let i = 0; i < this.confettiCount; i++) {
      const confetti = document.createElement('div');
      confetti.className = 'confetti-piece';

      const size = Math.random() * 6 + 3;
      const delay = Math.random() * 0.5;
      const duration = Math.random() * 1.5 + 2;
      const startX = Math.random() * 100;
      const rotation = Math.random() * 360;
      const colors = ['#FF6B6B', '#4ECDC4', '#45B7D1', '#FFA07A', '#98D8C8'];
      const color = colors[Math.floor(Math.random() * colors.length)];

      confetti.style.cssText = `
        position: absolute;
        width: ${size}px;
        height: ${size}px;
        background: ${color};
        left: ${startX}%;
        top: -10px;
        opacity: 0;
        transform: rotate(${rotation}deg);
        pointer-events: none;
      `;

      this.confetti.push({
        element: confetti,
        delay,
        duration,
        startX,
        rotation
      });

      confettiFragment.appendChild(confetti);
    }

    container.appendChild(confettiFragment);
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

    // 1. Background fade in
    this.timeline.to('.thank-you-bg', {
      opacity: 1,
      duration: 0.5,
      ease: 'power2.inOut'
    }, 0);

    // 2. Checkmark animation (draw effect)
    this.timeline.to(
      '.checkmark-circle',
      {
        strokeDashoffset: 0,
        duration: 0.8,
        ease: 'back.out'
      },
      0.2
    );

    this.timeline.to(
      '.checkmark-check',
      {
        strokeDashoffset: 0,
        duration: 0.6,
        ease: 'back.out'
      },
      0.5
    );

    // 3. Title entrance with scale and fade
    this.timeline.fromTo(
      '.thank-you-title',
      { opacity: 0, scale: 0.5, y: 20 },
      { opacity: 1, scale: 1, y: 0, duration: 0.6, ease: 'back.out' },
      0.3
    );

    // 4. Subtitle entrance
    this.timeline.fromTo(
      '.thank-you-subtitle',
      { opacity: 0, y: 10 },
      { opacity: 1, y: 0, duration: 0.5, ease: 'power2.out' },
      0.6
    );

    // 5. Details entrance with stagger
    this.timeline.fromTo(
      '.detail-text',
      { opacity: 0, x: -20 },
      { opacity: 1, x: 0, duration: 0.4, stagger: 0.15, ease: 'power2.out' },
      0.8
    );

    // 6. Celebration rays animation
    this.timeline.to(
      '.celebration-rays',
      { opacity: 1, duration: 0.5 },
      0.4
    );

    this.timeline.to(
      '.ray',
      { rotation: 360, duration: 3, ease: 'none', repeat: -1 },
      0.4
    );

    // 7. Animated shapes float in
    this.timeline.fromTo(
      '.shape',
      { opacity: 0, scale: 0 },
      { opacity: 0.3, scale: 1, duration: 0.5, stagger: 0.1, ease: 'back.out' },
      0.5
    );

    // 8. Shapes floating animation
    this.timeline.to(
      '.shape-1',
      { y: -30, x: -20, duration: 2, ease: 'sine.inOut', repeat: -1, yoyo: true },
      0.5
    );

    this.timeline.to(
      '.shape-2',
      { y: 30, x: 20, duration: 2.5, ease: 'sine.inOut', repeat: -1, yoyo: true },
      0.5
    );

    this.timeline.to(
      '.shape-3',
      { y: -20, x: 30, duration: 2.2, ease: 'sine.inOut', repeat: -1, yoyo: true },
      0.5
    );

    // 9. Particle burst animation
    this.particles.forEach((p, index) => {
      const angle = (index / this.particleCount) * Math.PI * 2;
      const velocity = 100 + Math.random() * 250;
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

    // 10. Confetti fall animation
    this.confetti.forEach((c, index) => {
      this.timeline.to(
        c.element,
        {
          y: window.innerHeight + 50,
          opacity: 1,
          rotation: c.rotation + 720,
          duration: c.duration,
          ease: 'power1.in'
        },
        c.delay
      );

      // Fade out during fall
      this.timeline.to(
        c.element,
        { opacity: 0, duration: 0.5 },
        c.delay + c.duration - 0.5
      );
    });

    // 11. Timer countdown
    this.timeline.call(() => {
      this.startCountdown();
    }, [], 1);

    // 12. Final fade out
    this.timeline.to(
      '.thank-you-wrapper',
      {
        opacity: 0,
        duration: 0.8,
        ease: 'power2.inOut',
        delay: this.duration - 1.5
      }
    );
  }

  /**
   * Start countdown timer
   */
  startCountdown() {
    const timerCount = document.querySelector('.timer-count');
    if (!timerCount) return;

    let count = Math.floor(this.duration);
    timerCount.textContent = count;

    const interval = setInterval(() => {
      count--;
      if (timerCount) {
        timerCount.textContent = count;
      }
      if (count <= 0) {
        clearInterval(interval);
      }
    }, 1000);
  }

  /**
   * Callback when animation completes
   */
  onAnimationComplete() {
    const container = document.getElementById(this.container);
    if (container) {
      container.style.pointerEvents = 'none';
      gsap.to(container, {
        opacity: 0,
        duration: 0.5,
        onComplete: () => {
          container.innerHTML = '';
          container.style.display = 'none';

          // restore body scroll
          try {
            if (typeof this._previousBodyOverflow !== 'undefined') {
              document.body.style.overflow = this._previousBodyOverflow;
            } else {
              document.body.style.overflow = '';
            }
          } catch (e) {
            // ignore
          }

          // remove esc handler
          try {
            if (this._escHandler) {
              document.removeEventListener('keydown', this._escHandler);
              this._escHandler = null;
            }
          } catch (e) {
            // ignore
          }

          // Dispatch custom event
          document.dispatchEvent(new CustomEvent('thankYouScreenComplete'));
        }
      });
    }
  }

  /**
   * Destroy the thank you screen
   */
  destroy() {
    if (this.timeline) {
      this.timeline.kill();
    }
    const container = document.getElementById(this.container);
    if (container) {
      container.innerHTML = '';
      container.style.display = 'none';
    }

    // restore body scroll
    try {
      if (typeof this._previousBodyOverflow !== 'undefined') {
        document.body.style.overflow = this._previousBodyOverflow;
      } else {
        document.body.style.overflow = '';
      }
    } catch (e) {
      // ignore
    }

    // remove esc handler
    try {
      if (this._escHandler) {
        document.removeEventListener('keydown', this._escHandler);
        this._escHandler = null;
      }
    } catch (e) {
      // ignore
    }
  }
}

// Export for use in other scripts
window.ThankYouScreen = ThankYouScreen;

export default ThankYouScreen;
