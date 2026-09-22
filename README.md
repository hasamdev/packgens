# Packgens

A custom WordPress theme for Packgens: a custom-packaging catalogue with quote
requests, WooCommerce commerce, testimonials, materials and a blog.

Built from the Packgens Figma design, reproducing the functionality of the
NexGen Boxes reference build on a new, modular architecture.

---

## Requirements

| | |
|---|---|
| WordPress | 6.0 or newer (tested on 7.1) |
| PHP | 7.4 or newer (developed on 8.2) |
| WooCommerce | required for the shop, product and category templates |
| Secure Custom Fields | required for all editable section content |

**Secure Custom Fields** (the WordPress.org fork of ACF) provides the field API.
The theme registers every field group in PHP, so nothing needs importing — just
activate the plugin. Field types used include repeater, group, gallery, link,
relationship, taxonomy and options pages, all of which SCF includes.

The theme degrades rather than fatals when either plugin is missing: section
content falls back to the wording in the templates and shop templates are
skipped. In practice **Secure Custom Fields is required** — around 100 call
sites read fields, and without it the site renders its structure with almost no
content.

> **Why is SCF → Field Groups empty?** Every group is registered from PHP with
> `acf_add_local_field_group()`, and that admin list only shows groups stored in
> the database. The fields are live — they appear on the page, product, category
> and options screens. Keeping them in code means they are version-controlled,
> deploy with the theme and cannot be deleted from the admin by accident.

---

## Installation

1. Copy the theme into `wp-content/themes/packgens`.
2. Activate **Packgens** under Appearance → Themes.
3. Install and activate **WooCommerce** and **Secure Custom Fields**.
4. Go to Settings → Permalinks and press Save to flush rewrite rules.
5. Optional: seed demo content (see *Demo content* below).

---

## Theme architecture

```
packgens/
├── functions.php              Bootstrap only; loads inc/ modules in order
├── style.css                  Theme header (all CSS lives in assets/css)
│
├── inc/
│   ├── helpers.php            Field accessors, icons, images, small view helpers
│   ├── setup.php              Theme supports, menus, image sizes, widget areas
│   ├── enqueue.php            Conditional CSS/JS registration
│   ├── post-types.php         customer + material post types and taxonomies
│   ├── fields.php             Secure Custom Fields local field groups
│   ├── theme-options.php      Site settings schema (contact, social, top bar, CTAs)
│   ├── nav-walkers.php        Desktop mega menu + mobile drawer walkers
│   ├── menu-fields.php        Per-menu-item image field for mega menu cards
│   ├── template-functions.php Body classes, breadcrumbs, pagination, sharing
│   ├── queries.php            Query helpers for the section templates
│   ├── sections.php           Section renderer and section header helpers
│   ├── forms.php              Native AJAX forms, validation, enquiry storage
│   ├── ajax.php               Predictive search + load-more endpoints
│   ├── security.php           Headers, enumeration blocking, upload rules
│   ├── seo.php                Schema.org output and SEO plugin compatibility
│   ├── performance.php        Asset trimming, preloads, resource hints
│   └── integrations/
│       ├── woocommerce.php    Loaded only when WooCommerce is active
│       └── wishlist.php       Saved items: storage, AJAX, buttons
│
├── template-parts/
│   ├── header/                topbar, utility row, nav row, drawer, cart, brand, strip
│   ├── footer/                usp strip, brand column, links, social, payments, bottom
│   ├── sections/              20 reusable page sections
│   ├── cards/                 product, category, post, review, industry, material
│   ├── components/            page header, quote card, no results
│   ├── forms/                 quote, contact, newsletter, product search
│   ├── post/                  filters, sidebar, related, aside (single-post rail)
│   └── product/               gallery, summary, support, tabs, related
│
├── templates/                 about, contact, faq, industries, legal, reviews, wishlist
├── woocommerce/               archive-product, content-product, single-product
├── assets/{css,js,fonts,icons,images}
├── assets/vendor/swiper/        Swiper 11 (MIT), vendored — no CDN
└── tools/                     asset + demo-content scripts (not loaded at runtime)
```

### Loading order

`functions.php` loads `inc/` modules in a fixed order — `helpers` first, since
everything depends on its field accessors. Add new behaviour to a module, or add
a module to the list; do not add logic to `functions.php`.

---

## Content architecture

### Post types

| Type | Purpose | Taxonomy |
|---|---|---|
| `product` | WooCommerce catalogue | `product_cat` |
| `customer` | Testimonials shown in the reviews sections | `category_customers` |
| `material` | Board, print, finish and coating options | `category_materials` |
| `pg_enquiry` | Archived form submissions (admin only, not public) | — |
| `post` | Blog | `category`, `post_tag` |

`customer` and `material` are building blocks rather than pages: hitting one
directly redirects, and they are marked `noindex`.

### Sections

Pages, the front page, product categories and single products all compose the
same section library. Each section is a field group named `{slug}_section` with
a consistent shape:

```
{slug}_section
├── enable         toggle (defaults to on)
├── eyebrow        optional small label above the title
├── title
├── description
├── ...section-specific fields
└── button         link
```

Available sections: `hero`, `categories`, `about`, `showcase`, `cta`,
`products`, `promo`, `process`, `why`, `comparison`, `cta_alt`, `content`,
`chat`, `stats`, `industries`, `faqs`, `brands`, `reviews`, `blogs`, plus
`materials` on the inner pages.

`cta_alt` is the same component as `cta` with its own content, so a page can
carry two call-to-action bands.

Order comes from `packgens_default_section_order()` per view type and can be
overridden per page with the **Section Order** field group.

### Product category defaults

Every product category renders one template (`woocommerce/archive-product.php`)
and one section library, so there is nothing to keep in step between them. The
content is shared the same way: **Packgens → Category Defaults** (SCF options
page, `PACKGENS_CAT_DEFAULTS_ID`) carries a full set of sections, and each
category falls back to it.

The merge is per sub-field, not per section, so a category stores only what it
wants to differ — typically its hero headline and the heading above the grid —
and inherits the rest. Edit the black CTA band on the defaults screen and every
category changes at once; override it on one term and only that term changes.

Whether a term "has" a value is read from its stored meta rows rather than
through `get_field()`, because SCF fills anything unsaved with the field's own
`default_value`: an untouched **Layout** select reads as `center`, not as
absent. See `packgens_stored_section_keys()` in `inc/sections.php`.

To add a section: add a field group in `inc/fields.php`, a template part in
`template-parts/sections/`, and an entry in `packgens_section_choices()`.

### Global content

Everything an editor touches lives under the **Packgens** menu. There is no
theme content in the Customizer.

| Screen | Holds |
|---|---|
| **Packgens → Global Content** | 13 tabs: article sidebar, header strip, trust strip, footer, quote form, and the interface text below |
| **Packgens → Site Settings** | Contact details, social profiles, announcement bar, header CTA, global links, form handling |
| **Packgens → Category Defaults** | The section content every product category falls back to |

### Interface text

Every visible string outside the section fields — form labels and placeholders,
footer column headings, blog headings, the 404 page, the saved-items empty state
— is editable under **Global Content**, on the tabs prefixed `Text:`.

Templates read them through one helper:

```php
packgens_label( 'forms', 'name_label', __( 'Full Name', 'packgens' ) );
packgens_the_label( 'forms', 'name_label', __( 'Full Name', 'packgens' ) );  // echoes
```

The English default stays in the template as the third argument, so a blank
field renders the original wording and the page still works with SCF switched
off. Field group names are `labels_{group}`; the admin field's *placeholder*
shows the default so an editor can see what an empty field will produce.

Screen-reader-only labels, `aria-label`s and the spam honeypot stay as
translatable code strings — about 40 of them. They are invisible plumbing, and
fields full of text nobody sees make the admin harder to use, not easier.

**Users → Profile → Author photo** takes a media library ID used on post
bylines. `packgens_author_avatar()` prefers it and falls back to `get_avatar()`,
so bylines work on installs that do not use Gravatar.

### Menus

| Location | Notes |
|---|---|
| Primary | Supports mega panels |
| Footer: Information / Industries / Products / Legal | Single level |

Add the CSS class `mega` to a top-level menu item for a mega panel, `cards` to
render children as image cards, and `columns-2` … `columns-6` for the grid. Each
menu item gains a **Mega menu image** field.

---

## Forms

Native to the theme, no form plugin. Types: `quote`, `contact`, `newsletter`,
`callback`, defined in `packgens_form_schema()`.

Every submission is:

1. nonce checked (`packgens_public`),
2. screened by a honeypot field and a 20-second per-IP rate limit,
3. validated and sanitised field by field,
4. stored as a private `pg_enquiry` post (so nothing is lost if mail fails),
5. emailed to the address in Packgens → Site Settings → Form handling.

Artwork uploads are capped at 10MB and restricted to JPG, PNG, GIF, WEBP, PDF,
AI, EPS, PSD and ZIP, verified with `wp_check_filetype_and_ext()`.

Both endpoints are registered — `wp_ajax_packgens_submit_form` and
`admin_post_packgens_submit_form` — so the forms work with JavaScript disabled.

Read submissions under **Enquiries** in the admin.

---

## Saved items

A wishlist without a plugin: the list is a handful of product IDs, and a plugin
would bring its own markup and stylesheet to argue with the theme.

| | |
|---|---|
| Signed in | `packgens_wishlist` user meta |
| Guest | `packgens_wishlist` cookie, 30 days |
| On sign-in | The guest list merges into the account, then the cookie is cleared |

`inc/integrations/wishlist.php` owns storage, the `packgens_wishlist` AJAX
toggle (nonce-checked), the heart button and the header count. The page is the
**Saved Items** template at `/wishlist/`, and `packgens_wishlist_url()` finds it.

Toggles are serialised in `assets/js/wishlist.js`: for guests the list is a
cookie, so two requests in flight would both read the pre-click value and the
second would undo the first.

---

## Carousels

Sliders run on [Swiper](https://swiperjs.com) 11 (MIT), vendored at
`assets/vendor/swiper/` so the theme ships self-contained — no CDN. The bundle
(~154KB JS, ~18KB CSS) loads only on templates that actually render a slider.

Markup comes from three helpers in `inc/sections.php`:

```php
packgens_carousel_open( $id, array(
    'class'    => 'pg-products__track',
    'bleed'    => true,                                  // slides run past the container
    'label'    => __( 'Products', 'packgens' ),
    'per_view' => array( 0 => 1.2, 768 => 3, 1200 => 4 ), // min-width => slides per view
    'loop'     => true,                                  // wrap instead of stopping
    'effect'   => 'fade',                                // cross-fade in place (hero)
    'autoplay' => 6000,                                  // ms between slides, 0 for none
    'group'    => 'pg-reviews-1',                        // one pair of arrows, several rows
    'stagger'  => true,                                  // offset this row by half a card
    'auto'     => 'forward',                             // continuous drift, see Marquee
) );

packgens_carousel_slide( 'template-parts/cards/product', null, array( 'id' => $id ) );

packgens_carousel_close();
```

`packgens_carousel_nav( $id )` prints the arrows; Swiper binds them by id and
marks them disabled at the ends, or when nothing overflows.

`per_view` is serialised into `data-pg-swiper` and expanded into Swiper's
`breakpoints` by `assets/js/carousels.js`. Slide spacing is read from the
track's computed `column-gap`, so it stays driven by `--pg-grid-gap` rather than
being hard-coded in JS.

Before Swiper initialises — and if the script never loads — the track is a
native scroll-snap row, so the cards stay reachable by touch, trackpad and
keyboard. Those rules are scoped to `:not(.swiper-initialized)`.

### Marquee rows

Passing `auto => 'forward'` or `'reverse'` opts a track out of Swiper entirely
and renders a CSS marquee instead: the slides are printed twice inside
`.pg-marquee__track` and translated by half the track width on a `linear
infinite` keyframe, so the loop has no seam. `assets/js/marquee.js` measures the
track and sets the duration from a constant speed, and the arrows nudge the row
by one card. The duplicate copy is `aria-hidden` with its controls taken out of
the tab order.

This is what the two reviews rows use, drifting in opposite directions. Swiper's
own autoplay was tried first and could not do it smoothly: at `delay: 0` it
still restarts a transition per slide, and `loop` jumps at the seam. The
animation is suppressed under `prefers-reduced-motion`.

---

## CSS architecture

All CSS is hand-written with custom properties. No build step, no preprocessor.

| File | Loaded |
|---|---|
| `fonts.css` | always (self-hosted variable fonts) |
| `base.css` | always — design tokens, reset, typography, layout primitives |
| `components.css` | always — buttons, badges, forms, cards, accordion, tabs |
| `header.css`, `footer.css`, `sections.css` | always |
| `home.css` | front page |
| `catalog.css` | shop, category, tag, search |
| `product.css` | single product |
| `blog.css` | blog archive and single post |
| `page.css` | pages, contact, FAQ, reviews, legal, 404 |
| `woocommerce.css` | whenever WooCommerce is active — notices, cart, checkout, account, saved items |
| `editor.css`, `admin.css` | block editor / admin |

Design tokens live in `:root` in `base.css`, taken from the Figma file:

```
Primary   #0022b4    Vivid   #0027d0    Form CTA  #0834f1
Navy      #012c44    Green   #1eba88    Cream     #f6f6ec
Ink       #191b1b    Body    #484848    Muted     #667085
Container 1320px     Pill radius 100px  Card radii 40/34/30/20px
```

Breakpoints: 560, 768, **992** (header swaps to the drawer), 1100, 1200, 1280.

---

## JavaScript modules

Vanilla, no jQuery, no framework, no bundler. Every file is deferred and wrapped
in an IIFE; the only global is `window.Packgens`.

| File | Loaded | Purpose |
|---|---|---|
| `app.js` | always | Shared helpers: `$`, `$$`, `ready`, `debounce`, `post`, `trapFocus` |
| `navigation.js` | always | Sticky header, mega menu, drawer, mobile search |
| `search.js` | always | Predictive product search with keyboard support |
| `accordion.js` | always | FAQ accordions |
| `forms.js` | always | AJAX submission, inline errors, file name display |
| `tabs.js` | conditional | ARIA tabs (materials, product details, process steps) |
| `carousels.js` | conditional | Swiper setup: breakpoints, fade, loop, autoplay, grouped arrows |
| `marquee.js` | conditional | Continuous CSS marquee rows (reviews) |
| `archive.js` | conditional | Load-more for blog, catalogue and reviews |
| `product.js` | product | Gallery, lightbox, quantity tiers |
| `wishlist.js` | WooCommerce | Saved-items toggle, header count |

Carousels fall back to CSS scroll-snap containers, so they work by touch and
keyboard before the script runs. The load-more button is a real link to page 2.

The process section is an ARIA tablist whose panels are the illustrations beside
it, so clicking a step swaps the image with no section-specific script.

---

## Branding

The Packgens mark ships with the theme at `assets/images/logo.svg` and is
inlined as SVG, so it stays crisp at any size and costs no extra request.

Precedence in `template-parts/header/branding.php`:

1. the Customizer custom logo, if one is set (Appearance → Customize → Site
   Identity) — lets a client swap the mark without touching files;
2. the theme's `assets/images/logo.svg`;
3. the site title as text.

Replacing the file swaps the mark everywhere: header, mobile drawer and footer.

---

## Fonts and icons

Fonts are **self-hosted** variable WOFF2 (Inter + Plus Jakarta Sans, SIL OFL),
about 190KB total, preloaded, with no external requests.

Regenerate after changing weights:

```bash
python tools/fonts.py .
python tools/fonts_dedupe.py .
```

Icons are inline SVG in `assets/icons/`, echoed by `packgens_icon()` so they
inherit `currentColor`. A few supplied by the designer are two-tone and keep
their own fills (`why-*`, `stat-*`, `brand-tile`); those are sized explicitly in
CSS because they carry no `width`/`height` of their own. Regenerate or extend
the single-colour set with:

```bash
python tools/make_icons.py assets/icons
```

Icon names are offered to editors automatically wherever a field uses
`packgens_icon_choices()`.

---

## Demo content

`tools/seed-demo.php` builds a complete site: pages, menus, product categories,
products, testimonials, materials, blog posts, Customizer settings and all
section content.

```bash
php wp-content/themes/packgens/tools/seed-demo.php --images=/path/to/images --url=example.test
php wp-content/themes/packgens/tools/seed-demo.php --reset
```

Everything it creates carries a `_packgens_demo` meta flag, so `--reset` removes
exactly what it made. Run it only on a fresh or staging install.

---

## Development

There is no build step. Edit the CSS/JS in `assets/` and reload.

With `WP_DEBUG` on, asset versions use `filemtime()` so nothing is cached
between edits.

Useful checks before committing:

```bash
find . -name "*.php" -exec php -l {} \;
```

---

## Deployment

1. Deploy the theme directory; there is nothing to compile.
2. Confirm WooCommerce and Secure Custom Fields are active.
3. Flush permalinks once.
4. Fill in **Packgens → Site Settings** (phone, email, address, socials).
5. Populate **Packgens → Global Content** for the strips, footer and interface text.
6. Populate **Packgens → Category Defaults** so every category has content.
7. Assign the five menus.
8. Leave `WP_DEBUG` off in production so asset versions use the theme version.

Server-level items intentionally left out of the theme: HSTS, a
Content-Security-Policy, and object caching. These depend on hosting and belong
in server configuration.

---

## Documentation

| File | Audience |
|---|---|
| `README.md` | Developers: architecture, conventions, deployment |
| `DEVELOPMENT_NOTES.md` | Developers: decisions, deviations, known limitations |
| `docs/CONTENT-GUIDE.md` | The client: where to edit every part of the site |
| `docs/TROUBLESHOOTING.md` | Both: symptoms, causes and fixes |
| `docs/Packgens-Website-Editor-Guide.pdf` | The client: the same guide as a printable 19-page PDF with admin screenshots |

Rebuild the PDF after an admin change with:

```bash
python tools/build-client-guide.py
```

Its screenshots live in `tools/guide-assets/`; recapture them from a signed-in
admin session if a screen changes.

---

## Known limitations

See `DEVELOPMENT_NOTES.md` for the full list and the reasoning behind the
architectural decisions.
