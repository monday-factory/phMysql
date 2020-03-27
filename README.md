# phMysql
Object oriented PHP library for building MySQL database schema and quires. 
<p align="center">
  <a href="https://travis-ci.org/usernane/phMysql">
    <img src="https://travis-ci.org/usernane/phMysql.svg?branch=master">
  </a>
  <a href="https://codecov.io/gh/usernane/phMysql">
    <img src="https://codecov.io/gh/usernane/phMysql/branch/master/graph/badge.svg" />
  </a>
  <a href="https://github.com/usernane/phMysql/releases">
      <img src="https://img.shields.io/github/release/usernane/phMySql.svg?label=latest" />
  </a>
  <a href="https://paypal.me/IbrahimBinAlshikh">
    <img src="https://img.shields.io/endpoint.svg?url=https%3A%2F%2Fprogrammingacademia.com%2Fwebfiori%2Fapis%2Fshields-get-dontate-badget">
  </a>
</p>

## API Docs
This library is a part of <a href="https://github.com/usernane/webfiori">WebFiori Framework</a>. To access API docs of the library, you can visid the following link: https://programmingacademia.com/webfiori/docs/phMysql .

## Features
* Ability to create MySQL database table structure using PHP.
* Creating MySQL queries in a simple manner. 
* Connect to MySQL database engine (require mysqli extension).
* Execute MySQL queries and map the result of a query to an object (Like an ORM).
* Validating the data before sending it to the DBMS.

## Supported PHP Versions
The library support all versions starting from version 5.6 up to version 7.4.

## Installation
The easy option is to download the latest release manually from <a href="https://github.com/usernane/phMySql/releases">Release</a>.

## The Idea
The overall idea of the library is as follows, every table in the database is represented as an instance of the class '<a href="https://github.com/usernane/phMysql/blob/master/src/MySQLTable.php">MySQLTable</a>'. The instance is associated with an instance of the class '<a href="https://github.com/usernane/phMysql/blob/master/src/MySQLQuery.php">MySQLQuery</a>'. The main aim of the class 'MySQLQuery' is to construct different types of queries which can be executed and get data from the table. 

The class '<a href="https://github.com/usernane/phMysql/blob/master/src/MySQLLink.php">MySQLink</a>' is used to connect to MySQL database and execute any instance of the class 'MySQLQuery'. In addition to that, it is used to access the data which can be the result of executing a 'select' query.

### Creating Database Tables
The first step in using the library is to create your database tables. As we have said before, every table is represented as an instance of the class <a href="https://programmingacademia.com/webfiori/docs/phMysql/MySQLTable">MySQLTable</a>. Also, we have said that an instance of this class is linked to the class <a href="https://programmingacademia.com/webfiori/docs/phMysql/MySQLQuery">MySQLQuery</a>. 

Let's assume that we want to create a database table with following structure:
* Table name: `users_information`
* Table columns:
 * Column name: `user_id`. Type: `int`. Size: `4`. Primary key.
 * Column name: `username`. Type: `varchar`. Size `25`.
 * Column name: `password`. Type: `varchar`. Size: `64`.
 * Column name: `created_on`. Type: `timestamp` Default to `current_timestamp`.
 * Column name: `last_updated`. Type: `datetime`. Can be `null`.
 
 Such table can be created as follows:
 ``` php
 $query = new MySQLQuery('users_information');
 $query->getTable()->addColumns([
    'user-id'=>[
        'datatype'=>'int',
        'size'=>4,
        'is-primary'=>true
    ],
    'username'=>[
        'datatype'=>'varchar',
        'size'=>25
    ],
    'password'=>[
        'datatype'=>'varchar',
        'size'=>64
    ],
    'created-on'=>[
        'datatype'=>'timestamp',
        'default'=>'current_timestamp'
    ],
    'last-updated'=>[
        'datatype'=>'datetime',
        'is-null'=>true
    ]
 ]);
 ```
 This will build the basic structure of the table. To get SQL query which can be used to create the table, we simply do as follows:
 ``` php
 $query->createTable();
 // display the constructed query.
 print_r('<pre>'.$query.'</pre>);
 ```
### Building Queries
The main aim of the class `MySQLQuery` is to build SQL queries. The class has many pre-made methods which can be used to construct diffrent types of queries to perform diffrent operations on the database. The most important operations are:
* Insert.
* Update.
* Delete.
* Read (or select).
For each operation, there exist a method in the class that corresponds to it.
#### Insert
The method `MySQLQuery::insertRecord()` is used to create an `insert` query. The method accepts an associative array. The keys of the array are columns keys and the values of the keys are the values that will be inserted. 
``` php
$query->insertRecord([
  'user-id'=>99,
  'username'=>'MySuperHeroIsYou',
  'password'=>'f5d44b6d4a7d91821d602d03c096280e86888fa16cf9c27c540bbc2fd4e73932',
  'created-on'=>date('Y-m-d H:i:s')
]);
```
#### Update
The method `MySQLQuery::updateRecord()` is used to create an `update` query. The method accepts 4 parameters. Two of them are optional. The first parameter is an associative array. The keys of the array are columns keys and the values of the keys are the new values. The second parameter is also an associative array that has update condition columns (the `where` part).
``` php
$query->updateRecord([
  'username'=>'MySuperHeroIsYou',
  'password'=>'f5d44b6d4a7d91821d602d03c096280e86888fa16cf9c27c540bbc2fd4e73932',
],
[
  'user-id'=>99
]);
```
#### Delete
The method `MySQLQuery::deleteRecord()` is used to create a `delete` query. The method accepts an associative array that has delete condition columns (the `where` part of the delete query). 
``` php
$query->deleteRecord([
  'user-id'=>99
]);
```
### Connecting to MySQL Database
The class `MySQLLink` is used to connect to MySQL database. It acts as a wrapper for the extension `mysqli`. It also adds extra features to it like the ability to map query result to a class object. 
To connect to a database, we have to create new instance of the class. The constructor of the class accepts 4 parameters:
* Database host address.
* Database username.
* A password.
* Port number.
The first parameter in most cases is `localhost` unless the database is hosted in another place. The username is the user which have a privilege to access the database. The port number is optional. If it is not provided, `3306` is used as a default value. If the connection to the database is established, we must select the database using the method `MySQLLink::setDB()`

The following code shows how to connect to the database. It also checks for connection errors.

``` php
use phMysql\MySQLLink;

$conn = new MySQLLink('localhost', 'root', '123456');

if($conn->getErrorCode() != 0) {
  //connection error. Show error message
  echo $conn->getErrorMessage();
} else {
  //connected. Select database now.
  
  if($conn->setDB('my_database')) {
  
    //connected. Now can execute quires.
  
  } else {
    //unable to set database
    echo $conn->getErrorMessage();
  }
}
```

### Executing MySQL Query
After connecting to the database, we can start running queries on it. As we have said before, the class `MySQLQuery` is used to construct our queries. In order to execute them, we have to use the class `MySQLLink`. To be specific, the method `MySQLLink::executeQuery()`. The method will return a boolean. If the query is successfully executed, the method will return true. If it fails, the method will return false.

Lets assume that we have a connection to a database and we have our query class that has the table `users_information`. The following code sample shows how to execute an insert query.

``` php
$query->insertRecord([
  'user-id'=>99,
  'username'=>'MySuperHeroIsYou',
  'password'=>'f5d44b6d4a7d91821d602d03c096280e86888fa16cf9c27c540bbc2fd4e73932',
  'created-on'=>date('Y-m-d H:i:s')
]);
if($conn->executeQuery($query)) {
  //query executed without errors
} else {
  //something went wrong. Show error message
  echo $conn->getErrorMessage();
}
```
### Fetching Raw Data
### Mapping Query Result to Class Object
### Joining Two Tables
```php


```
