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
        'use_container' => true,
        'use_pagination' => true,
        'use_empty_state' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Legacy CSS Classes (Deprecated)
    |--------------------------------------------------------------------------
    |
    | These classes are kept for backward compatibility but are no longer used
    | by default as the template now uses Flux UI components directly.
    |
    */

    'classes' => [
        'wrapper' => 'overflow-x-auto',
        'table' => 'min-w-full divide-y divide-gray-200',
        'thead' => 'bg-gray-50',
        'th' => 'px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider',
        'tbody' => 'bg-white divide-y divide-gray-200',
        'td' => 'px-6 py-4 whitespace-nowrap',
        'pagination' => 'mt-4',
        'search_input' => 'mb-4 p-2 border rounded',
    ],

];
