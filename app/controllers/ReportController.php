<?php
class ReportController extends Controller
{
    private User $userModel;
    private Property $propertyModel;
    private Booking $bookingModel;
    private Host $hostModel;
    private Location $locationModel;

    private array $reportRoles = ['main_admin', 'booking_property_admin', 'host_location_admin', 'staff', 'host'];

    public function __construct()
    {
        $this->userModel = $this->model('User');
        $this->propertyModel = $this->model('Property');
        $this->bookingModel = $this->model('Booking');
        $this->hostModel = $this->model('Host');
        $this->locationModel = $this->model('Location');
    }

    public function index(): void
    {
        Auth::requireRole($this->reportRoles);
        $role = Auth::role();
        $userId = Auth::userId();
        $isHost = $role === 'host';

        $data = [
            'title' => 'Reports',
            'role' => $role,
            'isHost' => $isHost,
            'summary' => $this->summary($role, $userId),
            'propertyStatus' => $isHost ? $this->propertyModel->statusBreakdownForHostUser((int) $userId) : $this->propertyModel->statusBreakdown(),
            'propertyAvailability' => $isHost ? $this->propertyModel->availabilityBreakdownForHostUser((int) $userId) : $this->propertyModel->availabilityBreakdown(),
            'propertyCategories' => $isHost ? [] : $this->propertyModel->categoryBreakdown(),
            'bookingStatus' => $isHost ? $this->bookingModel->statusBreakdownForHostUser((int) $userId) : $this->bookingModel->statusBreakdown(),
            'monthlyRevenue' => $isHost ? [] : $this->bookingModel->monthlyRevenue(6),
            'topLocations' => $isHost ? [] : $this->locationModel->topLocations(8),
            'topHosts' => $isHost ? [] : $this->hostModel->topHosts(8),
            'recentBookings' => $isHost ? $this->bookingModel->recentForHostUser((int) $userId, 8) : $this->bookingModel->recent(8),
            'recentProperties' => $isHost ? $this->propertyModel->recentForHostUser((int) $userId, 8) : $this->propertyModel->recent(8),
        ];

        $this->view('reports/index', $data);
    }

    public function export(): void
    {
        Auth::requireRole($this->reportRoles);
        $role = Auth::role();
        $userId = Auth::userId();
        $summary = $this->summary($role, $userId);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="staynest-report-' . date('Y-m-d') . '.csv"');

        $out = fopen('php://output', 'w');
        fputcsv($out, ['StayNest Report Export', date('Y-m-d H:i:s')]);
        fputcsv($out, []);
        fputcsv($out, ['Metric', 'Value']);
        foreach ($summary as $label => $value) {
            fputcsv($out, [ucwords(str_replace('_', ' ', $label)), $value]);
        }
        fputcsv($out, []);
        fputcsv($out, ['Booking Status', 'Count']);
        $bookingStatus = $role === 'host' ? $this->bookingModel->statusBreakdownForHostUser((int) $userId) : $this->bookingModel->statusBreakdown();
        foreach ($bookingStatus as $row) {
            fputcsv($out, [$row['label'], $row['total']]);
        }
        fclose($out);
        exit;
    }

    private function summary(?string $role, ?int $userId): array
    {
        if ($role === 'host') {
            return [
                'my_properties' => $this->propertyModel->countForHostUser((int) $userId),
                'approved_properties' => $this->propertyModel->countForHostUserByStatus((int) $userId, 'approved'),
                'pending_properties' => $this->propertyModel->countForHostUserByStatus((int) $userId, 'pending'),
                'property_bookings' => $this->bookingModel->countForHostUser((int) $userId),
                'confirmed_revenue' => number_format($this->bookingModel->revenueForHostUser((int) $userId), 2),
            ];
        }

        return [
            'total_users' => $this->userModel->countAll(),
            'total_hosts' => $this->hostModel->countAll(),
            'total_locations' => $this->locationModel->countAll(),
            'total_properties' => $this->propertyModel->countAll(),
            'approved_properties' => $this->propertyModel->countByStatus('approved'),
            'pending_properties' => $this->propertyModel->countByStatus('pending'),
            'total_bookings' => $this->bookingModel->countAll(),
            'pending_bookings' => $this->bookingModel->countByStatus('pending'),
            'confirmed_bookings' => $this->bookingModel->countByStatus('confirmed'),
            'confirmed_revenue' => number_format($this->bookingModel->totalConfirmedRevenue(), 2),
        ];
    }
}
