<?php
include 'checkAuth.php';

function getNumericSchedule($day)
{
    if ($day < 1 || $day > 6) {
        return false;
    } else {
        $schedule = explode("\n", file_get_contents("../NumericDays/" . $day));
        for ($i = 0; $i < count($schedule); $i++) {
            $schedule[$i] = intval($schedule[$i]);
        }
        return $schedule;
    }
}
function getTextSchedule($day)
{
    if ($day < 1 || $day > 6) {
        return false;
    } else {
        $tschedule = file_get_contents("../days/" . $day);
        return $tschedule;
    }
}

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
        getNumericSchedule(1),
        getNumericSchedule(2),
        getNumericSchedule(3),
        getNumericSchedule(4),
        getNumericSchedule(5),
        getNumericSchedule(6)
    )
);
echo json_encode($res, JSON_UNESCAPED_UNICODE);
