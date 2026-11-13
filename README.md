# Aqua — WordPress theme for a drinking water delivery business

A custom WordPress theme converted from a static HTML template into a fully editable, client-friendly website.
Every text, image, price and link on the site is managed from the WordPress admin — no code changes needed.

**Live demo:** https://aqua-drop.shop/

## Highlights

- **Fully editable with ACF PRO** — homepage slider, features, about, services, stats, price list and blog blocks;
  theme settings for header, footer, contacts, social links, map and 404 page. Field groups are stored as Local JSON (`acf-json/`).
- **Clean BEM CSS** — one file per component in `assets/css/blocks/`, design tokens in `assets/css/base.css`.
  Bootstrap 5 is used only for the grid and its JS components.
- **No jQuery** — small vanilla JS (`assets/js/theme.js`): preloader, sticky header, scroll animations, counters, back-to-top.
- **Contact Form 7** — contact and newsletter forms styled to match the design.
- **SEO & accessibility basics** — one `<h1>` per page, semantic menus, responsive images with `srcset`, `prefers-reduced-motion` support.
- **Responsive** — tested from 390 px to 1920 px, no horizontal scroll.
- **Client help** — "How to edit this website" dashboard widget and hints next to every field.

## Requirements

- WordPress (tested on 7.1), PHP 7.4+ (tested on 8.4 and 8.5)
- [Advanced Custom Fields PRO](https://www.advancedcustomfields.com/pro/) 6.3+
- [Contact Form 7](https://wordpress.org/plugins/contact-form-7/)

## Structure

```
acf-json/            ACF field groups (Local JSON)
assets/css/          base.css + BEM blocks
assets/js/theme.js   theme scripts
inc/                 ACF helpers and setup, admin widget, menu walker, CF7 tweaks
template-parts/      page sections and post cards
page-*.php           page templates: About, Services, Price List, Contact
```

## Credits

- Based on [Underscores](https://underscores.me/) (GPL v2 or later).
- Original HTML template "Acuas" by [HTML Codex](https://htmlcodex.com) — credit link kept in the footer.
- Photos from [Pexels](https://www.pexels.com), see `assets/img/CREDITS.md`. Bottle illustrations are original.

## License

GNU General Public License v2 or later — see `LICENSE`.
