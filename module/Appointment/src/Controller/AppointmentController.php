<?php
namespace Appointment\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Authentication\AuthenticationService;
use Laminas\Db\Adapter\Adapter;
use Laminas\View\Model\JsonModel;
use Laminas\Db\Adapter\Exception\InvalidQueryException;

class AppointmentController extends AbstractActionController
{
    private $dbAdapter;
    private $authService;
    private $rbacConfig;

    public function __construct(Adapter $dbAdapter,AuthenticationService $authService, array $rbacConfig)
    {
        $this->dbAdapter = $dbAdapter;
        $this->authService = $authService;
        $this->rbacConfig = $rbacConfig;
    }

    public function indexAction()
    {

        $identity = $this->authService->getIdentity();
        if (!$identity || !$this->isAllowed($identity->role, 'appointment.view')) {
            return $this->redirect()->toRoute('home');
        }

        $search = $this->params()->fromQuery('search', '');
        $date = $this->params()->fromQuery('date', '');
        $status = $this->params()->fromQuery('status', '');
        $query = 'SELECT a.*, p.name AS patient_name, d.name AS doctor_name 
                  FROM appointments a 
                  LEFT JOIN patients p ON a.patient_id = p.id 
                  LEFT JOIN doctors d ON a.doctor_id = d.id 
                  WHERE 1=1';
        $params = [];
        $error = null;

        if (!empty($search)) {
            $query .= ' AND (p.name LIKE ? OR d.name LIKE ?)';
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        if (!empty($date)) {
            $query .= ' AND DATE(a.appointment_date) = ?';
            $params[] = $date;
        }
        if (!empty($status)) {
            $query .= ' AND a.status = ?';
            $params[] = $status;
        }

        try {
            $appointments = $this->dbAdapter->query($query, $params)->toArray();
        } catch (\Exception $e) {
            $appointments = [];
            $error = 'Error fetching appointments: ' . $e->getMessage();
        }

        return [
            'appointments' => $appointments,
            'search' => $search,
            'date' => $date,
            'status' => $status,
            'error' => $error
        ];
    }

    public function fetchEventsAction()
    
    {
        $identity = $this->authService->getIdentity();
        if (!$this->isAllowed($identity->role,'appointment.view')) {
            return new JsonModel(['status' => false, 'msg' => 'Unauthorized']);
        }
        $sql = "SELECT a.id AS event_id, CONCAT('Consultation: ', p.name, ' with ', d.name) AS title, a.appointment_date AS start
                FROM appointments a
                JOIN patients p ON a.patient_id = p.id
                JOIN doctors d ON a.doctor_id = d.id
                WHERE a.status = 'scheduled'";
        $result = $this->dbAdapter->query($sql, Adapter::QUERY_MODE_EXECUTE);
        $events = [];
        foreach ($result as $row) {
            $events[] = [
                'id' => $row['event_id'],
                'title' => $row['title'],
                'start' => $row['start'],
            ];
        }
        return new JsonModel($events);
    }


    public function addAction()
    {
        $error = null;
        $identity = $this->authService->getIdentity();
        if (!$identity || !$this->isAllowed($identity->role, 'appointment.add')) {
            return $this->redirect()->toRoute('login');
        }
        if ($this->getRequest()->isPost()) {
            try {
                $data = $this->getRequest()->getPost();
                $this->dbAdapter->query(
                    'INSERT INTO appointments (patient_id, doctor_id, appointment_date, status) VALUES (?, ?, ?, ?)',
                    [(int)$data['patient_id'], (int)$data['doctor_id'], $data['appointment_date'], $data['status']]
                );
                return $this->redirect()->toRoute('appointment');
            } catch (InvalidQueryException $e) {
                $error = 'Failed to add appointment: ' . $e->getMessage();
            } catch (\Exception $e) {
                $error = 'An error occurred: ' . $e->getMessage();
            }
        }

        $patients = $this->dbAdapter->query('SELECT id, name FROM patients', [])->toArray();
        $doctors = $this->dbAdapter->query('SELECT id, name FROM doctors', [])->toArray();
        return ['patients' => $patients, 'doctors' => $doctors, 'error' => $error];
    }
    public function editAction()
    {
        $identity = $this->authService->getIdentity();
        if (!$identity || !$this->isAllowed($identity->role, 'appointment.edit')) {
            return $this->redirect()->toRoute('home');
        }

        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('appointment');
        }

        $appointment = $this->dbAdapter->query('SELECT * FROM appointments WHERE id = ?', [$id])->current();
        if (!$appointment) {
            return ['appointment' => null, 'error' => 'Appointment not found'];
        }

        $error = null;
        if ($this->getRequest()->isPost()) {
            try {
                $data = $this->getRequest()->getPost();
                $this->dbAdapter->query(
                    'UPDATE appointments SET patient_id = ?, doctor_id = ?, appointment_date = ?, status = ? WHERE id = ?',
                    [(int)$data['patient_id'], (int)$data['doctor_id'], $data['date'], $data['status'], $id]
                );
                return $this->redirect()->toRoute('appointment');
            } catch (InvalidQueryException $e) {
                $error = 'Failed to update appointment: ' . $e->getMessage();
            } catch (\Exception $e) {
                $error = 'An error occurred: ' . $e->getMessage();
            }
        }

        $patients = $this->dbAdapter->query('SELECT id, name FROM patients', [])->toArray();
        $doctors = $this->dbAdapter->query('SELECT id, name FROM doctors', [])->toArray();
        return ['appointment' => $appointment, 'patients' => $patients, 'doctors' => $doctors, 'error' => $error];
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