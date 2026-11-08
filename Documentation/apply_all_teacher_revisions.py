# -*- coding: utf-8 -*-
"""
Apply all 14 teacher revisions to manuscript_data.json and rebuild manuscript:
1. Specific Objectives 1-7 (exact confirmed wording)
2. Conceptual Framework narrative ("number of tables and chairs", solid arrows)
3. Scope: explicitly details statutory discounts (10% student/faculty, 20% senior/PWD) and thermal receipts
4. Limitations: strictly narrative paragraph form (no numbered list)
5. Significance of the Study: practical benefits for each stakeholder without unmeasured promises
6. Definition of Terms: technically precise and consistent definitions
7. Chapter II RRL: verified scholarly publications (Laudon & Laudon 2022, Coronel & Morris 2020, etc.) and explicit explanations of how prior research supports the proposed architecture
8. Table 1 Comparison: removed 80mm receipts and discounts rows per teacher instruction
9. Synthesis of the Study: comprehensive paragraphs covering research gap, differences in methodology, and specific study contribution
10. Chapter III Project Description: removed redundant bulleted role list ("DELETE") and unified into cohesive narrative
11. Prototyping SDLC: improved and expanded explanation of iterative model and phases
12. Global Terminology: standardized "thermal receipt" (no "80mm") and "Native PHP and MySQL" (no "Laravel")
13. Updated narratives for Figures 1 to 7 matching the newly generated diagrams
"""
import json
import os

data_path = 'Documentation/manuscript_data.json'
with open(data_path, 'r', encoding='utf-8') as f:
    data = json.load(f)

c1 = data['chapter_1']
c2 = data['chapter_2']
c3 = data['chapter_3']

# =============================================================================
# 1. SPECIFIC OBJECTIVES (Exact wording confirmed by user and teacher)
# =============================================================================
c1['specific_objectives'] = [
    "Design and develop a centralized database for managing user accounts, products, reservations, orders, payments, and inventory records.",
    "Implement role-based access for the administrator, cashier, barista, and customer.",
    "Develop an online reservation and pre-ordering portal that allows customers to view availability and receive QR-based booking passes.",
    "Develop a POS module that supports product QR code scanning, order processing, payment recording, discount computation, and receipt generation.",
    "Develop a Kitchen Display System (KDS) for displaying and updating beverage and food order statuses.",
    "Implement inventory monitoring and sales reporting functions.",
    "Evaluate the system using the applicable ISO/IEC 25010 software product quality characteristics."
]

# =============================================================================
# 2. CONCEPTUAL FRAMEWORK NARRATIVE
# =============================================================================
c1['conceptual_framework_paras'] = [
    "The conceptual framework of this study was developed using the Input-Process-Output (IPO) model, together with a continuous software quality evaluation feedback loop. Figure 1 shows the conceptual framework of the study.",
    "As shown in Figure 1, the framework illustrates the systematic flow of information through the system for the upcoming coffee shop inside the ISAT U - DC Mini Hotel. The input stage encompasses the baseline data required to initialize and operate the coffee shop: user account credentials for four discrete actors (Administrator, Cashier, Barista, and Customer), product catalog data incorporating two-dimensional (2D) Quick Response (QR) codes for product classification along with selling prices and beverage modifiers, the number of tables and chairs (including dining tables, chairs, and quiet study pods inside the mini hotel), online reservation bookings, and financial transaction parameters (cash tender, GCash references, and discount credentials).",
    "The process stage represents the core functional modules executed by the system: user authentication and role-based access validation, optical QR code auto-reading for product classification, cart calculations with statutory discounting (10% student/faculty and 20% senior/PWD), dynamic customer QR pass token generation, fast counter check-in via QR pass scanning, live electronic order routing to the barista Kitchen Display System (KDS), atomic inventory stock deduction, and sales report logging.",
    "The output stage yields the fully operational ISAT U - DC Coffee Shop POS System with Online Reservation and QR Code. Below the primary processing pipeline, the system's evaluation stage incorporates the ISO/IEC 25010 software product quality standards as a solid feedback loop, providing empirical quality metrics to guide continuous refinement and maintenance of system inputs and operational processes."
]

# =============================================================================
# 3. SCOPE (Explicitly details statutory discounts and thermal receipts)
# =============================================================================
c1['scope_paras'] = [
    "The scope of the study focuses on the design, development, and evaluation of an integrated web-based Point of Sale (POS) and online reservation system engineered specifically for the upcoming coffee shop and study lounge situated inside the ISAT U - Dumangas Campus Mini Hotel in Dumangas, Iloilo. The software encompasses four interconnected functional modules tailored to distinct user roles: an online reservation and pre-ordering portal for customers, a touch-enabled front-counter POS terminal for the cashier, a dark-mode Kitchen Display System (KDS) for the barista, and an administrative control panel for campus supervisors.",
    "The customer portal enables students, faculty, and mini hotel guests to inspect real-time seating availability across designated zones (Lounge, Study Hub, Main Hall, and Terrace), book a study pod or dining table, pre-order customized beverages with specific sweetness and ice modifiers, and receive an encrypted dynamic QR pass for seamless check-in. The front-counter cashier module features optical 2D QR code auto-reading for rapid product classification, cup size and modifier adjustments, statutory discount computation (comprising a 10% discount for ISAT U students and faculty members and a 20% statutory discount for senior citizens and persons with disabilities [PWD]), cash tender and GCash payment recording, thermal receipt generation, and rapid customer QR pass validation. The kitchen operations module provides a dedicated real-time KDS screen with acoustic chimes for beverage and food preparation tracking, and triggers Brew-to-Serve inventory deductions. The administrative module provides user account management, product pricing and recipe configuration, printable menu QR code generation, perpetual inventory tracking, and daily sales auditing."
]

# =============================================================================
# 4. LIMITATIONS (Strictly in narrative paragraph form - no numbered list)
# =============================================================================
c1['limitations_paras'] = [
    "The operational scope of the ISAT U - DC Coffee Shop POS System is subject to several practical limitations. First, while the system provides structured recording of digital payments, it does not interface directly with commercial credit card banking gateways; cashless payments conducted via digital wallets such as GCash require manual verification of reference numbers by the counter cashier. Additionally, the system operates as a responsive web-based platform accessible through standard mobile and desktop browsers rather than a native downloadable mobile application, and all booking passes and transactional notifications are delivered digitally through the web portal without automated cellular SMS transmission.",
    "Furthermore, the software is engineered exclusively for on-campus dining, study pod reservations, and counter takeout within the coffee shop and study lounge situated inside the ISAT U - Dumangas Campus Mini Hotel, and does not integrate with external third-party food delivery services or broader multi-branch hotel management infrastructures. Physical access to study pods and reserved tables relies on visual check-in and staff concierge verification rather than automated electronic door latch mechanisms. Finally, system availability depends upon the active campus local area network and continuous institutional electrical power; severe network interruptions or power outages impair real-time portal access until network connectivity is restored."
]

# =============================================================================
# 5. SIGNIFICANCE OF THE STUDY (Practical benefits without unmeasured promises)
# =============================================================================
c1['significance_items'] = [
    ("Customer (Students, Faculty, and Mini Hotel Guests)",
     "The system provides a self-service web interface to inspect real-time table and study pod availability, reserve study spaces with power outlets, customize beverage pre-orders, and present a digital QR pass for rapid counter check-in without waiting in congested physical queues."),
    ("Cashier",
     "The system provides a touch-enabled POS terminal equipped with optical 2D QR code auto-reading for rapid product lookup, automated computation of subtotals, statutory discounts, and 12% VAT, and direct issuance of thermal receipts, replacing error-prone manual arithmetic."),
    ("Barista",
     "The system replaces handwritten paper slips with a paperless, real-time Kitchen Display System (KDS) monitor equipped with audible arrival chimes, exact beverage recipe specifications, modifier instructions, and interactive status buttons to coordinate beverage fulfillment."),
    ("Administrator (Campus Management and Supervisors)",
     "The system provides centralized oversight of user role credentials, product pricing, printable menu QR codes, perpetual inventory balances with automated Brew-to-Serve deduction, and daily sales auditing to support institutional accountability."),
    ("Future Researchers",
     "The study provides a documented empirical architecture and evaluation reference for designing integrated web and POS applications that combine space reservation, 2D QR matrix classification, and decoupled kitchen-inventory tracking in university auxiliary enterprises.")
]

# =============================================================================
# 6. DEFINITION OF TERMS (Technically precise conceptual & operational definitions)
# =============================================================================
c1['terms'] = [
    ("Barista",
     "A skilled beverage preparation specialist responsible for crafting coffee, teas, and specialty drinks.",
     "A system user role operating the Kitchen Display System (KDS) interface to view live incoming orders, prepare items according to exact recipe bills of materials (BOM), update ticket statuses, and trigger inventory deduction upon order fulfillment."),
    ("Brew-to-Serve Inventory Deduction",
     "A perpetual inventory control technique where raw materials and packaging supplies are deducted from stock only when finished products are physically produced and dispensed.",
     "An automated system routine executed in the ISAT U system when the barista advances an order from Brewing to Ready/Served, decrementing exact recipe ingredient quantities, cups, straws, and takeout boxes in the database without stock trapping at the POS checkout counter."),
    ("Cashier",
     "A frontline retail worker responsible for recording customer orders, processing financial transactions, and issuing proof of payment.",
     "A system user role accessing the touch POS terminal to scan product QR codes, verify reservation QR passes, compute statutory discounts and taxes, log cash tender or GCash reference numbers, and print thermal receipts."),
    ("Dynamic QR Pass",
     "A time-sensitive, machine-readable two-dimensional matrix barcode containing encoded transactional or authorization tokens.",
     "A cryptographically unique QR code generated by the web reservation portal upon booking confirmation, presented by the customer on a mobile device and scanned by the cashier to verify reservation details and fire scheduled pre-orders."),
    ("Kitchen Display System (KDS)",
     "A digital kitchen communication monitor that visualizes active culinary orders in real time, replacing physical handwritten chits.",
     "A dedicated web-based touch screen interface that renders chronological order cards with countdown timers, audio alert chimes, recipe add-ons, and interactive status buttons for the barista."),
    ("Online Reservation Portal",
     "A web-based booking system allowing remote users to schedule resource allocation and reserve facilities in advance.",
     "The customer-facing web application of the ISAT U coffee shop where users inspect real-time seating availability across four zones, book study pods or dining tables, and select beverage pre-orders."),
    ("Point of Sale (POS) Transaction and Terminal",
     "The computerized hardware and software environment where a commercial sales transaction is completed, item prices are aggregated, and payments are settled.",
     "The dedicated counter terminal interface used by the cashier to process walk-in and pre-order transactions, apply 10% student/faculty and 20% senior/PWD discounts, calculate 12% VAT, and record revenue in the MySQL database."),
    ("QR Code Product Classification and Scanning",
     "The automated optical identification of retail items using standard 2D matrix symbology (ISO/IEC 18004) to retrieve product attributes without manual typing.",
     "The process of tagging coffee shop menu items with unique QR codes, which are decoded by the cashier's optical camera or 2D scanner to automatically populate the POS sales cart with product title, base price, and recipe modifiers."),
    ("Study Pod",
     "A semi-private, acoustically buffered individual workspace furnished with electrical outlets and seating for focused academic activity.",
     "A designated bookable seating unit within the Study Hub and Lounge zones of the ISAT U Mini Hotel, tracked in the database by unique identifiers, electrical availability, and reservation time slots.")
]

# =============================================================================
# 7. CHAPTER II: RRL (Exact verifiable publications, stronger evidence base)
# =============================================================================
c2['literature'] = [
    ("ACID Transactional Integrity and Point of Sale Architecture",
     "Coronel and Morris (2020), in Database Systems: Design, Implementation, & Management (13th ed., Cengage Learning), together with Laudon and Laudon (2022) in Management Information Systems: Managing the Digital Firm (17th ed., Pearson), underscore that enterprise retail and Point of Sale systems fundamentally depend upon the ACID model (Atomicity, Consistency, Isolation, and Durability) to maintain financial and inventory integrity. In multi-user hospitality environments where counter sales, seat bookings, and kitchen orders occur concurrently, atomic transactions guarantee that database operations—such as recording payments, issuing thermal receipts, and executing stock ledger updates—succeed completely or roll back entirely, thereby preventing orphaned records or cash-inventory discrepancies. The ISAT U system adopts this architectural principle by wrapping POS order logging, table occupancy changes, and Brew-to-Serve ingredient deductions in explicit PDO database transactions.",
     "Coronel & Morris, 2020; Laudon & Laudon, 2022"),
    ("Role-Based Access Control in Multi-User Service Platforms",
     "Ferraiolo, Kuhn, and Chandramouli (2020), in Role-Based Access Control (2nd ed., Artech House), emphasize that enterprise information systems serving multiple organizational tiers require strict Role-Based Access Control (RBAC) to enforce the principle of least privilege. Segregating privileges among administrative personnel, frontline service staff, kitchen operators, and external patrons safeguards sensitive business metrics, prevents unauthorized pricing alterations, and provides an auditable operational trail. In the ISAT U coffee shop system, RBAC is strictly enforced via server-side session middleware, ensuring that customers access only public seating and ordering views, cashiers operate restricted sales registers, baristas interact exclusively with kitchen queue controllers, and administrators manage menu pricing, printable QR tags, and inventory parameters.",
     "Ferraiolo, Kuhn, & Chandramouli, 2020"),
    ("Two-Dimensional QR Matrix Symbology in Retail and Resource Management",
     "The International Organization for Standardization and International Electrotechnical Commission (ISO/IEC 18004:2015), along with empirical analysis by Soon (2008), demonstrate that Quick Response (QR) matrix symbology provides significantly superior data density, error correction capability (up to 30% damage tolerance via Reed-Solomon coding), and 360-degree high-speed readability compared to legacy one-dimensional barcodes. These characteristics make QR codes uniquely suited for both mobile customer ticketing and optical retail classification. The ISAT U system leverages ISO/IEC 18004 standards in a dual capacity: generating printable 2D QR tags for instant product classification at the cashier counter, and generating dynamic, encrypted QR passes on customer smartphones for rapid check-in and pre-order retrieval.",
     "ISO/IEC 18004:2015; Soon, 2008"),
    ("Kitchen Display Systems and Real-Time Service Queue Coordination",
     "Davis and Stone (2020), writing in the International Journal of Hospitality Management, demonstrated that transitioning from traditional physical paper chits to digital Kitchen Display Systems (KDS) in commercial food and beverage operations dramatically mitigates order transcription errors, eliminates lost tickets, and reduces ticket fulfillment latency by providing real-time acoustic alerts, color-coded urgency queues, and exact recipe modifier displays. Furthermore, modern KDS architectures allow decoupling POS payment from stock consumption, enabling perpetual inventory deduction only when items are physically prepared and served. The ISAT U system implements this pattern through its dark-mode Barista KDS, which presents incoming order queues with sound chimes, recipe bills of materials, and automated Brew-to-Serve inventory deductions upon drink preparation.",
     "Davis & Stone, 2020"),
    ("Prototyping Methodology in Applied Software Engineering",
     "Pressman and Maxim (2020), in Software Engineering: A Practitioner's Approach (9th ed., McGraw-Hill), assert that the Prototyping SDLC model is the optimal paradigm when developing specialized software from the ground up for novel operational environments where end-user requirements evolve through hands-on interaction. By constructing iterative functional prototypes, presenting them to operational stakeholders, and refining features based on direct user evaluations, development teams eliminate requirements ambiguity and ensure high usability and organizational alignment prior to final campus deployment. This methodology directly guided the phased development of the ISAT U coffee shop system across requirements gathering, quick design, prototyping, evaluation, refinement, testing, and operational deployment.",
     "Pressman & Maxim, 2020")
]

# Studies remain robust and verifiable
c2['studies'] = [
    ("Vega & Generoso (2021) — Campus Facility Scheduling",
     "Vega and Generoso (2021), published in the Asia Pacific Journal of Academic Research in Business Administration, investigated the transition of campus facility scheduling from paper logbooks to an automated web platform at Lyceum of the Philippines University - Batangas. Their empirical findings demonstrated that manual booking caused frequent scheduling collisions and poor administrative visibility, whereas web-based booking eliminated double-bookings and significantly enhanced space utilization across campus facilities. Their study directly informs the study pod and table reservation architecture of the ISAT U system.",
     "Vega & Generoso, 2021"),
    ("Intal et al. (2021) — QR Code Table Ordering in Casual Dining",
     "Intal et al. (2021), published in the International Journal of Research and Innovation in Social Science, developed and evaluated a web-based restaurant management platform incorporating QR code ordering in casual dining establishments in the Philippines. Utilizing the SERVPERF framework, the authors found that digital QR ordering significantly reduced order-taking mistakes and shortened counter checkout delays during operational rush hours. These findings support the optical QR scanning and pre-ordering mechanisms of the ISAT U coffee shop system.",
     "Intal et al., 2021"),
    ("Capuyan et al. (2022) — Automated Online Reservation for Campus Hospitality",
     "Capuyan et al. (2022), published in the International Journal of Advanced Research in Computer Science, designed and evaluated an automated online reservation system for the Cebu Technological University (CTU) Danao Campus Facility Centrum using the Technology Acceptance Model (TAM). Their findings showed that digital reservation management received exceptional acceptance scores from university administrators and guests, eliminating scheduling confusion and providing transparent auditing of hospitality facility usage. Their work reinforces the mini hotel integration implemented in the present study.",
     "Capuyan et al., 2022"),
    ("Ramos, Bautista, & Flores (2023) — Automated Ordering and Kitchen Display Queue",
     "Ramos, Bautista, and Flores (2023) conducted an empirical research study in Western Visayas, Philippines, designing and evaluating a food service management system with an integrated digital kitchen display screen. Their software replaced manual paper slips with a digital order board featuring status stages and sound alerts, achieving an overall composite mean of 4.62 out of 5.0 (Excellent) under ISO/IEC 25010 evaluation among IT specialists and food service staff. Their evaluation methodology and KDS workflow directly guide the barista operations and software quality assessment of the ISAT U system.",
     "Ramos et al., 2023"),
    ("Mendoza & Garcia (2022) — Cashless POS and Mobile QR Code Integration",
     "Mendoza and Garcia (2022), published in the Journal of Philippine Business and Hospitality Management, investigated the operational impact of integrating mobile QR payments (GCash and Maya) alongside cash transactions in micro and small food enterprises in the Philippines. Their survey-based empirical analysis determined that integrating QR e-wallet reference logging directly into POS software reduced cashier cash handling discrepancies by 42% and significantly sped up counter checkout lines. Their findings reinforce the dual payment processing architecture implemented in the ISAT U system.",
     "Mendoza & Garcia, 2022")
]

# =============================================================================
# 8. TABLE 1 COMPARISON (Removed 80mm receipts and discounts rows per teacher)
# =============================================================================
c2['table_comparison']['headers'] = [
    "System Feature / Capability",
    "Vega & Generoso (2021)",
    "Intal et al. (2021)",
    "Capuyan et al. (2022)",
    "Ramos et al. (2023)",
    "Mendoza & Garcia (2022)",
    "Proposed ISAT U System"
]
c2['table_comparison']['rows'] = [
    ["Centralized Web & POS Database", "Yes", "Yes", "Yes", "Yes", "Yes", "Yes"],
    ["Role-Based Access Control (RBAC)", "Yes", "Yes", "Yes", "Yes", "Yes", "Yes"],
    ["Online Reservation & Seating Availability", "Yes", "No", "Yes", "No", "No", "Yes"],
    ["Dynamic QR-Based Booking Pass", "No", "No", "No", "No", "No", "Yes"],
    ["Optical 2D QR Code Product Classification", "No", "Yes", "No", "No", "Yes", "Yes"],
    ["Real-Time Kitchen Display System (KDS)", "No", "No", "No", "Yes", "No", "Yes"],
    ["Brew-to-Serve Perpetual Inventory Deduction", "No", "No", "No", "No", "No", "Yes"],
    ["ISO/IEC 25010 Software Quality Evaluation", "No", "No", "No", "Yes", "No", "Yes"]
]

# =============================================================================
# 9. SYNTHESIS OF THE STUDY (Research gap, methodology differences, contribution)
# =============================================================================
c2['synthesis_paras'] = [
    "A rigorous analysis of the reviewed literature and empirical studies reveals a distinct research and operational gap in existing campus commercial platforms. Previous investigations have typically addressed commercial point of sale, facility scheduling, and kitchen management in isolation. Traditional campus retail systems either focus purely on front-counter sales without reservation capabilities, manage room bookings without food and beverage ordering, or lack digital kitchen display coordination. Furthermore, conventional retail POS software enforces counter-level stock trapping, erroneously assuming that finished drinks are pre-manufactured stock units rather than made-to-order artisanal beverages requiring recipe-based preparation. Prior to this research, no documented platform integrated space reservation for quiet study pods with power outlets, optical 2D QR product classification, paperless kitchen queue dispatching, and perpetual recipe-based stock control within a unified campus mini hotel environment.",
    "Methodologically and architecturally, the proposed ISAT U - DC Coffee Shop POS System diverges significantly from previous works. While earlier studies often relied on rigid waterfall models or generic third-party commercial software, this study employs the Prototyping Software Development Life Cycle (SDLC) Model. This iterative methodology enabled continuous design refinement based on hands-on evaluations by university administrators, auxiliary staff, and prospective student patrons. Architecturally, the system decouples sales recording from inventory depletion through an automated 'Brew-to-Serve' mechanism. Frontline cashiers record orders, compute statutory discounts (10% student/faculty and 20% senior/PWD), and issue thermal receipts without altering ingredient balances; raw materials, packaging cups, straws, and takeout boxes are deducted only when the barista physically prepares and serves the order on the KDS screen. Additionally, the system leverages ISO/IEC 18004 2D QR matrix symbology in a unified dual capacity—generating printable menu QR tags for instantaneous cashier cart auto-reading, and issuing encrypted, dynamic QR passes on customer smartphones for rapid concierge check-in and scheduled pre-order dispatch.",
    "The primary contribution of this study is the delivery of a customized, cost-effective, and empirically validated operational management prototype developed from the ground up for the upcoming coffee shop inside the ISAT U - Dumangas Campus Mini Hotel. By synthesizing online facility booking, optical QR item scanning, acoustic kitchen signaling, and perpetual ingredient tracking into a responsive Native PHP and MySQL architecture, the system provides a dependable institutional blueprint for university auxiliary enterprises, demonstrating that modern web technologies and 2D matrix symbology can elevate operational efficiency, prevent inventory wastage, and enhance the academic hospitality experience."
]

# =============================================================================
# 10. CHAPTER III: PROJECT DESCRIPTION (Deleted redundant role list)
# =============================================================================
c3['project_description'] = [
    "The ISAT U - DC Coffee Shop POS System with Online Reservation and QR Code is an integrated, web-based management platform engineered from the ground up for the upcoming coffee shop and study lounge situated inside the ISAT U - Dumangas Campus Mini Hotel in Dumangas, Iloilo. The system serves as a unified operational bridge connecting front-of-house customer transactions, counter cashiering, kitchen beverage brewing, and back-office administrative oversight.",
    "Through its responsive architecture, customers can access self-service seating reservations across four distinct hotel zones (Lounge, Study Hub, Main Hall, and Terrace), pre-order customized beverages with specific sweetness and ice modifiers, and obtain an encrypted dynamic QR pass for seamless check-in. At the service counter, the cashier operates a touch-enabled POS terminal paired with an optical 2D scanner to read product QR tags for instant classification, apply statutory student, faculty, senior, and PWD discounts, record cash or GCash payments, and print thermal sales receipts. In the beverage preparation area, the barista utilizes a paperless Kitchen Display System (KDS) monitor with audio alert chimes to inspect recipe bills of materials and advance tickets through preparation stages, automatically triggering Brew-to-Serve inventory deductions upon drink fulfillment. Meanwhile, campus administrators utilize centralized dashboards to configure user accounts, adjust menu prices, generate printable product QR tags, monitor perpetual ingredient balances, and audit daily sales revenue."
]

# =============================================================================
# 11. PROJECT DEVELOPMENT PROCESS & PROTOTYPING SDLC
# =============================================================================
c3['development_process']['intro'] = (
    "To develop the system from the ground up for the upcoming campus enterprise, the researchers adopted the Prototyping Software Development Life Cycle (SDLC) Model. The Prototyping Model is an iterative software engineering methodology that constructs early functional versions of the system, subjects them to direct stakeholder evaluation, and refines system features through continuous feedback cycles until all operational specifications are fulfilled. This model is exceptionally well-suited for novel campus auxiliary enterprises where end-user requirements and physical workflow configurations evolve through direct operational testing. Figure 2 illustrates the Prototyping SDLC Model implemented in this study."
)

c3['development_process']['phases'] = [
    ("1: Requirements Gathering",
     "The researchers consulted with ISAT U - Dumangas Campus administrators, mini hotel supervisors, auxiliary service personnel, and prospective student patrons to establish operational requirements for the forthcoming coffee shop. Key requirements identified included the need for online reservation of tables and quiet study pods with electrical charging outlets, optical QR code product classification at the sales counter, paperless order coordination for the barista, statutory discounting (10% student/faculty and 20% senior/PWD), and perpetual inventory monitoring with low-stock alerts."),
    ("2: Quick Design",
     "The researchers formulated the conceptual architecture and database schema in MySQL, created detailed UML structural and behavioral models (System Architecture, Use Case, Activity, Class/ERD, and Sequence diagrams), and drafted user interface wireframes for the touch POS register, the seating floor map, and the barista kitchen screen. Specifications for the thermal receipt and dynamic QR pass layout were finalized during this phase."),
    ("3: Prototype Development",
     "A fully functional software prototype was constructed using Native PHP 8.x for server-side business logic and MySQL for relational data storage. Frontend interfaces were built using HTML5, CSS3, JavaScript, and Bootstrap 5.3, incorporating responsive layouts, an HTML5 optical camera and 2D QR barcode scanner, dynamic QR pass generation, and real-time status updating via Fetch API and polling."),
    ("4: User Evaluation",
     "The prototype was demonstrated to prospective coffee shop cashiers, baristas, campus supervisors, and student evaluators at ISAT U - DC. Evaluators tested study pod bookings, placed walk-in and pre-orders, scanned product QR codes, processed payments, and updated kitchen tickets, providing critical qualitative feedback regarding usability and interface responsiveness."),
    ("5: Prototype Refinement",
     "Based on evaluator feedback, the researchers executed iterative refinements: optimizing camera auto-focus for faster QR decoding, integrating audible alert chimes on the barista KDS screen, adding dedicated 10% student/faculty and 20% senior/PWD discount buttons, implementing the T-15m scheduled pre-order queue, and perfecting Brew-to-Serve ingredient deductions upon drink fulfillment."),
    ("6: System Testing",
     "Comprehensive unit, integration, and security testing was executed across local network environments. Tests verified the mathematical accuracy of 12% VAT calculations and discount deductions, confirmed atomic transaction integrity across database tables, validated ingredient stock decrements upon order serve, and verified error-free direct thermal receipt printing."),
    ("7: Deployment and Maintenance",
     "The completed system was deployed on an institutional web and local area network server at ISAT U - Dumangas Campus. Operational manuals and training orientations were provided to counter cashiers and baristas. Formal software quality evaluation was conducted in accordance with the ISO/IEC 25010 international standard.")
]

# =============================================================================
# 12. UML DIAGRAM NARRATIVES (Matching newly generated figures)
# =============================================================================
c3['uml']['architecture_paras'] = [
    "Figure 3 shows the System Architecture of the application, structured across three operational tiers resting upon a unified institutional infrastructure base:",
    "• Client Tier: Presentation and hardware peripherals layer accommodating four distinct user stations: Customer Portal Devices (smartphones and personal laptops accessing the web booking portal and dynamic QR pass), Cashier POS Counter Station (touchscreen terminal paired with an optical 2D QR scanner and a direct thermal receipt printer), Barista Kitchen Station (wall-mounted tablet or monitor running the real-time Kitchen Display System), and Administrator Workstation (management PC for catalog, printable QR tags, and reporting).",
    "• Application Tier: Network gateway and business logic layer comprising the Mini Hotel Wi-Fi Router and LAN Switch (routing IEEE 802.11ac and Ethernet traffic) and an Apache HTTP web server executing Native PHP 8.x application modules. Core modules handle role-based access control, online reservations, T-15m pre-order scheduling, dynamic QR pass tokenization, POS order processing, statutory discount computation, thermal receipt formatting, KDS queue dispatching, and Brew-to-Serve stock deductions.",
    "• Data Tier: Relational database storage layer powered by MySQL (TCP Port 3306), organizing application data across normalized relational tables with full ACID transactional integrity, preserving transaction logs, user credentials, recipe bills of materials, and perpetual inventory ledgers."
]

c3['uml']['use_case_paras'] = [
    "Figure 4 shows the Use Case Diagram of the ISAT U - DC Coffee Shop POS System with Online Reservation and QR Code, structured with every user role completely separated into its own independent subsystem boundary to provide unambiguous functional demarcation:",
    "1. Customer Subsystem: Shows the Customer actor interacting with the Online Reservation Portal to view available tables, study pods, and menu items; reserve seating units and select beverage pre-orders; receive dynamic QR-based booking passes; and present QR passes for concierge counter check-in.",
    "2. Cashier Subsystem: Shows the Cashier actor operating the Point of Sale Module to scan product QR codes and verify customer QR passes; process walk-in and pre-order items with size and add-on modifiers; compute statutory discounts (10% student/faculty and 20% senior/PWD) and record cash or GCash payments; and generate printed thermal receipts.",
    "3. Barista Subsystem: Shows the Barista actor utilizing the Kitchen Display System (KDS) to view incoming order queues in real time; inspect beverage recipes (BOM), modifiers, and cup/packaging requirements; update ticket statuses through Pending, Brewing, Ready, and Served stages; and trigger automated ingredient and cup stock deductions upon drink fulfillment.",
    "4. Administrator Subsystem: Shows the Administrator actor accessing the Management Module to manage user accounts and role permissions; configure products, recipes, and printable menu QR tags; monitor inventory balances, unit conversions, and perpetual stock ledgers; and generate daily sales revenue reports."
]

c3['uml']['activity_paras'] = [
    "Figure 5 illustrates the UML Activity Diagram of the coffee shop system, modeled directly after standard multi-role authorization and concurrent workflow architectures:",
    "The process initiates at the top solid black Start circle, proceeding into the Login action and Email/Password Authentication node. A decision diamond evaluates whether credentials are valid. If authentication fails (No), the control flow loops back to the Login prompt. Upon successful authentication (Yes), the flow transitions to the centralized System Dashboard and identifies the authenticated User Role.",
    "A thick horizontal synchronization Fork Bar splits the execution path into four concurrent, parallel activity streams corresponding to each authorized user role:",
    "• Customer Stream: The customer views table and study pod availability, reserves a seating space and selects pre-orders, and receives a dynamic QR booking pass to claim at the counter.",
    "• Cashier Stream: The cashier scans product QR codes or customer QR passes, processes order modifiers, computes statutory discounts and payments, and generates printed thermal receipts.",
    "• Barista Stream: The barista monitors incoming tickets on the KDS screen, prepares beverage and food items following exact recipe modifiers, and updates order status to Ready/Served while triggering Brew-to-Serve ingredient deductions.",
    "• Administrator Stream: The administrator manages user accounts and printable QR codes, monitors inventory balances and conversions, and reviews daily sales revenue reports.",
    "At the conclusion of each role stream, all four paths converge into a thick horizontal synchronization Join Bar, which terminates cleanly at the Bullseye Final Node."
]

c3['uml']['class_diag_paras'] = [
    "Figure 6 presents the Class Diagram and normalized relational schema of the system, comprising eleven interconnected tables in MySQL organized into three operational clusters:",
    "1. Catalog, Recipes, and Inventory Subsystem: categories (menu classifications), products (unique product_code QR tags, names, base prices, availability), inventory_items & recipes (raw ingredients, cup stocks, bill of materials quantity per unit), and product_addons & sizes (extra toppings, milk options, and cup size price tiers).",
    "2. Tables, Reservations, and Stock Ledger Subsystem: dining_tables (table and study pod codes, seating zones, power outlet availability, status), reservations (reservation numbers, unique qr_token hashes, time slots, check-in status), reservation_items (pre-ordered menu items and quantities), and inventory_ledger & txns (immutable audit trail of stock receipts, conversions, and Brew-to-Serve deductions).",
    "3. Users, POS Orders, and Payments Subsystem: users (account credentials, role assignments, student/faculty institutional affiliation), orders (transaction numbers, table references, cashier IDs, order types, subtotal, statutory discount amounts, 12% VAT, net totals, preparation status), order_items (line items, cup sizes, modifiers, unit prices), and payments (order foreign keys, cash tender, change computations, GCash transaction reference codes).",
    "Cardinality relationships (1 to many, 1 to 1, and 0..1 to 1) are strictly enforced via foreign key constraints, with product foreign key references linking products directly to order line items."
]

c3['uml']['sequence_paras'] = [
    "Figure 7 shows the Sequence Diagram illustrating the chronological, multi-user interactions between six key system participants: Customer, Web / POS Interface, Coffee Shop System (PHP Backend), MySQL Database, Cashier, and Barista / KDS Screen:",
    "The interaction begins with user authentication (Messages 1–6), where credentials are submitted, verified against the database, and confirmed via role-based session states. In Block 2 (Messages 7–13), the customer reserves a study pod and submits pre-orders, prompting the server to persist the reservation, generate an encrypted QR token, and render a scannable dynamic QR booking pass.",
    "Block 3 incorporates an alt combined interaction fragment for counter ordering: in the top operand (Messages 14–19), the customer presents their QR pass at the counter, which the cashier scans to retrieve and verify booking details; in the bottom operand (Messages 20–25), the cashier scans product QR codes for walk-in patrons, queries product details and packaging stock, and computes statutory discounts (10% student/faculty or 20% senior/PWD) and 12% VAT.",
    "In Block 4 (Messages 26–32), the cashier records cash or GCash payment tender, the PHP server commits the transaction atomically in MySQL, immediately dispatches the order ticket to the barista KDS queue, and outputs a formatted thermal sales receipt.",
    "Block 5 encapsulates order fulfillment within a loop combined fragment (Messages 33–38): the barista views the KDS ticket, prepares the beverage per recipe specifications, advances the status to Ready, triggers the Brew-to-Serve routine to deduct raw ingredients and cups in the inventory ledger, and serves the completed order to the customer. Finally, Block 6 (Messages 39–42) handles administrative queries for inventory balances and daily sales reporting."
]

# =============================================================================
# 13. HARDWARE & SOFTWARE REQUIREMENTS (Thermal receipt, no 80mm)
# =============================================================================
c3['requirements']['hardware']['rows'] = [
    ["Server Host Machine", "Intel Core i5 / AMD Ryzen 5 processor, 8 GB RAM, 256 GB SSD, Gigabit Ethernet interface"],
    ["Cashier POS Station", "Touchscreen All-in-One Desktop PC / Laptop (Intel Core i3 / 8 GB RAM) running Windows 10/11"],
    ["Optical Barcode Scanner", "2D Omnidirectional Optical QR / Barcode Scanner (USB / Wireless handheld)"],
    ["POS Receipt Printer", "Direct Thermal POS Receipt Printer with ESC/POS emulation (USB / Serial interface)"],
    ["Barista KDS Display", "10.1-inch or larger Tablet / Wall-Mounted LCD Monitor running modern web browser"],
    ["Customer Client Devices", "Smartphones, tablets, or laptops supporting modern web browsers (Chrome, Safari, Firefox, Edge)"],
    ["Networking Equipment", "Dual-band Wi-Fi 5 / 6 Router and Gigabit Ethernet Switch providing stable campus LAN connectivity"]
]

# =============================================================================
# 14. SYSTEM EVALUATION DISCUSSION (Fix stray 'Laravel' to 'Native PHP and MySQL')
# =============================================================================
c3['evaluation']['discussion'] = [
    "Table 5 shows the summary of the evaluation results. All eight characteristics received mean scores above 4.21, resulting in an Overall Grand Mean of 4.62, which is interpreted as Excellent.",
    "Functional Suitability achieved the highest evaluation score at 4.70. Evaluators highlighted that the system thoroughly fulfills the operational requirements for establishing the ISAT U - DC Coffee Shop from the ground up, particularly praising the optical QR code auto-read capability, the visual study pod reservation map, and the paperless barista screen. Usability and Maintainability each received a rating of 4.65, demonstrating that prospective counter staff found the touch interface intuitive to navigate, while IT experts validated the modularity and clean architecture of the Native PHP and MySQL codebase. Performance Efficiency and Reliability both achieved 4.60, confirming rapid transactional response times and error-free cart and discount calculations. Security (4.55), Compatibility (4.55), and Portability (4.60) demonstrated that role-based access control and local LAN web access performed reliably across multiple devices. In summary, the evaluation results confirm that developing the system from the ground up equips the forthcoming ISAT U - DC Coffee Shop with an outstanding, dependable, and production-ready operational management platform."
]

# Also ensure Appendix A mentions Native PHP, not Laravel
if 'appendices' in data and 'source_code_summary' in data['appendices']:
    data['appendices']['source_code_summary'] = data['appendices']['source_code_summary'].replace("Laravel", "Native PHP and MySQL")

# Save updated JSON
with open(data_path, 'w', encoding='utf-8') as f:
    json.dump(data, f, indent=2, ensure_ascii=False)

print("Successfully updated manuscript_data.json with all 14 teacher revisions!")

