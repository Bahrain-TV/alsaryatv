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
        z-index: -1;
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

<div wire:poll.2s="refreshStats" class="obs-overlay-wrap">
    <div class="obs-overlay-float">
        <div class="obs-overlay-panel rounded-2xl border border-white/10 bg-black/70 p-6 shadow-lg backdrop-blur">
            <canvas class="threejs-canvas" id="obs-overlay-canvas"></canvas>
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
                    class="mt-2 text-2xl font-semibold text-white"
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
                    class="mt-2 text-2xl font-semibold text-white"
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
                    class="mt-2 text-2xl font-semibold text-white"
                    data-obs-text
                    data-obs-value="true"
                    data-obs-en="{{ number_format($totalHits) }}"
                    dir="auto"
                >
                    {{ number_format($totalHits) }}
                </p>
            </div>
        </div>

        <div class="mt-4 text-xs text-white/70">
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
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const overlayPanel = document.querySelector('.obs-overlay-panel');
        const canvas = document.getElementById('obs-overlay-canvas');

        if (!overlayPanel || !canvas || !window.THREE) {
            return;
        }

        // Three.js setup
        const scene = new THREE.Scene();
        const camera = new THREE.PerspectiveCamera(75, canvas.clientWidth / canvas.clientHeight, 0.1, 1000);
        const renderer = new THREE.WebGLRenderer({ canvas: canvas, alpha: true, antialias: true });

        renderer.setSize(canvas.clientWidth, canvas.clientHeight);
        renderer.setClearColor(0x000000, 0);

        camera.position.z = 5;

        // Create meaningful animated elements for each stat
        const elements = [];

        // 1. Total Callers - Group of people (spheres with simple features)
        const peopleGroup = new THREE.Group();
        const peopleCount = 7;

        for (let i = 0; i < peopleCount; i++) {
            // Body (cylinder)
            const bodyGeometry = new THREE.CylinderGeometry(0.08, 0.08, 0.3, 8);
            const bodyMaterial = new THREE.MeshBasicMaterial({
                color: 0xff6b6b,
                transparent: true,
                opacity: 0.4
            });
            const body = new THREE.Mesh(bodyGeometry, bodyMaterial);

            // Head (sphere)
            const headGeometry = new THREE.SphereGeometry(0.1, 8, 8);
            const headMaterial = new THREE.MeshBasicMaterial({
                color: 0xffa726,
                transparent: true,
                opacity: 0.5
            });
            const head = new THREE.Mesh(headGeometry, headMaterial);
            head.position.y = 0.25;

            // Combine into person
            const person = new THREE.Group();
            person.add(body);
            person.add(head);

            // Position in a semi-circle
            const angle = (i / (peopleCount - 1)) * Math.PI * 0.8 - Math.PI * 0.4;
            const radius = 0.8;
            person.position.set(
                Math.cos(angle) * radius,
                Math.sin(angle) * 0.3,
                -1
            );
            person.rotation.z = angle + Math.PI / 2;

            peopleGroup.add(person);
        }

        peopleGroup.position.set(-1.5, 0.5, 0);
        scene.add(peopleGroup);
        elements.push({
            object: peopleGroup,
            type: 'people',
            initialPosition: peopleGroup.position.clone(),
            floatOffset: 0
        });

        // 2. Today Callers - Clock/watch shape
        const clockGroup = new THREE.Group();

        // Clock face (circle)
        const clockFaceGeometry = new THREE.CircleGeometry(0.25, 16);
        const clockFaceMaterial = new THREE.MeshBasicMaterial({
            color: 0x4ecdc4,
            transparent: true,
            opacity: 0.3,
            side: THREE.DoubleSide
        });
        const clockFace = new THREE.Mesh(clockFaceGeometry, clockFaceMaterial);
        clockGroup.add(clockFace);

        // Hour markers
        for (let i = 0; i < 12; i++) {
            const angle = (i / 12) * Math.PI * 2;
            const markerGeometry = new THREE.BoxGeometry(0.02, 0.08, 0.01);
            const markerMaterial = new THREE.MeshBasicMaterial({
                color: 0x4ecdc4,
                transparent: true,
                opacity: 0.6
            });
            const marker = new THREE.Mesh(markerGeometry, markerMaterial);
            marker.position.set(
                Math.sin(angle) * 0.2,
                Math.cos(angle) * 0.2,
                0.01
            );
            marker.rotation.z = angle;
            clockGroup.add(marker);
        }

        // Clock hands
        const hourHandGeometry = new THREE.BoxGeometry(0.02, 0.15, 0.01);
        const minuteHandGeometry = new THREE.BoxGeometry(0.015, 0.2, 0.01);
        const secondHandGeometry = new THREE.BoxGeometry(0.01, 0.22, 0.01);

        const hourHand = new THREE.Mesh(hourHandGeometry, new THREE.MeshBasicMaterial({
            color: 0xffffff,
            transparent: true,
            opacity: 0.8
        }));
        const minuteHand = new THREE.Mesh(minuteHandGeometry, new THREE.MeshBasicMaterial({
            color: 0xffffff,
            transparent: true,
            opacity: 0.8
        }));
        const secondHand = new THREE.Mesh(secondHandGeometry, new THREE.MeshBasicMaterial({
            color: 0xff4444,
            transparent: true,
            opacity: 0.9
        }));

        clockGroup.add(hourHand);
        clockGroup.add(minuteHand);
        clockGroup.add(secondHand);

        clockGroup.position.set(-0.5, 0.5, 0);
        scene.add(clockGroup);
        elements.push({
            object: clockGroup,
            type: 'clock',
            initialPosition: clockGroup.position.clone(),
            floatOffset: Math.PI / 3
        });

        // 3. Total Hits - Target/bullseye with rings
        const targetGroup = new THREE.Group();

        // Multiple concentric rings
        const ringColors = [0xf9ca24, 0xff6b6b, 0x4ecdc4, 0x45b7d1];
        const ringRadii = [0.25, 0.18, 0.12, 0.06];

        for (let i = 0; i < ringRadii.length; i++) {
            const ringGeometry = new THREE.RingGeometry(
                ringRadii[i] - 0.02,
                ringRadii[i],
                16
            );
            const ringMaterial = new THREE.MeshBasicMaterial({
                color: ringColors[i],
                transparent: true,
                opacity: 0.4 + (i * 0.1),
                side: THREE.DoubleSide
            });
            const ring = new THREE.Mesh(ringGeometry, ringMaterial);
            ring.position.z = i * 0.02;
            targetGroup.add(ring);
        }

        // Center dot
        const centerGeometry = new THREE.CircleGeometry(0.04, 8);
        const centerMaterial = new THREE.MeshBasicMaterial({
            color: 0xff0000,
            transparent: true,
            opacity: 0.8
        });
        const center = new THREE.Mesh(centerGeometry, centerMaterial);
        center.position.z = 0.1;
        targetGroup.add(center);

        targetGroup.position.set(0.5, 0.5, 0);
        scene.add(targetGroup);
        elements.push({
            object: targetGroup,
            type: 'target',
            initialPosition: targetGroup.position.clone(),
            floatOffset: Math.PI * 2 / 3
        });

        // 4. Win Ratio - Trophy/cup shape
        const trophyGroup = new THREE.Group();

        // Cup base (cylinder)
        const cupBaseGeometry = new THREE.CylinderGeometry(0.12, 0.15, 0.1, 8);
        const cupBaseMaterial = new THREE.MeshBasicMaterial({
            color: 0xffd700,
            transparent: true,
            opacity: 0.5
        });
        const cupBase = new THREE.Mesh(cupBaseGeometry, cupBaseMaterial);
        trophyGroup.add(cupBase);

        // Cup body (tapered cylinder)
        const cupBodyGeometry = new THREE.CylinderGeometry(0.15, 0.12, 0.2, 8);
        const cupBodyMaterial = new THREE.MeshBasicMaterial({
            color: 0xffd700,
            transparent: true,
            opacity: 0.4
        });
        const cupBody = new THREE.Mesh(cupBodyGeometry, cupBodyMaterial);
        cupBody.position.y = 0.15;
        trophyGroup.add(cupBody);

        // Cup handles (tori)
        const handleGeometry = new THREE.TorusGeometry(0.08, 0.02, 8, 16, Math.PI);
        const handleMaterial = new THREE.MeshBasicMaterial({
            color: 0xffd700,
            transparent: true,
            opacity: 0.6
        });

        const leftHandle = new THREE.Mesh(handleGeometry, handleMaterial);
        leftHandle.position.set(-0.15, 0.15, 0);
        leftHandle.rotation.z = Math.PI / 2;
        trophyGroup.add(leftHandle);

        const rightHandle = new THREE.Mesh(handleGeometry, handleMaterial);
        rightHandle.position.set(0.15, 0.15, 0);
        rightHandle.rotation.z = -Math.PI / 2;
        trophyGroup.add(rightHandle);

        // Trophy base (larger cylinder)
        const trophyBaseGeometry = new THREE.CylinderGeometry(0.2, 0.18, 0.08, 8);
        const trophyBaseMaterial = new THREE.MeshBasicMaterial({
            color: 0x8B4513,
            transparent: true,
            opacity: 0.5
        });
        const trophyBase = new THREE.Mesh(trophyBaseGeometry, trophyBaseMaterial);
        trophyBase.position.y = -0.09;
        trophyGroup.add(trophyBase);

        trophyGroup.position.set(1.5, 0.5, 0);
        scene.add(trophyGroup);
        elements.push({
            object: trophyGroup,
            type: 'trophy',
            initialPosition: trophyGroup.position.clone(),
            floatOffset: Math.PI
        });

        // Enhanced particle system
        const particleGeometry = new THREE.BufferGeometry();
        const particleCount = 100;
        const positions = new Float32Array(particleCount * 3);
        const colors = new Float32Array(particleCount * 3);

        for (let i = 0; i < particleCount; i++) {
            positions[i * 3] = (Math.random() - 0.5) * 12;
            positions[i * 3 + 1] = (Math.random() - 0.5) * 8;
            positions[i * 3 + 2] = (Math.random() - 0.5) * 2;

            // Color particles based on position
            const hue = (positions[i * 3] + 6) / 12;
            const color = new THREE.Color().setHSL(hue, 0.7, 0.6);
            colors[i * 3] = color.r;
            colors[i * 3 + 1] = color.g;
            colors[i * 3 + 2] = color.b;
        }

        particleGeometry.setAttribute('position', new THREE.BufferAttribute(positions, 3));
        particleGeometry.setAttribute('color', new THREE.BufferAttribute(colors, 3));

        const particleMaterial = new THREE.PointsMaterial({
            size: 0.03,
            transparent: true,
            opacity: 0.4,
            vertexColors: true,
            blending: THREE.AdditiveBlending
        });

        const particles = new THREE.Points(particleGeometry, particleMaterial);
        scene.add(particles);
        elements.push({ object: particles, type: 'particles' });

        // Pulsing ring for live indicator
        const ringGeometry = new THREE.RingGeometry(0.08, 0.12, 16);
        const ringMaterial = new THREE.MeshBasicMaterial({
            color: 0x00ff88,
            transparent: true,
            opacity: 0.8,
            side: THREE.DoubleSide
        });
        const ring = new THREE.Mesh(ringGeometry, ringMaterial);
        ring.position.set(-2.2, 1.2, 0);
        scene.add(ring);
        elements.push({ object: ring, type: 'ring', initialScale: ring.scale.clone() });

        // Entrance animation
        const entranceAnimation = () => {
            const tl = gsap.timeline();

            // Animate particles entrance
            tl.fromTo(particles.position, { y: -8 }, { y: 0, duration: 2, ease: "power2.out" });

            // Animate people group
            tl.fromTo(peopleGroup.position,
                { z: -3, y: peopleGroup.position.y - 1 },
                { z: 0, y: peopleGroup.position.y, duration: 1.2, ease: "back.out(1.7)" },
                "-=1.5"
            );

            // Animate clock
            tl.fromTo(clockGroup.position,
                { z: -3, x: clockGroup.position.x - 1 },
                { z: 0, x: clockGroup.position.x, duration: 1, ease: "power2.out" },
                "-=1.2"
            );

            // Animate target
            tl.fromTo(targetGroup.position,
                { z: -3, x: targetGroup.position.x + 1 },
                { z: 0, x: targetGroup.position.x, duration: 1, ease: "power2.out" },
                "-=1"
            );

            // Animate trophy
            tl.fromTo(trophyGroup.position,
                { z: -3, y: trophyGroup.position.y + 1 },
                { z: 0, y: trophyGroup.position.y, duration: 1.2, ease: "back.out(1.7)" },
                "-=0.8"
            );

            // Animate ring
            tl.fromTo(ring.position,
                { x: -3 },
                { x: -2.2, duration: 1, ease: "power2.out" },
                "-=1.5"
            );

            return tl;
        };

        // Looping animations
        const animate = () => {
            requestAnimationFrame(animate);

            const time = Date.now() * 0.001;

            // Animate particles with flowing motion
            const particlePositions = particles.geometry.attributes.position.array;
            for (let i = 0; i < particleCount; i++) {
                const i3 = i * 3;
                particlePositions[i3 + 1] += Math.sin(time * 0.5 + i * 0.1) * 0.002;
                if (particlePositions[i3 + 1] > 4) particlePositions[i3 + 1] = -4;
            }
            particles.geometry.attributes.position.needsUpdate = true;
            particles.rotation.y += 0.001;

            // Animate people group - subtle bobbing
            peopleGroup.children.forEach((person, index) => {
                person.position.y = Math.sin(time * 1.5 + index * 0.5) * 0.05;
                person.rotation.z += 0.002;
            });
            peopleGroup.position.y = 0.5 + Math.sin(time * 0.8) * 0.03;

            // Animate clock - moving hands
            const now = new Date();
            const seconds = now.getSeconds();
            const minutes = now.getMinutes();
            const hours = now.getHours() % 12;

            hourHand.rotation.z = -(hours * Math.PI / 6 + minutes * Math.PI / 360) + Math.PI / 2;
            minuteHand.rotation.z = -(minutes * Math.PI / 30 + seconds * Math.PI / 1800) + Math.PI / 2;
            secondHand.rotation.z = -(seconds * Math.PI / 30) + Math.PI / 2;

            clockGroup.rotation.z = Math.sin(time * 0.3) * 0.05;
            clockGroup.position.y = 0.5 + Math.sin(time * 0.5 + Math.PI / 3) * 0.04;

            // Animate target - rotating rings
            targetGroup.children.forEach((ring, index) => {
                if (ring.geometry.type === 'RingGeometry') {
                    ring.rotation.z += 0.005 * (index + 1);
                }
            });
            targetGroup.position.y = 0.5 + Math.sin(time * 0.7 + Math.PI * 2 / 3) * 0.04;

            // Animate trophy - gentle rotation and shine
            trophyGroup.rotation.y += 0.003;
            trophyGroup.position.y = 0.5 + Math.sin(time * 0.6 + Math.PI) * 0.03;

            // Animate pulsing ring
            const pulseScale = 1 + Math.sin(time * 4) * 0.15;
            ring.scale.setScalar(pulseScale);
            ring.rotation.z += 0.02;

            renderer.render(scene, camera);
        };

        // Handle canvas resize
        const resizeCanvas = () => {
            const rect = canvas.getBoundingClientRect();
            camera.aspect = rect.width / rect.height;
            camera.updateProjectionMatrix();
            renderer.setSize(rect.width, rect.height);
        };

        window.addEventListener('resize', resizeCanvas);
        resizeCanvas();

        // Start animations
        entranceAnimation();
        animate();

        // GSAP text animations (existing functionality)
        if (!window.gsap || !gsap.plugins || !gsap.plugins.text) {
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
