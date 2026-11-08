# -*- coding: utf-8 -*-
"""
Generate UML 2.5 Sequence Diagram for ISAT U - DC Coffee Shop POS System
Modeled directly after the teacher's provided reference Sequence Diagram (image10.png):
- 6 Top Rounded Participant Boxes:
  1. Customer
  2. Web / POS Interface
  3. Coffee Shop System (PHP)
  4. MySQL Database
  5. Cashier
  6. Barista / KDS
- Discrete vertical activation bars per interaction block
- Combined Interaction Fragments (loop / alt frames with pentagonal corner tags & guard conditions)
- Numbered synchronous request arrows (solid) and return arrows (dashed)
- Strictly Black and White, 300 DPI Vector Style
"""
import os
import matplotlib
matplotlib.use('Agg')
import matplotlib.pyplot as plt
import matplotlib.patches as patches

def draw_sequence_diagram(output_png):
    fig, ax = plt.subplots(figsize=(9.5, 13.0), dpi=300)
    ax.set_xlim(0, 120)
    ax.set_ylim(0, 175)
    ax.axis('off')

    font_family = 'Times New Roman'
    fig.patch.set_facecolor('#FFFFFF')

    # 6 Participants matching the exact top-header layout of teacher's reference figure
    participants = [
        ("Customer", 11.0),
        ("Web / POS\nInterface", 30.5),
        ("Coffee Shop\nSystem (PHP)", 50.0),
        ("MySQL\nDatabase", 69.5),
        ("Cashier", 89.0),
        ("Barista /\nKDS Screen", 108.5)
    ]

    top_y = 169.5
    bot_y = 3.0
    pw = 16.5
    ph = 6.2

    for name, px in participants:
        box = patches.FancyBboxPatch(
            (px - pw/2, top_y - ph/2), pw, ph,
            boxstyle="round,pad=0.1,rounding_size=1.0",
            facecolor="#FFFFFF", edgecolor="#000000", lw=1.3
        )
        ax.add_patch(box)
        ax.text(px, top_y, name, ha='center', va='center',
                fontfamily=font_family, fontsize=8.0, fontweight='bold', color="#000000", linespacing=1.1)
        ax.plot([px, px], [bot_y, top_y - ph/2], color="#000000", lw=0.9, linestyle="--", zorder=1)

    aw = 1.6
    half_aw = aw / 2.0

    def draw_act(px, y_top, y_bot):
        rect = patches.Rectangle(
            (px - half_aw, y_bot), aw, y_top - y_bot,
            facecolor="#FFFFFF", edgecolor="#000000", lw=1.0, zorder=3
        )
        ax.add_patch(rect)

    def draw_msg(x1, x2, y, label, is_return=False):
        sx = x1 + half_aw if x2 > x1 else x1 - half_aw
        ex = x2 - half_aw if x2 > x1 else x2 + half_aw
        if is_return:
            ax.annotate('', xy=(ex, y), xytext=(sx, y),
                        arrowprops=dict(arrowstyle="->", color="#000000", lw=1.0, linestyle="--", mutation_scale=10),
                        zorder=4)
        else:
            ax.annotate('', xy=(ex, y), xytext=(sx, y),
                        arrowprops=dict(arrowstyle="-|>", color="#000000", lw=1.1, linestyle="-", mutation_scale=10),
                        zorder=4)
        mid_x = (sx + ex) / 2.0
        ax.text(mid_x, y + 0.6, label, ha='center', va='bottom',
                fontfamily=font_family, fontsize=6.5, color="#000000",
                bbox=dict(boxstyle="square,pad=0.08", facecolor="#FFFFFF", edgecolor="none"),
                zorder=5)

    def draw_self_msg(px, y_top, y_bot, label):
        sx = px + half_aw
        loop_w = 4.0
        ax.plot([sx, sx + loop_w, sx + loop_w, sx], [y_top, y_top, y_bot, y_bot], color="#000000", lw=1.0, zorder=4)
        ax.annotate('', xy=(sx, y_bot), xytext=(sx + 1.0, y_bot),
                    arrowprops=dict(arrowstyle="-|>", color="#000000", lw=1.0, mutation_scale=9), zorder=4)
        ax.text(sx + loop_w + 0.6, (y_top + y_bot)/2.0, label, ha='left', va='center',
                fontfamily=font_family, fontsize=6.4, color="#000000", linespacing=1.05,
                bbox=dict(boxstyle="square,pad=0.08", facecolor="#FFFFFF", edgecolor="none"), zorder=5)

    def draw_fragment(x, y_bot, w, h, tag, guard_top, alt_div_y=None, guard_bot=None):
        # Main frame rectangle
        rect = patches.Rectangle((x, y_bot), w, h, facecolor="none", edgecolor="#000000", lw=1.2, zorder=2)
        ax.add_patch(rect)
        # Pentagonal tag in top-left corner
        y_top = y_bot + h
        tw = 6.5
        th = 2.6
        pent = patches.Polygon([
            [x, y_top],
            [x + tw, y_top],
            [x + tw, y_top - th + 0.8],
            [x + tw - 1.2, y_top - th],
            [x, y_top - th]
        ], closed=True, facecolor="#FFFFFF", edgecolor="#000000", lw=1.1, zorder=4)
        ax.add_patch(pent)
        ax.text(x + tw/2 - 0.2, y_top - th/2, tag, ha='center', va='center',
                fontfamily=font_family, fontsize=6.8, fontweight='bold', color="#000000", zorder=5)
        ax.text(x + tw + 1.5, y_top - th/2, guard_top, ha='left', va='center',
                fontfamily=font_family, fontsize=6.8, fontweight='bold', color="#000000",
                bbox=dict(boxstyle="square,pad=0.08", facecolor="#FFFFFF", edgecolor="none"), zorder=5)

        if alt_div_y is not None:
            ax.plot([x, x + w], [alt_div_y, alt_div_y], color="#000000", lw=1.0, linestyle="--", zorder=2)
            if guard_bot:
                ax.text(x + tw + 1.5, alt_div_y - 1.5, guard_bot, ha='left', va='center',
                        fontfamily=font_family, fontsize=6.8, fontweight='bold', color="#000000",
                        bbox=dict(boxstyle="square,pad=0.08", facecolor="#FFFFFF", edgecolor="none"), zorder=5)

    # X-coordinates
    x_cust, x_ui, x_sys, x_db, x_cash, x_bar = 11.0, 30.5, 50.0, 69.5, 89.0, 108.5

    # =========================================================================
    # BLOCK 1: USER LOGIN & ROLE AUTHENTICATION (y: 162 down to 147)
    # =========================================================================
    draw_act(x_cust, 162.5, 147.5)
    draw_act(x_ui,   162.0, 148.0)
    draw_act(x_sys,  160.0, 149.5)
    draw_act(x_db,   158.0, 152.0)

    draw_msg(x_cust, x_ui,  161.5, "1: Enter login credentials")
    draw_msg(x_ui,   x_sys, 159.2, "2: Send credentials")
    draw_msg(x_sys,  x_db,  157.0, "3: Verify user account & role")
    draw_msg(x_db,   x_sys, 153.5, "4: Return user role record", is_return=True)
    draw_msg(x_sys,  x_ui,  150.8, "5: Login confirmed (Role session)", is_return=True)
    draw_msg(x_ui,   x_cust, 148.5, "6: Display role dashboard / portal", is_return=True)

    # =========================================================================
    # BLOCK 2: ONLINE RESERVATION & PRE-ORDERING (y: 144 down to 124)
    # =========================================================================
    draw_act(x_cust, 143.5, 124.5)
    draw_act(x_ui,   143.0, 125.0)
    draw_act(x_sys,  141.0, 126.5)
    draw_act(x_db,   139.0, 129.0)

    draw_msg(x_cust, x_ui,  142.5, "7: Select table/study pod & pre-order items")
    draw_msg(x_ui,   x_sys, 140.0, "8: Submit reservation & pre-order request")
    draw_msg(x_sys,  x_db,  137.5, "9: Save reservation, items & QR token")
    draw_msg(x_db,   x_sys, 134.0, "10: Return booking confirmation ID", is_return=True)
    draw_self_msg(x_sys,    132.5, 129.5, "11: Generate QR booking\npass & T-15m schedule")
    draw_msg(x_sys,  x_ui,  127.5, "12: Send QR-based booking pass", is_return=True)
    draw_msg(x_ui,   x_cust, 125.2, "13: Display scannable QR booking pass", is_return=True)

    # =========================================================================
    # BLOCK 3: COMBINED FRAGMENT - POS ORDER PROCESSING & QR SCANNING (y: 121 down to 83)
    # =========================================================================
    draw_fragment(2.5, 83.0, 115.0, 38.0, "alt", "[QR Booking Pass Check-In / Pre-Order]",
                  alt_div_y=103.5, guard_bot="[Walk-In Product QR Code Scanning]")

    # Top Operand: QR Booking Pass Check-In
    draw_act(x_cust, 116.5, 114.0)
    draw_act(x_cash, 116.5, 105.5)
    draw_act(x_ui,   114.0, 106.0)
    draw_act(x_sys,  112.0, 106.5)
    draw_act(x_db,   110.5, 107.5)

    draw_msg(x_cust, x_cash, 115.5, "14: Present QR-based booking pass at counter")
    draw_msg(x_cash, x_ui,   113.0, "15: Scan customer QR booking pass")
    draw_msg(x_ui,   x_sys,  111.0, "16: Verify QR token & fetch pre-order")
    draw_msg(x_sys,  x_db,   109.2, "17: Retrieve reservation & pre-order items")
    draw_msg(x_db,   x_sys,  107.6, "18: Return booking & cart items", is_return=True)
    draw_msg(x_sys,  x_cash, 105.8, "19: Display verified booking & order total", is_return=True)

    # Bottom Operand: Walk-In Product QR Scanning & Checkout
    draw_act(x_cash, 100.5, 84.5)
    draw_act(x_ui,   100.0, 85.0)
    draw_act(x_sys,  98.0,  85.5)
    draw_act(x_db,   96.0,  87.0)

    draw_msg(x_cash, x_ui,   99.2, "20: Scan product QR code & select modifiers")
    draw_msg(x_ui,   x_sys,  97.0, "21: Request product details & cup stock")
    draw_msg(x_sys,  x_db,   95.0, "22: Query product record & packaging stock")
    draw_msg(x_db,   x_sys,  93.0, "23: Return product price & stock status", is_return=True)
    draw_self_msg(x_sys,     91.8, 89.0, "24: Compute discount (10%/20%)\n& 12% VAT net total")
    draw_msg(x_sys,  x_ui,   86.5, "25: Return computed order summary", is_return=True)

    # =========================================================================
    # BLOCK 4: PAYMENT RECORDING & THERMAL RECEIPT GENERATION (y: 80 down to 57)
    # =========================================================================
    draw_act(x_cash, 79.5, 58.0)
    draw_act(x_ui,   79.0, 58.5)
    draw_act(x_sys,  77.0, 61.0)
    draw_act(x_db,   74.5, 68.5)
    draw_act(x_bar,  66.5, 63.5)
    draw_act(x_cust, 60.5, 57.5)

    draw_msg(x_cash, x_ui,   78.2, "26: Encode Cash / GCash payment & confirm order")
    draw_msg(x_ui,   x_sys,  75.8, "27: Submit order & payment transaction")
    draw_msg(x_sys,  x_db,   73.5, "28: Save order, items & payment records")
    draw_msg(x_db,   x_sys,  70.0, "29: Confirm transaction saved", is_return=True)
    draw_msg(x_sys,  x_bar,  65.5, "30: Dispatch order ticket to KDS queue")
    draw_msg(x_sys,  x_ui,   62.2, "31: Generate formatted thermal receipt", is_return=True)
    draw_msg(x_cash, x_cust, 59.0, "32: Issue printed thermal receipt to customer", is_return=True)

    # =========================================================================
    # BLOCK 5: LOOP FRAGMENT - KDS PREPARATION & BREW-TO-SERVE DEDUCTION (y: 54 down to 25)
    # =========================================================================
    draw_fragment(2.5, 25.0, 115.0, 29.0, "loop", "[Each KDS Order Preparation Stage: Pending -> Brewing -> Ready/Served]")

    draw_act(x_bar,  50.0, 26.5)
    draw_act(x_sys,  47.5, 28.0)
    draw_act(x_db,   44.5, 34.0)
    draw_act(x_cust, 31.0, 26.5)

    draw_msg(x_bar,  x_sys,  48.5, "33: View KDS recipe & update status to Brewing / Ready")
    draw_msg(x_sys,  x_db,   43.5, "34: Update order status in database")
    draw_self_msg(x_sys,     41.5, 38.2, "35: Trigger Brew-to-Serve BOM\n& cup/packaging calculation")
    draw_msg(x_sys,  x_db,   36.2, "36: Deduct raw ingredients, cups & log inventory ledger")
    draw_msg(x_db,   x_sys,  34.2, "37: Confirm perpetual stock updated", is_return=True)
    draw_msg(x_bar,  x_cust, 28.5, "38: Notify customer & serve prepared beverage/food", is_return=True)

    # =========================================================================
    # BLOCK 6: ADMINISTRATOR INVENTORY MONITORING & SALES REPORTING (y: 22 down to 6)
    # =========================================================================
    draw_act(x_ui,  21.5, 6.5)
    draw_act(x_sys, 20.0, 7.5)
    draw_act(x_db,  18.0, 11.5)

    draw_msg(x_ui,  x_sys, 19.5, "39: Request inventory stock levels & daily sales report (Admin)")
    draw_msg(x_sys, x_db,  16.8, "40: Query inventory ledger, stock balances & sales records")
    draw_msg(x_db,  x_sys, 13.0, "41: Return aggregated inventory & sales dataset", is_return=True)
    draw_msg(x_sys, x_ui,  9.0,  "42: Render inventory monitoring & sales analytics dashboard", is_return=True)

    plt.tight_layout()
    os.makedirs(os.path.dirname(output_png), exist_ok=True)
    plt.savefig(output_png, dpi=300, bbox_inches='tight')
    plt.close()
    print(f"Generated Sequence Diagram matching teacher's reference figure: {output_png}")

if __name__ == "__main__":
    draw_sequence_diagram("Documentation/figures/figure7_sequence.png")
