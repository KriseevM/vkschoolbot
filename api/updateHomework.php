<?php
require_once "../dbconnectinfo.php";

$input = file_get_contents('php://input');
if(input != "")
{
	$data = json_decode($input);
        $ip = $_SERVER['REMOTE_ADDR'];
        $key = $data->key;
        include 'checkAuth.php';
        $link = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
	if(isset($data->ID))
	{
		$q = "UPDATE Homeworkdata SET Homework='".$data -> Homework."' WHERE ID = ".$data ->ID;
		$res = mysqli_query($link, $q);
		echo "OK";
	}
}
?>
