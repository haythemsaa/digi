<?php
$data['title'] = 'Clients';
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

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4><i class="fas fa-users me-2"></i>Clients</h4>
                <a href="<?php echo APP_URL; ?>/transport/addClient" class="btn btn-primary">
                    <i class="fas fa-plus-circle me-1"></i>Add Client
                </a>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover datatable">
                            <thead>
                                <tr>
                                    <th>Client</th>
                                    <th>Type</th>
                                    <th>Contact</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (isset($data['clients'])): ?>
                                    <?php foreach ($data['clients'] as $client): ?>
                                        <tr>
                                            <td>
                                                <strong>
                                                    <?php echo $client['company_name'] ?? ($client['first_name'] . ' ' . $client['last_name']); ?>
                                                </strong>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php echo $client['client_type'] === 'company' ? 'primary' : 'info'; ?>">
                                                    <?php echo ucfirst($client['client_type']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo $client['contact_person'] ?? 'N/A'; ?></td>
                                            <td><?php echo $client['email'] ?? 'N/A'; ?></td>
                                            <td><?php echo $client['phone'] ?? 'N/A'; ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $client['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                                    <?php echo ucfirst($client['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="<?php echo APP_URL; ?>/transport/viewClient/<?php echo $client['id']; ?>" class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once APP_PATH . '/views/includes/footer.php'; ?>
