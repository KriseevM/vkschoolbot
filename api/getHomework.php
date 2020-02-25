<?php
require_once "../dbconnectinfo.php";
$link = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
$input = $_GET['id'];
$res = mysqli_fetch_row(mysqli_query($link, "SELECT * FROM Homeworkdata WHERE ID=".$input));
$data = array('ID' => $res[0], 'Subject' => $res[1], 'Homework' => $res[2]);
echo json_encode($data, JSON_UNESCAPED_UNICODE);
?>
