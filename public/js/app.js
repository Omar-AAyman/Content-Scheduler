
// Main JavaScript file for Content Scheduler

// Immediately Invoked Function Expression to avoid global scope pollution
(function () {
    // ContentScheduler namespace
    window.ContentScheduler = {
        // UI utilities
        ui: {
            /**
             * Show a toast notification
             * @param {string} message - The message to display
             * @param {string} type - The type of toast (success, error, warning, info)
             */
            showToast: function (message, type = 'info') {
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
            showError: function (message) {
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
            logError: function (message, details = {}) {
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
        initMobileSidebar: function () {
            const mobileSidebarToggle = document.getElementById('mobileSidebarToggle');
            const sidebar = document.querySelector('.sidebar');

            if (!mobileSidebarToggle || !sidebar) return;

            mobileSidebarToggle.addEventListener('click', function () {
                sidebar.classList.toggle('show');
            });

            // Close sidebar when clicking outside
            document.addEventListener('click', function (event) {
                if (sidebar.classList.contains('show') &&
                    !sidebar.contains(event.target) &&
                    event.target !== mobileSidebarToggle) {
                    sidebar.classList.remove('show');
                }
            });
        },

        // Initialize all components
        init: function () {
            // Initialize mobile sidebar
            this.initMobileSidebar();

            // Initialize tooltips
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            // Initialize popovers
            const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
            popoverTriggerList.map(function (popoverTriggerEl) {
                return new bootstrap.Popover(popoverTriggerEl);
            });

            // Close alert buttons
            document.querySelectorAll('.btn-close[data-bs-dismiss="alert"]').forEach(button => {
                button.addEventListener('click', function () {
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
    document.addEventListener('DOMContentLoaded', function () {
        ContentScheduler.init();
    });


    // Post Editor Scripts
    function initPostEditor() {
        const postForm = document.querySelector('#postForm');
        if (!postForm) return;

        // Platform limits from global config or data attribute
        const platformLimits = window.platformLimits || {};
        const defaultLimit = 280;

        // Platform selection
        const platformBadges = document.querySelectorAll('.platform-badge');
        platformBadges.forEach(badge => {
            badge.addEventListener('click', function () {
                const platform = this.dataset.platform;
                const input = document.getElementById(`platform_${platform}`);

                this.classList.toggle('active');
                input.disabled = !this.classList.contains('active');

                updateCharacterLimit();

                const anyPlatformSelected = Array.from(platformBadges).some(b => b.classList.contains('active'));
                document.getElementById('platformError').classList.toggle('d-none', anyPlatformSelected);
            });
        });

        // Character counter
        const contentTextarea = document.getElementById('content');
        const charCount = document.getElementById('charCount');
        const charLimit = document.getElementById('charLimit');

        function updateCharCount() {
            const count = contentTextarea.value.length;
            const limit = parseInt(charLimit.textContent.substring(1));

            charCount.textContent = count;

            const charCounter = charCount.parentElement;
            if (count > limit) {
                charCounter.classList.add('danger');
                charCounter.classList.remove('warning');
            } else if (count > limit * 0.8) {
                charCounter.classList.add('warning');
                charCounter.classList.remove('danger');
            } else {
                charCounter.classList.remove('warning', 'danger');
            }

            updatePreviews();
        }

        function updateCharacterLimit() {
            const selectedPlatforms = Array.from(document.querySelectorAll('.platform-badge.active'))
                .map(badge => badge.dataset.platform);

            let limit = selectedPlatforms.length > 0
                ? Math.min(...selectedPlatforms.map(platform => platformLimits[platform] || defaultLimit))
                : Math.min(...Object.values(platformLimits)) || defaultLimit;

            charLimit.textContent = `/${limit}`;
            updateCharCount();
        }

        function updatePreviews() {
            const content = contentTextarea.value || 'Your post will appear here...';

            // Dynamically get platforms from badges
            const platforms = Array.from(platformBadges).map(badge => badge.dataset.platform);
            platforms.forEach(platform => {
                const previewContent = document.getElementById(`${platform}-preview-content`);

                if (previewContent) {
                    if (platform === 'twitter') {
                        const twitterContent = content.length > platformLimits.twitter
                            ? content.substring(0, platformLimits.twitter - 3) + '...'
                            : content;
                        previewContent.textContent = twitterContent;
                    } else if (platform === 'instagram') {
                        previewContent.textContent = content;
                    } else if (platform === 'linkedin') {
                        previewContent.textContent = content;
                    }
                }
            });
        }

        contentTextarea.addEventListener('input', updateCharCount);

        // Image preview
        const imageUrlInput = document.getElementById('image_url');
        const previewImageBtn = document.getElementById('previewImageBtn');
        const imagePreviewContainer = document.getElementById('imagePreviewContainer');
        const imagePreview = document.getElementById('imagePreview');

        previewImageBtn.addEventListener('click', function () {
            const imageUrl = imageUrlInput.value.trim();
            if (imageUrl) {
                imagePreview.src = imageUrl;
                imagePreviewContainer.classList.remove('d-none');
                updatePreviewImages(imageUrl);
            } else {
                alert('Please enter an image URL');
            }
        });

        function updatePreviewImages(imageUrl) {
            const platforms = Array.from(platformBadges).map(badge => badge.dataset.platform);
            platforms.forEach(platform => {
                const previewImage = document.getElementById(`${platform}-preview-image`);
                if (previewImage) {
                    if (platform === 'instagram') {
                        previewImage.innerHTML = `<img src="${imageUrl}" class="img-fluid rounded" alt="Preview image">`;
                    } else {
                        const img = previewImage.querySelector('img');
                        if (img) {
                            img.src = imageUrl;
                            previewImage.classList.remove('d-none');
                        }
                    }
                }
            });
        }

        imagePreview.addEventListener('error', function () {
            imagePreviewContainer.classList.add('d-none');
            alert('Failed to load image. Please check the URL.');
        });

        // Save as draft
        const saveAsDraftBtn = document.getElementById('saveAsDraftBtn');
        saveAsDraftBtn.addEventListener('click', function () {
            const draftInput = document.createElement('input');
            draftInput.type = 'hidden';
            draftInput.name = 'status';
            draftInput.value = 'draft';
            postForm.appendChild(draftInput);
            postForm.submit();
        });

        // Form submission
        postForm.addEventListener('submit', function (event) {
            const existingStatusInput = postForm.querySelector('input[name="status"]');
            if (!existingStatusInput) {
                const statusInput = document.createElement('input');
                statusInput.type = 'hidden';
                statusInput.name = 'status';
                statusInput.value = 'scheduled';
                postForm.appendChild(statusInput);
            }
        });

        // Initialize
        updateCharacterLimit();
    }
    // Analytics Scripts
    // Analytics Scripts
    function initAnalytics() {
        const performanceChartCanvas = document.getElementById('performanceChart');
        if (!performanceChartCanvas) return;

        // Initialize Chart.js for performance chart
        const performanceChart = new Chart(performanceChartCanvas.getContext('2d'), {
            type: 'line',
            data: {
                labels: [],
                datasets: [
                    {
                        label: 'Scheduled Posts',
                        data: [],
                        borderColor: '#0d6efd',
                        backgroundColor: 'rgba(13, 110, 253, 0.1)',
                        tension: 0.3,
                        fill: true
                    },
                    {
                        label: 'Published Posts',
                        data: [],
                        borderColor: '#198754',
                        backgroundColor: 'rgba(25, 135, 84, 0.1)',
                        tension: 0.3,
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'top' } },
                scales: {
                    y: { beginAtZero: true, title: { display: true, text: 'Number of Posts' } }
                }
            }
        });

        // Platform distribution chart
        const platformChartCanvas = document.getElementById('platformChart');
        const platformChart = new Chart(platformChartCanvas.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: window.analyticsData?.postsByPlatform?.map(p => p.name) || [],
                datasets: [{
                    data: window.analyticsData?.postsByPlatform?.map(p => p.count) || [],
                    backgroundColor: ['#0d6efd', '#f72585', '#4cc9f0'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                cutout: '70%'
            }
        });

        // Date range buttons
        const rangeButtons = document.querySelectorAll('[data-range]');
        rangeButtons.forEach(button => {
            button.addEventListener('click', function () {
                rangeButtons.forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');

                const range = this.dataset.range;
                let labels = [], scheduledData = [], publishedData = [];

                if (range === 'week') {
                    const weekData = window.analyticsData.weekData || [];
                    labels = weekData.map(item => new Date(item.date).toLocaleDateString('en-US', { weekday: 'short' }));
                    scheduledData = weekData.map(item => item.scheduled_count || 0);
                    publishedData = weekData.map(item => item.published_count || 0);
                } else if (range === 'month') {
                    const monthData = window.analyticsData.monthData || [];
                    labels = monthData.map((item, index) => `Week ${item.week - Math.min(...monthData.map(d => d.week)) + 1}`);
                    scheduledData = monthData.map(item => item.scheduled_count || 0);
                    publishedData = monthData.map(item => item.published_count || 0);
                } else if (range === 'year') {
                    const yearData = window.analyticsData.yearData || [];
                    labels = yearData.map(item => new Date(2025, item.month - 1).toLocaleString('en-US', { month: 'short' }));
                    scheduledData = yearData.map(item => item.scheduled_count || 0);
                    publishedData = yearData.map(item => item.published_count || 0);
                }

                performanceChart.data.labels = labels;
                performanceChart.data.datasets[0].data = scheduledData;
                performanceChart.data.datasets[1].data = publishedData;
                performanceChart.update();
            });

            if (button.dataset.range === 'week') {
                button.click();
            }
        });

        // Dropdown for top posts
        document.querySelectorAll('.top-posts-dropdown .dropdown-item').forEach(item => {
            item.addEventListener('click', async function (e) {
                e.preventDefault();

                const dropdownButton = this.closest('.dropdown').querySelector('.dropdown-toggle');
                if (dropdownButton) {
                    dropdownButton.textContent = this.textContent;
                }

                this.closest('.dropdown-menu').querySelectorAll('.dropdown-item').forEach(i => {
                    i.classList.remove('active');
                });
                this.classList.add('active');

                try {
                    const range = this.dataset.range;
                    const response = await fetch(`/analytics/top-posts?range=${range}`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    });

                    if (!response.ok) {
                        throw new Error('Failed to fetch top posts');
                    }

                    const data = await response.json();

                    const tbody = document.querySelector('table tbody');
                    tbody.innerHTML = '';
                    if (data.posts.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="6" class="text-center">No posts found</td></tr>';
                    } else {
                        data.posts.forEach(post => {
                            const successCount = post.platforms.filter(p => p.pivot.platform_status === 'published').length;
                            const totalPlatforms = post.platforms.length;
                            const successRate = totalPlatforms > 0 ? (successCount / totalPlatforms) * 100 : 0;

                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td>
                                    <div class="d-flex align-items-center">
                                        ${post.image_url ? `<div class="post-image me-2"><img src="${post.image_url}" class="rounded" width="40" alt="Post image"></div>` : ''}
                                        <div>
                                            <div class="fw-semibold">${post.title}</div>
                                            <small class="text-muted">${post.content.substring(0, 30)}${post.content.length > 30 ? '...' : ''}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>${post.platforms.map(p => `<span class="badge bg-primary">${p.name}</span>`).join(' ')}</td>
                                <td>${new Date(post.scheduled_time).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}</td>
                                <td>
                                    <span class="badge ${post.status === 'published' ? 'bg-success' : (post.status === 'scheduled' ? 'bg-primary' : 'bg-secondary')}">
                                        ${post.status.charAt(0).toUpperCase() + post.status.slice(1)}
                                    </span>
                                </td>
                                <td>${successRate.toFixed(1)}%</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-outline-primary" data-post-id="${post.id}">
                                        <i class="fas fa-chart-bar"></i>
                                    </button>
                                </td>
                            `;
                            tbody.appendChild(row);
                        });
                    }

                    // Re-attach event listeners for post analytics buttons
                    document.querySelectorAll('button[data-post-id]').forEach(button => {
                        button.addEventListener('click', async function () {
                            const postId = this.dataset.postId;
                            try {
                                const response = await fetch(`/analytics/posts/${postId}`, {
                                    headers: {
                                        'Accept': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                    }
                                });
                                const data = await response.json();
                                console.log(data); // Placeholder for modal/UI update
                            } catch (error) {
                                showToast('Failed to load post analytics', 'danger');
                            }
                        });
                    });
                } catch (error) {
                    showToast('Failed to load top posts', 'danger');
                }
            });
        });
    }

    // Dashboard Scripts
    function initDashboard() {
        const calendarEl = document.getElementById('calendar');
        if (!calendarEl) return;

        // View toggle for calendar/list
        const calendarView = document.getElementById('calendarView');
        const listView = document.getElementById('listView');
        const viewCalendarBtn = document.getElementById('viewCalendar');
        const viewListBtn = document.getElementById('viewList');

        if (viewCalendarBtn && listView && calendarView && viewListBtn) {
            viewCalendarBtn.addEventListener('click', function () {
                calendarView.style.display = 'block';
                listView.style.display = 'none';
                viewCalendarBtn.classList.add('btn-dark');
                viewCalendarBtn.classList.remove('btn-outline-dark');
                viewListBtn.classList.add('btn-outline-dark');
                viewListBtn.classList.remove('btn-dark');
                // Refresh calendar to fix rendering issues
                if (calendar) {
                    calendar.render();
                }
            });

            viewListBtn.addEventListener('click', function () {
                calendarView.style.display = 'none';
                listView.style.display = 'block';
                viewListBtn.classList.add('btn-dark');
                viewListBtn.classList.remove('btn-outline-dark');
                viewCalendarBtn.classList.add('btn-outline-dark');
                viewCalendarBtn.classList.remove('btn-dark');
            });
        }

        // Initialize calendar
        let calendar = null;
        if (calendarEl && typeof FullCalendar !== 'undefined') {
            try {
                calendar = new FullCalendar.Calendar(calendarEl, {
                    initialView: 'dayGridMonth',
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth,timeGridWeek,timeGridDay'
                    },
                    height: 'auto',
                    aspectRatio: 1.35,
                    events: (window.dashboardData?.posts || []).map(post => ({
                        title: post.title,
                        start: post.scheduled_time,
                        url: post.url,
                        backgroundColor: post.status === 'published' ? '#198754' :
                            post.status === 'scheduled' ? '#0d6efd' :
                                post.status === 'draft' ? '#6c757d' : '#dc3545',
                        borderColor: post.status === 'published' ? '#198754' :
                            post.status === 'scheduled' ? '#0d6efd' :
                                post.status === 'draft' ? '#6c757d' : '#dc3545',
                        textColor: '#ffffff'
                    })),
                    eventClick: function (info) {
                        if (info.event.url) {
                            info.jsEvent.preventDefault();
                            window.location.href = info.event.url;
                        }
                    },
                    eventDidMount: function (info) {
                        // Add tooltip with post details
                        info.el.setAttribute('title',
                            `${info.event.title} (${info.event.start.toLocaleString()})`);
                    }
                });
                calendar.render();
            } catch (error) {
                console.error('Failed to render calendar:', error);
            }
        } else {
            console.error('FullCalendar is not loaded or calendar element not found.');
        }

        // Activity toggle
        const toggleActivitiesBtn = document.getElementById('toggleActivities');
        const activityList = document.getElementById('activityList');
        let isShowingAll = false;
        let originalActivities = [];

        if (toggleActivitiesBtn && activityList) {
            originalActivities = Array.from(activityList.children).map(item => ({
                id: item.dataset.activityId,
                html: item.outerHTML
            }));

            toggleActivitiesBtn.addEventListener('click', function () {
                if (isShowingAll) {
                    activityList.innerHTML = originalActivities.map(activity => activity.html).join('');
                    toggleActivitiesBtn.textContent = 'Show More';
                    isShowingAll = false;
                } else {
                    fetch('/activities', {
                        method: 'GET',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.activities && Array.isArray(data.activities)) {
                                activityList.innerHTML = data.activities.map(activity => `
                                <div data-activity-id="${activity.id}" class="mb-2">
                                    ${activity.details.charAt(0).toUpperCase() + activity.details.slice(1)} —
                                    ${activity.human_readable_date}
                                </div>
                            `).join('');
                                toggleActivitiesBtn.textContent = 'Show Less';
                                isShowingAll = true;
                            } else {
                                activityList.innerHTML = '<p class="text-muted">No activities found.</p>';
                            }
                        })
                        .catch(error => {
                            console.error('Activity fetch error:', error);
                            activityList.innerHTML = '<p class="text-muted">Error loading activities.</p>';
                        });
                }
            });
        }
    }

    // Platform Settings Scripts
    function initPlatformSettings() {
        // Platform toggle switches
        const platformToggles = document.querySelectorAll('.platform-toggle');
        if (!platformToggles.length) return;

        platformToggles.forEach(toggle => {
            toggle.addEventListener('change', function () {
                const platformId = this.dataset.platformId;
                const isActive = this.checked;

                fetch('/platforms/' + platformId + '/toggle', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ is_active: isActive })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const platform = this.id.replace('Toggle', '');
                            const status = isActive ? 'active' : 'inactive';
                            ContentScheduler.ui.showToast(
                                `${platform.charAt(0).toUpperCase() + platform.slice(1)} is now ${status}`,
                                'success'
                            );
                            // Check if reload is needed
                            if (data.reload) {
                                setTimeout(() => window.location.reload(), 500); // Delay for toast visibility
                            }
                        } else {
                            this.checked = !isActive; // Revert toggle on error
                            ContentScheduler.ui.showToast(
                                data.message || 'Error updating platform status',
                                'error'
                            );
                        }
                    })
                    .catch(error => {
                        this.checked = !isActive;
                        ContentScheduler.ui.showToast(
                            'Error updating platform status',
                            'error'
                        );
                        console.error('Toggle error:', error);
                    });
            });
        });
    }
    // Initialize all page scripts
    document.addEventListener('DOMContentLoaded', function () {
        initPostEditor();
        initAnalytics();
        initDashboard();
        initPlatformSettings();
    });
})();
