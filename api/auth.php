<?php
$path = realpath(dirname(__FILE__));
include $path.'/API.php';
try
{

    $ip = $_SERVER['REMOTE_ADDR'];
    $user = $_POST['user'];
    $pass = $_POST['pass'];
    $key = API::auth($user, $pass, $ip);
    $api = new API($key, $ip);
    echo json_encode(['key' => $key, 'username' => $user, 'pr' => $api->pr_level]);
}
catch(Exception $e)
{
    die(json_encode(['error' => $e->getMessage(), 'errorcode' => $e->getCode()]));
}
catch(TypeError $e)
{
    die(json_encode(['error' => API::ERROR_MISSING_AUTH_DATA, 'errorcode' => 6]));
}