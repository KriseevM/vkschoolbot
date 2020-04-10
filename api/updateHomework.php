<?php

$input = file_get_contents('php://input');
if(input != "")
{
	$data = json_decode($input);
        $ip = $_SERVER['REMOTE_ADDR'];
        if(!isset($data->key)) 
        {
            die('{"error":"Key is required for authorisation","errorcode":6}');
        }
        $key = $data->key;
        include 'checkAuth.php';
        require_once "../dbconnectinfo.php";
        $link = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname) or die("{\"error\":\"Failed to connect to database.\",\"errorcode\":1}");
	if(isset($data->ID))
	{
		$q = "UPDATE Homeworkdata SET Homework='".$data -> Homework."' WHERE ID = ".$data ->ID;
		$res = mysqli_query($link, $q);
		echo "OK";
	}
}
?>
