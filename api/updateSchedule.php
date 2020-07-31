<?php
require_once '../vendor/autoload.php';
$input = file_get_contents('php://input');
if($input != "")
{
    $data = json_decode($input);        
    $validator = new JsonSchema\Validator;
    $schema = '{"type":"object", "properties":'
            . '{"key":{"type":"string", "required":"true"},'
            . '"TextSchedule":{"type":"array", "items":{"type":"string"}, "required":"true"},'
            . '"NumericSchedule":{"type":"array","items":{"type":"array", "items":{"type":"integer"}}, "required":"true"}'
            . '}'
        . '}';
    $validator->validate($data, json_decode($schema));
    if(!$validator->isValid())
    {
        $errortext = "Failed to validate parameters: ";
        foreach($validator->getErrors() as $error)
        {
            $errortext .= $error['message'].", ";
        }
        $errortext = rtrim($errortext, ", ");
        die(json_encode(['error' => $errortext, 'errorcode' => 7]));
    }
    $key = $data->key;
    $result = true;
    include 'checkAuth.php';
    for ($i = 1; $i <= 6; $i++) {
        $text_schedule_file = fopen('../days/' . $i, 'wb');
        if (!$text_schedule_file) {
            die('{"error":"Could not open required file", "errorcode":8}');
        }
        fwrite($text_schedule_file, $data->TextSchedule[$i - 1]);
        fclose($text_schedule_file);
        $numeric_schedule_file = fopen('../NumericDays/' . $i, 'wb');
        if (!$numeric_schedule_file) {
            die('{"error":"Could not open required file", "errorcode":8}');
        }
        fwrite($numeric_schedule_file, implode("\n", $data->NumericSchedule[$i - 1]));
        fclose($numeric_schedule_file);
        $result = $result
            && file_get_contents('../NumericDays/' . $i) == implode("\n", $data->NumericSchedule[$i - 1])
            && file_get_contents('../days/' . $i) == $data->TextSchedule[$i - 1];
    }
	
	echo json_encode(array('success' => $result), JSON_UNESCAPED_UNICODE);
	
}
else
{
    die('{"error":"Empty request","errorcode":7}');
}
