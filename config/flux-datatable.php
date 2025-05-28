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
    | Classes CSS par défaut
    |--------------------------------------------------------------------------
    |
    | Personnalise ici les classes Tailwind appliquées aux éléments de la table.
    |
    */

    'classes' => [
        'wrapper'        => 'overflow-x-auto',
        'table'          => 'min-w-full divide-y divide-gray-200',
        'thead'          => 'bg-gray-50',
        'th'             => 'px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider',
        'tbody'          => 'bg-white divide-y divide-gray-200',
        'td'             => 'px-6 py-4 whitespace-nowrap',
        'pagination'     => 'mt-4',
        'search_input'   => 'mb-4 p-2 border rounded',
        // etc...
    ],

];
