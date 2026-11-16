<?php

declare(strict_types=1);

return [
    // Envelope key for collections and direct responses.
    'plural_data_key' => 'entities',

    // Envelope key used by forMethod('show') and forMethod('update').
    'singular_data_key' => 'entity',

    // Explicit forMethod() / fromAction() convention; never inferred by response().
    'using_for_rest' => true,
    'methods_for_singular_key' => ['show', 'update'],

    // Spread associative payload fields into the envelope (meta/message remain reserved).
    'without_wrapping' => false,
];
