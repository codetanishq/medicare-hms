<?php
namespace User\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Authentication\AuthenticationService;
use Laminas\Db\Adapter\Adapter;

class DashboardController extends AbstractActionController
{
    private $authService;
    private $dbAdapter;

    public function __construct(AuthenticationService $authService, Adapter $dbAdapter)
    {
        $this->authService = $authService;
        $this->dbAdapter = $dbAdapter;
    }

    public function indexAction()
    {
        if (!$this->authService->hasIdentity()) {
            return $this->redirect()->toRoute('login');
        }

        $identity = $this->authService->getIdentity();
        $role = $identity->role;

        $data = [];
        if ($role === 'admin') {
            $data['totalPatients'] = $this->dbAdapter->query('SELECT COUNT(*) FROM patients', [])->current()->count;
            $data['todayAppointments'] = $this->dbAdapter->query(
                'SELECT COUNT(*) FROM appointments WHERE DATE(appointment_date) = CURDATE()',
                []
            )->current()->count;
        } elseif ($role === 'doctor') {
            $data['appointments'] = $this->dbAdapter->query(
                'SELECT a.*, p.name AS patient_name FROM appointments a JOIN patients p ON a.patient_id = p.id WHERE a.doctor_id = ?',
                [$identity->id]
            )->toArray();
        }

        return ['role' => $role, 'data' => $data];
    }
}