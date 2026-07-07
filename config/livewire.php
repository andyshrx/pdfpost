<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Component Namespaces
    |--------------------------------------------------------------------------
    |
    | The starter kit keeps layouts in resources/views/components/layouts,
    | but Livewire's default "layouts" namespace points at
    | resources/views/layouts. Point it at the right place so full page
    | components can find layouts::app.
    |
    */

    'component_namespaces' => [
        'layouts' => resource_path('views/components/layouts'),
        'pages' => resource_path('views/pages'),
    ],

];
