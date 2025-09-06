<?php

require_once __DIR__ . '/../models/Auth.php';

class AuthController {
    private $auth;

    public function __construct() {
        $this->auth = new Auth();
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';

            if ($this->auth->login($username, $password)) {
                header('Location: ' . BASE_URL . '/dashboard');
                exit;
            } else {
                $error = 'Invalid username or password';
                include __DIR__ . '/../../resources/views/login.php';
            }
        } else {
            include __DIR__ . '/../../resources/views/login.php';
        }
    }

    public function logout() {
        $this->auth->logout();
        header('Location: ' . BASE_URL . '/login');
        exit;
    }

    public function apiLogin() {
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents('php://input'), true);
        $username = $data['username'] ?? '';
        $password = $data['password'] ?? '';

        if ($this->auth->login($username, $password)) {
            echo json_encode(['success' => true, 'message' => 'Login successful']);
        } else {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
        }
    }
}
?>