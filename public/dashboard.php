<?php
session_start();

require __DIR__ . '/../vendor/autoload.php';

use Insi\Ssm\Auth;

$auth = new Auth();

if (!isset($_SESSION['user'])) {
    header(header: 'Location: login.php');
} elseif ($auth->isAdmin($_SESSION['user'])) {
    header(header: 'Location: admin.php');
} elseif ($auth->isModerator($_SESSION['user'])) {
    header(header: 'Location: mod.php');
} else {
    header(header: 'Location: uploadcontent.php');
}
?>