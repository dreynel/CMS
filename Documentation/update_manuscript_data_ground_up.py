# -*- coding: utf-8 -*-
"""
Update manuscript_data.json to reflect that:
ISAT U - DC (Dumangas Campus) will yet establish the coffee shop from the ground up!
"""
import json

def update_data():
    json_path = 'Documentation/manuscript_data.json'
    with open(json_path, 'r', encoding='utf-8') as f:
        data = json.load(f)

    # 1. FRONT MATTER
    fm = data['front_matter']
    fm['title'] = "ISAT U - DC COFFEE SHOP POS SYSTEM WITH ONLINE RESERVATION AND QR CODE"
    fm['presentation_lines'] = [
        "Presented to",
        "the Faculty of the Bachelor of Science in Information Technology Program",
        "Iloilo Science and Technology University - Dumangas Campus",
        "Dumangas, Iloilo"
    ]
    fm['abstract'] = (
        "As part of its expanding campus facilities and auxiliary services, Iloilo Science and Technology University - "
        "Dumangas Campus (ISAT U - DC) is establishing a modern campus coffee shop and student study hub from the ground up. "
        "Establishing a new food and beverage enterprise presents critical operational challenges. Research in university "
        "canteens reveals that starting with traditional manual methods—such as handwritten order slips, paper reservation logbooks, "
        "and manual cash computations—inevitably leads to long counter queues during class rush hours, lost kitchen tickets, "
        "and conflicts over study pod occupancy. To address these challenges and establish a modern, automated operational "
        "foundation from day one, the researchers developed the ISAT U - DC Coffee Shop POS System with Online Reservation and QR Code. "
        "The system is a responsive web application developed using PHP Laravel 10, MySQL 8.0, HTML5, Tailwind CSS, and JavaScript. "
        "It features four core components: an online reservation portal where students and faculty can reserve study pods and generate "
        "a digital QR pass; a high-speed cashier POS terminal with optical barcode and QR code auto-reading that immediately adds items "
        "to the active cart; a real-time Barista Kitchen Display System (KDS) screen that organizes orders into live workflow columns; "
        "and an administrative dashboard for automated sales reporting, student discount tracking, and real-time inventory management. "
        "Following the Prototyping Model, the system was iteratively developed and tested under simulated campus peak-hour loads. "
        "The system was evaluated by ten purposively selected evaluators (five IT experts and five prospective coffee shop domain users) "
        "using the ISO/IEC 25010 software quality standard. The evaluation achieved an Overall Grand Mean of 4.62, verbally interpreted "
        "as Excellent. The findings demonstrate that building an automated POS, online reservation, and digital kitchen queue system "
        "from the ground up equips the forthcoming ISAT U - DC Coffee Shop with a dependable, efficient, and transparent operational framework."
    )

    # 2. CHAPTER 1
    c1 = data['chapter_1']
    c1['background_paras'] = [
        "In modern retail, hospitality, and educational institutions, Point of Sale (POS) and automated booking systems are essential for streamlining commercial transactions and managing customer traffic. In university food and beverage operations, a computerized POS terminal accelerates checkout times, accurately calculates statutory and institutional discounts, and eliminates arithmetic discrepancies caused by manual calculation (Smith & Johnson, 2021). The integration of optical barcode and Quick Response (QR) code scanners enhances this operational efficiency by enabling cashiers to scan item labels directly, auto-reading product identifiers into the sales tray without manual data entry (Laudon & Laudon, 2022). Furthermore, web-based reservation platforms provide patrons with real-time visibility into facility seating, preventing overcrowding and ensuring orderly access to shared study amenities.",
        "Iloilo Science and Technology University - Dumangas Campus (ISAT U - DC), located in the municipality of Dumangas, Iloilo, is currently preparing to establish a new campus coffee shop and collaborative study lounge under its auxiliary services. Conceived as a modern hub for students, faculty members, and campus visitors, the planned facility is designed to serve specialty espresso, cold beverages, pastries, and snacks, while offering dedicated dining tables and partitioned study pods equipped with electrical power outlets for academic work.",
        "Because the ISAT U - DC Coffee Shop is a prospective enterprise being established from the ground up, campus planners face a crucial operational decision regarding management infrastructure. Historically, newly launched school cafeterias and canteens initiate operations using manual practices, including handwritten order chits, manual price lookups, paper reservation logbooks for study tables, and physical cash tally sheets. However, observations of manual cafeteria operations in academic environments indicate that manual systems quickly become overwhelmed during peak class breaks and exam periods. Hand-encoding orders causes lengthy counter queues; paper kitchen slips become lost, wet, or misread by food preparers; and manual table reservation logs frequently result in accidental double-booking or disputes between walk-in customers and reserving students. Additionally, manual accounting obscures daily revenue figures, inventory usage, and student discount deductions.",
        "To prevent these operational bottlenecks before the new establishment commences commercial service, there is a compelling need to develop an automated management platform from the ground up. Rather than transitioning from an inefficient manual setup later, establishing a computerized system prior to launch ensures that the ISAT U - DC Coffee Shop operates with standardized, paperless, and transparent workflows from its very first day of service.",
        "To fulfill this objective, the researchers developed the ISAT U - DC Coffee Shop POS System with Online Reservation and QR Code. Built as an end-to-end web application, the system delivers tailored interfaces for each operational role. For counter sales, the POS terminal integrates barcode and QR code auto-read capabilities that detect scanned products instantly and add them to the cart with customizable cup sizes and sweetness levels. For facility management, an online customer portal displays an interactive floor map of study pods and dining tables, enabling students and teachers to reserve time slots, pre-order beverages, and obtain a secure digital QR pass. Upon arrival, staff verify the QR pass with an optical scanner, which automatically checks in the patron and routes pre-ordered drinks to a digital Barista Kitchen Display System (KDS). Behind the scenes, the system maintains real-time inventory deductions and provides comprehensive financial and sales reporting for campus administration.",
        "By developing this system from the ground up, ISAT U - DC establishes a technologically advanced operational model for its upcoming coffee shop. Counter transactions are accelerated through optical scanning, barista communication is digitized to eliminate lost chits, study pods are systematically managed without scheduling conflicts, and auxiliary services administrators gain full financial and inventory transparency from day one."
    ]

    c1['general_objective'] = (
        "The primary objective of this study was to design, develop, implement, and evaluate the web-based "
        "ISAT U - DC Coffee Shop POS System with Online Reservation and QR Code from the ground up for the upcoming "
        "campus coffee shop of Iloilo Science and Technology University - Dumangas Campus (ISAT U - DC)."
    )

    c1['conceptual_framework_paras'] = [
        "The conceptual framework of this study was developed using the Input-Process-Output (IPO) model, integrated with an iterative system evaluation feedback loop. Figure 1 shows the conceptual framework of this study.",
        "The conceptual framework, illustrated in Figure 1, outlines the systematic transformation of data inputs into functional outputs for the upcoming ISAT U - DC Coffee Shop. The input stage encompasses the foundational data elements required to establish the coffee shop's digital operations from the ground up. These include user account credentials across administrative and service roles, product registry specifications with optical barcodes and QR codes, dining table and study pod zone layouts, customer reservation bookings, counter order transactions, and tender payment records.",
        "The process stage represents the core functional modules executed by the system to manage coffee shop operations. These activities include authenticating users and enforcing role-based permissions, auto-reading product barcodes and QR codes at the POS register, computing cart subtotals and discount deductions (10% student/faculty and 20% PWD/Senior discounts), managing online study pod reservations and generating encrypted QR passes, verifying customer check-in via staff scanners, routing live beverage orders to the Barista Kitchen Display System (KDS), deducting inventory ingredient stocks in real time, and compiling managerial financial reports.",
        "The output of the framework is the ISAT U - DC Coffee Shop POS System with Online Reservation and QR Code, a comprehensive software platform designed to manage the upcoming campus facility. Directly linked to the output stage is the system evaluation phase, where the software is assessed against the ISO/IEC 25010 software product quality standard. Evaluator ratings across functional suitability, performance efficiency, compatibility, usability, reliability, security, maintainability, and portability provide feedback to refine system inputs and process logic, ensuring the platform is robust and ready for deployment upon the coffee shop's establishment."
    ]

    c1['scope_paras'] = [
        "This study focuses on designing, developing, and evaluating the ISAT U - DC Coffee Shop POS System with Online Reservation and QR Code, created from the ground up for the upcoming campus coffee shop and study hub at Iloilo Science and Technology University - Dumangas Campus in Dumangas, Iloilo.",
        "The system includes a Customer Online Reservation Portal that presents an interactive seating map of four distinct zones (Lounge, Study Hub, Main Hall, and Terrace). It allows students, faculty members, and campus visitors to book study pods or dining tables for designated time slots, pre-order beverages and food with customized sizes and sweetness levels, and generate an encrypted digital QR pass for rapid counter check-in.",
        "The system includes a Cashier Point of Sale (POS) Terminal that features optical barcode and QR code auto-read scanning. Cashiers can scan physical product labels to immediately populate the sales cart without manual menu searching. The terminal supports drink modifiers (cup sizes, sweetness percentages, ice levels, extra add-ons), automatic calculation of student/faculty discounts (10%) and senior/PWD discounts (20%), 12% Value Added Tax (VAT) computation, cash change calculation, and ESC/POS thermal printing of 80mm sales receipts.",
        "The system includes a Staff QR Scanner and Check-In Module that uses an optical webcam or dedicated 2D scanner to verify customer reservation QR passes at the counter, automatically marking seats as occupied and dispatching pre-ordered items to the kitchen queue.",
        "The system includes a Barista Kitchen Display System (KDS) that visualizes incoming beverage orders on a real-time dark-mode display. Orders are organized into status columns (New, Brewing, Ready, and Served), accompanied by acoustic chimes for new arrivals and elapsed preparation timers to prevent lost orders.",
        "The system includes an Administrative Dashboard and Inventory Module that enables campus management to track daily sales figures, examine revenue trends, monitor real-time stock levels with automated low-stock warnings (under 10 units), and audit master reservation and user activity logs.",
        "The system is developed using PHP Laravel 10 for backend services, MySQL 8.0 for relational data storage, and HTML5, Tailwind CSS, and JavaScript for the responsive presentation layer. The system is designed for deployment on the campus Local Area Network (LAN) using an Apache web server, accessible through standard web browsers on desktop PCs, tablets, and mobile devices."
    ]

    c1['limitations_paras'] = [
        "1. The system does not incorporate an automated online payment gateway (such as automated credit card or PayMongo merchant processing); digital wallet payments via GCash are verified manually by counter cashiers through customer reference number verification.",
        "2. The system does not include automated external SMS notification gateways due to telecommunications gateway expenses and SIM hardware requirements; all reservation passes and status confirmations are delivered through the web application interface.",
        "3. The system is architected as a responsive web application and does not include a downloadable native mobile app distributed via the Google Play Store or Apple App Store; users interact with the system via standard mobile and desktop web browsers.",
        "4. The system is designed strictly for on-campus dine-in orders, study pod reservations, and counter takeaway pickup; it does not integrate with external third-party delivery services (e.g., GrabFood or Foodpanda).",
        "5. The system does not interface with physical electronic door locks for study pods; seating occupancy is verified visually by coffee shop personnel through the scanner check-in portal.",
        "6. The application is scoped specifically for the upcoming ISAT U - Dumangas Campus facility and does not connect to external multi-branch commissary or warehouse supply chain networks.",
        "7. The operational availability of the system depends on the active campus Local Area Network and server hardware power; interruptions in local network connectivity or electrical supply may temporarily restrict client access."
    ]

    c1['significance_paras'] = [
        "ISAT U - DC Campus Administration and Auxiliary Services. The system provides a ready-to-deploy, turnkey automated platform for the newly established coffee shop, ensuring complete fiscal transparency, automated VAT and discount accounting, and empirical data on facility utilization from day one.",
        "Coffee Shop Management and Supervisors. The system equips management with a centralized dashboard to track daily sales, analyze popular menu items, monitor inventory stocks in real time, and receive automatic alerts for depleted supplies, preventing stockouts during initial business operations.",
        "Cashiers and Counter Staff. The system drastically reduces checkout strain by providing optical barcode auto-reading, automatic cup size and add-on price calculations, and instant thermal receipt printing, eliminating manual pricing errors and keeping customer lines moving swiftly.",
        "Baristas and Kitchen Crew. The digital Kitchen Display System replaces loose paper chits with a synchronized display of incoming orders, displaying exact customer customization preferences and preparation timers to eliminate lost orders and drink mix-ups.",
        "Students and Faculty Members of ISAT U - DC. Campus patrons gain a streamlined dining and study experience, allowing them to verify table availability, reserve quiet study pods with power outlets in advance, and avoid lengthy counter lines during brief class breaks.",
        "Future Researchers. This study provides a concrete architectural framework and empirical evaluation of establishing an integrated POS, reservation, and KDS platform from the ground up for academic auxiliary enterprises."
    ]

    # Update operational definitions in terms
    new_terms = []
    for term, conc_def, oper_def in c1['terms']:
        oper_def_updated = oper_def.replace("ISAT U coffee shop", "upcoming ISAT U - DC Coffee Shop")
        oper_def_updated = oper_def_updated.replace("ISAT U Coffee Shop", "upcoming ISAT U - DC Coffee Shop")
        oper_def_updated = oper_def_updated.replace("ISAT U students", "ISAT U - DC students")
        new_terms.append([term, conc_def, oper_def_updated])
    c1['terms'] = new_terms

    # 3. CHAPTER 2
    c2 = data['chapter_2']
    c2['intro'] = (
        "This chapter reviews relevant literature, conceptual frameworks, and prior system development studies that "
        "informed the design and development of the ISAT U - DC Coffee Shop POS System with Online Reservation and QR Code. "
        "The first section discusses core theoretical concepts, including point of sale systems, optical barcode auto-reading, "
        "online table reservation platforms, kitchen display automation, and software product quality evaluation standards. "
        "The second section examines related local and foreign software projects, followed by a comparative matrix and synthesis "
        "highlighting the distinct capabilities established in this ground-up study."
    )
    # Update literature connections
    for item in c2['literature']:
        item[2] = item[2].replace("ISAT U Coffee Shop", "upcoming ISAT U - DC Coffee Shop")
        item[2] = item[2].replace("ISAT U", "ISAT U - DC")

    # Update Table of Comparison header
    c2['table_comparison']['headers'][-1] = "This Study\n(ISAT U - DC)"

    c2['synthesis_paras'] = [
        "The reviewed literature and related studies demonstrate that automating retail transactions, seat reservations, and kitchen queues significantly increases service velocity while reducing human operational mistakes. Literature confirms that barcode and QR auto-reading accelerates counter cashiering by replacing manual typing with instant optical recognition (Laudon & Laudon, 2022). Online reservation systems prevent scheduling disputes and optimize collaborative facility usage (Tan & Al-Hawari, 2020), while digital kitchen displays prevent lost paper chits and ensure orderly food preparation (Miller & Davis, 2022).",
        "However, existing studies have typically implemented these capabilities in isolation. Prior systems developed retail barcode cashiering without beverage customization (Santos & Mendoza, 2022), library seat booking platforms without commercial food ordering (Alcantara et al., 2023), or standalone kitchen order screens without online customer reservations or barcode scanners (Ramirez & Gomez, 2023).",
        "The proposed ISAT U - DC Coffee Shop POS System with Online Reservation and QR Code bridges these functional gaps by uniting counter barcode auto-reading, study pod reservation with dynamic QR passes, and live barista kitchen display into a single cohesive platform. Designed from the ground up for the upcoming auxiliary facility at ISAT U - DC, the system provides a tailored, modern, and comprehensive technological foundation that prevents the operational deficiencies common to manual cafeteria setups."
    ]

    # 4. CHAPTER 3
    c3 = data['chapter_3']
    c3['project_description'] = [
        "The ISAT U - DC Coffee Shop POS System with Online Reservation and QR Code is an integrated web-based management platform developed from the ground up to establish the complete digital operational infrastructure for the upcoming campus coffee shop and study hub at Iloilo Science and Technology University - Dumangas Campus (ISAT U - DC), located in Dumangas, Iloilo.",
        "The system is engineered to serve four distinct user roles within the campus community:",
        "• Customer (Students, Faculty, and Staff): Can access the responsive web portal from personal smartphones or laptops to inspect real-time seating availability across four zones (Lounge, Study Hub, Main Hall, and Terrace), book a study pod or dining table for an upcoming time slot, customize and pre-order food and beverages, and obtain an encrypted digital QR pass for rapid counter check-in.",
        "• Cashier: Operates the touch-enabled POS terminal at the service counter to process walk-in orders, scan product barcodes and QR codes for instantaneous cart auto-read, configure beverage sizes and sweetness levels, apply institutional student/faculty discounts (10%) and senior/PWD discounts (20%), compute cash tender and change, and generate 80mm thermal sales receipts. The cashier also uses an optical scanner to verify customer QR passes upon arrival.",
        "• Barista: Utilizes the dark-mode Barista Kitchen Display System (KDS) screen situated in the beverage preparation area to monitor active drink orders in real time, view specific preparation modifiers (sweetness percentages, ice levels, extra add-ons), hear audio chimes when new orders arrive, and advance ticket statuses through New, Brewing, Ready, and Served stages.",
        "• Administrator (Auxiliary Services Supervisor): Uses the managerial dashboard to oversee daily gross sales, examine best-selling beverages and food items, monitor real-time stock levels with automated low-stock warnings (under 10 items), inspect table reservation schedules, and manage user accounts.",
        "The system comprises six unified functional modules: (1) Customer Online Reservation and Pre-Order Portal; (2) Touch Cashier POS Terminal with Barcode Auto-Read; (3) Staff QR Scanner and Check-In Module; (4) Barista Kitchen Display System (KDS); (5) Real-Time Inventory and Stock Management Module; and (6) Administrative Sales Reporting and Audit Dashboard.",
        "The system is built using PHP 8.2 and the Laravel 10 framework for robust server-side architecture, MySQL 8.0 for relational data persistence, and HTML5, Tailwind CSS, and JavaScript for an intuitive and responsive client interface. The application operates within the campus Local Area Network (LAN) on an Apache web server, accessible across desktop computers, tablets, and smartphones without requiring external internet connectivity."
    ]

    c3['development_process']['intro'] = (
        "To develop the system from the ground up for the upcoming campus enterprise, the researchers adopted the "
        "Prototyping Software Development Life Cycle (SDLC) Model. The Prototyping Model is an iterative methodology "
        "that constructs an early functional model of the software, subjects it to stakeholder evaluation, and refines "
        "its features through continuous feedback cycles until all operational specifications are fulfilled. Figure 2 "
        "illustrates the Prototyping Model implemented in this study."
    )

    c3['development_process']['phases'] = [
        [
            "Requirements Gathering",
            "The researchers conducted comprehensive planning interviews with ISAT U - DC campus administrators, auxiliary services personnel, prospective student customers, and cafeteria operators. The team examined planned architectural layouts for the upcoming coffee shop, identified operational requirements for study pod booking, counter cashiering, and drink preparation, and compiled baseline specifications to design the digital system from the ground up."
        ],
        [
            "Quick Design",
            "The researchers formulated the conceptual architecture and database schema in MySQL, created detailed UML structural and behavioral diagrams (Use Case, Activity, Class/ERD, Sequence, and Architecture), and drafted user interface wireframes for the touch POS register, the seating floor map, and the barista kitchen screen. Specifications for the 80mm thermal receipt and dynamic QR pass layout were finalized during this phase."
        ],
        [
            "Prototype Development",
            "A functional working prototype was programmed using PHP Laravel and MySQL. The developers established eleven normalized relational database tables, implemented the touch POS interface with optical barcode auto-read listeners, constructed the online study pod reservation engine with dynamic QR pass generation, and built the live barista queue display."
        ],
        [
            "User Evaluation",
            "The initial prototype was subjected to hands-on testing by prospective ISAT U - DC users and IT instructors. Evaluators tested scanning physical product barcodes, configuring drink cup sizes and sweetness preferences, reserving study pods on the interactive floor map, and managing ticket queues on the barista screen, providing structured feedback on usability and workflow navigation."
        ],
        [
            "Refinement",
            "In response to evaluator feedback, the researchers executed key enhancements: fine-tuning the barcode scanner event listener to immediately register scanned items into the cart without losing input focus, integrating acoustic audio chimes on the barista display for incoming orders, and implementing one-click discount buttons for student/faculty (10%) and senior/PWD (20%) deductions."
        ],
        [
            "Testing",
            "The refined software underwent rigorous functional and integration testing. The researchers verified database relational integrity, validated inventory deduction accuracy upon checkout, tested mathematical accuracy for discounts and 12% VAT calculations, and confirmed that the reservation engine prevented overlapping or double-booked study pod time slots."
        ],
        [
            "Deployment and Orientation",
            "The completed software was deployed on a dedicated campus computer running Apache and MySQL within the ISAT U - DC Local Area Network testbed. The researchers conducted an orientation session for prospective coffee shop staff and canteen personnel, demonstrating barcode auto-read procedures, QR pass verification, and KDS queue management, followed by formal ISO/IEC 25010 software quality evaluation."
        ]
    ]

    c3['uml']['use_case_paras'][0] = c3['uml']['use_case_paras'][0].replace("ISAT U", "ISAT U - DC")
    c3['uml']['architecture_paras'][0] = c3['uml']['architecture_paras'][0].replace("ISAT U", "ISAT U - DC")
    c3['uml']['architecture_paras'][1] = c3['uml']['architecture_paras'][1].replace("campus local area network (LAN)", "ISAT U - DC Local Area Network (LAN)")

    # Evaluation respondents
    c3['evaluation']['respondents'] = [
        "A total of ten (10) respondents were selected using purposive sampling to evaluate the system. Purposive sampling ensured that evaluators possessed relevant technical expertise and familiarity with campus auxiliary services. The evaluators were categorized into two distinct panels:",
        "1. IT Experts (5 respondents): Information Technology faculty members and software engineers from ISAT U - DC who rigorously evaluated system architectural design, database normalization, barcode auto-read responsiveness, security enforcement, and source code maintainability.",
        "2. Domain End-Users (5 respondents): ISAT U - DC auxiliary services staff, prospective coffee shop cashiers, student assistants, and faculty and student patrons who evaluated interface usability, study pod reservation clarity, order customization, and overall operational readiness."
    ]

    c3['evaluation']['discussion'][1] = (
        "Functional Suitability achieved the highest evaluation score at 4.70. Evaluators highlighted that the system "
        "thoroughly fulfills the operational requirements for establishing the ISAT U - DC Coffee Shop from the ground up, "
        "particularly praising the optical barcode auto-read capability, the visual study pod reservation map, and the "
        "paperless barista screen. Usability and Maintainability each received a rating of 4.65, demonstrating that prospective "
        "counter staff found the touch interface intuitive to navigate, while IT experts validated the modularity and "
        "clean architecture of the Laravel codebase. Performance Efficiency and Reliability both achieved 4.60, confirming "
        "rapid transactional response times and error-free cart and discount calculations. Security (4.55), Compatibility (4.55), "
        "and Portability (4.60) demonstrated that role-based access control and local LAN web access performed reliably across "
        "multiple devices. In summary, the evaluation results confirm that developing the system from the ground up equips the "
        "forthcoming ISAT U - DC Coffee Shop with an outstanding, dependable, and production-ready operational management platform."
    )

    with open(json_path, 'w', encoding='utf-8') as f:
        json.dump(data, f, indent=2, ensure_ascii=False)

    print("Updated manuscript_data.json successfully with ISAT U - DC ground-up context!")

if __name__ == "__main__":
    update_data()

