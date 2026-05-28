<?php
class LocationController extends Controller
{
    private Location $locationModel;

    public function __construct()
    {
        $this->locationModel = $this->model('Location');
    }

    private function requireManager(): void
    {
        Auth::requireRole(['main_admin', 'host_location_admin']);
    }

    public function index(): void
    {
        $this->requireManager();
        $search = trim((string) ($_GET['search'] ?? ''));

        $this->view('locations/index', [
            'title' => 'Locations',
            'locations' => $this->locationModel->listAll($search),
            'search' => $search,
        ]);
    }

    public function create(): void
    {
        $this->requireManager();

        $data = [
            'title' => 'Add Location',
            'mode' => 'create',
            'action' => URL_ROOT . '/location/create',
            'location' => [
                'city' => '',
                'area' => '',
                'country' => 'Denmark',
                'postal_code' => '',
            ],
            'errors' => [],
        ];

        if ($this->isPost()) {
            $data = $this->handleForm($data);

            if (empty($data['errors'])) {
                $this->locationModel->create($data['location']);
                Auth::flash('success', 'Location added successfully.');
                $this->redirect('location');
            }
        }

        $this->view('locations/form', $data);
    }

    public function edit(int $id): void
    {
        $this->requireManager();
        $location = $this->locationModel->findById($id);

        if (!$location) {
            Auth::flash('error', 'Location not found.');
            $this->redirect('location');
        }

        $data = [
            'title' => 'Edit Location',
            'mode' => 'edit',
            'action' => URL_ROOT . '/location/edit/' . $id,
            'location' => $location,
            'errors' => [],
        ];

        if ($this->isPost()) {
            $data = $this->handleForm($data);

            if (empty($data['errors'])) {
                $this->locationModel->update($id, $data['location']);
                Auth::flash('success', 'Location updated successfully.');
                $this->redirect('location');
            }
        }

        $this->view('locations/form', $data);
    }

    public function delete(int $id): void
    {
        $this->requireManager();

        if (!$this->isPost() || !Auth::verifyCsrf($_POST['csrf_token'] ?? null)) {
            Auth::flash('error', 'Invalid delete request.');
            $this->redirect('location');
        }

        $location = $this->locationModel->findById($id);
        if (!$location) {
            Auth::flash('error', 'Location not found.');
            $this->redirect('location');
        }

        $propertyCount = $this->locationModel->countProperties($id);
        if ($propertyCount > 0) {
            Auth::flash('error', 'Cannot delete this location because it is linked with ' . $propertyCount . ' property record(s).');
            $this->redirect('location');
        }

        $this->locationModel->delete($id);
        Auth::flash('success', 'Location deleted successfully.');
        $this->redirect('location');
    }

    private function handleForm(array $data): array
    {
        if (!Auth::verifyCsrf($_POST['csrf_token'] ?? null)) {
            $data['errors']['general'] = 'Security token expired. Please submit the form again.';
            return $data;
        }

        $data['location'] = [
            'city' => $this->clean($this->input('city')),
            'area' => $this->clean($this->input('area')),
            'country' => $this->clean($this->input('country', 'Denmark')),
            'postal_code' => $this->clean($this->input('postal_code')),
        ];

        if (strlen($data['location']['city']) < 2) {
            $data['errors']['city'] = 'City must be at least 2 characters.';
        }

        if (strlen($data['location']['country']) < 2) {
            $data['errors']['country'] = 'Country must be at least 2 characters.';
        }

        if ($data['location']['postal_code'] !== '' && strlen($data['location']['postal_code']) > 20) {
            $data['errors']['postal_code'] = 'Postal code is too long.';
        }

        return $data;
    }
}
