<?php
$path = realpath(dirname(__FILE__));
include $path.'/API.php';
$ip = $_SERVER['REMOTE_ADDR'];
$user = $_POST['user'];
$pass = $_POST['pass'];
try
{
    $key = API::auth($user, $pass, $ip);
    echo json_encode(['key' => $key]);
}
catch(Exception $e)
{
    die(json_encode(['error' => $e->getMessage(), 'errorcode' => $e->getCode()]));
}