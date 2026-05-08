<?php
// C:\xampp\htdocs\Server_CW\Server_CW_02\Codeigniter\test.php
// Visit: http://localhost/Server_CW/Server_CW_02/Codeigniter/test.php

echo '<h2>Tests</h2>';

// Test 1: Can PHP run?
echo '<p style="color:green">✅ PHP is running - Version: ' . phpversion() . '</p>';

// Test 2: Does index.php exist?
if (file_exists('index.php')) {
    echo '<p style="color:green">✅ index.php exists</p>';
} else {
    echo '<p style="color:red">❌ index.php NOT found</p>';
}

// Test 3: Does application folder exist?
if (is_dir('application')) {
    echo '<p style="color:green">✅ application/ folder exists</p>';
} else {
    echo '<p style="color:red">❌ application/ folder NOT found</p>';
}

// Test 4: Does .htaccess exist?
if (file_exists('.htaccess')) {
    echo '<p style="color:green">✅ .htaccess exists</p>';
    echo '<pre>' . htmlspecialchars(file_get_contents('.htaccess')) . '</pre>';
} else {
    echo '<p style="color:red">❌ .htaccess NOT found - this is the problem!</p>';
}

// Test 5: Is mod_rewrite loaded?
if (function_exists('apache_get_modules')) {
    $modules = apache_get_modules();
    if (in_array('mod_rewrite', $modules)) {
        echo '<p style="color:green">✅ mod_rewrite is enabled</p>';
    } else {
        echo '<p style="color:red">❌ mod_rewrite NOT enabled - fix httpd.conf</p>';
    }
} else {
    echo '<p style="color:orange">⚠️ Cannot check mod_rewrite from PHP</p>';
}

// Test 6: Direct link to CI with index.php
echo '<br><p><strong>Try these links:</strong></p>';
echo '<p><a href="index.php/auth/login">→ Login WITH index.php (click this first)</a></p>';
echo '<p><a href="auth/login">→ Login WITHOUT index.php (needs .htaccess)</a></p>';