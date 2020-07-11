# Laravel Package Boilerplate

![CI](https://github.com/bayareawebpro/laravel-simple-jwt/workflows/ci/badge.svg)
![Coverage](https://codecov.io/gh/bayareawebpro/laravel-simple-jwt/branch/master/graph/badge.svg)
![Downloads](https://img.shields.io/packagist/dt/bayareawebpro/laravel-simple-jwt.svg)
![Version](https://img.shields.io/github/v/release/bayareawebpro/laravel-simple-jwt.svg)
![License](https://img.shields.io/badge/License-MIT-success.svg)

```bash
composer require bayareawebpro/laravel-simple-jwt
```


### Make Encryption Secret
```php
JsonWebToken::generateSecret();
```

### Add Secret to Environment File
```php
JWT_SECRET=xxx
```

### Configure Auth.php
```
'guards' => [
    ...
    'api' => [
        'driver' => 'laravel-jwt', 
        'provider' => 'users',
        'hash' => false,
    ],
],
'jwt' => [
    'secret' => env('JWT_SECRET'),
    'algorithm' => 'sha256',
],
```

### Register in Auth Service Provider.

```php
JsonWebToken::register(User::class, 'token');
```

### Create New Token
```php
$token = JsonWebToken::createForUser(User::first(), now()->addHours(3), [
  'my_key' => true
]);
```


### Authenticate
```text
http://laravel.test/api/user?token=xxx
```

### Get Data From Token
```php
$request->jwt()->get('my_key');
$request->jwt('my_key');
```

### Extend Token Lifetime & Claims
```php
$newToken = JsonWebToken::extendToken(request()->jwt(), now()->addHours(3), ['key' => true]);
```

### Testing

``` bash
composer test
composer lint
```
