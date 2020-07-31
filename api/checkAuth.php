<?php
ini_set('display_errors', 'Off');
$ip = $_SERVER['REMOTE_ADDR'];
$key = $_SERVER['HTTP_KEY'];
if (!isset($key) || !isset($ip)) {
    die('{"error":"Missing required parameters for authorisation","errorcode":6}');
}
if (!(preg_match("/^[a-f\d]{64}$/", $key) === 1)) {
    die('{"error":"Key has incorrect format","errorcode":6}');
}
$time = time();
$db = new SQLite3("../bot.db");
$check_query = "SELECT expiration_time, user FROM PassKeys WHERE passkey=:key AND ip=:ip";
$check_stmt = $db->prepare($check_query);
$check_stmt->bindValue(':key', $key);
$check_stmt->bindValue(':ip', $ip);
$res = $check_stmt->execute()->fetchArray(SQLITE3_NUM);
if ($res === false) {
    die("{\"error\":\"Key is invalid or your ip address does not match original ip address\",\"errorcode\":4}");
} else {
    $exp_time = $res[0];
    if ($time > $exp_time) {
        $remove_query = "DELETE FROM PassKeys WHERE passkey=:key";
        $remove_stmt = $db->prepare($remove_query);
        $remove_stmt->bindValue(':key', $key);
        $remove_stmt->execute();

        die("{\"error\":\"Key is expired\",\"errorcode\":5}");
    } else {
        $reset_time_query = "UPDATE PassKeys SET expiration_time=:time WHERE passkey=:key";
        $reset_time_stmt = $db->prepare($reset_time_query);
        $reset_time_stmt->bindValue(':time', $time + 1800);
        $reset_time_stmt->bindValue(':key', $key);
        $reset_time_stmt->execute();
        $auth_user = $res[1];

        $authpr_query = "SELECT pr_level FROM UserData WHERE user=:user";
        $authpr_stmt = $db->prepare($authpr_query);
        $authpr_stmt->bindValue(':user', $auth_user);
        $auth_pr = $authpr_stmt->execute()->fetchArray(SQLITE3_NUM)[0];
    }
}
