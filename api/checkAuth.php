<?php
$ip = $_SERVER['REMOTE_ADDR'];
if(!isset($key) || !isset($ip))
{
    die('{"error":"Missing required parameters for authorisation","errorcode":6}');
}
require 'APIInternalInfo.php';
$query = "Select expiration_time from $dbkeystable where passkey=\"$key\" and ip=\"$ip\"";
$time = time();
$userdblink = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname) or die("{\"error\":\"Failed to connect to database.\",\"errorcode\":1}");;
$res = mysqli_query($userdblink, $query) or die('{"error":"Failed to execute SQL query","errorcode":2}');
if(mysqli_num_rows($res) == 0)
{
    $auth = false;
    die("{\"error\":\"Key is invalid or your ip address does not match original ip address\",\"errorcode\":4}");
}
else 
{
    $exp_time = mysqli_fetch_row($res)[0];
    if($time > $exp_time)
    {
        $remove_query = "Delete from $dbkeystable where passkey=\"$key\"";
        mysqli_query($userdblink, $remove_query) or die('{"error":"Failed to execute SQL query","errorcode":2}');
        $auth = false;
        die("{\"error\":\"Key is expired\",\"errorcode\":5}");
    }
    else
    {
        $reset_time_query = "Update $dbkeystable Set expiration_time=".($time+1800)." where passkey=\"$key\"";
        mysqli_query($userdblink, $reset_time_query) or die('{"error":"Failed to execute SQL query","errorcode":2}');
        $auth = true;
    }
}
