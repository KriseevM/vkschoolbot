<?php
$input = file_get_contents('php://input');
if($input != "")
{
        $data = json_decode($input);
        $ip = $_SERVER['REMOTE_ADDR'];
        $key = $data->key;
        include 'checkAuth.php';
	$newchanges = $data->changes;
	if(isset($newchanges->TextChanges) && isset($newchanges->NumericChanges))
	{
		$fp = fopen('../changes', 'w');
		fwrite($fp, $newchanges->TextChanges);
		fclose($fp);
		$fc = fopen('../NumericChanges', 'w');
		fwrite($fc, implode("\n", $newchanges->NumericChanges));
		fclose($fc);
		echo 'OK';
	}
}
?>
