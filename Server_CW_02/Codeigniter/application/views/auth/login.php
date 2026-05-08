<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - University Analytics Dashboard</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family:'Segoe UI',sans-serif;
            background:linear-gradient(135deg,#1565C0 0%,#0D47A1 100%);
            min-height:100vh; display:flex;
            align-items:center; justify-content:center;
        }
        .login-wrapper {
            display:flex; width:100%; max-width:900px;
            box-shadow:0 20px 60px rgba(0,0,0,0.3);
            border-radius:16px; overflow:hidden;
        }
        .login-left {
            flex:1; background:rgba(255,255,255,0.1);
            padding:60px 40px; color:#fff;
            display:flex; flex-direction:column; justify-content:center;
        }
        .login-left h1 { font-size:32px; font-weight:700; margin-bottom:12px; }
        .login-left p  { opacity:0.8; font-size:15px; line-height:1.6; }
        .features      { margin-top:32px; }
        .feature-item  {
            display:flex; align-items:center; gap:12px;
            margin-bottom:16px; font-size:14px;
        }
        .feature-item span:first-child { font-size:24px; }
        .login-right {
            width:400px; background:#fff;
            padding:50px 40px; display:flex;
            flex-direction:column; justify-content:center;
        }
        h2 { color:#1565C0; margin-bottom:8px; font-size:24px; }
        .subtitle { color:#888; font-size:14px; margin-bottom:28px; }
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
            width:100%; padding:14px; background:#1565C0;
            color:#fff; border:none; border-radius:8px;
            font-size:15px; font-weight:600; cursor:pointer;
        }
        .btn:hover { background:#0D47A1; }
        .btn-resend {
            background:#FF9800; margin-top:8px;
        }
        .btn-resend:hover { background:#F57C00; }
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
            text-align:center; margin-top:20px; font-size:13px;
        }
        .links a { color:#1565C0; text-decoration:none; }
        .forgot-link {
            text-align:right; margin-bottom:16px;
        }
        .forgot-link a {
            font-size:12px; color:#888; text-decoration:none;
        }
        @media(max-width:700px) {
            .login-left { display:none; }
            .login-right { width:100%; }
        }
    </style>
</head>
<body>
<div class="login-wrapper">

    <!-- Left Panel -->
    <div class="login-left">
        <h1>📊 University Analytics</h1>
        <p>Real-time intelligence dashboard for alumni outcomes and curriculum development.</p>

        <div class="features">
            <div class="feature-item">
                <span>🎯</span>
                <span>Skills Gap Detection</span>
            </div>
            <div class="feature-item">
                <span>💼</span>
                <span>Employment Analytics</span>
            </div>
            <div class="feature-item">
                <span>🌍</span>
                <span>Geographic Distribution</span>
            </div>
            <div class="feature-item">
                <span>📈</span>
                <span>Certification Trends</span>
            </div>
            <div class="feature-item">
                <span>🏢</span>
                <span>Top Employers</span>
            </div>
        </div>
    </div>

    <!-- Right Panel - Login Form -->
    <div class="login-right">
        <h2>Welcome Back</h2>
        <p class="subtitle">Sign in to access the analytics dashboard</p>

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

        <?php if (isset($error)): ?>
            <div class="error-box">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($show_resend) && $show_resend): ?>
            <?php echo form_open('resend-verification'); ?>
                <input type="hidden" name="email"
                       value="<?php echo htmlspecialchars($resend_email); ?>">
                <button type="submit" class="btn btn-resend">
                    📧 Resend Verification Email
                </button>
            <?php echo form_close(); ?>
            <br>
        <?php endif; ?>

        <?php if (validation_errors()): ?>
            <div class="error-box"><?php echo validation_errors(); ?></div>
        <?php endif; ?>

        <?php echo form_open('login/submit'); ?>

            <div class="form-group">
                <label>University Email</label>
                <input type="email" name="email"
                       value="<?php echo set_value('email'); ?>"
                       placeholder="your.email@eastminster.ac.uk"
                       required>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password"
                       placeholder="Enter your password" required>
            </div>

            <div class="forgot-link">
                <a href="<?php echo site_url('forgot-password'); ?>">
                    Forgot password?
                </a>
            </div>

            <button type="submit" class="btn">Sign In</button>

        <?php echo form_close(); ?>

        <div class="links">
            Don't have an account?
            <a href="<?php echo site_url('register'); ?>">Register</a>
        </div>
    </div>
</div>
</body>
</html>