<?php
/**
 * Settings Controller
 */

class Settings extends Controller {

    private $settingModel;

    private $userModel;

    public function __construct() {
        $this->settingModel = $this->model('Setting');
        $this->userModel = $this->model('User');
    }

    public function index() {
        // Check if user is admin
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            $_SESSION['error'] = 'Access denied. Admin only.';
            $this->redirect('dashboard');
        }

        $settings = $this->settingModel->getAllSettings();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            foreach ($_POST as $key => $value) {
                if (strpos($key, 'setting_') === 0) {
                    $settingKey = str_replace('setting_', '', $key);
                    $this->settingModel->set($settingKey, $value);
                }
            }

            $_SESSION['success'] = 'Settings updated successfully';
            $this->redirect('settings');
        }

        $data = [
            'settings' => $settings,
            'active_menu' => 'settings',
            'page_title' => 'System Settings'
        ];

        $this->view('settings/index', $data);
    }

    public function profile() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

            $updateData = [
                'first_name' => $_POST['first_name'],
                'last_name' => $_POST['last_name'],
                'email' => $_POST['email'],
                'phone' => $_POST['phone'] ?? null
            ];

            if ($this->userModel->updateUser($_SESSION['user_id'], $updateData)) {
                // Update session data
                $_SESSION['first_name'] = $_POST['first_name'];
                $_SESSION['last_name'] = $_POST['last_name'];
                $_SESSION['email'] = $_POST['email'];
                $_SESSION['phone'] = $_POST['phone'] ?? null;

                $_SESSION['success'] = 'Profile updated successfully';
            } else {
                $_SESSION['error'] = 'Failed to update profile';
            }

            $this->redirect('settings/profile');
        }

        $data = [
            'active_menu' => 'profile',
            'page_title' => 'My Profile'
        ];

        $this->view('settings/profile', $data);
    }

    public function changePassword() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

            $user = $this->userModel->findById($_SESSION['user_id']);

            // Verify current password
            if (!password_verify($_POST['current_password'], $user['password'])) {
                $_SESSION['error'] = 'Current password is incorrect';
                $this->redirect('settings/profile');
            }

            // Verify new passwords match
            if ($_POST['new_password'] !== $_POST['confirm_password']) {
                $_SESSION['error'] = 'New passwords do not match';
                $this->redirect('settings/profile');
            }

            // Update password
            $hashedPassword = password_hash($_POST['new_password'], PASSWORD_BCRYPT);

            if ($this->userModel->updatePassword($_SESSION['user_id'], $hashedPassword)) {
                $_SESSION['success'] = 'Password changed successfully';
            } else {
                $_SESSION['error'] = 'Failed to change password';
            }
        }

        $this->redirect('settings/profile');
    }
}
