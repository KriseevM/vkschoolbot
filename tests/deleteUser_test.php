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
$time = time()+10;
$db = new SQLite3('bot.db');
$test_key = "daabd3a8ab2db21b84593afecbf3f6dc739e357e1da7a9e5d3b005d9e004766c";
$db->exec("INSERT INTO PassKeys (passkey, user, ip, expiration_time) VALUES (\"$test_key\", \"DEFAULT\", \"127.0.0.1\", $time)");
$db->exec("INSERT INTO UserData (user, pass, pr_level) VALUES (\"User1\", \"qwerty\", 1)");
$params = http_build_query(array('user' => 'User1'));
$ch = curl_init('localhost/vkschoolbot/api/deleteUser.php?'.$params);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["key: $test_key"]);
curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
$result = curl_exec($ch);
$true_result = json_encode(array('deleted' => true));

if($result == $true_result)
{
    restore_db();
    echo "OK\n";
}
else {
    log_error("Test failed\n");
    restore_db();
}