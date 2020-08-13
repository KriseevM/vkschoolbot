<?php
include 'API.php';
try {
    $key = $_SERVER['HTTP_KEY'];
    $ip = $_SERVER['REMOTE_ADDR'];
    $id = $_GET['id'];
    $api = new API($key, $ip);
    $result = $api->get_homework_method($id);
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    die(json_encode(['error' => $e->getMessage(), 'errorcode' => $e->getCode()]));
} catch (TypeError $e) {
    die(json_encode(['error' => API::ERROR_INVALID_PARAMETERS, 'errorcode' => 7]));
}
