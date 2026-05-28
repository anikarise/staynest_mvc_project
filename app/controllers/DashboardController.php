<?php
class DashboardController extends Controller
{
    private User $userModel;
    private Property $propertyModel;
    private Booking $bookingModel;
    private Host $hostModel;
    private Location $locationModel;

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
        Auth::requireLogin();
        $role = Auth::role();
        $userId = Auth::userId();

        $dashboardMap = [
            'customer' => 'dashboards/customer',
            'host' => 'dashboards/host',
            'staff' => 'dashboards/staff',
            'main_admin' => 'dashboards/main_admin',
            'booking_property_admin' => 'dashboards/booking_property_admin',
            'host_location_admin' => 'dashboards/host_location_admin',
        ];

        $hostProfile = $role === 'host' ? $this->hostModel->findByUserId($userId) : null;
        $stats = $this->buildStats($role, $userId);
        $analytics = $this->buildAnalytics($role, $userId);

        $view = $dashboardMap[$role] ?? 'dashboards/customer';
        $this->view($view, [
            'title' => 'Dashboard',
            'user' => Auth::user(),
            'roleLabel' => Auth::roleLabel($role),
            'stats' => $stats,
            'analytics' => $analytics,
            'hostProfile' => $hostProfile,
        ]);
    }

    private function buildStats(?string $role, ?int $userId): array
    {
        if ($role === 'customer') {
            return [
                'my_bookings' => $this->bookingModel->countForCustomer((int) $userId),
                'pending_bookings' => $this->bookingModel->countForCustomerByStatus((int) $userId, 'pending'),
                'confirmed_bookings' => $this->bookingModel->countForCustomerByStatus((int) $userId, 'confirmed'),
                'available_properties' => $this->propertyModel->countApprovedAvailable(),
            ];
        }

        if ($role === 'host') {
            return [
                'my_properties' => $this->propertyModel->countForHostUser((int) $userId),
                'approved_properties' => $this->propertyModel->countForHostUserByStatus((int) $userId, 'approved'),
                'pending_properties' => $this->propertyModel->countForHostUserByStatus((int) $userId, 'pending'),
                'property_bookings' => $this->bookingModel->countForHostUser((int) $userId),
                'confirmed_revenue' => $this->bookingModel->revenueForHostUser((int) $userId),
            ];
        }

        return [
            'users' => $this->userModel->countAll(),
            'properties' => $this->propertyModel->countAll(),
            'approved_properties' => $this->propertyModel->countByStatus('approved'),
            'pending_properties' => $this->propertyModel->countByStatus('pending'),
            'bookings' => $this->bookingModel->countAll(),
            'pending_bookings' => $this->bookingModel->countByStatus('pending'),
            'confirmed_bookings' => $this->bookingModel->countByStatus('confirmed'),
            'hosts' => $this->hostModel->countAll(),
            'locations' => $this->locationModel->countAll(),
            'confirmed_revenue' => $this->bookingModel->totalConfirmedRevenue(),
        ];
    }

    private function buildAnalytics(?string $role, ?int $userId): array
    {
        if ($role === 'customer') {
            return [
                'bookingStatus' => $this->bookingModel->statusBreakdownForCustomer((int) $userId),
                'recentBookings' => $this->bookingModel->recentForCustomer((int) $userId, 5),
                'recentProperties' => $this->propertyModel->recentApproved(5),
            ];
        }

        if ($role === 'host') {
            return [
                'propertyStatus' => $this->propertyModel->statusBreakdownForHostUser((int) $userId),
                'propertyAvailability' => $this->propertyModel->availabilityBreakdownForHostUser((int) $userId),
                'bookingStatus' => $this->bookingModel->statusBreakdownForHostUser((int) $userId),
                'recentBookings' => $this->bookingModel->recentForHostUser((int) $userId, 5),
                'recentProperties' => $this->propertyModel->recentForHostUser((int) $userId, 5),
            ];
        }

        return [
            'userRoles' => $this->userModel->countByRole(),
            'propertyStatus' => $this->propertyModel->statusBreakdown(),
            'propertyAvailability' => $this->propertyModel->availabilityBreakdown(),
            'propertyCategories' => $this->propertyModel->categoryBreakdown(),
            'bookingStatus' => $this->bookingModel->statusBreakdown(),
            'monthlyRevenue' => $this->bookingModel->monthlyRevenue(6),
            'topLocations' => $this->locationModel->topLocations(5),
            'topHosts' => $this->hostModel->topHosts(5),
            'recentBookings' => $this->bookingModel->recent(5),
            'recentProperties' => $this->propertyModel->recent(5),
        ];
    }
}
