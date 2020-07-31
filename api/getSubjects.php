<?php
include 'checkAuth.php';
$query = "SELECT ID, Subject FROM Homeworkdata";
$res = $db->query($query);


$output = array();
while($row = $res->fetchArray(SQLITE3_ASSOC))
{
	$output[] = array('ID' => $row["ID"], 'Name' => $row["Subject"]);
}
echo json_encode($output, JSON_UNESCAPED_UNICODE);
?>
