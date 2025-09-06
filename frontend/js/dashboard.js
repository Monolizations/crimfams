document.addEventListener('DOMContentLoaded', function() {
    // Highlight active sidebar link
    const currentPath = window.location.pathname;
    const sidebarLinks = document.querySelectorAll('.sidebar .nav-link');

    sidebarLinks.forEach(link => {
        if (link.getAttribute('href') && window.location.href.includes(link.getAttribute('href'))) {
            link.classList.add('active');
        } else {
            link.classList.remove('active');
        }
    });

    // Update navigation visibility based on user role
    updateNavigationVisibility();

    // Load dashboard content
    if (window.location.pathname.includes('dashboard.html')) {
        loadDashboardContent();
    }
});

function updateNavigationVisibility() {
    // Get user role from localStorage (set during login)
    const userRole = localStorage.getItem('userRole') || 'FACULTY';

    // Show/hide navigation items based on role
    const rolePermissions = {
        'FACULTY': ['leave-nav', 'schedules-nav', 'qr-scanner-nav'],
        'ADMIN': ['leave-nav', 'schedules-nav', 'faculties-nav', 'classrooms-nav', 'qr-scanner-nav', 'qr-generator-nav', 'reports-nav'],
        'SECRETARY': ['leave-nav', 'schedules-nav', 'faculties-nav', 'classrooms-nav', 'reports-nav'],
        'PROGRAM_HEAD': ['leave-nav', 'schedules-nav', 'qr-scanner-nav', 'qr-generator-nav']
    };

    const allowedNavs = rolePermissions[userRole] || [];

    // Hide all nav items first
    document.querySelectorAll('.nav-item').forEach(item => {
        if (!item.querySelector('a[href="dashboard.html"]') && !item.querySelector('#logout-btn')) {
            item.style.display = 'none';
        }
    });

    // Show allowed nav items
    allowedNavs.forEach(navId => {
        const navItem = document.getElementById(navId);
        if (navItem) {
            navItem.style.display = 'block';
        }
    });
}

async function loadDashboardContent() {
    try {
        const response = await fetch('/crimfams/api/dashboard.php', {
            method: 'GET',
            credentials: 'include'
        });

        if (response.ok) {
            const data = await response.json();
            renderDashboard(data);
        } else if (response.status === 401) {
            // Unauthorized, redirect to login
            window.location.href = 'login.html';
        } else {
            console.error('Failed to load dashboard:', response.status);
        }
    } catch (error) {
        console.error('Error loading dashboard:', error);
    }
}

function renderDashboard(data) {
    const container = document.getElementById('dashboard-content');
    const userRole = localStorage.getItem('userRole') || 'FACULTY';

    let html = `<h6>Welcome, ${userRole}!</h6>`;

    if (data.schedules && data.schedules.length > 0) {
        if (userRole === 'FACULTY') {
            html += `<h6 class="mt-4">Your Schedule Today</h6>`;
        } else {
            html += `<h6 class="mt-4">Today's Schedules</h6>`;
        }

        html += `
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Course</th>
                            <th>Room</th>
                            <th>Time</th>
        `;

        if (['PROGRAM_HEAD', 'SECRETARY', 'ADMIN'].includes(userRole)) {
            html += '<th>Faculty</th>';
        }

        html += `
                        </tr>
                    </thead>
                    <tbody>
        `;

        data.schedules.forEach(schedule => {
            html += `
                <tr>
                    <td>${schedule.course_code}</td>
                    <td>${schedule.room_number}</td>
                    <td>${schedule.start_time} - ${schedule.end_time}</td>
            `;

            if (['PROGRAM_HEAD', 'SECRETARY', 'ADMIN'].includes(userRole)) {
                html += `<td>${schedule.username || 'N/A'}</td>`;
            }

            html += '</tr>';
        });

        html += `
                    </tbody>
                </table>
            </div>
        `;
    } else {
        html += '<p>No schedules found for today.</p>';
    }

    container.innerHTML = html;
}

function toggleSidebar() {
    document.querySelector('.sidebar').classList.toggle('show');
}