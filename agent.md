# WORDORA — Project Knowledge & Agent Operating Guide v2.5

**Agency Name:** WORDORA  
**Tagline:** "Words That Work. Stories That Sell."  
**Design Direction:** "Premium literary magazine meets modern creative agency"  
**Website URL (Local):** `http://localhost/word/`  
**Admin URL (Local):** `http://localhost/word/admin/` (`info@wordora.in` / `admin123`)  

---

## 1. Core Architecture & Operating Protocols

1. **Continuous Documentation Sync:** Always keep `design.md` and `agent.md` fully up-to-date with every feature, visual change, and architectural addition.
2. **Immediate Cleanup:** Delete any temporary scripts, debug files, or reset scripts immediately after execution.
3. **Dynamic Base & Routing:** Never use hardcoded absolute root slashes (like `/assets/` or `/services.php`). Always use helpers: `url()`, `asset()`, `img()`, `media_url()`, `base_url()`.
4. **Universal Multi-Mode Hero Engine (All Pages):** Every page's Hero section (`index.php`, `who-we-are.php`, `services.php`, `contact.php`, `blog/index.php`, etc.) uses the unified `views/partials/hero-banner.php` multi-mode engine (`$heroPage = '...';`):
   - Supports 3 modes per page: **Multi-Slide Carousel (`slider`)**, **Single Image (`single`)**, and **HTML5 Video Loop (`video`)**.
   - Dual Video options across all studios: **Option 1 File Upload (MP4/WebM max 50MB)** + **Option 2 Direct URL input**.
   - Live video preview tile with dynamic video player and safe removal.
   - Database table `hero_slides` supports page scoping (`page` column) with admin studio management per page.
   - Deep Navy background (`--color-navy-deep: #0F1E36`) with subtle radial teal glow (`rgba(74, 139, 140, 0.22)`).
   - Matching height (`min-height: 640px` Desktop / `520px` Tablet / `480px` Mobile) and `130px 0 90px` padding.
   - Distinctive typography: Light Teal Mono Eyebrow, bold White `Playfair Display` headline, and translucent `Inter` description.
   - Dual action buttons (Primary + Ghost) and ambient floating icons (`floating-icons.php`).
   - Signature wavy hand-drawn SVG Ink divider (`ink-divider.php`) bottom transition.
5. **Physical File Deletion Policy:** When deleting or replacing media (images/videos) in admin studio editors, safely delete old uploaded files from `/uploads/` using `delete_uploaded_file(?string $path)` in `core/helpers.php` (never deletes system assets in `/img/`).

---

## 2. Directory Structure

```
WORDORA/
├── .htaccess                       ← Root routing, static asset pass-through, clean URL rewrite
├── index.php                       ← Root entry point delegating to public/index.php
├── design.md                       ← Complete Design System & Section Architecture (v2.5)
├── agent.md                        ← Project Knowledge Base & Feature Registry (v2.5)
├── wordora_db.sql                  ← Complete MySQL Schema + Seed Data (Pure UTF-8)
│
├── config/
│   └── config.php                  ← DB credentials, environment, 50MB upload limits
│
├── core/
│   ├── DB.php                      ← PDO Singleton (utf8mb4, strict mode)
│   ├── Auth.php                    ← Session auth, timeout, dynamic redirects
│   ├── CSRF.php                    ← Token generation, verification, meta/field helpers
│   ├── Upload.php                  ← Secure MIME finfo validation & file upload (50MB max)
│   └── helpers.php                 ← app_base(), url(), asset(), img(), media_url(), delete_uploaded_file()
│
├── models/
│   ├── Hero.php                    ← Hero Banner slides & mode manager per page
│   ├── Post.php                    ← Blog posts CRUD, search, view increment, file cleanup
│   ├── Category.php                ← Taxonomies & color tags
│   ├── Contact.php                 ← Inquiries & lead status tracking
│   ├── Service.php                 ← Agency services data, 12 detail columns & file cleanup
│   ├── Setting.php                 ← Key-value configuration & section registries
│   ├── Team.php                    ← Team profiles
│   ├── Testimonial.php             ← Client testimonials
│   └── User.php                    ← Admin users
│
├── views/
│   ├── layouts/
│   │   └── main.php                ← Public master layout (Fonts, RemixIcon, GSAP, Swiper)
│   ├── partials/
│   │   ├── nav.php                 ← Floating pill glassmorphic navbar with mega dropdown & Dynamic New Pill
│   │   ├── mobile-drawer.php       ← Off-canvas mobile navigation
│   │   ├── footer.php              ← Deep Navy editorial 4-column footer
│   │   ├── hero-banner.php         ← The Editorial Cover (Single / Slider / Video)
│   │   ├── floating-icons.php      ← GSAP ambient floating quill/pen icons
│   │   └── ink-divider.php         ← Hand-drawn SVG wavy divider
│
├── public/
│   ├── .htaccess                   ← Extensionless clean routing & blog slug handler
│   ├── index.php                   ← Homepage (Final 9-Section Master Flow)
│   ├── who-we-are.php              ← Who We Are Page (8 Sections Flow)
│   ├── services.php                ← What We Do / Services Page (8 Sections Flow)
│   ├── service-detail.php          ← Dedicated Individual Service Detail Page (8 Sections Flow)
│   ├── contact.php                 ← Contact Us Page (6 Sections + FAQ)
│   ├── blog/
│   │   ├── index.php               ← Blog Archive + Category Filter + Featured Card
│   │   └── post.php                ← Single Blog Article + Reading Progress Bar
│   └── api/
│       ├── contact.php             ← POST Inquiry Lead Endpoint (CSRF protected)
│       ├── subscribe.php           ← POST Newsletter Subscription Endpoint
│       └── blog-search.php         ← GET Live JSON Article Search Endpoint
│
├── admin/
│   ├── index.php                   ← Admin Dashboard (KPIs, Quick Navigation)
│   ├── login.php                   ← Admin Login Screen (Show/Hide Password Eye Button)
│   ├── logout.php                  ← Session Destroy & Redirect
│   ├── includes/
│   │   ├── header.php              ← Admin Header, Topbar, Flash Messages
│   │   ├── sidebar.php             ← Modern Dark Sidebar with active routes
│   │   ├── homepage-sections-editor.php ← 9-Tab Master Homepage Section Studio
│   │   ├── who-we-are-sections-editor.php ← 8-Tab Who We Are Section Studio
│   │   ├── services-sections-editor.php ← 8-Tab Services Section Studio (What We Do Matrix)
│   │   ├── careers-sections-editor.php  ← 6-Tab Careers Visual Studio & Job Applications Manager
│   │   └── footer.php              ← Admin Footer
│   ├── pages/
│   │   ├── home.php                ← Homepage Studio Controller
│   │   ├── who-we-are.php          ← Who We Are Studio Controller
│   │   ├── services.php            ← Services Studio Controller
│   │   └── careers.php             ← Careers & Job Applications Studio Controller
│   ├── hero/
│   │   ├── index.php               ← Multi-page Hero Slide List, Mode Settings
│   │   └── edit.php                ← Add/Edit Slide (Upload / Dual Video / Copy)
│   ├── posts/
│   │   ├── index.php               ← Blog Articles List & Unified Actions
│   │   └── edit.php                ← Add/Edit Article (Featured Image Upload / SEO)
│   ├── services/
│   │   ├── index.php               ← Service Detail Studio Index (List & Direct Studio Links)
│   │   └── edit.php                ← Full 8-Tab Service Detail Studio Editor (Single Image Hero Option)
│   ├── leads/
│   │   └── index.php               ← Contact Leads & Inquiries Manager
│   └── settings/
│       └── index.php               ← Site Configuration & Brand Assets
```

---

## 3. Master Homepage Flow (9 Editorial Sections)

1. **01 — HERO ("Words That Work. Stories That Sell."):** The Editorial Cover with Smart Multi-Mode Engine (Auto / Slider / Video / Single Image).
2. **02 — SELECTED WORK ("Stories built to move brands forward."):** Horizontal Editorial Case Study Swiper (`.work-showcase-swiper`) featuring premier client stories with clean arrow navigation buttons.
3. **03 — WHAT WE DO ("Content with a Job to Do."):** Asymmetric variable bento grid with 6 core services.
4. **03B — CAPABILITIES MARQUEE:** Continuous 3-row ticker on static paper banner (`papaer banner.png`) with solid frosted pills.
5. **04 — WHY BUSINESSES CHOOSE WORDORA:** Editorial story + 6-Pillar Value Proposition Grid + Magazine Pull Quote + Unboxed Stats (`1000+`, `170+`, `8+`, `98%`) with count-up animations.
6. **05 — WHO WE WRITE FOR ("Different Industries. One Obsession: Clarity."):** Horizontal Editorial Dashed Work Showcase Swiper (`.industry-work-swiper`) across 8 sectors with dashed borders, client artwork, and milestone metrics.
7. **06 — HOW WE WORK ("From Brief to Brilliance."):** 4-step process framework with connecting ink line.
8. **07 — PROOF / CLIENT STORIES ("The Work Speaks Too."):** Split editorial testimonial stage with avatar, verified badge, 5 gold stars, and 4-item result metrics banner.
9. **08 — FROM THE EDITORIAL DESK ("Ideas Worth Reading."):** Staggered blog grid pulling live published articles via `Post::getPublished(3)`.
10. **09 — START A CONVERSATION ("Start Something Worth Reading."):** Deep Navy rounded CTA panel with consultation buttons and trust pills.

---

## 4. Who We Are Page Architecture (8 Master Sections Spec)

1. **01 — HERO:** Full homepage-height cover banner (`min-height: 640px`) with deep navy background, directional gradient overlay, Playfair Display typography, dual action buttons, and floating ambient icons.
2. **02 — OUR MISSION:** Editorial split with magazine pull quote, agency narrative from Agra, and `img/why choose us.png` with ambient backdrop glow.
3. **03 — FULL-WIDTH 3-ROW CAPABILITIES MARQUEE:** Full-width 3-row continuous ticker with solid frosted glass pills (`0.94` opacity), static paper banner background (`img/papaer banner.png`).
4. **04 — OUR JOURNEY:** 2-column layout with sticky roadmap illustration (`img/journey.png`) + 5 vertical timeline milestones with connecting ink rail, glowing dots, and JetBrains Mono year badges.
5. **05 — CORE EDITORIAL VALUES:** Luxury Deep Navy container with `#faf8f5` tilted diamond artwork card (`img/value.png`) + 3 glowing dark glass value cards (Craft, Clarity, Conversion).
6. **06 — MEET THE TEAM:** 4-member editorial team grid with stylized initials avatars (PS, RV, AS, KM), roles, specialties, and LinkedIn profiles.
7. **07 — WHY BRANDS CHOOSE WORDORA:** 6-pillar value proposition grid + 4-item results metric strip (1000+ Projects Delivered, 170+ Happy Clients, 98% Retention, 8+ Years).
8. **08 — START A CONVERSATION:** Signature Deep Navy rounded CTA card matching homepage section 09 with dual consultation buttons, trust pills, and `img/cta 1.png`.

---

## 5. What We Do / Services Matrix Page Architecture (8 Master Sections Spec)

1. **01 — THE EDITORIAL COVER (HERO):** Multi-mode Hero Banner (Multi-Slide Carousel, Single Image, Background Video with max 50MB upload + direct URL).
2. **02 — QUICK JUMP PILL BAR & MATRIX HEADER:** Floating quick jump bar with anchor pills linking to all active services + section headline and description.
3. **03 — MASTER SERVICE SHOWCASE (STACKING CARDS):** Stacking sticky card deck where each card features verified deliverable badge, title, editorial description, core capability bullets grid, metric performance impact pill, dual CTA buttons (Explore Detailed Scope + Get Quote), and dashed media artwork frame.
4. **04 — OUR METHODOLOGY (4-STAGE EDITORIAL FRAMEWORK):** Deep Navy luxury container with left-side tilted `#FAF8F5` process graphic artwork card (`img/process.png`) and 4 glowing dark glass stage cards: Discovery, Architecture, Craftsmanship, and Polish.
5. **05 — THE EDITORIAL ADVANTAGE (COMPARISON TABLE):** 5-pillar evaluation table comparing Commodity/AI content vs. WORDORA Human Editorial across Research Depth, Search Engine Intent, Voice & Nuance, Turnaround Governance, and Commercial ROI.
6. **06 — ENGAGEMENT MODELS & SCOPE TIERS:** 3-tier scoping cards (Sprint Topic Cluster Engine, Most Popular Brand Voice & Launch, Executive C-Suite Retainer) with bullet points, CTAs, and featured card toggle.
7. **07 — FREQUENTLY ASKED QUESTIONS (FAQ):** Interactive accordions answering domain expertise, AI policy, turnaround, revisions, and NDAs + right-side tilted `#FAF8F5` FAQ artwork frame (`img/faq.png`).
8. **08 — START A CONVERSATION CTA SIGNATURE:** Full-bleed luxury banner with eyebrow badge, title, dual buttons (Start a Conversation + Our Editorial Story), trust pills (24h Response, NDA Protected, Free Content Audit), and `img/cta 1.png`.

---

## 6. Individual Service Detail Studio Architecture (8 Dedicated Sections — 1:1 Exact Frontend Sequence)

Each individual discipline (`/service-detail.php?slug=...`) is powered by its own dedicated 8-tab Visual Studio (`admin/services/edit.php`) structured in the **exact same 1-to-1 top-to-bottom sequence as the live frontend page**:
- **Top Service Switcher Bar:** Instant 1-click switcher pills for all 7 active disciplines (SEO Content, Social Media, Technical Writing, Brand Copy, Thought Leadership, Academic Writing, Blog Writing) with active highlight and `+ Add New Discipline` action.
- **Section Tabs in 1:1 Live Sequence:**
  1. **01 — HERO COVER (SINGLE IMAGE HERO OPTION):** Custom Single Background Image option (`hero_image`) with dark overlay, bold headline (`hero_headline`), lead intro (`hero_intro`), eyebrow discipline badge, and dual conversion CTAs.
  2. **02 — WHAT WE DO (DETAILED INTRODUCTION & ARTWORK):** In-depth bold lead paragraph (`what_we_do_lead`), extended narrative (`description`), performance metrics strip (`metrics_val`, `metrics_lbl`), and service master artwork mockup upload (`image_path`).
  3. **03 — WHAT'S INCLUDED (CORE CAPABILITIES CARDS):** Full interactive 8-card grid editor (Card Number, RemixIcon `icon`, Title `title`, In-depth Scope Description `desc`, Scope Tag `badge` e.g. `VERIFIED SCOPE`), plus deliverables summary (`deliverables`) and category badge (`tag`).
  4. **04 — OUR PRODUCTION PROCESS (4-STAGE FRAMEWORK):** 4 step-by-step milestone cards (Discovery, Blueprint, Craftsmanship, Polish) stored in `process_steps` JSON.
  5. **05 — WHY THIS DISCIPLINE MATTERS (3 IMPACT PILLARS):** 3 strategic impact pillars with custom icons, headlines, and descriptions stored in `why_matters` JSON.
  6. **06 — WHO THIS SERVICE IS BUILT FOR (4 TARGET PERSONAS):** 4 target buyer persona cards with role name and specific business benefits stored in `who_for` JSON.
  7. **07 — FREQUENTLY ASKED QUESTIONS (5 FAQS):** 5 domain-specific Q&A items resolving client objections stored in `faqs` JSON.
  8. **08 — BOTTOM CTA SIGNATURE & PAGE STATUS:** Tailored bottom CTA headline (`cta_title`), description (`cta_desc`), primary proposal button (`cta_btn_text`, `cta_btn_url`), sort order (`sort_order`), and active status published toggle (`is_active`).

### Careers Visual Section Studio & Job Applications Manager (`admin/pages/careers.php`)
- Dedicated 6-tab luxury studio managing `public/careers.php` and candidate submissions:
  1. **01 — HERO COVER:** Eyebrow badge, main headline, lead intro paragraph, and custom hero background image option.
  2. **02 — PERKS & REMOTE HIGHLIGHTS:** 4 remote working highlights cards (Icon, Title, Subtitle).
  3. **03 — OPEN JOB ROLES (INTERACTIVE ROLES MANAGER):** Dynamic CRUD for job openings (Title, Department, Department Slug, Type, Location, Salary, Experience, Excerpt, Overview, Responsibilities list, Tags, Active toggle) + `+ Add New Job Opening` button.
  4. **04 — WORKING PHILOSOPHY (4 CULTURE PILLARS):** 4 cultural pillar cards (Number, Icon, Title, Description).
  5. **05 — 4-STAGE HIRING PROTOCOL:** 4 step cards (Number, SLA Badge, Title, Description).
  6. **06 — JOB APPLICATIONS & RESUMES:** Integrated submissions manager with status KPIs (`Total`, `Pending`, `Reviewed`, `Shortlisted`, `Rejected`), filter pills, candidate data table, direct **Resume File Download (PDF/DOC)** button, 1-click status dropdown, view full profile modal, and delete action.

---

## 7. Admin Panel Design System & Standards

- **Unified Action Button System (`.table-actions`):**
  - **Edit Button (`.btn-adm-action.btn-adm-edit`):** Warm ivory `#FAF8F5` with subtle `#CBD5E1` border, transition to teal `#D4EAEA` with teal glow on hover.
  - **Delete Button (`.btn-adm-action.btn-adm-delete`):** Soft rose `#FEF2F2` with crimson text `#DC2626` and border `#FECACA`, transition to solid crimson `#DC2626` with white text on hover.
  - **Standardized across:** Homepage Editor, Who We Are Editor, Services Editor, Service Detail Studio, Hero Slides Studio, Blog Articles Index, Services Index, and Contact Leads.
- **Universal Hero Visual Overlay & Coverage Engine:**
  - Site Settings (`admin/settings/index.php`) provides **Global Hero Overlay Opacity** (`hero_overlay_opacity`, 0% to 100%) and **Horizontal Shading Coverage Area** (`hero_gradient_coverage`, 25% to 90%) with a **Real-Time Interactive Visual Preview Bar**.
  - Centralized in `get_hero_directional_gradient()` helper function in `core/helpers.php`.
  - Dynamically powers all hero sections across the entire website (`views/partials/hero-banner.php`, `service-detail.php`, `blog/post.php`).
- **Dynamic Navbar Mega-Dropdown & Auto-Sync:**
  - "What We Do" navbar mega-dropdown dynamically loops over `Service::getActive()`.
  - Newly created or modified services automatically appear in the navbar menu and What We Do stacking cards deck in accordance with their `sort_order`.
- **Clean Empty Structure on Service Creation:**
  - Clicking `Add New Service Page` (`admin/services/edit.php?id=new`) yields a completely clean blank structure with guided placeholders for immediate authoring.
- **Dedicated Service Detail Studio Image Resolution & Multi-Tab State Protection:**
  - `admin/services/edit.php` accurately resolves active hero preview: Custom Uploaded Hero -> Service Artwork Mockup -> Default Service Illustration (`img/Blog service.png`, `img/social media service.png`, etc.).
  - Replaced checkboxes with instant action buttons (`Revert to Default Artwork`) with dynamic UI state updates.
  - Multi-tab save protection: Submitting any individual tab (Tab 01 Hero Cover, Tab 02 Scope, Tab 03 Pillars, etc.) automatically preserves `is_active`, sort order, and all other tabs' data without accidental zeroing.
- **Section Studio Consistency:**
  - All Section Studios (`home.php`, `who-we-are.php`, `services.php`, `admin/services/edit.php`) feature identical Studio Headers with icon, title, description, and "View Live" button.
  - Identical luxury background `--wdr-canvas: #FAF8F5;`, `1.5px dashed rgba(74, 139, 140, 0.4)` border, `Playfair Display` headings, `Inter` body typography, and `JetBrains Mono` labels.
  - Standardized Tab Navigation Pill Bar with numbered badges (`01`, `02`, etc.) and responsive horizontal scrolling.
- **Universal JSON & Semicolon Bullets Backward Compatibility:**
  - All public views (`services.php`, `service-detail.php`, `admin/services/index.php`, `services-sections-editor.php`) seamlessly unpack capability items whether stored as structured JSON card objects (`[{"title": "..."}]`) or legacy semicolon-separated strings (`"Item 1; Item 2"`), preventing any raw JSON strings from leaking into the UI.
- **Strict Media File Handling (Max 50MB):**
  - All file upload inputs configured for max 50MB (52,428,800 bytes).
  - Video fields support both direct file upload and external CDN/URL.
  - Replaced or removed media is automatically deleted from disk in `/uploads/`.
- **Contact Us & Inquiries Studio Architecture:**
  - `admin/pages/contact.php` & `admin/includes/contact-sections-editor.php` provide 4 visual tabs:
    - Tab 01: Hero Cover & Atmosphere (Single Cover / Slider / HTML5 Video + Directional Gradient).
    - Tab 02: Consultation Form & Direct Contact Hub (Brief Form, dynamic services dropdown repeater, managing editor showcase card, 4 contact metric boxes, and enterprise retainer box).
    - Tab 03: FAQ Accordion Section & Artwork Frame (dynamic question-answer repeater with reorder/delete, and custom tilted artwork frame upload).
    - Tab 04: Contact Inquiries Directory (KPI summary stats, status filters, 1-click status update, view full brief modal, direct email response trigger).
  - `public/contact.php` is 100% dynamic, powered by `settings` table keys (`contact_form_...`, `contact_showcase_...`, `contact_info_cards`, `contact_enterprise_...`, `contact_faq_...`).
- **Case Studies Visual Section Studio & Database Architecture:**
  - `admin/pages/case-studies.php` & `admin/includes/case-studies-sections-editor.php` provide 4 tabs:
    - Tab 01: Hero Cover & Atmosphere (Single / Slider / Video + Cover Artwork upload).
    - Tab 02: Case Studies Directory (10+ detailed commercial case studies table with sort orders, metrics, active status).
    - Tab 03: Authoring Studio with full **Quill.js WYSIWYG Rich Text Editor** for Challenge, Solution, Deliverables, Results Summary, Metrics, and Testimonials.
    - Tab 04: Bottom Commercial Audit CTA Bar.
  - Public pages (`public/case-studies.php`, `public/case-study-detail.php`) load dynamically from `CaseStudy` model.
- **Blog & Dispatch Visual Section Studio with Full WYSIWYG Suite:**
  - `admin/pages/blog.php` & `admin/includes/blog-sections-editor.php` provide 4 visual tabs:
    - Tab 01: Hero Cover & Atmosphere.
    - Tab 02: Newsletter & Lead Capture Section.
    - Tab 03: Taxonomic Categories Manager with live article counters.
    - Tab 04: Articles Directory & WYSIWYG Editor Launcher.
  - `admin/posts/edit.php` integrated with Quill 2.0 WYSIWYG editor supporting Headings (H1-H6), Bold, Italic, Underline, Bullet/Numbered Lists, Blockquotes, Code blocks, Links, Color, Media, and seamless form synchronization.
