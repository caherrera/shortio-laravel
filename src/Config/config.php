<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Api
    |--------------------------------------------------------------------------
    |
    | This is host api for short.io
    |
    */

    'api' => env('SHORTIO_API', 'api.short.cm'),

    /*
    |--------------------------------------------------------------------------
    | Secure Protocol
    |--------------------------------------------------------------------------
    |
    | indicate if use secure protocol
    |
    */

    'secure'  => env('SHORTIO_SECURE', true),


    /*
    |--------------------------------------------------------------------------
    | Secret Key
    |--------------------------------------------------------------------------
    |
    | This key is private and gives access to all your links. Please do not
    | share this key with people you do not trust. Do not include it in HTML
    | and JavaScript webpages and iPhone/Android apps.
    |
    */
    'secret'  => env('SHORTIO_KEY', null),

    /*
    |--------------------------------------------------------------------------
    | Public Key
    |--------------------------------------------------------------------------
    |
    | This key allows only to create a link on your domains. It is safe to
    | include it in your frontend javascript code, iPhone, Android or desktop
    | apps. Please note, if you will include this key in your public source
    | code, everyone can create links on your domains. Please use it only if
    | you expect this behavior.
    |
    */
    'public'  => env('SHORTIO_PUBKEY', null),

    /*
    |--------------------------------------------------------------------------
    | Headers
    |--------------------------------------------------------------------------
    |
    | Any Extra Headers
    |
    */
    'headers' => env('SHORTIO_HEADERS', null),


];