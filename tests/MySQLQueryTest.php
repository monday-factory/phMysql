<?php
namespace phMysql\tests;

use phMysql\MySQLQuery;
use PHPUnit\Framework\TestCase;
/**
 * Unit tests for testing the class 'MySQLQuery'.
 *
 * @author Ibrahim
 */
class MySQLQueryTest extends TestCase {
    /**
     * @test
     */
    public function createStructureTest00() {
        $articleQuery = new ArticleQuery();
        $articleQuery->createStructure();
        $this->assertEquals("",'');
    }
    /**
     * @test
     */
    public function testAddForeignKey00() {
        $aq = new ArticleQuery();
        $aq->addForeignKey($aq->getStructure()->getForeignKeys()[0]);
        $this->assertEquals("alter table articles add constraint author_fk foreign key (author_id) references system_users(user_id) on delete set null on update set null",$aq->getQuery());
    }
    /**
     * @test
     */
    public function testAddPrimaryKey00() {
        $aq = new ArticleQuery();
        $aq->addPrimaryKey($aq->getStructure());
        $this->assertEquals("alter table articles add constraint articles_pk primary key (article_id);\n"
                ."alter table articles modify article_id int(11) not null unique auto_increment;\n",$aq->getQuery());
    }
    /**
     * @test
     */
    public function testCreateTable00() {
        $query = new MySQLQuery('users');
        $query->getTable()->addDefaultCols();
        $query->getTable()->addColumns([
            'username' => [
                'datatype' => 'varchar',
                'size' => 20,
                'unique' => true
            ],
            'password' => [
                'datatype' => 'varchar',
                'size' => 64,
                'comment' => 'The password of the user must be SHA256 hash (64 characters).'
            ],
            'last-success-login' => [
                'datatype' => 'datetime',
                'is-null' => true
            ],
            'last-failed-login' => [
                'datatype' => 'datetime',
                'is-null' => true
            ],
            'failed-login-attempts' => [
                'datatype' => 'int',
                'name' => 'f_logins',
                'size' => 2,
                'default' => 0
            ],
        ]);
        $query->createStructure();
        $this->assertEquals('create table if not exists users('."\n"
        .'    id int(11) not null unique,'."\n"
        .'    created_on timestamp not null default current_timestamp,'."\n"
        .'    last_updated datetime null,'."\n"
        .'    username varchar(20) not null collate utf8mb4_unicode_ci,'."\n"
        .'    password varchar(64) not null collate utf8mb4_unicode_ci comment \'The password of the user must be SHA256 hash (64 characters).\','."\n"
        .'    last_success_login datetime null,'."\n"
        .'    last_failed_login datetime null,'."\n"
        .'    f_logins int(2) not null default 0'."\n"
        .')'."\n"
        .'ENGINE = InnoDB'."\n"
        .'DEFAULT CHARSET = utf8mb4'."\n"
        .'collate = utf8mb4_unicode_ci;'."\n"
        .'alter table users add constraint users_pk primary key (id);'."\n"
        .'alter table users modify id int(11) not null unique auto_increment;'."\n",$query->getQuery());
    }
    /**
     * @test
     */
    public function testDeleteRecord00() {
        $aq = new ArticleQuery();
        $aq->deleteRecord([
            'article-id' => 77
        ]);
        $this->assertEquals('delete from articles where article_id = 77;',$aq->getQuery());
    }
    /**
     * @test
     */
    public function testDeleteRecord01() {
        $aq = new ArticleQuery();
        $aq->deleteRecord([
            'article-id' => 77,
            'author-id' => 98
        ]);
        $this->assertEquals('delete from articles where article_id = 77 and author_id = 98;',$aq->getQuery());
    }
    /**
     * @test
     */
    public function testInsert000() {
        $aq = new ArticleQuery();
        $aq->insertRecord([
            'author-id' => 66,
            'created-on' => '2019-11-17 12:08:22'
        ]);
        $this->assertEquals('insert into articles (author_id,created_on) values (66,\'2019-11-17 12:08:22\');',$aq->getQuery());
    }
    /**
     * @test
     */
    public function testInsert001() {
        $aq = new ArticleQuery();
        $aq->insertRecord([
            'author-id' => 66,
            'content' => null,
            'created-on' => '2019-11-17 12:05:02'
        ]);
        $this->assertEquals('insert into articles (author_id,article_content,created_on) values (66,null,\'2019-11-17 12:05:02\');',$aq->getQuery());
    }
    /**
     * @test
     */
    public function testInsert002() {
        $aq = new ArticleQuery();
        $aq->insertRecord([
            'author-id' => 66,
            'content' => 'null',
            'created-on' => '2019-09-09 00:00:00'
        ]);
        $this->assertEquals('insert into articles (author_id,article_content,created_on) values (66,null,\'2019-09-09 00:00:00\');',$aq->getQuery());
    }
    /**
     * @test
     */
    public function testInsert003() {
        $aq = new ArticleQuery();
        $aq->insertRecord([
            'author-id' => 66
        ]);
        $prev = date('Y-m-d H:i:s', time() - 1);
        $now = date('Y-m-d H:i:s');
        $query = $aq->getQuery();
        $next = date('Y-m-d H:i:s', time() + 1);
        $isEqual = $query == 'insert into articles (author_id,created_on) values (66,\''.$prev.'\');'
                || $query == 'insert into articles (author_id,created_on) values (66,,\''.$next.'\');'
                || $query == 'insert into articles (author_id,created_on) values (66,\''.$now.'\');';
        $this->assertTrue($isEqual);
    }
    /**
     * @test
     */
    public function testSelect000() {
        $obj = new QueryTestObj();
        $obj->select();
        $this->assertEquals('select * from first_table;',$obj->getQuery());
    }
    /**
     * @test
     */
    public function testSelect001() {
        $obj = new QueryTestObj();
        $obj->select([
            'limit' => 3
        ]);
        $this->assertEquals('select * from first_table limit 3;',$obj->getQuery());
    }
    /**
     * @test
     */
    public function testSelect002() {
        $obj = new QueryTestObj();
        $obj->select([
            'limit' => 3,
            'offset' => 7
        ]);
        $this->assertEquals('select * from first_table limit 3 offset 7;',$obj->getQuery());
    }
    /**
     * @test
     */
    public function testSelect004() {
        $obj = new QueryTestObj();
        $obj->select([
            'offset' => 3
        ]);
        $this->assertEquals('select * from first_table;',$obj->getQuery());
    }
    /**
     * @test
     */
    public function testSelect005() {
        $obj = new QueryTestObj();
        $obj->select([
            'order-by' => [
                [
                    'col' => 'first-col'
                ]
            ]
        ]);
        $this->assertEquals('select * from first_table order by col_00;',$obj->getQuery());
    }
    /**
     * @test
     */
    public function testSelect006() {
        $obj = new QueryTestObj();
        $obj->select([
            'order-by' => [
                [
                    'col' => 'first-col',
                    'order-type' => 'D'
                ],
                [
                    'col' => 'second-col'
                ],
                [
                    'col' => 'fourth-col',
                    'order-type' => 'A'
                ]
            ]
        ]);
        $this->assertEquals('select * from first_table order by col_00 desc, col_01, col_03 asc;',$obj->getQuery());
    }
    /**
     * @test
     */
    public function testSelect007() {
        $obj = new QueryTestObj();
        $obj->select([
            'columns' => ['first-col'],
            'order-by' => [
                [
                    'col' => 'first-col',
                    'order-type' => 'D'
                ],
                [
                    'col' => 'second-col'
                ],
                [
                    'col' => 'fourth-col',
                    'order-type' => 'A'
                ]
            ]
        ]);
        $this->assertEquals('select col_00 from first_table order by col_00 desc, col_01, col_03 asc;',$obj->getQuery());
    }
    /**
     * @test
     */
    public function testSelect008() {
        $obj = new QueryTestObj();
        $obj->select([
            'columns' => ['first-col','fourth-col'],
            'order-by' => [
                [
                    'col' => 'first-col',
                    'order-type' => 'D'
                ],
                [
                    'col' => 'second-col'
                ],
                [
                    'col' => 'fourth-col',
                    'order-type' => 'A'
                ]
            ]
        ]);
        $this->assertEquals('select col_00,'."\n"
                .'col_03 from first_table order by col_00 desc, col_01, col_03 asc;',$obj->getQuery());
    }
    /**
     * @test
     */
    public function testSelect009() {
        $obj = new QueryTestObj();
        $obj->select([
            'order-by' => [
                [
                    'col' => 'first-col'
                ]
            ],
            'group-by' => [
                [
                    'col' => 'first-col'
                ]
            ]
        ]);
        $this->assertEquals('select * from first_table group by col_00 order by col_00;',$obj->getQuery());
    }
    /**
     * @test
     */
    public function testSelect010() {
        $obj = new QueryTestObj();
        $obj->select([
            'condition-cols-and-vals' => [
                'fourth-col' => '7U'
            ],
            'order-by' => [
                [
                    'col' => 'first-col'
                ]
            ],
            'group-by' => [
                [
                    'col' => 'fourth-col'
                ],
                [
                    'col' => 'first-col'
                ]
            ]
        ]);
        $this->assertEquals('select * from first_table where col_03 = \'7U\' group by col_03, col_00 order by col_00;',$obj->getQuery());
    }
    /**
     * @test
     */
    public function testSelect011() {
        $obj = new QueryTestObj();
        $obj->select([
            'condition-cols-and-vals' => [
                'fourth-col' => '7U',
                'first-col' => '*I',
                'third-col' => 'X'
            ],
            'order-by' => [
                [
                    'col' => 'first-col'
                ]
            ],
            'group-by' => [
                [
                    'col' => 'fourth-col'
                ],
                [
                    'col' => 'first-col'
                ]
            ]
        ]);
        $this->assertEquals('select * from first_table where col_03 = \'7U\' and col_00 = \'*I\' and col_02 = \'X\' group by col_03, col_00 order by col_00;',$obj->getQuery());
    }
    public function testSelect012() {
        $aq = new ArticleQuery();
        //testing sql injection
        $aq->select([
            'where' => ['author-id' => '5;drop table random;']
        ]);
        $this->assertEquals('select * from articles where author_id = 5;',$aq->getQuery());
    }
    /**
     * @test
     */
    public function testSelect013() {
        $aq = new ArticleQuery();
        //testing sql injection
        $aq->select([
            'where' => ['author-id' => 'drop table random;']
        ]);
        $this->assertEquals('select * from articles where author_id = 0;',$aq->getQuery());
    }
    /**
     * @test
     */
    public function testSelect014() {
        $aq = new ArticleQuery();
        $aq->select([
            'where' => ['author-id' => 'is null']
        ]);
        $this->assertEquals('select * from articles where author_id is null;',$aq->getQuery());
    }
    /**
     * @test
     */
    public function testSelect015() {
        $aq = new ArticleQuery();
        $aq->select([
            'where' => ['author-id' => null]
        ]);
        $this->assertEquals('select * from articles where author_id is null;',$aq->getQuery());
    }
    /**
     * @test
     */
    public function testSelect016() {
        $aq = new ArticleQuery();
        $aq->select([
            'where' => ['author-id' => null],
            'conditions' => ['!=']
        ]);
        $this->assertEquals('select * from articles where author_id is not null;',$aq->getQuery());
    }
    /**
     * @test
     * @return MySQLQuery
     */
    public function testSelect017() {
        $query = new MySQLQuery('user_data');
        $query->getTable()->addColumns([
            'user-id' => [
                'datatype' => 'int',
                'size' => 10,
                'is-primary' => true
            ],
            'username' => [
                'size' => 25,
                'is-unique' => true
            ],
            'reg-date' => [
                'datatype' => 'timestamp',
                'default' => 'current_timestamp'
            ],
            'last-login' => [
                'datatype' => 'datetime'
            ]
        ]);
        $query->select([
            'where' => [
                'user-id' => [
                    'values' => [
                        1,4,6
                    ],
                    'join-operators' => [
                        'or','and'
                    ]
                ]
            ]
        ]);
        $this->assertEquals('select * from user_data where (user_id = 1 or user_id = 4 and user_id = 6);',$query->getQuery());

        return $query;
    }
    /**
     * @test
     * @param MySQLQuery $query
     * @depends testSelect017
     */
    public function testSelect018($query) {
        $query->select([
            'where' => [
                'user-id' => [
                    'values' => [
                        1,4,6
                    ],
                    'conditions' => 'in'
                ]
            ]
        ]);
        $this->assertEquals('select * from user_data where (user_id in(1,4,6));',$query->getQuery());
    }
    /**
     * @test
     * @param MySQLQuery $query
     * @depends testSelect017
     */
    public function testSelect019($query) {
        $query->select([
            'where' => [
                'user-id' => [
                    'values' => [
                        1,4,6
                    ],
                    'conditions' => 'not in'
                ]
            ]
        ]);
        $this->assertEquals('select * from user_data where (user_id not in(1,4,6));',$query->getQuery());
    }
    /**
     * @test
     */
    public function testSelect020() {
        $q = new MySQLQuery('hello');
        $q->getTable()->addColumns([
            'user-id' => [
                'name' => 'u_id',
                'type' => 'int',
                'size' => 4,
                'is-primary' => true
            ],
            'first-name' => [
                'size' => 15
            ],
            'last-name' => [
                'size' => 15
            ],
            'email-address' => [
                'size' => 150
            ]
        ]);
        $q->select([
            'columns' => [
                'user-id',
                'first-name' => 'f_name',
                'last-name' => 'l_name',
                'email-address'
            ]
        ]);
        $this->assertEquals('select u_id,'."\n"
                .'first_name as f_name,'."\n"
                .'last_name as l_name,'."\n"
                .'email_address from hello;',$q->getQuery());
        $q->select([
            'columns' => [
                'user-id',
                'first-name' => 'f_name',
                'last-name' => 'l_name',
                'email-address'
            ],
            'table-prefix' => true
        ]);
        $this->assertEquals('select hello.u_id,'."\n"
                .'hello.first_name as f_name,'."\n"
                .'hello.last_name as l_name,'."\n"
                .'hello.email_address from hello;',$q->getQuery());
//        $q->select([
//            'columns'=>[
//                'x'=>'bb',
//                'user-id',
//                'first-name'=>'f_name',
//                'last-name'=>'l_name',
//                'email-address',
//                'jjkk',
//                0
//            ]
//        ]);
//        $this->assertEquals('select u_id,'."\n"
//                . 'first_name as f_name,'."\n"
//                . 'last_name as l_name,'."\n"
//                . 'email_address from hello;',$q->getQuery());
//        $q->select();
//        $this->assertEquals('select * from hello;',$q->getQuery());
    }
    /**
     * @test
     */
    public function testSelectAll00() {
        $aq = new ArticleQuery();
        $aq->selectAll();
        $this->assertEquals('select * from articles;',$aq->getQuery());
    }
    /**
     * @test
     */
    public function testSelectAll01() {
        $aq = new ArticleQuery();
        $aq->selectAll(0,0);
        $this->assertEquals('select * from articles;',$aq->getQuery());
    }
    /**
     * @test
     */
    public function testSelectAll02() {
        $aq = new ArticleQuery();
        $aq->selectAll(1);
        $this->assertEquals('select * from articles limit 1;',$aq->getQuery());
    }
    /**
     * @test
     */
    public function testSelectAll03() {
        $aq = new ArticleQuery();
        $aq->selectAll(1,0);
        $this->assertEquals('select * from articles limit 1;',$aq->getQuery());
    }
    /**
     * @test
     */
    public function testSelectAll04() {
        $aq = new ArticleQuery();
        $aq->selectAll(10,10);
        $this->assertEquals('select * from articles limit 10 offset 10;',$aq->getQuery());
    }
    /**
     * @test
     */
    public function testSelectAll05() {
        $aq = new ArticleQuery();
        $aq->selectAll(10,-30);
        $this->assertEquals('select * from articles limit 10;',$aq->getQuery());
    }
    /**
     * @test
     */
    public function testSelectAll06() {
        $aq = new ArticleQuery();
        $aq->selectAll(-10,50);
        $this->assertEquals('select * from articles;',$aq->getQuery());
    }
    /**
     * @test
     */
    public function testSelectCount00() {
        $aq = new ArticleQuery();
        $aq->selectCount();
        $this->assertEquals('select count(*) as count from articles;',$aq->getQuery());
    }
    /**
     * @test
     */
    public function testSelectCount01() {
        $aq = new ArticleQuery();
        $aq->selectCount([
            'as' => 'atrticles count'
        ]);
        $this->assertEquals('select count(*) as atrticles_count from articles;',$aq->getQuery());
    }
    /**
     * @test
     */
    public function testSelectCount02() {
        $aq = new ArticleQuery();
        $aq->selectCount([
            'where' => [
                'author-id' => '66'
            ]
        ]);
        $this->assertEquals('select count(*) as count from '
                .'articles where author_id = 66;',$aq->getQuery());
    }
    /**
     * @test
     */
    public function testSelectCount03() {
        $aq = new ArticleQuery();
        $aq->selectCount([
            'where' => [
                'author-id' => '66',
                'last-updated' => '2019-09-09'
            ]
        ]);
        $this->assertEquals('select count(*) as count from '
                .'articles where author_id = 66 and '
                .'last_updated >= \'2019-09-09 00:00:00\' and last_updated <= \'2019-09-09 23:59:59\';',$aq->getQuery());
    }
    /**
     * @test
     */
    public function testSelectCount04() {
        $aq = new ArticleQuery();
        $aq->selectCount([
            'where' => [
                'author-id' => '100',
                'last-updated' => '2019-09-09'
            ],
            'conditions' => ['=','<']
        ]);
        $this->assertEquals('select count(*) as count from '
                .'articles where author_id = 100 and '
                .'last_updated < \'2019-09-09 00:00:00\';',$aq->getQuery());
    }
    /**
     * @test
     */
    public function testSelectCount05() {
        $aq = new ArticleQuery();
        $aq->selectCount([
            'where' => [
                'author-id' => '76',
                'last-updated' => '2019-09-09'
            ],
            'conditions' => ['=','<=']
        ]);
        $this->assertEquals('select count(*) as count from '
                .'articles where author_id = 76 and '
                .'last_updated <= \'2019-09-09 23:59:59\';',$aq->getQuery());
    }
    /**
     * @test
     */
    public function testSelectCount06() {
        $aq = new ArticleQuery();
        $aq->selectCount([
            'where' => [
                'author-id' => '90',
                'last-updated' => '2019-09-09 06:00:00'
            ],
            'conditions' => ['=','<=']
        ]);
        $this->assertEquals('select count(*) as count from '
                .'articles where author_id = 90 and '
                .'last_updated <= \'2019-09-09 06:00:00\';',$aq->getQuery());
    }
    /**
     * @test
     */
    public function testSelectCount07() {
        $aq = new ArticleQuery();
        $aq->selectCount([
            'where' => [
                'author-id' => '34',
                'last-updated' => '2019-09-09'
            ],
            'conditions' => ['=','>']
        ]);
        $this->assertEquals('select count(*) as count from '
                .'articles where author_id = 34 and '
                .'last_updated > \'2019-09-09 23:59:59\';',$aq->getQuery());
    }
    /**
     * @test
     */
    public function testSelectCount08() {
        $aq = new ArticleQuery();
        $aq->selectCount([
            'where' => [
                'author-id' => '98',
                'last-updated' => '2019-09-09'
            ],
            'conditions' => ['=','>=']
        ]);
        $this->assertEquals('select count(*) as count from '
                .'articles where author_id = 98 and '
                .'last_updated >= \'2019-09-09 00:00:00\';',$aq->getQuery());
    }
    /**
     * @test
     */
    public function testSelectCount09() {
        $aq = new ArticleQuery();
        $aq->selectCount([
            'where' => [
                'author-id' => '65',
                'last-updated' => '2019-09-09'
            ],
            'conditions' => ['=','!=']
        ]);
        $this->assertEquals('select count(*) as count from '
                .'articles where author_id = 65 and '
                .'last_updated < \'2019-09-09 00:00:00\' and last_updated > \'2019-09-09 23:59:59\';',$aq->getQuery());
    }
    /**
     * @test
     */
    public function testSelectMax00() {
        $aq = new ArticleQuery();
        $aq->selectMax('');
        $this->assertEquals('select * from articles',$aq->getQuery());
        $this->assertEquals('select',$aq->getType());
    }
    /**
     * @test
     */
    public function testSelectMax01() {
        $aq = new ArticleQuery();
        $aq->selectMax('article-id');
        $this->assertEquals('select max(article_id) as max from articles;',$aq->getQuery());
    }
    /**
     * @test
     */
    public function testSelectMax02() {
        $aq = new ArticleQuery();
        $aq->selectMax('article-id','maximum_id');
        $this->assertEquals('select max(article_id) as maximum_id from articles;',$aq->getQuery());
    }
    /**
     * @test
     */
    public function testSelectMin00() {
        $aq = new ArticleQuery();
        $aq->selectMin('');
        $this->assertEquals('select * from articles',$aq->getQuery());
        $this->assertEquals('select',$aq->getType());
    }
    /**
     * @test
     */
    public function testSelectMin01() {
        $aq = new ArticleQuery();
        $aq->selectMin('article-id');
        $this->assertEquals('select min(article_id) as min from articles;',$aq->getQuery());
    }
    /**
     * @test
     */
    public function testSelectMin02() {
        $aq = new ArticleQuery();
        $aq->selectMin('article-id','minimum_id');
        $this->assertEquals('select min(article_id) as minimum_id from articles;',$aq->getQuery());
    }
    /**
     * @test
     */
    public function testSetMaxPackete() {
        $aq = new ArticleQuery();
        $aq->setMaxPackete(1);
        $this->assertEquals('set global max_allowed_packet = 1048576;',$aq->getQuery());
        $aq->setMaxPackete(1,'kb');
        $this->assertEquals('set global max_allowed_packet = 1024;',$aq->getQuery());
        $aq->setMaxPackete(1,'b');
        $this->assertEquals('set global max_allowed_packet = 1;',$aq->getQuery());
        $aq->setMaxPackete(1,'gb');
        $this->assertEquals('set global max_allowed_packet = 1073741824;',$aq->getQuery());
        $aq->setMaxPackete(1,'ggv');
        $this->assertEquals('set global max_allowed_packet = 1048576;',$aq->getQuery());
    }
    /**
     * @test
     */
    public function testUpdateRecord00() {
        $aq = new ArticleQuery();
        $aq->updateRecord([
            'content' => 'Hello'
        ], [
            'article-id' => 77
        ]);
        $prev = date('Y-m-d H:i:s', time() - 1);
        $now = date('Y-m-d H:i:s');
        $query = $aq->getQuery();
        $next = date('Y-m-d H:i:s', time() + 1);
        $isEqual = $query == 'update articles set article_content = \'Hello\',last_updated = \''.$prev.'\' where article_id = 77;'
                || $query == 'update articles set article_content = \'Hello\',last_updated = \''.$now.'\' where article_id = 77;'
                || $query == 'update articles set article_content = \'Hello\',last_updated = \''.$next.'\' where article_id = 77;';
        $this->assertTrue($isEqual);
    }
    /**
     * @test
     */
    public function testUpdateRecord01() {
        $aq = new ArticleQuery();
        $aq->updateRecord([
            'content' => 'Hello',
            'last-updated' => '2019-11-09 10:00:56'
        ], [
            'article-id' => 77
        ]);
        $query = $aq->getQuery();
        $isEqual = $query == 'update articles set article_content = \'Hello\',last_updated = \'2019-11-09 10:00:56\' where article_id = 77;';
        $this->assertTrue($isEqual);
    }
    /**
     * @test
     */
    public function testUpdateRecord02() {
        $aq = new ArticleQuery();
        $aq->updateRecord([
            'content' => 'Hello',
            'last-updated' => '2019-11-09 10:00:56'
        ], [
            'article-id' => 77,
            'author-id' => 1
        ]);
        $query = $aq->getQuery();
        $isEqual = $query == 'update articles set article_content = \'Hello\',last_updated = \'2019-11-09 10:00:56\' where article_id = 77 and author_id = 1;';
        $this->assertTrue($isEqual);
    }
    /**
     * @test
     */
    public function testUpdateRecord03() {
        $aq = new ArticleQuery();
        $aq->updateRecord([
            'content' => 'Hello',
            'last-updated' => '2019-11-09 10:00:56'
        ], [
            'article-id' => 77,
            'author-id' => 1
        ],['!=']);
        $query = $aq->getQuery();
        $isEqual = $query == 'update articles set article_content = \'Hello\',last_updated = \'2019-11-09 10:00:56\' where article_id != 77 and author_id = 1;';
        $this->assertTrue($isEqual);
    }
}
