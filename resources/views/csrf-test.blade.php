@php
    // Test endpoint to verify CSRF is working
@endphp

<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <h1 class="text-2xl font-bold text-center mb-4">CSRF Token Test</h1>
        </x-slot>

        <div class="mb-4 p-4 bg-blue-100 rounded">
            <h2 class="font-bold mb-2">Current Session Info:</h2>
            <ul class="text-sm space-y-1">
                <li><strong>Session ID:</strong> {{ session()->getId() }}</li>
                <li><strong>CSRF Token:</strong> {{ substr(csrf_token(), 0, 10) }}...{{ substr(csrf_token(), -10) }}</li>
                <li><strong>User Agent:</strong> {{ request()->userAgent() }}</li>
                <li><strong>IP Address:</strong> {{ request()->ip() }}</li>
            </ul>
        </div>

        <form method="POST" action="{{ route('csrf.test') }}">
            @csrf

            <div class="mb-4 p-3 bg-green-100 rounded">
                <p class="text-sm font-mono">@csrf directive is present ✓</p>
            </div>

            <div class="mb-4 p-3 bg-yellow-100 rounded">
                <p class="text-sm"><strong>Form Details:</strong></p>
                <ul class="text-xs space-y-1 mt-2">
                    <li>• Method: <code>POST</code></li>
                    <li>• CSRF Token Field Present: <span id="token-status">checking...</span></li>
                    <li>• Meta Tag Token Present: <span id="meta-status">checking...</span></li>
                </ul>
            </div>

            <button type="submit" class="w-full bg-indigo-600 text-white py-2 px-4 rounded font-bold">
                Test CSRF Protection
            </button>
        </form>

        <div class="mt-6 p-4 bg-gray-100 rounded">
            <h3 class="font-bold mb-2">Debug Information:</h3>
            <div class="text-xs font-mono space-y-1">
                <p>Database Driver: <strong>{{ config('session.driver') }}</strong></p>
                <p>Session Domain: <strong>{{ config('session.domain') ?? '(empty)' }}</strong></p>
                <p>Session Secure: <strong>{{ config('session.secure') ? 'true' : 'false' }}</strong></p>
                <p>Session SameSite: <strong>{{ config('session.same_site') }}</strong></p>
                <p>APP Environment: <strong>{{ config('app.env') }}</strong></p>
                <p>APP Debug: <strong>{{ config('app.debug') ? 'ON' : 'OFF' }}</strong></p>
            </div>
        </div>
    </x-authentication-card>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Check for CSRF token in form
            const tokenInput = document.querySelector('input[name="_token"]');
            document.getElementById('token-status').textContent = tokenInput ? '✓ Found' : '✗ Missing';
            
            // Check for CSRF token in meta tag
            const metaToken = document.querySelector('meta[name="csrf-token"]');
            document.getElementById('meta-status').textContent = metaToken ? '✓ Found' : '✗ Missing';

            // Log tokens for debugging
            console.log('=== CSRF Token Test ===');
            console.log('Token from meta:', metaToken?.content.substring(0, 10) + '...');
            console.log('Token from form:', tokenInput?.value.substring(0, 10) + '...');
            console.log('Tokens match:', metaToken?.content === tokenInput?.value);
            console.log('Session cookie:', document.cookie);
            console.log('======================');
        });
    </script>
</x-guest-layout>
