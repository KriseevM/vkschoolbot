<?php
require_once '../vendor/autoload.php';
$input = file_get_contents('php://input');
if($input != "")
{
        $data = json_decode($input);
        $ip = $_SERVER['REMOTE_ADDR'];
        
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
            foreach($validator->getErrors() as $error)
            {
                die('{"error":"'.$error['message'].'":7}');
            }
        }
        $key = $data->key;
        
        include 'checkAuth.php';
        for($i = 1; $i <= 6; $i++)
        {
            $fp = fopen('../days/'.$i, 'w');
            fwrite($fp, $data->TextSchedule[$i-1]);
            fclose($fp);
            $fc = fopen('../NumericDays/'.$i, 'w');
            echo implode("\n", $data->NumericSchedule[$i-1])."..";
            fwrite($fc, implode("\n", $data->NumericSchedule[$i-1]));
            fclose($fc);
        }
	
	echo '{"result":true}';
	
}
else
{
    die('{"error":"Empty request","errorcode":7}');
}
