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
            'TextTimetable' => (object)[
                'type' => 'array',
                'items' => (object)[
                    'type' => 'string'
                ],
                'minItems' => 6,
                'maxItems' => 6,
                'required' => true
            ],
            'NumericTimetable' => (object)[
                'type' => 'array',
                'items' => (object)[
                    'type' => 'array',
                    'items' => (object)[
                        'type' => 'integer'
                    ],
                    'maxItems' => 8
                ],
                'minItems' => 6,
                'maxItems' => 6,
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

    $result = true;

    for ($i = 1; $i <= 6; $i++) {
        $text_timetable_file = fopen('../TextTimetable/' . $i, 'wb');
        if (!$text_timetable_file) {
            die('{"error":"Could not open required file", "errorcode":8}');
        }
        fwrite($text_timetable_file, $data->TextTimetable[$i - 1]);
        fclose($text_timetable_file);
        $numeric_timetable_file = fopen('../NumTimetable/' . $i, 'wb');
        if (!$numeric_timetable_file) {
            die('{"error":"Could not open required file", "errorcode":8}');
        }
        fwrite($numeric_timetable_file, implode("\n", $data->NumericTimetable[$i - 1]));
        fclose($numeric_timetable_file);
        $result = $result
            && file_get_contents('../NumTimetable/' . $i) == implode("\n", $data->NumericTimetable[$i - 1])
            && file_get_contents('../TextTimetable/' . $i) == $data->TextTimetable[$i - 1];
    }
    echo json_encode(array('success' => $result), JSON_UNESCAPED_UNICODE);
} else {
    die('{"error":"Empty request","errorcode":7}');
}
