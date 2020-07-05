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
                . '"names":{"type":"array","items":{"type":"string","pattern":"^[\\\\wА-Яа-яЁё\\\\s-]{1,50}$"}, "required":"true"}'
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

        // Переменная $db приходит из файла checkAuth.php. 
        // Но в этом файле происходит запись в базу, что влияет на вывод метода changes()
        $db->close();
        $db->open("../bot.db");
        $placeholders = rtrim(str_repeat('(?), ', count($data->names)), ', ');
        $query = "INSERT INTO Homeworkdata (Subject) VALUES $placeholders";
        $stmt = $db->prepare($query);
        for($i = 1; $i <= count($data->names); $i++)
        {
            $stmt->bindValue($i, $data->names[$i-1]);
        }
        $stmt->execute();
        $result = $db->changes();
        echo json_encode(array('added_subjects' => $result), JSON_UNESCAPED_UNICODE); 
	
	
}
else
{
    die('{"error":"Empty request","errorcode":7}');
}