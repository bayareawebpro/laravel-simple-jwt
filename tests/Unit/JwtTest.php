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
            ->assertStatus(401)
        ;
    }

    public function test_authorized_user()
    {
        $user = factory(MockUser::class)->create();
        $token = JsonWebToken::createForUser($user);
        $this
            ->json('GET',"/api/user?token={$token}")
            ->assertJson($user->toArray())
            ->assertStatus(200)
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
        $this->assertSame($expires->toDateTimeString(), $token->get('expires'));
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
            ->assertStatus(401)
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
        $this->assertSame($newToken->get('expires'), $extended->toDateTimeString());
        $this->assertNotSame($token->get('expires'), $newToken->get('expires'));
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
        $this->assertIsString(request()->jwt('expires'));
    }
}
