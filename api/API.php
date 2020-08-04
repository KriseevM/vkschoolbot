<?php
ini_set('display_errors', 'off');
final class API
{

    /**
     * Thrown on database connection fail.
     * Error code: 1
     */
    public const ERROR_DATABASE_CONNECTION = "Failed to connect to database";
    /**
     * Thrown if sql statement cannot be executed or failed on execution.
     * Error code: 2
     */
    public const ERROR_EXECUTING_SQL = "Failed to execute SQL query";
    /**
     * Thrown where auth data oassed to auth function is not valid.
     * Error code: 3
     */
    public const ERROR_INCORRECT_AUTH_DATA = "Incorrect login or password";
    /**
     * Thrown if key given to constructor is not correct or didnot found in database.
     * Error code: 4
     */
    public const ERROR_INVALID_KEY = "Key is invalid for this IP address";
    /**
     * Thrown if given key is expired.
     * Error code: 5
     */
    public const ERROR_EXPIRED_KEY = "Key is expired";
    /**
     * Thrown if some auth data is missing.
     * Error code: 6
     */
    public const ERROR_MISSING_AUTH_DATA = "Authorisation data is missing";
    /**
     * Thrown when some of the parameters did not pass all checks.
     * Error code: 7 
     */
    public const ERROR_INVALID_PARAMETERS = "Parameters are invalid";
    /**
     * Thrown if changes of timetable files are inaccessible for write.
     * Error code: 8
     */
    public const ERROR_FILE_INACCESSIBLE = "Can't access file";
    /**
     * Thrown when user with pr_level = 1 tries to run method for users with pr_level = 2.
     * Error code: 9 
     */
    public const ERROR_LOW_PRIVILEGES = "Method can not be executed by this user";

    private $path;
    private $pr_level;
    private SQLite3 $db;
    private static function random_string(): string
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
    public function __construct(string $key, string $ip)
    {
        $this->path = realpath(dirname(__FILE__));
        if (!(preg_match("/^[a-f\d]{64}$/", $key) === 1)) {
            throw new Exception(API::ERROR_INVALID_KEY, 4);
        }
        $time = time();
        $this->db = new SQLite3($this->path . "/../bot.db");
        $check_query = "SELECT expiration_time, user FROM PassKeys WHERE passkey=:key AND ip=:ip";
        $check_stmt = $this->db->prepare($check_query);
        $check_stmt->bindValue(':key', $key);
        $check_stmt->bindValue(':ip', $ip);
        $res = $check_stmt->execute()->fetchArray(SQLITE3_NUM);
        if ($res === false) {
            throw new Exception(API::ERROR_INVALID_KEY, 4);
        } else {
            $exp_time = $res[0];
            if ($time > $exp_time) {
                $remove_query = "DELETE FROM PassKeys WHERE passkey=:key";
                $remove_stmt = $this->db->prepare($remove_query);
                $remove_stmt->bindValue(':key', $key);
                $remove_stmt->execute();

                throw new Exception(API::ERROR_EXPIRED_KEY, 5);
            } else {
                $reset_time_query = "UPDATE PassKeys SET expiration_time=:time WHERE passkey=:key";
                $reset_time_stmt = $this->db->prepare($reset_time_query);
                $reset_time_stmt->bindValue(':time', $time + 1800);
                $reset_time_stmt->bindValue(':key', $key);
                $reset_time_stmt->execute();
                $auth_user = $res[1];
                $authpr_query = "SELECT pr_level FROM UserData WHERE user=:user";
                $authpr_stmt = $this->db->prepare($authpr_query);
                $authpr_stmt->bindValue(':user', $auth_user);
                $this->pr_level = $authpr_stmt->execute()->fetchArray(SQLITE3_NUM)[0];
            }
        }
    }
    public static function auth(string $user, string $pass, string $ip): string
    {
        $path = realpath(dirname(__FILE__));
        $db = new SQLite3($path . "/../bot.db");
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

    private function add_subjects(array $names)
    {
        $placeholders = rtrim(str_repeat('(?), ', count($names)), ', ');
        $query = "INSERT INTO Homeworkdata (Subject) VALUES $placeholders";
        $stmt = $this->db->prepare($query);
        for ($i = 1; $i <= count($names); $i++) {
            $stmt->bindValue($i, $names[$i - 1]);
        }
        $stmt->execute();
        $result = $this->db->changes();
        return $result;
    }

    /**
     * Добавление предметов в базу данных
     * $inputdata - декодированный из json объект
     */
    public function add_subjects_method(object $inputdata): int
    {
        if($this->pr_level < 2)
        {
            throw new Exception(API::ERROR_LOW_PRIVILEGES, 9);
        }
        $schema = (object)[
            'type' => 'object',
            'properties' => (object)[
                'names' => (object)[
                    'type' => 'array',
                    'items' => (object)[
                        'type' => 'string',
                        'pattern' => '^[\\wА-Яа-яЁё\\s-]{1,50}$'
                    ],
                    'required' => true
                ]
            ]
        ];
        API::validate($schema, $inputdata);
        return $this->add_subjects($inputdata->names);
    }
    private static function validate(object $schema, object $data)
    {
        $validator = new JsonSchema\Validator();
        $validator->validate($data, $schema);
        if (!$validator->isValid()) {
            $message = "Failed to validate parameters: ";
            foreach ($validator->getErrors() as $error) {
                $message .= $error['message'] . ", ";
            }
            $message = rtrim($message, ", ");
            throw new Exception($message, 7);
        }
    }
}
