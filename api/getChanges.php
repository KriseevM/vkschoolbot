<?php
require_once "ChangesData.php";
$ip = $_SERVER['REMOTE_ADDR'];
$key = $_GET['key'];
include 'checkAuth.php';
$data = new ChangesData();
echo json_encode($data, JSON_UNESCAPED_UNICODE);
?>
