<?php require_once APP_ROOT . '/views/includes/header.php'; ?>

<div class="admin-container">
    <h2><?php echo $data['title']; ?></h2>
    <p>Review and process pending loan applications below.</p>

    <?php flash('loan_management_success'); ?>
    <?php flash('loan_management_error'); ?>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Amount Requested</th>
                    <th>Date Applied</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($data['pending_loans'])): ?>
                    <tr>
                        <td colspan="4" style="text-align: center;">No pending loan applications.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($data['pending_loans'] as $loan): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($loan->username); ?></td>
                            <td>₦<?php echo number_format($loan->amount_requested, 2); ?></td>
                            <td><?php echo date('F j, Y', strtotime($loan->created_at)); ?></td>
                            <td class="action-buttons">
                                <form action="<?php echo BASE_URL; ?>/admin/approveLoan/<?php echo $loan->id; ?>" method="post" style="display:inline;">
                                    <button type="submit" class="btn btn-success">Approve</button>
                                </form>
                                <form action="<?php echo BASE_URL; ?>/admin/denyLoan/<?php echo $loan->id; ?>" method="post" style="display:inline;">
                                    <button type="submit" class="btn btn-danger">Deny</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
    .admin-container { width: 90%; max-width: 900px; margin: 20px auto; background: #fff; padding: 2rem; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
    .table-responsive { overflow-x: auto; }
    .table { width: 100%; border-collapse: collapse; margin-top: 1.5rem; }
    .table th, .table td { padding: 0.75rem; text-align: left; border-bottom: 1px solid #dee2e6; }
    .table thead th { vertical-align: bottom; border-bottom-width: 2px; }
    .btn { padding: .375rem .75rem; font-size: 0.9rem; border-radius: .2rem; cursor: pointer; border: 1px solid transparent; }
    .btn-success { color: #fff; background-color: #28a745; border-color: #28a745; }
    .btn-danger { color: #fff; background-color: #dc3545; border-color: #dc3545; }
    .action-buttons form { margin-right: 5px; }
</style>

<?php require_once APP_ROOT . '/views/includes/footer.php'; ?>