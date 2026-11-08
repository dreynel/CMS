# -*- coding: utf-8 -*-
"""
Generate and assemble all strictly Black and White 2D Vector figures for the ISAT U Coffee Shop Capstone Manuscript
- All 7 figures are strictly black and white (monochrome / grayscale 2D vector line art)
- Formatted and proportioned for 8.5 x 11 short bond paper with high-contrast, large, legible typography
- Activity Diagram and Sequence Diagram are structured PER USER across separate user roles
"""
import os
from draw_ipo_figure import draw_ipo_diagram
from draw_prototyping_figure import draw_prototyping_model
from draw_system_architecture import draw_system_architecture
from draw_use_case_diagram import draw_use_case_diagram
from draw_activity_diagram import draw_activity_diagram
from draw_class_erd_diagram import draw_class_erd_diagram
from draw_sequence_diagram import draw_sequence_diagram

def main():
    os.makedirs('Documentation/figures', exist_ok=True)
    
    print("1. Generating Conceptual Framework (Figure 1: Inline IPO + ISO 25010 below)...")
    draw_ipo_diagram("Documentation/figures/figure1_ipo.png")
    
    print("2. Generating Prototyping SDLC Model (Figure 2: Strictly B&W 2D Vector)...")
    draw_prototyping_model("Documentation/figures/figure2_prototyping.png")
    
    print("3. Generating Vector System Architecture (Figure 3: Strictly B&W 2D Vector)...")
    draw_system_architecture("Documentation/figures/figure3_system_architecture.png",
                             "Documentation/figures/figure3_system_architecture.svg")
    
    print("4. Generating Use Case Diagram (Figure 4: Strictly B&W 2D Vector per User)...")
    draw_use_case_diagram("Documentation/figures/figure4_use_case.png")
    
    print("5. Generating Swimlane Activity Diagram (Figure 5: Strictly B&W per User)...")
    draw_activity_diagram("Documentation/figures/figure5_activity.png")
    
    print("6. Generating Class Diagram & ERD (Figure 6: Strictly B&W 11 Tables Schema)...")
    draw_class_erd_diagram("Documentation/figures/figure6_class_erd.png")
    
    print("7. Generating Sequence Diagram (Figure 7: Strictly B&W per User Lifelines)...")
    draw_sequence_diagram("Documentation/figures/figure7_sequence.png")
    
    print(">>> All 7 figures generated successfully in Documentation/figures/ in strictly Black & White! <<<")

if __name__ == "__main__":
    main()
