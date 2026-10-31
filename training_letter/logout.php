<?php
// This file is part of Moodle - https://moodle.org/
//
// Logout script that terminates session and redirects directly to custom login page.

if (file_exists(__DIR__ . '/../../config.php')) {
    require_once(__DIR__ . '/../../config.php');
} else if (!empty($_SERVER['DOCUMENT_ROOT']) && file_exists($_SERVER['DOCUMENT_ROOT'] . '/config.php')) {
    require_once($_SERVER['DOCUMENT_ROOT'] . '/config.php');
} else {
    require_once('/var/www/moodle/public/config.php');
}

require_logout();

redirect(new moodle_url('/local/training_letter/login.php'));
