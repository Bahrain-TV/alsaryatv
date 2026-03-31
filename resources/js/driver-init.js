/**
 * Driver.js Integration
 * Load Driver.js library and initialize tutorials
 */

// Load Driver.js CSS
const driverCSS = document.createElement('link');
driverCSS.rel = 'stylesheet';
driverCSS.href = 'https://cdn.jsdelivr.net/npm/driver.js@1.3.2/dist/driver.css';
document.head.appendChild(driverCSS);

// Load Driver.js Script
const driverScript = document.createElement('script');
driverScript.src = 'https://cdn.jsdelivr.net/npm/driver.js@1.3.2/dist/driver.js.iife.js';
driverScript.onload = () => {
    // Driver.js is now available as window.driver
    console.log('Driver.js loaded successfully');

    // Import and initialize tutorial manager
    import('./tutorial-manager.js').then(({ tutorialManager }) => {
        // Optionally show welcome tutorial on first visit
        // tutorialManager.showWelcomeTutorialIfNew();
    });
};
driverScript.onerror = () => {
    console.error('Failed to load Driver.js');
};
document.head.appendChild(driverScript);

// Add custom Arabic styling for Driver.js popovers
const style = document.createElement('style');
style.textContent = `
    .driver-popover {
        direction: rtl;
        font-family: 'Tajawal', sans-serif !important;
        border-radius: 16px !important;
        box-shadow: 0 8px 32px rgba(0,0,0,0.35) !important;
    }

    .driver-popover-title {
        font-family: 'Tajawal', sans-serif !important;
        font-weight: 700 !important;
        font-size: 1.1rem !important;
        color: #1f2937 !important;
    }

    .driver-popover-description {
        font-family: 'Tajawal', sans-serif !important;
        font-size: 0.95rem !important;
        color: #4b5563 !important;
        line-height: 1.6 !important;
    }

    .driver-popover-footer {
        direction: ltr;
    }

    .driver-popover-progress-text {
        font-family: 'Tajawal', sans-serif !important;
        font-size: 0.8rem !important;
    }

    /* Popover animations */
    .driver-popover {
        animation: driverSlideIn 0.25s ease-out;
    }

    @keyframes driverSlideIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
`;
document.head.appendChild(style);
