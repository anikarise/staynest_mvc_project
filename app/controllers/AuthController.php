<?php
/*
|--------------------------------------------------------------------------
| AuthController
|--------------------------------------------------------------------------
| Handles public login, registration, logout, and account-status gatekeeping.
|
*/

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
            // CSRF protection keeps forged form submissions out of authentication flows.
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
                    // Verify credentials before applying account-status gates.
                    $user = $this->userModel->findByEmail($email);

                    if (!$user || !password_verify($password, $user['password'])) {
                        $data['errors']['general'] = 'Invalid email or password.';
                    // Pending or rejected Staff/Host accounts cannot enter dashboards until reviewed.
                    } elseif (($user['account_status'] ?? 'active') === 'pending') {
                        $data['errors']['general'] = 'Your account is waiting for admin approval.';
                    } elseif (($user['account_status'] ?? 'active') === 'rejected') {
                        $data['errors']['general'] = 'Your account request has been rejected.';
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
            'country_code' => '+45',
            'phone' => '',
            'role' => 'customer',
            'company_name' => '',
            'errors' => [],
            'phoneCountries' => $this->phoneCountries(),
        ];

        if ($this->isPost()) {
            // CSRF protection keeps forged form submissions out of authentication flows.
            if (!Auth::verifyCsrf($_POST['csrf_token'] ?? null)) {
                $data['errors']['general'] = 'Security token expired. Please submit the form again.';
            } else {
                $data['name'] = $this->clean($this->input('name'));
                $data['email'] = strtolower($this->input('email'));
                $data['country_code'] = $this->input('country_code', '+45');
                $data['phone'] = $this->clean($this->input('phone'));
                $data['role'] = $this->input('role', 'customer');
                $data['company_name'] = $this->clean($this->input('company_name'));
                $password = $this->input('password');
                $confirmPassword = $this->input('confirm_password');

                // Public registration intentionally excludes admin roles.
                $allowedPublicRoles = ['customer', 'staff', 'host'];

                $nameValidation = $this->userModel->validateName($data['name']);
                if ($nameValidation !== null) {
                    $data['errors']['name'] = $nameValidation;
                }

                if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                    $data['errors']['email'] = 'Enter a valid email address.';
                } elseif ($this->userModel->emailExists($data['email'])) {
                    $data['errors']['email'] = 'This email is already registered.';
                }

                if (!in_array($data['role'], $allowedPublicRoles, true)) {
                    $data['errors']['role'] = 'Choose a valid account type.';
                }

                $phoneValidation = $this->validatePhone($data['country_code'], $data['phone']);
                if ($phoneValidation['error'] !== null) {
                    $data['errors']['phone'] = $phoneValidation['error'];
                }

                if ($data['role'] === 'host' && strlen($data['company_name']) < 3) {
                    $data['errors']['company_name'] = 'Host accounts must include a company or host name.';
                }

                $passwordValidation = $this->userModel->validatePasswordStrength($password);
                if ($passwordValidation !== null) {
                    $data['errors']['password'] = $passwordValidation;
                }

                if ($password !== $confirmPassword) {
                    $data['errors']['confirm_password'] = 'Passwords do not match.';
                }

                if (empty($data['errors'])) {
                    // Customers become active immediately; Staff and Host accounts wait for Main Admin approval.
                    $accountStatus = $data['role'] === 'customer' ? 'active' : 'pending';
                    $userId = $this->userModel->create([
                        'name' => $data['name'],
                        'email' => $data['email'],
                        'phone' => $phoneValidation['full_number'],
                        'password' => $password,
                        'role' => $data['role'],
                        'status' => 'active',
                        'account_status' => $accountStatus,
                    ]);

                    if ($data['role'] === 'host') {
                        // Host requests also create a linked host profile for later property ownership.
                        $this->hostModel->createForUser($userId, $data['company_name'], 'New host account created from public registration.', $data['email']);
                    }

                    if ($data['role'] === 'host') {
                        Auth::flash('success', 'Your host account request has been submitted and is waiting for admin approval.');
                        $this->redirect('auth/login');
                    }

                    if ($data['role'] === 'staff') {
                        Auth::flash('success', 'Your staff account request has been submitted and is waiting for admin approval.');
                        $this->redirect('auth/login');
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
        // Phone validation stores a normalized country-code-prefixed number.
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

        $length = strlen($phone);
        $rules = $countries[$countryCode];
        if ($length < $rules['min'] || $length > $rules['max']) {
            return ['error' => 'Please enter a valid phone number.', 'full_number' => null];
        }

        return ['error' => null, 'full_number' => $countryCode . ' ' . $phone];
    }

    public function logout(): void
    {
        Auth::logout();
        session_start();
        Auth::flash('success', 'You have been logged out.');
        $this->redirect('auth/login');
    }
}
