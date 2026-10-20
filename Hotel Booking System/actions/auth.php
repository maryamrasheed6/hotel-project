<?php
/**
 * Royal Stay - Process Guest Authentication (Login & Registration)
 */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth-check.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . base_url('auth.php'));
    exit;
}

// 1. Verify CSRF
if (!verify_csrf_token()) {
    set_flash('error', 'Security token expired. Please try again.');
    header('Location: ' . base_url('auth.php'));
    exit;
}

$pdo = get_db();
$action = sanitize($_GET['action'] ?? 'login');
$redirect = sanitize($_POST['redirect'] ?? '');
$targetUrl = !empty($redirect) ? $redirect : base_url('my-bookings.php');

// ==========================================
// 1. LOGIN PROCESSING
// ==========================================
if ($action === 'login') {
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';

    if (!$email || empty($password)) {
        set_flash('error', 'Please provide both your registered email and password.');
        header('Location: ' . base_url('auth.php?redirect=' . urlencode($redirect)));
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        set_flash('error', 'Invalid email address or password combination.');
        header('Location: ' . base_url('auth.php?redirect=' . urlencode($redirect)));
        exit;
    }

    if ($user['status'] !== 'active') {
        set_flash('error', 'Your account has been deactivated. Please contact hotel concierge.');
        header('Location: ' . base_url('auth.php'));
        exit;
    }

    // Set Session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $user['role'];

    set_flash('success', "Welcome back, {$user['name']}! You are successfully signed in.");

    // If user is admin, redirect to admin index
    if ($user['role'] === 'admin') {
        header('Location: ' . base_url('admin/index.php'));
        exit;
    }

    header('Location: ' . $targetUrl);
    exit;
}

// ==========================================
// 2. REGISTRATION PROCESSING
// ==========================================
if ($action === 'register') {
    $name = sanitize($_POST['name'] ?? '');
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $phone = sanitize($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $passwordConfirm = $_POST['password_confirm'] ?? '';

    if (empty($name) || !$email || empty($phone) || empty($password)) {
        set_flash('error', 'Please complete all required fields with valid details.');
        header('Location: ' . base_url('auth.php?tab=register'));
        exit;
    }

    if (strlen($password) < 6) {
        set_flash('error', 'Password must be at least 6 characters in length.');
        header('Location: ' . base_url('auth.php?tab=register'));
        exit;
    }

    if ($password !== $passwordConfirm) {
        set_flash('error', 'Passwords do not match. Please re-enter.');
        header('Location: ' . base_url('auth.php?tab=register'));
        exit;
    }

    // Check if email already registered
    $checkStmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
    $checkStmt->execute([$email]);
    if ($checkStmt->fetch()) {
        set_flash('error', 'An account with this email address already exists. Please sign in instead.');
        header('Location: ' . base_url('auth.php'));
        exit;
    }

    // Hash password & Insert user
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    $insertStmt = $pdo->prepare("
        INSERT INTO users (name, email, phone, password, role, status, created_at)
        VALUES (?, ?, ?, ?, 'customer', 'active', NOW())
    ");
    $insertStmt->execute([$name, $email, $phone, $hashedPassword]);
    $userId = (int)$pdo->lastInsertId();

    // Auto-login registered customer
    $_SESSION['user_id'] = $userId;
    $_SESSION['user_name'] = $name;
    $_SESSION['user_email'] = $email;
    $_SESSION['user_role'] = 'customer';

    set_flash('success', "Welcome to Royal Stay, {$name}! Your account has been created.");
    header('Location: ' . $targetUrl);
    exit;
}

header('Location: ' . base_url('auth.php'));
exit;
