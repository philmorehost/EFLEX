import os
from playwright.sync_api import sync_playwright

def run(playwright):
    browser = playwright.chromium.launch()
    page = browser.new_page()

    # --- Admin Login and Screenshot ---
    page.goto("http://localhost:8000/login.php")
    page.fill('input[name="email"]', 'superadmin@cbt.com')
    page.fill('input[name="password"]', 'password123')
    page.click('button[type="submit"]')
    page.wait_for_url("http://localhost:8000/admin/index.php")
    page.screenshot(path="jules-scratch/verification/admin_dashboard.png")

    # --- Logout ---
    page.goto("http://localhost:8000/logout.php")
    page.wait_for_url("http://localhost:8000/login.php")

    # --- User Login and Screenshot ---
    page.goto("http://localhost:8000/login.php")
    page.fill('input[name="email"]', 'testuser@cbt.com')
    page.fill('input[name="password"]', 'password')
    page.click('button[type="submit"]')
    page.wait_for_url("http://localhost:8000/index.php")
    page.screenshot(path="jules-scratch/verification/user_dashboard.png")

    browser.close()

with sync_playwright() as playwright:
    run(playwright)
