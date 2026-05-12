<?php

session_start();

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/encryption.php';
require_once __DIR__ . '/core/BaseController.php';
require_once __DIR__ . '/models/User.php';
require_once __DIR__ . '/controllers/AuthController.php';

$controller = new AuthController();
$controller->login();