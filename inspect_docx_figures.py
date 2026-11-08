import docx

doc = docx.Document('Documentation/CMS - 2026-10-26.docx')

print(f"Total Paragraphs: {len(doc.paragraphs)}")

for i, p in enumerate(doc.paragraphs):
    if p.text.strip().startswith('Figure '):
        prev_p = doc.paragraphs[i-1] if i > 0 else None
        has_img = False
        if prev_p:
            # Check for inline shapes or drawings
            has_img = ('graphic' in prev_p._p.xml or 'drawing' in prev_p._p.xml)
        print(f"Paragraph {i}: Caption: '{p.text.strip()}' | Preceding has image: {has_img}")

