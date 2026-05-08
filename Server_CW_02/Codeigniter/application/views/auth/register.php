<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - University Analytics Dashboard</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family:'Segoe UI',sans-serif;
            background:linear-gradient(135deg,#1565C0,#0D47A1);
            min-height:100vh; display:flex;
            align-items:center; justify-content:center; padding:20px;
        }
        .container {
            background:#fff; padding:40px; border-radius:16px;
            box-shadow:0 20px 60px rgba(0,0,0,0.3);
            width:100%; max-width:520px;
        }
        .logo { text-align:center; margin-bottom:24px; }
        .logo h2 { color:#1565C0; font-size:24px; }
        .logo p  { color:#888; font-size:13px; margin-top:4px; }
        .domain-note {
            background:#e3f2fd; padding:10px 14px; border-radius:8px;
            margin-bottom:20px; font-size:13px; color:#1565C0;
        }
        .form-row { display:flex; gap:12px; }
        .form-row .form-group { flex:1; }
        .form-group { margin-bottom:16px; }
        .form-group label {
            display:block; margin-bottom:5px;
            font-weight:600; color:#444; font-size:13px;
        }
        .form-group input {
            width:100%; padding:11px 14px;
            border:2px solid #e0e0e0; border-radius:8px; font-size:14px;
        }
        .form-group input:focus {
            outline:none; border-color:#1565C0;
        }
        .password-hint {
            font-size:11px; color:#888; margin-top:4px;
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
        .links {
            text-align:center; margin-top:16px; font-size:13px;
        }
        .links a { color:#1565C0; text-decoration:none; }
    </style>
</head>
<body>
<div class="container">
    <div class="logo">
        <h2>📊 University Analytics</h2>
        <p>Create your account to access the dashboard</p>
    </div>

    <div class="domain-note">
        📧 Requires a valid university email (e.g., name@eastminster.ac.uk)
    </div>

    <?php if (isset($error)): ?>
        <div class="error-box"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if (validation_errors()): ?>
        <div class="error-box"><?php echo validation_errors(); ?></div>
    <?php endif; ?>

    <?php echo form_open('register/submit'); ?>

        <div class="form-row">
            <div class="form-group">
                <label>First Name</label>
                <input type="text" name="first_name"
                       value="<?php echo set_value('first_name'); ?>"
                       placeholder="John" required>
            </div>
            <div class="form-group">
                <label>Last Name</label>
                <input type="text" name="last_name"
                       value="<?php echo set_value('last_name'); ?>"
                       placeholder="Doe" required>
            </div>
        </div>

        <div class="form-group">
            <label>University Email</label>
            <input type="email" name="email"
                   value="<?php echo set_value('email'); ?>"
                   placeholder="john.doe@eastminster.ac.uk" required>
        </div>

        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password"
                   placeholder="Minimum 8 characters" required>
            <p class="password-hint">
                Must have: uppercase, lowercase, number, special character
            </p>
        </div>

        <div class="form-group">
            <label>Confirm Password</label>
            <input type="password" name="password_confirm"
                   placeholder="Re-enter password" required>
        </div>

        <button type="submit" class="btn">Create Account</button>

    <?php echo form_close(); ?>

    <div class="links">
        Already have an account?
        <a href="<?php echo site_url('login'); ?>">Sign In</a>
    </div>
</div>
</body>
</html>