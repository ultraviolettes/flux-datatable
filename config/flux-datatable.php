<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default per-page options
    |--------------------------------------------------------------------------
    |
    | Les options de pagination proposées dans le dropdown.
    |
    */

    'per_page' => [10, 25, 50, 100],

    /*
    |--------------------------------------------------------------------------
    | Flux UI Table Configuration
    |--------------------------------------------------------------------------
    |
    | This package now uses Flux UI Table components directly.
    | The styling is handled by the Flux UI components themselves.
    |
    */

    'flux_ui' => [
        // You can add custom configuration for Flux UI components here
        'use_pagination' => true,
        'use_view_mode' => false,
    ],

];
