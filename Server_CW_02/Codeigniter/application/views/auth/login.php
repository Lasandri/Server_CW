<?php
// Codeigniter/application/views/auth/login.php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Alumni Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px;
        }

        .auth-wrapper { width: 100%; max-width: 450px; }

        .auth-card {
            background: rgba(255, 255, 255, 0.97);
            border-radius: 20px;
            box-shadow: 0 25px 70px rgba(0, 0, 0, 0.5);
            padding: 45px 40px;
        }

        .auth-logo { text-align: center; margin-bottom: 30px; }

        .logo-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #0f3460, #e94560);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
        }

        .logo-icon i { font-size: 30px; color: white; }

        .auth-logo h2 { color: #0f3460; font-weight: 700; font-size: 24px; margin-bottom: 5px; }
        .auth-logo p  { color: #6c757d; font-size: 14px; }

        .form-label { font-weight: 600; color: #343a40; font-size: 14px; }

        .input-group-text {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-right: none;
            color: #6c757d;
        }

        .form-control {
            border: 1px solid #dee2e6;
            border-left: none;
            padding: 10px 15px;
            font-size: 14px;
        }

        .form-control:focus {
            border-color: #0f3460;
            box-shadow: 0 0 0 0.2rem rgba(15, 52, 96, 0.15);
        }

        .input-group:focus-within .input-group-text { border-color: #0f3460; }

        .btn-login {
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

        .btn-login:hover  { opacity: 0.9; color: white; }
        .btn-login:disabled { opacity: 0.7; }

        .btn-toggle-pwd {
            border: 1px solid #dee2e6;
            border-left: none;
            background: #f8f9fa;
            color: #6c757d;
        }

        .forgot-link {
            color: #0f3460;
            font-size: 13px;
            text-decoration: none;
            font-weight: 600;
        }

        .forgot-link:hover { color: #e94560; }

        .divider {
            text-align: center;
            margin: 20px 0;
            color: #adb5bd;
            font-size: 13px;
            position: relative;
        }

        .divider::before, .divider::after {
            content: '';
            position: absolute;
            top: 50%;
            width: 44%;
            height: 1px;
            background: #dee2e6;
        }

        .divider::before { left: 0; }
        .divider::after  { right: 0; }

        .register-link { text-align: center; font-size: 14px; color: #6c757d; }

        .register-link a {
            color: #0f3460;
            font-weight: 700;
            text-decoration: none;
        }

        .register-link a:hover { color: #e94560; text-decoration: underline; }

        .alert { border-radius: 10px; font-size: 14px; }
    </style>
</head>
<body>

<div class="auth-wrapper">
    <div class="auth-card">

        <!-- Logo -->
        <div class="auth-logo">
            <div class="logo-icon">
                <i class="fas fa-chart-line"></i>
            </div>
            <h2>Alumni Dashboard</h2>
            <p>University Analytics Portal</p>
        </div>

        <!-- ── Flash Messages ─────────────────────────── -->

        <?php if ($this->session->flashdata('success')): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle me-2"></i>
                <?php echo $this->session->flashdata('success'); ?>
            </div>
        <?php endif; ?>

        <?php if ($this->session->flashdata('error')): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?php echo $this->session->flashdata('error'); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-times-circle me-2"></i>
                <?php echo htmlspecialchars($error); ?>
                <?php if (isset($show_resend) && $show_resend): ?>
                    <br><br>
                    <a href="<?php echo base_url('auth/resend_verification'); ?>"
                       class="alert-link">
                        <i class="fas fa-envelope me-1"></i>Resend verification email
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($errors)): ?>
            <div class="alert alert-warning">
                <?php echo $errors; ?>
            </div>
        <?php endif; ?>

        <!-- ── Login Form ─────────────────────────────── -->

        <?php echo form_open('auth/login_submit', array('id' => 'loginForm')); ?>

            <!-- Email -->
            <div class="mb-3">
                <label for="email" class="form-label">University Email</label>
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="fas fa-envelope"></i>
                    </span>
                    <input type="email"
                           class="form-control"
                           id="email"
                           name="email"
                           placeholder="w123456789@my.westminster.ac.uk"
                           value="<?php echo isset($unverified_email) 
                               ? htmlspecialchars($unverified_email) 
                               : set_value('email'); ?>"
                           autocomplete="email"
                           autofocus
                           required>
                </div>
            </div>

            <!-- Password -->
            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <label for="password" class="form-label mb-0">Password</label>
                    <a href="<?php echo base_url('auth/forgot_password'); ?>" 
                       class="forgot-link">Forgot password?</a>
                </div>
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="fas fa-lock"></i>
                    </span>
                    <input type="password"
                           class="form-control"
                           id="password"
                           name="password"
                           placeholder="Enter your password"
                           autocomplete="current-password"
                           required>
                    <button class="btn btn-toggle-pwd" type="button"
                            onclick="togglePwd()">
                        <i class="fas fa-eye" id="pwdIcon"></i>
                    </button>
                </div>
            </div>

            <!-- Submit -->
            <div class="mb-3 mt-4">
                <button type="submit" class="btn btn-login" id="loginBtn">
                    <i class="fas fa-sign-in-alt me-2"></i>Sign In
                </button>
            </div>

        <?php echo form_close(); ?>

        <div class="divider">or</div>

        <div class="register-link">
            Don't have an account?
            <a href="<?php echo base_url('auth/register'); ?>">Register here</a>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function togglePwd() {
        var field = document.getElementById('password');
        var icon  = document.getElementById('pwdIcon');

        if (field.type === 'password') {
            field.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            field.type = 'password';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    }

    document.getElementById('loginForm').addEventListener('submit', function() {
        var btn = document.getElementById('loginBtn');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Signing In...';
    });
</script>
</body>
</html>