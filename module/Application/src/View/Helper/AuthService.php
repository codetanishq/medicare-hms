<?php
namespace Application\View\Helper;

use Laminas\View\Helper\AbstractHelper;
use Laminas\Authentication\AuthenticationService;

class AuthService extends AbstractHelper
{
    private $authService;

    public function __construct(AuthenticationService $authService)
    {
        $this->authService = $authService;
    }

    public function __invoke()
    {
        return $this->authService;
    }
}