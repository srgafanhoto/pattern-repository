<?php

namespace srgafanhoto\PatternRepository;

use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{

    /**
     * {@inheritdoc}
     */
    public function register()
    {

        $this->app->register(Providers\CustomBuilderServiceProvider::class);
        $this->app->register(Providers\SortLinkServiceProvider::class);
        $this->app->register(Providers\ValidatorServiceProvider::class);
    }

}
