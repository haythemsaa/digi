<?php
/**
 * Alerts List View
 */
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0"><?= t('alerts.title') ?></h3>
                    <a href="/alerts/settings" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-cog"></i> Settings
                    </a>
                </div>

                <div class="card-body">
                    <!-- Filters -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <select class="form-select" id="priorityFilter" onchange="filterAlerts()">
                                <option value="">All Priorities</option>
                                <option value="critical" <?= $priority_filter === 'critical' ? 'selected' : '' ?>>Critical</option>
                                <option value="high" <?= $priority_filter === 'high' ? 'selected' : '' ?>>High</option>
                                <option value="medium" <?= $priority_filter === 'medium' ? 'selected' : '' ?>>Medium</option>
                                <option value="low" <?= $priority_filter === 'low' ? 'selected' : '' ?>>Low</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <select class="form-select" id="statusFilter" onchange="filterAlerts()">
                                <option value="">All Statuses</option>
                                <option value="unread" <?= $status_filter === 'unread' ? 'selected' : '' ?>>Unread</option>
                                <option value="read" <?= $status_filter === 'read' ? 'selected' : '' ?>>Read</option>
                                <option value="acknowledged" <?= $status_filter === 'acknowledged' ? 'selected' : '' ?>>Acknowledged</option>
                            </select>
                        </div>
                    </div>

                    <!-- Alerts Table -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Priority</th>
                                    <th>Type</th>
                                    <th>Title</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($alerts)): ?>
                                <tr>
                                    <td colspan="6" class="text-center">No alerts found</td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($alerts as $alert): ?>
                                <tr class="<?= $alert['status'] === 'unread' ? 'table-warning' : '' ?>">
                                    <td>
                                        <span class="badge bg-<?=
                                            $alert['priority'] === 'critical' ? 'danger' :
                                            ($alert['priority'] === 'high' ? 'warning' :
                                            ($alert['priority'] === 'medium' ? 'info' : 'secondary'))
                                        ?>">
                                            <?= ucfirst($alert['priority']) ?>
                                        </span>
                                    </td>
                                    <td><?= ucfirst(str_replace('_', ' ', $alert['type'])) ?></td>
                                    <td><?= htmlspecialchars($alert['title']) ?></td>
                                    <td>
                                        <span class="badge bg-<?=
                                            $alert['status'] === 'unread' ? 'warning' :
                                            ($alert['status'] === 'acknowledged' ? 'success' : 'secondary')
                                        ?>">
                                            <?= ucfirst($alert['status']) ?>
                                        </span>
                                    </td>
                                    <td><?= date('Y-m-d H:i', strtotime($alert['created_at'])) ?></td>
                                    <td>
                                        <a href="/alerts/view?id=<?= $alert['id'] ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($pages > 1): ?>
                    <nav>
                        <ul class="pagination">
                            <?php for ($i = 1; $i <= $pages; $i++): ?>
                            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?><?= $priority_filter ? '&priority='.$priority_filter : '' ?><?= $status_filter ? '&status='.$status_filter : '' ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function filterAlerts() {
    const priority = document.getElementById('priorityFilter').value;
    const status = document.getElementById('statusFilter').value;

    let url = '/alerts?';
    if (priority) url += 'priority=' + priority + '&';
    if (status) url += 'status=' + status;

    window.location.href = url;
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
