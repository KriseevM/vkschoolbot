<?php
require_once '../vendor/autoload.php';
include 'checkAuth.php';
if($auth_pr !== 2)
{
    die("{\"error\":\"You are not allowed to use this method\",\"errorcode\":9}");
}
$input = file_get_contents('php://input');
if($input != "")
{
    
        //Checks and auth
	$data = json_decode($input);
    $validator = new JsonSchema\Validator;
    $schema = (object)[
        'type' => 'object',
        'properties' => (object)[
            'IDs' => (object)[
                'type' => 'array',
                'items' => (object)[
                    'type' => 'integer'
                ],
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
    
    // Переменная $db приходит из файла checkAuth.php. 
    // Но в этом файле происходит запись в базу, что влияет на вывод метода changes()
    $db->close();
    $db->open("../bot.db");
    
    $placeholders = rtrim(str_repeat('?, ', count($data->IDs)), ', ');
    $query = "DELETE FROM Homeworkdata WHERE ID IN ($placeholders);";
    $stmt = $db->prepare($query);
    for($i = 1; $i <= count($data->IDs); $i++)
    {
        $stmt->bindValue($i, $data->IDs[$i-1]);
    }
    $stmt->execute();
    $result = $db->changes();
	echo json_encode(array('deleted_subjects' => $result), JSON_UNESCAPED_UNICODE); 
	
}
else
{
    die('{"error":"Empty request","errorcode":7}');
}