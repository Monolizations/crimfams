<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedules - FAMS</title>
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
                    <a class="nav-link active" href="<?php echo BASE_URL; ?>/schedules">
                        <i class="fas fa-calendar-alt"></i> Schedules
                    </a>
                </li>
                <?php endif; ?>
                <?php if (in_array($_SESSION['role'], ['SECRETARY', 'PROGRAM_HEAD', 'ADMIN'])): ?>
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
                <h4 class="mb-0"><?php echo $_SESSION['role']; ?> - <?php echo in_array($_SESSION['role'], ['PROGRAM_HEAD', 'SECRETARY', 'ADMIN']) ? 'Master Schedule' : 'Schedules'; ?></h4>
            </header>
            <main class="p-4">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>Schedules Management</h5>
                            </div>
                            <div class="card-body">
                                <?php if (in_array($_SESSION['role'], ['ADMIN', 'SECRETARY', 'PROGRAM_HEAD'])): ?>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5>Master Schedule</h5>
                                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#scheduleModal" onclick="resetModal()">➕ Add New Schedule</button>
                                </div>
                                <?php endif; ?>

                                <?php if (isset($schedules) && $schedules): ?>
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Faculty</th>
                                                <th>Course</th>
                                                <th>Room</th>
                                                <th>Day</th>
                                                <th>Date</th>
                                                <th>Time</th>
                                                <?php if (in_array($_SESSION['role'], ['ADMIN', 'SECRETARY', 'PROGRAM_HEAD'])): ?>
                                                <th>Actions</th>
                                                <?php endif; ?>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($schedules as $schedule): ?>
                                            <tr>
                                                <td><?php echo $schedule['username'] ?? 'N/A'; ?></td>
                                                <td><?php echo $schedule['course_code']; ?></td>
                                                <td><?php echo $schedule['room_number']; ?></td>
                                                <td><?php echo $schedule['day_of_week'] ?? 'N/A'; ?></td>
                                                <td><?php echo $schedule['date']; ?></td>
                                                <td><?php echo date('H:i', strtotime($schedule['start_time'])) . ' - ' . date('H:i', strtotime($schedule['end_time'])); ?></td>
                                                <?php if (in_array($_SESSION['role'], ['ADMIN', 'SECRETARY', 'PROGRAM_HEAD'])): ?>
                                                <td>
                                                    <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#scheduleModal" onclick="editSchedule(<?php echo $schedule['id']; ?>, '<?php echo addslashes($schedule['faculty_id']); ?>', '<?php echo addslashes($schedule['course_code']); ?>', '<?php echo addslashes($schedule['room_number']); ?>', '<?php echo addslashes($schedule['day_of_week']); ?>', '<?php echo $schedule['date']; ?>', '<?php echo $schedule['start_time']; ?>', '<?php echo $schedule['end_time']; ?>')">✏️ Edit</button>
                                                    <form method="POST" style="display:inline;">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="id" value="<?php echo $schedule['id']; ?>">
                                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Delete this schedule?')">🗑️ Delete</button>
                                                    </form>
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

                            <!-- Schedule Modal -->
                            <?php if (in_array($_SESSION['role'], ['ADMIN', 'SECRETARY', 'PROGRAM_HEAD']) && isset($faculties)): ?>
                            <div class="modal fade" id="scheduleModal" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="modalTitle">Add New Schedule</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form method="POST">
                                            <div class="modal-body">
                                                <input type="hidden" name="action" id="modalAction" value="add">
                                                <input type="hidden" name="id" id="scheduleId">
                                                <div class="mb-3">
                                                    <label for="faculty_id" class="form-label">🔽 Select Faculty</label>
                                                    <select class="form-select" id="faculty_id" name="faculty_id" required>
                                                        <?php foreach ($faculties as $faculty): ?>
                                                        <option value="<?php echo $faculty['id']; ?>"><?php echo $faculty['username']; ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="course_code" class="form-label">🔽 Select Course</label>
                                                    <select class="form-select" id="course_code" name="course_code" required>
                                                        <option>CS101</option><option>CS102</option><option>IT101</option>
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="day_of_week" class="form-label">📅 Day of Week</label>
                                                    <select class="form-select" id="day_of_week" name="day_of_week" required>
                                                        <option>Monday</option><option>Tuesday</option><option>Wednesday</option><option>Thursday</option><option>Friday</option><option>Saturday</option><option>Sunday</option>
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="start_time" class="form-label">🕒 Start Time</label>
                                                    <input type="time" class="form-control" id="start_time" name="start_time" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="end_time" class="form-label">🕒 End Time</label>
                                                    <input type="time" class="form-control" id="end_time" name="end_time" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="room_number" class="form-label">🔽 Select Classroom</label>
                                                    <select class="form-select" id="room_number" name="room_number" required>
                                                        <option>Room 101</option><option>Room 102</option><option>Room 201</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                <button type="submit" class="btn btn-primary">💾 Save Schedule</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
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
        function resetModal() {
            document.getElementById('modalTitle').textContent = 'Add New Schedule';
            document.getElementById('modalAction').value = 'add';
            document.getElementById('scheduleId').value = '';
            document.getElementById('faculty_id').value = '';
            document.getElementById('course_code').value = '';
            document.getElementById('day_of_week').value = '';
            document.getElementById('start_time').value = '';
            document.getElementById('end_time').value = '';
            document.getElementById('room_number').value = '';
        }
        function editSchedule(id, faculty_id, course_code, room_number, day_of_week, date, start_time, end_time) {
            document.getElementById('modalTitle').textContent = 'Edit Schedule';
            document.getElementById('modalAction').value = 'edit';
            document.getElementById('scheduleId').value = id;
            document.getElementById('faculty_id').value = faculty_id;
            document.getElementById('course_code').value = course_code;
            document.getElementById('room_number').value = room_number;
            document.getElementById('day_of_week').value = day_of_week;
            document.getElementById('start_time').value = start_time;
            document.getElementById('end_time').value = end_time;
        }
    </script>
</body>
</html>