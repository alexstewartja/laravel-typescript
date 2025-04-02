<?php

use AlexStewartJa\TypeScript\Generators\ModelGenerator;
use AlexStewartJa\TypeScript\Transformers\PostgresTypeTransformer;
use Illuminate\Database\Eloquent\Model;

return [
    /*
     * This is the main generator class used to convert eloquent models to TypeScript definitions
     * */
    'generators' => [
        Model::class => ModelGenerator::class,
    ],

    /*
     * The database connection from which to generate TypeScript definitions
     * */
    'db_connection' => env('TYPESCRIPT_DB_CONNECTION', env('DB_CONNECTION', 'mysql')),

    /*
     * Database driver specific transformers which apply to Laravel 11+
     * */
    'type_transformers' => [
        'pgsql' => PostgresTypeTransformer::class
    ],

    /*
     * Specify options for code style/formatting of generated TypeScript definitions file
     * */
    'formatting' => [
        'spaces' => 4
    ],

    'paths' => [
        //
    ],

    'custom_rules' => [
        // \App\Rules\MyCustomRule::class => 'string',
        // \App\Rules\MyOtherCustomRule::class => ['string', 'number'],
    ],

    /*
     * Full path to file in which to store TypeScript definitions
     * */
    'output_file_path' => resource_path('js/types/models.d.ts'),

    'autoload_dev' => false,
];
