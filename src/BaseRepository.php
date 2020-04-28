<?php

namespace srgafanhoto\PatternRepository;

use DB;
use Illuminate\Database\Eloquent\Model;
use \Illuminate\Database\Eloquent\Builder;
use Illuminate\Container\Container as App;
use srgafanhoto\PatternRepository\Criteria\Criteria;
use srgafanhoto\PatternRepository\Validator\Validator;
use srgafanhoto\PatternRepository\Exceptions\RepositoryException;

/**
 * Class BaseRepository
 *
 * @package srgafanhoto\PatternRepository
 */
abstract class BaseRepository
{

    /**
     * @var App
     */
    private $app;

    /**
     * @var Model
     */
    protected $model;

    /**
     * @var Builder
     */
    protected $query;

    /**
     * BaseRepository constructor.
     *
     * @throws RepositoryException
     */
    public function __construct()
    {

        $this->app = app();
        $this->makeModel();
        $this->makeBuilder();
        $this->appReady();

    }

    /**
     * Execute after the repository is ready
     */
    protected function appReady(){}

    /**
     * Specify Model class name
     *
     * @return mixed
     */
    abstract protected function model();

    /**
     * @return Model
     * @throws RepositoryException
     */
    private function makeModel()
    {

        $model = $this->app->make($this->model());

        if (! $model instanceof Model) {

            throw new RepositoryException("Class {$this->model()} must be an instance of Illuminate\\Database\\Eloquent\\Model");

        }

        return $this->model = $model;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function makeBuilder()
    {

        $this->query = $this->model->newQuery();

        return $this->query;

    }

    /**
     * @param array $columns
     * @return mixed
     */
    public function all($columns = ['*'])
    {

        return $this->doQuery()->get($columns);
    }

    /**
     * @param  string $value
     * @param  string $key
     * @return array
     */
    public function lists($value, $key = null)
    {

        $lists = $this->doQuery()->pluck($value, $key);

        if (is_array($lists)) {

            return $lists;
        }

        return $lists->all();

    }

    /**
     * @param $id
     * @param array $columns
     * @return mixed
     */
    public function find($id, $columns = ['*'])
    {

        return $this->doQuery()->find($id, $columns);
    }

    /**
     * @param $attribute
     * @param $value
     * @param array $columns
     * @return mixed
     */
    public function findAllBy($attribute, $value, $columns = ['*'])
    {

        return $this->doQuery()->where($attribute, '=', $value)->get($columns);
    }

    /**
     * @param $attribute
     * @param $value
     * @param array $columns
     * @return mixed
     */
    public function findBy($attribute, $value, $columns = ['*'])
    {

        return $this->doQuery()->where($attribute, '=', $value)->first($columns);
    }

    /**
     * Find multiple models by their primary keys.
     *
     * @param  \Illuminate\Contracts\Support\Arrayable|array $ids
     * @param  array $columns
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function findMany(array $ids, $columns = ['*'])
    {

        return $this->doQuery()->findMany($ids, $columns);
    }

    /**
     * Find a model by its primary key or throw an exception.
     *
     * @param  mixed $id
     * @param  array $columns
     * @return Model|\Illuminate\Database\Eloquent\Collection
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findOrFail($id, $columns = ['*'])
    {
        return $this->doQuery()->findOrFail($id, $columns);
    }

    /**
     * Find a collection of models by the given query conditions.
     *
     * @param array $where
     * @param array $columns
     * @param bool $or
     *
     * @return \Illuminate\Database\Eloquent\Collection|null
     */
    public function findWhere(array $where, $columns = ['*'], $or = false)
    {

        $model = $this->doQuery();

        foreach ($where as $field => $value) {

            if ($value instanceof \Closure) {

                $model = (! $or)
                    ? $model->where($value)
                    : $model->orWhere($value);

            } elseif (is_array($value)) {

                if (count($value) === 3) {

                    list($field, $operator, $search) = $value;

                    $model = (! $or)
                        ? $model->where($field, $operator, $search)
                        : $model->orWhere($field, $operator, $search);

                } elseif (count($value) === 2) {

                    list($field, $search) = $value;

                    $model = (! $or)
                        ? $model->where($field, '=', $search)
                        : $model->orWhere($field, '=', $search);

                }

            } else {

                $model = (! $or)
                    ? $model->where($field, '=', $value)
                    : $model->orWhere($field, '=', $value);

            }

        }

        return $model->get($columns);
    }

    /**
     * @param $field
     * @param array $values
     * @param array $columns
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function findWhereIn($field, array $values, $columns = ['*'])
    {

        return $this->doQuery()->whereIn($field, $values)->get($columns);
    }

    /**
     * @param $field
     * @param array $values
     * @param array $columns
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection|static[]
     */
    public function findWhereNotIn($field, array $values, $columns = ['*'])
    {

        return $this->doQuery()->whereNotIn($field, $values)->get($columns);
    }

    /**
     * @param $relations
     * @return $this
     */
    public function scopeWith($relations)
    {

        $args = is_string($relations) ? func_get_args() : $relations;

        $this->scopeDoQuery(function($query) use(&$args){

            return $query->with($args);
        });

        return $this;
    }

    /**
     * @param string $column
     * @param string $direction
     * @return $this
     */
    public function scopeOrderBy($column = 'id', $direction = 'desc')
    {
        $this->scopeDoQuery(function($query) use ($column, $direction){

            return $query->orderBy($column, strtolower($direction) == 'desc' ? 'desc' : 'asc');

        });

        return $this;
    }

    /**
     * @param $limit
     * @return $this
     */
    public function scopeLimit($limit)
    {
        $this->scopeDoQuery(function($query) use ($limit){

            return $query->limit($limit);

        });

        return $this;
    }

    /**
     * @param array $data
     * @return \Illuminate\Database\Eloquent\Model
     */
    protected function saveModel(array $data)
    {

        foreach ($data as $key => $value) {
            $this->model->$key = $value;
        }

        $this->model->save();

        return $this->model;
    }

    /**
     * @param $id
     * @param array $fillables
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Database\Eloquent\Collection|Model
     */
    public function updateExistsRequest($id, array $fillables, \Illuminate\Http\Request $request)
    {

        $register = $this->doQuery()->findOrFail($id);
        $dataRequest = $request->all();

        foreach($fillables as $fillable) {

            if(array_has($dataRequest, $fillable)) {
                $register->$fillable = $request->get($fillable);
            }

        }

        $register->save();

        return $register;

    }

    /**
     * Execute the query as a "select" statement.
     *
     * @param  \srgafanhoto\PatternRepository\Criteria\Criteria[] $criteria
     * @param  array $columns
     * @return \Illuminate\Support\Collection
     */
    protected function get($criteria = [], $columns = ['*'])
    {

        return $this->matching($criteria)->get($columns);
    }

    /**
     * @return Builder
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @return string
     */
    public function getTable()
    {
        return $this->getModel()->getTable();
    }

    /**
     * Execute the query and get the first result.
     *
     * @param  \srgafanhoto\PatternRepository\Criteria\Criteria[] $criteria
     * @param  array $columns
     * @return Model|static|null
     */
    protected function first($criteria = [], $columns = ['*'])
    {

        return $this->matching($criteria)->first($columns);
    }

    /**
     * Paginate the given query.
     *
     * @param  \srgafanhoto\PatternRepository\Criteria\Criteria[] $criteria
     * @param  int $perPage
     * @param  array $columns
     * @param  string $pageName
     * @param  int|null $page
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     *
     * @throws \InvalidArgumentException
     */
    protected function paginate($criteria = [], $perPage = null, $columns = ['*'], $pageName = 'page', $page = null)
    {

        return $this->matching($criteria)->paginate($perPage, $columns, $pageName, $page);
    }

    /**
     * Paginate the given query into a simple paginator.
     *
     * @param  \srgafanhoto\PatternRepository\Criteria\Criteria[] $criteria
     * @param  int $perPage
     * @param  array $columns
     * @param  string $pageName
     * @param  int|null $page
     * @return \Illuminate\Contracts\Pagination\Paginator
     */
    protected function simplePaginate(
        $criteria = [],
        $perPage = null,
        $columns = ['*'],
        $pageName = 'page',
        $page = null
    ) {

        return $this->matching($criteria)->simplePaginate($perPage, $columns, $pageName, $page);
    }

    /**
     * Retrieve the "count" result of the query.
     *
     * @param  \srgafanhoto\PatternRepository\Criteria\Criteria[] $criteria
     * @param  string $columns
     * @return int
     */
    protected function count($criteria = [], $columns = '*')
    {

        return (int) $this->matching($criteria)->count($columns);
    }

    /**
     * matching.
     *
     * @param  \srgafanhoto\PatternRepository\Criteria\Criteria[] $criteria
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function matching($criteria)
    {

        $criteria = is_array($criteria) === false ? [$criteria] : $criteria;

        return array_reduce($criteria, function ($query, Criteria $criteria) {

            $criteria->each(function ($method) use ($query) {

                call_user_func_array([$query, $method->name], $method->parameters);
            });

            return $query;
        }, $this->doQuery());
    }

    /**
     * getModel.
     *
     * @return Model
     */
    protected function getModel()
    {

        return $this->model instanceof Model
            ? clone $this->model
            : $this->model->getModel();
    }

    /**
     * Create a new model instance that is existing.
     *
     * @param array $attributes
     * @param bool $exists
     * @return Model
     */
    protected function newInstance($attributes = [], $exists = false)
    {

        return $this->getModel()->newInstance($attributes, $exists);
    }

    /**
     * @param callable|null $callback
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDoQuery(callable $callback = null)
    {

        if(is_callable($callback)) {

            /**
             * @var Builder
             */
            $callbackBuilder = $callback($this->newQuery());
            $this->query = $callbackBuilder;

            return $this->doQuery();

        }

        return $this->doQuery();
    }

    /**
     * Get a new query builder for the model's table.
     *
     * @return Model|\Illuminate\Database\Query\Builder
     */
    protected function newQuery()
    {

        return $this->model instanceof Model
            ? $this->query->newQuery()
            : clone $this->model;
    }

    /**
     * Get a new query builder for the model's table.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function doQuery()
    {

        return $this->newQuery();
    }

    /**
     * Remove the scope from the given Eloquent query builder.
     *
     * @param $paramWhere
     * @param $value
     * @return Builder
     */
    protected function removeBuilderWhereIfValue($paramWhere, $value)
    {
        $query = $this->query->getQuery();

        $bindings = $query->getRawBindings()['where'];
        $bindingKey = 0;

        foreach ((array)$query->wheres as $key => $where) {

            if ($where['type'] === 'Basic' && $where['column'] === $paramWhere && $where['value'] === $value) {
                unset($query->wheres[$key]); //remove wheres
                unset($bindings[$bindingKey]); //remove where bindings
            }
            if ( ! in_array($where['type'], ['Null', 'NotNull'])) {
                $bindingKey++;
            }
        }

        $query->wheres = array_values($query->wheres); //reset wheres
        $query->setBindings($bindings); //reset where bindings

        $this->query->setQuery($query);

        return $this->query;
    }

    /**
     * @return \srgafanhoto\PatternRepository\Criteria\Criteria
     */
    protected function newCriteria()
    {

        return Criteria::create($this->newInstance());
    }

    /**
     * @return \srgafanhoto\PatternRepository\Criteria\Criteria
     */
    protected function doCriteria()
    {

        return $this->newCriteria();
    }

    /**
     * Validate data
     *
     * @param $validator
     * @param $data
     * @throws RepositoryException
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function validate($validator, array $data)
    {

        $instance = app()->make($validator);
        if (! $instance instanceof Validator) {

            throw new RepositoryException("Class {$validator} must be an instance of PatternRepository\\Repository\\Validator\\Validator");
        }

        return $instance->validate($data);
    }

    /**
     * Create a new register
     *
     * @param $data
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Query\Builder
     * @throws \Exception
     */
    public function create($data)
    {

        try {

            DB::beginTransaction();

            $create = $this->newQuery()->create($data);

            DB::commit();

            return $create;

        } catch (\Exception $e) {

            DB::rollback();

            throw $e;
        }

    }

    /**
     * Update data
     *
     * @param $id
     * @param $data
     * @return bool|int
     * @throws \Exception
     */
    public function update($id, $data)
    {

        try {

            DB::beginTransaction();

            $update = $this->findOrFail($id)->update($data);

            DB::commit();

            return $update;

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {

            DB::rollback();

            throw $e;

        } catch (\Exception $e) {

            DB::rollback();

            throw $e;
        }

    }
}
