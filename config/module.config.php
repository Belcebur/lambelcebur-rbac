<?php

namespace LamBelcebur\Rbac;

use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Laminas\Http\PhpEnvironment\Response;
use LamBelcebur\Rbac\Controller\Plugin\AccessPlugin;
use LamBelcebur\Rbac\Factory\Controller\Plugin\AccessPluginFactory;
use LamBelcebur\Rbac\Factory\Resource\RbacManagerFactory;
use LamBelcebur\Rbac\Factory\View\Helper\AccessFactory;
use LamBelcebur\Rbac\Resource\RbacManager;
use LamBelcebur\Rbac\View\Helper\Access;

return [
    'controller_plugins' => [
        'factories' => [
            AccessPlugin::class => AccessPluginFactory::class,
        ],
        'aliases' => [
            'access' => AccessPlugin::class,
        ],
    ],
    'view_helpers' => [
        'factories' => [
            Access::class => AccessFactory::class,
        ],
        'aliases' => [
            'access' => Access::class,
        ],
    ],
    'doctrine' => [
        'driver' => [
            'rbac_driver' => [
                'class' => AnnotationDriver::class,
                'cache' => 'array',
                'paths' => [
                    __DIR__ . '/../src/Entity',
                ],
            ],
            'orm_default' => [
                'drivers' => [
                    'LamBelcebur\Rbac\Entity' => 'rbac_driver',
                ],
            ],
        ],
    ],
    Module::CONFIG_KEY => [
        'access_filter' => [
            'options' => [
                'mode' => 'restrictive',
                'filter_identity' => static function ($identity) {
                    return $identity;
                },
            ],
        ],
        'assertions' => [],
        'redirect' => [
            RbacManager::AUTH_REQUIRED => [
                'name' => '',
                'params' => [],
                'options' => [],
                'http_status_code' => Response::STATUS_CODE_302,
            ],
            RbacManager::ACCESS_DENIED => [
                'name' => '',
                'params' => [],
                'options' => [],
                'http_status_code' => Response::STATUS_CODE_303,
            ],
        ],
    ],
    'service_manager' => [
        'factories' => [
            RbacManager::class => RbacManagerFactory::class,
        ],
    ],
];
