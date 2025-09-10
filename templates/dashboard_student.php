<?php
// templates/dashboard_student.php
// This template is included from dashboard.php for users with the 'User' role.
// It expects the $assigned_tests variable to be set.
?>
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">My Dashboard</h1>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 fw-bold text-primary">My Assigned Tests</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                    <thead class="table-light">
                        <tr>
                            <th>Test Name</th>
                            <th>Duration</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($assigned_tests)): ?>
                            <tr>
                                <td colspan="4" class="text-center">You have not been assigned any tests yet.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($assigned_tests as $test): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($test['test_name']); ?></td>
                                    <td><?php echo htmlspecialchars($test['duration']); ?> minutes</td>
                                    <td>
                                        <?php
                                            $status = htmlspecialchars($test['status']);
                                            $badge_class = 'bg-secondary';
                                            if ($status === 'pending') $badge_class = 'bg-primary';
                                            if ($status === 'completed') $badge_class = 'bg-success';
                                            if ($status === 'in_progress') $badge_class = 'bg-warning text-dark';
                                        ?>
                                        <span class="badge <?php echo $badge_class; ?>"><?php echo ucfirst($status); ?></span>
                                    </td>
                                    <td>
                                        <?php
                                        $is_scheduled = !empty($test['scheduled_time']);
                                        $is_future = $is_scheduled && (strtotime($test['scheduled_time']) > time());

                                        if ($test['status'] === 'pending') {
                                            if ($is_future) {
                                                echo '<button class="btn btn-sm btn-success" disabled>Scheduled for ' . date('M j, Y H:i', strtotime($test['scheduled_time'])) . '</button>';
                                            } else {
                                                echo '<a href="' . BASE_URL . '/take_test.php?id=' . $test['user_test_id'] . '" class="btn btn-sm btn-success"><i class="fas fa-play-circle me-1"></i>Start Test</a>';
                                            }
                                        } elseif ($test['status'] === 'completed') {
                                            echo '<a href="' . BASE_URL . '/results.php?id=' . $test['user_test_id'] . '" class="btn btn-sm btn-info">View Results</a>';
                                        } else {
                                            echo '-';
                                        }
                                        ?>
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
