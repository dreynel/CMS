# -*- coding: utf-8 -*-
"""
Generate UML Activity Diagram for ISAT U - DC Coffee Shop POS System
Modeled directly after the teacher's provided reference figure ("Basehan ang isa ka figure ang example figure"):
- Top authentication flow:
  Start Circle -> Login / Access System -> Authentication (Email and Password) -> Diamond "Authenticated?"
  -> [No] loops back to Login
  -> [Yes] routes to System Dashboard -> User Role
- Thick Horizontal Synchronization Fork Bar
- 4 Parallel Vertical Activity Streams (Customer, Cashier, Barista, Administrator)
- Thick Horizontal Synchronization Join Bar
- Bullseye Final Node at bottom center
- Strictly Black and White, 300 DPI Vector Style
"""
import os
import matplotlib
matplotlib.use('Agg')
import matplotlib.pyplot as plt
import matplotlib.patches as patches

def draw_activity_diagram(output_png):
    fig, ax = plt.subplots(figsize=(9.5, 9.2), dpi=300)
    ax.set_xlim(0, 110)
    ax.set_ylim(0, 134)
    ax.axis('off')

    font_family = 'Times New Roman'
    fig.patch.set_facecolor('#FFFFFF')

    def draw_rounded_box(cx, cy, w, h, text, fontsize=8.2):
        box = patches.FancyBboxPatch(
            (cx - w/2, cy - h/2), w, h,
            boxstyle="round,pad=0.1,rounding_size=1.2",
            facecolor="#FFFFFF", edgecolor="#000000", lw=1.3
        )
        ax.add_patch(box)
        ax.text(cx, cy, text, ha='center', va='center',
                fontfamily=font_family, fontsize=fontsize, color="#000000", linespacing=1.15)

    def draw_rect_box(cx, cy, w, h, text, fontsize=8.5, bold=False):
        box = patches.Rectangle(
            (cx - w/2, cy - h/2), w, h,
            facecolor="#FFFFFF", edgecolor="#000000", lw=1.3
        )
        ax.add_patch(box)
        ax.text(cx, cy, text, ha='center', va='center',
                fontfamily=font_family, fontsize=fontsize,
                fontweight='bold' if bold else 'normal', color="#000000")

    def draw_diamond(cx, cy, w, h, text, fontsize=8.0):
        pts = [
            [cx, cy + h/2],
            [cx + w/2, cy],
            [cx, cy - h/2],
            [cx - w/2, cy]
        ]
        poly = patches.Polygon(pts, closed=True, facecolor="#FFFFFF", edgecolor="#000000", lw=1.3)
        ax.add_patch(poly)
        ax.text(cx, cy, text, ha='center', va='center',
                fontfamily=font_family, fontsize=fontsize, color="#000000")

    def draw_arrow(x1, y1, x2, y2):
        ax.annotate('', xy=(x2, y2), xytext=(x1, y1),
                    arrowprops=dict(arrowstyle="-|>", color="#000000", lw=1.2, mutation_scale=11))

    # =========================================================================
    # 1. TOP AUTHENTICATION FLOW (MATCHING TEACHER'S REFERENCE FIGURE)
    # =========================================================================
    login_cx = 20.0
    bw_top = 24.0
    bh_top = 6.2

    # Start Initial Node (Solid black circle)
    start_y = 128.5
    ax.add_patch(patches.Circle((login_cx, start_y), 2.1, facecolor="#000000", edgecolor="#000000", lw=1.2))

    # Login Box
    login_y = 118.0
    draw_arrow(login_cx, start_y - 2.1, login_cx, login_y + bh_top/2)
    draw_rounded_box(login_cx, login_y, bw_top, bh_top, "Login", fontsize=9.0)

    # Authentication (Email and Password) Box
    auth_y = 106.5
    draw_arrow(login_cx, login_y - bh_top/2, login_cx, auth_y + bh_top/2)
    draw_rounded_box(login_cx, auth_y, bw_top, bh_top, "Authentication (Email and\nPassword)", fontsize=8.4)

    # Decision Diamond: Authenticated?
    diam_y = 93.0
    diam_w = 24.0
    diam_h = 9.6
    draw_arrow(login_cx, auth_y - bh_top/2, login_cx, diam_y + diam_h/2)
    draw_diamond(login_cx, diam_y, diam_w, diam_h, "Authenticated?", fontsize=8.2)

    # [No] Branch: From right vertex of diamond (login_cx + diam_w/2, diam_y) -> right to x=38 -> up to login_y -> left to Login box right edge
    loop_x = 38.5
    ax.plot([login_cx + diam_w/2, loop_x, loop_x], [diam_y, diam_y, login_y], color="#000000", lw=1.2)
    draw_arrow(loop_x, login_y, login_cx + bw_top/2, login_y)
    ax.text(loop_x, (diam_y + login_y)/2, "No", ha='center', va='center',
            fontfamily=font_family, fontsize=8.2, color="#000000",
            bbox=dict(boxstyle="square,pad=0.15", facecolor="#FFFFFF", edgecolor="none"))

    # [Yes] Branch: From bottom vertex of diamond (login_cx, diam_y - diam_h/2) -> down to y=82.0 -> right to System Dashboard
    dash_cx = 55.0
    dash_y = 82.0
    dash_w = 26.0
    dash_h = 6.0
    ax.plot([login_cx, login_cx], [diam_y - diam_h/2, dash_y], color="#000000", lw=1.2)
    draw_arrow(login_cx, dash_y, dash_cx - dash_w/2, dash_y)
    ax.text((login_cx + dash_cx - dash_w/2)/2, dash_y, "Yes", ha='center', va='center',
            fontfamily=font_family, fontsize=8.2, color="#000000",
            bbox=dict(boxstyle="square,pad=0.15", facecolor="#FFFFFF", edgecolor="none"))

    draw_rounded_box(dash_cx, dash_y, dash_w, dash_h, "System Dashboard", fontsize=9.0)

    # Role Box below System Dashboard (Rectangular box like "End-User" in teacher's example)
    role_y = 70.5
    role_w = 29.0
    role_h = 5.6
    draw_arrow(dash_cx, dash_y - dash_h/2, dash_cx, role_y + role_h/2)
    draw_rect_box(dash_cx, role_y, role_w, role_h, "User Role (Customer / Cashier / Barista / Admin)", fontsize=8.0)

    # =========================================================================
    # 2. TOP HORIZONTAL FORK BAR
    # =========================================================================
    fork_y = 61.5
    draw_arrow(dash_cx, role_y - role_h/2, dash_cx, fork_y + 0.6)
    ax.add_patch(patches.Rectangle((3.0, fork_y - 0.6), 104.0, 1.2, facecolor="#000000", edgecolor="#000000"))

    # =========================================================================
    # 3. FOUR PARALLEL VERTICAL ACTIVITY STREAMS BELOW FORK BAR
    # =========================================================================
    col_x = [14.5, 41.5, 68.5, 95.5]
    col_w = 23.5
    act_h = 7.0

    y_row1 = 50.5
    y_row2 = 38.0
    y_row3 = 25.5

    columns_data = [
        # Column 1: Customer
        (col_x[0], [
            "Customer: View Availability\nof Tables, Pods & Menu",
            "Reserve Table / Study Pod &\nSelect Pre-Order Items",
            "Receive QR Booking Pass &\nClaim Order at Counter"
        ]),
        # Column 2: Cashier
        (col_x[1], [
            "Cashier: Scan Product QR\nCode or Booking QR Pass",
            "Process Order, Compute\nDiscount & Record Payment",
            "Generate & Print Customer\nThermal Receipt"
        ]),
        # Column 3: Barista
        (col_x[2], [
            "Barista: View Incoming Order\nQueue on KDS Screen",
            "Prepare Beverage & Food\nItems per Recipe/Modifiers",
            "Update Status to Ready/Served\n& Deduct Ingredient Stock"
        ]),
        # Column 4: Administrator
        (col_x[3], [
            "Admin: Manage Users, Menu\n& Printable Product QR Tags",
            "Monitor Inventory Stock\nLevels & Item Conversions",
            "Generate & Review Daily\nSales Revenue Reports"
        ])
    ]

    join_y = 13.0

    for cx, steps in columns_data:
        # Arrow from Fork Bar down to Row 1 box
        draw_arrow(cx, fork_y - 0.6, cx, y_row1 + act_h/2)
        draw_rounded_box(cx, y_row1, col_w, act_h, steps[0], fontsize=7.6)

        # Arrow from Row 1 box down to Row 2 box
        draw_arrow(cx, y_row1 - act_h/2, cx, y_row2 + act_h/2)
        draw_rounded_box(cx, y_row2, col_w, act_h, steps[1], fontsize=7.6)

        # Arrow from Row 2 box down to Row 3 box
        draw_arrow(cx, y_row2 - act_h/2, cx, y_row3 + act_h/2)
        draw_rounded_box(cx, y_row3, col_w, act_h, steps[2], fontsize=7.6)

        # Arrow from Row 3 box down to Join Bar
        draw_arrow(cx, y_row3 - act_h/2, cx, join_y + 0.6)

    # =========================================================================
    # 4. BOTTOM HORIZONTAL JOIN BAR & FINAL BULLSEYE NODE
    # =========================================================================
    ax.add_patch(patches.Rectangle((3.0, join_y - 0.6), 104.0, 1.2, facecolor="#000000", edgecolor="#000000"))

    stop_y = 4.2
    draw_arrow(dash_cx, join_y - 0.6, dash_cx, stop_y + 2.3)

    # Bullseye Final Node
    ax.add_patch(patches.Circle((dash_cx, stop_y), 2.2, facecolor="#FFFFFF", edgecolor="#000000", lw=1.4))
    ax.add_patch(patches.Circle((dash_cx, stop_y), 1.4, facecolor="#000000", edgecolor="#000000"))

    plt.tight_layout()
    os.makedirs(os.path.dirname(output_png), exist_ok=True)
    plt.savefig(output_png, dpi=300, bbox_inches='tight')
    plt.close()
    print(f"Generated Activity Diagram matching teacher's reference figure: {output_png}")

if __name__ == "__main__":
    draw_activity_diagram("Documentation/figures/figure5_activity.png")
