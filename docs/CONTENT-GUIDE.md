# Editing your Packgens website

Everything you can see on the site is editable from the WordPress admin. You
never need a developer to change wording, images, prices, links or the order of
sections on a page.

This guide is written for the person who keeps the site up to date. It assumes
no technical knowledge.

---

## The short version

| I want to change… | Go to |
|---|---|
| Phone number, email, address, social links | **Packgens → Site Settings** |
| The announcement bar at the very top | **Packgens → Site Settings → Announcement bar** |
| Wording on a form, in the footer, on the 404 page | **Packgens → Global Content** (the `Text:` tabs) |
| A section on a specific page | Edit that **page** and scroll below the editor |
| Content shared by every product category | **Packgens → Category Defaults** |
| One product's details | Edit that **product** |
| The menus | **Appearance → Menus** |
| A blog post | **Posts** |
| Enquiries people have sent | **Enquiries** |

After any change, press **Update** (pages, products) or **Save** (the Packgens
screens), then reload the front of the site to see it.

---

## 1. Site Settings

**Packgens → Site Settings**

Six tabs holding the details that appear all over the site. Change the phone
number here and it updates in the header, the footer, the contact page and the
sidebar quote card at once.

| Tab | What it holds |
|---|---|
| Announcement bar | The thin strip at the very top, its live-chat label and link. Switch the whole bar off with the toggle. |
| Header | The blue button in the header and its link, plus the "Free Shipping" note |
| Contact details | Phone, caption, second phone, WhatsApp, email, postal address, opening hours |
| Social profiles | Facebook, Instagram, LinkedIn, YouTube, X. Leave one blank to hide its icon. |
| Global links | Where the "Get a quote", "Reviews" and "All products" buttons point |
| Form handling | Who receives enquiry emails, the thank-you page, and the message shown after sending |

**Tip.** The address and opening-hours boxes keep your line breaks, so type them
on separate lines exactly as you want them to appear.

---

## 2. Global Content

**Packgens → Global Content**

Content and wording that is shared across many pages.

### Layout tabs

| Tab | What it holds |
|---|---|
| Article sidebar | The trust card beside a blog post: heading, rating, image, tick list |
| Header strip | The four icons under the header on pages without a hero (Free Artwork Check, No Setup Fees, …) |
| Trust strip | The blue band above the footer |
| Footer | Newsletter wording, payment and delivery logos, copyright line |
| Quote form | The "Get Instant Quote" form's title, intro, button and small print |

### `Text:` tabs

Every remaining word on the site that is not part of a page. Each field's grey
**placeholder shows what the site says today** — leave a field empty and that
wording is used, so you only fill in what you want to change.

| Tab | Covers |
|---|---|
| Text: forms | Every label and placeholder in the quote and contact forms, the file-upload text, the contact form title and button |
| Text: header | The two search box placeholders |
| Text: footer | "Instant Information", "Send Email", "Call Information", "Address", "Follow us" |
| Text: blog | Filter labels, sidebar headings, "Related Blogs", "Visit Our Blog", "Table of Contents", "Share This Article", "Read More" |
| Text: contact channels | "Call Us", "Send Email", "Message Us", "Office Address" |
| Text: quote card | The blue quote card in sidebars: title, text, phone label, button |
| Text: saved items | The message shown when someone's saved list is empty |
| Text: 404 and search | The "page not found" heading, text and buttons |

**Note.** The quote form and the contact form share these labels, so changing
"Full Name" changes it in both.

---

## 3. Page sections

Open any page (**Pages → All Pages**) and scroll below the main editor. You will
find a stack of panels — Hero, About, Why Choose Us, Process, Reviews, FAQs and
so on. These are the bands that make up the page.

Every section works the same way:

- **Enable** — a switch. Turn it off to hide that band without losing the
  content you typed.
- **Eyebrow** — the small label above the heading (not on every section)
- **Title**, **Description**
- Section-specific fields — cards, list items, images, tabs
- **Button** — the link at the bottom. Clear the link to hide the button.

Two more panels are worth knowing:

- **Section Order** lets you drag the bands into a different order on that page.
- Some sections appear twice (there are two call-to-action bands) so a page can
  end with a different message than it opened with.

### Repeaters

Where a section holds a list — cards, steps, questions, features — you get a
row of blocks with **Add** and a drag handle. Drag to reorder, click the minus
to remove, and use **Add** for a new one.

---

## 4. Product categories

This is the one that saves the most time.

There are 21 product categories, and they all share one set of content:
**Packgens → Category Defaults**. Edit the black call-to-action band there and
it changes on every category page at once.

When a category needs something of its own — usually its hero headline and the
heading above its product grid — edit that category directly
(**Products → Categories → edit**). Anything you type there overrides the
default *for that category only*. Anything you leave alone keeps following the
default.

So: **change it once in Category Defaults; override only the exceptions.**

---

## 5. Products

Edit a product (**Products → All Products**) and, below the usual WooCommerce
boxes, you will find **Product Details**:

- Badge ("Best Seller", "Best Price") shown on the card
- Price caption and delivery note
- The rating strip and offer/coupon strip
- The help card and the two feature cards
- The tabs beneath the product (description, specifications, artwork guidelines,
  FAQs)

Price, stock and images stay in WooCommerce's own panels.

---

## 6. Menus

**Appearance → Menus.** There are five:

| Menu | Where |
|---|---|
| Packgens Primary | The main navigation |
| Packgens Footer Information / Industries / Products / Legal | The four footer columns |

To make a top-level item open a large panel, open **Screen Options** at the top
right, tick **CSS Classes**, then add `mega` to that item. Add `cards` as well
to show its children as image cards, and `columns-3` (or 2–6) to set how many
columns the panel uses. Each item also gains a **Mega menu image** field.

---

## 7. Blog

Write posts under **Posts** as normal. Two theme extras:

- **Users → Profile → Author photo** sets the picture beside your byline,
  without needing Gravatar.
- The sidebar beside a post (contents, trust card, share links) comes from
  **Global Content → Article sidebar**.

---

## 8. Enquiries

Every quote, contact, newsletter and callback submission is saved under
**Enquiries** in the admin as well as emailed. If an email ever fails to arrive,
the enquiry is still here — nothing is lost.

Set the recipient address in **Packgens → Site Settings → Form handling**.

---

## 9. Saved items

Shoppers can tap the heart on any product to save it. The heart in the header
shows how many they have saved, and the list lives at `/wishlist/`.

Signed-in customers keep their list on their account. Guests keep it in their
browser for 30 days, and it moves onto their account when they sign in.

The message shown when the list is empty is under
**Global Content → Text: saved items**.

---

## Things to know

**Leave a field empty to use the default.** Nearly every wording field falls
back to what the site says today. An empty field never produces a blank space on
the page.

**Turn a section off rather than deleting its content.** The Enable switch keeps
your text safe for later.

**Images.** Upload through the normal media library. Photographs are best at
about 1600px wide; the theme resizes the rest.

**You cannot break the site from these screens.** Nothing here changes code. If
something looks wrong, clear the field and the original wording returns.

**Legal pages.** Terms & Conditions, Privacy Policy and Return Policy ship with
placeholder wording. Replace it with text from your own legal adviser before
launch — it is written to be replaced, not used as-is.

---

## Where the field groups live

If you go looking for **SCF → Field Groups** you will find it empty. That is
expected: the fields are defined in the theme's code rather than the database,
which keeps them safe from accidental deletion and identical between your
staging and live sites. They still appear on every screen listed above.
