<?php
require_once '../vendor/autoload.php';
include 'checkAuth.php';
if ($auth_pr !== 2) {
    die("{\"error\":\"You are not allowed to use this method\",\"errorcode\":9}");
}
$input = file_get_contents('php://input');
if ($input != "") {
    $data = json_decode($input);
    $validator = new JsonSchema\Validator;
    $schema = (object)[
        'type' => 'object',
        'properties' => (object)[
            'names' => (object)[
                'type' => 'array',
                'items' => (object)[
                    'type' => 'string'
                ],
                'required' => true
            ]
        ]
    ];
    $validator->validate($data, json_decode($schema));
    if (!$validator->isValid()) {
        $errortext = "Failed to validate parameters: ";
        foreach ($validator->getErrors() as $error) {
            $errortext .= $error['message'] . ", ";
        }
        $errortext = rtrim($errortext, ", ");
        die(json_encode(['error' => $errortext, 'errorcode' => 7]));
    }
    $key = $data->key;

    // Переменная $db приходит из файла checkAuth.php. 
    // Но в этом файле происходит запись в базу, что влияет на вывод метода changes()
    $db->close();
    $db->open("../bot.db");
    $placeholders = rtrim(str_repeat('(?), ', count($data->names)), ', ');
    $query = "INSERT INTO Homeworkdata (Subject) VALUES $placeholders";
    $stmt = $db->prepare($query);
    for ($i = 1; $i <= count($data->names); $i++) {
        $stmt->bindValue($i, $data->names[$i - 1]);
    }
    $stmt->execute();
    $result = $db->changes();
    echo json_encode(array('added_subjects' => $result), JSON_UNESCAPED_UNICODE);
} else {
    die('{"error":"Empty request","errorcode":7}');
}
