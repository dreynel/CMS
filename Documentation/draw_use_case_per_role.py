# -*- coding: utf-8 -*-
"""
Generate Separate Use Case Diagrams Per User Role
- Customer
- Cashier
- Barista
- Administrator
Strictly Black and White, 300 DPI Vector Style, Simplified wordings.
"""
import os
import math
import matplotlib
matplotlib.use('Agg')
import matplotlib.pyplot as plt
import matplotlib.patches as patches

def ellipse_intersect(x_actor, y_actor, cx, cy, w, h):
    a = w / 2.0
    b = h / 2.0
    dx = cx - x_actor
    dy = cy - y_actor
    if dx == 0 and dy == 0:
        return cx, cy
    t = 1.0 / math.sqrt((dx / a)**2 + (dy / b)**2)
    return (cx - dx * t, cy - dy * t)

def draw_actor(ax, cx, cy, role_title):
    font_family = 'Times New Roman'
    head_r = 1.8
    head_y = cy + 5.5
    ax.add_patch(patches.Circle((cx, head_y), head_r, facecolor="#FFFFFF", edgecolor="#000000", lw=1.5))

    neck_y = head_y - head_r
    hips_y = cy - 1.5
    ax.plot([cx, cx], [neck_y, hips_y], color="#000000", lw=1.6, solid_capstyle='round')

    shoulder_y = neck_y - 0.8
    hand_y = shoulder_y - 1.3
    arm_w = 3.0
    ax.plot([cx - arm_w, cx, cx + arm_w], [hand_y, shoulder_y, hand_y],
            color="#000000", lw=1.6, solid_capstyle='round')

    feet_y = cy - 6.0
    leg_w = 2.4
    ax.plot([cx, cx - leg_w], [hips_y, feet_y], color="#000000", lw=1.6, solid_capstyle='round')
    ax.plot([cx, cx + leg_w], [hips_y, feet_y], color="#000000", lw=1.6, solid_capstyle='round')

    ax.text(cx, feet_y - 2.0, role_title,
            ha='center', va='top', fontfamily=font_family, fontsize=10.0, fontweight='bold', color="#000000")

    return (cx + arm_w, shoulder_y)

def draw_single_role_use_case(output_png, role_name, subsystem_title, use_cases):
    fig, ax = plt.subplots(figsize=(7.5, 5.5), dpi=300)
    ax.set_xlim(0, 100)
    ax.set_ylim(0, 75)
    ax.axis('off')

    font_family = 'Times New Roman'
    fig.patch.set_facecolor('#FFFFFF')

    # Title Banner
    banner = patches.FancyBboxPatch(
        (3.0, 64.5), 94.0, 6.2, boxstyle="round,pad=0.2,rounding_size=0.5",
        facecolor="#F0F0F0", edgecolor="#000000", lw=1.4
    )
    ax.add_patch(banner)
    ax.text(50.0, 67.6, f"USE CASE DIAGRAM: {role_name.upper()}",
            ha='center', va='center', fontfamily=font_family, fontsize=11.0, fontweight='bold', color="#000000")

    # Actor on the left
    anchor_x, anchor_y = draw_actor(ax, 14.0, 36.0, role_name)

    # System boundary box on the right
    bx = 32.0
    by = 7.0
    bw = 63.0
    bh = 54.0

    ax.add_patch(patches.Rectangle((bx, by), bw, bh, facecolor="#FFFFFF", edgecolor="#000000", lw=1.5))

    # Header of boundary
    hdr_h = 5.2
    ax.add_patch(patches.Rectangle((bx, by + bh - hdr_h), bw, hdr_h, facecolor="#EBEBEB", edgecolor="#000000", lw=1.2))
    ax.text(bx + bw/2, by + bh - hdr_h/2, subsystem_title,
            ha='center', va='center', fontfamily=font_family, fontsize=9.2, fontweight='bold', color="#000000")

    # 4 Use Case Ellipses
    uc_cx = bx + bw/2
    uc_w = 48.0
    uc_h = 8.5
    y_positions = [by + 39.5, by + 28.5, by + 17.5, by + 6.5]

    for i, uc_text in enumerate(use_cases):
        cy = y_positions[i]
        ellipse = patches.Ellipse((uc_cx, cy), uc_w, uc_h, facecolor="#FFFFFF", edgecolor="#000000", lw=1.3)
        ax.add_patch(ellipse)
        ax.text(uc_cx, cy, uc_text, ha='center', va='center',
                fontfamily=font_family, fontsize=8.6, fontweight='bold', color="#000000", linespacing=1.12)

        ex, ey = ellipse_intersect(anchor_x, anchor_y, uc_cx, cy, uc_w, uc_h)
        ax.plot([anchor_x, ex], [anchor_y, ey], color="#000000", lw=1.3, solid_capstyle='round')

    plt.tight_layout()
    os.makedirs(os.path.dirname(output_png), exist_ok=True)
    plt.savefig(output_png, dpi=300, bbox_inches='tight')
    plt.close()
    print(f"Generated {role_name} Use Case Diagram: {output_png}")

def generate_all_use_cases():
    roles_data = [
        ("Customer", "Customer Portal", [
            "View Available Tables & Menu",
            "Reserve Table or Cafe Seating",
            "Select Beverage Pre-Orders",
            "Receive QR-Based Booking Pass"
        ], "Documentation/figures/figure4a_use_case_customer.png"),
        ("Cashier", "Point of Sale (POS) Module", [
            "Scan Product QR Code",
            "Verify QR Booking Pass",
            "Compute Discounts & Total",
            "Process Payment & Print Receipt"
        ], "Documentation/figures/figure4b_use_case_cashier.png"),
        ("Barista", "Kitchen Display System (KDS)", [
            "View Order Queue on KDS",
            "Inspect Recipe & Modifiers",
            "Update Preparation Status",
            "Trigger Stock Deduction"
        ], "Documentation/figures/figure4c_use_case_barista.png"),
        ("Administrator", "Management Module", [
            "Manage User Accounts",
            "Manage Products & QR Codes",
            "Monitor Inventory & Yield",
            "Generate Sales Reports"
        ], "Documentation/figures/figure4d_use_case_admin.png"),
    ]

    for role, subsystem, ucs, path in roles_data:
        draw_single_role_use_case(path, role, subsystem, ucs)

if __name__ == "__main__":
    generate_all_use_cases()

