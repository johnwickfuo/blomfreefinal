# BLOMFREE Phase 5 — Marketplace Transformation

Branch: \`phase-5-marketplace\`. This replaces the corporate-first homepage and
keeps the existing Laravel 11, Vue 3, Inertia, Filament and order/checkout systems.

## The mobile shopping experience

- Compact header and search, category shortcuts, active promotional banners.
- Actual discounts, featured mixed listings, editor-selected inventory, recent listings
  and division shelves. Sections without listings do not render.
- Fixed mobile Home / Categories / Saved / Cart / Account navigation.
- Customer account hub at \`/my\`, category hub at \`/categories\`.
- Informational pages at \`/how-it-works\`, \`/payments\` and \`/help\`.
- Gadgets and Fashion: category, price, stock and sorting filters; quick cart additions
  for simple stocked products and variant selection for configurable products.
- Automobiles: original filters, comparisons and inquiries; land and animals: inspection
  links and available price filters. The catalogue's cards do **not** put land or
  automobiles through ordinary product checkout.

## Admin setup

Filament > **Marketplace Homepage** manages individual placements. Add a listing,
choose Featured, Editor's Picks or Today's Deals, choose an optional active window,
set \`display_order\`, and enable the record. A listing may appear in multiple sections.
The existing \`is_featured\` field remains a fallback for Featured if slots remain.

For **Today's Deals**, select a *published, in-stock fashion or gadget product* and
set an existing valid \`compare_price\` that exceeds the real checkout \`display_price\`.
This feature **does not change prices** in the cart, database, payment gateways or
product variants. Admins must ensure that the comparison price accurately reflects
a genuine earlier/regular selling price; the application cannot verify that history.
A countdown appears only when an active scheduled deal has \`ends_at\`; it describes
the *next* expiring offer, not all active offers.

Filament > **Homepage Sections** creates optional overrides for the fixed known
sections; choose section key and admin-facing title, enable/disable and display order.
No row means the section is enabled at its default order. This section controller
does not allow arbitrary HTML or unsupported new sections.

Filament > **Homepage Banners** already manages headline, image, dates, sorting
and call to action. The new homepage uses these banners near the top.
The public hero falls back to an informational non-product message if none exist.

## Safety and availability

- Cards use real models and media; draft, sold and out-of-stock content is excluded
  from the homepage and saved result pages.
- Unsold product offers appear with real base/variant prices; discount percentages are
  computed from the configured regular comparison price.
- Old About, CEO, installments, help, customer orders and tracking remain accessible.
- Saved listings reside on the user's device, not in a cloud account.
- New migrations are strictly additive: \`marketplace_placements\` and
  \`marketplace_sections\`. No production seeding is required.
- Test on a staging copy before switching production: review search, cart, variant
  selection, inspection booking, login/logout and browser widths.

## Deployment procedure

From the existing Laravel repository root on the VPS, use a persistent terminal session
(e.g. tmux) and ensure a restorable previous release exists. Avoid \`deploy.sh\`, which
forces \`main\`. Switch the application to maintenance mode, fetch and switch to this
branch, install Composer dependencies, run \`php artisan migrate --force\` **before
bringing the application online**, install node packages and run \`npm run build\`,
clear Laravel caches and bring the site up. Do not skip migrations or create sample
inventory. Never use \`migrate:fresh\` on production.

No GitHub push or build automatically deploys to blomfree.com.
