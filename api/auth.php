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

require_once 'APIInternalInfo.php';
$link = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname) or die("{\"error\":\"Failed to connect to database.\",\"errorcode\":1}");
$user = $_POST["user"];   
$pass = $_POST["pass"];
$ip = $_SERVER['REMOTE_ADDR'];
$time = time();
$check_query = "select * from $dbusernamestable where user=\"$user\" and pass=PASSWORD(\"$pass\")";
$res = mysqli_query($link, $check_query) or die('{"error":"Failed to execute SQL query","errorcode":2}');
$state = (mysqli_num_rows($res) > 0);
if($state)
{
    $new_time = $time+1800;
    $check_exists_query = "Select passkey from $dbkeystable where user=\"$user\" and ip=\"$ip\" and expiration_time > $time";
    $check_exists_res = mysqli_query($link, $check_exists_query) or die('{"error":"Failed to execute SQL query","errorcode":2}');
    if(mysqli_num_rows($check_exists_res) > 0)
    {
        $key = mysqli_fetch_row($check_exists_res)[0];
        echo "{\"key\":\"$key\"}";
        $reset_time_query = "Update $dbkeystable Set expiration_time=$new_time where passkey=\"$key\"";
        mysqli_query($link, $reset_time_query) or die('{"error":"Failed to execute SQL query","errorcode":2}');
        exit();
    }
    $remove_old_keys_query = "DELETE FROM $dbkeystable WHERE user=\"$user\" and ip=\"$ip\"";
    mysqli_query($link, $remove_old_keys_query);
    $pre_key = Salt().$time.Salt().$ip.Salt().$user.Salt();
    $key = hash('sha256', $pre_key);    
    $add_key_query = "Insert into $dbkeystable (passkey,user,ip,expiration_time) values(\"$key\",\"$user\",\"$ip\",$new_time)";
    mysqli_query($link, $add_key_query) or die('{"error":"Failed to execute SQL query","errorcode":2}');
    echo "{\"key\":\"$key\"}";
}
else 
{
    die("{\"error\":\"Incorrect login or password\",\"errorcode\":3}");
}

