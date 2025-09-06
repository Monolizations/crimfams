<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedules - CRIM FAMS</title>
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
                    <a class="nav-link active" href="schedules.php">
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
                <h4 class="mb-0">Schedules Management</h4>
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
                    if ($role !== 'ADMIN' && $role !== 'SECRETARY') {
                        http_response_code(403);
                        echo 'Access denied';
                        exit;
                    }
                    $action = $_POST['action'] ?? '';
                    if ($action === 'add') {
                        $faculty_id = $_POST['faculty_id'];
                        $course_code = $_POST['course_code'];
                        $room_number = $_POST['room_number'];
                        $day_of_week = $_POST['day_of_week'];
                        $start_time = $_POST['start_time'];
                        $end_time = $_POST['end_time'];
                        $stmt = $db->prepare('INSERT INTO schedules (faculty_id, course_code, room_number, day_of_week, start_time, end_time, date) VALUES (?, ?, ?, ?, ?, ?, CURDATE())');
                        $stmt->execute([$faculty_id, $course_code, $room_number, $day_of_week, $start_time, $end_time]);
                    } elseif ($action === 'edit') {
                        $id = $_POST['id'];
                        $faculty_id = $_POST['faculty_id'];
                        $course_code = $_POST['course_code'];
                        $room_number = $_POST['room_number'];
                        $day_of_week = $_POST['day_of_week'];
                        $start_time = $_POST['start_time'];
                        $end_time = $_POST['end_time'];
                        $stmt = $db->prepare('UPDATE schedules SET faculty_id = ?, course_code = ?, room_number = ?, day_of_week = ?, start_time = ?, end_time = ? WHERE id = ?');
                        $stmt->execute([$faculty_id, $course_code, $room_number, $day_of_week, $start_time, $end_time, $id]);
                    } elseif ($action === 'delete' && isset($_POST['id'])) {
                        $stmt = $db->prepare('DELETE FROM schedules WHERE id = ?');
                        $stmt->execute([$_POST['id']]);
                    }
                    header('Location: schedules.php');
                    exit;
                }

                // Fetch schedules
                if ($role === 'FACULTY') {
                    $stmt = $db->prepare('SELECT * FROM schedules WHERE faculty_id = ? ORDER BY date, start_time');
                    $stmt->execute([$user_id]);
                    $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
                } else {
                    $stmt = $db->query('SELECT s.*, u.username FROM schedules s JOIN users u ON s.faculty_id = u.id ORDER BY s.date, s.start_time');
                    $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
                }

                // Fetch faculties for dropdown
                if ($role === 'ADMIN' || $role === 'SECRETARY') {
                    $faculties = $db->query('SELECT id, username FROM users WHERE role = "FACULTY" OR role = "PROGRAM_HEAD"')->fetchAll(PDO::FETCH_ASSOC);
                }
                ?>

                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Schedules</h5>
                        <?php if ($role === 'ADMIN' || $role === 'SECRETARY'): ?>
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addScheduleModal">Add Schedule</button>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <?php if ($schedules): ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Course Code</th>
                                            <th>Room</th>
                                            <th>Day</th>
                                            <th>Time</th>
                                            <?php if ($role !== 'FACULTY'): ?>
                                            <th>Faculty</th>
                                            <?php endif; ?>
                                            <?php if ($role === 'ADMIN' || $role === 'SECRETARY'): ?>
                                            <th>Actions</th>
                                            <?php endif; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($schedules as $schedule): ?>
                                        <tr>
                                            <td><?php echo $schedule['date']; ?></td>
                                            <td><?php echo $schedule['course_code']; ?></td>
                                            <td><?php echo $schedule['room_number']; ?></td>
                                            <td><?php echo $schedule['day_of_week']; ?></td>
                                            <td><?php echo $schedule['start_time'] . ' - ' . $schedule['end_time']; ?></td>
                                            <?php if ($role !== 'FACULTY'): ?>
                                            <td><?php echo $schedule['username']; ?></td>
                                            <?php endif; ?>
                                            <?php if ($role === 'ADMIN' || $role === 'SECRETARY'): ?>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary" onclick="editSchedule(<?php echo $schedule['id']; ?>)">Edit</button>
                                                <button class="btn btn-sm btn-outline-danger" onclick="deleteSchedule(<?php echo $schedule['id']; ?>)">Delete</button>
                                            </td>
                                            <?php endif; ?>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p>No schedules found.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Add Schedule Modal -->
    <?php if ($role === 'ADMIN' || $role === 'SECRETARY'): ?>
    <div class="modal fade" id="addScheduleModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Schedule</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        <div class="mb-3">
                            <label class="form-label">Faculty</label>
                            <select name="faculty_id" class="form-select" required>
                                <?php foreach ($faculties as $faculty): ?>
                                <option value="<?php echo $faculty['id']; ?>"><?php echo $faculty['username']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Course Code</label>
                            <input type="text" name="course_code" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Room Number</label>
                            <input type="text" name="room_number" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Day of Week</label>
                            <select name="day_of_week" class="form-select" required>
                                <option value="Monday">Monday</option>
                                <option value="Tuesday">Tuesday</option>
                                <option value="Wednesday">Wednesday</option>
                                <option value="Thursday">Thursday</option>
                                <option value="Friday">Friday</option>
                                <option value="Saturday">Saturday</option>
                                <option value="Sunday">Sunday</option>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">Start Time</label>
                                <input type="time" name="start_time" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">End Time</label>
                                <input type="time" name="end_time" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Add Schedule</button>
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

        function editSchedule(id) {
            // Implement edit functionality
            alert('Edit schedule ' + id);
        }

        function deleteSchedule(id) {
            if (confirm('Are you sure you want to delete this schedule?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = '<input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="' + id + '">';
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>