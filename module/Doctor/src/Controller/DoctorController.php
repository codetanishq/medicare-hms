<?php
namespace Doctor\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Authentication\AuthenticationService;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Adapter\Exception\InvalidQueryException;

class DoctorController extends AbstractActionController
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
        if (!$identity || !$this->isAllowed($identity->role, 'doctor.view')) {
            return $this->redirect()->toRoute('home');
        }
        $search = $this->params()->fromQuery('search', '');
        $query = 'SELECT d.*, u.email FROM doctors d LEFT JOIN users u ON d.user_id = u.id WHERE 1=1';
        $params = [];
        $error = null;

        if (!empty($search)) {
            $query .= ' AND (d.name LIKE ? OR d.specialization LIKE ?)';
            $params = ["%$search%", "%$search%"];
        }

        try {
            $doctors = $this->dbAdapter->query($query, $params)->toArray();
        } catch (\Exception $e) {
            $doctors = [];
            $error = 'Error fetching doctors: ' . $e->getMessage();
        }

        return [
            'doctors' => $doctors,
            'search' => $search,
            'error' => $error
        ];
    }

    public function addAction()
    {
        $error = null;
        $identity = $this->authService->getIdentity();
        if (!$identity || !$this->isAllowed($identity->role, 'staff.manage')) {
            return $this->redirect()->toRoute('login');
        }

        if ($this->getRequest()->isPost()) {
            try {
                $data = $this->getRequest()->getPost();
                $this->dbAdapter->query(
                    'INSERT INTO doctors (user_id, name, specialization, contact) VALUES (?, ?, ?, ?)',
                    [$identity->id, $data['name'], $data['specialization'], $data['contact']]
                );
                return $this->redirect()->toRoute('doctor');
            } catch (InvalidQueryException $e) {
                $error = 'Failed to add doctor: ' . $e->getMessage();
            } catch (\Exception $e) {
                $error = 'An error occurred: ' . $e->getMessage();
            }
        }
        return ['error' => $error];
    }
    

    public function addPrescriptionAction()
    {
        $identity = $this->authService->getIdentity();
        if (!$identity || !$this->isAllowed($identity->role, 'prescription.add')) {
            return $this->redirect()->toRoute('login');
        }

        $patientId = (int) $this->params()->fromRoute('patient_id', 0);
        if (!$patientId) {
            return $this->redirect()->toRoute('patient');
        }

        $patient = $this->dbAdapter->query('SELECT * FROM patients WHERE id = ?', [$patientId])->current();
        if (!$patient) {
            return $this->redirect()->toRoute('patient');
        }

        // Fetch appointments for the patient
        $appointments = $this->dbAdapter->query(
            'SELECT a.id, a.appointment_date, d.name AS doctor_name 
             FROM appointments a 
             JOIN doctors d ON a.doctor_id = d.id 
             WHERE a.patient_id = ?',
            [$patientId]
        )->toArray();

        $error = null;
        if ($this->getRequest()->isPost()) {
            try {
                $data = $this->getRequest()->getPost();
                $appointmentId = (int) $data['appointment_id'];
                if (!$appointmentId) {
                    throw new \Exception('Appointment is required');
                }
                $this->dbAdapter->query(
                    'INSERT INTO prescriptions (patient_id, doctor_id, appointment_id, medication, dosage) VALUES (?, ?, ?, ?, ?)',
                    [$patientId, $identity->id, $appointmentId, $data['medication'], $data['dosage']]
                );
                return $this->redirect()->toRoute('patient/profile', ['id' => $patientId]);
            } catch (InvalidQueryException $e) {
                $error = 'Failed to add prescription: ' . $e->getMessage();
            } catch (\Exception $e) {
                $error = 'An error occurred: ' . $e->getMessage();
            }
        }

        return [
            'patient' => $patient,
            'appointments' => $appointments,
            'error' => $error
        ];
    }

    public function editAction()
    {
        $identity = $this->authService->getIdentity();
        if (!$identity || !$this->isAllowed($identity->role, 'doctor.edit')) {
            return $this->redirect()->toRoute('home');
        }

        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('doctor');
        }

        $doctor = $this->dbAdapter->query('SELECT * FROM doctors WHERE id = ?', [$id])->current();
        if (!$doctor) {
            return ['doctor' => $doctor, 'error' => 'Doctor Not Found'];
            }

        $error = null;
        if ($this->getRequest()->isPost()) {
            try {
                $data = $this->getRequest()->getPost();
                $this->dbAdapter->query(
                    'UPDATE doctors SET name = ?, specialization = ?, contact = ? WHERE id = ?',
                    [$data['name'], $data['specialization'], $data['contact'], $id]
                );
                return $this->redirect()->toRoute('doctor');
            } catch (InvalidQueryException $e) {
                $error = 'Failed to update doctor: ' . $e->getMessage();
            } catch (\Exception $e) {
                $error = 'An error occurred: ' . $e->getMessage();
            }
        }

        return ['doctor' => $doctor, 'error' => $error];
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


    
