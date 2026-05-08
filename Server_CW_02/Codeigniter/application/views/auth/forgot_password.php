<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - University Analytics</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family:'Segoe UI',sans-serif;
            background:linear-gradient(135deg,#1565C0,#0D47A1);
            min-height:100vh; display:flex;
            align-items:center; justify-content:center;
        }
        .container {
            background:#fff; padding:40px; border-radius:16px;
            box-shadow:0 20px 60px rgba(0,0,0,0.3);
            width:100%; max-width:420px;
        }
        h2 { color:#1565C0; margin-bottom:8px; }
        .subtitle { color:#888; font-size:13px; margin-bottom:24px; }
        .form-group { margin-bottom:16px; }
        .form-group label {
            display:block; margin-bottom:6px;
            font-weight:600; color:#444; font-size:13px;
        }
        .form-group input {
            width:100%; padding:12px; font-size:14px;
            border:2px solid #e0e0e0; border-radius:8px;
        }
        .form-group input:focus {
            outline:none; border-color:#1565C0;
        }
        .btn {
            width:100%; padding:13px; background:#1565C0;
            color:#fff; border:none; border-radius:8px;
            font-size:15px; font-weight:600; cursor:pointer;
        }
        .btn:hover { background:#0D47A1; }
        .error-box {
            background:#ffebee; color:#c62828; padding:12px;
            border-radius:8px; margin-bottom:16px; font-size:13px;
            border-left:4px solid #c62828;
        }
        .success-box {
            background:#e8f5e9; color:#2e7d32; padding:12px;
            border-radius:8px; margin-bottom:16px; font-size:13px;
            border-left:4px solid #4CAF50;
        }
        .links {
            text-align:center; margin-top:16px; font-size:13px;
        }
        .links a { color:#1565C0; text-decoration:none; }
    </style>
</head>
<body>
<div class="container">
    <h2>🔑 Forgot Password</h2>
    <p class="subtitle">Enter your email to receive a reset link</p>

    <?php if ($this->session->flashdata('success')): ?>
        <div class="success-box">
            <?php echo htmlspecialchars(
                $this->session->flashdata('success')
            ); ?>
        </div>
    <?php endif; ?>

    <?php if ($this->session->flashdata('error')): ?>
        <div class="error-box">
            <?php echo htmlspecialchars(
                $this->session->flashdata('error')
            ); ?>
        </div>
    <?php endif; ?>

    <?php if (validation_errors()): ?>
        <div class="error-box"><?php echo validation_errors(); ?></div>
    <?php endif; ?>

    <?php echo form_open('forgot-password/submit'); ?>

        <div class="form-group">
            <label>University Email</label>
            <input type="email" name="email"
                   placeholder="your.email@eastminster.ac.uk" required>
        </div>

        <button type="submit" class="btn">Send Reset Link</button>

    <?php echo form_close(); ?>

    <div class="links">
        <a href="<?php echo site_url('login'); ?>">← Back to Login</a>
    </div>
</div>
</body>
</html>