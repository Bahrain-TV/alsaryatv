<style>
    @import url('https://fonts.googleapis.com/css2?family=Tajawal:wght@200;300;400;500;700;800;900&display=swap');

    .obs-overlay-wrap {
        position: fixed;
        left: 50%;
        bottom: 10vh;
        transform: translateX(-50%);
        width: min(90vw, 100%);
        padding: 0 1.5rem;
        z-index: 50;
    }

    .obs-overlay-float {
        will-change: transform;
    }

    .obs-overlay-panel {
        position: relative;
        overflow: visible;
        font-family: 'Tajawal', sans-serif;
        height: 220px;
        display: flex;
        flex-direction: column;
    }

    .obs-overlay-panel [dir="auto"] {
        font-family: 'Tajawal', sans-serif;
    }

    .obs-overlay-pulse {
        animation: obsOverlayPulse 1.8s ease-in-out infinite;
    }

    .threejs-canvas {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        pointer-events: none;
        z-index: 0;
        border-radius: 1rem;
        opacity: 0.4;
    }

    /* Individual Card Display Container */
    .stat-card-container {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        opacity: 0;
        pointer-events: none;
        z-index: 40;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .stat-card-container.active {
        opacity: 1;
        pointer-events: auto;
    }

    .stat-card-individual {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        width: 100%;
        z-index: 50;
        padding-top: 1rem; 
    }

    .stat-card-individual .card-label {
        font-size: 1.5rem;
        font-weight: 600;
        color: rgba(255, 255, 255, 0.7);
        text-transform: uppercase;
        letter-spacing: 3px;
        margin-bottom: 0.5rem;
    }

    .stat-card-individual .card-value {
        font-size: 5rem;
        font-weight: 900;
        background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        line-height: 1;
        font-variant-numeric: tabular-nums;
        text-shadow: 0 10px 30px rgba(0,0,0,0.5);
    }

    /* Combined Cards Grid */
    .stat-cards-grid {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%) translateY(10px);
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1.5rem;
        width: 90%;
        opacity: 0;
        pointer-events: none;
        z-index: 1;
    }
    .stat-cards-grid.visible {
        opacity: 1;
        pointer-events: auto;
    }

    .stat-cards-grid .stat-card-small {
        padding: 1.5rem;
        border-radius: 0.75rem;
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .stat-card-small .card-label {
        font-size: 0.75rem;
        font-weight: 600;
        color: rgba(255, 255, 255, 0.6);
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 0.75rem;
    }

    .stat-card-small .card-value {
        font-size: 2.5rem;
        font-weight: bold;
        background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        line-height: 1;
        font-variant-numeric: tabular-nums;
    }

    /* Animations */
    @keyframes obsOverlayPulse {
        0%, 100% {
            opacity: 0.7;
            transform: scale(1);
        }
        50% {
            opacity: 1;
            transform: scale(1.25);
        }
    }

</style>

<div wire:poll.2s="refreshStats">
    <div class="obs-overlay-wrap">
        <div class="obs-overlay-float">
            <div class="obs-overlay-panel rounded-2xl border border-white/10 bg-black/70 p-6 shadow-lg backdrop-blur">
                <!-- Ramadan-themed Three.js Background Canvas -->
                <canvas class="threejs-canvas" id="obs-ramadan-canvas"></canvas>

                <!-- Content wrapper -->
                <div style="position: relative; z-index: 1;">
                    <!-- Header -->
                    <div class="flex flex-wrap items-center justify-between gap-3 mb-3">
                        <div class="flex items-center gap-2">
                            <span class="obs-overlay-pulse inline-flex h-2 w-2 rounded-full bg-emerald-400"></span>
                            <p class="text-xs font-semibold text-white">{{ __('Live statistics', [], 'en') }}</p>
                        </div>
                        <span class="text-xs text-white/70">{{ __('Updated') }} {{ $lastUpdatedAt }}</span>
                    </div>

                    <!-- Individual Card Display Area -->
                    <div class="stat-card-container" id="cardContainer">
                        <div class="stat-card-individual">
                            <div class="card-label" id="cardLabel">Total Callers</div>
                            <div class="card-value" id="cardValue">0</div>
                        </div>
                    </div>

                    <!-- Combined Cards Grid -->
                    <div class="stat-cards-grid" id="cardsGrid">
                        <div class="rounded-xl bg-white/5 p-4 stat-card-small">
                            <p class="card-label">{{ __('Total Callers', [], 'en') }}</p>
                            <p class="card-value">{{ number_format($totalCallers) }}</p>
                        </div>
                        <div class="rounded-xl bg-white/5 p-4 stat-card-small">
                            <p class="card-label">{{ __('Today Callers', [], 'en') }}</p>
                            <p class="card-value">{{ number_format($todayCallers) }}</p>
                        </div>
                        <div class="rounded-xl bg-white/5 p-4 stat-card-small">
                            <p class="card-label">{{ __('Total Hits', [], 'en') }}</p>
                            <p class="card-value">{{ number_format($totalHits) }}</p>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="mt-2 flex justify-between items-center text-xs text-white/70">
                        <div class="text-xs">
                            <span>{{ __('Win ratio', [], 'en') }}</span>:
                            <span>{{ $winRatio }}%</span>
                        </div>
                        <div class="text-xs text-white/50 font-mono">
                            v{{ config('alsarya.version', '1.0.0') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
<script>
// ============================================
// SPRING PHYSICS IMPLEMENTATION
// ============================================
class SpringValue {
    constructor(initialValue = 0, stiffness = 120, damping = 15) {
        this.position = initialValue;
        this.velocity = 0;
        this.target = initialValue;
        this.stiffness = stiffness;
        this.damping = damping;
    }

    setTarget(value) {
        this.target = value;
    }

    update(deltaTime) {
        const displacement = this.position - this.target;
        const springForce = -this.stiffness * displacement;
        const dampingForce = -this.damping * this.velocity;
        const acceleration = (springForce + dampingForce) / 1;

        this.velocity += acceleration * deltaTime;
        this.position += this.velocity * deltaTime;

        return this.position;
    }

    getValue() {
        return this.position;
    }
}

// ============================================
// OBS STATS CARD ANIMATION WITH THREE.JS
// ============================================
document.addEventListener('DOMContentLoaded', () => {
    const cardContainer = document.getElementById('cardContainer');
    const cardLabel = document.getElementById('cardLabel');
    const cardValue = document.getElementById('cardValue');
    const cardsGrid = document.getElementById('cardsGrid');
    const canvas = document.getElementById('obs-ramadan-canvas');

    // Spring physics for number animation
    const numberSpring = new SpringValue(0, 150, 20);
    let currentDisplayValue = 0;

    // Card data
    const cards = [
        {
            label: '{{ __("Total Callers", [], "en") }}',
            value: {{ $totalCallers }},
            delay: 0,
            duration: 2000
        },
        {
            label: '{{ __("Today Callers", [], "en") }}',
            value: {{ $todayCallers }},
            delay: 2200,
            duration: 2000
        },
        {
            label: '{{ __("Total Hits", [], "en") }}',
            value: {{ $totalHits }},
            delay: 4400,
            duration: 2000
        }
    ];

    // Format number with thousand separators
    function formatNumber(num) {
        return Math.floor(num).toLocaleString('en-US');
    }

    // Animate card entrance/exit with GSAP
    function animateCardEnter(card, callback) {
        numberSpring.setTarget(0);
        cardLabel.textContent = card.label;
        currentDisplayValue = 0;

        gsap.timeline()
            .to(cardContainer, {
                scale: 1,
                opacity: 1,
                duration: 0.8,
                ease: 'back.out'
            }, 0)
            .to(cardLabel, {
                y: 0,
                opacity: 1,
                duration: 0.6,
                ease: 'power2.out'
            }, 0.1)
            .to(cardValue, {
                y: 0,
                opacity: 1,
                duration: 0.6,
                ease: 'power2.out'
            }, 0.2)
            .call(() => {
                // Start spring animation for numbers
                numberSpring.setTarget(card.value);
            }, null, 0.4)
            .to({}, { duration: card.duration / 1000 }, 0)
            .call(() => {
                callback();
            });
    }

    function animateCardExit() {
        return gsap.to(cardContainer, {
            scale: 0.5,
            opacity: 0,
            duration: 0.8,
            ease: 'back.in'
        });
    }

    function showGridCards() {
        gsap.to(cardsGrid, {
            opacity: 1,
            duration: 0.6,
            ease: 'power2.out'
        });
    }

    function hideGridCards() {
        gsap.to(cardsGrid, {
            opacity: 0,
            duration: 0.6,
            ease: 'power2.in'
        });
    }

    // Main animation sequence
    function runAnimationSequence() {
        const timeline = gsap.timeline();

        // Card 1
        timeline.call(() => {
            cardContainer.classList.add('active');
            animateCardEnter(cards[0], () => {});
        }, null, 0);

        // Card 2
        timeline.call(() => {
            animateCardExit().then(() => {
                animateCardEnter(cards[1], () => {});
            });
        }, null, 2);

        // Card 3
        timeline.call(() => {
            animateCardExit().then(() => {
                animateCardEnter(cards[2], () => {});
            });
        }, null, 4);

        // Show grid
        timeline.call(() => {
            animateCardExit();
            setTimeout(() => {
                cardContainer.classList.remove('active');
                cardsGrid.classList.add('visible');
                showGridCards();
            }, 800);
        }, null, 6.4);

        // Wait and restart
        timeline.to({}, { duration: 8 }, 6.6);
        timeline.call(() => {
            cardsGrid.classList.remove('visible');
            hideGridCards();
            runAnimationSequence();
        });
    }

    // Animation loop for spring physics
    let animationFrameId = null;
    function updateSpringAnimation() {
        const deltaTime = 0.016; // 60fps
        const newValue = numberSpring.update(deltaTime);

        if (Math.abs(newValue - currentDisplayValue) > 0.1) {
            currentDisplayValue = newValue;
            cardValue.textContent = formatNumber(currentDisplayValue);
        }

        animationFrameId = requestAnimationFrame(updateSpringAnimation);
    }

    // Start animations
    updateSpringAnimation();
    runAnimationSequence();

    // ============================================
    // ENHANCED THREE.JS RAMADAN BACKGROUND
    // ============================================
    if (!canvas || !window.THREE) return;

    const scene = new THREE.Scene();
    const panelRect = canvas.parentElement.getBoundingClientRect();
    const camera = new THREE.PerspectiveCamera(60, panelRect.width / panelRect.height, 0.1, 1000);
    const renderer = new THREE.WebGLRenderer({
        canvas: canvas,
        alpha: true,
        antialias: true
    });

    renderer.setSize(panelRect.width, panelRect.height);
    renderer.setClearColor(0x000000, 0);
    camera.position.set(0, 0, 8);

    // Color palette
    const COLORS = {
        gold: 0xC59D5F,
        deepRed: 0xA81C2E,
        emerald: 0x10B981,
        lightGold: 0xF5DEB3,
        white: 0xFFFFFF
    };

    // Enhanced Lantern with spring animation
    function createEnhancedLantern(scale = 1) {
        const lantern = new THREE.Group();
        lantern.userData = {
            floatSpeed: 0.3 + Math.random() * 0.3,
            floatOffset: Math.random() * Math.PI * 2,
            rotationSpeed: 0.001 + Math.random() * 0.002,
            pulseSpeed: 1 + Math.random() * 1,
            originalScale: scale
        };

        // Top dome
        const domeGeometry = new THREE.SphereGeometry(0.4 * scale, 16, 16, 0, Math.PI * 2, 0, Math.PI / 2);
        const domeMaterial = new THREE.MeshBasicMaterial({
            color: COLORS.gold,
            transparent: true,
            opacity: 0.6
        });
        const dome = new THREE.Mesh(domeGeometry, domeMaterial);
        dome.position.y = 0.8 * scale;
        lantern.add(dome);

        // Body
        const bodyShape = new THREE.Shape();
        const sides = 6;
        const radius = 0.5 * scale;
        for (let i = 0; i < sides; i++) {
            const angle = (i / sides) * Math.PI * 2;
            const x = Math.cos(angle) * radius;
            const y = Math.sin(angle) * radius;
            if (i === 0) bodyShape.moveTo(x, y);
            else bodyShape.lineTo(x, y);
        }
        bodyShape.closePath();

        const extrudeSettings = { depth: 1.2 * scale, bevelEnabled: false };
        const bodyGeometry = new THREE.ExtrudeGeometry(bodyShape, extrudeSettings);
        const bodyMaterial = new THREE.MeshBasicMaterial({
            color: COLORS.lightGold,
            transparent: true,
            opacity: 0.3,
            side: THREE.DoubleSide
        });
        const body = new THREE.Mesh(bodyGeometry, bodyMaterial);
        body.rotation.x = Math.PI / 2;
        lantern.add(body);

        // Glow core
        const glowGeometry = new THREE.SphereGeometry(0.3 * scale, 16, 16);
        const glowMaterial = new THREE.MeshBasicMaterial({
            color: COLORS.gold,
            transparent: true,
            opacity: 0.8
        });
        const glow = new THREE.Mesh(glowGeometry, glowMaterial);
        glow.position.y = 0.5 * scale;
        glow.userData.originalOpacity = 0.8;
        lantern.add(glow);

        // Light rays
        for (let i = 0; i < 8; i++) {
            const angle = (i / 8) * Math.PI * 2;
            const rayGeometry = new THREE.BufferGeometry();
            const rayVertices = new Float32Array([
                0, 0.5 * scale, 0,
                Math.cos(angle) * 2 * scale, 0.5 * scale + Math.sin(angle) * 0.5, Math.sin(angle) * 2 * scale
            ]);
            rayGeometry.setAttribute('position', new THREE.BufferAttribute(rayVertices, 3));
            const rayMaterial = new THREE.LineBasicMaterial({
                color: COLORS.gold,
                transparent: true,
                opacity: 0.2
            });
            const ray = new THREE.Line(rayGeometry, rayMaterial);
            lantern.add(ray);
        }

        // Bottom ring
        const ringGeometry = new THREE.TorusGeometry(0.3 * scale, 0.05 * scale, 8, 16);
        const ringMaterial = new THREE.MeshBasicMaterial({
            color: COLORS.deepRed,
            transparent: true,
            opacity: 0.7
        });
        const ring = new THREE.Mesh(ringGeometry, ringMaterial);
        ring.position.y = -0.7 * scale;
        lantern.add(ring);

        return lantern;
    }

    // Create lanterns
    const lanterns = [];
    for (let i = 0; i < 5; i++) {
        const lantern = createEnhancedLantern(0.8 + Math.random() * 0.4);
        lantern.position.set(
            (Math.random() - 0.5) * 30,
            (Math.random() - 0.5) * 20,
            (Math.random() - 0.5) * 15
        );
        scene.add(lantern);
        lanterns.push(lantern);
    }

    // Create Crescent Moon with orbital animation
    function createCrescent() {
        const crescent = new THREE.Group();

        const outerGeometry = new THREE.CircleGeometry(1.5, 32);
        const outerMaterial = new THREE.MeshBasicMaterial({
            color: COLORS.gold,
            transparent: true,
            opacity: 0.8,
            side: THREE.DoubleSide
        });
        const outer = new THREE.Mesh(outerGeometry, outerMaterial);
        crescent.add(outer);

        const innerGeometry = new THREE.CircleGeometry(1.3, 32);
        const innerMaterial = new THREE.MeshBasicMaterial({
            color: 0x000000,
            transparent: true,
            opacity: 1,
            side: THREE.DoubleSide
        });
        const inner = new THREE.Mesh(innerGeometry, innerMaterial);
        inner.position.x = 0.7;
        crescent.add(inner);

        const glowGeometry = new THREE.CircleGeometry(1.8, 32);
        const glowMaterial = new THREE.MeshBasicMaterial({
            color: COLORS.lightGold,
            transparent: true,
            opacity: 0.2,
            side: THREE.DoubleSide
        });
        const glowMesh = new THREE.Mesh(glowGeometry, glowMaterial);
        glowMesh.position.z = -0.1;
        crescent.add(glowMesh);

        return crescent;
    }

    const crescent = createCrescent();
    crescent.position.set(-12, 8, -5);
    crescent.userData.orbitSpeed = 0.002;
    scene.add(crescent);

    // Create enhanced starfield
    const starGeometry = new THREE.BufferGeometry();
    const starCount = 300;
    const starPositions = new Float32Array(starCount * 3);
    const starColors = new Float32Array(starCount * 3);

    for (let i = 0; i < starCount; i++) {
        starPositions[i * 3] = (Math.random() - 0.5) * 50;
        starPositions[i * 3 + 1] = (Math.random() - 0.5) * 30;
        starPositions[i * 3 + 2] = (Math.random() - 0.5) * 30;

        const color = Math.random() > 0.5 ? new THREE.Color(COLORS.gold) : new THREE.Color(COLORS.white);
        starColors[i * 3] = color.r;
        starColors[i * 3 + 1] = color.g;
        starColors[i * 3 + 2] = color.b;
    }

    starGeometry.setAttribute('position', new THREE.BufferAttribute(starPositions, 3));
    starGeometry.setAttribute('color', new THREE.BufferAttribute(starColors, 3));

    const starMaterial = new THREE.PointsMaterial({
        size: 0.15,
        transparent: true,
        opacity: 0.8,
        vertexColors: true,
        blending: THREE.AdditiveBlending,
        sizeAttenuation: true
    });

    const stars = new THREE.Points(starGeometry, starMaterial);
    scene.add(stars);

    // Golden dust particles with physics
    const dustGeometry = new THREE.BufferGeometry();
    const dustCount = 400;
    const dustPositions = new Float32Array(dustCount * 3);
    const dustVelocity = new Float32Array(dustCount * 3);

    for (let i = 0; i < dustCount; i++) {
        dustPositions[i * 3] = (Math.random() - 0.5) * 40;
        dustPositions[i * 3 + 1] = (Math.random() - 0.5) * 25;
        dustPositions[i * 3 + 2] = (Math.random() - 0.5) * 20;

        dustVelocity[i * 3] = (Math.random() - 0.5) * 0.02;
        dustVelocity[i * 3 + 1] = Math.random() * 0.05;
        dustVelocity[i * 3 + 2] = (Math.random() - 0.5) * 0.02;
    }

    dustGeometry.setAttribute('position', new THREE.BufferAttribute(dustPositions, 3));
    dustGeometry.setAttribute('velocity', new THREE.BufferAttribute(dustVelocity, 3));

    const dustMaterial = new THREE.PointsMaterial({
        size: 0.1,
        color: COLORS.lightGold,
        transparent: true,
        opacity: 0.6,
        blending: THREE.AdditiveBlending
    });

    const dust = new THREE.Points(dustGeometry, dustMaterial);
    scene.add(dust);

    // Animation loop
    let time = 0;
    function animate() {
        requestAnimationFrame(animate);
        time += 0.01;

        // Animate lanterns with spring-like bobbing
        lanterns.forEach((lantern, index) => {
            const bobAmount = Math.sin(time * lantern.userData.floatSpeed + lantern.userData.floatOffset) * 0.015;
            lantern.position.y += bobAmount;
            lantern.rotation.y += lantern.userData.rotationSpeed;
            lantern.rotation.x = Math.sin(time * 0.5 + index) * 0.1;

            // Pulsing glow
            const glow = lantern.children.find(child => child.geometry?.type === 'SphereGeometry');
            if (glow) {
                glow.material.opacity = glow.userData.originalOpacity + Math.sin(time * 2 + index) * 0.3;
            }
        });

        // Animate crescent with orbital motion
        crescent.rotation.z += 0.002;
        crescent.position.x = Math.cos(time * crescent.userData.orbitSpeed) * 15 - 12;
        crescent.position.y = Math.sin(time * crescent.userData.orbitSpeed * 0.7) * 8 + 8;

        // Animate stars
        stars.rotation.y += 0.0005;
        const starPos = stars.geometry.attributes.position.array;
        for (let i = 0; i < starCount; i++) {
            starPos[i * 3 + 1] += Math.sin(time * 0.1 + i * 0.1) * 0.005;
        }
        stars.geometry.attributes.position.needsUpdate = true;

        // Animate dust with physics
        const dustPos = dust.geometry.attributes.position.array;
        const dustVel = dust.geometry.attributes.velocity.array;
        for (let i = 0; i < dustCount; i++) {
            dustPos[i * 3] += dustVel[i * 3];
            dustPos[i * 3 + 1] += dustVel[i * 3 + 1];
            dustPos[i * 3 + 2] += dustVel[i * 3 + 2];

            // Bounce particles
            if (dustPos[i * 3 + 1] > 15) dustVel[i * 3 + 1] *= -0.8;
            if (dustPos[i * 3 + 1] < -15) {
                dustPos[i * 3 + 1] = -15;
                dustVel[i * 3 + 1] *= -0.8;
            }

            if (Math.abs(dustPos[i * 3]) > 25) dustVel[i * 3] *= -0.9;
            if (Math.abs(dustPos[i * 3 + 2]) > 25) dustVel[i * 3 + 2] *= -0.9;

            dustVel[i * 3 + 1] -= 0.001; // Gravity
        }
        dust.geometry.attributes.position.needsUpdate = true;
        dust.rotation.y += 0.0008;

        // Subtle camera movement
        camera.position.x = Math.sin(time * 0.05) * 0.5;
        camera.position.y = Math.cos(time * 0.08) * 0.3;
        camera.lookAt(scene.position);

        renderer.render(scene, camera);
    }

    animate();

    // Handle window resize
    window.addEventListener('resize', () => {
        const panelRect = canvas.parentElement.getBoundingClientRect();
        camera.aspect = panelRect.width / panelRect.height;
        camera.updateProjectionMatrix();
        renderer.setSize(panelRect.width, panelRect.height);
    });

    // Cleanup
    window.addEventListener('beforeunload', () => {
        cancelAnimationFrame(animationFrameId);
    });
});
</script>
