<?php
/**
 * Royal Stay - Customer Authentication Portal (Sign In & Sign Up)
 */

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth-check.php';

// If already logged in, redirect to home or requested destination
if (is_logged_in()) {
    header('Location: ' . base_url('my-bookings.php'));
    exit;
}

$pageTitle = 'Sign In or Register';
$activeNav = 'auth';
$redirect = sanitize($_GET['redirect'] ?? '');

require_once __DIR__ . '/includes/header.php';
?>

<div class="page-banner">
    <div class="container">
        <h1>Guest Access</h1>
        <p>Sign in to manage your reservations, or create an account for exclusive privileges.</p>
        <div class="breadcrumb">
            <a href="<?= base_url() ?>">Home</a>
            <span>/</span>
            <span>Guest Access</span>
        </div>
    </div>
</div>

<div class="section">
    <div class="container">
        
        <div style="max-width: 520px; margin: 0 auto;">
            
            <div style="background: #ffffff; border-radius: var(--radius-lg); border: 1px solid var(--border); box-shadow: var(--shadow-lg); overflow: hidden;">
                
                <!-- Tab Headers -->
                <div style="display: flex; border-bottom: 1px solid var(--border); background: var(--bg-surface);">
                    <button type="button" id="tabLoginBtn" class="auth-tab-btn active" onclick="switchAuthTab('login')">
                        <i class="fa-solid fa-arrow-right-to-bracket"></i> Sign In
                    </button>
                    <button type="button" id="tabRegisterBtn" class="auth-tab-btn" onclick="switchAuthTab('register')">
                        <i class="fa-solid fa-user-plus"></i> Create Account
                    </button>
                </div>

                <div style="padding: 2.5rem;">
                    
                    <!-- TAB 1: SIGN IN -->
                    <div id="loginTabPane">
                        <div style="text-align: center; margin-bottom: 2rem;">
                            <h3 style="font-family: 'Playfair Display', serif; font-size: 1.6rem; color: var(--primary); margin-bottom: 0.35rem;">
                                Welcome Back
                            </h3>
                            <p style="color: var(--text-muted); font-size: 0.88rem;">
                                Access your reservation history, preferences, and expedited checkout.
                            </p>
                        </div>

                        <!-- Demo Customer Helper -->
                        <div style="background: #FFFDF9; border: 1px dashed var(--accent); border-radius: var(--radius-sm); padding: 0.85rem 1rem; margin-bottom: 1.5rem; display: flex; align-items: center; justify-content: space-between; font-size: 0.82rem;">
                            <div>
                                <span style="color: var(--accent-dark); font-weight: 600;"><i class="fa-solid fa-key"></i> Quick Demo Guest:</span>
                                <div style="color: var(--text-muted); font-size: 0.78rem;">john.doe@example.com / Customer@12345</div>
                            </div>
                            <button type="button" class="btn btn-outline btn-sm" onclick="fillDemoGuest()" style="font-size: 0.75rem; padding: 0.3rem 0.6rem;">
                                Auto-Fill
                            </button>
                        </div>

                        <form action="<?= base_url('actions/auth.php?action=login') ?>" method="POST">
                            <?= csrf_field() ?>
                            <input type="hidden" name="redirect" value="<?= e($redirect) ?>">

                            <div class="form-group">
                                <label class="form-label" for="loginEmail">Email Address</label>
                                <div style="position: relative;">
                                    <input type="email" name="email" id="loginEmail" class="form-control" placeholder="guest@example.com" required style="padding-left: 2.75rem;">
                                    <i class="fa-solid fa-envelope" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="loginPassword">Password</label>
                                <div style="position: relative;">
                                    <input type="password" name="password" id="loginPassword" class="form-control" placeholder="Enter your password" required style="padding-left: 2.75rem; padding-right: 2.75rem;">
                                    <i class="fa-solid fa-lock" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
                                    <button type="button" onclick="togglePasswordVisibility('loginPassword', this)" style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--text-muted); cursor: pointer;">
                                        <i class="fa-regular fa-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-gold btn-block" style="margin-top: 1.5rem; padding: 0.85rem;">
                                <i class="fa-solid fa-right-to-bracket"></i> Sign In to Account
                            </button>
                        </form>
                    </div>

                    <!-- TAB 2: REGISTER -->
                    <div id="registerTabPane" style="display: none;">
                        <div style="text-align: center; margin-bottom: 2rem;">
                            <h3 style="font-family: 'Playfair Display', serif; font-size: 1.6rem; color: var(--primary); margin-bottom: 0.35rem;">
                                Join Royal Stay
                            </h3>
                            <p style="color: var(--text-muted); font-size: 0.88rem;">
                                Experience seamless room booking and personalized luxury concierge service.
                            </p>
                        </div>

                        <form action="<?= base_url('actions/auth.php?action=register') ?>" method="POST">
                            <?= csrf_field() ?>
                            <input type="hidden" name="redirect" value="<?= e($redirect) ?>">

                            <div class="form-group">
                                <label class="form-label" for="regName">Full Legal Name *</label>
                                <div style="position: relative;">
                                    <input type="text" name="name" id="regName" class="form-control" placeholder="e.g. Eleanor Vance" required style="padding-left: 2.75rem;">
                                    <i class="fa-solid fa-user" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="regEmail">Email Address *</label>
                                <div style="position: relative;">
                                    <input type="email" name="email" id="regEmail" class="form-control" placeholder="e.g. eleanor@example.com" required style="padding-left: 2.75rem;">
                                    <i class="fa-solid fa-envelope" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="regPhone">Contact Phone Number *</label>
                                <div style="position: relative;">
                                    <input type="tel" name="phone" id="regPhone" class="form-control" placeholder="+1 (555) 000-0000" required style="padding-left: 2.75rem;">
                                    <i class="fa-solid fa-phone" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="regPassword">Create Password *</label>
                                <div style="position: relative;">
                                    <input type="password" name="password" id="regPassword" class="form-control" placeholder="Minimum 6 characters" minlength="6" required style="padding-left: 2.75rem; padding-right: 2.75rem;">
                                    <i class="fa-solid fa-lock" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
                                    <button type="button" onclick="togglePasswordVisibility('regPassword', this)" style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--text-muted); cursor: pointer;">
                                        <i class="fa-regular fa-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="regPasswordConfirm">Confirm Password *</label>
                                <div style="position: relative;">
                                    <input type="password" name="password_confirm" id="regPasswordConfirm" class="form-control" placeholder="Repeat your password" minlength="6" required style="padding-left: 2.75rem;">
                                    <i class="fa-solid fa-shield-check" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary btn-block" style="margin-top: 1.5rem; padding: 0.85rem;">
                                <i class="fa-solid fa-user-plus"></i> Complete Registration
                            </button>
                        </form>
                    </div>

                </div>

            </div>

        </div>

    </div>
</div>

<style>
.auth-tab-btn {
    flex: 1;
    padding: 1.1rem 1.5rem;
    background: none;
    border: none;
    font-weight: 600;
    font-size: 0.95rem;
    color: var(--text-muted);
    cursor: pointer;
    transition: var(--transition);
    border-bottom: 2px solid transparent;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}
.auth-tab-btn:hover {
    color: var(--primary);
    background: rgba(6, 59, 62, 0.03);
}
.auth-tab-btn.active {
    color: var(--primary);
    background: #ffffff;
    border-bottom: 2px solid var(--accent);
}
</style>

<script>
function switchAuthTab(tab) {
    const loginPane = document.getElementById('loginTabPane');
    const registerPane = document.getElementById('registerTabPane');
    const loginBtn = document.getElementById('tabLoginBtn');
    const registerBtn = document.getElementById('tabRegisterBtn');

    if (tab === 'login') {
        loginPane.style.display = 'block';
        registerPane.style.display = 'none';
        loginBtn.classList.add('active');
        registerBtn.classList.remove('active');
    } else {
        loginPane.style.display = 'none';
        registerPane.style.display = 'block';
        loginBtn.classList.remove('active');
        registerBtn.classList.add('active');
    }
}

function fillDemoGuest() {
    document.getElementById('loginEmail').value = 'john.doe@example.com';
    document.getElementById('loginPassword').value = 'Customer@12345';
}

function togglePasswordVisibility(fieldId, btn) {
    const field = document.getElementById(fieldId);
    const icon = btn.querySelector('i');
    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        field.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
