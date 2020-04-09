<?php
$ip = $_SERVER['REMOTE_ADDR'];
$key = $_GET['key'];
include 'checkAuth.php';
require_once "../dbconnectinfo.php";
$link = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
$res = mysqli_query ($link, "SELECT ID, Subject FROM Homeworkdata");


$output = array();
while($row = mysqli_fetch_row($res))
{
	$output[$row[0]] = $row[1];
}
echo json_encode($output, JSON_UNESCAPED_UNICODE);
?>
