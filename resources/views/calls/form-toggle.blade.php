<!-- Toggle buttons -->
<div class="flex justify-center gap-3 mb-6">
    <button type="button" id="toggleIndividual"
        class="px-6 py-2 text-base font-bold bg-blue-600 text-white rounded-lg shadow-lg hover:bg-blue-700 disabled:opacity-60 disabled:cursor-not-allowed active:scale-95 transition-all duration-200"
        aria-pressed="true" aria-label="نمط التسجيل الفردي">أفراد</button>
    <button type="button" id="toggleFamily"
        class="px-6 py-2 text-base font-bold bg-orange-600 text-white rounded-lg shadow-lg hover:bg-orange-700 disabled:opacity-60 disabled:cursor-not-allowed active:scale-95 transition-all duration-200"
        aria-pressed="false" aria-label="نمط التسجيل العائلي">عائلات</button>
</div>

<style>
    .form-container {
        perspective: 1200px;
        position: relative;
        min-height: auto;
    }

    #individualFormContainer, #familyFormContainer {
        animation: none;
        transform-origin: center;
        position: relative;
        width: 100%;
        opacity: 1;
        pointer-events: auto;
    }

    #individualFormContainer {
        display: block;
    }

    #familyFormContainer {
        display: none;
    }

    /* Rotation in effects */
    @keyframes rotateInYLeft {
        from {
            opacity: 0;
            transform: perspective(1000px) rotateY(-90deg);
        }
        to {
            opacity: 1;
            transform: perspective(1000px) rotateY(0deg);
        }
    }

    @keyframes rotateOutYRight {
        from {
            opacity: 1;
            transform: perspective(1000px) rotateY(0deg);
        }
        to {
            opacity: 0;
            transform: perspective(1000px) rotateY(90deg);
        }
    }

    @keyframes rotateInYRight {
        from {
            opacity: 0;
            transform: perspective(1000px) rotateY(90deg);
        }
        to {
            opacity: 1;
            transform: perspective(1000px) rotateY(0deg);
        }
    }

    @keyframes rotateOutYLeft {
        from {
            opacity: 1;
            transform: perspective(1000px) rotateY(0deg);
        }
        to {
            opacity: 0;
            transform: perspective(1000px) rotateY(-90deg);
        }
    }

    #individualFormContainer.show {
        animation: rotateInYLeft 0.6s ease-in-out forwards;
        display: block;
    }

    #individualFormContainer.hide {
        animation: rotateOutYRight 0.6s ease-in-out forwards;
        display: none;
    }

    #familyFormContainer.show {
        animation: rotateInYRight 0.6s ease-in-out forwards;
        display: block;
    }

    #familyFormContainer.hide {
        animation: rotateOutYLeft 0.6s ease-in-out forwards;
        display: none;
    }

    #individualFormContainer.show,
    #familyFormContainer.show {
        pointer-events: auto;
    }

    #individualFormContainer.hide,
    #familyFormContainer.hide {
        pointer-events: none;
    }

    /* Magic shimmer effect during rotation */
    @keyframes magicShimmer {
        0% {
            left: -100%;
            opacity: 0;
        }
        50% {
            opacity: 1;
        }
        100% {
            left: 100%;
            opacity: 0;
        }
    }

    .form-container.rotating::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg,
            transparent,
            rgba(255, 255, 255, 0.2),
            transparent);
        transform: skewX(-20deg);
        z-index: 10;
        pointer-events: none;
        animation: magicShimmer 0.6s ease-in-out;
    }

</style>

<!-- Form rotation container -->
<div class="form-container" id="formContainer">
    <!-- Individual Form -->
    <div id="individualFormContainer" class="show">
        <x-callers-form title="سجل الآن للمشاركة" buttonText="تسجيل" isHidden="false" />
    </div>

    <!-- Family Form -->
    <div id="familyFormContainer" class="hide">
        <x-family-callers-form title="سجل عائلتك الآن" buttonText="تسجيل العائلة" isHidden="true" />
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const individualBtn = document.getElementById('toggleIndividual');
        const familyBtn = document.getElementById('toggleFamily');
        const individualForm = document.getElementById('individualFormContainer');
        const familyForm = document.getElementById('familyFormContainer');
        const formContainer = document.getElementById('formContainer');
        let isAnimating = false;
        let hasGSAP = typeof gsap !== 'undefined';

        function switchForm(showIndividual) {
            // Check if already showing the requested form
            if (showIndividual && individualForm.classList.contains('show')) {
                return;
            }
            if (!showIndividual && familyForm.classList.contains('show')) {
                return;
            }

            if (isAnimating) return;
            isAnimating = true;

            // Disable buttons during animation
            individualBtn.disabled = true;
            familyBtn.disabled = true;

            // Add rotating class for shimmer effect
            if (formContainer) {
                formContainer.classList.add('rotating');
            }

            // Update button states immediately
            if (showIndividual) {
                individualBtn.setAttribute('aria-pressed', 'true');
                familyBtn.setAttribute('aria-pressed', 'false');
            } else {
                familyBtn.setAttribute('aria-pressed', 'true');
                individualBtn.setAttribute('aria-pressed', 'false');
            }

            // Use CSS animations or GSAP if available
            if (hasGSAP && typeof gsap !== 'undefined') {
                const tl = gsap.timeline({
                    onComplete: function() {
                        isAnimating = false;
                        individualBtn.disabled = false;
                        familyBtn.disabled = false;
                        if (formContainer) {
                            formContainer.classList.remove('rotating');
                        }
                    }
                });

                if (showIndividual) {
                    // Remove old classes from family, add new for individual
                    familyForm.classList.remove('show');
                    familyForm.classList.add('hide');
                    individualForm.classList.remove('hide');
                    individualForm.classList.add('show');

                    // Animate the container
                    tl.to(formContainer, {
                        duration: 0.3,
                        rotationY: -90,
                        x: -100,
                        opacity: 0.7,
                        ease: "power2.inOut"
                    }, 0)
                    .to(formContainer, {
                        duration: 0.3,
                        rotationY: 0,
                        x: 0,
                        opacity: 1,
                        ease: "power2.inOut"
                    }, 0.3);
                } else {
                    // Remove old classes from individual, add new for family
                    individualForm.classList.remove('show');
                    individualForm.classList.add('hide');
                    familyForm.classList.remove('hide');
                    familyForm.classList.add('show');

                    // Animate the container
                    tl.to(formContainer, {
                        duration: 0.3,
                        rotationY: 90,
                        x: 100,
                        opacity: 0.7,
                        ease: "power2.inOut"
                    }, 0)
                    .to(formContainer, {
                        duration: 0.3,
                        rotationY: 0,
                        x: 0,
                        opacity: 1,
                        ease: "power2.inOut"
                    }, 0.3);
                }
            } else {
                // Fallback without GSAP - use CSS animations
                setTimeout(() => {
                    if (showIndividual) {
                        familyForm.classList.remove('show');
                        familyForm.classList.add('hide');
                        individualForm.classList.remove('hide');
                        individualForm.classList.add('show');
                    } else {
                        individualForm.classList.remove('show');
                        individualForm.classList.add('hide');
                        familyForm.classList.remove('hide');
                        familyForm.classList.add('show');
                    }

                    isAnimating = false;
                    individualBtn.disabled = false;
                    familyBtn.disabled = false;
                    if (formContainer) {
                        formContainer.classList.remove('rotating');
                    }
                }, 600); // Match CSS animation duration
            }
        }

        // Prevent button click if already animating or if form is already shown
        individualBtn.addEventListener('click', function(e) {
            e.preventDefault();
            if (!isAnimating && !individualForm.classList.contains('show')) {
                switchForm(true);
            }
        });

        familyBtn.addEventListener('click', function(e) {
            e.preventDefault();
            if (!isAnimating && !familyForm.classList.contains('show')) {
                switchForm(false);
            }
        });

        // Set initial state
        individualForm.classList.add('show');
        familyForm.classList.add('hide');
    });
</script>