<?php declare(strict_types=1);

namespace BayAreaWebPro\JsonWebToken;

use BayAreaWebPro\JsonWebToken\Commands\GenerateSecretCommand;
use Illuminate\Support\ServiceProvider;

class JsonWebTokenServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'jwt');

        if ($this->app->runningInConsole()) {
            $this->commands([
                GenerateSecretCommand::class
            ]);
            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('jwt.php'),
            ], 'config');
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->app->bind('simple-jwt', JsonWebTokenService::class);
    }

    /**
     * Get the services provided by the provider.
     * @return array
     */
    public function provides()
    {
        return [
            'simple-jwt'
        ];
    }
}
