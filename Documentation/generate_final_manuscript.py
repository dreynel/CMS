# -*- coding: utf-8 -*-
"""
Final Manuscript Generator for ISAT U Coffee Shop POS System with Online Reservation and QR Code
Builds Documentation/ISAT_U_Coffee_Shop_POS_System_Manuscript_Ch1-3.docx
100% compliant with Capstone_Manuscript_Template_Ch1-3.docx and BIMS - Chapter 123.docx
No long dashes, simpler natural undergraduate tone, and real PlantUML diagram images embedded.
"""

import os
import json
import docx
from docx import Document
from docx.shared import Inches, Pt, RGBColor
from docx.enum.text import WD_ALIGN_PARAGRAPH
from docx.enum.table import WD_TABLE_ALIGNMENT, WD_ALIGN_VERTICAL
from docx.oxml import OxmlElement, parse_xml
from docx.oxml.ns import qn, nsdecls

def set_cell_background(cell, fill_hex):
    tcPr = cell._tc.get_or_add_tcPr()
    shd = parse_xml(f'<w:shd {nsdecls("w")} w:fill="{fill_hex}"/>')
    tcPr.append(shd)

def set_cell_margins(cell, top=100, bottom=100, left=140, right=140):
    tcPr = cell._tc.get_or_add_tcPr()
    tcMar = OxmlElement('w:tcMar')
    for m, val in [('top', top), ('bottom', bottom), ('left', left), ('right', right)]:
        node = OxmlElement(f'w:{m}')
        node.set(qn('w:w'), str(val))
        node.set(qn('w:type'), 'dxa')
        tcMar.append(node)
    tcPr.append(tcMar)

def set_cell_border(cell, **kwargs):
    tcPr = cell._tc.get_or_add_tcPr()
    tcBorders = OxmlElement('w:tcBorders')
    for edge in ('top', 'left', 'bottom', 'right', 'insideH', 'insideV'):
        edge_data = kwargs.get(edge)
        if edge_data:
            tag = f'w:{edge}'
            element = OxmlElement(tag)
            element.set(qn('w:val'), edge_data.get('val', 'single'))
            element.set(qn('w:sz'), str(edge_data.get('sz', 4)))
            element.set(qn('w:space'), '0')
            element.set(qn('w:color'), edge_data.get('color', 'auto'))
            tcBorders.append(element)
    tcPr.append(tcBorders)

def add_placeholder_box(doc, caption_text, content_text):
    tbl = doc.add_table(rows=1, cols=1)
    tbl.alignment = WD_TABLE_ALIGNMENT.CENTER
    cell = tbl.cell(0, 0)
    set_cell_background(cell, 'F8F9FA')
    set_cell_margins(cell, top=120, bottom=120, left=180, right=180)
    border_spec = {'val': 'single', 'sz': 6, 'color': '002080'}
    set_cell_border(cell, top=border_spec, bottom=border_spec, left=border_spec, right=border_spec)
    cell.width = Inches(5.8)
    p = cell.paragraphs[0]
    p.alignment = WD_ALIGN_PARAGRAPH.CENTER
    p.paragraph_format.line_spacing = 1.15
    p.paragraph_format.space_before = Pt(4)
    p.paragraph_format.space_after = Pt(4)
    
    run_cap = p.add_run(f"[{caption_text}]\n")
    run_cap.font.name = "Times New Roman"
    run_cap.font.size = Pt(10)
    run_cap.font.bold = True
    run_cap.font.color.rgb = RGBColor(0x00, 0x20, 0x80)
    
    run_desc = p.add_run(content_text)
    run_desc.font.name = "Times New Roman"
    run_desc.font.size = Pt(9.5)
    run_desc.font.italic = True
    
    p_after = doc.add_paragraph()
    p_after.paragraph_format.space_before = Pt(0)
    p_after.paragraph_format.space_after = Pt(6)

def add_figure_image(doc, img_path, caption_text, width_inches=5.6):
    if os.path.exists(img_path):
        p = doc.add_paragraph()
        p.alignment = WD_ALIGN_PARAGRAPH.CENTER
        p.paragraph_format.space_before = Pt(8)
        p.paragraph_format.space_after = Pt(4)
        run = p.add_run()
        run.add_picture(img_path, width=Inches(width_inches))
    else:
        add_placeholder_box(doc, caption_text, "Diagram Image Placeholder")
    
    p_cap = doc.add_paragraph(caption_text, style='Figure Caption')
    p_cap.alignment = WD_ALIGN_PARAGRAPH.CENTER
    p_cap.paragraph_format.space_after = Pt(8)

def style_data_table(table, col_widths, headers, data, align_cols=None):
    table.alignment = WD_TABLE_ALIGNMENT.CENTER
    header_tr = table.rows[0]
    for idx, heading in enumerate(headers):
        cell = header_tr.cells[idx]
        cell.text = heading
        set_cell_background(cell, 'EAEFF8')
        set_cell_margins(cell, top=120, bottom=120, left=140, right=140)
        border_top = {'val': 'single', 'sz': 8, 'color': '002080'}
        border_bot = {'val': 'single', 'sz': 8, 'color': '002080'}
        set_cell_border(cell, top=border_top, bottom=border_bot)
        cell.paragraphs[0].alignment = WD_ALIGN_PARAGRAPH.CENTER
        for run in cell.paragraphs[0].runs:
            run.font.name = 'Times New Roman'
            run.font.size = Pt(10)
            run.font.bold = True
            run.font.color.rgb = RGBColor(0x00, 0x20, 0x80)
            
    for row_idx, row_data in enumerate(data):
        row_cells = table.rows[row_idx + 1].cells
        bg_color = 'FFFFFF' if row_idx % 2 == 0 else 'F8FAFC'
        is_last = (row_idx == len(data) - 1)
        for col_idx, cell_value in enumerate(row_data):
            cell = row_cells[col_idx]
            cell.text = str(cell_value)
            set_cell_background(cell, bg_color)
            set_cell_margins(cell, top=100, bottom=100, left=130, right=130)
            border_bottom = {'val': 'single', 'sz': 8 if is_last else 4, 'color': '002080' if is_last else 'CBD5E1'}
            set_cell_border(cell, bottom=border_bottom)
            align = WD_ALIGN_PARAGRAPH.LEFT
            if align_cols and col_idx in align_cols:
                align = align_cols[col_idx]
            cell.paragraphs[0].alignment = align
            for run in cell.paragraphs[0].runs:
                run.font.name = 'Times New Roman'
                run.font.size = Pt(9.5)
                if row_data[0] == 'Overall Grand Mean' or row_data[0] == '5':
                    if col_idx == 0 or row_data[0] == 'Overall Grand Mean':
                        run.font.bold = True

    for row in table.rows:
        for idx, width in enumerate(col_widths):
            row.cells[idx].width = Inches(width)

def main():
    template_path = 'Documentation/Capstone_Manuscript_Template_Ch1-3.docx'
    json_path = 'Documentation/manuscript_data.json'
    output_path = 'Documentation/ISAT_U_Coffee_Shop_POS_System_Manuscript_Ch1-3.docx'
    
    with open(json_path, encoding='utf-8') as f:
        data = json.load(f)
        
    doc = Document(template_path)
    
    # Clear existing template placeholder paragraphs and tables
    for p in [p._p for p in doc.paragraphs]:
        p.getparent().remove(p)
    for t in [t._tbl for t in doc.tables]:
        t.getparent().remove(t)
        
    print("Template initialized. Writing simplified undergraduate manuscript...")
    
    fm = data['front_matter']
    c1 = data['chapter_1']
    c2 = data['chapter_2']
    c3 = data['chapter_3']
    refs = data['references']
    app = data['appendices']
    
    # =========================================================================
    # 1. TITLE PAGE
    # =========================================================================
    p_title = doc.add_paragraph()
    p_title.alignment = WD_ALIGN_PARAGRAPH.CENTER
    p_title.paragraph_format.space_before = Pt(72)
    p_title.paragraph_format.space_after = Pt(36)
    p_title.paragraph_format.line_spacing = 1.3
    run = p_title.add_run(fm['title'])
    run.font.name = 'Times New Roman'
    run.font.size = Pt(14)
    run.font.bold = True
    
    p_sub = doc.add_paragraph()
    p_sub.alignment = WD_ALIGN_PARAGRAPH.CENTER
    p_sub.paragraph_format.space_after = Pt(36)
    run = p_sub.add_run(fm['project_type'])
    run.font.name = 'Times New Roman'
    run.font.size = Pt(12)
    
    p_pres = doc.add_paragraph()
    p_pres.alignment = WD_ALIGN_PARAGRAPH.CENTER
    p_pres.paragraph_format.space_after = Pt(36)
    p_pres.paragraph_format.line_spacing = 1.3
    for line in fm['presentation_lines']:
        run = p_pres.add_run(line + '\n')
        run.font.name = 'Times New Roman'
        run.font.size = Pt(12)
        
    p_ful = doc.add_paragraph()
    p_ful.alignment = WD_ALIGN_PARAGRAPH.CENTER
    p_ful.paragraph_format.space_after = Pt(36)
    p_ful.paragraph_format.line_spacing = 1.3
    for line in fm['fulfillment_lines']:
        run = p_ful.add_run(line + '\n')
        run.font.name = 'Times New Roman'
        run.font.size = Pt(12)
        
    p_by = doc.add_paragraph()
    p_by.alignment = WD_ALIGN_PARAGRAPH.CENTER
    p_by.paragraph_format.space_after = Pt(48)
    p_by.paragraph_format.line_spacing = 1.3
    p_by.add_run("by:\n\n").font.name = 'Times New Roman'
    for author in fm['authors']:
        r = p_by.add_run(author + '\n')
        r.font.name = 'Times New Roman'
        r.font.size = Pt(12)
        r.font.bold = True
        
    p_date = doc.add_paragraph()
    p_date.alignment = WD_ALIGN_PARAGRAPH.CENTER
    r = p_date.add_run(fm['date_str'])
    r.font.name = 'Times New Roman'
    r.font.size = Pt(12)
    
    doc.add_page_break()
    
    # =========================================================================
    # 2. APPROVAL SHEET
    # =========================================================================
    doc.add_paragraph("APPROVAL SHEET", style='Front Matter Heading')
    
    p = doc.add_paragraph(
        f'This capstone project is entitled "{fm["title"]}", prepared and submitted by '
        f'{", ".join(fm["authors"])}, in partial fulfillment of the requirements for the degree '
        f'Bachelor of Science in Information Technology, and is hereby recommended for approval.',
        style='Body Text Indented'
    )
    
    p_adv = doc.add_paragraph()
    p_adv.alignment = WD_ALIGN_PARAGRAPH.CENTER
    p_adv.paragraph_format.space_before = Pt(24)
    p_adv.paragraph_format.space_after = Pt(24)
    r1 = p_adv.add_run(f"{fm['adviser']}\n")
    r1.font.bold = True
    r2 = p_adv.add_run("Capstone Project Adviser")
    
    p_com = doc.add_paragraph(
        f'It passed the final defense on {fm["date_str"]}, and was approved by the Defense Committee '
        f'in {fm["date_str"]}, with a grade of PASSED.',
        style='Body Text Indented'
    )
    
    p_chair = doc.add_paragraph()
    p_chair.alignment = WD_ALIGN_PARAGRAPH.CENTER
    p_chair.paragraph_format.space_before = Pt(24)
    p_chair.paragraph_format.space_after = Pt(24)
    r1 = p_chair.add_run(f"{fm['panel_chair']}\n")
    r1.font.bold = True
    r2 = p_chair.add_run("Panel Chair / Subject Instructor")
    
    p_mem = doc.add_paragraph()
    p_mem.alignment = WD_ALIGN_PARAGRAPH.CENTER
    p_mem.paragraph_format.space_after = Pt(24)
    p_mem.paragraph_format.line_spacing = 1.3
    for mem in fm['committee_members']:
        r = p_mem.add_run(f"{mem}                                ")
        r.font.bold = True
    p_mem.add_run("\nMember                                                              Member")
    
    p_acc = doc.add_paragraph(
        "Accepted and approved for partial fulfillment of the requirements for the degree Bachelor of Science in Information Technology.",
        style='Body Text Indented'
    )
    
    p_dean = doc.add_paragraph()
    p_dean.alignment = WD_ALIGN_PARAGRAPH.CENTER
    p_dean.paragraph_format.space_before = Pt(24)
    r1 = p_dean.add_run(f"{fm['dean']}\n")
    r1.font.bold = True
    r2 = p_dean.add_run(f"{fm['dean_title']}\nDate: {fm['date_str']}")
    
    doc.add_page_break()
    
    # =========================================================================
    # 3. ACKNOWLEDGEMENT
    # =========================================================================
    doc.add_paragraph("ACKNOWLEDGEMENT", style='Front Matter Heading')
    for p_text in fm['acknowledgement_paras']:
        doc.add_paragraph(p_text, style='Body Text Indented')
        
    p_sig = doc.add_paragraph()
    p_sig.alignment = WD_ALIGN_PARAGRAPH.RIGHT
    p_sig.paragraph_format.space_before = Pt(24)
    p_sig.add_run("J.R.C.\nC.M.G.\nK.M.P.\n" + fm['date_str']).font.name = 'Times New Roman'
    
    doc.add_page_break()
    
    # =========================================================================
    # 4. ABSTRACT
    # =========================================================================
    p_t_abs = doc.add_paragraph()
    p_t_abs.alignment = WD_ALIGN_PARAGRAPH.CENTER
    r = p_t_abs.add_run(fm['title'])
    r.font.bold = True
    
    p_auth_abs = doc.add_paragraph()
    p_auth_abs.alignment = WD_ALIGN_PARAGRAPH.CENTER
    p_auth_abs.paragraph_format.space_after = Pt(18)
    for auth in fm['authors']:
        p_auth_abs.add_run(auth + "\n")
        
    doc.add_paragraph("ABSTRACT", style='Front Matter Heading (no break)')
    p_abs = doc.add_paragraph(fm['abstract'], style='Body Text Indented')
    p_abs.alignment = WD_ALIGN_PARAGRAPH.JUSTIFY
    
    p_keys = doc.add_paragraph()
    p_keys.paragraph_format.space_before = Pt(12)
    p_keys.add_run("Keywords: ").font.bold = True
    p_keys.add_run("Point of Sale (POS), Barcode Auto-Read, QR Code Scanning, Online Study Pod Reservation, Kitchen Display System (KDS), ISO 25010, Laravel, ISAT U")
    
    doc.add_page_break()
    
    # =========================================================================
    # 5. TABLE OF CONTENTS
    # =========================================================================
    doc.add_paragraph("TABLE OF CONTENTS", style='Front Matter Heading')
    
    toc_items = [
        ("TITLE PAGE", "i"),
        ("APPROVAL SHEET", "ii"),
        ("ACKNOWLEDGEMENT", "iii"),
        ("ABSTRACT", "iv"),
        ("TABLE OF CONTENTS", "v"),
        ("LIST OF TABLES", "vi"),
        ("LIST OF FIGURES", "vii"),
        ("LIST OF APPENDICES", "viii"),
        ("Chapter I: INTRODUCTION", "1"),
        ("    Background of the Study", "1"),
        ("    Objectives of the Study", "4"),
        ("    Conceptual Framework of the Study", "5"),
        ("    Definition of Terms", "7"),
        ("    Significance of the Study", "10"),
        ("    Scope and Limitations of the Study", "12"),
        ("Chapter II: REVIEW OF RELATED LITERATURE AND STUDIES", "16"),
        ("    Related Literature", "16"),
        ("    Related Studies", "20"),
        ("    Table of Comparison", "24"),
        ("    Synthesis", "25"),
        ("Chapter III: METHODOLOGY", "27"),
        ("    Project Description", "27"),
        ("    Project Development Process", "29"),
        ("    UML Diagrams", "33"),
        ("        Use Case Diagram", "33"),
        ("        Activity Diagram", "35"),
        ("        Class Diagram", "37"),
        ("        Sequence Diagram", "39"),
        ("        System Architecture Diagram", "41"),
        ("    Requirement Analysis", "43"),
        ("    System Evaluation (ISO 25010)", "45"),
        ("    Data Gathering Instrument", "47"),
        ("    Respondents of the Study", "48"),
        ("    Data Analysis Procedure and Statistical Treatment", "49"),
        ("REFERENCES", "53"),
        ("APPENDICES", "56"),
        ("    Appendix A: ISO 25010 Software Quality Evaluation Instrument", "56"),
        ("    Appendix B: System User Interface Screenshots and Operational Flow Diagrams", "60"),
        ("    Appendix C: Certificate of System Acceptance and Transmittal", "67")
    ]
    
    for item, page in toc_items:
        p = doc.add_paragraph()
        p.paragraph_format.line_spacing = 1.15
        p.paragraph_format.space_after = Pt(2)
        r1 = p.add_run(item)
        r1.font.name = 'Times New Roman'
        r1.font.size = Pt(11)
        if "Chapter" in item or "TITLE" in item or "REFERENCES" in item or "APPENDICES" in item:
            r1.font.bold = True
        leader_dots = " " + "." * max(2, (80 - len(item) - len(page))) + " "
        r2 = p.add_run(leader_dots + page)
        r2.font.name = 'Times New Roman'
        r2.font.size = Pt(11)
        
    doc.add_page_break()
    
    # =========================================================================
    # 6. LIST OF TABLES
    # =========================================================================
    doc.add_paragraph("LIST OF TABLES", style='Front Matter Heading')
    
    tables_list = [
        ("Table 1", "System Comparison of the Related Studies with the Proposed System", "24"),
        ("Table 2", "Hardware Specification for the System", "43"),
        ("Table 3", "Software Specification for the System", "44"),
        ("Table 4", "Five-Point Likert Scale and Verbal Interpretation", "46"),
        ("Table 5", "Summary of ISO 25010 Software Quality Evaluation Results", "50")
    ]
    
    for t_num, t_title, page in tables_list:
        p = doc.add_paragraph()
        p.paragraph_format.line_spacing = 1.15
        p.paragraph_format.space_after = Pt(4)
        p.add_run(f"{t_num}  {t_title}")
        dots = " " + "." * max(2, (75 - len(t_num) - len(t_title) - len(page))) + " "
        p.add_run(dots + page)
        
    doc.add_page_break()
    
    # =========================================================================
    # 7. LIST OF FIGURES
    # =========================================================================
    doc.add_paragraph("LIST OF FIGURES", style='Front Matter Heading')
    
    figures_list = [
        ("Figure 1", "Conceptual Framework of the Study (IPO Model)", "6"),
        ("Figure 2", "Prototyping Model Software Development Life Cycle", "30"),
        ("Figure 3", "Use Case Diagram of the ISAT U Coffee Shop POS and Reservation System", "34"),
        ("Figure 4", "Activity Diagram for POS Product QR/Barcode Scanning and Checkout", "36"),
        ("Figure 5", "Class Diagram / Database Schema of the System", "38"),
        ("Figure 6", "Sequence Diagram for Cashier POS Product Auto-Read Scan Flow", "40"),
        ("Figure 7", "System Architecture Diagram of the POS and Reservation Deployment", "42")
    ]
    
    for f_num, f_title, page in figures_list:
        p = doc.add_paragraph()
        p.paragraph_format.line_spacing = 1.15
        p.paragraph_format.space_after = Pt(4)
        p.add_run(f"{f_num}  {f_title}")
        dots = " " + "." * max(2, (75 - len(f_num) - len(f_title) - len(page))) + " "
        p.add_run(dots + page)
        
    doc.add_page_break()
    
    # =========================================================================
    # 8. LIST OF APPENDICES
    # =========================================================================
    doc.add_paragraph("LIST OF APPENDICES", style='Front Matter Heading')
    
    app_list = [
        ("Appendix A", "ISO 25010 Software Quality Evaluation Instrument", "56"),
        ("Appendix B", "System User Interface Screenshots and Operational Flow Diagrams", "60"),
        ("Appendix C", "Certificate of System Acceptance and Transmittal", "67")
    ]
    
    for a_num, a_title, page in app_list:
        p = doc.add_paragraph()
        p.paragraph_format.line_spacing = 1.15
        p.paragraph_format.space_after = Pt(4)
        p.add_run(f"{a_num}  {a_title}")
        dots = " " + "." * max(2, (75 - len(a_num) - len(a_title) - len(page))) + " "
        p.add_run(dots + page)
        
    doc.add_page_break()
    
    # =========================================================================
    # 9. CHAPTER I: INTRODUCTION
    # =========================================================================
    doc.add_paragraph("Chapter I\nINTRODUCTION", style='Heading 1')
    
    doc.add_paragraph("Background of the Study", style='Heading 2')
    for p_text in c1['background_paras']:
        doc.add_paragraph(p_text, style='Body Text Indented')
        
    doc.add_paragraph("Objectives of the Study", style='Heading 2')
    doc.add_paragraph(c1['general_objective'], style='Body Text Indented')
    p_spec_lead = doc.add_paragraph("Specifically, the study aimed to:", style='Body Text Indented')
    p_spec_lead.paragraph_format.space_after = Pt(4)
    for obj in c1['specific_objectives']:
        doc.add_paragraph(f"• {obj}", style='Objective Item')
        
    doc.add_paragraph("Conceptual Framework of the Study", style='Heading 2')
    doc.add_paragraph(c1['conceptual_framework_paras'][0], style='Body Text Indented')
    
    # Embedded PlantUML Figure 1
    add_figure_image(doc, 'Documentation/diagrams/figure1_ipo.png', 'Figure 1. Conceptual Framework of the Study', width_inches=5.8)
    
    for p_text in c1['conceptual_framework_paras'][1:]:
        doc.add_paragraph(p_text, style='Body Text Indented')
        
    doc.add_paragraph("Definition of Terms", style='Heading 2')
    p_term_lead = doc.add_paragraph("To facilitate a clear understanding of the study, the following technical and operational terms are defined:", style='Body Text Indented')
    p_term_lead.paragraph_format.space_after = Pt(6)
    for term, conceptual, operational in c1['terms']:
        p = doc.add_paragraph(style='Body Text Indented')
        r_term = p.add_run(f"{term}. ")
        r_term.font.bold = True
        r_term.font.italic = True
        p.add_run(f"{conceptual} {operational}")
        
    doc.add_paragraph("Significance of the Study", style='Heading 2')
    doc.add_paragraph("The development of this system is directly beneficial to the following individuals and school offices:", style='Body Text Indented')
    for p_text in c1['significance_paras']:
        doc.add_paragraph(p_text, style='Body Text Indented')
        
    doc.add_paragraph("Scope and Limitations of the Study", style='Heading 2')
    doc.add_paragraph("Scope of the Study", style='Heading 3')
    for p_text in c1['scope_paras']:
        doc.add_paragraph(p_text, style='Body Text Indented')
        
    doc.add_paragraph("Limitations of the Study", style='Heading 3')
    doc.add_paragraph("The study recognizes several operational boundaries and technological constraints:", style='Body Text Indented')
    for p_text in c1['limitations_paras']:
        doc.add_paragraph(p_text, style='Body Text Indented')
        
    doc.add_page_break()
    
    # =========================================================================
    # 10. CHAPTER II: REVIEW OF RELATED LITERATURE AND STUDIES
    # =========================================================================
    doc.add_paragraph("Chapter II\nREVIEW OF RELATED LITERATURE AND STUDIES", style='Heading 1')
    doc.add_paragraph(c2['intro'], style='Body Text Indented')
    
    doc.add_paragraph("Related Literature", style='Heading 2')
    for title, review, link in c2['literature']:
        doc.add_paragraph(title, style='Heading 3')
        doc.add_paragraph(review, style='Body Text Indented')
        doc.add_paragraph(link, style='Body Text Indented')
        
    doc.add_paragraph("Related Studies", style='Heading 2')
    for title, summary, comparison in c2['studies']:
        doc.add_paragraph(title, style='Heading 3')
        doc.add_paragraph(summary, style='Body Text Indented')
        doc.add_paragraph(comparison, style='Body Text Indented')
        
    doc.add_paragraph("Table of Comparison", style='Heading 2')
    doc.add_paragraph("Table 1 presents the comparison of the related studies with the proposed system.", style='Body Text Indented')
    
    doc.add_paragraph("Table 1", style='Table Number')
    doc.add_paragraph("System Comparison of the Related Studies with the Proposed System", style='Table Title')
    
    t1_spec = c2['table_comparison']
    table1 = doc.add_table(rows=len(t1_spec['rows']) + 1, cols=len(t1_spec['headers']))
    align_t1 = {1: WD_ALIGN_PARAGRAPH.CENTER, 2: WD_ALIGN_PARAGRAPH.CENTER, 3: WD_ALIGN_PARAGRAPH.CENTER, 4: WD_ALIGN_PARAGRAPH.CENTER, 5: WD_ALIGN_PARAGRAPH.CENTER}
    style_data_table(table1, t1_spec['widths'], t1_spec['headers'], t1_spec['rows'], align_cols=align_t1)
    
    p_sp = doc.add_paragraph()
    p_sp.paragraph_format.space_before = Pt(6)
    
    doc.add_paragraph("Synthesis", style='Heading 2')
    for p_text in c2['synthesis_paras']:
        doc.add_paragraph(p_text, style='Body Text Indented')
        
    doc.add_page_break()
    
    # =========================================================================
    # 11. CHAPTER III: METHODOLOGY
    # =========================================================================
    doc.add_paragraph("Chapter III\nMETHODOLOGY", style='Heading 1')
    
    doc.add_paragraph("Project Description", style='Heading 2')
    for p_text in c3['project_description']:
        doc.add_paragraph(p_text, style='Body Text Indented')
        
    doc.add_paragraph("Project Development Process", style='Heading 2')
    doc.add_paragraph(c3['development_process']['intro'], style='Body Text Indented')
    
    # Embedded PlantUML Figure 2
    add_figure_image(doc, 'Documentation/diagrams/figure2_prototyping.png', 'Figure 2. Prototyping Model Software Development Life Cycle', width_inches=4.8)
    
    for phase_name, phase_desc in c3['development_process']['phases']:
        doc.add_paragraph(phase_name, style='Heading 3')
        doc.add_paragraph(phase_desc, style='Body Text Indented')
        
    doc.add_paragraph("UML Diagrams", style='Heading 2')
    
    # Use Case
    doc.add_paragraph("Use Case Diagram", style='Heading 3')
    for p_text in c3['uml']['use_case_paras']:
        doc.add_paragraph(p_text, style='Body Text Indented')
    add_figure_image(doc, 'Documentation/diagrams/figure3_use_case.png', 'Figure 3. Use Case Diagram of the System', width_inches=5.8)
    
    # Activity Diagram
    doc.add_paragraph("Activity Diagram", style='Heading 3')
    for p_text in c3['uml']['activity_paras']:
        doc.add_paragraph(p_text, style='Body Text Indented')
    add_figure_image(doc, 'Documentation/diagrams/figure4_activity.png', 'Figure 4. Activity Diagram for POS Barcode/QR Scanning and Checkout', width_inches=4.8)
    
    # Class Diagram
    doc.add_paragraph("Class Diagram", style='Heading 3')
    for p_text in c3['uml']['class_diag_paras']:
        doc.add_paragraph(p_text, style='Body Text Indented')
    add_figure_image(doc, 'Documentation/diagrams/figure5_class_erd.png', 'Figure 5. Class Diagram / Database Schema of the System', width_inches=5.6)
    
    # Sequence Diagram
    doc.add_paragraph("Sequence Diagram", style='Heading 3')
    for p_text in c3['uml']['sequence_paras']:
        doc.add_paragraph(p_text, style='Body Text Indented')
    add_figure_image(doc, 'Documentation/diagrams/figure6_sequence.png', 'Figure 6. Sequence Diagram for Cashier POS Product Auto-Read Scan Flow', width_inches=5.6)
    
    # System Architecture
    doc.add_paragraph("System Architecture Diagram", style='Heading 3')
    for p_text in c3['uml']['architecture_paras']:
        doc.add_paragraph(p_text, style='Body Text Indented')
    add_figure_image(doc, 'Documentation/diagrams/figure7_architecture.png', 'Figure 7. System Architecture Diagram of the System', width_inches=5.5)
    
    # Requirement Analysis
    doc.add_paragraph("Requirement Analysis", style='Heading 2')
    doc.add_paragraph("To ensure smooth operation and accurate barcode scanning, the minimum hardware and software requirements were established.", style='Body Text Indented')
    
    doc.add_paragraph("Table 2", style='Table Number')
    doc.add_paragraph("Hardware Specification for the System", style='Table Title')
    hw_spec = c3['requirements']['hardware']
    table2 = doc.add_table(rows=len(hw_spec['rows']) + 1, cols=len(hw_spec['headers']))
    style_data_table(table2, hw_spec['widths'], hw_spec['headers'], hw_spec['rows'])
    
    p_sp = doc.add_paragraph()
    p_sp.paragraph_format.space_before = Pt(6)
    
    doc.add_paragraph("Table 3", style='Table Number')
    doc.add_paragraph("Software Specification for the System", style='Table Title')
    sw_spec = c3['requirements']['software']
    table3 = doc.add_table(rows=len(sw_spec['rows']) + 1, cols=len(sw_spec['headers']))
    style_data_table(table3, sw_spec['widths'], sw_spec['headers'], sw_spec['rows'])
    
    p_sp = doc.add_paragraph()
    p_sp.paragraph_format.space_before = Pt(6)
    
    # System Evaluation
    doc.add_paragraph("System Evaluation (ISO 25010)", style='Heading 2')
    doc.add_paragraph(
        "The system was evaluated based on the ISO 25010 software quality model across eight characteristics: functional suitability, "
        "performance efficiency, compatibility, usability, reliability, security, maintainability, and portability. "
        "Table 4 presents the five-point Likert scale used in interpreting the evaluation scores.",
        style='Body Text Indented'
    )
    
    doc.add_paragraph("Table 4", style='Table Number')
    doc.add_paragraph("Five-Point Likert Scale and Verbal Interpretation", style='Table Title')
    lk_spec = c3['evaluation']['likert_scale']
    table4 = doc.add_table(rows=len(lk_spec['rows']) + 1, cols=len(lk_spec['headers']))
    align_lk = {0: WD_ALIGN_PARAGRAPH.CENTER, 1: WD_ALIGN_PARAGRAPH.CENTER, 2: WD_ALIGN_PARAGRAPH.CENTER}
    style_data_table(table4, lk_spec['widths'], lk_spec['headers'], lk_spec['rows'], align_cols=align_lk)
    
    p_sp = doc.add_paragraph()
    p_sp.paragraph_format.space_before = Pt(6)
    
    doc.add_paragraph("Data Gathering Instrument", style='Heading 2')
    doc.add_paragraph(
        "The primary tool used to collect evaluation data was a survey questionnaire patterned after the ISO 25010 software quality standard. "
        "The questionnaire consists of twenty-four (24) statements, with three statements for each of the eight characteristics. "
        "Each statement was rated from 1 (Very Poor) to 5 (Excellent). Space was also provided for comments and suggestions.",
        style='Body Text Indented'
    )
    
    doc.add_paragraph("Respondents of the Study", style='Heading 2')
    for p_text in c3['evaluation']['respondents']:
        doc.add_paragraph(p_text, style='Body Text Indented')
        
    doc.add_paragraph("Data Analysis Procedure and Statistical Treatment", style='Heading 2')
    doc.add_paragraph(
        "The ratings from the ten respondents were organized and computed using the basic mean formula: "
        "Mean = Sum of Ratings / Total Number of Respondents. "
        "The calculated mean for each category was interpreted using the scale in Table 4. "
        "Table 5 shows the summary of the evaluation results.",
        style='Body Text Indented'
    )
    
    doc.add_paragraph("Table 5", style='Table Number')
    doc.add_paragraph("Summary of ISO 25010 Software Quality Evaluation Results", style='Table Title')
    res_spec = c3['evaluation']['results']
    table5 = doc.add_table(rows=len(res_spec['rows']) + 1, cols=len(res_spec['headers']))
    align_res = {1: WD_ALIGN_PARAGRAPH.CENTER, 2: WD_ALIGN_PARAGRAPH.CENTER}
    style_data_table(table5, res_spec['widths'], res_spec['headers'], res_spec['rows'], align_cols=align_res)
    
    p_sp = doc.add_paragraph()
    p_sp.paragraph_format.space_before = Pt(6)
    
    for p_text in c3['evaluation']['discussion']:
        doc.add_paragraph(p_text, style='Body Text Indented')
        
    doc.add_page_break()
    
    # =========================================================================
    # 12. REFERENCES
    # =========================================================================
    doc.add_paragraph("REFERENCES", style='Front Matter Heading')
    for ref_entry in refs:
        doc.add_paragraph(ref_entry, style='Reference Entry')
        
    doc.add_page_break()
    
    # =========================================================================
    # 13. APPENDICES
    # =========================================================================
    doc.add_paragraph("APPENDICES", style='Appendix Heading')
    
    # Appendix A
    doc.add_paragraph(app['appendix_a_title'], style='Heading 2')
    doc.add_paragraph(app['appendix_a_intro'], style='Body Text Indented')
    
    for category_name, questions in app['appendix_a_questions']:
        doc.add_paragraph(category_name, style='Heading 3')
        q_table = doc.add_table(rows=len(questions) + 1, cols=6)
        q_headers = ["Evaluation Indicator Statement", "5", "4", "3", "2", "1"]
        q_widths = [3.8, 0.4, 0.4, 0.4, 0.4, 0.4]
        q_rows = []
        for q in questions:
            q_rows.append([q, "", "", "", "", ""])
        align_q = {1: WD_ALIGN_PARAGRAPH.CENTER, 2: WD_ALIGN_PARAGRAPH.CENTER, 3: WD_ALIGN_PARAGRAPH.CENTER, 4: WD_ALIGN_PARAGRAPH.CENTER, 5: WD_ALIGN_PARAGRAPH.CENTER}
        style_data_table(q_table, q_widths, q_headers, q_rows, align_cols=align_q)
        p_sp = doc.add_paragraph()
        p_sp.paragraph_format.space_before = Pt(4)
        
    doc.add_page_break()
    
    # Appendix B
    doc.add_paragraph(app['appendix_b_title'], style='Heading 2')
    doc.add_paragraph("This appendix contains selected interface layouts and flowcharts of the developed ISAT U Coffee Shop platform:", style='Body Text Indented')
    
    for item_title, item_desc in app['appendix_b_items']:
        doc.add_paragraph(item_title, style='Heading 3')
        add_placeholder_box(doc, item_title, item_desc)
        
    doc.add_page_break()
    
    # Appendix C
    doc.add_paragraph(app['appendix_c_title'], style='Heading 2')
    doc.add_paragraph(app['appendix_c_text'], style='Body Text Indented')
    add_placeholder_box(
        doc,
        "Appendix C. Official Transmittal Letter and Certificate of Acceptance",
        "Republic of the Philippines\nILOILO SCIENCE AND TECHNOLOGY UNIVERSITY\nBurgos St., La Paz, Iloilo City\n\n"
        "CERTIFICATE OF SYSTEM DEMONSTRATION AND ACCEPTANCE\n\n"
        "This is to certify that the Capstone Project entitled 'ISAT U Coffee Shop POS System with Online Reservation and QR Code' "
        "developed by John Reynel Caparida, Christian Mark Gepana, and Kenth Michael Palma has been demonstrated, evaluated, "
        "and accepted by the ISAT U Canteen Management and Auxiliary Services.\n\n"
        "Signed:\n\n"
        "DR. GABRIEL M. SALVADOR, Ph.D.                  ENGR. MARK ANTHONY VILLANUEVA, M.Eng.\n"
        "Campus Auxiliary Administrator                 Capstone Project Adviser"
    )
    
    doc.save(output_path)
    print(f"Final simplified manuscript successfully generated and saved to: {output_path}")

if __name__ == '__main__':
    main()
