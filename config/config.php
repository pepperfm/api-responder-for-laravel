<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Plural Data Key
    |--------------------------------------------------------------------------
    |
    | This key represents the plural form of data entities in the response.
    | It is used when returning collections of resources.
    |
    */

    'plural_data_key' => 'entities',

    /*
    |--------------------------------------------------------------------------
    | Singular Data Key
    |--------------------------------------------------------------------------
    |
    | This key represents the singular form of a data entity in the response.
    | It is used when returning a single resource.
    |
    */

    'singular_data_key' => 'entity',

    /*
    |--------------------------------------------------------------------------
    | Use RESTful Format
    |--------------------------------------------------------------------------
    |
    | Enable this option to follow RESTful conventions in response formatting.
    | If set to true, responses will adhere to standard REST API practices.
    |
    */

    'using_for_rest' => true,

    /*
    |--------------------------------------------------------------------------
    | Methods for Singular Response
    |--------------------------------------------------------------------------
    |
    | Specify the HTTP methods that should return data in singular format.
    | Add additional methods as necessary.
    |
    */

    'methods_for_singular_key' => ['show', 'update'],

    /*
    |--------------------------------------------------------------------------
    | Force JSON Response Header
    |--------------------------------------------------------------------------
    |
    | When enabled, this option ensures that the response includes a JSON
    | content type header, signaling to clients that the response is in JSON.
    |
    */

    'force_json_response_header' => true,
];
