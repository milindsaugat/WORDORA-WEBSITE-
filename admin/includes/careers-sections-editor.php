<?php
/**
 * WORDORA — Careers & Editorial Guild Visual Section Studio
 * Complete 6-Section Architecture + Integrated Job Applications Submissions Manager
 */
$db = DB::getInstance();
$activeTab = $_GET['tab'] ?? 'sec01';
$statusFilter = $_GET['status'] ?? 'all';
$error = '';
$success = flash_get('success');

// ════════════════════════════════════════════════════════════════
// POST PROCESSING HANDLER
// ════════════════════════════════════════════════════════════════
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CSRF::verify($_POST['csrf_token'] ?? '')) {
        $error = 'Security token expired. Please try again.';
    } else {
        $action = $_POST['action'] ?? 'save_settings';

        // 1. APPLICATION STATUS UPDATE
        if ($action === 'update_app_status') {
            $appId = (int)($_POST['app_id'] ?? 0);
            $newStatus = trim($_POST['status'] ?? 'pending');
            $allowedStatuses = ['pending', 'reviewed', 'shortlisted', 'rejected'];
            if ($appId > 0 && in_array($newStatus, $allowedStatuses)) {
                $stmt = $db->prepare("UPDATE job_applications SET status = ? WHERE id = ?");
                $stmt->execute([$newStatus, $appId]);
                flash_set('success', 'Application status updated to ' . ucfirst($newStatus) . ' successfully!');
                redirect('admin/pages/careers.php?tab=applications' . ($statusFilter !== 'all' ? '&status=' . urlencode($statusFilter) : ''));
            }
        }

        // 2. DELETE APPLICATION
        elseif ($action === 'delete_application') {
            $appId = (int)($_POST['app_id'] ?? 0);
            if ($appId > 0) {
                // Find and delete resume file if exists
                $app = $db->query("SELECT resume_path FROM job_applications WHERE id = " . (int)$appId)->fetch();
                if ($app && !empty($app['resume_path'])) {
                    $resumeFull = ROOT_PATH . '/public/' . ltrim($app['resume_path'], '/');
                    if (file_exists($resumeFull)) {
                        @unlink($resumeFull);
                    }
                }
                $db->prepare("DELETE FROM job_applications WHERE id = ?")->execute([$appId]);
                flash_set('success', 'Job application deleted successfully!');
                redirect('admin/pages/careers.php?tab=applications' . ($statusFilter !== 'all' ? '&status=' . urlencode($statusFilter) : ''));
            }
        }

        // 3. TAB 01: HERO COVER
        elseif ($activeTab === 'sec01') {
            $badge = trim($_POST['careers_hero_badge'] ?? 'JOIN OUR GLOBAL EDITORIAL COLLECTIVE');
            $title = trim($_POST['careers_hero_title'] ?? '');
            $desc  = trim($_POST['careers_hero_desc'] ?? '');

            // Handle hero background image
            $existingHero = setting('careers_hero_image', '');
            $heroImage = $existingHero;

            if (isset($_FILES['careers_hero_image_file']) && $_FILES['careers_hero_image_file']['error'] === UPLOAD_ERR_OK) {
                $uploader = new Upload('careers', 52428800);
                $upRes = $uploader->handle($_FILES['careers_hero_image_file']);
                if ($upRes['success']) {
                    if (!empty($existingHero) && $existingHero !== $upRes['path']) {
                        delete_uploaded_file($existingHero);
                    }
                    $heroImage = $upRes['path'];
                } else {
                    $error = 'Hero image upload failed: ' . $upRes['msg'];
                }
            } elseif (!empty($_POST['remove_hero_image']) && $_POST['remove_hero_image'] === '1') {
                delete_uploaded_file($existingHero);
                $heroImage = '';
            }

            if (empty($error)) {
                $stmt = $db->prepare("INSERT INTO settings (`setting_key`, `setting_value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `setting_value` = VALUES(`setting_value`)");
                $stmt->execute(['careers_hero_badge', $badge]);
                $stmt->execute(['careers_hero_title', $title]);
                $stmt->execute(['careers_hero_desc', $desc]);
                $stmt->execute(['careers_hero_image', $heroImage]);

                flash_set('success', 'Careers Hero Section updated successfully!');
                redirect('admin/pages/careers.php?tab=sec01');
            }
        }

        // 4. TAB 02: REMOTE PERKS BAR
        elseif ($activeTab === 'sec02') {
            $perks = [];
            if (!empty($_POST['perks']) && is_array($_POST['perks'])) {
                foreach ($_POST['perks'] as $p) {
                    if (!empty(trim($p['title'] ?? ''))) {
                        $perks[] = [
                            'icon'  => trim($p['icon'] ?? 'ri-global-line'),
                            'title' => trim($p['title']),
                            'desc'  => trim($p['desc'] ?? ''),
                        ];
                    }
                }
            }
            $stmt = $db->prepare("INSERT INTO settings (`setting_key`, `setting_value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `setting_value` = VALUES(`setting_value`)");
            $stmt->execute(['careers_perks', json_encode($perks, JSON_UNESCAPED_UNICODE)]);

            flash_set('success', 'Remote Perks & Highlights updated successfully!');
            redirect('admin/pages/careers.php?tab=sec02');
        }

        // 5. TAB 03: OPEN ROLES & JOB LISTINGS
        elseif ($activeTab === 'sec03') {
            $jobsBadge = trim($_POST['careers_jobs_badge'] ?? 'CURRENT OPPORTUNITIES');
            $jobsTitle = trim($_POST['careers_jobs_title'] ?? 'Open Roles Across 4 Disciplines');
            $jobsDesc  = trim($_POST['careers_jobs_desc'] ?? '');

            $jobsList = [];
            if (!empty($_POST['jobs']) && is_array($_POST['jobs'])) {
                $roleId = 1;
                foreach ($_POST['jobs'] as $j) {
                    $jobTitle = trim($j['title'] ?? '');
                    if (!empty($jobTitle)) {
                        $dept = trim($j['department'] ?? 'Editorial & Writing');
                        $deptSlug = trim($j['department_slug'] ?? slugify($dept));
                        if (empty($deptSlug)) $deptSlug = slugify($dept);
                        
                        $rawTags = trim($j['tags'] ?? '');
                        $tagsArr = !empty($rawTags) ? array_filter(array_map('trim', explode(',', $rawTags))) : [];

                        $rawResp = trim($j['responsibilities'] ?? '');
                        $respArr = !empty($rawResp) ? array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $rawResp))) : [];

                        $jobsList[] = [
                            'id'              => $roleId++,
                            'slug'            => !empty($j['slug']) ? slugify($j['slug']) : slugify($jobTitle),
                            'title'           => $jobTitle,
                            'department'      => $dept,
                            'department_slug' => $deptSlug,
                            'type'            => trim($j['type'] ?? 'Full-Time'),
                            'location'        => trim($j['location'] ?? '100% Remote Global'),
                            'salary'          => trim($j['salary'] ?? '₹14,00,000 – ₹20,00,000 / yr'),
                            'equity'          => trim($j['equity'] ?? 'Performance Bonus Included'),
                            'experience'      => trim($j['experience'] ?? '3+ Years Exp'),
                            'tags'            => array_values($tagsArr),
                            'excerpt'         => trim($j['excerpt'] ?? ''),
                            'overview'        => trim($j['overview'] ?? ''),
                            'responsibilities'=> array_values($respArr),
                            'is_active'       => isset($j['is_active']) ? 1 : 0,
                        ];
                    }
                }
            }

            $stmt = $db->prepare("INSERT INTO settings (`setting_key`, `setting_value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `setting_value` = VALUES(`setting_value`)");
            $stmt->execute(['careers_jobs_badge', $jobsBadge]);
            $stmt->execute(['careers_jobs_title', $jobsTitle]);
            $stmt->execute(['careers_jobs_desc', $jobsDesc]);
            $stmt->execute(['careers_jobs', json_encode($jobsList, JSON_UNESCAPED_UNICODE)]);

            flash_set('success', 'Open Job Roles & Listings updated successfully (' . count($jobsList) . ' roles saved)!');
            redirect('admin/pages/careers.php?tab=sec03');
        }

        // 6. TAB 04: WORKING PHILOSOPHY (4 CULTURE PILLARS)
        elseif ($activeTab === 'sec04') {
            $pilBadge = trim($_POST['careers_pillars_badge'] ?? 'OUR WORKING PHILOSOPHY');
            $pilTitle = trim($_POST['careers_pillars_title'] ?? 'Built For Writers Who Care Deeply About Craft');
            $pilDesc  = trim($_POST['careers_pillars_desc'] ?? '');

            $pillarsList = [];
            if (!empty($_POST['pillars']) && is_array($_POST['pillars'])) {
                foreach ($_POST['pillars'] as $p) {
                    if (!empty(trim($p['title'] ?? ''))) {
                        $pillarsList[] = [
                            'icon'  => trim($p['icon'] ?? 'ri-quill-pen-line'),
                            'title' => trim($p['title']),
                            'desc'  => trim($p['desc'] ?? ''),
                        ];
                    }
                }
            }

            $stmt = $db->prepare("INSERT INTO settings (`setting_key`, `setting_value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `setting_value` = VALUES(`setting_value`)");
            $stmt->execute(['careers_pillars_badge', $pilBadge]);
            $stmt->execute(['careers_pillars_title', $pilTitle]);
            $stmt->execute(['careers_pillars_desc', $pilDesc]);
            $stmt->execute(['careers_pillars', json_encode($pillarsList, JSON_UNESCAPED_UNICODE)]);

            flash_set('success', 'Working Philosophy & Culture Pillars updated successfully!');
            redirect('admin/pages/careers.php?tab=sec04');
        }

        // 7. TAB 05: 4-STAGE HIRING PROTOCOL
        elseif ($activeTab === 'sec05') {
            $protBadge = trim($_POST['careers_protocol_badge'] ?? 'OUR 4-STAGE HIRING PROTOCOL');
            $protTitle = trim($_POST['careers_protocol_title'] ?? 'Transparent, Fast & Respectful of Your Time');
            $protDesc  = trim($_POST['careers_protocol_desc'] ?? '');

            $protocolList = [];
            if (!empty($_POST['protocol']) && is_array($_POST['protocol'])) {
                foreach ($_POST['protocol'] as $idx => $pr) {
                    if (!empty(trim($pr['title'] ?? ''))) {
                        $protocolList[] = [
                            'num'   => trim($pr['num'] ?? sprintf('%02d', $idx + 1)),
                            'sla'   => trim($pr['sla'] ?? '48-Hour SLA'),
                            'title' => trim($pr['title']),
                            'desc'  => trim($pr['desc'] ?? ''),
                        ];
                    }
                }
            }

            $stmt = $db->prepare("INSERT INTO settings (`setting_key`, `setting_value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `setting_value` = VALUES(`setting_value`)");
            $stmt->execute(['careers_protocol_badge', $protBadge]);
            $stmt->execute(['careers_protocol_title', $protTitle]);
            $stmt->execute(['careers_protocol_desc', $protDesc]);
            $stmt->execute(['careers_protocol', json_encode($protocolList, JSON_UNESCAPED_UNICODE)]);

            flash_set('success', '4-Stage Hiring Protocol updated successfully!');
            redirect('admin/pages/careers.php?tab=sec05');
        }
    }
}

// ════════════════════════════════════════════════════════════════
// LOAD DATA FROM SETTINGS & DATABASE
// ════════════════════════════════════════════════════════════════
$heroBadge = setting('careers_hero_badge', 'JOIN OUR GLOBAL EDITORIAL COLLECTIVE');
$heroTitle = setting('careers_hero_title', 'Build The Future of Editorial Authority & Brand Narrative');
$heroDesc  = setting('careers_hero_desc', 'We are hiring world-class technical writers, investigative editors, SEO topic architects, and creative copywriters to author high-stakes content for top-tier brands worldwide.');
$heroImage = setting('careers_hero_image', '');

$perks = json_decode(setting('careers_perks', '[]'), true) ?: [
    ['icon' => 'ri-global-line', 'title' => '100% Remote Global', 'desc' => 'Work from anywhere on Earth'],
    ['icon' => 'ri-wallet-3-line', 'title' => 'Top 10% Compensation', 'desc' => 'Competitive INR pay & performance bonuses'],
    ['icon' => 'ri-book-open-line', 'title' => 'Annual Learning Stipend', 'desc' => 'Books, courses & hardware allowance'],
    ['icon' => 'ri-time-line', 'title' => 'Async & Flexible Hours', 'desc' => 'Zero timesheets or useless meetings'],
];

$jobsBadge = setting('careers_jobs_badge', 'CURRENT OPPORTUNITIES');
$jobsTitle = setting('careers_jobs_title', '9 Open Roles Across 4 Disciplines');
$jobsDesc  = setting('careers_jobs_desc', 'Explore our active remote openings. Every position includes full async flexibility and competitive compensation.');
$jobsList  = json_decode(setting('careers_jobs', '[]'), true) ?: [];

$pillarsBadge = setting('careers_pillars_badge', 'OUR WORKING PHILOSOPHY');
$pillarsTitle = setting('careers_pillars_title', 'Built For Writers Who Care Deeply About Craft');
$pillarsDesc  = setting('careers_pillars_desc', 'We don\'t do agency churn or AI prompt-spinning. We operate as an elite editorial guild with uncompromising standards, fair compensation, and radical autonomy.');
$pillarsList  = json_decode(setting('careers_pillars', '[]'), true) ?: [];

$protocolBadge = setting('careers_protocol_badge', 'OUR 4-STAGE HIRING PROTOCOL');
$protocolTitle = setting('careers_protocol_title', 'Transparent, Fast & Respectful of Your Time');
$protocolDesc  = setting('careers_protocol_desc', 'We respond to every candidate application within 48 hours. No 7-round interview marathons.');
$protocolList  = json_decode(setting('careers_protocol', '[]'), true) ?: [];

// Load Job Applications & Stats
$totalAppsCount = (int)$db->query("SELECT COUNT(*) FROM job_applications")->fetchColumn();
$pendingCount   = (int)$db->query("SELECT COUNT(*) FROM job_applications WHERE status = 'pending' OR status IS NULL OR status = ''")->fetchColumn();
$reviewedCount  = (int)$db->query("SELECT COUNT(*) FROM job_applications WHERE status = 'reviewed'")->fetchColumn();
$shortlistCount = (int)$db->query("SELECT COUNT(*) FROM job_applications WHERE status = 'shortlisted'")->fetchColumn();
$rejectedCount  = (int)$db->query("SELECT COUNT(*) FROM job_applications WHERE status = 'rejected'")->fetchColumn();

// Fetch filtered applications list
$sql = "SELECT * FROM job_applications";
$params = [];
if ($statusFilter === 'pending') {
    $sql .= " WHERE status = 'pending' OR status IS NULL OR status = ''";
} elseif (in_array($statusFilter, ['reviewed', 'shortlisted', 'rejected'])) {
    $sql .= " WHERE status = ?";
    $params[] = $statusFilter;
}
$sql .= " ORDER BY id DESC";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$applications = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Section Navigation Tabs
$tabs = [
    'sec01' => ['num' => '01', 'name' => 'Hero Cover', 'icon' => 'ri-image-line'],
    'sec02' => ['num' => '02', 'name' => 'Perks & Remote Bar', 'icon' => 'ri-wallet-3-line'],
    'sec03' => ['num' => '03', 'name' => 'Open Job Roles', 'icon' => 'ri-briefcase-4-line'],
    'sec04' => ['num' => '04', 'name' => 'Culture & Philosophy', 'icon' => 'ri-compass-3-line'],
    'sec05' => ['num' => '05', 'name' => '4-Stage Protocol', 'icon' => 'ri-node-tree'],
    'applications' => ['num' => '06', 'name' => 'Job Applications', 'icon' => 'ri-user-shared-line', 'badge' => $pendingCount],
];
?>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400..700;1,9..40,400..700&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600;700&family=Playfair+Display:ital,wght@0,500;0,700;0,800;1,500;1,700&display=swap" rel="stylesheet">

<style>
:root {
  --wdr-navy: #1B2A4A;
  --wdr-deep-navy: #0F1E36;
  --wdr-teal: #4A8B8C;
  --wdr-teal-light: #6BA8A9;
  --wdr-teal-pale: #D4EAEA;
  --wdr-canvas: #FAF8F5;
  --wdr-white: #FFFFFF;
  --wdr-font-display: 'Playfair Display', Georgia, serif;
  --wdr-font-body: 'Inter', sans-serif;
  --wdr-font-mono: 'JetBrains Mono', monospace;
}

.visual-studio-wrapper {
  max-width: 1320px;
  margin: 0 auto;
}

.visual-studio-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
  flex-wrap: wrap;
  gap: 16px;
}

.visual-title {
  font-family: var(--wdr-font-display);
  font-size: 26px;
  font-weight: 700;
  color: var(--wdr-navy);
}

.visual-subtitle {
  font-size: 13px;
  color: var(--admin-muted);
}

.visual-studio-card {
  background: var(--wdr-canvas);
  border: 1.5px dashed rgba(74, 139, 140, 0.4);
  border-radius: 20px;
  padding: 32px;
  margin-bottom: 28px;
  position: relative;
}

.visual-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: rgba(74, 139, 140, 0.12);
  color: var(--wdr-teal);
  border: 1px dashed var(--wdr-teal);
  padding: 6px 14px;
  border-radius: 20px;
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 0.06em;
  text-transform: uppercase;
}

.visual-label-upper {
  font-size: 11px;
  font-weight: 800;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  color: var(--wdr-teal);
  display: block;
  margin-bottom: 8px;
  font-family: var(--wdr-font-mono);
}

.visual-display-heading {
  font-family: var(--wdr-font-display);
  font-size: 28px;
  font-weight: 700;
  color: var(--wdr-navy);
  line-height: 1.25;
  margin: 12px 0 16px;
}

.visual-media-frame {
  background: var(--wdr-white);
  padding: 24px;
  border: 1.5px dashed rgba(74, 139, 140, 0.45);
  border-radius: 24px;
  box-shadow: 0 8px 24px rgba(15, 30, 54, 0.06);
  position: relative;
  text-align: center;
}

.visual-input-styled {
  width: 100%;
  padding: 10px 14px;
  border: 1.5px dashed rgba(74, 139, 140, 0.35);
  border-radius: 8px;
  background: #FFFFFF;
  font-size: 13.5px;
  color: var(--wdr-deep-navy);
  font-family: inherit;
  transition: all 0.2s ease;
  box-sizing: border-box;
}

.visual-input-styled:focus {
  outline: none;
  border-color: var(--wdr-teal);
  border-style: solid;
  box-shadow: 0 0 0 3px rgba(74, 139, 140, 0.15);
}

/* Action Buttons */
.table-actions {
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

.btn-adm-action {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 6px 12px;
  border-radius: 8px;
  font-size: 12px;
  font-weight: 600;
  text-decoration: none;
  cursor: pointer;
  border: 1.5px solid transparent;
  transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
  line-height: 1.2;
  box-sizing: border-box;
  font-family: var(--wdr-font-body);
}

.btn-adm-action.btn-adm-edit {
  background: #FAF8F5;
  color: var(--wdr-navy);
  border-color: #CBD5E1;
  box-shadow: 0 1px 3px rgba(15, 30, 54, 0.04);
}
.btn-adm-action.btn-adm-edit:hover {
  background: var(--wdr-teal-pale);
  border-color: var(--wdr-teal);
  color: var(--wdr-navy);
  transform: translateY(-1px);
  box-shadow: 0 3px 8px rgba(74, 139, 140, 0.15);
}

.btn-adm-action.btn-adm-delete {
  background: #FEF2F2;
  color: #DC2626;
  border-color: #FECACA;
  box-shadow: 0 1px 3px rgba(220, 38, 38, 0.04);
}
.btn-adm-action.btn-adm-delete:hover {
  background: #DC2626;
  color: #FFFFFF;
  border-color: #DC2626;
  transform: translateY(-1px);
  box-shadow: 0 3px 8px rgba(220, 38, 38, 0.25);
}

.job-editor-card {
  transition: all 0.25s ease;
}
.job-editor-card:hover {
  border-color: var(--wdr-teal) !important;
  box-shadow: 0 6px 20px rgba(74, 139, 140, 0.08);
}

/* ═══════════════════════════════════════════
   APPLICATIONS TABLE & KPI STATS STYLING
   ═══════════════════════════════════════════ */
.kpi-stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
  gap: 12px;
  margin-bottom: 24px;
}
.kpi-stat-card {
  border-radius: 14px;
  padding: 14px 18px;
  text-align: left;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  transition: all 0.2s ease;
}
.kpi-stat-card:hover {
  transform: translateY(-2px);
}
.kpi-stat-val {
  font-size: 24px;
  font-weight: 800;
  font-family: var(--wdr-font-display);
  line-height: 1;
  margin-bottom: 4px;
}
.kpi-stat-lbl {
  font-size: 10px;
  font-weight: 800;
  font-family: var(--wdr-font-mono);
  letter-spacing: 0.08em;
  text-transform: uppercase;
}

.admin-card-table-wrapper {
  background: #FFFFFF;
  border: 1.5px solid #E2E8F0;
  border-radius: 16px;
  box-shadow: 0 4px 20px rgba(15, 30, 54, 0.04);
  overflow: hidden;
}

.table-top-bar {
  padding: 16px 20px;
  background: #FFFFFF;
  border-bottom: 1px solid #E2E8F0;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 12px;
}

.table-responsive {
  width: 100%;
  overflow-x: auto;
}

.admin-table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0;
  text-align: left;
}

.admin-table th {
  background: #F8FAFC !important;
  padding: 14px 18px !important;
  font-size: 11px !important;
  font-weight: 700 !important;
  font-family: var(--wdr-font-mono) !important;
  letter-spacing: 0.05em !important;
  color: #475569 !important;
  border-bottom: 1.5px solid #E2E8F0 !important;
  text-transform: uppercase !important;
  white-space: nowrap !important;
}

.admin-table td {
  padding: 16px 18px !important;
  border-bottom: 1px solid #F1F5F9 !important;
  vertical-align: middle !important;
  background: #FFFFFF;
  font-size: 13px;
  color: #1E293B;
}

.admin-table tr:last-child td {
  border-bottom: none !important;
}

.admin-table tr:hover td {
  background: #F8FAFC !important;
}

.candidate-avatar-circle {
  width: 36px;
  height: 36px;
  border-radius: 10px;
  background: rgba(74, 139, 140, 0.12);
  color: var(--wdr-teal);
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 800;
  font-size: 14px;
  font-family: var(--wdr-font-mono);
  flex-shrink: 0;
  border: 1px solid rgba(74, 139, 140, 0.25);
}
</style>

<div class="visual-studio-wrapper">

  <!-- ═══════════════════════════════════════════
       STUDIO HEADER
       ═══════════════════════════════════════════ -->
  <div class="visual-studio-header">
    <div style="display: flex; align-items: center; gap: 16px;">
      <div style="width: 52px; height: 52px; border-radius: 14px; background: rgba(74, 139, 140, 0.12); color: var(--wdr-teal); display: flex; align-items: center; justify-content: center; font-size: 26px; border: 1.5px dashed rgba(74, 139, 140, 0.35);">
        <i class="ri-user-search-line"></i>
      </div>
      <div>
        <span class="visual-badge" style="margin-bottom: 4px;"><i class="ri-sparkling-fill"></i> RECRUITMENT &amp; EDITORIAL GUILD STUDIO</span>
        <h1 class="visual-title" style="margin: 0; font-size: 24px;">Careers Page &amp; Job Applications</h1>
        <p class="visual-subtitle" style="margin: 4px 0 0; font-size: 13px;">Manage open roles, hiring philosophy, remote perks, and review candidate resume submissions in real time.</p>
      </div>
    </div>

    <div style="display: flex; gap: 12px; align-items: center;">
      <a href="<?= url('careers.php') ?>" target="_blank" class="btn-adm btn-adm-outline">
        <i class="ri-external-link-line"></i> View Live Careers Page
      </a>
    </div>
  </div>

  <!-- Flash Messages -->
  <?php if (!empty($success)): ?>
    <div style="margin-bottom: 20px; padding: 12px 18px; border-radius: 10px; font-size: 13px; font-weight: 600; background: #ECFDF5; color: #065F46; border: 1px solid #A7F3D0; display: flex; align-items: center; gap: 8px;">
      <i class="ri-checkbox-circle-fill" style="font-size: 18px; color: #10B981;"></i> <?= e($success) ?>
    </div>
  <?php endif; ?>

  <?php if (!empty($error)): ?>
    <div style="margin-bottom: 20px; padding: 12px 18px; border-radius: 10px; font-size: 13px; font-weight: 600; background: #FEF2F2; color: #991B1B; border: 1px solid #FECACA; display: flex; align-items: center; gap: 8px;">
      <i class="ri-error-warning-fill" style="font-size: 18px; color: #EF4444;"></i> <?= e($error) ?>
    </div>
  <?php endif; ?>

  <!-- ═══════════════════════════════════════════
       TAB NAVIGATION PILL BAR
       ═══════════════════════════════════════════ -->
  <div style="display: flex; gap: 8px; margin-bottom: 24px; overflow-x: auto; padding-bottom: 8px;">
    <?php foreach ($tabs as $k => $t): 
        $isAct = ($activeTab === $k);
    ?>
    <a href="?tab=<?= $k ?>" style="padding: 10px 16px; border-radius: 12px; font-size: 13px; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; white-space: nowrap; transition: all 0.2s; <?= $isAct ? 'background: var(--wdr-navy); color: #FFF; box-shadow: 0 4px 14px rgba(15,30,54,0.18);' : 'background: #FFF; color: var(--wdr-navy); border: 1.5px solid var(--admin-border);' ?>">
      <span style="display: inline-block; width: 22px; height: 22px; border-radius: 6px; background: <?= $isAct ? 'var(--wdr-teal)' : 'var(--wdr-teal-pale)' ?>; color: <?= $isAct ? '#FFF' : 'var(--wdr-teal)' ?>; font-size: 11px; font-weight: 800; line-height: 22px; text-align: center;"><?= $t['num'] ?></span>
      <i class="<?= $t['icon'] ?>"></i> <?= $t['name'] ?>
      <?php if (!empty($t['badge']) && $t['badge'] > 0): ?>
        <span style="background: #EF4444; color: #FFF; font-size: 10px; font-weight: 800; padding: 2px 7px; border-radius: 10px; margin-left: 2px;"><?= (int)$t['badge'] ?></span>
      <?php endif; ?>
    </a>
    <?php endforeach; ?>
  </div>


  <!-- ═══════════════════════════════════════════
       TAB 01: HERO COVER SECTION
       ═══════════════════════════════════════════ -->
  <?php if ($activeTab === 'sec01'): ?>
  <form method="POST" action="?tab=sec01" enctype="multipart/form-data">
    <?= CSRF::field() ?>
    <div class="visual-studio-card">
      <div style="margin-bottom: 20px;">
        <span class="visual-badge"><i class="ri-image-line"></i> SECTION 01 — HERO BANNER</span>
        <h2 class="visual-display-heading" style="margin: 8px 0 4px;">Careers Hero Title &amp; Atmosphere</h2>
        <p style="color: var(--admin-muted); font-size: 13px; margin: 0;">Top header presentation, collective mandate badge, and optional custom atmospheric background.</p>
      </div>

      <div style="display: grid; grid-template-columns: 1.15fr 0.85fr; gap: 32px; align-items: start;">
        <div>
          <div style="margin-bottom: 16px;">
            <label class="visual-label-upper">Eyebrow Badge Tag</label>
            <input type="text" name="careers_hero_badge" class="visual-input-styled" value="<?= e($heroBadge) ?>" placeholder="e.g. JOIN OUR GLOBAL EDITORIAL COLLECTIVE" style="font-weight: 700;">
          </div>

          <div style="margin-bottom: 16px;">
            <label class="visual-label-upper">Hero Main Headline <span style="color:#ef4444;">*</span></label>
            <input type="text" name="careers_hero_title" class="visual-input-styled" required value="<?= e($heroTitle) ?>" placeholder="e.g. Build The Future of Editorial Authority & Brand Narrative" style="font-weight: 700; font-size: 16px;">
          </div>

          <div style="margin-bottom: 20px;">
            <label class="visual-label-upper">Hero Lead Description</label>
            <textarea name="careers_hero_desc" class="visual-input-styled" rows="4" placeholder="We are hiring world-class technical writers, investigative editors..."><?= e($heroDesc) ?></textarea>
          </div>
        </div>

        <!-- Right Column: Hero Cover Image Preview -->
        <div>
          <div class="visual-media-frame">
            <label class="visual-label-upper" style="text-align: center; margin-bottom: 12px;"><i class="ri-image-add-line"></i> Hero Cover Background</label>
            
            <?php 
            $hasCustomHero = !empty($heroImage);
            $resolvedHeroImg = $hasCustomHero ? $heroImage : 'img/home section 2.png';
            ?>
            
            <div id="preview_hero_wrap" style="position: relative; border-radius: 12px; overflow: hidden; margin-bottom: 16px; border: 1.5px solid var(--admin-border); background: #0F1E36;">
              <img id="preview_hero_img" src="<?= media_url($resolvedHeroImg) ?>" alt="Careers Hero" style="max-height: 180px; width: 100%; object-fit: cover; display: block;">
              <div style="position: absolute; bottom: 8px; left: 8px; z-index: 2;">
                <?php if ($hasCustomHero): ?>
                  <span class="badge badge-teal" style="font-size: 11px; box-shadow: 0 2px 6px rgba(0,0,0,0.3);">Custom Cover Uploaded</span>
                <?php else: ?>
                  <span class="badge" style="background: rgba(15,30,54,0.85); color: #FFF; font-size: 11px; border: 1px solid rgba(255,255,255,0.2);">Default Studio Atmosphere</span>
                <?php endif; ?>
              </div>
            </div>

            <div style="text-align: left; background: #FAF8F5; padding: 14px; border-radius: 12px; border: 1px dashed rgba(74, 139, 140, 0.35);">
              <label style="font-size: 11px; font-weight: 700; color: var(--wdr-navy); display: block; margin-bottom: 4px;">Upload Single Hero Cover Image (PNG / JPG / WEBP)</label>
              <input type="file" name="careers_hero_image_file" class="visual-input-styled" accept="image/*">
              <input type="hidden" name="remove_hero_image" id="remove_hero_image" value="0">
              <?php if ($hasCustomHero): ?>
                <button type="button" onclick="document.getElementById('remove_hero_image').value='1'; document.getElementById('preview_hero_img').src='<?= media_url('img/home section 2.png') ?>'; this.style.display='none';" class="btn-adm-action btn-adm-delete" style="margin-top: 10px; width: 100%; justify-content: center; padding: 8px 12px; font-size: 12px; font-weight: 600; cursor: pointer;">
                  <i class="ri-delete-bin-line"></i> Revert to Default Atmosphere
                </button>
              <?php endif; ?>
            </div>
          </div>
        </div>

      </div>

      <button type="submit" class="btn-adm btn-adm-primary"><i class="ri-save-line"></i> Save Careers Hero</button>
    </div>
  </form>
  <?php endif; ?>


  <!-- ═══════════════════════════════════════════
       TAB 02: REMOTE PERKS & HIGHLIGHTS BAR (4 CARDS)
       ═══════════════════════════════════════════ -->
  <?php if ($activeTab === 'sec02'): ?>
  <form method="POST" action="?tab=sec02">
    <?= CSRF::field() ?>
    <div class="visual-studio-card">
      <div style="margin-bottom: 20px;">
        <span class="visual-badge"><i class="ri-wallet-3-line"></i> SECTION 02 — PERKS &amp; REMOTE HIGHLIGHTS</span>
        <h2 class="visual-display-heading" style="margin: 8px 0 4px;">4 Remote Working Highlights &amp; Benefits</h2>
        <p style="color: var(--admin-muted); font-size: 13px; margin: 0;">Horizontal highlights bar displayed prominently directly below the hero transition.</p>
      </div>

      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 18px; margin-bottom: 24px;">
        <?php foreach ($perks as $idx => $p): ?>
          <div style="background: #FFF; border: 1.5px dashed rgba(74, 139, 140, 0.4); border-radius: 14px; padding: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; border-bottom: 1px dashed rgba(74, 139, 140, 0.25); padding-bottom: 8px;">
              <span style="font-family: var(--wdr-font-mono); font-size: 11px; font-weight: 800; color: var(--wdr-teal); text-transform: uppercase;">
                Perk Highlight #0<?= $idx + 1 ?>
              </span>
            </div>
            <div style="margin-bottom: 10px;">
              <label class="visual-label-upper">RemixIcon</label>
              <input type="text" name="perks[<?= $idx ?>][icon]" class="visual-input-styled" value="<?= e($p['icon'] ?? 'ri-global-line') ?>" placeholder="e.g. ri-wallet-3-line">
            </div>
            <div style="margin-bottom: 10px;">
              <label class="visual-label-upper">Perk Title</label>
              <input type="text" name="perks[<?= $idx ?>][title]" class="visual-input-styled" value="<?= e($p['title'] ?? '') ?>" placeholder="Title" style="font-weight: 700;">
            </div>
            <div>
              <label class="visual-label-upper">Subtitle / Description</label>
              <input type="text" name="perks[<?= $idx ?>][desc]" class="visual-input-styled" value="<?= e($p['desc'] ?? '') ?>" placeholder="Short benefit note">
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <button type="submit" class="btn-adm btn-adm-primary"><i class="ri-save-line"></i> Save Remote Perks</button>
    </div>
  </form>
  <?php endif; ?>


  <!-- ═══════════════════════════════════════════
       TAB 03: OPEN ROLES & JOB LISTINGS (INTERACTIVE ROLES MANAGER)
       ═══════════════════════════════════════════ -->
  <?php if ($activeTab === 'sec03'): ?>
  <form method="POST" action="?tab=sec03" id="jobsForm">
    <?= CSRF::field() ?>
    <div class="visual-studio-card">
      <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; flex-wrap: wrap; gap: 16px;">
        <div>
          <span class="visual-badge"><i class="ri-briefcase-4-line"></i> SECTION 03 — OPEN ROLES &amp; JOB LISTINGS</span>
          <h2 class="visual-display-heading" style="margin: 8px 0 4px;">Job Openings &amp; Application Scopes</h2>
          <p style="color: var(--admin-muted); font-size: 13px; margin: 0;">Full interactive manager for all active job cards, role specifications, compensation, and application modal briefs.</p>
        </div>
        <button type="button" onclick="addNewJobCard()" class="btn-adm" style="background: var(--wdr-teal); color: #FFF; font-weight: 700; font-size: 13px;">
          <i class="ri-add-line"></i> Add New Job Opening
        </button>
      </div>

      <!-- Section Title & Badge Settings -->
      <div style="background: #FFF; border: 1.5px dashed rgba(74, 139, 140, 0.4); border-radius: 14px; padding: 20px; margin-bottom: 24px;">
        <div style="display: grid; grid-template-columns: 1fr 1.5fr; gap: 16px; margin-bottom: 12px;">
          <div>
            <label class="visual-label-upper">Section Eyebrow Badge</label>
            <input type="text" name="careers_jobs_badge" class="visual-input-styled" value="<?= e($jobsBadge) ?>" placeholder="e.g. CURRENT OPPORTUNITIES">
          </div>
          <div>
            <label class="visual-label-upper">Section Main Heading</label>
            <input type="text" name="careers_jobs_title" class="visual-input-styled" value="<?= e($jobsTitle) ?>" placeholder="e.g. {count} Open Roles Across {disciplines} Disciplines" style="font-weight: 700;">
            <div style="font-size: 11px; color: var(--wdr-teal); margin-top: 4px; font-family: var(--wdr-font-mono);">
              <i class="ri-information-line"></i> Dynamic tags supported: <code>{count}</code>, <code>{roles}</code>, <code>{disciplines}</code> (Auto-syncs live numbers)
            </div>
          </div>
        </div>
        <div>
          <label class="visual-label-upper">Section Subtitle Note</label>
          <input type="text" name="careers_jobs_desc" class="visual-input-styled" value="<?= e($jobsDesc) ?>" placeholder="Explore our active remote openings...">
        </div>
      </div>

      <!-- Job Cards Container -->
      <div id="jobsListContainer" style="display: flex; flex-direction: column; gap: 20px; margin-bottom: 24px;">
        <?php foreach ($jobsList as $idx => $job): ?>
          <div class="job-editor-card" id="job_card_<?= $idx ?>" style="background: #FFF; border: 1.5px dashed rgba(74, 139, 140, 0.4); border-radius: 16px; padding: 24px; position: relative;">
            
            <!-- Card Header -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; border-bottom: 1px dashed rgba(74, 139, 140, 0.3); padding-bottom: 12px;">
              <div style="display: flex; align-items: center; gap: 10px;">
                <span style="display: inline-block; width: 28px; height: 28px; border-radius: 8px; background: var(--wdr-teal-pale); color: var(--wdr-teal); font-family: var(--wdr-font-mono); font-size: 12px; font-weight: 800; line-height: 28px; text-align: center;">
                  <?= sprintf('%02d', $idx + 1) ?>
                </span>
                <span style="font-weight: 700; font-size: 15px; color: var(--wdr-navy);">
                  <?= e($job['title'] ?: 'Untitled Position') ?>
                </span>
              </div>

              <div style="display: flex; align-items: center; gap: 14px;">
                <label style="display: flex; align-items: center; gap: 6px; cursor: pointer; font-size: 12px; font-weight: 700; color: var(--wdr-navy);">
                  <input type="checkbox" name="jobs[<?= $idx ?>][is_active]" value="1" <?= (!isset($job['is_active']) || !empty($job['is_active'])) ? 'checked' : '' ?> style="accent-color: var(--wdr-teal);"> Active
                </label>
                <button type="button" onclick="document.getElementById('job_card_<?= $idx ?>').remove()" class="btn-adm-action btn-adm-delete" title="Delete Role" style="padding: 4px 8px; font-size: 11px;">
                  <i class="ri-delete-bin-line"></i> Delete
                </button>
              </div>
            </div>

            <!-- Job Primary Fields -->
            <div style="display: grid; grid-template-columns: 1.2fr 0.8fr; gap: 16px; margin-bottom: 14px;">
              <div>
                <label class="visual-label-upper">Job Title <span style="color:#ef4444;">*</span></label>
                <input type="text" name="jobs[<?= $idx ?>][title]" class="visual-input-styled" required value="<?= e($job['title'] ?? '') ?>" placeholder="e.g. Senior Technical Writer — Cloud & DevOps" style="font-weight: 700;">
              </div>
              <div>
                <label class="visual-label-upper">URL Slug / Identifier</label>
                <input type="text" name="jobs[<?= $idx ?>][slug]" class="visual-input-styled" value="<?= e($job['slug'] ?? '') ?>" placeholder="e.g. senior-technical-writer-cloud-devops">
              </div>
            </div>

            <!-- Department & Filter Settings -->
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 14px; margin-bottom: 14px;">
              <div>
                <label class="visual-label-upper">Department Name</label>
                <input type="text" name="jobs[<?= $idx ?>][department]" class="visual-input-styled" value="<?= e($job['department'] ?? 'Editorial & Writing') ?>" placeholder="e.g. Technical Writing">
              </div>
              <div>
                <label class="visual-label-upper">Department Filter Slug</label>
                <input type="text" name="jobs[<?= $idx ?>][department_slug]" class="visual-input-styled" value="<?= e($job['department_slug'] ?? 'technical-writing') ?>" placeholder="e.g. technical-writing">
              </div>
              <div>
                <label class="visual-label-upper">Job Type</label>
                <input type="text" name="jobs[<?= $idx ?>][type]" class="visual-input-styled" value="<?= e($job['type'] ?? 'Full-Time') ?>" placeholder="e.g. Full-Time or Contract">
              </div>
              <div>
                <label class="visual-label-upper">Location / Remote</label>
                <input type="text" name="jobs[<?= $idx ?>][location]" class="visual-input-styled" value="<?= e($job['location'] ?? '100% Remote Global') ?>" placeholder="e.g. 100% Remote Global">
              </div>
            </div>

            <!-- Compensation & Tags -->
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 14px; margin-bottom: 14px;">
              <div>
                <label class="visual-label-upper">Salary / Compensation</label>
                <input type="text" name="jobs[<?= $idx ?>][salary]" class="visual-input-styled" value="<?= e($job['salary'] ?? '₹14,00,000 – ₹20,00,000 / yr') ?>" placeholder="e.g. ₹18,00,000 – ₹24,00,000 / yr" style="font-weight: 700;">
              </div>
              <div>
                <label class="visual-label-upper">Experience Level</label>
                <input type="text" name="jobs[<?= $idx ?>][experience]" class="visual-input-styled" value="<?= e($job['experience'] ?? '3+ Years Exp') ?>" placeholder="e.g. 5+ Years Exp">
              </div>
              <div>
                <label class="visual-label-upper">Skills Tags (Comma separated)</label>
                <input type="text" name="jobs[<?= $idx ?>][tags]" class="visual-input-styled" value="<?= e(is_array($job['tags'] ?? null) ? implode(', ', $job['tags']) : ($job['tags'] ?? '')) ?>" placeholder="e.g. Kubernetes, API Docs, Python">
              </div>
            </div>

            <!-- Card Excerpt & Overview -->
            <div style="margin-bottom: 14px;">
              <label class="visual-label-upper">Card Short Excerpt (Shown on 3x3 Grid)</label>
              <textarea name="jobs[<?= $idx ?>][excerpt]" class="visual-input-styled" rows="2" placeholder="Brief summary of the role..."><?= e($job['excerpt'] ?? '') ?></textarea>
            </div>

            <div style="margin-bottom: 14px;">
              <label class="visual-label-upper">Detailed Overview (Shown in Modal Popup)</label>
              <textarea name="jobs[<?= $idx ?>][overview]" class="visual-input-styled" rows="2" placeholder="Full context of what this writer or editor will achieve..."><?= e($job['overview'] ?? '') ?></textarea>
            </div>

            <div>
              <label class="visual-label-upper">Key Responsibilities (One bullet per line)</label>
              <?php 
              $respText = is_array($job['responsibilities'] ?? null) ? implode("\n", $job['responsibilities']) : ($job['responsibilities'] ?? '');
              ?>
              <textarea name="jobs[<?= $idx ?>][responsibilities]" class="visual-input-styled" rows="3" placeholder="Architect multi-part technical whitepapers...&#10;Author reproducible code recipes..."><?= e($respText) ?></textarea>
            </div>

          </div>
        <?php endforeach; ?>
      </div>

      <button type="submit" class="btn-adm btn-adm-primary"><i class="ri-save-line"></i> Save Job Openings</button>
    </div>
  </form>

  <script>
  let nextJobIdx = <?= count($jobsList) ?>;
  function addNewJobCard() {
    const container = document.getElementById('jobsListContainer');
    const idx = nextJobIdx++;
    const cardHtml = `
      <div class="job-editor-card" id="job_card_${idx}" style="background: #FFF; border: 1.5px dashed rgba(74, 139, 140, 0.4); border-radius: 16px; padding: 24px; position: relative;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; border-bottom: 1px dashed rgba(74, 139, 140, 0.3); padding-bottom: 12px;">
          <div style="display: flex; align-items: center; gap: 10px;">
            <span style="display: inline-block; width: 28px; height: 28px; border-radius: 8px; background: var(--wdr-teal); color: #FFF; font-family: var(--wdr-font-mono); font-size: 12px; font-weight: 800; line-height: 28px; text-align: center;">NEW</span>
            <span style="font-weight: 700; font-size: 15px; color: var(--wdr-navy);">New Job Opening</span>
          </div>
          <div style="display: flex; align-items: center; gap: 14px;">
            <label style="display: flex; align-items: center; gap: 6px; cursor: pointer; font-size: 12px; font-weight: 700; color: var(--wdr-navy);">
              <input type="checkbox" name="jobs[${idx}][is_active]" value="1" checked style="accent-color: var(--wdr-teal);"> Active
            </label>
            <button type="button" onclick="document.getElementById('job_card_${idx}').remove()" class="btn-adm-action btn-adm-delete" style="padding: 4px 8px; font-size: 11px;">
              <i class="ri-delete-bin-line"></i> Delete
            </button>
          </div>
        </div>

        <div style="display: grid; grid-template-columns: 1.2fr 0.8fr; gap: 16px; margin-bottom: 14px;">
          <div>
            <label class="visual-label-upper">Job Title <span style="color:#ef4444;">*</span></label>
            <input type="text" name="jobs[${idx}][title]" class="visual-input-styled" required placeholder="e.g. Senior Brand Copywriter" style="font-weight: 700;">
          </div>
          <div>
            <label class="visual-label-upper">URL Slug / Identifier</label>
            <input type="text" name="jobs[${idx}][slug]" class="visual-input-styled" placeholder="e.g. senior-brand-copywriter">
          </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 14px; margin-bottom: 14px;">
          <div>
            <label class="visual-label-upper">Department Name</label>
            <input type="text" name="jobs[${idx}][department]" class="visual-input-styled" value="Editorial & Writing" placeholder="e.g. Editorial & Writing">
          </div>
          <div>
            <label class="visual-label-upper">Department Filter Slug</label>
            <input type="text" name="jobs[${idx}][department_slug]" class="visual-input-styled" value="editorial-writing" placeholder="e.g. editorial-writing">
          </div>
          <div>
            <label class="visual-label-upper">Job Type</label>
            <input type="text" name="jobs[${idx}][type]" class="visual-input-styled" value="Full-Time">
          </div>
          <div>
            <label class="visual-label-upper">Location / Remote</label>
            <input type="text" name="jobs[${idx}][location]" class="visual-input-styled" value="100% Remote Global">
          </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 14px; margin-bottom: 14px;">
          <div>
            <label class="visual-label-upper">Salary / Compensation</label>
            <input type="text" name="jobs[${idx}][salary]" class="visual-input-styled" value="₹12,00,000 – ₹18,00,000 / yr" style="font-weight: 700;">
          </div>
          <div>
            <label class="visual-label-upper">Experience Level</label>
            <input type="text" name="jobs[${idx}][experience]" class="visual-input-styled" value="3+ Years Exp">
          </div>
          <div>
            <label class="visual-label-upper">Skills Tags (Comma separated)</label>
            <input type="text" name="jobs[${idx}][tags]" class="visual-input-styled" placeholder="e.g. Brand Voice, Manifestos, Copy">
          </div>
        </div>

        <div style="margin-bottom: 14px;">
          <label class="visual-label-upper">Card Short Excerpt</label>
          <textarea name="jobs[${idx}][excerpt]" class="visual-input-styled" rows="2" placeholder="Brief summary of the role..."></textarea>
        </div>

        <div style="margin-bottom: 14px;">
          <label class="visual-label-upper">Detailed Overview</label>
          <textarea name="jobs[${idx}][overview]" class="visual-input-styled" rows="2" placeholder="Full context for modal popup..."></textarea>
        </div>

        <div>
          <label class="visual-label-upper">Key Responsibilities (One bullet per line)</label>
          <textarea name="jobs[${idx}][responsibilities]" class="visual-input-styled" rows="3" placeholder="Lead creative direction...&#10;Collaborate with design team..."></textarea>
        </div>
      </div>
    `;
    container.insertAdjacentHTML('beforeend', cardHtml);
    document.getElementById(`job_card_${idx}`).scrollIntoView({ behavior: 'smooth' });
  }
  </script>
  <?php endif; ?>


  <!-- ═══════════════════════════════════════════
       TAB 04: WORKING PHILOSOPHY (4 CULTURE PILLARS)
       ═══════════════════════════════════════════ -->
  <?php if ($activeTab === 'sec04'): ?>
  <form method="POST" action="?tab=sec04">
    <?= CSRF::field() ?>
    <div class="visual-studio-card">
      <div style="margin-bottom: 20px;">
        <span class="visual-badge"><i class="ri-compass-3-line"></i> SECTION 04 — WORKING PHILOSOPHY</span>
        <h2 class="visual-display-heading" style="margin: 8px 0 4px;">Our Editorial Working Philosophy (4 Culture Pillars)</h2>
        <p style="color: var(--admin-muted); font-size: 13px; margin: 0;">Pillars defining our uncompromising standards, async freedom, and top 10% compensation.</p>
      </div>

      <div style="background: #FFF; border: 1.5px dashed rgba(74, 139, 140, 0.4); border-radius: 14px; padding: 20px; margin-bottom: 24px;">
        <div style="display: grid; grid-template-columns: 1fr 1.5fr; gap: 16px; margin-bottom: 12px;">
          <div>
            <label class="visual-label-upper">Section Eyebrow Badge</label>
            <input type="text" name="careers_pillars_badge" class="visual-input-styled" value="<?= e($pillarsBadge) ?>" placeholder="e.g. OUR WORKING PHILOSOPHY">
          </div>
          <div>
            <label class="visual-label-upper">Section Main Heading</label>
            <input type="text" name="careers_pillars_title" class="visual-input-styled" value="<?= e($pillarsTitle) ?>" placeholder="e.g. Built For Writers Who Care Deeply About Craft" style="font-weight: 700;">
          </div>
        </div>
        <div>
          <label class="visual-label-upper">Section Narrative Paragraph</label>
          <textarea name="careers_pillars_desc" class="visual-input-styled" rows="2"><?= e($pillarsDesc) ?></textarea>
        </div>
      </div>

      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 18px; margin-bottom: 24px;">
        <?php foreach ($pillarsList as $idx => $pl): ?>
          <div style="background: #FFF; border: 1.5px dashed rgba(74, 139, 140, 0.4); border-radius: 14px; padding: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; border-bottom: 1px dashed rgba(74, 139, 140, 0.25); padding-bottom: 8px;">
              <span style="font-family: var(--wdr-font-mono); font-size: 11px; font-weight: 800; color: var(--wdr-teal); text-transform: uppercase;">
                Culture Pillar #0<?= $idx + 1 ?>
              </span>
            </div>
            <div style="margin-bottom: 10px;">
              <label class="visual-label-upper">RemixIcon Class</label>
              <input type="text" name="pillars[<?= $idx ?>][icon]" class="visual-input-styled" value="<?= e($pl['icon'] ?? 'ri-quill-pen-line') ?>" placeholder="e.g. ri-compass-3-line">
            </div>
            <div style="margin-bottom: 10px;">
              <label class="visual-label-upper">Pillar Title</label>
              <input type="text" name="pillars[<?= $idx ?>][title]" class="visual-input-styled" value="<?= e($pl['title'] ?? '') ?>" placeholder="Title" style="font-weight: 700;">
            </div>
            <div>
              <label class="visual-label-upper">Pillar Narrative</label>
              <textarea name="pillars[<?= $idx ?>][desc]" class="visual-input-styled" rows="3" placeholder="Pillar explanation..."><?= e($pl['desc'] ?? '') ?></textarea>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <button type="submit" class="btn-adm btn-adm-primary"><i class="ri-save-line"></i> Save Working Philosophy</button>
    </div>
  </form>
  <?php endif; ?>


  <!-- ═══════════════════════════════════════════
       TAB 05: 4-STAGE HIRING PROTOCOL
       ═══════════════════════════════════════════ -->
  <?php if ($activeTab === 'sec05'): ?>
  <form method="POST" action="?tab=sec05">
    <?= CSRF::field() ?>
    <div class="visual-studio-card">
      <div style="margin-bottom: 20px;">
        <span class="visual-badge"><i class="ri-node-tree"></i> SECTION 05 — HIRING PROTOCOL</span>
        <h2 class="visual-display-heading" style="margin: 8px 0 4px;">4-Stage Respectful Hiring Protocol</h2>
        <p style="color: var(--admin-muted); font-size: 13px; margin: 0;">Transparent candidate journey from initial portfolio audit to paid micro-sprint and contract offer.</p>
      </div>

      <div style="background: #FFF; border: 1.5px dashed rgba(74, 139, 140, 0.4); border-radius: 14px; padding: 20px; margin-bottom: 24px;">
        <div style="display: grid; grid-template-columns: 1fr 1.5fr; gap: 16px; margin-bottom: 12px;">
          <div>
            <label class="visual-label-upper">Section Eyebrow Badge</label>
            <input type="text" name="careers_protocol_badge" class="visual-input-styled" value="<?= e($protocolBadge) ?>" placeholder="e.g. OUR 4-STAGE HIRING PROTOCOL">
          </div>
          <div>
            <label class="visual-label-upper">Section Main Heading</label>
            <input type="text" name="careers_protocol_title" class="visual-input-styled" value="<?= e($protocolTitle) ?>" placeholder="e.g. Transparent, Fast & Respectful of Your Time" style="font-weight: 700;">
          </div>
        </div>
        <div>
          <label class="visual-label-upper">Section Narrative Paragraph</label>
          <input type="text" name="careers_protocol_desc" class="visual-input-styled" value="<?= e($protocolDesc) ?>" placeholder="We respond to every candidate application within 48 hours...">
        </div>
      </div>

      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 18px; margin-bottom: 24px;">
        <?php foreach ($protocolList as $idx => $pr): ?>
          <div style="background: #FFF; border: 1.5px dashed rgba(74, 139, 140, 0.4); border-radius: 14px; padding: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; border-bottom: 1px dashed rgba(74, 139, 140, 0.25); padding-bottom: 8px;">
              <span style="font-family: var(--wdr-font-mono); font-size: 11px; font-weight: 800; color: var(--wdr-teal); text-transform: uppercase;">
                Stage #0<?= $idx + 1 ?>
              </span>
              <input type="text" name="protocol[<?= $idx ?>][sla]" class="visual-input-styled" value="<?= e($pr['sla'] ?? '48-Hour SLA') ?>" placeholder="SLA Badge" style="width: 120px; font-size: 11px; padding: 4px 8px; text-align: right; font-weight: 700; font-family: var(--wdr-font-mono);">
            </div>
            <div style="display: grid; grid-template-columns: 70px 1fr; gap: 10px; margin-bottom: 10px;">
              <div>
                <label class="visual-label-upper">Number</label>
                <input type="text" name="protocol[<?= $idx ?>][num]" class="visual-input-styled" value="<?= e($pr['num'] ?? sprintf('%02d', $idx + 1)) ?>" style="font-weight: 800; font-family: var(--wdr-font-mono); text-align: center;">
              </div>
              <div>
                <label class="visual-label-upper">Stage Title</label>
                <input type="text" name="protocol[<?= $idx ?>][title]" class="visual-input-styled" value="<?= e($pr['title'] ?? '') ?>" placeholder="Stage Title" style="font-weight: 700;">
              </div>
            </div>
            <div>
              <label class="visual-label-upper">Stage Description</label>
              <textarea name="protocol[<?= $idx ?>][desc]" class="visual-input-styled" rows="2" placeholder="Stage explanation..."><?= e($pr['desc'] ?? '') ?></textarea>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <button type="submit" class="btn-adm btn-adm-primary"><i class="ri-save-line"></i> Save Hiring Protocol</button>
    </div>
  </form>
  <?php endif; ?>


  <!-- ═══════════════════════════════════════════
       TAB 06: JOB APPLICATIONS & CANDIDATE RESUMES
       ═══════════════════════════════════════════ -->
  <?php if ($activeTab === 'applications'): ?>
  <div class="visual-studio-card">
    
    <div style="margin-bottom: 24px;">
      <span class="visual-badge"><i class="ri-user-shared-line"></i> SECTION 06 — CANDIDATE SUBMISSIONS</span>
      <h2 class="visual-display-heading" style="margin: 8px 0 4px;">Candidate Job Applications &amp; Resumes</h2>
      <p style="color: var(--admin-muted); font-size: 13px; margin: 0;">Review incoming candidate submissions, contact details, writing portfolios, and download original resumes.</p>
    </div>

    <!-- KPI Statistics Grid -->
    <div class="kpi-stats-grid">
      <div class="kpi-stat-card" style="background: #FFFFFF; border: 1.5px dashed rgba(74, 139, 140, 0.4); box-shadow: 0 2px 8px rgba(15,30,54,0.03);">
        <div class="kpi-stat-val" style="color: var(--wdr-navy);"><?= $totalAppsCount ?></div>
        <div class="kpi-stat-lbl" style="color: var(--wdr-teal);"><i class="ri-file-user-line"></i> Total Submissions</div>
      </div>
      <div class="kpi-stat-card" style="background: #FFFBEB; border: 1.5px solid #FDE68A;">
        <div class="kpi-stat-val" style="color: #92400E;"><?= $pendingCount ?></div>
        <div class="kpi-stat-lbl" style="color: #B45309;"><i class="ri-time-line"></i> Pending Review</div>
      </div>
      <div class="kpi-stat-card" style="background: #ECFDF5; border: 1.5px solid #A7F3D0;">
        <div class="kpi-stat-val" style="color: #065F46;"><?= $shortlistCount ?></div>
        <div class="kpi-stat-lbl" style="color: #047857;"><i class="ri-star-line"></i> Shortlisted</div>
      </div>
      <div class="kpi-stat-card" style="background: #EFF6FF; border: 1.5px solid #BFDBFE;">
        <div class="kpi-stat-val" style="color: #1E40AF;"><?= $reviewedCount ?></div>
        <div class="kpi-stat-lbl" style="color: #2563EB;"><i class="ri-eye-line"></i> Reviewed</div>
      </div>
      <div class="kpi-stat-card" style="background: #FEF2F2; border: 1.5px solid #FECACA;">
        <div class="kpi-stat-val" style="color: #991B1B;"><?= $rejectedCount ?></div>
        <div class="kpi-stat-lbl" style="color: #DC2626;"><i class="ri-close-circle-line"></i> Rejected</div>
      </div>
    </div>

    <!-- Status Filters Bar -->
    <div style="display: flex; gap: 8px; margin-bottom: 24px; flex-wrap: wrap;">
      <a href="?tab=applications&status=all" class="btn-adm-action" style="<?= $statusFilter === 'all' ? 'background: var(--wdr-navy); color: #FFF; border-color: var(--wdr-navy);' : 'background: #FFF; border: 1.5px dashed rgba(74,139,140,0.35); color: var(--wdr-navy);' ?>">
        <span>All Candidates</span> <strong style="font-family: var(--wdr-font-mono);">(<?= $totalAppsCount ?>)</strong>
      </a>
      <a href="?tab=applications&status=pending" class="btn-adm-action" style="<?= $statusFilter === 'pending' ? 'background: #D97706; color: #FFF; border-color: #D97706;' : 'background: #FFF; border: 1.5px dashed #FCD34D; color: #92400E;' ?>">
        <span>Pending</span> <strong style="font-family: var(--wdr-font-mono);">(<?= $pendingCount ?>)</strong>
      </a>
      <a href="?tab=applications&status=shortlisted" class="btn-adm-action" style="<?= $statusFilter === 'shortlisted' ? 'background: #059669; color: #FFF; border-color: #059669;' : 'background: #FFF; border: 1.5px dashed #A7F3D0; color: #065F46;' ?>">
        <span>Shortlisted</span> <strong style="font-family: var(--wdr-font-mono);">(<?= $shortlistCount ?>)</strong>
      </a>
      <a href="?tab=applications&status=reviewed" class="btn-adm-action" style="<?= $statusFilter === 'reviewed' ? 'background: #2563EB; color: #FFF; border-color: #2563EB;' : 'background: #FFF; border: 1.5px dashed #BFDBFE; color: #1E40AF;' ?>">
        <span>Reviewed</span> <strong style="font-family: var(--wdr-font-mono);">(<?= $reviewedCount ?>)</strong>
      </a>
      <a href="?tab=applications&status=rejected" class="btn-adm-action" style="<?= $statusFilter === 'rejected' ? 'background: #DC2626; color: #FFF; border-color: #DC2626;' : 'background: #FFF; border: 1.5px dashed #FECACA; color: #991B1B;' ?>">
        <span>Rejected</span> <strong style="font-family: var(--wdr-font-mono);">(<?= $rejectedCount ?>)</strong>
      </a>
    </div>

    <!-- Applications Table -->
    <?php if (empty($applications)): ?>
      <div style="text-align: center; padding: 56px 24px; background: #FFF; border-radius: 18px; border: 1.5px solid #E2E8F0; box-shadow: 0 4px 20px rgba(15, 30, 54, 0.04);">
        <i class="ri-inbox-line" style="font-size: 48px; color: var(--wdr-teal); opacity: 0.6;"></i>
        <h3 style="margin: 14px 0 6px; font-family: var(--wdr-font-display); font-size: 20px; color: var(--wdr-navy);">No Applications Found</h3>
        <p style="color: var(--admin-muted); font-size: 13.5px; margin: 0; max-width: 480px; margin-left: auto; margin-right: auto;">
          <?= $statusFilter !== 'all' ? 'No candidate applications currently match this status filter.' : 'When candidates submit their profiles through the frontend Careers page, they will instantly appear in this table.' ?>
        </p>
      </div>
    <?php else: ?>
      <div class="admin-card-table-wrapper">
        <div class="table-top-bar">
          <div style="font-size: 13.5px; font-weight: 700; color: var(--wdr-navy); display: flex; align-items: center; gap: 8px;">
            <i class="ri-user-follow-line" style="color: var(--wdr-teal); font-size: 16px;"></i> Candidates Applications Directory
          </div>
          <span class="visual-badge" style="padding: 4px 12px; font-size: 11px; font-weight: 700;">
            Showing <?= count($applications) ?> Records
          </span>
        </div>

        <div class="table-responsive">
          <table class="admin-table">
            <thead>
              <tr>
                <th style="width: 140px;">Date &amp; Time</th>
                <th style="min-width: 220px;">Applicant Profile</th>
                <th style="min-width: 230px;">Role Applied For</th>
                <th style="min-width: 240px;">Contact &amp; Location</th>
                <th style="text-align: center; width: 170px;">Resume Document</th>
                <th style="width: 150px;">Application Status</th>
                <th style="text-align: right; width: 130px;">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($applications as $app): 
                  $appStatus = $app['status'] ?: 'pending';
              ?>
                <tr>
                  <!-- Date & Time -->
                  <td>
                    <div style="font-weight: 700; color: #1E293B; font-size: 12.5px; font-family: var(--wdr-font-mono);">
                      <?= date('M d, Y', strtotime($app['created_at'])) ?>
                    </div>
                    <div style="font-size: 11px; color: #64748B; font-family: var(--wdr-font-mono); margin-top: 2px; display: flex; align-items: center; gap: 4px;">
                      <i class="ri-time-line" style="color: var(--wdr-teal);"></i> <?= date('h:i A', strtotime($app['created_at'])) ?>
                    </div>
                  </td>

                  <!-- Applicant Profile with Avatar -->
                  <td>
                    <div style="display: flex; align-items: center; gap: 12px;">
                      <div class="candidate-avatar-circle">
                        <?= strtoupper(substr($app['full_name'] ?: 'C', 0, 1)) ?>
                      </div>
                      <div>
                        <div style="font-weight: 700; color: var(--wdr-navy); font-size: 14px; line-height: 1.3;">
                          <?= e($app['full_name']) ?>
                        </div>
                        <?php if (!empty($app['experience_years'])): ?>
                          <span style="display: inline-block; font-size: 11px; color: var(--wdr-teal); font-family: var(--wdr-font-mono); font-weight: 700; background: rgba(74,139,140,0.12); padding: 1px 7px; border-radius: 6px; margin-top: 3px;">
                            <?= e($app['experience_years']) ?> Exp
                          </span>
                        <?php endif; ?>
                      </div>
                    </div>
                  </td>

                  <!-- Role Applied For -->
                  <td>
                    <div style="font-size: 13.5px; font-weight: 700; color: var(--wdr-navy); line-height: 1.35; margin-bottom: 3px;">
                      <?= e($app['job_title']) ?>
                    </div>
                    <?php if (!empty($app['expected_salary'])): ?>
                      <div style="font-size: 11.5px; color: #64748B; font-family: var(--wdr-font-mono);">
                        Expect: <strong style="color: var(--wdr-teal);"><?= e($app['expected_salary']) ?></strong>
                      </div>
                    <?php endif; ?>
                  </td>

                  <!-- Contact Details & City -->
                  <td>
                    <div style="font-size: 12.5px; margin-bottom: 3px;">
                      <a href="mailto:<?= e($app['email']) ?>" style="color: var(--wdr-navy); text-decoration: none; font-weight: 600;">
                        <i class="ri-mail-line" style="color: var(--wdr-teal); margin-right: 4px;"></i><?= e($app['email']) ?>
                      </a>
                    </div>
                    <div style="font-size: 12px; color: #64748B; margin-bottom: 2px;">
                      <a href="tel:<?= e($app['phone']) ?>" style="color: inherit; text-decoration: none;">
                        <i class="ri-phone-line" style="color: var(--wdr-teal); margin-right: 4px;"></i><?= e($app['phone']) ?>
                      </a>
                    </div>
                    <?php if (!empty($app['address'])): ?>
                      <div style="font-size: 11.5px; color: #64748B;">
                        <i class="ri-map-pin-line" style="color: var(--wdr-teal); margin-right: 3px;"></i><?= e($app['address']) ?>
                      </div>
                    <?php endif; ?>
                  </td>

                  <!-- Resume Document Preview & Download -->
                  <td style="text-align: center;">
                    <?php if (!empty($app['resume_path'])): 
                      $resumeFileUrl = url(ltrim($app['resume_path'], '/'));
                    ?>
                      <div style="display: inline-flex; align-items: center; gap: 6px; justify-content: center;">
                        <a href="<?= $resumeFileUrl ?>" target="_blank" class="btn-adm-action" style="background: #EFF6FF; border: 1.5px solid #BFDBFE; color: #1E40AF; padding: 6px 11px; font-size: 11.5px; font-weight: 700; display: inline-flex; align-items: center; gap: 5px;" title="Open &amp; Preview Resume in New Tab">
                          <i class="ri-file-text-line" style="font-size: 15px; color: #2563EB;"></i> View PDF
                        </a>
                        <a href="<?= $resumeFileUrl ?>" download class="btn-adm-action" style="background: #FAF8F5; border: 1.5px solid #CBD5E1; color: var(--wdr-navy); padding: 6px 9px; font-size: 13px;" title="Direct Download Resume File">
                          <i class="ri-download-2-line"></i>
                        </a>
                      </div>
                    <?php else: ?>
                      <span style="font-size: 11px; color: #94A3B8; font-style: italic;">No file attached</span>
                    <?php endif; ?>
                  </td>

                  <!-- Status Selector Dropdown -->
                  <td>
                    <form method="POST" action="?tab=applications&status=<?= urlencode($statusFilter) ?>" style="display: inline-block;">
                      <?= CSRF::field() ?>
                      <input type="hidden" name="action" value="update_app_status">
                      <input type="hidden" name="app_id" value="<?= (int)$app['id'] ?>">
                      
                      <select name="status" onchange="this.form.submit()" style="padding: 6px 10px; border-radius: 8px; font-size: 11.5px; font-weight: 700; cursor: pointer; outline: none; transition: all 0.2s ease; <?php 
                        if ($appStatus === 'shortlisted') echo 'background: #ECFDF5; color: #065F46; border: 1.5px solid #A7F3D0;';
                        elseif ($appStatus === 'reviewed') echo 'background: #EFF6FF; color: #1E40AF; border: 1.5px solid #BFDBFE;';
                        elseif ($appStatus === 'rejected') echo 'background: #FEF2F2; color: #991B1B; border: 1.5px solid #FECACA;';
                        else echo 'background: #FEF3C7; color: #92400E; border: 1.5px solid #FCD34D;';
                      ?>">
                        <option value="pending" <?= $appStatus === 'pending' ? 'selected' : '' ?>>⏳ Pending</option>
                        <option value="reviewed" <?= $appStatus === 'reviewed' ? 'selected' : '' ?>>👀 Reviewed</option>
                        <option value="shortlisted" <?= $appStatus === 'shortlisted' ? 'selected' : '' ?>>⭐ Shortlisted</option>
                        <option value="rejected" <?= $appStatus === 'rejected' ? 'selected' : '' ?>>❌ Rejected</option>
                      </select>
                    </form>
                  </td>

                  <!-- Actions Column -->
                  <td style="text-align: right;">
                    <div class="table-actions" style="justify-content: flex-end;">
                      <!-- View Profile Modal Button -->
                      <button type="button" onclick='showApplicantDetails(<?= json_encode($app, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)' class="btn-adm-action btn-adm-edit" title="View Full Application Profile &amp; Cover Note">
                        <i class="ri-eye-line"></i> View
                      </button>

                      <!-- Delete Form -->
                      <form method="POST" action="?tab=applications&status=<?= urlencode($statusFilter) ?>" onsubmit="return confirm('Are you sure you want to permanently delete this application?');" style="display: inline;">
                        <?= CSRF::field() ?>
                        <input type="hidden" name="action" value="delete_application">
                        <input type="hidden" name="app_id" value="<?= (int)$app['id'] ?>">
                        <button type="submit" class="btn-adm-action btn-adm-delete" title="Delete Application">
                          <i class="ri-delete-bin-line"></i>
                        </button>
                      </form>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    <?php endif; ?>

  </div>

  <!-- Applicant Details Modal -->
  <div id="applicantModal" style="display: none; position: fixed; inset: 0; background: rgba(15,30,54,0.75); z-index: 10000; align-items: center; justify-content: center; padding: 24px;">
    <div style="background: #FFF; border-radius: 20px; max-width: 650px; width: 100%; max-height: 90vh; overflow-y: auto; padding: 28px; position: relative; border: 1.5px dashed rgba(74, 139, 140, 0.4); box-shadow: 0 20px 40px rgba(0,0,0,0.3);">
      
      <button type="button" onclick="document.getElementById('applicantModal').style.display='none'" style="position: absolute; top: 20px; right: 20px; background: none; border: none; font-size: 24px; color: var(--admin-muted); cursor: pointer;">
        <i class="ri-close-line"></i>
      </button>

      <span class="visual-badge" style="margin-bottom: 6px;"><i class="ri-user-star-line"></i> CANDIDATE PROFILE</span>
      <h2 id="modal_app_name" style="font-family: var(--wdr-font-display); font-size: 22px; color: var(--wdr-navy); margin: 0 0 4px;"></h2>
      <p id="modal_app_job" style="color: var(--wdr-teal); font-weight: 700; font-size: 14px; margin: 0 0 20px;"></p>

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; background: #FAF8F5; padding: 16px; border-radius: 12px; border: 1px dashed rgba(74,139,140,0.35); margin-bottom: 20px;">
        <div>
          <div style="font-size: 11px; font-weight: 700; color: var(--admin-muted); text-transform: uppercase;">Email Address</div>
          <div id="modal_app_email" style="font-size: 13px; font-weight: 600; color: var(--wdr-navy);"></div>
        </div>
        <div>
          <div style="font-size: 11px; font-weight: 700; color: var(--admin-muted); text-transform: uppercase;">Phone / WhatsApp</div>
          <div id="modal_app_phone" style="font-size: 13px; font-weight: 600; color: var(--wdr-navy);"></div>
        </div>
        <div>
          <div style="font-size: 11px; font-weight: 700; color: var(--admin-muted); text-transform: uppercase;">Location / City</div>
          <div id="modal_app_address" style="font-size: 13px; font-weight: 600; color: var(--wdr-navy);"></div>
        </div>
        <div>
          <div style="font-size: 11px; font-weight: 700; color: var(--admin-muted); text-transform: uppercase;">Experience &amp; Salary Expectation</div>
          <div id="modal_app_exp_sal" style="font-size: 13px; font-weight: 600; color: var(--wdr-navy);"></div>
        </div>
      </div>

      <div style="margin-bottom: 18px;" id="modal_app_linkedin_box">
        <label class="visual-label-upper">LinkedIn / Portfolio Profile</label>
        <div id="modal_app_linkedin" style="font-size: 13px; word-break: break-all;"></div>
      </div>

      <div style="margin-bottom: 18px;">
        <label class="visual-label-upper">Published Writing Samples</label>
        <div id="modal_app_samples" style="background: #FAF8F5; padding: 12px; border-radius: 8px; font-size: 13px; line-height: 1.6; word-break: break-all; white-space: pre-wrap; color: var(--wdr-navy);"></div>
      </div>

      <div style="margin-bottom: 24px;">
        <label class="visual-label-upper">Why WORDORA? (Cover Note)</label>
        <div id="modal_app_cover" style="background: #FAF8F5; padding: 12px; border-radius: 8px; font-size: 13px; line-height: 1.6; white-space: pre-wrap; color: var(--wdr-navy);"></div>
      </div>

      <div id="modal_app_resume_action" style="text-align: center;"></div>

    </div>
  </div>

  <script>
  function showApplicantDetails(app) {
    document.getElementById('modal_app_name').innerText = app.full_name || 'Applicant';
    document.getElementById('modal_app_job').innerText = 'Applied for: ' + (app.job_title || 'General Application');
    document.getElementById('modal_app_email').innerText = app.email || '—';
    document.getElementById('modal_app_phone').innerText = app.phone || '—';
    document.getElementById('modal_app_address').innerText = app.address || '—';
    document.getElementById('modal_app_exp_sal').innerText = (app.experience_years ? app.experience_years + ' Exp' : 'Exp: N/A') + ' • ' + (app.expected_salary || 'Salary: Negotiable');
    
    if (app.linkedin_url) {
      document.getElementById('modal_app_linkedin').innerHTML = `<a href="${app.linkedin_url}" target="_blank" style="color: var(--wdr-teal); font-weight: 700; text-decoration: underline;">${app.linkedin_url} <i class="ri-external-link-line"></i></a>`;
      document.getElementById('modal_app_linkedin_box').style.display = 'block';
    } else {
      document.getElementById('modal_app_linkedin_box').style.display = 'none';
    }

    document.getElementById('modal_app_samples').innerText = app.writing_samples || 'None provided.';
    document.getElementById('modal_app_cover').innerText = app.cover_note || 'None provided.';

    if (app.resume_path) {
      const cleanPath = app.resume_path.replace(/^\//, '');
      const resumeUrl = '<?= url('') ?>/' + cleanPath;
      document.getElementById('modal_app_resume_action').innerHTML = `
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
          <a href="${resumeUrl}" target="_blank" class="btn-adm btn-adm-outline" style="justify-content: center; font-weight: 700; padding: 10px 16px;">
            <i class="ri-external-link-line"></i> Preview PDF in Tab
          </a>
          <a href="${resumeUrl}" download class="btn-adm btn-adm-primary" style="justify-content: center; font-weight: 700; padding: 10px 16px;">
            <i class="ri-file-download-line"></i> Download Resume
          </a>
        </div>
      `;
    } else {
      document.getElementById('modal_app_resume_action').innerHTML = `
        <span style="color: var(--admin-muted); font-size: 12px; font-style: italic;">No resume file attached.</span>
      `;
    }

    document.getElementById('applicantModal').style.display = 'flex';
  }
  </script>
  <?php endif; ?>

</div>
