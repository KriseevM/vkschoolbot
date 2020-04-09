<?php
require 'APIInternalInfo.php';
$query = "Select expiration_time from $dbkeystable where passkey=\"$key\" and ip=\"$ip\"";
$time = time();
$userdblink = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname) or die("false");
$res = mysqli_query($userdblink, $query);
if(mysqli_num_rows($res) == 0)
{
    $auth = false;
    die("{\"error\":\"Key is invalid or your ip address does not match original ip address\"}");
}
else 
{
    $exp_time = mysqli_fetch_row($res)[0];
    if($time > $exp_time)
    {
        $remove_query = "Delete from $dbkeystable where passkey=\"$key\"";
        mysqli_query($userdblink, $remove_query);
        $auth = false;
        die("{\"error\":\"Key is expired\"}");
    }
    else
    {
        $reset_time_query = "Update $dbkeystable Set expiration_time=".($time+1800)." where passkey=\"$key\"";
        mysqli_query($userdblink, $reset_time_query);
        $auth = true;
    }
}
