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
    header h2 {
        color: #fbbf24 !important;
    }

    body {
        font-family: 'Tajawal', sans-serif;
        color: #e2e8f0;
    }
    
    .dashboard-container {
        padding: 2rem;
    }

    .glass-card {
        background: rgba(30, 41, 59, 0.7);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 16px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }

    .custom-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .custom-table th {
        background: rgba(15, 23, 42, 0.8);
        color: #fbbf24;
        font-weight: 600;
        padding: 1rem;
        text-align: left;
        border-bottom: 2px solid rgba(251, 191, 36, 0.2);
    }

    .custom-table td {
        padding: 1rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        color: #cbd5e1;
        background: rgba(30, 41, 59, 0.4);
    }

    .badge {
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .badge-gold { background: rgba(251, 191, 36, 0.1); color: #fbbf24; border: 1px solid rgba(251, 191, 36, 0.2); }
    .badge-green { background: rgba(16, 185, 129, 0.1); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.2); }
    .badge-purple { background: rgba(139, 92, 246, 0.1); color: #a78bfa; border: 1px solid rgba(139, 92, 246, 0.2); }
</style>
@endpush

@section('content')
<div class="dashboard-container">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-purple-400">👨‍👩‍👧‍👦 Family Entries</h1>
        <a href="{{ route('dashboard') }}" class="px-6 py-2 bg-slate-700 text-white font-bold rounded-lg hover:bg-slate-600 transition-all">
            Back to Dashboard
        </a>
    </div>

    @if($families->isEmpty())
        <div class="glass-card p-12 text-center text-gray-500">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto mb-4 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            <p class="text-xl">No family entries found.</p>
        </div>
    @else
        <div class="glass-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Phone</th>
                            <th>CPR</th>
                            <th>Status</th>
                            <th>Winner Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($families as $family)
                            <tr>
                                <td class="font-medium text-white">{{ $family->name }}</td>
                                <td class="font-mono text-yellow-500">{{ $family->phone }}</td>
                                <td class="font-mono text-gray-400">{{ $family->cpr }}</td>
                                <td>
                                    <span class="badge badge-purple">FAMILY</span>
                                </td>
                                <td>
                                    @if($family->is_winner)
                                        <span class="badge badge-green">🏆 WINNER</span>
                                    @else
                                        <span class="bg-gray-700 text-gray-300 badge">Active</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection