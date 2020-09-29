<?php
namespace phMysql\tests;

use webfiori\phMysql\JoinTable;
use webfiori\phMysql\MySQLQuery;
use webfiori\phMysql\MySQLTable;
use PHPUnit\Framework\TestCase;
/**
 * Description of JoinTest
 *
 * @author Ibrahim
 */
class JoinTest extends TestCase {
    /**
     * @test
     */
    public function testConstructor00() {
        $table = new JoinTable(null, null);
        $this->assertEquals('left_table',$table->getLeftTable()->getName());
        $this->assertEquals('right_table',$table->getRightTable()->getName());
        $this->assertEquals('left',$table->getJoinType());
        $this->assertNull($table->getJoinCondition());
    }
    /**
     * @test
     */
    public function testConstructor01() {
        $leftTable = new MySQLTable('a_left_table');
        $leftTable->addDefaultCols();
        $rightTable = new MySQLTable('a_right_table');
        $rightTable->addDefaultCols();
        $joinTable = new JoinTable($leftTable, $rightTable, 'JoinTable');
        $this->assertEquals('a_left_table',$joinTable->getLeftTable()->getName());
        $this->assertEquals('a_right_table',$joinTable->getRightTable()->getName());
        $keys = $joinTable->colsKeys();
        $this->assertEquals(6,count($keys));
        $this->assertTrue($joinTable->hasColumn('left-id'));
        $this->assertTrue($joinTable->hasColumn('left-created-on'));
        $this->assertTrue($joinTable->hasColumn('left-last-updated'));
        $this->assertTrue($joinTable->hasColumn('right-id'));
        $this->assertTrue($joinTable->hasColumn('right-created-on'));
        $this->assertTrue($joinTable->hasColumn('right-last-updated'));

        $this->assertEquals('left_id',$joinTable->getCol('left-id')->getName());
        $this->assertEquals('left_created_on',$joinTable->getCol('left-created-on')->getName());
        $this->assertEquals('left_last_updated',$joinTable->getCol('left-last-updated')->getName());
        $this->assertEquals('right_id',$joinTable->getCol('right-id')->getName());
        $this->assertEquals('right_created_on',$joinTable->getCol('right-created-on')->getName());
        $this->assertEquals('right_last_updated',$joinTable->getCol('right-last-updated')->getName());

        return $joinTable;
    }
    public function testJoin00() {
        $query0 = new MySQLQuery('users');
        $query0->getTable()->addColumns([
            'user-id' => [
                'is-primary' => true,
                'size' => 15
            ],
            'created-on' => [
                'datatype' => 'timestamp',
                'default' => 'current_timestamp'
            ],
            'password' => [
                'size' => 64
            ],
            'display-name' => [
                'size' => 30
            ],
            'last-login' => [
                'datatype' => 'datetime',
                'is-null' => true
            ],
            'last-updated' => [
                'datatype' => 'datetime',
                'is-null' => true
            ]
        ]);
        $query1 = new MySQLQuery('user_articles');
        $query1->getTable()->addColumns([
            'article-id' => [
                'is-primary' => true,
                'size' => '10'
            ],
            'title' => [
                'size' => 150
            ],
            'content' => [
                'size' => 5000
            ],
            'created-on' => [
                'datatype' => 'timestamp',
                'default' => 'current_timestamp'
            ],
            'last-updated' => [
                'datatype' => 'datetime',
                'is-null' => true
            ],
            'user-id' => [
                'is-primary' => true,
                'size' => 15
            ],
        ]);
        $this->assertEquals(6,count($query0->getTable()->getColumns()));
        $this->assertEquals(6,count($query1->getTable()->getColumns()));
        $joinQuery = $query0->join([
            'right-table' => $query1,
            'join-cols' => [
                'user-id' => 'user-id'
            ],
            'join-type' => 'right',
            'alias' => 'UsersArticles'
        ]);
        $joinQuery->select();
        $this->assertEquals('select * from ('
                .'select '."\n"
                .'users.user_id as left_user_id,'."\n"
                .'users.created_on as left_created_on,'."\n"
                .'users.password,'."\n"
                .'users.display_name,'."\n"
                .'users.last_login,'."\n"
                .'users.last_updated as left_last_updated,'."\n"
                .'user_articles.article_id,'."\n"
                .'user_articles.title,'."\n"
                .'user_articles.content,'."\n"
                .'user_articles.created_on as right_created_on,'."\n"
                .'user_articles.last_updated as right_last_updated,'."\n"
                .'user_articles.user_id as right_user_id'."\n"
                .'from users right join user_articles'."\n"
                .'on users.user_id = user_articles.user_id)'."\n"
                .'as UsersArticles;',$joinQuery->getQuery());
        $joinQuery->select([
            'columns' => [
                'user-id'
            ]
        ]);
        $this->assertEquals('select * from ('
                .'select '."\n"
                .'users.user_id as left_user_id'."\n"
                .'from users right join user_articles'."\n"
                .'on users.user_id = user_articles.user_id)'."\n"
                .'as UsersArticles;',$joinQuery->getQuery());
        $joinQuery->select([
            'columns' => [
                'user-id' => 'a_user_id'
            ]
        ]);
        $this->assertEquals('select * from ('
                .'select '."\n"
                .'users.user_id as a_user_id'."\n"
                .'from users right join user_articles'."\n"
                .'on users.user_id = user_articles.user_id)'."\n"
                .'as UsersArticles;',$joinQuery->getQuery());
        $joinQuery->select([
            'columns' => [
                'user-id' => 'a_user_id',
                'right' => [
                    'user-id' => 'x_id',
                    'created-on'
                ]
            ]
        ]);
        $this->assertEquals('select * from ('
                .'select '."\n"
                .'user_articles.user_id as x_id,'."\n"
                .'user_articles.created_on as right_created_on,'."\n"
                .'users.user_id as a_user_id'."\n"
                .'from users right join user_articles'."\n"
                .'on users.user_id = user_articles.user_id)'."\n"
                .'as UsersArticles;',$joinQuery->getQuery());
        $joinQuery->select([
            'columns' => [
                'user-id' => 'a_user_id',
                'right' => [
                    'user-id' => 'x_id',
                    'created-on'
                ]
            ],
            'limit' => 6
        ]);
        $this->assertEquals('select * from ('
                .'select '."\n"
                .'user_articles.user_id as x_id,'."\n"
                .'user_articles.created_on as right_created_on,'."\n"
                .'users.user_id as a_user_id'."\n"
                .'from users right join user_articles'."\n"
                .'on users.user_id = user_articles.user_id)'."\n"
                .'as UsersArticles limit 6;',$joinQuery->getQuery());
        $joinQuery->select([
            'columns' => [
                'user-id' => 'a_user_id',
                'right' => [
                    'user-id' => 'x_id',
                    'created-on'
                ]
            ],
            'limit' => 6,
            'offset' => 770
        ]);
        $this->assertEquals('select * from ('
                .'select '."\n"
                .'user_articles.user_id as x_id,'."\n"
                .'user_articles.created_on as right_created_on,'."\n"
                .'users.user_id as a_user_id'."\n"
                .'from users right join user_articles'."\n"
                .'on users.user_id = user_articles.user_id)'."\n"
                .'as UsersArticles limit 6 offset 770;',$joinQuery->getQuery());
    }
    /**
     * 
     * @param MySQLQuery $query
     * @depends testSelect01
     */
    public function testJoinLevel2($query) {
        $anotherJoin = $query->join([
            'right-table' => $query->getTable()->getRightTable(),
            'join-cols' => [
                'main-user-id' => 'user-id'
            ],
            'join-type' => 'join',
            'alias' => 'MoreJoins'
        ]);
        $anotherJoin->select();
        $this->assertEquals("select * from (select \n"
                ."NewTable.left_user_id,\n"
                ."NewTable.right_user_id,\n"
                ."NewTable.article_title as left_article_title,\n"
                ."user_articles.user_id,\n"
                ."user_articles.article_title as right_article_title\n"
                ."from (select \n"
                ."users.user_id as left_user_id,\n"
                ."user_articles.user_id as right_user_id,\n"
                ."user_articles.article_title\n"
                ."from users right join user_articles\n"
                ."on users.user_id = user_articles.user_id) as NewTable join user_articles\n"
                ."on NewTable.left_user_id = user_articles.user_id)\n"
                ."as MoreJoins;",$anotherJoin->getQuery());
    }
    public function testJoinNewKeys00() {
        $query0 = new MySQLQuery('users');
        $query0->getTable()->addColumns([
            'user-id' => [
                'is-primary' => true,
                'size' => 15
            ],
            'created-on' => [
                'datatype' => 'timestamp',
                'default' => 'current_timestamp'
            ]
        ]);
        $query1 = new MySQLQuery('user_articles');
        $query1->getTable()->addColumns([
            'article-id' => [
                'is-primary' => true,
                'size' => '10'
            ],
            'user-id' => [
                'is-primary' => true,
                'size' => 15
            ],
        ]);
        $joinQuery = $query0->join([
            'right-table' => $query1,
            'join-cols' => [
                'user-id' => 'user-id'
            ],
            'join-type' => 'right',
            'alias' => 'UsersArticles',
            'keys-map' => [
                'left' => [
                    'user-id' => 'main-user-id'
                ]
            ]
        ]);
        $this->assertTrue($joinQuery->getTable()->hasColumn('main-user-id'));
        $joinQuery->select([
            'where' => [
                'main-user-id' => 77
            ]
        ]);
        $this->assertEquals('select * from ('
                .'select '."\n"
                .'users.user_id as left_user_id,'."\n"
                .'users.created_on,'."\n"
                .'user_articles.article_id,'."\n"
                .'user_articles.user_id as right_user_id'."\n"
                .'from users right join user_articles'."\n"
                .'on users.user_id = user_articles.user_id)'."\n"
                .'as UsersArticles where UsersArticles.left_user_id = \'77\';',$joinQuery->getQuery());
        $joinQuery->select([
            'where' => [
                'main-user-id' => 77
            ],
            'columns' => [
                'main-user-id' => 'u_id'
            ]
        ]);
        $this->assertEquals('select * from ('
                .'select '."\n"
                .'users.user_id as u_id'."\n"
                .'from users right join user_articles'."\n"
                .'on users.user_id = user_articles.user_id)'."\n"
                .'as UsersArticles where UsersArticles.u_id = \'77\';',$joinQuery->getQuery());
        $joinQuery2 = $joinQuery->join([
            'right-table' => $query0,
            'join-cols' => [
                'main-user-id' => 'user-id'
            ],
            'alias' => 'SubJoin',
            'join-type' => 'left',
            'keys-map' => [
                'left' => [
                    'main-user-id' => 'l-user',
                    'created-on' => 'l-created-on',
                    'article-id' => 'article-id',
                    'right-user-id' => 'l-user-2'
                ],
                'right' => [
                    'user-id' => 'r-user',
                    'created-on' => 'r-created-on'
                ]
            ]
        ]);
        $this->assertEquals(6,count($joinQuery2->getTable()->getColsNames()));
        $this->assertTrue($joinQuery2->getTable()->hasColumn('l-user'));
        $this->assertTrue($joinQuery2->getTable()->hasColumn('l-created-on'));
        $this->assertTrue($joinQuery2->getTable()->hasColumn('article-id'));
        $this->assertTrue($joinQuery2->getTable()->hasColumn('l-user-2'));
        $this->assertTrue($joinQuery2->getTable()->hasColumn('r-user'));
        $this->assertTrue($joinQuery2->getTable()->hasColumn('r-created-on'));
        $joinQuery2->select();
        $this->assertEquals('select * from (select '."\n"
                .'UsersArticles.left_user_id,'."\n"
                .'UsersArticles.created_on as left_created_on,'."\n"
                .'UsersArticles.article_id,'."\n"
                .'UsersArticles.right_user_id,'."\n"
                .'users.user_id,'."\n"
                .'users.created_on as right_created_on'."\n"
                .'from (select '."\n"
                .'users.user_id as left_user_id,'."\n"
                .'users.created_on,'."\n"
                .'user_articles.article_id,'."\n"
                .'user_articles.user_id as right_user_id'."\n"
                .'from users right join user_articles'."\n"
                .'on users.user_id = user_articles.user_id) as UsersArticles left join users'."\n"
                .'on UsersArticles.left_user_id = users.user_id)'."\n"
                .'as SubJoin;'
                .'',$joinQuery2->getQuery());
    }
    /**
     * @test
     */
    public function testJoinNewKeys01() {
        $query0 = new MySQLQuery('users');
        $query0->getTable()->addColumns([
            'user-id' => [
                'is-primary' => true,
                'size' => 15
            ]
        ]);
        $query1 = new MySQLQuery('user_articles');
        $query1->getTable()->addColumns([
            'user-id' => [
                'is-primary' => true,
                'size' => '10'
            ],
            'article-title' => [
                'size' => 150
            ]
        ]);
        $joinQuery = $query0->join([
            'right-table' => $query1,
            'join-cols' => [
                'user-id' => 'user-id'
            ],
            'join-type' => 'right',
            'alias' => 'NewTable',
            'keys-map' => [
                'left' => [
                    'user-id' => 'main-user-id'
                ],
                'right' => [
                    'user-id' => 'sub-user-id',
                    'article-title' => 'title'
                ]
            ]
        ]);
        $this->assertTrue($joinQuery->getTable()->hasColumn('main-user-id'));
        $this->assertTrue($joinQuery->getTable()->hasColumn('sub-user-id'));
        $this->assertTrue($joinQuery->getTable()->hasColumn('title'));
        $this->assertFalse($joinQuery->getTable()->hasColumn('user-id'));
        $this->assertFalse($joinQuery->getTable()->hasColumn('article-title'));
        $this->assertTrue($joinQuery->getTable()->getLeftTable()->hasColumn('user-id'));
        $this->assertTrue($joinQuery->getTable()->getRightTable()->hasColumn('user-id'));
        $this->assertTrue($joinQuery->getTable()->getRightTable()->hasColumn('article-title'));

        return $joinQuery;
    }

    /**
     * @depends testSetJoinCondition00
     * @param JoinTable $table
     */
    public function testJoinSelect00($table) {
        $query = new MySQLQuery();
        $query->setTable($table);
        $query->select();
        $this->assertEquals('select * from ('
                .'select '."\n"
                .'a_left_table.id as left_id,'."\n"
                .'a_left_table.created_on as left_created_on,'."\n"
                .'a_left_table.last_updated as left_last_updated,'."\n"
                .'a_right_table.id as right_id,'."\n"
                .'a_right_table.created_on as right_created_on,'."\n"
                .'a_right_table.last_updated as right_last_updated'."\n"
                .'from a_left_table left join a_right_table'."\n"
                .'on a_left_table.id = a_right_table.id and a_left_table.created_on = a_right_table.created_on'
                .")\nas JoinTable;",$query->getQuery());
        $query->select([
            'without-select' => true
        ]);
        $this->assertEquals(''
                .'select '."\n"
                .'a_left_table.id as left_id,'."\n"
                .'a_left_table.created_on as left_created_on,'."\n"
                .'a_left_table.last_updated as left_last_updated,'."\n"
                .'a_right_table.id as right_id,'."\n"
                .'a_right_table.created_on as right_created_on,'."\n"
                .'a_right_table.last_updated as right_last_updated'."\n"
                .'from a_left_table left join a_right_table'."\n"
                .'on a_left_table.id = a_right_table.id and a_left_table.created_on = a_right_table.created_on'
                .'',$query->getQuery());

        return $query;
    }
    /**
     * @depends testJoinSelect00
     * @param MySQLQuery $query
     * @test
     */
    public function testJoinSelect01($query) {
        $query->select([
            'columns' => [
                'id','created-on'
            ]
        ]);
        $this->assertEquals('select * from ('
                .'select '."\n"
                .'a_left_table.id as left_id,'."\n"
                .'a_left_table.created_on as left_created_on'."\n"
                .'from a_left_table left join a_right_table'."\n"
                .'on a_left_table.id = a_right_table.id and a_left_table.created_on = a_right_table.created_on'
                .")\nas JoinTable;",$query->getQuery());
        $query->select([
            'columns' => [
                'id' => 'user_id',
                'created-on' => 'insert_date'
            ]
        ]);
        $this->assertEquals('select * from ('
                .'select '."\n"
                .'a_left_table.id as user_id,'."\n"
                .'a_left_table.created_on as insert_date'."\n"
                .'from a_left_table left join a_right_table'."\n"
                .'on a_left_table.id = a_right_table.id and a_left_table.created_on = a_right_table.created_on'
                .")\nas JoinTable;",$query->getQuery());

        return $query;
    }
    /**
     * @depends testJoinSelect01
     * @param MySQLQuery $query
     * @test
     */
    public function testJoinSelect02($query) {
        $query->select([
            'columns' => [
                'left' => [
                    'id','created-on'
                ],
                'right' => [
                    'last-updated'
                ]
            ]
        ]);
        $this->assertEquals('select * from ('
                .'select '."\n"
                .'a_left_table.id as left_id,'."\n"
                .'a_left_table.created_on as left_created_on,'."\n"
                .'a_right_table.last_updated as right_last_updated'."\n"
                .'from a_left_table left join a_right_table'."\n"
                .'on a_left_table.id = a_right_table.id and a_left_table.created_on = a_right_table.created_on'
                .")\nas JoinTable;",$query->getQuery());
        $query->select([
            'columns' => [
                'left' => [
                    'id','created-on' => 'cr_date'
                ],
                'right' => [
                    'last-updated' => 'l_updated'
                ]
            ]
        ]);
        $this->assertEquals('select * from ('
                .'select '."\n"
                .'a_left_table.id as left_id,'."\n"
                .'a_left_table.created_on as cr_date,'."\n"
                .'a_right_table.last_updated as l_updated'."\n"
                .'from a_left_table left join a_right_table'."\n"
                .'on a_left_table.id = a_right_table.id and a_left_table.created_on = a_right_table.created_on'
                .")\nas JoinTable;",$query->getQuery());

        return $query;
    }
    /**
     * @depends testJoinSelect02
     * @param MySQLQuery $query
     * @test
     */
    public function testJoinSelect03($query) {
        $query->select([
            'columns' => [
                'left' => [
                    'id','created-on' => 'cr_date'
                ],
                'right' => [
                    'last-updated' => 'l_updated'
                ]
            ],
            'where' => [
                'left-id' => 44
            ]
        ]);
        $this->assertEquals('select * from ('
                .'select '."\n"
                .'a_left_table.id as left_id,'."\n"
                .'a_left_table.created_on as cr_date,'."\n"
                .'a_right_table.last_updated as l_updated'."\n"
                .'from a_left_table left join a_right_table'."\n"
                .'on a_left_table.id = a_right_table.id and a_left_table.created_on = a_right_table.created_on'
                .")\nas JoinTable where JoinTable.left_id = 44;",$query->getQuery());
    }
    /**
     * 
     * @param MySQLQuery $query
     * @depends testJoinNewKeys01
     */
    public function testSelect00($query) {
        $query->select([

        ]);
        $this->assertEquals("select * from (select \n"
                ."users.user_id as left_user_id,\n"
                ."user_articles.user_id as right_user_id,\n"
                ."user_articles.article_title\n"
                ."from users right join user_articles\n"
                ."on users.user_id = user_articles.user_id)\n"
                ."as NewTable;",$query->getQuery());
        $query->select([
            'columns' => [
                'main-user-id'
            ]
        ]);
        $this->assertEquals("select * from (select \n"
                ."users.user_id as left_user_id\n"
                ."from users right join user_articles\n"
                ."on users.user_id = user_articles.user_id)\n"
                ."as NewTable;",$query->getQuery());
        $query->select([
            'columns' => [
                'sub-user-id'
            ]
        ]);
        $this->assertEquals("select * from (select \n"
                ."user_articles.user_id as right_user_id\n"
                ."from users right join user_articles\n"
                ."on users.user_id = user_articles.user_id)\n"
                ."as NewTable;",$query->getQuery());
        $query->select([
            'columns' => [
                'title'
            ]
        ]);
        $this->assertEquals("select * from (select \n"
                ."user_articles.article_title\n"
                ."from users right join user_articles\n"
                ."on users.user_id = user_articles.user_id)\n"
                ."as NewTable;",$query->getQuery());

        return $query;
    }
    /**
     * @depends testSelect00
     * @param MySQLQuery $query
     */
    public function testSelect01($query) {
        $query->select([
            'columns' => [
                'main-user-id' => 'l_uid',
                'sub-user-id' => 'r_uid',
                'title' => 'art_title'
            ]
        ]);
        $this->assertEquals("select * from (select \n"
                ."users.user_id as l_uid,\n"
                ."user_articles.user_id as r_uid,\n"
                ."user_articles.article_title as art_title\n"
                ."from users right join user_articles\n"
                ."on users.user_id = user_articles.user_id)\n"
                ."as NewTable;",$query->getQuery());
        $query->select([
            'columns' => [
                'main-user-id' => 'x_user_id'
            ]
        ]);
        $this->assertEquals("select * from (select \n"
                ."users.user_id as x_user_id\n"
                ."from users right join user_articles\n"
                ."on users.user_id = user_articles.user_id)\n"
                ."as NewTable;",$query->getQuery());
        $query->select([
            'columns' => [
                'sub-user-id' => 'oiy_id'
            ]
        ]);
        $this->assertEquals("select * from (select \n"
                ."user_articles.user_id as oiy_id\n"
                ."from users right join user_articles\n"
                ."on users.user_id = user_articles.user_id)\n"
                ."as NewTable;",$query->getQuery());
        $query->select([
            'columns' => [
                'title' => 'article_title_super'
            ]
        ]);
        $this->assertEquals("select * from (select \n"
                ."user_articles.article_title as article_title_super\n"
                ."from users right join user_articles\n"
                ."on users.user_id = user_articles.user_id)\n"
                ."as NewTable;",$query->getQuery());

        return $query;
    }
    /**
     * @depends testConstructor01
     * @param JoinTable $table
     */
    public function testSetJoinCondition00($table) {
        $table->setJoinCondition(['id' => 'id']);
        $this->assertEquals('on a_left_table.id = a_right_table.id',$table->getJoinCondition());
        $table->setJoinCondition(['id' => 'id','created-on' => 'created-on']);
        $this->assertEquals('on a_left_table.id = a_right_table.id and a_left_table.created_on = a_right_table.created_on',$table->getJoinCondition());

        return $table;
    }
}
