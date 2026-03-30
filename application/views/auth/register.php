<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Alumni Influencers</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif; background:#f0f2f5; min-height:100vh; display:flex; align-items:center; justify-content:center; }
        .container { background:#fff; padding:40px; border-radius:12px; box-shadow:0 4px 20px rgba(0,0,0,0.1); width:100%; max-width:480px; }
        h2 { text-align:center; color:#333; margin-bottom:8px; }
        .subtitle { text-align:center; color:#666; margin-bottom:24px; font-size:14px; }
        .form-group { margin-bottom:16px; }
        .form-group label { display:block; margin-bottom:6px; font-weight:600; color:#444; font-size:14px; }
        .form-group input { width:100%; padding:12px; border:2px solid #e0e0e0; border-radius:8px; font-size:14px; }
        .form-group input:focus { outline:none; border-color:#4CAF50; }
        .form-row { display:flex; gap:12px; }
        .form-row .form-group { flex:1; }
        .btn { width:100%; padding:14px; background:#4CAF50; color:#fff; border:none; border-radius:8px; font-size:16px; font-weight:600; cursor:pointer; }
        .btn:hover { background:#43A047; }
        .error-box { background:#ffebee; color:#c62828; padding:12px; border-radius:8px; margin-bottom:16px; font-size:14px; border-left:4px solid #c62828; }
        .links { text-align:center; margin-top:20px; font-size:14px; }
        .links a { color:#4CAF50; text-decoration:none; }
        .password-req { font-size:12px; color:#888; margin-top:4px; }
        .domain-note { background:#e3f2fd; padding:10px; border-radius:8px; margin-bottom:16px; font-size:13px; color:#1565C0; }
    </style>
</head>
<body>
<div class="container">
    <h2>🎓 Alumni Registration</h2>
    <p class="subtitle">University of Eastminster Alumni Influencers Platform</p>

    <div class="domain-note">
        📧 Registration requires a valid university email (e.g., name@eastminster.ac.uk)
    </div>

    <!-- Custom error message -->
    <?php if (isset($error)): ?>
        <div class="error-box"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <!-- CI3 form validation errors -->
    <?php if (validation_errors()): ?>
        <div class="error-box"><?php echo validation_errors(); ?></div>
    <?php endif; ?>

    <?php echo form_open('register/submit'); ?>

        <div class="form-row">
            <div class="form-group">
                <label for="first_name">First Name</label>
                <input type="text" id="first_name" name="first_name"
                       value="<?php echo set_value('first_name'); ?>"
                       required placeholder="John">
            </div>
            <div class="form-group">
                <label for="last_name">Last Name</label>
                <input type="text" id="last_name" name="last_name"
                       value="<?php echo set_value('last_name'); ?>"
                       required placeholder="Doe">
            </div>
        </div>

        <div class="form-group">
            <label for="email">University Email</label>
            <input type="email" id="email" name="email"
                   value="<?php echo set_value('email'); ?>"
                   required placeholder="john.doe@eastminster.ac.uk">
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password"
                   required placeholder="Minimum 8 characters">
            <p class="password-req">Must contain: uppercase, lowercase, number, and special character</p>
        </div>

        <div class="form-group">
            <label for="password_confirm">Confirm Password</label>
            <input type="password" id="password_confirm" name="password_confirm"
                   required placeholder="Re-enter your password">
        </div>

        <button type="submit" class="btn">Create Account</button>

    <?php echo form_close(); ?>

    <div class="links">
        Already have an account? <a href="<?php echo site_url('login'); ?>">Log In</a>
    </div>
</div>
</body>
</html>