<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Quick Stats -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Quick Actions</h3>
                        <div class="space-y-3">
                            <a href="{{ route('admin.callers.index') }}" class="block bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md text-center transition-colors">
                                Manage Callers
                            </a>
                            <a href="{{ route('admin.callers.winners') }}" class="block bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-md text-center transition-colors">
                                View Winners
                            </a>
                        </div>
                    </div>
                    
                    <!-- Simple Stats -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">System Stats</h3>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Total Callers:</span>
                                <span class="font-semibold">{{ \App\Models\Caller::count() }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Total Winners:</span>
                                <span class="font-semibold">{{ \App\Models\Caller::where('is_winner', true)->count() }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Today's Callers:</span>
                                <span class="font-semibold">{{ \App\Models\Caller::whereDate('created_at', today())->count() }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
