<?php

namespace srgafanhoto\PatternRepository\Traits;

use BadMethodCallException;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use srgafanhoto\PatternRepository\Requests\SearchRequest;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use srgafanhoto\PatternRepository\Exceptions\RepositoryException;
use Illuminate\Support\Facades\Schema as SchemaFacade;
use srgafanhoto\PatternRepository\Exceptions\CriteriaColumnSortableException;

/**
 * Class CriteriaFromSearchTrait
 *
 * Esta classe é uma adaptação da implementação
 * da classe RequestCriteria do l5-repository e
 * do plugin column-sortable
 *
 * @see https://github.com/andersao/l5-repository
 * @see https://github.com/Kyslik/column-sortable/blob/L5.5-6/src/ColumnSortable/SortableLink.php
 * @package srgafanhoto\PatternRepository\Traits
 */
trait CriteriaFromSearchTrait
{

    /**
     * $methods.
     *
     * @var \Illuminate\Http\Request
     */
    private $request;

    /**
     * @param array $fieldsSearchable
     * @param null $defaultOrder
     * @param string $defaultSort
     * @return $this
     * @throws \srgafanhoto\PatternRepository\Exceptions\CriteriaColumnSortableException
     * @throws \srgafanhoto\PatternRepository\Exceptions\RepositoryException
     * @throws \Exception
     */
    public function fromRequest(array $fieldsSearchable = [], $defaultOrder = null, $defaultSort = 'asc')
    {

        $this->request = app(SearchRequest::class);

        $this->applySearch($fieldsSearchable);
        $this->applyOrder($defaultOrder, $defaultSort);
        $this->applyFilter();
        $this->applyWith();

        return $this;
    }

    /**
     * @param $fieldsSearchable
     * @throws \srgafanhoto\PatternRepository\Exceptions\RepositoryException
     */
    private function applySearch(array $fieldsSearchable)
    {

        $search = $this->request->get('search', null);
        $searchFields = $this->request->get('searchFields', null);
        $searchJoin = $this->request->get('searchJoin', null);

        if ($search) {

            $searchFields = is_array($searchFields) || is_null($searchFields) ? $searchFields : explode(';', $searchFields);
            $fields = $this->parserFieldsSearch($fieldsSearchable, $searchFields);
            $isFirstField = true;
            $searchData = $this->parserSearchData($search);
            $search = $this->parserSearchValue($search);
            $modelForceAndWhere = strtolower($searchJoin) === 'and';

            $this->where(function ($query) use (
                $fields,
                $search,
                $searchData,
                $isFirstField,
                $modelForceAndWhere
            ) {

                /** @var Builder $query */
                foreach ($fields as $field => $condition) {
                    if (is_numeric($field)) {
                        $field = $condition;
                        $condition = "=";
                    }
                    $value = null;
                    $condition = trim(strtolower($condition));
                    if (isset($searchData[$field])) {
                        $value = ($condition == "like" || $condition == "ilike") ? "%{$searchData[$field]}%" : $searchData[$field];
                    } else {
                        if (! is_null($search)) {
                            $value = ($condition == "like" || $condition == "ilike") ? "%{$search}%" : $search;
                        }
                    }
                    $relation = null;
                    if (stripos($field, '.')) {
                        $explode = explode('.', $field);
                        $field = array_pop($explode);
                        $relation = implode('.', $explode);
                    }
                    $modelTableName = $query->getModel()->getTable();
                    if ($isFirstField || $modelForceAndWhere) {
                        if (! is_null($value)) {
                            if (! is_null($relation)) {
                                $query->whereHas($relation, function (Builder $query) use ($field, $condition, $value) {

                                    $query->where($field, $condition, $value);
                                });
                            } else {
                                $query->where($modelTableName.'.'.$field, $condition, $value);
                            }
                            $isFirstField = false;
                        }
                    } else {
                        if (! is_null($value)) {
                            if (! is_null($relation)) {
                                $query->orWhereHas($relation, function (Builder $query) use ($field, $condition, $value) {

                                    $query->where($field, $condition, $value);
                                });
                            } else {
                                $query->orWhere($modelTableName.'.'.$field, $condition, $value);
                            }
                        }
                    }
                }
            });

        }

    }

    /**
     * @param $defaultOrder
     * @param $defaultSort
     * @throws \srgafanhoto\PatternRepository\Exceptions\CriteriaColumnSortableException
     * @throws \Exception
     */
    private function applyOrder($defaultOrder, $defaultSort)
    {

        $orderBy = $this->request->get('sort', $defaultOrder);
        $sortedBy = $this->request->get('order', $defaultSort);
        $sortedBy = ! empty($sortedBy) ? $sortedBy : $defaultSort;

        if (isset($orderBy) && ! empty($orderBy)) {

            $this->queryOrderBuilder($orderBy, $sortedBy);

        }

    }

    private function applyFilter()
    {

        $filter = $this->request->get('filter', null);

        if (isset($filter) && ! empty($filter)) {

            if (is_string($filter)) {
                $filter = explode(';', $filter);
            }

            $this->select($filter);
        }

    }

    private function applyWith()
    {

        $with = $this->request->get('with', null);

        if ($with) {

            $with = explode(';', $with);
            $this->with($with);
        }

    }

    /**************************************/
    /*          SEARCH HELPERS            */
    /**************************************/

    /**
     * @param $search
     *
     * @return array
     */
    private function parserSearchData($search)
    {

        $searchData = [];

        if (stripos($search, ':')) {
            $fields = explode(';', $search);

            foreach ($fields as $row) {
                try {
                    list($field, $value) = explode(':', $row);
                    $searchData[$field] = $value;
                } catch (\Exception $e) {
                    //Surround offset error
                }
            }
        }

        return $searchData;
    }

    /**
     * @param $search
     * @return null
     */
    private function parserSearchValue($search)
    {

        if (stripos($search, ';') || stripos($search, ':')) {
            $values = explode(';', $search);
            foreach ($values as $value) {
                $s = explode(':', $value);
                if (count($s) == 1) {
                    return $s[0];
                }
            }

            return null;
        }

        return $search;
    }

    /**
     * @param array $fields
     * @param array|null $searchFields
     * @return array
     * @throws \srgafanhoto\PatternRepository\Exceptions\RepositoryException
     */
    private function parserFieldsSearch(array $fields = [], array $searchFields = null)
    {

        if (! is_null($searchFields) && count($searchFields)) {
            $acceptedConditions = [
                '=',
                'like',
            ];
            $originalFields = $fields;
            $fields = [];

            foreach ($searchFields as $index => $field) {
                $field_parts = explode(':', $field);
                $temporaryIndex = array_search($field_parts[0], $originalFields);

                if (count($field_parts) == 2) {
                    if (in_array($field_parts[1], $acceptedConditions)) {
                        unset($originalFields[$temporaryIndex]);
                        $field = $field_parts[0];
                        $condition = $field_parts[1];
                        $originalFields[$field] = $condition;
                        $searchFields[$index] = $field;
                    }
                }
            }

            foreach ($originalFields as $field => $condition) {
                if (is_numeric($field)) {
                    $field = $condition;
                    $condition = "=";
                }
                if (in_array($field, $searchFields)) {
                    $fields[$field] = $condition;
                }
            }

            if (count($fields) == 0) {
                throw new RepositoryException(Str::replaceArray('As colunas :field não são aceitas nessa consulta.', [
                    'field' => implode(',', $searchFields),
                ], 'Repositório'));
            }

        }

        return $fields;
    }

    /**
     * @param $column
     * @param $direction
     * @throws \srgafanhoto\PatternRepository\Exceptions\CriteriaColumnSortableException
     * @throws \Exception
     */
    private function queryOrderBuilder($column, $direction)
    {

        /** @var Model $model */
        $model = $this->model;
        $explodeResult = $this->explodeSortParameter($column);

        if (! empty($explodeResult)) {

            $this->select($model->getTable() . '.*');

            $relation = null;
            $column = array_pop($explodeResult);
            $relations = $explodeResult;
            $relationPath = implode('.', $relations);

            try {

                /** @var $model Model */
                $model = $this->model;

                foreach($relations as $relation) {

                    /** @var $relation HasOne|BelongsTo */
                    $relation = $model->with($relation)->getRelation($relation);

                    $this->queryJoinBuilder($model, $relation);

                    $model = $relation->getModel();

                }

            } catch (BadMethodCallException $e) {

                throw new CriteriaColumnSortableException($relationPath, 1, $e);
            } catch (\Exception $e) {

                throw $e;
            }

            $model = $relation->getRelated();
        }

        if ($this->columnExists($model, $column)) {
            $column = $model->getTable().'.'.$column;
            $this->orderBy($column, $direction);
        }

    }

    /**
     * @param $parameter
     * @return array
     */
    public static function explodeSortParameter($parameter)
    {

        $separator = '.';
        if (Str::contains($parameter, $separator)) {
            $oneToOneSort = explode($separator, $parameter);

            return $oneToOneSort;
        }

        return [];
    }

    /**
     * @param \Illuminate\Database\Query\Builder $query
     * @param HasOne|BelongsTo $relation
     *
     * @return \Illuminate\Database\Query\Builder
     *
     * @throws \Exception
     */
    private function queryJoinBuilder($query, $relation)
    {


        $relatedTable = $relation->getRelated()->getTable();
        $parentTable = $relation->getParent()->getTable();

        if ($parentTable === $relatedTable) {
            $query->from($parentTable.' as parent_'.$parentTable);
            $parentTable = 'parent_'.$parentTable;
            $relation->getParent()->setTable($parentTable);
        }
        if ($relation instanceof HasOne) {
            $relatedPrimaryKey = $relation->getQualifiedForeignKeyName();
            $parentPrimaryKey = $relation->getQualifiedParentKeyName();

            return $this->formJoin($relatedTable, $parentPrimaryKey, $relatedPrimaryKey);
        } elseif ($relation instanceof BelongsTo) {

            $relatedPrimaryKey = $relation->getQualifiedOwnerKeyName();
            $parentPrimaryKey = $relation->getQualifiedForeignKeyName();

            return $this->formJoin($relatedTable, $parentPrimaryKey, $relatedPrimaryKey);
        } else {
            throw new \Exception("Tipo de ordenação inválida");
        }
    }

    /**
     * @param $relatedTable
     * @param $parentPrimaryKey
     * @param $relatedPrimaryKey
     * @return mixed
     */
    private function formJoin($relatedTable, $parentPrimaryKey, $relatedPrimaryKey)
    {

        $joinType = 'leftJoin';

        return $this
            ->{$joinType}($relatedTable, $parentPrimaryKey, '=', $relatedPrimaryKey);
    }

    /**
     * @param $model
     * @param $column
     *
     * @return bool
     */
    private function columnExists(Model $model, $column)
    {

        return (isset($model->sortable)) ? in_array($column, $model->sortable) :
            SchemaFacade::connection($model->getConnectionName())->hasColumn($model->getTable(), $column);
    }
}
