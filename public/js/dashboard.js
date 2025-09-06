document.addEventListener('DOMContentLoaded', function() {
    // Highlight active sidebar link
    const currentPath = window.location.pathname;
    const sidebarLinks = document.querySelectorAll('.sidebar .nav-link');

    sidebarLinks.forEach(link => {
        if (link.getAttribute('href') === window.location.href) {
            link.classList.add('active');
        } else {
            link.classList.remove('active');
        }
    });

    // Add any dashboard-specific JavaScript here
});