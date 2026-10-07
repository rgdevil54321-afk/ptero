<?php

return [


    'filename' => '_ide_helper.php',


    'models_filename' => '_ide_helper_models.php',

    'meta_filename' => '.phpstorm.meta.php',


    'include_fluent' => false,


    'include_factory_builders' => false,


    'write_model_magic_where' => false,


    'write_model_external_builder_methods' => true,


    'write_model_relation_count_properties' => false,
    'write_model_relation_exists_properties' => false,


    'write_eloquent_model_mixins' => false,


    'include_helpers' => false,

    'helper_files' => [
        base_path() . '/vendor/laravel/framework/src/Illuminate/Support/helpers.php',
        base_path() . '/vendor/laravel/framework/src/Illuminate/Foundation/helpers.php',
    ],


    'model_locations' => [
        'app/Models',
    ],


    'ignored_models' => [
    ],


    'model_hooks' => [
    ],


    'extra' => [
        'Eloquent' => ['Illuminate\Database\Eloquent\Builder', 'Illuminate\Database\Query\Builder'],
        'Session' => ['Illuminate\Session\Store'],
    ],

    'magic' => [],


    'interfaces' => [
    ],

    'model_camel_case_properties' => false,

    'type_overrides' => [
        'integer' => 'int',
        'boolean' => 'bool',
    ],

    'include_class_docblocks' => false,

    'force_fqn' => true,

    'use_generics_annotations' => true,

    'macro_default_return_types' => [
        Illuminate\Http\Client\Factory::class => Illuminate\Http\Client\PendingRequest::class,
    ],

    'additional_relation_types' => [],

    'additional_relation_return_types' => [],


    'enforce_nullable_relationships' => true,

    'post_migrate' => [
    ],

];
