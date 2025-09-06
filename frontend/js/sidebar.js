// Sidebar Component Manager
class SidebarManager {
    constructor() {
        this.currentPage = this.getCurrentPage();
        this.sidebarContainer = null;
    }

    // Load sidebar component
    async loadSidebar(containerId = 'sidebar-container') {
        try {
            const response = await fetch('../../public/views/sidebar.html');
            const sidebarHtml = await response.text();

            this.sidebarContainer = document.getElementById(containerId);
            if (this.sidebarContainer) {
                this.sidebarContainer.innerHTML = sidebarHtml;
                this.setActiveLink();
                this.setupLogoutHandler();
                this.applyRoleBasedNavigation();
            }
        } catch (error) {
            console.error('Error loading sidebar:', error);
        }
    }

    // Determine current page from URL
    getCurrentPage() {
        const path = window.location.pathname;
        const filename = path.split('/').pop();

        // Map filenames to page identifiers
        const pageMap = {
            'dashboard.html': 'dashboard',
            'schedules.html': 'schedules',
            'leave.html': 'leave',
            'faculties.html': 'faculties',
            'classrooms.html': 'classrooms',
            'qr-scanner.html': 'qr-scanner',
            'qr-generator.html': 'qr-generator',
            'reports.html': 'reports'
        };

        return pageMap[filename] || 'dashboard';
    }

    // Set active class on current page link
    setActiveLink() {
        // Remove active class from all links
        const navLinks = document.querySelectorAll('.sidebar .nav-link');
        navLinks.forEach(link => link.classList.remove('active'));

        // Add active class to current page link
        const currentLink = document.getElementById(`${this.currentPage}-link`);
        if (currentLink) {
            currentLink.classList.add('active');
        }
    }

    // Setup logout handler
    setupLogoutHandler() {
        const logoutBtn = document.getElementById('logout-btn');
        if (logoutBtn) {
            logoutBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.handleLogout();
            });
        }
    }

    // Handle logout
    async handleLogout() {
        try {
            const response = await fetch('/crimfams/api/logout.php', {
                method: 'POST',
                credentials: 'include'
            });

            if (response.ok) {
                localStorage.removeItem('user');
                localStorage.removeItem('userRole');
                window.location.href = 'login.html';
            }
        } catch (error) {
            console.error('Logout error:', error);
            // Fallback: clear local storage and redirect
            localStorage.removeItem('user');
            localStorage.removeItem('userRole');
            window.location.href = 'login.html';
        }
    }

    // Apply role-based navigation visibility
    applyRoleBasedNavigation() {
        const userRole = localStorage.getItem('userRole') || 'FACULTY';
        this.updateNavigationVisibility(userRole);
    }

    // Update navigation visibility based on user role
    updateNavigationVisibility(userRole) {
        const rolePermissions = {
            'FACULTY': ['leave-nav', 'schedules-nav', 'qr-scanner-nav'],
            'ADMIN': ['leave-nav', 'schedules-nav', 'faculties-nav', 'classrooms-nav', 'qr-scanner-nav', 'qr-generator-nav', 'reports-nav'],
            'SECRETARY': ['leave-nav', 'schedules-nav', 'faculties-nav', 'classrooms-nav', 'reports-nav'],
            'PROGRAM_HEAD': ['leave-nav', 'schedules-nav', 'qr-scanner-nav', 'qr-generator-nav']
        };

        const allowedNavs = rolePermissions[userRole] || [];

        // Hide all nav items first (except dashboard and logout)
        document.querySelectorAll('.sidebar .nav-item').forEach(item => {
            const link = item.querySelector('.nav-link');
            if (link && link.id !== 'dashboard-link' && link.id !== 'logout-btn') {
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
}

// Global sidebar manager instance
const sidebarManager = new SidebarManager();

// Auto-initialize sidebar on page load
document.addEventListener('DOMContentLoaded', function() {
    sidebarManager.loadSidebar();
});