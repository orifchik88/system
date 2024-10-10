<?php

namespace App\Providers;


use App\Repositories\ClaimRepository;
use App\Repositories\Interfaces\ClaimRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->bind(ClaimRepositoryInterface::class,ClaimRepository::class);
    }
}
