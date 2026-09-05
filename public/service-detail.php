<?php
/**
 * WORDORA — Service Detail Page (Master 8-Section Architecture)
 * Matches full site luxury aesthetics, typography, and interactive components.
 */
if (!defined('ROOT_PATH')) define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/core/helpers.php';

// Get current service by slug or id
$slug = trim($_GET['slug'] ?? '');
$service = null;

if (!empty($slug)) {
    $service = Service::getBySlug($slug);
    if (!$service && is_numeric($slug)) {
        $service = Service::getById((int)$slug);
    }
    if (!$service) {
        // Fallback: match by prefix or similarity
        $allServices = Service::getAll();
        foreach ($allServices as $sItem) {
            if (str_starts_with($sItem['slug'], $slug) || str_starts_with($slug, $sItem['slug'])) {
                $service = $sItem;
                break;
            }
        }
    }
}

if (!$service) {
    require_once ROOT_PATH . '/public/404.php';
    exit;
}

// Access control: If this is a development/tech service (id > 7) and dev services are toggled OFF -> show 404 Error Page
$isDevService = (int)($service['id'] ?? 0) > 7;
$devServicesEnabled = (setting('home_sec3c_enabled', '1') !== '0');
if ($isDevService && !$devServicesEnabled) {
    require_once ROOT_PATH . '/public/404.php';
    exit;
}

// Canonical 301 redirect if requested via legacy service-detail.php query string
$reqUri = $_SERVER['REQUEST_URI'] ?? '';
if (str_contains($reqUri, 'service-detail.php') && !empty($service['slug'])) {
    header('Location: ' . url('service/' . $service['slug']), true, 301);
    exit;
}

$fallbackFile = resolve_service_illustration($service['slug']);
$serviceImage = !empty($service['image_path']) ? media_url($service['image_path'], img($fallbackFile)) : img($fallbackFile);

// Parse included items / sub-services (user provided list)
$rawBullets = !empty($service['bullets']) ? array_filter(array_map('trim', explode(';', $service['bullets']))) : [];

// Service-specific detailed data dictionaries
$serviceDataBank = [
    'seo-content' => [
        'hero_headline' => 'Search-Dominant Content That Turns Traffic into Pipeline',
        'hero_intro' => 'Topic cluster architecture, search-intent optimization, and rigorous long-form guides engineered to capture page-one rankings and convert high-intent buyers.',
        'what_we_do_lead' => 'We don’t write generic keyword-stuffed articles. We engineer search-dominant content assets that establish category leadership.',
        'what_we_do_desc' => 'Every piece begins with semantic keyword clustering, competitor search-gap audits, and searcher intent mapping. Our senior domain writers draft comprehensive, authoritative guides that rank high on Google and satisfy reader questions completely.',
        'why_matters' => [
            ['title' => 'Compound Organic Traffic', 'desc' => 'Build high-authority topic clusters that generate qualified inbound leads 24/7 without recurring ad spend.', 'icon' => 'ri-line-chart-line'],
            ['title' => 'Google Helpful Content Proof', 'desc' => '100% human-researched and cited. Immune to algorithmic updates and AI content penalties.', 'icon' => 'ri-shield-check-line'],
            ['title' => 'Pipeline Conversion Focus', 'desc' => 'Every article includes strategic internal links, CTA placements, and conversion pathways tailored to your sales funnel.', 'icon' => 'ri-funds-box-line'],
        ],
        'who_for' => [
            ['role' => 'B2B SaaS Companies', 'desc' => 'Scaling organic product demo requests and outranking entrenched enterprise competitors.'],
            ['role' => 'E-Commerce Brands', 'desc' => 'Dominating high-intent transactional search terms and category buying guides.'],
            ['role' => 'Growth Startups', 'desc' => 'Establishing category authority and lowering customer acquisition costs from day one.'],
            ['role' => 'Digital Marketing Agencies', 'desc' => 'White-label editorial support that delivers verified search ranking improvements for clients.'],
        ],
        'faqs' => [
            ['q' => 'How do you choose target keywords for our articles?', 'a' => 'We conduct extensive competitor keyword gap analyses and search intent audits using enterprise SEO tooling, identifying high-intent commercial terms your buyers are actively searching for.'],
            ['q' => 'Are all SEO articles 100% written by humans?', 'a' => 'Yes, unconditionally. Every article is written by experienced domain writers, rigorously fact-checked, and polished by managing editors. We never use generative AI filler.'],
            ['q' => 'Do you include on-page SEO, meta schemas, and formatting?', 'a' => 'Yes. Every deliverable includes optimized title tags, meta descriptions, header structure (H1/H2/H3), schema suggestions, and internal linking schematics ready for CMS publishing.'],
            ['q' => 'What is the typical turnaround time for an SEO article?', 'a' => 'Standard long-form articles (1,500 – 2,500 words) are delivered within 5 to 7 business days, including full research and editorial review.'],
            ['q' => 'How many revisions are included in the scope?', 'a' => 'Every project includes two full rounds of editorial revisions within 14 days of delivery to ensure complete alignment with your vision.'],
        ]
    ],
    'social-media-content' => [
        'hero_headline' => 'Scroll-Stopping Social Copy Built for Community & Conversion',
        'hero_intro' => 'Captions, multi-slide carousels, and high-converting campaign copy structured to capture attention, drive viral engagement, and build loyal brand followings.',
        'what_we_do_lead' => 'Social feeds move fast. We craft sharp, compelling copy that stops the thumb and sparks genuine conversations.',
        'what_we_do_desc' => 'From strategic monthly content calendars to high-impact launch sequences, our social copywriters tailor tone, cadence, and hooks specifically for Instagram, LinkedIn, X, and Facebook.',
        'why_matters' => [
            ['title' => 'High-Retention Hooks', 'desc' => 'First-line hooks and visual narrative framing designed to maximize algorithmic reach and dwell time.', 'icon' => 'ri-flashlight-line'],
            ['title' => 'Consistent Brand Voice', 'desc' => 'Unified brand personality across all platforms so your audience immediately recognizes your stature.', 'icon' => 'ri-sparkling-fill'],
            ['title' => 'Community to Pipeline', 'desc' => 'Transform passive social followers into active subscribers, event attendees, and product customers.', 'icon' => 'ri-user-star-line'],
        ],
        'who_for' => [
            ['role' => 'Direct-to-Consumer Brands', 'desc' => 'Building viral product hype, user-generated momentum, and repeat customer loyalty.'],
            ['role' => 'B2B Founders & Execs', 'desc' => 'Scaling LinkedIn engagement, sharing company milestones, and attracting talent.'],
            ['role' => 'Creator & Media Brands', 'desc' => 'Maintaining high-velocity posting schedules with pristine editorial craft.'],
            ['role' => 'Marketing Teams', 'desc' => 'Freeing internal bandwidth by outsourcing monthly social copy calendars.'],
        ],
        'faqs' => [
            ['q' => 'Which social media platforms do you write copy for?', 'a' => 'We create tailored content for LinkedIn, Instagram, X (Twitter), Facebook, Threads, and YouTube Community posts.'],
            ['q' => 'Do you provide visual design or just copy?', 'a' => 'We deliver complete copy decks including slide-by-slide carousel scripts, visual design prompts, and headline hierarchies ready for your design team.'],
            ['q' => 'How far in advance do you deliver monthly content calendars?', 'a' => 'Monthly calendars are delivered 7 to 10 days before the start of the month, allowing ample time for review and scheduling.'],
            ['q' => 'Can you adapt to our brand’s unique humor and voice?', 'a' => 'Absolutely. We review your past top-performing posts and brand guidelines to mirror your voice seamlessly.'],
            ['q' => 'Do you include hashtag strategy and call-to-actions?', 'a' => 'Yes. Every post includes platform-optimized hashtag clusters and engagement-driven closing questions or link CTAs.'],
        ]
    ],
    'technical-writing' => [
        'hero_headline' => 'Complex Architectures Explained with Crystal Clarity',
        'hero_intro' => 'Developer documentation, OpenAPI references, SDK tutorials, and cloud architecture whitepapers that eliminate friction and speed up engineering adoption.',
        'what_we_do_lead' => 'Engineers value precision. We bridge the gap between complex software systems and intuitive developer documentation.',
        'what_we_do_desc' => 'Our technical writers possess backgrounds in software engineering, cloud infrastructure, and technical journalism. We test endpoints, verify code snippets, and build documentation that developers love to use.',
        'why_matters' => [
            ['title' => 'Accelerate Time-to-Hello-World', 'desc' => 'Reduce developer drop-off with frictionless quickstarts, verified code samples, and clear SDK guides.', 'icon' => 'ri-code-s-slash-line'],
            ['title' => 'Deflect Support Tickets', 'desc' => 'Comprehensive knowledge bases and troubleshooting guides that save engineering hours.', 'icon' => 'ri-customer-service-2-line'],
            ['title' => 'Win Enterprise RFPs', 'desc' => 'Authoritative architecture whitepapers and security overviews that satisfy enterprise compliance teams.', 'icon' => 'ri-shield-keyhole-line'],
        ],
        'who_for' => [
            ['role' => 'API-First SaaS Platforms', 'desc' => 'Empowering external developers to integrate with your REST, GraphQL, or gRPC APIs seamlessly.'],
            ['role' => 'DevTool & Infrastructure Startups', 'desc' => 'Translating cutting-edge cloud infrastructure into clear setup and CLI tutorials.'],
            ['role' => 'Enterprise IT Providers', 'desc' => 'Authoring technical whitepapers, migration guides, and compliance documentation.'],
            ['role' => 'Product Teams', 'desc' => 'Creating end-user knowledge bases, onboarding walkthrus, and feature release notes.'],
        ],
        'faqs' => [
            ['q' => 'Can your writers work directly in Markdown and Git repositories?', 'a' => 'Yes. We frequently submit pull requests directly to Git repositories (GitHub, GitLab) formatted for Docusaurus, Mintlify, GitBook, or MkDocs.'],
            ['q' => 'Do you test code samples before publishing?', 'a' => 'Yes. All SDK snippets, API endpoints, and configuration files are executed and verified in test sandbox environments.'],
            ['q' => 'Can you help restructure an existing messy documentation suite?', 'a' => 'Absolutely. We perform full information architecture audits, reorganizing topics into intuitive mental models for developers.'],
            ['q' => 'Do you sign NDAs before accessing pre-release software?', 'a' => 'Yes, unconditionally. We protect all proprietary codebases, roadmaps, and architecture blueprints under strict mutual NDAs.'],
            ['q' => 'What languages and frameworks do your writers understand?', 'a' => 'We cover Python, JavaScript/TypeScript, Go, Rust, Java, PHP, Docker, Kubernetes, AWS, GCP, and modern microservice architectures.'],
        ]
    ],
    'brand-copy' => [
        'hero_headline' => 'Words That Define Categories and Command Market Stature',
        'hero_intro' => 'Brand voice bibles, high-converting homepage copy, taglines, and campaign messaging that forge an emotional connection and separate you from competitors.',
        'what_we_do_lead' => 'Great products fail when they speak with a generic voice. We craft unforgettable brand copy that commands respect.',
        'what_we_do_desc' => 'We deconstruct your customer psychology, market positioning, and core differentiation to build a comprehensive messaging architecture that resonates across every touchpoint.',
        'why_matters' => [
            ['title' => 'Unmistakable Positioning', 'desc' => 'Own a distinct category narrative that competitors cannot easily copy or dilute.', 'icon' => 'ri-trophy-line'],
            ['title' => 'Conversion Rate Uplift', 'desc' => 'Clear value propositions that immediately answer "Why you?" within 5 seconds of landing.', 'icon' => 'ri-percent-line'],
            ['title' => 'Brand Loyalty & Trust', 'desc' => 'Consistent voice governance across marketing, sales decks, and customer communications.', 'icon' => 'ri-heart-3-line'],
        ],
        'who_for' => [
            ['role' => 'Rebranding Companies', 'desc' => 'Modernizing stale messaging to reflect market leadership and enterprise maturity.'],
            ['role' => 'Early-Stage Founders', 'desc' => 'Defining the initial brand manifesto, mission statement, and elevator pitch for launch.'],
            ['role' => 'Consumer & Luxury Brands', 'desc' => 'Crafting evocative packaging copy, campaign taglines, and manifesto stories.'],
            ['role' => 'Creative Agencies', 'desc' => 'Partnering on full-scale brand identity and messaging deck overhauls.'],
        ],
        'faqs' => [
            ['q' => 'What does a Brand Voice Bible include?', 'a' => 'It includes your brand manifesto, core value propositions, tone spectrum (Dos and Don’ts), vocabulary lexicon, headline formulas, and sample copy across web, email, and social.'],
            ['q' => 'How do you discover our true brand voice?', 'a' => 'We conduct founder interviews, analyze customer reviews, study competitor positioning gaps, and test message prototypes against your target audience.'],
            ['q' => 'Do you write the full website copy or just headlines?', 'a' => 'We provide complete end-to-end page copy decks (Hero, features, social proof, objection teardowns, microcopy, CTAs, and metadata).'],
            ['q' => 'How many tagline options will we receive?', 'a' => 'We typically present 15 to 20 distinct tagline concepts organized into strategic thematic angles, followed by refinement of top choices.'],
            ['q' => 'How long does a full brand messaging sprint take?', 'a' => 'A comprehensive Brand Voice & Website Messaging deck is typically completed in 10 to 14 business days.'],
        ]
    ],
    'thought-leadership' => [
        'hero_headline' => 'Executive Ghostwriting for Founders & C-Suite Market Leaders',
        'hero_intro' => 'Strategic LinkedIn essays, viral industry breakdown carousels, and founder narratives that build undeniable authority and generate high-ticket B2B pipeline.',
        'what_we_do_lead' => 'Your insights are your company’s greatest distribution asset. We turn executive expertise into commanding thought leadership.',
        'what_we_do_desc' => 'Through monthly 30-minute voice capture interviews, our executive ghostwriters extract your proprietary insights, battle scars, and strategic opinions, transforming them into viral, polished editorial essays.',
        'why_matters' => [
            ['title' => 'Direct Inbound Pipeline', 'desc' => 'Attract enterprise buyers, strategic partners, and tier-1 talent directly through executive social presence.', 'icon' => 'ri-radar-line'],
            ['title' => 'Zero Executive Time Waste', 'desc' => 'A high-leverage 30-minute interview fuels an entire month of thought-leadership content.', 'icon' => 'ri-time-line'],
            ['title' => 'Category Voice Authority', 'desc' => 'Position your founder or managing partner as the go-to expert cited by industry peers and media.', 'icon' => 'ri-megaphone-line'],
        ],
        'who_for' => [
            ['role' => 'Venture-Backed Founders', 'desc' => 'Building category buzz, hiring top engineers, and keeping investors engaged.'],
            ['role' => 'C-Suite Executives (CEOs, CMOs, CTOs)', 'desc' => 'Establishing thought leadership stature in enterprise tech, finance, and healthcare.'],
            ['role' => 'Managing Partners & Investors', 'desc' => 'Publishing investment theses, portfolio breakdowns, and market commentary.'],
            ['role' => 'B2B Consultants & Advisors', 'desc' => 'Generating high-ticket retainer client inquiries without cold outbound.'],
        ],
        'faqs' => [
            ['q' => 'How do you capture my authentic tone of voice?', 'a' => 'We conduct a monthly 30-minute recorded conversation and analyze your past writing, voice memos, and podcasts to mirror your exact cadence and perspective.'],
            ['q' => 'Will anyone know that I use a ghostwriter?', 'a' => 'No. All ghostwriting relationships are strictly protected under mutual NDAs. You retain 100% full authorship and copyright.'],
            ['q' => 'How much time will I need to dedicate each month?', 'a' => 'Only 30 to 45 minutes for the monthly discovery call, plus 10 minutes to review and approve the drafted content.'],
            ['q' => 'Do you also design LinkedIn carousel slide graphics?', 'a' => 'Yes. We deliver complete multi-slide carousel graphics formatted in Figma or PDF ready for native LinkedIn upload.'],
            ['q' => 'What if I disagree with an angle or want changes?', 'a' => 'We provide unlimited minor revisions on monthly retainers until you are 100% satisfied with every post before scheduling.'],
        ]
    ],
    'academic-writing' => [
        'hero_headline' => 'Rigorous Research, Peer-Reviewed Syntheses & Scholarly Depth',
        'hero_intro' => 'Academic monographs, literature reviews, dissertations, and research papers meticulously structured, formatted, and citation-verified to institutional standards.',
        'what_we_do_lead' => 'Scholarly work demands uncompromising intellectual rigor, authentic primary citations, and methodical clarity.',
        'what_we_do_desc' => 'Our academic writing team consists of post-graduate researchers and subject-matter scholars across STEM, humanities, law, and business sciences. Every document adheres to rigorous academic standards and institutional formatting.',
        'why_matters' => [
            ['title' => 'Flawless Citation Rigor', 'desc' => 'Accurate APA, Harvard, Chicago, MLA, and IEEE bibliographic references and inline citations.', 'icon' => 'ri-file-list-3-line'],
            ['title' => 'Primary Data Synthesis', 'desc' => 'Comprehensive literature matrices, methodology designs, and quantitative/qualitative analysis.', 'icon' => 'ri-database-2-line'],
            ['title' => 'Zero Plagiarism Guarantee', 'desc' => '100% original academic synthesis backed by institutional Turnitin authenticity reports.', 'icon' => 'ri-verified-badge-line'],
        ],
        'who_for' => [
            ['role' => 'Graduate & PhD Scholars', 'desc' => 'Structuring complex dissertations, literature reviews, and thesis methodology frameworks.'],
            ['role' => 'Academic Institutions', 'desc' => 'Authoring institutional whitepapers, conference proceedings, and curriculum materials.'],
            ['role' => 'Research Think Tanks', 'desc' => 'Publishing policy briefs, economic impact teardowns, and scholarly monographs.'],
            ['role' => 'Corporate Research Labs', 'desc' => 'Drafting peer-reviewed journal submissions and technical whitepaper studies.'],
        ],
        'faqs' => [
            ['q' => 'Which citation and referencing styles do you support?', 'a' => 'We strictly adhere to APA 7th, Harvard, Chicago (Notes & Bibliography or Author-Date), MLA 9th, IEEE, Vancouver, and OSCOLA.'],
            ['q' => 'How do you guarantee original research and zero plagiarism?', 'a' => 'Every document is written from primary academic sources and verified through institutional Turnitin screening with 0% AI and 0% plagiarism scores.'],
            ['q' => 'Can you work with specific data sets and statistical models?', 'a' => 'Yes. We synthesize SPSS, R, Python, and Excel data models, constructing clear methodology discussions and analytical appendices.'],
            ['q' => 'What academic disciplines do your writers cover?', 'a' => 'Our team includes specialists in Computer Science, Economics, Business Strategy, Public Health, Law, Engineering, and Social Sciences.'],
            ['q' => 'How are revisions handled for academic papers?', 'a' => 'We provide comprehensive revisions based on advisor or committee feedback until the deliverable meets full academic specifications.'],
        ]
    ],
    'blog-writing' => [
        'hero_headline' => 'Editorial Blog Content Built to Educate, Engage & Convert',
        'hero_intro' => 'High-engagement blog posts, company news digests, industry analysis essays, and thought-provoking articles crafted to turn casual readers into loyal brand advocates.',
        'what_we_do_lead' => 'A company blog shouldn’t be an afterthought. It is your brand’s public editorial publication and trust engine.',
        'what_we_do_desc' => 'We create polished, journalism-grade blog articles that entertain, educate, and establish your brand as the leading voice in your sector. Every article is written with compelling storytelling, rich insights, and seamless conversion paths.',
        'why_matters' => [
            ['title' => 'High Reader Retention', 'desc' => 'Engaging editorial prose with storytelling hooks that keep readers reading until the final sentence.', 'icon' => 'ri-book-read-line'],
            ['title' => 'Multi-Channel Repurposing', 'desc' => 'Every blog post is structured for effortless repurposing into LinkedIn posts, newsletters, and social snippets.', 'icon' => 'ri-repeat-line'],
            ['title' => 'Brand Trust & Inbound', 'desc' => 'Authoritative problem-solving content that positions your product as the natural solution.', 'icon' => 'ri-shield-star-line'],
        ],
        'who_for' => [
            ['role' => 'Growing B2B Businesses', 'desc' => 'Educating prospects on industry trends and establishing thought leadership.'],
            ['role' => 'Consumer Brands & Startups', 'desc' => 'Sharing company updates, product tutorials, culture stories, and founder insights.'],
            ['role' => 'Professional Services', 'desc' => 'Publishing legal, financial, and strategic advisories that win high-value clients.'],
            ['role' => 'Digital Publishers', 'desc' => 'Maintaining high editorial quality across high-volume publishing calendars.'],
        ],
        'faqs' => [
            ['q' => 'How often can you deliver blog articles?', 'a' => 'We structure weekly, bi-weekly, or daily editorial sprint packages depending on your publishing velocity and growth goals.'],
            ['q' => 'Do you provide topic ideation and editorial planning?', 'a' => 'Yes. We build quarterly editorial calendars with topic pitches, target audience hooks, and outline briefs for your approval.'],
            ['q' => 'Can you publish directly into our WordPress, Webflow, or Ghost CMS?', 'a' => 'Yes. We can format, upload, add royalty-free imagery, configure tags, and stage drafts directly inside your CMS.'],
            ['q' => 'What length are your standard blog posts?', 'a' => 'We write across short-form digests (800 – 1,200 words), standard guides (1,500 – 2,000 words), and deep-dive definitive guides (3,000+ words).'],
            ['q' => 'Are social media promotional snippets included with blog posts?', 'a' => 'Yes. Every blog article includes 2 tailored LinkedIn/Twitter social copy snippets to promote the post upon launch.'],
        ]
    ]
];

// Alias mapping for full slugs
$serviceDataBank['seo-content-writing'] = &$serviceDataBank['seo-content'];

$details = $serviceDataBank[$service['slug']] ?? $serviceDataBank['seo-content'];

// Structured Core Capabilities (What's Included Grid)
$capabilitiesList = [];
if (!empty($service['bullets'])) {
    $decodedBullets = json_decode($service['bullets'], true);
    if (is_array($decodedBullets) && !empty($decodedBullets)) {
        $capabilitiesList = $decodedBullets;
    } else {
        $parts = array_filter(array_map('trim', explode(';', $service['bullets'])));
        $iconsList = ['ri-quill-pen-line', 'ri-file-text-line', 'ri-layout-masonry-line', 'ri-search-eye-line', 'ri-focus-3-line', 'ri-sparkling-fill', 'ri-shield-star-line', 'ri-compass-3-line'];
        foreach ($parts as $idx => $pt) {
            $capabilitiesList[] = [
                'icon' => $iconsList[$idx % count($iconsList)],
                'title' => $pt,
                'desc' => 'Deeply researched, drafted to institutional standards, and fully aligned with your audience expectations.',
                'badge' => 'VERIFIED SCOPE'
            ];
        }
    }
}
if (empty($capabilitiesList)) {
    $defaultCaps = [
        'Strategic Topic Clustering', 'Search Intent Alignment', 'Expert Primary Research',
        'Comprehensive Outline Architecture', 'Human Domain Craftsmanship', 'Multi-Layer Editorial Review',
        'Fact-Checking & Citations', 'CMS-Ready Formatting & Polish'
    ];
    $iconsList = ['ri-quill-pen-line', 'ri-file-text-line', 'ri-layout-masonry-line', 'ri-search-eye-line', 'ri-focus-3-line', 'ri-sparkling-fill', 'ri-shield-star-line', 'ri-compass-3-line'];
    foreach ($defaultCaps as $idx => $dc) {
        $capabilitiesList[] = [
            'icon' => $iconsList[$idx % count($iconsList)],
            'title' => $dc,
            'desc' => 'Deeply researched, drafted to institutional standards, and fully aligned with your audience expectations.',
            'badge' => 'VERIFIED SCOPE'
        ];
    }
}

$whyMattersList = !empty($service['why_matters']) ? json_decode($service['why_matters'], true) : ($details['why_matters'] ?? []);
$whoForList = !empty($service['who_for']) ? json_decode($service['who_for'], true) : ($details['who_for'] ?? []);
$processStepsList = !empty($service['process_steps']) ? json_decode($service['process_steps'], true) : ($details['process_steps'] ?? []);
$faqsList = !empty($service['faqs']) ? json_decode($service['faqs'], true) : ($details['faqs'] ?? []);

$heroHeadline = !empty($service['hero_headline']) ? $service['hero_headline'] : $service['title'];
$heroIntro = !empty($service['hero_intro']) ? $service['hero_intro'] : ($details['hero_intro'] ?? $service['description']);
$heroBgImage = !empty($service['hero_image']) ? media_url($service['hero_image']) : (!empty($service['image_path']) ? media_url($service['image_path']) : img($fallbackFile));
$heroGradient = get_hero_directional_gradient();

// Prepare SEO Meta Tags (from Admin SEO Settings or Service attributes)
$svcSeoTitle = setting("seo_service_{$service['slug']}_title");
$svcSeoDesc  = setting("seo_service_{$service['slug']}_desc");
$svcSeoKw    = setting("seo_service_{$service['slug']}_keywords");
$svcSeoOg    = setting("seo_service_{$service['slug']}_og_image");

$meta = [
    'title'       => !empty($svcSeoTitle) ? $svcSeoTitle : (e($service['title']) . ' — Bespoke Editorial & Content Engineering | WORDORA'),
    'description' => !empty($svcSeoDesc) ? $svcSeoDesc : e($heroIntro),
    'keywords'    => !empty($svcSeoKw) ? $svcSeoKw : (e($service['title']) . ', SEO writing, content agency, editorial engineering'),
    'og_image'    => !empty($svcSeoOg) ? media_url($svcSeoOg) : $heroBgImage,
    'og_title'    => !empty($svcSeoTitle) ? $svcSeoTitle : (e($service['title']) . ' — WORDORA'),
    'og_desc'     => !empty($svcSeoDesc) ? $svcSeoDesc : e($heroIntro),
];

ob_start();
?>

<!-- ═══════════════════════════════════════════
     SECTION 1 — HERO: SERVICE TITLE & CORE PROPOSITION (SINGLE IMAGE HERO OPTION)
     ═══════════════════════════════════════════ -->
<section class="hero hero--bg-image" id="heroSection" style="background-image: <?= $heroGradient ?>, url('<?= $heroBgImage ?>');">
  <div class="container container-hero" style="position: relative; z-index: 2;">
    <div class="hero__body-full">
      <span class="label-upper hero__eyebrow animate-hero-text" style="color: var(--color-teal-light);">
        CORE EDITORIAL DISCIPLINE &bull; <?= e($service['tag'] ?: 'VERIFIED CRAFT') ?>
      </span>
      <h1 class="heading-hero animate-hero-text" style="font-size: clamp(2.3rem, 4.2vw, 3.6rem);">
        <?= e($heroHeadline) ?>
      </h1>
      <p class="body-lg animate-hero-text" style="max-width: 680px; color: rgba(255,255,255,0.85); margin-bottom: var(--space-6);">
        <?= e($heroIntro) ?>
      </p>
      <div class="hero__actions animate-hero-text">
        <a href="<?= !empty($service['cta_btn_url']) ? url($service['cta_btn_url']) : url('contact.php?service=' . urlencode($service['title'])) ?>" class="btn btn-primary btn-lg">
          <?= e($service['cta_btn_text'] ?: 'Start a Conversation') ?> <i class="ri-arrow-right-line"></i>
        </a>
        <a href="#whats-included" class="btn btn-ghost btn-lg">
          What's Included <i class="ri-arrow-down-line"></i>
        </a>
      </div>
    </div>
  </div>

  <?php include ROOT_PATH . '/views/partials/floating-icons.php'; ?>
</section>

<!-- Ink Stroke Transition -->
<?php include ROOT_PATH . '/views/partials/ink-divider.php'; ?>


<!-- ═══════════════════════════════════════════
     SECTION 2 — WHAT WE DO: DETAILED INTRODUCTION
     ═══════════════════════════════════════════ -->
<section class="section" style="background: var(--color-canvas); padding: var(--space-16) 0;">
  <div class="container" style="max-width: 1320px;">
    <div class="svc-dark-grid" style="align-items: center;">
      
      <!-- Left Column: Detailed Narrative -->
      <div class="reveal-up">
        <span class="label-upper" style="margin-bottom: var(--space-3);">WHAT WE DO</span>
        <h2 style="font-family: var(--font-display); font-size: clamp(1.65rem, 2.5vw, 2.25rem); font-weight: 700; color: var(--color-navy); margin-top: 8px; margin-bottom: var(--space-4); line-height: 1.25;">
          <?= e($service['what_we_do_lead'] ?: ($details['what_we_do_lead'] ?? '')) ?>
        </h2>
        <p class="body-lg" style="color: var(--color-text); margin-bottom: var(--space-4); line-height: 1.75;">
          <?= e($service['description'] ?: ($details['what_we_do_desc'] ?? '')) ?>
        </p>
        <p style="color: var(--color-text-muted); font-size: 0.9375rem; line-height: 1.7; margin-bottom: var(--space-6);">
          Every project is assigned to a senior domain writer with specialized industry knowledge and paired with a managing editor for strict quality assurance and tone governance.
        </p>

        <!-- Metrics Strip -->
        <div style="display: flex; gap: 24px; flex-wrap: wrap; padding: 18px 22px; background: var(--color-white); border: 1.5px dashed rgba(74, 139, 140, 0.45); border-radius: var(--radius-lg); box-shadow: none !important;">
          <div>
            <div style="font-family: var(--font-display); font-size: 1.75rem; font-weight: 700; color: var(--color-teal-ink);"><?= e($service['metrics_val'] ?: '+400%') ?></div>
            <div style="font-size: 0.8125rem; color: var(--color-text-muted); font-weight: 500;"><?= e($service['metrics_lbl'] ?: 'Performance Lift') ?></div>
          </div>
          <div style="width: 1px; background: var(--color-border);"></div>
          <div>
            <div style="font-family: var(--font-display); font-size: 1.75rem; font-weight: 700; color: var(--color-navy);">100%</div>
            <div style="font-size: 0.8125rem; color: var(--color-text-muted); font-weight: 500;">Human-Crafted &amp; Fact-Checked</div>
          </div>
        </div>
      </div>

      <!-- Right Column: Framed Illustration Card (#ffffff with Dashed Border, Ultra-Large Scale, Zero Shadow) -->
      <div class="reveal-up text-center">
        <div class="svc-artwork-card" style="background: #ffffff !important; transform: rotate(-1.5deg); box-shadow: none !important; border: 1.5px dashed rgba(74, 139, 140, 0.45); max-width: 660px; padding: 28px; border-radius: 32px;">
          <img src="<?= $serviceImage ?>" alt="<?= e($service['title']) ?> Master Showcase" loading="lazy" style="max-height: 520px; width: 100%; object-fit: contain;">
        </div>
      </div>

    </div>
  </div>
</section>

<!-- Ink Stroke Transition -->
<?php include ROOT_PATH . '/views/partials/ink-divider.php'; ?>


<!-- ═══════════════════════════════════════════
     SECTION 3 — SERVICES / WHAT'S INCLUDED (8 CARDS GRID)
     ═══════════════════════════════════════════ -->
<section class="section" id="whats-included" style="background: var(--color-white); padding: var(--space-16) 0;">
  <div class="container" style="max-width: 1280px;">
    <div class="reveal-up text-center" style="max-width: 720px; margin: 0 auto var(--space-12);">
      <span class="label-upper">CORE CAPABILITIES</span>
      <h2 class="heading-xl" style="margin-top: var(--space-2); margin-bottom: var(--space-3); font-size: clamp(2rem, 3.5vw, 2.75rem);">What's Included in This Discipline</h2>
      <p class="body-lg">
        <?= !empty($service['deliverables']) ? e($service['deliverables']) : 'Comprehensive deliverables engineered to address your exact brand, search, and distribution requirements.' ?>
      </p>
    </div>

    <!-- Capabilities Cards Grid -->
    <div class="reveal-up" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 22px;">
      <?php 
      $iconsList = ['ri-quill-pen-line', 'ri-file-text-line', 'ri-layout-masonry-line', 'ri-search-eye-line', 'ri-focus-3-line', 'ri-sparkling-fill', 'ri-shield-star-line', 'ri-compass-3-line'];
      foreach ($capabilitiesList as $bIndex => $capItem): 
        $cardIcon = !empty($capItem['icon']) ? $capItem['icon'] : $iconsList[$bIndex % count($iconsList)];
        $cardTitle = is_array($capItem) ? ($capItem['title'] ?? '') : $capItem;
        $cardDesc = is_array($capItem) && !empty($capItem['desc']) ? $capItem['desc'] : 'Deeply researched, drafted to institutional standards, and fully aligned with your audience expectations.';
        $cardBadge = is_array($capItem) && !empty($capItem['badge']) ? $capItem['badge'] : 'VERIFIED SCOPE';
      ?>
      <div style="background: var(--color-canvas); border: 1.5px dashed rgba(74, 139, 140, 0.45); border-radius: var(--radius-lg); padding: 24px; display: flex; flex-direction: column; justify-content: space-between; box-shadow: none !important; transition: all 0.25s ease;" onmouseover="this.style.borderColor='var(--color-teal-ink)'; this.style.transform='translateY(-3px)';" onmouseout="this.style.borderColor='rgba(74, 139, 140, 0.45)'; this.style.transform='translateY(0)';">
        <div>
          <div style="width: 40px; height: 40px; border-radius: var(--radius-md); background: rgba(74, 139, 140, 0.12); color: var(--color-teal-ink); display: flex; align-items: center; justify-content: center; font-size: 1.2rem; margin-bottom: 14px;">
            <i class="<?= e($cardIcon) ?>"></i>
          </div>
          <h3 style="font-family: var(--font-body); font-weight: 700; font-size: 1.05rem; color: var(--color-navy); margin-bottom: 8px;">
            <?= e($cardTitle) ?>
          </h3>
          <p style="font-size: 0.84375rem; color: var(--color-text-muted); line-height: 1.5; margin: 0;">
            <?= e($cardDesc) ?>
          </p>
        </div>
        <div style="margin-top: 18px; display: flex; align-items: center; justify-content: space-between; border-top: 1px dashed var(--color-border); padding-top: 12px; font-size: 0.75rem; font-family: var(--font-mono); color: var(--color-teal-ink); font-weight: 600;">
          <span><?= e($cardBadge) ?></span>
          <i class="ri-check-double-line"></i>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

  </div>
</section>

<!-- Ink Stroke Transition -->
<?php include ROOT_PATH . '/views/partials/ink-divider.php'; ?>


<!-- ═══════════════════════════════════════════
     SECTION 4 — OUR PRODUCTION PROCESS
     ═══════════════════════════════════════════ -->
<section class="section svc-dark-section" id="our-approach">
  <div class="svc-radial-glow"></div>
  <div class="container" style="max-width: 1280px;">
    <div class="reveal-up text-center" style="max-width: 720px; margin: 0 auto var(--space-12);">
      <span class="label-upper" style="color: var(--color-teal-light);">OUR PRODUCTION PROCESS</span>
      <h2 class="heading-xl" style="margin-top: var(--space-2); margin-bottom: var(--space-3); color: var(--color-white); font-size: clamp(2rem, 3.5vw, 2.85rem);">The Production Framework</h2>
      <p class="body-lg" style="color: rgba(255, 255, 255, 0.78);">
        A transparent, milestone-driven workflow that guarantees exceptional depth, precision, and verified delivery.
      </p>
    </div>

    <div class="svc-dark-grid reveal-up" style="align-items: center; gap: 48px;">
      
      <!-- Left Column: Visual Production Showcase with Process Graphics (#ffffff, Zero Shadow) -->
      <div style="display: flex; flex-direction: column; gap: 20px;">
        
        <!-- Primary Process Framework Card (#ffffff, Zero Shadow) -->
        <div style="background: #ffffff; border-radius: 20px; padding: 22px; border: 1.5px dashed rgba(74, 139, 140, 0.45); transform: rotate(-1.5deg); text-align: center; box-shadow: none !important;">
          <img src="<?= img('process.png') ?>" alt="Editorial Production Process" loading="lazy" style="max-height: 230px; width: 100%; object-fit: contain;">
          <div style="margin-top: 10px; font-family: var(--font-mono); font-size: 0.75rem; font-weight: 700; color: var(--color-navy); letter-spacing: 0.05em; text-transform: uppercase;">
            <i class="ri-git-commit-line" style="color: var(--color-teal-ink);"></i> Milestone-Driven Framework
          </div>
        </div>

        <!-- Dual Sub-Graphics Grid -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
          <div style="background: #ffffff; border-radius: 16px; padding: 16px; border: 1.5px dashed rgba(74, 139, 140, 0.45); transform: rotate(1.2deg); text-align: center; box-shadow: none !important;">
            <img src="<?= img('roadmap.png') ?>" alt="Production Roadmap" loading="lazy" style="max-height: 120px; width: 100%; object-fit: contain;">
            <div style="margin-top: 6px; font-size: 0.6875rem; font-family: var(--font-mono); font-weight: 700; color: var(--color-navy);">Sprint Timelines</div>
          </div>

          <div style="background: #ffffff; border-radius: 16px; padding: 16px; border: 1.5px dashed rgba(74, 139, 140, 0.45); transform: rotate(-1.2deg); text-align: center; box-shadow: none !important;">
            <img src="<?= img('story time line.png') ?>" alt="Editorial Story Review" loading="lazy" style="max-height: 120px; width: 100%; object-fit: contain;">
            <div style="margin-top: 6px; font-size: 0.6875rem; font-family: var(--font-mono); font-weight: 700; color: var(--color-navy);">Quality Assurance</div>
          </div>
        </div>

      </div>

      <!-- Right Column: Interactive Dark Glass Production Cards -->
      <div class="svc-dark-cards-stack" style="display: flex; flex-direction: column; gap: 12px;">
        <?php foreach ($processStepsList as $idx => $st): ?>
        <div class="svc-dark-card" style="box-shadow: none !important; border: 1px solid rgba(212, 234, 234, 0.18); padding: 1.25rem 1.5rem;">
          <div class="svc-dark-card__num"><?= e($st['num'] ?? sprintf('%02d', $idx + 1)) ?></div>
          <div>
            <div class="svc-dark-card__title"><?= e($st['title']) ?></div>
            <p class="svc-dark-card__desc"><?= e($st['desc']) ?></p>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

    </div>
  </div>
</section>

<!-- Ink Stroke Transition -->
<?php include ROOT_PATH . '/views/partials/ink-divider.php'; ?>


<!-- ═══════════════════════════════════════════
     SECTION 5 — WHY THIS MATTERS (BUSINESS IMPACT & ROI)
     ═══════════════════════════════════════════ -->
<section class="section" style="background: var(--color-canvas); padding: var(--space-16) 0;">
  <div class="container" style="max-width: 1240px;">
    <div class="reveal-up text-center" style="max-width: 720px; margin: 0 auto var(--space-10);">
      <span class="label-upper">COMMERCIAL VALUE &amp; ROI</span>
      <h2 class="heading-xl" style="margin-top: var(--space-2); margin-bottom: var(--space-3);">Why This Discipline Matters</h2>
      <p class="body-lg">
        How strategic editorial craftsmanship directly impacts your bottom line, brand stature, and customer acquisition.
      </p>
    </div>

    <div class="reveal-up" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 24px;">
      <?php foreach ($whyMattersList as $why): ?>
      <div style="background: var(--color-white); border: 1.5px dashed rgba(74, 139, 140, 0.45); border-radius: var(--radius-xl); padding: 32px; display: flex; flex-direction: column; justify-content: space-between;">
        <div>
          <div style="width: 48px; height: 48px; border-radius: var(--radius-md); background: rgba(74, 139, 140, 0.12); color: var(--color-teal-ink); display: flex; align-items: center; justify-content: center; font-size: 1.35rem; margin-bottom: 18px;">
            <i class="<?= e($why['icon'] ?? 'ri-shield-check-line') ?>"></i>
          </div>
          <h3 style="font-family: var(--font-display); font-size: 1.35rem; color: var(--color-navy); margin-bottom: 10px;">
            <?= e($why['title']) ?>
          </h3>
          <p style="font-size: 0.9375rem; color: var(--color-text-muted); line-height: 1.65; margin: 0;">
            <?= e($why['desc']) ?>
          </p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Ink Stroke Transition -->
<?php include ROOT_PATH . '/views/partials/ink-divider.php'; ?>


<!-- ═══════════════════════════════════════════
     SECTION 6 — WHO IT'S FOR (AUDIENCE PERSONAS)
     ═══════════════════════════════════════════ -->
<section class="section" style="background: var(--color-white); padding: var(--space-16) 0;">
  <div class="container" style="max-width: 1240px;">
    <div class="reveal-up text-center" style="max-width: 720px; margin: 0 auto var(--space-10);">
      <span class="label-upper">IDEAL CLIENT PROFILE</span>
      <h2 class="heading-xl" style="margin-top: var(--space-2); margin-bottom: var(--space-3);">Who This Service Is Built For</h2>
      <p class="body-lg">
        Engineered for organizations and leaders that recognize content as a strategic growth driver.
      </p>
    </div>

    <div class="reveal-up" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 20px;">
      <?php foreach ($whoForList as $w): ?>
      <div style="background: var(--color-canvas); border: 1px solid var(--color-border); border-radius: var(--radius-lg); padding: 26px; display: flex; flex-direction: column;">
        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 10px;">
          <i class="ri-checkbox-circle-fill" style="color: var(--color-teal-ink); font-size: 1.15rem;"></i>
          <h3 style="font-family: var(--font-body); font-weight: 700; font-size: 1.05rem; color: var(--color-navy); margin: 0;">
            <?= e($w['role']) ?>
          </h3>
        </div>
        <p style="font-size: 0.875rem; color: var(--color-text-muted); line-height: 1.6; margin: 0;">
          <?= e($w['desc']) ?>
        </p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Ink Stroke Transition -->
<?php include ROOT_PATH . '/views/partials/ink-divider.php'; ?>


<!-- ═══════════════════════════════════════════
     SECTION 7 — FAQ (SERVICE-SPECIFIC QUESTIONS)
     ═══════════════════════════════════════════ -->
<section class="section" style="background: var(--color-canvas); padding: var(--space-16) 0;">
  <div class="container" style="max-width: 1280px;">
    <div class="reveal-up text-center" style="max-width: 720px; margin: 0 auto var(--space-8);">
      <span class="label-upper">FREQUENTLY ASKED QUESTIONS</span>
      <h2 class="heading-xl" style="margin-top: var(--space-2); margin-bottom: var(--space-3);">Everything You Need to Know</h2>
      <p class="body-lg">Clear answers on how we scope, draft, refine, and deliver <?= e($service['title']) ?>.</p>
    </div>

    <div class="svc-faq-split reveal-up">
      <!-- FAQ Accordions (Left Side) -->
      <div style="display: flex; flex-direction: column; gap: 14px;">
        <?php foreach ($faqsList as $faq): ?>
        <details class="svc-faq">
          <summary>
            <span><?= e($faq['q']) ?></span>
            <i class="ri-arrow-down-s-line"></i>
          </summary>
          <div class="svc-faq__body">
            <?= e($faq['a']) ?>
          </div>
        </details>
        <?php endforeach; ?>
      </div>

      <!-- FAQ Artwork Illustration Frame (Right Side — Tilted #ffffff Frame) -->
      <div class="svc-faq-artwork">
        <div class="svc-faq-artwork-frame">
          <img src="<?= img('FAQ 2.png') ?>" alt="Frequently Asked Questions Illustration" loading="lazy">
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Ink Stroke Transition -->
<?php include ROOT_PATH . '/views/partials/ink-divider.php'; ?>


<!-- ═══════════════════════════════════════════
     SECTION 8 — FINAL CTA: LET'S CREATE SOMETHING WORTH READING
     ═══════════════════════════════════════════ -->
<section class="section" style="padding-top: var(--space-12); padding-bottom: var(--space-20); background: var(--color-canvas);">
  <div class="container">
    <div class="cta-signature reveal-up">
      <div class="cta-signature__content">
        <span class="badge" style="background: rgba(74, 139, 140, 0.35); color: var(--color-teal-pale); margin-bottom: var(--space-3); border: 1px solid rgba(212, 234, 234, 0.25);">
          <i class="ri-sparkling-fill"></i> READY TO ELEVATE YOUR WORDS?
        </span>
        
        <h2 class="cta-signature__title">
          <?= e($service['cta_title'] ?: "Let's create something worth reading.") ?>
        </h2>
        
        <p class="cta-signature__text">
          <?= e($service['cta_desc'] ?: "Tell us about your brand, your timeline, and what you need written. We'll deliver a tailored " . e($service['title']) . " proposal within 24 hours.") ?>
        </p>

        <div class="cta-signature__actions">
          <a href="<?= !empty($service['cta_btn_url']) ? url($service['cta_btn_url']) : url('contact.php?service=' . urlencode($service['title'])) ?>" class="btn btn-primary btn-lg">
            <?= e($service['cta_btn_text'] ?: 'Start Your Project') ?> <i class="ri-arrow-right-line"></i>
          </a>
          <a href="<?= url('contact.php?service=' . urlencode($service['title'])) ?>" class="btn btn-ghost btn-lg">
            Get a Quote <i class="ri-chat-1-line"></i>
          </a>
        </div>

        <div class="cta-trust-pills">
          <span class="cta-trust-pill"><i class="ri-checkbox-circle-fill"></i> 24h Response SLA</span>
          <span class="cta-trust-pill"><i class="ri-shield-check-fill"></i> NDA Protected</span>
          <span class="cta-trust-pill"><i class="ri-file-list-3-fill"></i> Free Scope Audit</span>
        </div>
      </div>

      <div class="cta-artwork-wrap">
        <img src="<?= img('cta 1.png') ?>" alt="Start Your Project with WORDORA" loading="lazy">
      </div>
    </div>
  </div>
</section>

<?php
$content = ob_get_clean();
include ROOT_PATH . '/views/layouts/main.php';
?>
