<?php
require_once 'includes/session.php';

$session = new SessionManager();
// Perform logout and redirect to home
$session->logout();
header('Location: index.php');
exit();