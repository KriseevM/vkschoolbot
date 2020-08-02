<?php
use PHPUnit\Framework\TestCase;
include realpath(dirname(__FILE__)).'/../api/API.php';
class APITest extends TestCase
{
    public function testAuth()
    {
        $path = realpath(dirname(__FILE__));
        $db = new SQLite3($path.'/../bot.db');
        $user = "DEFAULT";
        $pass = "1234";
        $ip = "127.0.0.1";
        $key = API::auth($user, $pass, $ip);
        
        $expected_key = $db->query("SELECT passkey FROM PassKeys WHERE user=\"$user\" AND ip=\"$ip\"")
            -> fetchArray(SQLITE3_NUM)[0];
        $this->assertEquals($expected_key, $key);
        return $key;
    }
    public function testAuthInvalidUserdata()
    {
        $this->expectExceptionMessage(API::ERROR_INCORRECT_AUTH_DATA);
        API::auth("FAILS", "FAILS", "127.0.0.1");
    }
}