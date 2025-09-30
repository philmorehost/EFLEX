<?php require_once APP_ROOT . '/app/views/includes/header.php'; ?>

<div class="service-container">
    <h2><?php echo $data['title']; ?></h2>
    <p><?php echo $data['description']; ?></p>

    <?php flash('loan_error'); ?>
    <?php flash('loan_success'); ?>

    <?php if ($data['active_loan']): ?>
        <div class="status-box info">
            <h4>Your Loan Status</h4>
            <p>You have a loan with status: <strong><?php echo ucfirst($data['active_loan']->status); ?></strong></p>
            <p>Amount: ₦<?php echo number_format($data['active_loan']->amount_requested, 2); ?></p>
            <?php if ($data['active_loan']->status == 'active'):
                $total_due = $data['active_loan']->amount_requested * (1 + $data['active_loan']->interest_rate / 100);
                $outstanding = $total_due - $data['active_loan']->amount_repaid;
            ?>
                <p>Total Due (with interest): ₦<?php echo number_format($total_due, 2); ?></p>
                <p>Amount Repaid: ₦<?php echo number_format($data['active_loan']->amount_repaid, 2); ?></p>
                <p><strong>Outstanding Balance: ₦<?php echo number_format($outstanding, 2); ?></strong></p>
                <p>Due Date: <?php echo date('F j, Y', strtotime($data['active_loan']->due_date)); ?></p>

                <hr>
                <h4>Make a Repayment</h4>
                <form action="<?php echo BASE_URL; ?>/loan/repay/<?php echo $data['active_loan']->id; ?>" method="post" class="service-form">
                    <div class="form-group">
                        <label for="amount">Repayment Amount</label>
                        <input type="number" name="amount" class="form-control" placeholder="e.g., 1000" required step="0.01" max="<?php echo $outstanding; ?>">
                    </div>
                    <button type="submit" class="btn btn-success">Repay From Wallet</button>
                </form>
            <?php endif; ?>
        </div>
    <?php elseif (!$data['is_eligible']): ?>
        <div class="status-box error">
            <h4>Not Eligible for Loan</h4>
            <p><?php echo htmlspecialchars($data['eligibility_message']); ?></p>
        </div>
    <?php else: ?>
        <div class="status-box success">
            <h4>You are Eligible!</h4>
            <p><?php echo htmlspecialchars($data['eligibility_message']); ?></p>
        </div>
        <form action="<?php echo BASE_URL; ?>/loan" method="post" class="service-form">
            <div class="form-group">
                <label for="amount">Loan Amount (Max: ₦<?php echo number_format($data['max_loan_amount'], 2); ?>)</label>
                <input type="number" name="amount" id="amount" class="form-control" placeholder="e.g., 5000" required max="<?php echo $data['max_loan_amount']; ?>">
            </div>

            <button type="submit" class="btn btn-primary">Apply for Loan</button>
        </form>
    <?php endif; ?>
</div>

<style>
    /* Most styles inherited */
    .service-container { width: 90%; max-width: 600px; margin: 20px auto; background: #fff; padding: 2rem; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
    .status-box {
        padding: 1rem;
        margin-bottom: 2rem;
        border-radius: 5px;
        border: 1px solid #ccc;
    }
    .status-box.info { background-color: #e2e3e5; border-color: #d6d8db; color: #383d41; }
    .status-box.error { background-color: #f8d7da; border-color: #f5c6cb; color: #721c24; }
    .status-box.success { background-color: #d4edda; border-color: #c3e6cb; color: #155724; }
    .status-box h4 { margin-top: 0; }
</style>

<?php require_once APP_ROOT . '/app/views/includes/footer.php'; ?>