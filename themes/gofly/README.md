# Go Fly — WordPress theme

Arabic (RTL) travel & tours theme for جو فلاي للسفر والسياحة, built from the Claude Design handoff (`project/client-site`).

## Requirements

- WordPress 6.4+, PHP 7.4+
- **ACF Pro** (required — all editable content)
- **Fluent Forms** (contact form and package quote form)
- Site language: العربية (Settings → General) so dates and RTL are correct

## Setup (≈10 minutes)

1. Upload `gofly/` to `wp-content/themes/` and activate it. Activate ACF Pro and Fluent Forms.
2. **Permalinks:** Settings → Permalinks → "Post name" → Save (packages live at `/packages/`).
3. **Create pages** and pick the template in the page sidebar:
   | Page | Template |
   |---|---|
   | الرئيسية | Default (then set as Homepage) |
   | المدونة | Default (then set as Posts page) |
   | الوجهات | الوجهات |
   | احجز الآن | احجز الآن (طيران وفنادق) |
   | تواصل معنا | تواصل معنا |
   | من نحن | من نحن |
4. **Settings → Reading:** "A static page" → Homepage = الرئيسية, Posts page = المدونة.
5. **Fluent Forms:** create
   - a *contact* form (name, mobile, email, destination, message; a radio field "نوع الطلب" renders as chips) → select it on the تواصل معنا page;
   - a *quote* form (name, mobile, travellers, hotel category) **plus a hidden field with the value `{embed_post.title}`** so each request carries the package name → select it in **إعدادات الموقع → الباقات**.
   The theme restyles Fluent Forms inputs, radios and the submit button to match the design.
6. **إعدادات الموقع** (left admin menu): WhatsApp number (international, e.g. `966510131525`), phone, email, hours, address, map, social links, logos and header colour (blue with the white logo, or white with the coloured logo), stats, partner logos, footer texts and badges.
7. **Menus** (Appearance → Menus):
   - *القائمة الرئيسية*: top-level items; child items become the dropdown card. Each child has **الأيقونة** (Material Symbols name, e.g. `location_on`, `hotel`) and **سطر فرعي** fields.
   - *الفوتر — العمود الأول/الثاني/الثالث*: the menu **name** is the column title (e.g. "روابط سريعة").
   - *الفوتر — الروابط القانونية*: privacy / terms / cancellation.
8. Add content: **الباقات → الوجهات** (image + country line), **فئات الباقات** (icon), then packages, posts and the home page sections.

## Demo content (optional)

**Appearance → استيراد المحتوى التجريبي** imports the design's content in about a minute: all pages with their templates and reading settings, every home section, 8 destinations, 5 categories, 9 packages (with the full Russia programme), 6 blog posts, the main menu with dropdown icons, the footer menus, and Site Settings (stats, partner logos, social links).

- Destination and blog photos are downloaded from Unsplash. If a download fails, or you untick the option, a brand-coloured placeholder is used instead. Partner logos, cars and the Russia photos ship with the theme in `assets/demo/`.
- Nothing that already exists is changed. **حذف المحتوى التجريبي** removes exactly what the import created, including its images.
- ACF Pro must be active. Fluent Forms forms aren't created; build the two forms and select them afterwards (see step 5 above).
- The demo data lives in `inc/demo/data.php`, if you want to adjust it.

## Importing packages

**الباقات → استيراد الباقات** creates or updates packages from a JSON file (`{"packages": [ … ]}`). Each package object uses the package field names from `acf-json/group_gofly_package.json` (repeaters as arrays of rows, `includes`/`excludes` one item per line), plus:

| Key | Meaning |
|---|---|
| `title`, `slug`, `excerpt` | Post title, URL slug (used to match existing packages), card summary. |
| `destination` | `{ "slug", "name", "subtitle" }`. Created if it doesn't exist. |
| `categories` | Package category slugs, e.g. `["europe"]`. A top-level `"categories": [{ "slug", "name", "icon" }]` list creates missing ones first. |
| `image`, `gallery` | Optional photo URLs, downloaded into the Media Library. |
| `owner_notes` | Optional list shown in the import report as things to review. |

The same file can include `"pages": [{ "title", "slug", "template": "service", "fields": { … } }]` to create pages such as the service landing pages (content in `inc/demo/services.json`).

New packages can be saved as drafts. Re-importing the same file updates the packages instead of duplicating them.

## Content model

| Type | What it holds |
|---|---|
| `package` (CPT, archive `/packages/`) | Title, featured image, excerpt + ACF tabs: general (days, nights, price, old price → automatic discount badge, rating, group size, cancellation note), media (gallery + optional MP4 video), programme (days, includes/excludes — one item per line), hotels, cars, room split, destination facts, restaurants, policies. Every section hides itself when empty. |
| `destination` (taxonomy) | Image, subtitle, optional custom link. Links open `/packages/?dest=slug` pre-filtered. |
| `package_category` (taxonomy) | Icon. Used for the archive "الفئة" chips (`/packages/?cat=slug`). |
| `testimonial` (CPT, admin only) | آراء المسافرين: title = traveller name, featured image = photo, plus quote, trip and rating. Order with the "الترتيب" box. |
| Posts | Native blog. Every `<h2>` becomes a table-of-contents entry automatically; optional "باقة مقترحة" in the sidebar. |
| Home page fields | Hero, services, destinations, packages, steps, gallery, Instagram, testimonials (pick and order them, or leave empty for the latest six), blog headings, FAQ. Empty sections are hidden. |

ACF field groups are stored in `acf-json/` and load automatically (no import needed). Edit them in ACF → Field Groups if you need changes; ACF writes back to the same folder.

## Structure

```
inc/               setup, enqueue, CPT/taxonomies, ACF glue, nav walker, helpers, template tags
template-parts/
  components/      page-hero, section-head, package-card, destination-card, post-card,
                   feature-card, accordion, faq-section, stats, empty-state
  home/            one file per home section
  package/         one file per package section + booking card, mobile bar, quote modal
page-templates/    booking, contact, about, destinations, service (landing page per service, own Fluent Form)
assets/css/main.css   all styles (tokens at the top), editor.css for the block editor
assets/js/         main.js (menu, dialogs, reveals, counters), packages.js (filters),
                   package.js (Swiper gallery), booking.js (WhatsApp booking widget)
```

## Notes

- **Booking page** keeps the design's flow: no form submission — it validates and opens WhatsApp with a ready message to the number in Site Settings. City suggestions use the free Open-Meteo geocoding API.
- **Packages archive** renders all packages and filters/sorts/paginates them instantly in the browser (page size in Site Settings). This suits an agency catalogue of up to a few hundred packages.
- Icons: Material Symbols Rounded (Google Fonts) and Font Awesome brands (cdnjs). Gallery: Swiper (jsDelivr), loaded on package pages only. Fonts: 29LT Bukra, bundled.
- Animations respect `prefers-reduced-motion`; content is in the HTML from the start (reveals/counters are progressive enhancement).
