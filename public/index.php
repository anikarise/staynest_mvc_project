<?php
session_start();
require_once '../config/config.php';

spl_autoload_register(function ($className) {
    $paths = [
        APP_ROOT . '/app/core/' . $className . '.php',
        APP_ROOT . '/app/controllers/' . $className . '.php',
        APP_ROOT . '/app/models/' . $className . '.php',
    ];

    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

if (APP_DEBUG) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

$router = new Router();
$router->dispatch();
