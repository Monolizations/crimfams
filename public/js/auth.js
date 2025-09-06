// Authentication and API utilities
class Auth {
    constructor() {
        this.baseURL = window.location.origin + '/crimfams';
        this.checkAuth();
    }

    async login(username, password) {
        try {
            const formData = new FormData();
            formData.append('username', username);
            formData.append('password', password);

            const response = await fetch(`${this.baseURL}/api/login.php`, {
                method: 'POST',
                body: formData,
                credentials: 'include'
            });

            const data = await response.json();

            if (data.success) {
                localStorage.setItem('user', JSON.stringify(data.user));
                localStorage.setItem('userRole', data.user.role);
                this.redirectBasedOnRole(data.user.role);
                return { success: true };
            } else {
                return { success: false, message: data.message };
            }
        } catch (error) {
            console.error('Login error:', error);
            return { success: false, message: 'Network error' };
        }
    }

    async logout() {
        try {
            await fetch(`${this.baseURL}/api/logout.php`, {
                method: 'POST',
                credentials: 'include'
            });
        } catch (error) {
            console.error('Logout error:', error);
        }

        localStorage.removeItem('user');
        window.location.href = 'login.html';
    }

    checkAuth() {
        const user = this.getCurrentUser();
        if (!user && window.location.pathname !== '/login.html' && !window.location.pathname.includes('login.html')) {
            window.location.href = 'login.html';
        }
        return user;
    }

    getCurrentUser() {
        const user = localStorage.getItem('user');
        return user ? JSON.parse(user) : null;
    }

    redirectBasedOnRole(role) {
        window.location.href = 'dashboard.html';
    }

    hasPermission(roles) {
        const user = this.getCurrentUser();
        return user && roles.includes(user.role);
    }
}

// Global auth instance
const auth = new Auth();

// Login form handler
document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('login-form');
    if (loginForm) {
        loginForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;

            const result = await auth.login(username, password);

            if (!result.success) {
                const errorDiv = document.getElementById('error-message');
                errorDiv.textContent = result.message;
                errorDiv.style.display = 'block';
            }
        });
    }

    // Logout handler
    const logoutBtn = document.getElementById('logout-btn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', function(e) {
            e.preventDefault();
            auth.logout();
        });
    }
});