<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Generator - CRIM FAMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
                <li class="nav-item">
                    <a class="nav-link active" href="<?php echo BASE_URL; ?>/qr-generator">
                        <i class="fas fa-qrcode"></i> QR Generator
                    </a>
                </li>
                <?php if (in_array($_SESSION['role'], ['FACULTY', 'PROGRAM_HEAD', 'ADMIN'])): ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo BASE_URL; ?>/qr-scanner">
                        <i class="fas fa-camera"></i> QR Scanner
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
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo BASE_URL; ?>/logout">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </li>
            </ul>
        </nav>
        <!-- Main Content -->
        <div class="main-content flex-fill">
            <header class="bg-white shadow-sm p-3">
                <h5>QR Code Generator</h5>
            </header>
            <main class="p-4">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>Generate QR Code</h5>
                            </div>
                            <div class="card-body">
                                <form id="qrForm">
                                    <div class="mb-3">
                                        <label for="qrType" class="form-label">Type</label>
                                        <select class="form-select" id="qrType" name="qrType">
                                            <option value="department_log">Department Log (Office Time-In/Out)</option>
                                            <option value="classroom_checkin">Classroom Check-In</option>
                                        </select>
                                    </div>
                                    <div class="mb-3" id="roomField" style="display: none;">
                                        <label for="roomId" class="form-label">Room ID</label>
                                        <input type="text" class="form-control" id="roomId" name="roomId" placeholder="e.g., Main South 203" value="Main South 203">
                                    </div>
                                    <button type="submit" class="btn btn-primary">Generate QR Code</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>QR Code</h5>
                            </div>
                            <div class="card-body text-center">
                                <div id="status" class="mb-3"></div>
                                <div id="qrCanvas"></div>
                                <br>
                                <a id="downloadBtn" class="btn btn-secondary mt-3" style="display: none;">Download QR Code</a>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script src="js/dashboard.js"></script>
    <script>
        const qrForm = document.getElementById('qrForm');
        const qrType = document.getElementById('qrType');
        const roomField = document.getElementById('roomField');
        const qrCanvas = document.getElementById('qrCanvas');
        const downloadBtn = document.getElementById('downloadBtn');

        qrType.addEventListener('change', () => {
            if (qrType.value === 'classroom_checkin') {
                roomField.style.display = 'block';
            } else {
                roomField.style.display = 'none';
            }
        });

        qrForm.addEventListener('submit', (e) => {
            e.preventDefault();
            alert('Form submitted');
            document.getElementById('status').textContent = 'Generating QR Code...';
            let qrData;
            if (qrType.value === 'department_log') {
                qrData = JSON.stringify({ type: 'department_log' });
            } else {
                const roomId = document.getElementById('roomId').value;
                if (!roomId) {
                    alert('Please enter Room ID');
                    document.getElementById('status').textContent = '';
                    return;
                }
                qrData = JSON.stringify({ type: 'classroom_checkin', roomId: roomId });
            }

            if (typeof QRCode !== 'undefined') {
                qrCanvas.innerHTML = '';
                new QRCode(qrCanvas, {
                    text: qrData,
                    width: 256,
                    height: 256
                });
                document.getElementById('status').textContent = 'QR Code generated!';
                const img = qrCanvas.querySelector('img');
                if (img) {
                    downloadBtn.href = img.src;
                    downloadBtn.download = 'qr-code.png';
                    downloadBtn.style.display = 'block';
                }
            } else {
                alert('QRCode library not loaded');
                document.getElementById('status').textContent = 'QRCode library not loaded';
            }
        });

        downloadBtn.addEventListener('click', () => {
            const link = document.createElement('a');
            link.download = 'qr-code.png';
            link.href = qrCanvas.toDataURL();
            link.click();
        });

        // PWA Install Prompt
        let deferredPrompt;
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            // Optional: Show install button if needed
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