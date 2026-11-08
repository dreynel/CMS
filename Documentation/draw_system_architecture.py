# -*- coding: utf-8 -*-
"""
Generate Minimalist System Architecture Diagram for ISAT U Coffee Shop POS System
- Minimized text per user instruction:
  "also for system architecture minimize text, only actual images and lines, etc... just purely name of the object"
  "simplify wordings, dont specify like mysql8,0"
- Purely the names of the objects, 2D vector technical drawings, and clean connecting lines.
- Strictly Black and White, 300 DPI Vector Style
"""
import os
import matplotlib
matplotlib.use('Agg')
import matplotlib.pyplot as plt
import matplotlib.patches as patches

def draw_system_architecture(output_png, output_svg=None):
    fig, ax = plt.subplots(figsize=(11.0, 7.2), dpi=300)
    ax.set_xlim(0, 110)
    ax.set_ylim(0, 72)
    ax.axis('off')

    font_family = 'Times New Roman'
    fig.patch.set_facecolor('#FFFFFF')

    # Top Title Banner
    banner = patches.FancyBboxPatch(
        (3.0, 64.5), 104.0, 5.5, boxstyle="round,pad=0.2,rounding_size=0.6",
        facecolor="#F0F0F0", edgecolor="#000000", lw=1.5
    )
    ax.add_patch(banner)
    ax.text(55.0, 67.25, "SYSTEM ARCHITECTURE DIAGRAM",
            ha='center', va='center', fontfamily=font_family, fontsize=12.0, fontweight='bold', color="#000000")

    # 3 Main Tier Panels
    # Client Tier (Left: x=3 to 38, w=35)
    ax.add_patch(patches.FancyBboxPatch(
        (3.0, 4.0), 35.0, 58.5, boxstyle="round,pad=0.0,rounding_size=0.6",
        facecolor="#FFFFFF", edgecolor="#000000", lw=1.5, linestyle="--"
    ))
    ax.text(20.5, 59.5, "Client Tier",
            ha='center', va='center', fontfamily=font_family, fontsize=11.0, fontweight='bold', color="#000000")

    # Application Tier (Middle: x=43 to 75, w=32)
    ax.add_patch(patches.FancyBboxPatch(
        (43.0, 4.0), 32.0, 58.5, boxstyle="round,pad=0.0,rounding_size=0.6",
        facecolor="#FFFFFF", edgecolor="#000000", lw=1.5, linestyle="--"
    ))
    ax.text(59.0, 59.5, "Application Tier",
            ha='center', va='center', fontfamily=font_family, fontsize=11.0, fontweight='bold', color="#000000")

    # Data Tier (Right: x=80 to 107, w=27)
    ax.add_patch(patches.FancyBboxPatch(
        (80.0, 4.0), 27.0, 58.5, boxstyle="round,pad=0.0,rounding_size=0.6",
        facecolor="#FFFFFF", edgecolor="#000000", lw=1.5, linestyle="--"
    ))
    ax.text(93.5, 59.5, "Data Tier",
            ha='center', va='center', fontfamily=font_family, fontsize=11.0, fontweight='bold', color="#000000")

    # =========================================================================
    # 2D VECTOR DRAWINGS
    # =========================================================================
    def draw_smartphone(x, y):
        ax.add_patch(patches.FancyBboxPatch((x-1.5, y-2.4), 3.0, 4.8, boxstyle="round,pad=0.05,rounding_size=0.35",
                                            facecolor="#FFFFFF", edgecolor="#000000", lw=1.3))
        ax.add_patch(patches.Rectangle((x-1.2, y-1.9), 2.4, 3.8, facecolor="#F8F8F8", edgecolor="#000000", lw=0.7))
        # Mini QR code inside
        ax.add_patch(patches.Rectangle((x-0.7, y-0.7), 1.4, 1.4, facecolor="#FFFFFF", edgecolor="#000000", lw=0.7))
        ax.add_patch(patches.Rectangle((x-0.5, y+0.2), 0.35, 0.35, facecolor="#000000"))
        ax.add_patch(patches.Rectangle((x+0.15, y+0.2), 0.35, 0.35, facecolor="#000000"))
        ax.add_patch(patches.Rectangle((x-0.5, y-0.5), 0.35, 0.35, facecolor="#000000"))

    def draw_laptop(x, y):
        ax.add_patch(patches.FancyBboxPatch((x-2.4, y-0.3), 4.8, 3.2, boxstyle="round,pad=0.05,rounding_size=0.2",
                                            facecolor="#FFFFFF", edgecolor="#000000", lw=1.3))
        ax.add_patch(patches.Rectangle((x-2.1, y), 4.2, 2.6, facecolor="#F8F8F8", edgecolor="#000000", lw=0.7))
        ax.add_patch(patches.Polygon([[x-3.0, y-1.0], [x+3.0, y-1.0], [x+2.4, y-0.3], [x-2.4, y-0.3]],
                                     closed=True, facecolor="#E0E0E0", edgecolor="#000000", lw=1.0))

    def draw_pos_station(x, y):
        # Monitor
        ax.add_patch(patches.FancyBboxPatch((x-2.5, y-0.6), 5.0, 3.4, boxstyle="round,pad=0.05,rounding_size=0.2",
                                            facecolor="#FFFFFF", edgecolor="#000000", lw=1.3))
        ax.add_patch(patches.Rectangle((x-2.2, y-0.3), 4.4, 2.8, facecolor="#F8F8F8", edgecolor="#000000", lw=0.7))
        ax.add_patch(patches.Rectangle((x-0.6, y-1.6), 1.2, 1.0, facecolor="#D8D8D8", edgecolor="#000000", lw=0.9))
        ax.add_patch(patches.Rectangle((x-1.8, y-2.0), 3.6, 0.4, facecolor="#000000"))

    def draw_scanner(x, y):
        # Scanner head
        ax.add_patch(patches.FancyBboxPatch((x-1.2, y+0.5), 2.4, 1.4, boxstyle="round,pad=0.05,rounding_size=0.2",
                                            facecolor="#FFFFFF", edgecolor="#000000", lw=1.2))
        ax.add_patch(patches.Polygon([[x-0.6, y+0.5], [x-0.3, y-1.6], [x+0.4, y-1.6], [x+0.2, y+0.5]],
                                     closed=True, facecolor="#E0E0E0", edgecolor="#000000", lw=1.0))
        # Beam lines
        ax.plot([x+1.2, x+3.2], [y+1.2, y+1.2], color="#000000", lw=1.0, linestyle=":")
        ax.plot([x+1.2, x+3.2], [y+0.9, y+0.7], color="#000000", lw=1.0, linestyle=":")
        ax.plot([x+1.2, x+3.2], [y+1.5, y+1.7], color="#000000", lw=1.0, linestyle=":")

    def draw_printer(x, y):
        ax.add_patch(patches.FancyBboxPatch((x-1.8, y-1.2), 3.6, 2.5, boxstyle="round,pad=0.05,rounding_size=0.25",
                                            facecolor="#FFFFFF", edgecolor="#000000", lw=1.3))
        ax.add_patch(patches.Rectangle((x-1.4, y+0.8), 2.8, 0.3, facecolor="#000000"))
        # Paper slip
        ax.add_patch(patches.Rectangle((x-1.2, y+1.1), 2.4, 1.8, facecolor="#FFFFFF", edgecolor="#000000", lw=0.9))
        ax.plot([x-0.9, x+0.9], [y+2.4, y+2.4], color="#000000", lw=0.8)
        ax.plot([x-0.9, x+0.5], [y+2.0, y+2.0], color="#000000", lw=0.8)
        ax.plot([x-0.9, x+0.7], [y+1.6, y+1.6], color="#000000", lw=0.8)

    def draw_kds(x, y):
        ax.add_patch(patches.FancyBboxPatch((x-2.6, y-1.8), 5.2, 3.6, boxstyle="round,pad=0.05,rounding_size=0.25",
                                            facecolor="#FFFFFF", edgecolor="#000000", lw=1.3))
        ax.add_patch(patches.Rectangle((x-2.3, y-1.5), 4.6, 3.0, facecolor="#F8F8F8", edgecolor="#000000", lw=0.7))
        # 3 mini ticket blocks
        ax.add_patch(patches.Rectangle((x-2.0, y-1.2), 1.2, 2.0, facecolor="#FFFFFF", edgecolor="#000000", lw=0.6))
        ax.add_patch(patches.Rectangle((x-0.6, y-1.2), 1.2, 2.0, facecolor="#FFFFFF", edgecolor="#000000", lw=0.6))
        ax.add_patch(patches.Rectangle((x+0.8, y-1.2), 1.2, 2.0, facecolor="#E0E0E0", edgecolor="#000000", lw=0.6))

    def draw_pc(x, y):
        ax.add_patch(patches.FancyBboxPatch((x-2.4, y-0.6), 4.8, 3.2, boxstyle="round,pad=0.05,rounding_size=0.2",
                                            facecolor="#FFFFFF", edgecolor="#000000", lw=1.3))
        ax.add_patch(patches.Rectangle((x-2.1, y-0.3), 4.2, 2.6, facecolor="#F8F8F8", edgecolor="#000000", lw=0.7))
        ax.add_patch(patches.Polygon([[x-3.0, y-1.5], [x+3.0, y-1.5], [x+2.4, y-0.6], [x-2.4, y-0.6]],
                                     closed=True, facecolor="#E0E0E0", edgecolor="#000000", lw=1.0))

    def draw_router(x, y):
        ax.add_patch(patches.FancyBboxPatch((x-3.2, y-1.2), 6.4, 2.4, boxstyle="round,pad=0.05,rounding_size=0.3",
                                            facecolor="#FFFFFF", edgecolor="#000000", lw=1.4))
        ax.plot([x-2.2, x-2.7], [ry+1.2, ry+3.6], color="#000000", lw=2.0)
        ax.plot([x+2.2, x+2.7], [ry+1.2, ry+3.6], color="#000000", lw=2.0)
        ax.add_patch(patches.Circle((x-2.7, ry+3.6), 0.28, facecolor="#000000"))
        ax.add_patch(patches.Circle((x+2.7, ry+3.6), 0.28, facecolor="#000000"))
        for i in range(5):
            ax.add_patch(patches.Circle((x-2.0 + i*1.0, y), 0.26, facecolor="#000000"))

    def draw_server_rack(x, y):
        ax.add_patch(patches.FancyBboxPatch((x-3.8, y-6.0), 7.6, 12.0, boxstyle="round,pad=0.05,rounding_size=0.3",
                                            facecolor="#FFFFFF", edgecolor="#000000", lw=1.5))
        for i in range(3):
            by = y + 3.2 - i*3.2
            ax.add_patch(patches.Rectangle((x-3.2, by-1.1), 6.4, 2.2, facecolor="#ECECEC", edgecolor="#000000", lw=0.9))
            ax.add_patch(patches.Circle((x+2.2, by), 0.35, facecolor="#000000"))
            ax.add_patch(patches.Circle((x+1.2, by), 0.25, facecolor="#FFFFFF", edgecolor="#000000", lw=0.8))
            for g in range(4):
                ax.plot([x-2.6 + g*0.8, x-2.6 + g*0.8], [by-0.6, by+0.6], color="#000000", lw=0.9)

    def draw_database(x, y):
        for i in range(3):
            cy_i = y + 3.4 - i*3.4
            ax.add_patch(patches.Rectangle((x-4.6, cy_i-2.0), 9.2, 2.0, facecolor="#FFFFFF", edgecolor="#000000", lw=1.3))
            ax.add_patch(patches.Arc((x, cy_i-2.0), 9.2, 2.6, angle=0, theta1=180, theta2=360, color="#000000", lw=1.3))
            ax.add_patch(patches.Ellipse((x, cy_i), 9.2, 2.6, facecolor="#E4E4E4", edgecolor="#000000", lw=1.3))

    # =========================================================================
    # PLACEMENT: PURELY NAMES OF OBJECTS & 2D VECTOR ART
    # =========================================================================

    # 1. CLIENT TIER (Left)
    # (A) Customer Devices (Top)
    draw_smartphone(12.0, 50.5)
    draw_laptop(28.0, 50.5)
    ax.text(20.5, 45.0, "Customer Smartphone & Laptop",
            ha='center', va='center', fontfamily=font_family, fontsize=8.4, fontweight='bold', color="#000000")

    # (B) Cashier Counter (Upper-Mid)
    draw_pos_station(10.5, 36.5)
    draw_scanner(22.0, 36.5)
    draw_printer(31.5, 36.5)
    ax.text(20.5, 31.0, "Cashier POS Terminal\n(with 2D Scanner & Thermal Receipt Printer)",
            ha='center', va='center', fontfamily=font_family, fontsize=8.0, fontweight='bold', color="#000000", linespacing=1.1)

    # (C) Barista Kitchen Station (Lower-Mid)
    draw_kds(20.5, 23.0)
    ax.text(20.5, 18.0, "Barista KDS Display Screen",
            ha='center', va='center', fontfamily=font_family, fontsize=8.4, fontweight='bold', color="#000000")

    # (D) Administrator Workstation (Bottom)
    draw_pc(20.5, 10.5)
    ax.text(20.5, 5.8, "Administrator PC Workstation",
            ha='center', va='center', fontfamily=font_family, fontsize=8.4, fontweight='bold', color="#000000")

    # 2. APPLICATION TIER (Center)
    # (A) Wi-Fi Router / LAN Switch (Top)
    rx, ry = 59.0, 48.0
    draw_router(rx, ry)
    ax.text(59.0, 42.5, "Wi-Fi Router / LAN Switch",
            ha='center', va='center', fontfamily=font_family, fontsize=9.2, fontweight='bold', color="#000000")

    # (B) Web Server (PHP) (Bottom)
    sx, sy = 59.0, 21.0
    draw_server_rack(sx, sy)
    ax.text(59.0, 11.0, "Web Server (PHP)",
            ha='center', va='center', fontfamily=font_family, fontsize=9.5, fontweight='bold', color="#000000")

    # 3. DATA TIER (Right)
    dx, dy = 93.5, 30.0
    draw_database(dx, dy)
    ax.text(93.5, 20.5, "Database Server (MySQL)",
            ha='center', va='center', fontfamily=font_family, fontsize=9.8, fontweight='bold', color="#000000")

    # =========================================================================
    # CONNECTIONS (MINIMAL TEXT, CLEAN ORTHOGONAL LINES)
    # =========================================================================
    # Vertical Network Bus in gap between Client Tier (x=38) and App Tier (x=43)
    bus_x = 40.5
    # Taps from each of the 4 client nodes to bus_x
    for cy_node in [48.0, 34.0, 21.0, 8.5]:
        ax.plot([36.0, bus_x], [cy_node, cy_node], color="#000000", lw=1.5)

    # Bus vertical trunk
    ax.plot([bus_x, bus_x], [8.5, 48.0], color="#000000", lw=2.2)

    # From bus_x to Router (x=53.5)
    ax.annotate('', xy=(53.5, 48.0), xytext=(bus_x, 48.0),
                arrowprops=dict(arrowstyle="<->", color="#000000", lw=2.0))
    ax.text(46.8, 50.2, "Wi-Fi / LAN", ha='center', va='bottom',
            fontfamily=font_family, fontsize=7.8, fontweight='bold', color="#000000",
            bbox=dict(boxstyle="round,pad=0.15", facecolor="#FFFFFF", edgecolor="#000000", lw=0.8))

    # From Router (y=45.5) down to Web Server (PHP) (y=29.0)
    ax.annotate('', xy=(59.0, 29.0), xytext=(59.0, 44.5),
                arrowprops=dict(arrowstyle="<->", color="#000000", lw=2.0))
    ax.text(60.2, 36.8, "HTTP / JSON", ha='left', va='center',
            fontfamily=font_family, fontsize=8.0, fontweight='bold', color="#000000",
            bbox=dict(boxstyle="round,pad=0.15", facecolor="#FFFFFF", edgecolor="none"))

    # From Web Server (PHP) (x=64.8, y=21.0) to Database (MySQL) (x=86.5, y=21.0)
    ax.plot([64.8, 77.0, 77.0, 86.5], [21.0, 21.0, 30.0, 30.0], color="#000000", lw=2.0)
    ax.annotate('', xy=(86.5, 30.0), xytext=(77.0, 30.0),
                arrowprops=dict(arrowstyle="<->", color="#000000", lw=2.0))
    ax.text(77.0, 25.5, "SQL Queries", ha='center', va='center',
            fontfamily=font_family, fontsize=8.0, fontweight='bold', color="#000000",
            bbox=dict(boxstyle="round,pad=0.2", facecolor="#FFFFFF", edgecolor="#000000", lw=0.8))

    plt.tight_layout()
    os.makedirs(os.path.dirname(output_png), exist_ok=True)
    plt.savefig(output_png, dpi=300, bbox_inches='tight')
    if output_svg:
        plt.savefig(output_svg, format='svg', bbox_inches='tight')
    plt.close()
    print(f"Generated Minimalist System Architecture Diagram: {output_png}")

if __name__ == "__main__":
    draw_system_architecture("Documentation/figures/figure3_system_architecture.png",
                             "Documentation/figures/figure3_system_architecture.svg")
