<?php
ini_set('display_errors','On');
$ip = $_SERVER['REMOTE_ADDR'];
if(!isset($key) || !isset($ip))
{
    die('{"error":"Missing required parameters for authorisation","errorcode":6}');
}
if(!(preg_match("/^[a-f\d]{64}$/", $key) === 1))
{
    die('{"error":"Key has incorrect format":6}');
}
$query = "SELECT expiration_time, user FROM PassKeys WHERE passkey=\"$key\" AND ip=\"$ip\"";
$time = time();
$db = new SQLite3("../bot.db");
$res = $db->query($query)->fetchArray(SQLITE3_NUM);
if($res === false)
{
    die("{\"error\":\"Key is invalid or your ip address does not match original ip address\",\"errorcode\":4}");
}
else 
{
    $exp_time = $res[0];
    if($time > $exp_time)
    {
        $remove_query = "DELETE FROM PassKeys WHERE passkey=\"$key\"";
        $db->exec($remove_query) or die('{"error":"Failed to execute SQL query","errorcode":2}');
        die("{\"error\":\"Key is expired\",\"errorcode\":5}");
    }
    else
    {
        $reset_time_query = "UPDATE PassKeys SET expiration_time=".($time+1800)." WHERE passkey=\"$key\"";
        $db->exec($reset_time_query) or die('{"error":"Failed to execute SQL query","errorcode":2}');
        $auth_user=$res[1];
        $auth_pr = $db->query("SELECT pr_level FROM UserData WHERE user=\"$auth_user\";")->fetchArray(SQLITE3_NUM)[0];
    }
}
