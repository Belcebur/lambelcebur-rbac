<?php


namespace LamBelcebur\Rbac\Factory\Resource;


use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use Laminas\Authentication\AuthenticationService;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\ServiceManager\Factory\FactoryInterface;
use LamBelcebur\Rbac\Module;
use LamBelcebur\Rbac\Resource\RbacManager;

class RbacManagerFactory implements FactoryInterface
{

    /**
     * Create an object
     *
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param null|array $options
     * @return RbacManager
     * @throws ServiceNotFoundException if unable to resolve the service.
     * @throws ServiceNotCreatedException if an exception is raised when
     *     creating a service.
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $assertionManagers = [];
        $config = $container->get('Config');
        $rbacAccessFilter = (array)($config[Module::CONFIG_KEY]['access_filter'] ?? []);
        $assertions = (array)($config[Module::CONFIG_KEY]['assertions'] ?? []);
        foreach ($assertions as $serviceName) {
            $assertionManagers[$serviceName] = $container->get($serviceName);
        }

        return new RbacManager(
            $container->get('Request'),
            $container->get(EntityManager::class),
            $container->get(AuthenticationService::class),
            $rbacAccessFilter,
            $assertionManagers
        );
    }
}
