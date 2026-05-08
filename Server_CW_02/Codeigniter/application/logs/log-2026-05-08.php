<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

DEBUG - 2026-05-08 00:25:15 --> No URI present. Default controller set.
DEBUG - 2026-05-08 00:25:15 --> Session class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 00:25:15 --> Form_validation class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 00:25:15 --> Email class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 04:08:05 --> No URI present. Default controller set.
DEBUG - 2026-05-08 04:08:05 --> Session class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 04:08:05 --> Form_validation class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 04:08:05 --> Email class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 04:08:25 --> No URI present. Default controller set.
DEBUG - 2026-05-08 04:08:25 --> Session class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 04:08:25 --> Form_validation class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 04:08:25 --> Email class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 04:08:35 --> No URI present. Default controller set.
DEBUG - 2026-05-08 04:08:35 --> Session class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 04:08:35 --> Form_validation class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 04:08:35 --> Email class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 04:10:28 --> Session class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 04:10:28 --> Form_validation class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 04:10:28 --> Email class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 07:11:32 --> No URI present. Default controller set.
DEBUG - 2026-05-08 07:11:32 --> Session class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 07:11:32 --> Form_validation class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 07:11:32 --> Email class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 07:52:30 --> Session class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 07:52:30 --> Form_validation class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 07:52:30 --> Email class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 08:43:45 --> Session class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 08:43:45 --> Form_validation class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 08:43:45 --> Email class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 08:43:49 --> Session class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 08:43:49 --> Form_validation class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 08:43:49 --> Email class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 08:43:50 --> Session class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 08:43:50 --> Form_validation class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 08:43:50 --> Session class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 08:44:05 --> Session class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 08:44:05 --> Form_validation class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 08:44:05 --> Session class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 08:44:07 --> Session class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 08:44:07 --> Form_validation class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 08:44:07 --> Session class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 08:45:49 --> Session class already loaded. Second attempt ignored.
ERROR - 2026-05-08 08:45:54 --> Query error: Duplicate column name 'id' - Invalid query: SELECT COUNT(*) AS `numrows`
FROM (
SELECT *
FROM `users` `u`
LEFT JOIN `alumni_profiles` `ap` ON `ap`.`user_id` = `u`.`id`
LEFT JOIN `degrees` `d` ON `d`.`user_id` = `u`.`id`
LEFT JOIN `employment_history` `eh` ON `eh`.`user_id` = `u`.`id` AND `eh`.`is_current` = 1
WHERE `u`.`is_active` = 1
GROUP BY `u`.`id`
) CI_count_all_results
ERROR - 2026-05-08 08:45:54 --> Severity: error --> Exception: Call to a member function num_rows() on bool C:\xampp\htdocs\Server_CW\Server_CW_02\Codeigniter\system\database\DB_query_builder.php 1502
DEBUG - 2026-05-08 08:46:56 --> Session class already loaded. Second attempt ignored.
ERROR - 2026-05-08 08:46:56 --> Query error: Duplicate column name 'id' - Invalid query: SELECT COUNT(*) AS `numrows`
FROM (
SELECT *
FROM `users` `u`
LEFT JOIN `alumni_profiles` `ap` ON `ap`.`user_id` = `u`.`id`
LEFT JOIN `degrees` `d` ON `d`.`user_id` = `u`.`id`
LEFT JOIN `employment_history` `eh` ON `eh`.`user_id` = `u`.`id` AND `eh`.`is_current` = 1
WHERE `u`.`is_active` = 1
GROUP BY `u`.`id`
) CI_count_all_results
ERROR - 2026-05-08 08:46:56 --> Severity: error --> Exception: Call to a member function num_rows() on bool C:\xampp\htdocs\Server_CW\Server_CW_02\Codeigniter\system\database\DB_query_builder.php 1502
DEBUG - 2026-05-08 08:47:00 --> Session class already loaded. Second attempt ignored.
ERROR - 2026-05-08 08:47:00 --> Query error: Duplicate column name 'id' - Invalid query: SELECT COUNT(*) AS `numrows`
FROM (
SELECT *
FROM `users` `u`
LEFT JOIN `alumni_profiles` `ap` ON `ap`.`user_id` = `u`.`id`
LEFT JOIN `degrees` `d` ON `d`.`user_id` = `u`.`id`
LEFT JOIN `employment_history` `eh` ON `eh`.`user_id` = `u`.`id` AND `eh`.`is_current` = 1
WHERE `u`.`is_active` = 1
GROUP BY `u`.`id`
) CI_count_all_results
ERROR - 2026-05-08 08:47:00 --> Severity: error --> Exception: Call to a member function num_rows() on bool C:\xampp\htdocs\Server_CW\Server_CW_02\Codeigniter\system\database\DB_query_builder.php 1502
DEBUG - 2026-05-08 08:47:01 --> Session class already loaded. Second attempt ignored.
ERROR - 2026-05-08 08:47:01 --> Query error: Duplicate column name 'id' - Invalid query: SELECT COUNT(*) AS `numrows`
FROM (
SELECT *
FROM `users` `u`
LEFT JOIN `alumni_profiles` `ap` ON `ap`.`user_id` = `u`.`id`
LEFT JOIN `degrees` `d` ON `d`.`user_id` = `u`.`id`
LEFT JOIN `employment_history` `eh` ON `eh`.`user_id` = `u`.`id` AND `eh`.`is_current` = 1
WHERE `u`.`is_active` = 1
GROUP BY `u`.`id`
) CI_count_all_results
ERROR - 2026-05-08 08:47:01 --> Severity: error --> Exception: Call to a member function num_rows() on bool C:\xampp\htdocs\Server_CW\Server_CW_02\Codeigniter\system\database\DB_query_builder.php 1502
DEBUG - 2026-05-08 08:47:05 --> Session class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 08:47:05 --> Form_validation class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 08:47:05 --> Session class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 08:47:11 --> Session class already loaded. Second attempt ignored.
ERROR - 2026-05-08 08:47:11 --> Query error: Duplicate column name 'id' - Invalid query: SELECT COUNT(*) AS `numrows`
FROM (
SELECT *
FROM `users` `u`
LEFT JOIN `alumni_profiles` `ap` ON `ap`.`user_id` = `u`.`id`
LEFT JOIN `degrees` `d` ON `d`.`user_id` = `u`.`id`
LEFT JOIN `employment_history` `eh` ON `eh`.`user_id` = `u`.`id` AND `eh`.`is_current` = 1
WHERE `u`.`is_active` = 1
GROUP BY `u`.`id`
) CI_count_all_results
ERROR - 2026-05-08 08:47:11 --> Severity: error --> Exception: Call to a member function num_rows() on bool C:\xampp\htdocs\Server_CW\Server_CW_02\Codeigniter\system\database\DB_query_builder.php 1502
DEBUG - 2026-05-08 08:49:24 --> Session class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 08:49:24 --> Form_validation class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 08:49:24 --> Session class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 08:50:41 --> Session class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 08:50:41 --> Form_validation class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 08:50:41 --> Session class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 08:51:18 --> Session class already loaded. Second attempt ignored.
ERROR - 2026-05-08 08:51:18 --> Query error: Duplicate column name 'id' - Invalid query: SELECT COUNT(*) AS `numrows`
FROM (
SELECT *
FROM `users` `u`
LEFT JOIN `alumni_profiles` `ap` ON `ap`.`user_id` = `u`.`id`
LEFT JOIN `degrees` `d` ON `d`.`user_id` = `u`.`id`
LEFT JOIN `employment_history` `eh` ON `eh`.`user_id` = `u`.`id` AND `eh`.`is_current` = 1
WHERE `u`.`is_active` = 1
GROUP BY `u`.`id`
) CI_count_all_results
ERROR - 2026-05-08 08:51:18 --> Severity: error --> Exception: Call to a member function num_rows() on bool C:\xampp\htdocs\Server_CW\Server_CW_02\Codeigniter\system\database\DB_query_builder.php 1502
DEBUG - 2026-05-08 08:56:55 --> Session class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 08:56:55 --> Form_validation class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 08:56:55 --> Session class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 08:56:58 --> Session class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 08:56:58 --> Form_validation class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 08:56:58 --> Session class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 08:57:00 --> Session class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 08:57:00 --> Form_validation class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 08:57:00 --> Session class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 08:58:36 --> Session class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 08:58:36 --> Form_validation class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 08:58:36 --> Session class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 09:03:17 --> Session class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 09:03:17 --> Form_validation class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 09:03:17 --> Session class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 09:03:20 --> Session class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 09:03:20 --> Form_validation class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 09:03:20 --> Session class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 09:03:21 --> Session class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 09:03:21 --> Form_validation class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 09:03:21 --> Session class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 09:03:29 --> Session class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 09:03:29 --> Form_validation class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 09:03:29 --> Session class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 09:04:26 --> Session class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 09:04:26 --> Form_validation class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 09:04:26 --> Session class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 09:10:56 --> Session class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 09:10:56 --> Form_validation class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 09:10:56 --> Session class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 09:10:57 --> Session class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 09:10:57 --> Form_validation class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 09:10:57 --> Session class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 09:10:57 --> Session class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 09:10:57 --> Form_validation class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 09:10:57 --> Session class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 09:10:57 --> Session class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 09:10:57 --> Form_validation class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 09:10:57 --> Session class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 09:10:58 --> Session class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 09:10:58 --> Form_validation class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 09:10:58 --> Session class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 09:10:58 --> Session class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 09:10:58 --> Form_validation class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 09:10:58 --> Session class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 09:11:01 --> Session class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 09:11:01 --> Form_validation class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 09:11:01 --> Email class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 09:11:01 --> Session class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 09:11:01 --> Form_validation class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 09:11:01 --> Email class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 09:11:04 --> Session class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 09:11:04 --> Form_validation class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 09:11:04 --> Email class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 09:11:09 --> Session class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 09:11:09 --> Form_validation class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 09:11:09 --> Email class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 09:11:09 --> Session class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 09:11:09 --> Form_validation class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 09:11:09 --> Session class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 09:22:00 --> Session class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 09:22:00 --> Form_validation class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 09:22:00 --> Session class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 09:22:10 --> Session class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 09:22:10 --> Form_validation class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 09:22:10 --> Session class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 09:22:12 --> Session class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 09:22:12 --> Form_validation class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 09:22:12 --> Session class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 09:26:08 --> Session class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 09:26:08 --> Form_validation class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 09:26:08 --> Email class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 09:26:09 --> Session class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 09:26:09 --> Form_validation class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 09:26:09 --> Email class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 09:26:13 --> Session class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 09:26:13 --> Form_validation class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 09:26:13 --> Email class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 09:26:13 --> Session class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 09:26:13 --> Form_validation class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 09:26:13 --> Session class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 09:35:15 --> Session class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 09:35:36 --> Session class already loaded. Second attempt ignored.
ERROR - 2026-05-08 09:35:37 --> Severity: error --> Exception: Call to undefined function site_url() C:\xampp\htdocs\Server_CW\Server_CW_02\Codeigniter\application\views\dashboard\header.php 261
DEBUG - 2026-05-08 09:36:02 --> Session class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 09:36:02 --> Form_validation class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 09:36:02 --> Email class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 09:36:02 --> Session class already loaded. Second attempt ignored.
ERROR - 2026-05-08 09:36:02 --> Severity: error --> Exception: Call to undefined function site_url() C:\xampp\htdocs\Server_CW\Server_CW_02\Codeigniter\application\views\dashboard\header.php 261
DEBUG - 2026-05-08 09:36:03 --> Session class already loaded. Second attempt ignored.
ERROR - 2026-05-08 09:36:03 --> Severity: error --> Exception: Call to undefined function site_url() C:\xampp\htdocs\Server_CW\Server_CW_02\Codeigniter\application\views\dashboard\header.php 261
DEBUG - 2026-05-08 09:36:07 --> No URI present. Default controller set.
DEBUG - 2026-05-08 09:36:07 --> Session class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 09:36:07 --> Form_validation class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 09:36:07 --> Email class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 09:36:07 --> Session class already loaded. Second attempt ignored.
ERROR - 2026-05-08 09:36:08 --> Severity: error --> Exception: Call to undefined function site_url() C:\xampp\htdocs\Server_CW\Server_CW_02\Codeigniter\application\views\dashboard\header.php 261
DEBUG - 2026-05-08 09:36:15 --> No URI present. Default controller set.
DEBUG - 2026-05-08 09:36:16 --> Session class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 09:36:16 --> Form_validation class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 09:36:16 --> Email class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 09:36:16 --> Session class already loaded. Second attempt ignored.
ERROR - 2026-05-08 09:36:16 --> Severity: error --> Exception: Call to undefined function site_url() C:\xampp\htdocs\Server_CW\Server_CW_02\Codeigniter\application\views\dashboard\header.php 261
DEBUG - 2026-05-08 09:36:42 --> No URI present. Default controller set.
DEBUG - 2026-05-08 09:36:42 --> Session class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 09:36:42 --> Form_validation class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 09:36:42 --> Email class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 09:36:43 --> Session class already loaded. Second attempt ignored.
ERROR - 2026-05-08 09:36:43 --> Severity: error --> Exception: Call to undefined function site_url() C:\xampp\htdocs\Server_CW\Server_CW_02\Codeigniter\application\views\dashboard\header.php 261
DEBUG - 2026-05-08 09:38:45 --> Session class already loaded. Second attempt ignored.
ERROR - 2026-05-08 09:38:45 --> Query error: Duplicate column name 'id' - Invalid query: SELECT COUNT(*) AS `numrows`
FROM (
SELECT *
FROM `users` `u`
LEFT JOIN `alumni_profiles` `ap` ON `ap`.`user_id` = `u`.`id`
LEFT JOIN `degrees` `d` ON `d`.`user_id` = `u`.`id`
LEFT JOIN `employment_history` `eh` ON `eh`.`user_id` = `u`.`id` AND `eh`.`is_current` = 1
WHERE `u`.`is_active` = 1
GROUP BY `u`.`id`
) CI_count_all_results
ERROR - 2026-05-08 09:38:45 --> Severity: error --> Exception: Call to a member function num_rows() on bool C:\xampp\htdocs\Server_CW\Server_CW_02\Codeigniter\system\database\DB_query_builder.php 1502
DEBUG - 2026-05-08 09:48:13 --> No URI present. Default controller set.
DEBUG - 2026-05-08 09:48:13 --> Session class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 09:48:13 --> Form_validation class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 09:48:13 --> Email class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 09:48:14 --> Session class already loaded. Second attempt ignored.
ERROR - 2026-05-08 09:48:14 --> Severity: error --> Exception: Call to undefined function site_url() C:\xampp\htdocs\Server_CW\Server_CW_02\Codeigniter\application\views\dashboard\header.php 261
DEBUG - 2026-05-08 09:48:15 --> No URI present. Default controller set.
DEBUG - 2026-05-08 09:48:15 --> Session class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 09:48:15 --> Form_validation class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 09:48:15 --> Email class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 09:48:15 --> Session class already loaded. Second attempt ignored.
ERROR - 2026-05-08 09:48:15 --> Severity: error --> Exception: Call to undefined function site_url() C:\xampp\htdocs\Server_CW\Server_CW_02\Codeigniter\application\views\dashboard\header.php 261
DEBUG - 2026-05-08 09:49:29 --> Session class already loaded. Second attempt ignored.
ERROR - 2026-05-08 09:49:30 --> Severity: error --> Exception: Call to undefined function site_url() C:\xampp\htdocs\Server_CW\Server_CW_02\Codeigniter\application\views\dashboard\header.php 261
DEBUG - 2026-05-08 09:50:06 --> Session class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 09:50:06 --> Form_validation class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 09:50:06 --> Email class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 09:50:06 --> Session class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 09:50:06 --> Form_validation class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 09:50:06 --> Email class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 09:50:10 --> Session class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 09:50:10 --> Form_validation class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 09:50:10 --> Email class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 09:50:13 --> Session class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 09:50:13 --> Form_validation class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 09:50:13 --> Email class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 09:50:17 --> Session class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 09:50:17 --> Form_validation class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 09:50:17 --> Email class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 09:50:18 --> Session class already loaded. Second attempt ignored.
ERROR - 2026-05-08 09:50:18 --> Severity: error --> Exception: Call to undefined function site_url() C:\xampp\htdocs\Server_CW\Server_CW_02\Codeigniter\application\views\dashboard\header.php 261
DEBUG - 2026-05-08 09:53:07 --> Session class already loaded. Second attempt ignored.
ERROR - 2026-05-08 09:53:07 --> Severity: error --> Exception: Call to undefined function site_url() C:\xampp\htdocs\Server_CW\Server_CW_02\Codeigniter\application\views\dashboard\header.php 261
DEBUG - 2026-05-08 09:53:09 --> Session class already loaded. Second attempt ignored.
ERROR - 2026-05-08 09:53:09 --> Severity: error --> Exception: Call to undefined function site_url() C:\xampp\htdocs\Server_CW\Server_CW_02\Codeigniter\application\views\dashboard\header.php 261
DEBUG - 2026-05-08 09:53:09 --> Session class already loaded. Second attempt ignored.
ERROR - 2026-05-08 09:53:09 --> Severity: error --> Exception: Call to undefined function site_url() C:\xampp\htdocs\Server_CW\Server_CW_02\Codeigniter\application\views\dashboard\header.php 261
DEBUG - 2026-05-08 09:53:10 --> Session class already loaded. Second attempt ignored.
ERROR - 2026-05-08 09:53:10 --> Severity: error --> Exception: Call to undefined function site_url() C:\xampp\htdocs\Server_CW\Server_CW_02\Codeigniter\application\views\dashboard\header.php 261
DEBUG - 2026-05-08 09:53:10 --> Session class already loaded. Second attempt ignored.
ERROR - 2026-05-08 09:53:10 --> Severity: error --> Exception: Call to undefined function site_url() C:\xampp\htdocs\Server_CW\Server_CW_02\Codeigniter\application\views\dashboard\header.php 261
DEBUG - 2026-05-08 09:53:10 --> Session class already loaded. Second attempt ignored.
ERROR - 2026-05-08 09:53:10 --> Severity: error --> Exception: Call to undefined function site_url() C:\xampp\htdocs\Server_CW\Server_CW_02\Codeigniter\application\views\dashboard\header.php 261
DEBUG - 2026-05-08 09:53:53 --> Session class already loaded. Second attempt ignored.
ERROR - 2026-05-08 09:53:54 --> Severity: error --> Exception: Call to undefined function site_url() C:\xampp\htdocs\Server_CW\Server_CW_02\Codeigniter\application\views\dashboard\index.php 72
DEBUG - 2026-05-08 09:55:42 --> Session class already loaded. Second attempt ignored.
ERROR - 2026-05-08 09:55:42 --> Severity: error --> Exception: Call to undefined function site_url() C:\xampp\htdocs\Server_CW\Server_CW_02\Codeigniter\application\views\dashboard\header.php 261
DEBUG - 2026-05-08 09:59:47 --> Session class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 10:00:13 --> Session class already loaded. Second attempt ignored.
ERROR - 2026-05-08 10:00:13 --> Severity: error --> Exception: Call to undefined function site_url() C:\xampp\htdocs\Server_CW\Server_CW_02\Codeigniter\application\views\dashboard\header.php 261
DEBUG - 2026-05-08 10:01:07 --> Session class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 10:01:49 --> Session class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 10:02:29 --> Session class already loaded. Second attempt ignored.
ERROR - 2026-05-08 10:02:29 --> Severity: error --> Exception: Call to undefined function site_url() C:\xampp\htdocs\Server_CW\Server_CW_02\Codeigniter\application\views\dashboard\header.php 261
DEBUG - 2026-05-08 10:02:55 --> Session class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 10:03:08 --> Session class already loaded. Second attempt ignored.
DEBUG - 2026-05-08 10:04:00 --> Session class already loaded. Second attempt ignored.
ERROR - 2026-05-08 10:04:01 --> Severity: error --> Exception: Call to undefined function site_url() C:\xampp\htdocs\Server_CW\Server_CW_02\Codeigniter\application\views\alumni\index.php 8
DEBUG - 2026-05-08 10:10:11 --> Session class already loaded. Second attempt ignored.
ERROR - 2026-05-08 10:10:36 --> Severity: error --> Exception: Call to undefined function site_url() C:\xampp\htdocs\Server_CW\Server_CW_02\Codeigniter\application\views\dashboard\header.php 261
DEBUG - 2026-05-08 10:12:46 --> Session class already loaded. Second attempt ignored.
ERROR - 2026-05-08 10:12:46 --> Severity: error --> Exception: Call to undefined function site_url() C:\xampp\htdocs\Server_CW\Server_CW_02\Codeigniter\application\views\dashboard\header.php 261
DEBUG - 2026-05-08 10:12:47 --> Session class already loaded. Second attempt ignored.
ERROR - 2026-05-08 10:12:47 --> Severity: error --> Exception: Call to undefined function site_url() C:\xampp\htdocs\Server_CW\Server_CW_02\Codeigniter\application\views\dashboard\header.php 261
DEBUG - 2026-05-08 10:12:47 --> Session class already loaded. Second attempt ignored.
ERROR - 2026-05-08 10:12:48 --> Severity: error --> Exception: Call to undefined function site_url() C:\xampp\htdocs\Server_CW\Server_CW_02\Codeigniter\application\views\dashboard\header.php 261
DEBUG - 2026-05-08 10:16:01 --> Session class already loaded. Second attempt ignored.
ERROR - 2026-05-08 10:16:01 --> Severity: error --> Exception: Call to undefined function site_url() C:\xampp\htdocs\Server_CW\Server_CW_02\Codeigniter\application\views\dashboard\header.php 261
DEBUG - 2026-05-08 10:30:32 --> Session class already loaded. Second attempt ignored.
ERROR - 2026-05-08 10:30:59 --> Severity: error --> Exception: Call to undefined function site_url() C:\xampp\htdocs\Server_CW\Server_CW_02\Codeigniter\application\views\dashboard\header.php 261
