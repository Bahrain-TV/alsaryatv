/**
 * Home Page Introduction Animations
 *
 * Handles all entry animations and form transitions for the home page
 */
document.addEventListener('DOMContentLoaded', function () {
    // Initialize animations with proper sequence and timing
    initializeAnimations();
});

/**
 * Initialize all page animations in a consistent sequence
 */
function initializeAnimations() {
    // Ensure all form fields are enabled by default
    enableFormFields('#callers-form');
    disableFormFields('#family-form');

    // Create master timeline for coordination
    const masterTimeline = gsap.timeline();

    // Logo entry animation
    const logoElements = gsap.utils.toArray('#logo img, #logo svg');
    masterTimeline.add(
        gsap.from(logoElements, {
            yPercent: -100,
            opacity: 0,
            duration: 0.8,
            scale: 1.5,
            stagger: 0.2,
            ease: 'back.out(1.7)'
        })
    );

    // Header animation
    const headerElements = gsap.utils.toArray('#header h1, #header p');
    masterTimeline.add(
        gsap.from(headerElements, {
            y: 50,
            opacity: 0,
            duration: 0.6,
            stagger: 0.15,
            ease: 'power3.out'
        }),
        "-=0.3"
    );

    // Buttons entry animation
    masterTimeline.add(
        gsap.from('#twins-buttons button', {
            y: 30,
            opacity: 0,
            duration: 0.7,
            stagger: 0.2,
            ease: 'back.out(1.4)'
        }),
        "-=0.2"
    );

    // Form animations - initial state
    const formTimeline = gsap.timeline();

    // Call form entry animation
    const callFormElements = gsap.utils.toArray('#call-form > *, #call-form input, #call-form label, #call-form button');
    const callFormAnimation = gsap.from('#call-form', {
        xPercent: -100,
        opacity: 0,
        duration: 0.7,
        ease: 'power4.out',
        paused: true,
        onStart: () => {
            gsap.from(callFormElements, {
                y: 20,
                opacity: 0,
                duration: 0.5,
                stagger: 0.1,
                ease: 'power2.out',
                delay: 0.3
            });
            enableFormFields('#call-form');
        }
    });

    // Family form entry animation
    const familyFormElements = gsap.utils.toArray('#family-form > *, #family-form input, #family-form label, #family-form button');
    const familyFormAnimation = gsap.from('#family-form', {
        xPercent: 100,
        opacity: 0,
        duration: 0.7,
        ease: 'power4.inOut',
        paused: true,
        onStart: () => {
            gsap.from(familyFormElements, {
                y: 20,
                opacity: 0,
                duration: 0.5,
                stagger: 0.1,
                ease: 'power2.out',
                delay: 0.3
            });
            enableFormFields('#family-form');
        }
    });

    // Add the call form animation to the main timeline
    masterTimeline.add(callFormAnimation.play(), "+=0.2");

    // Optional countdown animation if present
    if (document.querySelector('#countdown')) {
        const countdownElements = gsap.utils.toArray('#countdown *');
        masterTimeline.add(
            gsap.from(countdownElements, {
                y: 30,
                opacity: 0,
                duration: 0.5,
                stagger: 0.1,
                ease: 'power3.out'
            }),
            "-=0.2"
        );
    }



    // Setup button toggle functionality
    setupFormToggle(callFormAnimation, familyFormAnimation);
}

/**
 * Enable all form fields within a selector
 * @param {string} formSelector - CSS selector for the form
 */
function enableFormFields(formSelector) {
    document.querySelectorAll(`${formSelector} input, ${formSelector} textarea, ${formSelector} select`).forEach((el) => {
        el.disabled = false;
    });
}

/**
 * Disable all form fields within a selector
 * @param {string} formSelector - CSS selector for the form
 */
function disableFormFields(formSelector) {
    document.querySelectorAll(`${formSelector} input, ${formSelector} textarea, ${formSelector} select`).forEach((el) => {
        el.disabled = true;
    });
}

/**
 * Setup form toggle button interaction
 * @param {object} callFormAnimation - GSAP animation for call form
 * @param {object} familyFormAnimation - GSAP animation for family form
 */
function setupFormToggle(callFormAnimation, familyFormAnimation) {
    // Find toggle buttons if they exist
    const callToggleBtn = document.getElementById('toggle-call');
    const familyToggleBtn = document.getElementById('toggle-family');

    // Skip if buttons aren't found
    if (!callToggleBtn || !familyToggleBtn) return;

    // Hook up toggle functionality - handled in twin-buttons-panel.blade.php
}