<?php
if(!isset($_GET['key'])) 
{
    die('{"error":"Key is required for authorisation","errorcode":6}');
}
$key = $_GET['key'];
include 'checkAuth.php';

if($auth_pr !== 2)
{
    die("{\"error\":\"You are not allowed to use this method\",\"errorcode\":9}");
}

if(!isset($_GET['user']))
{
    die('{"error":"You must specify user parameter","errorcode":7}');
}
else
{
    // Дополнительная проверка ввода
    if (preg_match("/^[\w]+$/", $_GET['user']) === 0)
    {
        die('{"error":"Parameters are incorrect","errorcode":7}');
    }
    $user = $_GET['user'];
    if($user === $auth_user)
    {
        die("{\"error\":\"You can not remove the user you are logged in\",\"errorcode\":7}");
    }
    $userdel_req = "DELETE FROM UserData WHERE user=\"$user\";";
    $state = $db->exec($userdel_req);
    echo "{success:$state}";
}