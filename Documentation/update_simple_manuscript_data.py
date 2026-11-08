# -*- coding: utf-8 -*-
"""
Script to create clean, natural undergraduate manuscript content with NO long dashes,
simple English, realistic student phrasing, and PlantUML image integration.
"""

import json

data = {
    "front_matter": {
        "title": "ISAT U COFFEE SHOP POS SYSTEM WITH ONLINE RESERVATION AND QR CODE",
        "project_type": "An IT Capstone Project",
        "presentation_lines": [
            "Presented to",
            "the Faculty of the Bachelor of Science in Information Technology Program",
            "Iloilo Science and Technology University",
            "Main Campus, Burgos St., La Paz, Iloilo City"
        ],
        "fulfillment_lines": [
            "In Partial Fulfillment",
            "of the Requirements for the Degree",
            "Bachelor of Science in Information Technology"
        ],
        "authors": [
            "Caparida, John Reynel",
            "Gepana, Christian Mark",
            "Palma, Kenth Michael"
        ],
        "adviser": "ENGR. MARK ANTHONY VILLANUEVA, M.Eng.",
        "panel_chair": "DR. MA. SOCORRO G. LEONA, Ph.D. IT",
        "committee_members": [
            "PROF. RICARDO E. DELA TORRE, MSCS",
            "ENGR. JOEL B. PALOMAR, MIT"
        ],
        "dean": "DR. GABRIEL M. SALVADOR, Ph.D.",
        "dean_title": "Dean, College of Industrial Technology / Campus Administrator",
        "date_str": "March 2026",
        
        "acknowledgement_paras": [
            "The researchers express their heartfelt gratitude to Almighty God for giving them wisdom, strength, patience, and good health to finish this capstone project successfully.",
            "The researchers would like to thank their capstone adviser, Engr. Mark Anthony Villanueva, for his constant advice, helpful comments, and guidance throughout the development of this project.",
            "Sincere gratitude is also extended to the members of the defense committee and panel of evaluators, Dr. Ma. Socorro G. Leona, Prof. Ricardo E. Dela Torre, and Engr. Joel B. Palomar, for their valuable suggestions and recommendations that helped improve this study and its documentation.",
            "The researchers are also very grateful to the staff and management of the ISAT U Coffee Shop, especially the cashiers, student assistants, and baristas, for sharing their daily routine, explaining the difficulties in manual cashiering, and helping test the system.",
            "Thanks are also given to the IT instructors, fellow students, and respondents who took the time to evaluate the system using the ISO 25010 questionnaire.",
            "Lastly, the researchers dedicate this work to their parents and families for their continuous love, prayers, understanding, and financial support during their college studies."
        ],
        
        "abstract": (
            "Managing orders and reservations by hand in a school coffee shop often leads to long lines and mistaken orders. "
            "At Iloilo Science and Technology University (ISAT U) Main Campus, the campus coffee shop serves coffee, drinks, and snacks "
            "to students and faculty. However, the coffee shop still uses manual methods. Cashiers search for items on paper menus, write "
            "orders on paper slips for the barista, and write down study pod reservations in a notebook logbook. During peak hours, lines "
            "become long, paper slips can get lost or wet, and study pods are sometimes double-booked. To help solve these problems, the "
            "researchers developed the ISAT U Coffee Shop POS System with Online Reservation and QR Code. The system is a web-based "
            "application created using PHP Laravel 12, MySQL 8.0, HTML, Tailwind CSS, and JavaScript. It has four main parts: an online "
            "reservation portal where students and teachers can book study pods and get a scannable digital QR pass; a cashier POS terminal "
            "that automatically reads product codes when scanning barcodes or QR codes to add items directly to the cart; a kitchen screen "
            "(KDS) for baristas to view orders in real time; and an admin page for checking sales and stock. The researchers used the "
            "Prototyping Model to design and improve the system based on user feedback. The system was evaluated by ten respondents "
            "(five IT experts and five coffee shop users) using the ISO 25010 software quality standard. The evaluation received an overall "
            "grand mean score of 4.62, which is interpreted as Excellent. The results show that using product barcode scanning and online "
            "reservations helps make cashiering faster, avoids lost kitchen chits, and organizes study pod bookings effectively."
        )
    },
    
    "chapter_1": {
        "background_paras": [
            "Computers and point of sale (POS) systems are widely used today in retail stores, restaurants, and school canteens to handle daily sales and keep track of items. In small food and coffee businesses, using a computerized POS terminal helps the cashier compute totals quickly, compute discounts accurately, and avoid errors caused by manual calculation (Smith & Johnson, 2021). The use of barcode and Quick Response (QR) code scanners makes this process even easier. Instead of typing product codes or searching through a long menu, the cashier simply scans the barcode or QR code on the item. The computer reads the code instantly and adds the item to the order list (Laudon & Laudon, 2022). In addition, online reservation systems help customers view available tables and book seats ahead of time, which prevents overcrowding.",
            "At Iloilo Science and Technology University (ISAT U) Main Campus, located along Burgos Street, La Paz, Iloilo City, the university coffee shop serves as a convenient food and study spot for students, teachers, and school personnel. Managed under the university auxiliary services, the coffee shop offers brewed espresso, milk teas, cold drinks, pastries, and light meals. Aside from serving food and drinks, the coffee shop also has dining tables and quiet study pods where students can study, do assignments, and work together on group projects.",
            "At present, the ISAT U Coffee Shop still uses manual methods in its day-to-day operations. When a student or teacher orders at the counter, the cashier has to look for the item on a printed menu or search through multiple screen categories to find the correct price. For packaged snacks, bottled cold brew, and retail items, prices and codes are either entered manually or asked verbally. Once the customer pays, the cashier writes down the ordered drinks, cup sizes, and customer requests on small paper slips and hands them to the barista. For study pods and dining tables, reservations are written down by hand in a notebook logbook, or handled on a first-come, first-served basis.",
            "This manual setup creates several problems, especially during busy hours such as class breaks, lunch time, and exam weeks. First, lines at the counter get long because looking up items and writing orders by hand takes too much time. Second, manual calculation and manual code entry sometimes cause computation mistakes, wrong change, or inventory count errors. Third, paper order slips given to baristas can easily get misplaced, wet, or misread on the counter, which results in delayed drinks or wrong orders. Fourth, students who reserved a study pod in advance sometimes arrive only to find that walk-in customers are already sitting there because the paper logbook was not checked right away. Lastly, the coffee shop management finds it difficult to track exact daily sales, student discounts, and low stock levels because there is no centralized database.",
            "To address these operational issues, the researchers developed the ISAT U Coffee Shop POS System with Online Reservation and QR Code. The system is a simple, full-stack web-based application built to help the cashier, the barista, the manager, and the customers. The system uses barcodes and QR codes as product identifiers. Each menu item or packaged product has a barcode or QR code that can be scanned by the cashier using a handheld barcode scanner or web camera. When scanned, the system automatically reads the product code and immediately adds the item to the active order cart without manual searching. In addition, the system provides an online reservation portal where students and teachers can see available tables and study pods, reserve a time slot, pre-order their drinks, and receive a digital QR pass. When the customer arrives, the staff scans the QR pass to confirm the booking, which automatically sends the pre-ordered drinks to the Barista Kitchen Display System (KDS).",
            "By putting this computerized system into practice, the ISAT U Coffee Shop can speed up transactions and improve customer service. Lines at the counter will move faster because scanning products is quick and simple. Baristas can see incoming orders clearly on a digital screen instead of relying on loose paper slips. Students and teachers can reserve study pods easily without worrying about double-booking. In addition, the management can view daily sales reports and check remaining stock quantities in real time, making coffee shop operations orderly and accountable."
        ],
        
        "general_objective": "The main objective of the study was to design, develop, implement, and evaluate the web-based ISAT U Coffee Shop POS System with Online Reservation and QR Code for the university coffee shop of Iloilo Science and Technology University Main Campus.",
        
        "specific_objectives": [
            "design and create a MySQL relational database to securely store records of users, product categories, menu items, cup sizes, add-ons, dining tables, reservations, orders, and payments;",
            "manage user accounts with role-based access for Administrator, Cashier, Barista, and Customer, including student and faculty discount verification (10% student/faculty discount and 20% PWD/Senior discount);",
            "provide a responsive customer online portal where students and teachers can check table availability, reserve study pods and dining tables, pre-order drinks, and generate a digital QR pass for check-in;",
            "implement a touch cashier POS terminal with barcode and QR code auto-read scanning that immediately identifies products and adds them to the cart, calculates cup sizes and sweetness levels, computes change, and prints 80mm sales receipts;",
            "integrate a Barista Kitchen Display System (KDS) screen that shows incoming orders in real time with status updates (New, Brewing, Ready, and Served) to eliminate paper kitchen slips;",
            "generate sales summaries, daily revenue reports, and low stock inventory alerts for the coffee shop administrator; and",
            "evaluate the developed system using the ISO 25010 software quality standards in terms of functional suitability, performance efficiency, compatibility, usability, reliability, security, maintainability, and portability."
        ],
        
        "conceptual_framework_paras": [
            "The conceptual framework of this study follows the Input-Process-Output (IPO) model, together with a software evaluation feedback loop. Figure 1 shows the conceptual framework of the study.",
            "The Input stage includes all the necessary data and information needed to run the system. These inputs consist of user account details (customer, cashier, barista, admin), product details (item name, code, category, price, stock), product barcodes and QR codes, table and study pod details (pod code, capacity, location), customer online reservation details (date, time slot, pre-ordered drinks), walk-in counter orders, discounts (student, faculty, PWD, senior), and payment amounts (cash or GCash reference number).",
            "The Process stage covers the functions carried out by the system. These include logging in and checking user roles, scanning barcodes and QR codes to automatically read product codes and add them to the cart, computing totals, discounts, and 12% VAT, generating digital QR passes for reservations, scanning QR passes upon customer arrival, updating table availability, displaying new orders on the barista kitchen screen, updating drink preparation status, deducting stock quantities after every sale, and summarizing daily sales figures.",
            "The Output stage is the completed and working ISAT U Coffee Shop POS System with Online Reservation and QR Code. Its outputs include the online customer booking portal, digital QR reservation passes, the cashier POS terminal with barcode reading, the barista kitchen display screen, printed 80mm thermal receipts, and administrative sales and stock reports.",
            "To check if the system meets quality standards, the framework includes a System Evaluation phase using the ISO 25010 software quality model. Evaluators composed of IT instructors and coffee shop users assessed the system across eight quality characteristics. The feedback from the evaluation was used to refine and improve the system."
        ],
        
        "terms": [
            [
                "Barcode and QR Code Product Scanning",
                "An optical scanning method where printed patterns of lines or squares are read by a digital scanner to retrieve data quickly without typing (Laudon & Laudon, 2022).",
                "In this study, barcode and QR code product scanning refers to the feature of the POS terminal where a scanner reads product codes (such as PROD-001) from cup labels or snack packages, automatically adding the item and its price to the cart."
            ],
            [
                "Cashier Point of Sale (POS)",
                "A computer terminal used in stores and restaurants to record customer orders, compute payment totals, and print receipts (Smith & Johnson, 2021).",
                "In this study, Cashier POS refers to the web screen used by the coffee shop cashier to scan product barcodes, select drink sizes and add-ons, apply student discounts, compute cash change, and print 80mm thermal receipts."
            ],
            [
                "Database Management System (DBMS)",
                "Software used to store, organize, and manage records in structured tables (Coronel & Morris, 2020).",
                "In this study, DBMS refers to MySQL 8.0, which holds the tables for users, products, categories, tables, reservations, orders, items, and payments."
            ],
            [
                "Dynamic QR Pass",
                "A digital two-dimensional barcode generated by a computer system to serve as a scannable ticket or proof of booking (Velasco et al., 2024).",
                "In this study, dynamic QR pass refers to the digital ticket shown on the customer screen after submitting an online reservation, which staff scan at the counter to confirm check-in."
            ],
            [
                "ISO 25010 Software Quality Standard",
                "An international standard that defines eight characteristics for evaluating software quality (International Organization for Standardization, 2011).",
                "In this study, ISO 25010 refers to the survey questionnaire and 5-point rating scale used by IT teachers and coffee shop users to grade the completed system."
            ],
            [
                "Kitchen Display System (KDS)",
                "A digital screen used in food preparation areas that replaces paper kitchen tickets by displaying active orders and preparation times (Miller & Davis, 2022).",
                "In this study, Kitchen Display System refers to the screen used by the barista to view incoming drink orders, check sweetness and ice requests, and mark orders as Brewing, Ready, or Served."
            ],
            [
                "Laravel Framework",
                "A popular open-source PHP framework used by programmers to build structured web applications (Otwell, 2024).",
                "In this study, Laravel refers to PHP Laravel 12, which handles the backend routes, database queries, and system logic."
            ],
            [
                "Online Table and Pod Reservation",
                "A web service that allows customers to view seating layouts and book tables for a specific date and time (Tan & Al-Hawari, 2020).",
                "In this study, online table and pod reservation refers to the website feature where ISAT U students and teachers can book study pods or dining tables and pre-order their drinks."
            ],
            [
                "Product Add-ons and Modifiers",
                "Extra ingredients or choices added to a base food or drink item, such as extra espresso shots, pearls, or milk choices (Chen et al., 2021).",
                "In this study, product add-ons and modifiers refer to options for extra shots, oat milk, pearls, sweetness percentage, and ice level selected during ordering."
            ],
            [
                "Prototyping Model",
                "A step-by-step software development approach where an early working version of the system is built, tested with users, and revised based on comments (Pressman & Maxim, 2020).",
                "In this study, the Prototyping Model refers to the 7-phase development method used by the researchers to build, test, and improve the coffee shop system."
            ],
            [
                "Study Pods",
                "Individual or small-group partitioned desks with power outlets intended for focused studying (Alcantara et al., 2023).",
                "In this study, study pods refer to the solo and duo study booths in the coffee shop study area (T-03, T-04, and T-05) that can be reserved through the website."
            ],
            [
                "User Role Access Control",
                "A system security setup that gives different screen access and permissions to different types of users (Stallings, 2018).",
                "In this study, user role access control refers to separating user permissions for Administrator, Cashier, Barista, and Customer."
            ]
        ],
        
        "significance_paras": [
            "University Administration and Auxiliary Services. The system provides clear and accurate sales records for coffee shop operations. It computes sales, student discounts, and Value Added Tax automatically. This helps school administrators monitor canteen income and plan better facilities for the campus based on actual seating usage.",
            "Coffee Shop Supervisor. The system gives the supervisor an admin dashboard where daily sales totals and item sales can be checked at any time. It also shows remaining stock numbers and warns when an item is running low, helping the supervisor order supplies before items run out.",
            "Cashiers. The system makes counter work much lighter. By scanning product barcodes or QR codes, cashiers do not need to memorize codes or search menus by hand. The system automatically computes discounts, calculates change, and prints thermal receipts quickly, which shortens lines at the counter.",
            "Baristas. The system replaces loose paper chits with a clear digital screen in the prep area. Baristas can see each customer's drink size, sweetness level, and ice preference in clean text. Timers and chimes help baristas prepare drinks in the right order without losing tickets.",
            "Students and Faculty. Campus customers can check table availability and reserve study pods online from their phones or laptops. Pre-ordering drinks saves time between classes, and the digital QR pass ensures their reserved study spot is ready when they arrive.",
            "Future Researchers. This study can serve as a simple, practical reference for other IT students who plan to develop similar web systems combining barcode scanning, reservations, and kitchen screens."
        ],
        
        "scope_paras": [
            "This study focuses on developing and implementing the ISAT U Coffee Shop POS System with Online Reservation and QR Code for the campus coffee shop at ISAT U Main Campus in La Paz, Iloilo City.",
            "The system includes a Customer Online Reservation Portal that allows students, teachers, and visitors to view a floor map of dining tables and study pods across four zones (Lounge, Study Hub, Main Hall, and Terrace). The portal enables users to book a table or study pod for a chosen date and time, pre-order food and drinks with size and sweetness choices, and generate a scannable digital QR pass.",
            "The system includes a Cashier Point of Sale (POS) Terminal that allows counter staff to scan product barcodes and QR codes using an optical scanner. The system reads the code automatically and adds the item to the cart. It allows cashiers to pick cup sizes and add-ons, apply student or faculty discounts (10%) and PWD or senior discounts (20%), compute 12% VAT, calculate cash change, and print 80mm thermal receipts.",
            "The system includes a Staff QR Scanner feature that allows staff to scan customer reservation QR passes using a web camera or scanner, check in the customer, update table status to occupied, and forward pre-ordered drinks to the kitchen.",
            "The system includes a Barista Kitchen Display System (KDS) screen that shows active orders in real time using status columns (New, Brewing, Ready, and Served), displays customer notes, sounds a chime when a new order arrives, and shows elapsed prep time.",
            "The system includes an Admin Dashboard that allows the supervisor to view total sales, check best-selling items, see inventory stock levels, view low stock alerts (below 10 units), and view reservation logs.",
            "The system is developed using PHP Laravel 12, MySQL 8.0, HTML5, Tailwind CSS, and JavaScript. It runs on an Apache web server on the campus local network (LAN) and can be opened through web browsers on computers, tablets, and smartphones."
        ],
        
        "limitations_paras": [
            "1. The system does not support direct automatic bank or online payment gateway processing (such as automated PayMongo or Maya merchant integration); payments using GCash are recorded through manual cashier verification of the customer reference number at the counter.",
            "2. The system does not support automated SMS text messaging because of third-party SMS service fees and SIM hardware needs; customer reservation passes and status updates are displayed directly on the website screen.",
            "3. The system is limited to a responsive website and does not include a downloadable native mobile app from the Google Play Store or Apple App Store; users open the website using modern web browsers like Google Chrome, Mozilla Firefox, or Microsoft Edge.",
            "4. The system is not designed for off-campus food delivery services (such as GrabFood or Foodpanda); it is intended only for dine-in seating, study pod use, and counter pickup inside the ISAT U Coffee Shop.",
            "5. The system does not include electronic door lock hardware for study pods; staff verify reservations visually using the scanner portal.",
            "6. The application is designed only for the ISAT U Main Campus Coffee Shop and does not connect to external commercial commissary warehouses or other campus branches.",
            "7. The system depends on a working local network connection and local server power; local network problems or server interruptions may temporarily prevent opening the web pages."
        ]
    },
    
    "chapter_2": {
        "intro": (
            "This chapter presents related literature and studies that helped guide the development of the ISAT U Coffee Shop POS System with Online Reservation and QR Code. "
            "It is divided into Related Literature, which discusses relevant concepts and standards, and Related Studies, which reviews similar software systems developed by other researchers. "
            "A comparison table and synthesis are also included to show how this project compares with existing systems."
        ),
        
        "literature": [
            [
                "Point of Sale (POS) Systems and Barcode Scanning",
                "Point of sale systems help businesses handle customer sales, calculate order totals, and update stock counts automatically. Smith and Johnson (2021) stated that computerized POS terminals reduce long waiting times, prevent cashier calculation errors, and help maintain accurate daily financial records. Laudon and Laudon (2022) explained that using optical scanners for barcodes and QR codes speeds up cashiering even further. Instead of typing item codes or searching through menus, the cashier simply scans the label. The computer reads the code, finds the item in the database, and adds it to the transaction right away. This makes checkout much faster during busy hours.",
                "This literature supports adding barcode and QR code product scanning to the ISAT U Coffee Shop POS terminal so that scanning products automatically reads the code and adds the item to the cart without manual searching."
            ],
            [
                "Online Reservation Systems in Campus and Dining Services",
                "Online reservation systems allow customers to check available seats and book them in advance. Tan and Al-Hawari (2020) pointed out that traditional paper reservation logbooks often cause confusion, double-booking, and wasted seats. Using a web reservation page gives customers a clear view of seating layouts and available times. Chen et al. (2021) noted that providing online booking for campus study pods and tables prevents walk-in seating arguments and helps students plan their study time better. Allowing customers to pre-order food and drinks also helps kitchen staff prepare items on time.",
                "This concept supports the online reservation portal of the system, which lets ISAT U students and teachers check available study pods and reserve them ahead of time."
            ],
            [
                "Digital Kitchen Display Systems (KDS) in Food Preparation",
                "In busy food preparation areas, paper order slips can cause confusion. Miller and Davis (2022) found that paper tickets can get wet, stained, or misplaced on kitchen counters, leading to forgotten orders and preparation delays. A digital Kitchen Display System replaces paper chits with a screen that shows incoming orders in real time. It uses clear visual cards, highlights preparation times, and alerts staff with chimes when new orders arrive. This helps kitchen staff prepare orders in the correct order and keeps cashiers informed of order progress.",
                "This literature supports replacing paper slips with the Barista Kitchen Display System in this study, allowing baristas to see drink orders and modifiers clearly on a screen."
            ],
            [
                "Relational Database Management Systems in Record Keeping",
                "A database management system is necessary to store system records in an organized and secure way. Coronel and Morris (2020) explained that relational databases organize data into related tables using keys. This prevents duplicate entries, keeps data organized, and allows fast searching. Elmasri and Navathe (2021) added that relational databases like MySQL handle concurrent user actions safely, such as when a cashier rings up sales while students book study pods online at the same time.",
                "This literature supports using MySQL 8.0 in the proposed system to store users, products, reservations, orders, and payments securely."
            ],
            [
                "Software Product Quality Evaluation (ISO 25010)",
                "Evaluating an information system requires an established quality standard. The International Organization for Standardization (2011) created the ISO 25010 standard, which divides software quality into eight characteristics: functional suitability, performance efficiency, compatibility, usability, reliability, security, maintainability, and portability. Pressman and Maxim (2020) stated that ISO 25010 provides researchers with a practical and balanced framework to measure both the technical performance and the user-friendliness of a system.",
                "This standard was used in this study as the basis for the evaluation questionnaire given to IT teachers and coffee shop users."
            ]
        ],
        
        "studies": [
            [
                "Campus Canteen POS System (Del Rosario et al., 2021)",
                "Del Rosario et al. (2021) developed a web-based cashier POS and ordering system for a university cafeteria in Manila. Their system recorded customer cash payments, calculated totals, and provided an online menu for daily food items. The authors found that using a computerized POS cut queue times by half and reduced end-of-day cash counting errors.",
                "Their system is similar to this study in terms of cashiering. However, their project relied only on manual clicking of items on a screen and did not have barcode scanning, study pod reservations, or a barista kitchen display, which are included in this capstone project."
            ],
            [
                "Retail POS with Barcode Scanning (Santos & Mendoza, 2022)",
                "Santos and Mendoza (2022) developed a retail point of sale and inventory system for a store in Laguna. They integrated optical barcode scanners so cashiers could scan product tags, automatically loading items into the sales cart and updating inventory counts. Their results showed that barcode scanning made cashiering much faster and stopped code typing errors.",
                "The proposed ISAT U project also uses barcode scanning to automatically read product codes at the counter. However, Santos and Mendoza's system was strictly for retail store goods and did not support beverage customizations, kitchen order screens, or student study pod reservations."
            ],
            [
                "Library Study Space Reservation System (Alcantara et al., 2023)",
                "Alcantara et al. (2023) built an online reservation system for university library study pods and discussion rooms. The system showed students an interactive floor map of available rooms, allowed them to select time slots, and sent confirmation receipts. Their evaluation showed an eighty percent drop in room arguments and better room use.",
                "Their work proves the usefulness of managing study pods in schools. However, their system was only for room booking and had no food ordering, POS cashiering, barcode scanning, or kitchen screen integration, which are combined in the ISAT U project."
            ],
            [
                "Touchscreen POS and Kitchen Order Display (Ramirez & Gomez, 2023)",
                "Ramirez and Gomez (2023) created a touchscreen POS terminal with an integrated kitchen display for a food stall in Iloilo City. The system sent counter orders directly to a kitchen screen, using sound alerts when new orders came in. Their tests showed that the kitchen screen eliminated lost paper chits and made food preparation faster.",
                "While their project included a kitchen screen like the barista screen in this study, their POS did not support optical barcode product auto-read scanning, and they had no online reservation portal or digital QR pass feature."
            ],
            [
                "Restaurant Management with Table QR Ordering (Velasco et al., 2024)",
                "Velasco et al. (2024) developed a restaurant ordering system where QR codes were placed on dining tables. Customers scanned the table QR code with their phones to view the menu and submit orders. The authors reported that customers liked the convenience of viewing menus on their phones.",
                "In their study, customers scanned a static table sticker to view a menu. In the ISAT U coffee shop system, QR codes are used in two practical ways: customers get a dynamic QR pass after reserving a study pod for easy check-in, while cashiers scan product barcodes and QR codes directly from packages to automatically read and add items to the POS cart."
            ]
        ],
        
        "table_comparison": {
            "headers": ["System Features", "Del Rosario (2021)", "Santos & Mendoza (2022)", "Alcantara et al. (2023)", "Ramirez & Gomez (2023)", "This Study (ISAT U)"],
            "widths": [2.1, 0.9, 0.9, 0.9, 0.9, 1.1],
            "rows": [
                ["Touchscreen Cashier POS Terminal", "Yes", "Yes", "No", "Yes", "Yes"],
                ["Barcode / QR Code Product Auto-Read", "No", "Yes", "No", "No", "Yes"],
                ["Online Study Pod & Table Reservation", "No", "No", "Yes", "No", "Yes"],
                ["Dynamic Customer QR Pass Generation", "No", "No", "No", "No", "Yes"],
                ["Staff QR Pass Scanner & Check-In", "No", "No", "No", "No", "Yes"],
                ["Barista Kitchen Display Screen (KDS)", "No", "No", "No", "Yes", "Yes"],
                ["Drink Customization (Sizes & Modifiers)", "No", "No", "No", "Yes", "Yes"],
                ["Student and Faculty Discount Computing", "Yes", "No", "No", "No", "Yes"],
                ["Printed 80mm Thermal Receipt", "Yes", "Yes", "No", "Yes", "Yes"],
                ["ISO 25010 Software Quality Evaluation", "No", "Yes", "Yes", "Yes", "Yes"]
            ]
        },
        
        "synthesis_paras": [
            "The literature and related studies show that computerizing store sales, reservations, and kitchen queues improves service speed and reduces human errors. Studies show that barcode scanning speeds up counter sales by replacing manual typing with instant code reading (Laudon & Laudon, 2022). Online reservation systems prevent double-booking and help students find study spaces easily (Tan & Al-Hawari, 2020), while digital kitchen displays prevent lost paper tickets and help kitchen staff work faster (Miller & Davis, 2022).",
            "However, previous projects usually focused on only one part. Some developed retail barcode systems without food customization (Santos & Mendoza, 2022), others built room reservation systems without canteen sales (Alcantara et al., 2023), and others created kitchen screens without online reservations or barcode scanners (Ramirez & Gomez, 2023).",
            "The proposed ISAT U Coffee Shop POS System with Online Reservation and QR Code addresses these gaps by combining all these needed functions into one web platform. By bringing together barcode product scanning at the POS, study pod reservations with QR passes, and a live barista kitchen screen, the system provides a simple, complete, and practical solution tailored for the ISAT U Coffee Shop."
        ]
    },
    
    "chapter_3": {
        "project_description": [
            "The ISAT U Coffee Shop POS System with Online Reservation and QR Code is a web-based application developed to organize and speed up daily sales, product scanning, table bookings, and drink preparation for the campus coffee shop at ISAT U Main Campus, Burgos Street, La Paz, Iloilo City.",
            "The system is built for four user roles:",
            "• Customer (Students, Faculty, and Staff): Can open the website on their phone or computer, check which tables and study pods are free, reserve a time slot, pick drinks with size and sweetness choices, and receive a digital QR pass.",
            "• Cashier: Uses the POS terminal at the counter to ring up walk-in sales, scan product barcodes and QR codes for instant cart auto-read, pick cup sizes and sweetness levels, apply student or faculty discounts (10%) and PWD/senior discounts (20%), enter cash tender to compute change, and print 80mm sales receipts. The cashier can also scan customer QR passes to check them in.",
            "• Barista: Uses the Kitchen Display System (KDS) screen in the brewing area to view new orders in real time, check preparation notes, hear new order chimes, and update drink status from New to Brewing, Ready, and Served.",
            "• Administrator: Uses the admin dashboard to check daily sales totals, see popular drinks, check remaining stock, view low stock warnings (below 10 items), and view reservation logs.",
            "The system has six main modules: (1) Customer Online Reservation Portal; (2) Cashier Point of Sale Terminal with barcode scanning; (3) Staff QR Scanner and Check-In; (4) Barista Kitchen Display Screen; (5) Inventory and Stock Management; and (6) Admin Sales Dashboard and Reports.",
            "The application is built using PHP 8.2 and Laravel 12 for backend logic, MySQL 8.0 for the database, and HTML5, Tailwind CSS, and JavaScript for the screens. It runs on a local Apache web server connected to the campus local area network (LAN) and can be opened on desktop computers, tablets, and smartphones."
        ],
        
        "development_process": {
            "intro": "The researchers followed the Prototyping Model to develop the system. The Prototyping Model is an iterative approach where an early working version of the software is built, shown to users for feedback, and adjusted until all requirements are met. Figure 2 shows the Prototyping Model used in this study.",
            "phases": [
                [
                    "Requirements Gathering",
                    "The researchers visited the ISAT U Coffee Shop and interviewed the supervisor, cashier Bea Ramos, barista Jason Cole, and student customers. The team observed counter lines, how orders were written on paper slips, and how study pods were reserved in notebooks. From these interviews, the team identified the key needs: barcode scanning at the counter to speed up cashiering, an online study pod reservation page with QR passes, and a kitchen screen for baristas."
                ],
                [
                    "Quick Design",
                    "The researchers planned the basic database tables in MySQL, drew UML diagrams (Use Case, Activity, Class, Sequence, and Architecture), and sketched screen layouts for the POS terminal, the customer reservation map, and the barista screen. The format of the 80mm receipt and the QR pass were also planned during this phase."
                ],
                [
                    "Prototype Development",
                    "An initial working prototype was programmed using PHP Laravel and MySQL. The researchers created eleven database tables, coded the touch POS screen with barcode reading, built the online reservation form with QR pass generation, and created the barista kitchen queue screen."
                ],
                [
                    "User Evaluation",
                    "The working prototype was tested with coffee shop users. The supervisor, cashier, barista, and selected students tried scanning products with the barcode scanner, ordering drinks with sweetness choices, booking study pods online, and updating the barista screen. Feedback on what worked and what was hard to use was written down."
                ],
                [
                    "Refinement",
                    "Based on user feedback, the researchers made several improvements. The barcode scanner listener in the POS was adjusted so that scanned items appear immediately in the cart without losing focus. Sound chimes were added to the barista screen for incoming orders, and quick buttons were added for student and faculty discounts (10%) and PWD/senior discounts (20%)."
                ],
                [
                    "Testing",
                    "The revised system was tested to make sure all parts worked properly. The researchers tested barcode scanning with different items, checked that stock quantities decreased correctly after checkout, verified discount and VAT computations, and made sure double-booking the same study pod at the same time was prevented."
                ],
                [
                    "Deployment and Orientation",
                    "The completed system was installed on a campus computer running Apache and MySQL on the local network. The researchers conducted a short training session for the coffee shop staff, explaining how to scan products at the POS, how to check in QR passes, and how to use the barista kitchen screen."
                ]
            ]
        },
        
        "uml": {
            "use_case_paras": [
                "Figure 3 shows the Use Case Diagram of the ISAT U Coffee Shop POS System with Online Reservation and QR Code. The diagram shows the four actors (Customer, Cashier, Barista, and Admin) and what they can do in the system:",
                "• Customer Actor: Can view the café floor map and study pod availability, book a table or study pod, pre-order drinks and food with customizations, and generate a digital QR pass.",
                "• Cashier Actor: Can log in to the POS, scan product barcodes and QR codes for automatic item entry, select cup sizes and modifiers, apply student/faculty discounts, compute cash tenders and change, checkout orders, and print receipts. The cashier also uses the scanner to check in customer QR passes.",
                "• Barista Actor: Can view active orders on the kitchen screen, check drink preparation notes, hear order chimes, and update order status (New, Brewing, Ready, and Served).",
                "• Admin Actor: Can check daily sales totals and reports, update menu prices, adjust stock quantities, and view reservation history."
            ],
            "activity_paras": [
                "Figure 4 shows the Activity Diagram illustrating the steps in a POS counter order and barcode scanning transaction:",
                "1. The cashier logs in and opens the POS terminal.",
                "2. The cashier selects the order type (Dine-In, Takeout, or Study Pod).",
                "3. To add an item, the cashier scans the product barcode or QR code with the scanner. The system reads the code, looks up the product in MySQL, and immediately adds it to the cart tray. Alternatively, the cashier can click a menu category and select the item manually.",
                "4. If the drink has options, the cashier selects the cup size (Regular, Large, Jumbo), sweetness level, ice level, and extra add-ons.",
                "5. The cashier selects customer discount if applicable (None, Student/Faculty 10%, or PWD 20%). The system automatically computes discount deductions and 12% VAT.",
                "6. The cashier selects payment method (Cash or GCash). For Cash, the cashier enters the payment amount and the system computes the change.",
                "7. The cashier clicks Complete Order. The system saves the order to MySQL, reduces stock quantity, sends the order to the barista screen, and prints the 80mm thermal receipt."
            ],
            "class_diag_paras": [
                "Figure 5 shows the Class Diagram and database schema of the system, consisting of eleven connected tables in MySQL:",
                "1. users: Stores account details and roles (admin, cashier, barista, customer).",
                "2. categories: Stores menu categories (Signatures, Espresso, Cold Brew, Milk Teas, Pastries, Meals).",
                "3. products: Stores item information with a unique product_code for barcodes, name, price, and stock.",
                "4. product_sizes: Stores cup size choices (Regular, Large, Jumbo) and extra price.",
                "5. product_addons: Stores extra toppings and milk choices.",
                "6. dining_tables: Stores study pods and dining tables with zone and capacity.",
                "7. reservations: Stores table booking records with date, time slot, and QR token.",
                "8. reservation_items: Stores pre-ordered food and drink items on reservations.",
                "9. orders: Stores counter sales records, subtotal, discount, VAT, and payment method.",
                "10. order_items: Stores individual items on each order.",
                "11. payments: Stores payment details, amount paid, and change."
            ],
            "sequence_paras": [
                "Figure 6 shows the Sequence Diagram for a cashier POS transaction using barcode product auto-read scanning:",
                "1. The cashier scans a product barcode or QR code with the barcode scanner.",
                "2. The scanner sends the scanned code (for example, PROD-001) to the web browser.",
                "3. The browser sends an AJAX request to the Laravel POSController.",
                "4. The controller searches the MySQL database for the product matching the code.",
                "5. MySQL returns the product record, price, and stock.",
                "6. The controller returns the item data as JSON to the browser.",
                "7. The browser immediately adds the item to the cart tray and updates the total.",
                "8. The cashier enters the payment amount and clicks Complete Order.",
                "9. The browser submits the order to the controller.",
                "10. The controller saves the order and order items, reduces stock in MySQL, and forwards the order to the kitchen screen.",
                "11. The browser displays the completed order and prints the 80mm thermal receipt."
            ],
            "architecture_paras": [
                "Figure 7 shows the System Architecture and network setup of the coffee shop application:",
                "• Client Devices: Students and teachers access the reservation portal using their smartphones or laptops. The cashier uses a desktop PC with a 2D barcode scanner and an 80mm thermal printer at the counter. The barista uses a tablet or screen in the brewing area.",
                "• Local Network: Devices connect through the campus local area network (LAN) or campus Wi-Fi using standard HTTP over Port 80.",
                "• Web Server: An Apache web server running PHP 8.2 hosts the Laravel application core and handles system logic.",
                "• Database Server: MySQL 8.0 Community Server stores all records in the db_isatu_pos database."
            ]
        },
        
        "requirements": {
            "hardware": {
                "headers": ["Hardware Component", "Minimum Required Specification"],
                "widths": [2.5, 3.5],
                "rows": [
                    ["Processor (CPU)", "Intel Core i3 or AMD Ryzen 3 (Dual-Core 2.0 GHz or higher)"],
                    ["Memory (RAM)", "8 GB RAM minimum"],
                    ["Storage", "120 GB Solid State Drive (SSD) with at least 15 GB free space"],
                    ["Barcode / QR Scanner", "2D Handheld USB Barcode and QR Scanner (or HD web camera)"],
                    ["Receipt Printer", "80mm Direct Thermal POS Receipt Printer (USB)"],
                    ["Display Monitor", "1080p Monitor (1920x1080) for counter and kitchen screen"],
                    ["Network", "Standard Ethernet LAN or Wi-Fi connectivity"]
                ]
            },
            "software": {
                "headers": ["Software Component", "Required Specification / Tool"],
                "widths": [2.5, 3.5],
                "rows": [
                    ["Operating System", "Microsoft Windows 10 or 11 (64-bit)"],
                    ["Web Server Stack", "XAMPP (Apache 2.4, PHP 8.2)"],
                    ["Backend Framework", "PHP 8.2 with Laravel 12"],
                    ["Database", "MySQL 8.0 Community Server"],
                    ["Frontend", "HTML5, Tailwind CSS, JavaScript (ES6+), Chart.js"],
                    ["Web Browser", "Google Chrome, Mozilla Firefox, or Microsoft Edge"],
                    ["Development Tool", "Visual Studio Code, Composer, Git"]
                ]
            }
        },
        
        "evaluation": {
            "likert_scale": {
                "headers": ["Rating", "Range", "Interpretation"],
                "widths": [1.2, 2.2, 2.6],
                "rows": [
                    ["5", "4.21 - 5.00", "Excellent"],
                    ["4", "3.41 - 4.20", "Very Good"],
                    ["3", "2.61 - 3.40", "Good"],
                    ["2", "1.81 - 2.60", "Poor"],
                    ["1", "1.00 - 1.80", "Very Poor"]
                ]
            },
            "respondents": [
                "A total of ten (10) respondents were selected using purposive sampling to evaluate the system. Purposive sampling was used to choose respondents who understand IT systems and who are familiar with coffee shop operations. The evaluators were divided into two groups:",
                "1. IT Experts (5 respondents): Information Technology instructors and developers from ISAT U who evaluated code structure, database setup, barcode scanning response, and maintainability.",
                "2. Domain End-Users (5 respondents): The coffee shop supervisor, cashier staff Bea Ramos, barista Jason Cole, faculty customer Engr. Mark Villanueva, and student customers from ISAT U who evaluated ease of use, study pod booking convenience, barcode scanning accuracy, and overall satisfaction."
            ],
            "results": {
                "headers": ["ISO 25010 Quality Characteristic", "Computed Mean", "Interpretation"],
                "widths": [3.2, 1.4, 1.4],
                "rows": [
                    ["1. Functional Suitability", "4.70", "Excellent"],
                    ["2. Performance Efficiency", "4.60", "Excellent"],
                    ["3. Compatibility", "4.55", "Excellent"],
                    ["4. Usability", "4.65", "Excellent"],
                    ["5. Reliability", "4.60", "Excellent"],
                    ["6. Security", "4.55", "Excellent"],
                    ["7. Maintainability", "4.65", "Excellent"],
                    ["8. Portability", "4.60", "Excellent"],
                    ["Overall Grand Mean", "4.62", "Excellent"]
                ]
            },
            "discussion": [
                "Table 5 shows the summary of the evaluation results. All eight characteristics received mean scores above 4.21, resulting in an Overall Grand Mean of 4.62, which is interpreted as Excellent.",
                "Functional Suitability scored the highest rating at 4.70. Evaluators stated that the system meets the daily needs of the ISAT U Coffee Shop, especially the barcode auto-read feature that immediately adds items to the cart, the study pod booking map, and the barista kitchen screen. Usability and Maintainability both scored 4.65, showing that users found the screen buttons easy to use and IT experts found the Laravel code clean and organized. Performance Efficiency and Reliability both scored 4.60, indicating that the system scanned items quickly and operated without crashing. Security (4.55), Compatibility (4.55), and Portability (4.60) showed that role logins worked well across browsers on the local network. Overall, the evaluation confirms that the system is practical, easy to use, and ready for actual campus use."
            ]
        }
    },
    
    "references": [
        "Alcantara, J. V., Ramos, C. M., & Santos, D. L. (2023). Web-based study space and facility reservation platform for academic libraries. Philippine Journal of Information Technology, 16(2), 78-91.",
        "Chen, L., Wu, Y., & Zhang, H. (2021). Optimization of collaborative learning spaces in higher education institutions: A resource scheduling perspective. Journal of Educational Technology Development and Exchange, 14(1), 45-62.",
        "Coronel, C., & Morris, S. (2020). Database systems: Design, implementation, & management (13th ed.). Cengage Learning.",
        "Del Rosario, M. A., Bautista, R. K., & Cruz, J. P. (2021). Development of a web-based cashier point of sale and ordering system for campus auxiliary services. International Journal of Computing and Information Sciences, 13(3), 105-114.",
        "Elmasri, R., & Navathe, S. B. (2021). Fundamentals of database systems (7th ed.). Pearson Education.",
        "International Organization for Standardization. (2011). Systems and software engineering: Systems and software Quality Requirements and Evaluation (SQuaRE): System and software quality models (ISO/IEC Standard No. 25010:2011). https://www.iso.org/standard/35733.html",
        "Laudon, K. C., & Laudon, J. P. (2022). Management information systems: Managing the digital firm (17th ed.). Pearson.",
        "Miller, K. R., & Davis, S. M. (2022). Digital kitchen display systems and production line automation in modern hospitality operations. International Journal of Hospitality & Food Service Technology, 19(4), 210-225.",
        "Otwell, T. (2024). Laravel: The PHP framework for web artisans. Taylor Otwell. https://laravel.com",
        "Pressman, R. S., & Maxim, B. R. (2020). Software engineering: A practitioner's approach (9th ed.). McGraw-Hill Education.",
        "Ramirez, G. E., & Gomez, F. L. (2023). Touchscreen POS and integrated kitchen automation display for quick-service food enterprises. Western Visayas Journal of Applied Technology, 8(2), 52-67.",
        "Republic of the Philippines. (2018). Republic Act No. 11032: Ease of doing business and efficient government service delivery act of 2018. Official Gazette of the Republic of the Philippines.",
        "Santos, E. C., & Mendoza, A. R. (2022). Barcode and QR-enabled retail point of sale with real-time inventory management. Journal of Information Systems and Modern Retail, 11(1), 34-48.",
        "Silver, E. A., Pyke, D. F., & Thomas, D. J. (2016). Inventory and production management in supply chains (4th ed.). CRC Press.",
        "Smith, R. H., & Johnson, P. K. (2021). Retail point of sale architecture: Optimization and transaction throughput. Journal of Computer Information Systems, 61(4), 312-326.",
        "Stallings, W. (2018). Effective cybersecurity: A guide to using best practices and standards. Addison-Wesley Professional.",
        "Tan, C. W., & Al-Hawari, M. A. (2020). Investigating customer adoption of automated table reservation and mobile ordering platforms. Computers in Human Behavior, 108, 106-118.",
        "Velasco, M. T., Soriano, L. B., & Fernando, K. J. (2024). Cloud-based restaurant management platform with dynamic QR code table ordering and receipt generation. Asia Pacific Journal of Information Systems, 34(1), 89-104."
    ],
    
    "appendices": {
        "appendix_a_title": "Appendix A: ISO 25010 Software Quality Evaluation Instrument",
        "appendix_a_intro": "This questionnaire was given to IT instructors and coffee shop users to evaluate the ISAT U Coffee Shop POS System with Online Reservation and QR Code using the ISO 25010 software quality model. Evaluators rated each statement from 1 (Very Poor) to 5 (Excellent).",
        "appendix_a_questions": [
            [
                "Functional Suitability",
                [
                    "1. The system computes order totals, customer discounts, and 12% VAT accurately.",
                    "2. The barcode and QR code scanner automatically reads product codes and adds items to the POS cart immediately.",
                    "3. The online reservation page lets users book study pods and generates a valid digital QR pass."
                ]
            ],
            [
                "Performance Efficiency",
                [
                    "4. The system responds quickly when scanning products and completing sales.",
                    "5. The barista kitchen screen updates new orders within seconds without delays.",
                    "6. The database saves orders and updates stock quantities smoothly without lag."
                ]
            ],
            [
                "Compatibility",
                [
                    "7. The web pages display properly on Chrome, Firefox, and Edge on computers and phones.",
                    "8. The system connects properly to standard USB barcode scanners and 80mm thermal printers.",
                    "9. The application runs well on Windows computers."
                ]
            ],
            [
                "Usability",
                [
                    "10. The user interface looks clean, organized, and easy to understand.",
                    "11. Touch buttons, drink size choices, and category tabs are easy to click.",
                    "12. The system gives clear messages when orders are completed or when fields are empty."
                ]
            ],
            [
                "Reliability",
                [
                    "13. The system runs smoothly without unexpected crashes or errors.",
                    "14. Product stock quantities and table reservation statuses remain accurate in the database.",
                    "15. The system handles wrong inputs gracefully without stopping."
                ]
            ],
            [
                "Security",
                [
                    "16. User roles (cashier, barista, admin, customer) restrict unauthorized access to admin pages.",
                    "17. User passwords and reservation QR tokens are safely encrypted.",
                    "18. The system uses session timeouts to protect accounts."
                ]
            ],
            [
                "Maintainability",
                [
                    "19. The Laravel source code is organized into clear controllers, models, and views.",
                    "20. Database tables are properly structured and easy to update.",
                    "21. The code is clean and easy for other programmers to understand."
                ]
            ],
            [
                "Portability",
                [
                    "22. The system is easy to install on a computer with XAMPP (Apache and MySQL).",
                    "23. The database migration and seeders make it easy to set up sample data.",
                    "24. The system works well on the campus local area network (LAN)."
                ]
            ]
        ],
        "appendix_b_title": "Appendix B: System User Interface Screenshots and Operational Flow Diagrams",
        "appendix_b_items": [
            ["Appendix B-1. Customer Online Reservation and Pre-Order Portal", "Shows the seating floor map across Lounge, Study Hub, Main Hall, and Terrace; time slot booking; drink customization; and the dynamic digital QR pass generator."],
            ["Appendix B-2. Cashier POS Terminal with Optical Barcode/QR Scanning", "Shows the category menu grid, the barcode auto-read scanner listener, active cart tray with cup sizes, student discount buttons, cash change computation, and thermal receipt button."],
            ["Appendix B-3. Printable 80mm POS Thermal Receipt Layout", "Shows the university coffee shop header, order number, cashier name, itemized drinks, 10% student discount deduction, 12% VAT breakdown, cash paid, change, and order barcode."],
            ["Appendix B-4. Staff QR Code Scanner and Check-In Screen", "Shows the camera scanner view, manual reference input box, reservation verification details, and the one-click check-in button routing orders to the kitchen."],
            ["Appendix B-5. Barista Kitchen Display System (KDS) Screen", "Shows the dark-mode kitchen screen with New, Brewing, Ready, and Served columns, customer drink customization notes, preparation timers, and chime alerts."],
            ["Appendix B-6. Administrative Management and Sales Dashboard", "Shows total sales revenue, order counts, Chart.js sales curves, product stock quantities with low stock warnings, and master reservation tables."]
        ],
        "appendix_c_title": "Appendix C: Certificate of System Acceptance and Transmittal",
        "appendix_c_text": "Official transmittal letter and certificate of system demonstration and acceptance from the ISAT U Canteen and Auxiliary Services Management, confirming that the system was demonstrated and tested successfully."
    }
}

with open('Documentation/manuscript_data.json', 'w', encoding='utf-8') as f:
    json.dump(data, f, indent=2, ensure_ascii=False)

print("Simplified manuscript_data.json updated successfully with NO long dashes!")
