<?php
namespace Report\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Db\Adapter\Adapter;

class ReportController extends AbstractActionController
{
    private $dbAdapter;

    public function __construct(Adapter $dbAdapter)
    {
        $this->dbAdapter = $dbAdapter;
    }

    public function indexAction()
    {
        $appointmentStats = $this->dbAdapter->query(
            'SELECT status, COUNT(*) as count FROM appointments GROUP BY status',
            []
        )->toArray();
        return ['stats' => $appointmentStats];
    }
    public function generatePdfAction()
{
    $stats = $this->dbAdapter->query(
        'SELECT status, COUNT(*) as count FROM appointments GROUP BY status',
        []
    )->toArray();

    require_once 'vendor/tecnickcom/tcpdf/tcpdf.php';
    $pdf = new \TCPDF();
    $pdf->AddPage();
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Cell(0, 10, 'Appointment Report', 0, 1, 'C');
    $pdf->Ln(10);
    $pdf->SetFont('helvetica', '', 12);
    $html = '<table border="1"><tr><th>Status</th><th>Count</th></tr>';
    foreach ($stats as $stat) {
        $html .= '<tr><td>' . htmlspecialchars($stat['status']) . '</td><td>' . htmlspecialchars($stat['count']) . '</td></tr>';
    }
    $html .= '</table>';
    $pdf->writeHTML($html, true, false, true, false, '');
    $pdf->Output('appointment_report.pdf', 'D');
    exit;
}
}