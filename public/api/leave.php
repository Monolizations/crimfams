<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../src/models/Database.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$db = Database::getInstance()->getConnection();
$role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Fetch leave requests
    if ($role === 'FACULTY') {
        $stmt = $db->prepare('SELECT lr.*, u.username FROM leave_requests lr LEFT JOIN users u ON lr.faculty_id = u.id WHERE lr.faculty_id = ? ORDER BY lr.requested_at DESC');
        $stmt->execute([$user_id]);
        $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } elseif (in_array($role, ['ADMIN', 'SECRETARY'])) {
        $stmt = $db->query('SELECT lr.*, u.username FROM leave_requests lr JOIN users u ON lr.faculty_id = u.id ORDER BY lr.requested_at DESC');
        $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    echo json_encode(['requests' => $requests ?? []]);
} elseif ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $action = $data['action'] ?? '';

    if ($action === 'request' && $role === 'FACULTY') {
        $start_date = $data['start_date'];
        $end_date = $data['end_date'];
        $reason = $data['reason'];

        // Validate minimum 2 weeks
        $min_date = date('Y-m-d', strtotime('+2 weeks'));
        if ($start_date >= $min_date) {
            $stmt = $db->prepare('INSERT INTO leave_requests (faculty_id, start_date, end_date, reason) VALUES (?, ?, ?, ?)');
            $stmt->execute([$user_id, $start_date, $end_date, $reason]);
            echo json_encode(['success' => true]);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Start date must be at least 2 weeks from now']);
        }
    } elseif ($action === 'approve' && in_array($role, ['ADMIN', 'SECRETARY'])) {
        $stmt = $db->prepare('UPDATE leave_requests SET status = ? WHERE id = ?');
        $stmt->execute(['Approved', $data['id']]);
        echo json_encode(['success' => true]);
    } elseif ($action === 'reject' && in_array($role, ['ADMIN', 'SECRETARY'])) {
        $stmt = $db->prepare('UPDATE leave_requests SET status = ?, admin_note = ? WHERE id = ?');
        $stmt->execute(['Rejected', $data['admin_note'], $data['id']]);
        echo json_encode(['success' => true]);
    } else {
        http_response_code(403);
        echo json_encode(['error' => 'Forbidden']);
    }
}
?>