<?php
require_once "ChangesData.php";
if(!isset($_GET['key'])) 
{
    die('{"error":"Key is required for authorisation","errorcode":6}');
}
$key = $_GET['key'];
include 'checkAuth.php';
$data = new ChangesData();
echo json_encode($data, JSON_UNESCAPED_UNICODE);
?>
