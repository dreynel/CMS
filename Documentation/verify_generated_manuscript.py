# -*- coding: utf-8 -*-
"""
Verification Script for Generated Capstone Manuscript Chapters 1-3
"""
import docx
from docx import Document
import sys
sys.stdout.reconfigure(encoding='utf-8')

def verify():
    docx_path = 'Documentation/ISAT_U_Coffee_Shop_POS_System_Manuscript_Chapters_1-3.docx'
    doc = Document(docx_path)
    
    print("=== MANUSCRIPT AUDIT & VERIFICATION ===")
    print(f"Total Paragraphs: {len(doc.paragraphs)}")
    print(f"Total Tables: {len(doc.tables)}")
    print(f"Total Sections: {len(doc.sections)}")
    
    # 1. Verify First Paragraph (Must start at Chapter I)
    first_p = doc.paragraphs[0].text
    print(f"\n1. First Paragraph Content: {repr(first_p)}")
    assert "Chapter I" in first_p, f"Error: Manuscript does not start at Chapter I! Starts with {first_p}"
    print("   [PASS] Starts directly at Chapter I (No preliminary pages, no Table of Contents).")
    
    # 2. Check Definition of Terms (Non-bold, must be italic)
    found_terms = []
    term_checks = []
    in_def_terms = False
    for p in doc.paragraphs:
        if "Definition of Terms" in p.text:
            in_def_terms = True
            continue
        if in_def_terms:
            if p.text.startswith("Chapter II"):
                in_def_terms = False
                break
            if p.runs and len(p.runs) >= 2:
                first_run = p.runs[0]
                term_name = first_run.text.strip()
                if term_name.endswith('.'):
                    found_terms.append(term_name)
                    is_italic = first_run.italic
                    is_bold = first_run.bold
                    term_checks.append((term_name, is_italic, is_bold))
    
    print(f"\n2. Definition of Terms checked ({len(found_terms)} terms found):")
    for t_name, t_it, t_bd in term_checks[:5]:
        print(f"   - {t_name} -> Italic: {t_it}, Bold: {t_bd}")
    
    all_italic_nonbold = all(t_it and (t_bd is None or not t_bd) for _, t_it, t_bd in term_checks)
    assert all_italic_nonbold, "Error: Some terms in Definition of Terms are not italicized or are bolded!"
    print("   [PASS] All terms are non-bold and strictly italicized.")

    # 2b. Check Significance of the Study (Non-bold, must be italic stakeholder names)
    sig_stakeholders = []
    sig_checks = []
    in_sig = False
    for p in doc.paragraphs:
        if "Significance of the Study" in p.text:
            in_sig = True
            continue
        if in_sig:
            if "Definition of Terms" in p.text:
                in_sig = False
                break
            if p.runs and len(p.runs) >= 2:
                first_run = p.runs[0]
                st_name = first_run.text.strip()
                if st_name.endswith('.'):
                    sig_stakeholders.append(st_name)
                    is_italic = first_run.italic
                    is_bold = first_run.bold
                    sig_checks.append((st_name, is_italic, is_bold))
    
    print(f"\n2b. Significance of the Study checked ({len(sig_stakeholders)} stakeholders found):")
    for s_name, s_it, s_bd in sig_checks:
        print(f"   - {s_name} -> Italic: {s_it}, Bold: {s_bd}")
    assert len(sig_stakeholders) >= 5, f"Error: Expected at least 5 stakeholders, found {len(sig_stakeholders)}"
    all_sig_italic = all(s_it and (s_bd is None or not s_bd) for _, s_it, s_bd in sig_checks)
    assert all_sig_italic, "Error: Some stakeholders in Significance of the Study are not italicized or are bolded!"
    print("   [PASS] All stakeholder names are non-bold and strictly italicized.")

    # 2c. Check Mini Hotel & Cashier-Barista Context
    all_text = " ".join(p.text for p in doc.paragraphs)
    has_mini_hotel = "Mini Hotel" in all_text
    has_cashier_barista = "cashier" in all_text.lower() and "barista" in all_text.lower()
    print(f"\n2c. Mini Hotel & Cashier-Barista Context Check:")
    print(f"   Contains 'Mini Hotel': {has_mini_hotel}")
    print(f"   Contains 'Cashier-Barista': {has_cashier_barista}")
    assert has_mini_hotel, "Error: 'Mini Hotel' context missing from manuscript!"
    assert has_cashier_barista, "Error: 'Cashier-Barista' dual-role context missing from manuscript!"
    print("   [PASS] Mini Hotel and Cashier-Barista dual-role context confirmed.")

    # 3. Check Table of Comparison (Table 1)
    tbl1 = doc.tables[0]
    print(f"\n3. Table 1 (Comparison Matrix) Rows: {len(tbl1.rows)}, Cols: {len(tbl1.columns)}")
    table_text = ""
    for row in tbl1.rows:
        row_str = " | ".join(cell.text.strip().replace('\n', ' ') for cell in row.cells)
        table_text += row_str + "\n"
    
    has_check = "\u2714" in table_text
    has_cross = "\u2716" in table_text
    has_yes = "Yes" in table_text
    has_no = "No" in table_text
    print(f"   Contains Checkmark: {has_check}, Contains Crossmark: {has_cross}")
    print(f"   Contains 'Yes': {has_yes}, Contains 'No': {has_no}")
    assert has_check and has_cross, "Error: Table 1 does not use check and cross marks!"
    assert not has_yes and not has_no, "Error: Table 1 still contains 'Yes' or 'No'!"
    print("   [PASS] Table 1 exclusively uses check and cross marks (no 'Yes' or 'No').")
    
    # 4. Check Tables Black & White Formatting (No background fills)
    for idx, tbl in enumerate(doc.tables, 1):
        for r in tbl.rows:
            for cell in r.cells:
                tcPr = cell._tc.tcPr
                if tcPr is not None:
                    shd = tcPr.find('{http://schemas.openxmlformats.org/wordprocessingml/2006/main}shd')
                    if shd is not None:
                        fill = shd.get('{http://schemas.openxmlformats.org/wordprocessingml/2006/main}fill')
                        if fill and fill.lower() not in ['auto', 'none', 'ffffff']:
                            raise AssertionError(f"Table {idx} has colored fill: {fill}")
    print("\n4. Table Background Colors:")
    print("   [PASS] All tables have clean black and white backgrounds (no colored shading).")
    
    # 5. Check Figures Count (Figure captions only)
    figures = [p.text for p in doc.paragraphs if p.text.startswith("Figure ") and ("." in p.text[:10]) and ("shows" not in p.text) and ("illustrates" not in p.text) and ("presents" not in p.text)]
    print(f"\n5. Figures embedded in manuscript ({len(figures)} figure captions):")
    for f in figures:
        print(f"   - {f}")
    assert len(figures) == 16, f"Error: Expected 16 figures (1, 2, 3, 4a-4d, 5a-5d, 6, 7a-7d), found {len(figures)}"
    print("   [PASS] All 16 figures are present with captions.")

    # 5b. Verify Per-User-Role Coverage
    expected_roles = ["Customer", "Cashier", "Barista", "Administrator"]
    for role in expected_roles:
        uc_found = any(f"Figure 4" in f and role in f for f in figures)
        act_found = any(f"Figure 5" in f and role in f for f in figures)
        seq_found = any(f"Figure 7" in f and role in f for f in figures)
        print(f"   - Role '{role}': UseCase={uc_found}, Activity={act_found}, Sequence={seq_found}")
        assert uc_found, f"Error: Missing Use Case diagram for role {role}"
        assert act_found, f"Error: Missing Activity diagram for role {role}"
        assert seq_found, f"Error: Missing Sequence diagram for role {role}"
    print("   [PASS] All 4 user roles have dedicated Use Case, Activity, and Sequence diagrams.")

    # 5c. Check for disallowed version strings (e.g., mysql8,0)
    all_text_lower = all_text.lower()
    has_mysql8 = "mysql8" in all_text_lower or "mysql 8.0" in all_text_lower
    print(f"\n5c. Simplified wordings check (No 'mysql8,0' or 'mysql 8.0'):")
    print(f"   Contains 'mysql8': {has_mysql8}")
    assert not has_mysql8, "Error: Manuscript still contains version string 'mysql8'!"
    print("   [PASS] No complex version strings present. Wordings are simplified.")

    # 5d. Verify Exact 7 Specific Objectives
    in_obj = False
    obj_paras = []
    for p in doc.paragraphs:
        if "Objectives of the Study" in p.text or "Specific Objectives" in p.text:
            in_obj = True
            continue
        if in_obj:
            if "Significance of the Study" in p.text:
                break
            if p.text.strip().startswith(("1.", "2.", "3.", "4.", "5.", "6.", "7.")):
                obj_paras.append(p.text.strip())
    print(f"\n5d. Specific Objectives Count: {len(obj_paras)}")
    for o in obj_paras:
        print(f"   {o}")
    assert len(obj_paras) == 7, f"Error: Expected 7 specific objectives, found {len(obj_paras)}"
    print("   [PASS] All 7 exact specific objectives are intact.")

    # 6. Check References Section (Must be removed)
    has_refs = any(p.text.strip() == "REFERENCES" or p.text.strip() == "References" for p in doc.paragraphs)
    print(f"\n6. References Section check:")
    print(f"   References section present: {has_refs}")
    assert not has_refs, "Error: References section found in manuscript! Must be excluded."
    print("   [PASS] References section excluded as requested.")
    
    print("\n>>> ALL CHECKS PASSED 100%! THE MANUSCRIPT STRICTLY COMPLIES WITH ALL GUIDELINES. <<<")

if __name__ == "__main__":
    verify()
