<!-- Toggle buttons -->
<div class="flex justify-center gap-3 mb-6">
    <button type="button" id="toggleIndividual"
        class="px-6 py-2 text-base font-bold bg-blue-600 text-white rounded-lg shadow-lg hover:bg-blue-700 active:scale-95 transition-all duration-200"
        aria-pressed="true">أفراد</button>
    <button type="button" id="toggleFamily"
        class="px-6 py-2 text-base font-bold bg-orange-600 text-white rounded-lg shadow-lg hover:bg-orange-700 active:scale-95 transition-all duration-200"
        aria-pressed="false">عائلات</button>
</div>

<style>
    .form-container {
        perspective: 1200px;
        position: relative;
        min-height: 500px;
        height: 500px;
    }

    #individualFormContainer, #familyFormContainer {
        animation: none;
        transform-origin: center;
        transition: all 0.8s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
    }

    /* Rotation in effects */
    @keyframes rotateInYLeft {
        from {
            opacity: 0;
            transform: perspective(1000px) rotateY(-100deg) scale(0.8);
        }
        to {
            opacity: 1;
            transform: perspective(1000px) rotateY(0deg) scale(1);
        }
    }

    @keyframes rotateOutYRight {
        from {
            opacity: 1;
            transform: perspective(1000px) rotateY(0deg) scale(1);
        }
        to {
            opacity: 0;
            transform: perspective(1000px) rotateY(100deg) scale(0.8);
        }
    }

    @keyframes rotateInYRight {
        from {
            opacity: 0;
            transform: perspective(1000px) rotateY(100deg) scale(0.8);
        }
        to {
            opacity: 1;
            transform: perspective(1000px) rotateY(0deg) scale(1);
        }
    }

    @keyframes rotateOutYLeft {
        from {
            opacity: 1;
            transform: perspective(1000px) rotateY(0deg) scale(1);
        }
        to {
            opacity: 0;
            transform: perspective(1000px) rotateY(-100deg) scale(0.8);
        }
    }

    @keyframes magicalGlow {
        0%, 100% {
            filter: drop-shadow(0 0 8px rgba(59, 130, 246, 0.3));
        }
        50% {
            filter: drop-shadow(0 0 20px rgba(99, 102, 241, 0.6));
        }
    }

    #individualFormContainer.show {
        animation: rotateInYLeft 0.8s cubic-bezier(0.68, -0.55, 0.265, 1.55) forwards;
    }

    #individualFormContainer.hide {
        animation: rotateOutYRight 0.8s cubic-bezier(0.68, -0.55, 0.265, 1.55) forwards;
    }

    #familyFormContainer.show {
        animation: rotateInYRight 0.8s cubic-bezier(0.68, -0.55, 0.265, 1.55) forwards;
    }

    #familyFormContainer.hide {
        animation: rotateOutYLeft 0.8s cubic-bezier(0.68, -0.55, 0.265, 1.55) forwards;
    }

    #individualFormContainer.show,
    #familyFormContainer.show {
        animation-fill-mode: forwards;
        pointer-events: auto;
    }

    #individualFormContainer.hide,
    #familyFormContainer.hide {
        animation-fill-mode: forwards;
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

    .form-container::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -100%;
        width: 100%;
        height: 200%;
        background: linear-gradient(90deg, 
            transparent,
            rgba(255, 255, 255, 0.3),
            transparent);
        transform: skewX(-20deg);
        z-index: 10;
        pointer-events: none;
        animation: magicShimmer 0.8s ease-in-out;
    }

    .form-container.rotating::before {
        animation: magicShimmer 0.8s ease-in-out;
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

        function switchForm(showIndividual) {
            if (isAnimating) return;
            isAnimating = true;
            
            // Add rotating class for shimmer effect
            formContainer.classList.add('rotating');

            if (showIndividual) {
                // Hide family, show individual
                familyForm.classList.remove('show');
                familyForm.classList.add('hide');
                
                setTimeout(() => {
                    individualForm.classList.remove('hide');
                    individualForm.classList.add('show');
                    individualBtn.setAttribute('aria-pressed', 'true');
                    familyBtn.setAttribute('aria-pressed', 'false');
                }, 50);
            } else {
                // Hide individual, show family
                individualForm.classList.remove('show');
                individualForm.classList.add('hide');
                
                setTimeout(() => {
                    familyForm.classList.remove('hide');
                    familyForm.classList.add('show');
                    familyBtn.setAttribute('aria-pressed', 'true');
                    individualBtn.setAttribute('aria-pressed', 'false');
                }, 50);
            }

            // Remove rotating class after animation
            setTimeout(() => {
                formContainer.classList.remove('rotating');
                isAnimating = false;
            }, 800);
        }

        individualBtn.addEventListener('click', function() {
            if (!individualForm.classList.contains('show')) {
                switchForm(true);
            }
        });

        familyBtn.addEventListener('click', function() {
            if (!familyForm.classList.contains('show')) {
                switchForm(false);
            }
        });
    });
</script>