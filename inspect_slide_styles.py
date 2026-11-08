import pptx

prs = pptx.Presentation('Documentation/PPT-CAPs-repaired.pptx')

for i in [0, 1, 3, 4, 5, 6, 7, 8, 9]:
    slide = prs.slides[i]
    print(f'=== SLIDE {i+1} ===')
    for sh in slide.shapes:
        if sh.has_text_frame:
            for p in sh.text_frame.paragraphs:
                if p.text.strip():
                    font = p.font
                    color = font.color.rgb if font and font.color and font.color.type == pptx.enum.dml.MSO_COLOR_TYPE.RGB else None
                    print(f'  [{sh.name}] font={font.name}, size={font.size.pt if font and font.size else None}, bold={font.bold}, color={color} -> {p.text.strip()[:50]}')

