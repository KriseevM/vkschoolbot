<?php
$ip = $_SERVER['REMOTE_ADDR'];
$key = $_GET['key'];
include 'checkAuth.php';
require_once "../dbconnectinfo.php";
$link = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname) or die("{\"error\":\"Failed to connect to database.\",\"errorcode\":1}");
$input = $_GET['id'];
$fullres = mysqli_query($link, "SELECT * FROM Homeworkdata WHERE ID=".$input) or die('{"error":"Failed to execute SQL query","errorcode":2}');
$res = mysqli_fetch_row($fullres);
$data = array('ID' => $res[0], 'Subject' => $res[1], 'Homework' => $res[2]);
echo json_encode($data, JSON_UNESCAPED_UNICODE);
?>
