<?php
include 'API.php';
require_once '../vendor/autoload.php';
try {
    $key = $_SERVER['HTTP_KEY'];
    $ip = $_SERVER['REMOTE_ADDR'];
    $input = file_get_contents('php://input');
    $data = json_decode($input);
    $api = new API($key, $ip);
    $result = $api->add_user_method($data);
    echo json_encode(['added' => $result], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    die(json_encode(['error' => $e->getMessage(), 'errorcode' => $e->getCode()]));
} catch (TypeError $e) {
    die(json_encode(['error' => API::ERROR_INVALID_PARAMETERS, 'errorcode' => 7]));
}