<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Management - CRIM FAMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../frontend/css/dashboard.css" rel="stylesheet">
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <nav class="sidebar bg-dark">
            <div class="sidebar-header">
                <h3 class="text-white text-center py-3">CRIM FAMS</h3>
            </div>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                </li>
                <?php if (in_array($_SESSION['role'], ['FACULTY', 'PROGRAM_HEAD', 'ADMIN'])): ?>
                <li class="nav-item">
                    <a class="nav-link" href="schedules.php">
                        <i class="fas fa-calendar-alt"></i> Schedules
                    </a>
                </li>
                <?php endif; ?>
                <?php if (in_array($_SESSION['role'], ['SECRETARY', 'PROGRAM_HEAD', 'ADMIN', 'FACULTY'])): ?>
                <li class="nav-item">
                    <a class="nav-link active" href="leave.php">
                        <i class="fas fa-calendar-times"></i> Leave Management
                    </a>
                </li>
                <?php endif; ?>
                <?php if (in_array($_SESSION['role'], ['SECRETARY', 'ADMIN'])): ?>
                <li class="nav-item">
                    <a class="nav-link" href="faculties.php">
                        <i class="fas fa-users"></i> Faculties
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="classrooms.php">
                        <i class="fas fa-school"></i> Classrooms
                    </a>
                </li>
                <?php endif; ?>
                <?php if (in_array($_SESSION['role'], ['FACULTY', 'PROGRAM_HEAD', 'ADMIN'])): ?>
                <li class="nav-item">
                    <a class="nav-link" href="qr-scanner.php">
                        <i class="fas fa-qrcode"></i> QR Scanner
                    </a>
                </li>
                <?php endif; ?>
                <?php if (in_array($_SESSION['role'], ['PROGRAM_HEAD', 'ADMIN'])): ?>
                <li class="nav-item">
                    <a class="nav-link" href="qr-generator.php">
                        <i class="fas fa-qrcode"></i> QR Generator
                    </a>
                </li>
                <?php endif; ?>
                <?php if (in_array($_SESSION['role'], ['SECRETARY', 'ADMIN'])): ?>
                <li class="nav-item">
                    <a class="nav-link" href="reports.php">
                        <i class="fas fa-chart-bar"></i> Reports
                    </a>
                </li>
                <?php endif; ?>
                <li class="nav-item mt-auto">
                    <a class="nav-link text-danger" href="logout.php">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </li>
            </ul>
        </nav>

        <!-- Main Content -->
        <div class="main-content flex-grow-1">
            <header class="bg-white shadow-sm p-3 d-flex align-items-center">
                <button class="btn btn-outline-secondary d-md-none me-2" onclick="toggleSidebar()">☰</button>
                <h4 class="mb-0">Leave Management</h4>
            </header>
            <main class="p-4">
                <?php
                require_once __DIR__ . '/../config/config.php';
                require_once __DIR__ . '/../src/models/Database.php';
                $db = Database::getInstance()->getConnection();

                $role = $_SESSION['role'];
                $user_id = $_SESSION['user_id'];

                // Handle POST requests
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $action = $_POST['action'] ?? '';
                    if ($action === 'request' && $role === 'FACULTY') {
                        $start_date = $_POST['start_date'];
                        $end_date = $_POST['end_date'];
                        $reason = $_POST['reason'];
                        // Validate minimum 2 weeks
                        $min_date = date('Y-m-d', strtotime('+2 weeks'));
                        if ($start_date >= $min_date) {
                            $stmt = $db->prepare('INSERT INTO leave_requests (faculty_id, start_date, end_date, reason) VALUES (?, ?, ?, ?)');
                            $stmt->execute([$user_id, $start_date, $end_date, $reason]);
                        }
                    } elseif ($action === 'approve' && in_array($role, ['ADMIN', 'SECRETARY'])) {
                        $stmt = $db->prepare('UPDATE leave_requests SET status = ? WHERE id = ?');
                        $stmt->execute(['Approved', $_POST['id']]);
                    } elseif ($action === 'reject' && in_array($role, ['ADMIN', 'SECRETARY'])) {
                        $note = $_POST['admin_note'] ?? '';
                        $stmt = $db->prepare('UPDATE leave_requests SET status = ?, admin_note = ? WHERE id = ?');
                        $stmt->execute(['Rejected', $note, $_POST['id']]);
                    }
                    header('Location: leave.php');
                    exit;
                }

                // Fetch leave requests
                if ($role === 'FACULTY') {
                    $stmt = $db->prepare('SELECT lr.*, u.username FROM leave_requests lr LEFT JOIN users u ON lr.faculty_id = u.id WHERE lr.faculty_id = ? ORDER BY lr.requested_at DESC');
                    $stmt->execute([$user_id]);
                    $leave_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
                } elseif (in_array($role, ['ADMIN', 'SECRETARY'])) {
                    $stmt = $db->query('SELECT lr.*, u.username FROM leave_requests lr JOIN users u ON lr.faculty_id = u.id ORDER BY lr.requested_at DESC');
                    $leave_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
                }
                ?>

                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Leave Requests</h5>
                        <?php if ($role === 'FACULTY'): ?>
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#requestLeaveModal">Request Leave</button>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <?php if ($leave_requests): ?>
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
                                            <?php if (in_array($role, ['ADMIN', 'SECRETARY'])): ?>
                                            <th>Actions</th>
                                            <?php endif; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($leave_requests as $request): ?>
                                        <tr>
                                            <td><?php echo $request['username']; ?></td>
                                            <td><?php echo $request['start_date']; ?></td>
                                            <td><?php echo $request['end_date']; ?></td>
                                            <td><?php echo $request['reason']; ?></td>
                                            <td>
                                                <span class="badge bg-<?php
                                                    echo $request['status'] === 'Approved' ? 'success' :
                                                         ($request['status'] === 'Rejected' ? 'danger' : 'warning');
                                                ?>">
                                                    <?php echo $request['status']; ?>
                                                </span>
                                            </td>
                                            <td><?php echo $request['requested_at']; ?></td>
                                            <?php if (in_array($role, ['ADMIN', 'SECRETARY']) && $request['status'] === 'Pending'): ?>
                                            <td>
                                                <button class="btn btn-sm btn-outline-success" onclick="approveLeave(<?php echo $request['id']; ?>)">Approve</button>
                                                <button class="btn btn-sm btn-outline-danger" onclick="rejectLeave(<?php echo $request['id']; ?>)">Reject</button>
                                            </td>
                                            <?php endif; ?>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p>No leave requests found.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Request Leave Modal -->
    <?php if ($role === 'FACULTY'): ?>
    <div class="modal fade" id="requestLeaveModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Request Leave</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="request">
                        <div class="mb-3">
                            <label class="form-label">Start Date</label>
                            <input type="date" name="start_date" class="form-control" required min="<?php echo date('Y-m-d', strtotime('+2 weeks')); ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">End Date</label>
                            <input type="date" name="end_date" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Reason</label>
                            <textarea name="reason" class="form-control" rows="3" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Submit Request</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleSidebar() {
            document.querySelector('.sidebar').classList.toggle('show');
        }

        function approveLeave(id) {
            if (confirm('Are you sure you want to approve this leave request?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = '<input type="hidden" name="action" value="approve"><input type="hidden" name="id" value="' + id + '">';
                document.body.appendChild(form);
                form.submit();
            }
        }

        function rejectLeave(id) {
            const note = prompt('Enter rejection note:');
            if (note !== null) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = '<input type="hidden" name="action" value="reject"><input type="hidden" name="id" value="' + id + '"><input type="hidden" name="admin_note" value="' + note + '">';
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>