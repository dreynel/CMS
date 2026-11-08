# Hostinger Deployment Guide: ISAT U Coffee Shop POS & Online Reservation System

This project is built using **100% Native PHP 8.x, MySQL, and Bootstrap 5.3**. It has **zero framework requirements** (no Composer, no Laravel, no npm, no CLI commands needed on the server).

It runs directly out of the box on **Hostinger Shared Hosting, cPanel, or VPS**.

---

## 🚀 Quick 3-Step Hostinger Deployment

### Step 1: Upload Files to Hostinger File Manager
1. Log in to your **Hostinger hPanel** (`https://hpanel.hostinger.com`).
2. Go to **Websites** &rarr; Select your domain &rarr; Click **File Manager** (or use FTP / FileZilla).
3. Open the `public_html/` folder.
4. Upload all files from this repository directly into `public_html/`:
   ```text
   public_html/
   ├── api/                 (Native PHP endpoints)
   ├── assets/              (Bootstrap CSS, ISAT U logo)
   ├── config/              (database.php)
   ├── includes/            (header.php & footer.php)
   ├── .htaccess            (Apache configuration)
   ├── index.php            (Customer Booking Portal)
   ├── pass.php             (Digital QR Pass View)
   ├── pos.php              (Cashier Touch POS)
   ├── receipt.php          (80mm Thermal Receipt)
   ├── kitchen.php          (Barista KDS Display)
   ├── scanner.php          (Staff QR Scanner)
   ├── admin.php            (Manager Dashboard)
   ├── setup.php            (1-Click Web Installer)
   └── database.sql         (Database Schema & Seed Data)
   ```

---

### Step 2: Create MySQL Database in Hostinger
1. In **hPanel**, navigate to **Databases** &rarr; **Management**.
2. Click **Create New MySQL Database & User**:
   - **Database Name:** e.g. `u123456789_isatu`
   - **Database Username:** e.g. `u123456789_admin`
   - **Password:** Choose a strong password (e.g. `MySecurePass2026!`)
3. Note these credentials.

---

### Step 3: Run the 1-Click Web Installer OR Import via phpMyAdmin

#### Option A: Automatic 1-Click Web Installer (Easiest)
1. Open your browser and navigate to:
   ```text
   https://yourdomain.com/setup.php
   ```
2. The installer will display the database setup page.
3. If connecting for the first time, update `config/database.php` with your Hostinger database name, username, and password:
   ```php
   $host = 'localhost';
   $dbname = 'u123456789_isatu';
   $user = 'u123456789_admin';
   $pass = 'MySecurePass2026!';
   ```
4. Click **"Run Automated Database Setup"**.
5. It will automatically create all 11 tables and populate the default menu, dining tables, study pods, and initial demo accounts.

#### Option B: Manual phpMyAdmin Import
1. In Hostinger **hPanel** &rarr; **Databases** &rarr; Click **phpMyAdmin** next to your created database.
2. Click the **Import** tab at the top.
3. Click **Choose File** and select `database.sql`.
4. Click **Go / Import** at the bottom.
5. All 11 relational tables will be imported immediately.
#### Option B: Manual phpMyAdmin Import (Zero Configuration)
1. In Hostinger **hPanel** &rarr; **Databases** &rarr; Click **phpMyAdmin** next to your database.
2. Select your assigned database from the left-hand navigation list (e.g., `u123456789_isatu`).
3. Click the **Import** tab at the top navigation bar.
4. Click **Choose File** and select [`database_hostinger.sql`](file:///d:/xampp/htdocs/CMS/database_hostinger.sql) (or `database.sql`).
3. Click the **Import** tab on the top navigation bar.
4. Click **Choose File** and select [`database_hostinger.sql`](file:///d:/xampp/htdocs/CMS/database_hostinger.sql).
5. Scroll down and click **Go / Import**.
6. All 11 tables and full ISAT U seed data (menu items, categories, study pods, staff accounts) will import with 100% success!
6. All 13 tables and full ISAT U seed data (menu items, categories, study pods, staff accounts) will import with 100% success!

---

## 🔑 Application Portals & URLs

Once uploaded, you can access the core portals immediately:

| Portal | URL Path | Description |
|---|---|---|
| **Customer Online Booking & Pre-Order** | `https://yourdomain.com/index.php` | Seating floor map, study pods, drink customizer, instant QR pass |
| **Digital QR Pass** | `https://yourdomain.com/pass.php?token=...` | Boarding-pass format for phone display or printout |
| **Cashier Touch POS** | `https://yourdomain.com/pos.php` | Counter terminal, QR code product auto-read, cash/change calculator |
| **80mm Thermal Receipt** | `https://yourdomain.com/receipt.php?order_id=...` | ESC/POS printable receipt with Philippine 12% VAT breakdown |
| **Barista Kitchen Display System (KDS)** | `https://yourdomain.com/kitchen.php` | Dark-mode live Kanban board with 5s auto-polling & audio chimes |
| **Staff QR Scanner & Check-in** | `https://yourdomain.com/scanner.php` | Live camera QR scanner, token validator, 1-click dispatch to kitchen |
| **Manager Admin Dashboard** | `https://yourdomain.com/admin.php` | Real-time sales KPIs, Chart.js trends, inventory adjustment table |
| Portal | URL Path | Description | Default Test Credentials |
|---|---|---|---|
| **Single Unified Sign-in** | `https://yourdomain.com/login.php` | Single entry point with auto role detection | Demo 1-click accounts on screen |
| **Customer Online Booking & Pre-Order** | `https://yourdomain.com/booking` (or `index.php`) | Floor map, study pods, drink customizer, instant QR pass | Open to public |
| **Digital QR Pass** | `https://yourdomain.com/pass?code=...` | Digital boarding-pass ticket for phone display or printout | Open to public |
| **Cashier Touch POS** | `https://yourdomain.com/pos` (or `pos.php`) | Full-screen touch terminal, statutory discounts, Cash/GCash | `cashier.bea@isatu.edu.ph` / `password` |
| **Barista Kitchen Display System (KDS)** | `https://yourdomain.com/kitchen` (or `kitchen.php`) | Live brewing Kanban board with auto-polling & audio chimes | `barista.jason@isatu.edu.ph` / `password` |
| **Staff Concierge QR Scanner** | `https://yourdomain.com/scanner` (or `scanner.php`) | Mobile QR camera scanner, token check-in & table dispatch | `staff.maria@isatu.edu.ph` / `password` |
| **Manager Admin Console** | `https://yourdomain.com/admin` (or `admin.php`) | Financial reporting (Daily/Monthly/Yearly/Range), audit ledger | `admin@isatu.edu.ph` / `password` |
| **80mm Thermal Receipt** | `https://yourdomain.com/receipt.php?order_id=...` | ESC/POS printable receipt with Philippine 12% VAT breakdown | Cashier & Admin |

---

## 🔒 Security Best Practices for Production
- After initial setup, you may delete or restrict `setup.php`.
- The included `.htaccess` automatically blocks direct web access to `.sql`, `config/`, and `.log` files.
- The PDO database connection utilizes prepared statements for 100% SQL injection prevention.
- All user inputs are sanitized with `htmlspecialchars` and type casting to protect against XSS.

