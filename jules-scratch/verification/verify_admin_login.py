from playwright.sync_api import sync_playwright

def run(playwright):
    browser = playwright.chromium.launch()
    page = browser.new_page()

    # Navigate to the admin/login.php page using the local server
    page.goto("http://localhost:8000/admin/login.php")

    # Take a screenshot of the page
    page.screenshot(path="jules-scratch/verification/admin_login.png")

    browser.close()

with sync_playwright() as playwright:
    run(playwright)
