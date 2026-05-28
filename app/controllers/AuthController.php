<?php
class AuthController extends Controller
{
    private User $userModel;
    private Host $hostModel;

    public function __construct()
    {
        $this->userModel = $this->model('User');
        $this->hostModel = $this->model('Host');
    }

    public function login(): void
    {
        Auth::requireGuest();

        $data = [
            'title' => 'Login',
            'email' => '',
            'errors' => [],
        ];

        if ($this->isPost()) {
            if (!Auth::verifyCsrf($_POST['csrf_token'] ?? null)) {
                $data['errors']['general'] = 'Security token expired. Please submit the form again.';
            } else {
                $email = strtolower($this->input('email'));
                $password = $this->input('password');
                $data['email'] = $email;

                if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $data['errors']['email'] = 'Enter a valid email address.';
                }

                if ($password === '') {
                    $data['errors']['password'] = 'Enter your password.';
                }

                if (empty($data['errors'])) {
                    $user = $this->userModel->findByEmail($email);

                    if (!$user || !password_verify($password, $user['password'])) {
                        $data['errors']['general'] = 'Invalid email or password.';
                    } elseif (($user['status'] ?? '') !== 'active') {
                        $data['errors']['general'] = 'Your account is not active. Contact the administrator.';
                    } else {
                        Auth::login($user);
                        Auth::flash('success', 'Welcome back, ' . $user['name'] . '.');
                        $this->redirect(Auth::dashboardPath($user['role']));
                    }
                }
            }
        }

        $this->view('auth/login', $data);
    }

    public function register(): void
    {
        Auth::requireGuest();

        $data = [
            'title' => 'Register',
            'name' => '',
            'email' => '',
            'phone' => '',
            'role' => 'customer',
            'company_name' => '',
            'errors' => [],
        ];

        if ($this->isPost()) {
            if (!Auth::verifyCsrf($_POST['csrf_token'] ?? null)) {
                $data['errors']['general'] = 'Security token expired. Please submit the form again.';
            } else {
                $data['name'] = $this->clean($this->input('name'));
                $data['email'] = strtolower($this->input('email'));
                $data['phone'] = $this->clean($this->input('phone'));
                $data['role'] = $this->input('role', 'customer');
                $data['company_name'] = $this->clean($this->input('company_name'));
                $password = $this->input('password');
                $confirmPassword = $this->input('confirm_password');

                $allowedPublicRoles = ['customer', 'staff', 'host', 'main_admin'];

                if (strlen($data['name']) < 3) {
                    $data['errors']['name'] = 'Name must be at least 3 characters.';
                }

                if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                    $data['errors']['email'] = 'Enter a valid email address.';
                } elseif ($this->userModel->emailExists($data['email'])) {
                    $data['errors']['email'] = 'This email is already registered.';
                }

                if (!in_array($data['role'], $allowedPublicRoles, true)) {
                    $data['errors']['role'] = 'Choose a valid account type.';
                }

                if ($data['role'] === 'host' && strlen($data['company_name']) < 3) {
                    $data['errors']['company_name'] = 'Host accounts must include a company or host name.';
                }

                if (strlen($password) < 6) {
                    $data['errors']['password'] = 'Password must be at least 6 characters.';
                }

                if ($password !== $confirmPassword) {
                    $data['errors']['confirm_password'] = 'Passwords do not match.';
                }

                if (empty($data['errors'])) {
                    $userId = $this->userModel->create([
                        'name' => $data['name'],
                        'email' => $data['email'],
                        'phone' => $data['phone'],
                        'password' => $password,
                        'role' => $data['role'],
                        'status' => 'active',
                    ]);

                    if ($data['role'] === 'host') {
                        $this->hostModel->createForUser($userId, $data['company_name'], 'New host account created from public registration.', $data['email']);
                    }

                    $user = $this->userModel->findById($userId);
                    Auth::login($user);
                    Auth::flash('success', 'Account created successfully.');
                    $this->redirect(Auth::dashboardPath($user['role']));
                }
            }
        }

        $this->view('auth/register', $data);
    }

    public function logout(): void
    {
        Auth::logout();
        session_start();
        Auth::flash('success', 'You have been logged out.');
        $this->redirect('auth/login');
    }
}
