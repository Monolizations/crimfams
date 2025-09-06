<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - CRIM FAMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../frontend/css/dashboard.css" rel="stylesheet">
    <link rel="manifest" href="/manifest.webmanifest">
    <meta name="theme-color" content="#007bff">
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
                    <a class="nav-link active" href="dashboard.php">
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
                    <a class="nav-link" href="leave.php">
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
                <h4 class="mb-0"><?php echo $_SESSION['role']; ?> - <?php echo in_array($_SESSION['role'], ['PROGRAM_HEAD', 'SECRETARY', 'ADMIN']) ? 'Live Attendance Dashboard' : 'Dashboard'; ?></h4>
            </header>
            <main class="p-4">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>Dashboard Overview</h5>
                            </div>
                            <div class="card-body">
                                <?php
                                require_once __DIR__ . '/../config/config.php';
                                require_once __DIR__ . '/../src/models/Database.php';
                                $db = Database::getInstance()->getConnection();

                                $role = $_SESSION['role'];
                                $user_id = $_SESSION['user_id'];

                                if ($role === 'FACULTY') {
                                    // Fetch personal schedule
                                    $stmt = $db->prepare('SELECT * FROM schedules WHERE faculty_id = ? AND date = CURDATE() ORDER BY start_time');
                                    $stmt->execute([$user_id]);
                                    $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                    if ($schedules) {
                                        echo '<h5>Your Schedule Today</h5>';
                                        echo '<ul class="list-group mb-3">';
                                        foreach ($schedules as $schedule) {
                                            echo '<li class="list-group-item d-flex justify-content-between align-items-center">';
                                            echo $schedule['course_code'] . ' - ' . $schedule['room_number'];
                                            echo '<span class="badge bg-primary">' . date('H:i', strtotime($schedule['start_time'])) . ' - ' . date('H:i', strtotime($schedule['end_time'])) . '</span>';
                                            echo '</li>';
                                        }
                                        echo '</ul>';
                                    } else {
                                        echo '<p>No classes scheduled for today.</p>';
                                    }

                                    echo '<div class="d-flex flex-column flex-md-row gap-3 mb-4">';
                                    echo '<button class="btn btn-success btn-lg" onclick="openScanner(\'office\')">🏢 SCAN OFFICE TIME-IN/OUT</button>';
                                    echo '<button class="btn btn-primary btn-lg" onclick="openScanner(\'classroom\')">🚪 SCAN CLASSROOM CHECK-IN</button>';
                                    echo '</div>';
                                } elseif (in_array($role, ['PROGRAM_HEAD', 'SECRETARY', 'ADMIN'])) {
                                    // Fetch all schedules for live dashboard
                                    $query = 'SELECT s.*, u.username FROM schedules s JOIN users u ON s.faculty_id = u.id WHERE s.date = CURDATE() ORDER BY s.start_time';
                                    $stmt = $db->query($query);
                                    $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                    echo '<h5>Live Attendance Dashboard</h5>';
                                    echo '<div class="row mb-3">';
                                    echo '<div class="col-md-3"><select class="form-select"><option>🔽 Building: All</option><option>Building A</option><option>Building B</option></select></div>';
                                    echo '<div class="col-md-3"><select class="form-select"><option>🔽 Program: All</option><option>CS</option><option>IT</option></select></div>';
                                    echo '<div class="col-md-6"><input type="text" class="form-control" placeholder="🔎 Search by Faculty Name..."></div>';
                                    echo '</div>';

                                    if ($schedules) {
                                        echo '<div class="table-responsive">';
                                        echo '<table class="table table-striped">';
                                        echo '<thead><tr><th>Time</th><th>Class Code</th><th>Faculty Name</th><th>Room</th><th>Status</th></tr></thead>';
                                        echo '<tbody>';
                                        foreach ($schedules as $schedule) {
                                            $now = time();
                                            $start = strtotime($schedule['date'] . ' ' . $schedule['start_time']);
                                            $end = strtotime($schedule['date'] . ' ' . $schedule['end_time']);
                                            if ($now >= $start && $now <= $end) {
                                                $status = 'PRESENT';
                                                $color = 'green';
                                            } elseif ($now > $end) {
                                                $status = 'COMPLETED';
                                                $color = 'gray';
                                            } else {
                                                $status = 'UPCOMING';
                                                $color = 'gray';
                                            }
                                            echo '<tr>';
                                            echo '<td>' . date('H:i', strtotime($schedule['start_time'])) . ' - ' . date('H:i', strtotime($schedule['end_time'])) . '</td>';
                                            echo '<td>' . $schedule['course_code'] . '</td>';
                                            echo '<td>' . $schedule['username'] . '</td>';
                                            echo '<td>' . $schedule['room_number'] . '</td>';
                                            echo '<td><span style="color:' . $color . ';">● ' . $status . '</span></td>';
                                            echo '</tr>';
                                        }
                                        echo '</tbody></table></div>';
                                        echo '<p class="mt-3">Auto-refreshing every 30 seconds...</p>';
                                    } else {
                                        echo '<p>No classes scheduled for today.</p>';
                                    }
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Scanner Modal -->
    <div class="modal fade" id="scannerModal" tabindex="-1" aria-labelledby="scannerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="scannerModalLabel">QR Scanner</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="reader" style="width: 100%;"></div>
                    <div id="message" class="mt-3 text-center"></div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../frontend/js/html5-qrcode.min.js"></script>
    <script>
        function toggleSidebar() {
            document.querySelector('.sidebar').classList.toggle('show');
        }

        // Auto-refresh for live dashboard
        setInterval(function() {
            location.reload();
        }, 30000); // 30 seconds

        function openScanner(type) {
            alert('Opening scanner for ' + type);
            const modal = new bootstrap.Modal(document.getElementById('scannerModal'));
            modal.show();
            // Scanner implementation would go here
        }
    </script>
</body>
</html>