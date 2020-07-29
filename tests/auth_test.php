<?php

function restore_db()
{
    echo "Restoring database from backup... ";
    if (copy("bot.db.bak","bot.db")) {
        unlink("bot.db.bak");
        echo "Restored successfully!\n";
    }
    else
    {
        log_error("Failed to restore! Try to manually replace bot.db with bot.db.bak\n");
    }
}
function log_error(string $error)
{
    fwrite(STDERR, $error);
}
if(!copy("bot.db", "bot.db.bak"))
{
    log_error("Failed to backup database. Aborting\n");
    die();
}
$auth_data = array(
    'user' => 'DEFAULT',
    'pass' => '1234'
);
$auth_ch = curl_init('localhost/vkschoolbot/api/auth.php');
curl_setopt($auth_ch, CURLOPT_POST, 1);
curl_setopt($auth_ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($auth_ch, CURLOPT_POSTFIELDS, $auth_data);
$data = json_decode(curl_exec($auth_ch));
restore_db();
if($data == null)
{
    log_error("Error: incorrect json in response\n");
    die();
}
else if(isset($data->error))
{
    log_error("Error ({$data->errorcode}): {$data->error}\n");
    die();
}
else if(!isset($data->key) || !(preg_match("/^[a-f\d]{64}$/", $data->key)))
{
    log_error("Error: incorrect key format\n");
    die();
}
else
{
    echo "Test passed!\n";
}

