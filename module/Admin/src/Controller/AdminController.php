<?php
namespace Admin\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Db\Adapter\Adapter;

class AdminController extends AbstractActionController
{
    private $dbAdapter;

    public function __construct(Adapter $dbAdapter)
    {
        $this->dbAdapter = $dbAdapter;
    }

    public function indexAction()
    {
        return [];
    }

    public function staffAction()
    {
        $action = $this->params()->fromRoute('action', 'index');
        if ($action === 'index') {
            $staff = $this->dbAdapter->query('SELECT * FROM users WHERE role IN ("doctor", "receptionist")', [])->toArray();
            return ['staff' => $staff];
        } elseif ($action === 'add') {
            if ($this->getRequest()->isPost()) {
                $data = $this->getRequest()->getPost();
                $this->dbAdapter->query(
                    'INSERT INTO users (username, password, role, email) VALUES (?, ?, ?, ?)',
                    [$data['username'], md5($data['password']), $data['role'], $data['email']]
                );
                return $this->redirect()->toRoute('admin', ['action' => 'staff']);
            }
            return [];
        }
    }
}