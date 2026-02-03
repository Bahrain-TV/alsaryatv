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
driverScript.src = 'https://cdn.jsdelivr.net/npm/driver.js@1.3.2/dist/index.umd.js';
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
    .driver-popover-ar {
        direction: rtl;
    }

    .driver-popover-ar .driver-title {
        font-family: 'Tajawal', sans-serif;
        font-weight: 700;
        font-size: 1.25rem;
        color: #1f2937;
        margin-bottom: 0.5rem;
    }

    .driver-popover-ar .driver-description {
        font-family: 'Tajawal', sans-serif;
        font-size: 1rem;
        color: #4b5563;
        line-height: 1.5;
    }

    .driver-popover-ar .driver-button {
        font-family: 'Tajawal', sans-serif;
        font-weight: 600;
    }

    .driver-popover-ar .driver-button:hover {
        background-color: #3b82f6;
    }

    .driver-popover-ar .driver-button-primary {
        background-color: #2563eb;
        color: white;
    }

    .driver-popover-ar .driver-progress {
        font-family: 'Tajawal', sans-serif;
    }

    /* Highlight styling */
    .driver-highlight {
        border-radius: 8px;
        box-shadow: 0 0 0 9999px rgba(0, 0, 0, 0.75);
    }

    /* Popover animations */
    .driver-popover {
        animation: slideIn 0.3s ease-out;
    }

    @keyframes slideIn {
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
