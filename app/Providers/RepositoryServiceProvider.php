<?php

namespace App\Providers;


use App\Repositories\ArticleRepository;
use App\Repositories\BlockRepository;
use App\Repositories\ClaimRepository;
use App\Repositories\IllegalObjectRepository;
use App\Repositories\Interfaces\ArticleRepositoryInterface;
use App\Repositories\Interfaces\BlockRepositoryInterface;
use App\Repositories\Interfaces\ClaimRepositoryInterface;
use App\Repositories\Interfaces\IllegalObjectRepositoryInterface;
use App\Repositories\Interfaces\MonitoringRepositoryInterface;
use App\Repositories\Interfaces\RegulationRepositoryInterface;
use App\Repositories\Interfaces\ResponseRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Repositories\MonitoringRepository;
use App\Repositories\RegulationRepository;
use App\Repositories\ResponseRepository;
use App\Repositories\UserRepository;
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
        $this->app->bind(IllegalObjectRepositoryInterface::class,IllegalObjectRepository::class);
        $this->app->bind(ArticleRepositoryInterface::class,ArticleRepository::class);
        $this->app->bind(BlockRepositoryInterface::class,BlockRepository::class);
        $this->app->bind(UserRepositoryInterface::class,UserRepository::class);
        $this->app->bind(RegulationRepositoryInterface::class,RegulationRepository::class);
        $this->app->bind(MonitoringRepositoryInterface::class,MonitoringRepository::class);
        $this->app->bind(ResponseRepositoryInterface::class,ResponseRepository::class);
    }
}
