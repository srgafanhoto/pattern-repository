<?php

namespace srgafanhoto\PatternRepository\Providers;


use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

class SortLinkServiceProvider extends ServiceProvider
{

    public function register()
    {

        Blade::directive('sortlink', function ($expression) {
            $expression = ($expression[0] === '(') ? substr($expression, 1, -1) : $expression;
            return "<?php echo \srgafanhoto\PatternRepository\Blade\SortLink::render(array ({$expression}));?>";
        });

    }

}
