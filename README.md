# lambelcebur-rbac
Extended RBAC with Doctrine ORM

## See
- [https://packagist.org/explore/?query=lambelcebur](https://packagist.org/explore/?query=lambelcebur)
- [https://olegkrivtsov.github.io/using-zend-framework-3-book/html/](https://olegkrivtsov.github.io/using-zend-framework-3-book/html/)

## Installation

Installation of this module uses composer. For composer documentation, please refer to
[getcomposer.org](http://getcomposer.org/).

```sh
composer require lambelcebur/rbac
```

Then add `LamBelcebur\Rbac` to your `config/application.config.php`.

## Default Config

```php
<?php
use Laminas\Http\PhpEnvironment\Response;use LamBelcebur\Rbac\Module;use LamBelcebur\Rbac\Resource\RbacManager;return [
    Module::CONFIG_KEY => [
        'access_filter' => [
            'options' => [
                'mode' => 'restrictive', // permissive
                'filter_identity' => static function ($identity) {
                    return $identity; // Customize your identity to compare with config
                },
            ],
        ],
        'assertions' => [
            // YOUR_CUSTOM_ASSERTION_CLASS,
            // YOUR_OTHER_CUSTOM_ASSERTION_CLASS,
        ],
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
];
?>
```

## Config


### Default Const



```php
<?php
    use LamBelcebur\Rbac\Module;Module::RBAC_PUBLIC_ACCESS = [
        'actions' => '*',
        'allow' => '*',
        'methods' => '*',
    ];


    Module::RBAC_LOGGED_IN_ACCESS = [
        'actions' => '*',
        'allow' => '@',
        'methods' => '*',
    ];
?>
```

### Examples
```php
<?php
use Application\Controller\ApiController;use Application\Controller\DashboardController;use Application\Controller\IndexController;use Application\Controller\PublicController;use LamBelcebur\Rbac\Module;return [
    Module::CONFIG_KEY => [
        'access_filter' => [
            'options' => [
                'mode' => 'restrictive' // restrictive o permissive
            ],
            'controllers' => [
                IndexController::class => [
                    // Allow anyone to visit "index" and "about" actions
                    ['actions' => ['index', 'about'], 'allow' => '*'], // ONLY GET method
                    // Allow authorized users to visit "settings" action
                    ['actions' => ['settings'], 'allow' => '@', 'methods'=>'*'], // All methods
                    // Allow authorized users to visit "settings" action
                    Module::RBAC_PUBLIC_ACCESS, // Other Public access
                ],
                DashboardController::class => [
                    Module::RBAC_LOGGED_IN_ACCESS,
                ],
                PublicController::class => [
                    Module::RBAC_PUBLIC_ACCESS,
                ],
                // \Laminas\Mvc\Controller\AbstractRestfulController
                ApiController::class => [  
                    ['actions' => null, 'methods' => ['GET','DELETE','POST'], 'allow' => '@'],
                    ['actions' => null, 'methods' => ['PUT'], 'allow' => [
                        '@' =>[1,2,3,4,5], // Users 1,2,3,4,5 
                        '+' =>['a','b'] // Roles a and b 
                    ]],
                ],
            ]
        ],
    ]
];
?>
```

## Entities

- `LamBelcebur\Rbac\Entity\Permission`
- `LamBelcebur\Rbac\Entity\Role`

## Entity Traits
> Use with your User Entity
- `LamBelcebur\Rbac\EntityTrait\UserRole` 


# ViewHelper

```php
<?php 
/** @var Access $access */
use LamBelcebur\Rbac\View\Helper\Access;$access=$this->access();
if (!$access('profile.own.view', ['user'=>$user])) {
    return $this->redirect()->toRoute('not-authorized');
}
?>  
```


# PluginController

```php
<?php 
/** @var AccessPlugin $access */
use LamBelcebur\Rbac\Controller\Plugin\AccessPlugin;$access=$this->access();
if (!$access('profile.own.view', ['user'=>$user])) {
    return $this->redirect()->toRoute('not-authorized');
}  
?>
```
