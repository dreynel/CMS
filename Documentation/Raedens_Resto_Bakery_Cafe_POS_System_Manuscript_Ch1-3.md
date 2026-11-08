# RAEDEN'S RESTO, BAKERY, CAFE POS SYSTEM WITH ONLINE RESERVATION AND QR CODE
## Capstone Manuscript (Chapters 1 to 3)


# Chapter I
INTRODUCTION


## Background of the Study

Computers and software are very helpful in running modern stores and coffee shops. In a cafe, a Point of Sale or POS system helps staff take orders quickly, compute prices and discounts correctly, and print customer receipts without making math mistakes. Using Quick Response (QR) codes makes counter work even faster because products and customer passes are identified right away. At the same time, an online booking website helps customers see available seats ahead of time, which prevents long lines and crowded dining areas.

Raeden's Resto, Bakery, Cafe is a popular food service and dining establishment offering a diverse selection of freshly baked pastries, hearty kitchen meals, and handcrafted coffee and specialty beverages. As a bustling commercial dining venue, the establishment serves daily walk-in diners, families, and patrons seeking comfortable table dining and cafe refreshments. Currently, the business operates under a typical manual setup common among independent restaurants, bakeries, and cafes. Front-of-house staff take orders using handwritten paper slips, seating is managed through informal visual checks, kitchen orders are communicated verbally or through physical paper tickets, and ingredient inventory is monitored through periodic manual visual counts.

In the day-to-day operations of Raeden's Resto, Bakery, Cafe, front-of-house service and food and beverage preparation are handled across distinct stations: cashiers at the sales counter, dining attendants managing floor seating, and baristas and kitchen staff in the food preparation area. However, operating under a traditional manual setup creates serious operational bottlenecks during peak breakfast, lunch, and weekend dining rushes. Relying on handwritten paper order slips and verbal communication between the front counter and kitchen frequently leads to misread handwriting, misplaced or soiled paper chits, forgotten recipe modifiers, and delayed food and drink preparation. Furthermore, calculating customer bills, applying 20% statutory discounts for senior citizens and persons with disabilities (PWD), and recording cash or GCash payments by hand is time-consuming and prone to human arithmetic error.

In addition, managing table seating manually causes customer dissatisfaction and lost revenue. Without an online reservation portal, patrons encounter unpredictable wait times upon arrival, while verbally reserved tables often sit empty when customers arrive late, lacking a structured policy to release unoccupied seats. Crucially, tracking raw baking ingredients and coffee supplies manually prevents management from accurately determining production capacity. The establishment lacks a computerized feature to calculate the exact amount of finished products that can be produced based on current ingredient stocks, leading to unexpected stockouts of signature menu items or costly ingredient spoilage.

To resolve these operational challenges, the researchers developed the Raeden's Resto, Bakery, Cafe POS System with Online Reservation and QR Code. The system provides dedicated, synchronized interfaces tailored to the establishment's workflow. Customers can access an online portal to view dining table and seating layouts, reserve tables, select pre-ordered meals and beverages, agree to a mandatory 15-minute seating reservation policy, and receive a dynamic QR booking pass. At the establishment, the staff verifies customer arrival and seating assignments through QR scanning, with automated alerts flagging tables unoccupied after 15 minutes so they can be released to waiting walk-in diners. At the front counter, the cashier uses a touch POS terminal to scan the customer's QR pass—instantly auto-fetching pre-ordered items into the sales cart for review, optional editing, and payment recording—as well as scanning printed product QR code tags for walk-in items, computing statutory discounts, and issuing thermal sales receipts. In the food and beverage preparation area, kitchen staff and baristas utilize a digital Kitchen Display System (KDS) screen to track live orders alongside recipe Bill of Materials (BOM) specifications, atomically deducting raw ingredients upon order completion. The system also equips administrators with a comprehensive inventory suite featuring stock vouchers, perpetual audit ledgers, daily sales analytics, and a Finished Product Yield Calculator that computes maximum realizable finished goods based on available raw ingredient stock and identifies production bottlenecks.

By deploying this integrated system, Raeden's Resto, Bakery, Cafe will successfully transition from an error-prone manual setup to an automated, paperless, and modern operating environment. The platform streamlines table seating and reservations, coordinates orders between cashiers and kitchen staff in real time, automates statutory discount and receipt computations, and provides precise yield-driven inventory control to prevent ingredient stockouts and eliminate operational delays.


## Objectives of the Study

The main objective of the study was to develop and evaluate a Point of Sale (POS) and online reservation system with QR code for Raeden's Resto, Bakery, Cafe.

Specifically, the study aimed to:

1. Design and develop a centralized database for managing user accounts, products, reservations, orders, payments, and inventory records.

2. Implement role-based access for the administrator, cashier, barista, and customer.

3. Develop an online reservation and pre-ordering portal that allows customers to view availability and receive QR-based booking passes.

4. Develop a POS module that supports product QR code scanning, order processing, payment recording, discount computation, and receipt generation.

5. Develop a Kitchen Display System (KDS) for displaying and updating beverage and food order statuses.

6. Implement inventory monitoring and sales reporting functions.

7. Evaluate the system using the applicable ISO/IEC 25010 software product quality characteristics.


## Conceptual Framework

The conceptual framework of this study was developed using the Input-Process-Output (IPO) model, together with a continuous software quality evaluation feedback loop. Figure 1 shows the conceptual framework of the study.

As shown in Figure 1, the framework illustrates the systematic flow of information through the system for Raeden's Resto, Bakery, Cafe. The input stage encompasses the operational data required to drive the establishment: user account credentials for authorized staff (Administrator, Cashier, Barista/Kitchen Staff, and Staff), with customers accessing the public online reservation portal directly without account registration; product catalog and menu data incorporating two-dimensional (2D) Quick Response (QR) codes, selling prices, recipe Bill of Materials (BOM), and dish modifiers; the number of tables and chairs (including dining tables, chairs, and cafe booth seating); online reservation bookings with agreed 15-minute seating holding policy terms; and financial transaction parameters (cash tender, GCash references, and statutory discount credentials).


> **Figure 1. Conceptual Framework of the Study**

The process stage represents the core functional modules executed by the system: staff authentication and role-based access validation, optical QR code auto-reading for product classification, customer QR pass scanning for seating verification at the staff desk and automated pre-order retrieval at the POS counter, cart calculations with statutory and customer discounting (including 20% senior citizen and PWD statutory discount and 10% student discount), live electronic order routing to the barista Kitchen Display System (KDS), atomic inventory stock deduction upon drink fulfillment, and sales report logging.

The output stage yields the fully operational Raeden's Resto, Bakery, Cafe POS System with Online Reservation and QR Code. Below the primary processing pipeline, the system's evaluation stage incorporates the ISO/IEC 25010 software product quality standard as a solid feedback loop, providing empirical quality metrics to guide continuous refinement and operational optimization.


## Scope and Limitations


### Scope

The scope of the study focuses on the design, development, and evaluation of an integrated web-based Point of Sale (POS), online table reservation, and inventory management system engineered specifically for Raeden's Resto, Bakery, Cafe. The software encompasses five interconnected operational modules tailored to distinct user roles: a public online reservation and pre-ordering portal for customers, a staff desk for guest seating verification and 15-minute grace period management, a touch-enabled front-counter POS terminal for cashiers, a real-time Kitchen Display System (KDS) for kitchen and barista personnel, and an administrative management suite for restaurant owners and supervisors featuring sales auditing and a Finished Product Yield Calculator.

The customer portal enables diners to inspect real-time seating availability across designated dining zones (Main Dining Hall, Bakery Counter, Cafe Lounge, and Outdoor Patio), book a dining table or booth seat, pre-order meals and beverages with specific modifiers, agree to the mandatory 15-minute reservation grace policy, and receive an encrypted dynamic QR pass for seamless arrival check-in. The staff module allows floor attendants to scan customer QR passes to verify reservations, confirm on-premises arrival, and monitor 15-minute expiration timers to release no-show tables to walk-in patrons. The front-counter cashier module features optical 2D QR code auto-reading for rapid product entry, QR pass scanning to auto-fetch pre-orders into the sales cart with full editing and walk-in modification support, statutory discount computation (comprising a 20% discount for senior citizens and persons with disabilities [PWD] pursuant to Republic Act Nos. 9994 and 10754), cash tender and GCash payment recording, thermal sales receipt printing, and transaction auditing. The kitchen operations module provides a dedicated real-time KDS screen with color-coded preparation timers for tracking meal and beverage fulfillment, triggering perpetual inventory deduction upon order serving. The administrative module provides user account administration, product pricing, recipe Bill of Materials (BOM) setup, printable menu QR code generation, sales analytics, and a Finished Product Yield Calculator that computes maximum batch yields from available raw ingredients and identifies bottleneck stock.


### Limitations

The operational scope of the Raeden's Resto, Bakery, Cafe POS System is subject to several practical limitations. First, while the system provides structured recording of digital payments, it does not interface directly with external banking gateway APIs; electronic payments conducted via mobile wallets such as GCash require manual verification of transaction reference numbers by the cashier. Additionally, the system operates as a responsive web-based platform accessible through standard mobile and desktop browsers rather than a native downloadable app store application, and all booking confirmations and digital passes are accessed directly via web interface without automated cellular SMS delivery.

Furthermore, the software is engineered exclusively for on-premises dining, table reservations, and counter takeout at Raeden's Resto, Bakery, Cafe, and does not integrate with external third-party food delivery aggregators (such as GrabFood or FoodPanda). Physical table allocation and guest check-in rely on staff QR verification rather than automated electronic turnstiles. Finally, system availability depends upon the establishment's local area network and continuous electrical power supply, requiring uninterruptible power supply (UPS) backup to maintain operation during commercial power fluctuations.


## Significance of the Study

The development and implementation of this system will benefit various stakeholders of the commercial food service, bakery, and dining industry:

Raeden's Business Owners and Management. The system provides restaurant, bakery, and cafe owners and supervisors with centralized operational oversight of user accounts, menu pricing, printable product QR tags, perpetual inventory tracking, finished product yield forecasting, and daily sales audit logs. This enables management to maximize operational profitability, eliminate ingredient spoilage and stockouts, and formulate data-driven business decisions based on accurate sales analytics.

Frontline Cashiers. The system provides a touch-enabled POS terminal equipped with optical 2D QR auto-reading for rapid product lookup and 1-click pre-order cart auto-fetching from customer QR booking passes. This eliminates manual calculation of bills, change, and 20% senior citizen and PWD statutory discounts, reduces queue congestion during peak dining hours, and generates official thermal receipts accurately.

Staff and Floor Attendants. The system provides a dedicated seating check-in module featuring optical QR scanning and automated 15-minute reservation grace period alerts. Floor personnel can instantly verify guest attendance, display assigned dining table numbers, and systematically release unattended tables back to available status for walk-in diners, optimizing seating occupancy.

Kitchen Staff and Baristas. The system replaces handwritten paper chits and shouted verbal orders with a paperless, real-time Kitchen Display System (KDS) screen equipped with audible arrival chimes, color-coded countdown timers, and exact recipe Bill of Materials (BOM) specifications. This eliminates misplaced or soiled order slips, ensures strict recipe compliance, and coordinates beverage and culinary preparation seamlessly.

Customers (Diners and Patrons). The system provides an accessible, self-service online reservation portal where patrons can inspect real-time dining room seating layouts, reserve tables, customize food and beverage pre-orders, and receive an encrypted dynamic QR pass without needing an account login, delivering a transparent and convenient dining experience.

Future Researchers. The study provides a documented empirical architecture and evaluation reference for designing integrated web-based Point of Sale, online seating reservation, optical 2D matrix auto-reading, and recipe Bill of Materials yield calculation platforms in the hospitality, food service, and commercial dining sectors.


## Definition of Terms

For a better understanding of the terms used in this study, the following technical and operational terms are conceptually and operationally defined:

&nbsp;&nbsp;&nbsp;&nbsp;*Barista and Kitchen Staff.* A skilled beverage preparation specialist and culinary worker responsible for crafting coffee, teas, specialty drinks, and baked food items.

&nbsp;&nbsp;&nbsp;&nbsp;*In this study,* it refers to the authorized kitchen user role operating the real-time Kitchen Display System (KDS) interface in Raeden's Resto, Bakery, Cafe to monitor incoming orders, prepare items following recipe Bill of Materials (BOM) formulations, update preparation progress, and atomically trigger inventory stock deductions upon order fulfillment.

&nbsp;&nbsp;&nbsp;&nbsp;*Bill of Materials (BOM).* A comprehensive list of raw materials, ingredients, components, and assemblies required to construct, manufacture, or produce a finished product.

&nbsp;&nbsp;&nbsp;&nbsp;*In this study,* it refers to the structured recipe formulation assigned to each beverage and bakery menu item in the database, defining the exact required quantities and units of measure of coffee beans, syrups, dairy, sugar, and packaging materials consumed per serving.

&nbsp;&nbsp;&nbsp;&nbsp;*Bottleneck Ingredient Analysis.* The computational identification of the single raw ingredient or packaging material that reaches exhaustion first, constraining maximum finished product yield.

&nbsp;&nbsp;&nbsp;&nbsp;*In this study,* it refers to the diagnostic capability within the Finished Product Yield Calculator of Raeden's Resto, Bakery, Cafe that flags the specific low-stock ingredient limiting the production batch of bakery items and handcrafted beverages.

&nbsp;&nbsp;&nbsp;&nbsp;*Brew-to-Serve Inventory Deduction.* A perpetual inventory management model wherein raw materials, ingredients, and packaging supplies are deducted from available stock only upon the physical production and dispensation of finished goods, rather than at the initial point of sale.

&nbsp;&nbsp;&nbsp;&nbsp;*In this study,* it refers to the automated database routine in Raeden's Resto, Bakery, Cafe that decrements raw ingredient and packaging balances when the kitchen staff or barista advances an order to Ready or Served on the Kitchen Display System (KDS), preventing inventory discrepancies caused by order cancellations or modifications.

&nbsp;&nbsp;&nbsp;&nbsp;*Cashier.* A retail service worker responsible for greeting patrons, tabulating purchased items, calculating payable amounts, receiving payments, and issuing official transaction receipts.

&nbsp;&nbsp;&nbsp;&nbsp;*In this study,* it refers to the frontline staff role operating the touch-enabled Point of Sale (POS) terminal at the front counter of Raeden's Resto, Bakery, Cafe to scan product QR codes, scan customer QR booking passes to auto-fetch pre-ordered items into an editable sales cart, compute statutory discounts, receive cash or GCash payments, and print 80mm thermal receipts.

&nbsp;&nbsp;&nbsp;&nbsp;*Customer Classification.* The systematic categorization of dining patrons into distinct customer tiers to determine applicable statutory discounts, member privileges, and verification credentials.

&nbsp;&nbsp;&nbsp;&nbsp;*In this study,* it refers to the selection options in the booking portal and POS checkout at Raeden's Resto, Bakery, Cafe categorized into Regular Diners, Senior Citizens/PWDs, Students, and VIP Members.

&nbsp;&nbsp;&nbsp;&nbsp;*Dining Zones and Table Map.* A visual, spatial organization of physical seating units and guest furniture within a commercial dining establishment divided by atmosphere and purpose.

&nbsp;&nbsp;&nbsp;&nbsp;*In this study,* it refers to the four distinct spatial sections configured within Raeden's Resto, Bakery, Cafe (Main Dining Hall, Bakery Counter, Cafe Lounge, and Outdoor Patio), rendered interactively on the customer reservation portal and staff dashboard to represent real-time table availability.

&nbsp;&nbsp;&nbsp;&nbsp;*Dynamic QR Pass.* A time-sensitive, machine-readable two-dimensional matrix barcode containing cryptographically encoded identification, scheduling, and authorization metadata.

&nbsp;&nbsp;&nbsp;&nbsp;*In this study,* it refers to the unique, system-generated 2D QR booking pass issued to customers upon completing an online table reservation, presented on their smartphone to the staff upon arrival for seating verification and to the cashier for instantaneous pre-order retrieval into the POS register.

&nbsp;&nbsp;&nbsp;&nbsp;*Finished Product Yield Calculator.* A computational inventory optimization mechanism that determines the maximum possible output of finished products that can be produced based on available raw material stocks and standardized component formulas.

&nbsp;&nbsp;&nbsp;&nbsp;*In this study,* it refers to the administrative decision-support module that evaluates current raw baking and beverage ingredient quantities against Bill of Materials (BOM) requirements to compute the exact number of servings Raeden's Resto, Bakery, Cafe can prepare, highlighting the specific bottleneck ingredient that limits further production.

&nbsp;&nbsp;&nbsp;&nbsp;*Kitchen Display System (KDS).* A digital kitchen communication and order management screen that visually organizes food and beverage orders in real time, replacing handwritten paper tickets and shouted verbal orders.

&nbsp;&nbsp;&nbsp;&nbsp;*In this study,* it refers to the synchronized, web-based touch monitor in the food and beverage preparation area of Raeden's Resto, Bakery, Cafe displaying chronological order tickets, preparation countdown timers, acoustic alert chimes, recipe modifier tags, and one-touch status transition controls.

&nbsp;&nbsp;&nbsp;&nbsp;*Online Reservation Portal.* An interactive, web-based scheduling platform that enables prospective customers to inspect facility availability, select designated seating, and submit booking requests remotely prior to physical arrival.

&nbsp;&nbsp;&nbsp;&nbsp;*In this study,* it refers to the public, responsive web interface that allows patrons of Raeden's Resto, Bakery, Cafe to view live table and booth seating availability across dining zones, book tables without requiring an account login, select pre-ordered menu items, agree to the mandatory 15-minute seating policy, and obtain a QR booking pass.

&nbsp;&nbsp;&nbsp;&nbsp;*Perpetual Inventory Tracking.* A continuous, real-time inventory management method that immediately updates stock balances following every acquisition, consumption, or physical adjustment without waiting for periodic physical audits.

&nbsp;&nbsp;&nbsp;&nbsp;*In this study,* it refers to the automated inventory ledger in Raeden's Resto, Bakery, Cafe that decrements ingredient and packaging quantities as orders are fulfilled and increments balances upon recording stock-in deliveries.

&nbsp;&nbsp;&nbsp;&nbsp;*Point of Sale (POS) System.* An integrated hardware and software solution utilized at retail checkout counters to aggregate sales items, record commercial transactions, calculate taxes and discounts, and process payments.

&nbsp;&nbsp;&nbsp;&nbsp;*In this study,* it refers to the touch-optimized counter terminal used by cashiers in Raeden's Resto, Bakery, Cafe featuring optical 2D barcode scanning, pre-order cart auto-fetching with walk-in item adjustments, 20% senior citizen and PWD discount computation, sales audit logging, and thermal receipt printing.

&nbsp;&nbsp;&nbsp;&nbsp;*Product QR Code Classification and Scanning.* The automated optical scanning and decoding of two-dimensional matrix symbols conforming to ISO/IEC 18004 standards to retrieve catalog data and item prices without manual data entry.

&nbsp;&nbsp;&nbsp;&nbsp;*In this study,* it refers to the generation of printable QR code tags for bakery and beverage items that can be optically read by the cashier's scanner or device camera to immediately populate the POS sales cart with the product name, unit price, size options, and recipe modifiers.

&nbsp;&nbsp;&nbsp;&nbsp;*Staff Floor Attendant.* A hospitality service employee responsible for welcoming arriving patrons, verifying scheduled reservations, coordinating dining table seatings, and maintaining floor order.

&nbsp;&nbsp;&nbsp;&nbsp;*In this study,* it refers to the authorized staff role operating the QR scanner terminal at Raeden's Resto, Bakery, Cafe to verify guest QR booking passes, confirm table occupancy, and monitor the 15-minute grace period to release unoccupied seats to walk-in diners.

&nbsp;&nbsp;&nbsp;&nbsp;*Statutory Discounts (Senior Citizen and PWD).* Legally mandated price reductions and value-added tax exemptions granted by government authority to qualified sectors of society upon presentation of valid identification credentials.

&nbsp;&nbsp;&nbsp;&nbsp;*In this study,* it refers to the automated 20% discount calculation and VAT exemption applied by the cashier POS terminal pursuant to Philippine Republic Act No. 9994 (Expanded Senior Citizens Act of 2010) and Republic Act No. 10754 (An Act Expanding the Benefits and Privileges of Persons with Disability), correctly adjusting the subtotal on official thermal receipts.

&nbsp;&nbsp;&nbsp;&nbsp;*Thermal Sales Receipt.* An officially formatted proof of purchase printed on heat-sensitive paper containing transaction timestamps, table codes, itemized purchases, applicable discounts, value-added tax breakdown, and cashier identification.

&nbsp;&nbsp;&nbsp;&nbsp;*In this study,* it refers to the 80mm sales docket generated by the cashier POS terminal at Raeden's Resto, Bakery, Cafe upon tender confirmation.

&nbsp;&nbsp;&nbsp;&nbsp;*15-Minute Seating Grace Period Policy.* A time-delimited facility reservation holding policy that guarantees designated space availability for a defined duration following the scheduled appointment, after which unclaimed reservations are forfeited.

&nbsp;&nbsp;&nbsp;&nbsp;*In this study,* it refers to the operational rule and system alert mechanism whereby a reserved table or booth seat at Raeden's Resto, Bakery, Cafe is held for exactly 15 minutes past the booking time; if the customer has not checked in onsite, the system alerts the staff to forfeit the reservation and release the table to available status for walk-in patrons.

# Chapter II
REVIEW OF RELATED LITERATURE AND STUDIES

This chapter presents a comprehensive review of related literature and published empirical studies that provided the theoretical, conceptual, and technical foundation for the development of the Raeden's Resto, Bakery, Cafe POS System with Online Reservation and QR Code. The first section examines authoritative literature discussing the advanced concepts and technical mechanisms of the system, including transactional data integrity in point of sale architectures, optical 2D matrix symbology, asynchronous kitchen display workflows, facility reservation and space allocation algorithms, role-based access control with separation of duties, and the ISO/IEC 25010 software quality evaluation model. The second section analyzes published empirical research studies, focusing on Philippine restaurant, bakery, cafe, and facility reservation implementations. A comparative matrix and synthesis highlighting the functional distinctions of the present study conclude the chapter.


## Related Literature


### ACID Transactional Integrity and Point of Sale Architecture

Coronel and Morris (2020), in Database Systems: Design, Implementation, & Management (13th ed., Cengage Learning), together with Laudon and Laudon (2022) in Management Information Systems: Managing the Digital Firm (17th ed., Pearson), underscore that enterprise retail and Point of Sale systems fundamentally depend upon the ACID model (Atomicity, Consistency, Isolation, and Durability) to maintain financial and inventory integrity. In multi-user hospitality environments where counter sales, seat bookings, and kitchen orders occur concurrently, atomic transactions guarantee that database operations—such as recording payments, issuing thermal receipts, and executing stock ledger updates—succeed completely or roll back entirely, thereby preventing orphaned records or cash-inventory discrepancies. The Raeden's system adopts this architectural principle by wrapping POS order logging, table occupancy changes, and Brew-to-Serve ingredient deductions in explicit PDO database transactions.

Coronel & Morris, 2020; Laudon & Laudon, 2022


### Role-Based Access Control in Multi-User Service Platforms

Ferraiolo, Kuhn, and Chandramouli (2020), in Role-Based Access Control (2nd ed., Artech House), emphasize that enterprise information systems serving multiple organizational tiers require strict Role-Based Access Control (RBAC) to enforce the principle of least privilege. Segregating privileges among administrative personnel, frontline service staff, kitchen operators, and external patrons safeguards sensitive business metrics, prevents unauthorized pricing alterations, and provides an auditable operational trail. In the Raeden's coffee shop system, RBAC is strictly enforced via server-side session middleware, ensuring that customers access only public seating and ordering views, cashiers operate restricted sales registers, baristas interact exclusively with kitchen queue controllers, and administrators manage menu pricing, printable QR tags, and inventory parameters.

Ferraiolo, Kuhn, & Chandramouli, 2020


### Two-Dimensional QR Matrix Symbology in Retail and Resource Management

The International Organization for Standardization and International Electrotechnical Commission (ISO/IEC 18004:2015), along with empirical analysis by Soon (2008), demonstrate that Quick Response (QR) matrix symbology provides significantly superior data density, error correction capability (up to 30% damage tolerance via Reed-Solomon coding), and 360-degree high-speed readability compared to legacy one-dimensional barcodes. These characteristics make QR codes uniquely suited for both mobile customer ticketing and optical retail classification. The Raeden's system leverages ISO/IEC 18004 standards in a dual capacity: generating printable 2D QR tags for instant product classification at the cashier counter, and generating dynamic, encrypted QR passes on customer smartphones for rapid check-in and pre-order retrieval.

ISO/IEC 18004:2015; Soon, 2008


### Kitchen Display Systems and Real-Time Service Queue Coordination

Davis and Stone (2020), writing in the International Journal of Hospitality Management, demonstrated that transitioning from traditional physical paper chits to digital Kitchen Display Systems (KDS) in commercial food and beverage operations dramatically mitigates order transcription errors, eliminates lost tickets, and reduces ticket fulfillment latency by providing real-time acoustic alerts, color-coded urgency queues, and exact recipe modifier displays. Furthermore, modern KDS architectures allow decoupling POS payment from stock consumption, enabling perpetual inventory deduction only when items are physically prepared and served. The Raeden's system implements this pattern through its dark-mode Barista KDS, which presents incoming order queues with sound chimes, recipe bills of materials, and automated Brew-to-Serve inventory deductions upon drink preparation.

Davis & Stone, 2020


### Prototyping Methodology in Applied Software Engineering

Pressman and Maxim (2020), in Software Engineering: A Practitioner's Approach (9th ed., McGraw-Hill), assert that the Prototyping SDLC model is the optimal paradigm when developing specialized software from the ground up for novel operational environments where end-user requirements evolve through hands-on interaction. By constructing iterative functional prototypes, presenting them to operational stakeholders, and refining features based on direct user evaluations, development teams eliminate requirements ambiguity and ensure high usability and organizational alignment prior to final operational deployment. This methodology directly guided the phased development of the Raeden's Resto, Bakery, Cafe system across requirements gathering, quick design, prototyping, evaluation, refinement, testing, and operational deployment.

Pressman & Maxim, 2020


## Related Studies

Vega & Generoso (2021) — Hospitality Facility Scheduling

Vega and Generoso (2021), published in the Asia Pacific Journal of Academic Research in Business Administration, investigated the transition of facility scheduling from paper logbooks to an automated web platform. Their empirical findings demonstrated that manual booking caused frequent scheduling collisions and poor administrative visibility, whereas web-based booking eliminated double-bookings and significantly enhanced space utilization. Their study directly informs the table and seating reservation architecture of the Raeden's Resto, Bakery, Cafe system.

Vega & Generoso, 2021


### Intal et al. (2021) — QR Code Table Ordering in Casual Dining

Intal et al. (2021), published in the International Journal of Research and Innovation in Social Science, developed and evaluated a web-based restaurant management platform incorporating QR code ordering in casual dining establishments in the Philippines. Utilizing the SERVPERF framework, the authors found that digital QR ordering significantly reduced order-taking mistakes and shortened counter checkout delays during operational rush hours. These findings support the optical QR scanning and pre-ordering mechanisms of the Raeden's coffee shop system.

Intal et al., 2021

Capuyan et al. (2022) — Automated Online Reservation for Hospitality Services

Capuyan et al. (2022), published in the International Journal of Advanced Research in Computer Science, designed and evaluated an automated online reservation system for hospitality facilities using the Technology Acceptance Model (TAM). Their findings showed that digital reservation management received exceptional acceptance scores from managers and guests, eliminating scheduling confusion and providing transparent auditing of facility usage. Their work reinforces the online seating reservation and staff check-in modules implemented in the present study.

Capuyan et al., 2022


### Ramos, Bautista, & Flores (2023) — Automated Ordering and Kitchen Display Queue

Ramos, Bautista, and Flores (2023) conducted an empirical research study in Western Visayas, Philippines, designing and evaluating a food service management system with an integrated digital kitchen display screen. Their software replaced manual paper slips with a digital order board featuring status stages and sound alerts, achieving an overall composite mean of 4.62 out of 5.0 (Excellent) under ISO/IEC 25010 evaluation among IT specialists and food service staff. Their evaluation methodology and KDS workflow directly guide the barista operations and software quality assessment of the Raeden's system.

Ramos et al., 2023


### Mendoza & Garcia (2022) — Cashless POS and Mobile QR Code Integration

Mendoza and Garcia (2022), published in the Journal of Philippine Business and Hospitality Management, investigated the operational impact of integrating mobile QR payments (GCash and Maya) alongside cash transactions in micro and small food enterprises in the Philippines. Their survey-based empirical analysis determined that integrating QR e-wallet reference logging directly into POS software reduced cashier cash handling discrepancies by 42% and significantly sped up counter checkout lines. Their findings reinforce the dual payment processing architecture implemented in the Raeden's system.

Mendoza & Garcia, 2022


## Table of Comparison


> **Table 1 shows the comparison between previous related studies and the proposed Raeden's Resto, Bakery, Cafe POS System with Online Reservation and QR Code in terms of key system capabilities. Check marks (✔) indicate the presence of a feature, while cross marks (✖) indicate its absence.**


> **Table 1**

Comparison of Related Studies with the Proposed System


## Synthesis of the Study

A rigorous analysis of the reviewed literature and empirical studies reveals a distinct research and operational gap in existing food service and dining platforms. Previous investigations have typically addressed commercial point of sale, facility scheduling, and kitchen management in isolation. Traditional dining systems either focus purely on front-counter sales without reservation capabilities, manage table bookings without food and beverage ordering, or lack digital kitchen display coordination. Furthermore, conventional retail POS software enforces counter-level stock trapping, erroneously assuming that finished meals and beverages are pre-manufactured stock units rather than made-to-order artisanal dishes requiring recipe-based preparation. Prior to this research, no documented platform integrated customer table reservation with QR passes, optical 2D QR product classification, paperless kitchen queue dispatching, perpetual recipe-based stock control, and a Finished Product Yield Calculator within a unified restaurant, bakery, and cafe establishment.

Methodologically and architecturally, the proposed Raeden's Resto, Bakery, Cafe POS System diverges significantly from previous works. While earlier studies often relied on rigid waterfall models or generic third-party commercial software, this study employs the Prototyping Software Development Life Cycle (SDLC) Model. This iterative methodology enabled continuous design refinement based on hands-on evaluations by business managers, cashiers, baristas, kitchen staff, and dining patrons. Architecturally, the system decouples sales recording from inventory depletion through an automated 'Brew-to-Serve' mechanism. Frontline cashiers record orders, compute 20% statutory discounts for senior citizens and PWDs, and issue thermal receipts without altering ingredient balances; raw materials, packaging boxes, and cups are deducted only when kitchen staff or baristas physically prepare and serve the order on the KDS screen. Additionally, the system leverages ISO/IEC 18004 2D QR matrix symbology in a unified dual capacity—generating printable menu QR tags for instantaneous cashier cart auto-reading, and issuing encrypted dynamic QR passes on customer smartphones for rapid staff check-in and scheduled pre-order retrieval.

The primary contribution of this study is the delivery of a customized, cost-effective, and empirically validated operational management prototype developed specifically for Raeden's Resto, Bakery, Cafe to overcome its manual setup. By synthesizing online table booking, optical QR item scanning, acoustic kitchen signaling, perpetual ingredient tracking, and finished product yield calculation into a responsive Native PHP and MySQL architecture, the system provides a dependable operational blueprint for food service enterprises, demonstrating that modern web technologies and 2D matrix symbology can elevate operational efficiency, prevent ingredient stockouts and wastage, and enhance the dining customer experience.


# Chapter III
METHODOLOGY


## Project Description

The Raeden's Resto, Bakery, Cafe POS System with Online Reservation and QR Code is an integrated, web-based management platform engineered to modernize operations for Raeden's Resto, Bakery, Cafe. The system serves as a unified operational bridge connecting front-of-house customer table reservations, staff seating management, counter cashiering, kitchen and bakery fulfillment, and back-office administrative inventory oversight.

Through its responsive architecture, customers can access self-service seating reservations across designated dining zones (Main Dining Hall, Bakery Counter, Cafe Lounge, and Outdoor Patio), pre-order customized meals and beverages, and obtain an encrypted dynamic QR pass for seamless check-in. At the service counter, the cashier operates a touch-enabled POS terminal paired with an optical 2D scanner to read product QR tags for instant classification, apply statutory senior citizen and PWD discounts, record cash or GCash payments, and print thermal sales receipts. In the food and beverage preparation area, kitchen personnel and baristas utilize a paperless Kitchen Display System (KDS) monitor with audio alert chimes to inspect recipe bills of materials and advance tickets through preparation stages, automatically triggering Brew-to-Serve inventory deductions upon order fulfillment. Meanwhile, business managers utilize centralized dashboards to configure user accounts, adjust menu prices, generate printable product QR tags, monitor perpetual ingredient balances, audit daily sales revenue, and calculate finished product yield capacity based on available raw ingredients.


## Project Development Process

To develop the system specifically to modernize the manual setup at Raeden's Resto, Bakery, Cafe, the researchers adopted the Prototyping Software Development Life Cycle (SDLC) Model. The Prototyping Model is an iterative software engineering methodology that constructs early functional versions of the system, subjects them to direct stakeholder evaluation, and refines system features through continuous feedback cycles until all operational specifications are fulfilled. This model is exceptionally well-suited for commercial food service enterprises where workflow configurations, counter interactions, and kitchen coordination evolve through direct operational testing. Figure 2 illustrates the Prototyping SDLC Model implemented in this study.


> **Figure 2. Prototyping Software Development Life Cycle (SDLC) Model**


### Phase 1: Requirements Gathering

The researchers consulted with Raeden's Resto, Bakery, Cafe management, cashiers, baristas, kitchen personnel, and dining patrons to establish operational requirements for modernizing the establishment's manual setup. Key requirements identified included online reservation of dining tables and cafe seating with a 15-minute holding grace period, optical QR code product classification at the sales counter, paperless order coordination for kitchen and barista staff, statutory discounting (20% senior citizen and PWD), perpetual inventory monitoring, and a Finished Product Yield Calculator to evaluate recipe production capacities from raw ingredient stocks.


### Phase 2: Quick Design

The researchers formulated the conceptual architecture and database schema in MySQL, created detailed UML structural and behavioral models (System Architecture, Use Case, Activity, Class/ERD, and Sequence diagrams), and drafted user interface wireframes for the touch POS register, the seating floor map, and the barista kitchen screen. Specifications for the thermal receipt and dynamic QR pass layout were finalized during this phase.


### Phase 3: Prototype Development

A fully functional software prototype was constructed using Native PHP 8.x for server-side business logic and MySQL for relational data storage. Frontend interfaces were built using HTML5, CSS3, JavaScript, and Bootstrap 5.3, incorporating responsive layouts, an HTML5 optical camera and 2D QR barcode scanner, dynamic QR pass generation, and real-time status updating via Fetch API and polling.


### Phase 4: User Evaluation

The prototype was demonstrated to store cashiers, baristas, kitchen staff, restaurant supervisors, and dining patrons at Raeden's Resto, Bakery, Cafe. Evaluators tested table bookings, placed walk-in and pre-orders, scanned product QR codes, processed payments, and updated kitchen tickets, providing critical qualitative feedback regarding usability and interface responsiveness.


### Phase 5: Prototype Refinement

Based on evaluator feedback, the researchers executed iterative refinements: optimizing camera auto-focus for faster QR decoding, integrating audible alert chimes on the barista KDS screen, adding dedicated 20% senior/PWD, 10% student, and VIP/Member discount buttons, implementing POS QR pass scanning for automatic pre-order cart retrieval with editable walk-in adjustments, establishing the 15-minute seating reservation holding policy with staff overdue alerts, and perfecting Brew-to-Serve ingredient deductions upon drink fulfillment.


### Phase 6: System Testing

Comprehensive unit, integration, and security testing was executed across local network environments. Tests verified the mathematical accuracy of 12% VAT calculations and discount deductions, confirmed atomic transaction integrity across database tables, validated ingredient stock decrements upon order serve, and verified error-free direct thermal receipt printing.


### Phase 7: Deployment and Maintenance

The completed system was deployed on a local area network server at Raeden's Resto, Bakery, Cafe. Operational manuals and training orientations were provided to counter cashiers, floor attendants, and kitchen staff. Formal software quality evaluation was conducted in accordance with the ISO/IEC 25010 international standard.


## System Architecture and UML Diagrams

To describe the system structure, physical deployment, and process flows, the researchers utilized a realistic vector system architecture diagram and Unified Modeling Language (UML) models.


### System Architecture


> **Figure 3 shows the System Architecture of the application, structured across three operational tiers resting upon a unified institutional infrastructure base:**

• Client Tier: Presentation and hardware peripherals layer accommodating four distinct user stations: Customer Smartphone & Laptop (accessing the web booking portal and dynamic QR pass), Cashier POS Terminal (touchscreen terminal paired with a 2D QR scanner and thermal receipt printer), Barista KDS Display Screen (display monitor running the real-time Kitchen Display System), and Administrator PC Workstation (management computer for catalog administration, printable QR code tags, and reporting).

• Application Tier: Network gateway and business logic layer comprising the Wi-Fi Router / LAN Switch (routing local area network traffic) and a Web Server (PHP) executing application modules. Core modules handle role-based access control, online reservations, pre-order scheduling, QR pass tokenization, POS order processing, statutory discount computation, thermal receipt formatting, KDS queue dispatching, and inventory stock deductions.

• Data Tier: Relational database storage layer powered by a Database Server (MySQL), organizing application data across normalized relational tables with transactional integrity, preserving transaction logs, user credentials, recipe bills of materials, and perpetual inventory ledgers.


> **Figure 3. System Architecture Diagram of Raeden's Resto, Bakery, Cafe POS System**


### Use Case Diagrams

To model the functional interactions between the different actors and the system, Unified Modeling Language (UML) Use Case Diagrams were constructed separately for each user role. Each diagram defines the specific subsystem boundary, actor, and operational use cases accessible to that role.


> **Figure 4a illustrates the Use Case Diagram for the Customer role within the Customer Portal subsystem. The Customer actor interacts with four primary use cases: View Available Tables & Menu (browsing current seating layouts, dining zones, and item prices), Reserve Table or Cafe Seating (selecting desired seating and reservation time slots), Select Beverage Pre-Orders (choosing drinks, meals, and optional modifiers), and Receive QR-Based Booking Pass (generating an encrypted dynamic QR pass for staff validation and cashier counter retrieval).**


> **Figure 4a. Use Case Diagram for Customer Role**


> **Figure 4b illustrates the Use Case Diagram for the Cashier role within the Point of Sale (POS) Module subsystem. The Cashier actor executes four core operational use cases: Scan Product QR Code (rapid item entry using the 2D barcode scanner), Verify QR Booking Pass (retrieving and confirming customer reservations and pre-orders), Compute Discounts & Total (applying statutory and member discounts for senior citizens, PWDs, students, and VIP members), and Process Payment & Print Receipt (recording cash or GCash tender and generating 58mm thermal sales receipts).**


> **Figure 4b. Use Case Diagram for Cashier Role**


> **Figure 4c illustrates the Use Case Diagram for the Barista role within the Kitchen Display System (KDS) subsystem. The Barista actor interacts with four kitchen-centric use cases: View Order Queue on KDS (monitoring incoming orders with color-coded preparation timers), Inspect Recipe & Modifiers (viewing exact bill-of-materials ingredient proportions and cup specifications), Update Preparation Status (advancing orders across Pending, Brewing, Ready, and Served stages), and Trigger Stock Deduction (initiating automated inventory deductions upon drink fulfillment).**


> **Figure 4c. Use Case Diagram for Barista Role**


> **Figure 4d illustrates the Use Case Diagram for the Administrator role within the Management Module subsystem. The Administrator actor performs high-level governance through four use cases: Manage User Accounts (creating, updating, and assigning role permissions to staff), Manage Products & QR Codes (maintaining catalog pricing, recipe formulations, and generating printable 2D QR tags), Monitor Inventory Levels (tracking raw ingredient balances, cup stock, and reorder alerts), and Generate Sales Reports (producing daily revenue analytics, VAT summaries, and perpetual inventory audit trails).**


> **Figure 4d. Use Case Diagram for Administrator Role**


### Activity Diagrams

The dynamic operational workflows of the system are modeled using UML Activity Diagrams developed independently for each user role. Modeled after institutional execution and access standards, each diagram traces the flow of control—either from direct public access for customers or from credential authentication for authorized staff—through role-specific operational actions to final completion.


> **Figure 5a illustrates the Activity Diagram for the Customer role. The workflow begins at the solid black Start circle, proceeding directly into the public Customer Booking Interface without requiring user login or account registration. From the portal interface, execution forks into parallel activities: browsing available dining tables and cafe seating layouts, reserving a desired seat or table, selecting and customizing food and beverage pre-orders, and agreeing to the mandatory 15-minute seating reservation policy. Upon agreement and submission, the system generates a dynamic QR booking pass with scheduled table assignment details. The customer presents this QR pass to the staff for onsite seating check-in and to the cashier for POS order retrieval, before terminating cleanly at the bullseye final node.**


> **Figure 5a. Activity Diagram for Customer Role**


> **Figure 5b illustrates the Activity Diagram for the Cashier role. Following authentication and role validation, the cashier enters the POS Counter Workspace. The execution splits across core transaction activities: scanning product QR codes with the 2D scanner to populate the sales cart, scanning customer QR booking passes to retrieve verified pre-orders, applying statutory and member discounts (20% senior/PWD or 10% student) and calculating 12% VAT and net totals, and collecting payment tender (cash or GCash) to print the final thermal receipt. The concurrent activities synchronize at the join bar before reaching the final end node.**


> **Figure 5b. Activity Diagram for Cashier Role**


> **Figure 5c illustrates the Activity Diagram for the Barista role. Upon authentication, the barista accesses the Kitchen Display System (KDS) workspace. Active order tickets arrive on the screen, detailing exact recipe formulations and customer modifiers. The barista marks tickets into the Brewing stage during drink preparation, advances the status to Ready and Served upon completion, and triggers the Brew-to-Serve automated routine to decrement raw ingredient and cup inventory balances. The paths join at the synchronization bar and terminate at the final node.**


> **Figure 5c. Activity Diagram for Barista Role**


> **Figure 5d illustrates the Activity Diagram for the Administrator role. Following administrative login and authentication, the administrator gains access to the centralized management console. Execution forks into administrative governance streams: managing user accounts and staff role assignments, configuring products and recipes while generating printable 2D QR code menu tags, monitoring perpetual inventory stock balances and recording stock-in deliveries, and auditing financial reports and daily sales revenue. All paths merge at the join bar to terminate at the bullseye final node.**


> **Figure 5d. Activity Diagram for Administrator Role**


### Class Diagram and Database Schema


> **Figure 6 presents the Class Diagram and database schema of the system, comprising eleven interconnected entity classes in MySQL organized into three operational subsystems:**

1. Catalog, Recipes, and Inventory Subsystem: categories (menu classifications), products (unique product_code QR tags, names, base prices, availability), inventory_items & recipes (raw ingredients, cup stocks, bill of materials quantity per unit), and product_addons & sizes (extra toppings, milk options, and cup size price tiers).

2. Tables, Reservations, and Stock Ledger Subsystem: dining_tables (table and booth codes, seating zones, capacity, status), reservations (reservation numbers, unique qr_token hashes, time slots, check-in status), reservation_items (pre-ordered menu items and quantities), and inventory_ledger & txns (immutable audit trail of stock receipts, conversions, finished product yield evaluations, and Brew-to-Serve deductions).

3. Users, POS Orders, and Payments Subsystem: users (account credentials, role assignments, customer classification and ID numbers), orders (transaction numbers, table references, cashier IDs, order types, subtotal, statutory discount amounts, 12% VAT, net totals, preparation status), order_items (line items, cup sizes, modifiers, unit prices), and payments (order foreign keys, cash tender, change computations, GCash transaction reference codes).

Cardinality relationships (1 to many, 1 to 1, and 0..1 to 1) are strictly enforced via foreign key constraints, with product foreign key references linking products directly to order line items.


> **Figure 6. Class Diagram and Entity-Relationship Schema of the System**


### Sequence Diagrams

To capture the chronological, message-based interactions between users, user interfaces, application controllers, and the database, UML Sequence Diagrams were created separately for each user role. Each diagram details the lifelines, activation bars, synchronous request messages, and dashed return messages that govern transactions.


> **Figure 7a illustrates the Sequence Diagram for the Customer role, tracing interactions across four lifelines: Customer (Actor), Web Portal (Boundary), PHP Server (Control), and Database (Entity). The customer accesses the public online reservation portal directly without account authentication (Messages 1-4). In the booking and pre-ordering phase (Messages 5-10), the customer queries available tables and menus, selects a seating slot and beverage pre-orders, agrees to the mandatory 15-minute seating reservation policy, and submits the booking request. The PHP server validates the reservation, persists the records into the database, and returns a dynamic QR booking pass. Finally (Messages 11-12), the customer presents the QR booking pass onsite to the staff for seating verification and to the cashier for seamless POS counter order retrieval.**


> **Figure 7a. Sequence Diagram for Customer Role**


> **Figure 7b illustrates the Sequence Diagram for the Cashier role across five lifelines: Cashier (Actor), POS Terminal (Boundary), PHP Server (Control), Database (Entity), and Customer (Actor). After authentication (Messages 1–5), the cashier scans product QR codes or customer QR booking passes (Messages 6–11), prompting the server to retrieve item data, check packaging stock, and compute statutory discounts. The customer tenders cash or GCash payment (Messages 12–18), whereupon the transaction is committed to the database, dispatching the order to the kitchen and generating a printed thermal sales receipt.**


> **Figure 7b. Sequence Diagram for Cashier Role**


> **Figure 7c illustrates the Sequence Diagram for the Barista role across five lifelines: Barista (Actor), KDS Screen (Boundary), PHP Server (Control), Database (Entity), and Customer (Actor). The barista opens the KDS screen, which queries pending orders from the database and displays incoming tickets (Messages 1–5). The barista updates ticket status to Brewing (Messages 6–9), and upon completion marks the order as Ready and Served (Messages 10–16). This triggers the automated Brew-to-Serve routine to deduct ingredients and cups in the database and log inventory movements, concluding with drink service at the pickup counter.**


> **Figure 7c. Sequence Diagram for Barista Role**


> **Figure 7d illustrates the Sequence Diagram for the Administrator role across four lifelines: Administrator (Actor), Admin Console (Boundary), PHP Server (Control), and Database (Entity). Following administrative authentication (Messages 1–5), the administrator configures product details and recipes to generate printable 2D QR tags (Messages 6–11). The administrator queries perpetual inventory stock balances and audit ledgers (Messages 12–15), and generates comprehensive daily sales reports with computed VAT breakdowns (Messages 16–18).**


> **Figure 7d. Sequence Diagram for Administrator Role**


## Requirement Analysis

The system was developed and tested to run reliably under standard institutional computing environments. The hardware and software requirements needed to deploy and operate the system are detailed below:


### Hardware Requirements


> **Table 2**

Minimum Hardware Requirements


### Software Requirements


> **Table 3**

Required Software Specifications


## System Evaluation (ISO/IEC 25010)

To evaluate the technical quality, usability, and operational suitability of the Raeden's Resto, Bakery, Cafe POS System with Online Reservation and QR Code, the researchers utilized the ISO/IEC 25010 software product quality standard. The evaluation examined eight quality characteristics: functional suitability, performance efficiency, compatibility, usability, reliability, security, maintainability, and portability.

A 5-point Likert Scale was utilized to score each criterion in the evaluation survey. Table 4 presents the scoring scale, rating ranges, and their qualitative interpretations.


> **Table 4**

5-Point Likert Scale and Interpretation Range


## Data Gathering and Statistical Treatment

Evaluation data was gathered through direct demonstration and hands-on testing of the system by selected evaluators. Evaluators were purposively selected to represent both technical experts and domain end-users:

A total of ten (10) respondents were selected using purposive sampling to evaluate the system. Purposive sampling ensured that evaluators possessed relevant technical expertise and direct familiarity with food service, bakery, and cafe operations. The evaluators were categorized into two distinct panels:

1. IT Experts (5 respondents): Information Technology professionals, systems analysts, and software engineers who rigorously evaluated system architectural design, database normalization, barcode auto-read responsiveness, security enforcement, and source code maintainability.

2. Domain End-Users (5 respondents): Raeden's Resto, Bakery, Cafe management personnel, counter cashiers, baristas, kitchen staff, and dining patrons who evaluated interface usability, table reservation clarity, order customization, and overall operational readiness.

The statistical treatment utilized for analyzing the evaluation responses was the weighted mean. The mean score for each quality characteristic was computed using the formula:

Mean = (Sum of All Item Ratings) / (Total Number of Respondents)
