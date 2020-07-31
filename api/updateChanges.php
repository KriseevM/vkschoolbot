<?php
require_once '../vendor/autoload.php';
include 'checkAuth.php';
$input = file_get_contents('php://input');
if ($input != "") {

    $data = json_decode($input);

    $validator = new JsonSchema\Validator;
    $schema = (object)[
        'type' => 'object',
        'properties' => (object)[
            'TextChanges' => (object)[
                'type' => 'string',
                'required' => true
            ],
            'NumericChanges' => (object)[
                'type' => 'array',
                'items' => (object)[
                    'type' => 'integer'
                ],
                'minItems' => 8,
                'maxItems' => 8,
                'required' => true
            ]
        ]
    ];

    $validator->validate($data, $schema);
    if (!$validator->isValid()) {
        $errortext = "Failed to validate parameters: ";
        foreach ($validator->getErrors() as $error) {
            $errortext .= $error['message'] . ", ";
        }
        $errortext = rtrim($errortext, ", ");
        die(json_encode(['error' => $errortext, 'errorcode' => 7]));
    }


    $text_timetable_file = fopen('../changes', 'wb');
    if (!$text_timetable_file) {
        die('{"error":"Could not open required file", "errorcode":8}');
    }
    fwrite($text_timetable_file, $data->TextChanges);
    fclose($text_timetable_file);
    $numeric_timetable_file = fopen('../NumericChanges', 'wb');
    if (!$numeric_timetable_file) {
        die('{"error":"Could not open required file", "errorcode":8}');
    }
    fwrite($numeric_timetable_file, implode("\n", $data->NumericChanges));
    fclose($numeric_timetable_file);
    echo json_encode(array('success' => true), JSON_UNESCAPED_UNICODE);
} else {
    die('{"error":"Empty request","errorcode":7}');
}
