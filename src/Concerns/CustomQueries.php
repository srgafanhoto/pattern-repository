<?php

namespace srgafanhoto\PatternRepository\Concerns;

use Carbon\Carbon;
use srgafanhoto\PatternRepository\Criteria\Method;

trait CustomQueries
{

    public function whereDateFormat($column, $operator, $value, $format = 'd/m/Y')
    {

        $value = Carbon::createFromFormat($format, $value);

        $this->methods[] = new Method('where', [$column, $operator, $value]);

        return $this;
    }

    public function orWhereDateFormat($column, $operator, $value, $format = 'd/m/Y')
    {

        $value = Carbon::createFromFormat($format, $value);

        $this->methods[] = new Method('orWhere', [$column, $operator, $value]);

        return $this;
    }

}
