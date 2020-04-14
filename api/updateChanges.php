<?php
$input = file_get_contents('php://input');
if($input != "")
{
        $data = json_decode($input);
        $ip = $_SERVER['REMOTE_ADDR'];
        if(!isset($data->key)) 
        {
            die('{"error":"Key is required for authorisation","errorcode":6}');
        }
        $key = $data->key;
        if(!isset($data->changes))
        {
            die('{"error":"Missing required changes parameter","errorcode":7}');
        }
        if(!isset($data->changes->TextChanges) || !isset($data->changes->NumericChanges))
        {
            die('{"error":"Parameter changes is incorrect","errorcode":7}');
        }
        else if(count($data->changes->NumericChanges) != 8)
        {
            die('{"error":"Parameter changes is incorrect","errorcode":7}');
        }
        include 'checkAuth.php';
	$newchanges = $data->changes;
	$fp = fopen('../changes', 'w');
	fwrite($fp, $newchanges->TextChanges);
	fclose($fp);
	$fc = fopen('../NumericChanges', 'w');
	fwrite($fc, implode("\n", $newchanges->NumericChanges));
	fclose($fc);
	echo '{"result":true}';
	
}
?>
