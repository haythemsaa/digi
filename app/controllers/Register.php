<?php
/**
 * Register Controller
 */

class Register extends Controller {

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
            'username' => '',
            'email' => '',
            'first_name' => '',
            'last_name' => '',
            'phone' => '',
            'password' => '',
            'confirm_password' => '',
            'username_err' => '',
            'email_err' => '',
            'first_name_err' => '',
            'last_name_err' => '',
            'password_err' => '',
            'confirm_password_err' => ''
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Sanitize POST data
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

            $data['username'] = trim($_POST['username']);
            $data['email'] = trim($_POST['email']);
            $data['first_name'] = trim($_POST['first_name']);
            $data['last_name'] = trim($_POST['last_name']);
            $data['phone'] = trim($_POST['phone']);
            $data['password'] = trim($_POST['password']);
            $data['confirm_password'] = trim($_POST['confirm_password']);

            // Validate username
            if (empty($data['username'])) {
                $data['username_err'] = 'Please enter username';
            } elseif ($this->userModel->findByUsername($data['username'])) {
                $data['username_err'] = 'Username already taken';
            }

            // Validate email
            if (empty($data['email'])) {
                $data['email_err'] = 'Please enter email';
            } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $data['email_err'] = 'Invalid email format';
            } elseif ($this->userModel->findByEmail($data['email'])) {
                $data['email_err'] = 'Email already registered';
            }

            // Validate first name
            if (empty($data['first_name'])) {
                $data['first_name_err'] = 'Please enter first name';
            }

            // Validate last name
            if (empty($data['last_name'])) {
                $data['last_name_err'] = 'Please enter last name';
            }

            // Validate password
            if (empty($data['password'])) {
                $data['password_err'] = 'Please enter password';
            } elseif (strlen($data['password']) < 6) {
                $data['password_err'] = 'Password must be at least 6 characters';
            }

            // Validate confirm password
            if (empty($data['confirm_password'])) {
                $data['confirm_password_err'] = 'Please confirm password';
            } elseif ($data['password'] !== $data['confirm_password']) {
                $data['confirm_password_err'] = 'Passwords do not match';
            }

            // If no errors, register user
            if (empty($data['username_err']) && empty($data['email_err']) &&
                empty($data['first_name_err']) && empty($data['last_name_err']) &&
                empty($data['password_err']) && empty($data['confirm_password_err'])) {

                if ($this->userModel->register($data)) {
                    $_SESSION['success'] = 'Registration successful! Please login.';
                    $this->redirect('login');
                } else {
                    die('Something went wrong');
                }
            }
        }

        $this->view('auth/register', $data);
    }
}
