<?php

namespace srgafanhoto\PatternRepository\Concerns;

use srgafanhoto\PatternRepository\Criteria\Method;

trait SoftDeletingScope
{
    /**
     * Add the with-trashed extension to the builder.
     *
     * @return $this
     */
    public function withTrashed()
    {
        $this->methods[] = new Method(__FUNCTION__);

        return $this;
    }

    /**
     * Add the without-trashed extension to the builder.
     *
     * @return $this
     */
    public function withoutTrashed()
    {
        $this->methods[] = new Method(__FUNCTION__);

        return $this;
    }

    /**
     * Add the only-trashed extension to the builder.
     *
     * @return $this
     */
    public function onlyTrashed()
    {
        $this->methods[] = new Method(__FUNCTION__);

        return $this;
    }
}
