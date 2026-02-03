import gsap from 'gsap';

export function initMasterTimeline() {
    const master = gsap.timeline({
        paused: false,
        defaults: { ease: "power3.out" }
    });

    // Initial states
    gsap.set('.fixed-layout', {
        height: '100vh',
        overflow: 'hidden'
    });

    gsap.set('#logo-animation-wrapper', {
        scale: 1,
        y: 0,
        position: 'relative',
        zIndex: 15
    });

    gsap.set(['.toggle-button', '.forms-stack', '.sponsors-container'], {
        opacity: 0,
        y: 20
    });

    // 
    // Create sequences
    const introSequence = createIntroSequence();
    const buttonsSequence = createButtonsSequence();
    const sponsorsSequence = createSponsorsSequence();
    const formsSequence = createFormsSequence();

    // Build master sequence
    master
        .add(introSequence)
        .add(buttonsSequence, "-=0.5")
        .add(sponsorsSequence, "-=0.3")
        .add(formsSequence, "-=0.2");

    return master;
}

function createIntroSequence() {
    const tl = gsap.timeline();

    tl.from('.intro-dance', {
        y: -50,
        scale: 0.8,
        opacity: 0,
        duration: 1.2,
        stagger: 0.2,
        ease: "back.out(1.7)",
        rotate: -5
    }).to('.intro-dance', {
        y: 0,
        scale: 1,
        rotate: 0,
        duration: 0.8,
        ease: "elastic.out(1, 0.5)"
    });

    return tl;
}

function createButtonsSequence() {
    const tl = gsap.timeline();

    // Slide in buttons from sides with bounce
    tl.to('.toggle-button', {
        opacity: 1,
        y: 0,
        x: (i) => i === 0 ? ['-100%', '0%'] : ['100%', '0%'],
        scale: (i) => [0.8, 1.1, 1],
        duration: 0.8,
        stagger: 0.15,
        ease: "back.out(1.7)",
        rotation: (i) => [0, i === 0 ? 10 : -10, 0],
        onComplete: initializeButtonEffects
    });

    return tl;
}

function initializeButtonEffects() {
    const buttons = document.querySelectorAll('.toggle-button');
    
    buttons.forEach(button => {
        button.addEventListener('click', () => {
            if (button.classList.contains('active')) return;

            const isFamily = button.id === 'toggle-family';
            const tl = gsap.timeline();

            // Squeeze logo and header up
            tl.to('#logo-animation-wrapper', {
                scale: 0.8,
                y: -50,
                duration: 0.8
            })
            .to('#header-pane', {
                y: -30,
                scale: 0.95,
                duration: 0.8
            }, "<")

            // Slide current form out and new form in
            .to('.form-panel[data-state="entering"]', {
                x: isFamily ? '-100%' : '100%',
                opacity: 0,
                scale: 0.9,
                duration: 0.6
            })
            .to('.form-panel[data-state="inactive"]', {
                x: 0,
                opacity: 1,
                scale: 1,
                duration: 0.6
            }, "-=0.3")

            // Update states
            .add(() => {
                document.querySelectorAll('.form-panel').forEach(panel => {
                    const currentState = panel.getAttribute('data-state');
                    panel.setAttribute('data-state', 
                        currentState === 'entering' ? 'inactive' : 'entering'
                    );
                });
            })

            // Visual feedback
            .to(button, {
                scale: [1, 1.2, 1.1],
                duration: 0.4,
                ease: "back.out(1.7)"
            });
        });

        // Hover effects
        button.addEventListener('mouseenter', () => {
            if (gsap.isTweening(button) || button.classList.contains('active')) return;
            
            gsap.to(button, {
                scale: 1.05,
                duration: 0.3,
                ease: "power2.out"
            });

            // Add ripple effect
            const ripple = document.createElement('div');
            ripple.className = 'button-ripple';
            button.appendChild(ripple);

            gsap.to(ripple, {
                scale: 1.5,
                opacity: 0,
                duration: 0.8,
                onComplete: () => ripple.remove()
            });
        });

        button.addEventListener('mouseleave', () => {
            if (!button.classList.contains('active')) {
                gsap.to(button, {
                    scale: 1,
                    duration: 0.3,
                    ease: "power2.in"
                });
            }
        });
    });
}

function createSponsorsSequence() {
    const tl = gsap.timeline();

    tl.to('.sponsors-container', {
        opacity: 1,
        y: 0,
        duration: 1,
        ease: "power2.out",
        stagger: {
            each: 0.2,
            from: "center"
        }
    });

    // Add lightning effect
    setTimeout(() => {
        const leftLogo = document.getElementById('left-sponsor-logo');
        if (leftLogo) {
            gsap.to(leftLogo, {
                filter: 'brightness(1.4) contrast(1.2)',
                duration: 0.2,
                yoyo: true,
                repeat: 1,
                delay: 0.5
            });
        }
    }, 1000);

    return tl;
}

function createFormsSequence() {
    const tl = gsap.timeline();

    tl.to('.forms-stack', {
        opacity: 1,
        y: 0,
        duration: 0.8,
        ease: "power2.inOut"
    });

    // Set initial form states
    tl.set('#call-form-container', {
        attr: { 'data-state': 'entering' }
    })
    .set('#family-form-container', {
        attr: { 'data-state': 'inactive' }
    });

    return tl;
}

export function initGlitchEffect() {
    const glitchTargets = ['#logo img', '#days'];
    let isGlitching = false;

    function triggerGlitch() {
        if (isGlitching) return;
        isGlitching = true;

        const randomTarget = glitchTargets[Math.floor(Math.random() * glitchTargets.length)];
        const element = document.querySelector(randomTarget);
        
        if (!element) return;

        const tl = gsap.timeline({
            onComplete: () => isGlitching = false
        });

        // Enhanced glitch effect
        for (let i = 0; i < 3; i++) {
            tl.to(element, {
                x: gsap.utils.random(-5, 5),
                y: gsap.utils.random(-5, 5),
                opacity: gsap.utils.random(0.8, 1),
                scale: gsap.utils.random(0.95, 1.05),
                duration: 0.1,
                skewX: gsap.utils.random(-3, 3)
            })
            .to(element, {
                x: 0,
                y: 0,
                opacity: 1,
                scale: 1,
                skewX: 0,
                duration: 0.1
            });
        }
    }

    // Create glitch timeline
    const glitchTimeline = gsap.timeline({
        repeat: -1,
        repeatDelay: gsap.utils.random(10, 20)
    });

    glitchTimeline
        .add(triggerGlitch)
        .add(() => gsap.delayedCall(gsap.utils.random(5, 15), triggerGlitch));

    setTimeout(() => glitchTimeline.play(), 5000);
}