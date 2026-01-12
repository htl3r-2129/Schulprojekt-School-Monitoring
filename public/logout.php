<?php
session_start();

session_unset();
session_destroy();
// Zurück zum Login
header('Location: login.php');
exit;