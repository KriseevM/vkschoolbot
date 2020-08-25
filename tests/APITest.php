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
        copy($path."/../changes", $path."/../changes.bak");
        copy($path."/../NumericChanges", $path."/../NumericChanges.bak");
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
        return $api;
    }
    /**
     * @depends testAddSubjects
     */
    public function testUpdateHomework(API $api)
    {
        $data = [
            'ID' => 1,
            'Homework' => 'Homework for subject 1'
        ];
        $expected = true;
        $actual = $api->update_homework_method((object) $data);
        $this->assertEquals($expected, $actual);
        return $api;      
    }
    /**
     * @depends testUpdateHomework
     */
    public function testGetHomework(API $api)
    {
        $expected = [
            'ID' => 1,
            'Homework' => 'Homework for subject 1'
        ];
        $actual = $api->get_homework_method(1);
        $this->assertEquals($expected, $actual);
        return $api;
    }
    /**
     * @depends testGetHomework
     */
    public function testDeleteSubjects(API $api)
    {
        $data = [
            'IDs' => [5,6]
        ];
        $expected = 2;
        $actual = $api->delete_subjects_method($data);
        $this->assertEquals($expected, $actual);
    }
    /**
     * @depends testCheckAuth
     */
    public function testAddUser(API $api)
    {
        $data = (object)[
            'user' => 'User1',
            'pass' => 'User1pass',
            'pr' => 2
        ];
        $result = $api->add_user_method($data);
        $this->assertTrue($result);
        return $api;
    }
    /**
     * @depends testAddUser
     */
    public function testDeleteUser(API $api)
    {
        $result = $api->delete_user_method("User1");
        $this->assertTrue($result);
    }
    /**
     * @depends testCheckAuth
     */
    public function testUpdateChanges(API $api)
    {
        $data = (object)[
            'TextChanges' => 'New changes',
            'NumericChanges' => [
                8,7,6,5,4,3,2,1
            ]
        ];
        $result = $api->update_changes_method($data);
        $this->assertTrue($result);
        return $api;
    }
    /**
     * @depends testUpdateChanges
     */
    public function testGetChanges(API $api)
    {
        $expected = [
            'TextChanges' => 'New changes',
            'NumericChanges' => [
                8,7,6,5,4,3,2,1
            ]
        ];
        $actual = $api->get_changes_method();
        $this->assertEquals($expected, $actual);
    }
    /**
     * @depends testCheckAuth
     */
    public function testUpdateTimetable(API $api)
    {
        $data = (object) [
            'TextTimetable' =>[
                "Timetable 1",
                "Timetable 2",
                "Timetable 3",
                "Timetable 4",
                "Timetable 5",
                "Timetable 6"
            ],
            'NumericTimetable' =>[
                [6, 5, 4, 3, 2, 1],
                [7, 6, 5, 4, 3, 2],
                [8, 7, 6, 5, 4, 3],
                [3, 2, 1, 6, 5, 4],
                [2, 1, 6, 5, 4, 3],
                [1, 6, 5, 4, 3, 2]
            ]
        ];
        $result = $api->update_timetable_method($data);
        $this->assertTrue($result);
        return $api;
    }
    /**
     * @depends testUpdateTimetable
     */
    public function testGetTimetable(API $api)
    {
        $expected = [
            'TextTimetable' =>[
                "Timetable 1",
                "Timetable 2",
                "Timetable 3",
                "Timetable 4",
                "Timetable 5",
                "Timetable 6"
            ],
            'NumericTimetable' =>[
                [6, 5, 4, 3, 2, 1],
                [7, 6, 5, 4, 3, 2],
                [8, 7, 6, 5, 4, 3],
                [3, 2, 1, 6, 5, 4],
                [2, 1, 6, 5, 4, 3],
                [1, 6, 5, 4, 3, 2]
            ]
        ];
        $actual = $api->get_timetable_method();
        $this->assertEquals($expected, $actual);
    }
    /**
     * @afterClass
     */
    public static function restore()
    {
        $path = realpath(dirname(__FILE__));
        if(copy($path."/../bot.db.bak", $path."/../bot.db"))
        {
            unlink($path."/../bot.db.bak");
        }
        if(copy($path."/../changes.bak", $path."/../changes"))
        {
            unlink($path."/../changes.bak");
        }
        if(copy($path."/../NumericChanges.bak", $path."/../NumericChanges"))
        {
            unlink($path."/../NumericChanges.bak");
        }
    }
}
