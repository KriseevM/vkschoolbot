<?php
include 'API.php';
try {
    $key = $_SERVER['HTTP_KEY'];
    $ip = $_SERVER['REMOTE_ADDR'];
    $user = $_GET['user'];
    $api = new API($key, $ip);
    $result = $api->delete_user_method($user);
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    die(json_encode(['error' => $e->getMessage(), 'errorcode' => $e->getCode()]));
} catch (TypeError $e) {
    die(json_encode(['error' => API::ERROR_INVALID_PARAMETERS, 'errorcode' => 7]));
}