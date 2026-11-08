# -*- coding: utf-8 -*-
"""
Update Manuscript Data (Chapters 1-3), PlantUML files, 300-DPI Vector Diagrams,
and Word/Markdown Manuscripts to reflect all latest system features:
1. Made-to-Order POS Terminal (Sales-only at POS, no finished product stock trap)
   + Cup, Straw, and Takeout Box packaging stock validation before ordering.
2. Barista Kitchen Display System (KDS) showing full Base Recipe Ingredients (BOM),
   Allowed Add-ons, and Packaging specs, with atomic Brew-to-Serve inventory deduction.
3. Perpetual Inventory Suite with Datalist-validated Stock In/Out vouchers,
   Item Conversions, Master Item List (Raw vs Finished starting at 0 stock),
   and Recipe & Allowed Add-ons Manager.
4. Finished Product QR Code Generation, Printable QR Tag Sheets for New & Existing Menus,
   and POS Optical Camera + 2D Hardware QR Code Scanning.
5. Scheduled Pre-Orders 15 Minutes Before Booking Arrival (T-15m), Customer Digital QR Pass
   live status tracking & post-booking order management, and Staff Concierge Scanner.
"""
import json
import os
from generate_all_figures import main as regenerate_all_figures
from build_complete_isatu_manuscript import build_manuscript

def update_json_and_puml():
    json_path = 'Documentation/manuscript_data.json'
    with open(json_path, 'r', encoding='utf-8') as f:
        data = json.load(f)

    # -----------------------------------------------------------------
    # CHAPTER 1 UPDATES
    # -----------------------------------------------------------------
    c1 = data['chapter_1']
    c1['background_paras'][4] = (
        "The researchers developed the ISAT U - DC Coffee Shop POS System with Online Reservation and QR Code. "
        "The system provides dedicated interfaces for each separate user. Customers can visit the website to view "
        "seat layouts, reserve study pods with power outlets, schedule pre-ordered beverages and snacks that "
        "automatically dispatch to the kitchen 15 minutes before their arrival time (T-15m), and manage their "
        "booking or track live preparation status through a dynamic QR pass. At the front counter, the cashier "
        "uses the touch POS terminal to scan printed product QR code tags (via optical camera or 2D scanner) for "
        "instant product lookup, verify disposable cup, straw, and takeout box availability before ordering, check "
        "in guest reservation QR passes at the concierge desk, calculate statutory discounts, and print thermal "
        "sales receipts without trapping finished products as stocked units. At the beverage preparation area, "
        "the barista uses a digital Kitchen Display System (KDS) screen to view incoming tickets and T-15m "
        "scheduled pre-orders alongside each item's exact base recipe ingredients, selected add-ons, and required "
        "cups, straws, and takeout boxes—atomically deducting raw ingredients and packaging from the perpetual "
        "inventory ledger upon brewing and serving. The system also gives administrators a perpetual inventory "
        "suite with datalist-validated Stock In/Out vouchers, item conversions, recipe and add-on configuration, "
        "printable menu QR code tag sheets, and sales audit dashboards."
    )
    c1['background_paras'][5] = (
        "By using this system, the upcoming coffee shop inside the ISAT U - DC Mini Hotel will have an easy and "
        "organized way to serve customers, coordinate orders between the cashier and barista seamlessly, manage "
        "study pods, and track sales, recipes, packaging, and raw ingredient inventory without using loose paper slips."
    )

    c1['specific_objectives'] = [
        "design and set up a relational database with a perpetual inventory ledger to store and organize coffee shop records;",
        "create user accounts for the administrator, cashier, barista, and customers;",
        "develop an online portal for reserving tables and study pods with T-15m scheduled pre-ordering, post-booking order management, and digital QR passes;",
        "create a cashier POS screen to process made-to-order counter sales via optical camera and 2D QR code product scanning, cup and packaging stock validation, payments, and receipts quickly;",
        "set up a kitchen display screen (KDS) to help the barista view product recipes, allowed add-ons, and T-15m scheduled orders, and automatically deduct raw ingredients, cups, straws, and takeout boxes upon brewing and serving;",
        "manage master items with printable menu QR code tags, datalist-validated Stock In/Out and item conversions, and generate daily sales and inventory ledger reports; and",
        "evaluate the system using the ISO 25010 software quality standards."
    ]

    c1['conceptual_framework_paras'] = [
        "The conceptual framework of this study was developed using the Input-Process-Output (IPO) model, together with a system evaluation feedback loop. Figure 1 shows the conceptual framework of this study.",
        "As shown in Figure 1, the framework illustrates the systematic flow of information through the system for the upcoming coffee shop inside the ISAT U - DC Mini Hotel. The input stage encompasses the baseline data required to initialize and operate the coffee shop: user account credentials for four discrete actors (Administrator, Cashier, Barista, and Customer), master item catalog and recipe data incorporating printable two-dimensional (2D) QR code tags for finished products alongside raw material ingredients, allowed add-ons, and disposable packaging (12oz/16oz cups, straws, and takeout boxes), facility seating configurations for dining tables and quiet study pods inside the mini hotel, online reservation bookings with T-15m scheduled pre-orders, and POS sales transaction parameters (cash tender, GCash references, and statutory discount credentials).",
        "The process stage represents the core functional modules executed by the system: user authentication and role-based access validation, automatic generation of printable finished product QR tags and optical/2D QR scanning in the POS terminal, pre-order validation of disposable cup and takeout packaging stock while recording POS sales as made-to-order units, automated T-15m scheduled pre-order dispatch to the kitchen prior to guest arrival alongside staff concierge QR pass check-in, real-time Barista Kitchen Display System (KDS) recipe and add-on display with atomic Brew-to-Serve inventory deduction of raw ingredients, add-ons, cups, straws, and takeout boxes, and datalist-validated Stock In/Out vouchers, item conversions, and perpetual inventory ledger auditing.",
        "The output of the framework is the completed and operational ISAT U - DC Coffee Shop POS System with Online Reservation and QR Code. Directly beneath the output is the system evaluation stage, where the developed software is rigorously appraised across software engineering metrics using the ISO/IEC 25010 Software Quality Standards to ensure functional suitability, usability, performance efficiency, and operational reliability."
    ]

    c1['scope_paras'] = [
        "The study focuses on the design, development, and evaluation of the ISAT U - DC Coffee Shop POS System with Online Reservation and QR Code for the upcoming auxiliary coffee shop inside the Mini Hotel of ISAT U - Dumangas Campus.",
        "The system includes an online reservation portal where students, faculty, and hotel guests can browse dining tables and study pods, reserve time slots, attach customized beverage and snack pre-orders that automatically dispatch to the kitchen 15 minutes before arrival (T-15m), and receive an interactive digital QR pass with live KDS status tracking and post-booking order management.",
        "The front-counter cashier module features optical camera and 2D scanner QR code product reading, printable menu QR tag sheets, pre-order validation of disposable cups, straws, and takeout boxes, made-to-order sales recording (without trapping finished products as stocked units), statutory discount calculations (10% student/faculty discount and 20% senior/PWD discount), cash tender and GCash payment recording, 80mm thermal receipt printing, and staff concierge QR pass check-in with early-arrival kitchen dispatch.",
        "The kitchen operations module provides a dedicated real-time Kitchen Display System (KDS) for the barista, presenting active counter tickets and T-15m scheduled pre-orders alongside each item's exact base recipe ingredients, selected add-ons, and required cups/packaging, with atomic Brew-to-Serve inventory deduction upon order preparation and serving.",
        "The administrative module includes a perpetual inventory suite (Master Item List for raw materials and finished products starting at zero stock, datalist-validated Stock In/Out vouchers, Item Conversion, Recipe and Allowed Add-ons Manager, and printable Menu QR Code Tag Generator) and daily sales and accounting ledger reports.",
        "The system incorporates role-based access control with four distinct user roles: Administrator, Cashier, Barista, and Customer.",
        "Delimitations: The system uses two-dimensional (2D) Quick Response (QR) codes exclusively for finished product tagging, POS scanning, and customer check-in passes, completely omitting traditional one-dimensional (1D) linear barcodes. This deliberate architectural choice ensures higher data density, fast omnidirectional optical reading from both printed tags and mobile screens, and a unified hardware footprint."
    ]

    c1['terms'] = [
        [
            "Barista",
            "A specialized hospitality worker responsible for preparing espresso, brewed beverages, and specialty drinks.",
            "In this study, it refers to the dedicated staff member who operates the Kitchen Display System (KDS) at the brewing station to inspect product recipes and add-ons, prepare orders, and trigger atomic ingredient and packaging deduction upon brewing and serving."
        ],
        [
            "Brew-to-Serve Inventory Deduction",
            "A culinary inventory control mechanism in which raw materials and packaging are decremented upon kitchen fulfillment rather than point-of-sale order entry.",
            "In this study, it refers to the workflow where finished products are treated as non-stocked made-to-order units at the POS terminal, and raw ingredients, add-ons, cups, straws, and takeout boxes are atomically deducted from the perpetual inventory ledger when the barista marks an order as brewed and served."
        ],
        [
            "Cashier",
            "A retail or hospitality worker assigned to process monetary transactions and point-of-sale operations.",
            "In this study, it refers to the dedicated staff member who operates the front-counter POS terminal to scan finished product QR code tags, validate cup and packaging stock, receive payments, print receipts, and check in guest reservations at the concierge scanner."
        ],
        [
            "Dynamic QR Pass",
            "A two-dimensional scannable code containing encrypted or structured transaction data.",
            "In this study, it refers to the interactive digital pass issued to a customer after booking a study pod, allowing the guest to monitor their T-15m scheduled pre-order countdown, track live KDS preparation status, modify pre-ordered items before preparation begins, and check in at the concierge desk."
        ],
        [
            "Kitchen Display System (KDS)",
            "A digital order fulfillment interface that replaces paper kitchen chits with an electronic display screen.",
            "In this study, it refers to the screen mounted in the brewing station where the barista views active POS tickets and T-15m scheduled pre-orders, inspects base recipe ingredients and add-ons, and updates order preparation status."
        ],
        [
            "Perpetual Inventory Ledger and Datalist Validation",
            "An accounting inventory system that continuously records every stock movement (receipts, withdrawals, conversions, and kitchen deductions) with running balances.",
            "In this study, it refers to the inventory module where all items start at zero stock and movements are posted via searchable HTML5 datalist-validated Stock In/Out vouchers, item conversions, and barista Brew-to-Serve deductions."
        ],
        [
            "Point of Sale (POS) Terminal",
            "A computerized terminal used to record customer sales transactions, compute tax deductions, and issue customer receipts.",
            "In this study, it refers to the touchscreen web application used by the cashier at the counter to scan product QR tags via camera or 2D scanner, validate packaging availability, ring up made-to-order sales, and calculate change."
        ],
        [
            "Online Reservation Portal and T-15m Scheduled Pre-Order",
            "A web-based scheduling platform that lets users browse facility seating layouts, reserve slots remotely, and synchronize culinary preparation with arrival times.",
            "In this study, it refers to the website where students, faculty, and guests reserve study pods inside the mini hotel and attach pre-orders that automatically dispatch to the Barista KDS 15 minutes before the scheduled arrival time."
        ],
        [
            "QR Code Product Tagging and POS Scanning",
            "A two-dimensional optical data acquisition method where standardized Quick Response (QR) matrix symbols generated for finished menu products are printed as tags and scanned by an optical camera or 2D scanner to automatically retrieve product details into the POS cart.",
            "In this study, it refers to generating printable QR code tags when adding finished products or managing existing menus, and scanning those QR tags in the POS terminal for instant cart entry or customization."
        ],
        [
            "Study Pod",
            "A semi-private dedicated desk or booth designed for quiet individual study and computer work.",
            "In this study, it refers to the numbered seating booths inside the mini hotel coffee shop equipped with electrical outlets for laptop and phone charging."
        ]
    ]

    # -----------------------------------------------------------------
    # CHAPTER 3 UPDATES (Project Description & All Diagram Narratives)
    # -----------------------------------------------------------------
    c3 = data['chapter_3']
    c3['project_description'] = [
        "The ISAT U - DC Coffee Shop POS System with Online Reservation and QR Code is an integrated web-based management platform developed from the ground up to establish the complete digital operational infrastructure for the upcoming campus coffee shop and study hub situated inside the existing Mini Hotel at Iloilo Science and Technology University - Dumangas Campus (ISAT U - DC), located in Dumangas, Iloilo.",
        "The system is engineered to serve four distinct user roles within the campus mini hotel community:",
        "• Customer (Students, Faculty, and Mini Hotel Guests): Can access the responsive web portal from personal smartphones or laptops to inspect real-time seating availability across four zones inside the mini hotel (Lounge, Study Hub, Main Hall, and Terrace), book a study pod or dining table for an upcoming time slot, attach customized pre-ordered food and beverages that automatically dispatch to the kitchen 15 minutes before arrival (T-15m), and use an interactive digital QR pass to track live KDS preparation status or modify pre-ordered items before preparation begins.",
        "• Cashier: Operates the touch-enabled POS terminal at the service counter to process made-to-order walk-in sales, scan printed finished product QR code tags (using either an optical camera scanner, QR image upload, or a 2D USB scanner) for instantaneous cart entry or customization, verify disposable cup, straw, and takeout box stock before ordering, apply institutional student/faculty discounts (10%) and senior/PWD discounts (20%), compute cash tender and change, print 80mm thermal sales receipts, and operate the Staff Concierge Scanner to check in guest QR passes or fire early-arrival pre-orders to the kitchen.",
        "• Barista: Operates the dedicated dark-mode Kitchen Display System (KDS) screen at the brewing station to monitor active counter tickets and T-15m scheduled pre-orders in real time, inspect each item's exact base recipe ingredients (Bill of Materials), selected add-ons, and required cups/packaging, hear audio chimes when new orders arrive, and click Brew & Serve to transition tickets to Ready while atomically deducting raw ingredients, add-ons, cups, straws, and takeout boxes from the perpetual inventory ledger.",
        "• Administrator (Campus Management and Supervisors): Manages user accounts and credentials, maintains the Master Item List (Raw Materials and Finished Products starting at 0 stock), generates and prints 2D POS QR Code Tags for new and existing menus, configures product recipes (BOM) and allowed add-ons, posts datalist-validated Stock In/Out vouchers and item conversions, audits the perpetual inventory ledger, and generates analytical sales reports.",
        "The system is built using Native PHP 8.x and MySQL (PDO) for reliable transactional and ledger storage, with HTML5, Bootstrap 5.3, JavaScript, QRCode.js, and Html5Qrcode for an intuitive, responsive, and Hostinger-ready web interface.",
        "The system comprises six unified functional modules: (1) Customer Online Reservation, T-15m Scheduled Pre-Order, and Interactive QR Pass Portal; (2) Touch Cashier POS Terminal with Optical/2D Product QR Tag Scanning and Packaging Validation; (3) Staff Concierge QR Scanner and Early-Arrival Dispatch Module; (4) Barista Kitchen Display System (KDS) with Recipe/Add-on Viewer and Brew-to-Serve Inventory Deduction; (5) Perpetual Inventory Suite with Datalist Stock In/Out, Item Conversion, Recipe/Add-ons Manager, and Printable Menu QR Tag Generator; and (6) Administrative Sales Reporting and General Journal Ledger Audit Dashboard."
    ]

    c3['uml']['architecture_paras'] = [
        "Figure 3 shows the System Architecture of the application, structured across three operational tiers to connect the separate users smoothly:",
        "• Client Tier: Customers access the online reservation and interactive QR pass portal on smartphones or personal laptops. At the sales counter, the Cashier operates a desktop PC or touchscreen POS terminal paired with an optical camera / 2D USB QR scanner and an 80mm thermal receipt printer. In the brewing area, the Barista uses a dedicated kitchen tablet or wall monitor displaying the real-time Kitchen Display System (KDS) with recipe and add-on breakdowns.",
        "• Application Tier: An Apache web server running Native PHP 8.x handles core business logic, including role-based authentication (Administrator, Cashier, Barista, Customer), reservation time-slot scheduling and T-15m automated pre-order dispatching, dynamic QR pass generation and concierge check-in verification, printable finished product QR tag generation and POS optical/2D QR auto-detection, cup/packaging stock validation, statutory discount computation, and asynchronous KDS Brew-to-Serve ingredient and packaging deduction.",
        "• Data Tier: A relational MySQL database safely stores all application data across normalized tables covering users, menu categories, products (with QR product codes), master inventory items, product recipes (BOM), allowed product add-ons, stock transactions, item conversions, the perpetual inventory ledger, dining tables, reservations (with T-15m scheduled pre-order tracking), POS orders, order items, and payments."
    ]

    c3['uml']['use_case_paras'] = [
        "Figure 4 shows the Use Case Diagram of the ISAT U - DC Coffee Shop POS System with Online Reservation and QR Code. The diagram clearly separates front-of-house customer and cashier transactions from back-of-house kitchen and administrative management, showing four distinct actors: Customer, Cashier, Barista, and Administrator.",
        "The Customer (Student, Faculty, or Hotel Guest) interacts with the online reservation portal to browse available study pods and dining tables, submit booking reservations with T-15m scheduled pre-orders, manage or update pre-ordered items and track live KDS preparation status on their dynamic QR pass, and present their QR pass for concierge check-in. At the front counter, the Cashier logs into the POS terminal, scans printed finished product QR tags (via camera or 2D scanner) to auto-read items into the sales cart, validates cup and takeout packaging stock, applies student/faculty (10%) or senior/PWD (20%) discounts, processes cash or GCash payments to issue 80mm thermal receipts, and scans guest QR passes to verify bookings or fire early-arrival pre-orders to the kitchen.",
        "In the kitchen area, the Barista operates the dedicated Kitchen Display System (KDS), monitoring live POS tickets and T-15m scheduled pre-orders, inspecting full product recipes (BOM), allowed add-ons, and cup/packaging specifications, clicking Brew & Serve to atomically deduct raw ingredients and packaging from inventory, and notifying customers for pickup. In the back office, the Administrator manages master items, generates and prints POS QR code tags for new and existing menus, configures product recipes and allowed add-ons, posts datalist-validated Stock In/Out vouchers and item conversions, and audits the perpetual inventory ledger and sales reports."
    ]

    c3['uml']['activity_paras'] = [
        "Figure 5 shows the Swimlane Activity Diagram of the coffee shop system, partitioned per user across four operational swimlanes (Customer, Cashier, System Engine, and Barista):",
        "1. Customer Lane: The customer accesses the online reservation portal to inspect available study pods and dining tables, reserves a time slot with a T-15m scheduled pre-order, and receives a dynamic scannable QR pass with live KDS status tracking. The customer presents the QR pass upon arriving at the counter (or places a walk-in order directly).",
        "2. Cashier Lane: The cashier logs into the touch POS terminal and scans the guest's QR pass at the concierge scanner or scans printed finished product QR code tags using the optical camera or 2D scanner. The cashier selects sizes and allowed add-ons, verifies disposable cup, straw, and takeout box availability, applies statutory discounts (10% student/faculty or 20% senior/PWD), collects cash tender or verifies GCash QR references, and issues the printed 80mm thermal receipt.",
        "3. System Engine Lane: The system validates the scanned product QR tag or reservation QR token, checks cup and packaging stock, computes 12% VAT and discount deductions, records the made-to-order sale in MySQL (without trapping finished product stock at the POS), and routes the order ticket (or auto-dispatches T-15m scheduled pre-orders 15 minutes before arrival) electronically to the Barista KDS queue.",
        "4. Barista Lane: The barista receives the order ticket in real time on the Kitchen Display System (KDS) accompanied by an acoustic chime alert and full recipe (BOM), add-on, and packaging details. Once the barista prepares the order and clicks Brew & Serve (advancing the status to Ready), the system atomically deducts the base recipe ingredients, selected add-on ingredients, disposable cups, straws, and takeout boxes from the perpetual inventory ledger, and notifies the customer to claim their freshly prepared order."
    ]

    c3['uml']['class_diag_paras'] = [
        "Figure 6 shows the Class Diagram and relational database schema of the system, organized across three interconnected subsystems in MySQL:",
        "1. Menu, Recipes, and Perpetual Inventory Subsystem (categories, products, inventory_items, product_recipes, product_addons): Stores menu categories, POS products tagged with unique 2D QR codes (product_code), master inventory items (raw ingredients, packaging, and finished products starting at 0 stock), bill-of-materials base recipes (product_recipes), and product-specific allowed add-ons linked directly to raw material ingredients.",
        "2. Space, T-15m Reservations, and Inventory Ledger Subsystem (dining_tables, reservations, reservation_items, stock_transactions, item_conversions, inventory_ledger): Stores study pods and dining tables across four zones, customer bookings with unique QR tokens (qr_token), T-15m scheduled pre-order links (scheduled_order_id and pre_order_prep_status), pre-ordered items, datalist-validated Stock In/Out vouchers and unit conversions, and the immutable perpetual inventory ledger tracking running stock balances.",
        "3. Users, POS Orders, and KDS Fulfillment Subsystem (users, orders, order_items, payments): Stores role-based user accounts (admin, cashier, barista, customer), made-to-order POS sales headers (with inventory_deducted flag synchronized with Barista KDS Brew-to-Serve fulfillment), customized order line items, and cash/GCash payment records."
    ]

    c3['uml']['sequence_paras'] = [
        "Figure 7 shows the Sequence Diagram illustrating the chronological, multi-user interactions between the Customer, Cashier, Cashier POS Terminal, PHP Application Server, MySQL Database, Barista KDS Screen, and Barista:",
        "1. The Customer presents a reservation QR pass (with T-15m scheduled pre-order) or places a walk-in order at the service counter.",
        "2. The Cashier scans the printed product QR tag (via camera or 2D scanner) or the guest's QR pass into the POS terminal.",
        "3. The Cashier POS Terminal sends an asynchronous lookup request (GET /api/pos.php?action=qr) to the PHP Application Server.",
        "4. The PHP Application Server queries the MySQL Database for the matching product, its recipe and allowed add-ons, and current disposable cup/packaging stock.",
        "5. The MySQL Database returns the product details, allowed add-ons, and packaging availability to the server.",
        "6. The PHP Server returns the structured JSON payload to the Cashier POS Terminal.",
        "7. The Cashier POS Terminal validates cup, straw, and takeout box stock, adds the item to the order tray, and computes 12% VAT and statutory discounts.",
        "8. The Cashier enters the payment tender (Cash or GCash reference) and clicks Complete Sale.",
        "9. The Cashier POS Terminal transmits the made-to-order sale transaction (POST /api/pos.php) to the PHP Server.",
        "10. The PHP Server records the order and payment in MySQL without deducting finished product stock at the POS.",
        "11. The PHP Server dispatches the live order ticket (alongside any T-15m scheduled pre-orders) with full recipe and add-on breakdowns to the Barista KDS Screen.",
        "12. The Cashier POS Terminal prints an 80mm thermal receipt, which the Cashier hands to the Customer.",
        "13. The Barista KDS Screen sounds an alert chime and displays the ticket's base recipe ingredients, selected add-ons, and required cups/packaging.",
        "14. The Barista brews the beverage, prepares the order, and clicks Brew & Serve (POST /api/kitchen.php).",
        "15. The PHP Server atomically deducts the base recipe ingredients, selected add-on ingredients, disposable cups, straws, and takeout boxes from MySQL (inventory_items and inventory_ledger).",
        "16. The Barista calls the Customer and serves the freshly prepared order."
    ]

    with open(json_path, 'w', encoding='utf-8') as f:
        json.dump(data, f, indent=4, ensure_ascii=False)
    print("Updated Documentation/manuscript_data.json successfully.")

if __name__ == '__main__':
    update_json_and_puml()
    regenerate_all_figures()
    build_manuscript()

