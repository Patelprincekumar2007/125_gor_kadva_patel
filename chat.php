<?php
require_once 'config/db.php';
$queryString = $_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : '';
header('Location: ' . SITE_URL . '/messages.php' . $queryString);
exit();
