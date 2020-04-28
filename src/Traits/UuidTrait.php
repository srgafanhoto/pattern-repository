<?php

namespace srgafanhoto\PatternRepository\Traits;

/**
 * Class UuidTrait
 *
 * Helper trait to help uuids queries
 *
 */
trait UuidTrait
{

    /**
     * Find a model by its primary slug.
     *
     * @param string $uuid
     * @param array $columns
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|static[]|static|null
     */
    public function findByUuid($uuid, array $columns = ['*'])
    {
        return $this->doQuery()->whereUuid($uuid)->first($columns);
    }

    /**
     * Find a model by its primary slug or throw an exception.
     *
     * @param string $uuid
     * @param array $columns
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findByUuidOrFail($uuid, array $columns = ['*'])
    {
        return $this->doQuery()->whereUuid($uuid)->firstOrFail($columns);
    }

}
