<?php
ini_set('display_errors', 'Off');
require_once 'vendor/autoload.php';
require 'APIInfo.php';
$days = [1 => "понедельник", 2 => "вторник", 3 => "среду", 4 => "четверг", 5 => "пятницу", 6 => "субботу"];
$vk = new \VK\Client\VKApiClient();

function GetHomeworkMessage(string $empty_hw_msg, string $hw_header, int $day)
{
    $homework = getHomework($day);
    $message = "";
    while($row = $homework->fetchArray(SQLITE3_ASSOC))
    {
        $message .= "🌝".$row["Subject"].": ".$row["Homework"]."\n";
    }
    if($message == "")
    {
        $message = $empty_hw_msg;
    }
    else {
        $message = $hw_header."\n".$message;
    }
    return $message;
}

function sendMessage($text, $peer)
{
    global $vk, $token;
    $vk->messages()->send($token, array(
        'message' => $text,
        'peer_id' => $peer,
	'random_id' => 0
    ));
}
function getAllHomework()
{
     $db = new SQLite3("bot.db");
 
     $sql = "SELECT Subject, Homework FROM Homeworkdata where Homework != \"\"";
     $res = $db->query($sql);
     return $res;
}

function getHomework($day) {
     $db = new SQLite3("bot.db");
     $schedule = getSchedule($day);
     $changes = getChanges();
     $finalSchedule = applyChanges($schedule, $changes);
 
     $sql = "SELECT Subject, Homework FROM Homeworkdata where ID in (".implode(",", $finalSchedule).") AND Homework != \"\"";
     $res = $db->query($sql);
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

function sendKeyboard($peer_id, $msg)
{
    
    global $vk, $token;
    $res = $vk->messages()->send($token, array(
        'peer_id' => $peer_id,
        'random_id' => 1,
        'message' => $msg,
        'keyboard'=> '{"one_time":false,"buttons":[[{"action":{"type":"text","label":"дз"},"color":"primary"},{"action":{"type":"text","label":"дз на сегодня"},"color":"primary"}],[{"action":{"type":"text","label":"все дз"},"color":"primary"},{"action":{"type":"text","label":"все расписание"},"color":"primary"}],[{"action":{"type":"text","label":"расписание на сегодня"},"color":"primary"}],[{"action":{"type":"text","label":"расписание на завтра"},"color":"primary"},{"action":{"type":"text","label":"замены"},"color":"primary"}]]}'
    ));
    
}
$data = json_decode(file_get_contents("php://input"));
switch($data->type)
{
    case 'confirmation':
        echo $confirmation;
        break;
    case 'message_new':
    case 'message_edit':
        
        echo 'ok';
        $peer = $data->object->message->peer_id;
        mb_internal_encoding(mb_detect_encoding($data->object->message->text));
        $cmd = mb_split('] ', mb_strtolower($data->object->message->text));
        
        switch(end($cmd))
        {
            case '':
            
                if(isset($data->object->message->action))
                {
                if($data->object->message->action->type == "chat_invite_user")
                {
                    sendKeyboard($peer, "Welcome :)");
                    
                }
                }
                break;
            case 'начать':
                sendKeyboard($peer, "Теперь здесь можно пользоваться клавишами бота :)");
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
                $now = date("N", $data->object->message->date);
                 if($now == 7 || $now == 6) {
                    $message = GetHomeworkMessage("На понедельник ничего не задали", "Домашнее задание на понедельник:", 1);
                 } else {
                    $message = GetHomeworkMessage("На завтра ничего не задали", "Домашнее задание на завтра:", $now+1);
                    
                 }
                sendMessage($message, $peer);
                 break;
                 case 'дз на сегодня':
                $now = date("N", $data->object->message->date);
                if($now == 7) {
                    $message = GetHomeworkMessage("Какие уроки?! Сегодня воскресенье!\nНо на завтра ничего не задали :)", "Сегодня уроков нет. \nДомашнее задание на завтра:", 1);
                } else {
                    $message = GetHomeworkMessage("На сегодня ничего не задали", "Домашнее задание на сегодня:", $now);      
                }
                sendMessage($message, $peer);
                 break;
                 case "замены":
                case "изменения":
                    $message = file_get_contents("changes");
                    sendMessage($message, $peer);
                     break;
                 case 'расписание на завтра':
                 case 'расписание':
                    $now = date("N", $data->object->message->date);
                     if($now == 7 || $now == 6) {
                        $message = "Расписание на понедельник:\n".file_get_contents('days/1');
 
                     } else {
                        $message = 'Расписание на '.$days[$now + 1].":\n".file_get_contents('days/'.($now + 1));
                     }
                     sendMessage($message, $peer);
                     break;
                     case 'расписание на сегодня':
                        $now = date("N", $data->object->message->date);
                         if($now == 7) {
                            $message = "Сегодняя уроков нет :)\nЛадно, расписание на понедельник:\n".file_get_contents('days/1');
 
                         } else {
                            $message = 'Расписание на '.$days[$now].":\n".file_get_contents('days/'.($now));
                         }
                        sendMessage($message, $peer);
                         break;
                     case "все дз":
                     $homework = getAllHomework();
                     $message = "";
                     while($row = $homework->fetchArray(SQLITE3_ASSOC))
                     {
                         $message .= $row["Subject"].": ".$row["Homework"]."\n";
                     }
                     if($message == "")
                     {
                         $message = "Задание отсутствует";
                     }
                     else
                     {
                         $message = "Всё домашнее задание:\n".$message;
                     }
                     
                     sendMessage($message, $peer);
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
                             sendMessage($message, $peer);
                             break;
            }   
        break;
}


