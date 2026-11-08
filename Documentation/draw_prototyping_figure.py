# -*- coding: utf-8 -*-
"""
Generate Enlarged ("Padakuon") Prototyping SDLC Model Diagram for ISAT U Coffee Shop POS System
Strictly Black and White, 2D Vector Style
- Enlarged cards and clean typography so ZERO text overflows any card border
- Replaced "80mm receipt" with "Thermal receipt" per teacher instruction
- High contrast, 300 DPI Times New Roman typography optimized for full 6.0-inch width on short bond paper
"""
import os
import matplotlib
matplotlib.use('Agg')
import matplotlib.pyplot as plt
import matplotlib.patches as patches

def draw_prototyping_model(output_png):
    # Enlarged canvas (9.6 x 6.4 in at 300 DPI) for crystal-clear full-width rendering
    fig, ax = plt.subplots(figsize=(9.6, 6.4), dpi=300)
    ax.set_xlim(0, 136)
    ax.set_ylim(0, 90)
    ax.axis('off')

    font_family = 'Times New Roman'
    fig.patch.set_facecolor('#FFFFFF')

    # Main outer title banner
    banner = patches.FancyBboxPatch(
        (3, 80), 130, 7.2, boxstyle="round,pad=0.2,rounding_size=0.6",
        facecolor="#F0F0F0", edgecolor="#000000", lw=1.6
    )
    ax.add_patch(banner)
    ax.text(68.0, 83.6, "PROTOTYPING SOFTWARE DEVELOPMENT LIFE CYCLE (SDLC) MODEL",
            ha='center', va='center', fontfamily=font_family, fontsize=13.5, fontweight='bold', color="#000000")

    # Enlarged Card dimensions (width=27.5, height=22.5)
    # 4 columns across x:
    # Col 1: x = 3.5  to 31.0
    # Col 2: x = 36.5 to 64.0
    # Col 3: x = 69.5 to 97.0
    # Col 4: x = 102.5 to 130.0
    cw = 27.5
    ch = 22.5

    phases = [
        (
            1, "1. Requirements\nGathering",
            3.5, 53.0,
            ["• Consult ISAT U planners", "• Identify table & study pod", "   seating capacity needs", "• Define POS & KDS scope"]
        ),
        (
            2, "2. Quick Design",
            36.5, 53.0,
            ["• Design MySQL database", "• Create UML & UI wireframes", "• Design thermal receipt &", "   QR booking pass layout"]
        ),
        (
            3, "3. Prototype\nDevelopment",
            69.5, 53.0,
            ["• Build Native PHP & MySQL", "• Develop reservation portal", "• Build POS QR scanner &", "   Barista KDS module"]
        ),
        (
            4, "4. User\nEvaluation",
            102.5, 53.0,
            ["• Demonstrate prototype to", "   campus stakeholders", "• Test booking & POS flow", "• Gather user feedback"]
        ),
        (
            5, "5. Prototype\nRefinement",
            69.5, 19.5,
            ["• Refine QR code scanning", "• Add T-15m pre-order queue", "• Implement Brew-to-Serve", "   ingredient & cup deduction"]
        ),
        (
            6, "6. System\nTesting",
            36.5, 19.5,
            ["• Test role access control", "• Verify discount & VAT math", "• Test inventory deduction &", "   thermal receipt printing"]
        ),
        (
            7, "7. Deployment &\nMaintenance",
            3.5, 19.5,
            ["• Deploy on campus server", "• Train cashier & barista", "• Conduct ISO/IEC 25010", "   system evaluation"]
        ),
    ]

    for pid, title, px, py, bullets in phases:
        # Card body (White with crisp black border)
        card = patches.FancyBboxPatch(
            (px, py), cw, ch, boxstyle="round,pad=0.2,rounding_size=0.6",
            facecolor="#FFFFFF", edgecolor="#000000", lw=1.6
        )
        ax.add_patch(card)

        # Header shading
        hdr_h = 6.8
        hdr = patches.FancyBboxPatch(
            (px, py + ch - hdr_h), cw, hdr_h, boxstyle="round,pad=0.2,rounding_size=0.6",
            facecolor="#F4F4F4", edgecolor="#000000", lw=1.2
        )
        ax.add_patch(hdr)

        # Header badge circle (Solid black with white number)
        badge_r = 2.1
        badge_x = px + 3.4
        badge_y = py + ch - hdr_h/2
        badge = patches.Circle((badge_x, badge_y), badge_r, facecolor="#000000", edgecolor="#000000", lw=1.0)
        ax.add_patch(badge)
        ax.text(badge_x, badge_y, str(pid), ha='center', va='center',
                fontfamily=font_family, fontsize=11.0, fontweight='bold', color="#FFFFFF")

        # Card Title
        ax.text(px + 6.4, badge_y, title, ha='left', va='center',
                fontfamily=font_family, fontsize=10.5, fontweight='bold', color="#000000", linespacing=1.08)

        # Bullets inside body (strictly within card boundaries)
        bullet_text = "\n".join(bullets)
        ax.text(px + 1.6, py + ch - hdr_h - 1.6, bullet_text, ha='left', va='top',
                fontfamily=font_family, fontsize=9.2, color="#000000", linespacing=1.25)

    # Validated connections between cards (Edge to Edge, zero overlap)
    top_mid_y = 53.0 + ch/2  # 64.25
    bot_mid_y = 19.5 + ch/2  # 30.75

    # Forward arrows top row: 1 -> 2 -> 3 -> 4
    ax.annotate('', xy=(36.3, top_mid_y), xytext=(31.2, top_mid_y),
                arrowprops=dict(arrowstyle="->", color="#000000", lw=2.0))
    ax.annotate('', xy=(69.3, top_mid_y), xytext=(64.2, top_mid_y),
                arrowprops=dict(arrowstyle="->", color="#000000", lw=2.0))
    ax.annotate('', xy=(102.3, top_mid_y), xytext=(97.2, top_mid_y),
                arrowprops=dict(arrowstyle="->", color="#000000", lw=2.0))

    # Downward transition: Phase 4 -> Phase 5
    p4_cx = 102.5 + cw/2  # 116.25
    ax.plot([p4_cx, p4_cx], [52.8, bot_mid_y], color="#000000", lw=1.8, linestyle="--")
    ax.annotate('', xy=(97.2, bot_mid_y), xytext=(p4_cx, bot_mid_y),
                arrowprops=dict(arrowstyle="->", color="#000000", lw=1.8, linestyle="--"))
    ax.text(107.0, bot_mid_y + 2.0, "Adjustments\nNeeded", ha='center', va='bottom',
            fontfamily=font_family, fontsize=9.2, fontweight='bold', color="#000000",
            bbox=dict(boxstyle="round,pad=0.2", facecolor="#FFFFFF", edgecolor="none"))

    # Iteration Feedback Loop: Phase 5 back to Phase 3
    p5_cx = 69.5 + cw/2  # 83.25
    ax.annotate('', xy=(p5_cx, 52.8), xytext=(p5_cx, 42.2),
                arrowprops=dict(arrowstyle="->", color="#000000", lw=1.8, linestyle="--"))
    ax.text(p5_cx + 1.8, 47.5, "Iterative\nRefinement", ha='left', va='center',
            fontfamily=font_family, fontsize=9.2, fontweight='bold', color="#000000",
            bbox=dict(boxstyle="round,pad=0.2", facecolor="#FFFFFF", edgecolor="none"))

    # Bottom row forward arrows: 5 -> 6 -> 7
    ax.annotate('', xy=(64.2, bot_mid_y), xytext=(69.3, bot_mid_y),
                arrowprops=dict(arrowstyle="->", color="#000000", lw=2.0))
    ax.text(66.75, bot_mid_y + 2.2, "Approved", ha='center', va='bottom',
            fontfamily=font_family, fontsize=8.8, fontweight='bold', color="#000000",
            bbox=dict(boxstyle="round,pad=0.15", facecolor="#FFFFFF", edgecolor="none"))

    ax.annotate('', xy=(31.2, bot_mid_y), xytext=(36.3, bot_mid_y),
                arrowprops=dict(arrowstyle="->", color="#000000", lw=2.0))

    # Phase 7 bottom to Operational Deployment Badge
    p7_cx = 3.5 + cw/2  # 17.25
    ax.annotate('', xy=(p7_cx, 10.5), xytext=(p7_cx, 19.3),
                arrowprops=dict(arrowstyle="->", color="#000000", lw=2.0))

    deploy_box = patches.FancyBboxPatch(
        (3.5, 3.0), 48.0, 7.0, boxstyle="round,pad=0.2,rounding_size=0.5",
        facecolor="#F0F0F0", edgecolor="#000000", lw=1.5
    )
    ax.add_patch(deploy_box)
    ax.text(27.5, 6.5, "Operational System in ISAT U - DC Mini Hotel",
            ha='center', va='center', fontfamily=font_family, fontsize=10.5, fontweight='bold', color="#000000")

    plt.tight_layout()
    os.makedirs(os.path.dirname(output_png), exist_ok=True)
    plt.savefig(output_png, dpi=300, bbox_inches='tight')
    plt.close()
    print(f"Generated Enlarged ('Padakuon') Strictly B&W Prototyping SDLC Model: {output_png}")

if __name__ == "__main__":
    draw_prototyping_model("Documentation/figures/figure2_prototyping.png")
