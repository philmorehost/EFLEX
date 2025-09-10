# CBT Platform - Installation Guide

This guide provides step-by-step instructions on how to install the CBT Platform script on both a local XAMPP server and a live cPanel hosting environment.

---

### **General First Steps (For Both Environments)**

1.  **Get the Project Files:**
    *   Download or clone all the project files to your local computer.

2.  **Create a Database:**
    *   Whether you are using XAMPP (with phpMyAdmin) or cPanel, you need to create a new, empty database. For example, you can name it `cbt_platform`.
    *   Create a database user and assign it to the new database with full privileges. Make sure to note down the database name, username, and password.

---

### **Installation on XAMPP (Localhost)**

1.  **Place Files:**
    *   Copy the entire project folder into your XAMPP `htdocs` directory. For example, `C:/xampp/htdocs/cbt-platform/`.

2.  **Update Configuration:**
    *   Open the `config/config.php` file in a text editor.
    *   Update the database credentials (`DB_NAME`, `DB_USER`, `DB_PASS`) to match the database you created in the general steps. For a default XAMPP setup, `DB_USER` is 'root' and `DB_PASS` is empty.

3.  **Run the Installer:**
    *   Start your Apache and MySQL services in the XAMPP Control Panel.
    *   Open your web browser and navigate to the installer script. If you placed the project in `cbt-platform`, the URL is: `http://localhost/cbt-platform/database/install.php`.
    *   You should see a success message indicating that the database tables and default admin user have been created.

4.  **Configure Apache Virtual Host (Highly Recommended):**
    *   For the site to work correctly with clean URLs, you should point a virtual host to the `/public` directory.
    *   Open `C:/xampp/apache/conf/extra/httpd-vhosts.conf`.
    *   Add the following code at the end of the file:
        ```apache
        <VirtualHost *:80>
            DocumentRoot "C:/xampp/htdocs/cbt-platform/public"
            ServerName cbt.localhost
            <Directory "C:/xampp/htdocs/cbt-platform/public">
                AllowOverride All
                Require all granted
            </Directory>
        </VirtualHost>
        ```
    *   Open your hosts file (`C:/Windows/System32/drivers/etc/hosts` on Windows) and add the line: `127.0.0.1 cbt.localhost`.
    *   Restart the Apache service from the XAMPP Control Panel.

5.  **Final Steps:**
    *   You can now access the site at `http://cbt.localhost`.
    *   Log in with the default Super Admin credentials:
        *   **Email:** `superadmin@example.com`
        *   **Password:** `password`
    *   **IMPORTANT:** For security, delete the `database/install.php` file now.

---

### **Installation on cPanel (Live Hosting)**

1.  **Upload Files:**
    *   Log in to your cPanel account.
    *   Open the "File Manager" and navigate to the directory where you want to install the site (e.g., `public_html/cbt/`).
    *   Upload the zipped project files and extract them.

2.  **Update Configuration:**
    *   Using the File Manager, open the `config/config.php` file for editing.
    *   Update the database credentials (`DB_NAME`, `DB_USER`, `DB_PASS`) to match the database you created in cPanel.

3.  **Run the Installer:**
    *   Open your web browser and navigate to the installer script (e.g., `https://yourdomain.com/cbt/database/install.php`).
    *   You should see a success message.

4.  **Set the Document Root:**
    *   This is the most important step for live hosting. You need to point the domain or subdomain to the `/public` directory.
    *   If you are using a subdomain (e.g., `cbt.yourdomain.com`), go to the "Subdomains" section in cPanel. When creating or editing the subdomain, change its "Document Root" to point to the `/public` folder (e.g., `public_html/cbt/public`).
    *   If you are using the main domain, this is more complex and may require `.htaccess` rules or server configuration changes. Using a subdomain is highly recommended.

5.  **Set Up Cron Job:**
    *   In cPanel, find the "Cron Jobs" tool.
    *   Under "Add New Cron Job", select a schedule (e.g., "Once every 5 minutes").
    *   In the "Command" field, enter the command displayed on the System Settings page of the application. It will look something like this (you must use the full server path):
        ```bash
        /usr/local/bin/php /home/your_cpanel_user/public_html/cbt/cron/run_tasks.php
        ```
    *   Click "Add New Cron Job".

6.  **Final Steps:**
    *   You can now access the site at its domain (e.g., `https://cbt.yourdomain.com`).
    *   Log in with the default Super Admin credentials.
    *   **IMPORTANT:** Using the File Manager, delete the `database/install.php` file.
