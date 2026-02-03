document.addEventListener('DOMContentLoaded', function() {
    // Status cycle: PENDING -> REJECTED -> APPROVED
    const statusCycle = ['PENDING', 'REJECTED', 'APPROVED'];
    
    // Initialize status buttons
    initStatusButtons();
    
    // Initialize winner toggle buttons
    initWinnerButtons();
    
    // Initialize family toggle buttons
    initFamilyButtons();
    
    // Initialize filter buttons
    initFilterButtons();
    
    function initStatusButtons() {
        document.querySelectorAll('.btn-status-toggle').forEach(button => {
            button.addEventListener('click', function() {
                const callerId = this.getAttribute('data-caller-id');
                const currentStatus = this.getAttribute('data-status');
                
                // Find next status in cycle
                let currentIndex = statusCycle.indexOf(currentStatus);
                let nextIndex = (currentIndex + 1) % statusCycle.length;
                let nextStatus = statusCycle[nextIndex];
                
                // Update button UI
                updateButtonStatus(this, nextStatus);
                
                // Save status change via AJAX
                updateCallerStatus(callerId, nextStatus);
            });
        });
    }
    
    function updateButtonStatus(button, status) {
        // Remove all status classes
        button.classList.remove('status-pending', 'status-rejected', 'status-approved');
        
        // Add new status class
        button.classList.add('status-' + status.toLowerCase());
        
        // Update text and data attribute
        button.textContent = status;
        button.setAttribute('data-status', status);
        
        // If approved, trigger animation and live stage
        if (status === 'APPROVED') {
            const row = button.closest('tr');
            row.classList.add('approved-animation');
            
            // Send to live stage
            sendToLiveStage(button.getAttribute('data-caller-id'));
            
            // Animation with yoyo effect (forward and reverse)
            setTimeout(() => {
                // Clear the animation
                row.classList.remove('approved-animation');
                
                // Add it again with yoyo effect
                setTimeout(() => {
                    row.classList.add('approved-animation');
                    
                    // Remove again after animation completes
                    setTimeout(() => {
                        row.classList.remove('approved-animation');
                    }, 1000);
                }, 50);
            }, 1000);
        }
    }
    
    function updateCallerStatus(callerId, status) {
        fetch('/api/callers/' + callerId + '/status', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ status: status })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('Status updated successfully');
            } else {
                console.error('Error updating status');
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }
    
    function sendToLiveStage(callerId) {
        fetch('/api/callers/' + callerId + '/live', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('Caller sent to live stage');
            }
        })
        .catch(error => {
            console.error('Error sending to live stage:', error);
        });
    }
    
    function initWinnerButtons() {
        document.querySelectorAll('.btn-winner-toggle').forEach(button => {
            button.addEventListener('click', function() {
                const callerId = this.getAttribute('data-caller-id');
                const isWinner = this.getAttribute('data-is-winner') === 'true';
                
                // If already a winner, don't allow toggling back
                if (isWinner) {
                    return; // Exit early, no toggling back allowed
                }
                
                // Mark as winner in UI (one way only)
                updateWinnerStatus(this, true);
                
                // Save winner status change via AJAX
                toggleWinnerStatus(callerId);
            });
        });
    }
    
    function updateWinnerStatus(button, isWinner) {
        // Remove all winner classes
        button.classList.remove('is-winner', 'not-winner');
        
        // Add new winner class
        button.classList.add(isWinner ? 'is-winner' : 'not-winner');
        
        // Update text and data attribute
        button.innerHTML = isWinner ? '<i class="fas fa-trophy me-1"></i> WINNER' : '<i class="fas fa-trophy me-1"></i> Mark as Winner';
        button.setAttribute('data-is-winner', isWinner ? 'true' : 'false');
        
        // If marked as winner, also disable the button
        if (isWinner) {
            // Disable the button
            button.disabled = true;
            button.classList.add('disabled');
            
            // Update button styling
            button.classList.remove('btn-outline-secondary');
            button.classList.add('btn-success');
            
            // Add animation when becoming a winner
            const row = button.closest('tr');
            row.classList.add('winner-animation');
            
            // Remove animation after it completes
            setTimeout(() => row.classList.remove('winner-animation'), 2000);
        }
    }
    
    function toggleWinnerStatus(callerId) {
        fetch('/api/callers/' + callerId + '/toggle-winner', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('Winner status updated successfully');
                
                // Make button more prominent if it's a winner
                const button = document.querySelector(`[data-caller-id="${callerId}"].btn-winner-toggle`);
                if (button) {
                    if (data.is_winner) {
                        button.classList.replace('btn-outline-secondary', 'btn-success');
                    }
                }
            } else {
                console.error('Error updating winner status');
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }

    function initFamilyButtons() {
        document.querySelectorAll('.btn-family-toggle').forEach(button => {
            button.addEventListener('click', function() {
                const callerId = this.getAttribute('data-caller-id');
                // Add updating class while processing
                this.classList.add('updating');
                const currentStatus = this.getAttribute('data-is-family') === 'true';
                
                // Toggle the family status in UI
                updateFamilyStatus(this, !currentStatus);
                
                // Save family status change via AJAX
                toggleFamilyStatus(callerId);
                
                // Remove updating class after a short delay
                setTimeout(() => this.classList.remove('updating'), 300);
            });
        });
    }
    
    function updateFamilyStatus(button, isFamily) {
        // Remove all family classes
        button.classList.remove('is-family', 'not-family');
        
        // Add new family class
        button.classList.add(isFamily ? 'is-family' : 'not-family');
        
        // Update text and data attribute
        button.innerHTML = isFamily ? '<i class="fas fa-users me-1"></i> FAMILY' : '<i class="fas fa-users me-1"></i> INDIVIDUAL';
        
        // Update button styling
        button.classList.remove('btn-purple', 'btn-outline-secondary');
        button.classList.add(isFamily ? 'btn-purple' : 'btn-outline-secondary');
        button.setAttribute('data-is-family', isFamily ? 'true' : 'false');
        
        // Update the entry type cell in the same row
        const row = button.closest('tr');
        if (row) {
            const entryTypeCell = row.querySelector('.entry-type-cell');
            if (entryTypeCell) {
                entryTypeCell.textContent = isFamily ? 'FAMILY' : 'INDIVIDUAL';
                entryTypeCell.setAttribute('data-is-family', isFamily ? 'true' : 'false');
            }
        }
        
        // Add animation if changing to family
        if (isFamily) {
            row.classList.add('family-animation');
            
            // Remove animation after it completes
            setTimeout(() => {
                row.classList.remove('family-animation');
            }, 2000);
        }
    }
    
    function toggleFamilyStatus(callerId) {
        fetch('/api/callers/' + callerId + '/toggle-family', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('Family status updated successfully');
                
                // Update button styling based on server response
                const button = document.querySelector(`[data-caller-id="${callerId}"].btn-family-toggle`);
                if (button) {
                    if (data.is_family) {
                        button.classList.replace('btn-outline-secondary', 'btn-purple');
                    } else {
                        button.classList.replace('btn-purple', 'btn-outline-secondary');
                    }
                }
                
                // Update the entry type cell in case it wasn't updated from the button
                const row = document.querySelector(`[data-caller-id="${callerId}"]`).closest('tr');
                if (row) {
                    const entryTypeCell = row.querySelector('.entry-type-cell');
                    if (entryTypeCell) {
                        entryTypeCell.textContent = data.is_family ? 'FAMILY' : 'INDIVIDUAL';
                        entryTypeCell.setAttribute('data-is-family', data.is_family ? 'true' : 'false');
                    }
                }
            } else {
                console.error('Error updating family status');
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }
    
    function initFilterButtons() {
        // Filter by family entries
        document.getElementById('filter-families')?.addEventListener('click', function() {
            filterRows('family', true);
            highlightActiveFilter(this);
            this.classList.add('btn-primary');
        });
        
        // Filter by individual entries
        document.getElementById('filter-individuals')?.addEventListener('click', function() {
            filterRows('family', false);
            highlightActiveFilter(this);
        });
        
        // Reset filters
        document.getElementById('filter-reset')?.addEventListener('click', function() {
            resetFilters();
            highlightActiveFilter(null);
        });
    }
    
    function filterRows(filterType, value) {
        const rows = document.querySelectorAll('tbody tr');
        
        rows.forEach(row => {
            if (filterType === 'family') {
                const isFamily = row.querySelector('.entry-type-cell').getAttribute('data-is-family') === 'true';
                row.style.display = (isFamily === value) ? '' : 'none';
            }
        });
    }
    
    function resetFilters() {
        const rows = document.querySelectorAll('tbody tr');
        rows.forEach(row => {
            row.style.display = '';
        });
    }
    
    function highlightActiveFilter(activeButton) {
        // Remove active class from all filter buttons
        document.querySelectorAll('#filter-families, #filter-individuals').forEach(btn => {
            btn.classList.remove('active-filter');
        });
        
        // Reset button styles
        document.getElementById('filter-families')?.classList.remove('btn-primary');
        document.getElementById('filter-families')?.classList.add('btn-secondary');
        document.getElementById('filter-individuals')?.classList.remove('btn-primary');
        document.getElementById('filter-individuals')?.classList.add('btn-secondary');
        
        // Add primary style to active button
        // Add active class to the clicked button
        if (activeButton) {
            activeButton.classList.add('active-filter');
        }
    }
});
