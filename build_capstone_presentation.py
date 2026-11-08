import os
import io
import pptx
from pptx.util import Inches, Pt
from pptx.dml.color import RGBColor
from pptx.enum.text import PP_ALIGN, MSO_ANCHOR
from pptx.enum.shapes import MSO_SHAPE_TYPE, MSO_SHAPE

TEMPLATE_PATH = 'Documentation/PPT-CAPs-repaired.pptx'
OUTPUT_PATH = 'Documentation/ISAT_U_Coffee_Shop_POS_Presentation.pptx'

prs = pptx.Presentation(TEMPLATE_PATH)
slide_w = prs.slide_width
slide_h = prs.slide_height

print(f"Loaded template with {len(prs.slides)} slides. Dimensions: {slide_w.inches}x{slide_h.inches} in.")

# Color Palette
COLOR_NAVY = RGBColor(11, 46, 89)
COLOR_GOLD = RGBColor(217, 119, 6)
COLOR_DARK = RGBColor(33, 37, 41)
COLOR_WHITE = RGBColor(255, 255, 255)
COLOR_MUTED = RGBColor(108, 117, 125)
COLOR_LIGHT_BG = RGBColor(248, 249, 250)

# Helper: remove shape by condition
def remove_shapes(slide, filter_func):
    for sh in list(slide.shapes):
        if filter_func(sh):
            sp = sh._element
            sp.getparent().remove(sp)

# Helper: set styled text
def set_styled_paragraphs(shape, paragraphs_data):
    """
    paragraphs_data: list of dicts:
    [
        {'text': 'Hello', 'font_size': 18, 'bold': True, 'color': COLOR_NAVY, 'align': PP_ALIGN.LEFT, 'space_after': 6}
    ]
    """
    tf = shape.text_frame
    tf.clear()
    for idx, d in enumerate(paragraphs_data):
        p = tf.paragraphs[0] if idx == 0 else tf.add_paragraph()
        p.text = d.get('text', '')
        p.alignment = d.get('align', PP_ALIGN.LEFT)
        p.font.name = d.get('font_name', 'Times New Roman')
        p.font.size = Pt(d.get('font_size', 16))
        p.font.bold = d.get('bold', False)
        if 'color' in d and d['color']:
            p.font.color.rgb = d['color']
        if 'space_after' in d:
            p.space_after = Pt(d['space_after'])

# Helper: extract background image bytes from Slide 11
bg_bytes = None
s11 = prs.slides[10]
for sh in s11.shapes:
    if sh.name == 'Picture 2':
        bg_bytes = sh.image.blob
        break

def add_methodology_slide(title_sub):
    blank_layout = prs.slide_layouts[6]
    slide = prs.slides.add_slide(blank_layout)
    if bg_bytes:
        slide.shapes.add_picture(io.BytesIO(bg_bytes), Inches(-0.01), 0, Inches(13.35), Inches(7.52))
    
    # Title box (Chapter III: Methodology)
    tb_title = slide.shapes.add_textbox(Inches(3.8), Inches(0.28), Inches(5.8), Inches(0.55))
    tf = tb_title.text_frame
    p = tf.paragraphs[0]
    p.text = "Chapter III: Methodology"
    p.font.name = "Times New Roman"
    p.font.size = Pt(28)
    p.font.bold = True
    p.alignment = PP_ALIGN.CENTER
    
    # Subtitle box
    tb_sub = slide.shapes.add_textbox(Inches(1.0), Inches(0.9), Inches(11.33), Inches(0.45))
    tf_sub = tb_sub.text_frame
    p_sub = tf_sub.paragraphs[0]
    p_sub.text = title_sub
    p_sub.font.name = "Times New Roman"
    p_sub.font.size = Pt(19)
    p_sub.font.bold = True
    p_sub.font.color.rgb = COLOR_NAVY
    p_sub.alignment = PP_ALIGN.CENTER
    return slide

# ==============================================================================
# SLIDE 1: Group Members / Title Details
# ==============================================================================
s1 = prs.slides[0]
for sh in s1.shapes:
    if sh.has_text_frame and 'Members:' in sh.text_frame.text:
        set_styled_paragraphs(sh, [
            {'text': 'RESEARCHERS:', 'font_size': 18, 'bold': True, 'color': COLOR_NAVY, 'space_after': 4},
            {'text': 'John Reynel Caparida', 'font_size': 16, 'bold': False, 'space_after': 2},
            {'text': 'Christian Mark Gepana', 'font_size': 16, 'bold': False, 'space_after': 2},
            {'text': 'Kenth Michael Palma', 'font_size': 16, 'bold': False, 'space_after': 14},
            {'text': 'CAPSTONE ADVISER:', 'font_size': 18, 'bold': True, 'color': COLOR_NAVY, 'space_after': 4},
            {'text': 'Engr. Mark Anthony Villanueva, M.Eng.', 'font_size': 16, 'bold': False, 'space_after': 14},
            {'text': 'ACADEMIC PROGRAM:', 'font_size': 18, 'bold': True, 'color': COLOR_NAVY, 'space_after': 4},
            {'text': 'Bachelor of Science in Information Technology', 'font_size': 15, 'bold': False, 'space_after': 2},
            {'text': 'Iloilo Science and Technology University - Dumangas Campus', 'font_size': 14, 'bold': False},
        ])
    elif sh.name == 'Picture Placeholder 8':
        # Replace with ISAT U Logo if available
        logo_path = 'assets/images/isatu.png'
        if os.path.exists(logo_path):
            l, t, w, h = sh.left, sh.top, sh.width, sh.height
            sp = sh._element
            sp.getparent().remove(sp)
            s1.shapes.add_picture(logo_path, l + Inches(0.8), t + Inches(0.5), Inches(3.5), Inches(3.5))

print("Updated Slide 1 (Members & Institutional Info).")

# ==============================================================================
# SLIDE 2: Title Slide
# ==============================================================================
s2 = prs.slides[1]
for sh in s2.shapes:
    if sh.has_text_frame and ('PTAConnect' in sh.text_frame.text or 'Title' in sh.name):
        set_styled_paragraphs(sh, [
            {'text': 'ISAT U - DC COFFEE SHOP POS SYSTEM WITH ONLINE RESERVATION AND QR CODE', 'font_size': 32, 'bold': True, 'align': PP_ALIGN.CENTER, 'space_after': 16},
            {'text': 'An Information Technology Capstone Project', 'font_size': 20, 'bold': False, 'align': PP_ALIGN.CENTER, 'space_after': 6},
            {'text': 'Iloilo Science and Technology University - Dumangas Campus', 'font_size': 18, 'bold': False, 'align': PP_ALIGN.CENTER},
        ])

print("Updated Slide 2 (Project Title).")

# ==============================================================================
# SLIDE 3: Background of the Study
# ==============================================================================
s3 = prs.slides[2]
# Remove distracting little clipart/clouds to make room for structured cards
remove_shapes(s3, lambda sh: sh.name.startswith('Cloud') or sh.name in ['Picture 10', 'Picture 15', 'Picture 2'])

# Add 3 clean structured narrative cards
card_w = Inches(3.7)
card_h = Inches(4.8)
top_pos = Inches(1.8)

cards_data = [
    {
        'left': Inches(0.8),
        'header': '1. Institutional Setting',
        'points': [
            'ISAT U - DC Mini Hotel operates as a hospitality training, seminar, and guest center.',
            'The university is opening a new coffee shop and study lounge inside the hotel.',
            'Facility offers dining tables and quiet study pods equipped with power outlets.'
        ]
    },
    {
        'left': Inches(4.8),
        'header': '2. Operational Problems',
        'points': [
            'Cashier-to-barista communication reliant on loose paper chits and verbal calls.',
            'Paper slips easily get wet, lost, or stained during peak class break rush hours.',
            'Verbal shouting causes order mix-ups, wrong cup sizes, and delayed service.',
            'Uncontrolled study pod crowding and long counter waiting lines.'
        ]
    },
    {
        'left': Inches(8.8),
        'header': '3. Proposed Solution',
        'points': [
            'Self-service Online Reservation & Pre-Ordering portal for study pods and tables.',
            'Dynamic QR Booking Pass with 15-minute seating holding policy.',
            'Staff Concierge seating desk with overdue table release alerts.',
            'Touch POS terminal with optical 2D QR product scanning & pre-order fetching.',
            'Paperless Barista KDS screen with audio chimes & Brew-to-Serve inventory.'
        ]
    }
]

for cd in cards_data:
    shape = s3.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, cd['left'], top_pos, card_w, card_h)
    shape.fill.solid()
    shape.fill.fore_color.rgb = COLOR_WHITE
    shape.line.color.rgb = COLOR_NAVY
    shape.line.width = Pt(1.5)
    
    tf = shape.text_frame
    tf.word_wrap = True
    tf.margin_top = Inches(0.2)
    tf.margin_left = Inches(0.25)
    tf.margin_right = Inches(0.25)
    tf.margin_bottom = Inches(0.2)
    
    p = tf.paragraphs[0]
    p.text = cd['header']
    p.font.name = 'Times New Roman'
    p.font.size = Pt(18)
    p.font.bold = True
    p.font.color.rgb = COLOR_NAVY
    p.space_after = Pt(12)
    
    for pt in cd['points']:
        p_pt = tf.add_paragraph()
        p_pt.text = "• " + pt
        p_pt.font.name = 'Times New Roman'
        p_pt.font.size = Pt(14)
        p_pt.font.color.rgb = COLOR_DARK
        p_pt.space_after = Pt(8)

print("Updated Slide 3 (Background of the Study).")

# ==============================================================================
# SLIDE 4: Objectives of the Study
# ==============================================================================
s4 = prs.slides[3]
# Remove overlapping decorative cliparts
remove_shapes(s4, lambda sh: sh.name in ['Picture 4', 'Picture 6', 'Picture 8', 'Picture 12', 'Picture 2'])

for sh in s4.shapes:
    if sh.has_text_frame and ('Create a database' in sh.text_frame.text or 'Content Placeholder' in sh.name):
        sh.left = Inches(1.0)
        sh.top = Inches(1.7)
        sh.width = Inches(11.3)
        sh.height = Inches(4.8)
        
        objectives_text = [
            {'text': 'General Objective:', 'font_size': 18, 'bold': True, 'color': COLOR_NAVY, 'space_after': 4},
            {'text': 'To design, develop, implement, and evaluate the ISAT U - DC Coffee Shop POS System with Online Reservation and QR Code for the mini hotel.', 'font_size': 15, 'bold': False, 'space_after': 12},
            {'text': 'Specific Objectives:', 'font_size': 18, 'bold': True, 'color': COLOR_NAVY, 'space_after': 6},
            {'text': '1. Design and develop a centralized database for managing user accounts, products, reservations, orders, payments, and inventory records.', 'font_size': 15, 'space_after': 6},
            {'text': '2. Implement role-based access for the administrator, cashier, barista, and customer.', 'font_size': 15, 'space_after': 6},
            {'text': '3. Develop an online reservation and pre-ordering portal that allows customers to view availability and receive QR-based booking passes.', 'font_size': 15, 'space_after': 6},
            {'text': '4. Develop a POS module that supports product QR code scanning, order processing, payment recording, discount computation, and receipt generation.', 'font_size': 15, 'space_after': 6},
            {'text': '5. Develop a Kitchen Display System (KDS) for displaying and updating beverage and food order statuses.', 'font_size': 15, 'space_after': 6},
            {'text': '6. Implement inventory monitoring and sales reporting functions.', 'font_size': 15, 'space_after': 6},
            {'text': '7. Evaluate the system using the applicable ISO/IEC 25010 software product quality characteristics.', 'font_size': 15},
        ]
        set_styled_paragraphs(sh, objectives_text)

print("Updated Slide 4 (7 Specific Objectives).")

# ==============================================================================
# SLIDE 5: Conceptual Framework
# ==============================================================================
s5 = prs.slides[4]
for sh in s5.shapes:
    if sh.name == 'Rounded Rectangle 25':
        set_styled_paragraphs(sh, [
            {'text': 'User Accounts (Staff)', 'font_size': 13, 'bold': True, 'color': COLOR_NAVY, 'space_after': 3},
            {'text': 'Admin, Cashier, Barista', 'font_size': 12, 'space_after': 6},
            {'text': 'Public Customer Portal', 'font_size': 13, 'bold': True, 'color': COLOR_NAVY, 'space_after': 3},
            {'text': 'Direct Access (No Login)', 'font_size': 12, 'space_after': 6},
            {'text': 'Tables & Study Pods', 'font_size': 13, 'bold': True, 'color': COLOR_NAVY, 'space_after': 3},
            {'text': '4 Mini Hotel Zones', 'font_size': 12, 'space_after': 6},
            {'text': 'Products & 2D QR Tags', 'font_size': 13, 'bold': True, 'color': COLOR_NAVY, 'space_after': 3},
            {'text': 'Prices, Recipes, Modifiers', 'font_size': 12, 'space_after': 6},
            {'text': 'Online Reservations', 'font_size': 13, 'bold': True, 'color': COLOR_NAVY, 'space_after': 3},
            {'text': '15-Minute Seating Policy', 'font_size': 12, 'space_after': 6},
            {'text': 'Financial Parameters', 'font_size': 13, 'bold': True, 'color': COLOR_NAVY, 'space_after': 3},
            {'text': 'Cash, GCash, Discounts', 'font_size': 12},
        ])
    elif sh.name == 'Rounded Rectangle 26':
        set_styled_paragraphs(sh, [
            {'text': 'Staff Authentication', 'font_size': 13, 'bold': True, 'color': COLOR_NAVY, 'space_after': 3},
            {'text': 'Role-Based Validation', 'font_size': 12, 'space_after': 6},
            {'text': 'Optical 2D QR Scanning', 'font_size': 13, 'bold': True, 'color': COLOR_NAVY, 'space_after': 3},
            {'text': 'Product Auto-Reading', 'font_size': 12, 'space_after': 6},
            {'text': 'Concierge QR Check-In', 'font_size': 13, 'bold': True, 'color': COLOR_NAVY, 'space_after': 3},
            {'text': 'Seating Arrival & Table Map', 'font_size': 12, 'space_after': 6},
            {'text': 'POS Pre-Order Fetching', 'font_size': 13, 'bold': True, 'color': COLOR_NAVY, 'space_after': 3},
            {'text': 'Editable Walk-in Cart', 'font_size': 12, 'space_after': 6},
            {'text': 'Statutory Discounting', 'font_size': 13, 'bold': True, 'color': COLOR_NAVY, 'space_after': 3},
            {'text': '10% Student / 20% Senior', 'font_size': 12, 'space_after': 6},
            {'text': 'Barista KDS Dispatch', 'font_size': 13, 'bold': True, 'color': COLOR_NAVY, 'space_after': 3},
            {'text': 'Brew-to-Serve Stock Ledger', 'font_size': 12},
        ])
    elif sh.name == 'TextBox 10':
        set_styled_paragraphs(sh, [
            {'text': 'ISAT U - DC Coffee Shop POS System with Online Reservation and QR Code', 'font_size': 14, 'bold': True, 'align': PP_ALIGN.CENTER, 'color': COLOR_NAVY, 'space_after': 6},
            {'text': 'Operational web system deployed inside the Mini Hotel', 'font_size': 12, 'align': PP_ALIGN.CENTER},
        ])
    elif sh.name == 'Rounded Rectangle 14':
        set_styled_paragraphs(sh, [
            {'text': 'ISO/IEC 25010 Software Product Quality Evaluation (Continuous Feedback Loop)', 'font_size': 13, 'bold': True, 'align': PP_ALIGN.CENTER, 'color': COLOR_WHITE},
        ])

print("Updated Slide 5 (Conceptual Framework IPO).")

# ==============================================================================
# SLIDE 6: Significance of the Study
# ==============================================================================
s6 = prs.slides[5]
for sh in s6.shapes:
    if sh.has_text_frame and 'PTA Officers' in sh.text_frame.text:
        set_styled_paragraphs(sh, [
            {'text': '1. ISAT U - DC Mini Hotel Administration & Supervisors', 'font_size': 16, 'bold': True, 'color': COLOR_NAVY, 'space_after': 2},
            {'text': 'Provides centralized management of study spaces, transparent sales monitoring, and digital record integrity.', 'font_size': 14, 'space_after': 10},
            {'text': '2. Campus Auxiliary Services & Management', 'font_size': 16, 'bold': True, 'color': COLOR_NAVY, 'space_after': 2},
            {'text': 'Delivers perpetual ingredient and packaging tracking through recipe-based Brew-to-Serve automated deductions.', 'font_size': 14, 'space_after': 10},
            {'text': '3. Frontline Cashiers', 'font_size': 16, 'bold': True, 'color': COLOR_NAVY, 'space_after': 2},
            {'text': 'Eliminates manual math errors, speeds up checkout via 2D QR auto-reading, and automatically fetches pre-orders.', 'font_size': 14, 'space_after': 10},
            {'text': '4. Baristas & Kitchen Preparation Staff', 'font_size': 16, 'bold': True, 'color': COLOR_NAVY, 'space_after': 2},
            {'text': 'Eliminates lost paper chits with a real-time KDS queue screen, acoustic alerts, and clear recipe formulations.', 'font_size': 14, 'space_after': 10},
            {'text': '5. Students, Faculty & Campus Hotel Guests', 'font_size': 16, 'bold': True, 'color': COLOR_NAVY, 'space_after': 2},
            {'text': 'Offers hassle-free study pod booking with power outlets, remote pre-ordering, and transparent discount privileges.', 'font_size': 14, 'space_after': 10},
            {'text': '6. Future Researchers & IT Developers', 'font_size': 16, 'bold': True, 'color': COLOR_NAVY, 'space_after': 2},
            {'text': 'Serves as an empirical baseline for hospitality point-of-sale and paperless kitchen coordination architectures.', 'font_size': 14},
        ])

print("Updated Slide 6 (Significance of the Study).")

# ==============================================================================
# SLIDE 7: Scope and Delimitation
# ==============================================================================
s7 = prs.slides[6]
for sh in s7.shapes:
    if sh.has_text_frame and 'Scope:' in sh.text_frame.text:
        sh.left = Inches(1.0)
        sh.top = Inches(1.6)
        sh.width = Inches(11.33)
        sh.height = Inches(5.0)
        set_styled_paragraphs(sh, [
            {'text': 'PROJECT SCOPE (IN-SCOPE):', 'font_size': 17, 'bold': True, 'color': COLOR_NAVY, 'space_after': 4},
            {'text': '• Online Reservation Portal: Allows students and guests to inspect seating availability across 4 mini hotel zones and book tables/study pods.', 'font_size': 14, 'space_after': 4},
            {'text': '• Dynamic QR Booking Pass: Generates an encrypted 2D pass with mandatory 15-minute seating holding agreement.', 'font_size': 14, 'space_after': 4},
            {'text': '• Staff Concierge Desk: Onsite QR pass verification, arrival confirmation, and overdue table release alerts (+15m no-show forfeiture).', 'font_size': 14, 'space_after': 4},
            {'text': '• Front-Counter POS Terminal: Optical 2D QR product scanning, pre-order cart fetching with walk-in adjustments, statutory discounting (10% student, 20% senior/PWD), and thermal receipt printing.', 'font_size': 14, 'space_after': 4},
            {'text': '• Barista Kitchen Display System (KDS): Live electronic ticket routing with audio alert chimes and recipe bills of materials.', 'font_size': 14, 'space_after': 4},
            {'text': '• Brew-to-Serve Inventory Ledger: Real-time ingredient and packaging deduction upon drink fulfillment, with stock vouchers and conversion.', 'font_size': 14, 'space_after': 12},
            {'text': 'PROJECT DELIMITATION (EXCLUSIONS):', 'font_size': 17, 'bold': True, 'color': COLOR_NAVY, 'space_after': 4},
            {'text': '• Direct online payment gateway (credit/debit card) integration is excluded (payments recorded at counter via Cash or GCash).', 'font_size': 14, 'space_after': 4},
            {'text': '• Off-campus doorstep food delivery and courier dispatching are excluded.', 'font_size': 14, 'space_after': 4},
            {'text': '• Automated third-party external vendor procurement / EDI ordering is excluded.', 'font_size': 14},
        ])

print("Updated Slide 7 (Scope and Delimitation).")

# ==============================================================================
# SLIDE 8: Chapter II: Review of Related Literature (Related Literatures)
# ==============================================================================
s8 = prs.slides[7]
for sh in s8.shapes:
    if sh.has_text_frame and 'Database Management' in sh.text_frame.text:
        sh.left = Inches(1.2)
        sh.top = Inches(1.8)
        sh.width = Inches(10.8)
        sh.height = Inches(4.8)
        set_styled_paragraphs(sh, [
            {'text': '1. Point of Sale (POS) Systems & Optical 2D Barcode Automation', 'font_size': 16, 'bold': True, 'color': COLOR_NAVY, 'space_after': 2},
            {'text': 'Focuses on touch-based register transactions, optical camera / 2D QR decoding for instantaneous item identification, and error reduction in busy campus retail environments.', 'font_size': 14, 'space_after': 12},
            {'text': '2. Online Reservation & Campus Facility Scheduling', 'font_size': 16, 'bold': True, 'color': COLOR_NAVY, 'space_after': 2},
            {'text': 'Examines responsive web portals for study space allocation, real-time table availability mapping, and automated booking pass generation.', 'font_size': 14, 'space_after': 12},
            {'text': '3. Kitchen Display Systems (KDS) & Paperless Queue Dispatching', 'font_size': 16, 'bold': True, 'color': COLOR_NAVY, 'space_after': 2},
            {'text': 'Traces the replacement of vulnerable handwritten paper slips with synchronized digital kitchen display screens and acoustic alert chimes.', 'font_size': 14, 'space_after': 12},
            {'text': '4. Recipe-Based "Brew-to-Serve" Perpetual Inventory Control', 'font_size': 16, 'bold': True, 'color': COLOR_NAVY, 'space_after': 2},
            {'text': 'Addresses decoupling sales checkouts from stock depletion; deducts raw materials, syrups, cups, and straws only when drinks are prepared by the barista.', 'font_size': 14, 'space_after': 12},
            {'text': '5. ISO/IEC 25010 Software Product Quality Evaluation Standard', 'font_size': 16, 'bold': True, 'color': COLOR_NAVY, 'space_after': 2},
            {'text': 'Establishes empirical measurement across Functional Suitability, Performance Efficiency, Usability, Reliability, Security, and Maintainability.', 'font_size': 14},
        ])

print("Updated Slide 8 (Related Literature).")

# ==============================================================================
# SLIDE 9: Chapter II: Review of Related Literature (Related Studies)
# ==============================================================================
s9 = prs.slides[8]
for sh in s9.shapes:
    if sh.has_text_frame and 'Parent Teacher Interaction' in sh.text_frame.text:
        sh.left = Inches(1.2)
        sh.top = Inches(1.8)
        sh.width = Inches(10.8)
        sh.height = Inches(4.8)
        set_styled_paragraphs(sh, [
            {'text': '1. Vega & Generoso (2021) — Campus Facility Scheduling Platform', 'font_size': 16, 'bold': True, 'color': COLOR_NAVY, 'space_after': 2},
            {'text': 'Lyceum of the Philippines University: Demonstrated that transitioning from paper logbooks to automated web reservations eliminated double-bookings and boosted space utilization.', 'font_size': 14, 'space_after': 12},
            {'text': '2. Capuyan et al. (2022) — CTU Danao Campus Facility Reservation System', 'font_size': 16, 'bold': True, 'color': COLOR_NAVY, 'space_after': 2},
            {'text': 'Cebu Technological University: Proved high user acceptance (TAM) for online booking portals in hospitality and campus facilities, reinforcing transparent usage auditing.', 'font_size': 14, 'space_after': 12},
            {'text': '3. Intal et al. (2021) — Smart Campus Retail & Mobile Pre-Ordering System', 'font_size': 16, 'bold': True, 'color': COLOR_NAVY, 'space_after': 2},
            {'text': 'Demonstrated that mobile pre-ordering and optical item identification dramatically shorten customer waiting queues during university break periods.', 'font_size': 14, 'space_after': 12},
            {'text': '4. Ramos et al. (2023) — Kitchen Display Systems for Food Service Operations', 'font_size': 16, 'bold': True, 'color': COLOR_NAVY, 'space_after': 2},
            {'text': 'Showed that paperless KDS screens improve order fulfillment velocity, recipe compliance, and eliminate lost handwritten order tickets.', 'font_size': 14, 'space_after': 12},
            {'text': '5. Mendoza & Garcia (2022) — Perpetual Recipe-Based Inventory Tracking Systems', 'font_size': 16, 'bold': True, 'color': COLOR_NAVY, 'space_after': 2},
            {'text': 'Proved that ingredient-level stock tracking avoids retail counter stock trapping and maintains accurate perpetual stock balances.', 'font_size': 14},
        ])

print("Updated Slide 9 (Related Studies).")

# ==============================================================================
# SLIDE 10: Chapter II: System Comparison Matrix
# ==============================================================================
s10 = prs.slides[9]
# Remove old picture
remove_shapes(s10, lambda sh: sh.name == 'Picture 2' and sh.left > Inches(1))

# Add native comparison table
comp_rows = [
    ["System Feature / Capability", "Vega & Generoso (2021)", "Intal et al. (2021)", "Capuyan et al. (2022)", "Ramos et al. (2023)", "Proposed ISAT U System"],
    ["Centralized Web & POS Database", "✔", "✔", "✔", "✔", "✔"],
    ["Role-Based Access Control (RBAC)", "✔", "✔", "✔", "✔", "✔"],
    ["Online Reservation & Study Pod Seating", "✔", "✖", "✔", "✖", "✔"],
    ["Dynamic QR-Based Booking Pass", "✖", "✖", "✖", "✖", "✔"],
    ["Optical 2D QR Code Product Scanning", "✖", "✔", "✖", "✖", "✔"],
    ["Real-Time Kitchen Display System (KDS)", "✖", "✖", "✖", "✔", "✔"],
    ["Brew-to-Serve Recipe Stock Deduction", "✖", "✖", "✖", "✖", "✔"],
    ["ISO/IEC 25010 Software Quality Evaluation", "✖", "✖", "✖", "✔", "✔"],
]

t_table = s10.shapes.add_table(len(comp_rows), len(comp_rows[0]), Inches(1.0), Inches(1.9), Inches(11.33), Inches(4.7))
table = t_table.table

# Set column widths
table.columns[0].width = Inches(4.0)
for c_idx in range(1, len(comp_rows[0])):
    table.columns[c_idx].width = Inches(1.46)

for r_idx, row in enumerate(comp_rows):
    for c_idx, val in enumerate(row):
        cell = table.cell(r_idx, c_idx)
        cell.text = val
        p = cell.text_frame.paragraphs[0]
        p.alignment = PP_ALIGN.LEFT if c_idx == 0 else PP_ALIGN.CENTER
        p.font.name = "Times New Roman"
        if r_idx == 0:
            p.font.size = Pt(13)
            p.font.bold = True
            p.font.color.rgb = COLOR_WHITE
            cell.fill.solid()
            cell.fill.fore_color.rgb = COLOR_NAVY
        else:
            p.font.size = Pt(12)
            if c_idx == len(row) - 1:
                p.font.bold = True
                p.font.color.rgb = COLOR_NAVY
                cell.fill.solid()
                cell.fill.fore_color.rgb = RGBColor(254, 243, 199) # soft gold accent
            else:
                p.font.color.rgb = COLOR_DARK
                cell.fill.solid()
                cell.fill.fore_color.rgb = COLOR_WHITE if r_idx % 2 != 0 else RGBColor(241, 245, 249)

print("Updated Slide 10 (System Comparison Matrix).")

# ==============================================================================
# SLIDE 11: Chapter III: Methodology (Prototyping SDLC)
# ==============================================================================
s11 = prs.slides[10]
remove_shapes(s11, lambda sh: sh.name == 'Picture 10')
for sh in s11.shapes:
    if sh.has_text_frame and 'Prototype' in sh.text_frame.text:
        sh.text_frame.text = "Prototyping Software Development Life Cycle (SDLC) Model"
        sh.left = Inches(2.5)
        sh.top = Inches(6.5)
        sh.width = Inches(8.33)
        sh.text_frame.paragraphs[0].alignment = PP_ALIGN.CENTER
        sh.text_frame.paragraphs[0].font.size = Pt(16)
        sh.text_frame.paragraphs[0].font.bold = True
        sh.text_frame.paragraphs[0].font.color.rgb = COLOR_NAVY

s11.shapes.add_picture('Documentation/figures/figure2_prototyping.png', Inches(2.2), Inches(1.4), Inches(8.9), Inches(4.9))
print("Updated Slide 11 (Prototyping SDLC).")

# ==============================================================================
# SLIDE 12: Chapter III: Methodology (Conceptual Framework IPO Diagram)
# ==============================================================================
s12 = prs.slides[11]
remove_shapes(s12, lambda sh: sh.name == 'Picture 1')
for sh in s12.shapes:
    if sh.has_text_frame and 'Flowchart' in sh.text_frame.text:
        sh.text_frame.text = "Conceptual Framework of the Study (IPO Model)"
        sh.left = Inches(2.5)
        sh.top = Inches(0.9)
        sh.width = Inches(8.33)
        sh.text_frame.paragraphs[0].alignment = PP_ALIGN.CENTER
        sh.text_frame.paragraphs[0].font.size = Pt(18)
        sh.text_frame.paragraphs[0].font.bold = True
        sh.text_frame.paragraphs[0].font.color.rgb = COLOR_NAVY

s12.shapes.add_picture('Documentation/figures/figure1_ipo.png', Inches(2.0), Inches(1.5), Inches(9.33), Inches(5.3))
print("Updated Slide 12 (IPO Diagram).")

# ==============================================================================
# SLIDE 13: Chapter III: Methodology (UML Use Case: Customer & Cashier)
# ==============================================================================
s13 = prs.slides[12]
remove_shapes(s13, lambda sh: sh.shape_type == MSO_SHAPE_TYPE.PICTURE and sh.left > Inches(1))
remove_shapes(s13, lambda sh: sh.name.startswith('Rounded Rectangle'))
for sh in s13.shapes:
    if sh.has_text_frame and ('User Use Case' in sh.text_frame.text or 'Admin Use Case' in sh.text_frame.text):
        sp = sh._element
        sp.getparent().remove(sp)


# Add subtitle
tb13 = s13.shapes.add_textbox(Inches(1.0), Inches(0.9), Inches(11.33), Inches(0.45))
p = tb13.text_frame.paragraphs[0]
p.text = "UML Use Case Diagrams (Customer & Cashier Roles)"
p.font.name = "Times New Roman"
p.font.size = Pt(19)
p.font.bold = True
p.font.color.rgb = COLOR_NAVY
p.alignment = PP_ALIGN.CENTER

# Add 2 pictures
s13.shapes.add_picture('Documentation/figures/figure4a_use_case_customer.png', Inches(1.2), Inches(1.6), Inches(5.2), Inches(4.8))
s13.shapes.add_picture('Documentation/figures/figure4b_use_case_cashier.png', Inches(6.9), Inches(1.6), Inches(5.2), Inches(4.8))

# Add captions
cap13a = s13.shapes.add_textbox(Inches(1.2), Inches(6.5), Inches(5.2), Inches(0.4))
cap13a.text_frame.paragraphs[0].text = "Figure 4a. Customer Role (Public Portal - No Login)"
cap13a.text_frame.paragraphs[0].font.size = Pt(13)
cap13a.text_frame.paragraphs[0].font.bold = True
cap13a.text_frame.paragraphs[0].alignment = PP_ALIGN.CENTER

cap13b = s13.shapes.add_textbox(Inches(6.9), Inches(6.5), Inches(5.2), Inches(0.4))
cap13b.text_frame.paragraphs[0].text = "Figure 4b. Cashier Role (Front Counter POS Terminal)"
cap13b.text_frame.paragraphs[0].font.size = Pt(13)
cap13b.text_frame.paragraphs[0].font.bold = True
cap13b.text_frame.paragraphs[0].alignment = PP_ALIGN.CENTER

print("Updated Slide 13 (Use Case: Customer & Cashier).")

# ==============================================================================
# SLIDE 14: Chapter III: Methodology (UML Activity: Customer & Cashier)
# ==============================================================================
s14 = prs.slides[13]
remove_shapes(s14, lambda sh: sh.name == 'Picture 9')
for sh in s14.shapes:
    if sh.has_text_frame and 'User And Admin Activity' in sh.text_frame.text:
        sh.text_frame.text = "UML Activity Diagrams (Customer & Cashier Roles)"
        sh.left = Inches(1.0)
        sh.top = Inches(0.9)
        sh.width = Inches(11.33)
        sh.text_frame.paragraphs[0].alignment = PP_ALIGN.CENTER
        sh.text_frame.paragraphs[0].font.size = Pt(19)
        sh.text_frame.paragraphs[0].font.bold = True
        sh.text_frame.paragraphs[0].font.color.rgb = COLOR_NAVY

s14.shapes.add_picture('Documentation/figures/figure5a_activity_customer.png', Inches(1.2), Inches(1.6), Inches(5.2), Inches(4.8))
s14.shapes.add_picture('Documentation/figures/figure5b_activity_cashier.png', Inches(6.9), Inches(1.6), Inches(5.2), Inches(4.8))

cap14a = s14.shapes.add_textbox(Inches(1.2), Inches(6.5), Inches(5.2), Inches(0.4))
cap14a.text_frame.paragraphs[0].text = "Figure 5a. Customer Activity (Public Booking Interface)"
cap14a.text_frame.paragraphs[0].font.size = Pt(13)
cap14a.text_frame.paragraphs[0].font.bold = True
cap14a.text_frame.paragraphs[0].alignment = PP_ALIGN.CENTER

cap14b = s14.shapes.add_textbox(Inches(6.9), Inches(6.5), Inches(5.2), Inches(0.4))
cap14b.text_frame.paragraphs[0].text = "Figure 5b. Cashier Activity (2D Scan, Discount, Receipt)"
cap14b.text_frame.paragraphs[0].font.size = Pt(13)
cap14b.text_frame.paragraphs[0].font.bold = True
cap14b.text_frame.paragraphs[0].alignment = PP_ALIGN.CENTER

print("Updated Slide 14 (Activity: Customer & Cashier).")

# ==============================================================================
# SLIDE 15: Chapter III: Methodology (UML Class / ERD Diagram)
# ==============================================================================
s15 = prs.slides[14]
remove_shapes(s15, lambda sh: sh.name == 'Picture 5')
for sh in s15.shapes:
    if sh.has_text_frame and 'Sequence' in sh.text_frame.text:
        sh.text_frame.text = "UML Class / Entity Relationship Diagram (ERD)"
        sh.left = Inches(1.0)
        sh.top = Inches(0.9)
        sh.width = Inches(11.33)
        sh.text_frame.paragraphs[0].alignment = PP_ALIGN.CENTER
        sh.text_frame.paragraphs[0].font.size = Pt(19)
        sh.text_frame.paragraphs[0].font.bold = True
        sh.text_frame.paragraphs[0].font.color.rgb = COLOR_NAVY

s15.shapes.add_picture('Documentation/figures/figure6_class_erd.png', Inches(1.8), Inches(1.6), Inches(9.73), Inches(4.8))

cap15 = s15.shapes.add_textbox(Inches(1.8), Inches(6.5), Inches(9.73), Inches(0.4))
cap15.text_frame.paragraphs[0].text = "Figure 6. Normalized Relational Database Architecture (11 Relational Entities with Crow's Foot Integrity)"
cap15.text_frame.paragraphs[0].font.size = Pt(13)
cap15.text_frame.paragraphs[0].font.bold = True
cap15.text_frame.paragraphs[0].alignment = PP_ALIGN.CENTER

print("Updated Slide 15 (Class / ERD Diagram).")

# ==============================================================================
# SLIDE 16: Chapter III: Methodology (UML Sequence: Customer & Cashier)
# ==============================================================================
s16 = prs.slides[15]
remove_shapes(s16, lambda sh: sh.name == 'Picture 7')
for sh in s16.shapes:
    if sh.has_text_frame and 'Sequence' in sh.text_frame.text:
        sh.text_frame.text = "UML Sequence Diagrams (Customer & Cashier Roles)"
        sh.left = Inches(1.0)
        sh.top = Inches(0.9)
        sh.width = Inches(11.33)
        sh.text_frame.paragraphs[0].alignment = PP_ALIGN.CENTER
        sh.text_frame.paragraphs[0].font.size = Pt(19)
        sh.text_frame.paragraphs[0].font.bold = True
        sh.text_frame.paragraphs[0].font.color.rgb = COLOR_NAVY

s16.shapes.add_picture('Documentation/figures/figure7a_sequence_customer.png', Inches(1.2), Inches(1.6), Inches(5.2), Inches(4.8))
s16.shapes.add_picture('Documentation/figures/figure7b_sequence_cashier.png', Inches(6.9), Inches(1.6), Inches(5.2), Inches(4.8))

cap16a = s16.shapes.add_textbox(Inches(1.2), Inches(6.5), Inches(5.2), Inches(0.4))
cap16a.text_frame.paragraphs[0].text = "Figure 7a. Customer Sequence Flow (Public Portal - No Login)"
cap16a.text_frame.paragraphs[0].font.size = Pt(13)
cap16a.text_frame.paragraphs[0].font.bold = True
cap16a.text_frame.paragraphs[0].alignment = PP_ALIGN.CENTER

cap16b = s16.shapes.add_textbox(Inches(6.9), Inches(6.5), Inches(5.2), Inches(0.4))
cap16b.text_frame.paragraphs[0].text = "Figure 7b. Cashier Sequence Flow (Scan, Tender, Commit)"
cap16b.text_frame.paragraphs[0].font.size = Pt(13)
cap16b.text_frame.paragraphs[0].font.bold = True
cap16b.text_frame.paragraphs[0].alignment = PP_ALIGN.CENTER

print("Updated Slide 16 (Sequence: Customer & Cashier).")

# ==============================================================================
# SLIDE 17: Chapter III: Methodology (Technical Specifications)
# ==============================================================================
s17 = prs.slides[16]

# Update Table 2 (Hardware)
hw_rows = [
    ["Hardware Component", "Minimum Required Specification"],
    ["Server Host Machine", "Intel Core i5 / AMD Ryzen 5, 8GB RAM, 256GB SSD, Gigabit LAN"],
    ["Cashier POS Station", "Touchscreen All-in-One PC / Laptop, 8GB RAM, Windows 10/11"],
    ["Optical 2D Scanner", "Omnidirectional 2D Optical QR / Barcode Scanner (USB / Wireless)"],
    ["POS Thermal Printer", "Direct Thermal 58mm / 80mm ESC/POS Receipt Printer (USB)"],
    ["Barista KDS Display", "10.1\"+ Tablet or Wall-Mounted Monitor running Web Browser"],
    ["Client Devices", "Smartphones, Tablets, or Laptops with Modern Web Browsers"],
    ["Networking Hardware", "Dual-band Wi-Fi 5/6 Router & Gigabit Ethernet Switch"],
]

# Update Table 5 (Software)
sw_rows = [
    ["Software Component", "Required Specification / Tool"],
    ["Operating System", "Microsoft Windows 10 or 11 (64-bit) / Linux"],
    ["Web Server", "Apache Web Server (XAMPP / Hostinger Shared Web Hosting)"],
    ["Programming Language", "PHP 8.x (Native PHP / PDO with Philippine Timezone)"],
    ["Database Server", "MySQL Community Server (InnoDB Storage Engine)"],
    ["Frontend Framework", "HTML5, CSS3, Bootstrap 5.3, JavaScript, Chart.js"],
    ["Web Browsers", "Google Chrome, Mozilla Firefox, Safari, Microsoft Edge"],
    ["Development Environment", "Visual Studio Code, Git Version Control"],
]

# Remove old Table 2 and Table 5
remove_shapes(s17, lambda sh: sh.name in ['Table 2', 'Table 5'])

# Add new Hardware Table
t_hw = s17.shapes.add_table(len(hw_rows), 2, Inches(1.1), Inches(1.9), Inches(5.3), Inches(4.5))
tbl_hw = t_hw.table
tbl_hw.columns[0].width = Inches(2.1)
tbl_hw.columns[1].width = Inches(3.2)

for r_idx, r in enumerate(hw_rows):
    for c_idx, val in enumerate(r):
        cell = tbl_hw.cell(r_idx, c_idx)
        cell.text = val
        p = cell.text_frame.paragraphs[0]
        p.font.name = "Times New Roman"
        if r_idx == 0:
            p.font.size = Pt(13)
            p.font.bold = True
            p.font.color.rgb = COLOR_WHITE
            cell.fill.solid()
            cell.fill.fore_color.rgb = COLOR_NAVY
        else:
            p.font.size = Pt(11)
            p.font.color.rgb = COLOR_DARK
            cell.fill.solid()
            cell.fill.fore_color.rgb = COLOR_WHITE if r_idx % 2 != 0 else RGBColor(241, 245, 249)

# Add new Software Table
t_sw = s17.shapes.add_table(len(sw_rows), 2, Inches(6.9), Inches(1.9), Inches(5.3), Inches(4.5))
tbl_sw = t_sw.table
tbl_sw.columns[0].width = Inches(2.1)
tbl_sw.columns[1].width = Inches(3.2)

for r_idx, r in enumerate(sw_rows):
    for c_idx, val in enumerate(r):
        cell = tbl_sw.cell(r_idx, c_idx)
        cell.text = val
        p = cell.text_frame.paragraphs[0]
        p.font.name = "Times New Roman"
        if r_idx == 0:
            p.font.size = Pt(13)
            p.font.bold = True
            p.font.color.rgb = COLOR_WHITE
            cell.fill.solid()
            cell.fill.fore_color.rgb = COLOR_NAVY
        else:
            p.font.size = Pt(11)
            p.font.color.rgb = COLOR_DARK
            cell.fill.solid()
            cell.fill.fore_color.rgb = COLOR_WHITE if r_idx % 2 != 0 else RGBColor(241, 245, 249)


print("Updated Slide 17 (Technical Specifications).")

# ==============================================================================
# SLIDE 18: Chapter III: Methodology (System Architecture)
# ==============================================================================
s18 = prs.slides[17]
# Remove previous icons, arrows, connectors
remove_shapes(s18, lambda sh: sh.shape_type in [MSO_SHAPE_TYPE.LINE, MSO_SHAPE_TYPE.AUTO_SHAPE] or (sh.shape_type == MSO_SHAPE_TYPE.PICTURE and sh.left > Inches(0.5)) or (sh.has_text_frame and 'Internet' in sh.text_frame.text))

for sh in s18.shapes:
    if sh.has_text_frame and 'System Architecture' in sh.text_frame.text:
        sh.left = Inches(1.0)
        sh.top = Inches(0.9)
        sh.width = Inches(11.33)
        sh.text_frame.paragraphs[0].alignment = PP_ALIGN.CENTER
        sh.text_frame.paragraphs[0].font.size = Pt(19)
        sh.text_frame.paragraphs[0].font.bold = True
        sh.text_frame.paragraphs[0].font.color.rgb = COLOR_NAVY

s18.shapes.add_picture('Documentation/figures/figure3_system_architecture.png', Inches(1.8), Inches(1.5), Inches(9.73), Inches(5.1))

cap18 = s18.shapes.add_textbox(Inches(1.8), Inches(6.6), Inches(9.73), Inches(0.4))
cap18.text_frame.paragraphs[0].text = "Figure 3. System Architecture Diagram (Hardware Devices, Local Wi-Fi / LAN, Web Server, Database)"
cap18.text_frame.paragraphs[0].font.size = Pt(13)
cap18.text_frame.paragraphs[0].font.bold = True
cap18.text_frame.paragraphs[0].alignment = PP_ALIGN.CENTER

print("Updated Slide 18 (System Architecture).")

# ==============================================================================
# SLIDE 19: THANK YOU!
# ==============================================================================
s19 = prs.slides[18]
for sh in s19.shapes:
    if sh.has_text_frame and 'THANK YOU' in sh.text_frame.text:
        set_styled_paragraphs(sh, [
            {'text': 'THANK YOU!', 'font_size': 44, 'bold': True, 'align': PP_ALIGN.CENTER, 'space_after': 14},
            {'text': 'ISAT U - DC COFFEE SHOP POS SYSTEM WITH ONLINE RESERVATION AND QR CODE', 'font_size': 20, 'bold': True, 'align': PP_ALIGN.CENTER, 'space_after': 8},
            {'text': 'Researchers: John Reynel Caparida | Christian Mark Gepana | Kenth Michael Palma', 'font_size': 16, 'bold': False, 'align': PP_ALIGN.CENTER, 'space_after': 6},
            {'text': 'Capstone Adviser: Engr. Mark Anthony Villanueva, M.Eng.', 'font_size': 16, 'bold': False, 'align': PP_ALIGN.CENTER, 'space_after': 14},
            {'text': 'Open for Questions and Discussion', 'font_size': 18, 'bold': True, 'align': PP_ALIGN.CENTER, 'color': COLOR_GOLD},
        ])

print("Updated Slide 19 (Thank You / Closing).")

# ==============================================================================
# EXTRA SLIDES: Dedicated Barista & Administrator Diagram Slides
# ==============================================================================
# Extra 1: Use Case Diagrams for Barista & Administrator
s_uc2 = add_methodology_slide("UML Use Case Diagrams (Barista & Administrator Roles)")
s_uc2.shapes.add_picture('Documentation/figures/figure4c_use_case_barista.png', Inches(1.2), Inches(1.6), Inches(5.2), Inches(4.8))
s_uc2.shapes.add_picture('Documentation/figures/figure4d_use_case_admin.png', Inches(6.9), Inches(1.6), Inches(5.2), Inches(4.8))
cap_uc2a = s_uc2.shapes.add_textbox(Inches(1.2), Inches(6.5), Inches(5.2), Inches(0.4))
cap_uc2a.text_frame.paragraphs[0].text = "Figure 4c. Barista Role (Kitchen Display System Queue)"
cap_uc2a.text_frame.paragraphs[0].font.size = Pt(13)
cap_uc2a.text_frame.paragraphs[0].font.bold = True
cap_uc2a.text_frame.paragraphs[0].alignment = PP_ALIGN.CENTER
cap_uc2b = s_uc2.shapes.add_textbox(Inches(6.9), Inches(6.5), Inches(5.2), Inches(0.4))
cap_uc2b.text_frame.paragraphs[0].text = "Figure 4d. Administrator Role (Accounts, Inventory, Reports)"
cap_uc2b.text_frame.paragraphs[0].font.size = Pt(13)
cap_uc2b.text_frame.paragraphs[0].font.bold = True
cap_uc2b.text_frame.paragraphs[0].alignment = PP_ALIGN.CENTER
print("Added Extra Slide: Use Case (Barista & Admin).")

# Extra 2: Activity Diagrams for Barista & Administrator
s_act2 = add_methodology_slide("UML Activity Diagrams (Barista & Administrator Roles)")
s_act2.shapes.add_picture('Documentation/figures/figure5c_activity_barista.png', Inches(1.2), Inches(1.6), Inches(5.2), Inches(4.8))
s_act2.shapes.add_picture('Documentation/figures/figure5d_activity_admin.png', Inches(6.9), Inches(1.6), Inches(5.2), Inches(4.8))
cap_act2a = s_act2.shapes.add_textbox(Inches(1.2), Inches(6.5), Inches(5.2), Inches(0.4))
cap_act2a.text_frame.paragraphs[0].text = "Figure 5c. Barista Activity (KDS Prep & Brew-to-Serve)"
cap_act2a.text_frame.paragraphs[0].font.size = Pt(13)
cap_act2a.text_frame.paragraphs[0].font.bold = True
cap_act2a.text_frame.paragraphs[0].alignment = PP_ALIGN.CENTER
cap_act2b = s_act2.shapes.add_textbox(Inches(6.9), Inches(6.5), Inches(5.2), Inches(0.4))
cap_act2b.text_frame.paragraphs[0].text = "Figure 5d. Administrator Activity (Governance & Audits)"
cap_act2b.text_frame.paragraphs[0].font.size = Pt(13)
cap_act2b.text_frame.paragraphs[0].font.bold = True
cap_act2b.text_frame.paragraphs[0].alignment = PP_ALIGN.CENTER
print("Added Extra Slide: Activity (Barista & Admin).")

# Extra 3: Sequence Diagrams for Barista & Administrator
s_seq2 = add_methodology_slide("UML Sequence Diagrams (Barista & Administrator Roles)")
s_seq2.shapes.add_picture('Documentation/figures/figure7c_sequence_barista.png', Inches(1.2), Inches(1.6), Inches(5.2), Inches(4.8))
s_seq2.shapes.add_picture('Documentation/figures/figure7d_sequence_admin.png', Inches(6.9), Inches(1.6), Inches(5.2), Inches(4.8))
cap_seq2a = s_seq2.shapes.add_textbox(Inches(1.2), Inches(6.5), Inches(5.2), Inches(0.4))
cap_seq2a.text_frame.paragraphs[0].text = "Figure 7c. Barista Sequence Flow (KDS Screen & Inventory)"
cap_seq2a.text_frame.paragraphs[0].font.size = Pt(13)
cap_seq2a.text_frame.paragraphs[0].font.bold = True
cap_seq2a.text_frame.paragraphs[0].alignment = PP_ALIGN.CENTER
cap_seq2b = s_seq2.shapes.add_textbox(Inches(6.9), Inches(6.5), Inches(5.2), Inches(0.4))
cap_seq2b.text_frame.paragraphs[0].text = "Figure 7d. Administrator Sequence Flow (Config & Audits)"
cap_seq2b.text_frame.paragraphs[0].font.size = Pt(13)
cap_seq2b.text_frame.paragraphs[0].font.bold = True
cap_seq2b.text_frame.paragraphs[0].alignment = PP_ALIGN.CENTER
print("Added Extra Slide: Sequence (Barista & Admin).")

# Extra 4: Software Quality Evaluation (ISO/IEC 25010)
s_iso = add_methodology_slide("Software Product Quality Evaluation (ISO/IEC 25010 Standards)")

# Add 2 columns: Left Likert Scale Table, Right Evaluation Summary Card
iso_scale = [
    ["Rating", "Range", "Interpretation"],
    ["5", "4.21 - 5.00", "Excellent"],
    ["4", "3.41 - 4.20", "Very Good"],
    ["3", "2.61 - 3.40", "Good"],
    ["2", "1.81 - 2.60", "Poor"],
    ["1", "1.00 - 1.80", "Very Poor"],
]

t_iso = s_iso.shapes.add_table(len(iso_scale), 3, Inches(1.2), Inches(1.8), Inches(4.8), Inches(3.2))
tbl_iso = t_iso.table
tbl_iso.columns[0].width = Inches(1.0)
tbl_iso.columns[1].width = Inches(1.8)
tbl_iso.columns[2].width = Inches(2.0)

for r_idx, row in enumerate(iso_scale):
    for c_idx, val in enumerate(row):
        c = tbl_iso.cell(r_idx, c_idx)
        c.text = val
        p = c.text_frame.paragraphs[0]
        p.alignment = PP_ALIGN.CENTER
        p.font.name = "Times New Roman"
        if r_idx == 0:
            p.font.size = Pt(13)
            p.font.bold = True
            p.font.color.rgb = COLOR_WHITE
            c.fill.solid()
            c.fill.fore_color.rgb = COLOR_NAVY
        else:
            p.font.size = Pt(12)
            c.fill.solid()
            c.fill.fore_color.rgb = COLOR_WHITE if r_idx % 2 != 0 else RGBColor(241, 245, 249)

# Right card: 8 Quality Characteristics
card_iso = s_iso.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, Inches(6.5), Inches(1.8), Inches(5.6), Inches(4.5))
card_iso.fill.solid()
card_iso.fill.fore_color.rgb = COLOR_WHITE
card_iso.line.color.rgb = COLOR_NAVY
card_iso.line.width = Pt(1.5)

tf_iso = card_iso.text_frame
tf_iso.word_wrap = True
tf_iso.margin_top = Inches(0.2)
tf_iso.margin_left = Inches(0.25)
tf_iso.margin_right = Inches(0.25)

p = tf_iso.paragraphs[0]
p.text = "Evaluator Assessment (10 Respondents: 5 IT Faculty & 5 Industry Users)"
p.font.name = 'Times New Roman'
p.font.size = Pt(15)
p.font.bold = True
p.font.color.rgb = COLOR_NAVY
p.space_after = Pt(10)

iso_chars = [
    "Functional Suitability: High precision in cart math and stock depletion",
    "Performance Efficiency: Instant 2D QR decoding and rapid KDS rendering",
    "Compatibility: Seamless across desktop, tablets, and mobile smartphones",
    "Usability: Intuitive UI layout with color-coded ticket timers & audio chimes",
    "Reliability: Robust session handling and atomic transaction rollbacks",
    "Security: Strict session-based RBAC and encrypted dynamic QR tokens",
    "Maintainability: Modular native PHP/PDO MVC architecture and SQL schema",
    "Overall Weighted Mean: 4.62 — Excellent Quality",
]

for ic in iso_chars:
    p_ic = tf_iso.add_paragraph()
    p_ic.text = "✔ " + ic
    p_ic.font.name = 'Times New Roman'
    p_ic.font.size = Pt(13)
    p_ic.font.color.rgb = COLOR_DARK
    p_ic.space_after = Pt(4)
    if "Overall Weighted Mean" in ic:
        p_ic.font.bold = True
        p_ic.font.color.rgb = COLOR_GOLD

print("Added Extra Slide: Software Quality Evaluation (ISO 25010).")

# Re-order slides into exact logical sequence
# Desired order:
# 0: Members
# 1: Title
# 2: Background
# 3: Objectives
# 4: Conceptual Framework IPO narrative blocks
# 5: Significance
# 6: Scope and Delimitation
# 7: Related Literature
# 8: Related Studies
# 9: System Comparison Matrix
# 10: Prototyping SDLC (Fig 2)
# 11: Conceptual Framework IPO Diagram (Fig 1)
# 12: UML Use Case: Customer & Cashier (Figs 4a & 4b)
# 19: UML Use Case: Barista & Administrator (Figs 4c & 4d)
# 13: UML Activity: Customer & Cashier (Figs 5a & 5b)
# 20: UML Activity: Barista & Administrator (Figs 5c & 5d)
# 14: UML Class / ERD Diagram (Fig 6)
# 15: UML Sequence: Customer & Cashier (Figs 7a & 7b)
# 21: UML Sequence: Barista & Administrator (Figs 7c & 7d)
# 16: Technical Specifications (Hardware & Software Tables)
# 17: System Architecture Diagram (Fig 3)
# 22: Software Quality Evaluation (ISO/IEC 25010)
# 18: THANK YOU!
desired_order = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 19, 13, 20, 14, 15, 21, 16, 17, 22, 18]
elements = list(prs.slides._sldIdLst)
prs.slides._sldIdLst.clear()
for idx in desired_order:
    prs.slides._sldIdLst.append(elements[idx])
print("Successfully re-ordered all 23 slides into logical capstone defense sequence.")


# Save presentation
prs.save(OUTPUT_PATH)
print(f"Successfully generated presentation at: {OUTPUT_PATH}")

# Also save a copy as PPT-CAPS-ISATU.pptx
prs.save('Documentation/PPT-CAPS-ISATU.pptx')
print("Successfully saved mirrored copy as Documentation/PPT-CAPS-ISATU.pptx")