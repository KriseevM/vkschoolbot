<?php

ini_set('display_errors', 'off');
final class API
{
    const ERROR_DATABASE_CONNECTION = "Failed to connect to database"; // errorcode 1
    const ERROR_EXECUTING_SQL = "Failed to execute SQL query"; // errorcode 2
    const ERROR_INCORRECT_AUTH_DATA = "Incorrect login or password"; // errorcode 3
    const ERROR_INVALID_KEY = "Key is invalid for this IP address"; // errorcode 4
    const ERROR_EXPIRED_KEY = "Key is expired"; // errorcode 5
    const ERROR_MISSING_AUTH_DATA = "Authorisation data is missing"; // errorcode 6
    const ERROR_INVALID_PARAMETERS = "Parameters are invalid"; // errorcode 7
    const ERROR_FILE_INACCESSIBLE = "Can't access file"; // errorcode 8
    const ERROR_LOW_PRIVILEGES = "Method can not be executed by this user"; // errorcode 9

    

    private static function random_string() : string
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
    public function __construct(string $key)
    {
    }
    public static function auth(string $user, string $pass, string $ip): string
    {
        $path = realpath(dirname(__FILE__));
        $db = new SQLite3($path."/../bot.db");
        if (!preg_match("/^[\w]+$/", $user)) {
            throw new Exception(API::ERROR_INCORRECT_AUTH_DATA, 3);
        }
        $time = time();
        $check_query = "SELECT * FROM UserData WHERE user=:user AND pass=:pass";
        $stmt = $db->prepare($check_query);
        $stmt->bindValue(':user', $user);
        $stmt->bindValue(':pass', hash('sha256', $pass));
        $res = $stmt->execute();
        if ($res->fetchArray(SQLITE3_ASSOC) != false) {
            $new_time = $time + 1800;
            $check_exists_query = "SELECT passkey FROM PassKeys WHERE user=:user AND ip=:ip AND expiration_time > :time";
            $check_exists_stmt = $db->prepare($check_exists_query);
            $check_exists_stmt->bindValue(':user', $user);
            $check_exists_stmt->bindValue(':ip', $ip);
            $check_exists_stmt->bindValue(':time', $time);
            $check_exists_res = $check_exists_stmt->execute()->fetchArray(SQLITE3_ASSOC);
            if ($check_exists_res != false) {
                $key = $check_exists_res["passkey"];
                $reset_time_query = "UPDATE PassKeys SET expiration_time=:new_time WHERE passkey=:key";
                $reset_time_stmt = $db->prepare($reset_time_query);
                $reset_time_stmt->bindValue(':key', $key);
                $reset_time_stmt->bindValue(':new_time', $new_time);
                $reset_time_stmt->execute();
                return $key;
            }
            // Удаляются все ключи, которые не были включены в результат только из-за времени действия
            $remove_old_keys_query = "DELETE FROM PassKeys WHERE user=:user AND ip=:ip";
            $remove_old_keys_stmt = $db->prepare($remove_old_keys_query);
            $remove_old_keys_stmt->bindValue(':user', $user);
            $remove_old_keys_stmt->bindValue(':ip', $ip);
            $remove_old_keys_stmt->execute();
            $pre_key = API::random_string() . $time . API::random_string() . $ip . API::random_string() . $user . API::random_string();
            $key = hash('sha256', $pre_key);
            $add_key_query = "INSERT INTO PassKeys (passkey,user,ip,expiration_time) "
                . "VALUES (:key,:user,:ip,:new_time)";
            $add_key_stmt = $db->prepare($add_key_query);
            $add_key_stmt->bindValue(':key', $key);
            $add_key_stmt->bindValue(':user', $user);
            $add_key_stmt->bindValue(':ip', $ip);
            $add_key_stmt->bindValue(':new_time', $new_time);
            $add_key_stmt->execute();
            return $key;
        } else {
            throw new Exception(API::ERROR_INCORRECT_AUTH_DATA, 3);
        }
    }
}
