/**
 * Main Application JavaScript
 */
import './bootstrap';
import 'alpinejs';
import gsap from 'gsap';
import ScrollTrigger from 'gsap/ScrollTrigger';
import { TextPlugin } from 'gsap/TextPlugin';
import { initMasterTimeline, initGlitchEffect } from './animations';
import { TornadoEffect } from './tornado-effect';
import ThankYouScreen from './thank-you-screen';

// Register GSAP plugins
gsap.registerPlugin(ScrollTrigger, TextPlugin);

// Make GSAP available globally for inline scripts in blade templates
window.gsap = gsap;

// Make TornadoEffect available globally
window.TornadoEffect = TornadoEffect;

// Make ThankYouScreen available globally
window.ThankYouScreen = ThankYouScreen;

// Theme detection and management
window.ThemeManager = {
    init() {
        this.detectAndApplyTheme();
        this.setupThemeListeners();
        // Initialize toggle button state
        this.updateToggleButton(document.documentElement.dataset.theme);
    },

    detectAndApplyTheme() {
        const storedTheme = localStorage.getItem('theme');
        const autoTheme = this.getAutoTheme();
        const theme = storedTheme || autoTheme;

        this.applyTheme(theme);
        localStorage.setItem('theme', theme);
    },

    getAutoTheme() {
        // First check browser preference
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        const prefersLight = window.matchMedia('(prefers-color-scheme: light)').matches;

        if (prefersDark) return 'dark';
        if (prefersLight) return 'light';

        // Fallback to time-based detection
        return this.getTimeBasedTheme();
    },

    getTimeBasedTheme() {
        const now = new Date();
        const hour = now.getHours();

        // Dark mode: 6 PM to 6 AM
        // Light mode: 6 AM to 6 PM
        return (hour >= 18 || hour < 6) ? 'dark' : 'light';
    },

    applyTheme(theme) {
        const html = document.documentElement;
        html.classList.toggle('dark', theme === 'dark');
        html.dataset.theme = theme;

        // Update meta theme-color for mobile browsers
        const metaThemeColor = document.querySelector('meta[name="theme-color"]');
        if (metaThemeColor) {
            metaThemeColor.content = theme === 'dark' ? '#0f172a' : '#ffffff';
        }

        // Update theme toggle button
        this.updateToggleButton(theme);
    },

    setupThemeListeners() {
        // Listen for system theme changes
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
            if (!localStorage.getItem('theme')) {
                this.applyTheme(e.matches ? 'dark' : 'light');
            }
        });

        // Periodic check for time-based theme (every 5 minutes)
        setInterval(() => {
            if (!localStorage.getItem('theme')) {
                const newTheme = this.getAutoTheme();
                const currentTheme = document.documentElement.dataset.theme;

                if (newTheme !== currentTheme) {
                    this.applyTheme(newTheme);
                }
            }
        }, 5 * 60 * 1000); // 5 minutes
    },

    setTheme(theme) {
        localStorage.setItem('theme', theme);
        this.applyTheme(theme);
    },

    toggleTheme() {
        const currentTheme = document.documentElement.dataset.theme;
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        this.setTheme(newTheme);
    },

    updateToggleButton(theme) {
        const sunIcon = document.getElementById('theme-icon-sun');
        const moonIcon = document.getElementById('theme-icon-moon');

        if (sunIcon && moonIcon) {
            if (theme === 'dark') {
                sunIcon.style.opacity = '0';
                moonIcon.style.opacity = '1';
            } else {
                sunIcon.style.opacity = '1';
                moonIcon.style.opacity = '0';
            }
        }
    }
};

// DOM Ready handler
document.addEventListener('DOMContentLoaded', () => {
    // Initialize theme management
    if (window.ThemeManager) {
        window.ThemeManager.init();
    }

    // Check if animations module is loaded
    if (typeof initMasterTimeline === 'function') {
        // Initialize master timeline
        const masterTimeline = initMasterTimeline();
        if (masterTimeline) {
            masterTimeline.play();
        }
    }
    
    // Initialize effects if functions exist
    if (typeof initGlitchEffect === 'function') {
        initGlitchEffect();
    }
    
    // Only initialize form animations if needed elements exist
    if (document.querySelector('.forms-stack') && document.querySelector('.toggle-button')) {
        initFormFlipAnimations();
    }
    
    // Only adjust sponsors if they exist
    if (document.querySelector('.sponsors-container')) {
        adjustSponsorsPosition();
    }

    // Initialize logo animation if element exists
    if (document.getElementById('logo')) {
        const logoTimeline = gsap.timeline({
            delay: 3,
        });
        logoTimeline.from('#logo', {
            opacity: 0,
            duration: 1,
            ease: 'power2.inOut',
        });
    }

    // Hide preloader once the page has fully loaded
    hidePreloader();
});

// Properly hide the preloader
function hidePreloader() {
    const preloader = document.getElementById('preloader');
    if (preloader) {
        // Add event listener for when all resources are loaded
        window.addEventListener('load', function() {
            // Add a fade-out effect
            gsap.to(preloader, {
                opacity: 0,
                duration: 0.5,
                onComplete: function() {
                    preloader.style.display = 'none';
                }
            });
        });
        
        // Fallback timer in case load event doesn't fire
        setTimeout(function() {
            if (preloader.style.display !== 'none') {
                gsap.to(preloader, {
                    opacity: 0,
                    duration: 0.5,
                    onComplete: function() {
                        preloader.style.display = 'none';
                    }
                });
            }
        }, 5000);
    }
}

// Window resize handler with debounce
let resizeTimeout;
window.addEventListener('resize', () => {
    clearTimeout(resizeTimeout);
    resizeTimeout = setTimeout(() => {
        if (document.querySelector('.sponsors-container')) {
            adjustSponsorsPosition();
        }
    }, 250);
});

// Handle form transitions from animations.js
document.addEventListener('formTransition', (event) => {
    const { button, isFamily } = event.detail;
    if (!button) return;
    
    const buttonColor = isFamily ? '#9A3412' : '#4338CA';
    
    // Only fire confetti if function exists
    if (typeof fireConfetti === 'function') {
        // Fire confetti effect
        fireConfetti(button, [buttonColor, '#ffffff', '#f8fafc', buttonColor]);
    }
    
    // Focus form input after animation
    setTimeout(() => focusFirstFormInput(button), 1000);
});

/**
 * Fire confetti effect
 */
function fireConfetti(element, colors = ['#4F46E5', '#EC4899', '#EF4444', '#F97316']) {
    if (typeof window.confetti !== 'function') return;
    if (!element) return;
    
    const rect = element.getBoundingClientRect();
    const xRatio = (rect.left + rect.width / 2) / window.innerWidth;
    const yRatio = (rect.top + rect.height / 2) / window.innerHeight;
    
    confetti({
        particleCount: 100,
        spread: 70,
        origin: { x: xRatio, y: yRatio },
        colors: colors,
        disableForReducedMotion: true,
        scalar: 1.2
    });
}

/**
 * Initialize form flip animations
 */
function initFormFlipAnimations() {
    const toggleButtons = document.querySelectorAll('.toggle-button');
    const formPanels = document.querySelectorAll('.form-panel');
    const formsStack = document.querySelector('.forms-stack');
    
    if (!toggleButtons.length || !formPanels.length || !formsStack) return;
    
    // Setup toggle button handlers
    toggleButtons.forEach(button => {
        button.addEventListener('click', () => {
            const targetId = button.getAttribute('data-target');
            if (!targetId) return;
            
            const targetPanel = document.getElementById(targetId);
            const isFamily = button.id === 'toggle-family';
            
            if (!targetPanel) return;
            
            // Update active button
            toggleButtons.forEach(btn => btn.classList.remove('active'));
            button.classList.add('active');
            
            // Update form panel visibility
            formPanels.forEach(panel => {
                panel.classList.remove('active');
                // Update ARIA attributes
                panel.setAttribute('aria-hidden', 'true');
            });
            
            targetPanel.classList.add('active');
            targetPanel.setAttribute('aria-hidden', 'false');
            
            // Dispatch custom event for additional effects
            document.dispatchEvent(new CustomEvent('formTransition', {
                detail: { button, isFamily }
            }));
            
            // Adjust sponsors position with animation
            setTimeout(() => {
                if (document.querySelector('.sponsors-container')) {
                    adjustSponsorsPosition(true);
                }
            }, 100);
        });
    });
}

/**
 * Focus first form input
 */
function focusFirstFormInput(toggleButton) {
    if (!toggleButton) return;
    
    const formId = toggleButton.getAttribute('data-target');
    if (!formId) return;

    const form = document.getElementById(formId);
    if (!form) return;

    const firstInput = form.querySelector('input:not([type="hidden"])');
    if (firstInput) {
        firstInput.focus();
    }
}

/**
 * Adjust sponsors position
 */
function adjustSponsorsPosition(animate = false) {
    const formsStack = document.querySelector('.forms-stack');
    const sponsorsContainer = document.querySelector('.sponsors-container');
    
    if (!formsStack || !sponsorsContainer) return;
    
    const activeButton = document.querySelector('.toggle-button.active');
    const isFamily = activeButton?.id === 'toggle-family';
    const extraMargin = window.innerWidth <= 768 && isFamily ? 50 : 0;
    const idealMarginTop = Math.max(20, formsStack.offsetHeight + 40);
    
    gsap.to(sponsorsContainer, {
        marginTop: `${idealMarginTop}px`,
        y: extraMargin,
        duration: animate ? 0.5 : 0,
        ease: "power2.inOut"
    });
}

// Make functions available globally for animations.js
window.fireConfetti = fireConfetti;
window.adjustSponsorsPosition = adjustSponsorsPosition;