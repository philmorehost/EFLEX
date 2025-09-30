<?php require_once APP_ROOT . '/views/includes/header.php'; ?>

<div class="dashboard-container">
    <h1><?php echo $data['title']; ?></h1>
    <p><?php echo $data['description']; ?></p>

    <div class="dashboard-widgets">
        <div class="widget">
            <h3>Wallet Balance</h3>
            <p class="balance"><?php echo $data['balance']; ?></p>
            <a href="<?php echo BASE_URL; ?>/wallet" class="btn btn-primary">Fund Wallet</a>
        </div>
        <div class="widget">
            <h3>Recent Transactions</h3>
            <p>No recent transactions.</p>
        </div>
    </div>

    <h2>Services</h2>
    <div class="service-buttons">
        <a href="<?php echo BASE_URL; ?>/services/airtime" class="service-btn">Airtime</a>
        <a href="<?php echo BASE_URL; ?>/services/data" class="service-btn">Data</a>
        <a href="<?php echo BASE_URL; ?>/services/cable" class="service-btn">Cable TV</a>
        <a href="<?php echo BASE_URL; ?>/services/electricity" class="service-btn">Electricity</a>
        <a href="#" class="service-btn">Transfer</a>
        <a href="#" class="service-btn">Savings</a>
    </div>
</div>

<style>
    .dashboard-container {
        width: 90%;
        max-width: 1200px;
        margin: auto;
    }
    .dashboard-widgets {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin: 20px 0;
    }
    .widget {
        background: #fff;
        padding: 20px;
        border-radius: 5px;
        box-shadow: 0 0 10px rgba(0,0,0,0.05);
    }
    .widget h3 {
        margin-top: 0;
    }
    .balance {
        font-size: 2em;
        font-weight: bold;
        color: #28a745;
    }
    .service-buttons {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 15px;
        margin-top: 20px;
    }
    .service-btn {
        background-color: #4a90e2;
        color: white;
        padding: 20px;
        text-align: center;
        border-radius: 5px;
        text-decoration: none;
        font-size: 1.1em;
        transition: background-color 0.3s;
    }
    .service-btn:hover {
        background-color: #357abd;
    }
    .btn { display: inline-block; font-weight: 400; padding: .5rem 1rem; font-size: 1rem; border-radius: .25rem; text-decoration: none; }
    .btn-primary { color: #fff; background-color: #007bff; border-color: #007bff; }
</style>

<?php require_once APP_ROOT . '/views/includes/footer.php'; ?>