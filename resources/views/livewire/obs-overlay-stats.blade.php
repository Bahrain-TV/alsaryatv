<style>
    @import url('https://fonts.googleapis.com/css2?family=Tajawal:wght@200;300;400;500;700;800;900&display=swap');

    .obs-overlay-wrap {
        position: fixed;
        left: 50%;
        bottom: 10vh;
        transform: translateX(-50%);
        width: min(64rem, 100%);
        padding: 0 1.5rem;
        z-index: 50;
    }

    .obs-overlay-float {
        animation: obsOverlayFloat 7s ease-in-out infinite 0.8s;
        will-change: transform;
    }

    .obs-overlay-panel {
        animation: obsOverlayEnter 0.7s ease-out both;
        position: relative;
        overflow: hidden;
        font-family: 'Tajawal', sans-serif;
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

    @keyframes obsOverlayEnter {
        0% {
            opacity: 0;
            transform: translateY(18px) scale(0.98);
        }
        100% {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    @keyframes obsOverlayFloat {
        0%,
        100% {
            transform: translateY(0);
        }
        50% {
            transform: translateY(-8px);
        }
    }

    @keyframes obsOverlayPulse {
        0%,
        100% {
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
                <!-- Ramadan-themed Three.js Background Canvas (INSIDE PANEL) -->
                <canvas class="threejs-canvas" id="obs-ramadan-canvas"></canvas>

                <!-- Content wrapper with higher z-index -->
                <div style="position: relative; z-index: 1;">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <span class="obs-overlay-pulse inline-flex h-2.5 w-2.5 rounded-full bg-emerald-400"></span>
                    <p
                        class="text-sm font-semibold text-white"
                        data-obs-text
                        data-obs-en="{{ __('Live dashboard feed', [], 'en') }}"
                        data-obs-ar="{{ __('Live dashboard feed', [], 'ar') }}"
                        dir="auto"
                    >
                        {{ __('Live dashboard feed', [], 'en') }}
                    </p>
                </div>
                <span class="text-xs text-white/70">{{ __('Updated') }} {{ $lastUpdatedAt }}</span>
            </div>

        <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-xl bg-white/5 p-4">
                <p
                    class="text-xs uppercase tracking-wide text-white/60"
                    data-obs-text
                    data-obs-en="{{ __('Total callers', [], 'en') }}"
                    data-obs-ar="{{ __('Total callers', [], 'ar') }}"
                    dir="auto"
                >
                    {{ __('Total callers', [], 'en') }}
                </p>
                <p
                    class="mt-2 text-4xl font-bold text-white"
                    data-obs-text
                    data-obs-value="true"
                    data-obs-en="{{ number_format($totalCallers) }}"
                    dir="auto"
                >
                    {{ number_format($totalCallers) }}
                </p>
            </div>
            <div class="rounded-xl bg-white/5 p-4">
                <p
                    class="text-xs uppercase tracking-wide text-white/60"
                    data-obs-text
                    data-obs-en="{{ __('Today callers', [], 'en') }}"
                    data-obs-ar="{{ __('Today callers', [], 'ar') }}"
                    dir="auto"
                >
                    {{ __('Today callers', [], 'en') }}
                </p>
                <p
                    class="mt-2 text-4xl font-bold text-white"
                    data-obs-text
                    data-obs-value="true"
                    data-obs-en="{{ number_format($todayCallers) }}"
                    dir="auto"
                >
                    {{ number_format($todayCallers) }}
                </p>
            </div>
            <div class="rounded-xl bg-white/5 p-4">
                <p
                    class="text-xs uppercase tracking-wide text-white/60"
                    data-obs-text
                    data-obs-en="{{ __('Total hits', [], 'en') }}"
                    data-obs-ar="{{ __('Total hits', [], 'ar') }}"
                    dir="auto"
                >
                    {{ __('Total hits', [], 'en') }}
                </p>
                <p
                    class="mt-2 text-4xl font-bold text-white"
                    data-obs-text
                    data-obs-value="true"
                    data-obs-en="{{ number_format($totalHits) }}"
                    dir="auto"
                >
                    {{ number_format($totalHits) }}
                </p>
            </div>
        </div>

        <div class="mt-4 flex justify-between items-center text-xs text-white/70">
            <div>
                <span
                    data-obs-text
                    data-obs-en="{{ __('Win ratio', [], 'en') }}"
                    data-obs-ar="{{ __('Win ratio', [], 'ar') }}"
                    dir="auto"
                >
                    {{ __('Win ratio', [], 'en') }}
                </span>
                :
                <span
                    data-obs-text
                    data-obs-value="true"
                    data-obs-en="{{ $winRatio }}%"
                    dir="auto"
                >
                    {{ $winRatio }}%
                </span>
            </div>
            <div class="text-white/50 font-mono">
                v{{ config('alsarya.version', '1.0.0') }}
            </div>
        </div>
                </div>
        </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const canvas = document.getElementById('obs-ramadan-canvas');
    if (!canvas || !window.THREE) return;

    // ============================
    // RAMADAN-THEMED THREE.JS SCENE
    // ============================

    const scene = new THREE.Scene();

    // Get canvas dimensions from parent panel
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

    // Color Palette - Ramadan Theme
    const COLORS = {
        gold: 0xC59D5F,
        deepRed: 0xA81C2E,
        emerald: 0x10B981,
        darkBlue: 0x1E3A8A,
        lightGold: 0xF5DEB3,
        white: 0xFFFFFF
    };

    // ============================
    // RAMADAN LANTERN (FANOOS) - 3D Model
    // ============================
    function createLantern(scale = 1) {
        const lantern = new THREE.Group();

        // Top dome
        const domeGeometry = new THREE.SphereGeometry(0.4 * scale, 16, 16, 0, Math.PI * 2, 0, Math.PI / 2);
        const domeMaterial = new THREE.MeshBasicMaterial({
            color: COLORS.gold,
            transparent: true,
            opacity: 0.6,
            wireframe: false
        });
        const dome = new THREE.Mesh(domeGeometry, domeMaterial);
        dome.position.y = 0.8 * scale;
        lantern.add(dome);

        // Body (hexagonal prism)
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
        body.position.y = 0;
        lantern.add(body);

        // Lantern glow (inner light)
        const glowGeometry = new THREE.SphereGeometry(0.3 * scale, 16, 16);
        const glowMaterial = new THREE.MeshBasicMaterial({
            color: COLORS.gold,
            transparent: true,
            opacity: 0.8
        });
        const glow = new THREE.Mesh(glowGeometry, glowMaterial);
        glow.position.y = 0.5 * scale;
        lantern.add(glow);

        // Light rays (lines emanating from lantern)
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
        const ringMaterial = new THREE.MeshBasicMaterial({ color: COLORS.deepRed, transparent: true, opacity: 0.7 });
        const ring = new THREE.Mesh(ringGeometry, ringMaterial);
        ring.position.y = -0.7 * scale;
        lantern.add(ring);

        return lantern;
    }

    // Create multiple floating lanterns
    const lanterns = [];
    for (let i = 0; i < 5; i++) {
        const lantern = createLantern(0.8 + Math.random() * 0.4);
        lantern.position.set(
            (Math.random() - 0.5) * 30,
            (Math.random() - 0.5) * 20,
            (Math.random() - 0.5) * 15
        );
        lantern.userData = {
            floatSpeed: 0.3 + Math.random() * 0.3,
            floatOffset: Math.random() * Math.PI * 2,
            rotationSpeed: 0.001 + Math.random() * 0.002
        };
        scene.add(lantern);
        lanterns.push(lantern);
    }

    // ============================
    // CRESCENT MOON - 3D Model
    // ============================
    function createCrescent() {
        const crescent = new THREE.Group();

        // Outer circle
        const outerGeometry = new THREE.CircleGeometry(1.5, 32);
        const outerMaterial = new THREE.MeshBasicMaterial({
            color: COLORS.gold,
            transparent: true,
            opacity: 0.8,
            side: THREE.DoubleSide
        });
        const outer = new THREE.Mesh(outerGeometry, outerMaterial);
        crescent.add(outer);

        // Inner circle (cutout) - positioned to create crescent shape
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

        // Glow effect around crescent
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
    scene.add(crescent);

    // ============================
    // STARS - Particle System with Twinkling
    // ============================
    const starGeometry = new THREE.BufferGeometry();
    const starCount = 200;
    const starPositions = new Float32Array(starCount * 3);
    const starColors = new Float32Array(starCount * 3);
    const starSizes = new Float32Array(starCount);

    for (let i = 0; i < starCount; i++) {
        starPositions[i * 3] = (Math.random() - 0.5) * 50;
        starPositions[i * 3 + 1] = (Math.random() - 0.5) * 30;
        starPositions[i * 3 + 2] = (Math.random() - 0.5) * 30;

        // Random star colors (gold and white mix)
        const color = Math.random() > 0.5 ? new THREE.Color(COLORS.gold) : new THREE.Color(COLORS.white);
        starColors[i * 3] = color.r;
        starColors[i * 3 + 1] = color.g;
        starColors[i * 3 + 2] = color.b;

        starSizes[i] = Math.random() * 2 + 1;
    }

    starGeometry.setAttribute('position', new THREE.BufferAttribute(starPositions, 3));
    starGeometry.setAttribute('color', new THREE.BufferAttribute(starColors, 3));
    starGeometry.setAttribute('size', new THREE.BufferAttribute(starSizes, 1));

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

    // ============================
    // ISLAMIC GEOMETRIC PATTERNS - Rotating Mandala
    // ============================
    function createMandala() {
        const mandala = new THREE.Group();
        const segments = 8;
        const radius = 3;

        for (let i = 0; i < segments; i++) {
            const angle = (i / segments) * Math.PI * 2;

            // Petal shape
            const petalShape = new THREE.Shape();
            petalShape.moveTo(0, 0);
            petalShape.quadraticCurveTo(0.5, 0.8, 1, 0);
            petalShape.quadraticCurveTo(0.5, -0.2, 0, 0);

            const petalGeometry = new THREE.ShapeGeometry(petalShape);
            const petalMaterial = new THREE.MeshBasicMaterial({
                color: i % 2 === 0 ? COLORS.emerald : COLORS.deepRed,
                transparent: true,
                opacity: 0.3,
                side: THREE.DoubleSide
            });
            const petal = new THREE.Mesh(petalGeometry, petalMaterial);
            petal.rotation.z = angle;
            petal.position.x = Math.cos(angle) * radius;
            petal.position.y = Math.sin(angle) * radius;
            mandala.add(petal);

            // Inner ring patterns
            const ringGeometry = new THREE.RingGeometry(radius - 0.5, radius - 0.3, 32);
            const ringMaterial = new THREE.MeshBasicMaterial({
                color: COLORS.gold,
                transparent: true,
                opacity: 0.2,
                side: THREE.DoubleSide
            });
            const ringMesh = new THREE.Mesh(ringGeometry, ringMaterial);
            mandala.add(ringMesh);
        }

        return mandala;
    }

    const mandala = createMandala();
    mandala.position.set(12, -6, -10);
    scene.add(mandala);

    // ============================
    // ANIMATED PARTICLES - Golden Dust
    // ============================
    const dustGeometry = new THREE.BufferGeometry();
    const dustCount = 300;
    const dustPositions = new Float32Array(dustCount * 3);

    for (let i = 0; i < dustCount; i++) {
        dustPositions[i * 3] = (Math.random() - 0.5) * 40;
        dustPositions[i * 3 + 1] = (Math.random() - 0.5) * 25;
        dustPositions[i * 3 + 2] = (Math.random() - 0.5) * 20;
    }

    dustGeometry.setAttribute('position', new THREE.BufferAttribute(dustPositions, 3));

    const dustMaterial = new THREE.PointsMaterial({
        size: 0.08,
        color: COLORS.lightGold,
        transparent: true,
        opacity: 0.6,
        blending: THREE.AdditiveBlending
    });

    const dust = new THREE.Points(dustGeometry, dustMaterial);
    scene.add(dust);

    // ============================
    // ANIMATION LOOP
    // ============================
    let time = 0;

    function animate() {
        requestAnimationFrame(animate);
        time += 0.01;

        // Animate lanterns - floating and rotating
        lanterns.forEach((lantern, index) => {
            lantern.position.y += Math.sin(time * lantern.userData.floatSpeed + lantern.userData.floatOffset) * 0.01;
            lantern.rotation.y += lantern.userData.rotationSpeed;

            // Glow pulsing
            const glow = lantern.children.find(child => child.geometry?.type === 'SphereGeometry');
            if (glow) {
                glow.material.opacity = 0.6 + Math.sin(time * 2 + index) * 0.2;
            }
        });

        // Animate crescent - gentle rotation
        crescent.rotation.z += 0.002;
        crescent.children[2].material.opacity = 0.15 + Math.sin(time * 0.5) * 0.1; // Glow pulse

        // Animate stars - twinkling effect
        const starSizesAttr = stars.geometry.attributes.size;
        for (let i = 0; i < starCount; i++) {
            starSizesAttr.array[i] = (Math.sin(time * 2 + i * 0.1) + 1) * 0.5 + 0.5;
        }
        starSizesAttr.needsUpdate = true;
        stars.rotation.y += 0.0005;

        // Animate mandala - rotating patterns
        mandala.rotation.z += 0.003;
        mandala.children.forEach((child, i) => {
            if (child.geometry?.type === 'ShapeGeometry') {
                child.rotation.z += 0.001 * (i % 2 === 0 ? 1 : -1);
            }
        });

        // Animate golden dust - slow drift
        const dustPos = dust.geometry.attributes.position.array;
        for (let i = 0; i < dustCount; i++) {
            dustPos[i * 3 + 1] += Math.sin(time * 0.5 + i * 0.05) * 0.02;
            if (dustPos[i * 3 + 1] > 12) dustPos[i * 3 + 1] = -12;
        }
        dust.geometry.attributes.position.needsUpdate = true;
        dust.rotation.y += 0.001;

        // Subtle camera movement for depth
        camera.position.x = Math.sin(time * 0.1) * 0.5;
        camera.position.y = Math.cos(time * 0.15) * 0.3;
        camera.lookAt(scene.position);

        renderer.render(scene, camera);
    }

    // Handle window resize
    window.addEventListener('resize', () => {
        const panelRect = canvas.parentElement.getBoundingClientRect();
        camera.aspect = panelRect.width / panelRect.height;
        camera.updateProjectionMatrix();
        renderer.setSize(panelRect.width, panelRect.height);
    });

    // Start animation
    animate();

    // GSAP text animations (existing functionality preserved)
    const overlayPanel = document.querySelector('.obs-overlay-panel');
    if (!overlayPanel || !window.gsap || !gsap.plugins || !gsap.plugins.text) {
        return;
    }

    const cycleSeconds = 30;
    const transitionSeconds = 0.6;
    const arabicHoldSeconds = 6;
    const englishHoldSeconds = Math.max(
        1,
        cycleSeconds - (transitionSeconds * 2 + arabicHoldSeconds)
    );
    const introDelaySeconds = 1;
    const introCountSeconds = 1.6;
    const introLabelSeconds = 0.45;
    const introLabelStagger = 0.08;
    const introValueStagger = 0.12;

    const arabicDigits = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
    const toArabicDigits = (value) =>
        value.replace(/\d/g, (digit) => arabicDigits[Number(digit)]);

    const formatNumber = (value, decimals) => {
        return new Intl.NumberFormat('en-US', {
            minimumFractionDigits: decimals,
            maximumFractionDigits: decimals
        }).format(value);
    };

    const collectTargets = () => {
        const targets = Array.from(overlayPanel.querySelectorAll('[data-obs-text]'));
        targets.forEach((element) => {
            const english = element.getAttribute('data-obs-en')?.trim() ||
                element.textContent.trim();
            element.setAttribute('data-obs-en', english);

            if (element.dataset.obsValue === 'true') {
                element.setAttribute('data-obs-ar', toArabicDigits(english));
            }
        });
        return targets;
    };

    const getLabelTargets = () => Array.from(
        overlayPanel.querySelectorAll('[data-obs-text]:not([data-obs-value="true"])')
    );

    const getValueTargets = () => Array.from(
        overlayPanel.querySelectorAll('[data-obs-text][data-obs-value="true"]')
    );

    const animateLocale = (locale) => {
        const targets = collectTargets();
        targets.forEach((element) => {
            const targetText = locale === 'ar'
                ? element.getAttribute('data-obs-ar')
                : element.getAttribute('data-obs-en');
            if (!targetText) {
                return;
            }

            gsap.to(element, {
                text: { value: targetText, delimiter: '' },
                duration: transitionSeconds,
                ease: 'power2.inOut',
                overwrite: 'auto'
            });
        });
    };

    const setIntroBlankState = () => {
        getLabelTargets().forEach((element) => {
            gsap.set(element, { text: '' });
        });

        getValueTargets().forEach((element) => {
            const targetText = element.getAttribute('data-obs-ar')?.trim() ||
                element.textContent.trim();
            const zeroText = targetText.includes('%') ? '0%' : '0';
            gsap.set(element, { text: zeroText });
        });
    };

    const buildLabelIntro = () => {
        const targets = getLabelTargets();
        const tl = gsap.timeline();

        targets.forEach((element, index) => {
            const targetText = element.getAttribute('data-obs-ar') || '';
            tl.to(element, {
                text: { value: targetText, delimiter: '' },
                duration: introLabelSeconds,
                ease: 'power2.out',
                overwrite: 'auto'
            }, index * introLabelStagger);
        });

        return tl;
    };

    const buildValueIntro = () => {
        const targets = getValueTargets();
        const tl = gsap.timeline();

        targets.forEach((element, index) => {
            const targetText = element.getAttribute('data-obs-ar')?.trim() ||
                element.textContent.trim();
            const cleanTarget = targetText.replace(/,/g, '');
            const decimals = cleanTarget.includes('.')
                ? cleanTarget.split('.')[1].replace(/\D/g, '').length
                : 0;
            const hasPercent = cleanTarget.includes('%');
            const numericTarget = Number(cleanTarget.replace(/[^0-9.]/g, '')) || 0;

            const state = { value: 0 };
            tl.to(state, {
                value: numericTarget,
                duration: introCountSeconds,
                ease: 'power2.out',
                onUpdate: () => {
                    const formatted = formatNumber(state.value, decimals);
                    const nextText = hasPercent ? `${formatted}%` : formatted;
                    gsap.set(element, { text: nextText });
                }
            }, index * introValueStagger);
        });

        return tl;
    };

    if (window.obsOverlayTimeline) {
        window.obsOverlayTimeline.kill();
    }

    const timeline = gsap.timeline({ repeat: -1 });
    timeline.add(() => setIntroBlankState());
    timeline.to({}, { duration: introDelaySeconds });
    timeline.add(buildLabelIntro());
    timeline.add(buildValueIntro(), '>-0.05');
    timeline.to({}, { duration: arabicHoldSeconds });
    timeline.add(() => animateLocale('en'));
    timeline.to({}, { duration: englishHoldSeconds });
    timeline.add(() => animateLocale('ar'));
    timeline.to({}, { duration: transitionSeconds });

    window.obsOverlayTimeline = timeline;
});
</script>
