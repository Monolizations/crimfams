<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - CRIM FAMS</title>
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
                    <a class="nav-link active" href="reports.php">
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
                <h4 class="mb-0">Reports</h4>
            </header>
            <main class="p-4">
                <?php
                require_once __DIR__ . '/../config/config.php';
                require_once __DIR__ . '/../src/models/Database.php';
                $db = Database::getInstance()->getConnection();

                $role = $_SESSION['role'];

                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    if (isset($_POST['generate'])) {
                        $report_type = $_POST['report_type'];
                        $start_date = $_POST['start_date'];
                        $end_date = $_POST['end_date'];
                        $program = $_POST['program'];
                        $faculty = $_POST['faculty'];

                        // Generate sample report data
                        $stmt = $db->prepare('SELECT s.date, u.username, s.course_code FROM schedules s JOIN users u ON s.faculty_id = u.id WHERE s.date BETWEEN ? AND ?');
                        $stmt->execute([$start_date, $end_date]);
                        $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        $report_data = [];
                        foreach ($schedules as $schedule) {
                            $report_data[] = [
                                'date' => $schedule['date'],
                                'username' => $schedule['username'],
                                'course_code' => $schedule['course_code'],
                                'status' => 'Present' // Sample
                            ];
                        }
                    } elseif (isset($_POST['export'])) {
                        // Export to CSV
                        header('Content-Type: text/csv');
                        header('Content-Disposition: attachment; filename="report.csv"');
                        $output = fopen('php://output', 'w');
                        fputcsv($output, ['Date', 'Faculty', 'Course', 'Status']);
                        if (isset($report_data)) {
                            foreach ($report_data as $row) {
                                fputcsv($output, $row);
                            }
                        }
                        fclose($output);
                        exit;
                    }
                }

                // Fetch faculties for dropdown
                $faculties = $db->query('SELECT id, username FROM users WHERE role = "FACULTY" OR role = "PROGRAM_HEAD"')->fetchAll(PDO::FETCH_ASSOC);
                ?>

                <div class="card">
                    <div class="card-header">
                        <h5>Generate Reports</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <label class="form-label">Report Type</label>
                                    <select name="report_type" class="form-select" required>
                                        <option value="attendance">Attendance Report</option>
                                        <option value="schedules">Schedule Report</option>
                                        <option value="leave">Leave Report</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Start Date</label>
                                    <input type="date" name="start_date" class="form-control" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">End Date</label>
                                    <input type="date" name="end_date" class="form-control" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Program</label>
                                    <select name="program" class="form-select">
                                        <option value="">All Programs</option>
                                        <option value="CS">Computer Science</option>
                                        <option value="IT">Information Technology</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Faculty</label>
                                    <select name="faculty" class="form-select">
                                        <option value="">All Faculty</option>
                                        <?php foreach ($faculties as $faculty): ?>
                                        <option value="<?php echo $faculty['id']; ?>"><?php echo $faculty['username']; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <button type="submit" name="generate" class="btn btn-primary">Generate Report</button>
                        </form>

                        <?php if (isset($report_data)): ?>
                        <hr>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6>Report Results</h6>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="report_type" value="<?php echo $report_type; ?>">
                                <input type="hidden" name="start_date" value="<?php echo $start_date; ?>">
                                <input type="hidden" name="end_date" value="<?php echo $end_date; ?>">
                                <input type="hidden" name="program" value="<?php echo $program; ?>">
                                <input type="hidden" name="faculty" value="<?php echo $faculty; ?>">
                                <button type="submit" name="export" class="btn btn-success btn-sm">Export to CSV</button>
                            </form>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Faculty</th>
                                        <th>Course</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($report_data as $row): ?>
                                    <tr>
                                        <td><?php echo $row['date']; ?></td>
                                        <td><?php echo $row['username']; ?></td>
                                        <td><?php echo $row['course_code']; ?></td>
                                        <td><?php echo $row['status']; ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleSidebar() {
            document.querySelector('.sidebar').classList.toggle('show');
        }
    </script>
</body>
</html>