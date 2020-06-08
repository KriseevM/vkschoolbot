<?php
require_once '../vendor/autoload.php';
$input = file_get_contents('php://input');
if($input != "")
{
    
        //Checks and auth
	$data = json_decode($input);
        $validator = new JsonSchema\Validator;
        $schema = '{"type":"object", "properties":'
                . '{"key":{"type":"string", "required":"true"},'
                . '"ID":{"type":"integer", "required":"true"},'
                . '"Homework":{"type":"string", "required":"true"}'
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
        
        $query = "UPDATE Homeworkdata SET Homework='".$data -> Homework."' WHERE ID = ".$data ->ID;
	$db->exec($query) or die('{"error":"Failed to execute SQL query","errorcode":2}');;
	echo '{"result":true}';
	
}
else
{
    die('{"error":"Empty request","errorcode":7}');
}
?>
