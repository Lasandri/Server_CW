<?php
// Codeigniter/application/controllers/Test.php
// Visit: http://localhost/Server_CW/Server_CW_02/Codeigniter/test

defined('BASEPATH') OR exit('No direct script access allowed');

class Test extends CI_Controller {

    public function index() {
        echo '<h1 style="color:green">✅ CodeIgniter Works!</h1>';
        echo '<p>PHP: ' . phpversion() . '</p>';
        echo '<p>Base URL: ' . base_url() . '</p>';
        echo '<hr>';
        echo '<a href="' . base_url('auth/login') . '">Go to Login</a>';
    }
}