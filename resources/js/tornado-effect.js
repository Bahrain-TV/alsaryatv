/**
 * Tornado Particle Effect
 * Creates a spinning tornado effect with particles during form transitions
 */

import gsap from 'gsap';

export class TornadoEffect {
    constructor(options = {}) {
        this.container = options.container || document.body;
        this.particleCount = options.particleCount || 80;
        this.tornadoDuration = options.tornadoDuration || 750; // Match form flip duration
        this.colors = options.colors || ['#FF8C00', '#1E40AF', '#7C3AED', '#EC4899', '#06B6D4'];
        this.particleSize = options.particleSize || { min: 4, max: 10 };
        this.tornadoRadius = options.tornadoRadius || 120; // Max radius of tornado
        this.centerX = 0;
        this.centerY = 0;
        this.particles = [];
        this.animationFrameId = null;
    }

    /**
     * Initialize tornado effect at a specific position
     */
    initialize(x, y) {
        this.centerX = x;
        this.centerY = y;

        // Create particle container
        this.particleContainer = document.createElement('div');
        this.particleContainer.id = 'tornado-particle-container';
        this.particleContainer.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 9999;
            overflow: hidden;
        `;
        document.body.appendChild(this.particleContainer);

        // Create particles
        this.createParticles();
    }

    /**
     * Create individual particles
     */
    createParticles() {
        for (let i = 0; i < this.particleCount; i++) {
            const size = this.randomRange(this.particleSize.min, this.particleSize.max);
            const hue = Math.random() * 360;

            // Create particle element
            const particle = document.createElement('div');
            particle.className = 'tornado-particle';
            particle.style.cssText = `
                position: fixed;
                width: ${size}px;
                height: ${size}px;
                border-radius: 50%;
                background: hsl(${hue}, 85%, 55%);
                box-shadow: 0 0 ${size * 2}px hsl(${hue}, 85%, 55%), inset 0 0 ${size}px rgba(255, 255, 255, 0.3);
                left: ${this.centerX}px;
                top: ${this.centerY}px;
                opacity: 0.9;
                pointer-events: none;
                will-change: transform, opacity, box-shadow;
            `;

            this.particleContainer.appendChild(particle);

            const particleData = {
                element: particle,
                x: this.centerX,
                y: this.centerY,
                vx: 0,
                vy: 0,
                size,
                hue, // Store hue for dynamic glow updates
                angle: Math.random() * Math.PI * 2, // Starting angle for spiral
                distance: 0, // Starting at center
                maxDistance: this.tornadoRadius,
                lifetime: this.tornadoDuration,
                age: 0
            };

            this.particles.push(particleData);
        }
    }

    /**
     * Animate particles in tornado spiral pattern
     */
    animate() {
        const deltaTime = 16; // ~60fps
        const progress = this.currentTime / this.tornadoDuration;

        // Use GSAP timeline for coordinated animation
        gsap.to({}, {
            duration: this.tornadoDuration / 1000,
            onUpdate: () => {
                const currentProgress = gsap.getProperty({}, 'x') || progress;

                this.particles.forEach((particle, index) => {
                    // Stagger particle emission
                    const particleDelay = (index / this.particleCount) * 200; // 200ms spread
                    const particleProgress = Math.max(0, (this.currentTime - particleDelay) / this.tornadoDuration);

                    if (particleProgress <= 0) {
                        particle.distance = 0;
                    } else {
                        // Create spiral motion: expand outward while rotating
                        // Distance increases over time (tornado expanding)
                        particle.distance = particleProgress * this.tornadoRadius;

                        // Rotation: 2-3 full rotations during animation
                        const rotations = 2.5;
                        particle.angle = (index / this.particleCount * Math.PI * 2) +
                                       (particleProgress * Math.PI * 2 * rotations);
                    }

                    // Calculate new position using spiral formula
                    particle.x = this.centerX + Math.cos(particle.angle) * particle.distance;
                    particle.y = this.centerY + Math.sin(particle.angle) * particle.distance;

                    // Apply wobble/turbulence for organic tornado feel
                    const wobbleAmount = Math.sin(particleProgress * Math.PI * 4 + index) * 15;
                    const wobbleY = Math.cos(particleProgress * Math.PI * 3 + index * 0.5) * 10;

                    particle.x += wobbleAmount;
                    particle.y += wobbleY;

                    // Height variation (some particles go higher)
                    const heightVariation = Math.sin(index / this.particleCount * Math.PI) * 200;

                    // Fade out towards the end
                    const opacity = Math.max(0, 1 - (particleProgress - 0.7) / 0.3);

                    // Update DOM
                    particle.element.style.transform = `
                        translate3d(${particle.x - particle.size / 2}px,
                                   ${particle.y - particle.size / 2 + heightVariation}px,
                                   0)
                        scale(${1 + particleProgress * 0.3})
                    `;
                    particle.element.style.opacity = opacity;
                });
            }
        });
    }

    /**
     * Start the tornado effect
     */
    start() {
        this.currentTime = 0;

        // Use GSAP to orchestrate the animation
        const timeline = gsap.timeline();

        timeline.to({ progress: 0 }, {
            progress: 1,
            duration: this.tornadoDuration / 1000,
            ease: 'linear',
            onUpdate: () => {
                this.currentTime += 16; // Approximate frame time
                this.animateFrame();
            },
            onComplete: () => {
                this.cleanup();
            }
        });

        return timeline;
    }

    /**
     * Animate a single frame
     */
    animateFrame() {
        const progress = this.currentTime / this.tornadoDuration;

        this.particles.forEach((particle, index) => {
            // Stagger particle emission for sequential spiral effect
            const particleDelay = (index / this.particleCount) * 200;
            const particleProgress = Math.max(0, (this.currentTime - particleDelay) / this.tornadoDuration);

            if (particleProgress <= 0) {
                particle.distance = 0;
            } else {
                // Use easing function for more natural spiral expansion
                // Easing: starts slow, accelerates, then slows down near end
                const easeProgress = this.easeInOutQuad(Math.min(particleProgress, 1));
                particle.distance = easeProgress * this.tornadoRadius;

                // Rotation: 2.5 full rotations with acceleration
                const rotations = 2.5;
                particle.angle = (index / this.particleCount * Math.PI * 2) +
                               (particleProgress * Math.PI * 2 * rotations);
            }

            // Calculate position using Archimedean spiral formula
            // r = a + b*θ creates a more realistic tornado spiral
            const spiralIntensity = 0.3;
            const adjustedDistance = particle.distance * (1 + spiralIntensity * particleProgress);

            particle.x = this.centerX + Math.cos(particle.angle) * adjustedDistance;
            particle.y = this.centerY + Math.sin(particle.angle) * adjustedDistance;

            // Multi-layered turbulence for chaotic organic motion
            const wobble1 = Math.sin(particleProgress * Math.PI * 5 + index * 0.2) * 15;
            const wobble2 = Math.cos(particleProgress * Math.PI * 3 + index * 0.5) * 10;
            const wobble3 = Math.sin((particleProgress * particleProgress) * Math.PI * 2 + index * 0.7) * 8;

            particle.x += wobble1 + wobble2 * 0.5;
            particle.y += wobble2 + wobble3 * 0.7;

            // Enhanced vertical displacement: tornado column effect
            // Particles at different angles have different height variations
            const baseHeight = (Math.sin(index / this.particleCount * Math.PI * 2) * 120);
            const riseFall = (particleProgress * 180) - (particleProgress * particleProgress * 80);
            const heightVariation = baseHeight + riseFall;

            // Scale particles: grow as they move outward
            const scale = 1 + (particleProgress * 0.6);

            // Advanced fade: stay bright most of time, fade quickly at end
            let opacity = 0.85;
            if (particleProgress > 0.75) {
                opacity = 0.85 * (1 - (particleProgress - 0.75) / 0.25);
            }

            // Add slight rotation to particles for visual interest
            const rotation = particleProgress * 360 * 2;

            // Update particle visual with enhanced effects
            particle.element.style.transform = `
                translate3d(${particle.x - particle.size / 2}px,
                           ${particle.y - particle.size / 2 + heightVariation}px,
                           0)
                scale(${scale})
                rotate(${rotation}deg)
            `;
            particle.element.style.opacity = opacity;

            // Update glow effect based on progress
            const glowSize = Math.round(particle.size * (1 + particleProgress * 0.3));
            const glowOpacity = 0.4 * opacity;
            particle.element.style.boxShadow = `
                0 0 ${glowSize}px hsla(${particle.hue}, 85%, 55%, ${glowOpacity}),
                inset 0 0 ${particle.size}px rgba(255, 255, 255, ${0.2 * opacity})
            `;
        });
    }

    /**
     * Easing function: ease-in-out-quad
     */
    easeInOutQuad(t) {
        return t < 0.5 ? 2 * t * t : -1 + (4 - 2 * t) * t;
    }

    /**
     * Clean up particles and container
     */
    cleanup() {
        if (this.particleContainer) {
            this.particleContainer.remove();
        }
        this.particles = [];
    }

    /**
     * Utility: random range
     */
    randomRange(min, max) {
        return Math.random() * (max - min) + min;
    }
}
