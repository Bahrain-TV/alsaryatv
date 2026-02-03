/**
 * Countdown Timer Functionality
 *
 * Provides unified countdown functionality for the application
 * with elegant animations and responsive behavior
 */

class CountdownTimer {
    /**
     * Initialize a new countdown timer
     * @param {Object} options - Configuration options
     * @param {string|Date} options.targetDate - Target date for countdown (Date object or ISO string)
     * @param {string} options.daysSelector - CSS selector for days element
     * @param {string} options.hoursSelector - CSS selector for hours element
     * @param {string} options.minutesSelector - CSS selector for minutes element
     * @param {string} options.secondsSelector - CSS selector for seconds element
     * @param {boolean} options.useAnimation - Whether to animate value changes
     * @param {Function} options.onComplete - Callback when countdown reaches zero
     */
    constructor(options) {
        // Default configurations
        this.config = {
            targetDate: null,
            daysSelector: '.timer-value.days',
            hoursSelector: '.timer-value.hours',
            minutesSelector: '.timer-value.minutes',
            secondsSelector: '.timer-value.seconds',
            useAnimation: true,
            onComplete: null,
            ...options
        };

        // Parse target date
        this.targetDate = typeof this.config.targetDate === 'string'
            ? new Date(this.config.targetDate).getTime()
            : this.config.targetDate instanceof Date
                ? this.config.targetDate.getTime()
                : null;

        if (!this.targetDate) {
            console.error('CountdownTimer: No valid target date provided');
            return;
        }

        // Get DOM elements
        this.elements = {
            days: document.querySelector(this.config.daysSelector),
            hours: document.querySelector(this.config.hoursSelector),
            minutes: document.querySelector(this.config.minutesSelector),
            seconds: document.querySelector(this.config.secondsSelector)
        };

        // Validate elements
        if (!this.elements.days || !this.elements.hours ||
            !this.elements.minutes || !this.elements.seconds) {
            console.error('CountdownTimer: One or more elements not found');
            return;
        }

        // Store previous values for animation
        this.prevValues = {
            days: 0,
            hours: 0,
            minutes: 0,
            seconds: 0
        };

        // Start the countdown
        this.start();
    }

    /**
     * Start the countdown timer
     */
    start() {
        // Initial update
        this.update();

        // Update every second
        this.interval = setInterval(() => this.update(), 1000);
    }

    /**
     * Stop the countdown timer
     */
    stop() {
        if (this.interval) {
            clearInterval(this.interval);
        }
    }

    /**
     * Update the countdown display
     */
    update() {
        const now = new Date().getTime();
        const distance = this.targetDate - now;

        // Check if countdown is complete
        if (distance < 0) {
            this.displayValues(0, 0, 0, 0);
            this.stop();

            // Execute onComplete callback if provided
            if (typeof this.config.onComplete === 'function') {
                this.config.onComplete();
            }
            return;
        }

        // Calculate time units
        const days = Math.floor(distance / (1000 * 60 * 60 * 24));
        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);

        // Display the values
        this.displayValues(days, hours, minutes, seconds);
    }

    /**
     * Display countdown values with optional animation
     * @param {number} days - Number of days
     * @param {number} hours - Number of hours
     * @param {number} minutes - Number of minutes
     * @param {number} seconds - Number of seconds
     */
    displayValues(days, hours, minutes, seconds) {
        // Format values with leading zeros
        const formattedDays = String(days).padStart(2, '0');
        const formattedHours = String(hours).padStart(2, '0');
        const formattedMinutes = String(minutes).padStart(2, '0');
        const formattedSeconds = String(seconds).padStart(2, '0');

        // Update displayed values
        this.elements.days.textContent = formattedDays;
        this.elements.hours.textContent = formattedHours;
        this.elements.minutes.textContent = formattedMinutes;
        this.elements.seconds.textContent = formattedSeconds;

        // Animate changing values if enabled and GSAP is available
        if (this.config.useAnimation && typeof gsap !== 'undefined') {
            // Animate seconds when they change
            if (seconds !== this.prevValues.seconds) {
                this.animateElement(this.elements.seconds);
                this.prevValues.seconds = seconds;
            }

            // Animate minutes when they change
            if (minutes !== this.prevValues.minutes) {
                this.animateElement(this.elements.minutes);
                this.prevValues.minutes = minutes;
            }

            // Animate hours when they change
            if (hours !== this.prevValues.hours) {
                this.animateElement(this.elements.hours);
                this.prevValues.hours = hours;
            }

            // Animate days when they change
            if (days !== this.prevValues.days) {
                this.animateElement(this.elements.days);
                this.prevValues.days = days;
            }
        }
    }

    /**
     * Animate value change with GSAP
     * @param {HTMLElement} element - Element to animate
     */
    animateElement(element) {
        gsap.fromTo(element,
            { opacity: 0.5, scale: 0.95 },
            { opacity: 1, scale: 1, duration: 0.5, ease: 'power2.out' }
        );
    }
}

// Initialize with default callback on DOM load
document.addEventListener('DOMContentLoaded', () => {
    // Check if we have a countdown container
    const countdownContainer = document.getElementById('countdown-container');
    if (!countdownContainer) return;

    // Initialize with default March 1st target date
    // This can be overridden by data attributes on the container
    const targetDateStr = countdownContainer.dataset.targetDate || '2025-03-01T20:00:00';

    // Create the countdown timer
    window.countdownTimer = new CountdownTimer({
        targetDate: targetDateStr,
        useAnimation: true,
        onComplete: () => {
            console.log('Countdown complete!');

            // Optional: Show a message when countdown completes
            const messageEl = document.createElement('div');
            messageEl.className = 'text-center text-2xl font-bold text-white mt-4';
            messageEl.textContent = 'البث المباشر بدأ!';

            countdownContainer.appendChild(messageEl);
        }
    });

    // Enhance the appearance with initial animation if GSAP is available
    if (typeof gsap !== 'undefined') {
        const timerSegments = document.querySelectorAll('.timer-segment');
        gsap.fromTo(timerSegments,
            { y: 30, opacity: 0 },
            { y: 0, opacity: 1, duration: 0.8, stagger: 0.15, ease: 'power2.out' }
        );
    }
});