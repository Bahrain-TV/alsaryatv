@php
    use Illuminate\Support\Facades\Cache;
    try {
        $totalFamilyRegistrations = Cache::remember('total_family_registrations', now()->addMinutes(5), function() {
            return \App\Models\Caller::where('is_family', true)->count();
        });
    } catch (\Exception $e) {
        $totalFamilyRegistrations = 0;
    }
@endphp

<div id="family-form" class="bg-orange-800 bg-opacity-85 rounded-lg mx-auto w-full max-w-xs sm:max-w-sm md:max-w-md shadow-lg">
    <div class="w-full p-4 sm:p-5">
        <h2 class="text-xl sm:text-2xl text-slate-100 font-tajawal font-bold mb-3 sm:mb-4 text-center">{{ $title ?? 'تسجيل العائلات' }}</h2>

        @if ($errors->any())
            <div class="mb-3 p-3 bg-red-100 bg-opacity-90 text-red-700 rounded text-xs sm:text-sm">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST"
              action="{{ route('callers.store') }}"
              class="space-y-1.5"
              id="familyCallerForm">
            @csrf
            <input type="hidden" name="is_new_caller" value="1">
            <input type="hidden" name="action_type" value="create">
            <input type="hidden" name="increment_if_exists" value="1">
            <input type="hidden" name="is_update" value="0">
            <input type="hidden" name="caller_type" value="family">
            <input type="hidden" name="is_family" value="1">
            <input type="hidden" name="store_cpr_in_session" value="1">
            <input type="hidden" name="family_id" value="0">
            <input type="hidden" name="id" value="0">

            <div class="space-y-0">
                @if(View::exists('components.form-input'))
                    <x-form-input name="name" label="الاسم كاملاً" placeholder="الإسم الرباعي كاملاً"
                        :value="old('name')" required />
                    <x-form-input type="tel" name="phone_number" label="رقم الهاتف" placeholder="3333 5555"
                        :value="old('phone_number')" required />
                    <x-form-input name="cpr" label="الرقم الشخصي" :value="old('cpr')" required />
                @else
                    <div class="mt-5">
                        <label class="block text-right text-base sm:text-lg font-bold text-white mb-5 drop-shadow-lg">الاسم كاملاً</label>
                        <input type="text" name="name" required placeholder="الإسم الرباعي كاملاً" value="{{ old('name') }}" class="w-full px-4 py-3 sm:py-3 bg-white text-gray-900 border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm sm:text-base">
                    </div>
                    <div class="mt-5">
                        <label class="block text-right text-base sm:text-lg font-bold text-white mb-5 drop-shadow-lg">رقم الهاتف</label>
                        <input type="tel" name="phone_number" required placeholder="3333 5555" value="{{ old('phone_number') }}" class="w-full px-4 py-3 sm:py-3 bg-white text-gray-900 border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm sm:text-base">
                    </div>
                    <div class="mt-5">
                        <label class="block text-right text-base sm:text-lg font-bold text-white mb-5 drop-shadow-lg">الرقم الشخصي</label>
                        <input type="text" name="cpr" required value="{{ old('cpr') }}" class="w-full px-4 py-3 sm:py-3 bg-white text-gray-900 border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm sm:text-base">
                    </div>
                @endif

                <div class="flex items-start mt-4">
                    <input id="policy_agreement" name="policy_agreement" type="checkbox" required class="h-5 w-5 sm:h-4 sm:w-4 rounded border-gray-300 flex-shrink-0 mt-0.5 sm:mt-0">
                    <label for="policy_agreement" class="mr-2 sm:ml-2 text-xs sm:text-sm text-white font-medium">
                        أوافق على شروط المشاركة
                    </label>
                </div>
            </div>

            <div class="mt-4">
                <button type="submit"
                    class="w-full px-4 py-3 sm:py-2.5 rounded-lg text-sm sm:text-base font-bold text-white bg-orange-600 hover:bg-orange-700 active:bg-orange-800 transition-colors duration-200 touch-action-manipulation">
                    {{ $buttonText ?? 'إرسال' }}
                </button>
            </div>
        </form>
    </div>
</div>

<style>
/* Prevent form inputs from being zoomed on iOS when focused */
input[type="text"],
input[type="tel"],
input[type="email"],
input[type="number"] {
    font-size: 16px;
}

/* Smooth touch feedback */
#family-form button,
#family-form input[type="checkbox"] {
    -webkit-tap-highlight-color: transparent;
}

/* Better touch target for checkbox */
#family-form .flex {
    min-height: 44px;
    display: flex;
    align-items: center;
}
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('familyCallerForm');
        const submitButton = form.querySelector('button[type="submit"]');
        const getCSRFToken = () => document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        if (window.axios) {
            axios.defaults.headers.common['X-CSRF-TOKEN'] = getCSRFToken();
            axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
        }

        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            if (!validateCallerForm(this)) return;

            try {
                submitButton.disabled = true;
                submitButton.innerHTML = 'جاري الإرسال...';
                const cprInput = this.querySelector('input[name="cpr"]');
                if (cprInput && cprInput.value) {
                    sessionStorage.setItem('last_submitted_cpr', cprInput.value.trim());
                }
                this.submit();
            } catch (error) {
                console.error("Error during form submission:", error);
                submitButton.disabled = false;
                submitButton.innerHTML = '{{ $buttonText ?? 'إرسال' }}';
            }
        });

        refreshCSRFToken();
    });

    function validateCallerForm(form) {
        const requiredFields = form.querySelectorAll('[required]');
        let valid = true;
        let firstInvalid = null;

        requiredFields.forEach(field => {
            if (field.type === 'checkbox' && !field.checked) {
                valid = false;
                if (!firstInvalid) firstInvalid = field;
            } else if (field.type !== 'checkbox' && !field.value.trim()) {
                valid = false;
                if (!firstInvalid) firstInvalid = field;
            }
        });

        if (!valid && firstInvalid) {
            firstInvalid.focus();
            alert('يرجى ملء جميع الحقول المطلوبة');
            return false;
        }
        return true;
    }

    async function refreshCSRFToken() {
        try {
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (window.axios) axios.defaults.headers.common['X-CSRF-TOKEN'] = token;
        } catch (e) {}
    }
</script>
