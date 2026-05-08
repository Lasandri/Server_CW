<?php
// Codeigniter/application/views/auth/reset_password.php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Alumni Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px;
        }
        .auth-wrapper { width: 100%; max-width: 450px; }
        .auth-card {
            background: rgba(255, 255, 255, 0.97);
            border-radius: 20px;
            box-shadow: 0 25px 70px rgba(0,0,0,0.5);
            padding: 45px 40px;
        }
        .auth-logo { text-align: center; margin-bottom: 30px; }
        .logo-icon {
            width: 70px; height: 70px;
            background: linear-gradient(135deg, #198754, #0f3460);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 15px;
        }
        .logo-icon i { font-size: 30px; color: white; }
        .auth-logo h2 { color: #0f3460; font-weight: 700; font-size: 24px; margin-bottom: 5px; }
        .auth-logo p  { color: #6c757d; font-size: 14px; }
        .form-label { font-weight: 600; color: #343a40; font-size: 14px; }
        .input-group-text {
            background: #f8f9fa; border: 1px solid #dee2e6;
            border-right: none; color: #6c757d;
        }
        .form-control {
            border: 1px solid #dee2e6; border-left: none;
            padding: 10px 15px; font-size: 14px;
        }
        .form-control:focus {
            border-color: #198754;
            box-shadow: 0 0 0 0.2rem rgba(25, 135, 84, 0.15);
        }
        .btn-reset {
            background: linear-gradient(135deg, #198754, #0f3460);
            border: none; color: white; padding: 12px;
            font-size: 16px; font-weight: 600; border-radius: 8px;
            width: 100%;
        }
        .btn-reset:hover { opacity: 0.9; color: white; }
        .btn-toggle-pwd {
            border: 1px solid #dee2e6; border-left: none;
            background: #f8f9fa; color: #6c757d;
        }
        .strength-bar-container {
            height: 5px; background: #e9ecef;
            border-radius: 3px; margin-top: 8px; overflow: hidden;
        }
        .strength-bar { height: 100%; width: 0%; border-radius: 3px; transition: all 0.4s; }
        .strength-weak   { background: #dc3545; width: 25%; }
        .strength-fair   { background: #fd7e14; width: 50%; }
        .strength-good   { background: #198754; width: 75%; }
        .strength-strong { background: #0d6efd; width: 100%; }
        .alert { border-radius: 10px; font-size: 14px; }
        .back-link { text-align: center; margin-top: 15px; }
        .back-link a { color: #6c757d; font-size: 14px; text-decoration: none; }
        .back-link a:hover { color: #0f3460; }
    </style>
</head>
<body>

<div class="auth-wrapper">
    <div class="auth-card">

        <div class="auth-logo">
            <div class="logo-icon">
                <i class="fas fa-shield-alt"></i>
            </div>
            <h2>Reset Password</h2>
            <p>Create a new secure password</p>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-times-circle me-2"></i>
                <?php echo htmlspecialchars($error); ?>
                <br><br>
                <a href="<?php echo base_url('auth/forgot_password'); ?>" class="alert-link">
                    Request a new reset link
                </a>
            </div>
        <?php endif; ?>

        <?php if (isset($errors)): ?>
            <div class="alert alert-warning"><?php echo $errors; ?></div>
        <?php endif; ?>

        <?php echo form_open('auth/reset_password_submit', array('id' => 'resetForm')); ?>

            <!-- Hidden Token -->
            <input type="hidden" name="token"
                   value="<?php echo htmlspecialchars(isset($token) ? $token : ''); ?>">

            <!-- New Password -->
            <div class="mb-3">
                <label for="password" class="form-label">New Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password"
                           class="form-control"
                           id="password"
                           name="password"
                           placeholder="Min. 8 characters"
                           oninput="checkStrength(this.value)"
                           required>
                    <button class="btn btn-toggle-pwd" type="button"
                            onclick="togglePwd('password','icon1')">
                        <i class="fas fa-eye" id="icon1"></i>
                    </button>
                </div>
                <div class="strength-bar-container">
                    <div class="strength-bar" id="strengthBar"></div>
                </div>
                <small id="strengthText" class="text-muted"></small>
            </div>

            <!-- Confirm New Password -->
            <div class="mb-4">
                <label for="confirm_password" class="form-label">Confirm New Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password"
                           class="form-control"
                           id="confirm_password"
                           name="confirm_password"
                           placeholder="Repeat new password"
                           oninput="checkMatch()"
                           required>
                    <button class="btn btn-toggle-pwd" type="button"
                            onclick="togglePwd('confirm_password','icon2')">
                        <i class="fas fa-eye" id="icon2"></i>
                    </button>
                </div>
                <div id="matchMsg" class="mt-1" style="font-size:12px;"></div>
            </div>

            <button type="submit" class="btn btn-reset">
                <i class="fas fa-save me-2"></i>Reset Password
            </button>

        <?php echo form_close(); ?>

        <div class="back-link">
            <a href="<?php echo base_url('auth/login'); ?>">
                <i class="fas fa-arrow-left me-1"></i>Back to Login
            </a>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function togglePwd(fId, iId) {
        var f = document.getElementById(fId);
        var i = document.getElementById(iId);
        f.type = f.type === 'password' ? 'text' : 'password';
        i.classList.toggle('fa-eye');
        i.classList.toggle('fa-eye-slash');
    }

    function checkStrength(password) {
        var bar  = document.getElementById('strengthBar');
        var text = document.getElementById('strengthText');
        var passed = [
            password.length >= 8,
            /[A-Z]/.test(password),
            /[a-z]/.test(password),
            /[0-9]/.test(password),
            /[@$!%*?&]/.test(password)
        ].filter(Boolean).length;

        bar.className = 'strength-bar';
        if (password.length === 0) { bar.style.width = '0%'; text.textContent = ''; return; }
        if (passed <= 1) { bar.classList.add('strength-weak');   text.textContent = 'Weak';   text.style.color = '#dc3545'; }
        else if (passed === 2) { bar.classList.add('strength-fair');   text.textContent = 'Fair';   text.style.color = '#fd7e14'; }
        else if (passed <= 4) { bar.classList.add('strength-good');   text.textContent = 'Good';   text.style.color = '#198754'; }
        else                  { bar.classList.add('strength-strong'); text.textContent = 'Strong'; text.style.color = '#0d6efd'; }
    }

    function checkMatch() {
        var pwd  = document.getElementById('password').value;
        var cpwd = document.getElementById('confirm_password').value;
        var msg  = document.getElementById('matchMsg');
        if (!cpwd) { msg.innerHTML = ''; return; }
        msg.innerHTML = pwd === cpwd
            ? '<span style="color:#198754"><i class="fas fa-check me-1"></i>Passwords match</span>'
            : '<span style="color:#dc3545"><i class="fas fa-times me-1"></i>Passwords do not match</span>';
    }
</script>
</body>
</html>