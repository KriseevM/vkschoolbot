<?php
require_once "ChangesData.php";

$data = new ChangesData();
echo json_encode($data, JSON_UNESCAPED_UNICODE);
?>
