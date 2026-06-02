<?php
/*
|--------------------------------------------------------------------------
| HostController
|--------------------------------------------------------------------------
| Manages host profiles, linked host-user relationships, contact validation,
| and safe host profile deletion.
|
*/

class HostController extends Controller
{
    private Host $hostModel;

    public function __construct()
    {
        $this->hostModel = $this->model('Host');
    }

    private function requireManager(): void
    {
        // Host profile administration is restricted to Main Admin and Host/Location Admin.
        Auth::requireRole(['main_admin', 'host_location_admin']);
    }

    public function index(): void
    {
        $this->requireManager();
        $search = trim((string) ($_GET['search'] ?? ''));
        $status = trim((string) ($_GET['status'] ?? ''));
        $statusOptions = ['active', 'pending', 'rejected', 'inactive'];
        $status = in_array($status, $statusOptions, true) ? $status : null;

        // Host search combines company fields with linked user status/contact data.
        $this->view('hosts/index', [
            'title' => 'Hosts',
            'hosts' => $this->hostModel->listAll($search, $status),
            'search' => $search,
            'status' => $status,
            'statusOptions' => $statusOptions,
        ]);
    }

    public function create(): void
    {
        $this->requireManager();

        $data = [
            'title' => 'Add Host Profile',
            'mode' => 'create',
            'action' => URL_ROOT . '/host/create',
            // A host profile must be linked to an existing user whose role is Host.
            'host' => [
                'user_id' => '',
                'company_name' => '',
                'company_description' => '',
                'contact_information' => '',
                'contact_email' => '',
                'country_code' => '+45',
                'contact_phone' => '',
            ],
            'availableUsers' => $this->hostModel->availableHostUsers(),
            'phoneCountries' => $this->phoneCountries(),
            'errors' => [],
        ];

        if ($this->isPost()) {
            $data = $this->handleForm($data, true);

            if (empty($data['errors'])) {
                $this->hostModel->create($data['host']);
                Auth::flash('success', 'Host profile added successfully.');
                $this->redirect('host');
            }
        }

        $this->view('hosts/form', $data);
    }

    public function edit(int $id): void
    {
        $this->requireManager();
        $host = $this->hostModel->findById($id);

        if (!$host) {
            Auth::flash('error', 'Host profile not found.');
            $this->redirect('host');
        }

        $data = [
            'title' => 'Edit Host Profile',
            'mode' => 'edit',
            'action' => URL_ROOT . '/host/edit/' . $id,
            'host' => $this->withContactFields($host),
            'availableUsers' => $this->hostModel->availableHostUsers($id),
            'phoneCountries' => $this->phoneCountries(),
            'errors' => [],
        ];

        if ($this->isPost()) {
            $data = $this->handleForm($data, false);

            if (empty($data['errors'])) {
                $this->hostModel->update($id, $data['host']);
                Auth::flash('success', 'Host profile updated successfully.');
                $this->redirect('host');
            }
        }

        $this->view('hosts/form', $data);
    }

    public function delete(int $id): void
    {
        $this->requireManager();

        if (!$this->isPost() || !Auth::verifyCsrf($_POST['csrf_token'] ?? null)) {
            Auth::flash('error', 'Invalid delete request.');
            $this->redirect('host');
        }

        $host = $this->hostModel->findById($id);
        if (!$host) {
            Auth::flash('error', 'Host profile not found.');
            $this->redirect('host');
        }

        $propertyCount = $this->hostModel->countProperties($id);
        // Prevent deleting host profiles that still own properties.
        if ($propertyCount > 0) {
            Auth::flash('error', 'Cannot delete this host because it owns ' . $propertyCount . ' property record(s).');
            $this->redirect('host');
        }

        $this->hostModel->delete($id);
        Auth::flash('success', 'Host profile deleted successfully. The linked user account was not deleted.');
        $this->redirect('host');
    }

    public function profile(): void
    {
        Auth::requireRole(['host']);
        $host = $this->hostModel->findByUserId(Auth::userId());

        if (!$host) {
            Auth::flash('error', 'Your host profile is missing. Contact the administrator.');
            $this->redirect('dashboard');
        }

        $data = [
            'title' => 'My Host Profile',
            'mode' => 'host-profile',
            'action' => URL_ROOT . '/host/profile',
            'host' => $this->withContactFields($host),
            'availableUsers' => [],
            'phoneCountries' => $this->phoneCountries(),
            'errors' => [],
        ];

        if ($this->isPost()) {
            $data = $this->handleForm($data, false);

            if (empty($data['errors'])) {
                $this->hostModel->update((int) $host['host_id'], $data['host']);
                Auth::flash('success', 'Host profile updated successfully.');
                $this->redirect('host/profile');
            }
        }

        $this->view('hosts/form', $data);
    }

    private function handleForm(array $data, bool $requireUser): array
    {
        // Shared create/edit validation normalizes structured contact data for one DB column.
        if (!Auth::verifyCsrf($_POST['csrf_token'] ?? null)) {
            $data['errors']['general'] = 'Security token expired. Please submit the form again.';
            return $data;
        }

        $existingHost = $data['host'];
        $userId = (int) $this->input('user_id', (string) ($existingHost['user_id'] ?? 0));
        $contactEmail = strtolower($this->input('contact_email'));
        $countryCode = $this->input('country_code', '+45');
        $contactPhone = $this->clean($this->input('contact_phone'));
        $phoneValidation = $this->validatePhone($countryCode, $contactPhone);

        $data['host'] = array_merge($existingHost, [
            'user_id' => $userId,
            'company_name' => $this->clean($this->input('company_name')),
            'company_description' => $this->clean($this->input('company_description')),
            'contact_email' => $contactEmail,
            'country_code' => $countryCode,
            'contact_phone' => $contactPhone,
            'contact_information' => $phoneValidation['error'] === null ? $contactEmail . ' | ' . $phoneValidation['full_number'] : '',
        ]);

        if ($requireUser) {
            // New profiles cannot duplicate an existing host-user relationship.
            if ($userId <= 0) {
                $data['errors']['user_id'] = 'Select a host user account.';
            } elseif ($this->hostModel->userAlreadyHasHostProfile($userId)) {
                $data['errors']['user_id'] = 'This user already has a host profile.';
            }
        }

        if (strlen($data['host']['company_name']) < 3) {
            $data['errors']['company_name'] = 'Company or host name must be at least 3 characters.';
        }

        if ($contactEmail === '') {
            $data['errors']['contact_email'] = 'Contact email is required.';
        } elseif (!filter_var($contactEmail, FILTER_VALIDATE_EMAIL)) {
            $data['errors']['contact_email'] = 'Enter a valid contact email address.';
        }

        if ($phoneValidation['error'] !== null) {
            $data['errors']['contact_phone'] = $phoneValidation['error'];
        }

        return $data;
    }

    private function phoneCountries(): array
    {
        return [
            '+45' => ['label' => 'Denmark (+45)', 'min' => 8, 'max' => 8, 'placeholder' => '12 34 56 78'],
            '+46' => ['label' => 'Sweden (+46)', 'min' => 7, 'max' => 10, 'placeholder' => '701234567'],
            '+47' => ['label' => 'Norway (+47)', 'min' => 8, 'max' => 8, 'placeholder' => '12345678'],
            '+49' => ['label' => 'Germany (+49)', 'min' => 7, 'max' => 11, 'placeholder' => '15123456789'],
            '+44' => ['label' => 'UK (+44)', 'min' => 10, 'max' => 10, 'placeholder' => '7123456789'],
        ];
    }

    private function validatePhone(string $countryCode, string $phone): array
    {
        // Country-specific phone rules match public registration validation.
        $countries = $this->phoneCountries();

        if (!isset($countries[$countryCode])) {
            return ['error' => 'Please enter a valid phone number.', 'full_number' => null];
        }

        if ($phone === '') {
            return ['error' => 'Phone number is required.', 'full_number' => null];
        }

        if (!preg_match('/^\d+$/', $phone)) {
            return ['error' => 'Please enter a valid phone number.', 'full_number' => null];
        }

        $rules = $countries[$countryCode];
        $length = strlen($phone);
        if ($length < $rules['min'] || $length > $rules['max']) {
            return ['error' => 'Please enter a valid phone number.', 'full_number' => null];
        }

        return ['error' => null, 'full_number' => $countryCode . ' ' . $phone];
    }

    private function withContactFields(array $host): array
    {
        // Existing combined contact text is split back into form fields for editing.
        $host['contact_email'] = '';
        $host['country_code'] = '+45';
        $host['contact_phone'] = '';
        $contact = trim((string) ($host['contact_information'] ?? ''));

        if ($contact === '') {
            return $host;
        }

        if (str_contains($contact, '|')) {
            [$email, $phone] = array_map('trim', explode('|', $contact, 2));
            $host['contact_email'] = filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : '';
            if (preg_match('/^(\+\d{2})\s*(\d+)$/', $phone, $matches) && isset($this->phoneCountries()[$matches[1]])) {
                $host['country_code'] = $matches[1];
                $host['contact_phone'] = $matches[2];
            }
            return $host;
        }

        if (filter_var($contact, FILTER_VALIDATE_EMAIL)) {
            $host['contact_email'] = $contact;
        }

        return $host;
    }
}
