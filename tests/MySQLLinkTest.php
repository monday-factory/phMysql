<?php
namespace phMysql\tests;

use phMysql\MySQLLink;
use PHPUnit\Framework\TestCase;
use phMysql\entity\User;
use phMysql\MySQLQuery;
/**
 * Integration testing. This class will test the whole library 
 * by integrating all classes in the library. It will be used to execute actual 
 * quires in a real database.
 *
 * @author Ibrahim
 */
class MySQLLinkTest extends TestCase {
    /**
     * @test
     * @depends testSetDb01
     * @param MySQLLink $conn Description
     */
    public function testOtherQuery00($conn) {
        $query = new MySQLQuery();
        $query->schemaViewsCount('testing_db');
        $this->assertTrue($conn->executeQuery($query));
        $result = $conn->getRow()['views_count'];
        $this->assertEquals(0, $result);
    }
    /**
     * @test
     * @depends testSetDb01
     * @param MySQLLink $conn Description
     */
    public function testOtherQuery01($conn) {
        $query = new MySQLQuery();
        $query->schemaTablesCount('testing_db');
        $this->assertTrue($conn->executeQuery($query));
        $result = $conn->getRow()['tables_count'];
        $this->assertEquals(2, $result);
    }
    /**
     * @test
     * @depends testSetDb01
     * @param MySQLLink $conn Description
     */
    public function testOtherQuery02($conn) {
        $query = new MySQLQuery();
        $custom = 'select x from y;';
        $query->setQuery($custom, 'select');
        $this->assertFalse($conn->executeQuery($query));
        $errCode = $conn->getErrorCode();
        $errMsg = $conn->getErrorMessage();
        $this->assertEquals(1146, $errCode);
        $this->assertEquals("Table 'testing_db.y' doesn't exist", $errMsg);
    }
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
            'name' => 'Test User #33',
            'is-active' => false
        ]);
        $this->assertTrue($conn->executeQuery($q));

        return $conn;
    }
    /**
     * @test
     */
    public function testConnect00() {
        $conn = new MySQLLink('programmingacademia.com', 'root', '123456','32478');
        $this->assertEquals(32478,$conn->getPortNumber());
        $this->assertEquals('programmingacademia.com',$conn->getHost());
        $this->assertEquals('root',$conn->getUsername());
        $this->assertEquals(2002,$conn->getErrorCode());
        $errMsg = $conn->getErrorMessage();
        $bool = $errMsg = 'No connection could be made because the target machine actively refused it.' 
                || $errMsg = 'Connection refused';
        $this->assertTrue($bool);
    }
    /**
     * @test
     */
    public function testConnect01() {
        $conn = new MySQLLink('programmingacademia.com', 'root', '123456',5543);
        $this->assertEquals(2002,$conn->getErrorCode());
        $errMesg = $conn->getErrorMessage();
        $check = $errMesg == 'No connection could be made because the target machine actively refused it.' ||
                $errMesg == 'Connection refused';
        $this->assertTrue($check);
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
     * @depends testSetDb01
     * @param MySQLLink $conn Description
     */
    public function testGetCol00($conn) {
        $q2 = new ArticleQuery();
        $q2->select([
            'columns' => [
                'title','content'
            ]
        ]);
        $this->assertEquals(-1,$conn->rows());
        $result = $conn->executeQuery($q2);
        $this->assertTrue($result);
        $this->assertEquals(20,$conn->rows());
        $data = $conn->getColumn('random');
        $this->assertEquals(\phMysql\MySQLTable::NO_SUCH_COL,$data);
        $col1Data = $conn->getColumn('title');
        $this->assertEquals(20, count($col1Data));
        $col2Data = $conn->getColumn('content');
        $this->assertEquals(20, count($col2Data));
        $col3Data = $conn->getColumn('author-id');
        $this->assertEquals(0, count($col3Data));
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
        $this->assertFalse($obj->getIsActive());
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
                'author-id' => 2
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
     * @depends testAddDataTest00
     * @param MySQLLink $conn
     */
    public function testGetData02($conn) {
        $q = new UsersQuery();
        $q->select([
            'where'=>[
                'user-id'=>1
            ],
            'map-result-to'=>'phMysql\\entity\\User'
        ]);
        $r = $conn->executeQuery($q);
        $this->assertTrue($r);
        $this->assertEquals(1, $conn->rows());
        $user = $conn->nextRow();
        $this->assertTrue($user instanceof User);
        $this->assertEquals(1, $user->getUserId());
        $this->assertTrue($user->isActive());
        return $conn;
    }
    /**
     * @depends testAddDataTest00
     * @param MySQLLink $conn
     */
    public function testGetData03($conn) {
        $q = new UsersQuery();
        $q->insertRecord([
            'user-id'=>100,
            'is-active'=>false,
            'name'=>'Not Active',
            'email'=>'x@yy.xz'
        ]);
        $r = $conn->executeQuery($q);
        $this->assertTrue($r);
        $q->select([
            'where'=>[
                'user-id'=>100
            ],
            'map-result-to'=>'phMysql\\entity\\User'
        ]);
        $r = $conn->executeQuery($q);
        $this->assertTrue($r);
        $this->assertEquals(1, $conn->rows());
        $user = $conn->nextRow();
        $this->assertTrue($user instanceof User);
        $this->assertEquals(100, $user->getUserId());
        $this->assertFalse($user->isActive());
    }
    /**
     * @depends testSetDb01
     * @param MySQLLink $conn
     */
    public function testNextRow00($conn) {
        $q2 = new ArticleQuery();
        $q2->select([
            'where' => [
                'author-id' => 1
            ]
        ]);
        $result = $conn->executeQuery($q2);
        $this->assertTrue($result);
        $index = 1;

        while ($row = $conn->nextRow()) {
            $this->assertEquals('User # 1 Article #'.$index,$row['title']);
            $index++;
        }
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
        $this->assertEquals(-1,$conn->rows());
    }
    /**
     * @test
     */
    public function testSetDb01() {
        $conn = new MySQLLink('localhost', 'root', '123456');
        $this->assertEquals(0,$conn->getErrorCode());
        $this->assertEquals("NO ERRORS",$conn->getErrorMessage());
        $this->assertTrue($conn->setDB('testing_db'));
        $this->assertEquals('testing_db',$conn->getDBName());
        $this->assertEquals(0,$conn->getErrorCode());
        $this->assertEquals("NO ERRORS",$conn->getErrorMessage());
        $this->assertEquals(-1,$conn->rows());
        print_r("\nDB Set to ".$conn->getDBName()."\n");
        return $conn;
    }
}
