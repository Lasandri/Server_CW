<?php
// Codeigniter/application/views/auth/forgot_password.php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Alumni Dashboard</title>
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
            width: 70px; height: 70px;
            background: linear-gradient(135deg, #fd7e14, #e94560);
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
            border-color: #fd7e14;
            box-shadow: 0 0 0 0.2rem rgba(253, 126, 20, 0.15);
        }
        .btn-forgot {
            background: linear-gradient(135deg, #fd7e14, #e94560);
            border: none; color: white; padding: 12px;
            font-size: 16px; font-weight: 600; border-radius: 8px;
            width: 100%; transition: opacity 0.3s;
        }
        .btn-forgot:hover { opacity: 0.9; color: white; }
        .back-link { text-align: center; margin-top: 15px; }
        .back-link a { color: #6c757d; font-size: 14px; text-decoration: none; }
        .back-link a:hover { color: #0f3460; }
        .alert { border-radius: 10px; font-size: 14px; }
        .success-box {
            text-align: center; padding: 20px;
            background: #d1e7dd; border-radius: 10px;
            margin-bottom: 20px;
        }
        .success-box i { font-size: 40px; color: #198754; margin-bottom: 10px; }
    </style>
</head>
<body>

<div class="auth-wrapper">
    <div class="auth-card">

        <div class="auth-logo">
            <div class="logo-icon">
                <i class="fas fa-key"></i>
            </div>
            <h2>Forgot Password?</h2>
            <p>We'll send a reset link to your email</p>
        </div>

        <?php if (isset($message)): ?>
            <!-- Success state -->
            <div class="success-box">
                <i class="fas fa-envelope-open-text d-block"></i>
                <strong>Check your email!</strong><br>
                <small><?php echo htmlspecialchars($message); ?></small>
            </div>
            <div class="back-link">
                <a href="<?php echo base_url('auth/login'); ?>">
                    <i class="fas fa-arrow-left me-1"></i>Back to Login
                </a>
            </div>

        <?php else: ?>

            <?php if (isset($errors)): ?>
                <div class="alert alert-warning"><?php echo $errors; ?></div>
            <?php endif; ?>

            <?php echo form_open('auth/forgot_password_submit', array('id' => 'forgotForm')); ?>

                <div class="mb-4">
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
                               autocomplete="email"
                               autofocus
                               required>
                    </div>
                </div>

                <button type="submit" class="btn btn-forgot">
                    <i class="fas fa-paper-plane me-2"></i>Send Reset Link
                </button>

            <?php echo form_close(); ?>

            <div class="back-link">
                <a href="<?php echo base_url('auth/login'); ?>">
                    <i class="fas fa-arrow-left me-1"></i>Back to Login
                </a>
            </div>

        <?php endif; ?>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>