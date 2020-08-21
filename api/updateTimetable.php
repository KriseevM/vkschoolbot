<?php
require_once '../vendor/autoload.php';
include 'API.php';
try {
    $input = file_get_contents('php://input');
    $data = json_decode($input);
    $key = $_SERVER['HTTP_KEY'];
    $ip = $_SERVER['REMOTE_ADDR'];
    $api = new API($key, $ip);
    $result = $api->update_timetable_method($data);
    echo json_encode(['updated' => $result]);
} catch (Exception $e) {
    die(json_encode(['error' => $e->getMessage(), 'errorcode' => $e->getCode()]));
} catch (TypeError $e) {
    die(json_encode(['error' => API::ERROR_INVALID_PARAMETERS, 'errorcode' => 7]));
}