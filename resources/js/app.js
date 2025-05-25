import './bootstrap';

// Main JavaScript file for Content Scheduler

// Immediately Invoked Function Expression to avoid global scope pollution
(function() {
    // ContentScheduler namespace
    window.ContentScheduler = {
        // UI utilities
        ui: {
            /**
             * Show a toast notification
             * @param {string} message - The message to display
             * @param {string} type - The type of toast (success, error, warning, info)
             */
            showToast: function(message, type = 'info') {
                const toastContainer = document.querySelector('.toast-container');
                if (!toastContainer) return;

                const toast = document.createElement('div');
                toast.className = `toast ${type} show`;
                toast.setAttribute('role', 'alert');
                toast.setAttribute('aria-live', 'assertive');
                toast.setAttribute('aria-atomic', 'true');

                toast.innerHTML = `
                    <div class="toast-header">
                        <strong class="me-auto">${type.charAt(0).toUpperCase() + type.slice(1)}</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                    <div class="toast-body">
                        ${message}
                    </div>
                `;

                toastContainer.appendChild(toast);

                // Auto-remove after 5 seconds
                setTimeout(() => {
                    toast.classList.remove('show');
                    setTimeout(() => {
                        toast.remove();
                    }, 300);
                }, 5000);
            },

            /**
             * Show an error indicator
             * @param {string} message - The error message
             */
            showError: function(message) {
                const errorIndicator = document.getElementById('errorIndicator');
                const errorMessage = document.getElementById('errorMessage');

                if (!errorIndicator || !errorMessage) return;

                errorMessage.textContent = message;
                errorIndicator.classList.add('show');

                // Log to console for debugging
                console.error(message);

                // Send to backend for logging
                this.logError(message);
            },

            /**
             * Log an error to the backend
             * @param {string} message - The error message
             * @param {Object} details - Additional error details
             */
            logError: function(message, details = {}) {
                // Only log errors in production
                if (process.env.NODE_ENV !== 'production') {
                    console.log('Error logging disabled in development');
                    return;
                }

                // Send error to backend
                fetch('/api/log-error', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        message: message,
                        details: details,
                        url: window.location.href,
                        timestamp: new Date().toISOString()
                    })
                }).catch(err => {
                    console.error('Failed to log error:', err);
                });
            }
        },

        // Mobile sidebar toggle
        initMobileSidebar: function() {
            const mobileSidebarToggle = document.getElementById('mobileSidebarToggle');
            const sidebar = document.querySelector('.sidebar');

            if (!mobileSidebarToggle || !sidebar) return;

            mobileSidebarToggle.addEventListener('click', function() {
                sidebar.classList.toggle('show');
            });

            // Close sidebar when clicking outside
            document.addEventListener('click', function(event) {
                if (sidebar.classList.contains('show') &&
                    !sidebar.contains(event.target) &&
                    event.target !== mobileSidebarToggle) {
                    sidebar.classList.remove('show');
                }
            });
        },

        // Initialize all components
        init: function() {
            // Initialize mobile sidebar
            this.initMobileSidebar();

            // Initialize tooltips
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            // Initialize popovers
            const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
            popoverTriggerList.map(function(popoverTriggerEl) {
                return new bootstrap.Popover(popoverTriggerEl);
            });

            // Close alert buttons
            document.querySelectorAll('.btn-close[data-bs-dismiss="alert"]').forEach(button => {
                button.addEventListener('click', function() {
                    this.closest('.alert').classList.remove('show');
                    setTimeout(() => {
                        this.closest('.alert').remove();
                    }, 300);
                });
            });

            console.log('Content Scheduler initialized');
        }
    };

    // Initialize when DOM is loaded
    document.addEventListener('DOMContentLoaded', function() {
        ContentScheduler.init();
    });
})();
