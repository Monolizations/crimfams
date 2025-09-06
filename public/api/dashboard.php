<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../src/models/Database.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
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

$schedules = [];
if ($role === 'FACULTY') {
    $stmt = $db->prepare('SELECT * FROM schedules WHERE faculty_id = ? AND date = CURDATE() ORDER BY start_time');
    $stmt->execute([$user_id]);
    $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
} elseif (in_array($role, ['PROGRAM_HEAD', 'SECRETARY', 'ADMIN'])) {
    $query = 'SELECT s.*, u.username FROM schedules s JOIN users u ON s.faculty_id = u.id WHERE s.date = CURDATE() ORDER BY s.start_time';
    $stmt = $db->query($query);
    $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

echo json_encode(['schedules' => $schedules]);
?>