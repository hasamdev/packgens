# Development notes

Decisions taken while building the Packgens theme, and where it deliberately
differs from the NexGen Boxes reference build or from the Figma file.

---

## 1. Where each source was authoritative

| Source | Used for |
|---|---|
| Packgens Figma file | Layout, type, colour, spacing, components, copy |
| nexgenboxes.com | Functionality, user flows, URL structure, content model |
| NexGen Boxes theme | Engineering reference: what to keep, what to improve |

No NexGen Boxes design, branding, or copy was carried over. All visual design
comes from the Figma file, and all page copy is either from the Figma or written
for this project.

### Reading the Figma file

The `.fig` file is a ZIP containing a Kiwi-encoded `canvas.fig`. It was decoded
directly (schema block via deflate, data block via Zstandard) to read exact
values rather than eyeball them, which is where the design tokens, spacing and
type scale in `base.css` come from. The 150 embedded images were extracted and
are the source of the demo media.

---

## 2. Deviations from the Figma

### 2.1 Heading typeface

The Figma sets headings in **Uber Move**, Uber's proprietary corporate typeface.
It cannot be licensed for a client website.

Headings use **Plus Jakarta Sans** (700/800), a geometric humanist sans with a
comparable weight and feel, self-hosted under the SIL Open Font License. Body
copy stays on **Inter**, as in the Figma.

Both are single tokens, so swapping in a licensed face is a two-line change:

```css
--pg-font-display: "Plus Jakarta Sans", ...;
--pg-font-body: "Inter", ...;
```

The Figma also uses Poppins, Manrope and DM Sans in places. These appear to be
leftovers from the template the file was built on rather than deliberate
choices, so they were consolidated onto the two-family system.

### 2.2 No mobile or tablet artboards

Every frame in the Figma is 1920px wide. There are no mobile designs.

Responsive behaviour was derived from the desktop layouts, the reference site's
behaviour, and standard practice. Verified at 390, 768, 1024, 1440 and 1920.

The most significant derived decision: **the header swaps to a drawer at 992px**,
not 768px. Between 768 and 991 the desktop header is too cramped — the search
field clips and the nav wraps to two lines.

### 2.3 No mega menu panel is designed

The Figma nav shows chevrons on "Industries" but contains no dropdown artboard.

The mega panel was built to match the design language (white panel, 20px radius,
large shadow, image cards on a light surface) using the reference site's
structure — a card grid of product categories with images. It is configurable
per menu item, so its shape can change without code.

### 2.4 Hero overlay on narrow screens

The hero uses the design's overlay verbatim:

```css
linear-gradient(270deg,
  rgba(0, 34, 180, 0.1045) 0%,
  rgba(0, 34, 180, 0.95) 27.36%,
  rgba(0, 34, 180, 0.95) 76.58%,
  rgba(0, 34, 180, 0.1045) 100%);
```

Those stops are measured against a 1920px artboard, where the opaque band is
about 945px wide. At 390px the same percentages leave a readable band of only
~190px, with the headline running over bare photography. Below 992px the ramp
therefore tightens to 8% / 92%, keeping the design's soft edges while holding
the copy on a solid ground. White on the resulting band measures well above
4.5:1.

### 2.5 Swiper rather than the native scroll row

The sliders were first built as native scroll-snap rows: no dependency, and
usable before any JavaScript ran. Swiper was requested instead, so the carousels
now initialise on Swiper 11 and the scroll-snap layout survives underneath as
the pre-init and no-JS state (`:not(.swiper-initialized)`).

Two things this costs, recorded so the trade is visible:

- **Weight.** ~154KB JS and ~18KB CSS, conditionally loaded. That is more than
  the rest of the theme's JavaScript put together.
- **Stylesheet order.** Swiper's base CSS uses single-class selectors that
  collide with the theme's (`.swiper` sets `padding: 0`, `.swiper-button-lock`
  sets `display: none`). Swiper's stylesheet is therefore enqueued *before* the
  theme's, so theme rules win on equal specificity, and the two rules that must
  beat Swiper regardless are written at two classes deep.

Slide spacing is read from the track's computed `column-gap` at init rather than
passed as a number, so `--pg-grid-gap` stays the single source of truth.

`packgens_carousel_open()` has since grown `loop`, `effect: fade`, `autoplay`,
`group` (one pair of arrows driving several rows) and `stagger` (offset a row by
half a card). The options are serialised into `data-pg-swiper` by hand rather
than with `wp_json_encode()`, because float slide counts round badly — 2.2
serialises as `2.2000000000000002`.

### 2.6 Section heights

Rendered page heights land close to the Figma frames (homepage 13,289px against
the Figma's 13,289px), but section padding is fluid (`clamp()`) rather than
fixed, so individual sections vary by a few pixels at 1920px and scale properly
below it. This is intentional: pixel-locked spacing does not survive a
responsive build.

### 2.7 A CSS marquee, not Swiper autoplay, for the reviews rows

The two review rows drift continuously in opposite directions. Swiper was tried
first and could not do it smoothly: even at `delay: 0` its autoplay restarts a
transition per slide, and `loop` visibly jumps at the seam.

Those rows therefore opt out of Swiper (`auto => 'forward' | 'reverse'`) and
render a CSS marquee: the slides are printed twice and the track translates by
half its width on a `linear infinite` keyframe, which has no seam to jump.
`assets/js/marquee.js` measures the track and derives the duration from a fixed
px/second speed, so rows of different lengths move at the same pace. The arrows
nudge the row by one card and wind it back after the transition.

The duplicated copy is `aria-hidden` and its controls are removed from the tab
order, so the row is announced and tabbed once. The animation is suppressed
under `prefers-reduced-motion`.

### 2.8 `:has()` for the chat bar

The chat bar tucks into the bottom of the long-form card above it: the card
reserves the bar's height plus an inset, and the bar is pulled back up into it.
That reservation has to be applied to the *preceding* section, which is what
`:has(+ .pg-section--chat)` does.

`:has()` is supported by every current browser but not by older Safari. The
fallback is benign — the bar simply sits below the card as its own band, which
is also what happens under 992px, where the bar wraps to two rows and a fixed
reservation would overflow the card.

---

## 3. Deviations from the reference build

### 3.1 Forms are native, not Contact Form 7

The reference site runs every form through Contact Form 7. This theme implements
them directly (`inc/forms.php`).

Reasons: full control of the markup so it matches the Figma exactly, no plugin
dependency, and submissions are archived as posts so a mail failure never loses
a lead. The reference build emailed only.

Trade-off: the field set is defined in code (`packgens_form_schema()`) rather
than editable in an admin screen. Labels, categories, success text and the
recipient are all editable; adding a *new field* is a code change. Given the
forms are fixed by the design, this is the right trade.

### 3.2 Post types are registered in code

The reference build registers `customer` and `material` through the Secure
Custom Fields UI, which puts the content model in the database. Here they are
registered in `inc/post-types.php` and the field groups in `inc/fields.php`, so
the model is in version control and a fresh install matches production.

### 3.3 Field group naming collision fixed

The reference model uses the name `reviews_section` for two different things:
the page-level reviews block and the per-testimonial fields. Because the field
API resolves groups by name, writing one can silently resolve to the other.

Here the testimonial group is named `testimonial`. The sort meta key changed
accordingly, from `reviews_section_date` to `testimonial_date` — worth knowing
if testimonial data is ever migrated from the reference site.

### 3.4 No Bootstrap, no jQuery, no Select2, no Owl Carousel

The reference theme loads Bootstrap, jQuery, Swiper, Select2, Owl Carousel and
lazysizes. Only Swiper is carried over, and only where a slider renders:

- **Layout** — CSS Grid and Flexbox.
- **Carousels** — Swiper 11, vendored and conditionally loaded (see §2.5), over
  a CSS scroll-snap row that works before the script runs. Continuous rows use
  a CSS marquee instead (see §2.7).
- **Lazy loading** — native `loading="lazy"`.
- **Selects** — native `<select>`, styled.

That still removes roughly 250KB of JavaScript from every page relative to the
reference build, and nothing at all on templates without a slider.

### 3.5 Structural clean-up

Problems in the reference theme that were deliberately not reproduced: a 47KB
`functions.php`, a 46KB `footer.php`, a 62KB `inc/woocommerce.php`, duplicate
function definitions across `functions.php` and `inc/security.php`
(`nexgenboxes_disable_autosave` and `nexgenboxes_performance_optimizations` were
each declared twice), and `- bk.php` / `- old.php` leftovers.

### 3.6 WooCommerce via hooks

Only four plugin templates are overridden: `archive-product`, `content-product`,
`single-product`, `content-single-product`. Everything else is rewired with
actions and filters in `inc/integrations/woocommerce.php`, which keeps the theme
upgrade-safe.

The module only loads when WooCommerce is active, so the theme activates cleanly
without it.

### 3.7 The long-form section lost its Read more control

The content section could collapse behind a Read more button. The design has no
such control, so the toggle, its field and the `setupCollapse` half of
`accordion.js` were removed rather than left as unreachable code. Long-form copy
now always renders in full, which is also better for crawlers.

### 3.8 Author photos do not depend on Gravatar

Post bylines called `get_avatar()`, which needs a network round trip to
gravatar.com and renders nothing on an offline or intranet install. A media
library ID on the user profile now takes precedence, via
`packgens_author_avatar()`, falling back to Gravatar when it is not set.

---

## 4. Security decisions

- Every AJAX and admin-post handler verifies a nonce; capability checks are used
  where an action is privileged.
- The load-more endpoint never accepts raw query arguments. It takes a
  **HMAC-signed** payload of a whitelisted subset (`packgens_pack_query()` /
  `packgens_unpack_query()`), so a client cannot inject arbitrary `WP_Query`
  arguments.
- Uploads are validated with `wp_check_filetype_and_ext()` (content, not just
  extension), size-capped, and stored as private attachments.
- SVG uploads stay disabled except for users who already have
  `unfiltered_html`. The reference build allowed SVG uploads for everyone,
  including bypassing WordPress's unsupported-MIME protection.
- The `wp/v2/users` REST route and `?author=N` enumeration are blocked for
  anonymous visitors. The rest of the REST API is untouched.
- A conservative set of security headers is sent. HSTS and CSP are deliberately
  left to server configuration.

---

## 5. Performance decisions

- CSS and JS are attached per view (`packgens_view_context()`), so the homepage
  loads no product CSS and a blog post loads no catalogue CSS.
- Fonts are self-hosted variable WOFF2, subset to latin and latin-ext,
  preloaded. ~190KB, no third-party requests.
- Icons are inline SVG: no sprite request, no icon font, and they inherit
  `currentColor`.
- The hero image is preloaded with `fetchpriority="high"`, and the first
  in-content image is excluded from lazy loading.
- Emoji scripts, block library CSS on non-block pages, and the usual `wp_head`
  noise are removed.

No minification or concatenation step is included. With HTTP/2 the benefit is
small and it would add a build step to a theme that otherwise has none. If a
build is wanted later, the file structure is already split for it.

---

## 6. Accessibility

Verified across all 13 page types: exactly one `h1` per page, no skipped
heading levels, every image has an `alt`, every form control has a label or
`aria-label`, every icon-only button has a screen-reader name, and the
`header` / `main` / `footer` / `nav` landmarks are present.

Also implemented: a skip link, focus-visible outlines, focus trapping in the
drawer and lightbox, arrow-key support in accordions, tabs and the search
combobox, `prefers-reduced-motion` handling, and `aria-live` regions for form
results.

### Colour contrast

Every token pair in use was measured against WCAG 2.1 AA. Two failures came
from the Figma palette and were corrected:

| Pair | Figma | Fixed |
|---|---|---|
| White text on the green band `#1eba88` | 2.49:1 | `#12805f` at **4.91:1** |
| Empty rating star `#d0d5dd` on white | 1.47:1 | `#8892a3` at **3.14:1** |

The mint green is still used, unchanged, for accents where it carries no text:
tick marks, success states and badges (`--pg-green`). Only surfaces that hold
text use the deeper `--pg-green-band`. Text on that band is full white rather
than the 82% white used on the blue and navy bands, which would drop to 3.48:1.

**This is a visible change from the design and is worth confirming with the
designer.** Reverting is a one-line change in `base.css`; the accessibility
consequence is documented here so the decision is explicit.

Everything else passes comfortably: body text 9.15:1, muted text 4.55–4.97:1
on every surface it appears on, headings 17.3:1, links 8.83–9.54:1, white on
blue 11.12:1, white on navy 14.53:1.

---

## 7. Demo content and media

`tools/seed-demo.php` seeds a complete site from the images embedded in the
Figma file.

**Before launch, replace the demo media.** Several mockups extracted from the
Figma carry third-party branding baked into the photograph — including "NexGen
Boxes" on two product mockups, and brand marks such as Johnny Cupcakes, graze,
Deliveroo and L'Oréal. They are fine for a design preview but must not ship as
Packgens marketing imagery. The Trustpilot and Google review badges are likewise
placeholders and should only be used once the accounts genuinely exist.

The demo copy is written for this project and is safe to keep or edit.

---

## 8. Editable interface text

### 8.1 One helper, defaults left in the templates

The brief asked for a site the client edits without a developer. Section content
was already field-driven, but around 85 visible strings were still `esc_html_e()`
calls: every form label and placeholder, the footer column headings, the blog
headings, the 404 page.

Each now reads through one accessor:

```php
packgens_label( 'forms', 'name_label', __( 'Full Name', 'packgens' ) );
```

The English wording stays in the template as the third argument rather than
moving into a field `default_value`. Three reasons:

1. **A blank field cannot blank the page.** The fallback is structural, not a
   value someone can clear.
2. **The theme survives SCF being deactivated.** `packgens_field()` returns the
   default when the field API is absent, so the site renders in English rather
   than with holes.
3. **No duplication to drift.** Putting the same string in a `default_value`
   *and* the template means two copies that can disagree. The admin field's
   *placeholder* shows the default instead, which is display-only.

Field groups are named `labels_{group}` and live on tabs prefixed `Text:` so
they sort together and stay distinct from layout content.

### 8.2 What deliberately stayed in code

About 40 strings remain: screen-reader-only spans, `aria-label`s, the spam
honeypot's label, and `register_nav_menus()` location names. They are invisible
plumbing. Exposing them would add 40 fields of text nobody sees and make the
screens that matter harder to scan.

Two exceptions were wired anyway, because they are read aloud rather than
decorative: the blog sidebar's search placeholder (visible), and the quote
form's Length/Width/Height `aria-label`s, which now follow their placeholder
fields so a screen reader announces whatever the client typed.

### 8.3 The Customizer panel was removed

Contact details, social profiles, the announcement bar and the header CTA lived
in `theme_mods` behind **Appearance → Customize**. Everything else lived under
**Packgens**. Two screens for one job is a support burden, and a client will not
think to look in the second one.

The schema in `theme-options.php` is unchanged and still the single source of
truth for keys, types and defaults; `packgens_site_option_fields()` renders it
as SCF tabs on a **Site Settings** sub-page. `packgens_get_option()` reads the
field first and falls back to the old `theme_mod`, so values written before the
move are still found even if the migration is never run.

Kept: **Site Identity → Logo**, which is core and where people expect it.

---

## 9. Saved items without a plugin

A wishlist is a handful of product IDs. The available plugins bring their own
templates, stylesheet and settings screen, all of which would have to be fought
back into the theme's design — more work than writing it, and a permanent
upgrade risk.

`inc/integrations/wishlist.php` is about 200 lines: user meta when signed in, a
30-day cookie for guests, a merge on `wp_login`, one nonce-checked AJAX action,
a button helper and a header count.

Two things worth keeping if this is refactored:

- **The toggles are serialised in JS.** For guests the list is a cookie, so two
  requests in flight both read the pre-click value and the second undoes the
  first. `wishlist.js` chains them through one promise.
- **The count is a cart fragment.** It re-renders through
  `woocommerce_add_to_cart_fragments` so a cached page does not show a stale
  number.

---

## 10. WooCommerce blocks

Cart and Checkout are the block versions, not the shortcodes. Styling them meant
three specific fights worth recording.

### 10.1 Notices were never printed

The theme's archive and single-product templates do not fire
`woocommerce_before_shop_loop` or `woocommerce_before_single_product`, which is
where WooCommerce hooks `woocommerce_output_all_notices`. "Added to your cart"
and every error were invisible on the shop, the category pages and the product
pages. `packgens_wc_notices()` prints them on `woocommerce_before_main_content`,
which both templates do fire. The buffer is cleared on output, so pages that do
fire the original hooks cannot double up.

Two display bugs went with it: WooCommerce positions its `::before` tick
absolutely, which drops it on top of the message once the notice becomes a flex
row; and the notice is moved into focus for screen readers, so the page's 3px
focus ring drew a blue box that read as an error state.

### 10.2 The sidebar wrapped

`.wc-block-components-sidebar-layout` is a flex row whose columns are sized to a
hard 65% / 35%. Adding a `gap` pushes the row past 100% and the sidebar wraps
underneath. The fix is `flex-wrap: nowrap` plus `min-width: 0`, letting the
columns shrink into the gap.

### 10.3 Specificity, not `!important`

The plugin's field rule is
`.wc-block-components-form .wc-block-components-text-input input[type=text]` —
three classes and an element. Theme rules written one class deep silently lost.
`woocommerce.css` matches that weight instead and wins on load order, and the
places where it matters carry a comment saying so, because they look
over-specified otherwise.

The payment method rows are the same story in reverse: the plugin marks the
chosen method with an inset ring in `currentColor`, which inherited the theme's
body grey and read as a disabled state rather than a selection.

---

## 11. Site-wide fixes found late

Two bugs that had been present since early on and were only exposed by later
work. Both are one-line, and both are easy to reintroduce.

### 11.1 `overflow-x: hidden` on `body` breaks every sticky element

Sticky sidebars on the blog, single posts and the FAQ page had never worked. An
element with `overflow` other than `visible` becomes the scroll container that
`position: sticky` descendants are measured against, so nothing inside can
stick. `body` now uses `overflow-x: clip`, which contains full-bleed rows
without creating a scroll container.

### 11.2 A class-level `display` outranks `[hidden]`

`.pg-wishlist-empty { display: grid }` and the `hidden` attribute have the same
specificity, so the later rule won and the empty state showed alongside saved
products. Any component that toggles with `hidden` and sets its own `display`
needs `.thing[hidden] { display: none }` spelled out.

---

## 12. Known limitations

1. **The green band tone was darkened for contrast** (see §6) — confirm with
   the designer.
2. **No mobile design reference** — responsive layouts are derived, not
   specified. Worth a design review.
3. **Cart, checkout and account use WooCommerce's own markup**, restyled with
   the theme's tokens (see §10). They were not in the Figma, so they were not
   rebuilt. Cart and Checkout are the block versions; My Account is the classic
   shortcode.
4. **The mega menu is a derived component** (see §2.3).
5. **Cross-browser testing is Chromium-only.** Layout uses widely supported
   features (Grid, custom properties, `clamp()`, `:is()`, `aspect-ratio`,
   scroll-snap), but Safari and Firefox have not been checked on real devices.
   `grid-template-rows: 0fr → 1fr` accordion animation needs Safari 16+; it
   degrades to an instant open on older versions.
6. **The quote form's field set is code-defined** (see §3.1).
7. **No automated test suite.** Verification was manual: PHP lint across 92
   files, JS syntax checks, browser console checks per template, a
   markup/accessibility audit script, form endpoint tests (valid, invalid,
   honeypot, rate limit, bad nonce) and screenshot comparison against the Figma
   frames at several viewport widths.
8. **The Google review mark is missing from the icon set.** The Trustpilot badge
   draws its logo; the Google badge renders the wordmark only, because no
   `google.svg` was supplied. Dropping the file into `assets/icons/` is enough —
   the markup already asks for it.
9. **Two supplied icons are raster images inside an SVG wrapper**
   (`step-artwork`, `why-price`). They will not stay sharp on high-DPI screens
   and cannot take a colour. Vector re-exports would fix both.
10. **Demo contact details and logos still need replacing.** The footer carries
   placeholder phone numbers, email, social profiles and payment marks; the
   Figma shows a different set (Google+, Apple Pay, G Pay). These are all
   options content, not code — **Packgens → Site Settings**.
11. **The legal pages ship placeholder wording.** Terms, Privacy and Return
   Policy are structured drafts written to be replaced by the client's own
   adviser, not used as they stand.
12. **Four product categories were created to give the footer links somewhere to
   go** (Cosmetic, Food, Candle, Automotive Boxes). The products assigned to
   them are the closest fits in the demo catalogue, not a real merchandising
   decision.
13. **No payment gateway is configured.** Checkout lists the offline methods
   WooCommerce ships with; a live gateway is a store setting, not a theme one.

---

## 13. Possible next steps

- Supply `google.svg` and vector re-exports of the two raster icons.
- Confirm the darkened green band with the designer.
- Mobile design review with the designer.
- Real Safari and Firefox testing.
- A `wp_cache`-backed transient layer for the category and materials queries if
  the catalogue grows past a few hundred products.
- Product-level structured data enrichment beyond the specification table.
- If a build step is ever introduced, the CSS and JS are already split into
  sensible bundles.
- Replace the placeholder legal copy and the four stand-in category assignments.
- Decide whether the "Top featured products" variant from the early brief is
  still wanted; it was never built.
