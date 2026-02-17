<x-guest-layout>
    <div class="py-6">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-6">
                <!-- Header moved here from x-slot -->
                <h2 class="font-semibold text-xl text-gray-800 leading-tight text-center mb-6">
                    {{ __('تسجيل في برنامج السارية') }}
                </h2>

                <div class="text-center mb-4">
                    <h1 class="text-2xl font-bold text-gray-900">اختر نوع التسجيل</h1>
                    <p class="mt-1 text-gray-600">يمكنك التسجيل كفرد أو كعائلة للمشاركة في المسابقات</p>
                </div>
                
                @include('calls.form-toggle')
            </div>
        </div>
    </div>
</x-guest-layout>
