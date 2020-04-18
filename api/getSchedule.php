<?php
function getNumSchedule($day) {
    if($day < 1 || $day > 6) {
         return false;
     } else {
        $schedule = explode ("\n", file_get_contents("../NumericDays/".$day));
        for($i = 0; $i < count($schedule); $i++)
        {
            $schedule[$i] = intval($schedule[$i]);
        }
         return $schedule;
     }
}
function getTextSchedule($day) {
    if($day < 1 || $day > 6) {
         return false;
     } else {
        $tschedule = file_get_contents("../days/".$day);
         return $tschedule;
     }
}

$ip = $_SERVER['REMOTE_ADDR'];
if(!isset($_GET['key'])) 
{
    die('{"error":"Key is required for authorisation","errorcode":6}');
}
$key = $_GET['key'];
include 'checkAuth.php';
$res = array(
    'TextSchedule' => array(
        getTextSchedule(1),
        getTextSchedule(2),
        getTextSchedule(3),
        getTextSchedule(4),
        getTextSchedule(5),
        getTextSchedule(6)
    ),
    'NumericSchedule' => array(
        getNumSchedule(1),
        getNumSchedule(2),
        getNumSchedule(3),
        getNumSchedule(4),
        getNumSchedule(5),
        getNumSchedule(6)
    )
);
echo json_encode($res, JSON_UNESCAPED_UNICODE);

