<?php
if(!isset($_GET['key'])) 
{
    die('{"error":"Key is required for authorisation","errorcode":6}');
}
$key = $_GET['key'];
include 'checkAuth.php';
if(!isset($_GET['id']))
{
    die('{"error":"Missing required id parameter","errorcode":7}');
}
if(!is_numeric($_GET['id']))
{
    die('{"error":"Parameters are invalid","errorcode":7}');
}
$input = $_GET['id'];   
$res = $db->query("SELECT * FROM Homeworkdata WHERE ID=".$input)->fetchArray(SQLITE3_NUM) or die('{"error":"Failed to execute SQL query","errorcode":2}');
$data = array('ID' => intval($res[0]), 'Homework' => $res[2]);
echo json_encode($data, JSON_UNESCAPED_UNICODE);
?>
