<?php
namespace phMysql\tests;

use phMysql\MySQLLink;
use PHPUnit\Framework\TestCase;
/**
 * Integration testing. This class will test the whole library 
 * by integrating all classes in the library. It will be used to execute actual 
 * quires in a real database.
 *
 * @author Ibrahim
 */
class MySQLLinkTest extends TestCase {
    /**
     * @depends testSetDb01
     * @param MySQLLink $conn
     * @test
     */
    public function testAddDataTest00($conn) {
        $q = new UsersQuery();
        $q->insertRecord([
            'user-id' => 33,
            'email' => '33@test.com',
            'name' => 'Test User #33'
        ]);
        $this->assertTrue($conn->executeQuery($q));

        return $conn;
    }
    /**
     * @test
     */
    public function testConnect00() {
        $conn = new MySQLLink('programmingacademia.com', 'root', '123456','32478');
        $this->assertEquals(2002,$conn->getErrorCode());
        $this->assertTrue('No connection could be made because the target machine actively refused it.' == $conn->getErrorMessage() 
                || 'Connection refused' == $conn->getErrorMessage());
    }
    /**
     * @test
     */
    public function testConnect01() {
        $conn = new MySQLLink('programmingacademia.com', 'root', '123456',5543);
        $this->assertEquals(2002,$conn->getErrorCode());
        $this->assertTrue('No connection could be made because the target machine actively refused it.' == $conn->getErrorMessage() 
                || 'Connection refused' == $conn->getErrorMessage());
    }
    /**
     * @test
     */
    public function testConnect02() {
        $conn = new MySQLLink('localhost', 'root', 'gfgdgdg');
        $this->assertEquals(1045,$conn->getErrorCode());
        $this->assertEquals("Access denied for user 'root'@'localhost' (using password: YES)",$conn->getErrorMessage());
    }
    /**
     * @test
     */
    public function testConnect03() {
        $conn = new MySQLLink('localhost', 'root', '123456');
        $this->assertEquals(0,$conn->getErrorCode());
        $this->assertEquals("NO ERRORS",$conn->getErrorMessage());

        return $conn;
    }
    /**
     * @test
     * @param MySQLLink $conn
     * @depends testAddDataTest00
     */
    public function testGetDataTest00($conn) {
        $q = new UsersQuery();
        $q->select([
            'where' => [
                'user-id' => [
                    'values' => [33]
                ]
            ],
            'map-result-to' => '\phMysql\tests\EntityUser'
        ]);
        $this->assertTrue($conn->executeQuery($q));
        $this->assertEquals(1,$conn->rows());
        $obj = $conn->getRow();
        $this->assertTrue($obj instanceof EntityUser);
        $this->assertEquals('ID: [33] Name: [Test User #33] Email: [33@test.com]',$obj.'');
    }
    /**
     * @test
     * @param MySQLLink $conn
     * @depends testAddDataTest00
     */
    public function testGetDataTest01($conn) {
        $q = new UsersQuery();
        $q2 = new ArticleQuery();
        $q3 = $q2->join([
            'right-table' => $q,
            'join-cols' => [
                'author-id' => 'user-id'
            ]
        ]);
        $q3->select([
            'where' => [
                'author-id' => 1
            ]
        ]);
        $r = $conn->executeQuery($q3);

        if ($r === false) {
            print_r("\n".$conn->getErrorCode().': '.$conn->getErrorMessage()."\n");
        }
        $this->assertTrue($r);
        $this->assertEquals(4,$conn->rows());
    }
    /**
     * 
     * @param MySQLLink $conn
     * @depends testConnect03
     * @test
     */
    public function testSetDb00($conn) {
        $this->assertFalse($conn->setDB('not_exist'));
        $this->assertEquals(1049,$conn->getErrorCode());
        $this->assertEquals("Unknown database 'not_exist'",$conn->getErrorMessage());
    }
    /**
     * @test
     */
    public function testSetDb01() {
        $conn = new MySQLLink('localhost', 'root', '123456');
        $this->assertEquals(0,$conn->getErrorCode());
        $this->assertEquals("NO ERRORS",$conn->getErrorMessage());
        $this->assertTrue($conn->setDB('testing_db'));
        $this->assertEquals(0,$conn->getErrorCode());
        $this->assertEquals("NO ERRORS",$conn->getErrorMessage());

        return $conn;
    }
}
