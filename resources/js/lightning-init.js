import LightningEffect from './lightning-effect';

// Initialize the lightning effect on specific elements
document.addEventListener('DOMContentLoaded', () => {
    // Get elements with 'lightning-effect' class
    const elements = document.querySelectorAll('.lightning-effect');
    
    // Initialize lightning effect on each element
    elements.forEach(element => {
        new LightningEffect({
            targetElement: element,
            width: element.offsetWidth,
            height: element.offsetHeight,
            autoPlay: true,
            duration: 3000
        });
    });
});
