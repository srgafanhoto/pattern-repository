<?php

namespace srgafanhoto\PatternRepository\Criteria;

use srgafanhoto\PatternRepository\Exceptions\CriteriaColumnSortableException;
use srgafanhoto\PatternRepository\Traits\CriteriaFromSearchTrait;
use Closure;
use BadMethodCallException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;
use srgafanhoto\PatternRepository\Requests\SearchRequest;
use srgafanhoto\PatternRepository\Concerns\CustomQueries;
use srgafanhoto\PatternRepository\Concerns\BuildsQueries;
use srgafanhoto\PatternRepository\Concerns\SoftDeletingScope;
use srgafanhoto\PatternRepository\Concerns\QueriesRelationships;
use srgafanhoto\PatternRepository\Concerns\EloquentBuildsQueries;
use srgafanhoto\PatternRepository\Exceptions\RepositoryException;
use Illuminate\Support\Facades\Schema;

class Criteria
{

    use BuildsQueries,
        QueriesRelationships,
        EloquentBuildsQueries,
        SoftDeletingScope,
        CustomQueries,
        CriteriaFromSearchTrait;

    /**
     * Model
     *
     */
    private $model;

    /**
     * $methods.
     *
     * @var \srgafanhoto\PatternRepository\Criteria\Method[]
     */
    protected $methods = [];

    /**
     * Criteria constructor.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     */
    public function __construct(Model $model)
    {

        $this->model = $model;
    }

    /**
     * Handle dynamic method calls into the method.
     *
     * @param  string $method
     * @param  array $parameters
     * @return mixed
     *
     * @throws \BadMethodCallException
     */
    public function __call($method, $parameters)
    {

        if (Str::startsWith($method, 'where')) {
            return $this->dynamicWhere($method, $parameters);
        }

        $className = static::class;

        throw new BadMethodCallException("Call to undefined method {$className}::{$method}()");
    }

    /**
     * create.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return static
     */
    public static function create(Model $model)
    {

        return new static($model);
    }

    /**
     * alias raw.
     *
     * @param mixed $value
     * @return Expression
     */
    public static function expr($value)
    {

        return static::raw($value);
    }

    /**
     * @param mixed $value
     * @return \srgafanhoto\PatternRepository\Criteria\Expression
     */
    public static function raw($value)
    {

        return new Expression($value);
    }

    /**
     * each.
     *
     * @param  Closure $callback
     * @return void
     */
    public function each(Closure $callback)
    {

        foreach ($this->methods as $method) {
            $callback($method);
        }
    }

    /**
     * toArray.
     *
     * @return array
     */
    public function toArray()
    {

        return array_map(function ($method) {

            return [
                'method'     => $method->name,
                'parameters' => $method->parameters,
            ];
        }, $this->methods);
    }

}
