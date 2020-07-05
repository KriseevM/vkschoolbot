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
    $homework = $data->Homework;
    $ID = $data->ID;
    $query = "UPDATE Homeworkdata SET Homework=:homework WHERE ID = :id";
    $stmt = $db->prepare($query);
    $stmt->bindValue(':homework', $homework);
    $stmt->bindValue(':id', $ID);
    $stmt->execute();
    $result = boolval($db->changes());
	echo json_encode(array('updated' => $result), JSON_UNESCAPED_UNICODE);
	
}
else
{
    die('{"error":"Empty request","errorcode":7}');
}
?>
