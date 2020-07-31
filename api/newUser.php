<?php
include 'checkAuth.php';
if($auth_pr !== 2)
{
    die("{\"error\":\"You are not allowed to use this method\",\"errorcode\":9}");
}
// Переменная $db приходит из файла checkAuth.php. 
// Но в этом файле происходит запись в базу, что влияет на вывод метода changes()
$db->close();
$db->open("../bot.db");


if(!(isset($_GET['user']) && isset($_GET['pass']) && isset($_GET['pr'])))
{
    die('{"error":"You must specify user, pass and pr parameters","errorcode":7}');
}
else
{
    // Дополнительная проверка ввода
    if (!(($_GET['pr'] == 1 || $_GET['pr'] == 2) && preg_match("/^[\w]+$/", $_GET['user']) === 1))
    {
        die('{"error":"Parameters are incorrect","errorcode":7}');
    }
    $user = $_GET['user'];
    $pass = hash("sha256", $_GET['pass']);
    $pr_level=$_GET['pr'];
    $query = "INSERT INTO UserData (user, pass, pr_level) VALUES(:user,:pass,:pr_level);";
    $stmt = $db->prepare($query);
    $stmt->bindValue(':user', $user);
    $stmt->bindValue(':pass', $pass);
    $stmt->bindValue(':pr_level', $pr_level);
    $result = $stmt->execute();
    $result = boolval($db->changes());
    echo json_encode(array('created' => $result), JSON_UNESCAPED_UNICODE);
}