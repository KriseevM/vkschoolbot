<?php
try {
    include 'API.php';
    $key = $_SERVER['HTTP_KEY'];
    $ip = $_SERVER['REMOTE_ADDR'];
    $api = new API($key, $ip);
    $result = $api->get_timetable_method();
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    die(json_encode(['error' => $e->getMessage(), 'errorcode' => $e->getCode()]));
} catch (TypeError $e) {
    die(json_encode(['error' => API::ERROR_INVALID_KEY, 'errorcode' => 4]));
}
