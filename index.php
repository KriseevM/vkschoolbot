<?php
require_once 'dbconnectinfo.php';
require_once 'APIInfo.php';

$data = json_decode(file_get_contents('php://input'));
switch ($data->type) {
    case 'confirmation': echo $confirmation; break;
    case 'message_edit':
	case 'message_new':
	echo 'ok';
//...???????? id ??? ??????
$peer_id = $data->object->from_id;
$user_id = $data->object->from_id;
$text = mb_strtolower($data->object->text);
$message = "";
//????? ? ??????? users.get ???????? ?????? ?? ??????
//$user_info = json_decode(file_get_contents("https://api.vk.com/method/users.get?user_ids={$user_id}&access_token={$token}&v=5.0"));

//? ????????? ?? ?????? ??? ???
//$user_name = $user_info->response[0]->first_name;

//? ??????? messages.send ?????????? ???????? ?????????
if($text == "расписание" || $text == "расписание на сегодня"){
// ?????? ? ?? ?????????? ??????????? "?????" - ????? ???????? ?????????
if(time() % 86400 > 21600){
if(date("N", time() + 86400) == 7){
    $message = "Расписание на ".date("d.m.o", time()+172800).":\n".file_get_contents("days/".date("N", time()+172800));
}

else
{
	$message = "Расписание на ".date("d.m.o", time()+86400).":\n".file_get_contents("days/".date("N", time()+86400));
//'user_id' => $peer_id,
}
}
else
{
	if(date("N") == 7){
$message = "Расписание на ".date("d.m.o", time()+86400).":\n".file_get_contents("days/".date("N", time()+86400));
}
else
{
	$message = "Расписание на ".date("d.m.o").":\n".file_get_contents("days/".date("N"));
}
}
}
else if($text == "дз" || $text == "домашка" || $text == "че задали?" || $text == "чё задали?" || $text == "чо задали?" || $text == "что задали?" || $text == "какое дз?" || $text == "че задали" || $text == "чё задали" || $text == "чо задали" || $text == "что задали" || $text == "какое дз" || $text == "задание")
{
    $link = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);

    //mysqli_select_db("himeworks");



  if(time() % 86400 > 23400){//задание на завтра
  if(date("N", time() + 86400) == 7){
      $message = "Задание на ".date("d.m.o", time()+172800).":\n";
      ////////////////////////
      //если нужно писать задание на завтра,
      // а завтра - воскресенье (пишем на послезавтра - +172800)
      $array1 = explode("\n", file_get_contents("NumericDays/".date("N", time()+172800)));
$array2 = explode("\n", file_get_contents("NumericChanges"));
$i = 0;
//Смешиваем расписание и замены
foreach($array2 as $val)
{
	if($val != "")
	{
		$array1[$i] = $val;
    if($val == "x")
    {
      $array1[$i] = "";
    }
	}
	$i =$i+1;
}

foreach($array1 as $arr)
{
	if($arr == "") continue;
	$q = mysqli_query($link, "SELECT Subject, Homework FROM Homeworkdata WHERE ID=".$arr);
	$res = mysqli_fetch_row($q);
	if($res[1] == "") continue;
	$message.=" ◆ ".$res[0].": ".$res[1]."\n";

}
if($message == "Задание на ".date("d.m.o", time()+172800).":\n")
{
  $message = "На ".date("d.m.o", time()+172800)." ничего не задали";
}
      ////////////////////////
  }

  else
  {
  	$message = "Задание на ".date("d.m.o", time()+86400).":\n";
    ///////////////////////
    //!!! до 3:00 в воскресенье исполняется это!!!
  //задание на завтра, если завтра не воскр.
$array1 = explode("\n", file_get_contents("NumericDays/".date("N", time()+86400)));
$array2 = explode("\n", file_get_contents("NumericChanges"));
$i = 0;
foreach($array2 as $val)
{
	if($val != "")
	{
		$array1[$i] = $val;
	}
	$i =$i+1;
}

foreach($array1 as $arr)
{
	if($arr == "") continue;
	$q = mysqli_query($link, "SELECT Subject, Homework FROM Homeworkdata WHERE ID=".$arr);
	$res = mysqli_fetch_row($q);
	if($res[1] == "") continue;
	$message.=" ◆ ".$res[0].": ".$res[1]."\n";

}
if($message == "Задание на ".date("d.m.o", time()+86400).":\n")
{
  $message = "На ".date("d.m.o", time()+86400)." ничего не задали";
}
  /////////////////////////
  }
  }
  else//задание на сегодня
  {
  	if(date("N") == 7){
  	//задание на сегодня если сегодня воскр
      // (т.е. задание на завтра)


  $message = "Задание на ".date("d.m.o", time()+86400).":\n";
///////////////////////
$array1 = explode("\n", file_get_contents("NumericDays/".date("N", time()+86400)));
$array2 = explode("\n", file_get_contents("NumericChanges"));
$i = 0;
foreach($array2 as $val)
{
	if($val != "")
	{
		$array1[$i] = $val;
	}
	$i =$i+1;
}

foreach($array1 as $arr)
{
	if($arr == "") continue;
	$q = mysqli_query($link, "SELECT Subject, Homework FROM Homeworkdata WHERE ID=".$arr);
	$res = mysqli_fetch_row($q);
	if($res[1] == "") continue;
	$message.=" ◆ ".$res[0].": ".$res[1]."\n";

}
if($message == "Задание на ".date("d.m.o", time()+86400).":\n")
{
  $message = "На ".date("d.m.o", time()+86400)." ничего не задали";
}
///////////////////////
  }
  else
  {
  	//задание на сегодня, если сегодня не воскр
  	$message = "Задание на ".date("d.m.o").":\n";
      ////////////
      $array1 = explode("\n", file_get_contents("NumericDays/".date("N")));
$array2 = explode("\n", file_get_contents("NumericChanges"));
$i = 0;
foreach($array2 as $val)
{
	if($val != "")
	{
		$array1[$i] = $val;
	}
	$i =$i+1;
}
foreach($array1 as $arr)
{
	if($arr == "") continue;
	$q = mysqli_query($link, "SELECT Subject, Homework FROM Homeworkdata WHERE ID=".$arr);
	$res = mysqli_fetch_row($q);
	if($res[1] == "") continue;
	$message.=" ◆ ".$res[0].": ".$res[1]."\n";

}
if($message == "Задание на ".date("d.m.o").":\n")
{
  $message = "На ".date("d.m.o")." ничего не задали";
}
      ////////////
  }
  }
}
else if($text == "замены" || $text == "какие замены" || $text == "какие замены?" || $text == "изменения")
{
  $message = file_get_contents("publicinfo/changes");
}
else if($text == "расписание на неделю" || $text == "все расписание" || $text == "всё расписание" || $text == "расписание на всю неделю")
{
	$message = "Расписание на всю неделю:"."\nПонедельник:\n".file_get_contents("days/1")."\nВторник:\n".file_get_contents("days/2")."\nСреда:\n".file_get_contents("days/3")."\nЧетверг:\n".file_get_contents("days/4")."\nПятница:\n".file_get_contents("days/5")."\nСуббота:\n".file_get_contents("days/6");
}
else if($text == "гдз_срочна" || $text == "гдз_срочно" || $text == "гдз")
{
    $message = "http://gramota.ru";
}
else if($text == "все дз" || $text == "всё дз")
{
	$link = mysqli_connect("localhost:3306", "Bot", "GresReboot20010", "homeworks");
    $res = mysqli_query($link, 'SELECT Subject, Homework FROM Homeworkdata WHERE Homework != ""');
    $message = "Полный перечень заданий:\n";
    for($i = 0; $i < $res->num_rows; $i++)
    {
    	$row = mysqli_fetch_row($res);
    	$message .= $row[0].": ".$row[1]."\n";
    }
}
SEND:
if($data->object->from_id != $data->object->peer_id)
{
$request_params = array(
'message' => $message,
'chat_id' => $data->object->peer_id - 2000000000,
'access_token' => $token,
'v' => '5.85'
);
}
else
{
	$request_params = array(
'message' => $message,
'user_id' => $data->object->from_id,
'access_token' => $token,
'v' => '5.85'
);
}
$get_params = http_build_query($request_params);

file_get_contents('https://api.vk.com/method/messages.send?'. $get_params);

//?????
//?????????? "ok" ??????? Callback API
break;
}

?>
