<?php
namespace User\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Authentication\AuthenticationService;
use Laminas\Db\Adapter\Adapter;
use Laminas\Http\Request;


class AuthController extends AbstractActionController
{
    private $authService;
    private $dbAdapter;

    public function __construct(AuthenticationService $authService, Adapter $dbAdapter)
    {
        $this->authService = $authService;
        $this->dbAdapter = $dbAdapter;
    }

    public function loginAction()
    {
        if ($this->authService->hasIdentity()) {
            return $this->redirect()->toRoute('dashboard');
        }

        /** @var Request $request */
        $request=$this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $username = $data['username'];
            $password = $data['password'];

            $hashedPassword = md5($password);

            $result = $this->dbAdapter->query(
                'SELECT * FROM users WHERE username = ? AND password = ?',
                [$username, $hashedPassword]
            )->current();

            if ($result) {
                $this->authService->getStorage()->write($result);
                return $this->redirect()->toRoute('dashboard');

            } else {
                return ['error' => 'Invalid credentials'];
            }
        }

        return [];
    }

    public function logoutAction()
    {
        $this->authService->clearIdentity();
        return $this->redirect()->toRoute('login');
    }
}