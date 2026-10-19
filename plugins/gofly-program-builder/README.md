# Go Fly Program Builder (WordPress plugin)

The Go Fly program builder (the `GoFly-Program-Builder` HTML tool) inside wp-admin. It's a Vue 3 app with a shared program library, design styles, content templates, two-way sync with the website packages, native-role permissions and an activity log.

## Install

1. Upload `gofly-program-builder/` to `wp-content/plugins/` and activate it.
2. Open **برامج PDF → أداة البرامج**.

No build step is needed on the server: the built app is committed in `dist/`. Activation adds the capabilities, creates the log table and imports the 14 Russia content templates.

## What staff can do

- **Start a program:** blank, from a content template (Russia RU-M5 … RU-MS15 ship with the plugin), or from a website package. On a package screen, use the "إنشاء برنامج PDF" row action or the sidebar box.
- **Edit:** the same editor as the original tool, with a live A4 preview. It has the cover, the six section types (text, list, days, hotels, flights, price), reorder / hide / delete controls, and per-line show/hide. Images come from the Media Library.
- **Save:** the program code is generated automatically, e.g. `GF-0012`, or `GF-RU-M5-0012` when started from a template. You can search the library by code, customer name or phone. You can also duplicate a program or "save as new".
- **Download PDF / Share:** the output is identical to the original tool (html2canvas + jsPDF, A4, cards never split across pages, header and footer on every page, clickable hotel links).
- **Design styles:** الموجة، صورة كاملة، أنيق، جريء (original), plus بسيط، مجلة، فاخر داكن (new).
- **Website sync:** when a program linked to a package is saved, staff are asked *"تحديث الباقة على الموقع؟"*. A field-by-field diff lets them pick what to update: title, subtitle, image, days/nights, price/old price, includes/excludes, itinerary, hotels, policies. Unlinked programs can be pushed as a new draft package. Customer data, flights and price breakdowns are never published.
- **Company identity:** name, CR, phone, WhatsApp, logo, colours, font and bank details. Admins edit them once for everyone; the defaults come from the theme's Site Settings.

## Roles (native WordPress roles)

| | Administrator | Editor | Author |
|---|:-:|:-:|:-:|
| Use the builder, create/edit/download **own** programs | ✓ | ✓ | ✓ |
| Open, edit, delete **anyone's** programs | ✓ | ✓ | |
| Update / create website packages | ✓ | ✓ | |
| Manage content templates | ✓ | ✓ | |
| Company identity settings | ✓ | | |
| Activity log + CSV export | ✓ | | |

Contributors and subscribers have no access. Permissions are checked on every REST request, not just hidden in the UI.

## Activity log

**برامج PDF → سجل النشاط** records who did what, when and from which IP. The logged actions are: created, created from package, updated, duplicated, deleted, PDF downloaded (with style and page count), pushed to package (with the fields), template saved/deleted, and settings saved. You can filter by user, action and date, and export to CSV.

## Development

```
cd app
npm install
npm run build      # writes ../dist/app.js and ../dist/app.css
npm run dev        # rebuild on change
```

```
gofly-program-builder.php   bootstrap + activation (caps, log table, preset templates)
includes/                   CPTs, capabilities, programs storage, REST API, package sync, activity log, settings, admin screens
app/src/                    Vue app: App.vue, components/, store.js, api.js,
                            schema.js (document model), render.js (A4 renderer), pdf.js (export),
                            styles/paper.css (all 7 design styles), styles/app.css (editor UI)
assets/presets/             Russia templates (russia.json) and their photos
```

Programs are stored as private `gf_program` posts and content templates as `gf_program_tpl` posts. The builder's JSON document is in the `_gfpb_data` meta key, so documents stay compatible with the original tool.
