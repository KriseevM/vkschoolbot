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

if(!isset($_GET['user']))
{
    die('{"error":"You must specify user parameter","errorcode":7}');
}
else
{
    // Дополнительная проверка ввода
    if (preg_match("/^[\w]+$/", $_GET['user']) === 0)
    {
        die('{"error":"Parameters are incorrect","errorcode":7}');
    }
    $user = $_GET['user'];
    if($user === $auth_user)
    {
        die("{\"error\":\"You can not remove the user you are logged in\",\"errorcode\":7}");
    }
    $query = "DELETE FROM UserData WHERE user=:user;";
    $stmt = $db ->prepare($query);
    $stmt->bindValue(':user',$user);
    $stmt->execute();
    $result = boolval($db->changes());
    echo json_encode(array('deleted' => $result), JSON_UNESCAPED_UNICODE); 
}