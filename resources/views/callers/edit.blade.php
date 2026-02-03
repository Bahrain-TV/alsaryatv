<x-layouts.app title="تعديل بيانات المتصل">
    <div class="container mx-auto px-4 py-8">
        <div class="mb-6">
            <h1 class="text-2xl font-bold mb-2">تعديل بيانات المتصل</h1>
            <a href="{{ route('dashboard') }}" class="text-indigo-600 hover:text-indigo-800">
                &larr; العودة إلى لوحة التحكم
            </a>
        </div>

        @if ($errors->any())
        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
            <strong>يرجى تصحيح الأخطاء التالية:</strong>
            <ul class="mt-2 list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="p-6">
                <form method="POST" action="{{ route('callers.update', $caller->id) }}" class="space-y-4">
                    @csrf
                    @method('PUT')
                    
                    <input type="hidden" name="id" value="{{ $caller->id }}">
                    <input type="hidden" name="is_update" value="1">
                    <input type="hidden" name="is_family" value="{{ $caller->is_family ? 1 : 0 }}">
                    <input type="hidden" name="caller_type" value="{{ $caller->is_family ? 'family' : 'individual' }}">
                    
                    <!-- Status Badge -->
                    <div class="mb-4">
                        <span class="px-3 py-1 text-sm rounded-full {{ $caller->is_family ? 'bg-orange-100 text-orange-800' : 'bg-indigo-100 text-indigo-800' }}">
                            {{ $caller->is_family ? 'عائلة' : 'فرد' }}
                        </span>
                        
                        @if($caller->is_winner)
                        <span class="px-3 py-1 text-sm rounded-full bg-green-100 text-green-800 mr-2">
                            فائز
                        </span>
                        @endif
                    </div>
                    
                    <!-- Basic Information -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">الاسم الكامل</label>
                            <input type="text" name="name" id="name" value="{{ old('name', $caller->name) }}" required
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rtl">
                        </div>
                        
                        <div>
                            <label for="cpr" class="block text-sm font-medium text-gray-700 mb-1">الرقم الشخصي</label>
                            <input type="text" name="cpr" id="cpr" value="{{ old('cpr', $caller->cpr) }}" required
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rtl">
                        </div>
                        
                        <div>
                            <label for="phone_number" class="block text-sm font-medium text-gray-700 mb-1">رقم الهاتف</label>
                            <input type="tel" name="phone_number" id="phone_number" value="{{ old('phone_number', $caller->phone_number) }}" required
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rtl">
                        </div>
                        
                        <div>
                            <label for="hits" class="block text-sm font-medium text-gray-700 mb-1">عدد المشاركات</label>
                            <input type="number" name="hits" id="hits" value="{{ old('hits', $caller->hits) }}" min="1"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        </div>
                    </div>

                    <!-- Admin Options -->
                    <div class="pt-4 border-t border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-3">خيارات إضافية</h3>
                        
                        <div class="flex flex-col space-y-4 md:flex-row md:space-y-0 md:space-x-4 md:space-x-reverse">
                            <div class="flex items-center">
                                <input type="checkbox" name="is_winner" id="is_winner" value="1" 
                                       {{ old('is_winner', $caller->is_winner) ? 'checked' : '' }}
                                       class="h-5 w-5 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                <label for="is_winner" class="mr-2 block text-sm text-gray-700">تعيين كفائز</label>
                            </div>
                            
                            <div class="flex items-center">
                                <input type="checkbox" name="is_contacted" id="is_contacted" value="1" 
                                       {{ old('is_contacted', $caller->is_contacted) ? 'checked' : '' }}
                                       class="h-5 w-5 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                <label for="is_contacted" class="mr-2 block text-sm text-gray-700">تم التواصل</label>
                            </div>

                            <div class="flex items-center">
                                <input type="checkbox" name="is_verified" id="is_verified" value="1" 
                                       {{ old('is_verified', $caller->is_verified) ? 'checked' : '' }}
                                       class="h-5 w-5 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                <label for="is_verified" class="mr-2 block text-sm text-gray-700">تم التحقق</label>
                            </div>
                        </div>
                    </div>

                    <!-- Notes Section -->
                    <div class="pt-4">
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">ملاحظات</label>
                        <textarea name="notes" id="notes" rows="3"
                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rtl">{{ old('notes', $caller->notes) }}</textarea>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center justify-end space-x-3 space-x-reverse pt-4">
                        <button type="button" onclick="confirmDelete({{ $caller->id }})"
                            class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                            حذف
                        </button>
                        
                        <button type="submit"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                            حفظ التغييرات
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Delete Form (Hidden) -->
        <form id="delete-form" method="POST" style="display: none;">
            @csrf
            @method('DELETE')
        </form>
    </div>

    <script>
        function confirmDelete(id) {
            if (confirm('هل أنت متأكد من رغبتك في حذف هذا المتصل؟ لا يمكن التراجع عن هذا الإجراء.')) {
                const form = document.getElementById('delete-form');
                form.action = `/callers/${id}`;
                form.submit();
            }
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            // Add any additional JavaScript functionality here
            // For example, form validation or dynamic UI behaviors
        });
    </script>
</x-layouts.app>
