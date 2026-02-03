@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Callers Management</h1>
    
    <div class="mb-3">
        <div class="btn-group mb-2">
            <a href="{{ route('winners') }}" class="btn btn-warning">View Winners</a>
            <a href="{{ route('families') }}" class="btn btn-info"><i class="fas fa-users me-1"></i> View All Families</a>
        </div>
        <div class="btn-group ms-2 mb-2">
            <button id="filter-families" class="btn btn-primary">Filter: Families Only</button>
            <button id="filter-individuals" class="btn btn-secondary">Filter: Individuals Only</button>
            <button id="filter-reset" class="btn btn-outline-dark">Reset Filters</button>
        </div>
        <!-- Column visibility toggles -->
        <div class="form-check form-check-inline ms-3">
            <input class="form-check-input" type="checkbox" id="toggle-status-column" value="status">
            <label class="form-check-label" for="toggle-status-column">
                <i class="fas fa-eye"></i> Show Status
            </label>
        </div>
    </div>
    
    <!-- This button will be hidden by CSS -->
    <button id="add-caller-button" class="btn btn-primary">Add Caller</button>
    
    <table class="table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Phone</th>
                <th>CPR</th>
                <th class="status-column" style="display: none;">Status</th>
                <th>Winner</th>
                <th>Family</th>
                <th class="entry-type">النوع</th>
            </tr>
        </thead>
        <tbody>
            @foreach($callers as $caller)
            <tr>
                <td class="caller-name-cell">{{ $caller->name }}</td>
                <td>{{ $caller->phone }}</td>
                <td>{{ $caller->cpr }}</td>
                <td class="status-column" style="display: none;">
                    <span class="badge status-{{ strtolower($caller->status ?? 'pending') }}">
                        {{ $caller->status ?? 'PENDING' }}
                    </span>
                </td>
                <td>
                    <button
                        class="btn-winner-toggle btn {{ $caller->is_winner ? 'btn-success is-winner disabled' : 'btn-outline-secondary not-winner' }}"
                        data-caller-id="{{ $caller->id }}" 
                        data-is-winner="{{ $caller->is_winner ? 'true' : 'false' }}" 
                        title="Once marked as winner, cannot be changed back" 
                        {{ $caller->is_winner ? 'disabled' : '' }}>
                        <i class="fas fa-trophy me-1"></i> {{ $caller->is_winner ? 'WINNER' : 'Mark as Winner' }}
                    </button>
                </td>
                 <td>
                     <button 
                         class="btn-family-toggle btn {{ $caller->is_family ? 'btn-purple is-family' : 'btn-outline-secondary not-family' }}"
                         data-caller-id="{{ $caller->id }}"
                         data-is-family="{{ $caller->is_family ? 'true' : 'false' }}"
                         title="Click to toggle family status"
                     >
                         <i class="fas fa-users me-1"></i>
                         {{ $caller->is_family ? 'FAMILY' : 'INDIVIDUAL' }}
                     </button>
                 </td>
                <td class="entry-type-cell" data-is-family="{{ $caller->is_family ? 'true' : 'false' }}">
                    {{ $caller->is_family ? 'FAMILY' : 'INDIVIDUAL' }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/caller-status.js') }}"></script>
<script>
    // Column visibility toggle
    document.addEventListener('DOMContentLoaded', function() {
        const statusToggle = document.getElementById('toggle-status-column');
        
        if (statusToggle) {
            // Check if status column visibility preference is in localStorage
            const showStatus = localStorage.getItem('show_status_column') === 'true';
            statusToggle.checked = showStatus;
            toggleStatusColumnVisibility(showStatus);
            
            statusToggle.addEventListener('change', function() {
                toggleStatusColumnVisibility(this.checked);
                localStorage.setItem('show_status_column', this.checked);
            });
        }
        
        function toggleStatusColumnVisibility(show) {
            document.querySelectorAll('.status-column').forEach(el => 
                el.style.display = show ? 'table-cell' : 'none');
        }
    });
</script>
@endpush
