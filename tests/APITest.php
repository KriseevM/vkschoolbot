<?php
use PHPUnit\Framework\TestCase;
include 'API.php';
class APITest extends TestCase
{
    public function testAuth()
    {
        $db = new SQLite3('bot.db');
        $user = "DEFAULT";
        $pass = "1234";
        $ip = "127.0.0.1";
        $key = API::auth($user, $pass, $ip);
        
        $expected_key = $db->query("SELECT passkey FROM PassKeys WHERE user=\"$user\" AND ip=\"$ip\"")
            -> fetchArray(SQLITE3_NUM)[0];
        $this->assertEquals($expected_key, $key);
        return $key;
    }
    public function testAuthError()
    {
        $this->expectExceptionMessage(API::ERROR_INCORRECT_AUTH_DATA);
        API::auth("FAILS", "FAILS", "127.0.0.1");
    }
}