<?php
$data['title'] = 'Users Management';
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

            <!-- Stats -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <h6>Total Users</h6>
                            <h2><?php echo count($data['users']); ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <h6>Active</h6>
                            <h2><?php echo count(array_filter($data['users'], fn($u) => $u['status'] === 'active')); ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <h6>Admins</h6>
                            <h2><?php echo count(array_filter($data['users'], fn($u) => $u['role'] === 'admin')); ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <h6>Drivers</h6>
                            <h2><?php echo count(array_filter($data['users'], fn($u) => $u['role'] === 'driver')); ?></h2>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="d-flex justify-content-between mb-4">
                <a href="<?php echo APP_URL; ?>/users/add" class="btn btn-primary">
                    <i class="fas fa-plus-circle me-2"></i>Add New User
                </a>
            </div>

            <!-- Users Table -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-users me-2"></i>All Users</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover datatable">
                            <thead>
                                <tr>
                                    <th>Username</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Last Login</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data['users'] as $user): ?>
                                    <tr>
                                        <td><strong><?php echo $user['username']; ?></strong></td>
                                        <td><?php echo $user['first_name'] . ' ' . $user['last_name']; ?></td>
                                        <td><?php echo $user['email']; ?></td>
                                        <td>
                                            <?php
                                            $roleColors = [
                                                'admin' => 'danger',
                                                'manager' => 'primary',
                                                'dispatcher' => 'info',
                                                'driver' => 'success',
                                                'mechanic' => 'warning',
                                                'accountant' => 'secondary'
                                            ];
                                            $color = $roleColors[$user['role']] ?? 'secondary';
                                            ?>
                                            <span class="badge bg-<?php echo $color; ?>"><?php echo ucfirst($user['role']); ?></span>
                                        </td>
                                        <td>
                                            <?php
                                            $statusColors = ['active' => 'success', 'inactive' => 'secondary', 'suspended' => 'danger'];
                                            $scolor = $statusColors[$user['status']] ?? 'secondary';
                                            ?>
                                            <span class="badge bg-<?php echo $scolor; ?>"><?php echo ucfirst($user['status']); ?></span>
                                        </td>
                                        <td>
                                            <?php if ($user['last_login']): ?>
                                                <small><?php echo date('d/m/Y H:i', strtotime($user['last_login'])); ?></small>
                                            <?php else: ?>
                                                <small class="text-muted">Never</small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="<?php echo APP_URL; ?>/users/edit/<?php echo $user['id']; ?>" class="btn btn-sm btn-warning">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                <button onclick="deleteUser(<?php echo $user['id']; ?>)" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function deleteUser(id) {
    confirmDelete('This user and all related data will be permanently deleted.').then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '<?php echo APP_URL; ?>/users/delete/' + id;
            document.body.appendChild(form);
            form.submit();
        }
    });
}
</script>

<?php require_once APP_PATH . '/views/includes/footer.php'; ?>
