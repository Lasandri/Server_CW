<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/userguide3/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes with
| underscores in the controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
$route['default_controller'] = 'auth/showLogin';

// Registration
$route['register']              = 'auth/showRegister';        // GET
$route['register/submit']       = 'auth/register';            // POST

// Email Verification
$route['verify-email']          = 'auth/verifyEmail';         // GET with ?token=
$route['resend-verification']   = 'auth/resendVerification';  // POST

// Login / Logout
$route['login']                 = 'auth/showLogin';           // GET
$route['login/submit']          = 'auth/login';               // POST
$route['logout']                = 'auth/logout';              // GET

// Password Reset
$route['forgot-password']       = 'auth/showForgotPassword';  // GET
$route['forgot-password/submit']= 'auth/forgotPassword';      // POST
$route['reset-password']        = 'auth/showResetPassword';   // GET with ?token=
$route['reset-password/submit'] = 'auth/resetPassword';       // POST

// Protected pages
$route['dashboard']             = 'dashboard/index';





// ================================================================
// Profile Routes (all require login - enforced in controller)
// ================================================================

// Profile dashboard
$route['profile']                       = 'profile/index';
// Personal info
$route['profile/personal']              = 'profile/personal';
$route['profile/personal/save']         = 'profile/personal_save';

// Profile image
$route['profile/image']                 = 'profile/image';
$route['profile/image/upload']          = 'profile/image_upload';

// Degrees
$route['profile/degrees']               = 'profile/degrees';
$route['profile/degree/add']            = 'profile/degree_add';
$route['profile/degree/edit/(:num)']    = 'profile/degree_edit/$1';
$route['profile/degree/save']           = 'profile/degree_save';
$route['profile/degree/delete/(:num)']  = 'profile/degree_delete/$1';

// Certifications
$route['profile/certifications']               = 'profile/certifications';
$route['profile/certification/add']            = 'profile/certification_add';
$route['profile/certification/edit/(:num)']    = 'profile/certification_edit/$1';
$route['profile/certification/save']           = 'profile/certification_save';
$route['profile/certification/delete/(:num)']  = 'profile/certification_delete/$1';

// Licences
$route['profile/licences']               = 'profile/licences';
$route['profile/licence/add']            = 'profile/licence_add';
$route['profile/licence/edit/(:num)']    = 'profile/licence_edit/$1';
$route['profile/licence/save']           = 'profile/licence_save';
$route['profile/licence/delete/(:num)']  = 'profile/licence_delete/$1';

// Professional Courses
$route['profile/courses']               = 'profile/courses';
$route['profile/course/add']            = 'profile/course_add';
$route['profile/course/edit/(:num)']    = 'profile/course_edit/$1';
$route['profile/course/save']           = 'profile/course_save';
$route['profile/course/delete/(:num)']  = 'profile/course_delete/$1';

// Employment
$route['profile/employment']               = 'profile/employment';
$route['profile/employment/add']           = 'profile/employment_add';
$route['profile/employment/edit/(:num)']   = 'profile/employment_edit/$1';
$route['profile/employment/save']          = 'profile/employment_save';
$route['profile/employment/delete/(:num)'] = 'profile/employment_delete/$1';

// ================================================================
// Bidding Routes — use underscores in route URLs
// ================================================================
$route['bidding']                      = 'bidding/index';
$route['bidding/place']                = 'bidding/place';
$route['bidding/update']               = 'bidding/update';
$route['bidding/cancel']               = 'bidding/cancel';
$route['bidding/history']              = 'bidding/history';
$route['bidding/notifications']        = 'bidding/notifications';
$route['bidding/select_winner']        = 'bidding/select_winner';
$route['bidding/reset_winner']         = 'bidding/reset_winner';
$route['bidding/alumni_of_the_day']    = 'bidding/alumni_of_the_day';

// Also keep hyphenated versions so both work
$route['bidding/select-winner']        = 'bidding/select_winner';
$route['bidding/reset-winner']         = 'bidding/reset_winner';
$route['bidding/alumni-of-the-day']    = 'bidding/alumni_of_the_day';


// ================================================================
// Developer / API Key Routes
// ================================================================
$route['developer']                    = 'developer/index';
$route['developer/create']             = 'developer/create';
$route['developer/create_save']        = 'developer/create_save';
$route['developer/stats/(:num)']       = 'developer/stats/$1';
$route['developer/revoke']             = 'developer/revoke';
$route['developer/revoke_token']       = 'developer/revoke_token';
$route['developer/reactivate/(:num)']  = 'developer/reactivate/$1';
$route['developer/delete/(:num)']      = 'developer/delete_key/$1';


// ================================================================
// ALUMNI ROUTES
// ================================================================
$route['alumni']                 = 'alumni/index';
$route['alumni/view/(:num)']     = 'alumni/view/$1';
$route['alumni/export']          = 'alumni/export';
$route['alumni/export-pdf']      = 'alumni/exportPdf';

// ================================================================
// ANALYTICS ROUTES
// ================================================================
$route['analytics']                     = 'analytics/index';
$route['analytics/skills-gap']          = 'analytics/skillsGap';
$route['analytics/employment']          = 'analytics/employment';
$route['analytics/job-titles']          = 'analytics/jobTitles';
$route['analytics/employers']           = 'analytics/employers';
$route['analytics/geographic']          = 'analytics/geographic';
$route['analytics/trends']              = 'analytics/trends';
$route['analytics/export/(:any)']       = 'analytics/export/$1';

$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;
