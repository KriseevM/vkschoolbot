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
        if(!isset($data->ID))
        {
            die('{"error":"Missing required parameter ID","errorcode":7}');
        }
        if(!isset($data->Homework))
        {
            die('{"error":"Missing required parameter Homework","errorcode":7}');
        }
        require_once "../dbconnectinfo.php";
        $link = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname) or die("{\"error\":\"Failed to connect to database.\",\"errorcode\":1}");
	$q = "UPDATE Homeworkdata SET Homework='".$data -> Homework."' WHERE ID = ".$data ->ID;
	$res = mysqli_query($link, $q);
	echo "OK";
	
}
?>
