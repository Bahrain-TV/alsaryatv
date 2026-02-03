/**
 * Form Configuration and Utilities
 * This file ensures proper setup between Blade components and JavaScript
 */

export const formConfig = {
    // DOM element IDs and classes
    selectors: {
        buttonContainer: '.button-container',
        toggleButtons: '.toggle-button',
        formPanels: '.form-panel',
        formsStack: '.forms-stack',
        sponsorsContainer: '.sponsors-container'
    },
    
    // Animation settings
    animations: {
        duration: 0.5,
        ease: "power2.inOut",
        delay: 0.1,
        sponsorsAnimationDelay: 100
    },
    
    // Form dimensions by screen size and form type
    dimensions: {
        desktop: {
            individual: '500px',
            family: '550px'
        },
        mobile: {
            individual: '480px',
            family: '530px'
        },
        breakpoint: 768 // Mobile breakpoint
    }
};

/**
 * Check if DOM structure is properly configured
 * Use for debugging setup issues
 */
export function validateFormSetup() {
    const config = formConfig.selectors;
    const issues = [];
    
    // Check required elements
    if (!document.querySelector(config.toggleButtons)) {
        issues.push('Toggle buttons not found. Check class: ' + config.toggleButtons);
    }
    
    if (!document.querySelector(config.formPanels)) {
        issues.push('Form panels not found. Check class: ' + config.formPanels);
    }
    
    if (!document.querySelector(config.formsStack)) {
        issues.push('Forms stack container not found. Check class: ' + config.formsStack);
    }
    
    // Check button configuration
    const buttons = document.querySelectorAll(config.toggleButtons);
    buttons.forEach(button => {
        const targetId = button.getAttribute('data-target');
        if (!targetId) {
            issues.push(`Button ${button.id || 'unknown'} is missing data-target attribute`);
        } else if (!document.getElementById(targetId)) {
            issues.push(`Button target ${targetId} does not exist in the DOM`);
        }
    });
    
    // Return validation results
    return {
        valid: issues.length === 0,
        issues: issues
    };
}
