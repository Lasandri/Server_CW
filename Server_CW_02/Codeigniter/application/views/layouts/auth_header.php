<?php
// Codeigniter/application/views/layouts/auth_header.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'Alumni Dashboard'; ?></title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        /* ── Authentication Page Styling ── */
        body {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .auth-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4);
            width: 100%;
            max-width: 480px;
            padding: 40px;
        }

        .auth-logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .auth-logo h2 {
            color: #0f3460;
            font-weight: 700;
        }

        .auth-logo p {
            color: #6c757d;
            font-size: 14px;
        }

        .form-control:focus {
            border-color: #0f3460;
            box-shadow: 0 0 0 0.2rem rgba(15, 52, 96, 0.25);
        }

        .btn-primary {
            background: #0f3460;
            border: none;
            padding: 12px;
            font-size: 16px;
            font-weight: 600;
        }

        .btn-primary:hover {
            background: #e94560;
        }

        .input-group-text {
            background: #f8f9fa;
            border-right: none;
        }

        .form-control {
            border-left: none;
        }

        /* Password strength indicator */
        .password-strength {
            height: 4px;
            border-radius: 2px;
            margin-top: 5px;
            transition: all 0.3s;
        }

        .strength-weak   { background: #dc3545; width: 25%; }
        .strength-fair   { background: #ffc107; width: 50%; }
        .strength-good   { background: #198754; width: 75%; }
        .strength-strong { background: #0d6efd; width: 100%; }

        .field-error { color: #dc3545; font-size: 12px; }
    </style>
</head>
<body>
<div class="container">