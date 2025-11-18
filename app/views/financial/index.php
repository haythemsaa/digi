<?php
$data['title'] = 'Financial Management';
require_once APP_PATH . '/views/includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once APP_PATH . '/views/includes/sidebar.php'; ?>

        <div class="col-md-9 col-lg-10 main-content">
            <?php require_once APP_PATH . '/views/includes/navbar.php'; ?>

            <!-- Stats -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <h6>Total Income</h6>
                            <h2><?php echo number_format($data['summary']['total_income'] ?? 0, 2); ?> TND</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-danger text-white">
                        <div class="card-body">
                            <h6>Total Expenses</h6>
                            <h2><?php echo number_format($data['summary']['total_expenses'] ?? 0, 2); ?> TND</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <h6>Net Balance</h6>
                            <h2><?php echo number_format($data['summary']['net_balance'] ?? 0, 2); ?> TND</h2>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Accounts -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-wallet me-2"></i>Accounts</h5>
                    <a href="<?php echo APP_URL; ?>/financial/addAccount" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus me-1"></i>Add Account
                    </a>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php foreach ($data['accounts'] as $account): ?>
                            <div class="col-md-4 mb-3">
                                <div class="card">
                                    <div class="card-body">
                                        <h6><?php echo $account['account_name']; ?></h6>
                                        <p class="mb-1"><small class="text-muted"><?php echo ucfirst($account['account_type']); ?></small></p>
                                        <h4 class="mb-0 <?php echo $account['balance'] >= 0 ? 'text-success' : 'text-danger'; ?>">
                                            <?php echo number_format($account['balance'], 2); ?> <?php echo $account['currency']; ?>
                                        </h4>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Recent Transactions -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-exchange-alt me-2"></i>Recent Transactions</h5>
                    <a href="<?php echo APP_URL; ?>/financial/addTransaction" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus me-1"></i>Add Transaction
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover datatable">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Account</th>
                                    <th>Type</th>
                                    <th>Category</th>
                                    <th>Description</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data['transactions'] as $trans): ?>
                                    <tr>
                                        <td><?php echo date('d/m/Y', strtotime($trans['transaction_date'])); ?></td>
                                        <td><?php echo $trans['account_name']; ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $trans['type'] === 'income' ? 'success' : 'danger'; ?>">
                                                <?php echo ucfirst($trans['type']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo ucfirst($trans['category']); ?></td>
                                        <td><?php echo $trans['description']; ?></td>
                                        <td class="<?php echo $trans['type'] === 'income' ? 'text-success' : 'text-danger'; ?>">
                                            <?php echo $trans['type'] === 'income' ? '+' : '-'; ?>
                                            <?php echo number_format($trans['amount'], 2); ?> TND
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

<?php require_once APP_PATH . '/views/includes/footer.php'; ?>
