<?php

namespace srgafanhoto\PatternRepository\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;

class ValidatorServiceProvider extends ServiceProvider
{

    public function boot()
    {

        Validator::extend('user_password_equals', function ($field, $value, $parameters) {

            return \Hash::check($value, auth()->user()->password);
        });

        Validator::replacer('user_password_equals', function ($message, $attribute, $rule, $parameters) {

            return "A senha digitada no campo :attribute não corresponde à senha atual do banco de dados";
        });

    }
}
