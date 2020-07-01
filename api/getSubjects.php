<?php
if(!isset($_GET['key'])) 
{
    die('{"error":"Key is required for authorisation","errorcode":6}');
}
$key = $_GET['key'];
include 'checkAuth.php';
$query = "SELECT ID, Subject FROM Homeworkdata";
$res = $db->query($query);


$output = array();
while($row = $res->fetchArray(SQLITE3_ASSOC))
{
	$output[$row["ID"]] = $row["Subject"];
}
echo json_encode($output, JSON_UNESCAPED_UNICODE);
?>
