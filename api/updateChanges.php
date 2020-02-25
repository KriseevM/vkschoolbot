<?php
$input = file_get_contents('php://input');
if($input != "")
{
	$newchanges = json_decode($input);
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
