<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Office Logbook - CRIM FAMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="css/dashboard.css" rel="stylesheet">
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
                    <a class="nav-link" href="<?php echo BASE_URL; ?>/dashboard">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                </li>
                <?php if (in_array($_SESSION['role'], ['FACULTY', 'PROGRAM_HEAD', 'ADMIN'])): ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo BASE_URL; ?>/schedules">
                        <i class="fas fa-calendar-alt"></i> Schedules
                    </a>
                </li>
                <?php endif; ?>
                <?php if (in_array($_SESSION['role'], ['SECRETARY', 'PROGRAM_HEAD', 'ADMIN', 'FACULTY'])): ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo BASE_URL; ?>/leave">
                        <i class="fas fa-calendar-times"></i> Leave Management
                    </a>
                </li>
                <?php endif; ?>
                <?php if (in_array($_SESSION['role'], ['SECRETARY', 'ADMIN'])): ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo BASE_URL; ?>/faculties">
                        <i class="fas fa-users"></i> Faculties
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo BASE_URL; ?>/classrooms">
                        <i class="fas fa-school"></i> Classrooms
                    </a>
                </li>
                <?php endif; ?>
                <?php if (in_array($_SESSION['role'], ['FACULTY', 'PROGRAM_HEAD', 'ADMIN'])): ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo BASE_URL; ?>/qr-scanner">
                        <i class="fas fa-qrcode"></i> QR Scanner
                    </a>
                </li>
                <?php endif; ?>
                <?php if (in_array($_SESSION['role'], ['PROGRAM_HEAD', 'ADMIN'])): ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo BASE_URL; ?>/qr-generator">
                        <i class="fas fa-qrcode"></i> QR Generator
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="<?php echo BASE_URL; ?>/office-logbook">
                        <i class="fas fa-book"></i> Office Logbook
                    </a>
                </li>
                <?php endif; ?>
                <?php if (in_array($_SESSION['role'], ['SECRETARY', 'ADMIN'])): ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo BASE_URL; ?>/reports">
                        <i class="fas fa-chart-bar"></i> Reports
                    </a>
                </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo BASE_URL; ?>/profile">
                        <i class="fas fa-user"></i> Profile
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo BASE_URL; ?>/settings">
                        <i class="fas fa-cog"></i> Settings
                    </a>
                </li>
                <li class="nav-item mt-auto">
                    <a class="nav-link text-danger" href="<?php echo BASE_URL; ?>/logout">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </li>
            </ul>
        </nav>

        <!-- Main Content -->
        <div class="main-content flex-grow-1">
            <header class="bg-white shadow-sm p-3 d-flex align-items-center">
                <button class="btn btn-outline-secondary d-md-none me-2" onclick="toggleSidebar()">☰</button>
                <h4 class="mb-0"><?php echo $_SESSION['role']; ?> - Office Logbook</h4>
            </header>
            <main class="p-4">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>Daily Office Attendance</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="log_date" class="form-label">Select Date</label>
                                    <input type="date" class="form-control" id="log_date" value="<?php echo date('Y-m-d'); ?>">
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Faculty Name</th>
                                                <th>Time-In</th>
                                                <th>Time-Out</th>
                                                <th>Total Hours</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Sample data -->
                                            <tr>
                                                <td>faculty1</td>
                                                <td>8:00 AM</td>
                                                <td>5:00 PM</td>
                                                <td>9h</td>
                                            </tr>
                                            <tr>
                                                <td>programhead1</td>
                                                <td>8:30 AM</td>
                                                <td>4:30 PM</td>
                                                <td>8h</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/dashboard.js"></script>
    <script>
        function toggleSidebar() {
            document.querySelector('.sidebar').classList.toggle('show');
        }
    </script>
</body>
</html>