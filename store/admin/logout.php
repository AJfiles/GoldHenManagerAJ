<?php
require_once __DIR__ . '/config.php';
store_admin_start_session();
$_SESSION = []; session_destroy(); header('Location: index.php'); exit;
