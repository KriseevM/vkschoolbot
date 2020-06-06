<?php
$ip = $_SERVER['REMOTE_ADDR'];
if(!isset($key) || !isset($ip))
{
    die('{"error":"Missing required parameters for authorisation","errorcode":6}');
}
$query = "Select expiration_time from PassKeys where passkey=\"$key\" and ip=\"$ip\"";
$time = time();
$db = new SQLite3("../bot.db");
$res = $db->query($query)->fetchArray(SQLITE3_NUM);
if($res == false)
{
    die("{\"error\":\"Key is invalid or your ip address does not match original ip address\",\"errorcode\":4}");
}
else 
{
    $exp_time = $res[0];
    if($time > $exp_time)
    {
        $remove_query = "Delete from PassKeys where passkey=\"$key\"";
        $db->exec($remove_query) or die('{"error":"Failed to execute SQL query","errorcode":2}');
        die("{\"error\":\"Key is expired\",\"errorcode\":5}");
    }
    else
    {
        $reset_time_query = "Update PassKeys Set expiration_time=".($time+1800)." where passkey=\"$key\"";
        $db->exec($reset_time_query) or die('{"error":"Failed to execute SQL query","errorcode":2}');
    }
}
