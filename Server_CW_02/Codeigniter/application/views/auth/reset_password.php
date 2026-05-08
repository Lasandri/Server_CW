<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - University Analytics</title>
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
        .password-hint { font-size:11px; color:#888; margin-top:4px; }
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
    </style>
</head>
<body>
<div class="container">
    <h2>🔐 Reset Password</h2>
    <p class="subtitle">Enter your new password below</p>

    <?php if (isset($error)): ?>
        <div class="error-box"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if (validation_errors()): ?>
        <div class="error-box"><?php echo validation_errors(); ?></div>
    <?php endif; ?>

    <?php echo form_open('reset-password/submit'); ?>

        <input type="hidden" name="token"
               value="<?php echo htmlspecialchars($token); ?>">

        <div class="form-group">
            <label>New Password</label>
            <input type="password" name="password"
                   placeholder="Minimum 8 characters" required>
            <p class="password-hint">
                Must have: uppercase, lowercase, number, special character
            </p>
        </div>

        <div class="form-group">
            <label>Confirm New Password</label>
            <input type="password" name="password_confirm"
                   placeholder="Re-enter new password" required>
        </div>

        <button type="submit" class="btn">Reset Password</button>

    <?php echo form_close(); ?>
</div>
</body>
</html>