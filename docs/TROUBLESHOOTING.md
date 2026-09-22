# Troubleshooting

Symptoms in the order they tend to be reported, with the cause and the fix.

---

## Content

### "SCF → Field Groups is empty. Is the plugin even used?"

It is used, heavily — around 100 call sites read fields, and there are thousands
of stored values. All 30 field groups are registered from PHP with
`acf_add_local_field_group()`, and that admin list only shows groups saved in
the database.

Confirm with:

```php
count( acf_get_field_groups() );   // 30, each marked local => php
```

The fields appear where they are used: page, product and category edit screens,
and the three **Packgens** options screens. Keeping them in code means they are
version-controlled, deploy with the theme and cannot be deleted from the admin.

### A section shows old wording after I emptied its field

That is deliberate. Every interface string falls back to the English default in
the template, so an empty field renders the original rather than nothing. To
change the wording, type the new wording; to hide a whole band, use its
**Enable** switch.

### A field I saved is not showing on the front end

1. Confirm you saved the right screen — page sections live on the page, shared
   content on **Packgens → Global Content**.
2. For a product category, remember it may be inheriting from **Category
   Defaults**; a value typed on the term overrides it, an empty one does not.
3. Check the section's **Enable** switch.

### Every category page changed when I only meant to change one

You edited **Packgens → Category Defaults**, which all categories inherit.
Undo it there, then make the change on the individual category instead.

### A category is ignoring a change to Category Defaults

That category has its own value stored for that field. Clear the field on the
category and it will follow the default again.

Whether a term "has" a value is read from its stored meta rows, not through
`get_field()` — SCF fills anything unsaved with the field's own `default_value`,
so an untouched **Layout** select reads as `center` rather than as absent. See
`packgens_stored_section_keys()` in `inc/sections.php`.

---

## WooCommerce

### "Added to your cart" never appears

The theme's archive and single-product templates do not fire
`woocommerce_before_shop_loop` / `woocommerce_before_single_product`, which is
where WooCommerce prints notices. `packgens_wc_notices()` in
`inc/integrations/woocommerce.php` prints them on `woocommerce_before_main_content`
instead. If notices vanish again, check that hook is still firing in the
template you are looking at.

### A notice has its tick sitting on top of the text

WooCommerce positions its `::before` tick absolutely. The theme resets it to
`position: static` so it becomes a flex item. If a new notice type appears
broken, add its class to that rule in `woocommerce.css`.

### The cart sidebar drops below the items instead of beside them

WooCommerce sizes the two columns to a hard 65% / 35%. Any `gap` on the flex row
pushes them past 100% and the sidebar wraps. The theme sets `flex-wrap: nowrap`
so the columns shrink into the gap instead. Do not remove that.

### A WooCommerce style will not override

The plugin's selectors are often three classes deep — for example
`.wc-block-components-form .wc-block-components-text-input input[type=text]`.
Match that weight rather than adding `!important`; the theme's stylesheet loads
after the plugin's, so an equal-specificity rule wins. There are comments in
`woocommerce.css` marking the places where this matters.

### The header heart or the save button is unstyled on a non-shop page

`woocommerce.css` loads whenever WooCommerce is active, not only on WooCommerce
views, because the heart is in the header everywhere. If that condition is
narrowed, those controls lose their styling on the homepage.

---

## Saved items

### The heart does nothing

Check the browser console for a failed `admin-ajax.php` request. The endpoint is
`packgens_wishlist` and it is nonce-checked against `packgens_public`; a stale
cached page can carry an expired nonce. A hard reload fixes it.

### Two quick clicks only save one product

Fixed, but worth knowing why: for guests the list is a cookie, so two requests
in flight both read the pre-click value and the second overwrites the first.
`assets/js/wishlist.js` serialises the toggles in a promise chain. Keep that
chain if you refactor the file.

### The empty-state message shows alongside saved products

A `display` rule on a class outranks the `hidden` attribute. The theme states
`.pg-wishlist-empty[hidden] { display: none }` explicitly. Any new component
that toggles with `hidden` and also sets `display` needs the same line.

---

## Layout and CSS

### A sticky sidebar does not stick

Check nothing above it has `overflow-x: hidden`. An overflow container becomes
the scroll box that every `position: sticky` descendant is measured against, so
nothing inside can stick. `body` uses `overflow-x: clip`, which contains
full-bleed rows without creating a scroll container. Changing that one word
breaks sticky positioning site-wide.

### A rule I added has no effect

Check specificity before adding `!important`:

- A later rule only wins at **equal** specificity.
- `.pg-hero--form .pg-hero__strip` (0,2,0) beats `.pg-hero__strip` (0,1,0).
- Media queries do not add weight — a rule inside one still loses to a
  same-specificity rule that comes later in the file.

### An icon sits slightly off-centre in a round button

The icon is usually wrapped in two spans. If either is left inline it puts the
glyph on the text baseline of the line box rather than in the middle of it. Give
the wrappers `display: flex; align-items: center; line-height: 0` and the SVG
`display: block`.

### The header overlaps the first section

The header is absolutely positioned, and pages without a hero have to provide
their own clearance:

```css
padding-top: calc(var(--pg-header-h) + <space>);
```

`--pg-header-h` already accounts for the trust strip when it renders — it is
`--pg-header-band-h + --pg-header-strip-h`, and the strip token is only raised
by `.pg-has-plain-header:has(.pg-header__strip)`. Do not hard-code a pixel
value in its place.

---

## Assets

### CSS or JS changes are not showing

With `WP_DEBUG` on, asset versions use `filemtime()` and never cache. With it
off they use the theme version in `style.css` — bump that on deploy, or the
browser will keep the old file.

### A stylesheet is not loading at all

Assets are conditional. `inc/enqueue.php` builds a context array
(`front`, `page`, `product`, `shop_archive`, …) and enqueues from it. A new
template needs its context adding there, or its stylesheet never loads.

---

## Diagnostics

Lint every PHP file:

```bash
find . -name "*.php" -exec php -l {} \;
```

Check a page renders without notices:

```bash
curl -s http://your-site.test/some-page/ | grep -c "Fatal error\|Warning:\|Notice:"
```

Confirm the field groups registered:

```php
do_action( 'acf/init' );
foreach ( acf_get_field_groups() as $g ) {
    printf( "%s %s local=%s\n", $g['key'], $g['title'], $g['local'] ?? 'no' );
}
```
