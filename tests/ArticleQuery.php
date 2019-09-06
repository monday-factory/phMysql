<?php
namespace phMysql\tests;
use phMysql\MySQLQuery;
use phMysql\Column;
use phMysql\MySQLTable;
/**
 * Description of ArticleQuery
 *
 * @author Ibrahim
 */
class ArticleQuery extends MySQLQuery{
    /**
     *
     * @var MySQLTable 
     */
    private $table;
    public function __construct() {
        parent::__construct();
        $this->table = new MySQLTable('articles');
        $this->table->addDefaultCols([
            'id'=>[
                'key-name'=>'article-id',
                'db-name'=>'article_id'
            ],
            'created-on'=>[],
            'last-updated'=>[]
        ]);
        $this->table->addColumn('author-id', new Column('author_id', 'int', 11));
        $this->table->addColumn('author-name', new Column('author_name', 'varchar', 20));
        $this->table->getCol('author-name')->setIsPrimary(true);
        $this->table->addColumn('content', new Column('content', 'varchar', 5000));
        
        $this->table->addReference('phMysql\tests\UsersQuery', [
            'author-id'=>'user-id'
        ], 'author_fk');
    }
    /**
     * 
     * @return MySQLTable
     */
    public function getStructure(){
        return $this->table;
    }

}

