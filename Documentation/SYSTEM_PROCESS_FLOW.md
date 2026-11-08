# System Process Flow & Operational Architecture
## Raeden’s Resto, Bakery, Cafe: Point of Sale with Online Table Reservation, QR-Code Seating Pass, Kitchen Display System (KDS), and Recipe BOM Inventory Management

---

## 1. Executive Overview & System Operational Framework

The **Raeden’s Resto, Bakery, Cafe Management System** is a unified, multi-role web-based platform engineered to automate and synchronize the operational touchpoints of a commercial food service, artisan bakery, and specialty coffee establishment. The architecture addresses the acute inefficiencies of traditional manual workflows—specifically paper chit loss, seating misallocation, manual statutory discount computation, and speculative visual inventory tracking.

The system synchronizes five core operational roles:
1. **Customer (Public/Diner):** Self-service visual table selection, modalized menu browsing, pre-ordering, and cryptographic dynamic QR boarding pass generation.
2. **Staff (Front Desk & Floor Attendant):** Optical QR code verification, rapid customer seating, manual reservation overrides, and automated enforcement of the **15-Minute Seating Grace Period Policy**.
3. **Cashier (Point of Sale Station):** One-tap QR pre-order fetching, physical product barcode/QR scanning, item modifier customizations, automated 20% statutory Senior/PWD discounting, payment tender recording, and thermal receipt issuance.
4. **Kitchen Staff & Barista (Kitchen Display System):** Paperless real-time preparation queue, acoustic arrival alerts, recipe Bill of Materials (BOM) guidance, and order lifecycle state transitions (`Received` $\to$ `Brewing` $\to$ `Ready` $\to$ `Served`).
5. **Admin & Store Manager (Back-Office Hub):** Perpetual stock tracking, stock-in receiving, recipe BOM formulation, **Finished Product Yield Calculation with Bottleneck Ingredient Analysis**, sales auditing, and system user management.

---

## 2. High-Level End-to-End System Flowchart

The following high-level diagram visualizes the holistic operational progression, from a customer’s online table booking and pre-ordering to floor check-in, cashier payment, kitchen fulfillment, and automated inventory deduction.

```mermaid
flowchart TD
    subgraph S1["1. Customer Online Reservation Portal"]
        A1["Access Booking Portal"] --> A2["Select Date, Time Slot & Guest Count"]
        A2 --> A3["Query Dining Table Availability Matrix"]
        A3 --> A4{"Table Available?"}
        A4 -- "No" --> A2
        A4 -- "Yes" --> A5["Select Table Node (Study Hub / Lounge / Main Hall / Terrace)"]
        A5 --> A6["Browse Menu & Specials Modal"]
        A6 --> A7["Configure Customizations (Size, Sweetness, Ice, Add-ons)"]
        A7 --> A8["Agree to 15-Minute Seating Reservation Policy"]
        A8 --> A9["Submit Reservation & Generate Encrypted QR Pass"]
    end

    subgraph S2["2. Staff Seating Check-In Desk"]
        B1["Customer Arrives at Establishment"] --> B2["Present Dynamic QR Pass to Staff"]
        B2 --> B3["Staff Scans QR Code / Enters Pass Token"]
        B3 --> B4{"Arrival <= 15 Min Grace Period?"}
        B4 -- "No (Overdue)" --> B5["System Forfeits Table -> Sets Status to No-Show"]
        B5 --> B6["Table Released to Available for Walk-Ins"]
        B4 -- "Yes (Valid)" --> B7["Mark Reservation as Checked-In"]
        B7 --> B8["Update Dining Table Status to Occupied"]
        B8 --> B9["Escort Customer to Designated Table"]
    end

    subgraph S3["3. Cashier Point of Sale (POS) Station"]
        C1["Customer Orders at POS Counter (Walk-In or Seated)"]
        C2{"Is Seated with QR Pass?"}
        C1 --> C2
        C2 -- "Yes" --> C3["Scan QR Code -> Auto-Load Pre-Order Cart into POS"]
        C2 -- "No" --> C4["Manual Touch Menu Selection or Scan Product Barcode/QR"]
        C3 --> C5["Review Cart & Modify Items if Requested"]
        C4 --> C5
        C5 --> C6{"Apply Statutory Discount?"}
        C6 -- "Senior / PWD (20%)" --> C7["Validate ID & Deduct 20% on Qualified Items"]
        C6 -- "None / Standard" --> C8["Compute Subtotal + 12% VAT Breakdown"]
        C7 --> C8
        C8 --> C9["Process Payment (Cash with Auto-Change or GCash)"]
        C9 --> C10["Generate Sales Record & Print 80mm Thermal Receipt"]
    end

    subgraph S4["4. Barista & Kitchen Display System (KDS)"]
        D1["Order Dispatched to KDS Queue"] --> D2["Chime Sounds -> Order Card in Received Status"]
        D2 --> D3["Barista Taps Card -> Transition to Brewing / Preparing"]
        D3 --> D4["Inspect Recipe Bill of Materials (BOM) Specs"]
        D4 --> D5["Complete Preparation -> Transition to Ready"]
        D5 --> D6["Customer Collects Order / Food Runner Serves"]
        D6 --> D7["Transition to Served / Completed"]
    end

    subgraph S5["5. Inventory Control & Yield Calculation Engine"]
        E1["Order Completed"] --> E2["Trigger Automatic Stock Depletion"]
        E2 --> E3["Deduct Raw Materials via Recipe BOM Quantities"]
        E3 --> E4["Update Perpetual Inventory Balances"]
        E4 --> E5["Re-evaluate Finished Product Yield & Identify Bottlenecks"]
        E5 --> E6{"Stock Below Safety Threshold?"}
        E6 -- "Yes" --> E7["Display Critical Stock-Out Warning on POS & Admin"]
        E6 -- "No" --> E8["Inventory in Normal State"]
    end

    A9 -.-> B2
    B7 -.-> C3
    C10 ==> D1
    D7 ==> E1
```

---

## 3. Detailed Modular Process Flows

### 3.1 Module 1: Customer Online Reservation & Pre-Ordering Process Flow

The customer reservation portal enables diners to secure seating and optionally pre-order artisan dishes and specialty beverages prior to arrival.

#### Operational Steps:
1. **Time Slot & Party Verification:** The customer selects a target booking date, time slot (e.g., `10:00 AM - 12:00 PM`, `01:00 PM - 03:00 PM`), and party size.
2. **Dynamic Seating Conflict Trapping:** The system performs an asynchronous `GET` query to `/api/reservations.php?action=check_availability` passing the chosen date and slot. Tables currently flagged as `maintenance`, `occupied`, or reserved by an existing confirmed reservation during the exact time slot are visually disabled and trapped from selection.
3. **Table Node Allocation:** The diner selects an available table across four themed dining zones:
   - **Study Hub:** Quiet study pods with individual electrical outlets (`POD-01`, `POD-02`, `POD-03`).
   - **Lounge:** Plush sofa arrangements for casual gatherings (`LNG-01`, `LNG-02`).
   - **Main Hall:** Central family and group dining tables (`HAL-01`, `HAL-02`).
   - **Outdoor Terrace:** Open-air patio seating (`TER-01`, `TER-02`).
4. **Modalized Menu & Customization Browser:** The user opens the **Menu & Specials Modal** (`#menuSpecialsModal`). A sticky mobile navigation toolbar allows live filtering by category and keyword search. Tapping **"Customize & Add"** opens the item modifier modal:
   - **Cup Size Selection:** Regular (12oz), Large (16oz), Jumbo (22oz) with incremental price recalculation.
   - **Sweetness Tuning:** 0%, 25%, 50%, 75%, 100%.
   - **Ice Levels:** Hot, Less Ice, Regular Ice, Extra Ice.
   - **Add-on Modifiers:** Extra Espresso Shot, Oat Milk Substitution, Flavored Syrups, Coffee Jelly.
5. **Seating Policy Acknowledgment:** Before submission, the diner must check the required checkbox consenting to the **15-Minute Seating Reservation Policy**.
6. **Token Cryptography & QR Generation:** The server validates input data, issues a unique tracking code (`RES-YYYYMMDD-XXX`) and high-entropy alphanumeric QR token (`ISATU-RES-XXXXXXXXXXXX-XXXX`), saves the booking into `reservations` and `reservation_items`, and returns the dynamic scannable QR Pass.

```mermaid
sequenceDiagram
    autonumber
    actor Customer
    participant UI as Customer Portal (Browser)
    participant API as Reservations Controller (api/reservations.php)
    participant DB as MySQL Database
    participant QR as Client QRCode.js Engine

    Customer->>UI: Selects Date, Time Slot & Party Size
    UI->>API: GET check_availability(date, time_slot)
    API->>DB: Query dining_tables & active reservations
    DB-->>API: Return table matrix & conflict records
    API-->>UI: Return JSON availability status per table node
    UI-->>Customer: Renders Interactive Dining Floorplan (Green=Available, Gray=Booked)
    
    Customer->>UI: Clicks Available Table Node (e.g., Table HAL-02)
    Customer->>UI: Opens "Menu & Specials" Modal
    Customer->>UI: Configures Drink (Size: 16oz, Sugar: 50%, Oat Milk)
    UI->>UI: Appends Customized Line Item to Pre-Order Cart
    
    Customer->>UI: Fills Name, Phone & Checks 15-Min Grace Period Policy
    Customer->>UI: Clicks "Confirm Reservation & Generate QR Pass"
    UI->>API: POST /api/reservations.php (Payload: Table, Customer, Items)
    
    API->>DB: Begin Database Transaction
    API->>DB: Re-verify Table Slot Availability (Prevent Race Condition)
    API->>DB: INSERT INTO reservations (reservation_number, qr_token, status='Confirmed')
    API->>DB: INSERT INTO reservation_items (Product, Size, Customizations, Price)
    API->>DB: Commit Transaction
    DB-->>API: Success Response + Generated Tokens
    API-->>UI: Return JSON (Success, qr_token, reservation_number)
    
    UI->>QR: Generate High-Correction 2D Matrix (Level H)
    QR-->>UI: Render Dynamic QR Boarding Pass
    UI-->>Customer: Displays Scannable Boarding Pass Modal & Download Link
```

---

### 3.2 Module 2: Staff Seating Check-In & Table Management Flow

The Staff Station governs physical customer arrivals, seating enforcement, and the execution of the 15-minute table release rule.

```mermaid
flowchart TD
    Start(["Customer Arrives at Host Desk"]) --> Scan["Staff Scans Customer QR Boarding Pass"]
    Scan --> Lookup["API Queries Database by qr_token"]
    Lookup --> Found{"Booking Found?"}
    
    Found -- "No" --> Err["Display Alert: Invalid or Unknown QR Pass"]
    Found -- "Yes" --> DateCheck{"Is Booking Date = Today?"}
    
    DateCheck -- "No" --> DateErr["Display Notice: Booking Scheduled for Another Date"]
    DateCheck -- "Yes" --> StatusCheck{"Check Current Status"}
    
    StatusCheck -- "Checked-In" --> WarnAlready["Notice: Guest is Already Checked-In"]
    StatusCheck -- "Cancelled / No-Show" --> WarnForfeit["Alert: Booking Has Been Forfeited / Cancelled"]
    StatusCheck -- "Confirmed" --> GraceCheck{"Arrival Time <= Slot Start Time + 15 Minutes?"}
    
    GraceCheck -- "Exceeded Grace Period" --> AutoForfeit["System Executes Grace Period Forfeiture"]
    AutoForfeit --> SetNoShow["UPDATE reservations SET status = 'No-Show'"]
    SetNoShow --> ReleaseTable["UPDATE dining_tables SET status = 'available'"]
    ReleaseTable --> AlertStaff["Display Alert: Table Released for Walk-In Diners"]
    
    GraceCheck -- "Within Grace Period" --> ValidCheckin["Confirm Guest Check-In"]
    ValidCheckin --> SetCheckedIn["UPDATE reservations SET status = 'Checked-In', checked_in_at = NOW()"]
    SetCheckedIn --> OccupyTable["UPDATE dining_tables SET status = 'occupied'"]
    OccupyTable --> LoadDisplay["Display Seating Summary & Pre-Order Details"]
    LoadDisplay --> Escort["Staff Escorts Guest to Assigned Table"]
    Escort --> End(["End Seating Process"])
```

#### Input-Process-Output (IPO) Analysis for Seating Desk:
| Stage | Description / Elements |
| :--- | :--- |
| **Input** | Optical 2D QR Code video stream from camera scanner, or manual pass token entry (`ISATU-RES-XXXXXXXXXXXX-XXXX`). |
| **Process** | Query reservation record; compare system timestamp (`Asia/Manila` +08:00) against scheduled `time_slot`; evaluate 15-minute threshold; update database state atomically. |
| **Output** | Green check-in modal with guest name, table badge, and pre-ordered dishes; table status updated to `occupied`; or automatic table release alert if grace period expired. |

---

### 3.3 Module 3: Cashier Point of Sale (POS) & Billing Process Flow

The Cashier POS Station handles walk-in orders, table billing, optical QR pre-order fetching, statutory discount deductions, multi-tender transactions, and receipt printing.

```mermaid
flowchart TD
    A(["Order Intake at POS Counter"]) --> B{"Order Method?"}
    
    B -- "Pre-Ordered via QR Pass" --> C1["Cashier Scans Customer QR Pass"]
    C1 --> C2["API Fetches Pending Pre-Order Items"]
    C2 --> C3["Cart Auto-Populated with Reserved Table & Customized Items"]
    
    B -- "Walk-In / Counter Order" --> D1["Cashier Selects Order Type (Dine-In Table / Takeout)"]
    D1 --> D2["Touchscreen Menu Catalog or Optical Product Barcode Scan"]
    D2 --> D3["Configure Modifiers (Size, Ice, Sugar, Add-ons)"]
    D3 --> D4["Add Items to Active POS Register Cart"]
    
    C3 --> E["Review Cart Items & Quantities"]
    D4 --> E
    
    E --> F{"Customer Requests Statutory Discount?"}
    F -- "Yes (Senior Citizen / PWD)" --> G1["Select Discount Type: Senior / PWD (20%)"]
    G1 --> G2["Enter Government ID / PWD Booklet Number"]
    G2 --> G3["Apply Formula: Net Due = Subtotal - (Eligible Subtotal * 0.20)"]
    
    F -- "No / Standard" --> G4["Discount = 0.00; Net Due = Subtotal"]
    
    G3 --> H["Calculate Statutory 12% VAT Breakdown"]
    G4 --> H
    
    H --> I["Proceed to Tender / Payment Modal"]
    I --> J{"Select Payment Method"}
    
    J -- "Cash Payment" --> K1["Enter Cash Tendered Amount"]
    K1 --> K2["Compute Change = Cash Tendered - Net Due"]
    K2 --> K3{"Cash Tendered >= Net Due?"}
    K3 -- "No" --> K1
    K3 -- "Yes" --> L["Record Transaction in Database"]
    
    J -- "GCash Payment" --> M1["Customer Scans Store QR Ph / Sends Payment"]
    M1 --> M2["Cashier Enters 13-Digit GCash Reference Number"]
    M2 --> L
    
    L --> N["INSERT INTO orders (order_number, cashier_id, net_total, payment_method)"]
    N --> O["INSERT INTO order_items (Recipe Line Breakdown)"]
    O --> P["INSERT INTO payments (amount_tendered, amount_change, reference)"]
    P --> Q["Broadcast Order Event to Kitchen Display System (KDS)"]
    Q --> R["Trigger Thermal Printer (80mm ESC/POS Receipt Output)"]
    R --> S(["Transaction Finished"])
```

#### Statutory Discount Calculation Formula:
For transactions claiming Republic Act No. 9994 (Expanded Senior Citizens Act) or Republic Act No. 10754 (An Act Expanding the Benefits of Persons with Disability):
$$\text{Discount Amount} = \sum_{k \in \text{Qualified Items}} (\text{Price}_k \times \text{Quantity}_k) \times 0.20$$
$$\text{Net Amount Due} = \text{Total Gross Subtotal} - \text{Discount Amount}$$
$$\text{VAT-Exempt Sales} = \text{Net Amount Due}$$
$$\text{Change} = \text{Amount Tendered} - \text{Net Amount Due}$$

---

### 3.4 Module 4: Barista & Kitchen Display System (KDS) Process Flow

The paperless KDS screen coordinates kitchen preparation, tracks order lead times, and enforces standard recipe assembly.

```mermaid
stateDiagram-v2
    [*] --> Received: Cashier Completes POS Order (Web Audio Chime Sounds)
    
    state Received {
        direction LR
        ticket_pending: Yellow Header
        prep_timer: Countdown Elapsed Timer Active
        bom_specs: Displays Item Recipe Customizations
    }
    
    Received --> Brewing: Barista Taps Ticket (Preparation Commences)
    
    state Brewing {
        direction LR
        ticket_in_prep: Blue Header
        brewing_notice: Recipe Assembly in Progress
    }
    
    Brewing --> Ready: Barista Finishes Preparation & Plating
    
    state Ready {
        direction LR
        ticket_ready: Green Flashing Header
        ready_alert: Audio Notification for Pickup / Delivery
    }
    
    Ready --> Served: Counter Handover or Table Runner Delivers
    
    state Served {
        direction LR
        ticket_closed: Order Archived
        inventory_hook: Perpetual Stock Depletion Executed
    }
    
    Served --> [*]
```

#### Detailed KDS State Transitions:
1. **`Received` (Pending):** When the cashier confirms payment, an order event is recorded. The ticket appears with a bright amber header, displaying the customer’s name, table designation, elapsed timer, and line items with explicit customization directives (e.g., *"16oz Iced Caramel Macchiato: 25% Sugar, Oat Milk, +1 Espresso Shot"*). An audible web audio chime alerts kitchen staff.
2. **`Brewing` (In Progress):** The barista taps the order card to acknowledge receipt. The header turns cobalt blue. Kitchen attendants reference the recipe Bill of Materials (BOM) to assemble food and beverage orders to precise proportions.
3. **`Ready` (Awaiting Collection):** Upon completion, the barista taps **"Mark as Ready"**. The card transitions to vibrant emerald green, signaling front-of-house floor staff or the customer for service.
4. **`Served` (Completed):** When handed to the customer or placed on the dining table, the ticket is archived into historical logs. This completion event triggers the inventory management engine to calculate perpetual stock deduction.

---

### 3.5 Module 5: Finished Product Yield Calculator & Perpetual Inventory Flow

The perpetual inventory subsystem automates recipe ingredient tracking, tracks raw material levels, and forecasts the maximum quantity of finished food and drinks the kitchen can prepare.

```mermaid
flowchart TD
    subgraph Inflow["A. Material Receiving (Stock In)"]
        A1["Receive Raw Supplies (Coffee Beans, Milk, Flour, Syrups)"] --> A2["Staff Accesses Inventory Suite"]
        A2 --> A3["Record Stock-In Transaction (Batch No, Qty, Unit Cost, Expiry)"]
        A3 --> A4["Generate & Print Physical Inventory QR Codes"]
        A4 --> A5["Perpetual Stock Added to Database"]
    end

    subgraph Outflow["B. Point of Sale & KDS Consumption"]
        B1["Order Marked as Served"] --> B2["Fetch Recipe Bill of Materials (BOM)"]
        B2 --> B3["Iterate Ingredients per Product Recipe"]
        B3 --> B4["Deduct: Current Stock = Current Stock - (Recipe Qty * Order Qty)"]
        B4 --> B5["Log Stock Transaction Movement in stock_transaction_lines"]
    end

    subgraph YieldEngine["C. Finished Product Yield & Bottleneck Engine"]
        C1["Trigger Yield Recalculation"] --> C2["Retrieve Finished Goods (FG) Items"]
        C2 --> C3["For Each FG: Retrieve Recipe BOM Requirements (RAW Items)"]
        C3 --> C4["Calculate Theoretical Servings for Each Raw Ingredient:"]
        C4 --> C5["Servings(j) = Floor( StockBalance(j) / RecipeUsage(j) )"]
        C5 --> C6["Determine Product Yield: Yield = Min( Servings(j) across all j )"]
        C6 --> C7["Identify Bottleneck Ingredient: The Ingredient Yielding the Minimum Value"]
        C7 --> C8{"Is Calculated Yield <= Threshold?"}
        C8 -- "Yield = 0 (Stockout)" --> C9["Flag RED Alert: Out of Stock on POS & Admin"]
        C8 -- "Yield <= Reorder Point" --> C10["Flag AMBER Alert: Critical Bottleneck Ingredient"]
        C8 -- "Yield > Reorder Point" --> C11["Flag GREEN: Normal Production Capacity"]
    end

    A5 ==> C1
    B5 ==> C1
```

#### Mathematical Formulation of the Finished Product Yield Calculator:
Let:
- $P_i$ be the $i$-th finished good menu item (e.g., *Golden Tamaraw Latte*).
- $B(P_i) = \{ (R_{i,1}, u_{i,1}), (R_{i,2}, u_{i,2}), \dots, (R_{i,m}, u_{i,m}) \}$ be the recipe Bill of Materials (BOM) for product $P_i$, where $R_{i,j}$ represents the $j$-th raw material ingredient and $u_{i,j}$ denotes the required usage quantity per serving.
- $S(R_{i,j})$ be the current available stock balance of raw ingredient $R_{i,j}$ in perpetual storage.

The maximum **Finished Product Yield** $Y(P_i)$ represents the integer number of complete servings producible from available supplies:
$$Y(P_i) = \min_{j \in B(P_i)} \left\lfloor \frac{S(R_{i,j})}{u_{i,j}} \right\rfloor$$

The **Bottleneck Ingredient** $R^*_i$ is formally defined as the specific raw material constraining the yield:
$$R^*_i = \arg\min_{R_{i,j} \in B(P_i)} \left\lfloor \frac{S(R_{i,j})}{u_{i,j}} \right\rfloor$$

---

### 3.6 Module 6: Administrative Oversight, Audit, and Security Process Flow

The Administrative Dashboard consolidates management controls into a unified back-office view.

```mermaid
flowchart LR
    Admin(["Store Manager / Administrator"]) --> Auth{"Role-Based Auth (Session)"}
    
    Auth -- "Valid Admin" --> Console["Admin Management Console"]
    
    Console --> M1["1. Master Reservation Log"]
    M1 --> M1A["Filter by Date / Zone / Status"]
    M1A --> M1B["Manual Override: Force Check-In / Cancel / Reallocate Table"]
    
    Console --> M2["2. Sales Analytics & Daily Audits"]
    M2 --> M2A["Gross Sales vs Net Revenue"]
    M2A --> M2B["Statutory Discount Totals (Senior/PWD)"]
    M2B --> M2C["Payment Split (Cash vs GCash)"]
    
    Console --> M3["3. Menu & Recipe BOM Management"]
    M3 --> M3A["Add / Edit Products & Pricing"]
    M3A --> M3B["Configure BOM Ingredients per Finished Good"]
    M3B --> M3C["Generate Printable Product QR Tags"]
    
    Console --> M4["4. User & Security Configuration"]
    M4 --> M4A["Create Staff / Cashier / Barista Accounts"]
    M4A --> M4B["Audit Trail Log: Track All Modifications"]
```

---

## 4. Multi-Role Interaction Sequence (Swimlane Architecture)

The sequence diagram below models the cross-functional communication and data flow across all human actors and system subsystems for a complete dining lifecycle.

```mermaid
sequenceDiagram
    autonumber
    actor Customer as Customer
    actor Staff as Staff Attendant
    actor Cashier as Cashier
    actor Kitchen as Barista/Kitchen
    participant Web as Web System / API
    participant DB as Central Database
    actor Admin as Store Admin

    Customer->>Web: Books Table & Pre-Orders Items
    Web->>DB: Records Reservation (Confirmed)
    Web-->>Customer: Issues Dynamic QR Pass
    
    Customer->>Staff: Presents QR Pass at Front Desk
    Staff->>Web: Scans QR Pass via Camera Scanner
    Web->>DB: Validates 15-Min Grace Period & Sets Status = Checked-In
    Web->>DB: Updates Table Status = Occupied
    Staff-->>Customer: Escorts to Reserved Table
    
    Customer->>Cashier: Proceeds to Counter / Shows QR Pass
    Cashier->>Web: Scans QR -> Auto-Fetches Pre-Order Cart
    Customer->>Cashier: Requests Senior Citizen Discount (Presents ID)
    Cashier->>Web: Applies 20% Senior Discount & Receives Payment
    Web->>DB: Logs Completed Order & Generates Thermal Receipt
    Web->>Kitchen: Broadcasts Live Ticket to KDS (Web Audio Chime)
    
    Kitchen->>Web: Taps Ticket -> Status Changes to Brewing
    Kitchen->>Web: Completes Assembly -> Status Changes to Ready
    Kitchen-->>Customer: Serves Artisan Coffee & Pastries
    
    Kitchen->>Web: Marks Order as Served
    Web->>DB: Triggers Perpetual Recipe BOM Inventory Deduction
    Web->>DB: Recalculates Finished Product Yields & Bottlenecks
    
    Admin->>Web: Accesses Back-Office Dashboard
    Web->>DB: Queries Daily Financial Audits & Stock Balances
    DB-->>Admin: Renders Real-Time Sales, Yield Projections & Reorder Alerts
```

---

## 5. System Exception & Edge Case Handling Matrix

To ensure field reliability during peak dining operations, the system enforces automated exception controls:

| Operational Scenario | Potential System Hazard | Architectural Countermeasure / Resolution |
| :--- | :--- | :--- |
| **Simultaneous Table Selection** | Two customers click the same table node simultaneously. | **Database Transaction Lock:** An atomic check verifies slot availability at the exact instant of submission; the second transaction fails gracefully with a user-friendly alert: *"Table just booked by another diner. Please choose another table."* |
| **No-Show Customer** | Customer reserves a table during peak hours but fails to appear. | **15-Minute Grace Period Automation:** If arrival time exceeds `reservation_time + 15 minutes`, the check-in scanner refuses the pass, changes status to `No-Show`, and restores the table to `available` for walk-in guests. |
| **Unlinked Pre-Order Billing** | Cashier manual billing creates double entries. | **Single-Fetch Token Trapping:** When a QR pre-order is loaded into the POS register, its reference token is linked to the order. Once checked out, the token cannot be re-fetched. |
| **Ingredient Stockout During Shift** | Barista runs out of milk mid-shift after an order is placed. | **Real-Time Bottleneck Warning:** The Yield Calculator tracks raw stocks perpetually. When stock hits zero, the POS product card is automatically greyed out with an *"Out of Stock"* badge, disabling additions. |
| **Hardware / Network Disconnection** | Internet drops on local café router. | **Local Fallback Architecture:** The system runs natively on local Apache/MySQL (`localhost/CMS`) with graceful offline degradation; cached receipts can be reprinted from order history. |
| **Statutory Discount Fraud** | Non-qualified discounts applied without oversight. | **Mandatory ID Recording & Audit Trail:** The POS register enforces the input of a government Senior/PWD ID number before unlocking the 20% discount calculation, saving the ID directly into the sales audit record. |

---

## 6. Synthesis for Manuscript Chapter 3 Integration

This document corresponds directly to the academic methodologies required in **Chapter 3 (System Design and Methodology)** of the research manuscript:
1. **Flowcharts (Section 3.x Operational Flowchart):** The diagrams in Section 2, Section 3, and Section 4 provide the academic visualizations for the operational workflows of all system roles.
2. **Formula Documentation:** The mathematical expressions for **Statutory Discounting** (Section 3.3) and the **Finished Product Yield Calculator with Bottleneck Analysis** (Section 3.5) align with the mathematical formulations in the research design.
3. **Data Integrity & Traceability:** The transactional sequences demonstrate full relational database integrity, foreign key cascading, and non-destructive historical sales preservation.
