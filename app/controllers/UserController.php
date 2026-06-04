<?php
/*
|--------------------------------------------------------------------------
| UserController
|--------------------------------------------------------------------------
| Handles Main Admin user management, account approvals, protected deletion,
| and authenticated profile updates.
|
*/

class UserController extends Controller
{
    private User $userModel;

    public function __construct()
    {
        $this->userModel = $this->model('User');
    }

    public function index(): void
    {
        Auth::requireRole(['main_admin']);
        $search = trim((string) ($_GET['search'] ?? ''));
        $role = trim((string) ($_GET['role'] ?? ''));
        $accountStatus = trim((string) ($_GET['account_status'] ?? ''));
        $roleOptions = ['customer', 'host', 'staff', 'main_admin', 'booking_property_admin', 'host_location_admin'];
        $statusOptions = ['active', 'pending', 'rejected', 'inactive'];
        $role = in_array($role, $roleOptions, true) ? $role : null;
        $accountStatus = in_array($accountStatus, $statusOptions, true) ? $accountStatus : null;

        // Main Admin can combine search, role, and account-status filters.
        $this->view('users/index', [
            'title' => 'Users',
            'users' => $this->userModel->listAll($search, $role, $accountStatus),
            'search' => $search,
            'role' => $role,
            'accountStatus' => $accountStatus,
            'roleOptions' => $roleOptions,
            'statusOptions' => $statusOptions,
        ]);
    }

    public function approve(int $id): void
    {
        $this->moderateAccount($id, 'active', 'Account approved successfully.');
    }

    public function reject(int $id): void
    {
        $this->moderateAccount($id, 'rejected', 'Account request rejected.');
    }

    public function delete(int $id): void
    {
        Auth::requireRole(['main_admin']);

        // Deletion is POST-only, CSRF-protected, and blocked for self/linked records.
        if (!$this->isPost() || !Auth::verifyCsrf($_POST['csrf_token'] ?? null)) {
            Auth::flash('error', 'Invalid delete request.');
            $this->redirect('user');
        }

        $user = $this->userModel->findById($id);
        if (!$user) {
            Auth::flash('error', 'User not found.');
            $this->redirect('user');
        }

        if ((int) $user['user_id'] === Auth::userId()) {
            Auth::flash('error', 'You cannot delete your own account.');
            $this->redirect('user');
        }

        if ($this->userModel->hasLinkedRecords($id)) {
            Auth::flash('error', 'This user cannot be deleted because they are linked to existing records.');
            $this->redirect('user');
        }

        $this->userModel->delete($id);
        Auth::flash('success', 'User deleted successfully.');
        $this->redirect('user');
    }

    public function profile(): void
    {
        Auth::requireLogin();

        $userId = Auth::userId();
        $user = $this->userModel->findById($userId);
        if (!$user) {
            Auth::logout();
            $this->redirect('auth/login');
        }

        $data = [
            'title' => 'My Profile',
            'user' => $user,
            'name' => $user['name'],
            'phone' => $user['phone'] ?? '',
            'errors' => [],
        ];

        if ($this->isPost()) {
            // Profile updates validate identity and optional password changes before saving.
            if (!Auth::verifyCsrf($_POST['csrf_token'] ?? null)) {
                $data['errors']['general'] = 'Security token expired. Please submit the form again.';
            } else {
                $data['name'] = $this->clean($this->input('name'));
                $data['phone'] = $this->clean($this->input('phone'));
                $newPassword = $this->input('new_password');
                $confirmPassword = $this->input('confirm_password');

                $nameValidation = $this->userModel->validateName($data['name']);
                if ($nameValidation !== null) {
                    $data['errors']['name'] = $nameValidation;
                }

                if ($newPassword !== '') {
                    $passwordValidation = $this->userModel->validatePasswordStrength($newPassword);
                    if ($passwordValidation !== null) {
                        $data['errors']['new_password'] = $passwordValidation;
                    }

                    if ($newPassword !== $confirmPassword) {
                        $data['errors']['confirm_password'] = 'Passwords do not match.';
                    }
                }

                if (empty($data['errors'])) {
                    $this->userModel->updateProfile($userId, [
                        'name' => $data['name'],
                        'phone' => $data['phone'],
                    ]);

                    if ($newPassword !== '') {
                        $this->userModel->updatePassword($userId, $newPassword);
                    }

                    $updatedUser = $this->userModel->findById($userId);
                    Auth::updateSessionUser($updatedUser);
                    Auth::flash('success', 'Profile updated successfully.');
                    $this->redirect('user/profile');
                }
            }
        }

        $this->view('users/profile', $data);
    }

    private function moderateAccount(int $id, string $accountStatus, string $message): void
    {
        Auth::requireRole(['main_admin']);

        // Only pending Host/Staff accounts move through the approval workflow.
        if (!Auth::verifyCsrf($_POST['csrf_token'] ?? null)) {
            Auth::flash('error', 'Security token expired. Please try again.');
            $this->redirect('user');
        }

        $user = $this->userModel->findById($id);
        if (!$user || !in_array($user['role'], ['host', 'staff'], true)) {
            Auth::flash('error', 'Only Host and Staff account requests can be approved or rejected here.');
            $this->redirect('user');
        }

        $this->userModel->updateAccountStatus($id, $accountStatus);
        Auth::flash('success', $message);
        $this->redirect('user');
    }
}
