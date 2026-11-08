# -*- coding: utf-8 -*-
"""
Generate UML 2.5 Use Case Diagram with Every User Separated ("Separate every user")
for ISAT U - DC Coffee Shop POS System with Online Reservation and QR Code.
- Incorporates teacher revisions:
  1. Separates every user role into its own independent Actor + System Boundary panel:
     - Panel 1: Customer (Online Reservation & Pre-Order Portal)
     - Panel 2: Cashier (Point of Sale Terminal Module)
     - Panel 3: Barista (Kitchen Display System Module)
     - Panel 4: Administrator (Database, Inventory & Reporting Module)
  2. Uses "Thermal Receipt" (no "80mm" specification)
  3. Exact ray-ellipse boundary intersection so zero lines cross inside ellipses
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

def draw_use_case_diagram(output_path):
    fig, ax = plt.subplots(figsize=(10.5, 10.2), dpi=300)
    ax.set_xlim(0, 140)
    ax.set_ylim(0, 130)
    ax.axis('off')

    fig.patch.set_facecolor('#FFFFFF')
    font_family = 'Times New Roman'

    # Top Title Banner
    title_box = patches.FancyBboxPatch(
        (3.0, 122.5), 134.0, 5.5,
        boxstyle="round,pad=0.2,rounding_size=0.6",
        facecolor="#EFEFEF", edgecolor="#000000", lw=1.5
    )
    ax.add_patch(title_box)
    ax.text(70.0, 125.25, "USE CASE DIAGRAM: SEPARATED BY USER ROLE (CUSTOMER, CASHIER, BARISTA, ADMINISTRATOR)",
            ha='center', va='center', fontfamily=font_family, fontsize=11.0, fontweight='bold', color="#000000")

    def draw_actor(cx, cy, role_title, sub_label):
        head_r = 2.0
        head_y = cy + 6.0
        head = patches.Circle((cx, head_y), head_r, facecolor="#FFFFFF", edgecolor="#000000", lw=1.6)
        ax.add_patch(head)

        neck_y = head_y - head_r
        hips_y = cy - 1.8
        ax.plot([cx, cx], [neck_y, hips_y], color="#000000", lw=1.7, solid_capstyle='round')

        shoulder_y = neck_y - 1.0
        hand_y = shoulder_y - 1.5
        arm_w = 3.4
        ax.plot([cx - arm_w, cx, cx + arm_w], [hand_y, shoulder_y, hand_y],
                color="#000000", lw=1.7, solid_capstyle='round', solid_joinstyle='round')

        feet_y = cy - 6.8
        leg_w = 2.8
        ax.plot([cx, cx - leg_w], [hips_y, feet_y], color="#000000", lw=1.7, solid_capstyle='round')
        ax.plot([cx, cx + leg_w], [hips_y, feet_y], color="#000000", lw=1.7, solid_capstyle='round')

        ax.text(cx, feet_y - 2.0, role_title,
                ha='center', va='top', fontfamily=font_family, fontsize=9.8, fontweight='bold', color="#000000")
        ax.text(cx, feet_y - 4.8, sub_label,
                ha='center', va='top', fontfamily=font_family, fontsize=7.6, fontstyle='italic', color="#222222")

        return (cx + arm_w, shoulder_y)

    def draw_user_subsystem(panel_x, panel_y, role_title, role_sub, sys_header, use_cases):
        """
        Draws a completely separated User Use Case Panel:
        - Outer subtle frame separating this user from other users
        - Stick-figure Actor on the left
        - Dedicated UML System Boundary Box on the right containing 4 Use Case ellipses
        """
        pw = 65.0
        ph = 56.0

        # Outer separation container
        outer = patches.FancyBboxPatch(
            (panel_x, panel_y), pw, ph,
            boxstyle="round,pad=0.0,rounding_size=0.6",
            facecolor="#FFFFFF", edgecolor="#000000", lw=1.4, linestyle="--"
        )
        ax.add_patch(outer)

        # Actor placed at left side of panel
        actor_cx = panel_x + 9.5
        actor_cy = panel_y + ph / 2 + 1.5
        anchor_x, anchor_y = draw_actor(actor_cx, actor_cy, role_title, role_sub)

        # System Boundary Box for this specific user role
        bx = panel_x + 21.0
        by = panel_y + 3.0
        bw = 41.5
        bh = 50.0
        boundary = patches.Rectangle((bx, by), bw, bh, facecolor="#FFFFFF", edgecolor="#000000", lw=1.5)
        ax.add_patch(boundary)

        hdr_h = 4.8
        hdr = patches.Rectangle((bx, by + bh - hdr_h), bw, hdr_h, facecolor="#EAEAEA", edgecolor="#000000", lw=1.2)
        ax.add_patch(hdr)
        ax.text(bx + bw / 2, by + bh - hdr_h / 2, sys_header,
                ha='center', va='center', fontfamily=font_family, fontsize=8.8, fontweight='bold', color="#000000")

        # 4 Use Case Ellipses stacked cleanly inside the boundary box
        uc_cx = bx + bw / 2
        uc_w = 36.0
        uc_h = 8.2
        y_offsets = [40.0, 29.5, 19.0, 8.5]

        for idx, label in enumerate(use_cases):
            uc_cy = by + y_offsets[idx]
            ellipse = patches.Ellipse((uc_cx, uc_cy), uc_w, uc_h, facecolor="#FFFFFF", edgecolor="#000000", lw=1.3)
            ax.add_patch(ellipse)
            ax.text(uc_cx, uc_cy, label,
                    ha='center', va='center', fontfamily=font_family, fontsize=8.0, fontweight='bold',
                    color="#000000", linespacing=1.15)

            ex, ey = ellipse_intersect(anchor_x, anchor_y, uc_cx, uc_cy, uc_w, uc_h)
            ax.plot([anchor_x, ex], [anchor_y, ey], color="#000000", lw=1.3, solid_capstyle='round')

    # 1. TOP-LEFT: CUSTOMER SUBSYSTEM
    draw_user_subsystem(
        panel_x=3.0, panel_y=64.0,
        role_title="1. Customer", role_sub="(Student / Faculty / Guest)",
        sys_header="Customer Online Reservation Portal",
        use_cases=[
            "View Available Tables, Study\nPods & Menu Catalog",
            "Reserve Table / Study Pod &\nSelect Beverage Pre-Orders",
            "Receive & View Dynamic\nQR-Based Booking Pass",
            "Present QR Booking Pass\nfor Counter Check-In"
        ]
    )

    # 2. TOP-RIGHT: CASHIER SUBSYSTEM
    draw_user_subsystem(
        panel_x=72.0, panel_y=64.0,
        role_title="2. Cashier", role_sub="(Front Counter Staff)",
        sys_header="Cashier Point of Sale (POS) Module",
        use_cases=[
            "Scan Product QR Code &\nVerify QR Booking Pass",
            "Process Walk-In & Pre-Order\nItems with Modifiers",
            "Compute Statutory Discounts &\nRecord Cash / GCash Payment",
            "Generate & Print Customer\nThermal Receipt"
        ]
    )

    # 3. BOTTOM-LEFT: BARISTA SUBSYSTEM
    draw_user_subsystem(
        panel_x=3.0, panel_y=5.0,
        role_title="3. Barista", role_sub="(Kitchen Brewing Staff)",
        sys_header="Barista Kitchen Display System (KDS)",
        use_cases=[
            "View Incoming Beverage &\nFood Order Queue on KDS",
            "Inspect Order Recipe (BOM),\nModifiers & Packaging",
            "Update Order Status (Pending,\nBrewing, Ready & Served)",
            "Trigger Ingredient & Cup\nInventory Deduction on Serve"
        ]
    )

    # 4. BOTTOM-RIGHT: ADMINISTRATOR SUBSYSTEM
    draw_user_subsystem(
        panel_x=72.0, panel_y=5.0,
        role_title="4. Administrator", role_sub="(Campus Management)",
        sys_header="Administrator Management Module",
        use_cases=[
            "Manage User Accounts &\nRole-Based Access Control",
            "Manage Products, Recipes &\nPrintable Menu QR Codes",
            "Monitor Inventory Stock Levels,\nConversions & Stock Ledger",
            "Generate & Audit Daily\nSales Revenue Reports"
        ]
    )

    # Bottom Footer Notation
    ax.text(70.0, 2.0,
            "UML 2.5 Use Case Notation  |  Separated by User Role (Customer, Cashier, Barista, Administrator)  |  300 DPI Vector",
            ha='center', va='center', fontfamily=font_family, fontsize=8.0, fontstyle='italic', color="#333333")

    plt.tight_layout()
    os.makedirs(os.path.dirname(output_path), exist_ok=True)
    plt.savefig(output_path, dpi=300, bbox_inches='tight')
    plt.close()
    print(f"Generated Separated-by-User Use Case Diagram at: {output_path}")

if __name__ == "__main__":
    draw_use_case_diagram("Documentation/figures/figure4_use_case.png")
