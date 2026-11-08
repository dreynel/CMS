# -*- coding: utf-8 -*-
"""
Update manuscript_data.json with:
1. Simplified, generalized objectives of the study (1 brief sentence each, no jargons, no specs like MySQL8, PHP, etc.)
2. Simplified Introduction / Background of the study (high-school level, clear, plain English, not so deep)
3. Simplified overall wording across Scope, Limitations, Significance, Terms, and Abstract.
4. Removed obsolete Laravel mentions.
"""

import json

json_path = 'Documentation/manuscript_data.json'

with open(json_path, 'r', encoding='utf-8') as f:
    data = json.load(f)

# 1. Update Abstract
data['front_matter']['abstract'] = (
    "As part of its auxiliary services, Iloilo Science and Technology University - Dumangas Campus (ISAT U - DC) "
    "maintains an existing Mini Hotel used for campus visitors, seminars, and student training. To provide better "
    "amenities and quiet study spaces, the school is planning to open a new campus coffee shop and study lounge "
    "inside this mini hotel. The coffee shop will use a setup where one staff member works as both the cashier and "
    "the barista who prepares the drinks. In a manual setup, using paper notebooks and handwritten slips leads to lost "
    "orders, slow service, and crowded tables. To solve these problems before opening, the researchers developed the "
    "ISAT U - DC Coffee Shop POS System with Online Reservation and QR Code. The system is a web-based application "
    "featuring an online portal where students, teachers, and hotel guests can view available seats, reserve study pods "
    "with power outlets, pre-order drinks, and receive a digital QR pass; a cashier POS screen with barcode and QR scanning "
    "for fast checkout; a real-time kitchen screen for the barista to view incoming orders without loose paper slips; "
    "and an admin dashboard to track daily sales and product inventory. Developed using the Prototyping Model, the system "
    "was evaluated by ten respondents (five IT teachers and five coffee shop users) using the ISO 25010 software quality "
    "standards. The evaluation resulted in an Overall Mean of 4.62, interpreted as Excellent. The findings show that the "
    "system helps the cashier-barista work efficiently, eliminates lost paper tickets, and provides an organized way "
    "to manage the upcoming coffee shop inside the ISAT U - DC Mini Hotel."
)

# 2. Update Chapter 1 Background (Introduction)
data['chapter_1']['background_paras'] = [
    (
        "Computers and software are very helpful in running modern stores and coffee shops. In a cafe, a Point of Sale "
        "or POS system helps cashiers take orders quickly, compute prices and discounts correctly, and print customer "
        "receipts without making math mistakes. Using barcodes and QR codes makes counter work even faster because products "
        "are identified right away. At the same time, an online booking website helps customers see available seats ahead "
        "of time, which prevents long lines and crowded dining areas."
    ),
    (
        "At Iloilo Science and Technology University - Dumangas Campus (ISAT U - DC) in Dumangas, Iloilo, there is an existing "
        "Mini Hotel. The school uses this hotel for campus guests, seminars, and student training. The university is now planning "
        "to open a new coffee shop and study lounge inside this mini hotel. This new coffee shop will sell coffee, cold drinks, "
        "and snacks to students, teachers, and visitors. It will also have tables and quiet study pods with power outlets where "
        "students can do their homework and charge their laptops."
    ),
    (
        "Because this coffee shop is new, the school plans to have one person work as both the cashier and the barista who "
        "makes the drinks. Doing all of this by hand with pen and paper can cause many problems. Writing orders on paper slips "
        "is messy, and paper can easily get wet, torn, or lost on the counter. During busy break times, a single worker would "
        "have a hard time computing bills, preparing drinks, and checking study pod seats all at once. This leads to slow "
        "service, wrong orders, and unhappy customers."
    ),
    (
        "To prevent these problems before the coffee shop opens, the researchers created a computerized management system. "
        "Setting up a system before opening makes sure the new coffee shop starts with a smooth, paperless, and organized way "
        "of working from day one."
    ),
    (
        "The researchers developed the ISAT U - DC Coffee Shop POS System with Online Reservation and QR Code. The system has "
        "simple screens for each user. Customers can visit a website to view available seats, reserve a study pod, pre-order "
        "their drinks, and receive a digital QR pass on their phone. When they arrive, the staff scans the QR pass to check them "
        "in, and the order is sent directly to a kitchen screen so the barista can prepare the drinks. At the counter, the cashier "
        "can scan items, compute change, and print receipts. The system also tracks daily sales and ingredients so the manager "
        "knows when stocks are running low."
    ),
    (
        "By using this system, the upcoming coffee shop inside the ISAT U - DC Mini Hotel will have an easy and organized way to "
        "serve customers, manage study pods, and track sales without using loose paper slips."
    )
]

# 3. Update Objectives of the Study (Simplified, generalized, 1 sentence each, NO jargons, NO specs)
data['chapter_1']['general_objective'] = (
    "The main objective of the study was to develop and evaluate a Point of Sale (POS) and online reservation system with "
    "QR code for the upcoming coffee shop inside the ISAT U - Dumangas Campus Mini Hotel."
)

data['chapter_1']['specific_objectives'] = [
    "design and set up a database to store and organize coffee shop records;",
    "create user accounts for the administrator, cashier-barista, and customers;",
    "develop an online portal for reserving tables and study pods with pre-ordering and digital QR passes;",
    "create a cashier POS screen to process orders, payments, and receipts quickly;",
    "set up a kitchen display screen to help the barista view and prepare orders easily;",
    "generate daily sales reports and monitor product inventory; and",
    "evaluate the system using the ISO 25010 software quality standards."
]

# 4. Update Conceptual Framework (Clear, simple narrative)
data['chapter_1']['conceptual_framework_paras'] = [
    (
        "The conceptual framework of this study was developed using the Input-Process-Output (IPO) model, together with a "
        "system evaluation feedback loop. Figure 1 shows the conceptual framework of this study."
    ),
    (
        "As shown in Figure 1, the framework shows how information flows through the system for the upcoming coffee shop inside "
        "the ISAT U - DC Mini Hotel. The input stage includes the basic information needed to run the coffee shop. This includes "
        "user login details, menu items and prices, table and study pod seating layouts, customer reservations, and payment details."
    ),
    (
        "The process stage represents the main tasks done by the system. These include logging in users, scanning item codes and "
        "customer QR passes, computing order totals and discounts, sending drink orders to the barista kitchen screen, updating "
        "inventory stocks, and creating sales reports."
    ),
    (
        "The output of the framework is the completed ISAT U - DC Coffee Shop POS System with Online Reservation and QR Code. Below "
        "the output is the system evaluation, where the software is graded using the ISO 25010 software quality standards to make "
        "sure it is useful, reliable, and easy to use."
    )
]

# 5. Update Scope and Limitations (Simple wording)
data['chapter_1']['scope_paras'] = [
    (
        "This study focuses on developing and testing the ISAT U - DC Coffee Shop POS System with Online Reservation and QR Code "
        "for the upcoming campus coffee shop and study hub inside the existing Mini Hotel at Iloilo Science and Technology University - "
        "Dumangas Campus in Dumangas, Iloilo."
    ),
    (
        "The system includes an online customer page that shows an interactive seating map of four zones inside the mini hotel "
        "(Lounge, Study Hub, Main Hall, and Terrace). Students, teachers, and hotel guests can reserve study pods or tables for specific "
        "time slots, pre-order food and drinks with customized sizes and sweetness levels, and get a digital QR pass on their phones."
    ),
    (
        "The system includes a cashier POS screen designed for the cashier-barista. The staff can scan product barcodes or QR codes "
        "to add items to the cart quickly. The screen supports drink choices (cup sizes, sweetness levels, ice levels, and extra add-ons), "
        "automatic discounts for students, teachers, and senior citizens, cash change calculation, and receipt printing."
    ),
    (
        "The system includes a staff QR scanner to quickly scan customer passes at the counter, mark seats as taken, and send orders "
        "directly to the kitchen."
    ),
    (
        "The system includes a barista kitchen display screen placed in the preparation area. It shows incoming drink orders in real time "
        "with preparation timers and sound alerts so orders do not get forgotten."
    ),
    (
        "The system includes an admin dashboard where managers can view daily sales summaries, check popular items, monitor remaining "
        "stocks with low-stock alerts, and check reservation schedules."
    ),
    (
        "The system is built as a web-based application with a database backend and responsive web pages that run on computers, tablets, "
        "and smartphones through standard web browsers."
    )
]

data['chapter_1']['limitations_paras'] = [
    "1. The system does not process credit cards automatically online; digital wallet payments via GCash are checked manually by the cashier using the reference number.",
    "2. The system does not send SMS text messages to mobile phones; all booking passes and confirmations are shown on the website.",
    "3. The system is a web application and does not have a downloadable mobile app from app stores; users access it using standard web browsers.",
    "4. The system is only for on-campus dining, study pod reservations, and counter takeout; it does not connect to outside food delivery services.",
    "5. The system does not have electronic door locks for the study pods; staff visually check guests into their tables.",
    "6. The system is made only for the single coffee shop inside the ISAT U - DC Mini Hotel and does not connect to other hotel branch systems.",
    "7. The system needs the campus local network and server power to run; if the local network or electricity goes down, the system cannot be opened."
]

# 6. Update Significance of the Study (Plain English)
data['chapter_1']['significance_items'] = [
    [
        "ISAT U - DC Campus Administration and Auxiliary Services",
        "The system provides the university with an easy-to-use computerized system for the new coffee shop inside the mini hotel, helping them track sales, discounts, and room usage accurately from day one."
    ],
    [
        "Mini Hotel and Coffee Shop Management",
        "The system helps managers monitor daily income, see popular food and drinks, and check stock levels in real time so the shop does not run out of ingredients."
    ],
    [
        "Cashier-Barista (Dual-Role Staff)",
        "Because one staff member works as both cashier and barista, the system makes their daily work much easier. Fast scanning speeds up the counter line, and the kitchen screen shows drink orders clearly without needing loose paper slips that can get wet or lost."
    ],
    [
        "Students and Faculty Members of ISAT U - DC",
        "Students and teachers can easily check seat availability, reserve quiet study pods with power outlets in advance, and pre-order drinks to avoid long lines during quick class breaks."
    ],
    [
        "Mini Hotel Guests and Campus Visitors",
        "Hotel guests and campus visitors enjoy faster service, easy contactless QR check-in, and clear printed receipts."
    ],
    [
        "Future Researchers",
        "This study serves as a helpful reference and guide for other students and researchers who want to build similar school POS, reservation, or kitchen display systems."
    ]
]

# 7. Update Definition of Terms (Simple, non-deep English, removed Laravel Framework)
data['chapter_1']['terms'] = [
    [
        "Barcode and QR Code Product Scanning",
        "A scanning method where printed code lines or squares on items are read by a scanner.",
        "In this study, it refers to scanning item codes at the counter to quickly add them and their prices to the sales cart without typing."
    ],
    [
        "Cashier Point of Sale (POS)",
        "A computer screen used at the sales counter to enter orders, calculate payments and change, and print receipts.",
        "In this study, it refers to the screen used by the cashier to take orders, apply student discounts, and print sales receipts."
    ],
    [
        "Database",
        "An organized computer storage that saves system records in structured tables.",
        "In this study, it refers to the digital storage that safely keeps records of users, products, study pods, reservations, orders, and sales."
    ],
    [
        "Digital QR Pass",
        "A square digital barcode shown on a phone screen that acts as a ticket.",
        "In this study, it refers to the QR pass shown after booking a table, which staff scan to check the customer in."
    ],
    [
        "ISO 25010 Software Quality Standard",
        "A set of international rules used to test and grade software.",
        "In this study, it refers to the evaluation survey used by IT instructors and coffee shop users to rate the quality and usability of the system."
    ],
    [
        "Kitchen Display System (KDS)",
        "A screen in the food preparation area that replaces paper tickets.",
        "In this study, it refers to the screen used by the barista to view incoming drink orders and mark them as brewing, ready, or served."
    ],
    [
        "Online Table and Pod Reservation",
        "A website feature that allows customers to view open seats and book a table before arriving.",
        "In this study, it refers to the web page where students and teachers can reserve study pods or tables and pre-order their drinks."
    ],
    [
        "Product Add-ons and Modifiers",
        "Extra choices added to a food or drink item, such as extra espresso shots, oat milk, sugar level, or ice level.",
        "In this study, it refers to drink customizations chosen during ordering."
    ],
    [
        "Prototyping Model",
        "A step-by-step way of making software by building an early working version, testing it with users, and improving it based on their feedback.",
        "In this study, it refers to the development method used by the researchers to build and improve the coffee shop system."
    ],
    [
        "Study Pods",
        "Small desks with power outlets in a quiet area intended for focused studying.",
        "In this study, it refers to the solo and pair study desks in the coffee shop study area that can be reserved through the website."
    ],
    [
        "User Accounts",
        "Separate login accounts that give different features to different users.",
        "In this study, it refers to the separate access rights given to administrators, cashiers, baristas, and customers."
    ]
]

# 8. Update Table 3 Software Requirements in Chapter 3
data['chapter_3']['requirements']['software']['rows'] = [
    [
        "Operating System",
        "Microsoft Windows 10 or 11 (64-bit) / Linux"
    ],
    [
        "Web Server",
        "Apache Web Server (XAMPP / Hostinger)"
    ],
    [
        "Programming Language",
        "PHP 8.x (Native PHP / PDO)"
    ],
    [
        "Database Engine",
        "MySQL Community Server"
    ],
    [
        "Frontend Design",
        "HTML5, Bootstrap 5.3, JavaScript, Chart.js"
    ],
    [
        "Web Browser",
        "Google Chrome, Mozilla Firefox, or Microsoft Edge"
    ],
    [
        "Development Tool",
        "Visual Studio Code, Git"
    ]
]

# 9. Update Sequence and Activity Diagram descriptions in Chapter 3 to remove Laravel mentions
data['chapter_3']['uml']['sequence_paras'] = [
    "Figure 7 shows the Sequence Diagram for a cashier POS transaction using barcode product scanning:",
    "1. The cashier scans a product barcode or QR code with the scanner.",
    "2. The scanner sends the scanned code to the web browser.",
    "3. The browser sends an AJAX request to the POS API server.",
    "4. The system searches the database for the product matching the code.",
    "5. The database returns the product details, price, and stock count.",
    "6. The server returns the item data to the browser.",
    "7. The browser immediately adds the item to the cart and updates the total.",
    "8. The cashier enters the payment amount and clicks Complete Order.",
    "9. The browser submits the order to the server.",
    "10. The server saves the order, reduces stock in the database, and forwards the order to the kitchen screen.",
    "11. The browser displays the completed transaction and prints the receipt."
]

data['chapter_3']['uml']['architecture_paras'] = [
    "Figure 3 shows the System Architecture and network setup of the coffee shop application:",
    "• Client Devices: Students and teachers access the reservation portal using smartphones or laptops. The cashier uses a desktop PC with a barcode scanner and receipt printer at the counter. The barista uses a tablet or screen in the brewing area.",
    "• Local Network: Devices connect through the campus local area network (LAN) or campus Wi-Fi using standard HTTP.",
    "• Web Server: An Apache web server running PHP hosts the application and handles system logic.",
    "• Database Server: MySQL stores all records in the coffee shop database."
]

data['chapter_3']['project_description'][4] = (
    "The system is built using Native PHP 8.x and MySQL for reliable data storage, with HTML5, Bootstrap 5.3, "
    "and JavaScript for an intuitive and responsive client interface. The application operates within the campus Local "
    "Area Network (LAN) or online hosting, accessible across desktop computers, tablets, and smartphones using standard web browsers."
)

with open(json_path, 'w', encoding='utf-8') as f:
    json.dump(data, f, indent=2, ensure_ascii=False)

print("Successfully updated manuscript_data.json with simplified wording and generalized objectives!")

