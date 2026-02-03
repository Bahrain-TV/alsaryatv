<div class="mx-auto w-full relative max-w-5xl fixed-layout" id="twins-buttons">
    {{-- <div class="flex flex-col sm:flex-row justify-center items-center gap-4 sm:gap-8 px-4 button-container">
        <button class="toggle-button active" id="toggle-call" data-target="call-form-container">
            <div class="w-full bg-indigo-800 text-slate-100 px-4 sm:px-6 md:px-10 py-2 sm:py-3 font-medium font-tajawal text-lg sm:text-xl rounded-xl shadow-lg transition-all">
                تسجيل الأفراد
            </div>
        </button>
        <button class="toggle-button" id="toggle-family" data-target="family-form-container">
            <div class="w-full bg-orange-800 text-slate-100 text-lg sm:text-xl font-medium font-tajawal px-4 sm:px-6 md:px-10 py-2 sm:py-3 rounded-xl shadow-lg transition-all">
                تسجيل العائلات
            </div>
        </button>
    </div> --}}

    {{-- @include('sponsors') --}}

    
    <!-- Accessibility features for screen readers -->
    <div class="sr-only">
        <span id="call-form-label">نموذج تسجيل الأفراد</span>
        <span id="family-form-label">نموذج تسجيل العائلات</span>
    </div>
</div>

{{-- @include('layouts.footer', ['hits' => $hits ?? 100]) --}}

<style>
/* Layout Container */
.fixed-layout {
    position: relative;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    overflow: visible;
    padding-top: max(5vh, 2rem);
}

/* Button Container */
.button-container {
    position: relative;
    z-index: 20;
    margin-bottom: 2rem;
    width: 100%;
}

/* Forms Stack */
.forms-stack {
    position: relative;
    z-index: 10;
    overflow: hidden;
    border-radius: 0.75rem;
    transform: translateZ(0); /* Prevent safari rendering issues */
    will-change: contents;
}

/* Form Panels */
.form-panel {
    will-change: transform, opacity;
    transition: transform 500ms cubic-bezier(0.4, 0, 0.2, 1),
                opacity 500ms cubic-bezier(0.4, 0, 0.2, 1),
                visibility 0s 500ms;
    opacity: 0;
    transform: translateX(100%);
    pointer-events: none;
    visibility: hidden;
    backface-visibility: hidden;
    -webkit-backface-visibility: hidden;
}

.form-panel.active {
    opacity: 1;
    transform: translateX(0);
    pointer-events: auto;
    visibility: visible;
    transition: transform 500ms cubic-bezier(0.4, 0, 0.2, 1),
                opacity 500ms cubic-bezier(0.4, 0, 0.2, 1),
                visibility 0s 0s;
}

/* Form Content */
.form-content {
    opacity: 1;
    transform: translateZ(0);
    transition: opacity 300ms ease;
    height: 100%;
    overflow: hidden;
}

/* Enhanced Button Styles */
.toggle-button {
    position: relative;
    z-index: 5;
    transition: all 400ms cubic-bezier(0.4, 0, 0.2, 1);
    transform: translateY(0);
    opacity: 1;
    overflow: hidden;
    cursor: pointer;
    outline: none;
    border: none;
    flex: 1;
    max-width: 280px;
    min-width: 200px;
    transform: scale(0.95);
}

.toggle-button:focus-visible {
    outline: 2px solid white;
    outline-offset: 2px;
}

.toggle-button.active div {
    transform: scale(1.05);
    box-shadow: 0 0 15px rgba(255, 255, 255, 0.3);
    filter: brightness(1.1);
}

.toggle-button div {
    position: relative;
    z-index: 1;
    transition: all 400ms cubic-bezier(0.4, 0, 0.2, 1);
    transform-origin: center;
    backdrop-filter: blur(5px);
    -webkit-backdrop-filter: blur(5px);
}

/* Button Hover State */
.toggle-button:not(.active):hover div {
    transform: scale(1.02);
    filter: brightness(1.05);
}

.toggle-button:not(.active) div {
    transform: scale(0.95);
    filter: brightness(0.9);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

/* Active Button Glow */
.toggle-button.active::after {
    content: '';
    position: absolute;
    inset: -5px;
    background: radial-gradient(circle at center, 
        rgba(255,255,255,0.2) 0%,
        rgba(255,255,255,0) 70%);
    filter: blur(5px);
    z-index: -1;
    pointer-events: none;
}

/* Responsive Adjustments */
@media (max-height: 800px) {
    .fixed-layout {
        padding-top: max(3vh, 1rem);
    }
    .forms-stack {
        height: calc(70vh - 100px) !important;
    }
    .button-container {
        margin-bottom: 1rem;
    }
}

@media (max-width: 380px) {
    .toggle-button div {
        font-size: 0.875rem;
        padding: 0.5rem;
    }
    .fixed-layout {
        padding-top: max(2vh, 0.5rem);
    }
    .toggle-button {
        min-width: unset;
        width: 100%;
        max-width: 100%;
    }
}

@media (min-height: 900px) {
    .fixed-layout {
        padding-top: max(10vh, 3rem);
    }
    .forms-stack {
        max-height: 600px;
    }
}

/* Focused responsive design for buttons to prevent overlap */
@media (max-width: 640px) {
    .button-container {
        flex-direction: column;
        align-items: center;
        width: 100%;
    }
    
    .toggle-button {
        width: 100%;
        max-width: 90%;
    }
}

@media (min-width: 641px) and (max-width: 768px) {
    .toggle-button {
        max-width: 45%;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Get all form panels and toggle buttons
    const formPanels = document.querySelectorAll('.form-panel');
    const toggleButtons = document.querySelectorAll('.toggle-button');
    
    // Add accessibility attributes
    toggleButtons.forEach(button => {
        const targetId = button.getAttribute('data-target');
        const isActive = button.classList.contains('active');
        const labelId = targetId === 'call-form-container' ? 'call-form-label' : 'family-form-label';
        
        button.setAttribute('aria-expanded', isActive ? 'true' : 'false');
        button.setAttribute('aria-controls', targetId);
        button.setAttribute('aria-labelledby', labelId);
    });
    
    // Initialize form panels for accessibility
    formPanels.forEach(panel => {
        const isActive = panel.classList.contains('active');
        panel.setAttribute('aria-hidden', isActive ? 'false' : 'true');
    });

    // Handle button stacking and form switching
    toggleButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Remove active class from all buttons and panels
            toggleButtons.forEach(btn => btn.classList.remove('active'));
            formPanels.forEach(panel => panel.classList.remove('active'));

            // Add active class to the clicked button and corresponding panel
            this.classList.add('active');
            document.getElementById(this.getAttribute('data-target')).classList.add('active');

            // Adjust z-index for stacking
            if (this.id === 'toggle-family') {
                document.getElementById('toggle-call').style.zIndex = '1';
                this.style.zIndex = '2';
            } else {
                document.getElementById('toggle-family').style.zIndex = '1';
                this.style.zIndex = '2';
            }
        });
    });
    
    // Note: Main button click handlers are managed in app.js
    // This script only initializes accessibility attributes and button stacking
});
</script>