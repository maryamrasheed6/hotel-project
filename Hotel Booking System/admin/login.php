<?php
/**
 * Royal Stay - Administrator Login Portal
 */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth-check.php';

// If already admin, redirect to dashboard
if (is_admin()) {
    header('Location: ' . base_url('admin/index.php'));
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token()) {
        $error = 'Security session expired. Please attempt sign in again.';
    } else {
        $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
        $password = $_POST['password'] ?? '';

        if (!$email || empty($password)) {
            $error = 'Please provide both administrator email and password.';
        } else {
            $pdo = get_db();
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND role = 'admin' LIMIT 1");
            $stmt->execute([$email]);
            $admin = $stmt->fetch();

            if ($admin && password_verify($password, $admin['password'])) {
                if ($admin['status'] !== 'active') {
                    $error = 'Your administrative account has been deactivated.';
                } else {
                    // Set admin session
                    $_SESSION['user_id'] = $admin['id'];
                    $_SESSION['user_name'] = $admin['name'];
                    $_SESSION['user_email'] = $admin['email'];
                    $_SESSION['user_role'] = 'admin';

                    set_flash('success', "Welcome to Royal Stay Admin Portal, {$admin['name']}!");
                    header('Location: ' . base_url('admin/index.php'));
                    exit;
                }
            } else {
                $error = 'Invalid administrator credentials. Access restricted to authorized personnel.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Administration Portal | Royal Stay</title>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>👑</text></svg>">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Core & Admin Stylesheet -->
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/admin.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/responsive.css') ?>">
</head>
<body class="admin-login-body">

    <div class="admin-login-card">
        
        <div class="admin-login-brand">
            <div class="logo-icon">
                <i class="fa-solid fa-crown"></i>
            </div>
            <h2>Royal Stay</h2>
            <p>Executive Administration & Reservation Portal</p>
        </div>

        <?php if (!empty($error)): ?>
            <div style="background: var(--error-bg); color: var(--error); border: 1px solid rgba(239, 68, 68, 0.3); border-radius: 8px; padding: 0.85rem 1rem; margin-bottom: 1.5rem; font-size: 0.88rem; display: flex; align-items: center; gap: 0.75rem;">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <span><?= e($error) ?></span>
            </div>
        <?php endif; ?>

        <?= render_flash() ?>

        <!-- Quick Demo Autofill Helper -->
        <div style="background: #FFFDF9; border: 1px dashed var(--admin-accent); border-radius: 8px; padding: 0.85rem 1rem; margin-bottom: 1.5rem; display: flex; align-items: center; justify-content: space-between; font-size: 0.82rem;">
            <div>
                <span style="color: #B45309; font-weight: 600;"><i class="fa-solid fa-key"></i> Quick Admin Access:</span>
                <div style="color: var(--admin-text-muted); font-size: 0.78rem;">admin@royalstay.com / Admin@12345</div>
            </div>
            <button type="button" class="btn btn-outline btn-sm" onclick="fillAdminDemo()" style="padding: 0.3rem 0.6rem; font-size: 0.75rem;">
                Auto-Fill
            </button>
        </div>

        <form action="<?= base_url('admin/login.php') ?>" method="POST">
            <?= csrf_field() ?>

            <div class="form-group">
                <label class="form-label" for="adminEmail">Administrator Email</label>
                <div style="position: relative;">
                    <input type="email" name="email" id="adminEmail" class="form-control" placeholder="admin@royalstay.com" required style="padding-left: 2.75rem;">
                    <i class="fa-solid fa-envelope" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--admin-text-muted);"></i>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="adminPassword">Security Key / Password</label>
                <div style="position: relative;">
                    <input type="password" name="password" id="adminPassword" class="form-control" placeholder="Enter administrative password" required style="padding-left: 2.75rem; padding-right: 2.75rem;">
                    <i class="fa-solid fa-lock" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--admin-text-muted);"></i>
                    <button type="button" onclick="toggleAdminPassword(this)" style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--admin-text-muted); cursor: pointer;">
                        <i class="fa-regular fa-eye"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn btn-gold btn-block" style="margin-top: 1.5rem; padding: 0.9rem; font-size: 0.95rem;">
                <i class="fa-solid fa-shield-halved"></i> Access Management Portal
            </button>
        </form>

        <div style="text-align: center; margin-top: 2rem; border-top: 1px solid var(--admin-border); padding-top: 1.25rem;">
            <a href="<?= base_url() ?>" style="color: var(--admin-text-muted); font-size: 0.85rem; text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem;">
                <i class="fa-solid fa-arrow-left"></i> Return to Public Resort Site
            </a>
        </div>

    </div>

    <script>
    function fillAdminDemo() {
        document.getElementById('adminEmail').value = 'admin@royalstay.com';
        document.getElementById('adminPassword').value = 'Admin@12345';
    }

    function toggleAdminPassword(btn) {
        const pass = document.getElementById('adminPassword');
        const icon = btn.querySelector('i');
        if (pass.type === 'password') {
            pass.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            pass.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
    </script>
</body>
</html>
