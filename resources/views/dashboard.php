<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - FAMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="css/dashboard.css" rel="stylesheet">
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
                    <a class="nav-link active" href="<?php echo BASE_URL; ?>/dashboard">
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
                    <a class="nav-link" href="<?php echo BASE_URL; ?>/office-logbook">
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
                <h4 class="mb-0"><?php echo $_SESSION['role']; ?> - <?php echo in_array($_SESSION['role'], ['PROGRAM_HEAD', 'SECRETARY', 'ADMIN']) ? 'Live Attendance Dashboard' : (isset($page) ? $page : 'Dashboard'); ?></h4>
            </header>
            <main class="p-4">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5><?php echo isset($page) ? $page : 'Dashboard'; ?> Overview</h5>
                            </div>
                            <div class="card-body">
                                <?php
                                $role = $_SESSION['role'];

                                if ($role === 'FACULTY') {
                                    // Show personal schedule
                                    if (isset($schedules) && $schedules) {
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
                                     echo '<button class="btn btn-success btn-lg" data-type="office" onclick="openScanner(this)">🏢 SCAN OFFICE TIME-IN/OUT</button>';
                                     echo '<button class="btn btn-primary btn-lg" data-type="classroom" onclick="openScanner(this)">🚪 SCAN CLASSROOM CHECK-IN</button>';
                                     echo '<button id="installBtn" class="btn btn-info btn-lg" style="display: none;">📱 Add to Home Screen</button>';
                                     echo '</div>';
                                } elseif (in_array($role, ['PROGRAM_HEAD', 'SECRETARY', 'ADMIN'])) {
                                    // Live Attendance Dashboard
                                    echo '<h5>Live Attendance Dashboard</h5>';
                                    echo '<div class="row mb-3">';
                                    echo '<div class="col-md-3"><select class="form-select"><option>🔽 Building: All</option><option>Building A</option><option>Building B</option></select></div>';
                                    echo '<div class="col-md-3"><select class="form-select"><option>🔽 Program: All</option><option>CS</option><option>IT</option></select></div>';
                                    echo '<div class="col-md-6"><input type="text" class="form-control" placeholder="🔎 Search by Faculty Name..."></div>';
                                    echo '</div>';
                                    if (isset($schedules) && $schedules) {
                                        echo '<div class="table-responsive">';
                                        echo '<table class="table table-striped" id="attendanceTable">';
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
    <script src="/js/html5-qrcode.min.js"></script>
    <script src="js/dashboard.js"></script>
    <script>
        function toggleSidebar() {
            document.querySelector('.sidebar').classList.toggle('show');
        }
        // Auto-refresh for live dashboard
        setInterval(function() {
            location.reload();
        }, 30000); // 30 seconds

        // PWA Install Prompt
        let deferredPrompt;
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            const installBtn = document.getElementById('installBtn');
            if (installBtn) {
                installBtn.style.display = 'block';
            }
        });
        document.getElementById('installBtn').addEventListener('click', () => {
            if (deferredPrompt) {
                deferredPrompt.prompt();
                deferredPrompt.userChoice.then((choiceResult) => {
                    if (choiceResult.outcome === 'accepted') {
                        console.log('User accepted the install prompt');
                        document.getElementById('installBtn').style.display = 'none';
                    } else {
                        console.log('User dismissed the install prompt');
                    }
                    deferredPrompt = null;
                });
            }
        });

        // QR Scanner Modal
        let currentScanner;
        let currentType;
        function openScanner(btn) {
            alert('Opening scanner for ' + btn.getAttribute('data-type'));
            currentType = btn.getAttribute('data-type');
            const modal = new bootstrap.Modal(document.getElementById('scannerModal'));
            modal.show();
            document.getElementById('scannerModal').addEventListener('shown.bs.modal', function () {
                currentScanner = new Html5QrcodeScanner("reader", { fps: 10, qrbox: 250 });
                currentScanner.render(onScanSuccess, onScanFailure);
            });
            document.getElementById('scannerModal').addEventListener('hidden.bs.modal', function () {
                if (currentScanner) {
                    currentScanner.clear();
                    currentScanner = null;
                }
                document.getElementById('message').textContent = '';
            });
        }

        function onScanSuccess(decodedText, decodedResult) {
            if (currentScanner) {
                currentScanner.clear();
                $('#scannerModal').modal('hide');
            }
            processQR(decodedText);
        }

        function onScanFailure(error) {
            // console.warn(`Code scan error = ${error}`);
        }

        function processQR(data) {
            try {
                const qrData = JSON.parse(data);
                if (qrData.type === 'department_log' && currentType === 'office') {
                    sendToServer({ action: 'office_log', timestamp: new Date().toISOString() });
                } else if (qrData.type === 'classroom_checkin' && currentType === 'classroom') {
                    sendToServer({ action: 'classroom_checkin', roomId: qrData.roomId, timestamp: new Date().toISOString() });
                } else {
                    alert('Invalid QR code for this scan type.');
                }
            } catch (e) {
                alert('Invalid QR code format.');
            }
        }

        function sendToServer(data) {
            fetch('<?php echo BASE_URL; ?>/api/scan', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    alert(result.message);
                } else {
                    alert('Error: ' + result.message);
                }
            })
            .catch(err => {
                // Offline: save to IndexedDB
                saveOffline(data);
                alert('Check-in saved. Will sync when online.');
            });
        }

        function saveOffline(data) {
            console.log('Saving offline:', data);
        }
        document.getElementById('installBtn').addEventListener('click', () => {
            if (deferredPrompt) {
                deferredPrompt.prompt();
                deferredPrompt.userChoice.then((choiceResult) => {
                    if (choiceResult.outcome === 'accepted') {
                        console.log('User accepted the install prompt');
                        document.getElementById('installBtn').style.display = 'none';
                    } else {
                        console.log('User dismissed the install prompt');
                    }
                    deferredPrompt = null;
                });
            } else {
                alert('App not ready for installation. Try refreshing the page or adding icons.');
            }
        });
    </script>
    <script>
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/service-worker.js')
                .then(reg => console.log('Service Worker registered'))
                .catch(err => console.log('Service Worker registration failed'));
        }
    </script>
</body>
</html>