@extends('layouts.app')

@push('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800&display=swap');
    
    /* Override Jetstream Default Layout Background */
    .min-h-screen.bg-gray-100 {
        background: radial-gradient(circle at center, #1e293b 0%, #0f172a 100%) !important;
    }
    
    /* Darken the Header */
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
        padding: clamp(1rem, 2.5vw, 2.5rem);
    }

    /* Glassmorphism Cards */
    .glass-card {
        background: rgba(30, 41, 59, 0.7);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 16px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }

    .stat-card {
        background: linear-gradient(135deg, rgba(30, 41, 59, 0.9) 0%, rgba(15, 23, 42, 0.9) 100%);
        border-left: 4px solid #fbbf24;
    }

    /* Table Styles */
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

    .custom-table tr:hover td {
        background: rgba(251, 191, 36, 0.05);
    }

    /* Badges & Buttons */
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
    
    .btn-action {
        transition: all 0.2s;
        border: 1px solid transparent;
    }
    .btn-action:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    }

    /* Search Input */
    .search-input {
        background: rgba(15, 23, 42, 0.6);
        border: 1px solid rgba(255, 255, 255, 0.1);
        color: white;
        padding: 0.75rem 1rem;
        border-radius: 12px;
        width: 100%;
        transition: all 0.3s;
    }
    .search-input:focus {
        outline: none;
        border-color: #fbbf24;
        box-shadow: 0 0 0 3px rgba(251, 191, 36, 0.1);
    }

    .filters-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        align-items: center;
        justify-content: space-between;
    }

    .filter-buttons {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
        justify-content: center;
    }

    .action-stack {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .winner-picker {
        border: 1px solid rgba(251, 191, 36, 0.2);
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
    }

    .winner-display {
        background: rgba(15, 23, 42, 0.7);
        border: 1px solid rgba(251, 191, 36, 0.2);
        border-radius: 16px;
        padding: 1.5rem;
        text-align: center;
        min-width: min(420px, 100%);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .winner-display.spinning {
        animation: winnerSpin 1s ease-in-out infinite;
        box-shadow: 0 0 40px rgba(251, 191, 36, 0.2);
    }

    .winner-name {
        font-size: clamp(1.5rem, 2.5vw, 2.2rem);
        font-weight: 800;
        color: #fbbf24;
        margin-bottom: 0.5rem;
        letter-spacing: 0.5px;
    }

    .winner-meta {
        font-size: 0.95rem;
        color: #cbd5e1;
        display: flex;
        flex-direction: column;
        gap: 0.35rem;
    }

    .robbery-button {
        background: linear-gradient(135deg, #f97316 0%, #ef4444 45%, #b91c1c 100%);
        color: #0f172a;
        font-size: clamp(1.25rem, 2.8vw, 2rem);
        font-weight: 900;
        padding: 1.25rem 2.5rem;
        border-radius: 18px;
        border: none;
        cursor: pointer;
        box-shadow: 0 12px 24px rgba(239, 68, 68, 0.35);
        text-transform: uppercase;
        letter-spacing: 1px;
        transition: transform 0.2s ease, box-shadow 0.2s ease, opacity 0.2s ease;
    }

    .robbery-button:hover {
        transform: translateY(-2px) scale(1.02);
        box-shadow: 0 18px 32px rgba(239, 68, 68, 0.45);
    }

    .robbery-button:disabled {
        opacity: 0.55;
        cursor: not-allowed;
        transform: none;
    }

    @keyframes winnerSpin {
        0% { transform: translateY(0) scale(1); }
        50% { transform: translateY(-6px) scale(1.02); }
        100% { transform: translateY(0) scale(1); }
    }

    @media (max-width: 768px) {
        .dashboard-container {
            padding: 1rem;
        }

        .filters-actions {
            flex-direction: column;
            align-items: stretch;
        }

        .filter-buttons {
            justify-content: flex-start;
        }

        .custom-table th,
        .custom-table td {
            padding: 0.75rem;
            font-size: 0.85rem;
        }

        .action-stack {
            flex-direction: column;
            align-items: stretch;
        }

        .winner-display {
            min-width: 100%;
        }

        .robbery-button {
            width: 100%;
        }
    }
</style>
@endpush

@section('content')
<div class="dashboard-container" x-data="callerDashboard()">
    
    <!-- Stats Row -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="glass-card stat-card p-6">
            <h3 class="text-gray-400 text-sm font-medium uppercase tracking-wider">Total Callers</h3>
            <p class="text-3xl font-bold text-white mt-2" x-text="stats.total"></p>
        </div>
        <div class="glass-card stat-card p-6" style="border-color: #34d399;">
            <h3 class="text-gray-400 text-sm font-medium uppercase tracking-wider">Winners</h3>
            <p class="text-3xl font-bold text-white mt-2" x-text="stats.winners"></p>
        </div>
        <div class="glass-card stat-card p-6" style="border-color: #a78bfa;">
            <h3 class="text-gray-400 text-sm font-medium uppercase tracking-wider">Families</h3>
            <p class="text-3xl font-bold text-white mt-2" x-text="stats.families"></p>
        </div>
        <div class="glass-card stat-card p-6" style="border-color: #f472b6;">
            <h3 class="text-gray-400 text-sm font-medium uppercase tracking-wider">Total Hits</h3>
            <p class="text-3xl font-bold text-white mt-2" x-text="stats.hits"></p>
        </div>
    </div>

    <!-- Filters & Actions -->
    <div class="glass-card p-6 mb-8 winner-picker">
        <div class="flex flex-col xl:flex-row items-center justify-between gap-6">
            <div class="text-center xl:text-right">
                <h2 class="text-2xl font-extrabold text-yellow-400">سحب الفائز العشوائي</h2>
                <p class="text-sm text-gray-300 mt-2">اضغط الزر الكبير لاختيار فائز واحد مع حركة عشوائية.</p>
            </div>
            <div class="winner-display" :class="{ 'spinning': isPicking }">
                <div class="winner-name" x-text="randomWinner ? randomWinner.name : 'جاهز للسحب'">
                </div>
                <div class="winner-meta">
                    <span x-text="randomWinner ? '📱 ' + randomWinner.phone : '📱 ---'"></span>
                    <span x-text="randomWinner ? '🆔 ' + randomWinner.cpr : '🆔 ---'"></span>
                </div>
            </div>
            <div class="flex flex-col items-center gap-3 w-full xl:w-auto">
                <button class="robbery-button" @click="pickRandomWinner" :disabled="isPicking || callers.length === 0">
                    🔥 BIG ROBBERY
                </button>
                <span x-show="pickError" class="text-sm text-red-400" x-text="pickError"></span>
            </div>
        </div>
    </div>

    <div class="glass-card p-6 mb-8">
        <div class="filters-actions">
            <div class="w-full md:w-1/3">
                <div class="relative">
                    <input type="text" x-model="search" placeholder="Search by Name, CPR, or Phone..." class="search-input">
                    <span class="absolute right-4 top-3.5 text-gray-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </span>
                </div>
            </div>
            
            <div class="filter-buttons">
                <button @click="filterType = 'all'" :class="{'bg-yellow-500 text-black': filterType === 'all', 'bg-slate-700 text-white': filterType !== 'all'}" class="px-4 py-2 rounded-lg font-medium transition-colors">
                    All
                </button>
                <button @click="filterType = 'family'" :class="{'bg-purple-500 text-white': filterType === 'family', 'bg-slate-700 text-white': filterType !== 'family'}" class="px-4 py-2 rounded-lg font-medium transition-colors">
                    Families Only
                </button>
                <button @click="filterType = 'individual'" :class="{'bg-blue-500 text-white': filterType === 'individual', 'bg-slate-700 text-white': filterType !== 'individual'}" class="px-4 py-2 rounded-lg font-medium transition-colors">
                    Individuals
                </button>
                 <button @click="toggleWinnersOnly()" :class="{'bg-green-500 text-white': showWinnersOnly, 'bg-slate-700 text-white': !showWinnersOnly}" class="px-4 py-2 rounded-lg font-medium transition-colors">
                    Winners Only
                </button>
            </div>
            
            <a href="{{ route('callers.create') }}" class="px-6 py-2 bg-gradient-to-r from-yellow-500 to-amber-600 text-black font-bold rounded-lg hover:shadow-lg transition-all transform hover:scale-105">
                + Add Caller
            </a>
        </div>
    </div>

    <!-- Data Table -->
    <div class="glass-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>CPR</th>
                        <th>Hits</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="caller in filteredCallers" :key="caller.id">
                        <tr class="transition-colors duration-200">
                            <td class="font-medium text-white" x-text="caller.name"></td>
                            <td class="font-mono text-yellow-500" x-text="caller.phone"></td>
                            <td class="font-mono text-gray-400" x-text="caller.cpr"></td>
                             <td>
                                <span class="badge badge-gold" x-text="caller.hits || 0"></span>
                            </td>
                            <td>
                                <span class="badge" :class="caller.is_family ? 'badge-purple' : 'badge-green'" x-text="caller.is_family ? 'FAMILY' : 'INDIVIDUAL'"></span>
                            </td>
                            <td>
                                <span class="badge" :class="caller.is_winner ? 'badge-green' : 'bg-gray-700 text-gray-300'">
                                    <template x-if="caller.is_winner">
                                        <span>🏆 WINNER</span>
                                    </template>
                                    <template x-if="!caller.is_winner">
                                        <span>Active</span>
                                    </template>
                                </span>
                            </td>
                            <td>
                                <div class="action-stack">
                                     <!-- Winner Toggle -->
                                    <button 
                                        @click="toggleWinner(caller)"
                                        :class="caller.is_winner ? 'bg-red-500/20 text-red-400 border-red-500/30' : 'bg-green-500/20 text-green-400 border-green-500/30'"
                                        class="px-3 py-1 rounded border text-xs font-bold hover:bg-opacity-30 transition-colors"
                                        x-text="caller.is_winner ? 'Unmark Winner' : 'Mark Winner'"
                                    ></button>
                                    
                                     <a :href="`/callers/${caller.id}/edit`" class="bg-blue-500/20 text-blue-400 border border-blue-500/30 px-3 py-1 rounded text-xs font-bold hover:bg-opacity-30 transition-colors">
                                        Edit
                                    </a>
                                    
                                    <button 
                                        @click="confirmDelete(caller)"
                                        class="bg-red-500/20 text-red-400 border border-red-500/30 px-3 py-1 rounded text-xs font-bold hover:bg-opacity-30 transition-colors"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                    
                     <!-- Empty State -->
                    <tr x-show="filteredCallers.length === 0">
                        <td colspan="7" class="text-center py-12 text-gray-500">
                            <div class="flex flex-col items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mb-4 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>No callers found matching your criteria.</span>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    
    <div class="mt-4 text-center text-xs text-gray-500">
        Showing <span x-text="filteredCallers.length"></span> of <span x-text="callers.length"></span> records
    </div>

    <!-- Hidden Delete Form -->
    <form id="delete-form" method="POST" class="hidden">
        @csrf
        @method('DELETE')
    </form>
</div>

<script src="//unpkg.com/alpinejs" defer></script>
<script>
    function callerDashboard() {
        return {
            search: '',
            filterType: 'all', // all, family, individual
            showWinnersOnly: false,
            callers: @json($callers),
            randomWinner: null,
            isPicking: false,
            pickError: null,
            
            get stats() {
                return {
                    total: this.callers.length,
                    winners: this.callers.filter(c => c.is_winner).length,
                    families: this.callers.filter(c => c.is_family).length,
                    hits: this.callers.reduce((acc, curr) => acc + (parseInt(curr.hits) || 0), 0)
                };
            },

            get filteredCallers() {
                return this.callers.filter(caller => {
                    const matchesSearch = (
                        (caller.name || '').toLowerCase().includes(this.search.toLowerCase()) ||
                        (caller.cpr || '').includes(this.search) ||
                        (caller.phone || '').includes(this.search)
                    );
                    
                    const matchesType = 
                        this.filterType === 'all' ? true :
                        this.filterType === 'family' ? caller.is_family :
                        !caller.is_family;
                        
                    const matchesWinner = this.showWinnersOnly ? caller.is_winner : true;

                    return matchesSearch && matchesType && matchesWinner;
                });
            },
            
            toggleWinnersOnly() {
                this.showWinnersOnly = !this.showWinnersOnly;
            },

            async pickRandomWinner() {
                if (this.isPicking) {
                    return;
                }

                this.pickError = null;
                this.isPicking = true;

                const spinInterval = 120;
                const minSpinMs = 2200;
                const startedAt = Date.now();
                const spinner = setInterval(() => {
                    if (this.callers.length > 0) {
                        this.randomWinner = this.callers[Math.floor(Math.random() * this.callers.length)];
                    }
                }, spinInterval);

                let responseData = null;

                try {
                    const response = await fetch('/callers/random-winner', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    });

                    responseData = await response.json();

                    if (!response.ok || !responseData.success) {
                        throw new Error(responseData.message || 'Failed to select winner');
                    }
                } catch (error) {
                    this.pickError = error.message || 'An error occurred while selecting a winner.';
                }

                const elapsed = Date.now() - startedAt;
                const remaining = Math.max(0, minSpinMs - elapsed);

                setTimeout(() => {
                    clearInterval(spinner);
                    this.isPicking = false;

                    if (responseData && responseData.success) {
                        const winner = responseData.winner;
                        this.randomWinner = winner;

                        const idx = this.callers.findIndex(c => c.id === winner.id);
                        if (idx !== -1) {
                            this.callers[idx].is_winner = true;
                        }
                    }
                }, remaining);
            },
            
            async toggleWinner(caller) {
                // Determine new state
                const originalState = caller.is_winner;
                
                try {
                    const response = await fetch(`/callers/${caller.id}/toggle-winner`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        caller.is_winner = data.is_winner;
                        // Optional: trigger a toast or notification here
                        console.log(data.message);
                    } else {
                        // Revert if failed
                        caller.is_winner = originalState;
                        alert('Failed to update status');
                    }
                } catch (error) {
                    caller.is_winner = originalState;
                    console.error('Error:', error);
                    alert('An error occurred while updating winner status');
                }
            },

            confirmDelete(caller) {
                if (confirm(`Are you sure you want to delete ${caller.name}?`)) {
                    const form = document.getElementById('delete-form');
                    form.action = `/callers/${caller.id}`;
                    form.submit();
                }
            }
        };
    }
</script>
@endsection

