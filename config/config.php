<?php

declare(strict_types=1);

return [
    // The default key for collections and direct response() calls.
    'plural_data_key' => 'entities',

    // Key used for configured REST actions, e.g. show/update.
    'singular_data_key' => 'entity',

    // REST naming is applied by forMethod() and fromAction(), not guessed on response().
    'using_for_rest' => true,

    'methods_for_singular_key' => ['show', 'update'],

    // Spread associative data into the response root, keeping meta/message.
    'without_wrapping' => false,
];
