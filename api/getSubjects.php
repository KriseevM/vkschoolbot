<?php
include 'API.php';
$key = $_SERVER['HTTP_KEY'];
$ip = $_SERVER['REMOTE_ADDR'];
$api = new API($key, $ip);
$result = $api->get_subjects_method();
echo json_encode($result, JSON_UNESCAPED_UNICODE);
