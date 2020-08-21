<?php
require_once '../vendor/autoload.php';
include 'API.php';
try {
    $input = file_get_contents('php://input');
    $data = json_decode($input);
    $key = $_SERVER['HTTP_KEY'];
    $ip = $_SERVER['REMOTE_ADDR'];
    $api = new API($key, $ip);
    $result = $api->delete_subjects_method($data);
    echo json_encode(['deleted_subjects' => $result]);
} catch (Exception $e) {
    die(json_encode(['error' => $e->getMessage(), 'errorcode' => $e->getCode()]));
} catch (TypeError $e) {
    die(json_encode(['error' => API::ERROR_INVALID_PARAMETERS, 'errorcode' => 6]));
}