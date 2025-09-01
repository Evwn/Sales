<?php

return [

    /*
    |--------------------------------------------------------------------------
    | History Encryption
    |--------------------------------------------------------------------------
    |
    | This option controls whether Inertia should encrypt the page history
    | stored in the browser. Enabling this feature increases security by
    | preventing sensitive information from being exposed after logout.
    |
    */

    'history' => [
        'encrypt' => true, // or true if you want global encryption
    ],

];
