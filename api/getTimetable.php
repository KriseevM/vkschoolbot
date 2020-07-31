<?php
include 'checkAuth.php';

function getNumericTimetable($day)
{
    if ($day < 1 || $day > 6) {
        return false;
    } else {
        $timetable = explode("\n", file_get_contents("../NumTimetable/" . $day));
        for ($i = 0; $i < count($timetable); $i++) {
            $timetable[$i] = intval($timetable[$i]);
        }
        return $timetable;
    }
}
function getTextTimetable($day)
{
    if ($day < 1 || $day > 6) {
        return false;
    } else {
        $timetable = file_get_contents("../TextTimetable/" . $day);
        return $timetable;
    }
}

$res = array(
    'TextTimetable' => array(
        getTextTimetable(1),
        getTextTimetable(2),
        getTextTimetable(3),
        getTextTimetable(4),
        getTextTimetable(5),
        getTextTimetable(6)
    ),
    'NumericTimetable' => array(
        getNumericTimetable(1),
        getNumericTimetable(2),
        getNumericTimetable(3),
        getNumericTimetable(4),
        getNumericTimetable(5),
        getNumericTimetable(6)
    )
);
echo json_encode($res, JSON_UNESCAPED_UNICODE);
