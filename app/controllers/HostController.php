<?php
class HostController extends Controller
{
    private Host $hostModel;

    public function __construct()
    {
        $this->hostModel = $this->model('Host');
    }

    private function requireManager(): void
    {
        Auth::requireRole(['main_admin', 'host_location_admin']);
    }

    public function index(): void
    {
        $this->requireManager();
        $search = trim((string) ($_GET['search'] ?? ''));

        $this->view('hosts/index', [
            'title' => 'Hosts',
            'hosts' => $this->hostModel->listAll($search),
            'search' => $search,
        ]);
    }

    public function create(): void
    {
        $this->requireManager();

        $data = [
            'title' => 'Add Host Profile',
            'mode' => 'create',
            'action' => URL_ROOT . '/host/create',
            'host' => [
                'user_id' => '',
                'company_name' => '',
                'company_description' => '',
                'contact_information' => '',
            ],
            'availableUsers' => $this->hostModel->availableHostUsers(),
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
            'host' => $host,
            'availableUsers' => $this->hostModel->availableHostUsers($id),
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
            'host' => $host,
            'availableUsers' => [],
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
        if (!Auth::verifyCsrf($_POST['csrf_token'] ?? null)) {
            $data['errors']['general'] = 'Security token expired. Please submit the form again.';
            return $data;
        }

        $existingHost = $data['host'];
        $userId = (int) $this->input('user_id', (string) ($existingHost['user_id'] ?? 0));
        $data['host'] = array_merge($existingHost, [
            'user_id' => $userId,
            'company_name' => $this->clean($this->input('company_name')),
            'company_description' => $this->clean($this->input('company_description')),
            'contact_information' => $this->clean($this->input('contact_information')),
        ]);

        if ($requireUser) {
            if ($userId <= 0) {
                $data['errors']['user_id'] = 'Select a host user account.';
            } elseif ($this->hostModel->userAlreadyHasHostProfile($userId)) {
                $data['errors']['user_id'] = 'This user already has a host profile.';
            }
        }

        if (strlen($data['host']['company_name']) < 3) {
            $data['errors']['company_name'] = 'Company or host name must be at least 3 characters.';
        }

        if ($data['host']['contact_information'] !== '' && strlen($data['host']['contact_information']) < 5) {
            $data['errors']['contact_information'] = 'Contact information is too short.';
        }

        return $data;
    }
}
