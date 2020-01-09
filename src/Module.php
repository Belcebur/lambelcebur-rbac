<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonModule for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace LamBelcebur\Rbac;

use Exception;
use Laminas\Http\PhpEnvironment\Request;
use Laminas\Http\PhpEnvironment\Response;
use Laminas\ModuleManager\Feature\DependencyIndicatorInterface;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Mvc\Controller\AbstractController;
use Laminas\Mvc\Controller\AbstractRestfulController;
use Laminas\Mvc\MvcEvent;
use Laminas\Stdlib\ResponseInterface;
use Laminas\Uri\Http;
use LamBelcebur\Rbac\Resource\RbacManager;
use function get_class;
use function in_array;

class Module implements DependencyIndicatorInterface
{

    public const CONFIG_KEY = __NAMESPACE__;

    public const RBAC_PUBLIC_ACCESS = [
        'actions' => '*',
        'allow' => '*',
        'methods' => '*',
    ];

    public const RBAC_LOGGED_IN_ACCESS = [
        'actions' => '*',
        'allow' => '@',
        'methods' => '*',
    ];

    public function getConfig(): array
    {
        return include __DIR__ . '/../config/module.config.php';
    }

    /**
     * @param MvcEvent $mvcEvent
     */
    public function onBootstrap(MvcEvent $mvcEvent): void
    {
        $eventManager = $mvcEvent->getApplication()->getEventManager();
        $sharedEventManager = $eventManager->getSharedManager();
        if ($sharedEventManager) {
            $sharedEventManager->attach(AbstractController::class, MvcEvent::EVENT_DISPATCH, [
                $this,
                'rbacFilterAccess',
            ], 100);
        }
    }

    /**
     * @param MvcEvent $mvcEvent
     * @return ResponseInterface
     */
    public function rbacFilterAccess(MvcEvent $mvcEvent): ?ResponseInterface
    {
        /** @var AbstractController|AbstractRestfulController|AbstractActionController $controller */
        $controller = $mvcEvent->getTarget();

        $serviceManager = $mvcEvent->getApplication()->getServiceManager();
        $redirectConfig = (array)($serviceManager->get('Config')[__NAMESPACE__]['redirect'] ?? []);

        /** @var Request $request */
        $request = $controller->getRequest();

        /** @var Http $uri */
        $uri = clone $request->getUri();
        $currentUrl = $uri->toString();
        if (in_array($uri->getPath(), $this->getRedirectUrls($controller, $redirectConfig), true)) {
            return null;
        }

        $controllerName = get_class($controller);
        $actionName = $controller->params()->fromRoute('action', null);

        $rbacManager = $serviceManager->get(RbacManager::class);

        $result = $rbacManager->filterAccess($controllerName, $actionName);

        $uri->setScheme(null)
            ->setHost(null)
            ->setPort(null)
            ->setUserInfo(null)
            ->setPath('/')
            ->setQuery(['redirectUrl' => $currentUrl]);

        $defaultUrl = $uri->toString();

        if (array_key_exists($result, $redirectConfig)) {
            /** @var Response $response */
            $response = $mvcEvent->getResponse();
            $response->setStatusCode($redirectConfig[$result]['http_status_code'] ?? Response::STATUS_CODE_302);
            try {
                return $controller->redirect()->toRoute(
                    $redirectConfig[$result]['name'],
                    [],
                    ['query' => array_merge($redirectConfig[$result]['options'], ['redirectUrl' => $currentUrl])],
                    true
                );
            } catch (Exception $exception) {
                return $controller->redirect()->toUrl($defaultUrl);
            }
        }
        return null;
    }

    private function getRedirectUrls(AbstractController $controller, array $redirectConfig): array
    {
        $urls = [];

        foreach ($redirectConfig as $key => $options) {
            try {
                $urls[$key] = $controller->url()->fromRoute($options['name'], $options['params'], $options, true);
            } catch (Exception $exception) {
                $urls[$key] = '/';
            }
        }
        return $urls;

    }

    /**
     * Expected to return an array of modules on which the current one depends on
     *
     * @return array
     */
    public function getModuleDependencies(): array
    {
        return [
            'DoctrineModule',
            'DoctrineORMModule'
        ];
    }
}
