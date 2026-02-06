<div class="px-4 py-6">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @livewire('animated-stats-widget')
    </div>

    <!-- Include GSAP script for advanced animations -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Animate the stats cards when they appear
            gsap.from(".bg-white", {
                duration: 0.8,
                y: 50,
                opacity: 0,
                stagger: 0.1,
                ease: "power3.out",
                scrollTrigger: {
                    trigger: ".grid",
                    start: "top 80%",
                }
            });

            // Add hover effects to the cards
            const cards = document.querySelectorAll('.bg-white');
            cards.forEach(card => {
                card.addEventListener('mouseenter', () => {
                    gsap.to(card, {
                        duration: 0.3,
                        y: -5,
                        boxShadow: "0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04)",
                        ease: "power2.out"
                    });
                });

                card.addEventListener('mouseleave', () => {
                    gsap.to(card, {
                        duration: 0.3,
                        y: 0,
                        boxShadow: "0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06)",
                        ease: "power2.out"
                    });
                });
            });
        });
    </script>
</div>