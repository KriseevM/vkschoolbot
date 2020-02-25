<?php
$s = str_split($_GET['newch']);
$arr = explode('', $s);
$fp = fopen('/var/www/html/bot/NumericChanges', 'w');
$sa = '';
foreach ($s as $a) {
  if($a == " ")
  {
    $sa = $sa."\n";

  }
  else {
    $sa = $sa.$a."\n";
  }
}
fwrite($fp, $sa);
fclose($fp);
echo "true";
?>
