# -*- coding: utf-8 -*-
"""
Generate Strictly Black and White 2D Vector Class Diagram / ERD for ISAT U Coffee Shop POS System
- Dynamically calculates exact box heights with generous bottom padding so ZERO text lines touch or overflow borders
- Zero lines cutting through table boxes; zero cardinality labels overlapping borders
- High-contrast 300 DPI Times New Roman typography
"""
import os
import matplotlib
matplotlib.use('Agg')
import matplotlib.pyplot as plt
import matplotlib.patches as patches

def draw_class_erd_diagram(output_png):
    fig, ax = plt.subplots(figsize=(8.5, 10.5), dpi=300)
    ax.set_xlim(0, 110)
    ax.set_ylim(0, 142)
    ax.axis('off')

    font_family = 'Times New Roman'
    fig.patch.set_facecolor('#FFFFFF')

    # Top Title Banner
    banner = patches.FancyBboxPatch(
        (2.5, 134.0), 105.0, 6.0, boxstyle="round,pad=0.2,rounding_size=0.5",
        facecolor="#F0F0F0", edgecolor="#000000", lw=1.5
    )
    ax.add_patch(banner)
    ax.text(55.0, 137.0, "CLASS DIAGRAM & RELATIONAL SCHEMA (CENTRALIZED DATABASE TABLES)",
            ha='center', va='center', fontfamily=font_family, fontsize=10.5, fontweight='bold', color="#000000")

    def draw_table(x, y_top, w, table_name, pk_list, fields_list):
        hdr_h = 3.8
        pk_h = len(pk_list) * 2.4 + 1.0
        fld_h = len(fields_list) * 2.15 + 1.8
        total_h = hdr_h + pk_h + fld_h
        y_bot = y_top - total_h

        # Outer box
        box = patches.Rectangle((x, y_bot), w, total_h, facecolor="#FFFFFF", edgecolor="#000000", lw=1.3)
        ax.add_patch(box)

        # Header box
        hdr = patches.Rectangle((x, y_top - hdr_h), w, hdr_h, facecolor="#E8E8E8", edgecolor="#000000", lw=1.1)
        ax.add_patch(hdr)
        ax.text(x + w/2, y_top - hdr_h/2, table_name, ha='center', va='center',
                fontfamily=font_family, fontsize=8.4, fontweight='bold', color="#000000")

        # PK fields
        curr_y = y_top - hdr_h - 0.8
        for pk in pk_list:
            ax.text(x + 1.2, curr_y, pk, ha='left', va='top',
                    fontfamily=font_family, fontsize=7.2, fontweight='bold', color="#000000")
            curr_y -= 2.4

        # Divider line
        div_y = y_top - hdr_h - pk_h
        ax.plot([x, x + w], [div_y, div_y], color="#000000", lw=0.9)

        # Regular fields
        curr_y = div_y - 0.9
        for f in fields_list:
            ax.text(x + 1.2, curr_y, f, ha='left', va='top',
                    fontfamily=font_family, fontsize=6.9, color="#000000")
            curr_y -= 2.15

        return y_top, y_bot

    # 3 Columns across x (0..110):
    # Col 1: x = 3.0 to 34.0 (w = 31.0)
    # Col 2: x = 39.5 to 70.5 (w = 31.0)
    # Col 3: x = 76.0 to 107.0 (w = 31.0)
    c1_x, c2_x, c3_x = 3.0, 39.5, 76.0
    tw = 31.0

    # =========================================================================
    # COLUMN 1: CATALOG, RECIPES & INVENTORY SUBSYSTEM
    # =========================================================================
    t_cat_top, t_cat_bot = draw_table(
        c1_x, 130.5, tw, "categories",
        ["+ id : int [PK]"],
        ["name : varchar(100)", "slug : varchar(100)", "icon : varchar(50)", "sort_order : int"]
    )

    t_prod_top, t_prod_bot = draw_table(
        c1_x, 108.5, tw, "products",
        ["+ id : int [PK]"],
        ["category_id : int [FK]", "product_code : varchar [QR Code]", "name : varchar(150)",
         "price : decimal(10,2)", "cost_price : decimal(10,2)", "unit : varchar(30)", "is_available : tinyint(1)"]
    )

    t_inv_top, t_inv_bot = draw_table(
        c1_x, 78.5, tw, "inventory_items & recipes",
        ["+ id : int [PK]"],
        ["item_code : varchar(40) [UK]", "item_type : enum(raw/finished)", "product_id : int [FK]",
         "current_stock : decimal(14,4)", "qty_per_unit : decimal(14,4)"]
    )

    t_add_top, t_add_bot = draw_table(
        c1_x, 51.5, tw, "product_addons & sizes",
        ["+ id : int [PK]"],
        ["product_id : int [FK]", "ingredient_item_id : int [FK]", "addon_name : varchar(100)",
         "size_name : varchar(50)", "price : decimal(10,2)"]
    )

    # =========================================================================
    # COLUMN 2: TABLES, RESERVATIONS & INVENTORY LEDGER SUBSYSTEM
    # =========================================================================
    t_tbl_top, t_tbl_bot = draw_table(
        c2_x, 130.5, tw, "dining_tables",
        ["+ id : int [PK]"],
        ["table_code : varchar(20)", "name : varchar(100)", "zone : enum(4 zones)",
         "capacity : int", "status : enum(avail/occ)"]
    )

    t_res_top, t_res_bot = draw_table(
        c2_x, 106.5, tw, "reservations",
        ["+ id : int [PK]"],
        ["reservation_number : varchar(30)", "qr_token : varchar(64) [UK]", "table_id : int [FK]",
         "reservation_date : date", "time_slot : varchar(50)", "scheduled_order_id : int [FK]",
         "status : enum(Confirmed/CheckedIn)"]
    )

    t_rit_top, t_rit_bot = draw_table(
        c2_x, 76.5, tw, "reservation_items",
        ["+ id : int [PK]"],
        ["reservation_id : int [FK]", "product_id : int [FK]", "size_name : varchar(50)",
         "quantity : int", "subtotal : decimal(10,2)"]
    )

    t_led_top, t_led_bot = draw_table(
        c2_x, 51.5, tw, "inventory_ledger & txns",
        ["+ id : bigint [PK]"],
        ["item_id : int [FK]", "txn_type : enum(IN/OUT/SALE)", "qty_in / qty_out : decimal",
         "running_balance : decimal", "reference_no : varchar(60)"]
    )

    # =========================================================================
    # COLUMN 3: USERS, POS ORDERS & PAYMENTS SUBSYSTEM
    # =========================================================================
    t_usr_top, t_usr_bot = draw_table(
        c3_x, 130.5, tw, "users",
        ["+ id : int [PK]"],
        ["name : varchar(100)", "email : varchar(100) [UK]", "password : varchar(255)",
         "role : enum(admin/cashier/barista/cust)", "affiliation : enum(student/faculty)", "id_number : varchar(50)"]
    )

    t_ord_top, t_ord_bot = draw_table(
        c3_x, 103.5, tw, "orders",
        ["+ id : int [PK]"],
        ["order_number : varchar(50) [UK]", "reservation_id : int [FK]", "cashier_id : int [FK]",
         "order_type : enum(dine_in/takeout)", "subtotal : decimal(10,2)", "discount_amount : decimal(10,2)",
         "vat_amount : decimal(10,2)", "net_total : decimal(10,2)", "status : enum(Pending/Brewing/Ready)"]
    )

    t_oit_top, t_oit_bot = draw_table(
        c3_x, 71.5, tw, "order_items",
        ["+ id : int [PK]"],
        ["order_id : int [FK]", "product_id : int [FK]", "size_name : varchar(50)",
         "quantity : int", "unit_price : decimal(10,2)", "subtotal : decimal(10,2)"]
    )

    t_pay_top, t_pay_bot = draw_table(
        c3_x, 45.5, tw, "payments",
        ["+ id : int [PK]"],
        ["order_id : int [FK]", "amount_paid : decimal(10,2)", "change_amount : decimal(10,2)",
         "payment_method : enum(cash/gcash)", "reference_no : varchar(100)"]
    )

    # =========================================================================
    # VALIDATED ORTHOGONAL RELATIONSHIP LINES (ZERO COLLISIONS)
    # =========================================================================
    c1_mid = c1_x + tw/2
    c2_mid = c2_x + tw/2
    c3_mid = c3_x + tw/2

    # 1. categories -> products
    ax.plot([c1_mid, c1_mid], [t_cat_bot, t_prod_top], color="#000000", lw=1.3)
    ax.text(c1_mid - 1.5, (t_cat_bot + t_prod_top)/2, "1", ha='right', va='center', fontfamily=font_family, fontsize=7.5, fontweight='bold')
    ax.text(c1_mid + 1.5, (t_cat_bot + t_prod_top)/2, "*", ha='left', va='center', fontfamily=font_family, fontsize=7.5, fontweight='bold')

    # 2. products -> inventory_items & recipes
    ax.plot([c1_mid, c1_mid], [t_prod_bot, t_inv_top], color="#000000", lw=1.3)
    ax.text(c1_mid - 1.5, (t_prod_bot + t_inv_top)/2, "1", ha='right', va='center', fontfamily=font_family, fontsize=7.5, fontweight='bold')
    ax.text(c1_mid + 1.5, (t_prod_bot + t_inv_top)/2, "*", ha='left', va='center', fontfamily=font_family, fontsize=7.5, fontweight='bold')

    # 3. inventory_items -> product_addons & sizes
    ax.plot([c1_mid, c1_mid], [t_inv_bot, t_add_top], color="#000000", lw=1.3)
    ax.text(c1_mid - 1.5, (t_inv_bot + t_add_top)/2, "1", ha='right', va='center', fontfamily=font_family, fontsize=7.5, fontweight='bold')
    ax.text(c1_mid + 1.5, (t_inv_bot + t_add_top)/2, "*", ha='left', va='center', fontfamily=font_family, fontsize=7.5, fontweight='bold')

    # 4. dining_tables -> reservations
    ax.plot([c2_mid, c2_mid], [t_tbl_bot, t_res_top], color="#000000", lw=1.3)
    ax.text(c2_mid - 1.5, (t_tbl_bot + t_res_top)/2, "1", ha='right', va='center', fontfamily=font_family, fontsize=7.5, fontweight='bold')
    ax.text(c2_mid + 1.5, (t_tbl_bot + t_res_top)/2, "*", ha='left', va='center', fontfamily=font_family, fontsize=7.5, fontweight='bold')

    # 5. reservations -> reservation_items
    ax.plot([c2_mid, c2_mid], [t_res_bot, t_rit_top], color="#000000", lw=1.3)
    ax.text(c2_mid - 1.5, (t_res_bot + t_rit_top)/2, "1", ha='right', va='center', fontfamily=font_family, fontsize=7.5, fontweight='bold')
    ax.text(c2_mid + 1.5, (t_res_bot + t_rit_top)/2, "*", ha='left', va='center', fontfamily=font_family, fontsize=7.5, fontweight='bold')

    # 6. inventory_items -> inventory_ledger (in Col 1-Col 2 channel x=36.0)
    ax.plot([c1_x + tw, 36.2, 36.2, c2_x], [68.0, 68.0, 42.0, 42.0], color="#000000", lw=1.2)
    ax.text(c1_x + tw + 0.6, 69.0, "1", ha='left', va='bottom', fontfamily=font_family, fontsize=7.2, fontweight='bold')
    ax.text(c2_x - 0.6, 43.0, "*", ha='right', va='bottom', fontfamily=font_family, fontsize=7.2, fontweight='bold')

    # 7. products -> reservation_items (in Col 1-Col 2 channel x=37.5)
    ax.plot([c1_x + tw, 37.6, 37.6, c2_x], [95.0, 95.0, 68.0, 68.0], color="#000000", lw=1.2)
    ax.text(c1_x + tw + 0.6, 96.0, "1", ha='left', va='bottom', fontfamily=font_family, fontsize=7.2, fontweight='bold')
    ax.text(c2_x - 0.6, 69.0, "*", ha='right', va='bottom', fontfamily=font_family, fontsize=7.2, fontweight='bold')

    # 8. reservations -> orders (Horizontal across Col 2-Col 3 gap: x=70.5 to 76.0)
    ax.plot([c2_x + tw, c3_x], [92.0, 92.0], color="#000000", lw=1.3)
    ax.text(c2_x + tw + 0.5, 93.0, "0..1", ha='left', va='bottom', fontfamily=font_family, fontsize=7.2, fontweight='bold')
    ax.text(c3_x - 0.5, 93.0, "1", ha='right', va='bottom', fontfamily=font_family, fontsize=7.2, fontweight='bold')

    # 9. users -> orders
    ax.plot([c3_mid, c3_mid], [t_usr_bot, t_ord_top], color="#000000", lw=1.3)
    ax.text(c3_mid - 1.5, (t_usr_bot + t_ord_top)/2, "1", ha='right', va='center', fontfamily=font_family, fontsize=7.5, fontweight='bold')
    ax.text(c3_mid + 1.5, (t_usr_bot + t_ord_top)/2, "*", ha='left', va='center', fontfamily=font_family, fontsize=7.5, fontweight='bold')

    # 10. orders -> order_items
    ax.plot([c3_mid, c3_mid], [t_ord_bot, t_oit_top], color="#000000", lw=1.3)
    ax.text(c3_mid - 1.5, (t_ord_bot + t_oit_top)/2, "1", ha='right', va='center', fontfamily=font_family, fontsize=7.5, fontweight='bold')
    ax.text(c3_mid + 1.5, (t_ord_bot + t_oit_top)/2, "*", ha='left', va='center', fontfamily=font_family, fontsize=7.5, fontweight='bold')

    # 11. orders -> payments (routed along right side of Col 3 or between order_items & payments)
    ax.plot([c3_mid, c3_mid], [t_oit_bot, t_pay_top], color="#000000", lw=1.3)
    ax.text(c3_mid - 1.5, (t_oit_bot + t_pay_top)/2, "1", ha='right', va='center', fontfamily=font_family, fontsize=7.5, fontweight='bold')
    ax.text(c3_mid + 1.5, (t_oit_bot + t_pay_top)/2, "1", ha='left', va='center', fontfamily=font_family, fontsize=7.5, fontweight='bold')

    # 12. products -> order_items (Clean orthogonal route through empty channel without crossing any table!)
    # From products right edge (34.0, 90.0) -> down channel x=35.0 to y=17.0 -> across to x=73.5 -> up to y=60.0 -> right into order_items left edge (76.0, 60.0)!
    ax.plot([c1_x + tw, 35.0, 35.0, 73.5, 73.5, c3_x], [90.0, 90.0, 17.0, 17.0, 60.0, 60.0],
            color="#000000", lw=1.2, linestyle=":")
    ax.text(35.0, 91.0, "1", ha='center', va='bottom', fontfamily=font_family, fontsize=7.2, fontweight='bold')
    ax.text(c3_x - 0.6, 61.0, "*", ha='right', va='bottom', fontfamily=font_family, fontsize=7.2, fontweight='bold')
    ax.text(54.25, 18.2, "Product Foreign Key Reference in order_items",
            ha='center', va='bottom', fontfamily=font_family, fontsize=7.5, fontweight='bold', color="#000000",
            bbox=dict(boxstyle="round,pad=0.2", facecolor="#FFFFFF", edgecolor="none"))

    # Bottom Legend
    ax.text(55.0, 8.5,
            "Relational Schema Notation:   [PK] = Primary Key     [FK] = Foreign Key     [UK] = Unique Key     1 / * = Cardinality",
            ha='center', va='center', fontfamily=font_family, fontsize=8.0, fontweight='bold', color="#000000",
            bbox=dict(boxstyle="round,pad=0.35", facecolor="#F4F4F4", edgecolor="#000000", lw=1.0))

    plt.tight_layout()
    os.makedirs(os.path.dirname(output_png), exist_ok=True)
    plt.savefig(output_png, dpi=300, bbox_inches='tight')
    plt.close()
    print(f"Generated Strictly B&W Class Diagram / ERD (Zero Overflows): {output_png}")

if __name__ == "__main__":
    draw_class_erd_diagram("Documentation/figures/figure6_class_erd.png")
