// Leave Management functionality
class LeaveManager {
    constructor() {
        this.baseURL = window.location.origin;
        this.init();
    }

    init() {
        this.loadLeaveRequests();
        this.setupEventListeners();
        this.updateNavigationVisibility();
    }

    updateNavigationVisibility() {
        const user = auth.getCurrentUser();
        if (!user) return;

        // Show/hide navigation items based on role
        const rolePermissions = {
            'FACULTY': ['leave-nav', 'schedules-nav', 'qr-scanner-nav'],
            'ADMIN': ['leave-nav', 'schedules-nav', 'faculties-nav', 'classrooms-nav', 'qr-scanner-nav', 'qr-generator-nav', 'reports-nav'],
            'SECRETARY': ['leave-nav', 'schedules-nav', 'faculties-nav', 'classrooms-nav', 'reports-nav'],
            'PROGRAM_HEAD': ['leave-nav', 'schedules-nav', 'qr-scanner-nav', 'qr-generator-nav']
        };

        const allowedNavs = rolePermissions[user.role] || [];

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

        // Show request leave button for faculty
        if (user.role === 'FACULTY') {
            const requestBtn = document.getElementById('request-leave-btn');
            if (requestBtn) {
                requestBtn.style.display = 'block';
            }
        }
    }

    async loadLeaveRequests() {
        try {
            const response = await fetch(`/crimfams/api/leave.php`, {
                credentials: 'include'
            });

            if (response.ok) {
                const data = await response.json();
                this.renderLeaveRequests(data.requests);
            } else if (response.status === 401) {
                window.location.href = 'login.html';
            }
        } catch (error) {
            console.error('Error loading leave requests:', error);
        }
    }

    renderLeaveRequests(requests) {
        const container = document.getElementById('leave-requests-container');
        const user = auth.getCurrentUser();

        if (!requests || requests.length === 0) {
            container.innerHTML = '<p>No leave requests found.</p>';
            return;
        }

        let html = `
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Faculty</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Reason</th>
                            <th>Status</th>
                            <th>Requested At</th>
                            ${user.role === 'ADMIN' || user.role === 'SECRETARY' ? '<th>Actions</th>' : '<th>Note</th>'}
                        </tr>
                    </thead>
                    <tbody>
        `;

        requests.forEach(request => {
            const statusBadge = this.getStatusBadge(request.status);
            const actions = this.getActionsHtml(request, user);

            html += `
                <tr>
                    <td>${request.username || 'You'}</td>
                    <td>${request.start_date}</td>
                    <td>${request.end_date}</td>
                    <td>${request.reason}</td>
                    <td>${statusBadge}</td>
                    <td>${request.requested_at}</td>
                    <td>${actions}</td>
                </tr>
            `;
        });

        html += `
                    </tbody>
                </table>
            </div>
        `;

        container.innerHTML = html;
    }

    getStatusBadge(status) {
        const badges = {
            'Approved': 'success',
            'Rejected': 'danger',
            'Pending': 'warning'
        };
        return `<span class="badge bg-${badges[status] || 'secondary'}">${status}</span>`;
    }

    getActionsHtml(request, user) {
        if (user.role !== 'ADMIN' && user.role !== 'SECRETARY') {
            return request.status === 'Rejected' && request.admin_note ?
                `<button class="btn btn-info btn-sm" onclick="leaveManager.showNote('${request.admin_note.replace(/'/g, "\\'")}')">View Note</button>` :
                '';
        }

        if (request.status !== 'Pending') {
            return request.status === 'Rejected' && request.admin_note ?
                `<button class="btn btn-info btn-sm" onclick="leaveManager.showNote('${request.admin_note.replace(/'/g, "\\'")}')">View Note</button>` :
                '';
        }

        return `
            <form style="display:inline;" onsubmit="leaveManager.approveRequest(event, ${request.id})">
                <button type="submit" class="btn btn-success btn-sm">Approve</button>
            </form>
            <button type="button" class="btn btn-danger btn-sm" onclick="leaveManager.rejectRequest(${request.id})">Reject</button>
        `;
    }

    async approveRequest(event, id) {
        event.preventDefault();

        try {
            const response = await fetch(`/crimfams/api/leave.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id }),
                credentials: 'include'
            });

            if (response.ok) {
                this.loadLeaveRequests();
            }
        } catch (error) {
            console.error('Error approving request:', error);
        }
    }

    rejectRequest(id) {
        document.getElementById('reject-id').value = id;
        const modal = new bootstrap.Modal(document.getElementById('rejectModal'));
        modal.show();
    }

    showNote(note) {
        document.getElementById('note-content').textContent = note;
        const modal = new bootstrap.Modal(document.getElementById('noteModal'));
        modal.show();
    }

    setupEventListeners() {
        // Leave request form
        const leaveForm = document.getElementById('leave-request-form');
        if (leaveForm) {
            leaveForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                await this.submitLeaveRequest();
            });
        }

        // Reject form
        const rejectForm = document.getElementById('reject-leave-form');
        if (rejectForm) {
            rejectForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                await this.submitRejection();
            });
        }
    }

    async submitLeaveRequest() {
        const formData = new FormData(document.getElementById('leave-request-form'));
        const data = Object.fromEntries(formData);

        try {
            const response = await fetch(`/crimfams/api/leave.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data),
                credentials: 'include'
            });

            if (response.ok) {
                bootstrap.Modal.getInstance(document.getElementById('requestLeaveModal')).hide();
                document.getElementById('leave-request-form').reset();
                this.loadLeaveRequests();
            }
        } catch (error) {
            console.error('Error submitting leave request:', error);
        }
    }

    async submitRejection() {
        const formData = new FormData(document.getElementById('reject-leave-form'));
        const data = Object.fromEntries(formData);

        try {
            const response = await fetch(`/crimfams/api/leave.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data),
                credentials: 'include'
            });

            if (response.ok) {
                bootstrap.Modal.getInstance(document.getElementById('rejectModal')).hide();
                document.getElementById('reject-leave-form').reset();
                this.loadLeaveRequests();
            }
        } catch (error) {
            console.error('Error submitting rejection:', error);
        }
    }
}

// Global instance
let leaveManager;

document.addEventListener('DOMContentLoaded', function() {
    if (window.location.pathname.includes('leave.html')) {
        leaveManager = new LeaveManager();
    }
});