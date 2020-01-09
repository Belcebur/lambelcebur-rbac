<?php


namespace LamBelcebur\Rbac\View\Helper;


use Laminas\View\Helper\AbstractHelper;
use LamBelcebur\Rbac\Resource\RbacManager;

class Access extends AbstractHelper
{
    /** @var RbacManager */
    private $rbacManager;

    public function __construct(RbacManager $rbacManager)
    {
        $this->rbacManager = $rbacManager;
    }

    public function __invoke($permission, $params = [])
    {
        return $this->rbacManager->isGranted(null, $permission, $params);
    }
}
