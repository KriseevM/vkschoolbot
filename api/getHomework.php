<?php
include 'checkAuth.php';
if(!isset($_GET['id']))
{
    die('{"error":"Missing required id parameter","errorcode":7}');
}
if(!is_numeric($_GET['id']))
{
    die('{"error":"Parameters are invalid","errorcode":7}');
}
$id = $_GET['id'];
$query = "SELECT * FROM Homeworkdata WHERE ID=:id";
$stmt = $db->prepare($query);
$stmt->bindValue(':id', $id);
$res = $stmt->execute()->fetchArray(SQLITE3_NUM);
$data = array('ID' => intval($res[0]), 'Homework' => $res[2]);
echo json_encode($data, JSON_UNESCAPED_UNICODE);
?>
