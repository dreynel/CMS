# -*- coding: utf-8 -*-
"""
Generate Separate Sequence Diagrams Per User Role
Modeled directly after the teacher's reference example (image10.png):
- Customer Sequence Diagram
- Cashier Sequence Diagram
- Barista Sequence Diagram
- Administrator Sequence Diagram
Discrete activation bars, numbered request/return messages, combined fragments (alt/loop).
Strictly Black and White, 300 DPI Vector Style, Simplified wordings.
"""
import os
import matplotlib
matplotlib.use('Agg')
import matplotlib.pyplot as plt
import matplotlib.patches as patches

def draw_single_sequence(output_png, role_title, participants, messages, fragments=None):
    fig, ax = plt.subplots(figsize=(8.5, 9.5), dpi=300)
    ax.set_xlim(0, 100)
    ax.set_ylim(0, 115)
    ax.axis('off')

    font_family = 'Times New Roman'
    fig.patch.set_facecolor('#FFFFFF')

    # Top Title Banner
    banner = patches.FancyBboxPatch(
        (2.5, 106.5), 95.0, 5.8, boxstyle="round,pad=0.2,rounding_size=0.5",
        facecolor="#F0F0F0", edgecolor="#000000", lw=1.4
    )
    ax.add_patch(banner)
    ax.text(50.0, 109.4, f"SEQUENCE DIAGRAM: {role_title.upper()}",
            ha='center', va='center', fontfamily=font_family, fontsize=11.0, fontweight='bold', color="#000000")

    # Participants
    num_p = len(participants)
    spacing = 88.0 / (num_p - 1) if num_p > 1 else 0
    p_coords = {}

    top_y = 100.0
    bot_y = 4.0
    pw = 18.0 if num_p <= 4 else 15.0
    ph = 5.6

    for i, (p_name, p_role) in enumerate(participants):
        px = 6.0 + i * spacing
        p_coords[p_name] = px

        box = patches.FancyBboxPatch(
            (px - pw/2, top_y - ph/2), pw, ph,
            boxstyle="round,pad=0.1,rounding_size=0.8",
            facecolor="#FFFFFF", edgecolor="#000000", lw=1.3
        )
        ax.add_patch(box)
        ax.text(px, top_y, p_name, ha='center', va='center',
                fontfamily=font_family, fontsize=7.8, fontweight='bold', color="#000000", linespacing=1.05)

        # Dashed lifeline
        ax.plot([px, px], [bot_y, top_y - ph/2], color="#000000", lw=0.9, linestyle="--", zorder=1)

    aw = 1.6
    half_aw = aw / 2.0

    def draw_act(px, y_top, y_bot):
        rect = patches.Rectangle(
            (px - half_aw, y_bot), aw, y_top - y_bot,
            facecolor="#FFFFFF", edgecolor="#000000", lw=1.0, zorder=3
        )
        ax.add_patch(rect)

    def draw_msg(from_p, to_p, y, label, is_return=False):
        x1 = p_coords[from_p]
        x2 = p_coords[to_p]
        sx = x1 + half_aw if x2 > x1 else x1 - half_aw
        ex = x2 - half_aw if x2 > x1 else x2 + half_aw

        if is_return:
            ax.annotate('', xy=(ex, y), xytext=(sx, y),
                        arrowprops=dict(arrowstyle="->", color="#000000", lw=1.0, linestyle="--", mutation_scale=9),
                        zorder=4)
        else:
            ax.annotate('', xy=(ex, y), xytext=(sx, y),
                        arrowprops=dict(arrowstyle="-|>", color="#000000", lw=1.1, linestyle="-", mutation_scale=9),
                        zorder=4)

        mid_x = (sx + ex) / 2.0
        ax.text(mid_x, y + 0.6, label, ha='center', va='bottom',
                fontfamily=font_family, fontsize=6.8, color="#000000",
                bbox=dict(boxstyle="square,pad=0.08", facecolor="#FFFFFF", edgecolor="none"),
                zorder=5)

    def draw_self_msg(p_name, y_top, y_bot, label):
        px = p_coords[p_name]
        sx = px + half_aw
        loop_w = 4.2
        ax.plot([sx, sx + loop_w, sx + loop_w, sx], [y_top, y_top, y_bot, y_bot], color="#000000", lw=1.0, zorder=4)
        ax.annotate('', xy=(sx, y_bot), xytext=(sx + 0.8, y_bot),
                    arrowprops=dict(arrowstyle="-|>", color="#000000", lw=1.0, mutation_scale=8), zorder=4)
        ax.text(sx + loop_w + 0.6, (y_top + y_bot)/2.0, label, ha='left', va='center',
                fontfamily=font_family, fontsize=6.5, color="#000000", linespacing=1.05,
                bbox=dict(boxstyle="square,pad=0.08", facecolor="#FFFFFF", edgecolor="none"), zorder=5)

    def draw_fragment(x, y_bot, w, h, tag, guard):
        rect = patches.Rectangle((x, y_bot), w, h, facecolor="none", edgecolor="#000000", lw=1.1, zorder=2)
        ax.add_patch(rect)
        y_top = y_bot + h
        tw = 6.0
        th = 2.4
        pent = patches.Polygon([
            [x, y_top],
            [x + tw, y_top],
            [x + tw, y_top - th + 0.6],
            [x + tw - 1.0, y_top - th],
            [x, y_top - th]
        ], closed=True, facecolor="#FFFFFF", edgecolor="#000000", lw=1.0, zorder=4)
        ax.add_patch(pent)
        ax.text(x + tw/2 - 0.2, y_top - th/2, tag, ha='center', va='center',
                fontfamily=font_family, fontsize=6.8, fontweight='bold', color="#000000", zorder=5)
        ax.text(x + tw + 1.2, y_top - th/2, guard, ha='left', va='center',
                fontfamily=font_family, fontsize=6.8, fontweight='bold', color="#000000",
                bbox=dict(boxstyle="square,pad=0.08", facecolor="#FFFFFF", edgecolor="none"), zorder=5)

    # Draw fragments if any
    if fragments:
        for fx, fy, fw, fh, ftag, fguard in fragments:
            draw_fragment(fx, fy, fw, fh, ftag, fguard)

    # Draw messages and activations
    for m in messages:
        mtype = m[0]
        if mtype == "act":
            draw_act(p_coords[m[1]], m[2], m[3])
        elif mtype == "msg":
            draw_msg(m[1], m[2], m[3], m[4], is_return=m[5] if len(m) > 5 else False)
        elif mtype == "self":
            draw_self_msg(m[1], m[2], m[3], m[4])

    plt.tight_layout()
    os.makedirs(os.path.dirname(output_png), exist_ok=True)
    plt.savefig(output_png, dpi=300, bbox_inches='tight')
    plt.close()
    print(f"Generated {role_title} Sequence Diagram: {output_png}")

def generate_all_sequences():
    # 1. CUSTOMER SEQUENCE (NO LOGIN REQUIRED - DIRECT BOOKING & POS PASS)
    c_parts = [
        ("Customer", "actor"),
        ("Web Portal", "boundary"),
        ("PHP Server", "control"),
        ("Database", "database")
    ]
    c_msgs = [
        ("act", "Customer", 94, 8),
        ("act", "Web Portal", 94, 12),
        ("act", "PHP Server", 90, 16),
        ("act", "Database", 86, 26),

        ("msg", "Customer", "Web Portal", 93, "1: Access Online Reservation Portal"),
        ("msg", "Web Portal", "PHP Server", 88, "2: Request available tables & active menu"),
        ("msg", "PHP Server", "Database", 83, "3: Query table availability & menu items"),
        ("msg", "Database", "PHP Server", 78, "4: Return available tables & menu data", True),
        ("msg", "PHP Server", "Web Portal", 73, "5: Render seating matrix & menu catalog", True),

        ("msg", "Customer", "Web Portal", 66, "6: Select dining table/seat, slot & pre-orders"),
        ("msg", "Customer", "Web Portal", 60, "7: Review & agree to 15-min seating policy"),
        ("msg", "Web Portal", "PHP Server", 54, "8: Submit reservation & guest details (name, phone)"),
        ("msg", "PHP Server", "Database", 48, "9: Save reservation (status: Confirmed) & lock table"),
        ("msg", "Database", "PHP Server", 42, "10: Return booking confirmation & reservation ID", True),

        ("self", "PHP Server", 37, 31, "11: Generate Dynamic QR Pass token\nwith 15m holding schedule"),
        ("msg", "PHP Server", "Web Portal", 26, "12: Send Dynamic QR Pass token", True),
        ("msg", "Web Portal", "Customer", 20, "13: Display scannable QR Pass & assigned table", True),

        ("msg", "Customer", "Web Portal", 12, "14: Present QR Pass to Staff (Seating) & POS Cashier (Payment)"),
    ]
    draw_single_sequence("Documentation/figures/figure7a_sequence_customer.png",
                         "Customer Transactions", c_parts, c_msgs)

    # 2. CASHIER SEQUENCE
    ca_parts = [
        ("Cashier", "actor"),
        ("POS Terminal", "boundary"),
        ("PHP Server", "control"),
        ("Database", "database"),
        ("Customer", "actor")
    ]
    ca_msgs = [
        ("act", "Cashier", 93, 10),
        ("act", "POS Terminal", 93, 12),
        ("act", "PHP Server", 89, 14),
        ("act", "Database", 86, 18),
        ("act", "Customer", 75, 10),

        ("msg", "Cashier", "POS Terminal", 92, "1: Login to POS terminal"),
        ("msg", "POS Terminal", "PHP Server", 88, "2: Verify cashier credentials"),
        ("msg", "PHP Server", "Database", 85, "3: Validate cashier role"),
        ("msg", "Database", "PHP Server", 81, "4: Return cashier session", True),
        ("msg", "PHP Server", "POS Terminal", 77, "5: Open POS Counter Workspace", True),

        ("msg", "Cashier", "POS Terminal", 70, "6: Scan product QR code / booking QR pass"),
        ("msg", "POS Terminal", "PHP Server", 65, "7: Query product details / booking items"),
        ("msg", "PHP Server", "Database", 60, "8: Query product price & cup stock"),
        ("msg", "Database", "PHP Server", 55, "9: Return product details & packaging", True),
        ("self", "PHP Server", 51, 46, "10: Compute discounts (10%/20%)\n& 12% VAT net total"),
        ("msg", "PHP Server", "POS Terminal", 42, "11: Return sales cart total", True),

        ("msg", "Customer", "Cashier", 36, "12: Tender Cash or present GCash QR reference"),
        ("msg", "Cashier", "POS Terminal", 30, "13: Enter payment & click Complete Sale"),
        ("msg", "POS Terminal", "PHP Server", 25, "14: Commit order & payment transaction"),
        ("msg", "PHP Server", "Database", 20, "15: Save order & payment records"),
        ("msg", "Database", "PHP Server", 16, "16: Confirm transaction committed", True),
        ("msg", "PHP Server", "POS Terminal", 13, "17: Generate thermal receipt", True),
        ("msg", "POS Terminal", "Customer", 10, "18: Issue printed thermal receipt", True),
    ]
    draw_single_sequence("Documentation/figures/figure7b_sequence_cashier.png",
                         "Cashier POS & Sales Processing", ca_parts, ca_msgs)

    # 3. BARISTA SEQUENCE
    b_parts = [
        ("Barista", "actor"),
        ("KDS Screen", "boundary"),
        ("PHP Server", "control"),
        ("Database", "database"),
        ("Customer", "actor")
    ]
    b_msgs = [
        ("act", "Barista", 93, 10),
        ("act", "KDS Screen", 93, 12),
        ("act", "PHP Server", 89, 14),
        ("act", "Database", 86, 18),
        ("act", "Customer", 30, 8),

        ("msg", "Barista", "KDS Screen", 92, "1: Open Kitchen Display System (KDS)"),
        ("msg", "KDS Screen", "PHP Server", 88, "2: Polling active order tickets"),
        ("msg", "PHP Server", "Database", 84, "3: SELECT pending & brewing orders"),
        ("msg", "Database", "PHP Server", 80, "4: Return incoming orders & recipes", True),
        ("msg", "PHP Server", "KDS Screen", 76, "5: Display order tickets with audio chime", True),

        ("msg", "Barista", "KDS Screen", 68, "6: Inspect recipe modifiers & tap 'Brewing'"),
        ("msg", "KDS Screen", "PHP Server", 63, "7: Update ticket status to Brewing"),
        ("msg", "PHP Server", "Database", 58, "8: UPDATE orders status = 'Brewing'"),
        ("msg", "Database", "PHP Server", 54, "9: Confirm status updated", True),

        ("msg", "Barista", "KDS Screen", 46, "10: Finish preparation & tap 'Brew & Serve'"),
        ("msg", "KDS Screen", "PHP Server", 41, "11: Advance status to Ready / Served"),
        ("self", "PHP Server", 37, 31, "12: Execute Brew-to-Serve BOM\n& cup/packaging deduction"),
        ("msg", "PHP Server", "Database", 26, "13: Deduct ingredient stock & log ledger"),
        ("msg", "Database", "PHP Server", 21, "14: Confirm inventory deducted", True),
        ("msg", "PHP Server", "KDS Screen", 16, "15: Remove completed ticket from active queue", True),
        ("msg", "Barista", "Customer", 11, "16: Call order & serve beverage at pickup counter", True),
    ]
    draw_single_sequence("Documentation/figures/figure7c_sequence_barista.png",
                         "Barista KDS & Kitchen Fulfillment", b_parts, b_msgs)

    # 4. ADMINISTRATOR SEQUENCE
    ad_parts = [
        ("Administrator", "actor"),
        ("Admin Console", "boundary"),
        ("PHP Server", "control"),
        ("Database", "database")
    ]
    ad_msgs = [
        ("act", "Administrator", 93, 10),
        ("act", "Admin Console", 93, 12),
        ("act", "PHP Server", 90, 14),
        ("act", "Database", 88, 16),

        ("msg", "Administrator", "Admin Console", 92, "1: Login to Admin Console"),
        ("msg", "Admin Console", "PHP Server", 88, "2: Verify administrator credentials"),
        ("msg", "PHP Server", "Database", 85, "3: Validate admin privileges"),
        ("msg", "Database", "PHP Server", 81, "4: Return admin session", True),
        ("msg", "PHP Server", "Admin Console", 77, "5: Render Administrative Dashboard", True),

        ("msg", "Administrator", "Admin Console", 70, "6: Add/update product, recipe (BOM) & price"),
        ("msg", "Admin Console", "PHP Server", 65, "7: Submit product & recipe data"),
        ("msg", "PHP Server", "Database", 60, "8: Save product & recipe records"),
        ("msg", "Database", "PHP Server", 56, "9: Confirm product record saved", True),
        ("self", "PHP Server", 52, 47, "10: Generate printable 2D\nQR code menu tag"),
        ("msg", "PHP Server", "Admin Console", 43, "11: Display printable QR tag", True),

        ("msg", "Administrator", "Admin Console", 36, "12: Request finished yield & stock ledger"),
        ("msg", "Admin Console", "PHP Server", 31, "13: Query recipe yield & item conversions"),
        ("msg", "PHP Server", "Database", 26, "14: SELECT recipe BOM & raw stock balances"),
        ("msg", "Database", "PHP Server", 21, "15: Return yield analysis & stock dataset", True),

        ("msg", "Administrator", "Admin Console", 15, "16: Generate daily sales revenue report"),
        ("msg", "Admin Console", "PHP Server", 11, "17: Compute daily VAT & net totals"),
        ("msg", "PHP Server", "Admin Console", 8, "18: Display sales analytics report", True),
    ]
    draw_single_sequence("Documentation/figures/figure7d_sequence_admin.png",
                         "Administrator Management & Auditing", ad_parts, ad_msgs)

if __name__ == "__main__":
    generate_all_sequences()

