<?php
class BookingController extends Controller
{
    private Booking $bookingModel;
    private Property $propertyModel;
    private User $userModel;

    private array $managerRoles = ['main_admin', 'booking_property_admin'];
    private array $statuses = ['pending', 'confirmed', 'rejected', 'cancelled'];

    public function __construct()
    {
        $this->bookingModel = $this->model('Booking');
        $this->propertyModel = $this->model('Property');
        $this->userModel = $this->model('User');
    }

    public function index(): void
    {
        Auth::requireRole(['main_admin', 'booking_property_admin', 'customer', 'staff', 'host']);

        $search = trim((string) ($_GET['search'] ?? ''));
        $status = trim((string) ($_GET['status'] ?? '')) ?: null;
        $role = Auth::role();
        $canManage = in_array($role, $this->managerRoles, true);
        $isHost = $role === 'host';

        if ($canManage) {
            $bookings = $this->bookingModel->listForManager($search, $status);
            $mode = 'manager';
        } elseif ($isHost) {
            $bookings = $this->bookingModel->listForHostUser(Auth::userId(), $search, $status);
            $mode = 'host';
        } else {
            $bookings = $this->bookingModel->listForCustomer(Auth::userId(), $search, $status);
            $mode = 'customer';
        }

        $this->view('bookings/index', [
            'title' => 'Bookings',
            'bookings' => $bookings,
            'search' => $search,
            'status' => $status,
            'statuses' => $this->statuses,
            'mode' => $mode,
            'canManage' => $canManage,
            'isHost' => $isHost,
            'stats' => [
                'total' => $this->bookingModel->countAll(),
                'pending' => $this->bookingModel->countByStatus('pending'),
                'confirmed' => $this->bookingModel->countByStatus('confirmed'),
                'revenue' => $this->bookingModel->totalConfirmedRevenue(),
            ],
        ]);
    }

    public function create(?int $propertyId = null): void
    {
        Auth::requireLogin();
        $role = Auth::role();
        $isManager = in_array($role, $this->managerRoles, true);

        if (!$isManager && !in_array($role, ['customer', 'staff'], true)) {
            Auth::flash('error', 'Only customers, staff, and booking admins can create bookings.');
            $this->redirect('booking');
        }

        $selectedProperty = $propertyId ? $this->propertyModel->findById((int) $propertyId) : null;
        if ($selectedProperty && (!$this->isBookableProperty($selectedProperty))) {
            Auth::flash('error', 'This property is not currently available for booking.');
            $this->redirect('property');
        }

        $defaultPropertyId = $selectedProperty['property_id'] ?? '';
        $data = $this->formData('Add Booking', 'create', URL_ROOT . '/booking/create' . ($propertyId ? '/' . (int) $propertyId : ''), [
            'user_id' => $isManager ? '' : Auth::userId(),
            'property_id' => $defaultPropertyId,
            'check_in_date' => '',
            'check_out_date' => '',
            'total_price' => '0.00',
            'booking_status' => $isManager ? 'confirmed' : 'pending',
        ], $isManager);

        if ($this->isPost()) {
            $data = $this->handleForm($data, null, $isManager);

            if (empty($data['errors'])) {
                $this->bookingModel->create($data['booking']);
                Auth::flash('success', $isManager ? 'Booking created successfully.' : 'Booking request submitted successfully. Wait for confirmation.');
                $this->redirect('booking');
            }
        }

        $this->view('bookings/form', $data);
    }

    public function edit(int $id): void
    {
        Auth::requireLogin();
        $booking = $this->bookingModel->findById($id);

        if (!$booking) {
            Auth::flash('error', 'Booking not found.');
            $this->redirect('booking');
        }

        $role = Auth::role();
        $isManager = in_array($role, $this->managerRoles, true);
        $isOwner = (int) $booking['user_id'] === Auth::userId();

        if (!$isManager && (!$isOwner || $booking['booking_status'] !== 'pending')) {
            Auth::flash('error', 'Only pending bookings owned by you can be edited.');
            $this->redirect('booking');
        }

        $data = $this->formData('Edit Booking', 'edit', URL_ROOT . '/booking/edit/' . $id, $booking, $isManager);

        if ($this->isPost()) {
            $data = $this->handleForm($data, $booking, $isManager);

            if (empty($data['errors'])) {
                $this->bookingModel->update($id, $data['booking']);
                Auth::flash('success', $isManager ? 'Booking updated successfully.' : 'Booking updated successfully and remains pending.');
                $this->redirect('booking');
            }
        }

        $this->view('bookings/form', $data);
    }

    public function cancel(int $id): void
    {
        Auth::requireLogin();

        if (!$this->isPost() || !Auth::verifyCsrf($_POST['csrf_token'] ?? null)) {
            Auth::flash('error', 'Invalid cancel request.');
            $this->redirect('booking');
        }

        $booking = $this->bookingModel->findById($id);
        if (!$booking) {
            Auth::flash('error', 'Booking not found.');
            $this->redirect('booking');
        }

        $isManager = in_array(Auth::role(), $this->managerRoles, true);
        $isOwner = (int) $booking['user_id'] === Auth::userId();

        if (!$isManager && !$isOwner) {
            http_response_code(403);
            require APP_ROOT . '/app/views/errors/403.php';
            exit;
        }

        if (in_array($booking['booking_status'], ['rejected', 'cancelled'], true)) {
            Auth::flash('error', 'This booking is already closed.');
            $this->redirect('booking');
        }

        $this->bookingModel->updateStatus($id, 'cancelled');
        Auth::flash('success', 'Booking cancelled successfully.');
        $this->redirect('booking');
    }

    public function delete(int $id): void
    {
        Auth::requireRole($this->managerRoles);

        if (!$this->isPost() || !Auth::verifyCsrf($_POST['csrf_token'] ?? null)) {
            Auth::flash('error', 'Invalid delete request.');
            $this->redirect('booking');
        }

        if (!$this->bookingModel->findById($id)) {
            Auth::flash('error', 'Booking not found.');
            $this->redirect('booking');
        }

        $this->bookingModel->delete($id);
        Auth::flash('success', 'Booking deleted successfully.');
        $this->redirect('booking');
    }

    public function confirm(int $id): void
    {
        $this->moderate($id, 'confirmed', 'Booking confirmed successfully.');
    }

    public function reject(int $id): void
    {
        $this->moderate($id, 'rejected', 'Booking rejected successfully.');
    }

    private function moderate(int $id, string $status, string $message): void
    {
        Auth::requireRole($this->managerRoles);

        if (!$this->isPost() || !Auth::verifyCsrf($_POST['csrf_token'] ?? null)) {
            Auth::flash('error', 'Invalid booking action.');
            $this->redirect('booking');
        }

        $booking = $this->bookingModel->findById($id);
        if (!$booking) {
            Auth::flash('error', 'Booking not found.');
            $this->redirect('booking');
        }

        if ($status === 'confirmed') {
            if ($booking['property_status'] !== 'approved' || $booking['property_availability'] !== 'available') {
                Auth::flash('error', 'Cannot confirm because the property is not approved and available.');
                $this->redirect('booking');
            }

            if ($this->bookingModel->hasDateConflict((int) $booking['property_id'], $booking['check_in_date'], $booking['check_out_date'], $id, ['confirmed'])) {
                Auth::flash('error', 'Cannot confirm because another confirmed booking overlaps these dates.');
                $this->redirect('booking');
            }
        }

        $this->bookingModel->updateStatus($id, $status);
        Auth::flash('success', $message);
        $this->redirect('booking');
    }

    private function formData(string $title, string $mode, string $action, array $booking, bool $isManager): array
    {
        return [
            'title' => $title,
            'mode' => $mode,
            'action' => $action,
            'booking' => $booking,
            'users' => $this->userModel->listBookableUsers(),
            'properties' => $this->propertyModel->listApprovedAvailable(),
            'statuses' => $this->statuses,
            'isManager' => $isManager,
            'errors' => [],
        ];
    }

    private function handleForm(array $data, ?array $existingBooking, bool $isManager): array
    {
        if (!Auth::verifyCsrf($_POST['csrf_token'] ?? null)) {
            $data['errors']['general'] = 'Security token expired. Please submit the form again.';
            return $data;
        }

        $userId = $isManager ? (int) $this->input('user_id') : Auth::userId();
        $propertyId = (int) $this->input('property_id');
        $checkIn = $this->input('check_in_date');
        $checkOut = $this->input('check_out_date');
        $status = $isManager ? $this->input('booking_status', 'pending') : ($existingBooking['booking_status'] ?? 'pending');

        if (!$isManager) {
            $status = 'pending';
        }

        $booking = [
            'user_id' => $userId,
            'property_id' => $propertyId,
            'check_in_date' => $checkIn,
            'check_out_date' => $checkOut,
            'total_price' => '0.00',
            'booking_status' => $status,
        ];

        if ($userId <= 0 || !$this->userModel->findById($userId)) {
            $data['errors']['user_id'] = 'Select a valid customer/user.';
        }

        $property = $propertyId > 0 ? $this->propertyModel->findById($propertyId) : null;
        if (!$property) {
            $data['errors']['property_id'] = 'Select a valid property.';
        } elseif (!$this->isBookableProperty($property)) {
            $data['errors']['property_id'] = 'Only approved and available properties can be booked.';
        }

        $today = date('Y-m-d');
        if (!$this->isValidDate($checkIn)) {
            $data['errors']['check_in_date'] = 'Select a valid check-in date.';
        } elseif ($checkIn < $today) {
            $data['errors']['check_in_date'] = 'Check-in date cannot be in the past.';
        }

        if (!$this->isValidDate($checkOut)) {
            $data['errors']['check_out_date'] = 'Select a valid check-out date.';
        } elseif ($this->isValidDate($checkIn) && $checkOut <= $checkIn) {
            $data['errors']['check_out_date'] = 'Check-out date must be after check-in date.';
        }

        if (!in_array($status, $this->statuses, true)) {
            $data['errors']['booking_status'] = 'Select a valid booking status.';
        }

        if (empty($data['errors']) && $property) {
            $excludeId = $existingBooking ? (int) $existingBooking['booking_id'] : null;
            $conflictStatuses = $status === 'confirmed' ? ['confirmed'] : ['pending', 'confirmed'];
            if ($this->bookingModel->hasDateConflict($propertyId, $checkIn, $checkOut, $excludeId, $conflictStatuses)) {
                $data['errors']['general'] = 'Another active booking already overlaps these dates.';
            }

            $nights = $this->calculateNights($checkIn, $checkOut);
            $booking['total_price'] = number_format($nights * (float) $property['price'], 2, '.', '');
        }

        $data['booking'] = $booking;
        return $data;
    }

    private function isBookableProperty(array $property): bool
    {
        return ($property['status'] ?? '') === 'approved' && ($property['availability'] ?? '') === 'available';
    }

    private function isValidDate(string $date): bool
    {
        $dt = DateTime::createFromFormat('Y-m-d', $date);
        return $dt && $dt->format('Y-m-d') === $date;
    }

    private function calculateNights(string $checkIn, string $checkOut): int
    {
        $start = new DateTime($checkIn);
        $end = new DateTime($checkOut);
        return max(1, (int) $start->diff($end)->days);
    }
}
