<?php
/**
 * WORDORA — Careers Page
 * Layout: 3x3 Job Openings Grid + Department Filter Tabs + Restyled Philosophy & 4-Stage Protocol + Wide Interactive Modal Popup with Resume Upload
 */
if (!defined('ROOT_PATH')) define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/core/helpers.php';
require_once ROOT_PATH . '/core/CSRF.php';

$allJobs = json_decode(setting('careers_jobs', '[]'), true) ?: [];
$jobs = !empty($allJobs) ? array_values(array_filter($allJobs, fn($j) => !isset($j['is_active']) || !empty($j['is_active']))) : [];

// Compute dynamic department filters
$deptCounts = [];
foreach ($jobs as $j) {
    $dSlug = $j['department_slug'] ?? slugify($j['department'] ?? 'editorial-writing');
    $dName = $j['department'] ?? 'Editorial & Writing';
    if (!isset($deptCounts[$dSlug])) {
        $deptCounts[$dSlug] = ['name' => $dName, 'count' => 0];
    }
    $deptCounts[$dSlug]['count']++;
}

$jobCount  = count($jobs);
$deptCount = count($deptCounts);
$roleWord  = $jobCount === 1 ? 'Role' : 'Roles';
$discWord  = $deptCount === 1 ? 'Discipline' : 'Disciplines';

$meta = [
    'title' => 'Careers & Open Roles — Join WORDORA Editorial Collective',
    'description' => "Explore {$jobCount} open remote {$roleWord} across {$deptCount} {$discWord} at WORDORA. We are hiring senior technical writers, B2B SaaS strategists, investigative editors, and brand copywriters globally.",
];

// Dynamic Settings from Admin Studio
$heroBadge = setting('careers_hero_badge', 'JOIN OUR GLOBAL EDITORIAL COLLECTIVE');
$heroTitle = setting('careers_hero_title', 'Build The Future of Editorial Authority & Brand Narrative');
$heroDesc  = setting('careers_hero_desc', 'We are hiring world-class technical writers, investigative editors, SEO topic architects, and creative copywriters to author high-stakes content for top-tier brands worldwide.');
$heroImage = setting('careers_hero_image', '');
$heroBgImage = !empty($heroImage) ? media_url($heroImage) : img('home section 2.png');
$heroGradient = get_hero_directional_gradient();

$perks = json_decode(setting('careers_perks', '[]'), true);
if (empty($perks)) {
    $perks = [
        ['icon' => 'ri-global-line', 'title' => '100% Remote Global', 'desc' => 'Work from anywhere on Earth'],
        ['icon' => 'ri-wallet-3-line', 'title' => 'Top 10% Compensation', 'desc' => 'Competitive INR pay & performance bonuses'],
        ['icon' => 'ri-book-open-line', 'title' => 'Annual Learning Stipend', 'desc' => 'Books, courses & hardware allowance'],
        ['icon' => 'ri-time-line', 'title' => 'Async & Flexible Hours', 'desc' => 'Zero timesheets or useless meetings'],
    ];
}

$jobsBadge    = setting('careers_jobs_badge', 'CURRENT OPPORTUNITIES');
$jobsTitleRaw = setting('careers_jobs_title', '{count} Open Roles Across {disciplines} Disciplines');
$jobsDesc     = setting('careers_jobs_desc', 'Explore our active remote openings. Every position includes full async flexibility and competitive compensation.');

// Dynamic title formatting with placeholder support & auto-syncing
$jobsTitle = str_replace(
    ['{count}', '{roles}', '{disciplines}', '{depts}'],
    [$jobCount, $jobCount . ' ' . $roleWord, $deptCount, $deptCount . ' ' . $discWord],
    $jobsTitleRaw
);

// If the title still contains fixed numbers (e.g. "9 Open Roles Across 4 Disciplines"), dynamically sync them to actual live counts
if (preg_match('/\b\d+\s+Open\s+Role[s]?\s+Across\s+\d+\s+Discipline[s]?\b/i', $jobsTitle)) {
    $jobsTitle = preg_replace('/\b\d+\s+Open\s+Role[s]?\s+Across\s+\d+\s+Discipline[s]?\b/i', "{$jobCount} Open {$roleWord} Across {$deptCount} {$discWord}", $jobsTitle);
} elseif (preg_match('/\b\d+\s+Open\s+Role[s]?\b/i', $jobsTitle)) {
    $jobsTitle = preg_replace('/\b\d+\s+Open\s+Role[s]?\b/i', "{$jobCount} Open {$roleWord}", $jobsTitle);
}

$pillarsBadge = setting('careers_pillars_badge', 'OUR WORKING PHILOSOPHY');
$pillarsTitle = setting('careers_pillars_title', 'Built For Writers Who Care Deeply About Craft');
$pillarsDesc  = setting('careers_pillars_desc', 'We don\'t do agency churn or AI prompt-spinning. We operate as an elite editorial guild with uncompromising standards, fair compensation, and radical autonomy.');
$pillars = json_decode(setting('careers_pillars', '[]'), true) ?: [];

$protocolBadge = setting('careers_protocol_badge', 'OUR 4-STAGE HIRING PROTOCOL');
$protocolTitle = setting('careers_protocol_title', 'Transparent, Fast & Respectful of Your Time');
$protocolDesc  = setting('careers_protocol_desc', 'We respond to every candidate application within 48 hours. No 7-round interview marathons.');
$protocol = json_decode(setting('careers_protocol', '[]'), true) ?: [];

ob_start();
?>

<!-- ═══════════════════════════════════════════
     01 — HERO BANNER (CLEAN & SPACIOUS)
     ═══════════════════════════════════════════ -->
<section class="hero hero--bg-image" id="heroSection" style="background-image: <?= $heroGradient ?>, url('<?= $heroBgImage ?>');">
  <div class="hero__overlay-radial"></div>

  <div class="container container-hero" style="position: relative; z-index: 2;">
    <div class="hero__body-full" style="max-width: 980px;">
      
      <span class="label-upper hero__eyebrow animate-hero-text" style="color: var(--color-teal-light);">
        <i class="ri-rocket-line"></i> <?= e($heroBadge) ?>
      </span>

      <h1 class="heading-hero animate-hero-text" style="font-size: clamp(2.2rem, 3.8vw, 3.3rem); line-height: 1.2; margin-bottom: var(--space-4);">
        <?= e($heroTitle) ?>
      </h1>

      <p class="body-lg animate-hero-text" style="max-width: 820px; color: rgba(255, 255, 255, 0.85); margin-bottom: 0; line-height: 1.65;">
        <?= e($heroDesc) ?>
      </p>

    </div>
  </div>

  <?php include ROOT_PATH . '/views/partials/floating-icons.php'; ?>
</section>

<!-- Ink Stroke Transition (Clean, completely visible and unblocked) -->
<?php include ROOT_PATH . '/views/partials/ink-divider.php'; ?>


<!-- ═══════════════════════════════════════════
     PERKS & REMOTE WORKING HIGHLIGHTS BAR (CLEAN BELOW INK DIVIDER)
     ═══════════════════════════════════════════ -->
<div class="container" style="max-width: 1280px; margin-top: var(--space-8); margin-bottom: var(--space-12); position: relative; z-index: 5;">
  <div class="reveal-up" style="background: #ffffff; border: 1.5px dashed rgba(74, 139, 140, 0.45); border-radius: 20px; padding: 22px 30px; display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; box-shadow: none !important;">
    <?php foreach ($perks as $p): ?>
    <div style="display: flex; align-items: center; gap: 14px;">
      <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(74, 139, 140, 0.12); color: var(--color-teal-ink); display: flex; align-items: center; justify-content: center; font-size: 1.3rem; flex-shrink: 0; border: 1px dashed rgba(74,139,140,0.3);">
        <i class="<?= e($p['icon'] ?: 'ri-global-line') ?>"></i>
      </div>
      <div>
        <div style="font-weight: 700; color: var(--color-navy); font-size: 0.9375rem;"><?= e($p['title']) ?></div>
        <div style="font-size: 0.75rem; color: var(--color-text-muted);"><?= e($p['desc']) ?></div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>


<!-- ═══════════════════════════════════════════
     02 — 3x3 JOB OPENINGS GRID SECTION
     ═══════════════════════════════════════════ -->
<section class="section" style="background: var(--color-canvas); padding: 0 0 var(--space-20);">
  <div class="container" style="max-width: 1280px;">

    <!-- Section Header & Filter Tabs -->
    <div class="reveal-up" style="display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 20px; margin-bottom: var(--space-8); padding-bottom: var(--space-4); border-bottom: 1.5px dashed rgba(74, 139, 140, 0.3);">
      <div>
        <span class="label-upper"><?= e($jobsBadge) ?></span>
        <h2 class="heading-lg" style="color: var(--color-navy); margin-top: 4px;">
          <?= e($jobsTitle) ?>
        </h2>
      </div>

      <!-- Department Filter Pills -->
      <div style="display: flex; gap: 8px; flex-wrap: wrap;" id="careerFilters">
        <button class="btn btn-primary btn-sm career-filter-btn is-active" data-dept="all" style="padding: 7px 16px; font-size: 0.8125rem;">
          All Roles (<?= count($jobs) ?>)
        </button>
        <?php foreach ($deptCounts as $dSlug => $dData): ?>
        <button class="btn btn-ghost btn-sm career-filter-btn" data-dept="<?= e($dSlug) ?>" style="padding: 7px 16px; font-size: 0.8125rem; background: #ffffff; border: 1px dashed rgba(74, 139, 140, 0.35); color: var(--color-navy);">
          <?= e($dData['name']) ?> (<?= (int)$dData['count'] ?>)
        </button>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- 3x3 Jobs Grid -->
    <div class="grid-3" id="jobsContainer" style="gap: 28px;">
      <?php foreach ($jobs as $job): ?>
      <div class="job-card reveal-up" data-dept="<?= e($job['department_slug'] ?? slugify($job['department'] ?? 'editorial-writing')) ?>" 
           style="background: #ffffff; border: 1.5px dashed rgba(74, 139, 140, 0.35); border-radius: 20px; padding: 28px; display: flex; flex-direction: column; justify-content: space-between; transition: all 0.3s ease; box-shadow: none !important;">
        
        <div>
          <!-- Top Category & Remote Pills -->
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 8px;">
            <span class="badge badge-teal" style="font-size: 0.72rem; text-transform: uppercase;">
              <?= e($job['department']) ?>
            </span>
            <span style="font-size: 0.75rem; color: var(--color-teal-ink); font-family: var(--font-mono); font-weight: 700; display: inline-flex; align-items: center; gap: 4px;">
              <i class="ri-checkbox-circle-fill"></i> <?= e($job['location']) ?>
            </span>
          </div>

          <!-- Job Title -->
          <h3 style="font-family: var(--font-display); font-size: 1.2rem; line-height: 1.35; color: var(--color-navy); margin-bottom: 10px;">
            <a href="javascript:void(0)" onclick='openJobModal(<?= json_encode($job, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)' style="text-decoration: none; color: inherit; transition: color 0.2s ease;" class="job-title-link">
              <?= e($job['title']) ?>
            </a>
          </h3>

          <!-- Compensation & Experience Ribbon -->
          <div style="display: flex; gap: 10px; align-items: center; margin-bottom: 14px; font-size: 0.8125rem; font-family: var(--font-mono); color: var(--color-teal-ink); font-weight: 700;">
            <span><i class="ri-wallet-3-line"></i> <?= e($job['salary']) ?></span>
            <span style="color: var(--color-border);">•</span>
            <span style="color: var(--color-text-muted);"><?= e($job['experience']) ?></span>
          </div>

          <!-- Excerpt -->
          <p style="font-size: 0.84375rem; color: var(--color-text-muted); line-height: 1.6; margin-bottom: 18px;">
            <?= e($job['excerpt']) ?>
          </p>

          <!-- Tags -->
          <?php if (!empty($job['tags']) && is_array($job['tags'])): ?>
          <div style="display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 22px;">
            <?php foreach ($job['tags'] as $t): ?>
            <span style="font-size: 0.72rem; padding: 3px 9px; background: #FAF8F5; border: 1px dashed rgba(74, 139, 140, 0.3); border-radius: 6px; color: var(--color-navy); font-weight: 600;">
              #<?= e($t) ?>
            </span>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
        </div>

        <!-- Action Button -> Opens Modal Popup -->
        <div style="padding-top: 14px; border-top: 1px dashed var(--color-border);">
          <button type="button" onclick='openJobModal(<?= json_encode($job, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)' class="btn btn-ghost btn-sm" style="width: 100%; justify-content: center; background: #FAF8F5; border: 1.5px dashed rgba(74, 139, 140, 0.4); color: var(--color-navy); font-size: 0.8125rem; font-weight: 700; cursor: pointer;">
            <span>View Role &amp; Apply</span> <i class="ri-arrow-right-line"></i>
          </button>
        </div>

      </div>
      <?php endforeach; ?>
    </div>

  </div>
</section>


<!-- ═══════════════════════════════════════════
     03 — WHY WORK AT WORDORA: RESTYLED CULTURE PILLARS (4 COLUMNS)
     ═══════════════════════════════════════════ -->
<section class="section" style="background: #ffffff; border-top: 1.5px dashed var(--color-border); border-bottom: 1.5px dashed var(--color-border); padding: var(--space-16) 0;">
  <div class="container" style="max-width: 1280px;">
    
    <div class="section-header reveal-up text-center" style="max-width: 760px; margin: 0 auto var(--space-10);">
      <span class="label-upper" style="color: var(--color-teal-ink); font-weight: 700;"><?= e($pillarsBadge) ?></span>
      <h2 class="heading-xl" style="color: var(--color-navy); margin-top: 6px;"><?= e($pillarsTitle) ?></h2>
      <p class="body-lg" style="margin-top: var(--space-3); color: var(--color-text-muted);">
        <?= e($pillarsDesc) ?>
      </p>
    </div>

    <!-- 4 Side-by-Side Cards Grid -->
    <div class="grid-4" style="gap: 22px;">
      <?php foreach ($pillars as $idx => $pil): ?>
      <div class="reveal-up" style="background: #ffffff; border: 1.5px dashed rgba(74, 139, 140, 0.4); border-radius: 18px; padding: 24px; text-align: left; transition: all 0.3s ease; box-shadow: none !important; display: flex; flex-direction: column; justify-content: space-between;">
        <div>
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(74, 139, 140, 0.12); color: var(--color-teal-ink); display: flex; align-items: center; justify-content: center; font-size: 1.35rem; border: 1px dashed rgba(74,139,140,0.3);">
              <i class="<?= e($pil['icon'] ?: 'ri-compass-3-line') ?>"></i>
            </div>
            <span style="font-family: var(--font-mono); font-size: 0.8125rem; font-weight: 700; color: var(--color-teal-ink);"><?= sprintf('%02d', $idx + 1) ?></span>
          </div>
          <h3 style="font-family: var(--font-display); font-size: 1.15rem; color: var(--color-navy); margin-bottom: 6px;"><?= e($pil['title']) ?></h3>
          <p style="font-size: 0.8125rem; color: var(--color-text-muted); line-height: 1.6; margin: 0;">
            <?= e($pil['desc']) ?>
          </p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

  </div>
</section>


<!-- ═══════════════════════════════════════════
     04 — APPLICATION PROCESS: RESTYLED 4-STAGE HIRING PROTOCOL (4 COLUMNS)
     ═══════════════════════════════════════════ -->
<section class="section" style="background: var(--color-canvas); padding: var(--space-16) 0 var(--space-20);">
  <div class="container" style="max-width: 1280px;">
    
    <div class="section-header reveal-up text-center" style="margin-bottom: var(--space-10);">
      <span class="label-upper" style="color: var(--color-teal-ink); font-weight: 700;"><?= e($protocolBadge) ?></span>
      <h2 class="heading-lg" style="color: var(--color-navy); margin-top: 6px;"><?= e($protocolTitle) ?></h2>
      <p class="body-md" style="color: var(--color-text-muted); margin-top: 4px; max-width: 680px; margin-left: auto; margin-right: auto;">
        <?= e($protocolDesc) ?>
      </p>
    </div>

    <!-- 4 Side-by-Side Cards Grid -->
    <div class="grid-4" style="gap: 22px;">
      <?php foreach ($protocol as $idx => $pr): ?>
      <div class="reveal-up" style="background: #ffffff; border: 1.5px dashed rgba(74, 139, 140, 0.4); border-radius: 18px; padding: 24px; display: flex; flex-direction: column; justify-content: space-between; box-shadow: none !important;">
        <div>
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
            <div style="width: 36px; height: 36px; border-radius: 10px; background: var(--color-teal-ink); color: #ffffff; display: flex; align-items: center; justify-content: center; font-weight: 700; font-family: var(--font-mono); font-size: 0.9375rem;">
              <?= e($pr['num'] ?? sprintf('%02d', $idx + 1)) ?>
            </div>
            <span class="badge badge-teal" style="font-size: 0.7rem; padding: 3px 8px;"><?= e($pr['sla'] ?? '48-Hour SLA') ?></span>
          </div>
          <h4 style="font-family: var(--font-body); font-weight: 700; font-size: 1rem; color: var(--color-navy); line-height: 1.35; margin: 0 0 6px 0;">
            <?= e($pr['title']) ?>
          </h4>
          <p style="font-size: 0.8125rem; color: var(--color-text-muted); margin: 0; line-height: 1.55;">
            <?= e($pr['desc']) ?>
          </p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

  </div>
</section>


<!-- ═══════════════════════════════════════════
     05 — WIDE INTERACTIVE JOB APPLICATION MODAL POPUP (HIDDEN SCROLLBAR & RESUME UPLOAD)
     ═══════════════════════════════════════════ -->
<div id="jobModalBackdrop" class="job-modal-backdrop" style="display: none;">
  <div class="job-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="modalJobTitle">
    
    <!-- Modal Close Button -->
    <button type="button" class="job-modal-close" onclick="closeJobModal()" aria-label="Close Application Form">
      <i class="ri-close-line"></i>
    </button>

    <!-- Modal Header -->
    <div class="job-modal-header">
      <div style="display: flex; gap: 8px; align-items: center; margin-bottom: 8px; flex-wrap: wrap;">
        <span id="modalJobDept" class="badge badge-teal" style="font-size: 0.72rem; text-transform: uppercase;"></span>
        <span id="modalJobLoc" style="font-size: 0.75rem; color: var(--color-teal-ink); font-weight: 700; font-family: var(--font-mono);"></span>
      </div>

      <h2 id="modalJobTitle" style="font-family: var(--font-display); font-size: 1.65rem; color: var(--color-navy); line-height: 1.25; margin: 0 0 8px 0;"></h2>

      <div style="display: flex; gap: 12px; align-items: center; font-size: 0.84375rem; font-family: var(--font-mono); color: var(--color-teal-ink); font-weight: 700;">
        <span id="modalJobSalary"></span>
        <span style="color: var(--color-border);">•</span>
        <span id="modalJobExp" style="color: var(--color-text-muted);"></span>
      </div>
    </div>

    <!-- Modal Body -->
    <div class="job-modal-body">
      
      <!-- Role Briefing Snippet -->
      <div style="background: #FAF8F5; border: 1.5px dashed rgba(74, 139, 140, 0.35); border-radius: 16px; padding: 18px 22px; margin-bottom: 22px;">
        <h4 style="font-size: 0.8125rem; font-weight: 700; color: var(--color-navy); margin: 0 0 6px 0; text-transform: uppercase; font-family: var(--font-mono); letter-spacing: 0.05em;">
          Role Overview &amp; Responsibilities
        </h4>
        <p id="modalJobOverview" style="font-size: 0.875rem; color: var(--color-text-muted); line-height: 1.6; margin: 0 0 10px 0;"></p>
        <ul id="modalJobResponsibilities" style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 6px; font-size: 0.8125rem; color: var(--color-navy);"></ul>
      </div>

      <!-- Application Form with Multipart File Upload Support -->
      <form id="jobApplicationForm" action="<?= url('api/apply.php') ?>" method="POST" enctype="multipart/form-data" novalidate style="display: flex; flex-direction: column; gap: 14px;">
        <?= CSRF::field() ?>
        <input type="hidden" id="formJobTitle" name="job_title" value="">
        <input type="hidden" id="formJobSlug" name="job_slug" value="">

        <!-- Full Name & Email -->
        <div class="grid-2" style="gap: 14px;">
          <div class="form-group" style="margin: 0;">
            <label class="form-label" style="font-size: 0.8125rem; font-weight: 700; color: var(--color-navy); margin-bottom: 4px;">
              Full Name *
            </label>
            <input type="text" name="full_name" required placeholder="Enter your full name" class="form-input" style="padding: 10px 14px; font-size: 0.875rem; border-radius: 8px;">
            <span class="form-error" data-error="full_name" style="font-size: 0.75rem; color: #e53e3e;"></span>
          </div>

          <div class="form-group" style="margin: 0;">
            <label class="form-label" style="font-size: 0.8125rem; font-weight: 700; color: var(--color-navy); margin-bottom: 4px;">
              Work Email *
            </label>
            <input type="email" name="email" required placeholder="Enter your work email" class="form-input" style="padding: 10px 14px; font-size: 0.875rem; border-radius: 8px;">
            <span class="form-error" data-error="email" style="font-size: 0.75rem; color: #e53e3e;"></span>
          </div>
        </div>

        <!-- Phone & Address / City -->
        <div class="grid-2" style="gap: 14px;">
          <div class="form-group" style="margin: 0;">
            <label class="form-label" style="font-size: 0.8125rem; font-weight: 700; color: var(--color-navy); margin-bottom: 4px;">
              Phone Number / WhatsApp *
            </label>
            <input type="tel" name="phone" required placeholder="Phone Number / WhatsApp" class="form-input" style="padding: 10px 14px; font-size: 0.875rem; border-radius: 8px;">
            <span class="form-error" data-error="phone" style="font-size: 0.75rem; color: #e53e3e;"></span>
          </div>

          <div class="form-group" style="margin: 0;">
            <label class="form-label" style="font-size: 0.8125rem; font-weight: 700; color: var(--color-navy); margin-bottom: 4px;">
              Current City / Address *
            </label>
            <input type="text" name="address" required placeholder="City, State / Country" class="form-input" style="padding: 10px 14px; font-size: 0.875rem; border-radius: 8px;">
            <span class="form-error" data-error="address" style="font-size: 0.75rem; color: #e53e3e;"></span>
          </div>
        </div>

        <!-- LinkedIn / Portfolio -->
        <div class="form-group" style="margin: 0;">
          <label class="form-label" style="font-size: 0.8125rem; font-weight: 700; color: var(--color-navy); margin-bottom: 4px;">
            LinkedIn or Portfolio URL
          </label>
          <input type="url" name="linkedin_url" placeholder="LinkedIn or Portfolio profile link" class="form-input" style="padding: 10px 14px; font-size: 0.875rem; border-radius: 8px;">
        </div>

        <!-- RESUME / CV UPLOAD FIELD -->
        <div class="form-group" style="margin: 0;">
          <label class="form-label" style="font-size: 0.8125rem; font-weight: 700; color: var(--color-navy); margin-bottom: 4px;">
            Upload Resume / CV (PDF, DOC, DOCX - Max 10MB) *
          </label>
          <div class="resume-upload-box" id="resumeUploadBox" style="border: 1.5px dashed rgba(74, 139, 140, 0.45); background: #FAF8F5; border-radius: 10px; padding: 12px 18px; display: flex; align-items: center; justify-content: space-between; gap: 12px; cursor: pointer; transition: all 0.2s ease;">
            <div style="display: flex; align-items: center; gap: 10px;">
              <i class="ri-file-upload-line" style="font-size: 1.4rem; color: var(--color-teal-ink);"></i>
              <div>
                <div id="resumeFileName" style="font-size: 0.84375rem; font-weight: 700; color: var(--color-navy);">Choose Resume File or Click Here</div>
                <div style="font-size: 0.72rem; color: var(--color-text-muted);">PDF, DOC, DOCX up to 10MB</div>
              </div>
            </div>
            <span class="btn btn-ghost btn-sm" style="padding: 4px 12px; font-size: 0.75rem; background: #ffffff; border: 1px dashed var(--color-teal-ink); color: var(--color-teal-ink); pointer-events: none;">Browse File</span>
            <input type="file" id="resumeFileInput" name="resume" accept=".pdf,.doc,.docx" style="display: none;">
          </div>
          <span class="form-error" data-error="resume" style="font-size: 0.75rem; color: #e53e3e;"></span>
        </div>

        <!-- Writing Samples -->
        <div class="form-group" style="margin: 0;">
          <label class="form-label" style="font-size: 0.8125rem; font-weight: 700; color: var(--color-navy); margin-bottom: 4px;">
            Links to 2-3 Best Published Writing Samples
          </label>
          <textarea name="writing_samples" rows="2" placeholder="Paste URLs to published articles, developer tutorials, or portfolio samples..." class="form-textarea" style="padding: 10px 14px; font-size: 0.84375rem; border-radius: 8px;"></textarea>
          <span class="form-error" data-error="writing_samples" style="font-size: 0.75rem; color: #e53e3e;"></span>
        </div>

        <!-- Experience & Expected Salary -->
        <div class="grid-2" style="gap: 14px;">
          <div class="form-group" style="margin: 0;">
            <label class="form-label" style="font-size: 0.8125rem; font-weight: 700; color: var(--color-navy); margin-bottom: 4px;">
              Years of Experience
            </label>
            <input type="text" name="experience_years" placeholder="e.g. 4 Years" class="form-input" style="padding: 10px 14px; font-size: 0.8125rem; border-radius: 8px;">
          </div>
          <div class="form-group" style="margin: 0;">
            <label class="form-label" style="font-size: 0.8125rem; font-weight: 700; color: var(--color-navy); margin-bottom: 4px;">
              Expected Salary / Compensation
            </label>
            <input type="text" name="expected_salary" placeholder="Expected Compensation" class="form-input" style="padding: 10px 14px; font-size: 0.8125rem; border-radius: 8px;">
          </div>
        </div>

        <!-- Pitch Note -->
        <div class="form-group" style="margin: 0;">
          <label class="form-label" style="font-size: 0.8125rem; font-weight: 700; color: var(--color-navy); margin-bottom: 4px;">
            Why WORDORA? (Brief Note)
          </label>
          <textarea name="cover_note" rows="2" placeholder="Tell us why you'd like to write with us and your preferred domains..." class="form-textarea" style="padding: 10px 14px; font-size: 0.84375rem; border-radius: 8px;"></textarea>
        </div>

        <!-- Submit Button -->
        <button type="submit" id="submitAppBtn" class="btn btn-primary btn-lg" style="width: 100%; justify-content: center; margin-top: 6px;">
          <span>Submit Application</span> <i class="ri-send-plane-2-line"></i>
        </button>

        <!-- Status Message Box -->
        <div id="applicationStatusBox" style="display: none; padding: 14px; border-radius: 8px; font-size: 0.84375rem; line-height: 1.5; text-align: center;"></div>
      </form>

    </div>

  </div>
</div>

<style>
/* Wide Modal Popup Styling with Hidden Scrollbar */
.job-modal-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background: rgba(15, 30, 54, 0.82);
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  z-index: 10000;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 24px;
  overflow-y: auto;
  opacity: 0;
  pointer-events: none;
  transition: opacity 0.3s ease;
  scrollbar-width: none; /* Firefox */
  -ms-overflow-style: none; /* IE/Edge */
}

.job-modal-backdrop::-webkit-scrollbar {
  display: none; /* Chrome/Safari */
}

.job-modal-backdrop.is-open {
  opacity: 1;
  pointer-events: auto;
}

.job-modal-dialog {
  background: #ffffff;
  border: 2px dashed var(--color-teal-ink);
  border-radius: 26px;
  width: 100%;
  max-width: 880px; /* Wider and spacious */
  max-height: 92vh;
  overflow-y: auto;
  position: relative;
  box-shadow: 0 30px 60px -15px rgba(0, 0, 0, 0.45);
  transform: scale(0.95) translateY(20px);
  transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
  padding: 36px 40px;
  scrollbar-width: none; /* Firefox - hides scrollbar */
  -ms-overflow-style: none; /* IE/Edge - hides scrollbar */
}

.job-modal-dialog::-webkit-scrollbar {
  display: none; /* Chrome/Safari - hides scrollbar */
}

.job-modal-backdrop.is-open .job-modal-dialog {
  transform: scale(1) translateY(0);
}

.job-modal-close {
  position: absolute;
  top: 22px;
  right: 22px;
  width: 38px;
  height: 38px;
  border-radius: 50%;
  background: #FAF8F5;
  border: 1px dashed rgba(74, 139, 140, 0.4);
  color: var(--color-navy);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.3rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.job-modal-close:hover {
  background: var(--color-teal-ink);
  color: #ffffff;
}

.job-modal-header {
  padding-bottom: 16px;
  border-bottom: 1px dashed var(--color-border);
  margin-bottom: 22px;
}

.resume-upload-box:hover {
  border-color: var(--color-teal-ink) !important;
  background: #ffffff !important;
}

@media (max-width: 768px) {
  .job-modal-dialog {
    padding: 24px 20px;
    max-width: 95vw;
  }
}
</style>

<script>
// Filter Pills Switcher
document.addEventListener('DOMContentLoaded', () => {
  const filterBtns = document.querySelectorAll('.career-filter-btn');
  const jobCards = document.querySelectorAll('.job-card');

  filterBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      const dept = btn.getAttribute('data-dept');

      filterBtns.forEach(b => {
        b.classList.remove('btn-primary', 'is-active');
        b.classList.add('btn-ghost');
        b.style.background = '#ffffff';
        b.style.color = 'var(--color-navy)';
      });

      btn.classList.remove('btn-ghost');
      btn.classList.add('btn-primary', 'is-active');
      btn.style.background = '';
      btn.style.color = '';

      jobCards.forEach(card => {
        if (dept === 'all' || card.getAttribute('data-dept') === dept) {
          card.style.display = 'flex';
          if (typeof gsap !== 'undefined') {
            gsap.fromTo(card, { opacity: 0, y: 15 }, { opacity: 1, y: 0, duration: 0.35, ease: 'power2.out' });
          }
        } else {
          card.style.display = 'none';
        }
      });
    });
  });

  // Resume File Upload Custom Box Trigger
  const resumeBox = document.getElementById('resumeUploadBox');
  const resumeInput = document.getElementById('resumeFileInput');
  const resumeLabel = document.getElementById('resumeFileName');

  if (resumeBox && resumeInput) {
    resumeBox.addEventListener('click', () => {
      resumeInput.click();
    });

    resumeInput.addEventListener('change', () => {
      if (resumeInput.files && resumeInput.files[0]) {
        const file = resumeInput.files[0];
        resumeLabel.innerHTML = '<span style="color: var(--color-teal-ink);">' + file.name + '</span> (' + (file.size / 1024 / 1024).toFixed(2) + ' MB)';
        resumeBox.style.borderColor = 'var(--color-teal-ink)';
        resumeBox.style.background = '#e6fffa';
      } else {
        resumeLabel.textContent = 'Choose Resume File or Click Here';
        resumeBox.style.borderColor = 'rgba(74, 139, 140, 0.45)';
        resumeBox.style.background = '#FAF8F5';
      }
    });
  }

  // Application Form Submit Handling via AJAX (Multipart Support)
  const appForm = document.getElementById('jobApplicationForm');
  const statusBox = document.getElementById('applicationStatusBox');
  const submitBtn = document.getElementById('submitAppBtn');

  if (appForm) {
    appForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      
      appForm.querySelectorAll('.form-error').forEach(el => el.textContent = '');
      if (statusBox) statusBox.style.display = 'none';

      const originalBtnHtml = submitBtn.innerHTML;
      submitBtn.disabled = true;
      submitBtn.innerHTML = '<span>Submitting Application...</span> <i class="ri-loader-4-line ri-spin"></i>';

      try {
        const formData = new FormData(appForm);
        const response = await fetch(appForm.action, {
          method: 'POST',
          body: formData
        });

        const data = await response.json();

        if (response.ok && data.success) {
          statusBox.style.display = 'block';
          statusBox.style.background = '#e6fffa';
          statusBox.style.border = '1px solid #319795';
          statusBox.style.color = '#234e52';
          statusBox.innerHTML = '<strong>Application Received! 🎉</strong><br>' + data.message;
          appForm.reset();
          if (resumeLabel) {
            resumeLabel.textContent = 'Choose Resume File or Click Here';
            resumeBox.style.borderColor = 'rgba(74, 139, 140, 0.45)';
            resumeBox.style.background = '#FAF8F5';
          }
        } else {
          statusBox.style.display = 'block';
          statusBox.style.background = '#fff5f5';
          statusBox.style.border = '1px solid #e53e3e';
          statusBox.style.color = '#9b2c2c';
          statusBox.innerHTML = '<strong>Submission Error:</strong> ' + (data.message || 'Please check the required fields.');

          if (data.errors) {
            for (const [key, msg] of Object.entries(data.errors)) {
              const errEl = appForm.querySelector(`[data-error="${key}"]`);
              if (errEl) errEl.textContent = msg;
            }
          }
        }
      } catch (err) {
        statusBox.style.display = 'block';
        statusBox.style.background = '#fff5f5';
        statusBox.style.border = '1px solid #e53e3e';
        statusBox.style.color = '#9b2c2c';
        statusBox.innerHTML = 'Something went wrong. Please try again or email info@wordora.in directly.';
      } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalBtnHtml;
      }
    });
  }
});

// Open Job Application Modal
function openJobModal(job) {
  const modal = document.getElementById('jobModalBackdrop');
  if (!modal) return;

  document.getElementById('modalJobDept').textContent = job.department || 'Editorial Team';
  document.getElementById('modalJobLoc').innerHTML = '<i class="ri-checkbox-circle-fill"></i> ' + (job.location || '100% Remote');
  document.getElementById('modalJobTitle').textContent = job.title;
  document.getElementById('modalJobSalary').innerHTML = '<i class="ri-wallet-3-line"></i> ' + job.salary;
  document.getElementById('modalJobExp').textContent = (job.type || 'Full-Time') + ' • ' + (job.experience || '3+ Yrs');
  
  document.getElementById('modalJobOverview').textContent = job.overview || job.excerpt;

  const respList = document.getElementById('modalJobResponsibilities');
  respList.innerHTML = '';
  if (job.responsibilities && job.responsibilities.length) {
    job.responsibilities.forEach(r => {
      const li = document.createElement('li');
      li.style.display = 'flex';
      li.style.gap = '8px';
      li.style.alignItems = 'flex-start';
      li.innerHTML = '<i class="ri-check-line" style="color: var(--color-teal-ink); font-weight: bold;"></i> <span>' + r + '</span>';
      respList.appendChild(li);
    });
  }

  document.getElementById('formJobTitle').value = job.title;
  document.getElementById('formJobSlug').value = job.slug;

  const statusBox = document.getElementById('applicationStatusBox');
  if (statusBox) statusBox.style.display = 'none';

  modal.style.display = 'flex';
  setTimeout(() => {
    modal.classList.add('is-open');
    document.body.style.overflow = 'hidden';
  }, 10);
}

// Close Job Application Modal
function closeJobModal() {
  const modal = document.getElementById('jobModalBackdrop');
  if (!modal) return;

  modal.classList.remove('is-open');
  document.body.style.overflow = '';
  setTimeout(() => {
    modal.style.display = 'none';
  }, 300);
}

// Close on clicking backdrop outside dialog
document.addEventListener('click', (e) => {
  const modal = document.getElementById('jobModalBackdrop');
  if (modal && e.target === modal) {
    closeJobModal();
  }
});

// Close on Escape key
document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape') {
    closeJobModal();
  }
});
</script>

<?php
$content = ob_get_clean();
include ROOT_PATH . '/views/layouts/main.php';
