<?php
// Codeigniter/application/views/auth/register.php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Alumni Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px;
        }

        .auth-wrapper {
            width: 100%;
            max-width: 500px;
        }

        .auth-card {
            background: rgba(255, 255, 255, 0.97);
            border-radius: 20px;
            box-shadow: 0 25px 70px rgba(0, 0, 0, 0.5);
            padding: 45px 40px;
        }

        .auth-logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .auth-logo .logo-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #0f3460, #e94560);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
        }

        .auth-logo .logo-icon i {
            font-size: 30px;
            color: white;
        }

        .auth-logo h2 {
            color: #0f3460;
            font-weight: 700;
            font-size: 24px;
            margin-bottom: 5px;
        }

        .auth-logo p {
            color: #6c757d;
            font-size: 14px;
        }

        .form-label {
            font-weight: 600;
            color: #343a40;
            font-size: 14px;
            margin-bottom: 6px;
        }

        .input-group-text {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-right: none;
            color: #6c757d;
        }

        .form-control {
            border-left: none;
            border: 1px solid #dee2e6;
            padding: 10px 15px;
            font-size: 14px;
        }

        .form-control:focus {
            border-color: #0f3460;
            box-shadow: 0 0 0 0.2rem rgba(15, 52, 96, 0.15);
        }

        .input-group .form-control:focus {
            border-left: none;
        }

        .input-group:focus-within .input-group-text {
            border-color: #0f3460;
        }

        .btn-register {
            background: linear-gradient(135deg, #0f3460, #e94560);
            border: none;
            color: white;
            padding: 12px;
            font-size: 16px;
            font-weight: 600;
            border-radius: 8px;
            width: 100%;
            transition: opacity 0.3s;
        }

        .btn-register:hover {
            opacity: 0.9;
            color: white;
        }

        .btn-register:disabled {
            opacity: 0.7;
        }

        /* Password strength bar */
        .strength-bar-container {
            height: 5px;
            background: #e9ecef;
            border-radius: 3px;
            margin-top: 8px;
            overflow: hidden;
        }

        .strength-bar {
            height: 100%;
            width: 0%;
            border-radius: 3px;
            transition: all 0.4s ease;
        }

        .strength-weak   { background: #dc3545; width: 25%; }
        .strength-fair   { background: #fd7e14; width: 50%; }
        .strength-good   { background: #198754; width: 75%; }
        .strength-strong { background: #0d6efd; width: 100%; }

        /* Password requirements list */
        .pwd-requirements {
            list-style: none;
            padding: 0;
            margin: 8px 0 0;
            font-size: 12px;
        }

        .pwd-requirements li {
            padding: 2px 0;
            color: #dc3545;
            transition: color 0.3s;
        }

        .pwd-requirements li.met {
            color: #198754;
        }

        .pwd-requirements li i {
            width: 14px;
            margin-right: 5px;
        }

        /* Domain badge */
        .domain-badge {
            background: #e8f4fd;
            border: 1px solid #bee3f8;
            color: #0f3460;
            border-radius: 6px;
            padding: 6px 10px;
            font-size: 12px;
            margin-top: 6px;
        }

        .divider {
            text-align: center;
            margin: 20px 0;
            color: #adb5bd;
            font-size: 13px;
            position: relative;
        }

        .divider::before,
        .divider::after {
            content: '';
            position: absolute;
            top: 50%;
            width: 42%;
            height: 1px;
            background: #dee2e6;
        }

        .divider::before { left: 0; }
        .divider::after  { right: 0; }

        .login-link {
            text-align: center;
            font-size: 14px;
            color: #6c757d;
        }

        .login-link a {
            color: #0f3460;
            font-weight: 700;
            text-decoration: none;
        }

        .login-link a:hover {
            color: #e94560;
            text-decoration: underline;
        }

        .alert {
            border-radius: 10px;
            font-size: 14px;
        }

        /* Toggle password button */
        .btn-toggle-pwd {
            border: 1px solid #dee2e6;
            border-left: none;
            background: #f8f9fa;
            color: #6c757d;
        }

        .btn-toggle-pwd:hover {
            background: #e9ecef;
        }
    </style>
</head>
<body>

<div class="auth-wrapper">
    <div class="auth-card">

        <!-- Logo Header -->
        <div class="auth-logo">
            <div class="logo-icon">
                <i class="fas fa-user-graduate"></i>
            </div>
            <h2>Alumni Dashboard</h2>
            <p>Create your university staff account</p>
        </div>

        <!-- ── Flash Messages ─────────────────────────────────── -->

        <?php if ($this->session->flashdata('error')): ?>
            <div class="alert alert-danger" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?php echo $this->session->flashdata('error'); ?>
            </div>
        <?php endif; ?>

        <!-- API Error Message -->
        <?php if (isset($error)): ?>
            <div class="alert alert-danger" role="alert">
                <i class="fas fa-times-circle me-2"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <!-- Field-level errors from API validator -->
        <?php if (isset($field_errors) && !empty($field_errors)): ?>
            <div class="alert alert-warning" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Please fix the following:</strong>
                <ul class="mb-0 mt-1">
                    <?php foreach ($field_errors as $fe): ?>
                        <li><?php echo htmlspecialchars($fe['msg']); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- CI3 Form Validation Errors -->
        <?php if (isset($errors)): ?>
            <div class="alert alert-warning" role="alert">
                <?php echo $errors; ?>
            </div>
        <?php endif; ?>

        <!-- ── Registration Form ──────────────────────────────── -->

        <?php echo form_open('auth/register_submit', array('id' => 'registerForm')); ?>

            <!-- Full Name -->
            <div class="mb-3">
                <label for="full_name" class="form-label">
                    <i class="fas fa-user me-1 text-muted"></i>Full Name
                </label>
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="fas fa-user"></i>
                    </span>
                    <input type="text"
                           class="form-control"
                           id="full_name"
                           name="full_name"
                           placeholder="e.g. John Smith"
                           value="<?php echo set_value('full_name'); ?>"
                           autocomplete="name"
                           required>
                </div>
            </div>

            <!-- University Email -->
            <div class="mb-3">
                <label for="email" class="form-label">
                    <i class="fas fa-envelope me-1 text-muted"></i>University Email
                </label>
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="fas fa-envelope"></i>
                    </span>
                    <input type="email"
                           class="form-control"
                           id="email"
                           name="email"
                           placeholder="w123456789@my.westminster.ac.uk"
                           value="<?php echo set_value('email'); ?>"
                           autocomplete="email"
                           required>
                </div>
                <div class="domain-badge mt-2">
    <i class="fas fa-info-circle me-1"></i>
    Accepted: <strong>@my.westminster.ac.uk</strong>, 
    <strong>@westminster.ac.uk</strong>, 
    <strong>@iit.ac.lk</strong>
</div>
            </div>

            <!-- Password -->
            <div class="mb-3">
                <label for="password" class="form-label">
                    <i class="fas fa-lock me-1 text-muted"></i>Password
                </label>
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="fas fa-lock"></i>
                    </span>
                    <input type="password"
                           class="form-control"
                           id="password"
                           name="password"
                           placeholder="Create a strong password"
                           autocomplete="new-password"
                           oninput="checkStrength(this.value)"
                           required>
                    <button class="btn btn-toggle-pwd" type="button"
                            onclick="togglePwd('password', 'icon1')">
                        <i class="fas fa-eye" id="icon1"></i>
                    </button>
                </div>

                <!-- Strength Bar -->
                <div class="strength-bar-container">
                    <div class="strength-bar" id="strengthBar"></div>
                </div>
                <small id="strengthText" class="text-muted"></small>

                <!-- Requirements -->
                <ul class="pwd-requirements" id="pwdReqs">
                    <li id="req-len">
                        <i class="fas fa-times"></i>At least 8 characters
                    </li>
                    <li id="req-upper">
                        <i class="fas fa-times"></i>One uppercase letter (A-Z)
                    </li>
                    <li id="req-lower">
                        <i class="fas fa-times"></i>One lowercase letter (a-z)
                    </li>
                    <li id="req-num">
                        <i class="fas fa-times"></i>One number (0-9)
                    </li>
                    <li id="req-special">
                        <i class="fas fa-times"></i>One special character (@$!%*?&)
                    </li>
                </ul>
            </div>

            <!-- Confirm Password -->
            <div class="mb-4">
                <label for="confirm_password" class="form-label">
                    <i class="fas fa-lock me-1 text-muted"></i>Confirm Password
                </label>
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="fas fa-lock"></i>
                    </span>
                    <input type="password"
                           class="form-control"
                           id="confirm_password"
                           name="confirm_password"
                           placeholder="Repeat your password"
                           autocomplete="new-password"
                           oninput="checkMatch()"
                           required>
                    <button class="btn btn-toggle-pwd" type="button"
                            onclick="togglePwd('confirm_password', 'icon2')">
                        <i class="fas fa-eye" id="icon2"></i>
                    </button>
                </div>
                <div id="matchMsg" class="mt-1" style="font-size:12px;"></div>
            </div>

            <!-- Submit Button -->
            <div class="mb-3">
                <button type="submit" class="btn btn-register" id="submitBtn">
                    <i class="fas fa-user-plus me-2"></i>Create Account
                </button>
            </div>

        <?php echo form_close(); ?>

        <!-- Divider -->
        <div class="divider">or</div>

        <!-- Link to Login -->
        <div class="login-link">
            Already have an account?
            <a href="<?php echo base_url('auth/login'); ?>">Sign in here</a>
        </div>

        <!-- Resend verification link -->
        <div class="login-link mt-2">
            <a href="<?php echo base_url('auth/resend_verification'); ?>" 
               style="color:#6c757d; font-weight:400; font-size:13px;">
                <i class="fas fa-envelope me-1"></i>Didn't receive verification email?
            </a>
        </div>

    </div><!-- end auth-card -->
</div><!-- end auth-wrapper -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // ── Toggle Password Visibility ────────────────────────────────────────
    function togglePwd(fieldId, iconId) {
        var field = document.getElementById(fieldId);
        var icon  = document.getElementById(iconId);

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

    // ── Password Strength Checker ─────────────────────────────────────────
    function checkStrength(password) {
        var bar  = document.getElementById('strengthBar');
        var text = document.getElementById('strengthText');

        // Define each requirement
        var requirements = {
            'req-len'    : password.length >= 8,
            'req-upper'  : /[A-Z]/.test(password),
            'req-lower'  : /[a-z]/.test(password),
            'req-num'    : /[0-9]/.test(password),
            'req-special': /[@$!%*?&]/.test(password)
        };

        var passed = 0;

        // Update each requirement item
        for (var id in requirements) {
            var li   = document.getElementById(id);
            var icon = li.querySelector('i');
            if (requirements[id]) {
                passed++;
                li.classList.add('met');
                icon.classList.remove('fa-times');
                icon.classList.add('fa-check');
            } else {
                li.classList.remove('met');
                icon.classList.remove('fa-check');
                icon.classList.add('fa-times');
            }
        }

        // Update strength bar
        bar.className = 'strength-bar';

        if (password.length === 0) {
            bar.style.width = '0%';
            text.textContent = '';
            text.style.color = '';
        } else if (passed <= 1) {
            bar.classList.add('strength-weak');
            text.textContent = 'Weak password';
            text.style.color = '#dc3545';
        } else if (passed === 2) {
            bar.classList.add('strength-fair');
            text.textContent = 'Fair password';
            text.style.color = '#fd7e14';
        } else if (passed <= 4) {
            bar.classList.add('strength-good');
            text.textContent = 'Good password';
            text.style.color = '#198754';
        } else {
            bar.classList.add('strength-strong');
            text.textContent = '✓ Strong password';
            text.style.color = '#0d6efd';
        }
    }

    // ── Password Match Checker ────────────────────────────────────────────
    function checkMatch() {
        var pwd  = document.getElementById('password').value;
        var cpwd = document.getElementById('confirm_password').value;
        var msg  = document.getElementById('matchMsg');

        if (cpwd.length === 0) {
            msg.innerHTML = '';
            return;
        }

        if (pwd === cpwd) {
            msg.innerHTML = '<span style="color:#198754;">' +
                '<i class="fas fa-check me-1"></i>Passwords match</span>';
        } else {
            msg.innerHTML = '<span style="color:#dc3545;">' +
                '<i class="fas fa-times me-1"></i>Passwords do not match</span>';
        }
    }

    // ── Prevent Double Submit ─────────────────────────────────────────────
    document.getElementById('registerForm').addEventListener('submit', function(e) {
        var pwd  = document.getElementById('password').value;
        var cpwd = document.getElementById('confirm_password').value;
        var name = document.getElementById('full_name').value.trim();
        var email = document.getElementById('email').value.trim();

        // Basic client-side check before submitting
        if (!name || !email || !pwd || !cpwd) {
            e.preventDefault();
            alert('Please fill in all fields.');
            return;
        }

        if (pwd !== cpwd) {
            e.preventDefault();
            alert('Passwords do not match!');
            return;
        }

        if (pwd.length < 8) {
            e.preventDefault();
            alert('Password must be at least 8 characters.');
            return;
        }

        // Disable button to prevent double submit
        var btn = document.getElementById('submitBtn');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Creating Account...';
    });
</script>
</body>
</html>