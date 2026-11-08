# -*- coding: utf-8 -*-
"""
Update manuscript_data.json with:
1. Coffee shop will be established inside existing Mini Hotel of ISAT U - DC (hotel already built, coffee shop yet to be established).
2. Cashier is also the barista (dual-role staffing model).
3. Significance of the study structured with italic stakeholder names.
"""
import json

def update_data():
    json_path = 'Documentation/manuscript_data.json'
    with open(json_path, 'r', encoding='utf-8') as f:
        data = json.load(f)

    # 1. FRONT MATTER
    fm = data['front_matter']
    fm['abstract'] = (
        "As part of its auxiliary services, Iloilo Science and Technology University - Dumangas Campus (ISAT U - DC) "
        "maintains an already built and operational Mini Hotel that caters to campus visitors, seminars, and university functions. "
        "To expand hospitality amenities and provide dedicated student study facilities, the administration is preparing to establish "
        "a modern campus coffee shop and collaborative study lounge from the ground up inside the existing Mini Hotel. "
        "The forthcoming coffee shop operates under a streamlined staffing model where the cashier also serves as the barista. "
        "In a manual environment, requiring a single staff member to handle customer billing, calculate change, maintain paper seat logs, "
        "and prepare espresso beverages causes severe operational bottlenecks, lost paper chits, and delayed service. "
        "To resolve these operational challenges and establish a modern technological foundation from day one, the researchers developed "
        "the ISAT U - DC Coffee Shop POS System with Online Reservation and QR Code. The system is a responsive web application built using "
        "PHP Laravel 10, MySQL 8.0, HTML5, Tailwind CSS, and JavaScript. It features an online customer portal where students, faculty, "
        "and hotel guests can view seating maps across four zones (Lounge, Study Hub, Main Hall, Terrace), reserve study pods with power outlets, "
        "pre-order beverages, and receive a digital QR pass; a touch cashier POS terminal with optical barcode and QR code auto-reading for "
        "rapid checkout; a real-time Barista Kitchen Display System (KDS) screen mounted at the brew bar to organize orders into live workflow "
        "columns without loose paper slips; and an administrative dashboard for automated sales auditing, discount tracking, and real-time "
        "inventory monitoring. Developed through the Prototyping Model, the system was evaluated by ten purposively selected evaluators "
        "(five IT experts and five domain users) using the ISO/IEC 25010 software quality standard. The evaluation yielded an Overall Grand "
        "Mean of 4.62, verbally interpreted as Excellent. The findings demonstrate that establishing an automated POS and reservation platform "
        "from the ground up effectively supports the dual-role cashier-barista, prevents lost kitchen tickets, and provides an organized, "
        "transparent operational model for the upcoming coffee shop inside the ISAT U - DC Mini Hotel."
    )

    # 2. CHAPTER 1
    c1 = data['chapter_1']
    c1['background_paras'] = [
        "In modern retail, hospitality, and educational institutions, Point of Sale (POS) and automated booking systems are essential for streamlining commercial transactions and managing customer traffic. In food and beverage operations, a computerized POS terminal accelerates checkout times, accurately calculates statutory and institutional discounts, and eliminates arithmetic discrepancies caused by manual calculation (Smith & Johnson, 2021). The integration of optical barcode and Quick Response (QR) code scanners enhances this operational efficiency by enabling staff to scan item labels directly, auto-reading product identifiers into the sales tray without manual data entry (Laudon & Laudon, 2022). Furthermore, web-based reservation platforms provide patrons with real-time visibility into facility seating, preventing overcrowding and ensuring orderly access to shared study amenities.",
        "At Iloilo Science and Technology University - Dumangas Campus (ISAT U - DC), located in Dumangas, Iloilo, the university maintains an already built and operational Mini Hotel. The facility is actively utilized for accommodating institutional guests, university dignitaries, hospitality training, and campus events. Under the university's auxiliary services, the campus administration is preparing to establish a new campus coffee shop and collaborative study lounge inside the existing Mini Hotel facility. Conceived as a modern hub for students, faculty members, and hotel guests, the planned coffee shop is designed to serve specialty espresso, cold beverages, pastries, and snacks, while offering dedicated dining tables and partitioned study pods equipped with electrical power outlets for academic work.",
        "Because the coffee shop inside the existing Mini Hotel is a prospective enterprise being established from the ground up, planning its operational setup is critical. In particular, the coffee shop will adopt a streamlined staffing model where the cashier also serves as the barista. In a traditional manual setting, requiring a single staff member to handle both counter transactions and beverage preparation leads to severe bottlenecks. If run manually, the cashier-barista would have to manually compute totals and discounts, record study pod bookings in a paper logbook, write orders on loose paper chits, and attempt to read handwritten notes while steaming milk or pulling espresso shots. During morning rushes and class breaks, loose paper slips easily get wet, lost, or misplaced on the counter, leading to delayed drinks, mistaken orders, and frustrated customers. Furthermore, manual seat management inside the hotel lounge often results in walk-in patrons occupying study pods that students had hoped to use.",
        "To prevent these operational bottlenecks before the new establishment commences commercial service, there is a compelling need to develop an automated management platform from the ground up. Rather than transitioning from an inefficient manual setup later, establishing a computerized system prior to launch ensures that the coffee shop inside the ISAT U - DC Mini Hotel operates with standardized, paperless, and transparent workflows from its very first day of service.",
        "To fulfill this objective, the researchers developed the ISAT U - DC Coffee Shop POS System with Online Reservation and QR Code. Built as an end-to-end web application, the system delivers tailored interfaces for each operational role. For counter sales, the POS terminal integrates barcode and QR code auto-read capabilities that detect scanned products instantly and add them to the cart with customizable cup sizes and sweetness levels. For facility management, an online customer portal displays an interactive floor map of study pods and dining tables inside the mini hotel, enabling students and hotel guests to reserve time slots, pre-order beverages, and obtain a secure digital QR pass. Upon arrival, the cashier-barista verifies the QR pass with an optical scanner, which automatically checks in the patron and routes pre-ordered drinks to a digital Kitchen Display System (KDS) mounted at the espresso bar. Behind the scenes, the system maintains real-time inventory deductions and provides comprehensive financial and sales reporting for campus administration.",
        "By developing this system from the ground up, ISAT U - DC establishes a technologically advanced operational model for the upcoming coffee shop inside the Mini Hotel. Counter transactions are accelerated through optical scanning, the dual-role cashier-barista is freed from managing loose paper tickets, study pods are systematically managed without scheduling conflicts, and auxiliary services administrators gain full financial and inventory transparency from day one."
    ]

    c1['general_objective'] = (
        "The primary objective of this study was to design, develop, implement, and evaluate the web-based "
        "ISAT U - DC Coffee Shop POS System with Online Reservation and QR Code from the ground up for the upcoming "
        "campus coffee shop situated inside the existing Mini Hotel of Iloilo Science and Technology University - Dumangas Campus (ISAT U - DC)."
    )

    c1['specific_objectives'] = [
        "design and create a MySQL relational database to securely store records of users, product categories, menu items, cup sizes, add-ons, dining tables, study pods, reservations, orders, and payments;",
        "manage user accounts with role-based access control for Administrator, Cashier-Barista (dual role), and Customer, including student and faculty discount verification (10% student/faculty discount and 20% PWD/Senior discount);",
        "provide a responsive customer online portal where students, faculty, and mini hotel guests can check seating availability, reserve study pods and dining tables, pre-order drinks, and generate a digital QR pass for check-in;",
        "implement a touch cashier POS terminal with optical barcode and QR code auto-read scanning that immediately identifies products and adds them to the cart, calculates cup sizes and sweetness levels, computes change, and prints 80mm sales receipts;",
        "integrate a Barista Kitchen Display System (KDS) screen mounted in the preparation area that shows incoming orders in real time with status updates (New, Brewing, Ready, and Served) to eliminate loose paper kitchen slips for the cashier-barista;",
        "generate sales summaries, daily revenue reports, and low stock inventory alerts for the coffee shop and mini hotel administrator; and",
        "evaluate the developed system using the ISO 25010 software quality standards in terms of functional suitability, performance efficiency, compatibility, usability, reliability, security, maintainability, and portability."
    ]

    c1['conceptual_framework_paras'][1] = (
        "The conceptual framework, illustrated in Figure 1, outlines the systematic transformation of data inputs into "
        "functional outputs for the upcoming coffee shop inside the ISAT U - DC Mini Hotel. The input stage encompasses the "
        "foundational data elements required to establish the coffee shop's digital operations from the ground up. These include "
        "user account credentials across administrative, cashier-barista, and customer roles, product registry specifications with "
        "optical barcodes and QR codes, dining table and study pod zone layouts inside the mini hotel, customer reservation bookings, "
        "counter order transactions, and tender payment records."
    )

    c1['scope_paras'][0] = (
        "This study focuses on designing, developing, and evaluating the ISAT U - DC Coffee Shop POS System with Online "
        "Reservation and QR Code, created from the ground up for the upcoming campus coffee shop and study hub situated inside "
        "the existing Mini Hotel at Iloilo Science and Technology University - Dumangas Campus in Dumangas, Iloilo."
    )
    c1['scope_paras'][1] = (
        "The system includes a Customer Online Reservation Portal that presents an interactive seating map of four distinct "
        "zones inside the mini hotel (Lounge, Study Hub, Main Hall, and Terrace). It allows students, faculty members, and "
        "hotel guests to book study pods or dining tables for designated time slots, pre-order beverages and food with customized "
        "sizes and sweetness levels, and generate an encrypted digital QR pass for rapid counter check-in."
    )
    c1['scope_paras'][2] = (
        "The system includes a Cashier Point of Sale (POS) Terminal tailored for the cashier-barista, featuring optical "
        "barcode and QR code auto-read scanning. Staff can scan physical product labels to immediately populate the sales cart "
        "without manual menu searching. The terminal supports drink modifiers (cup sizes, sweetness percentages, ice levels, "
        "extra add-ons), automatic calculation of student/faculty discounts (10%) and senior/PWD discounts (20%), 12% Value Added "
        "Tax (VAT) computation, cash change calculation, and ESC/POS thermal printing of 80mm sales receipts."
    )

    c1['limitations_paras'][5] = (
        "6. The application is scoped specifically for the upcoming coffee shop inside the ISAT U - DC Mini Hotel and "
        "does not connect to external multi-branch commissary or hotel property management reservation networks."
    )

    # 3. SIGNIFICANCE OF THE STUDY (Structured with separate Stakeholder and Description for italic rendering)
    c1['significance_items'] = [
        [
            "ISAT U - DC Campus Administration and Auxiliary Services",
            "The system provides a ready-to-deploy, turnkey automated platform for the newly established coffee shop inside the existing campus mini hotel, ensuring complete fiscal transparency, automated VAT and discount accounting, and empirical data on facility utilization from day one."
        ],
        [
            "Mini Hotel and Coffee Shop Management",
            "The system equips management with a centralized dashboard to track daily sales, analyze popular menu items, monitor inventory stocks in real time, and receive automatic alerts for depleted supplies, preventing stockouts during initial business operations within the hotel facility."
        ],
        [
            "Cashier-Barista (Dual-Role Staff)",
            "Because the coffee shop operates with a dual-role staffing model where the cashier is also the barista, the system significantly lightens operational multitasking. The optical barcode auto-reading and touch interface accelerate counter checkout, allowing the cashier-barista to swiftly complete sales and transition to beverage brewing. Meanwhile, the digital Kitchen Display System (KDS) mounted adjacent to the espresso bar displays incoming customization queues in real time, completely eliminating the need to handle and juggle loose paper slips while steaming milk or preparing espresso."
        ],
        [
            "Students and Faculty Members of ISAT U - DC",
            "Campus patrons gain a streamlined dining and study experience inside the mini hotel lounge, allowing them to verify table availability, reserve quiet study pods with power outlets in advance, and avoid lengthy counter lines during brief class breaks."
        ],
        [
            "Mini Hotel Guests and Campus Visitors",
            "Visitors and guests lodging at the ISAT U - DC Mini Hotel enjoy convenient access to quality refreshments and dedicated meeting or work tables, benefiting from prompt service, contactless QR check-ins, and accurate digital billing."
        ],
        [
            "Future Researchers",
            "This study provides a concrete architectural framework and empirical evaluation of establishing an integrated POS, online reservation, and digital kitchen display platform from the ground up for academic hospitality and auxiliary enterprises."
        ]
    ]

    # Also keep significance_paras as formatted list for backward compatibility
    c1['significance_paras'] = [
        f"{st}. {desc}" for st, desc in c1['significance_items']
    ]

    # 4. CHAPTER 3
    c3 = data['chapter_3']
    c3['project_description'] = [
        "The ISAT U - DC Coffee Shop POS System with Online Reservation and QR Code is an integrated web-based management platform developed from the ground up to establish the complete digital operational infrastructure for the upcoming campus coffee shop and study hub situated inside the existing Mini Hotel at Iloilo Science and Technology University - Dumangas Campus (ISAT U - DC), located in Dumangas, Iloilo.",
        "The system is engineered to serve three primary user roles within the campus mini hotel community:",
        "• Customer (Students, Faculty, and Mini Hotel Guests): Can access the responsive web portal from personal smartphones or laptops to inspect real-time seating availability across four zones inside the mini hotel (Lounge, Study Hub, Main Hall, and Terrace), book a study pod or dining table for an upcoming time slot, customize and pre-order food and beverages, and obtain an encrypted digital QR pass for rapid counter check-in.",
        "• Cashier-Barista (Dual-Role Staff): Operates the touch-enabled POS terminal at the service counter to process walk-in orders, scan product barcodes and QR codes for instantaneous cart auto-read, configure beverage sizes and sweetness levels, apply institutional student/faculty discounts (10%) and senior/PWD discounts (20%), compute cash tender and change, and generate 80mm thermal sales receipts. As the barista, the same staff member utilizes the adjacent dark-mode Kitchen Display System (KDS) screen at the brewing station to monitor active drink orders in real time, view specific preparation modifiers (sweetness percentages, ice levels, extra add-ons), hear audio chimes when new orders arrive, and advance ticket statuses through New, Brewing, Ready, and Served stages.",
        "• Administrator (Mini Hotel and Auxiliary Services Supervisor): Uses the managerial dashboard to oversee daily gross sales, examine best-selling beverages and food items, monitor real-time stock levels with automated low-stock warnings (under 10 items), inspect table reservation schedules, and manage user accounts.",
        "The system comprises six unified functional modules: (1) Customer Online Reservation and Pre-Order Portal; (2) Touch Cashier POS Terminal with Barcode Auto-Read; (3) Staff QR Scanner and Check-In Module; (4) Barista Kitchen Display System (KDS); (5) Real-Time Inventory and Stock Management Module; and (6) Administrative Sales Reporting and Audit Dashboard.",
        "The system is built using PHP 8.2 and the Laravel 10 framework for robust server-side architecture, MySQL 8.0 for relational data persistence, and HTML5, Tailwind CSS, and JavaScript for an intuitive and responsive client interface. The application operates within the campus Local Area Network (LAN) on an Apache web server, accessible across desktop computers, tablets, and smartphones without requiring external internet connectivity."
    ]

    c3['development_process']['phases'][0] = [
        "Requirements Gathering",
        "The researchers conducted comprehensive planning interviews with ISAT U - DC campus administrators, auxiliary services personnel, mini hotel management, and prospective student customers. The team inspected the existing Mini Hotel facility, surveyed the planned layout for the upcoming ground-up coffee shop, and analyzed the operational workflow required for a single staff member acting as both cashier and barista, compiling baseline specifications to design the digital system."
    ]

    with open(json_path, 'w', encoding='utf-8') as f:
        json.dump(data, f, indent=2, ensure_ascii=False)

    print("Updated manuscript_data.json with Mini Hotel context and cashier-barista dual role!")

if __name__ == "__main__":
    update_data()
