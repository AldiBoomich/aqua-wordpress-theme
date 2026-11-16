# Aqua: design system

The contract every template is built from. Tokens live in `src/css/tokens.css` (CSS) and `src/js/tokens.js` (motion);
if a value you need is missing, add it here and to the tokens first. Never hard-code a color, size or curve in a block.

## Subject
- **Business:** Aqua delivers purified spring water (0.5 L to 19 L bottles), water coolers, home filters and lab testing
  to homes and offices across the city. Since 2010.
- **Main action:** order water (prices / order form). Secondary: call.
- **What is different:** 7-stage purification, every batch lab-tested against 40+ markers, delivery within 24 hours,
  free cooler rental with a monthly plan, reusable 19 L bottles.
- **The world it is made of:** clear water, light refracting through it, glass and PET, filtration media, the lab,
  a calm cold morning light.

## Direction
Light, cold, quiet: a page that feels like a glass of clear water. Generous space, very large and light type,
deep-water ink instead of black, thin 1px lines. The brand aqua appears only where water is.

**Signature — living water.** One idea used in a few places: the hero is a real water surface that ripples under the
cursor (WebGL), the "From source to glass" scene shows water passing the 7 stages, icons and bottles hold water that
moves (liquid physics), and the footer wordmark fills with water. Everything else stays still and disciplined.

**Rejected defaults:** Inter/Manrope + an italic serif accent word (the current AI cliché) → one Swiss grotesk;
bright cyan pill buttons → deep-teal buttons whose fill rises like water; four icon cards → a marquee of facts;
three pricing cards with "Most popular" → a horizontal shelf of the real bottles; a dark photo banner on every
page → a light typographic page head; a blocking preloader → a short intro played on the hero itself.

## Color (contrast measured on --c-ground)
| Token | Value | Role | Contrast |
|---|---|---|---|
| --c-ground | #F7F9FA | page background (60%) | – |
| --c-surface | #EAF1F4 | glacier mist: alternate sections, fields | – |
| --c-surface-raised | #FFFFFF | floating panels (menus, cards) | – |
| --c-ink | #0E2A3B | text, deep water | 14.1:1 |
| --c-ink-muted | #4A6070 | secondary text, field borders | 6.2:1 (5.8:1 on surface) |
| --c-action | #00708C | buttons, text links | 5.4:1; white text on it 5.7:1 |
| --c-water | #00D1F9 | brand aqua: water, glints, lines only — never text on light | 1.7:1 (decorative) / 8.1:1 on deep |
| --c-deep | #0E2A3B | footer and deep sections | white 14.1:1, 72% white 8.0:1 |

## Type
- **Family:** Switzer (Indian Type Foundry, variable 100–900, ITF Free Font License; self-hosted, not in Git).
  Fallback: Arial resized to Switzer's metrics (size-adjust 100.7%) so the swap does not move text.
- **Weights:** 300 for display, 400 text, 500 UI/labels, 600 rare emphasis.
- **Scale (fluid, clamp):** 13 / 14 / 15 (UI) / 16→18 (body) / 18→22 (lead) / 20→26 (h4) / 24→38 (h3) /
  36→76 (h2) / 46→140 (h1) / up to 26rem (footer wordmark).
- **Leading:** display 0.92, headings 1.04, snug 1.3, body 1.6. **Tracking:** display −0.045em, headings −0.03em.
- **Measure:** 65ch. `text-wrap: balance` on headings, `pretty` on paragraphs, tabular numbers for prices and hours.
- Sentence case everywhere. No all-caps eyebrows, no single accented word in a headline, no arrows glued to labels.

## Layout
- Wrap max 1440px; side gutter 16 → 48px; 12-column grid, gap 16 → 32px. Some sections run full-bleed.
- Spacing scale: 4 8 12 16 24 32 48 64 96 128 160 200. Section padding 72 → 200px (`--section-y`), tight 56 → 128px.
- Radius: 12px (fields, menus), 28px (media, large panels), pill (buttons, round controls).
- Elevation: two layered ink-tinted shadows, only for things that float (menus, the sticky header).
- Alignment: left-aligned content; centering is reserved for the footer wordmark.

## Motion
- Curves: `--ease-out` cubic-bezier(.16,1,.3,1) ≈ GSAP `expo.out`; `--ease-in-out` (.65,0,.35,1) ≈ `power3.inOut`;
  `--ease-ui` (.2,.7,.2,1) for small UI.
- Durations: 160ms press/hover · 280ms menus · 600ms reveals · 1000ms scene moments.
- Only transform, opacity and clip-path are animated. Infinite motion pauses off-screen and in background tabs.
- Moments per page: one entrance (hero / page head) + at most two scroll moments. Hovers answer the pointer:
  button fill rises like water, link underlines flow in from the left and out to the right.
- Between pages: cross-document View Transitions — the header stays put, the old page lifts 24px and fades (300ms),
  then the new one rises from 40px below and fades in (600ms, from 240ms: the pages barely overlap). Skipped without
  hardware acceleration (`html.no-gpu`: a software-drawn full-screen fade stutters). Links prefetch on hover
  (WordPress Speculation Rules, "moderate").
- During a transition the new page stays light: motion setup runs in small tasks (never one long block), and the
  hero water starts when the transition has finished (`window.aquaRevealed`; its canvas fades in over the same photo).
- Mouse only: a 10px water drop trails the pointer (the system arrow stays) and swells into a lens over links and
  buttons (a light rim on deep sections); over the hero water it stays a plain drop (the water itself answers the
  pointer). Primary and light buttons and the round toggles lean up to 8px towards the pointer and spring back
  (CSS `translate`, so the press scale and the rising fill keep working).
- Anchors: one offset, `html { scroll-padding-top }`; Lenis reads it — never add `scroll-margin` on targets.
- Every one of these is switchable in Theme Settings → Motion.
- `prefers-reduced-motion: reduce` → no WebGL, no smooth scroll, no parallax, no page transitions, no cursor or
  magnetic buttons; content simply appears.
- Content is visible without JavaScript.

## Imagery
- Pexels photos (credits in `assets/img/CREDITS.md`), graded light and cold: lifted shadows, slight desaturation,
  cyan-leaning whites. Every photo is replaceable in ACF and served with srcset (WebP/AVIF).
- Ratios: hero full-bleed (16:9 desktop, 4:5 phone crop), story images 4:5, cards 4:3.

## Components
- **Button:** pill, 48px (44 small, 56 large). Primary = deep teal, the fill that rises on hover is deep ink.
  Secondary = transparent with a 28% ink border. Light = for deep backgrounds, rising aqua fill.
- **Header:** fixed; transparent at the top, frosted glass (desktop) / solid (phone) after scrolling; hides while
  scrolling down, returns on any scroll up. Logo | centered menu | phone + "Order water".
- **Full-screen menu (phones/tablets):** clips open from the top, links rise one by one, contacts and the order
  button at the bottom; inert when closed, focus kept inside while open.
- **Footer:** deep water under a drifting water line; intro + newsletter, menu, hours, contacts, social;
  the wordmark that fills with water; © line with the legal link and "Back to top".
- **Page head:** light title area for inner pages, different heights by role so pages do not all open alike:
  default (title + lead: Products, Contact, Blog), `--media` (wide photo under the title, LCP: About, Service),
  `--compact` (posts, search, 404, regular and legal pages). The H1 words rise from masks in pure CSS; the lead
  is never hidden. No breadcrumbs, no eyebrow.
- **Prose:** editor content in a 65ch column; underlined links, quiet aqua rule for quotes, tabular tables.
- **Search / 404:** a labelled search field, results as a plain list separated by lines; when nothing is found,
  a short list of key pages (and on the 404, one drop of living water).
- **Pagination:** round 44px targets, Previous / Next, the current page filled with ink.
- **Legal:** sticky numbered table of contents from the H2s + a 65ch reading column.
- **Ledger:** facts or terms as an editorial list (title | text | short detail) on hairlines; no icons, no cards
  (About: quality & eco; Products: delivery terms).
- **Business panel:** the only deep-water panel above the footer (Service): title | text + light button, three points.
- **Contact:** visible labels, 48px fields, 16px+ text, the error under its field and focus on the first error;
  details as a list on hairlines; the map loads only on demand (rings on still water mark the place until then).
- **Blog:** topic links (plain text, the current one underlined) → the first post large, then three columns;
  a post: compact head (topic, date, reading time) → wide photo → prose → previous / next → more articles.

## Page structure
- **Home:** hero (living water, one screen) → facts marquee → about (line-by-line statement, 15 years) →
  From source to glass (pinned, 7 stages) → bottles shelf (horizontal scroll) → services list → numbers →
  journal (1 large + 2 small) → final call to order → footer.
- **About:** page head + spring photo → story (statement, text, portrait) → quality & eco ledger →
  7 stages as a list (a hairline fills with water on scroll) → numbers → call to order.
- **Service:** page head + office photo → six services with what is included → for business (deep panel) →
  call to order.
- **Products:** page head → the bottles shelf (same as the homepage, with the cooler-rental note) →
  delivery terms ledger → call to order. A bottle's order button opens the contact form with that bottle as the subject.
- **Contact:** page head with the phone → form beside the contact details and hours → map on demand.
- **Blog / post / search / 404 / legal:** page head, editorial grid or reading column.

## Copy rules
Plain, specific, sentence case. Buttons say what happens ("Order water", "Get a free quote").
Banned: elevate, seamless, cutting-edge, unleash, world-class, "Welcome to our website".
All visible and screen-reader text comes from ACF (Home page fields, Theme Settings incl. the Labels tab).
