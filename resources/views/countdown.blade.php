<!-- Insert this code after the header and before the main content -->
<div class="relative w-full max-w-2xl px-6 lg:max-w-7xl">
    <div class="flex flex-col items-center justify-center py-10">
        <div class="rounded-lg bg-white p-8 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-white/[0.05]">
            <div class="text-center">
                <h2 class="text-2xl font-semibold text-black">Coming Soon</h2>
                <div class="mt-6 flex items-center justify-center gap-6">
                    <div class="flex flex-col items-center">
                        <span id="days" class="text-4xl font-bold text-[#FF2D20]">00</span>
                        <span class="text-sm text-gray-500">Days</span>
                    </div>
                    <span class="text-2xl font-bold text-[#FF2D20]">:</span>
                    <div class="flex flex-col items-center">
                        <span id="hours" class="text-4xl font-bold text-[#FF2D20]">00</span>
                        <span class="text-sm text-gray-500">Hours</span>
                    </div>
                    <span class="text-2xl font-bold text-[#FF2D20]">:</span>
                    <div class="flex flex-col items-center">
                        <span id="minutes" class="text-4xl font-bold text-[#FF2D20]">00</span>
                        <span class="text-sm text-gray-500">Minutes</span>
                    </div>
                    <span class="text-2xl font-bold text-[#FF2D20]">:</span>
                    <div class="flex flex-col items-center">
                        <span id="seconds" class="text-4xl font-bold text-[#FF2D20]">00</span>
                        <span class="text-sm text-gray-500">Seconds</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add this script before the closing body tag -->
<script>
    // Set the date we're counting down to (1st of March 2025)
    const countDownDate = new Date("Mar 1, 2025 21:00:00").getTime();

    // Update the countdown every 1 second
    const countdown = setInterval(function() {
        const now = new Date().getTime();
        const distance = countDownDate - now;

        // Calculate time
        const days = Math.floor(distance / (1000 * 60 * 60 * 24));
        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);

        // Display the result
        document.getElementById("days").textContent = String(days).padStart(2, '0');
        document.getElementById("hours").textContent = String(hours).padStart(2, '0');
        document.getElementById("minutes").textContent = String(minutes).padStart(2, '0');
        document.getElementById("seconds").textContent = String(seconds).padStart(2, '0');

        // If the countdown is finished, display expired
        if (distance < 0) {
            clearInterval(countdown);
            document.getElementById("days").textContent = "00";
            document.getElementById("hours").textContent = "00";
            document.getElementById("minutes").textContent = "00";
            document.getElementById("seconds").textContent = "00";
        }
    }, 1000);
</script>