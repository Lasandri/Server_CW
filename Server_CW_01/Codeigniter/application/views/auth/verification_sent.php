<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Email - Alumni Influencers</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Segoe UI',sans-serif; background:#f0f2f5; min-height:100vh; display:flex; align-items:center; justify-content:center; }
        .container { background:#fff; padding:40px; border-radius:12px; box-shadow:0 4px 20px rgba(0,0,0,0.1); width:100%; max-width:480px; text-align:center; }
        .icon { font-size:64px; margin-bottom:16px; }
        h2 { color:#333; margin-bottom:12px; }
        p { color:#666; margin-bottom:8px; font-size:15px; }
        .email-hl { background:#e3f2fd; padding:8px 16px; border-radius:6px; display:inline-block; margin:12px 0; font-weight:600; color:#1565C0; }
        .note { background:#fff3e0; padding:12px; border-radius:8px; margin-top:16px; font-size:13px; color:#e65100; }
        .links { margin-top:24px; font-size:14px; }
        .links a { color:#2196F3; text-decoration:none; }
    </style>
</head>
<body>
<div class="container">
    <div class="icon">📧</div>
    <h2>Check Your Email</h2>
    <p>We've sent a verification link to:</p>
    <div class="email-hl"><?php echo htmlspecialchars($email); ?></div>
    <p>Click the link in the email to verify your account.</p>
    <div class="note">
        ⏰ The link expires in <strong>24 hours</strong>.<br>
        Check your spam folder if you don't see it.
    </div>
    <div class="links">
        <a href="<?php echo site_url('login'); ?>">← Go to Login</a>
    </div>
</div>
</body>
</html>