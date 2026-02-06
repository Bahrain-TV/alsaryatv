<div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
    <div class="flex items-center justify-between mb-6">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white">سجل الفائزين</h3>
        <div class="flex space-x-2">
            <button class="px-3 py-1 text-sm rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200">الكل</button>
            <button class="px-3 py-1 text-sm rounded-lg bg-amber-500 text-white">هذا الشهر</button>
            <button class="px-3 py-1 text-sm rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200">هذا الأسبوع</button>
        </div>
    </div>
    
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead>
                <tr>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">الاسم</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">رقم الهاتف</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">وقت الفوز</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">الحالة</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                <!-- Winner 1 -->
                <tr>
                    <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">فاطمة علي</td>
                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">055XXXXXX</td>
                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">10:30 ص - اليوم</td>
                    <td class="px-4 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-emerald-100 dark:bg-emerald-800/30 text-emerald-800 dark:text-emerald-200">
                            فائز
                        </span>
                    </td>
                </tr>
                
                <!-- Winner 2 -->
                <tr>
                    <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">محمد أحمد</td>
                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">050XXXXXX</td>
                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">2:15 م - أمس</td>
                    <td class="px-4 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-emerald-100 dark:bg-emerald-800/30 text-emerald-800 dark:text-emerald-200">
                            فائز
                        </span>
                    </td>
                </tr>
                
                <!-- Winner 3 -->
                <tr>
                    <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">نورا سعيد</td>
                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">054XXXXXX</td>
                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">4:45 م - 15 أبريل</td>
                    <td class="px-4 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-emerald-100 dark:bg-emerald-800/30 text-emerald-800 dark:text-emerald-200">
                            فائز
                        </span>
                    </td>
                </tr>
                
                <!-- Winner 4 -->
                <tr>
                    <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">علي عبدالله</td>
                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">055XXXXXX</td>
                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">7:20 م - 14 أبريل</td>
                    <td class="px-4 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-emerald-100 dark:bg-emerald-800/30 text-emerald-800 dark:text-emerald-200">
                            فائز
                        </span>
                    </td>
                </tr>
                
                <!-- Winner 5 -->
                <tr>
                    <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">سارة خالد</td>
                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">050XXXXXX</td>
                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">9:10 ص - 13 أبريل</td>
                    <td class="px-4 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-emerald-100 dark:bg-emerald-800/30 text-emerald-800 dark:text-emerald-200">
                            فائز
                        </span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <div class="mt-6 flex items-center justify-between">
        <div class="text-sm text-gray-700 dark:text-gray-400">
            عرض <span class="font-medium">1</span> إلى <span class="font-medium">5</span> من <span class="font-medium">12</span> نتائج
        </div>
        <div class="flex space-x-2">
            <button class="px-3 py-1 rounded-md bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 text-sm">
                السابق
            </button>
            <button class="px-3 py-1 rounded-md bg-amber-500 text-white text-sm">
                1
            </button>
            <button class="px-3 py-1 rounded-md bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 text-sm">
                2
            </button>
            <button class="px-3 py-1 rounded-md bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 text-sm">
                3
            </button>
            <button class="px-3 py-1 rounded-md bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 text-sm">
                التالي
            </button>
        </div>
    </div>
</div>