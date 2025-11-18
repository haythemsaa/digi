<?php
$data['title'] = 'System Settings';
require_once APP_PATH . '/views/includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once APP_PATH . '/views/includes/sidebar.php'; ?>

        <div class="col-md-9 col-lg-10 main-content">
            <?php require_once APP_PATH . '/views/includes/navbar.php'; ?>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle me-2"></i><?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-cog me-2"></i>System Settings</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <h6 class="border-bottom pb-2 mb-3"><i class="fas fa-building me-2"></i>Company Information</h6>

                                <div class="mb-3">
                                    <label class="form-label">Company Name</label>
                                    <input type="text"
                                           name="setting_company_name"
                                           class="form-control"
                                           value="<?php
                                           foreach ($data['settings'] as $s) {
                                               if ($s['setting_key'] === 'company_name') echo $s['setting_value'];
                                           }
                                           ?>">
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Currency</label>
                                        <select name="setting_currency" class="form-select">
                                            <?php
                                            $currencyValue = '';
                                            foreach ($data['settings'] as $s) {
                                                if ($s['setting_key'] === 'currency') $currencyValue = $s['setting_value'];
                                            }
                                            ?>
                                            <option value="TND" <?php echo $currencyValue === 'TND' ? 'selected' : ''; ?>>TND (Tunisian Dinar)</option>
                                            <option value="EUR" <?php echo $currencyValue === 'EUR' ? 'selected' : ''; ?>>EUR (Euro)</option>
                                            <option value="USD" <?php echo $currencyValue === 'USD' ? 'selected' : ''; ?>>USD (US Dollar)</option>
                                        </select>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Timezone</label>
                                        <select name="setting_timezone" class="form-select">
                                            <?php
                                            $timezoneValue = '';
                                            foreach ($data['settings'] as $s) {
                                                if ($s['setting_key'] === 'timezone') $timezoneValue = $s['setting_value'];
                                            }
                                            ?>
                                            <option value="Africa/Tunis" <?php echo $timezoneValue === 'Africa/Tunis' ? 'selected' : ''; ?>>Africa/Tunis</option>
                                            <option value="Europe/Paris" <?php echo $timezoneValue === 'Europe/Paris' ? 'selected' : ''; ?>>Europe/Paris</option>
                                            <option value="UTC" <?php echo $timezoneValue === 'UTC' ? 'selected' : ''; ?>>UTC</option>
                                        </select>
                                    </div>
                                </div>

                                <h6 class="border-bottom pb-2 mb-3 mt-4"><i class="fas fa-bell me-2"></i>Notifications</h6>

                                <div class="mb-3">
                                    <label class="form-label">Maintenance Alert (Days Before)</label>
                                    <input type="number"
                                           name="setting_maintenance_alert_days"
                                           class="form-control"
                                           value="<?php
                                           foreach ($data['settings'] as $s) {
                                               if ($s['setting_key'] === 'maintenance_alert_days') echo $s['setting_value'];
                                           }
                                           ?>">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Default Speed Limit (km/h)</label>
                                    <input type="number"
                                           name="setting_speed_limit_default"
                                           class="form-control"
                                           value="<?php
                                           foreach ($data['settings'] as $s) {
                                               if ($s['setting_key'] === 'speed_limit_default') echo $s['setting_value'];
                                           }
                                           ?>">
                                </div>

                                <h6 class="border-bottom pb-2 mb-3 mt-4"><i class="fas fa-table me-2"></i>Display</h6>

                                <div class="mb-3">
                                    <label class="form-label">Items Per Page</label>
                                    <input type="number"
                                           name="setting_items_per_page"
                                           class="form-control"
                                           value="<?php
                                           foreach ($data['settings'] as $s) {
                                               if ($s['setting_key'] === 'items_per_page') echo $s['setting_value'];
                                           }
                                           ?>">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Date Format</label>
                                    <select name="setting_date_format" class="form-select">
                                        <?php
                                        $dateFormatValue = '';
                                        foreach ($data['settings'] as $s) {
                                            if ($s['setting_key'] === 'date_format') $dateFormatValue = $s['setting_value'];
                                        }
                                        ?>
                                        <option value="Y-m-d" <?php echo $dateFormatValue === 'Y-m-d' ? 'selected' : ''; ?>>YYYY-MM-DD</option>
                                        <option value="d/m/Y" <?php echo $dateFormatValue === 'd/m/Y' ? 'selected' : ''; ?>>DD/MM/YYYY</option>
                                        <option value="m/d/Y" <?php echo $dateFormatValue === 'm/d/Y' ? 'selected' : ''; ?>>MM/DD/YYYY</option>
                                    </select>
                                </div>

                                <div class="d-flex gap-2 mt-4">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>Save Settings
                                    </button>
                                    <a href="<?php echo APP_URL; ?>/dashboard" class="btn btn-secondary">
                                        <i class="fas fa-times me-2"></i>Cancel
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once APP_PATH . '/views/includes/footer.php'; ?>
