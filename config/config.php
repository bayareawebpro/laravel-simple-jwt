<?php
return [
    /*
    |--------------------------------------------------------------------------
    | JsonWebToken Secret
    |--------------------------------------------------------------------------
    */
    'secret' => env('JWT_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | JsonWebToken Signature Algorithm
    | Name: HS512 is an Abbreviation of HMAC using SHA-512. (used in token header)
    | Alias: https://www.php.net/manual/en/function.hash-hmac-algos.php
    |--------------------------------------------------------------------------
    */
    'algorithm' => [
        'name' => 'HS512',
        'alias' => 'sha512',
    ]
];
