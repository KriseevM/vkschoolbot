<?php
ini_set('display_errors', 'Off');
// система авторизации с использованием простейшего ключа, формируемого
// из данных клиента и соли, преобразованных через sha-256
// При отправке любого запроса необходим будет параметр key, иначе будет возвращена ошибка
// Для проверки ключа программы скрипты API будут вызывать скрипт checkAuth.php
// Для получения ключа используется данный скрипт
// Для обращения необходимо отправить POST-запрос к данному скрипту на сервере
// и передать следующие параметры:
// user => имя пользователя
// pass => его пароль
// Скртпт проверит наличие пользователя в таблице базы данных с соответствующими полями
// и в случае наличия сгенерирует ключ по имеющимся данным, иначе вернёт ошибку
// После создания ключ помещается в таблицу базы данных. В таблице должны быть поля:
// passkey => ключ (CHAR(64) NOT NULL)
// user => имя пользователя, на которого зарегистрирован ключ. Можно сделать 
//         внешним ключом к столбцу user таблицы с пользователями.
//         Столбец нужен для того, чтобы избежать повторого создания ключа с 
//         одними и теми же параметрами
// ip => ip-адрес (VARCHAR(45) NOT NULL), с которого был создан ключ. 
//       Если ключ отправлен с  другого адреса, то checkAuth.php вернёт ошибку
// expiration_time => время окончания действия запроса. Изначально задаётся на 
//                    текущее время + 30 минут. При каждом обнаружении ключа 
//                    checkAuth.php значение сбрасывается, т.е. задаётся время 
//                    30 минут от момента запроса
// 
// При повторном обращении для авторизации того же пользователя (если срок 
// действия ключа ещё не закончился) будет продлёт и возвращён имеющийся ключ

function Salt() { 
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_-()#@!$%^&*=+.'; 
    $random_string = ''; 
    $n = rand(7, 15);
    for ($i = 0; $i < $n; $i++) { 
        $index = rand(0, strlen($characters) - 1); 
        $random_string .= $characters[$index]; 
    } 
  
    return $random_string; 
} 

$db = new SQLite3("../bot.db");
$user = $_POST["user"];   
$pass = hash('sha256', $_POST["pass"]);
$ip = $_SERVER['REMOTE_ADDR'];
$time = time();
$check_query = "select * from UserData where user=\"$user\" and pass=\"$pass\"";
$res = $db->query($check_query);
if($res->fetchArray(SQLITE3_ASSOC) != false)
{
    $new_time = $time+1800;
    $check_exists_query = "Select passkey from PassKeys where user=\"$user\" and ip=\"$ip\" and expiration_time > $time";
    $check_exists_res = $db->query($check_exists_query);
    if($check_exists_res->fetchArray(SQLITE3_ASSOC) != false)
    {
        $key = $check_exists_res["key"];
        echo "{\"key\":\"$key\"}";
        $reset_time_query = "Update PassKeys Set expiration_time=$new_time where passkey=\"$key\"";
        $db->exec($reset_time_query);
        exit();
    }
    $remove_old_keys_query = "DELETE FROM PassKeys WHERE user=\"$user\" and ip=\"$ip\"";
    $db->exec($remove_old_keys_query);
    $pre_key = Salt().$time.Salt().$ip.Salt().$user.Salt();
    $key = hash('sha256', $pre_key);    
    $add_key_query = "Insert into PassKeys (passkey,user,ip,expiration_time) values(\"$key\",\"$user\",\"$ip\",$new_time)";
    $db->exec($add_key_query) or die('{"error":"Failed to execute SQL query","errorcode":2}');
    echo "{\"key\":\"$key\"}";
}
else 
{
    die("{\"error\":\"Incorrect login or password\",\"errorcode\":3}");
}

