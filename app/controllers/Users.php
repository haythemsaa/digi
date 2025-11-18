<?php
/**
 * Users Management Controller (Admin only)
 */

class Users extends Controller {

    private $userModel;

    public function __construct() {
        // Check if user is admin
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            $_SESSION['error'] = 'Access denied. Admin only.';
            $this->redirect('dashboard');
        }

        $this->userModel = $this->model('User');
    }

    public function index() {
        $users = $this->userModel->getAllUsers(100, 0);

        $data = [
            'users' => $users,
            'active_menu' => 'users',
            'page_title' => 'Users Management'
        ];

        $this->view('users/index', $data);
    }

    public function add() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

            $errors = $this->validateInput($_POST, [
                'username' => 'required',
                'email' => 'required|email',
                'first_name' => 'required',
                'last_name' => 'required',
                'password' => 'required|min:6',
                'role' => 'required'
            ]);

            if (empty($errors)) {
                // Check if username or email exists
                if ($this->userModel->findByUsername($_POST['username'])) {
                    $errors['username'] = 'Username already exists';
                }

                if ($this->userModel->findByEmail($_POST['email'])) {
                    $errors['email'] = 'Email already exists';
                }

                if (empty($errors)) {
                    if ($this->userModel->register($_POST)) {
                        $_SESSION['success'] = 'User created successfully';
                        $this->redirect('users');
                    } else {
                        $_SESSION['error'] = 'Failed to create user';
                    }
                }
            }
        }

        $data = [
            'active_menu' => 'users',
            'page_title' => 'Add User'
        ];

        $this->view('users/add', $data);
    }

    public function edit($id) {
        $user = $this->userModel->findById($id);

        if (!$user) {
            $_SESSION['error'] = 'User not found';
            $this->redirect('users');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

            if ($this->userModel->updateUser($id, $_POST)) {
                $_SESSION['success'] = 'User updated successfully';
                $this->redirect('users');
            } else {
                $_SESSION['error'] = 'Failed to update user';
            }
        }

        $data = [
            'user' => $user,
            'active_menu' => 'users',
            'page_title' => 'Edit User'
        ];

        $this->view('users/edit', $data);
    }

    public function delete($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Prevent deleting yourself
            if ($id == $_SESSION['user_id']) {
                $_SESSION['error'] = 'You cannot delete your own account';
                $this->redirect('users');
            }

            if ($this->userModel->deleteUser($id)) {
                $_SESSION['success'] = 'User deleted successfully';
            } else {
                $_SESSION['error'] = 'Failed to delete user';
            }
        }

        $this->redirect('users');
    }
}
