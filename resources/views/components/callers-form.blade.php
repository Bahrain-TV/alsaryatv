@php
    use Illuminate\Support\Facades\Cache;
    $totalIndividualRegistrations = Cache::remember('total_individual_registrations', now()->addMinutes(5), function() {
        return App\Models\Caller::where('is_family', false)->count();
    });
@endphp

<div class="bg-slate-950/80 border border-amber-400/30 rounded-xl mx-auto w-full max-w-xs sm:max-w-sm md:max-w-md shadow-xl shadow-black/30 backdrop-blur-sm">
    <div class="w-full p-4 sm:p-5">
        <h2 class="text-xl sm:text-2xl text-amber-200 font-tajawal font-bold mb-3 sm:mb-4 text-center">{{ $title }}</h2>

        @if ($errors->any())
        <div class="mb-3 p-3 bg-red-500/15 text-red-200 border border-red-400/30 rounded text-xs sm:text-sm">
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form method="POST"
              action="{{ route('callers.store') }}"
              class="space-y-1.5 form-aldoyh"
              id="individualCallerForm">
            @csrf
            <input type="hidden" name="is_new_caller" value="1">
            <input type="hidden" name="action_type" value="create">
            <!-- Add this special flag to indicate hits should be incremented even for "new" submissions -->
            <input type="hidden" name="increment_if_exists" value="1">
            <input type="hidden" name="is_update" value="0">
            <input type="hidden" name="caller_type" value="individual">
            <input type="hidden" name="store_cpr_in_session" value="1">
            <input type="hidden" name="family_id" value="0">
            <!-- Include an ID of 0 to ensure it's treated as new -->
            <input type="hidden" name="id" value="0">

            <div class="space-y-0">
                <x-form-input
                    name="name"
                    label="الاسم كاملاً"
                    placeholder="الإسم الرباعي كاملاً"
                    :value="old('name')"
                    required
                />

                <x-form-input
                    type="tel"
                    name="phone_number"
                    label="رقم الهاتف"
                    placeholder="+973 3333 3333"
                    :value="old('phone_number')"
                    required
                />

                <x-form-input
                    name="cpr"
                    label="الرقم الشخصي"
                    required
                />
            </div>

            <div class="mt-4">
                <button type="submit"
                    class="w-full px-4 py-3 sm:py-2.5 rounded-lg text-sm sm:text-base font-bold text-slate-900 bg-amber-400 hover:bg-amber-300 active:bg-amber-500 transition-colors duration-200 touch-action-manipulation">
                    {{ $buttonText ?? 'إرسال' }}
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('individualCallerForm');
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
                // Show loading state
                submitButton.disabled = true;
                submitButton.innerHTML = 'جاري الإرسال...';
                
                // Store CPR in session storage for success page to use
                const cprInput = this.querySelector('input[name="cpr"]');
                if (cprInput && cprInput.value) {
                    const cprValue = cprInput.value.trim();
                    sessionStorage.setItem('last_submitted_cpr', cprValue);
                    
                    // Add CPR duplication check
                    try {
                        const checkResult = await checkCprDuplication(cprValue);
                        
                        if (checkResult.exists) {
                            console.log("CPR already registered, incrementing hits");
                            // Add hidden field to explicitly indicate hits increment is needed
                            const hitsField = document.createElement('input');
                            hitsField.type = 'hidden';
                            hitsField.name = 'increment_hits';
                            hitsField.value = '1';
                            this.appendChild(hitsField);
                        }
                    } catch (checkError) {
                        console.error("Error checking CPR:", checkError);
                        // Continue with submission even if check fails
                    }
                }
                
                // Ensure the form has the latest CSRF token
                await refreshCSRFToken();
                
                // Submit the form
                this.submit();
            } catch (error) {
                console.error("Error during form submission:", error);
                submitButton.disabled = false;
                submitButton.innerHTML = '{{ $buttonText ?? 'إرسال' }}';
            }
        });
        
        // Ensure we have a valid CSRF token
        refreshCSRFToken();
    });

    function validateCallerForm(form) {
        const requiredFields = form.querySelectorAll('[required]');
        let valid = true;
        let firstInvalid = null;

        requiredFields.forEach(field => {
            if (field.value.trim() === '') {
                valid = false;
                field.classList.add('border-red-500');
                if (!firstInvalid) firstInvalid = field;
            } else {
                field.classList.remove('border-red-500');
            }
        });

        if (!valid) {
            firstInvalid.focus();
            alert('يرجى ملء جميع الحقول المطلوبة');
            return false;
        }
        
        return true;
    }
    
    // Add CPR duplication check function
    async function checkCprDuplication(cpr) {
        try {
            // Get CSRF token
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            
            const response = await fetch('/api/check-cpr', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ cpr: cpr }),
                credentials: 'same-origin'
            });
            
            if (!response.ok) {
                throw new Error('Network response error');
            }
            
            return await response.json();
        } catch (error) {
            console.error("Error checking CPR duplication:", error);
            // Return default object indicating no duplication to continue form flow
            return { exists: false };
        }
    }
    
    // Improved CSRF token refresh function
    async function refreshCSRFToken() {
        try {
            // Get a fresh CSRF token from Laravel
            const response = await fetch('/sanctum/csrf-cookie', {
                method: 'GET',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                cache: 'no-store'
            });
            
            if (!response.ok) {
                throw new Error('Failed to refresh CSRF token');
            }
            
            // Force wait a moment for the cookie to be properly set
            await new Promise(resolve => setTimeout(resolve, 300));
            
            // Get the token from the meta tag now that it should be updated
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            
            // Update axios if available
            if (window.axios) {
                axios.defaults.headers.common['X-CSRF-TOKEN'] = token;
            }
            
            console.log('CSRF token refreshed successfully.');
            return token;
        } catch (error) {
            console.error('CSRF token refresh failed:', error);
            throw error;
        }
    }
</script>