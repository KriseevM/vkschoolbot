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
                . '"TextChanges":{"type":"string", "required":"true"},'
                . '"NumericChanges":{"type":"array","items":{"type":"integer"}, "required":"true"}'
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
	$fp = fopen('../changes', 'w');
	fwrite($fp, $data->TextChanges);
	fclose($fp);
	$fc = fopen('../NumericChanges', 'w');
	fwrite($fc, implode("\n", $data->NumericChanges));
	fclose($fc);
	echo '{"result":true}';
	
}
else
{
    die('{"error":"Empty request","errorcode":7}');
}
?>
