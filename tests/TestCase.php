<?php declare(strict_types=1);

namespace BayAreaWebPro\JsonWebToken\Tests;

use Illuminate\Support\Facades\Config;
use BayAreaWebPro\JsonWebToken\JsonWebToken;
use BayAreaWebPro\JsonWebToken\JsonWebTokenServiceProvider;
use BayAreaWebPro\JsonWebToken\Tests\Fixtures\Models\MockUser;

class TestCase extends \Orchestra\Testbench\TestCase
{

    /**
     * Load package service provider
     * @param Application $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [JsonWebTokenServiceProvider::class];
    }

    /**
     * Load package alias
     * @param \Illuminate\Foundation\Application $app
     * @return array
     */
    protected function getPackageAliases($app)
    {
        return [
            'JsonWebToken' => JsonWebToken::class,
        ];
    }

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();
        require __DIR__.'/Fixtures/routes.php';
        $this->withFactories(__DIR__.'/Fixtures/Factories');
        $this->loadMigrationsFrom(__DIR__ . '/Fixtures/Migrations');
        JsonWebToken::register(MockUser::class, 'token');

        Config::set('auth.guards.api',[
            'driver' => 'simple-jwt',
            'provider' => 'users',
            'hash' => false,
        ]);
    }
}
