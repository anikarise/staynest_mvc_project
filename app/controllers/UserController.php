<?php
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
        $this->view('users/index', [
            'title' => 'Users',
            'users' => $this->userModel->listAll(),
        ]);
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
            if (!Auth::verifyCsrf($_POST['csrf_token'] ?? null)) {
                $data['errors']['general'] = 'Security token expired. Please submit the form again.';
            } else {
                $data['name'] = $this->clean($this->input('name'));
                $data['phone'] = $this->clean($this->input('phone'));
                $newPassword = $this->input('new_password');
                $confirmPassword = $this->input('confirm_password');

                if (strlen($data['name']) < 3) {
                    $data['errors']['name'] = 'Name must be at least 3 characters.';
                }

                if ($newPassword !== '') {
                    if (strlen($newPassword) < 6) {
                        $data['errors']['new_password'] = 'New password must be at least 6 characters.';
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
}
