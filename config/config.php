<?php
// StayNest project configuration

define('APP_NAME', 'StayNest');
define('APP_ROOT', dirname(__DIR__));
define('URL_ROOT', 'http://localhost/staynest_mvc_project/public');
define('PUBLIC_PATH', APP_ROOT . '/public');
define('UPLOAD_PATH', PUBLIC_PATH . '/uploads');

// Database settings for XAMPP localhost
define('DB_HOST', 'localhost');
define('DB_NAME', 'staynest_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Development mode
define('APP_DEBUG', true);
