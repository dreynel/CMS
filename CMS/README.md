# ISAT U Coffee Shop POS System with Online Reservation & QR Code

> **Official Campus Coffee & Smart Study Pod Reservation System**  
> Designed for **Iloilo Science and Technology University (ISAT U)**  
> Main Campus: Burgos St., La Paz, Iloilo City, Philippines

---

## ☕ Project Overview

The **ISAT U Coffee Shop POS & Online Reservation System** is a unified campus café management solution. It bridges the gap between student/faculty pre-orders, study pod reservations, high-speed touch cashier point-of-sale, and real-time kitchen barista fulfillment through scannable digital **QR Passes**.

---

## 🎨 Official ISAT U Brand Palette

- **Primary Navy Blue**: `#002080` / `#001557` (University Institutional Blue)
- **Academic Gold / Amber**: `#F59E0B` / `#D97706` (University Golden Seal)
- **Coffee Roastery Tones**: `#2B1810` (Espresso Dark), `#FDFBF7` (Cream Warmth), `#5D4037` (Café Mocha)
- **Official Seal**: Located in `assets/images/isatu.png`

---

## 📂 Project Directory Structure

```
CMS/
├── assets/
│   ├── css/
│   │   └── style.css            # Custom animations, POS touch cards, 80mm thermal receipt print styles
│   ├── js/
│   │   ├── mock-data.js         # Authentic ISAT U menu items, seating zones, and seed records
│   │   ├── store.js             # Reactive LocalStorage & BroadcastChannel cross-tab sync store
│   │   ├── pos.js               # Cashier POS controller (drink customizer, discounts, payment, receipts)
│   │   ├── reservation.js       # Customer table booking, time slot picker & QR pass generator
│   │   ├── scanner.js           # Staff camera QR code scanner & auto-checkin validator
│   │   ├── kitchen.js           # Barista Kitchen Display System (KDS) live queue
│   │   └── admin.js             # Sales analytics, Chart.js graphs, inventory stock controller
│   └── images/
│       └── isatu.png            # High-resolution ISAT U official university seal
├── index.html                   # Customer Online Reservation & Pre-Ordering Portal + QR Pass
├── pos.html                     # Cashier Point of Sale (POS) Touch Terminal
├── scanner.html                 # Staff QR Code Scanner & Reservation Validator
├── kitchen.html                 # Barista Kitchen Display System (KDS) Live Screen
├── admin.html                   # Admin & Management Dashboard (Analytics, Inventory, Bookings)
├── database/                    # Ready for MySQL & Laravel Eloquent
│   ├── schema.sql               # Complete relational MySQL schema with 11 tables
│   └── seeder.sql               # Seed records for ISAT U menu, tables, and test accounts
├── laravel-prep/                # Blueprint for PHP Laravel backend migration
│   └── ARCHITECTURE.md          # Models, Controllers, WebSockets, and Routes guide
└── README.md                    # Project documentation & user guide
```

---

## 🚀 Interactive Modules & Workflow

### 1. Customer Online Reservation & Pre-Order (`index.html`)
- **Interactive Floor Plan**: Select between *Study Hub (Quiet Pods)*, *Lounge Zone (Couches)*, *Main Hall*, and *Outdoor Al Fresco Terrace*.
- **Time Slot & Date Picker**: Book slots from morning study to evening research sessions.
- **Affiliation Discount Verification**: Supports ISAT U Student, Faculty, Visitor, and Senior/PWD discounts.
- **Advance Menu Pre-Ordering**: Select signature drinks, customize cup sizes, sweetness %, and add-ons.
- **Dynamic Digital QR Pass Generator**: Instantly renders an authentic QR code ticket that the guest can save, print, or bring on their mobile device.

### 2. Cashier POS Terminal (`pos.html`)
- **High-Speed Touch Menu Grid**: Live categorized filtering (Signatures, Espresso, Cold Brew, Pastries, Meals).
- **Modifier Modal**: Quick adjustment of cup size, sugar level (0% to 100%), ice temperature, and toppings.
- **Discounts Engine**: One-touch **ISAT U Student/Faculty (10%)** and **Senior/PWD (20%)** discount recalculation.
- **Multi-Channel Checkout**: Cash with quick change calculation presets, GCash QR, and Campus ID card balance.
- **Thermal POS Receipt**: Formatted for standard 80mm thermal receipt printers with full itemization and barcodes (`window.print()`).

### 3. Staff QR Code Scanner (`scanner.html`)
- **Camera & Image Scanner**: Integrated `html5-qrcode` engine with live camera stream, file drag-and-drop, and manual code search.
- **Instant Ticket Decoder**: Decodes customer reservation passes, showing guest identity, table assignment, payment status, and food orders.
- **One-Click Check-In & Kitchen Push**: Automatically changes table status to *Occupied* and routes the order to the barista screen.

### 4. Barista Kitchen Display System (`kitchen.html`)
- **Kanban Order Board**: Grouped into *1. New Orders*, *2. Brewing / Preparing*, *3. Ready for Pickup*, and *4. Completed*.
- **Urgency Timers**: Dynamic elapsed minutes indicator with color alerts if an order exceeds 10 minutes.
- **Sound Chime Alerts**: Web Audio tone on incoming orders.

### 5. Admin & Manager Analytics (`admin.html`)
- **Real-Time KPIs**: Total sales revenue, orders count, active table bookings, and low stock inventory alerts.
- **Interactive Visual Charts**: Hourly sales velocity (Line Chart) and category volume breakdown (Donut Chart).
- **Reservation Master List**: Filter, approve, check-in, or cancel table reservations.
- **Inventory Manager**: Increment/decrement product stocks with live warning highlights.

---

## ⚡ Real-Time Cross-Tab State Synchronization

All pages are connected via `assets/js/store.js` utilizing **`LocalStorage`** and **`BroadcastChannel`**:
1. Open `index.html` in one browser tab and place a reservation with drinks.
2. Open `scanner.html` in another tab and scan the code. Click **"Check-In Guest"**.
3. Open `kitchen.html` in a third tab: the order immediately pops up in the **New Orders** queue!
4. Open `pos.html` and `admin.html`: sales metrics, table states, and inventory counts reflect the change in real-time.

---

## 🛠️ How to Run Locally in XAMPP

1. Ensure your XAMPP Apache service is running.
2. The project is situated at `d:/xampp/htdocs/CMS`.
3. Open your browser and navigate to:
   - **Customer Portal**: `http://localhost/CMS/index.html`
   - **Cashier POS**: `http://localhost/CMS/pos.html`
   - **Staff QR Scanner**: `http://localhost/CMS/scanner.html`
   - **Kitchen KDS**: `http://localhost/CMS/kitchen.html`
   - **Admin Dashboard**: `http://localhost/CMS/admin.html`

---

## 🐘 PHP Laravel & MySQL Backend Migration

When you are ready to implement the backend:
1. Import `database/schema.sql` into your MySQL server (via phpMyAdmin or `mysql -u root -p < database/schema.sql`).
2. Run `database/seeder.sql` to populate initial menu items and accounts.
3. Follow the comprehensive roadmap in `laravel-prep/ARCHITECTURE.md` to map the Blade templates, Eloquent models, API controllers, and Laravel Reverb WebSockets.

---
© 2026 Iloilo Science and Technology University (ISAT U). All Rights Reserved.
