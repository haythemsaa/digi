/**
 * DigiParc Fleet Management - Custom JavaScript
 * Main application JavaScript
 */

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('DigiParc Fleet Management initialized');

    // Initialize tooltips if Bootstrap is available
    if (typeof bootstrap !== 'undefined') {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }

    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
        alerts.forEach(function(alert) {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);

    // Confirm before leaving page with unsaved changes
    let formChanged = false;
    const forms = document.querySelectorAll('form');

    forms.forEach(function(form) {
        const inputs = form.querySelectorAll('input, textarea, select');

        inputs.forEach(function(input) {
            input.addEventListener('change', function() {
                formChanged = true;
            });
        });

        form.addEventListener('submit', function() {
            formChanged = false;
        });
    });

    window.addEventListener('beforeunload', function(e) {
        if (formChanged) {
            e.preventDefault();
            e.returnValue = '';
            return '';
        }
    });

    // Mobile sidebar toggle
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.querySelector('.sidebar');

    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('active');
        });
    }

    // Real-time clock in navbar (if element exists)
    const clockElement = document.getElementById('currentTime');
    if (clockElement) {
        function updateClock() {
            const now = new Date();
            const timeString = now.toLocaleTimeString();
            clockElement.textContent = timeString;
        }

        updateClock();
        setInterval(updateClock, 1000);
    }

    // Image preview for file uploads
    const fileInputs = document.querySelectorAll('input[type="file"]');
    fileInputs.forEach(function(input) {
        input.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById(input.id + '_preview');
                    if (preview) {
                        preview.src = e.target.result;
                        preview.style.display = 'block';
                    }
                };
                reader.readAsDataURL(file);
            }
        });
    });

    // Auto-format phone numbers
    const phoneInputs = document.querySelectorAll('input[type="tel"]');
    phoneInputs.forEach(function(input) {
        input.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 0) {
                if (value.length <= 2) {
                    value = value;
                } else if (value.length <= 5) {
                    value = value.slice(0, 2) + ' ' + value.slice(2);
                } else if (value.length <= 8) {
                    value = value.slice(0, 2) + ' ' + value.slice(2, 5) + ' ' + value.slice(5);
                } else {
                    value = value.slice(0, 2) + ' ' + value.slice(2, 5) + ' ' + value.slice(5, 8);
                }
            }
            e.target.value = value;
        });
    });

    // Table search functionality
    const tableSearch = document.getElementById('tableSearch');
    if (tableSearch) {
        tableSearch.addEventListener('keyup', function(e) {
            const searchText = e.target.value.toLowerCase();
            const tableRows = document.querySelectorAll('table tbody tr');

            tableRows.forEach(function(row) {
                const text = row.textContent.toLowerCase();
                if (text.includes(searchText)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }

    // Initialize date pickers to today if empty
    const dateInputs = document.querySelectorAll('input[type="date"]');
    dateInputs.forEach(function(input) {
        if (!input.value && input.hasAttribute('data-default-today')) {
            const today = new Date().toISOString().split('T')[0];
            input.value = today;
        }
    });

    // Confirm dialogs for delete actions
    const deleteButtons = document.querySelectorAll('[data-confirm-delete]');
    deleteButtons.forEach(function(button) {
        button.addEventListener('click', function(e) {
            const message = button.getAttribute('data-confirm-message') ||
                          'Are you sure you want to delete this item?';

            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });

    // Copy to clipboard functionality
    const copyButtons = document.querySelectorAll('[data-copy]');
    copyButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            const text = button.getAttribute('data-copy');
            navigator.clipboard.writeText(text).then(function() {
                const originalText = button.textContent;
                button.textContent = 'Copied!';
                setTimeout(function() {
                    button.textContent = originalText;
                }, 2000);
            });
        });
    });
});

// Utility functions

/**
 * Show loading spinner
 */
function showLoading() {
    const spinner = document.getElementById('loadingSpinner');
    if (spinner) {
        spinner.classList.add('active');
    }
}

/**
 * Hide loading spinner
 */
function hideLoading() {
    const spinner = document.getElementById('loadingSpinner');
    if (spinner) {
        spinner.classList.remove('active');
    }
}

/**
 * Show toast notification
 */
function showToast(message, type = 'success') {
    if (typeof Swal !== 'undefined') {
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });

        Toast.fire({
            icon: type,
            title: message
        });
    } else {
        console.log('[Toast]', message);
    }
}

/**
 * Confirm delete with SweetAlert
 */
function confirmDelete(message) {
    if (typeof Swal !== 'undefined') {
        return Swal.fire({
            title: 'Are you sure?',
            text: message || 'This action cannot be undone!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        });
    } else {
        return Promise.resolve({ isConfirmed: confirm(message || 'Delete this item?') });
    }
}

/**
 * Format number with thousand separators
 */
function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

/**
 * Format currency
 */
function formatCurrency(amount, currency = 'TND') {
    return formatNumber(amount.toFixed(2)) + ' ' + currency;
}

/**
 * Calculate distance between two points (Haversine)
 */
function calculateDistance(lat1, lon1, lat2, lon2) {
    const R = 6371; // Earth's radius in km
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLon = (lon2 - lon1) * Math.PI / 180;

    const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
              Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
              Math.sin(dLon/2) * Math.sin(dLon/2);

    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    const distance = R * c;

    return distance.toFixed(2);
}

/**
 * Debounce function
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * Export table to CSV
 */
function exportTableToCSV(tableId, filename = 'export.csv') {
    const table = document.getElementById(tableId);
    if (!table) return;

    let csv = [];
    const rows = table.querySelectorAll('tr');

    rows.forEach(function(row) {
        const cols = row.querySelectorAll('td, th');
        const csvRow = [];

        cols.forEach(function(col) {
            csvRow.push('"' + col.textContent.replace(/"/g, '""') + '"');
        });

        csv.push(csvRow.join(','));
    });

    const csvContent = csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');

    a.href = url;
    a.download = filename;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
}

// Global error handler
window.addEventListener('error', function(e) {
    console.error('Global error:', e.error);
});

// Console welcome message
console.log('%cDigiParc Fleet Management', 'color: #667eea; font-size: 24px; font-weight: bold;');
console.log('%cVersion 1.0.0', 'color: #764ba2; font-size: 14px;');
console.log('%c⚠️ Warning: This is a browser feature intended for developers. Do not paste code here that you don\'t understand!', 'color: #dc3545; font-size: 12px; font-weight: bold;');
