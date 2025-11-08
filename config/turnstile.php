<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Turnstile Site Key
    |--------------------------------------------------------------------------
    |
    | The site key from your Cloudflare Turnstile dashboard
    |
    */
    'site_key' => env('TURNSTILE_SITE_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | Turnstile Secret Key
    |--------------------------------------------------------------------------
    |
    | The secret key from your Cloudflare Turnstile dashboard
    |
    */
    'secret_key' => env('TURNSTILE_SECRET_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | Default Theme
    |--------------------------------------------------------------------------
    |
    | Options: light, dark, auto
    |
    */
    'default_theme' => env('TURNSTILE_THEME', 'light'),
];