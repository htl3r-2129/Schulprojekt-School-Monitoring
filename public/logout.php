<?php
session_start();
require __DIR__ . '/../vendor/autoload.php';

use Insi\Ssm\Auth;

// Instanziierung ohne Argument
$auth = new Auth(); 

$auth->logout();

// Zurück zum Login
header('Location: login.php');
exit;