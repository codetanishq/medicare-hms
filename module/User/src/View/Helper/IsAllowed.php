<?php
namespace User\View\Helper;

use Laminas\View\Helper\AbstractHelper;

class IsAllowed extends AbstractHelper
{
    private $rbacConfig;

    public function __construct(array $rbacConfig)
    {
        $this->rbacConfig = $rbacConfig;
    }

    public function __invoke($role, $permission)
    {
        $roles = [$role];
        while (isset($this->rbacConfig['roles'][$role])) {
            $roles = array_merge($roles, $this->rbacConfig['roles'][$role]);
            $role = $this->rbacConfig['roles'][$role][0] ?? null;
        }
        foreach ($roles as $r) {
            if (in_array($permission, $this->rbacConfig['permissions'][$r] ?? [])) {
                return true;
            }
        }
        return false;
    }
}