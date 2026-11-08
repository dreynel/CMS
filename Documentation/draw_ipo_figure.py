# -*- coding: utf-8 -*-
"""
Generate Conceptual Framework (IPO Model) Diagram for ISAT U Coffee Shop POS System
Strictly Black and White, 2D Vector Style
- Incorporates teacher revisions:
  1. Changed facility seating setup to "Number of Tables & Chairs (Dining Tables, Chairs & Quiet Study Pods)"
  2. Made all arrows (including feedback arrows through System's Evaluation) solid lines
"""
import os
import matplotlib
matplotlib.use('Agg')
import matplotlib.pyplot as plt
import matplotlib.patches as patches

def draw_ipo_diagram(output_path):
    # Designed at 6.4 x 5.0 inches to match 1:1 bond paper printable width (6.0 in)
    fig, ax = plt.subplots(figsize=(6.4, 5.0), dpi=300)
    ax.set_xlim(0, 62.5)
    ax.set_ylim(0, 49.0)
    ax.axis('off')

    font_family = 'Times New Roman'
    fig.patch.set_facecolor('#FFFFFF')

    box_w = 17.5
    box_h = 33.5
    box_y = 12.5
    header_h = 4.6

    x_in = 2.0
    x_pr = 22.5
    x_out = 43.0

    # 1. INPUT BOX
    rect_in = patches.FancyBboxPatch(
        (x_in, box_y), box_w, box_h,
        boxstyle="round,pad=0.0,rounding_size=0.4",
        facecolor="#FFFFFF", edgecolor="#000000", linewidth=1.5
    )
    ax.add_patch(rect_in)
    rect_in_hdr = patches.FancyBboxPatch(
        (x_in, box_y + box_h - header_h), box_w, header_h,
        boxstyle="round,pad=0.0,rounding_size=0.4",
        facecolor="#EFEFEF", edgecolor="#000000", linewidth=1.2
    )
    ax.add_patch(rect_in_hdr)
    ax.text(x_in + box_w/2, box_y + box_h - header_h/2, "INPUT",
            ha='center', va='center', fontfamily=font_family, fontsize=11.5, fontweight='bold', color="#000000")

    input_text = (
        "• User Accounts & Roles\n"
        "   (Admin, Cashier,\n"
        "   and Barista)\n\n"
        "• Product Catalog Data\n"
        "   (Product QR Codes,\n"
        "   Prices & Modifiers)\n\n"
        "• Number of Tables & Chairs\n"
        "   (Dining Tables, Chairs &\n"
        "   Cafe Booth Seating)\n\n"
        "• Online Booking Records\n"
        "   (Time Slots, Guests &\n"
        "   Pre-ordered Items)\n\n"
        "• Transaction Data\n"
        "   (Cash Tender, GCash &\n"
        "   Discount Information)"
    )
    ax.text(x_in + 1.0, box_y + box_h - header_h - 1.0, input_text,
            ha='left', va='top', fontfamily=font_family, fontsize=7.6, linespacing=1.08, color="#000000")

    # 2. PROCESS BOX
    rect_pr = patches.FancyBboxPatch(
        (x_pr, box_y), box_w, box_h,
        boxstyle="round,pad=0.0,rounding_size=0.4",
        facecolor="#FFFFFF", edgecolor="#000000", linewidth=1.5
    )
    ax.add_patch(rect_pr)
    rect_pr_hdr = patches.FancyBboxPatch(
        (x_pr, box_y + box_h - header_h), box_w, header_h,
        boxstyle="round,pad=0.0,rounding_size=0.4",
        facecolor="#EFEFEF", edgecolor="#000000", linewidth=1.2
    )
    ax.add_patch(rect_pr_hdr)
    ax.text(x_pr + box_w/2, box_y + box_h - header_h/2, "PROCESS",
            ha='center', va='center', fontfamily=font_family, fontsize=11.5, fontweight='bold', color="#000000")

    process_text = (
        "• Staff authentication &\n"
        "   role access validation\n\n"
        "• Product QR code scanning\n"
        "   & order processing\n\n"
        "• Payment recording, discount\n"
        "   & thermal receipt printing\n\n"
        "• Online reservation & QR\n"
        "   booking pass generation\n\n"
        "• KDS order display &\n"
        "   status updating\n\n"
        "• Inventory monitoring &\n"
        "   sales report generation"
    )
    ax.text(x_pr + 1.0, box_y + box_h - header_h - 1.0, process_text,
            ha='left', va='top', fontfamily=font_family, fontsize=7.6, linespacing=1.10, color="#000000")

    # 3. OUTPUT BOX (Contains ONLY the name of the system)
    rect_out = patches.FancyBboxPatch(
        (x_out, box_y), box_w, box_h,
        boxstyle="round,pad=0.0,rounding_size=0.4",
        facecolor="#FFFFFF", edgecolor="#000000", linewidth=1.5
    )
    ax.add_patch(rect_out)
    rect_out_hdr = patches.FancyBboxPatch(
        (x_out, box_y + box_h - header_h), box_w, header_h,
        boxstyle="round,pad=0.0,rounding_size=0.4",
        facecolor="#EFEFEF", edgecolor="#000000", linewidth=1.2
    )
    ax.add_patch(rect_out_hdr)
    ax.text(x_out + box_w/2, box_y + box_h - header_h/2, "OUTPUT",
            ha='center', va='center', fontfamily=font_family, fontsize=11.5, fontweight='bold', color="#000000")

    output_text = (
        "RAEDEN'S RESTO,\n"
        "BAKERY, CAFE\n"
        "POS SYSTEM\n"
        "WITH ONLINE\n"
        "RESERVATION\n"
        "AND QR CODE"
    )
    ax.text(x_out + box_w/2, box_y + (box_h - header_h)/2, output_text,
            ha='center', va='center', fontfamily=font_family, fontsize=10.0, fontweight='bold', linespacing=1.25, color="#000000")

    # Straight SOLID horizontal arrows between inline boxes
    mid_y = box_y + box_h/2
    ax.annotate('', xy=(x_pr, mid_y), xytext=(x_in + box_w, mid_y),
                arrowprops=dict(arrowstyle="->", color="#000000", lw=1.6, linestyle="-"))
    ax.annotate('', xy=(x_out, mid_y), xytext=(x_pr + box_w, mid_y),
                arrowprops=dict(arrowstyle="->", color="#000000", lw=1.6, linestyle="-"))

    # 4. SYSTEM'S EVALUATION BOX (Centered below)
    eval_w = 36.0
    eval_h = 5.8
    eval_x = (62.5 - eval_w)/2
    eval_y = 2.5
    rect_eval = patches.FancyBboxPatch(
        (eval_x, eval_y), eval_w, eval_h,
        boxstyle="round,pad=0.0,rounding_size=0.4",
        facecolor="#F6F6F6", edgecolor="#000000", linewidth=1.5
    )
    ax.add_patch(rect_eval)
    ax.text(eval_x + eval_w/2, eval_y + eval_h/2,
            "SYSTEM'S EVALUATION\n(ISO/IEC 25010 Software Quality Standards)",
            ha='center', va='center', fontfamily=font_family, fontsize=9.8, fontweight='bold', linespacing=1.15, color="#000000")

    # Orthogonal SOLID feedback lines: Output -> Evaluation -> Input
    x_out_mid = x_out + box_w/2
    y_eval_mid = eval_y + eval_h/2
    x_in_mid = x_in + box_w/2

    # Drop from bottom of Output box (SOLID line)
    ax.plot([x_out_mid, x_out_mid], [box_y, y_eval_mid], color="#000000", lw=1.5, linestyle="-")
    # Horizontal solid arrow to right edge of Evaluation box
    ax.annotate('', xy=(eval_x + eval_w, y_eval_mid), xytext=(x_out_mid, y_eval_mid),
                arrowprops=dict(arrowstyle="->", color="#000000", lw=1.5, linestyle="-"))

    # Solid line from left edge of Evaluation box to Input box x-center
    ax.plot([eval_x, x_in_mid], [y_eval_mid, y_eval_mid], color="#000000", lw=1.5, linestyle="-")
    # Vertical solid arrow up to bottom of Input box
    ax.annotate('', xy=(x_in_mid, box_y), xytext=(x_in_mid, y_eval_mid),
                arrowprops=dict(arrowstyle="->", color="#000000", lw=1.5, linestyle="-"))

    plt.tight_layout()
    os.makedirs(os.path.dirname(output_path), exist_ok=True)
    plt.savefig(output_path, dpi=300, bbox_inches='tight')
    plt.close()
    print(f"Generated Strictly B&W Conceptual Framework diagram (Solid Arrows + Number of Tables & Chairs): {output_path}")

if __name__ == "__main__":
    draw_ipo_diagram("Documentation/figures/figure1_ipo.png")
