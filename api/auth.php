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

function random_string()
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_-()#@!$%^&*=+.';
    $random_string = '';
    $n = rand(5, 10);
    for ($i = 0; $i < $n; $i++) {
        $index = rand(0, strlen($characters) - 1);
        $random_string .= $characters[$index];
    }

    return $random_string;
}

$db = new SQLite3("../bot.db");
$user = $_POST["user"];
if (!preg_match("/^[\w]+$/", $user)) {
    die("{\"error\":\"Incorrect login or password\",\"errorcode\":3}");
}

$pass = hash('sha256', $_POST["pass"]);
$ip = $_SERVER['REMOTE_ADDR'];
$time = time();
$check_query = "SELECT * FROM UserData WHERE user=:user AND pass=:pass";
$stmt = $db->prepare($check_query);
$stmt->bindValue(':user', $user);
$stmt->bindValue(':pass', $pass);
$res = $stmt->execute();

if ($res->fetchArray(SQLITE3_ASSOC) != false) {
    $new_time = $time + 1800;
    $check_exists_query = "SELECT passkey FROM PassKeys WHERE user=:user AND ip=:ip AND expiration_time > :time";
    $check_exists_stmt = $db->prepare($check_exists_query);
    $check_exists_stmt->bindValue(':user', $user);
    $check_exists_stmt->bindValue(':ip', $ip);
    $check_exists_stmt->bindValue(':time', $time);
    $check_exists_res = $check_exists_stmt->execute()
        ->fetchArray(SQLITE3_ASSOC);
    if ($check_exists_res != false) {
        $key = $check_exists_res["passkey"];
        echo "{\"key\":\"$key\"}";
        $reset_time_query = "UPDATE PassKeys SET expiration_time=:new_time WHERE passkey=:key";
        $reset_time_stmt = $db->prepare($reset_time_query);
        $reset_time_stmt->bindValue(':key', $key);
        $reset_time_stmt->bindValue(':new_time', $new_time);
        $reset_time_stmt->execute();
        exit();
    }
    $remove_old_keys_query = "DELETE FROM PassKeys WHERE user=:user AND ip=:ip";
    $remove_old_keys_stmt = $db->prepare($remove_old_keys_query);
    $remove_old_keys_stmt->bindValue(':user', $user);
    $remove_old_keys_stmt->bindValue(':ip', $ip);
    $remove_old_keys_stmt->execute();
    $pre_key = random_string() . $time . random_string() . $ip . random_string() . $user . random_string();
    $key = hash('sha256', $pre_key);
    $add_key_query = "INSERT INTO PassKeys (passkey,user,ip,expiration_time) "
        . "VALUES (:key,:user,:ip,:new_time)";
    $add_key_stmt = $db->prepare($add_key_query);
    $add_key_stmt->bindValue(':key', $key);
    $add_key_stmt->bindValue(':user', $user);
    $add_key_stmt->bindValue(':ip', $ip);
    $add_key_stmt->bindValue(':new_time', $new_time);
    $add_key_stmt->execute();
    echo "{\"key\":\"$key\"}";
} else {
    die("{\"error\":\"Incorrect login or password\",\"errorcode\":3}");
}
