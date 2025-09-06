<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/controllers/AuthController.php';
require_once __DIR__ . '/../src/models/Database.php';
$db = Database::getInstance()->getConnection();

$request = $_SERVER['REQUEST_URI'];
$base_path = ''; // Adjust if needed

$path = str_replace($base_path, '', $request);
$path = explode('?', $path)[0];

switch ($path) {
    case '/':
    case '/login':
        $authController = new AuthController();
        $authController->login();
        break;
    case '/logout':
        $authController = new AuthController();
        $authController->logout();
        break;
    case '/dashboard':
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
        $role = $_SESSION['role'];
        $user_id = $_SESSION['user_id'];
        if ($role === 'FACULTY') {
            $stmt = $db->prepare('SELECT * FROM schedules WHERE faculty_id = ? AND date = CURDATE() ORDER BY start_time');
            $stmt->execute([$user_id]);
            $schedules = $stmt->fetchAll();
        } elseif (in_array($role, ['PROGRAM_HEAD', 'SECRETARY', 'ADMIN'])) {
            $query = 'SELECT s.*, u.username FROM schedules s JOIN users u ON s.faculty_id = u.id WHERE s.date = CURDATE() ORDER BY s.start_time';
            $stmt = $db->query($query);
            $schedules = $stmt->fetchAll();
        }
        $page = 'Dashboard';
        include __DIR__ . '/../resources/views/dashboard.php';
        break;
    case '/schedules':
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
        $role = $_SESSION['role'];
        $user_id = $_SESSION['user_id'];

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
            // Redirect to avoid resubmission
            header('Location: ' . BASE_URL . '/schedules');
            exit;
        }

        // Fetch schedules
        if ($role === 'FACULTY') {
            $stmt = $db->prepare('SELECT * FROM schedules WHERE faculty_id = ? ORDER BY date, start_time');
            $stmt->execute([$user_id]);
            $schedules = $stmt->fetchAll();
        } else {
            $stmt = $db->query('SELECT s.*, u.username FROM schedules s JOIN users u ON s.faculty_id = u.id ORDER BY s.date, s.start_time');
            $schedules = $stmt->fetchAll();
        }

        // Fetch faculties for dropdown
        if ($role === 'ADMIN' || $role === 'SECRETARY') {
            $faculties = $db->query('SELECT id, username FROM users WHERE role = "FACULTY" OR role = "PROGRAM_HEAD"')->fetchAll();
        }

        include __DIR__ . '/../resources/views/schedules.php';
        break;
    case '/leave':
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
        $role = $_SESSION['role'];
        $user_id = $_SESSION['user_id'];

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
            header('Location: ' . BASE_URL . '/leave');
            exit;
        }

        // Fetch leave requests
        if ($role === 'FACULTY') {
            $stmt = $db->prepare('SELECT lr.*, u.username FROM leave_requests lr LEFT JOIN users u ON lr.faculty_id = u.id WHERE lr.faculty_id = ? ORDER BY lr.requested_at DESC');
            $stmt->execute([$user_id]);
            $leave_requests = $stmt->fetchAll();
        } elseif (in_array($role, ['ADMIN', 'SECRETARY'])) {
            $stmt = $db->query('SELECT lr.*, u.username FROM leave_requests lr JOIN users u ON lr.faculty_id = u.id ORDER BY lr.requested_at DESC');
            $leave_requests = $stmt->fetchAll();
        }

        include __DIR__ . '/../resources/views/leave.php';
        break;
    case '/faculties':
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
        $page = 'Faculties';
        include __DIR__ . '/../resources/views/dashboard.php';
        break;
    case '/classrooms':
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
        $role = $_SESSION['role'];
        if (!in_array($role, ['SECRETARY', 'ADMIN'])) {
            http_response_code(403);
            echo 'Access denied';
            exit;
        }
        include __DIR__ . '/../resources/views/classrooms.php';
        break;
    case '/qr-scanner':
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
        $page = 'QR Scanner';
        include __DIR__ . '/../resources/views/dashboard.php';
        break;
    case '/qr-generator':
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
        include __DIR__ . '/../resources/views/qr_generator.php';
        break;
    case '/office-logbook':
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
        $role = $_SESSION['role'];
        if (!in_array($role, ['PROGRAM_HEAD', 'ADMIN'])) {
            http_response_code(403);
            echo 'Access denied';
            exit;
        }
        include __DIR__ . '/../resources/views/office_logbook.php';
        break;
    case '/reports':
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
        $role = $_SESSION['role'];
        if (!in_array($role, ['SECRETARY', 'ADMIN'])) {
            http_response_code(403);
            echo 'Access denied';
            exit;
        }

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
                $schedules = $stmt->fetchAll();
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
        $faculties = $db->query('SELECT id, username FROM users WHERE role = "FACULTY" OR role = "PROGRAM_HEAD"')->fetchAll();

        include __DIR__ . '/../resources/views/reports.php';
        break;
    case '/profile':
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
        $page = 'Profile';
        include __DIR__ . '/../resources/views/dashboard.php';
        break;
    case '/settings':
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
        $page = 'Settings';
        include __DIR__ . '/../resources/views/dashboard.php';
        break;
    case '/qr-scanner':
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
        include __DIR__ . '/../resources/views/qr_scanner.php';
        break;
    case '/api/scan':
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents('php://input'), true);
        $user_id = $_SESSION['user_id'];
        // Process scan
        if ($data['action'] === 'office_log') {
            // Handle office log
            echo json_encode(['success' => true, 'message' => '✅ Time-in recorded at ' . date('H:i', strtotime($data['timestamp']))]);
        } elseif ($data['action'] === 'classroom_checkin') {
            // Handle classroom checkin
            echo json_encode(['success' => true, 'message' => '✅ Success! Checked in to ' . $data['roomId']]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
        }
        break;
    case '/api/login':
        $authController = new AuthController();
        $authController->apiLogin();
        break;
    default:
        http_response_code(404);
        echo '404 Not Found';
        break;
}

?>