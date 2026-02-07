@extends('layouts.app')

@push('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Changa:wght@500;700;800&family=Tajawal:wght@300;400;500;700;800&display=swap');
    
    :root {
        --dashboard-ink: #0b1220;
        --dashboard-ink-soft: #0f172a;
        --dashboard-accent: #f59e0b;
        --dashboard-accent-2: #f97316;
        --dashboard-blue: #38bdf8;
        --dashboard-violet: #a855f7;
        --dashboard-emerald: #34d399;
        --dashboard-rose: #fb7185;
        --dashboard-font: 'Tajawal', sans-serif;
        --dashboard-display: 'Changa', 'Tajawal', sans-serif;
    }

    /* Override Jetstream Default Layout Background */
    .min-h-screen.bg-gray-100 {
        background:
            radial-gradient(circle at 20% 10%, rgba(56, 189, 248, 0.12), transparent 45%),
            radial-gradient(circle at 80% 0%, rgba(168, 85, 247, 0.15), transparent 45%),
            radial-gradient(circle at 0% 100%, rgba(249, 115, 22, 0.12), transparent 40%),
            linear-gradient(180deg, #0b1220 0%, #0f172a 100%) !important;
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
        font-family: var(--dashboard-font);
        color: #e2e8f0;
    }
    
    .dashboard-container {
        padding: clamp(1.2rem, 3vw, 3rem);
        display: flex;
        flex-direction: column;
        gap: 2rem;
    }

    .dashboard-header {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 1.5rem;
        padding: 1.5rem 2rem;
        border-radius: 24px;
        background: linear-gradient(120deg, rgba(15, 23, 42, 0.9), rgba(30, 41, 59, 0.7));
        border: 1px solid rgba(148, 163, 184, 0.15);
        box-shadow: 0 30px 80px rgba(2, 6, 23, 0.45);
        position: relative;
        overflow: hidden;
    }

    .dashboard-header::after {
        content: '';
        position: absolute;
        inset: 0;
        background: radial-gradient(circle at top right, rgba(251, 191, 36, 0.18), transparent 55%);
        pointer-events: none;
    }

    .dashboard-title {
        font-family: var(--dashboard-display);
        font-size: clamp(1.8rem, 3vw, 2.6rem);
        font-weight: 800;
        color: #fde68a;
        letter-spacing: 0.5px;
    }

    .dashboard-subtitle {
        color: #cbd5e1;
        max-width: 520px;
        margin-top: 0.5rem;
        line-height: 1.7;
    }

    .dashboard-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
        align-items: center;
    }

    /* Glassmorphism Cards */
    .glass-card {
        background: rgba(15, 23, 42, 0.7);
        backdrop-filter: blur(18px);
        border: 1px solid rgba(148, 163, 184, 0.15);
        border-radius: 20px;
        box-shadow: 0 25px 45px rgba(2, 6, 23, 0.35);
    }

    .stat-grid {
        display: grid;
        gap: 1.5rem;
        grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
    }

    .stat-card {
        background: linear-gradient(145deg, rgba(30, 41, 59, 0.9), rgba(15, 23, 42, 0.9));
        border-top: 4px solid var(--dashboard-accent);
        padding: 1.5rem;
        position: relative;
        overflow: hidden;
    }

    .stat-card::after {
        content: '';
        position: absolute;
        inset: 0;
        background: radial-gradient(circle at top right, rgba(251, 191, 36, 0.12), transparent 55%);
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .stat-card:hover::after {
        opacity: 1;
    }

    .stat-label {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.12em;
        color: #94a3b8;
    }

    .stat-value {
        font-size: clamp(1.8rem, 2.5vw, 2.6rem);
        font-weight: 800;
        color: #f8fafc;
        margin-top: 0.75rem;
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
        text-transform: uppercase;
        letter-spacing: 0.08em;
        font-size: 0.7rem;
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

    .rubber-button {
        background: linear-gradient(160deg, #fbbf24 0%, #f59e0b 35%, #d97706 100%);
        color: #0f172a;
        font-size: clamp(1.2rem, 2.8vw, 2rem);
        font-weight: 900;
        padding: 1.2rem 2.6rem;
        border-radius: 9999px;
        border: 2px solid rgba(255, 255, 255, 0.2);
        cursor: pointer;
        box-shadow:
            inset 0 -6px 12px rgba(124, 45, 18, 0.35),
            inset 0 4px 8px rgba(255, 255, 255, 0.25),
            0 14px 30px rgba(245, 158, 11, 0.4);
        text-transform: uppercase;
        letter-spacing: 1px;
        transition: transform 0.15s ease, box-shadow 0.2s ease, opacity 0.2s ease;
    }

    .rubber-button:hover {
        transform: translateY(-1px) scale(1.01);
        box-shadow:
            inset 0 -6px 12px rgba(124, 45, 18, 0.4),
            inset 0 4px 10px rgba(255, 255, 255, 0.3),
            0 18px 36px rgba(245, 158, 11, 0.45);
    }

    .rubber-button:active {
        transform: translateY(2px) scale(0.99);
        box-shadow:
            inset 0 -2px 6px rgba(124, 45, 18, 0.5),
            inset 0 2px 6px rgba(255, 255, 255, 0.2),
            0 10px 18px rgba(245, 158, 11, 0.35);
    }

    .rubber-button:disabled {
        opacity: 0.55;
        cursor: not-allowed;
        transform: none;
    }

    @keyframes winnerSpin {
        0% { transform: translateY(0) scale(1); }
        50% { transform: translateY(-6px) scale(1.02); }
        100% { transform: translateY(0) scale(1); }
    }

    @media (max-width: 900px) {
        .dashboard-header {
            padding: 1.25rem;
        }

        .dashboard-actions {
            width: 100%;
            justify-content: flex-start;
        }
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

        .rubber-button {
            width: 100%;
        }

        .custom-table thead {
            display: none;
        }

        .custom-table,
        .custom-table tbody,
        .custom-table tr,
        .custom-table td {
            display: block;
            width: 100%;
        }

        .custom-table tr {
            margin-bottom: 1rem;
            border: 1px solid rgba(148, 163, 184, 0.15);
            border-radius: 16px;
            overflow: hidden;
        }

        .custom-table td {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            padding: 0.85rem 1rem;
        }

        .custom-table td::before {
            content: attr(data-label);
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            font-size: 0.7rem;
        }
    }
</style>
@endpush

@section('content')
<div class="dashboard-container" x-data="callerDashboard()">
    <div class="dashboard-header">
        <div>
            <h1 class="dashboard-title">لوحة التحكم - المتصلين</h1>
            <p class="dashboard-subtitle">كل بيانات المشاركين في مكان واحد مع سحب فائز سريع وتصفية فورية.</p>
        </div>
        <div class="dashboard-actions">
            <a href="{{ route('callers.create') }}" class="px-6 py-3 bg-gradient-to-r from-yellow-400 to-amber-500 text-black font-bold rounded-xl hover:shadow-lg transition-all">
                + إضافة متصل
            </a>
            <a href="{{ route('winners') }}" class="px-6 py-3 bg-white/10 text-white font-semibold rounded-xl border border-white/20 hover:bg-white/20 transition-all">
                عرض الفائزين
            </a>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="stat-grid">
        <div class="glass-card stat-card">
            <span class="stat-label">Total Callers</span>
            <div class="stat-value" x-text="stats.total"></div>
        </div>
        <div class="glass-card stat-card" style="border-top-color: var(--dashboard-emerald);">
            <span class="stat-label">Winners</span>
            <div class="stat-value" x-text="stats.winners"></div>
        </div>
        <div class="glass-card stat-card" style="border-top-color: var(--dashboard-violet);">
            <span class="stat-label">Families</span>
            <div class="stat-value" x-text="stats.families"></div>
        </div>
        <div class="glass-card stat-card" style="border-top-color: var(--dashboard-rose);">
            <span class="stat-label">Total Hits</span>
            <div class="stat-value" x-text="stats.hits"></div>
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
                <button class="rubber-button" @click="pickRandomWinner" :disabled="isPicking || callers.length === 0">
                    اختيار الفائز العشوائي
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
                            <td class="font-medium text-white" data-label="Name" x-text="caller.name"></td>
                            <td class="font-mono text-yellow-500" data-label="Phone" x-text="caller.phone"></td>
                            <td class="font-mono text-gray-400" data-label="CPR" x-text="caller.cpr"></td>
                            <td data-label="Hits">
                                <span class="badge badge-gold" x-text="caller.hits || 0"></span>
                            </td>
                            <td data-label="Type">
                                <span class="badge" :class="caller.is_family ? 'badge-purple' : 'badge-green'" x-text="caller.is_family ? 'FAMILY' : 'INDIVIDUAL'"></span>
                            </td>
                            <td data-label="Status">
                                <span class="badge" :class="caller.is_winner ? 'badge-green' : 'bg-gray-700 text-gray-300'">
                                    <template x-if="caller.is_winner">
                                        <span>🏆 WINNER</span>
                                    </template>
                                    <template x-if="!caller.is_winner">
                                        <span>Active</span>
                                    </template>
                                </span>
                            </td>
                            <td data-label="Actions">
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

