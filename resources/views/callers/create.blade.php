@extends('layouts.app')

@push('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800&display=swap');
    
    .min-h-screen.bg-gray-100 {
        background: radial-gradient(circle at center, #1e293b 0%, #0f172a 100%) !important;
    }
    
    header.bg-white.shadow {
        background-color: rgba(30, 41, 59, 0.8) !important;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        color: #fbbf24;
    }

    body {
        font-family: 'Tajawal', sans-serif;
        color: #e2e8f0;
    }
    
    .glass-card {
        background: rgba(30, 41, 59, 0.7);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 16px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }

    .form-input {
        background: rgba(15, 23, 42, 0.6);
        border: 1px solid rgba(255, 255, 255, 0.1);
        color: white;
        padding: 0.75rem 1rem;
        border-radius: 12px;
        width: 100%;
        transition: all 0.3s;
    }
    .form-input:focus {
        outline: none;
        border-color: #fbbf24;
        box-shadow: 0 0 0 3px rgba(251, 191, 36, 0.1);
    }
    .form-label {
        display: block;
        color: #cbd5e1;
        margin-bottom: 0.5rem;
        font-weight: 500;
        text-align: right;
    }
</style>
@endpush

@section('content')
<div class="max-w-3xl mx-auto py-12 px-4">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-yellow-500">➕ Add New Caller</h1>
        <a href="{{ route('dashboard') }}" class="px-6 py-2 bg-slate-700 text-white font-bold rounded-lg hover:bg-slate-600 transition-all">
            Cancel
        </a>
    </div>

    <div class="glass-card p-8">
        <form method="POST" action="{{ route('callers.store') }}" dir="rtl">
            @csrf
            
            <div class="grid grid-cols-1 gap-6">
                <!-- Name -->
                <div>
                    <label for="name" class="form-label">الاسم الكامل</label>
                    <input type="text" id="name" name="name" required value="{{ old('name') }}" class="form-input" placeholder="أدخل اسم المتصل">
                    @error('name') <span class="text-red-400 text-sm mt-1">{{ $message }}</span> @enderror
                </div>

                <!-- CPR -->
                <div>
                    <label for="cpr" class="form-label">رقم الهوية (CPR)</label>
                    <input type="text" id="cpr" name="cpr" required value="{{ old('cpr') }}" pattern="\d*" title="Please enter a valid CPR number." class="form-input" placeholder="أدخل رقم الهوية">
                    @error('cpr') <span class="text-red-400 text-sm mt-1">{{ $message }}</span> @enderror
                </div>

                <!-- Phone -->
                <div>
                    <label for="phone_number" class="form-label">رقم الهاتف</label>
                    <input type="tel" id="phone_number" name="phone_number" required value="{{ old('phone_number') }}" class="form-input" placeholder="أدخل رقم الهاتف">
                    @error('phone_number') <span class="text-red-400 text-sm mt-1">{{ $message }}</span> @enderror
                </div>

                <!-- Caller Type -->
                <div>
                    <label for="caller_type" class="form-label">نوع المتصل</label>
                    <select id="caller_type" name="caller_type" required class="form-input">
                        <option value="individual" {{ old('caller_type') == 'individual' ? 'selected' : '' }}>فرد</option>
                        <option value="family" {{ old('caller_type') == 'family' ? 'selected' : '' }}>عائلة</option>
                    </select>
                    @error('caller_type') <span class="text-red-400 text-sm mt-1">{{ $message }}</span> @enderror
                </div>

                <!-- Hits (Optional for edit, but let's keep it simple for create) -->
                <input type="hidden" name="is_new_caller" value="1">
            </div>

            <div class="mt-10">
                <button type="submit" class="w-full py-3 bg-gradient-to-r from-yellow-500 to-amber-600 text-black font-bold rounded-xl hover:shadow-lg transition-all transform hover:scale-[1.02]">
                    حفظ المتصل
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
