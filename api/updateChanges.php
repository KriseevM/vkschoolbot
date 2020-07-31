<?php
require_once '../vendor/autoload.php';
$input = file_get_contents('php://input');
if($input != "")
{
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
                    'required'=> true
                ]
            ]
        ];
      
        $validator->validate($data, $schema);
        if(!$validator->isValid())
        {
            foreach($validator->getErrors() as $error)
            {
                die('{"error":"'.$error['message'].'":7}');
            }
        }
        $key = $data->key;
        
        include 'checkAuth.php';
	$text_schedule_file = fopen('../changes', 'wb');
        if(!$text_schedule_file) {die('{"error":"Could not open required file", "errorcode":8}');}
	fwrite($text_schedule_file, $data->TextChanges);
	fclose($text_schedule_file);
	$numeric_schedule_file = fopen('../NumericChanges', 'wb');
        
        if(!$numeric_schedule_file) {die('{"error":"Could not open required file", "errorcode":8}');}
	fwrite($numeric_schedule_file, implode("\n", $data->NumericChanges));
	fclose($numeric_schedule_file);
	echo json_encode(array('success' => true), JSON_UNESCAPED_UNICODE);
	
}
else
{
    die('{"error":"Empty request","errorcode":7}');
}
?>
