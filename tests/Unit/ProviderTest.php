<?php declare(strict_types=1);

namespace BayAreaWebPro\JsonWebToken\Tests\Unit;

use BayAreaWebPro\JsonWebToken\JsonWebToken;
use BayAreaWebPro\JsonWebToken\JsonWebTokenService;
use BayAreaWebPro\JsonWebToken\JsonWebTokenServiceProvider;
use BayAreaWebPro\JsonWebToken\Tests\TestCase;

class ProviderTest extends TestCase
{
    public function test_provider_is_registered()
    {
        $this->assertInstanceOf(
            JsonWebTokenServiceProvider::class,
            $this->app->getProvider(JsonWebTokenServiceProvider::class),
            'Provider is registered with container.'
        );
    }

    public function test_provider_declares_provided()
    {
        $this->assertTrue(in_array('simple-jwt',
                collect(app()->getProviders(JsonWebTokenServiceProvider::class))
                ->first()
                ->provides()
        ), 'Provider declares provided services.');
    }

    public function test_container_can_resolve_instance()
    {
        $this->assertInstanceOf(
            JsonWebTokenService::class,
            $this->app->make('simple-jwt'),
            'Container can make instance of service.'
        );
    }

    public function test_alias_can_resolve_instance()
    {
        $this->assertInstanceOf(
            JsonWebTokenService::class,
            \JsonWebToken::getFacadeRoot(),
            'Alias class can make instance of service.'
        );
    }

    public function test_facade_can_resolve_instance()
    {
        $this->assertInstanceOf(
            JsonWebTokenService::class,
            JsonWebToken::getFacadeRoot(),
            'Facade can make instance of service.'
        );
    }
}
