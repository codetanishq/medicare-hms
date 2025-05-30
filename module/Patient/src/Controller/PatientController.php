<?php
namespace Patient\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Authentication\AuthenticationService;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Adapter\Exception\InvalidQueryException;

class PatientController extends AbstractActionController
{
    private $dbAdapter;
    private $authService;
    private $rbacConfig;

    public function __construct(Adapter $dbAdapter, AuthenticationService $authService, array $rbacConfig)
    {
        $this->dbAdapter = $dbAdapter;
        $this->authService = $authService;
        $this->rbacConfig = $rbacConfig;
    }

    public function indexAction()
    {
        $identity = $this->authService->getIdentity();
        if (!$identity || !$this->isAllowed($identity->role, 'patient.view')) {
            return $this->redirect()->toRoute('login');
        }

        $patients = $this->dbAdapter->query('SELECT * FROM patients', [])->toArray();
        return ['patients' => $patients];
    }

    public function addAction()
    {
        $error = null;
        $identity = $this->authService->getIdentity();
        if (!$identity || !$this->isAllowed($identity->role, 'patient.add')) {
            return $this->redirect()->toRoute('login');
        }

        if ($this->getRequest()->isPost()) {
            try {
                $data = $this->getRequest()->getPost();
                $this->dbAdapter->query(
                    'INSERT INTO patients (user_id, name, age, gender, contact, medical_history) VALUES (?, ?, ?, ?, ?, ?)',
                    [$identity->id, $data['name'], (int)$data['age'], $data['gender'], $data['contact'], $data['medical_history'] ?? '']
                );
                return $this->redirect()->toRoute('patient');
            } catch (InvalidQueryException $e) {
                $error = 'Failed to add patient: ' . $e->getMessage();
            } catch (\Exception $e) {
                $error = 'An error occurred: ' . $e->getMessage();
            }
        }
        return ['error' => $error];
    }

    public function profileAction()
    {
        $identity = $this->authService->getIdentity();
        if (!$identity || !$this->isAllowed($identity->role, 'patient.view')) {
            return $this->redirect()->toRoute('login');
        }

        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('patient');
        }

        $patient = $this->dbAdapter->query('SELECT * FROM patients WHERE id = ?', [$id])->current();
        if (!$patient) {
            return ['patient' => null, 'prescriptions' => []];
        }

        $prescriptions = $this->dbAdapter->query('SELECT * FROM prescriptions WHERE patient_id = ?', [$id])->toArray();

        return [
            'patient' => $patient,
            'prescriptions' => $prescriptions,
        ];
    }


    public function editAction()
    {
        $identity = $this->authService->getIdentity();
        if (!$identity || !$this->isAllowed($identity->role, 'patient.edit')) {
            return $this->redirect()->toRoute('home');
        }

        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('patient');
        }

        $patient = $this->dbAdapter->query('SELECT * FROM patients WHERE id = ?', [$id])->current();
        if (!$patient) {
            return ['patient' => null, 'error' => 'Patient not found'];
        }

        $error = null;
        if ($this->getRequest()->isPost()) {
            try {
                $data = $this->getRequest()->getPost();
                $this->dbAdapter->query(
                    'UPDATE patients SET name = ?, age = ?, gender = ?, contact = ?, medical_history = ? WHERE id = ?',
                    [$data['name'], (int)$data['age'], $data['gender'], $data['contact'], $data['medical_history'] ?? '', $id]
                );
                return $this->redirect()->toRoute('patient');
            } catch (InvalidQueryException $e) {
                $error = 'Failed to update patient: ' . $e->getMessage();
            } catch (\Exception $e) {
                $error = 'An error occurred: ' . $e->getMessage();
            }
        }

        return ['patient' => $patient, 'error' => $error];
    }

    private function isAllowed($role, $permission)
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