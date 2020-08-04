<?php
use PHPUnit\Framework\TestCase;
include realpath(dirname(__FILE__)).'/../api/API.php';
class APITest extends TestCase
{
    private $user = "DEFAULT";
    private $pass = "1234";
    private $ip = "127.0.0.1";
    
    /**
     * @beforeClass
     */
    public static function prepare()
    {
        $path = realpath(dirname(__FILE__));
        copy($path."/../bot.db", $path."/../bot.db.bak");
    }
    public function testAuth()
    {
        $path = realpath(dirname(__FILE__));
        $db = new SQLite3($path.'/../bot.db');
        $key = API::auth($this->user, $this->pass, $this->ip);
        
        $expected_key = $db->query("SELECT passkey FROM PassKeys WHERE user=\"$this->user\" AND ip=\"$this->ip\"")
            -> fetchArray(SQLITE3_NUM)[0];
        $this->assertEquals($expected_key, $key);
        return $key;
    }
    public function testAuthInvalidUserdata()
    {
        $this->expectExceptionMessage(API::ERROR_INCORRECT_AUTH_DATA);
        API::auth("FAILS", "FAILS", "127.0.0.1");
    }
    /**
     * @depends testAuth
     */
    public function testCheckAuth($key)
    {
        $api = new API($key, $this->ip);
        $this->assertInstanceOf(API::class, $api);
        return $api;
    }
    public function testCheckAuthInvalidKey()
    {
        $this->expectExceptionMessage(API::ERROR_INVALID_KEY);
        $api = new API("FAILED", $this->ip);
    }
    /**
     * @depends testCheckAuth
     */
    public function testAddSubjects(API $api)
    {
        $data = "{\"names\":[\"Subject1\", \"Subject2\"]}";
        $actual = $api->add_subjects_method(json_decode($data));
        $expected = 2;
        $this->assertEquals($expected, $actual);
        return $api;
    }
    /**
     * @depends testCheckAuth
     */
    public function testDoubleAddSubjects(API $api)
    {
        $data = "{\"names\":[\"Subject3\", \"Subject4\"]}";
        $actual = $api->add_subjects_method(json_decode($data));
        $expected = 2;
        $this->assertEquals($expected, $actual);
        $data = "{\"names\":[\"Subject5\", \"Subject6\"]}";
        $actual = $api->add_subjects_method(json_decode($data));
        $expected = 2;
        $this->assertEquals($expected, $actual);
        return $api;
    }
    /**
     * @depends testAddSubjects
     */
    public function testGetSubjects(API $api)
    {
        $expected = [
            ['ID' => 1, 'Name' => 'Subject1'],
            ['ID' => 2, 'Name' => 'Subject2'],
            ['ID' => 3, 'Name' => 'Subject3'],
            ['ID' => 4, 'Name' => 'Subject4'],
            ['ID' => 5, 'Name' => 'Subject5'],
            ['ID' => 6, 'Name' => 'Subject6']
        ];
        $actual = $api->get_subjects_method();
        $this->assertEquals($expected, $actual);
    }
    /**
     * @afterClass
     */
    public static function restoreDatabase()
    {
        $path = realpath(dirname(__FILE__));
        unlink($path."/../bot.db");
        if(copy($path."/../bot.db.bak", $path."/../bot.db"))
        {
            unlink($path."/../bot.db.bak");
        }
    }
}