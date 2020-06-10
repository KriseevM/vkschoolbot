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
                . '"IDs":{"type":"array","items":{"type":"integer"}, "required":"true"}'
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
        if($auth_pr !== 2)
        {
            die("{\"error\":\"You are not allowed to use this method\",\"errorcode\":9}");
        }
        $query = "DELETE FROM Homeworkdata WHERE ID IN (".implode(",",$data->IDs).");";
	$result = $db->exec($query);
	echo json_encode(array('success' => $result), JSON_UNESCAPED_UNICODE); 
	
}
else
{
    die('{"error":"Empty request","errorcode":7}');
}