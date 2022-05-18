<?php

namespace Att\Workit;

use Att\Workit\Commands\MakeRepositoryCommand;
use Att\Workit\Commands\MakeServiceCommand;
use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;

class WorkItServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__.'/Gateway.php');

        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeServiceCommand::class,
                MakeRepositoryCommand::class,
            ]);
        }

        File::copyDirectory(__DIR__.'/stubs', base_path('stubs'));
    }
}
