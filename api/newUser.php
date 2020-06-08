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

if(!(isset($_GET['user']) && isset($_GET['pass']) && isset($_GET['pr'])))
{
    die('{"error":"You must specify user, pass and pr parameters","errorcode":7}');
}
else
{
    // Дополнительная проверка ввода
    if (!(($_GET['pr'] == 1 || $_GET['pr'] == 2) && preg_match("/^[\w]+$/", $_GET['user']) === 1))
    {
        die('{"error":"Parameters are incorrect","errorcode":7}');
    }
    $user = $_GET['user'];
    $pass = hash("sha256", $_GET['pass']);
    $pr_level=$_GET['pr'];
    $useradd_req = "INSERT INTO UserData (user, pass, pr_level) VALUES(\"$user\",\"$pass\",$pr_level);";
    $state = $db->exec($useradd_req);
    echo "{success:$state}";
}