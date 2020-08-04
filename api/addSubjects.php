<?php
require_once '../vendor/autoload.php';
include 'API.php';
try {
    $input = file_get_contents('php://input');
    $data = json_decode($input);
    $key = $_SERVER['HTTP_KEY'];
    $ip = $_SERVER['REMOTE_ADDR'];
    $api = new API($key, $ip);
    $api->add_subjects_method($data);
} catch (Exception $e) {
    die(json_encode(['error' => $e->getMessage(), 'errorcode' => $e->getCode()]));
} catch (TypeError $e) {
    die(json_encode(['error' => API::ERROR_MISSING_AUTH_DATA, 'errorcode' => 6]));
}
