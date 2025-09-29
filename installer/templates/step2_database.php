<h2>Step 2: Database Configuration</h2>
<p>Please provide your database connection details. The installer will use these to set up the application's tables.</p>

<div id="connection-status" style="display:none; padding: 10px; margin-bottom: 15px; border-radius: 5px;"></div>

<form id="db-form">
    <div class="form-group">
        <label for="db_host">Database Host</label>
        <input type="text" id="db_host" name="db_host" value="localhost" required>
    </div>
    <div class="form-group">
        <label for="db_name">Database Name</label>
        <input type="text" id="db_name" name="db_name" required>
    </div>
    <div class="form-group">
        <label for="db_user">Database Username</label>
        <input type="text" id="db_user" name="db_user" required>
    </div>
    <div class="form-group">
        <label for="db_pass">Database Password</label>
        <input type="password" id="db_pass" name="db_pass">
    </div>
</form>

<div class="installer-footer">
    <button id="test-connection-btn" class="btn">Test Connection</button>
    <a id="next-step-btn" href="index.php?step=3" class="btn" style="display: none;">Next Step</a>
</div>

<style>
    .form-group {
        margin-bottom: 15px;
    }
    .form-group label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
    }
    .form-group input {
        width: 100%;
        padding: 8px;
        border: 1px solid #ccc;
        border-radius: 4px;
        box-sizing: border-box;
    }
    #connection-status.success {
        background-color: #e9f7ef;
        border: 1px solid #28a745;
        color: #155724;
    }
    #connection-status.error {
        background-color: #f8d7da;
        border: 1px solid #dc3545;
        color: #721c24;
    }
</style>

<script>
document.getElementById('test-connection-btn').addEventListener('click', async function() {
    const form = document.getElementById('db-form');
    const formData = new FormData(form);
    const statusDiv = document.getElementById('connection-status');
    const nextBtn = document.getElementById('next-step-btn');
    const testBtn = this;

    testBtn.textContent = 'Testing...';
    testBtn.disabled = true;
    statusDiv.style.display = 'none';
    nextBtn.style.display = 'none';

    try {
        const response = await fetch('actions/test_db.php', {
            method: 'POST',
            body: formData
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const result = await response.json();

        statusDiv.textContent = result.message;
        statusDiv.className = result.status ? 'success' : 'error';
        statusDiv.style.display = 'block';

        if (result.status) {
            nextBtn.style.display = 'inline-block';
            // Disable form fields after successful test
            form.querySelectorAll('input').forEach(input => input.disabled = true);
        }

    } catch (error) {
        statusDiv.textContent = 'An unexpected error occurred: ' + error.message;
        statusDiv.className = 'error';
        statusDiv.style.display = 'block';
    } finally {
        testBtn.textContent = 'Test Connection';
        testBtn.disabled = false;
    }
});
</script>