# -*- coding: utf-8 -*-
"""
Script to generate all 7 PlantUML diagram images cleanly for the capstone manuscript.
"""

import os
import plantuml

server = plantuml.PlantUML(url='http://www.plantuml.com/plantuml/img/')
os.makedirs('Documentation/diagrams', exist_ok=True)

diagrams = {
    "figure1_ipo.puml": """
@startuml
skinparam monochrome true
skinparam defaultFontName "Times New Roman"
skinparam defaultFontSize 11

package "INPUT" as InPkg {
  [User Accounts and Roles]
  [Product 2D QR Code Data]
  [Dining Table and Pod Setup]
  [Reservation Details and Pre-orders]
  [Walk-in Counter Orders and Modifiers]
  [Cash and GCash Payment Details]
}

package "PROCESS" as ProcPkg {
  [User Authentication]
  [Optical QR Code Auto-Read]
  [Cart and Discount Computation]
  [Generating Dynamic QR Pass]
  [QR Pass Check-In Verification]
  [Updating Table Occupancy]
  [Live Order Routing to KDS]
  [Inventory Stock Deduction]
  [Sales KPI and Report Generation]
}

package "OUTPUT" as OutPkg {
  [ISAT U Coffee Shop POS System]
  [Online Reservation Portal]
  [Dynamic Customer QR Passes]
  [Touch Cashier POS Terminal]
  [Barista Kitchen Screen (KDS)]
  [80mm Thermal Receipts]
  [Admin Sales Dashboard]
}

package "SYSTEM EVALUATION (ISO 25010)" as EvalPkg {
  [Functional Suitability and Usability]
  [Performance, Reliability, Security]
  [Maintainability and Portability]
}

InPkg -right-> ProcPkg
ProcPkg -right-> OutPkg
OutPkg -down-> EvalPkg
EvalPkg -left-> ProcPkg : Feedback Loop
EvalPkg -left-> InPkg : Adjustments
@enduml
""",

    "figure2_prototyping.puml": """
@startuml
skinparam monochrome true
skinparam defaultFontName "Times New Roman"
skinparam defaultFontSize 11

start
:Phase 1: Requirements Gathering
(Interview coffee shop staff, observe counter and study pods);

:Phase 2: Quick Design
(Design database tables, screen layouts, QR code flow);

:Phase 3: Prototype Development
(Build initial web app using Native PHP, MySQL, and Bootstrap 5.3);

repeat
  :Phase 4: User Evaluation
  (Demonstrate prototype to cashiers, baristas, and students);
  :Phase 5: Refinement
  (Improve QR code reading speed, fix button layout, add chimes);
repeat while (More user adjustments needed?) is (yes)
->no;

:Phase 6: Testing
(Test calculations, QR scanner, receipt printing, and browser display);

:Phase 7: Deployment and Orientation
(Install on campus computer, train cashiers and baristas);
stop
@enduml
""",

    "figure3_use_case.puml": """
@startuml
skinparam monochrome true
skinparam defaultFontName "Times New Roman"
skinparam defaultFontSize 11
left to right direction

actor Customer as "Customer (Student/Faculty)"
actor Cashier as "Cashier"
actor Barista as "Barista"
actor Admin as "Administrator"

rectangle "ISAT U Coffee Shop POS and Reservation System" {
  usecase "View Floor Map and Pod Availability" as UC1
  usecase "Book Table or Study Pod" as UC2
  usecase "Pre-Order Drinks and Modifiers" as UC3
  usecase "Generate Dynamic QR Pass" as UC4
  
  usecase "Log In to POS Terminal" as UC5
  usecase "Scan Product 2D QR Code" as UC6
  usecase "Customize Drink Size and Add-ons" as UC7
  usecase "Apply Student / Faculty Discount" as UC8
  usecase "Process Payment and Print Receipt" as UC9
  usecase "Scan and Check-In Customer QR Pass" as UC10
  
  usecase "View Real-Time Kitchen Queue" as UC11
  usecase "Update Order Status (Brewing/Ready)" as UC12
  
  usecase "View Sales KPIs and Daily Reports" as UC13
  usecase "Manage Product Prices and Stock" as UC14
  usecase "Manage Table and Reservation Logs" as UC15
}

Customer --> UC1
Customer --> UC2
Customer --> UC3
Customer --> UC4

Cashier --> UC5
Cashier --> UC6
Cashier --> UC7
Cashier --> UC8
Cashier --> UC9
Cashier --> UC10

Barista --> UC11
Barista --> UC12

Admin --> UC13
Admin --> UC14
Admin --> UC15
@enduml
""",

    "figure4_activity.puml": """
@startuml
skinparam monochrome true
skinparam defaultFontName "Times New Roman"
skinparam defaultFontSize 11

start
:Cashier logs in and opens POS terminal;
:Select order type (Dine-In, Takeout, or Study Pod);

if (How to add item?) then (Scan Product QR Code)
  :Cashier scans product QR code with optical scanner;
  :System reads product code automatically;
  :System finds item in database;
  :Item is added to cart tray instantly;
else (Manual Touch)
  :Cashier clicks category and item button;
  :Item is added to cart tray;
endif

if (Is drink customizable?) then (yes)
  :Select cup size (Regular, Large, Jumbo);
  :Select sweetness level and ice level;
  :Select extra add-ons (extra shot, pearls);
else (no)
endif

:Select customer discount (None, Student/Faculty 10%, PWD 20%);
:System calculates subtotal, discount, 12% VAT, and total;

if (Payment Method?) then (Cash)
  :Cashier enters cash tender;
  :System calculates change;
else (GCash)
  :Cashier verifies reference number;
endif

:Click Complete Order;
:System saves order to MySQL;
:System reduces stock quantity;
:Order sent to Barista Kitchen Screen (KDS);
:POS prints 80mm thermal receipt;
stop
@enduml
""",

    "figure5_class_erd.puml": """
@startuml
skinparam monochrome true
skinparam defaultFontName "Times New Roman"
skinparam defaultFontSize 10
hide circle

class users {
  + id : int [PK]
  --
  name : varchar
  email : varchar
  password : varchar
  role : enum
  affiliation : enum
  id_number : varchar
  phone : varchar
}

class categories {
  + id : int [PK]
  --
  name : varchar
  slug : varchar
}

class products {
  + id : int [PK]
  --
  category_id : int [FK]
  product_code : varchar [Unique]
  name : varchar
  price : decimal
  cost_price : decimal
  stock_quantity : int
  min_stock_alert : int
}

class product_sizes {
  + id : int [PK]
  --
  product_id : int [FK]
  size_name : varchar
  price_additional : decimal
}

class product_addons {
  + id : int [PK]
  --
  product_id : int [FK]
  addon_name : varchar
  price : decimal
}

class dining_tables {
  + id : int [PK]
  --
  table_code : varchar
  name : varchar
  zone : varchar
  capacity : int
  status : enum
}

class reservations {
  + id : int [PK]
  --
  reservation_number : varchar
  qr_token : varchar
  table_id : int [FK]
  customer_name : varchar
  reservation_date : date
  time_slot : varchar
  guest_count : int
  status : varchar
}

class reservation_items {
  + id : int [PK]
  --
  reservation_id : int [FK]
  product_id : int [FK]
  size_name : varchar
  quantity : int
  subtotal : decimal
}

class orders {
  + id : int [PK]
  --
  order_number : varchar
  reservation_id : int [FK]
  cashier_id : int [FK]
  order_type : varchar
  subtotal : decimal
  discount_amount : decimal
  vat_amount : decimal
  net_total : decimal
  payment_method : varchar
  status : varchar
}

class order_items {
  + id : int [PK]
  --
  order_id : int [FK]
  product_id : int [FK]
  size_name : varchar
  quantity : int
  unit_price : decimal
  subtotal : decimal
}

class payments {
  + id : int [PK]
  --
  order_id : int [FK]
  amount_paid : decimal
  change_amount : decimal
  payment_method : varchar
}

categories "1" -- "*" products
products "1" -- "*" product_sizes
products "1" -- "*" product_addons
dining_tables "1" -- "*" reservations
reservations "1" -- "*" reservation_items
products "1" -- "*" reservation_items
users "1" -- "*" orders : cashier
reservations "0..1" -- "*" orders
orders "1" -- "*" order_items
products "1" -- "*" order_items
orders "1" -- "1" payments
@enduml
""",

    "figure6_sequence.puml": """
@startuml
skinparam monochrome true
skinparam defaultFontName "Times New Roman"
skinparam defaultFontSize 11

actor Cashier
boundary "POS Web Screen" as POS
control "Laravel POSController" as Controller
database "MySQL Database" as DB
boundary "Kitchen Screen (KDS)" as KDS

Cashier -> POS : Scans product 2D QR code
POS -> Controller : Send scanned code (e.g. 'PROD-001')
Controller -> DB : Find product by product_code
DB --> Controller : Return name, price, stock
Controller --> POS : Return item info (JSON)
POS -> POS : Auto-add item to cart tray and update total

Cashier -> POS : Selects payment (Cash) and clicks Complete Order
POS -> Controller : Submit order payload
Controller -> DB : Save order and order items
Controller -> DB : Deduct stock from products table
DB --> Controller : Confirm saved
Controller -> KDS : Push order to kitchen queue
Controller --> POS : Order complete confirmation
POS -> POS : Show and print 80mm thermal receipt
@enduml
""",

    "figure7_architecture.puml": """
@startuml
skinparam monochrome true
skinparam defaultFontName "Times New Roman"
skinparam defaultFontSize 11

rectangle "Client Devices" {
  [Customer Smartphones / Laptops (Online Reservation & QR Pass)] as CustomerDev
  [Cashier Counter Desktop PC (POS Screen + Scanner + Printer)] as CashierDev
  [Kitchen Screen / Tablet (Barista KDS Screen)] as KitchenDev
}

cloud "Campus Network (LAN / Wi-Fi)" as Net {
  [Local Area Network HTTP Port 80] as LAN
}

rectangle "Host Web Server Machine" {
  [Apache Web Server 2.4 and PHP 8.2 Engine] as WebServer
  [Laravel Application Framework Core] as AppCore
  database "MySQL 8.0 Community Server (db_isatu_pos)" as DB
}

CustomerDev --> LAN
CashierDev --> LAN
KitchenDev --> LAN

LAN --> WebServer
WebServer --> AppCore
AppCore --> DB
@enduml
"""
}

for filename, puml_code in diagrams.items():
    filepath = os.path.join('Documentation/diagrams', filename)
    with open(filepath, 'w', encoding='utf-8') as f:
        f.write(puml_code.strip())
    
    png_path = filepath.replace('.puml', '.png')
    try:
        png_bytes = server.processes(puml_code.strip())
        with open(png_path, 'wb') as f:
            f.write(png_bytes)
        print(f"Generated: {png_path} ({len(png_bytes)} bytes)")
    except Exception as e:
        print(f"Error generating {png_path}: {e}")

print("PlantUML batch finished.")
