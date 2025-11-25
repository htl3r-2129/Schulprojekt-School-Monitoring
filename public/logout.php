<?php
session_start();
require __DIR__ . '/../vendor/autoload.php';

use App\classes\Auth;

$auth = new Auth();
$auth->logout();

// Zurück zum Login
header('Location: login.php');
exit;
