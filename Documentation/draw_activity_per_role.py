# -*- coding: utf-8 -*-
"""
Generate Separate Activity Diagrams Per User Role
Modeled directly after the teacher's reference example (image7.png):
- Top authentication flow:
  Start Circle -> Login -> Authentication (Email and Password) -> Diamond Authenticated?
  -> [No] loops back to Login
  -> [Yes] routes to System Dashboard -> User Role Box
- Thick Horizontal Synchronization Fork Bar
- 4 Parallel Vertical Activity Streams for that role
- Thick Horizontal Synchronization Join Bar
- Bullseye Final Node
Strictly Black and White, 300 DPI Vector Style, Simplified wordings.
"""
import os
import matplotlib
matplotlib.use('Agg')
import matplotlib.pyplot as plt
import matplotlib.patches as patches

def draw_single_role_activity(output_png, role_name, columns_data):
    fig, ax = plt.subplots(figsize=(9.0, 9.6), dpi=300)
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

    # Top Title Banner
    banner = patches.FancyBboxPatch(
        (3.0, 126.5), 104.0, 5.5, boxstyle="round,pad=0.2,rounding_size=0.5",
        facecolor="#F0F0F0", edgecolor="#000000", lw=1.4
    )
    ax.add_patch(banner)
    ax.text(55.0, 129.25, f"ACTIVITY DIAGRAM: {role_name.upper()}",
            ha='center', va='center', fontfamily=font_family, fontsize=11.0, fontweight='bold', color="#000000")

    is_customer = (role_name.lower() == 'customer')

    if is_customer:
        # =========================================================================
        # 1. CUSTOMER WORKFLOW (NO LOGIN REQUIRED - DIRECT BOOKING ACCESS)
        # =========================================================================
        dash_cx = 55.0

        # Start Node (Solid black circle)
        start_y = 121.0
        ax.add_patch(patches.Circle((dash_cx, start_y), 2.0, facecolor="#000000", edgecolor="#000000", lw=1.2))

        # Access Online Portal Box
        step1_y = 111.5
        s1_w, s1_h = 44.0, 6.2
        draw_arrow(dash_cx, start_y - 2.0, dash_cx, step1_y + s1_h/2)
        draw_rounded_box(dash_cx, step1_y, s1_w, s1_h, "Access Online Reservation Portal", fontsize=9.0)

        # Public Booking Interface Box
        step2_y = 99.0
        s2_w, s2_h = 52.0, 6.6
        draw_arrow(dash_cx, step1_y - s1_h/2, dash_cx, step2_y + s2_h/2)
        draw_rounded_box(dash_cx, step2_y, s2_w, s2_h, "Customer Booking Interface\n(Public Access - No Login Required)", fontsize=8.4)

        # Customer Role Box
        role_y = 86.5
        role_w, role_h = 36.0, 5.6
        draw_arrow(dash_cx, step2_y - s2_h/2, dash_cx, role_y + role_h/2)
        draw_rect_box(dash_cx, role_y, role_w, role_h, "Customer (Guest Booking)", fontsize=8.8, bold=True)

        # Fork Bar
        fork_y = 74.5
        draw_arrow(dash_cx, role_y - role_h/2, dash_cx, fork_y + 0.6)
        ax.add_patch(patches.Rectangle((3.0, fork_y - 0.6), 104.0, 1.2, facecolor="#000000", edgecolor="#000000"))

        # 4 Parallel Streams
        col_x = [15.0, 41.5, 68.5, 95.0]
        col_w = 23.0
        act_h = 7.4
        y_row1 = 62.0
        y_row2 = 46.0
        join_y = 33.0

        for idx, (s1_txt, s2_txt) in enumerate(columns_data):
            cx = col_x[idx]
            # Fork to Row 1
            draw_arrow(cx, fork_y - 0.6, cx, y_row1 + act_h/2)
            draw_rounded_box(cx, y_row1, col_w, act_h, s1_txt, fontsize=7.8)

            # Row 1 to Row 2
            draw_arrow(cx, y_row1 - act_h/2, cx, y_row2 + act_h/2)
            draw_rounded_box(cx, y_row2, col_w, act_h, s2_txt, fontsize=7.8)

            # Row 2 to Join Bar
            draw_arrow(cx, y_row2 - act_h/2, cx, join_y + 0.6)

        # Join Bar
        ax.add_patch(patches.Rectangle((3.0, join_y - 0.6), 104.0, 1.2, facecolor="#000000", edgecolor="#000000"))

        # Generate QR Pass Box
        post_y1 = 23.0
        pw1, ph1 = 48.0, 6.2
        draw_arrow(dash_cx, join_y - 0.6, dash_cx, post_y1 + ph1/2)
        draw_rounded_box(dash_cx, post_y1, pw1, ph1, "Generate Dynamic QR Booking Pass", fontsize=8.6)

        # Present QR Pass Box
        post_y2 = 12.0
        pw2, ph2 = 54.0, 6.4
        draw_arrow(dash_cx, post_y1 - ph1/2, dash_cx, post_y2 + ph2/2)
        draw_rounded_box(dash_cx, post_y2, pw2, ph2, "Present QR Pass to Staff (Seating Check-In)\n& Cashier POS (Order Review & Payment)", fontsize=7.8)

        # Bullseye Final Node
        stop_y = 3.5
        draw_arrow(dash_cx, post_y2 - ph2/2, dash_cx, stop_y + 2.0)
        ax.add_patch(patches.Circle((dash_cx, stop_y), 2.0, facecolor="#FFFFFF", edgecolor="#000000", lw=1.4))
        ax.add_patch(patches.Circle((dash_cx, stop_y), 1.2, facecolor="#000000", edgecolor="#000000"))

    else:
        # =========================================================================
        # 1. STAFF AUTHENTICATION FLOW (CASHIER, BARISTA, ADMINISTRATOR)
        # =========================================================================
        login_cx = 20.0
        bw_top = 24.0
        bh_top = 6.0

        # Start Node (Solid black circle)
        start_y = 120.0
        ax.add_patch(patches.Circle((login_cx, start_y), 2.0, facecolor="#000000", edgecolor="#000000", lw=1.2))

        # Login Box
        login_y = 110.5
        draw_arrow(login_cx, start_y - 2.0, login_cx, login_y + bh_top/2)
        draw_rounded_box(login_cx, login_y, bw_top, bh_top, "Login", fontsize=9.0)

        # Authentication Box
        auth_y = 99.5
        draw_arrow(login_cx, login_y - bh_top/2, login_cx, auth_y + bh_top/2)
        draw_rounded_box(login_cx, auth_y, bw_top, bh_top, "Authentication (Email and\nPassword)", fontsize=8.2)

        # Decision Diamond
        diam_y = 86.5
        diam_w = 23.0
        diam_h = 9.2
        draw_arrow(login_cx, auth_y - bh_top/2, login_cx, diam_y + diam_h/2)
        draw_diamond(login_cx, diam_y, diam_w, diam_h, "Authenticated?", fontsize=8.0)

        # [No] Branch -> Loop back to Login
        loop_x = 37.5
        ax.plot([login_cx + diam_w/2, loop_x, loop_x], [diam_y, diam_y, login_y], color="#000000", lw=1.2)
        draw_arrow(loop_x, login_y, login_cx + bw_top/2, login_y)
        ax.text(loop_x, (diam_y + login_y)/2, "No", ha='center', va='center',
                fontfamily=font_family, fontsize=8.0, color="#000000",
                bbox=dict(boxstyle="square,pad=0.15", facecolor="#FFFFFF", edgecolor="none"))

        # [Yes] Branch -> Dashboard
        dash_cx = 55.0
        dash_y = 75.5
        dash_w = 26.0
        dash_h = 5.8
        ax.plot([login_cx, login_cx], [diam_y - diam_h/2, dash_y], color="#000000", lw=1.2)
        draw_arrow(login_cx, dash_y, dash_cx - dash_w/2, dash_y)
        ax.text((login_cx + dash_cx - dash_w/2)/2, dash_y, "Yes", ha='center', va='center',
                fontfamily=font_family, fontsize=8.0, color="#000000",
                bbox=dict(boxstyle="square,pad=0.15", facecolor="#FFFFFF", edgecolor="none"))

        draw_rounded_box(dash_cx, dash_y, dash_w, dash_h, "System Dashboard", fontsize=9.0)

        # Role Box (e.g. "Cashier", "Barista", "Administrator")
        role_y = 65.0
        role_w = 26.0
        role_h = 5.4
        draw_arrow(dash_cx, dash_y - dash_h/2, dash_cx, role_y + role_h/2)
        draw_rect_box(dash_cx, role_y, role_w, role_h, role_name, fontsize=8.8, bold=True)

        # =========================================================================
        # 2. HORIZONTAL FORK BAR
        # =========================================================================
        fork_y = 56.5
        draw_arrow(dash_cx, role_y - role_h/2, dash_cx, fork_y + 0.6)
        ax.add_patch(patches.Rectangle((3.0, fork_y - 0.6), 104.0, 1.2, facecolor="#000000", edgecolor="#000000"))

        # =========================================================================
        # 3. FOUR PARALLEL STREAMS (2 ROWS OF ACTIONS EACH)
        # =========================================================================
        col_x = [15.0, 41.5, 68.5, 95.0]
        col_w = 23.0
        act_h = 7.2

        y_row1 = 44.5
        y_row2 = 27.5
        join_y = 13.5

        for idx, (step1, step2) in enumerate(columns_data):
            cx = col_x[idx]
            # Fork to Step 1
            draw_arrow(cx, fork_y - 0.6, cx, y_row1 + act_h/2)
            draw_rounded_box(cx, y_row1, col_w, act_h, step1, fontsize=7.8)

            # Step 1 to Step 2
            draw_arrow(cx, y_row1 - act_h/2, cx, y_row2 + act_h/2)
            draw_rounded_box(cx, y_row2, col_w, act_h, step2, fontsize=7.8)

            # Step 2 to Join Bar
            draw_arrow(cx, y_row2 - act_h/2, cx, join_y + 0.6)

        # =========================================================================
        # 4. HORIZONTAL JOIN BAR & FINAL BULLSEYE NODE
        # =========================================================================
        ax.add_patch(patches.Rectangle((3.0, join_y - 0.6), 104.0, 1.2, facecolor="#000000", edgecolor="#000000"))

        stop_y = 4.5
        draw_arrow(dash_cx, join_y - 0.6, dash_cx, stop_y + 2.2)

        # Bullseye Final Node
        ax.add_patch(patches.Circle((dash_cx, stop_y), 2.2, facecolor="#FFFFFF", edgecolor="#000000", lw=1.4))
        ax.add_patch(patches.Circle((dash_cx, stop_y), 1.4, facecolor="#000000", edgecolor="#000000"))

    plt.tight_layout()
    os.makedirs(os.path.dirname(output_png), exist_ok=True)
    plt.savefig(output_png, dpi=300, bbox_inches='tight')
    plt.close()
    print(f"Generated {role_name} Activity Diagram: {output_png}")

def generate_all_activities():
    activities = [
        ("Customer", [
            ("Browse Available Tables\n& Cafe Seating", "View Seating Layout\n& Item Prices"),
            ("Reserve Dining Table\nor Cafe Table", "Select Date & Time\nSlot Reservation"),
            ("Select Refreshments\n(Optional Pre-Orders)", "Customize Sizes, Sugar\n& Recipe Modifiers"),
            ("Review 15-Minute\nSeating Grace Policy", "Agree to Terms &\nConfirm Reservation")
        ], "Documentation/figures/figure5a_activity_customer.png"),

        ("Cashier", [
            ("Scan Product\nQR Code", "Add Product Directly\nto POS Sales Cart"),
            ("Scan Customer\nQR Booking Pass", "Retrieve Verified\nPre-Order Details"),
            ("Apply Student/Senior\nStatutory Discount", "Compute Subtotal,\n12% VAT & Net Total"),
            ("Collect Payment\n(Cash / GCash)", "Print Customer\nThermal Receipt")
        ], "Documentation/figures/figure5b_activity_cashier.png"),

        ("Barista", [
            ("Receive Order Ticket\non KDS Screen", "View Exact Beverage\nRecipe & Modifiers"),
            ("Prepare Beverage\n& Food Items", "Update Ticket Status\nto Brewing Stage"),
            ("Complete Order\nPreparation", "Advance Ticket Status\nto Ready / Served"),
            ("Trigger Brew-to-Serve\nInventory Routine", "Deduct Ingredients &\nCups in Database")
        ], "Documentation/figures/figure5c_activity_barista.png"),

        ("Administrator", [
            ("Manage User\nAccounts", "Assign System Roles\n& User Credentials"),
            ("Configure Products\n& Recipes (BOM)", "Generate Printable\nMenu QR Code Tags"),
            ("Calculate Yield &\nMonitor Stock", "Analyze BOM Yield &\nBottleneck Stocks"),
            ("Audit Perpetual\nStock Ledger", "Generate Daily Sales\nRevenue Reports")
        ], "Documentation/figures/figure5d_activity_admin.png"),
    ]

    for role, cols, path in activities:
        draw_single_role_activity(path, role, cols)

if __name__ == "__main__":
    generate_all_activities()

