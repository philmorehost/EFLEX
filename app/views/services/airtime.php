<?php require_once APP_ROOT . '/views/includes/header.php'; ?>

<div class="service-container">
    <h2><?php echo $data['title']; ?></h2>
    <p><?php echo $data['description']; ?></p>

    <?php flash('service_error'); ?>
    <?php flash('service_success'); ?>

    <form action="<?php echo BASE_URL; ?>/services/airtime" method="post" class="service-form">
        <div class="form-group">
            <label for="network">Select Network</label>
            <select name="network" id="network" class="form-control" required>
                <option value="">-- Select Network --</option>
                <option value="mtn">MTN</option>
                <option value="glo">GLO</option>
                <option value="airtel">Airtel</option>
                <option value="9mobile">9mobile</option>
            </select>
        </div>
        <div class="form-group">
            <label for="phone_number">Phone Number</label>
            <input type="tel" name="phone_number" id="phone_number" class="form-control" placeholder="Enter phone number" required>
        </div>
        <div class="form-group">
            <label for="amount">Amount (₦)</label>
            <input type="number" name="amount" id="amount" class="form-control" placeholder="e.g., 100" required>
        </div>

        <button type="submit" class="btn btn-primary">Purchase Airtime</button>
    </form>
</div>

<style>
    .service-container {
        width: 90%;
        max-width: 500px;
        margin: 20px auto;
        background: #fff;
        padding: 2rem;
        border-radius: 5px;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    .service-form {
        margin-top: 1.5rem;
    }
    .form-group {
        margin-bottom: 1rem;
    }
    .form-group label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
    }
    .form-control {
        width: 100%;
        padding: .75rem;
        border: 1px solid #ccc;
        border-radius: 4px;
        box-sizing: border-box;
        font-size: 1rem;
    }
    .btn { display: inline-block; font-weight: 400; padding: .75rem 1.5rem; font-size: 1rem; border-radius: .25rem; text-decoration: none; cursor: pointer; border: 1px solid transparent; width: 100%; }
    .btn-primary { color: #fff; background-color: #007bff; border-color: #007bff; }
    .alert { padding: .75rem 1.25rem; margin-bottom: 1rem; border: 1px solid transparent; border-radius: .25rem; }
    .alert-danger { color: #721c24; background-color: #f8d7da; border-color: #f5c6cb; }
    .alert-success { color: #155724; background-color: #d4edda; border-color: #c3e6cb; }
</style>

<?php require_once APP_ROOT . '/views/includes/footer.php'; ?>