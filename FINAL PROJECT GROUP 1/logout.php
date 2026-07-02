<?php
require_once __DIR__ . '/includes/functions.php';
start_session();
session_unset();
session_destroy();
header('Location: login.php');
exit;
