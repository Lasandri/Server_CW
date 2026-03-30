<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
 * Email Configuration
 * For development, use Mailtrap (https://mailtrap.io)
 * For production, use your university SMTP server
 */

$config['protocol']    = 'smtp';
$config['smtp_host']   = 'smtp.mailtrap.io';
$config['smtp_port']   = 2525;
$config['smtp_user']   = '2a8e57c103b459';   // Change this
$config['smtp_pass']   = 'fd6fe761485982';   // Change this
$config['smtp_crypto'] = 'tls';
$config['mailtype']    = 'html';
$config['charset']     = 'UTF-8';
$config['wordwrap']    = TRUE;
$config['newline']     = "\r\n";


