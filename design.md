# WORDORA — Comprehensive Design System & UI Specification v2.5

**Brand Name:** WORDORA  
**Tagline:** "Words That Work. Stories That Sell."  
**Core Identity:** Premium Editorial Content Studio & Brand Copywriting Agency  
**Design Direction:** "Premium literary magazine meets modern creative agency"  
**Location:** Agra, Uttar Pradesh, India  

---

## 1. Official Color Palette & Design Tokens

Strictly use these exact CSS custom properties:

```css
:root {
  /* Brand Core Palette */
  --color-navy:          #1B2A4A;   /* Primary headings, hero text, primary buttons */
  --color-navy-deep:     #0F1E36;   /* Dark hero mode, footer, admin sidebar */
  --color-teal-ink:      #4A8B8C;   /* Primary accent, active links, icons, badges */
  --color-teal-light:    #6BA8A9;   /* Hover accents, highlights */
  --color-teal-pale:     #D4EAEA;   /* Card backgrounds, tag highlights, soft surfaces */
  --color-white:         #FFFFFF;   /* Cards, dropdowns, inputs */
  --color-canvas:        #FAF8F5;   /* Warm editorial canvas background */

  /* Semantic Variables */
  --color-text-main:     #0F1E36;
  --color-text-muted:    #4A627A;
  --color-text-light:    #8DA4B8;
  --color-border:        #E2E8EE;
  --color-border-subtle: rgba(74, 139, 140, 0.2);

  /* Shadows & Glass */
  --shadow-sm:           0 2px 8px rgba(15, 30, 54, 0.04);
  --shadow-md:           0 8px 24px rgba(15, 30, 54, 0.07);
  --shadow-lg:           0 16px 40px rgba(15, 30, 54, 0.10);
  --shadow-glow:         0 0 30px rgba(74, 139, 140, 0.25);
  --glass-bg:            rgba(255, 255, 255, 0.88);
  --glass-border:        rgba(212, 234, 234, 0.6);
  --glass-blur:          blur(16px);
}
```

---

## 2. Typography Hierarchy

| Role | Font Family | Weights | Usage |
|---|---|---|---|
| **Display / Headlines** | `Playfair Display`, serif | 700, 900, 900 italic | Hero titles, Section headings, Magazine Pull Quotes |
| **Body Text** | `Inter`, sans-serif | 400, 500, 600 | Editorial copy, article paragraphs, descriptions |
| **UI / Navigation / Buttons** | `DM Sans`, sans-serif | 500, 600, 700 | Navbar links, badges, buttons, form labels |
| **Stats & Numbers** | `JetBrains Mono`, monospace | 500, 600 | Numeric counters, milestone dates, metadata |

---

## 3. Container System

- **Main Container (`.container`):** `max-width: 1320px`
- **Editorial Content (`.container-editorial`):** `max-width: 1180px`
- **Hero Canvas (`.container-hero`):** `max-width: 1380px`
- **Text-Heavy Layouts (`.container-text`):** `max-width: 760px`

---

## 3B. Universal Hero Section Standard & Directional Gradient Overlay (Entire Site)

> [!IMPORTANT]
> **Mandatory Universal Rule**: Sabhi pages (`index.php`, `who-we-are.php`, `services.php`, `service-detail.php`, `blog/index.php`, `blog/post.php`, `contact.php`) ke Hero sections me **Directional Gradient Overlay** mandatory hai:
> - **Left 50% (Text Area):** Rich high-contrast dark overlay (`rgba(15, 30, 54, 0.96) -> rgba(15, 30, 54, 0.82) -> rgba(15, 30, 54, 0.18)`) taaki white text, headlines aur buttons 100% crisp aur readable rahein.
> - **Right 50% (Artwork / Image Area):** **0.0 (ZERO) Opacity** (`rgba(15, 30, 54, 0.0) 65% -> 100%`) taaki custom artwork, desk illustration, ya uploaded background image bina kisi dark veil/fade ke full high-definition clarity me dikhe.

1. **Multi-Mode Engine (`views/partials/hero-banner.php`)**:
   - **Mode 1: Multi-Slide Carousel (`slider`)**: Swiper.js fade slider where each slide features its own full background artwork, eyebrow, main headline, description, and dual buttons.
   - **Mode 2: Single Background Image (`single` / `single_image`)**: Static high-impact background artwork with directional gradient overlay.
   - **Mode 3: HTML5 Video Hero (`video`)**: Autoplay looping background video with directional dark overlay.
2. **Dual Video Source Options**:
   - **Option 1: File Upload**: MP4/WebM files up to **50MB** (`52428800` bytes).
   - **Option 2: Direct URL**: External video URL / CDN.
   - Live video preview tile with dynamic video player and remove button.
3. **Physical File Cleanup**:
   - Safely removes unlinked/replaced media in `/uploads/` via `delete_uploaded_file()`.

---

## 4. Admin Panel Unified Design Standards

1. **Unified Table Actions (`.table-actions`):**
   - **Edit Button (`.btn-adm-action.btn-adm-edit`):** `#FAF8F5` background, `#CBD5E1` border, `#1B2A4A` text. On hover: `#D4EAEA` background, `#4A8B8C` border, teal glow.
   - **Delete Button (`.btn-adm-action.btn-adm-delete`):** `#FEF2F2` background, `#FECACA` border, `#DC2626` text. On hover: solid `#DC2626` background, `#FFFFFF` text.
2. **Visual Section Studios (Homepage, Who We Are, Services Matrix, Service Detail Studio):**
   - **Standard Header:** Left-aligned icon + title + description + Right-aligned "View Live" button.
   - **Standard Tab Navigation:** Horizontal pill list with numbered badges (`01`, `02`, etc.) and active Navy card highlight.
   - **Media Uploaders:** Visual dashed dropzones with instant live preview and remove buttons.
3. **Standard Buttons:**
   - `.btn-adm.btn-adm-primary`: Deep Navy `#1B2A4A` with white text.
   - `.btn-adm.btn-adm-outline`: Transparent with `#CBD5E1` border and `#1B2A4A` text.

---

## 5. What We Do / Services Matrix Page Architecture (8 Master Sections Spec)

1. **01 — HERO:** Full Deep Navy editorial cover banner with multi-mode slider / single image / video and dual CTAs.
2. **02 — QUICK JUMP PILL BAR & MATRIX HEADER:** Floating quick jump bar with anchor pills linking to all active services + section headline and description.
3. **03 — MASTER SERVICE SHOWCASE (STACKING CARDS):** Stacking sticky card deck where each card features verified deliverable badge, title, editorial description, core capability bullets grid, metric performance impact pill, dual CTA buttons (Explore Detailed Scope + Get Quote), and dashed media artwork frame.
4. **04 — OUR METHODOLOGY (4-STAGE EDITORIAL FRAMEWORK):** Deep Navy luxury container with left-side tilted `#FAF8F5` process graphic artwork card (`img/process.png`) and 4 glowing dark glass stage cards: Discovery, Architecture, Craftsmanship, and Polish.
5. **05 — THE EDITORIAL ADVANTAGE (COMPARISON TABLE):** 5-pillar evaluation table comparing Commodity/AI content vs. WORDORA Human Editorial across Research Depth, Search Engine Intent, Voice & Nuance, Turnaround Governance, and Commercial ROI.
6. **06 — ENGAGEMENT MODELS & SCOPE TIERS:** 3-tier scoping cards (Sprint Topic Cluster Engine, Most Popular Brand Voice & Launch, Executive C-Suite Retainer) with bullet points, CTAs, and featured card toggle.
7. **07 — FREQUENTLY ASKED QUESTIONS (FAQ):** Interactive accordions answering domain expertise, AI policy, turnaround, revisions, and NDAs + right-side tilted `#FAF8F5` FAQ artwork frame (`img/faq.png`).
8. **08 — START A CONVERSATION CTA SIGNATURE:** Full-bleed luxury banner with eyebrow badge, title, dual buttons (Start a Conversation + Our Editorial Story), trust pills (24h Response, NDA Protected, Free Content Audit), and `img/cta 1.png`.

---

## 6. Dedicated Individual Service Detail Page & Studio (8 Sections Architecture)

Each individual service page (`public/service-detail.php?slug=...`) and its dedicated studio (`admin/services/edit.php`):
1. **01 — HERO COVER (SINGLE IMAGE HERO OPTION):** Custom Single Background Image option (`hero_image`) with dark overlay, bold headline (`hero_headline`), lead intro (`hero_intro`), eyebrow discipline badge, and dual conversion CTAs.
2. **02 — WHAT WE DO DETAILED NARRATIVE & SCOPE:** In-depth bold lead paragraph (`what_we_do_lead`), extended narrative (`description`), included sub-capabilities bullets (`bullets`), deliverables summary (`deliverables`), category tag (`tag`), RemixIcon (`icon`), and service artwork mockup upload (`image_path`).
3. **03 — WHY IT MATTERS (3 PILLARS):** 3 strategic impact pillars with custom icons, headlines, and descriptions stored in `why_matters` JSON.
4. **04 — WHO THIS SERVICE IS FOR (4 PERSONAS):** 4 target buyer persona cards with role name and specific business benefits stored in `who_for` JSON.
5. **05 — 4-STAGE PRODUCTION PROCESS:** 4 step-by-step milestone cards (Discovery, Blueprint, Craftsmanship, Polish) stored in `process_steps` JSON.
6. **06 — DELIVERABLES & METRICS PERFORMANCE:** Verified growth lift metric value (`metrics_val` e.g. `+400%`, `4x`), metric label (`metrics_lbl`), sort order (`sort_order`), and active status toggle (`is_active`).
7. **07 — FREQUENTLY ASKED QUESTIONS (5 FAQS):** 5 domain-specific Q&A items resolving client objections stored in `faqs` JSON.
8. **08 — BOTTOM CONSULTATION SIGNATURE:** Tailored bottom CTA headline (`cta_title`), description (`cta_desc`), and primary proposal button (`cta_btn_text`, `cta_btn_url`).
