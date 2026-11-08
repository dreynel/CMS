import json
import os

def render_md_table(headers, rows):
    tbl = []
    tbl.append("| " + " | ".join(headers) + " |")
    tbl.append("| " + " | ".join(["---"] * len(headers)) + " |")
    for r in rows:
        tbl.append("| " + " | ".join([str(c) for c in r]) + " |")
    return "\n".join(tbl)

def export_markdown():
    json_path = 'Documentation/manuscript_data.json'
    md_path = 'Documentation/ISAT_U_Coffee_Shop_POS_System_Manuscript_Ch1-3.md'
    
    with open(json_path, encoding='utf-8') as f:
        data = json.load(f)
        
    fm = data['front_matter']
    c1 = data['chapter_1']
    c2 = data['chapter_2']
    c3 = data['chapter_3']
    refs = data['references']
    app = data['appendices']
    
    lines = []
    
    # Title & Front Matter
    lines.append(f"# {fm['title']}")
    lines.append("")
    lines.append(f"**{fm['project_type']}**")
    lines.append("")
    for line in fm['presentation_lines']:
        lines.append(line)
    lines.append("")
    for line in fm['fulfillment_lines']:
        lines.append(line)
    lines.append("")
    lines.append("**By:**")
    for author in fm['authors']:
        lines.append(f"- **{author}**")
    lines.append("")
    lines.append(f"**Date:** {fm['date_str']}")
    lines.append("")
    lines.append("---")
    lines.append("")
    
    # Approval Sheet
    lines.append("## APPROVAL SHEET")
    lines.append("")
    lines.append(
        f'This capstone project is entitled **"{fm["title"]}"**, prepared and submitted by '
        f'**{", ".join(fm["authors"])}**, in partial fulfillment of the requirements for the degree '
        f'Bachelor of Science in Information Technology, and is hereby recommended for approval.'
    )
    lines.append("")
    lines.append(f"**Adviser:** {fm['adviser']} (Capstone Project Adviser)")
    lines.append("")
    lines.append(
        f'It passed the final defense on {fm["date_str"]}, and was approved by the Defense Committee '
        f'in {fm["date_str"]}, with a grade of **PASSED**.'
    )
    lines.append("")
    lines.append(f"**Panel Chair:** {fm['panel_chair']}")
    lines.append("")
    lines.append("**Committee Members:**")
    for mem in fm['committee_members']:
        lines.append(f"- {mem}")
    lines.append("")
    lines.append(f"**Accepted and Approved:** {fm['dean']}, {fm['dean_title']}")
    lines.append("")
    lines.append("---")
    lines.append("")
    
    # Acknowledgements
    lines.append("## ACKNOWLEDGEMENTS")
    lines.append("")
    for p in fm['acknowledgement_paras']:
        lines.append(p)
        lines.append("")
    lines.append("*J.R.C., C.M.G., K.M.P.*")
    lines.append("")
    lines.append("---")
    lines.append("")
    
    # Abstract
    lines.append("## ABSTRACT")
    lines.append("")
    lines.append(f"**Title:** {fm['title']}")
    lines.append("")
    lines.append(f"**Authors:** {', '.join(fm['authors'])}")
    lines.append("")
    lines.append(fm['abstract'])
    lines.append("")
    lines.append("**Keywords:** Point of Sale (POS), Barcode Auto-Read, QR Code Scanning, Online Study Pod Reservation, Kitchen Display System (KDS), ISO 25010, Laravel, ISAT U")
    lines.append("")
    lines.append("---")
    lines.append("")
    
    # Table of Contents
    lines.append("## TABLE OF CONTENTS")
    lines.append("")
    lines.append("- **TITLE PAGE** (i)")
    lines.append("- **APPROVAL SHEET** (ii)")
    lines.append("- **ACKNOWLEDGEMENT** (iii)")
    lines.append("- **ABSTRACT** (iv)")
    lines.append("- **TABLE OF CONTENTS** (v)")
    lines.append("- **LIST OF TABLES** (vi)")
    lines.append("- **LIST OF FIGURES** (vii)")
    lines.append("- **LIST OF APPENDICES** (viii)")
    lines.append("- **CHAPTER I: INTRODUCTION** (1)")
    lines.append("  - Background of the Study (1)")
    lines.append("  - Objectives of the Study (3)")
    lines.append("  - Conceptual Framework of the Study (5)")
    lines.append("  - Definition of Terms (8)")
    lines.append("  - Significance of the Study (11)")
    lines.append("  - Scope and Limitations of the Study (13)")
    lines.append("- **CHAPTER II: REVIEW OF RELATED LITERATURE AND STUDIES** (16)")
    lines.append("  - Related Literature (16)")
    lines.append("  - Related Studies (19)")
    lines.append("  - Table of Comparison (24)")
    lines.append("  - Synthesis (26)")
    lines.append("- **CHAPTER III: METHODOLOGY** (29)")
    lines.append("  - Project Description (29)")
    lines.append("  - Project Development Process (Prototyping Model) (30)")
    lines.append("  - UML Diagrams (33)")
    lines.append("    - Use Case Diagram (33)")
    lines.append("    - Activity Diagram (35)")
    lines.append("    - Class Diagram / Database Schema (37)")
    lines.append("    - Sequence Diagram (39)")
    lines.append("    - System Architecture Diagram (41)")
    lines.append("  - Requirement Analysis (43)")
    lines.append("  - System Evaluation (ISO 25010) (45)")
    lines.append("  - Data Gathering Instrument (47)")
    lines.append("  - Respondents of the Study (48)")
    lines.append("  - Data Analysis Procedure and Statistical Treatment (49)")
    lines.append("- **REFERENCES** (53)")
    lines.append("- **APPENDICES** (56)")
    lines.append("  - Appendix A: ISO 25010 Software Quality Evaluation Instrument (56)")
    lines.append("  - Appendix B: System User Interface Screenshots and Operational Flow Diagrams (60)")
    lines.append("  - Appendix C: Certificate of System Acceptance and Transmittal (67)")
    lines.append("")
    lines.append("---")
    lines.append("")
    
    # List of Tables
    lines.append("## LIST OF TABLES")
    lines.append("")
    lines.append("- **Table 1:** System Comparison of the Related Studies with the Proposed System (Page 24)")
    lines.append("- **Table 2:** Hardware Specification for the System (Page 43)")
    lines.append("- **Table 3:** Software Specification for the System (Page 44)")
    lines.append("- **Table 4:** Five-Point Likert Scale and Verbal Interpretation (Page 46)")
    lines.append("- **Table 5:** Summary of ISO 25010 Software Quality Evaluation Results (Page 50)")
    lines.append("")
    lines.append("---")
    lines.append("")
    
    # List of Figures
    lines.append("## LIST OF FIGURES")
    lines.append("")
    lines.append("- **Figure 1:** Conceptual Framework of the Study (IPO Model) (Page 6)")
    lines.append("- **Figure 2:** Prototyping Model Software Development Life Cycle (Page 30)")
    lines.append("- **Figure 3:** Use Case Diagram of the ISAT U Coffee Shop POS and Reservation System (Page 34)")
    lines.append("- **Figure 4:** Activity Diagram for POS Product QR/Barcode Scanning and Checkout (Page 36)")
    lines.append("- **Figure 5:** Class Diagram / Database Schema of the System (Page 38)")
    lines.append("- **Figure 6:** Sequence Diagram for Cashier POS Product Auto-Read Scan Flow (Page 40)")
    lines.append("- **Figure 7:** System Architecture Diagram of the POS and Reservation Deployment (Page 42)")
    lines.append("")
    lines.append("---")
    lines.append("")
    
    # CHAPTER I
    lines.append("# CHAPTER I: INTRODUCTION")
    lines.append("")
    lines.append("## Background of the Study")
    lines.append("")
    for p in c1['background_paras']:
        lines.append(p)
        lines.append("")
        
    lines.append("## Objectives of the Study")
    lines.append("")
    lines.append(c1['general_objective'])
    lines.append("")
    lines.append("Specifically, the study aimed to:")
    lines.append("")
    for obj in c1['specific_objectives']:
        lines.append(f"- {obj}")
    lines.append("")
    
    lines.append("## Conceptual Framework of the Study")
    lines.append("")
    lines.append(c1['conceptual_framework_paras'][0])
    lines.append("")
    lines.append("![Figure 1: Conceptual Framework of the Study](diagrams/figure1_ipo.png)")
    lines.append("")
    lines.append("*Figure 1. Conceptual Framework of the Study (Input - Process - Output Model)*")
    lines.append("")
    for p in c1['conceptual_framework_paras'][1:]:
        lines.append(p)
        lines.append("")
        
    lines.append("## Definition of Terms")
    lines.append("")
    lines.append("To facilitate a clear understanding of the study, the following technical and operational terms are defined:")
    lines.append("")
    for term, conceptual, operational in c1['terms']:
        lines.append(f"- **{term}:** {conceptual} {operational}")
        lines.append("")
        
    lines.append("## Significance of the Study")
    lines.append("")
    lines.append("The development of this system is directly beneficial to the following individuals and school offices:")
    lines.append("")
    for p in c1['significance_paras']:
        lines.append(p)
        lines.append("")
        
    lines.append("## Scope and Limitations of the Study")
    lines.append("")
    lines.append("### Scope of the Study")
    lines.append("")
    for p in c1['scope_paras']:
        lines.append(p)
        lines.append("")
        
    lines.append("### Limitations of the Study")
    lines.append("")
    lines.append("The study recognizes several operational boundaries and technological constraints:")
    lines.append("")
    for p in c1['limitations_paras']:
        lines.append(p)
        lines.append("")
        
    lines.append("---")
    lines.append("")
    
    # CHAPTER II
    lines.append("# CHAPTER II: REVIEW OF RELATED LITERATURE AND STUDIES")
    lines.append("")
    lines.append(c2['intro'])
    lines.append("")
    
    lines.append("## Related Literature")
    lines.append("")
    for title, review, link in c2['literature']:
        lines.append(f"### {title}")
        lines.append("")
        lines.append(review)
        lines.append("")
        lines.append(link)
        lines.append("")
        
    lines.append("## Related Studies")
    lines.append("")
    for title, summary, comparison in c2['studies']:
        lines.append(f"### {title}")
        lines.append("")
        lines.append(summary)
        lines.append("")
        lines.append(comparison)
        lines.append("")
        
    lines.append("## Table of Comparison")
    lines.append("")
    lines.append("Table 1 presents the comparison of the related studies with the proposed system.")
    lines.append("")
    lines.append("### Table 1: System Comparison of the Related Studies with the Proposed System")
    lines.append("")
    t1 = c2['table_comparison']
    lines.append(render_md_table(t1['headers'], t1['rows']))
    lines.append("")
    
    lines.append("## Synthesis")
    lines.append("")
    for p in c2['synthesis_paras']:
        lines.append(p)
        lines.append("")
        
    lines.append("---")
    lines.append("")
    
    # CHAPTER III
    lines.append("# CHAPTER III: METHODOLOGY")
    lines.append("")
    lines.append("## Project Description")
    lines.append("")
    for p in c3['project_description']:
        lines.append(p)
        lines.append("")
        
    lines.append("## Project Development Process")
    lines.append("")
    lines.append(c3['development_process']['intro'])
    lines.append("")
    lines.append("![Figure 2: Prototyping Model SDLC](diagrams/figure2_prototyping.png)")
    lines.append("")
    lines.append("*Figure 2. Prototyping Model Software Development Life Cycle*")
    lines.append("")
    for phase_name, phase_desc in c3['development_process']['phases']:
        lines.append(f"### {phase_name}")
        lines.append("")
        lines.append(phase_desc)
        lines.append("")
        
    lines.append("## UML Diagrams")
    lines.append("")
    
    # Use Case
    lines.append("### Use Case Diagram")
    lines.append("")
    for p in c3['uml']['use_case_paras']:
        lines.append(p)
        lines.append("")
    lines.append("![Figure 3: Use Case Diagram](diagrams/figure3_use_case.png)")
    lines.append("")
    lines.append("*Figure 3. Use Case Diagram of the System*")
    lines.append("")
    
    # Activity
    lines.append("### Activity Diagram")
    lines.append("")
    for p in c3['uml']['activity_paras']:
        lines.append(p)
        lines.append("")
    lines.append("![Figure 4: Activity Diagram](diagrams/figure4_activity.png)")
    lines.append("")
    lines.append("*Figure 4. Activity Diagram for POS Barcode/QR Scanning and Checkout*")
    lines.append("")
    
    # Class
    lines.append("### Class Diagram and Database Schema")
    lines.append("")
    for p in c3['uml']['class_diag_paras']:
        lines.append(p)
        lines.append("")
    lines.append("![Figure 5: Class Diagram / ERD Schema](diagrams/figure5_class_erd.png)")
    lines.append("")
    lines.append("*Figure 5. Class Diagram / Database Schema of the System*")
    lines.append("")
    
    # Sequence
    lines.append("### Sequence Diagram")
    lines.append("")
    for p in c3['uml']['sequence_paras']:
        lines.append(p)
        lines.append("")
    lines.append("![Figure 6: Sequence Diagram](diagrams/figure6_sequence.png)")
    lines.append("")
    lines.append("*Figure 6. Sequence Diagram for Cashier POS Product Auto-Read Scan Flow*")
    lines.append("")
    
    # Architecture
    lines.append("### System Architecture Diagram")
    lines.append("")
    for p in c3['uml']['architecture_paras']:
        lines.append(p)
        lines.append("")
    lines.append("![Figure 7: System Architecture Diagram](diagrams/figure7_architecture.png)")
    lines.append("")
    lines.append("*Figure 7. System Architecture Diagram of the System*")
    lines.append("")
    
    # Requirement Analysis
    lines.append("## Requirement Analysis")
    lines.append("")
    lines.append("To ensure smooth operation and accurate barcode scanning, the minimum hardware and software requirements were established.")
    lines.append("")
    lines.append("### Table 2: Hardware Specification for the System")
    lines.append("")
    hw = c3['requirements']['hardware']
    lines.append(render_md_table(hw['headers'], hw['rows']))
    lines.append("")
    
    lines.append("### Table 3: Software Specification for the System")
    lines.append("")
    sw = c3['requirements']['software']
    lines.append(render_md_table(sw['headers'], sw['rows']))
    lines.append("")
    
    # System Evaluation
    lines.append("## System Evaluation (ISO 25010)")
    lines.append("")
    lines.append(
        "The system was evaluated based on the ISO 25010 software quality model across eight characteristics: functional suitability, "
        "performance efficiency, compatibility, usability, reliability, security, maintainability, and portability. "
        "Table 4 presents the five-point Likert scale used in interpreting the evaluation scores."
    )
    lines.append("")
    lines.append("### Table 4: Five-Point Likert Scale and Verbal Interpretation")
    lines.append("")
    lk = c3['evaluation']['likert_scale']
    lines.append(render_md_table(lk['headers'], lk['rows']))
    lines.append("")
    
    lines.append("## Data Gathering Instrument")
    lines.append("")
    lines.append(
        "The primary tool used to collect evaluation data was a survey questionnaire patterned after the ISO 25010 software quality standard. "
        "The questionnaire consists of twenty-four (24) statements, with three statements for each of the eight characteristics. "
        "Each statement was rated from 1 (Very Poor) to 5 (Excellent). Space was also provided for comments and suggestions."
    )
    lines.append("")
    
    lines.append("## Respondents of the Study")
    lines.append("")
    for p in c3['evaluation']['respondents']:
        lines.append(p)
        lines.append("")
        
    lines.append("## Data Analysis Procedure and Statistical Treatment")
    lines.append("")
    lines.append(
        "The ratings from the ten respondents were organized and computed using the basic mean formula: "
        "**Mean = Sum of Ratings / Total Number of Respondents**. "
        "The calculated mean for each category was interpreted using the scale in Table 4. "
        "Table 5 shows the summary of the evaluation results."
    )
    lines.append("")
    lines.append("### Table 5: Summary of ISO 25010 Software Quality Evaluation Results")
    lines.append("")
    res = c3['evaluation']['results']
    lines.append(render_md_table(res['headers'], res['rows']))
    lines.append("")
    for p in c3['evaluation']['discussion']:
        lines.append(p)
        lines.append("")
        
    lines.append("---")
    lines.append("")
    
    # REFERENCES
    lines.append("# REFERENCES")
    lines.append("")
    for ref in refs:
        lines.append(f"- {ref}")
        lines.append("")
        
    lines.append("---")
    lines.append("")
    
    # APPENDICES
    lines.append("# APPENDICES")
    lines.append("")
    
    # Appendix A
    lines.append(f"## {app['appendix_a_title']}")
    lines.append("")
    lines.append(app['appendix_a_intro'])
    lines.append("")
    for category_name, questions in app['appendix_a_questions']:
        lines.append(f"### {category_name}")
        lines.append("")
        for q in questions:
            lines.append(f"1. [ ] {q}")
        lines.append("")
        
    lines.append("---")
    lines.append("")
    
    # Appendix B
    lines.append(f"## {app['appendix_b_title']}")
    lines.append("")
    lines.append("This appendix contains selected interface layouts and flowcharts of the developed ISAT U Coffee Shop platform:")
    lines.append("")
    for item_title, item_desc in app['appendix_b_items']:
        lines.append(f"### {item_title}")
        lines.append("")
        lines.append(f"> {item_desc}")
        lines.append("")
        
    lines.append("---")
    lines.append("")
    
    # Appendix C
    lines.append(f"## {app['appendix_c_title']}")
    lines.append("")
    lines.append(app['appendix_c_text'])
    lines.append("")
    lines.append("```")
    lines.append("Republic of the Philippines")
    lines.append("ILOILO SCIENCE AND TECHNOLOGY UNIVERSITY")
    lines.append("Burgos St., La Paz, Iloilo City")
    lines.append("")
    lines.append("CERTIFICATE OF SYSTEM DEMONSTRATION AND ACCEPTANCE")
    lines.append("")
    lines.append("This is to certify that the Capstone Project entitled 'ISAT U Coffee Shop POS System with Online Reservation and QR Code' developed by John Reynel Caparida, Christian Mark Gepana, and Kenth Michael Palma has been demonstrated, evaluated, and accepted by the ISAT U Canteen Management and Auxiliary Services.")
    lines.append("")
    lines.append("Signed:")
    lines.append("")
    lines.append("DR. GABRIEL M. SALVADOR, Ph.D.                  ENGR. MARK ANTHONY VILLANUEVA, M.Eng.")
    lines.append("Campus Auxiliary Administrator                 Capstone Project Adviser")
    lines.append("```")
    lines.append("")
    
    content = "\n".join(lines)
    content = content.replace('—', ' - ').replace('–', '-')
    
    with open(md_path, 'w', encoding='utf-8') as f:
        f.write(content)
        
    print(f"Markdown manuscript successfully written to {md_path}")

if __name__ == '__main__':
    export_markdown()
