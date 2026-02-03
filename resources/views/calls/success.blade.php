@extends('layouts.app')

@section('content')
<div class="py-6 sm:py-8 md:py-10 max-w-3xl mx-auto px-4">
    <div class="mb-6 text-center">
        <x-application-logo class="h-16 sm:h-20 mx-auto" />
    </div>

    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="p-5 sm:p-6 border-b border-gray-200 bg-gradient-to-r from-green-50 to-emerald-50">
            <h1 class="text-2xl sm:text-3xl font-bold text-center font-tajawal text-emerald-800">
                <i class="fas fa-check-circle text-emerald-600 mr-2"></i> تم التسجيل بنجاح
            </h1>
        </div>

        <div class="p-5 sm:p-6 text-center">
            <div class="mb-6">
                <p class="text-lg sm:text-xl font-tajawal font-medium text-gray-700">
                    شكراً <span class="font-bold text-emerald-700">{{ session('name') }}</span> لتسجيلك في برنامج
                    السارية
                </p>
                <p class="mt-2 text-gray-600">سيتم إدخال اسمك في قاعدة بيانات المسابقة</p>
            </div>

            @if(session('userHits'))
            <!-- User hits statistics section -->
            <div class="mt-6 grid grid-cols-2 gap-4">
                <div class="bg-emerald-50 rounded-lg p-4 shadow-inner">
                    <p class="text-sm text-emerald-700">عدد مشاركاتك</p>
                    <p class="text-2xl font-bold text-emerald-800">{{ number_format(session('userHits', 1)) }}</p>
                </div>
                <div class="bg-blue-50 rounded-lg p-4 shadow-inner">
                    <p class="text-sm text-blue-700">إجمالي المشاركات</p>
                    <p class="text-2xl font-bold text-blue-800">{{ number_format(session('totalHits', 0)) }}</p>
                </div>
            </div>
            @endif

            <!-- Countdown section -->
            <div class="mt-8 text-center">
                <p class="text-gray-600">سيتم تحويلك إلى الصفحة الرئيسية خلال</p>
                <p class="text-3xl font-bold text-indigo-600 counter">{{ session('seconds', 30) }}</p>
            </div>

            <div class="mt-6">
                <a href="{{ route('home') }}"
                    class="inline-block px-5 py-3 rounded-lg bg-indigo-600 text-white font-medium hover:bg-indigo-700 transition duration-200">
                    العودة للرئيسية
                </a>
            </div>
        </div>
    </div>

    <div class="mt-6 text-center text-sm text-gray-500">
        <p>© {{ now()->year }} برنامج السارية. جميع الحقوق محفوظة</p>
    </div>
</div>

<script>
    // Countdown timer
    document.addEventListener('DOMContentLoaded', function() {
        let countdown = {{ session('seconds', 30) }};
        const counterElement = document.querySelector('.counter');
        
        // Check if we have user hits stored in sessionStorage
        const lastCpr = sessionStorage.getItem('last_submitted_cpr');
        if (lastCpr) {
            console.log('Last submitted CPR:', lastCpr);
        }
        
        const timer = setInterval(() => {
            countdown--;
            if (counterElement) {
                counterElement.textContent = countdown;
            }
            
            if (countdown <= 0) {
                clearInterval(timer);
                window.location.href = "{{ route('home') }}";
            }
        }, 1000);
    });
</script>
@endsection