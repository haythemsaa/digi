<?php
/**
 * Settings Controller
 */

class Settings extends Controller {

    private $settingModel;

    public function __construct() {
        // Check if user is admin
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            $_SESSION['error'] = 'Access denied. Admin only.';
            $this->redirect('dashboard');
        }

        $this->settingModel = $this->model('Setting');
    }

    public function index() {
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
}
