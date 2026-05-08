<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Email - University Analytics</title>
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
            width:100%; max-width:460px; text-align:center;
        }
        .icon    { font-size:64px; margin-bottom:16px; }
        h2       { color:#1565C0; margin-bottom:12px; }
        p        { color:#666; font-size:14px; line-height:1.6; }
        .email-highlight {
            background:#e3f2fd; color:#1565C0; padding:8px 16px;
            border-radius:8px; display:inline-block;
            margin:12px 0; font-weight:600;
        }
        .note {
            background:#fff3e0; padding:12px 16px;
            border-radius:8px; margin-top:20px;
            font-size:13px; color:#e65100;
        }
        .links { margin-top:24px; }
        .links a { color:#1565C0; text-decoration:none; font-size:14px; }
    </style>
</head>
<body>
<div class="container">
    <div class="icon">📧</div>
    <h2>Check Your Email</h2>
    <p>We've sent a verification link to:</p>
    <div class="email-highlight">
        <?php echo htmlspecialchars($email); ?>
    </div>
    <p>Click the link in the email to verify your account.</p>
    <div class="note">
        ⏰ Link expires in <strong>24 hours</strong>.<br>
        Check your spam folder if you don't see it.
    </div>
    <div class="links">
        <a href="<?php echo site_url('login'); ?>">← Go to Login</a>
    </div>
</div>
</body>
</html>