<?php
session_start();

// Redirect if the setup is not complete
if (!isset($_SESSION['db_credentials']) || !isset($_SESSION['site_details'])) {
    header('Location: index.php?step=1');
    exit;
}
?>

<h2>Step 4: Final Installation</h2>
<p>Everything is ready to go! Press the button below to write the configuration files and set up your database.</p>

<div id="install-progress">
    <ul id="progress-list"></ul>
</div>

<div id="install-result" style="display:none; padding: 10px; margin-bottom: 15px; border-radius: 5px;"></div>

<div class="installer-footer">
    <button id="start-install-btn" class="btn">Install Now</button>
    <a id="site-link" href="../" class="btn" style="display:none;">Go to Your Website</a>
</div>

<style>
#progress-list {
    list-style-type: none;
    padding: 0;
    font-family: monospace;
    background: #2c3e50;
    color: #ecf0f1;
    padding: 15px;
    border-radius: 5px;
    height: 200px;
    overflow-y: auto;
}
#progress-list li {
    padding: 2px 0;
    border-bottom: 1px dotted #34495e;
}
#install-result.success { background-color: #e9f7ef; border: 1px solid #28a745; color: #155724; }
#install-result.error { background-color: #f8d7da; border: 1px solid #dc3545; color: #721c24; }
</style>

<script>
document.getElementById('start-install-btn').addEventListener('click', async function() {
    const installBtn = this;
    const progressList = document.getElementById('progress-list');
    const resultDiv = document.getElementById('install-result');
    const siteLink = document.getElementById('site-link');

    installBtn.disabled = true;
    installBtn.textContent = 'Installing...';

    function addProgress(message) {
        const li = document.createElement('li');
        li.textContent = `[${new Date().toLocaleTimeString()}] ${message}`;
        progressList.appendChild(li);
        progressList.scrollTop = progressList.scrollHeight;
    }

    addProgress('Starting installation...');

    try {
        addProgress('Sending request to server...');
        const response = await fetch('actions/install.php', {
            method: 'POST',
        });

        if (!response.ok) {
            throw new Error(`Server returned an error: ${response.statusText}`);
        }

        addProgress('Waiting for server response...');
        const result = await response.json();

        addProgress(`Server says: ${result.message}`);

        resultDiv.textContent = result.message;
        resultDiv.className = result.success ? 'success' : 'error';
        resultDiv.style.display = 'block';

        if (result.success) {
            installBtn.style.display = 'none';
            siteLink.style.display = 'inline-block';
            addProgress('🎉 Installation Complete! 🎉');
            addProgress('IMPORTANT: For security, please delete the "installer" directory from your server.');
        } else {
             installBtn.disabled = false;
             installBtn.textContent = 'Retry Installation';
        }

    } catch (error) {
        addProgress(`An error occurred: ${error.message}`);
        resultDiv.textContent = 'A critical error occurred. Check the browser console for details.';
        resultDiv.className = 'error';
        resultDiv.style.display = 'block';
        installBtn.disabled = false;
        installBtn.textContent = 'Retry Installation';
    }
});
</script>