<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Alumni Influencers</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif; background:#f0f2f5; min-height:100vh; display:flex; align-items:center; justify-content:center; }
        .container { background:#fff; padding:40px; border-radius:12px; box-shadow:0 4px 20px rgba(0,0,0,0.1); width:100%; max-width:420px; }
        h2 { text-align:center; color:#333; margin-bottom:24px; }
        .form-group { margin-bottom:16px; }
        .form-group label { display:block; margin-bottom:6px; font-weight:600; color:#444; font-size:14px; }
        .form-group input { width:100%; padding:12px; border:2px solid #e0e0e0; border-radius:8px; font-size:14px; }
        .form-group input:focus { outline:none; border-color:#2196F3; }
        .btn { width:100%; padding:14px; background:#2196F3; color:#fff; border:none; border-radius:8px; font-size:16px; font-weight:600; cursor:pointer; }
        .btn:hover { background:#1976D2; }
        .btn-resend { background:#FF9800; margin-top:8px; }
        .btn-resend:hover { background:#F57C00; }
        .error-box { background:#ffebee; color:#c62828; padding:12px; border-radius:8px; margin-bottom:16px; font-size:14px; border-left:4px solid #c62828; }
        .success-box { background:#e8f5e9; color:#2e7d32; padding:12px; border-radius:8px; margin-bottom:16px; font-size:14px; border-left:4px solid #2e7d32; }
        .links { text-align:center; margin-top:20px; font-size:14px; }
        .links a { color:#2196F3; text-decoration:none; }
        .forgot-link { text-align:right; margin-bottom:16px; }
        .forgot-link a { font-size:13px; color:#888; text-decoration:none; }
    </style>
</head>
<body>
<div class="container">
    <h2>🔐 Alumni Login</h2>

    <!-- Flash messages -->
    <?php if ($this->session->flashdata('success')): ?>
        <div class="success-box"><?php echo htmlspecialchars($this->session->flashdata('success')); ?></div>
    <?php endif; ?>

    <?php if ($this->session->flashdata('error')): ?>
        <div class="error-box"><?php echo htmlspecialchars($this->session->flashdata('error')); ?></div>
    <?php endif; ?>

    <!-- Controller error -->
    <?php if (isset($error)): ?>
        <div class="error-box"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <!-- Resend verification button -->
    <?php if (isset($show_resend) && $show_resend): ?>
        <?php echo form_open('resend-verification'); ?>
            <input type="hidden" name="email" value="<?php echo htmlspecialchars($resend_email); ?>">
            <button type="submit" class="btn btn-resend">📧 Resend Verification Email</button>
        <?php echo form_close(); ?>
        <br>
    <?php endif; ?>

    <!-- Validation errors -->
    <?php if (validation_errors()): ?>
        <div class="error-box"><?php echo validation_errors(); ?></div>
    <?php endif; ?>

    <?php echo form_open('login/submit'); ?>

        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email"
                   value="<?php echo set_value('email'); ?>"
                   required placeholder="your.email@eastminster.ac.uk">
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password"
                   required placeholder="Enter your password">
        </div>

        <div class="forgot-link">
            <a href="<?php echo site_url('forgot-password'); ?>">Forgot your password?</a>
        </div>

        <button type="submit" class="btn">Log In</button>

    <?php echo form_close(); ?>

    <div class="links">
        Don't have an account? <a href="<?php echo site_url('register'); ?>">Register</a>
    </div>
</div>
</body>
</html>