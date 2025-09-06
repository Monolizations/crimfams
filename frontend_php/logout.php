<?php
session_start();
require_once __DIR__ . '/../src/controllers/AuthController.php';

$authController = new AuthController();
$authController->logout();

header('Location: login.php');
exit;
?>