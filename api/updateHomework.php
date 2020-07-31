<?php
require_once '../vendor/autoload.php';
$input = file_get_contents('php://input');
if($input != "")
{
    
        //Checks and auth
	$data = json_decode($input);
    $validator = new JsonSchema\Validator;
    $schema = (object)[
        'type' => 'object',
        'properties' => (object) [
            'ID' => (object)[
                'type' => 'integer',
                'required' => true
            ],
            'Homework' => (object)[
                'type' => 'string',
                'required' => true
            ]
        ]
    ];        
    $validator->validate($data, $schema);
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
