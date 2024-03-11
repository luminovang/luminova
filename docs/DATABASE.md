### Application Database Management

To work with database depending on your use case, you can extent the `Query` class or `Connection` class.
Once you have setup the database configuration in your `.env` file.


#### Extending Luminova\Database\Connection

Examples 

```php
$this->db->prepare("UPDATE users SET name = :name");
$this->db->bind(":name", $name);
$this->db->execute();
$response = $this->db->rowCount();
```

```php
$this->db->query("UPDATE users SET name = 'Peter'");
$response = $this->db->rowCount();
```
#### Response Methods 

Get all response from select query

```php 
$this->db->getAll();
```

Get one response useful when limit is 1

```php 
$this->db->getOne();
```

Get response as object `stdClass`

```php 
$this->db->getObject();
```

Shorthand for `getResult`, get response array array 
```php 
$this->db->getArray();
```

Get response as array or object
```php 
$this->db->getResult($type);
```

Get response as integer useful when counting records
```php 
$this->db->getInt();
```

#### Extending Class

You can extend the `Connection` class or initialize the class to grab the connection instance `$conn = new Connection();`

```php 
namespace Luminova\Database;

use \Luminova\Database\Connection;

class MyConn extends Connection 
{  
    public function __construct(){
		parent::__construct();
	}

    public function update(string $name): int {
        $updateQuery = "UPDATE users SET name = :name";
        $this->db->prepare($updateQuery);
        $this->db->bind(":name", $name);
        $this->db->execute();
        return $this->db->rowCount()     
    }

    public function get(string $id): int {
        $updateQuery = "SELECT * FROM users WHERE uid = :uid LIMIT 1";
        $this->db->prepare($updateQuery);
        $this->db->bind(":uid", $id);
        $this->db->execute();
        return $this->db->getOne()     
    }
}
```

#### Extending Luminova\Database\Query

You can extend the `Query` class or initialize the class to grab the connection instance `$query = new Query();` or use the singleton instance `$query = Query::getInstance();`, depending on your use case.

Here are some examples 

```php 
$tbl = $query->table("user");
$tbl->where("id", '=', 1);
$tbl->limit(100, 0);
$tbl->cache('key', 'storage');
$items = $tbl->select(/* Optional array fields ex: ['name', 'email']*/);
```

Using Query builder, the second parameter of `execute` is the return type which can be `all, one, object, array, total, lastId, stmt or className to map return data`

```php 
$tbl = $query->query("SELECT * FROM users WHERE name = :name");
$tbl->cache('key', 'storage'); // If you want result to be cached
$result = $tbl->execute(['name' => 'Peter'], 'array');
```

Update record
```php 
$tbl = $query->table("user");
$tbl->where("id", '=', 1);
$updated = $tbl->update([
    'name' => 'Foo'
]);
```

Retrieve one record 

```php 
$tbl = $query->table("user");
$tbl->where("id", '=', 1);
$items = $tbl->find(/* Optional array fields ex: ['name', 'email']*/);
```

Retrieve total row count 

```php 
$tbl = $query->table("user");
$tbl->where("country", '=', "Nigeria");
$items = $tbl->total();
```

Insert into the database table 
```php 
$tbl = $query->table("user");
$added = $tbl->insert([
    [
        'name' => 'Foo'
        'email' => 'foo@example.com'
    ],
    [
        'name' => 'Bar'
        'email' => 'bar@example.com'
    ]
]);
```

#### Extending Class

```php 
namespace Luminova\Database;
use \Luminova\Database\Query;

class MyQuery extends Query 
{  
    public function __construct(){
		parent::__construct();
	}

    public function updateData(string $name): int {
        return $this->table("user")->where("uid", '=', 1)->update([
            "name" => $name
        ]);
    }

    public function getUser(string $id): int {
        return $this->table("user")->where("uid", '=', $id)->find([
            "name", "email", "age"
        ]);    
    }
    public function getFriends(string $id): int {
        return $this->table("friends")->where("uid", '=', $id)->and("friendListId", '=', "myFriends")->select([
            "name", "email", "age"
        ]);    
    }
}
```

#### Query Class Method

Method                                |  Description
--------------------------------------|--------------------------
table(string $table, string $alias = ''): self            | Table name to query
join(string $table, string $type = 'INNER', string $alias = ''): self            | Table name to join
on(array $seeds): self  | Table join `ON` condition 
innerJoin(string $table, string $alias = ''): self  | Inner join, shorthand for join with second parameter
leftJoin(string $table, string $alias = ''): self | Left join, shorthand for join with second parameter
limit(int $limit = 50, int $offset = 0): self  | Limit with offset and count
order(string $order): self  | Table sorting by order
group(string $group): self | Table grouping column by order
where(string $column, string $operator, string $key): self  | table `WHERE` clause
and(string $column, string $operator, string $key): self  | Table `AND` clause
set(string $column, mixed $value): self | Update table set value with column and value
or(string $column, string $operator, string $key): self  | Table `OR` clause 
andOr(string $column, string $operator, string $key, string $orColumn, string $orOperator string $orKey): self  | Table `(AND OR)` clause
in(string $column, array $list): self | Find in List using `IN` selector 
inset(string $search, string $operator, array $list): self  | Find in set using `IN_SET` selector 
cache(string $storage, ?string $key = '', ?string $uid = '', int $expiry = 7 * 24 * 60 * 60): self  | Cache table response and return cache next time till expiration. The cache method must be called before `find or select` method
insert(array $values, bool $bind = true): int  | Insert into table. optional second parameter to use prepared statement or query execution
select(array $columns = ["*"]): mixed  | Retrieve records from table, optional parameter to specify columns
find(array $columns = ["*"]): mixed  | Retrieve one record from table, optional parameter to specify columns 
update(?array $sets = null): int  | Update table record. if you already used set method, then the second parameter is not required to set columns and values
delete(int $limit = 0): int | Delete all records from table with optional parameter to set limit
truncate(bool $transaction = true): bool | Truncate table to clear all records, optional parameter to set using transaction.
drop(): int   | Drop table
createTable(array $columns): int  | Create a new table 
create(Columns $column): int  | Create new table using column class 
withColumns() : Columns | Generate table columns and setups
reset(): void | Reset unneeded query variables and free memory
free(): void | Free memory  
close(): void | Close database connection


## Application Request & Validations

Validating user request and input

[Request Validations](VALIDATION.md)