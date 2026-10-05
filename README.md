# dondorf-dummy

A showcase website built with [Kirby CMS](https://getkirby.com) 5. It is a stripped-down copy of a
real project, published to demonstrate the **setup, structure and code** — not the content.

> ⚠️ **All content in this repository is placeholder data.**
> There are no real credentials, personal data, license keys or production content here.
> See [What was removed](#what-was-removed-from-the-original).

---

## What this project is

A single-page event site for a community space. It combines:

- a **welcome/hero section** with an animated slideshow and a collapsible intro text,
- a **calendar of events** (upcoming + recurring) with filtering and a modal detail view,
- an **initiatives/collectives** section,
- an **interactive floor plan** (SVG/CSS) that links rooms to initiatives,
- an **info section** and **imprint**,
- a **contact** block driven by structured content,
- a **booking request form** that stores submissions as Kirby pages,
- a **newsletter signup** (Mailchimp),
- a simple **page-view counter**,
- a **cookie banner** and a **DE/EN language switch**.

Everything is server-rendered with Kirby templates, enhanced with small vanilla
JavaScript modules — there is no frontend framework and no build step.

---

## Tech stack

| Layer      | Choice                                                         |
| ---------- | -------------------------------------------------------------- |
| CMS        | Kirby 5 (`getkirby/cms`)                                       |
| Language   | PHP 8.1+                                                       |
| Templates  | Kirby PHP templates + snippets                                 |
| Frontend   | Plain CSS + vanilla ES6 (no framework, no bundler)             |
| Forms      | `mzur/kirby-uniform`, `mzur/kirby-form` (+ `mzur/kirby-flash`) |
| Mail       | PHPMailer via Kirby's email API (SMTP)                         |
| Newsletter | Custom Mailchimp plugin                                        |
| i18n       | Custom DE/EN field pairs + client-side toggle                  |

---

## Requirements

- PHP **8.1 – 8.4**
- [Composer](https://getcomposer.org)
- A web server (Apache/Nginx) or PHP's built-in server for local use

---

## Installation

```bash
# 1. Install Kirby and the form plugins (Kirby core + vendor are NOT committed)
composer install

# 2. Create your local environment file
cp .env.example .env
#    …then fill in the values (see Configuration below)

# 3. Run the site locally
composer start
#    → http://localhost:8000
```

> Kirby is proprietary software. Install it via Composer and obtain a license for
> production use: <https://getkirby.com/buy>.

---

## Configuration

Environment variables are loaded from `.env` by [`index.php`](./index.php) and
[`site/config/config.php`](./site/config/config.php):

| Variable             | Purpose                                             |
| -------------------- | --------------------------------------------------- |
| `KIRBY_CONTENT_SALT` | Salt for encrypted content (regenerate per install) |
| `KIRBY_COOKIE_KEY`   | Signing key for cookies                             |
| `SMTP_HOST`          | Mail server host                                    |
| `SMTP_PORT`          | Mail server port (default `587`)                    |
| `SMTP_SECURITY`      | `tls` or `ssl`                                      |
| `SMTP_USERNAME`      | SMTP user                                           |
| `SMTP_PASSWORD`      | SMTP password                                       |

Generate fresh secrets for the Kirby values, e.g. `openssl rand -hex 32`.

Kirby options (locales, YAML handler, email transport, Uniform messages) live in
[`site/config/config.php`](./site/config/config.php).

---

## Project structure

```
.
├── index.php                  # Entry point: loads .env, boots Kirby, renders
├── router.php                 # Router for PHP's built-in server
├── .htaccess                  # Apache rules (pretty URLs, block dotfiles/content/site/kirby)
├── .user.ini                  # Raises PHP input limits for large Panel forms
├── composer.json              # PHP deps: kirby/cms, mzur/kirby-uniform
├── assets/                    # Static CSS + JS, grouped by feature
│   ├── css/                   # One stylesheet per component (header, calendar, …)
│   └── js/                    # One module per feature (calendar, floorplan, …)
├── content/                   # Kirby content (Markdown-ish .txt files) — SAMPLE DATA
│   ├── site.txt               # Site-wide fields (titles, section copy, structured events/contacts)
│   ├── home/                  # Home page
│   ├── events/                # Event listing page + individual events
│   ├── bookings/              # Booking requests (runtime, git-ignored)
│   ├── newsletter/            # Subscribers (runtime, git-ignored)
│   └── error/                 # Error page
└── site/
    ├── blueprints/            # Panel editor definitions for pages, users and the site
    ├── collections/           # Reusable page queries (e.g. notes)
    ├── config/                # config.php, api.php, routes.php
    ├── controllers/           # Per-template logic (pass data to templates)
    ├── models/                # Page models (custom page methods)
    ├── plugins/               # Custom project plugins (see below)
    ├── scripts/               # CLI helpers
    ├── snippets/              # Reusable template partials
    └── templates/             # Page templates (home, default, booking)
```

---

## How it works

### Rendering

`index.php` loads `.env`, requires `kirby/bootstrap.php` and renders the app.
Kirby maps a URL to a **template** in `site/templates/`, optionally running a matching
**controller** in `site/controllers/` first, and templates compose **snippets** from
`site/snippets/`.

- [site/templates/home.php](./site/templates/home.php) builds the whole landing page by
  composing snippets: `header`, `slideshow`, `calendar`, `collectives`, `floorplan`,
  `info-section`, `booking-form`, `menu-overlay`, `cookie-banner`, `footer`.
- [site/templates/default.php](./site/templates/default.php) is the generic page template.
- [site/templates/booking.php](./site/templates/booking.php) renders a confirmation for a
  stored booking.

### Bilingual content (DE/EN)

Rather than Kirby's multi-language setup, the site stores paired fields
(`heading_welcome_de` / `heading_welcome_en`, `description_de` / `description_en`, …).
Templates output both inside `.lang-de` / `.lang-en` spans and a small script toggles
their visibility; the header exposes a `#langSwitch` button. See
[`site/snippets/header.php`](./site/snippets/header.php) and
[`assets/js/script.js`](./assets/js/script.js).

### Events & calendar

Events are defined either as **structured entries** on the site (`Events` structure in
`content/site.txt`) or as **event pages** under `content/events/`.
[`site/plugins/content-storage/events.php`](./site/plugins/content-storage/events.php)
expands recurring events (`wiederholung`: `daily`/`weekly`/`monthly`, with `wiederholen_bis`)
into individual **occurrences** and overlays per-date _exceptions_ (cancelled / rescheduled).
[`site/snippets/calendar.php`](./site/snippets/calendar.php) renders upcoming and regular
events; the JS in [`assets/js/calendar.js`](./assets/js/calendar.js) and
[`assets/js/event-modal.js`](./assets/js/event-modal.js) provide filtering and the detail modal.
An editor-only JSON feed is exposed at `admin/events`
(see [`site/config/routes.php`](./site/config/routes.php)).

### Booking form

[`site/controllers/home.php`](./site/controllers/home.php) and
[`site/controllers/booking-form.php`](./site/controllers/booking-form.php) validate the
form with **Uniform**, then create a child page under `content/bookings/` and email the
editors plus the requester. The form markup is
[`site/snippets/booking-form.php`](./site/snippets/booking-form.php).

### Newsletter

Signups are handled client-side ([`assets/js/newsletter.js`](./assets/js/newsletter.js))
against the custom **Mailchimp** plugin (`site/plugins/mailchimp/index.php`), which wraps
the Mailchimp API. Configure `api_key`, `datacenter` and `audience_id` via Kirby options
(defaults are empty, so the plugin reports "not configured" until you set them).

### Page stats

[`site/plugins/page-stats/index.php`](./site/plugins/page-stats/index.php) records page
hits into `site/logs/page_hits.json` via `$page->trackHit()` (called in the home template)
and exposes `$site->hitsCount()` / `$site->statsOverview()`.

---

## Custom plugins

These are project-specific and live in `site/plugins/`:

| Plugin            | Purpose                                                                         |
| ----------------- | ------------------------------------------------------------------------------- |
| `content-storage` | Mirrors site fields into `content/site-sections/*` and expands recurring events |
| `page-stats`      | File-based page-view counter                                                    |
| `mailchimp`       | Newsletter subscription against the Mailchimp API                               |

## Third-party plugins (installed via Composer)

These are **not** committed — `composer install` places them in `site/plugins/`:

- `mzur/kirby-uniform` — form validation & emailing
- `mzur/kirby-form` — form helpers (`csrf_field()`), pulled in by Uniform
- `mzur/kirby-flash` — flash messages, pulled in by kirby-form

---

## Deployment

The site is deployed by `git pull` on shared hosting, so `kirby/` and `vendor/` may be
committed in a private deployment repo. In this public showcase they are intentionally
**excluded** and installed with Composer. Point the web root at the project directory and
ensure the Kirby/PHP requirements above are met. Generate `robots.txt` and `sitemap.xml`
as needed (`sitemap` generation is not included in this showcase).

---

## What was removed from the original

To make the setup shareable, the following were stripped from this copy:

- credentials and secrets (`.env`, Kirby license file, SMTP details),
- Kirby Panel accounts and password hashes,
- real content and personal data (events, contacts, bookings, newsletter subscribers),
- production `media/`, cache, session and log files,
- Kirby core (`kirby/`) and Composer dependencies (`vendor/`),
- the third-party `mzur/*` plugins (installed via Composer instead),
- site-specific identifiers (Google Search Console token, production email addresses).

All remaining content is fictional sample data.

---

## Attribution

Original website design and development: [Moritz Gauss](https://moritzgauss.com).
Built with [Kirby](https://getkirby.com) © Bastian Allgeier.
