<?php

namespace srgafanhoto\PatternRepository\Providers;

use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\ServiceProvider;

class CustomBuilderServiceProvider extends ServiceProvider
{

    public function register()
    {

        Builder::macro('whereDateFormat', function ($column, $operator, $value, $format = 'd/m/Y') {

            $value = Carbon::createFromFormat($format, $value);

            return $this->where($column, $operator, $value);

        });

        Builder::macro('orWhereDateFormat', function ($column, $operator, $value, $format = 'd/m/Y') {

            $value = Carbon::createFromFormat($format, $value);

            return $this->where($column, $operator, $value, 'or');

        });

    }

}
