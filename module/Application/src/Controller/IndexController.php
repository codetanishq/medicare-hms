<?php
namespace Application\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Db\Adapter\Adapter;

class IndexController extends AbstractActionController
{
    private $dbAdapter;

    public function __construct(Adapter $dbAdapter)
    {
        $this->dbAdapter = $dbAdapter;
    }

    public function indexAction()
    {
        $totalPatients = 0;
        $todayAppointments = 0;
        $totalDoctors = 0;

        try {
            $totalPatients = $this->dbAdapter->query('SELECT COUNT(*) as count FROM patients', [])->current()->count;
        } catch (\Exception $e) {
            // Handle missing patients table
        }
        try {
            $totalDoctors = $this->dbAdapter->query('SELECT COUNT(*) as count FROM doctors', [])->current()->count;
        } catch (\Exception $e) {
            // Handle missing patients table
        }

        try {
            $todayAppointments = $this->dbAdapter->query(
                'SELECT COUNT(*) as count FROM appointments WHERE DATE(appointment_date) = CURDATE()',
                []
            )->current()->count;
        } catch (\Exception $e) {
            // Handle missing appointments table
        }

        return [
            'totalPatients' => $totalPatients,
            'todayAppointments' => $todayAppointments,
            'totalDoctors' => $totalDoctors,
        ];
    }
}