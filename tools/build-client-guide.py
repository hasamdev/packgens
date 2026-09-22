# -*- coding: utf-8 -*-
"""Build the client's editing guide as a PDF."""
import datetime
import os

from PIL import Image as PILImage
from reportlab.lib import colors
from reportlab.lib.enums import TA_LEFT
from reportlab.lib.pagesizes import A4
from reportlab.lib.styles import ParagraphStyle, getSampleStyleSheet
from reportlab.lib.units import mm
from reportlab.platypus import (BaseDocTemplate, Frame, Image, KeepTogether,
                               NextPageTemplate, PageBreak, PageTemplate,
                               Paragraph, Spacer, Table, TableStyle)

HERE = os.path.dirname(os.path.abspath(__file__))
OUT = os.path.join(HERE, 'Packgens-Website-Editor-Guide.pdf')

BLUE = colors.HexColor('#0022B4')
VIVID = colors.HexColor('#0027D0')
INK = colors.HexColor('#191B1B')
BODY = colors.HexColor('#484848')
MUTED = colors.HexColor('#667085')
LIGHT = colors.HexColor('#F3F5F6')
LINE = colors.HexColor('#E4E7EC')
GREEN = colors.HexColor('#1EBA88')

PAGE_W, PAGE_H = A4
MARGIN = 20 * mm
CONTENT_W = PAGE_W - (MARGIN * 2)

styles = getSampleStyleSheet()


def S(name, **kw):
    base = kw.pop('parent', styles['Normal'])
    return ParagraphStyle(name, parent=base, **kw)


BodyS = S('PgBody', fontName='Helvetica', fontSize=10, leading=15.5,
          textColor=BODY, spaceAfter=7, alignment=TA_LEFT)
LeadS = S('PgLead', parent=BodyS, fontSize=11.5, leading=17.5, textColor=INK,
          spaceAfter=10)
H1 = S('PgH1', fontName='Helvetica-Bold', fontSize=20, leading=25,
       textColor=INK, spaceBefore=4, spaceAfter=3)
H2 = S('PgH2', fontName='Helvetica-Bold', fontSize=13.5, leading=18,
       textColor=INK, spaceBefore=14, spaceAfter=5)
H3 = S('PgH3', fontName='Helvetica-Bold', fontSize=11, leading=15,
       textColor=BLUE, spaceBefore=10, spaceAfter=3)
Kicker = S('PgKick', fontName='Helvetica-Bold', fontSize=8.5, leading=12,
           textColor=VIVID, spaceAfter=2)
Cap = S('PgCap', fontName='Helvetica-Oblique', fontSize=8.5, leading=12,
        textColor=MUTED, spaceBefore=4, spaceAfter=12)
Bullet = S('PgBullet', parent=BodyS, leftIndent=12, bulletIndent=2,
           spaceAfter=4)
StepS = S('PgStep', parent=BodyS, leftIndent=16, bulletIndent=0, spaceAfter=5)
CellS = S('PgCell', fontName='Helvetica', fontSize=9, leading=13,
          textColor=BODY)
CellB = S('PgCellB', fontName='Helvetica-Bold', fontSize=9, leading=13,
          textColor=INK)
CellH = S('PgCellH', fontName='Helvetica-Bold', fontSize=8.5, leading=12,
          textColor=colors.white)
NoteS = S('PgNote', fontName='Helvetica', fontSize=9.5, leading=14.5,
          textColor=INK)

story = []


# --------------------------------------------------------------- helpers
def h1(text, kicker=None):
    if kicker:
        story.append(Paragraph(kicker.upper(), Kicker))
    story.append(Paragraph(text, H1))
    story.append(rule())
    story.append(Spacer(1, 8))


def rule(width=CONTENT_W, colour=BLUE, thick=2):
    t = Table([['']], colWidths=[width], rowHeights=[thick])
    t.setStyle(TableStyle([('BACKGROUND', (0, 0), (-1, -1), colour)]))
    return t


def h2(text):
    story.append(Paragraph(text, H2))


def h3(text):
    story.append(Paragraph(text, H3))


def p(text, style=BodyS):
    story.append(Paragraph(text, style))


def bullets(items):
    for item in items:
        story.append(Paragraph(item, Bullet, bulletText='•'))
    story.append(Spacer(1, 4))


def steps(items):
    for i, item in enumerate(items, 1):
        story.append(Paragraph(item, StepS, bulletText='%d.' % i))
    story.append(Spacer(1, 4))


def table(rows, widths, header=True):
    data = []
    for r, row in enumerate(rows):
        line = []
        for cell in row:
            if r == 0 and header:
                line.append(Paragraph(cell, CellH))
            else:
                line.append(Paragraph(cell, CellS))
        data.append(line)

    t = Table(data, colWidths=widths, repeatRows=1 if header else 0)
    style = [
        ('VALIGN', (0, 0), (-1, -1), 'TOP'),
        ('LEFTPADDING', (0, 0), (-1, -1), 8),
        ('RIGHTPADDING', (0, 0), (-1, -1), 8),
        ('TOPPADDING', (0, 0), (-1, -1), 6),
        ('BOTTOMPADDING', (0, 0), (-1, -1), 6),
        ('LINEBELOW', (0, 0), (-1, -2), 0.5, LINE),
        ('BOX', (0, 0), (-1, -1), 0.5, LINE),
    ]
    if header:
        style += [
            ('BACKGROUND', (0, 0), (-1, 0), BLUE),
            ('LINEBELOW', (0, 0), (-1, 0), 0, colors.white),
        ]
        style += [('ROWBACKGROUNDS', (0, 1), (-1, -1), [colors.white, LIGHT])]
    else:
        style += [('ROWBACKGROUNDS', (0, 0), (-1, -1), [colors.white, LIGHT])]

    t.setStyle(TableStyle(style))
    story.append(t)
    story.append(Spacer(1, 10))


def note(text, title='Note', colour=None):
    colour = colour or VIVID
    inner = [
        [Paragraph('<b>%s</b>' % title, S('n1', parent=NoteS, textColor=colour))],
        [Paragraph(text, NoteS)],
    ]
    t = Table(inner, colWidths=[CONTENT_W - 16])
    t.setStyle(TableStyle([
        ('LEFTPADDING', (0, 0), (-1, -1), 0),
        ('RIGHTPADDING', (0, 0), (-1, -1), 0),
        ('TOPPADDING', (0, 0), (-1, 0), 0),
        ('BOTTOMPADDING', (0, 0), (-1, 0), 2),
        ('TOPPADDING', (0, 1), (-1, 1), 0),
        ('BOTTOMPADDING', (0, 1), (-1, 1), 0),
    ]))
    outer = Table([[t]], colWidths=[CONTENT_W])
    outer.setStyle(TableStyle([
        ('BACKGROUND', (0, 0), (-1, -1), LIGHT),
        ('LEFTPADDING', (0, 0), (-1, -1), 12),
        ('RIGHTPADDING', (0, 0), (-1, -1), 12),
        ('TOPPADDING', (0, 0), (-1, -1), 10),
        ('BOTTOMPADDING', (0, 0), (-1, -1), 10),
        ('LINEBEFORE', (0, 0), (0, -1), 3, colour),
    ]))
    story.append(KeepTogether([outer, Spacer(1, 10)]))


def shot(name, caption, max_h=None):
    path = os.path.join(HERE, 'shot-%s.png' % name)
    if not os.path.exists(path):
        return
    w, h = PILImage.open(path).size
    draw_w = CONTENT_W
    draw_h = draw_w * h / float(w)
    if max_h and draw_h > max_h:
        draw_h = max_h
        draw_w = draw_h * w / float(h)
    img = Image(path, width=draw_w, height=draw_h)
    img.hAlign = 'LEFT'
    box = Table([[img]], colWidths=[draw_w])
    box.setStyle(TableStyle([
        ('BOX', (0, 0), (-1, -1), 0.5, LINE),
        ('LEFTPADDING', (0, 0), (-1, -1), 0),
        ('RIGHTPADDING', (0, 0), (-1, -1), 0),
        ('TOPPADDING', (0, 0), (-1, -1), 0),
        ('BOTTOMPADDING', (0, 0), (-1, -1), 0),
    ]))
    story.append(KeepTogether([box, Paragraph(caption, Cap)]))


def recipe(title, items, tail=None):
    block = [Paragraph(title, H3)]
    for i, item in enumerate(items, 1):
        block.append(Paragraph(item, StepS, bulletText='%d.' % i))
    if tail:
        block.append(Spacer(1, 2))
        block.append(Paragraph(tail, S('rt', parent=BodyS, textColor=MUTED)))
    block.append(Spacer(1, 6))
    story.append(KeepTogether(block))


# --------------------------------------------------------------- cover
def draw_cover(canvas, doc):
    canvas.saveState()
    canvas.setFillColor(BLUE)
    canvas.rect(0, PAGE_H - 300, PAGE_W, 300, stroke=0, fill=1)

    canvas.setFillColor(colors.white)
    canvas.setFont('Helvetica-Bold', 30)
    canvas.drawString(MARGIN, PAGE_H - 150, 'Packgens')
    canvas.setFont('Helvetica', 13)
    canvas.drawString(MARGIN, PAGE_H - 176, 'Custom packaging, printed to order')

    canvas.setFillColor(INK)
    canvas.setFont('Helvetica-Bold', 26)
    canvas.drawString(MARGIN, PAGE_H - 380, 'Website Editor’s Guide')

    canvas.setFillColor(BODY)
    canvas.setFont('Helvetica', 12)
    canvas.drawString(MARGIN, PAGE_H - 406,
                      'How to change anything on your website, without a developer')

    canvas.setFillColor(VIVID)
    canvas.setFont('Helvetica-Bold', 9)
    canvas.drawString(MARGIN, PAGE_H - 470, 'INSIDE')

    canvas.setFillColor(BODY)
    canvas.setFont('Helvetica', 11)
    inside = [
        'Where every piece of content lives',
        'Editing the sections that make up a page',
        'One screen that updates all 21 category pages',
        'Rewording any form, heading or button',
        'Step-by-step recipes for everyday changes',
        'A single quick-reference table',
    ]
    y = PAGE_H - 496
    for line in inside:
        canvas.setFillColor(VIVID)
        canvas.circle(MARGIN + 3, y + 4, 2.2, stroke=0, fill=1)
        canvas.setFillColor(BODY)
        canvas.drawString(MARGIN + 14, y, line)
        y -= 21

    canvas.setStrokeColor(LINE)
    canvas.setLineWidth(1)
    canvas.line(MARGIN, 150, PAGE_W - MARGIN, 150)

    canvas.setFillColor(MUTED)
    canvas.setFont('Helvetica', 9.5)
    canvas.drawString(MARGIN, 130, 'Prepared for the Packgens team')
    canvas.drawString(MARGIN, 114, datetime.date.today().strftime('%d %B %Y'))
    canvas.drawRightString(PAGE_W - MARGIN, 130, 'Version 1.0')
    canvas.drawRightString(PAGE_W - MARGIN, 114, 'WordPress + WooCommerce')
    canvas.restoreState()


def draw_page(canvas, doc):
    canvas.saveState()
    canvas.setStrokeColor(LINE)
    canvas.setLineWidth(0.5)
    canvas.line(MARGIN, PAGE_H - MARGIN + 14, PAGE_W - MARGIN, PAGE_H - MARGIN + 14)
    canvas.setFont('Helvetica', 8)
    canvas.setFillColor(MUTED)
    canvas.drawString(MARGIN, PAGE_H - MARGIN + 20, 'Packgens — Website Editor’s Guide')

    canvas.line(MARGIN, MARGIN - 12, PAGE_W - MARGIN, MARGIN - 12)
    canvas.drawRightString(PAGE_W - MARGIN, MARGIN - 24, str(canvas.getPageNumber()))
    canvas.restoreState()


doc = BaseDocTemplate(OUT, pagesize=A4,
                      leftMargin=MARGIN, rightMargin=MARGIN,
                      topMargin=MARGIN, bottomMargin=MARGIN,
                      title='Packgens — Website Editor’s Guide',
                      author='Packgens', subject='Client editing guide')

frame = Frame(MARGIN, MARGIN, CONTENT_W, PAGE_H - (MARGIN * 2), id='body')
doc.addPageTemplates([
    PageTemplate(id='cover', frames=[frame], onPage=draw_cover),
    PageTemplate(id='body', frames=[frame], onPage=draw_page),
])

# =========================================================== 1. Cover
story.append(NextPageTemplate('body'))
story.append(PageBreak())

# =========================================================== 2. Contents
h1('What is in this guide')
p('Your website was built so that you can change it yourself. Wording, prices, '
  'photographs, links, whole sections of a page — all of it is editable from '
  'the WordPress admin. Nothing in this guide requires a developer, and nothing '
  'in it can break the site.', LeadS)

table([
    ['Section', 'What it covers'],
    ['1. Before you start', 'Signing in, and four rules that make everything else easier'],
    ['2. The Packgens menu', 'A map of the three screens where your content lives'],
    ['3. Site Settings', 'Phone, email, address, social links, the announcement bar'],
    ['4. Global Content', 'Wording shared across the whole site, including every form'],
    ['5. Page sections', 'Editing the bands that make up a page'],
    ['6. Product categories', 'The one screen that updates all 21 category pages'],
    ['7. Products', 'Badges, price captions, tabs and feature cards'],
    ['8. Blog', 'Writing posts, and what appears beside them'],
    ['9. Menus', 'The main navigation and the four footer columns'],
    ['10. Enquiries', 'Where form submissions are stored'],
    ['11. Saved items', 'How the wishlist works'],
    ['12. Common tasks', 'Step-by-step recipes for the things you will do most'],
    ['13. Quick reference', 'One table: “I want to change X → go to Y”'],
    ['14. Questions and answers', 'Including why one admin screen looks empty'],
], [150, CONTENT_W - 150])

story.append(PageBreak())

# =========================================================== 3. Before you start
h1('Before you start', '1')

h2('Signing in')
p('Go to <b>your-site.com/wp-admin</b> and sign in with the username and password '
  'you were given. If you forget the password, use <b>Lost your password?</b> on '
  'that screen — a reset link is emailed to you.')

h2('Four rules worth knowing')

h3('1. Saving is always the last step')
p('Nothing you type takes effect until you press <b>Update</b> (on a page, post or '
  'product) or <b>Save</b> (on the Packgens screens). If you navigate away without '
  'saving, your changes are lost.')

h3('2. An empty field is not a blank space')
p('Almost every wording field falls back to what the site already says. Clear a '
  'field and the original wording comes back — it does not leave a gap on the '
  'page. This is why you only need to fill in the things you actually want to '
  'change.')

h3('3. Hide sections, do not delete them')
p('Every section on a page has a <b>Show this section</b> switch. Turning it off '
  'hides the band and keeps your text safe for later. Deleting the text means '
  'retyping it.')

h3('4. Check the front of the site after you save')
p('Open the page in a new browser tab and refresh. If something looks wrong, the '
  'fix is almost always to clear the field you just filled in, which restores the '
  'original.')

note('You cannot break the website from any screen in this guide. None of them '
     'change the site’s code. The worst case is wording you do not like, and '
     'clearing the field undoes it.', 'Reassurance', GREEN)

story.append(PageBreak())

# =========================================================== 4. Packgens menu
h1('The Packgens menu', '2')
p('Look down the black sidebar in the admin. Near the top you will find '
  '<b>Packgens</b>. Everything that is shared across the site lives under it, on '
  'three screens.', LeadS)

table([
    ['Screen', 'What it holds'],
    ['<b>Packgens</b><br/>(Global Content)',
     'Wording and content that appears across many pages: every form label, the '
     'footer headings, blog headings, the strips above and below the header, the '
     'article sidebar, the 404 page'],
    ['<b>Category Defaults</b>',
     'The content every product category page falls back to. Change it once here '
     'and all 21 categories update'],
    ['<b>Site Settings</b>',
     'Your phone number, email, postal address, opening hours, social profiles, '
     'the announcement bar and the header button'],
], [120, CONTENT_W - 120])

shot('site-settings', 'The Packgens menu is highlighted on the left. Site Settings '
     'is open, showing the Announcement bar tab.')

note('Page-specific content is not here. To change something that only appears on '
     'one page, edit that page — see section 5.', 'Where page content lives')

story.append(PageBreak())

# =========================================================== 5. Site Settings
h1('Site Settings', '3')
p('<b>Packgens → Site Settings.</b> Six tabs holding the details that appear '
  'all over the site. Change the phone number here and it updates in the header, '
  'the footer, the contact page and the sidebar quote card at the same time.', LeadS)

table([
    ['Tab', 'What it holds'],
    ['Announcement bar', 'The thin strip at the very top of every page, its live-chat '
     'label and link. The switch at the top hides the whole bar.'],
    ['Header', 'The blue button in the header and where it points, plus the '
     '“Free Shipping” note beside the phone number'],
    ['Contact details', 'Phone, its caption, a second phone, WhatsApp, email, postal '
     'address and opening hours'],
    ['Social profiles', 'Facebook, Instagram, LinkedIn, YouTube and X. Leave one '
     'blank and its icon disappears from the site.'],
    ['Global links', 'Where the “Get a quote”, “Reviews” and '
     '“All products” buttons send people'],
    ['Form handling', 'Who receives enquiry emails, the thank-you page, and the '
     'message shown after someone sends a form'],
], [110, CONTENT_W - 110])

note('The address and opening-hours boxes keep your line breaks. Type them on '
     'separate lines exactly as you want them to appear on the site.', 'Tip')

story.append(PageBreak())

# =========================================================== 6. Global Content
h1('Global Content', '4')
p('<b>Packgens → Packgens.</b> Thirteen tabs. The first five hold blocks of '
  'content; the eight beginning <b>Text:</b> hold individual words and phrases.', LeadS)

shot('global-content', 'All thirteen tabs. The Article sidebar tab is open.')

h2('The content tabs')
table([
    ['Tab', 'What it holds'],
    ['Article sidebar', 'The card beside a blog post: heading, rating, image and tick list'],
    ['Header strip', 'The four icons under the header on pages without a large banner'],
    ['Trust strip', 'The blue band above the footer'],
    ['Footer', 'Newsletter wording, payment and delivery logos, the copyright line'],
    ['Quote form', 'The “Get Instant Quote” form’s title, intro, button and small print'],
], [110, CONTENT_W - 110])

story.append(PageBreak())

h2('The Text: tabs')
p('These hold every remaining word on the site that is not part of a page. Each '
  'field shows its current wording as grey placeholder text — so you can see '
  'what an empty field will produce before you type anything.')

shot('text-forms', 'Text: forms. The grey text in each box is what the site says '
     'today; type over it only if you want it to say something else.')

table([
    ['Tab', 'Covers'],
    ['Text: forms', 'Every label and placeholder in the quote and contact forms, the '
     'file-upload text, the contact form title and its button'],
    ['Text: header', 'The two search box placeholders'],
    ['Text: footer', '“Instant Information”, “Send Email”, '
     '“Call Information”, “Address”, “Follow us”'],
    ['Text: blog', 'Filter labels, sidebar headings, “Related Blogs”, '
     '“Visit Our Blog”, “Table of Contents”, “Share This '
     'Article”, “Read More”'],
    ['Text: contact channels', '“Call Us”, “Send Email”, '
     '“Message Us”, “Office Address”'],
    ['Text: quote card', 'The blue quote card in sidebars: title, text, phone label, button'],
    ['Text: saved items', 'The message shown when a shopper’s saved list is empty'],
    ['Text: 404 and search', 'The “page not found” heading, text and buttons'],
], [110, CONTENT_W - 110])

note('The quote form and the contact form share these labels. Changing '
     '“Full Name” changes it in both.', 'Worth knowing')

story.append(PageBreak())

# =========================================================== 7. Page sections
h1('Page sections', '5')
p('Your pages are built from bands stacked on top of each other — a banner, an '
  'about block, a process strip, reviews, questions, a call to action. Each band is '
  'a <b>section</b>, and each section has its own panel on the page’s edit '
  'screen.', LeadS)

steps([
    'Go to <b>Pages → All Pages</b> and click the page you want to change.',
    'Scroll past the main editor. At the bottom you will see a bar labelled '
    '<b>Meta Boxes</b> — drag it upward or click it to open.',
    'You will find a stack of panels: Section Order, Hero, About, Why Choose Us, '
    'and so on.',
    'Edit the panel you need, then press <b>Save</b> at the top right.',
])

shot('page-sections', 'The About page. Section Order is at the top, with the Hero '
     'section open below it.')

story.append(PageBreak())

h2('What every section has')
table([
    ['Field', 'What it does'],
    ['Show this section', 'A switch. Turn it off to hide the band and keep the '
     'content for later.'],
    ['Eyebrow', 'The small line of text above the heading (not on every section)'],
    ['Title', 'The heading'],
    ['Description', 'The paragraph under the heading'],
    ['…then section-specific fields', 'Cards, list items, images, tabs — '
     'whatever that band is made of'],
    ['Button', 'The link at the bottom. Clear the link and the button disappears.'],
], [130, CONTENT_W - 130])

h2('Section Order')
p('The first panel lets you tick which sections appear and drag them into a '
  'different order for that page. Leave it untouched and the page uses the '
  'standard order.')

h2('Lists inside a section')
p('Where a section holds a list — cards, steps, questions, features — you '
  'get a stack of numbered rows.')
bullets([
    '<b>Add</b> at the bottom creates a new row.',
    'The handle at the left of a row drags it up or down.',
    'The minus at the right removes a row.',
])

note('Two call-to-action bands are available on every page, so a page can open with '
     'one message and close with a different one.', 'Tip')

story.append(PageBreak())

# =========================================================== 8. Categories
h1('Product categories', '6')
p('This is the section that saves the most time.', LeadS)

p('There are 21 product categories, and they all share one set of content: '
  '<b>Packgens → Category Defaults</b>. Edit the black call-to-action band '
  'there and it changes on every category page at once.')

p('When a category needs something of its own — usually its own headline and '
  'the heading above its product grid — edit that category directly under '
  '<b>Products → Categories</b>. Anything you type there applies to that '
  'category only. Anything you leave alone keeps following the default.')

note('<b>Change it once in Category Defaults. Override only the exceptions.</b><br/>'
     'If you find yourself making the same edit on several categories, you are in '
     'the wrong screen — make it once on Category Defaults instead.',
     'The rule to remember')

shot('category-defaults', 'Category Defaults. Everything here applies to all 21 '
     'category pages unless a category overrides it.')

story.append(PageBreak())

# =========================================================== 9. Products
h1('Products', '7')
p('Edit a product under <b>Products → All Products</b>. Price, stock, images '
  'and the short description stay in WooCommerce’s own boxes. Below them you '
  'will find <b>Product Details</b>, which is the theme’s.', LeadS)

table([
    ['Field', 'Where it appears'],
    ['Badge', 'The small label on the product card — “Best Seller”, '
     '“Best Price”. Leave it empty for no badge.'],
    ['Price caption', 'The “/ unit” text after the price'],
    ['Delivery note', 'The dashed button under Add to Basket'],
    ['Rating strip', 'The stars and review count above the title'],
    ['Offer strip', 'The discount code panel with the copy button'],
    ['Help card', 'The “Need help customizing?” card under the gallery'],
    ['Feature cards', 'The two cards beside the help card'],
    ['Tabs', 'Description, specifications, artwork guidelines and FAQs below the product'],
], [110, CONTENT_W - 110])

h2('Adding a new product')
steps([
    'Go to <b>Products → Add New</b>.',
    'Give it a name and a short description — the description is what appears '
    'on the product card.',
    'Set the price under <b>Product data → General</b>.',
    'Add a <b>Product image</b> and, if you have more, a <b>Product gallery</b>.',
    'Tick its <b>Categories</b> on the right — this decides which category '
    'pages it appears on.',
    'Fill in <b>Product Details</b> if you want a badge or the extra tabs.',
    'Press <b>Publish</b>.',
])

story.append(PageBreak())

# =========================================================== 10. Blog, menus
h1('Blog', '8')
p('Write posts under <b>Posts → Add New</b> exactly as you would on any '
  'WordPress site. Two things are specific to your site:', LeadS)
bullets([
    '<b>Users → Profile → Author photo</b> sets the picture beside your '
    'name on a post.',
    'The sidebar beside a post — contents, trust card, share links — comes '
    'from <b>Global Content → Article sidebar</b>, so it is the same on every post.',
])

h1('Menus', '9')
p('<b>Appearance → Menus.</b> There are five: the main navigation and the four '
  'footer columns.', LeadS)

table([
    ['Menu', 'Where it appears'],
    ['Packgens Primary', 'The main navigation across the header'],
    ['Packgens Footer Information', 'First footer column'],
    ['Packgens Footer Industries', 'Second footer column'],
    ['Packgens Footer Products', 'Third footer column'],
    ['Packgens Footer Legal', 'The links along the very bottom'],
], [170, CONTENT_W - 170])

p('To add a link, pick a page or product category from the panel on the left and '
  'press <b>Add to Menu</b>, then drag it into place. Drag an item slightly right '
  'to make it a sub-item. Press <b>Save Menu</b>.')

shot('menus', 'Appearance → Menus. Pick the menu to edit from the dropdown at '
     'the top, then drag items to reorder them.')

note('To make a top-level item open a large drop-down panel, open <b>Screen '
     'Options</b> at the top right, tick <b>CSS Classes</b>, then type <b>mega</b> '
     'in that item’s CSS Classes box. Add <b>cards</b> as well to show its '
     'children as picture cards.', 'Advanced')

story.append(PageBreak())

# =========================================================== 11. Enquiries
h1('Enquiries', '10')
p('Every quote request, contact message, newsletter sign-up and callback request '
  'is saved in the admin under <b>Enquiries</b> as well as emailed to you.', LeadS)

p('This matters: if an email is ever delayed, blocked or sent to spam, the enquiry '
  'is still here. Nothing is lost.')

p('Set the address that receives the emails in <b>Packgens → Site Settings '
  '→ Form handling</b>.')

h1('Saved items', '11')
p('Shoppers can tap the heart on any product to save it for later. The heart in the '
  'header shows how many they have saved, and the list lives at '
  '<b>/wishlist/</b>.', LeadS)
bullets([
    'Signed-in customers keep their list on their account, on any device.',
    'Guests keep it in their browser for 30 days.',
    'When a guest signs in, their saved items move onto their account automatically.',
])
p('The message shown when the list is empty is under <b>Global Content → '
  'Text: saved items</b>.')

story.append(PageBreak())

# =========================================================== 12. Common tasks
h1('Common tasks', '12')
p('Step-by-step for the things you are most likely to need.', LeadS)

recipe('Change the phone number everywhere', [
    'Packgens → Site Settings → <b>Contact details</b>.',
    'Edit <b>Primary phone</b>.',
    'Press <b>Save</b>.',
], 'It updates in the header, the footer, the contact page and the sidebar quote '
   'card at once.')

recipe('Change the announcement bar, or switch it off', [
    'Packgens → Site Settings → <b>Announcement bar</b>.',
    'Edit <b>Announcement text</b>, or set <b>Show announcement bar</b> to No.',
    'Press <b>Save</b>.',
])

recipe('Reword a form field', [
    'Packgens → Packgens → <b>Text: forms</b>.',
    'Find the field — the grey text shows its current wording.',
    'Type the new wording and press <b>Save</b>.',
])

recipe('Hide a section on one page', [
    'Pages → All Pages → open the page.',
    'Open the <b>Meta Boxes</b> bar at the bottom.',
    'Find that section’s panel and set <b>Show this section</b> to No.',
    'Press <b>Save</b>.',
])

recipe('Change something on every category page', [
    'Packgens → <b>Category Defaults</b>.',
    'Edit the section you want to change.',
    'Press <b>Save</b>. All 21 categories update.',
])

recipe('Give one category its own headline', [
    'Products → Categories → hover the category → <b>Edit</b>.',
    'Scroll to the <b>Hero</b> panel and set the title.',
    'Press <b>Update</b>. Only that category changes.',
])

recipe('Add a product to a category page', [
    'Products → All Products → open the product.',
    'Tick the category on the right, under <b>Product categories</b>.',
    'Press <b>Update</b>.',
])

recipe('Write and publish a blog post', [
    'Posts → <b>Add New</b>.',
    'Give it a title, write the body, and set a <b>Featured image</b> on the right.',
    'Choose a <b>Category</b>, then press <b>Publish</b>.',
])

story.append(PageBreak())

# =========================================================== 13. Quick reference
h1('Quick reference', '13')
p('One table for the whole site.', LeadS)

table([
    ['I want to change…', 'Go to'],
    ['Phone, email, address, opening hours', 'Packgens → Site Settings → Contact details'],
    ['Social media links', 'Packgens → Site Settings → Social profiles'],
    ['The bar at the very top of the page', 'Packgens → Site Settings → Announcement bar'],
    ['The blue button in the header', 'Packgens → Site Settings → Header'],
    ['Who receives enquiry emails', 'Packgens → Site Settings → Form handling'],
    ['Wording on a form', 'Packgens → Packgens → Text: forms'],
    ['Footer column headings', 'Packgens → Packgens → Text: footer'],
    ['“Read More”, blog headings', 'Packgens → Packgens → Text: blog'],
    ['The “page not found” page', 'Packgens → Packgens → Text: 404 and search'],
    ['The strip of icons under the header', 'Packgens → Packgens → Header strip'],
    ['The blue band above the footer', 'Packgens → Packgens → Trust strip'],
    ['The card beside a blog post', 'Packgens → Packgens → Article sidebar'],
    ['A section on one specific page', 'Pages → that page → Meta Boxes'],
    ['The order of sections on a page', 'Pages → that page → Section Order'],
    ['Content on all category pages', 'Packgens → Category Defaults'],
    ['One category only', 'Products → Categories → edit that category'],
    ['A product’s badge, tabs or cards', 'Products → that product → Product Details'],
    ['A product’s price or images', 'Products → that product → Product data'],
    ['Navigation and footer links', 'Appearance → Menus'],
    ['A blog post', 'Posts'],
    ['Your author photo', 'Users → Profile → Author photo'],
    ['Form submissions people have sent', 'Enquiries'],
    ['The logo', 'Appearance → Customize → Site Identity'],
], [180, CONTENT_W - 180])

story.append(PageBreak())

# =========================================================== 14. Q&A
h1('Questions and answers', '14')

h3('I saved a change but the site looks the same')
p('Refresh the page in your browser — hold Shift and press Reload to bypass the '
  'cache. If it still looks the same, check you saved the right screen: content that '
  'appears on one page lives on that page, and content that appears everywhere lives '
  'under Packgens.')

h3('I cleared a field and the old wording came back')
p('That is intended. Wording fields fall back to the site’s original text so an '
  'empty field never leaves a blank space. To change the wording, type the new '
  'wording. To remove a whole band, use its <b>Show this section</b> switch.')

h3('I changed one category and they all changed')
p('You were on <b>Category Defaults</b>, which all categories share. Undo it there, '
  'then make the change on the individual category under Products → Categories.')

h3('I changed Category Defaults and one category ignored it')
p('That category has its own value saved for that field, which overrides the '
  'default. Clear the field on the category and it will follow the default again.')

h3('The SCF → Field Groups screen is empty. Is something wrong?')
p('No. The fields are built into your website’s theme rather than stored in the '
  'database, which keeps them safe from accidental deletion and identical between '
  'your test and live sites. That screen only lists fields created through the admin. '
  'All of your fields are working — they appear on the screens in this guide.')

h3('Can I add a new section to a page?')
p('You can turn on any of the sections your site already has, using <b>Section '
  'Order</b> on that page. A brand-new kind of section — a layout that does not '
  'exist yet — needs a developer.')

h3('Can I break the site?')
p('Not from the screens in this guide. None of them change code. The worst outcome '
  'is wording you do not like, and clearing the field restores the original.')

note('<b>Before launch:</b> the Terms &amp; Conditions, Privacy Policy and Return '
     'Policy pages ship with placeholder wording written to be replaced. Have your '
     'own legal adviser supply the real text, then paste it into those three pages '
     'under <b>Pages</b>.', 'Important', colors.HexColor('#D92D20'))

story.append(Spacer(1, 14))
story.append(rule(CONTENT_W, LINE, 1))
story.append(Spacer(1, 8))
p('If you get stuck on something this guide does not cover, send a screenshot of '
  'the screen you are on and a note of what you were trying to change.',
  S('x2', parent=BodyS, textColor=MUTED))

doc.build(story)
print('written:', OUT)
print('size:', round(os.path.getsize(OUT) / 1024.0), 'KB')
