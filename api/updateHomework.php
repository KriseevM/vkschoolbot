<?php
require_once "../dbconnectinfo.php";
$link = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
$input = file_get_contents('php://input');
if(input != "")
{
	$data = json_decode($input);
	if(isset($data->ID))
	{
		$q = "UPDATE Homeworkdata SET Homework='".$data -> Homework."' WHERE ID = ".$data ->ID;
		$res = mysqli_query($link, $q);
		echo "OK";
	}
}
?>
