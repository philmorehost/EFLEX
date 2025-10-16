import os
from playwright.sync_api import sync_playwright

def run(playwright):
    browser = playwright.chromium.launch()
    page = browser.new_page()
    page.goto("http://localhost:8000/login.php")
    page.fill('input[name="email"]', 'superadmin@cbt.com')
    page.fill('input[name="password"]', 'password123')
    page.click('button[type="submit"]')
    page.goto("http://localhost:8000/knowledge_base.php")
    page.screenshot(path="jules-scratch/verification/kb_page.png")
    browser.close()

with sync_playwright() as playwright:
    run(playwright)
