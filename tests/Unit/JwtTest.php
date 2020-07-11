<?php namespace BayAreaWebPro\JsonWebToken\Tests\Unit;

use BayAreaWebPro\JsonWebToken\JsonWebToken;
use BayAreaWebPro\JsonWebToken\Tests\Fixtures\Models\MockUser;
use BayAreaWebPro\JsonWebToken\Tests\TestCase;
use Illuminate\Support\Collection;

class JwtTest extends TestCase
{
    public function test_unauthorized_user()
    {
        $this
            ->json('GET',"/api/user?token=fake")
            ->assertUnauthorized()
        ;
    }

    public function test_authorized_user()
    {
        $user = factory(MockUser::class)->create();
        $token = JsonWebToken::createForUser($user);
        $this
            ->json('GET',"/api/user?token={$token}")
            ->assertJson($user->toArray())
            ->assertOk()
        ;
    }

    public function test_authorized_header()
    {
        $user = factory(MockUser::class)->create();
        $token = JsonWebToken::createForUser($user);
        $this
            ->json('GET',"/api/user", [], [
                'Authorization'=>"Bearer {$token}"
            ])
            ->assertJson($user->toArray())
            ->assertOk()
        ;
    }

    public function test_valid_token()
    {
        $expires = now()->addRealSeconds(60);
        $user = factory(MockUser::class)->create();

        $token = JsonWebToken::createForUser($user, $expires, ['test' => true]);
        $token = JsonWebToken::parseToken($token);

        $this->assertTrue($token->get('valid'));
        $this->assertTrue($token->get('test'));
        $this->assertSame($expires->toDateTimeString(), $token->get('exp'));
    }

    public function test_invalid_token()
    {
        $fake = JsonWebToken::parseToken('fake');
        $this->assertfalse($fake->get('valid'));
        $this->assertNull($fake->get('test'));
    }

    public function test_expired_token()
    {
        $user = factory(MockUser::class)->create();
        $token = JsonWebToken::createForUser($user, now()->subHours(1));

        $token = JsonWebToken::parseToken($token);
        $this->assertFalse($token->get('valid'));

        $this
            ->json('GET',"/api/user?token={$token}")
            ->assertUnauthorized()
        ;
    }

    public function test_extended_token()
    {
        $user = factory(MockUser::class)->create();
        $token = JsonWebToken::createForUser($user, now()->addHours(1));
        $token = JsonWebToken::parseToken($token);

        $extended = now()->addHours(2);
        $newToken = JsonWebToken::extendToken($token, $extended, ['new'=>true]);
        $newToken = JsonWebToken::parseToken($newToken);

        $this->assertTrue($newToken->get('new'));
        $this->assertTrue($newToken->get('valid'));
        $this->assertSame($newToken->get('exp'), $extended->toDateTimeString());
        $this->assertNotSame($token->get('exp'), $newToken->get('exp'));
    }

    public function test_generate_secret()
    {
        $secret = JsonWebToken::generateSecret(32);
        $this->assertIsString($secret);
        $this->assertSame(32, strlen($secret));
    }

    public function test_request_macro()
    {
        $user = factory(MockUser::class)->create();
        $token = JsonWebToken::createForUser($user, now()->addHours(1));
        request()->merge(['token' => $token]);

        $this->assertInstanceOf(Collection::class, request()->jwt());
        $this->assertIsInt(request()->jwt('user'));
        $this->assertIsBool(request()->jwt('valid'));
        $this->assertIsString(request()->jwt('exp'));
    }

    public function test_secret_command()
    {
        $this
            ->artisan('jwt:secret')
            ->expectsOutput('JWT Secret')
            ->assertExitCode(0)
            ->execute();
    }

    public function test_blacklist()
    {
        $user = factory(MockUser::class)->create();
        $token = JsonWebToken::createForUser($user, now()->addHours(1));
        $tokenData = JsonWebToken::parseToken($token);

        JsonWebToken::rejectionHandler(fn($parsed)=>$parsed->get('jti') === $tokenData->get('jti'));
        $this
            ->json('GET',"/api/user?token={$token}")
            ->assertUnauthorized()
        ;
    }
}
