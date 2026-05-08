<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Alumni Influencers</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Segoe UI',sans-serif; background:#f0f2f5; min-height:100vh; display:flex; align-items:center; justify-content:center; }
        .container { background:#fff; padding:40px; border-radius:12px; box-shadow:0 4px 20px rgba(0,0,0,0.1); width:100%; max-width:420px; }
        h2 { text-align:center; color:#333; margin-bottom:24px; }
        .form-group { margin-bottom:16px; }
        .form-group label { display:block; margin-bottom:6px; font-weight:600; color:#444; font-size:14px; }
        .form-group input { width:100%; padding:12px; border:2px solid #e0e0e0; border-radius:8px; font-size:14px; }
        .btn { width:100%; padding:14px; background:#2196F3; color:#fff; border:none; border-radius:8px; font-size:16px; font-weight:600; cursor:pointer; }
        .btn:hover { background:#1976D2; }
        .error-box { background:#ffebee; color:#c62828; padding:12px; border-radius:8px; margin-bottom:16px; border-left:4px solid #c62828; }
        .password-req { font-size:12px; color:#888; margin-top:4px; }
    </style>
</head>
<body>
<div class="container">
    <h2>🔒 Set New Password</h2>

    <?php if (isset($error)): ?>
        <div class="error-box"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if (validation_errors()): ?>
        <div class="error-box"><?php echo validation_errors(); ?></div>
    <?php endif; ?>

    <?php echo form_open('reset-password/submit'); ?>

        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

        <div class="form-group">
            <label for="password">New Password</label>
            <input type="password" id="password" name="password"
                   required placeholder="Minimum 8 characters">
            <p class="password-req">Must contain: uppercase, lowercase, number, and special character</p>
        </div>

        <div class="form-group">
            <label for="password_confirm">Confirm New Password</label>
            <input type="password" id="password_confirm" name="password_confirm"
                   required placeholder="Re-enter your new password">
        </div>

        <button type="submit" class="btn">Reset Password</button>

    <?php echo form_close(); ?>
</div>
</body>
</html>