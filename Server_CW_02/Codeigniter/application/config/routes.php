<?php
// application/config/routes.php

defined('BASEPATH') OR exit('No direct script access allowed');

$route['default_controller']   = 'Auth';
$route['404_override']         = '';
$route['translate_uri_dashes'] = FALSE;

$route['auth']                            = 'Auth/login';
$route['auth/login']                      = 'Auth/login';
$route['auth/login_submit']               = 'Auth/login_submit';
$route['auth/register']                   = 'Auth/register';
$route['auth/register_submit']            = 'Auth/register_submit';
$route['auth/logout']                     = 'Auth/logout';
$route['auth/verify_email/(:any)']        = 'Auth/verify_email/$1';
$route['auth/resend_verification']        = 'Auth/resend_verification';
$route['auth/resend_verification_submit'] = 'Auth/resend_verification_submit';
$route['auth/forgot_password']            = 'Auth/forgot_password';
$route['auth/forgot_password_submit']     = 'Auth/forgot_password_submit';
$route['auth/reset_password/(:any)']      = 'Auth/reset_password/$1';
$route['auth/reset_password_submit']      = 'Auth/reset_password_submit';
$route['dashboard']                       = 'Dashboard/index';