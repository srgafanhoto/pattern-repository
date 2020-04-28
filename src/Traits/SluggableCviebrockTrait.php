<?php

namespace srgafanhoto\PatternRepository\Traits;

/**
 * Class SluggableScopeHelpers
 *
 * Helper trait for defining the primary slug of a model
 * and providing useful scopes and query methods.
 *
 * @package Cviebrock\EloquentSluggable
 */
trait SluggableCviebrockTrait
{

    /**
     * Primary slug column of this model.
     *
     * @return string
     */
    public function getSlugKeyName()
    {
        if (property_exists($this->model, 'slugKeyName')) {
            return $this->model->slugKeyName;
        }

        $config = $this->model->sluggable();
        $name = reset($config);
        $key = key($config);

        // check for short configuration
        if ($key === 0) {
            return $name;
        }

        return $key;
    }

    /**
     * Primary slug value of this model.
     *
     * @return string
     */
    public function getSlugKey()
    {
        return $this->model->getAttribute($this->getSlugKeyName());
    }

    /**
     * Query for finding a model by its primary slug.
     *
     * @param string $slug
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function whereSlug($slug)
    {
        return $this->doQuery()->where($this->getSlugKeyName(), $slug);
    }

    /**
     * Find a model by its primary slug.
     *
     * @param string $slug
     * @param array $columns
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|static[]|static|null
     */
    public function findBySlug($slug, array $columns = ['*'])
    {
        return $this->whereSlug($slug)->first($columns);
    }

    /**
     * Find a model by its primary slug or throw an exception.
     *
     * @param string $slug
     * @param array $columns
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findBySlugOrFail($slug, array $columns = ['*'])
    {
        return $this->whereSlug($slug)->firstOrFail($columns);
    }
}
