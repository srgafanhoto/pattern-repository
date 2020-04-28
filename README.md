# Repository Design Pattern for Laravel

## Methods

### srgafanhoto\PatternRepository\BaseRepository

#### Public methods

- all($columns = ['*'])
- lists($value, $key = null)
- find($id, $columns = ['*'])
- findAllBy($attribute, $value, $columns = ['*'])
- findBy($attribute, $value, $columns = ['*'])
- findMany(array $ids, $columns = ['*'])
- findOrFail($id, $columns = ['*'])
- findWhere(array $where, $columns = ['*'], $or = false)
- findWhereIn($field, array $values, $columns = ['*'])
- findWhereNotIn($field, array $values, $columns = ['*'])

#### Protected methods

- saveModel(array $data)
- get($criteria = [], $columns = ['*'])
- first($criteria = [], $columns = ['*'])
- paginate($criteria = [], $perPage = null, $columns = ['*'], $pageName = 'page', $page = null)
- simplePaginate($criteria = [], $perPage = null, $columns = ['*'], $pageName = 'page', $page = null)
- count($criteria = [], $columns = '*')
- matching($criteria)
- getModel()
- newInstance($attributes = [], $exists = false)
- newQuery()
- validate($validator, array $data)

### srgafanhoto\PatternRepository\Criteria\Criteria

- static create()
- static expr($value)
- static raw($value)
- select($columns = ['*'])
- selectRaw($expression, array $bindings = [])
- selectSub($query, $as)
- addSelect($column)
- distinct()
- from($table)
- join($table, $first, $operator = null, $second = null, $type = 'inner', $where = false)
- joinWhere($table, $first, $operator, $second, $type = 'inner')
- leftJoin($table, $first, $operator = null, $second = null)
- leftJoinWhere($table, $first, $operator, $second)
- rightJoin($table, $first, $operator = null, $second = null)
- rightJoinWhere($table, $first, $operator, $second)
- crossJoin($table, $first = null, $operator = null, $second = null)
- mergeWheres($wheres, $bindings)
- tap($callback)
- where($column, $operator = null, $value = null, $boolean = 'and')
- orWhere($column, $operator = null, $value = null)
- whereColumn($first, $operator = null, $second = null, $boolean = 'and')
- orWhereColumn($first, $operator = null, $second = null)
- whereRaw($sql, $bindings = [], $boolean = 'and')
- orWhereRaw($sql, array $bindings = [])
- whereIn($column, $values, $boolean = 'and', $not = false)
- orWhereIn($column, $values)
- whereNotIn($column, $values, $boolean = 'and')
- orWhereNotIn($column, $values)
- whereNull($column, $boolean = 'and', $not = false)
- orWhereNull($column)
- whereNotNull($column, $boolean = 'and')
- whereBetween($column, array $values, $boolean = 'and', $not = false)
- orWhereBetween($column, array $values)
- whereNotBetween($column, array $values, $boolean = 'and')
- orWhereNotBetween($column, array $values)
- orWhereNotNull($column)
- whereDate($column, $operator, $value = null, $boolean = 'and')
- orWhereDate($column, $operator, $value)
- whereTime($column, $operator, $value, $boolean = 'and')
- orWhereTime($column, $operator, $value)
- whereDay($column, $operator, $value = null, $boolean = 'and')
- whereMonth($column, $operator, $value = null, $boolean = 'and')
- whereYear($column, $operator, $value = null, $boolean = 'and')
- whereNested(Closure $callback, $boolean = 'and')
- addNestedWhereQuery($query, $boolean = 'and')
- whereExists(Closure $callback, $boolean = 'and', $not = false)
- orWhereExists(Closure $callback, $not = false)
- whereNotExists(Closure $callback, $boolean = 'and')
- orWhereNotExists(Closure $callback)
- addWhereExistsQuery(Builder $query, $boolean = 'and', $not = false)
- dynamicWhere($method, $parameters)
- groupBy()
- having($column, $operator = null, $value = null, $boolean = 'and')
- orHaving($column, $operator = null, $value = null)
- havingRaw($sql, array $bindings = [], $boolean = 'and')
- orHavingRaw($sql, array $bindings = [])
- orderBy($column, $direction = 'asc')
- orderByDesc($column)
- latest($column = 'created_at')
- oldest($column = 'created_at')
- inRandomOrder($seed = '')
- orderByRaw($sql, $bindings = [])
- skip($value)
- offset($value)
- take($value)
- limit($value)
- forPage($page, $perPage = 15)
- forPageAfterId($perPage = 15, $lastId = 0, $column = 'id')
- union($query, $all = false)
- unionAll($query)
- lock($value = true)
- lockForUpdate()
- sharedLock()
- when($value, $callback, $default = null)
- unless($value, $callback, $default = null)
- whereKey($id)
- whereKeyNot($id)
- with($relations)
- without($relations)
- setQuery($query)
- setModel(Model $model)
- has($relation, $operator = '>=', $count = 1, $boolean = 'and', Closure $callback = null)
- orHas($relation, $operator = '>=', $count = 1)
- doesntHave($relation, $boolean = 'and', Closure $callback = null)
- whereHas($relation, Closure $callback = null, $operator = '>=', $count = 1)
- orWhereHas($relation, Closure $callback = null, $operator = '>=', $count = 1)
- whereDoesntHave($relation, Closure $callback = null)
- withCount($relations)
- mergeConstraintsFrom(Builder $from)
- withTrashed()
- withoutTrashed()
- onlyTrashed()

#### PatternRepository Custom Criteria

- fromRequest($fieldsSearchable = [])
- whereDateFormat($column, $operator, $value, $format = 'd/m/Y')
- orWhereDateFormat($column, $operator, $value, $format = 'd/m/Y')

## Usage

### Eloquent

#### Create a Model

Create your model normally, but it is important to define the attributes that can be filled from the input form data.

```php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $fillable = [
        'title',
        'author',
     ];
}    
```

#### Create a Repository

```php
namespace App\Domains\Module\Repository;
 
use App\Post;
use srgafanhoto\PatternRepository\BaseRepository;
use srgafanhoto\PatternRepository\Criteria\Criteria;
use App\Domains\Module\Validators\UserValidation;
 
class PostRepository extends BaseRepository
{
 
    protected function model()
    {
 
        return Post::class;
    }
}
```

#### Controller

```php
namespace App\Http\Controllers;
 
use App\Repositories\Contracts\PostRepository;
 
class PostsController extends Controller
{
 
    protected $repository;
 
    public function __construct(PostRepository $repository)
    {
    
        $this->repository = $repository;
    }
}
```

## Example of Methods

Find all results in Repository

```php
$posts = $this->repository->all();
```

Find result by id

```php
$post = $this->repository->find($id);
```

### Find by conditions

#### Using the Criteria

Criteria is support all of Eloquent functions

##### From Request Criteria

```php
use srgafanhoto\PatternRepository\Criteria\Criteria;
 
$criteria = Criteria::create()
    ->fromRequest();
 
$this->repository->get($criteria);
$this->repository->paginate($criteria);
```

##### Single Criteria

```php
use srgafanhoto\PatternRepository\Criteria\Criteria;
 
$criteria = Criteria::create()
    ->select('*')
    ->where('author', '=', 'author')
    ->orWhere('title', '=', 'title')
    ->orderBy('author', 'asc');
 
$this->repository->get($criteria);
$this->repository->paginate($criteria);
```

#### Multiple Criteria

```php
use srgafanhoto\PatternRepository\Criteria\Criteria;
 
$criteria = [];
 
$criteria[] = Criteria::create()
    ->orderBy('author', 'asc');
 
$criteria[] = Criteria::create()
    ->where('author', '=', 'author')
    ->orWhere('title', '=', 'title');
 
$this->repository->get($criteria);
// $this->repository->paginate($criteria);
```

##### With

```php
use srgafanhoto\PatternRepository\Criteria\Criteria;
 
$criteria = Criteria::create()
    ->with('author', function($criteria) {
        $criteria->where('author', 'author');
    });
 
$this->repository->get($criteria);
// $this->repository->paginate($criteria);
```

#### Join

```php

use srgafanhoto\PatternRepository\Criteria\Criteria;
 
$criteria = Criteria::create()
    ->join('author', function ($criteria) {
        $criteria->on('posts.author_id', '=', 'author.id');
    });
 
$this->repository->get($criteria);
// $this->repository->paginate($criteria);
```

#### Expression

```php
 
use srgafanhoto\PatternRepository\Criteria\Criteria;
 
$criteria = Criteria::create()
    ->where('created_at', '<=', Criteria::expr('NOW()'));
 
$this->repository->get($criteria);
// $this->repository->paginate($criteria);
```

#### Custom Criteria

```php
use srgafanhoto\PatternRepository\Criteria\Criteria;
 
class CustomCriteria extends Criteria
{
 
    public function __construct($id)
    {
     
        $this->where('id', '=', $id);
    }
}

$this->repository->get((new CustomCriteria(1))->where('autor', 'autor'));
```

## ToDo
- Cache
