# -*- coding: utf-8 -*-
"""
Build Complete Capstone Manuscript Chapters 1 to 3 for:
ISAT U Coffee Shop POS System with Online Reservation and QR Code

Strict Compliance with User Instructions & Module Guidelines:
1. Starts directly at Chapter I (No preliminary pages, no Table of Contents, no Title Page, no Approval Sheet).
2. Right-aligned Arabic page numbering in header starting at page 1.
3. Margins: 1.5 in Left, 1.0 in Top, Bottom, Right.
4. Font: Times New Roman 12 pt.
5. Conceptual Framework: Inline IPO boxes with system name alone in output, ISO 25010 evaluation below.
6. Definition of Terms: Non-bold, italic terms.
7. RRL Comparison Table: Check (✔) and Cross (✖) marks instead of Yes/No.
8. System Architecture: Real vector/device graphics (Smartphone, Laptop, POS, Scanner, Thermal Printer, KDS, Server, Database).
9. Tables: Black and white only (APA 7th style: top, header-bottom, and bottom borders only, no colored fills).
10. References: Excluded for now as requested.
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

def set_cell_margins(cell, top=100, bottom=100, left=140, right=140):
    tcPr = cell._tc.get_or_add_tcPr()
    tcMar = OxmlElement('w:tcMar')
    for m, val in [('top', top), ('bottom', bottom), ('left', left), ('right', right)]:
        node = OxmlElement(f'w:{m}')
        node.set(qn('w:w'), str(val))
        node.set(qn('w:type'), 'dxa')
        tcMar.append(node)
    tcPr.append(tcMar)

def set_apa_table_borders(table):
    """Clean Black and White APA 7th Edition borders: top, bottom, and header bottom only."""
    tblPr = table._tbl.tblPr
    borders = parse_xml(
        f'<w:tblBorders {nsdecls("w")}>\n'
        f'  <w:top w:val="single" w:sz="6" w:space="0" w:color="000000"/>\n'
        f'  <w:left w:val="none"/>\n'
        f'  <w:bottom w:val="single" w:sz="6" w:space="0" w:color="000000"/>\n'
        f'  <w:right w:val="none"/>\n'
        f'  <w:insideH w:val="none"/>\n'
        f'  <w:insideV w:val="none"/>\n'
        f'</w:tblBorders>'
    )
    tblPr.append(borders)

def set_header_bottom_border(row):
    """Add black bottom border to header row cells."""
    for cell in row.cells:
        tcPr = cell._tc.get_or_add_tcPr()
        tcBorders = parse_xml(
            f'<w:tcBorders {nsdecls("w")}>\n'
            f'  <w:bottom w:val="single" w:sz="6" w:space="0" w:color="000000"/>\n'
            f'</w:tcBorders>'
        )
        tcPr.append(tcBorders)

def build_manuscript():
    template_path = 'Documentation/Capstone_Manuscript_Template_Ch1-3.docx'
    json_path = 'Documentation/manuscript_data.json'
    output_docx = 'Documentation/ISAT_U_Coffee_Shop_POS_System_Manuscript_Ch1-3.docx'
    output_md = 'Documentation/ISAT_U_Coffee_Shop_POS_System_Manuscript_Ch1-3.md'

    with open(json_path, encoding='utf-8') as f:
        data = json.load(f)

    c1 = data['chapter_1']
    c2 = data['chapter_2']
    c3 = data['chapter_3']

    doc = Document(template_path)
    body = doc._body._element

    # 1. Remove all existing paragraphs and tables from template (removes Title Page, Approval Sheet, Abstract, TOC, etc.)
    for elem in list(body):
        if elem.tag.endswith('p') or elem.tag.endswith('tbl'):
            body.remove(elem)

    # 2. Setup Page Margins: 1.5 in Left, 1.0 in Top, Bottom, Right
    s = doc.sections[0]
    s.top_margin = Inches(1.0)
    s.bottom_margin = Inches(1.0)
    s.left_margin = Inches(1.5)
    s.right_margin = Inches(1.0)

    # 3. Setup Header: Arabic page number right aligned (1, 2, 3...)
    hdr = s.header
    for p in hdr.paragraphs:
        p.text = ''
    p_hdr = hdr.paragraphs[0]
    p_hdr.alignment = WD_ALIGN_PARAGRAPH.RIGHT

    field_xml = (
        '<w:r xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">'
        '  <w:rPr>'
        '    <w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman"/>'
        '    <w:sz w:val="24"/>'
        '  </w:rPr>'
        '  <w:fldChar w:fldCharType="begin"/>'
        '  <w:instrText xml:space="preserve">PAGE</w:instrText>'
        '  <w:fldChar w:fldCharType="separate"/>'
        '  <w:fldChar w:fldCharType="end"/>'
        '</w:r>'
    )
    p_hdr._p.append(parse_xml(field_xml))

    # Clear footer (no footer page numbers)
    ftr = s.footer
    for p in ftr.paragraphs:
        p.text = ''

    # =========================================================================
    # HELPER FORMATTING FUNCTIONS
    # =========================================================================
    def add_h1(text):
        p = doc.add_paragraph(style='Heading 1')
        p.alignment = WD_ALIGN_PARAGRAPH.CENTER
        p.paragraph_format.space_before = Pt(12)
        p.paragraph_format.space_after = Pt(12)
        lines = text.split('\n')
        for i, line in enumerate(lines):
            r = p.add_run(line)
            r.font.name = 'Times New Roman'
            r.font.size = Pt(12)
            r.bold = False
            if i < len(lines) - 1:
                p.add_run('\n')
        return p

    def add_h2(text):
        p = doc.add_paragraph(style='Heading 2')
        p.alignment = WD_ALIGN_PARAGRAPH.LEFT
        p.paragraph_format.space_before = Pt(12)
        p.paragraph_format.space_after = Pt(6)
        r = p.add_run(text)
        r.font.name = 'Times New Roman'
        r.font.size = Pt(12)
        r.italic = True
        r.bold = False
        return p

    def add_h3(text):
        p = doc.add_paragraph(style='Heading 3')
        p.alignment = WD_ALIGN_PARAGRAPH.LEFT
        p.paragraph_format.space_before = Pt(8)
        p.paragraph_format.space_after = Pt(4)
        r = p.add_run(text)
        r.font.name = 'Times New Roman'
        r.font.size = Pt(12)
        r.italic = True
        r.bold = False
        return p

    def add_body(text, indent=True):
        p = doc.add_paragraph(style='Body Text Indented')
        p.alignment = WD_ALIGN_PARAGRAPH.JUSTIFY
        p.paragraph_format.line_spacing = 1.5
        p.paragraph_format.space_after = Pt(6)
        r = p.add_run(text)
        r.font.name = 'Times New Roman'
        r.font.size = Pt(12)
        if not indent:
            p.paragraph_format.first_line_indent = Inches(0)
        return p

    def add_obj_item(text):
        p = doc.add_paragraph(style='Objective Item')
        p.alignment = WD_ALIGN_PARAGRAPH.JUSTIFY
        p.paragraph_format.line_spacing = 1.5
        p.paragraph_format.left_indent = Inches(0.5)
        p.paragraph_format.first_line_indent = Inches(0)
        p.paragraph_format.space_after = Pt(4)
        r = p.add_run(text)
        r.font.name = 'Times New Roman'
        r.font.size = Pt(12)
        return p

    def add_figure(fig_num, title, image_file, width_in=5.6):
        p_img = doc.add_paragraph()
        p_img.alignment = WD_ALIGN_PARAGRAPH.CENTER
        p_img.paragraph_format.space_before = Pt(12)
        p_img.paragraph_format.space_after = Pt(4)
        r_img = p_img.add_run()
        if os.path.exists(image_file):
            r_img.add_picture(image_file, width=Inches(width_in))
        else:
            r_img.add_text(f"[Figure image not found: {image_file}]")

        p_cap = doc.add_paragraph(style='Figure Caption')
        p_cap.alignment = WD_ALIGN_PARAGRAPH.LEFT
        p_cap.paragraph_format.space_before = Pt(4)
        p_cap.paragraph_format.space_after = Pt(12)
        r_label = p_cap.add_run(f"Figure {fig_num}. ")
        r_label.font.name = 'Times New Roman'
        r_label.font.size = Pt(12)
        r_label.bold = False
        r_title = p_cap.add_run(title)
        r_title.italic = True
        r_title.bold = False
        r_title.font.name = 'Times New Roman'
        r_title.font.size = Pt(12)
        return p_cap

    def add_table_header(table_num, table_title):
        p_num = doc.add_paragraph(style='Table Number')
        p_num.alignment = WD_ALIGN_PARAGRAPH.LEFT
        p_num.paragraph_format.space_before = Pt(12)
        p_num.paragraph_format.space_after = Pt(2)
        r_num = p_num.add_run(table_num)
        r_num.font.name = 'Times New Roman'
        r_num.font.size = Pt(12)
        r_num.bold = False

        p_ttl = doc.add_paragraph(style='Table Title')
        p_ttl.alignment = WD_ALIGN_PARAGRAPH.LEFT
        p_ttl.paragraph_format.space_before = Pt(0)
        p_ttl.paragraph_format.space_after = Pt(6)
        r_ttl = p_ttl.add_run(table_title)
        r_ttl.italic = True
        r_ttl.bold = False
        r_ttl.font.name = 'Times New Roman'
        r_ttl.font.size = Pt(12)

    def add_definition(term, def_text):
        """Definition of terms: Non-bold, italicized term, followed by plain definition."""
        p = doc.add_paragraph(style='Body Text Indented')
        p.alignment = WD_ALIGN_PARAGRAPH.JUSTIFY
        p.paragraph_format.line_spacing = 1.5
        p.paragraph_format.space_after = Pt(6)

        # Term is strictly ITALIC and NON-BOLD
        r_term = p.add_run(f"{term}. ")
        r_term.italic = True
        r_term.bold = False
        r_term.font.name = 'Times New Roman'
        r_term.font.size = Pt(12)

        r_def = p.add_run(def_text)
        r_def.italic = False
        r_def.bold = False
        r_def.font.name = 'Times New Roman'
        r_def.font.size = Pt(12)
        return p

    def add_significance_item(stakeholder, desc):
        """Significance of the study: Non-bold, italicized stakeholder title, followed by regular description."""
        p = doc.add_paragraph(style='Body Text Indented')
        p.alignment = WD_ALIGN_PARAGRAPH.JUSTIFY
        p.paragraph_format.line_spacing = 1.5
        p.paragraph_format.space_after = Pt(6)

        r_st = p.add_run(f"{stakeholder}. ")
        r_st.italic = True
        r_st.bold = False
        r_st.font.name = 'Times New Roman'
        r_st.font.size = Pt(12)

        r_desc = p.add_run(desc)
        r_desc.italic = False
        r_desc.bold = False
        r_desc.font.name = 'Times New Roman'
        r_desc.font.size = Pt(12)
        return p

    def add_bw_table(col_widths, headers, data, align_cols=None):
        """Creates a black-and-white only table (APA 7th edition style). No color fills."""
        tbl = doc.add_table(rows=len(data) + 1, cols=len(headers))
        tbl.alignment = WD_TABLE_ALIGNMENT.CENTER
        set_apa_table_borders(tbl)

        # Header Row
        hdr_row = tbl.rows[0]
        set_header_bottom_border(hdr_row)
        for idx, text in enumerate(headers):
            cell = hdr_row.cells[idx]
            cell.text = text
            set_cell_margins(cell, top=100, bottom=100, left=120, right=120)
            p = cell.paragraphs[0]
            p.alignment = WD_ALIGN_PARAGRAPH.CENTER
            p.paragraph_format.line_spacing = 1.15
            for r in p.runs:
                r.font.name = 'Times New Roman'
                r.font.size = Pt(10)
                r.bold = False
                r.font.color.rgb = RGBColor(0x00, 0x00, 0x00)

        # Data Rows
        for r_idx, row_data in enumerate(data):
            row = tbl.rows[r_idx + 1]
            for c_idx, val in enumerate(row_data):
                cell = row.cells[c_idx]
                cell.text = str(val)
                set_cell_margins(cell, top=90, bottom=90, left=120, right=120)
                p = cell.paragraphs[0]
                align = WD_ALIGN_PARAGRAPH.LEFT
                if align_cols and c_idx in align_cols:
                    align = align_cols[c_idx]
                p.alignment = align
                p.paragraph_format.line_spacing = 1.15
                for r in p.runs:
                    r.font.name = 'Times New Roman'
                    r.font.size = Pt(9.5)
                    r.bold = False
                    r.font.color.rgb = RGBColor(0x00, 0x00, 0x00)

        for row in tbl.rows:
            for idx, w in enumerate(col_widths):
                row.cells[idx].width = Inches(w)

        # Space after table
        p_sp = doc.add_paragraph()
        p_sp.paragraph_format.space_before = Pt(4)
        p_sp.paragraph_format.space_after = Pt(8)

    print("Building Document: Starting directly at Chapter I...")

    # =========================================================================
    # CHAPTER I: INTRODUCTION
    # =========================================================================
    add_h1("Chapter I\nINTRODUCTION")

    add_h2("Background of the Study")
    for para in c1['background_paras']:
        add_body(para)

    add_h2("Objectives of the Study")
    add_body(c1['general_objective'])
    add_body("Specifically, the study aimed to:", indent=False)
    for idx, obj in enumerate(c1['specific_objectives'], 1):
        add_obj_item(f"{idx}. {obj}")

    add_h2("Conceptual Framework")
    for para in c1['conceptual_framework_paras'][:2]:
        add_body(para)

    add_figure(1, "Conceptual Framework of the Study", "Documentation/figures/figure1_ipo.png", width_in=5.8)

    for para in c1['conceptual_framework_paras'][2:]:
        add_body(para)

    add_h2("Scope and Limitations")
    add_h3("Scope")
    for para in c1['scope_paras']:
        add_body(para)

    add_h3("Limitations")
    for para in c1['limitations_paras']:
        add_body(para)

    add_h2("Significance of the Study")
    add_body("The development and implementation of this system will benefit various stakeholders of the university:", indent=False)
    if 'significance_items' in c1:
        for st_name, desc in c1['significance_items']:
            add_significance_item(st_name, desc)
    else:
        for para in c1['significance_paras']:
            add_body(para)

    add_h2("Definition of Terms")
    add_body(
        "For a better understanding of the terms used in this study, the following technical and operational terms are conceptually and operationally defined:",
        indent=False
    )
    for term, conc_def, oper_def in c1['terms']:
        combined_def = f"{conc_def} {oper_def}"
        add_definition(term, combined_def)

    # Page break before Chapter II
    doc.add_page_break()

    # =========================================================================
    # CHAPTER II: REVIEW OF RELATED LITERATURE AND STUDIES
    # =========================================================================
    add_h1("Chapter II\nREVIEW OF RELATED LITERATURE AND STUDIES")
    add_body(c2['intro'])

    add_h2("Related Literature")
    for lit_title, lit_text, lit_conn in c2['literature']:
        add_h3(lit_title)
        add_body(lit_text)
        add_body(lit_conn)

    add_h2("Related Studies")
    for st_title, st_text, st_conn in c2['studies']:
        add_h3(st_title)
        add_body(st_text)
        add_body(st_conn)

    add_h2("Table of Comparison")
    add_body(
        "Table 1 shows the comparison between previous related studies and the proposed ISAT U - DC Coffee Shop POS System with Online Reservation and QR Code in terms of key system capabilities. Check marks (✔) indicate the presence of a feature, while cross marks (✖) indicate its absence.",
        indent=False
    )

    add_table_header("Table 1", "Comparison of Related Studies with the Proposed System")

    # Clean RRL comparison table with ✔ and ✖
    rrl_headers = c2['table_comparison']['headers']
    rrl_widths = c2['table_comparison']['widths']

    # Convert Yes/No to ✔ / ✖
    rrl_data = []
    for row in c2['table_comparison']['rows']:
        new_row = [row[0]]
        for val in row[1:]:
            if val == "Yes":
                new_row.append("✔")
            elif val == "No":
                new_row.append("✖")
            else:
                new_row.append(val)
        rrl_data.append(new_row)

    align_center_cols = {i: WD_ALIGN_PARAGRAPH.CENTER for i in range(1, len(rrl_headers))}
    add_bw_table(rrl_widths, rrl_headers, rrl_data, align_cols=align_center_cols)

    add_h2("Synthesis of the Study")
    for para in c2['synthesis_paras']:
        add_body(para)

    # Page break before Chapter III
    doc.add_page_break()

    # =========================================================================
    # CHAPTER III: METHODOLOGY
    # =========================================================================
    add_h1("Chapter III\nMETHODOLOGY")

    add_h2("Project Description")
    for para in c3['project_description']:
        add_body(para)

    add_h2("Project Development Process")
    add_body(c3['development_process']['intro'])
    add_figure(2, "Prototyping Software Development Life Cycle (SDLC) Model",
               "Documentation/figures/figure2_prototyping.png", width_in=6.0)

    for phase_name, phase_desc in c3['development_process']['phases']:
        add_h3(f"Phase {phase_name}")
        add_body(phase_desc)

    add_h2("System Architecture and UML Diagrams")
    add_body(
        "To describe the system structure, physical deployment, and process flows, the researchers utilized a realistic vector system architecture diagram and Unified Modeling Language (UML) models.",
        indent=False
    )

    # 1. Architecture Diagram
    add_h3("System Architecture")
    for para in c3['uml']['architecture_paras']:
        add_body(para)
    add_figure(3, "System Architecture Diagram of the ISAT U - DC Coffee Shop POS System",
               "Documentation/figures/figure3_system_architecture.png", width_in=5.8)

    # 2. Use Case Diagrams (Per User Role)
    add_h3("Use Case Diagrams")
    add_body(c3['uml']['use_case_overview'])
    for uc in c3['uml']['use_case_roles']:
        add_body(uc['desc'])
        add_figure(uc['fig_num'], uc['title'], uc['img'], width_in=5.6)

    # 3. Activity Diagrams (Per User Role)
    add_h3("Activity Diagrams")
    add_body(c3['uml']['activity_overview'])
    for act in c3['uml']['activity_roles']:
        add_body(act['desc'])
        add_figure(act['fig_num'], act['title'], act['img'], width_in=5.6)

    # 4. Class Diagram / ERD
    add_h3("Class Diagram and Database Schema")
    for para in c3['uml']['class_diag_paras']:
        add_body(para)
    add_figure(6, "Class Diagram and Entity-Relationship Schema of the System",
               "Documentation/figures/figure6_class_erd.png", width_in=5.8)

    # 5. Sequence Diagrams (Per User Role)
    add_h3("Sequence Diagrams")
    add_body(c3['uml']['sequence_overview'])
    for seq in c3['uml']['sequence_roles']:
        add_body(seq['desc'])
        add_figure(seq['fig_num'], seq['title'], seq['img'], width_in=5.6)

    add_h2("Requirement Analysis")
    add_body(
        "The system was developed and tested to run reliably under standard institutional computing environments. The hardware and software requirements needed to deploy and operate the system are detailed below:",
        indent=False
    )

    add_h3("Hardware Requirements")
    add_table_header("Table 2", "Minimum Hardware Requirements")
    hw_headers = ["Hardware Component", "Minimum Required Specification"]
    hw_widths = [2.2, 3.8]
    add_bw_table(hw_widths, hw_headers, c3['requirements']['hardware']['rows'])

    add_h3("Software Requirements")
    add_table_header("Table 3", "Required Software Specifications")
    sw_headers = ["Software Component", "Required Specification / Tool"]
    sw_widths = [2.2, 3.8]
    add_bw_table(sw_widths, sw_headers, c3['requirements']['software']['rows'])

    add_h2("System Evaluation (ISO/IEC 25010)")
    add_body(
        "To evaluate the technical quality, usability, and operational suitability of the ISAT U - DC Coffee Shop POS System with Online Reservation and QR Code, the researchers utilized the ISO/IEC 25010 software product quality standard. The evaluation examined eight quality characteristics: functional suitability, performance efficiency, compatibility, usability, reliability, security, maintainability, and portability.",
        indent=True
    )
    add_body(
        "A 5-point Likert Scale was utilized to score each criterion in the evaluation survey. Table 4 presents the scoring scale, rating ranges, and their qualitative interpretations.",
        indent=False
    )

    add_table_header("Table 4", "5-Point Likert Scale and Interpretation Range")
    likert_headers = ["Rating", "Range", "Interpretation"]
    likert_widths = [1.2, 2.2, 2.6]
    likert_align = {0: WD_ALIGN_PARAGRAPH.CENTER, 1: WD_ALIGN_PARAGRAPH.CENTER, 2: WD_ALIGN_PARAGRAPH.CENTER}
    add_bw_table(likert_widths, likert_headers, c3['evaluation']['likert_scale']['rows'], align_cols=likert_align)

    add_h2("Data Gathering and Statistical Treatment")
    add_body(
        "Evaluation data was gathered through direct demonstration and hands-on testing of the system by selected evaluators. Evaluators were purposively selected to represent both technical experts and domain end-users:",
        indent=False
    )
    for resp_desc in c3['evaluation']['respondents']:
        add_body(resp_desc)

    add_body(
        "The statistical treatment utilized for analyzing the evaluation responses was the weighted mean. The mean score for each quality characteristic was computed using the formula:",
        indent=False
    )
    p_formula = doc.add_paragraph()
    p_formula.alignment = WD_ALIGN_PARAGRAPH.CENTER
    p_formula.paragraph_format.space_before = Pt(6)
    p_formula.paragraph_format.space_after = Pt(6)
    r_form = p_formula.add_run("Mean = (Sum of All Item Ratings) / (Total Number of Respondents)")
    r_form.font.name = 'Times New Roman'
    r_form.font.size = Pt(11)
    r_form.italic = True

    add_body(
        "Table 5 summarizes the computed mean ratings and corresponding verbal interpretations for the eight ISO/IEC 25010 software quality characteristics.",
        indent=False
    )

    add_table_header("Table 5", "Summary of ISO 25010 Software Quality Evaluation Results")
    eval_headers = ["ISO/IEC 25010 Quality Characteristic", "Computed Mean", "Interpretation"]
    eval_widths = [3.2, 1.4, 1.4]
    eval_align = {1: WD_ALIGN_PARAGRAPH.CENTER, 2: WD_ALIGN_PARAGRAPH.CENTER}
    add_bw_table(eval_widths, eval_headers, c3['evaluation']['results']['rows'], align_cols=eval_align)

    for disc_para in c3['evaluation']['discussion']:
        add_body(disc_para)

    # Save DOCX with fallback in case one file is currently locked/open in Word
    output_files = [
        'Documentation/ISAT_U_Coffee_Shop_POS_System_Manuscript_Chapters_1-3.docx',
        'Documentation/ISAT_U_Coffee_Shop_POS_Chapters_1-3_Updated.docx',
        output_docx
    ]
    for out_path in output_files:
        try:
            doc.save(out_path)
            print(f"Document saved successfully: {out_path}")
        except Exception as e:
            print(f"Notice: Could not write {out_path} ({e})")

    # Generate companion Markdown file
    generate_markdown(c1, c2, c3, rrl_headers, rrl_data, output_md)
    print(f"Markdown file saved successfully: {output_md}")


def generate_markdown(c1, c2, c3, rrl_headers, rrl_data, output_md):
    lines = []
    lines.append("# ISAT U - DC COFFEE SHOP POS SYSTEM WITH ONLINE RESERVATION AND QR CODE")
    lines.append("## Capstone Manuscript (Chapters 1 to 3)\n")

    # Chapter 1
    lines.append("## Chapter I: INTRODUCTION\n")
    lines.append("### Background of the Study\n")
    for p in c1['background_paras']:
        lines.append(f"{p}\n")

    lines.append("### Objectives of the Study\n")
    lines.append(f"{c1['general_objective']}\n")
    lines.append("Specifically, the study aimed to:\n")
    for idx, obj in enumerate(c1['specific_objectives'], 1):
        lines.append(f"{idx}. {obj}")
    lines.append("")

    lines.append("### Conceptual Framework\n")
    for p in c1['conceptual_framework_paras'][:2]:
        lines.append(f"{p}\n")
    lines.append("![Figure 1. Conceptual Framework of the Study](figures/figure1_ipo.png)\n*Figure 1. Conceptual Framework of the Study*\n")
    for p in c1['conceptual_framework_paras'][2:]:
        lines.append(f"{p}\n")

    lines.append("### Scope and Limitations\n")
    lines.append("*Scope*\n")
    for p in c1['scope_paras']:
        lines.append(f"{p}\n")
    lines.append("*Limitations*\n")
    for p in c1['limitations_paras']:
        lines.append(f"{p}\n")

    lines.append("### Significance of the Study\n")
    lines.append("The development and implementation of this system will benefit various stakeholders of the university:\n")
    if 'significance_items' in c1:
        for st_name, desc in c1['significance_items']:
            lines.append(f"*{st_name}.* {desc}\n")
    else:
        for p in c1['significance_paras']:
            lines.append(f"- {p}\n")

    lines.append("### Definition of Terms\n")
    for term, conc_def, oper_def in c1['terms']:
        lines.append(f"*{term}*. {conc_def} {oper_def}\n")

    # Chapter 2
    lines.append("\n## Chapter II: REVIEW OF RELATED LITERATURE AND STUDIES\n")
    lines.append(f"{c2['intro']}\n")

    lines.append("### Related Literature\n")
    for lit_title, lit_text, lit_conn in c2['literature']:
        lines.append(f"*{lit_title}*\n")
        lines.append(f"{lit_text}\n")
        lines.append(f"{lit_conn}\n")

    lines.append("### Related Studies\n")
    for st_title, st_text, st_conn in c2['studies']:
        lines.append(f"*{st_title}*\n")
        lines.append(f"{st_text}\n")
        lines.append(f"{st_conn}\n")

    lines.append("### Table of Comparison\n")
    lines.append(f"Table 1. *Comparison of Related Studies with the Proposed System*\n")
    # Markdown Table
    lines.append("| " + " | ".join(h.replace('\n', ' ') for h in rrl_headers) + " |")
    lines.append("| " + " | ".join(["---"] * len(rrl_headers)) + " |")
    for row in rrl_data:
        lines.append("| " + " | ".join(row) + " |")
    lines.append("")

    lines.append("### Synthesis of the Study\n")
    for p in c2['synthesis_paras']:
        lines.append(f"{p}\n")

    # Chapter 3
    lines.append("\n## Chapter III: METHODOLOGY\n")
    lines.append("### Project Description\n")
    for p in c3['project_description']:
        lines.append(f"{p}\n")

    lines.append("### Project Development Process\n")
    lines.append(f"{c3['development_process']['intro']}\n")
    lines.append("![Figure 2. Prototyping SDLC Model](figures/figure2_prototyping.png)\n*Figure 2. Prototyping Software Development Life Cycle (SDLC) Model*\n")
    for phase_name, phase_desc in c3['development_process']['phases']:
        lines.append(f"*Phase {phase_name}*\n")
        lines.append(f"{phase_desc}\n")

    lines.append("### System Architecture and UML Diagrams\n")
    lines.append("*System Architecture*\n")
    for p in c3['uml']['architecture_paras']:
        lines.append(f"{p}\n")
    lines.append("![Figure 3. System Architecture Diagram](figures/figure3_system_architecture.png)\n*Figure 3. System Architecture Diagram of the ISAT U - DC Coffee Shop POS System*\n")

    lines.append("*Use Case Diagrams*\n")
    lines.append(f"{c3['uml']['use_case_overview']}\n")
    for uc in c3['uml']['use_case_roles']:
        lines.append(f"{uc['desc']}\n")
        img_rel = uc['img'].replace("Documentation/", "")
        lines.append(f"![Figure {uc['fig_num']}. {uc['title']}]({img_rel})\n*Figure {uc['fig_num']}. {uc['title']}*\n")

    lines.append("*Activity Diagrams*\n")
    lines.append(f"{c3['uml']['activity_overview']}\n")
    for act in c3['uml']['activity_roles']:
        lines.append(f"{act['desc']}\n")
        img_rel = act['img'].replace("Documentation/", "")
        lines.append(f"![Figure {act['fig_num']}. {act['title']}]({img_rel})\n*Figure {act['fig_num']}. {act['title']}*\n")

    lines.append("*Class Diagram and Database Schema*\n")
    for p in c3['uml']['class_diag_paras']:
        lines.append(f"{p}\n")
    lines.append("![Figure 6. Class Diagram](figures/figure6_class_erd.png)\n*Figure 6. Class Diagram and Entity-Relationship Schema of the System*\n")

    lines.append("*Sequence Diagrams*\n")
    lines.append(f"{c3['uml']['sequence_overview']}\n")
    for seq in c3['uml']['sequence_roles']:
        lines.append(f"{seq['desc']}\n")
        img_rel = seq['img'].replace("Documentation/", "")
        lines.append(f"![Figure {seq['fig_num']}. {seq['title']}]({img_rel})\n*Figure {seq['fig_num']}. {seq['title']}*\n")

    lines.append("### Requirement Analysis\n")
    lines.append("*Hardware Requirements*\n")
    lines.append("Table 2. *Minimum Hardware Requirements*\n")
    lines.append("| Hardware Component | Minimum Required Specification |")
    lines.append("| --- | --- |")
    for r in c3['requirements']['hardware']['rows']:
        lines.append(f"| {r[0]} | {r[1]} |")
    lines.append("")

    lines.append("*Software Requirements*\n")
    lines.append("Table 3. *Required Software Specifications*\n")
    lines.append("| Software Component | Required Specification / Tool |")
    lines.append("| --- | --- |")
    for r in c3['requirements']['software']['rows']:
        lines.append(f"| {r[0]} | {r[1]} |")
    lines.append("")

    lines.append("### System Evaluation (ISO/IEC 25010)\n")
    lines.append("Table 4. *5-Point Likert Scale and Interpretation Range*\n")
    lines.append("| Rating | Range | Interpretation |")
    lines.append("| --- | --- | --- |")
    for r in c3['evaluation']['likert_scale']['rows']:
        lines.append(f"| {r[0]} | {r[1]} | {r[2]} |")
    lines.append("")

    lines.append("### Data Gathering and Statistical Treatment\n")
    for p in c3['evaluation']['respondents']:
        lines.append(f"{p}\n")

    lines.append("$$\\text{Mean} = \\frac{\\sum \\text{Ratings}}{\\text{Number of Respondents}}$$\n")

    lines.append("Table 5. *Summary of ISO 25010 Software Quality Evaluation Results*\n")
    lines.append("| ISO/IEC 25010 Quality Characteristic | Computed Mean | Interpretation |")
    lines.append("| --- | --- | --- |")
    for r in c3['evaluation']['results']['rows']:
        lines.append(f"| {r[0]} | {r[1]} | {r[2]} |")
    lines.append("")

    for p in c3['evaluation']['discussion']:
        lines.append(f"{p}\n")

    with open(output_md, 'w', encoding='utf-8') as f:
        f.write("\n".join(lines))

if __name__ == "__main__":
    build_manuscript()
