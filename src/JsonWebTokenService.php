<?php declare(strict_types=1);

namespace BayAreaWebPro\JsonWebToken;

use stdClass;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Database\Eloquent\Model;

/**
 * JsonWebToken RFC Testing
 * @url https://jwt.io/
 */
class JsonWebTokenService
{
    /**
     * Register The Model & Macros
     * @param string $model
     * @param string $keyName
     */
    public static function register(string $model, string $keyName = 'token'): void
    {
        Auth::viaRequest('simple-jwt', function (Request $request) use ($model) {
            $token = $request->jwt();
            if ($token->get('valid')) {
                return $model::query()->find($token->get('user'));
            }
        });

        Request::macro('jwt', function (?string $key = null) use ($keyName) {
            $app = App::getFacadeRoot();
            $token = $app->bound('jwt-decoded')
                ? $app->get('jwt-decoded')
                : $app->instance('jwt-decoded', JsonWebTokenService::parseToken(
                    request()->bearerToken() ?? $this->get($keyName)
                ));
            if ($key) {
                return $token->get($key);
            }
            return $token;
        });
    }

    /**
     * Create a new token for a model.
     * @param Model $user
     * @param array $data
     * @param Carbon|null $expires
     * @return string
     */
    public static function createForUser(Model $user, ?Carbon $expires = null, array $data = []): string
    {
        $headers = static::getHeaders();
        $payload = static::encodeData(array_merge([
            "expires" => ($expires ?? Carbon::now()->addDays(30))->toDateTimeString(),
            "user"    => $user->getKey(),
        ], $data));

        return "{$headers}.{$payload}." . static::createSignature("{$headers}.{$payload}");
    }

    /**
     * Parse the token to collection instance.
     * @param string $token
     * @return Collection
     */
    public static function parseToken(?string $token = null): Collection
    {
        if (Str::substrCount($token, '.') === 2) {
            list($headers, $payload, $signature) = explode('.', $token);
            if (static::verifySignature("{$headers}.{$payload}", $signature)) {
                $tokenProperties = Collection::make(static::decodeData($payload));
                $tokenProperties->put('valid', static::isValidTimestamp($tokenProperties->get('expires')));
                return $tokenProperties;
            }
        }
        return Collection::make(['valid' => false]);
    }

    /**
     * Decode Data
     * @param string $value
     * @return stdClass
     */
    protected static function decodeData(string $value): stdClass
    {
        return json_decode(base64_decode(strtr($value, '-_', '+/')));
    }

    /**
     * Encode Data
     * @param array $value
     * @return string
     */
    protected static function encodeData(array $value): string
    {
        return rtrim(strtr(base64_encode(json_encode($value)), '+/', '-_'), '=');
    }

    /**
     * Create HMAC Signature
     * @param string $value
     * @return string
     */
    protected static function createSignature(string $value): string
    {
        return hash_hmac(static::getAlgorithm(), $value, static::getEncryptionKey(), false);
    }

    /**
     * Get Encryption Key
     * @return string
     */
    protected static function getEncryptionKey(): string
    {
        return base64_encode(Config::get('jwt.secret'));
    }

    /**
     * Get Encryption Key
     * @return string
     */
    protected static function getAlgorithm(): string
    {
        return Config::get('jwt.algorithm.alias');
    }

    /**
     * Make Encryption Key
     * @param int $length
     * @return string
     */
    public static function generateSecret(int $length = 64): string
    {
        return Str::random($length);
    }

    /**
     * Verify HMAC Signature
     * @param string $payload
     * @param string $value
     * @return bool
     */
    protected static function verifySignature(string $payload, string $value): bool
    {
        return hash_equals($value, static::createSignature($payload));
    }

    /**
     * Extend Expires Claim
     * @param Collection $token
     * @param Carbon $carbon
     * @param array $claims
     * @return string
     */
    public static function extendToken(Collection $token, Carbon $carbon, array $claims = []): string
    {
        $headers = static::getHeaders();
        $payload = static::encodeData(
            $token
                ->toBase()
                ->except(['valid', 'alg', 'typ'])
                ->put('expires', $carbon->toDateTimeString())
                ->merge($claims)
                ->toArray()
        );
        return "{$headers}.{$payload}." . static::createSignature("{$headers}.{$payload}");
    }

    /**
     * Get the token headers.
     * @return string
     */
    protected static function getHeaders(): string
    {
        return static::encodeData([
            "typ" => "JWT",
            "alg" => Config::get('jwt.algorithm.name'),
        ]);
    }

    /**
     * Is the timestamp claim valid.
     * @param string|null $timestamp
     * @return bool
     */
    protected static function isValidTimestamp(?string $timestamp = null): bool
    {
        return Carbon::parse($timestamp)->greaterThanOrEqualTo(Carbon::now());
    }
}
