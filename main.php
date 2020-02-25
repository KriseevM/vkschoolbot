<?php
require 'APIInfo.php';
$days = [1 => "понедельник", 2 => "вторник", 3 => "среду", 4 => "четверг", 5 => "пятницу", 6 => "субботу"];
function sendMessage($message, $token, $peer_id) {
    $request_params = array(
'message' => $message,
'peer_id' => $peer_id,
'access_token' => $token,
'v' => '5.85'
);
    $context = stream_context_create(array(
	    'http' => array(
		    'method' => 'POST',
		    'header' => "Content-type: application/x-www-form-urlencoded\r\n",
		    'content' => http_build_query($request_params)
	    )
    ));

    return file_get_contents('https://api.vk.com/method/messages.send?', false, $context);
}

function sendKbd($token, $peer_id) {
	$request_params = array(
		'message' => "Теперь вы можете пользоваться клавишами! :)",
		'keyboard' => '{"one_time":false,"buttons":[[{"action":{"type":"text","label":"дз"},"color":"primary"},{"action":{"type":"text","label":"замены"},"color":"primary"}],[{"action":{"type":"text","label":"расписание на сегодня"},"color":"primary"}],[{"action":{"type":"text","label":"расписание на завтра"},"color":"primary"}]]}',
		'peer_id' => $peer_id,
		'access_token' => $token,
		'v' => '5.85'
);
     $get_params = http_build_query($request_params);

    return file_get_contents('https://api.vk.com/method/messages.send?'. $get_params);
}


function getAllHomework()
{
    require 'dbconnectinfo.php';
     $link = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
 
     $sql = "SELECT Subject, Homework FROM Homeworkdata where Homework != \"\"";
     $res = mysqli_query($link, $sql);
     return $res;
}

function getHomework($day) {
    require 'dbconnectinfo.php';
     $link = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
     $schedule = getSchedule($day);
     $changes = getChanges();
     $finalSchedule = applyChanges($schedule, $changes);
 
     $sql = "SELECT Subject, Homework FROM Homeworkdata where ID in (".implode(",", $finalSchedule).") AND Homework != \"\"";
     $res = mysqli_query($link, $sql);
     return $res;
}
function getSchedule($day) {
    if($day < 1 || $day > 6) {
        echo false;
         return false;
     } else {
        $schedule = explode ("\n", file_get_contents("NumericDays/".$day));
         return $schedule;
     }
}
function getChanges() {
    $changes = explode("\n", file_get_contents("NumericChanges"));
     return $changes;
}
function applyChanges ($sched, $changes) {
    $newSchedule = $sched;
     for($i = 0;
     $i < 8;
     $i++) {
        if(array_key_exists($i, $changes)) {
            if($changes[$i] == -2) {
                continue;
             } else {
                $newSchedule[$i] = $changes[$i];
             }
         }
     }
     $newSchedule = array_diff($newSchedule, ['-1', '']);
     return $newSchedule;
 
}
$data = json_decode(file_get_contents('php://input'));
switch ($data->type) {
    case 'confirmation':echo $confirmation;
     break;
     case 'message_edit':
    case 'message_new':
        echo "ok";
        $message = '';
         $peer_id = $data->object->from_id;
         $q = 'https://api.vk.com/method/users.get?'.http_build_query(array(
 'user_ids' => $peer_id, 
 'access_token' => $token,
 'v' => '5.101'
 ));
         $userinfo = file_get_contents($q);
         $username = json_decode($userinfo) -> response[0] -> first_name;
 
         $user_id = $data->object->from_id;
         $text = $data->object->text;
         switch(mb_strtolower($text)) {
	 case "start":
	 case "начать":
		 sendKbd($token, $peer_id);
            break;
            case "дз":
            case "домашка":
            case "домашнее задание":
            case "домашняя работа":
            case "че задали?":
            case "чо задали?":
            case "чо задали":
            case "что задали?":
            case "что задали":
            case "че задали":
            case "задание":
                $now = date("N", $data->object->date);
                 if($now == 7 || $now == 6) {
                    $homework = getHomework(1);
                     if(mysqli_num_rows($homework) == 0) {
                        $message = "На понедельник ничего не задали";
                     } else {
                        $message = "Домашнее задание на понедельник:";
                         while($row = mysqli_fetch_row($homework)) {
                            $message.= "\n• ".$row[0].": ".$row[1];
                         }
                     }
                 } else {
                    $homework = getHomework($now + 1);
                     if(mysqli_num_rows($homework) == 0) {
                        $message = "На завтра ничего не задали";
                     } else {
                        $message = "Домашнее задание на завтра:";
                         while($row = mysqli_fetch_row($homework)) {
                            $message.= "\n• ".$row[0].": ".$row[1];
                         }
                     }
                 }
                sendMessage($message, $token, $data->object->peer_id);
                 break;
                 case 'дз на сегодня':
                $now = date("N", $data->object->date);
                if($now == 7) {
                    $homework = getHomework(1);
                     if(mysqli_num_rows($homework) == 0) {
                        $message = "На понедельник ничего не задали";
                     } else {
                        $message = "Домашнее задание на понедельник:";
                         while($row = mysqli_fetch_row($homework)) {
                            $message.= "\n• ".$row[0].": ".$row[1];
                         }
                     }
                 } else {
                    $homework = getHomework($now);
                     if(mysqli_num_rows($homework) == 0) {
                        $message = "На сегодня ничего не задали";
                     } else {
                        $message = "Домашнее задание на сегодня:";
                         while($row = mysqli_fetch_row($homework)) {
                            $message.= "\n• ".$row[0].": ".$row[1];
                         }
                     }
                 }
                sendMessage($message, $token, $data->object->peer_id);
                 break;
                 case "замены":
                case "изменения":
                    $message = file_get_contents("changes");
                    sendMessage($message, $token, $data->object->peer_id);
                     break;
                 case 'расписание на завтра':
                 case 'расписание':
                    $now = date("N", $data->object->date);
                     if($now == 7 || $now == 6) {
                        $message = "Расписание на понедельник:\n".file_get_contents('days/1');
 
                     } else {
                        $message = 'Расписание на '.$days[$now + 1].":\n".file_get_contents('days/'.($now + 1));
		     }
		     sendMessage($message, $token, $data->object->peer_id);
                     break;
                     case 'расписание на сегодня':
                        $now = date("N", $data->object->date);
                         if($now == 7) {
                            $message = "Сегодняя уроков нет :)\nЛадно, расписание на понедельник:\n".file_get_contents('days/1');
 
                         } else {
                            $message = 'Расписание на '.$days[$now].":\n".file_get_contents('days/'.($now));
                         }
                        sendMessage($message, $token, $data->object->peer_id);
                         break;
		     case "все дз":
                     $homework = getAllHomework();
                     if(mysqli_num_rows($homework) == 0) {
                        $message = "Задание отсутствует";
                     } else {
                        $message = "Домашнее задание по всем предметам:";
                         while($row = mysqli_fetch_row($homework)) {
                            $message.= "\n• ".$row[0].": ".$row[1];
                         }
		     }
                     sendMessage($message, $token, $data->object->peer_id);
		     break;
		     case 'всё расписание':
                     case 'все расписание':
		     case 'расписание на всю неделю':
			     $message = "Расписание";
			     $message .= "\nНа понедельник:\n".file_get_contents('days/1');
			     $message .= "\nНа вторник:\n".file_get_contents('days/2');
			     $message .= "\nНа среду:\n".file_get_contents('days/3');
			     $message .= "\nНа четверг:\n".file_get_contents('days/4');
			     $message .= "\nНа пятницу:\n".file_get_contents('days/5');
			     $message .= "\nНа субботу:\n".file_get_contents('days/6');
			     sendMessage($message, $token, $data->object->peer_id);
			     break;
                         
                     }
 
 
 
                     break;
                 }

                 ?>
