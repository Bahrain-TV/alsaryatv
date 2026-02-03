<x-policy-layout>
    <h1 class="text-4xl font-bold mb-8 text-center text-white">سياسة الخصوصية</h1>
    
    <div class="space-y-6 prose prose-invert prose-lg max-w-none rtl:text-right">
        {!! $policy !!}
    </div>

    <div class="mt-12 text-center">
        <a href="{{ route('home') }}" 
           class="inline-flex items-center px-6 py-3 border border-gray-600 text-base font-medium rounded-md 
           shadow-sm text-gray-200 bg-gray-800/80 hover:bg-gray-700 hover:text-white 
           focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 
           transition-all duration-200 backdrop-blur-sm">
            العودة إلى الرئيسية
        </a>
    </div>

    <div class="mt-8 border-t border-gray-700 pt-4 text-center">
        <p class="text-gray-400 text-sm">
            Useful Information: Last updated on {{ date('Y-m-d') }}. For more details, please contact support@example.com.
        </p>
        <button onclick="window.print()" class="mt-2 inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm rounded hover:bg-blue-500">
            <svg class="h-5 w-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 9V2h12v7M6 9h12v10H6V9zM6 22h12"></path>
            </svg>
            Print as PDF
        </button>
    </div>
</x-policy-layout>
