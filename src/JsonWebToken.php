<?php declare(strict_types=1);

namespace BayAreaWebPro\JsonWebToken;

use Illuminate\Support\Facades\Facade;

/**
 * @method static string extendToken(\Illuminate\Support\Collection $token, \Carbon\Carbon $carbon, array $claims = [])
 * @method static string createForUser(Model $user, ?\Carbon\Carbon $expires = null, array $data = [])
 * @method static void register(string $model, string $keyName = 'token')
 * @method static \Illuminate\Support\Collection parseToken(string $token)
 * @method static string generateSecret(int $length)
 * @method static void rejectionHandler(\Closure $closure)
 * @see \BayAreaWebPro\JsonWebToken\JsonWebTokenService
 */
class JsonWebToken extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'simple-jwt';
    }
}
