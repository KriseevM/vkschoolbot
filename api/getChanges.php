<?php
include 'checkAuth.php';

$data = array(
    'TextChanges' => file_get_contents("../changes"),
    'NumericChanges' => array()
);
$numbers = explode("\n", file_get_contents("../NumericChanges"));
for($i = 0; $i < 8; $i++)
{
    $el = $numbers[$i];
    if(is_numeric($el))
    {
        $data['NumericChanges'][$i] = intval($el);
    }    
}
echo json_encode($data, JSON_UNESCAPED_UNICODE);
?>
