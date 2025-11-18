<?php
/**
 * Login Controller
 */

class Login extends Controller {

    private $userModel;

    public function __construct() {
        $this->userModel = $this->model('User');
    }

    public function index() {
        // If already logged in, redirect to dashboard
        if (isset($_SESSION['user_id'])) {
            $this->redirect('dashboard');
        }

        $data = [
            'email' => '',
            'password' => '',
            'email_err' => '',
            'password_err' => '',
            'error' => ''
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Sanitize POST data
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

            $data['email'] = trim($_POST['email']);
            $data['password'] = trim($_POST['password']);

            // Validate email
            if (empty($data['email'])) {
                $data['email_err'] = 'Please enter email';
            }

            // Validate password
            if (empty($data['password'])) {
                $data['password_err'] = 'Please enter password';
            }

            // If no errors, attempt login
            if (empty($data['email_err']) && empty($data['password_err'])) {
                $user = $this->userModel->login($data['email'], $data['password']);

                if ($user) {
                    // Check if user is active
                    if ($user['status'] !== 'active') {
                        $data['error'] = 'Your account has been ' . $user['status'];
                    } else {
                        // Create session
                        $this->createUserSession($user);
                    }
                } else {
                    $data['error'] = 'Invalid email or password';
                }
            }
        }

        $this->view('auth/login', $data);
    }

    /**
     * Create session for logged in user
     */
    private function createUserSession($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['first_name'] = $user['first_name'];
        $_SESSION['last_name'] = $user['last_name'];
        $_SESSION['role'] = $user['role'];

        $this->redirect('dashboard');
    }

    /**
     * Logout
     */
    public function logout() {
        unset($_SESSION['user_id']);
        unset($_SESSION['username']);
        unset($_SESSION['email']);
        unset($_SESSION['first_name']);
        unset($_SESSION['last_name']);
        unset($_SESSION['role']);

        session_destroy();
        $this->redirect('login');
    }
}
