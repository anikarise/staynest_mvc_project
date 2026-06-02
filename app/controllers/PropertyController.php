<?php
/*
|--------------------------------------------------------------------------
| PropertyController
|--------------------------------------------------------------------------
| Handles property listing, host submissions, manager approval, validation,
| image uploads, and safe deletion.
|
*/

class PropertyController extends Controller
{
    private Property $propertyModel;
    private Host $hostModel;
    private Location $locationModel;

    private array $managerRoles = ['main_admin', 'booking_property_admin'];
    private array $categories = ['Apartment', 'Studio', 'Room', 'House', 'Student Housing', 'Family Housing'];

    public function __construct()
    {
        $this->propertyModel = $this->model('Property');
        $this->hostModel = $this->model('Host');
        $this->locationModel = $this->model('Location');
    }

    public function index(): void
    {
        $search = trim((string) ($_GET['search'] ?? ''));
        $locationId = isset($_GET['location_id']) ? (int) $_GET['location_id'] : null;
        $availability = trim((string) ($_GET['availability'] ?? '')) ?: null;
        $status = trim((string) ($_GET['status'] ?? '')) ?: null;
        $role = Auth::role();
        $canManage = Auth::check() && in_array($role, $this->managerRoles, true);
        $isHost = Auth::check() && $role === 'host';

        // Public users see only approved properties; managers and hosts receive broader scoped lists.
        if ($canManage) {
            $properties = $this->propertyModel->listForManager($search, $locationId, $availability, $status);
            $mode = 'manager';
        } elseif ($isHost) {
            $properties = $this->propertyModel->listForHostUser(Auth::userId(), $search, $locationId, $availability, $status);
            $mode = 'host';
        } else {
            $properties = $this->propertyModel->listForPublic($search, $locationId, $availability);
            $mode = 'public';
        }

        $this->view('properties/index', [
            'title' => 'Properties',
            'properties' => $properties,
            'locations' => $this->locationModel->listAll(),
            'search' => $search,
            'locationId' => $locationId,
            'availability' => $availability,
            'status' => $status,
            'mode' => $mode,
            'canManage' => $canManage,
            'isHost' => $isHost,
        ]);
    }

    public function show(int $id): void
    {
        $property = $this->propertyModel->findById($id);

        if (!$property) {
            Auth::flash('error', 'Property not found.');
            $this->redirect('property');
        }

        $role = Auth::role();
        $canManage = Auth::check() && in_array($role, $this->managerRoles, true);
        $isHostOwner = Auth::check() && $role === 'host' && $this->propertyModel->userOwnsProperty($id, Auth::userId());

        // Non-approved properties are hidden unless the viewer manages or owns them.
        if (($property['status'] ?? '') !== 'approved' && !$canManage && !$isHostOwner) {
            http_response_code(403);
            require APP_ROOT . '/app/views/errors/403.php';
            exit;
        }

        $this->view('properties/show', [
            'title' => $property['title'],
            'property' => $property,
            'canManage' => $canManage,
            'isHostOwner' => $isHostOwner,
        ]);
    }

    public function create(): void
    {
        $this->requirePropertyCreator();
        $role = Auth::role();
        $isManager = in_array($role, $this->managerRoles, true);
        $host = $role === 'host' ? $this->hostModel->findByUserId(Auth::userId()) : null;

        if ($role === 'host' && !$host) {
            Auth::flash('error', 'Your host profile is missing. Create or contact admin before adding properties.');
            $this->redirect('host/profile');
        }

        $data = $this->formData('Add Property', 'create', URL_ROOT . '/property/create', [
            'host_id' => $host['host_id'] ?? '',
            'location_id' => '',
            'title' => '',
            'description' => '',
            'image' => '',
            'price' => '',
            'category' => 'Apartment',
            'availability' => 'available',
            // Host submissions enter moderation; manager-created properties are approved immediately.
            'status' => $isManager ? 'approved' : 'pending',
        ], $isManager);

        if ($this->isPost()) {
            $data = $this->handleForm($data, null);

            if (empty($data['errors'])) {
                $this->propertyModel->create($data['property']);
                Auth::flash('success', $isManager ? 'Property added successfully.' : 'Property submitted successfully and is waiting for admin approval.');
                $this->redirect('property');
            }
        }

        $this->view('properties/form', $data);
    }

    public function edit(int $id): void
    {
        $this->requirePropertyCreator();
        $property = $this->propertyModel->findById($id);

        if (!$property) {
            Auth::flash('error', 'Property not found.');
            $this->redirect('property');
        }

        $role = Auth::role();
        $isManager = in_array($role, $this->managerRoles, true);
        if (!$isManager && !$this->propertyModel->userOwnsProperty($id, Auth::userId())) {
            http_response_code(403);
            require APP_ROOT . '/app/views/errors/403.php';
            exit;
        }

        $data = $this->formData('Edit Property', 'edit', URL_ROOT . '/property/edit/' . $id, $property, $isManager);

        if ($this->isPost()) {
            $data = $this->handleForm($data, $property);

            if (empty($data['errors'])) {
                $this->propertyModel->update($id, $data['property']);
                Auth::flash('success', $isManager ? 'Property updated successfully.' : 'Property updated and sent back for moderation.');
                $this->redirect('property');
            }
        }

        $this->view('properties/form', $data);
    }

    public function delete(int $id): void
    {
        $this->requirePropertyCreator();

        if (!$this->isPost() || !Auth::verifyCsrf($_POST['csrf_token'] ?? null)) {
            Auth::flash('error', 'Invalid delete request.');
            $this->redirect('property');
        }

        $property = $this->propertyModel->findById($id);
        if (!$property) {
            Auth::flash('error', 'Property not found.');
            $this->redirect('property');
        }

        $isManager = in_array(Auth::role(), $this->managerRoles, true);
        if (!$isManager && !$this->propertyModel->userOwnsProperty($id, Auth::userId())) {
            http_response_code(403);
            require APP_ROOT . '/app/views/errors/403.php';
            exit;
        }

        $bookingCount = $this->propertyModel->countBookings($id);
        // Properties with booking history are protected from accidental deletion.
        if ($bookingCount > 0) {
            Auth::flash('error', 'Cannot delete this property because it has ' . $bookingCount . ' booking record(s).');
            $this->redirect('property');
        }

        $image = $property['image'] ?? '';
        $this->propertyModel->delete($id);
        $this->deleteUploadedImage($image);
        Auth::flash('success', 'Property deleted successfully.');
        $this->redirect('property');
    }

    public function approve(int $id): void
    {
        $this->moderate($id, 'approved', 'Property approved successfully.');
    }

    public function reject(int $id): void
    {
        $this->moderate($id, 'rejected', 'Property rejected successfully.');
    }

    private function moderate(int $id, string $status, string $message): void
    {
        Auth::requireRole($this->managerRoles);

        // Property approval/rejection is limited to booking/property administrators.
        if (!$this->isPost() || !Auth::verifyCsrf($_POST['csrf_token'] ?? null)) {
            Auth::flash('error', 'Invalid moderation request.');
            $this->redirect('property');
        }

        if (!$this->propertyModel->findById($id)) {
            Auth::flash('error', 'Property not found.');
            $this->redirect('property');
        }

        $this->propertyModel->updateStatus($id, $status);
        Auth::flash('success', $message);
        $this->redirect('property');
    }

    private function requirePropertyCreator(): void
    {
        Auth::requireRole(['main_admin', 'booking_property_admin', 'host']);
    }

    private function formData(string $title, string $mode, string $action, array $property, bool $isManager): array
    {
        return [
            'title' => $title,
            'mode' => $mode,
            'action' => $action,
            'property' => $property,
            'hosts' => $this->hostModel->listAll(),
            'locations' => $this->locationModel->listAll(),
            'categories' => $this->categories,
            'isManager' => $isManager,
            'errors' => [],
        ];
    }

    private function handleForm(array $data, ?array $existingProperty): array
    {
        // Validate form ownership and moderation fields before saving property records.
        if (!Auth::verifyCsrf($_POST['csrf_token'] ?? null)) {
            $data['errors']['general'] = 'Security token expired. Please submit the form again.';
            return $data;
        }

        $role = Auth::role();
        $isManager = in_array($role, $this->managerRoles, true);
        $host = $role === 'host' ? $this->hostModel->findByUserId(Auth::userId()) : null;
        $existingImage = $existingProperty['image'] ?? '';

        $hostId = $isManager ? (int) $this->input('host_id') : (int) ($host['host_id'] ?? 0);
        // Hosts cannot self-approve; their edits return to pending moderation.
        $status = $isManager ? $this->input('status', 'pending') : 'pending';

        $property = [
            'host_id' => $hostId,
            'location_id' => (int) $this->input('location_id'),
            'title' => $this->clean($this->input('title')),
            'description' => $this->clean($this->input('description')),
            'image' => $existingImage,
            'price' => $this->input('price'),
            'category' => $this->clean($this->input('category', 'Apartment')),
            'availability' => $this->input('availability', 'available'),
            'status' => $status,
        ];

        if ($property['host_id'] <= 0) {
            $data['errors']['host_id'] = 'Select a valid host.';
        }

        if ($property['location_id'] <= 0) {
            $data['errors']['location_id'] = 'Select a valid location.';
        }

        if (strlen($property['title']) < 4) {
            $data['errors']['title'] = 'Property title must be at least 4 characters.';
        }

        if ($property['description'] !== '' && strlen($property['description']) < 10) {
            $data['errors']['description'] = 'Description should be at least 10 characters or left empty.';
        }

        if (!is_numeric($property['price']) || (float) $property['price'] <= 0) {
            $data['errors']['price'] = 'Price must be a positive number.';
        } else {
            $property['price'] = number_format((float) $property['price'], 2, '.', '');
        }

        if (!in_array($property['category'], $this->categories, true)) {
            $data['errors']['category'] = 'Select a valid property category.';
        }

        if (!in_array($property['availability'], ['available', 'unavailable'], true)) {
            $data['errors']['availability'] = 'Select a valid availability value.';
        }

        if (!in_array($property['status'], ['pending', 'approved', 'rejected'], true)) {
            $data['errors']['status'] = 'Select a valid status value.';
        }

        $uploadResult = $this->handleImageUpload($existingImage);
        // Image upload validation is isolated so create/edit share the same checks.
        if (!empty($uploadResult['error'])) {
            $data['errors']['image'] = $uploadResult['error'];
        } elseif (!empty($uploadResult['filename'])) {
            $property['image'] = $uploadResult['filename'];
        }

        $data['property'] = $property;
        return $data;
    }

    private function handleImageUpload(string $existingImage = ''): array
    {
        if (empty($_FILES['image']) || ($_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return ['filename' => null, 'error' => null];
        }

        if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            return ['filename' => null, 'error' => 'Image upload failed. Try again.'];
        }

        $file = $_FILES['image'];
        $maxSize = 2 * 1024 * 1024;
        // Keep uploads small and verify the MIME type using image metadata.
        if ($file['size'] > $maxSize) {
            return ['filename' => null, 'error' => 'Image must be 2MB or smaller.'];
        }

        $tmpPath = $file['tmp_name'];
        $imageInfo = @getimagesize($tmpPath);
        if ($imageInfo === false) {
            return ['filename' => null, 'error' => 'Uploaded file is not a valid image.'];
        }

        $allowedMime = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        ];

        $mime = $imageInfo['mime'] ?? '';
        if (!isset($allowedMime[$mime])) {
            return ['filename' => null, 'error' => 'Only JPG, PNG, and WEBP images are allowed.'];
        }

        $uploadDir = PUBLIC_PATH . '/uploads/properties';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }

        // Randomized filenames avoid collisions and hide original client filenames.
        $filename = 'property_' . date('YmdHis') . '_' . bin2hex(random_bytes(5)) . '.' . $allowedMime[$mime];
        $destination = $uploadDir . '/' . $filename;

        if (!move_uploaded_file($tmpPath, $destination)) {
            return ['filename' => null, 'error' => 'Could not save uploaded image.'];
        }

        $this->deleteUploadedImage($existingImage);
        return ['filename' => $filename, 'error' => null];
    }

    private function deleteUploadedImage(?string $filename): void
    {
        if (!$filename) {
            return;
        }

        // Ignore path-like filenames so deletion stays inside the property upload folder.
        if (strpos($filename, '/') !== false || strpos($filename, '\\') !== false) {
            return;
        }

        $path = PUBLIC_PATH . '/uploads/properties/' . $filename;
        if (is_file($path)) {
            @unlink($path);
        }
    }
}
